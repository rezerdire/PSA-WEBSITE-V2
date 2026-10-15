<?php

namespace App\Filament\Resources\BoothAttendances\Tables;

use App\Models\Booth;
use App\Models\BoothAttendance;
use App\Models\Chapter;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BoothAttendancesTable
{
    protected static function fullName($record): string
    {
        return trim(preg_replace(
            '/\s+/',
            ' ',
            "{$record->mem_first_name} {$record->mem_middle_name} {$record->mem_last_name}"
        ));
    }

    public static function configure(Table $table): Table
    {
        $members  = (new Member)->getTable();
        $chapters = (new Chapter)->getTable();

        return $table
            // One row per attendee (with name and chapter joined in)
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->leftJoin($members, "{$members}.member_id_no", '=', 'booth_attendances.member_id_no')
                ->leftJoin($chapters, "{$chapters}.psa_chapter_code", '=', "{$members}.psa_chapter_code")
                ->select([
                    'booth_attendances.member_id_no',
                    DB::raw('MIN(booth_attendances.attendance_id) as attendance_id'),
                    DB::raw('COUNT(*) as booths_count'),
                    DB::raw('MAX(booth_attendances.visited_at) as last_visit'),
                    DB::raw("MAX({$members}.mem_first_name) as mem_first_name"),
                    DB::raw("MAX({$members}.mem_middle_name) as mem_middle_name"),
                    DB::raw("MAX({$members}.mem_last_name) as mem_last_name"),
                    DB::raw("MAX({$chapters}.psa_chapter_desc) as chapter_desc"),
                ])
                ->groupBy('booth_attendances.member_id_no')
            )
            ->defaultSort('last_visit', 'desc')
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('member_id_no')
                    ->label('PSA ID')
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where('booth_attendances.member_id_no', 'like', "%{$search}%")),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->state(fn ($record) => self::fullName($record))
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where(function (Builder $q) use ($search, $members) {
                            $q->where("{$members}.mem_first_name", 'like', "%{$search}%")
                                ->orWhere("{$members}.mem_middle_name", 'like', "%{$search}%")
                                ->orWhere("{$members}.mem_last_name", 'like', "%{$search}%");
                        })),

                TextColumn::make('chapter_desc')
                    ->label('Chapter')
                    ->placeholder('-')
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where("{$chapters}.psa_chapter_desc", 'like', "%{$search}%")),

                TextColumn::make('booths_count')
                    ->label('Booths visited')
                    ->badge()
                    ->suffix(fn () => ' / ' . Booth::count())
                    ->sortable(),

                TextColumn::make('last_visit')
                    ->label('Last visit')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // Clicking a row opens the "View" slide-over
            ->recordAction(ViewAction::class)
            ->recordActions([
                ViewAction::make()
                    ->label('View booths')
                    ->slideOver()
                    ->modalHeading(fn ($record) => 'Booths visited – ' . (self::fullName($record) ?: $record->member_id_no))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        TextEntry::make('attendee_name')
                            ->label('Name')
                            ->state(fn ($record) => self::fullName($record) ?: '-')
                            ->weight('bold'),

                        TextEntry::make('attendee_id')
                            ->label('PSA ID')
                            ->state(fn ($record) => $record->member_id_no),

                        TextEntry::make('attendee_chapter')
                            ->label('Chapter')
                            ->state(fn ($record) => $record->chapter_desc ?: '-'),

                        TextEntry::make('summary')
                            ->hiddenLabel()
                            ->state(fn ($record) => $record->booths_count . ' of ' . Booth::count() . ' booths visited')
                            ->weight('bold'),

                        RepeatableEntry::make('visits')
                            ->hiddenLabel()
                            ->state(fn ($record) => BoothAttendance::with('booth')
                                ->where('member_id_no', $record->member_id_no)
                                ->orderBy('visited_at')
                                ->get()
                                ->map(fn ($a) => [
                                    'booth_name' => $a->booth?->booth_name,
                                    'booth_type' => $a->booth?->booth_type,
                                    'location'   => $a->booth?->location,
                                    'visited_at' => $a->visited_at,
                                ])
                                ->all()
                            )
                            ->schema([
                                TextEntry::make('booth_name')->label('Booth'),
                                TextEntry::make('location')->placeholder('-'),
                                TextEntry::make('visited_at')->label('Visited at')->dateTime(),
                            ])
                            ->columns(3),
                    ]),

                // Replaces edit/delete, since each row now represents many scans
                Action::make('resetVisits')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This removes ALL booth visits recorded for this attendee.')
                    ->action(fn ($record) => BoothAttendance::where('member_id_no', $record->member_id_no)->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
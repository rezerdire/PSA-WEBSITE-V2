<?php

namespace App\Filament\Resources\WorkshopRegistrations\Tables;
use App\Models\WorkshopRegistration;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkshopRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            // Every row opens the hidden view action; only Edit remains visible.
            ->recordAction('view_record')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('psa_id')
                    ->label('PSA ID')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn ($state): string => str_starts_with((string) $state, 'NMW_')
                        ? 'danger'
                        : 'primary')
                    ->searchable(),

                TextColumn::make('full_name')
                    ->label('Full name')
                    ->grow()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->where('mem_first_name', 'like', "%{$search}%")
                                ->orWhere('mem_middle_name', 'like', "%{$search}%")
                                ->orWhere('mem_last_name', 'like', "%{$search}%")
                                ->orWhereRaw(
                                    "CONCAT(mem_first_name, ' ', mem_last_name) like ?",
                                    ["%{$search}%"],
                                );
                        });
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('mem_last_name', $direction)
                        ->orderBy('mem_first_name', $direction)),

                TextColumn::make('mem_type')
                    ->label('Member type')
                    ->badge()
                    ->color(fn ($state): string => match ((string) $state) {
                        'Non-member' => 'danger',
                        'Regular Member' => 'info',
                        'Life Member' => 'success',
                        'Trainee Member' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('prc_number')
                    ->label('PRC number')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('workshops')
                    ->label('Workshops')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ((string) $state) {
                        'RA' => 'RA',
                        'AIRWAY' => 'AIRWAY',
                        'POCUS' => 'POCUS',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ((string) $state) {
                        'RA' => 'danger',
                        'AIRWAY' => 'success',
                        'POCUS' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y g:i A')
                    ->timezone('Asia/Manila')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('mem_type')
                    ->label('Member type')
                    ->options([
                        'Regular Member' => 'Regular Member',
                        'Life Member' => 'Life Member',
                        'Trainee Member' => 'Trainee Member',
                        'Non-member' => 'Non-member',
                    ])
                    ->native(false),

                SelectFilter::make('workshop')
                    ->label('Workshop')
                    ->multiple()
                    ->options([
                        'RA' => 'Regional Anesthesia (RA)',
                        'AIRWAY' => 'Airway',
                        'POCUS' => 'POCUS',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $values = array_values(array_filter($data['values'] ?? []));

                        if ($values === []) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($values): void {
                            foreach ($values as $value) {
                                $query->orWhereJsonContains('workshops', $value);
                            }
                        });
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('view_record')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->extraAttributes(['class' => 'hidden'])
                    ->modalHeading(fn (WorkshopRegistration $record): string => 'Workshop registration — ' . $record->full_name)
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make('Registrant details')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('Full name')
                                    ->weight('bold')
                                    ->columnSpanFull(),

                                TextEntry::make('psa_id')
                                    ->label('PSA ID')
                                    ->badge()
                                    ->placeholder('—')
                                    ->fontFamily('mono')
                                    ->color(fn ($state): string => str_starts_with((string) $state, 'NMW_')
                                        ? 'danger'
                                        : 'primary'),

                                TextEntry::make('mem_type')
                                    ->label('Member type')
                                    ->badge()
                                    ->color(fn ($state): string => match ((string) $state) {
                                        'Non-member' => 'danger',
                                        'Regular Member' => 'info',
                                        'Life Member' => 'success',
                                        'Trainee Member' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('prc_number')
                                    ->label('PRC number')
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('email')
                                    ->label('Email address')
                                    ->icon('heroicon-m-envelope'),

                                TextEntry::make('phone')
                                    ->label('Phone')
                                    ->icon('heroicon-m-phone'),

                                TextEntry::make('hospital')
                                    ->label('Hospital / institution')
                                    ->icon('heroicon-m-building-office-2')
                                    ->columnSpanFull(),

                                TextEntry::make('created_at')
                                    ->label('Registered at')
                                    ->dateTime('F d, Y g:i A')
                                    ->timezone('Asia/Manila')
                                    ->icon('heroicon-m-calendar')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Workshop selection')
                            ->schema([
                                TextEntry::make('workshops')
                                    ->label('Workshops')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => match ((string) $state) {
                                        'RA' => 'RA',
                                        'AIRWAY' => 'AIRWAY',
                                        'POCUS' => 'POCUS',
                                        default => (string) $state,
                                    })
                                    ->color(fn ($state): string => match ((string) $state) {
                                        'RA' => 'danger',
                                        'AIRWAY' => 'success',
                                        'POCUS' => 'info',
                                        default => 'gray',
                                    }),
                            ]),
                    ])
                    ->action(fn () => null),

                EditAction::make(),
            ])
            ->toolbarActions([
                Action::make('downloadExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn ($livewire) => static::download(
                        $livewire->getFilteredSortedTableQuery()->lazy(),
                    )),

                BulkActionGroup::make([
                    BulkAction::make('downloadSelected')
                        ->label('Download selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => static::download($records))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function download(iterable $records): StreamedResponse
    {
        $filename = 'workshop-registrations-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($records) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'PSA ID',
                'Full Name',
                'Member Type',
                'PRC Number',
                'Hospital',
                'Email',
                'Phone',
                'Workshop',
                'Registered At',
            ]));

            /** @var WorkshopRegistration $record */
            foreach ($records as $record) {
                $writer->addRow(Row::fromValues([
                    $record->psa_id,
                    $record->full_name,
                    $record->mem_type,
                    $record->prc_number,
                    $record->hospital,
                    $record->email,
                    $record->phone,
                    is_array($record->workshops)
                        ? implode(', ', $record->workshops)
                        : $record->workshops,
                    $record->created_at?->format('Y-m-d H:i:s'),
                ]));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
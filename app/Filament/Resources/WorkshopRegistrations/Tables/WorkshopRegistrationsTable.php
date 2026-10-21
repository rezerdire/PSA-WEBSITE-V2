<?php

namespace App\Filament\Resources\WorkshopRegistrations\Tables;

use App\Models\WorkshopRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
            ->columns([
                TextColumn::make('psa_id')
                    ->label('PSA ID')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Full name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('mem_first_name', 'like', "%{$search}%")
                                ->orWhere('mem_middle_name', 'like', "%{$search}%")
                                ->orWhere('mem_last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(mem_first_name, ' ', mem_last_name) like ?", ["%{$search}%"]);
                        });
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('mem_last_name', $direction)
                        ->orderBy('mem_first_name', $direction)),
                TextColumn::make('mem_type')
                    ->label('Member type')
                    ->searchable(),
                TextColumn::make('prc_number')
                    ->label('PRC number')
                    ->searchable(),
                TextColumn::make('hospital')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('workshops')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Registered at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('downloadExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($livewire) => static::download(
                        $livewire->getFilteredSortedTableQuery()->lazy(),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
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
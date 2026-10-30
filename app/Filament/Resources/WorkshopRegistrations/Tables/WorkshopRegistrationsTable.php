<?php

namespace App\Filament\Resources\WorkshopRegistrations\Tables;

use App\Filament\Resources\WorkshopRegistrations\Schemas\WorkshopRegistrationInfolist;
use App\Mail\WorkshopRegistrationConfirmation;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkshopRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        $workshopLabels = Workshop::optionMap();

        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction(ViewAction::class)
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

                TextColumn::make('email')
                    ->label('Email address')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('workshops')
                    ->label('Workshops')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $workshopLabels[(string) $state] ?? (string) $state)
                    ->color(fn ($state): string => match ((string) $state) {
                        'RA' => 'danger',
                        'AIRWAY' => 'success',
                        'POCUS' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('—'),

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
                    ->options($workshopLabels)
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
                ViewAction::make()
                    ->label('View registration')
                    ->slideOver()
                    ->modalHeading(fn (WorkshopRegistration $record): string => $record->full_name ?: 'Workshop registration')
                    ->modalWidth('2xl')
                    ->schema(WorkshopRegistrationInfolist::components()),

                EditAction::make(),

                Action::make('resend_confirmation')
                    ->label('Resend confirmation')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Resend confirmation email?')
                    ->modalDescription(fn (WorkshopRegistration $record): string => "Send the workshop confirmation again to {$record->email}?")
                    ->visible(fn (WorkshopRegistration $record): bool => filled($record->email))
                    ->action(function (WorkshopRegistration $record): void {
                        try {
                            Mail::to($record->email)->send(new WorkshopRegistrationConfirmation(
                                $record,
                                static::confirmationWorkshops($record),
                            ));

                            Notification::make()
                                ->title('Confirmation email sent')
                                ->body("The confirmation was sent to {$record->email}.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Workshop confirmation email resend failed.', [
                                'registration_id' => $record->getKey(),
                                'mailer' => (string) config('mail.default'),
                                'mail_host' => (string) config('mail.mailers.' . config('mail.default') . '.host', ''),
                                'mail_port' => config('mail.mailers.' . config('mail.default') . '.port'),
                                'exception' => $e,
                            ]);

                            Notification::make()
                                ->title('Confirmation email could not be sent')
                                ->body('Check the mail configuration and try again.')
                                ->danger()
                                ->send();
                        }
                    }),
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

    protected static function confirmationWorkshops(WorkshopRegistration $record): array
    {
        $codes = is_array($record->workshops)
            ? array_values(array_map('strval', $record->workshops))
            : [];
        $workshops = Workshop::query()
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code');

        return array_map(static function (string $code) use ($workshops): array {
            $workshop = $workshops->get($code);

            return [
                'code' => $code,
                'label' => $workshop?->name ?? $code,
                'session' => $workshop?->session,
                'venue' => $workshop?->venue,
            ];
        }, $codes);
    }

    protected static function download(iterable $records): StreamedResponse
    {
        $filename = 'workshop-registrations-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($records) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $workshopLabels = Workshop::optionMap();

            $writer->addRow(Row::fromValues([
                'PSA ID',
                'Full Name',
                'Member Type',
                'PRC Number',
                'Hospital',
                'Email',
                'Phone',
                'Workshops',
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
                        ? implode(', ', array_map(
                            fn (string $code): string => $workshopLabels[$code] ?? $code,
                            $record->workshops,
                        ))
                        : (string) $record->workshops,
                    $record->created_at?->format('Y-m-d H:i:s'),
                ]));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
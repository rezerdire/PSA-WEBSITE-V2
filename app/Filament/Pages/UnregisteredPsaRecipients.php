<?php

namespace App\Filament\Pages;

use App\Filament\Pages\UnregisteredPsaSendHistory;
use App\Models\UnregisteredPsaRecipient;
use App\Services\PsaIdCardLocator;
use App\Services\UnregisteredPsaRecipientService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnregisteredPsaRecipients extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Unregistered PSA IDs';

    protected static ?string $title = 'Unregistered PSA ID Email';

    protected string $view = 'filament.pages.unregistered-psa-recipients';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendHistory')
                ->label('Bulk send history')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(UnregisteredPsaSendHistory::getUrl()),
            Action::make('createRecipient')
                ->label('Add recipient')
                ->schema([
                    TextInput::make('psa_id')->label('PSA ID')->required()->maxLength(50)->unique('unregistered_psa_recipients', 'psa_id'),
                    TextInput::make('first_name')->label('First name')->required()->maxLength(255),
                    TextInput::make('middle_name')->label('Middle name')->maxLength(255),
                    TextInput::make('last_name')->label('Last name')->required()->maxLength(255),
                    TextInput::make('email')->email()->required()->maxLength(255),
                    TextInput::make('chapter')->label('Chapter')->maxLength(255),
                    FileUpload::make('id_card_path')->label('PSA ID image')->disk('psa_id_cards')->image()->acceptedFileTypes(['image/png', 'image/jpeg'])->maxSize(10240),
                ])
                ->action(function (array $data): void {
                    $data['full_name'] = implode(' ', array_filter([$data['first_name'], $data['middle_name'] ?? null, $data['last_name']]));
                    UnregisteredPsaRecipient::create($data);
                    PsaIdCardLocator::flush();
                }),
            Action::make('importCsv')
                ->label('Import / Update CSV')
                ->schema([
                    FileUpload::make('file')->label('CSV file')->disk('local')->directory('unregistered-psa-imports')->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])->required(),
                ])
                ->action(function (array $data, UnregisteredPsaRecipientService $service): void {
                    try {
                        $count = $service->import(Storage::disk('local')->path($data['file']));
                        Notification::make()->title("Imported or updated {$count} recipients.")->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('CSV import failed')->body($exception->getMessage())->danger()->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),
            Action::make('bulkSend')
                ->label('Bulk Send PSA IDs')
                ->color('primary')
                ->schema([
                    TextInput::make('limit')->label('Recipients')->numeric()->integer()->minValue(1)->maxValue(500)->default(50)->required(),
                ])
                ->action(function (array $data, UnregisteredPsaRecipientService $service): void {
                    try {
                        $batch = $service->queuePending((int) $data['limit']);
                        Notification::make()->title("Queued {$batch->total_count} recipients for delivery.")->body('Delivery continues in the background. View live progress in Bulk send history; the queue worker must be running.')->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('Bulk send failed')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('downloadCsv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => $this->downloadCsv()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table->query(UnregisteredPsaRecipient::query())
            ->columns([
                TextColumn::make('psa_id')->label('PSA ID')->searchable()->sortable(),
                TextColumn::make('first_name')->label('First name')->searchable()->placeholder('—'),
                TextColumn::make('middle_name')->label('Middle name')->searchable()->placeholder('—'),
                TextColumn::make('last_name')->label('Last name')->searchable()->placeholder('—'),
                ImageColumn::make('id_card_preview')
                    ->label('ID Preview')
                    ->imageHeight(64)
                    ->imageWidth(96)
                    ->state(fn (UnregisteredPsaRecipient $record): ?string => app(PsaIdCardLocator::class)->urlFor($record->psa_id))
                    ->url(fn (UnregisteredPsaRecipient $record): ?string => app(PsaIdCardLocator::class)->urlFor($record->psa_id)),
                IconColumn::make('id_card_available')
                    ->label('ID Image')
                    ->boolean()
                    ->state(fn (UnregisteredPsaRecipient $record): bool => app(PsaIdCardLocator::class)->pathFor($record->psa_id) !== null),
                TextColumn::make('email')->searchable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'sent' => 'success',
                    'failed' => 'danger',
                    'queued' => 'warning',
                    'processing' => 'info',
                    default => 'gray',
                }),
                TextColumn::make('sent_at')->dateTime()->sortable()->placeholder('—'),
                TextColumn::make('chapter')->label('Chapter')->searchable()->placeholder('—'),
                TextColumn::make('error')->limit(60)->placeholder('—'),
            ])
            ->recordActions([
                Action::make('sendSpecific')
                    ->label('Send PSA ID')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->modalDescription(fn (UnregisteredPsaRecipient $record): string => "Send the PSA ID to {$record->email}?")
                    ->action(function (UnregisteredPsaRecipient $record, UnregisteredPsaRecipientService $service): void {
                        if ($service->sendOne($record)) {
                            Notification::make()->title("PSA ID sent to {$record->email}.")->success()->send();
                        } else {
                            Notification::make()->title('PSA ID send failed')->body($record->fresh()->error)->danger()->send();
                        }
                    }),
                EditAction::make()
                    ->schema([
                        TextInput::make('psa_id')->label('PSA ID')->required()->maxLength(50)->unique('unregistered_psa_recipients', 'psa_id', ignoreRecord: true),
                        TextInput::make('first_name')->label('First name')->required()->maxLength(255),
                        TextInput::make('middle_name')->label('Middle name')->maxLength(255),
                        TextInput::make('last_name')->label('Last name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255),
                        TextInput::make('chapter')->label('Chapter')->maxLength(255),
                        FileUpload::make('id_card_path')->label('PSA ID image')->disk('psa_id_cards')->image()->acceptedFileTypes(['image/png', 'image/jpeg'])->maxSize(10240),
                    ])
                    ->action(function (UnregisteredPsaRecipient $record, array $data): void {
                        $oldPath = $record->id_card_path;
                        $data['full_name'] = implode(' ', array_filter([$data['first_name'], $data['middle_name'] ?? null, $data['last_name']]));
                        $record->update($data);
                        if ($oldPath && $oldPath !== ($data['id_card_path'] ?? null)) {
                            Storage::disk('psa_id_cards')->delete($oldPath);
                        }
                        PsaIdCardLocator::flush();
                    }),
            ])
            ->defaultSort('id', 'desc');
    }

    private function downloadCsv(): StreamedResponse
    {
        $filename = 'unregistered-psa-recipients-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['PSA ID', 'First Name', 'Middle Name', 'Last Name', 'Email', 'Chapter']);

            foreach (UnregisteredPsaRecipient::query()->orderBy('psa_id')->cursor() as $recipient) {
                fputcsv($handle, [
                    $recipient->psa_id,
                    $recipient->first_name,
                    $recipient->middle_name,
                    $recipient->last_name,
                    $recipient->email,
                    $recipient->chapter,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Resources\Registrations\Widgets\RegistrationStatsOverview;
use App\Services\BulkRegistrationQrService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected string|Width|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulkQrSending')
                ->label('Bulk Send QR')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->modalHeading('Bulk Send QR Codes')
                ->modalDescription('Approved member registrations without a previous QR send will be selected oldest first. Non-member registrations are excluded.')
                ->schema([
                    TextInput::make('limit')
                        ->label('Number of registrants')
                        ->helperText('Invalid or missing email addresses are included in the limit and recorded as failed.')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(500)
                        ->default(50)
                        ->required(),
                ])
                ->action(function (array $data, $livewire): void {
                    try {
                        $batch = app(BulkRegistrationQrService::class)->start(
                            (int) $data['limit'],
                            auth()->id(),
                        );
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Bulk QR send was not started')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $livewire->redirect(RegistrationResource::getUrl('bulk-qr', ['batch' => $batch->id]));
                }),
            Actions\Action::make('bulkQrHistory')
                ->label('QR Bulk History')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(RegistrationResource::getUrl('bulk-qr')),
            Actions\CreateAction::make(),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         RegistrationStatsOverview::class,
    //     ];
    // }
}
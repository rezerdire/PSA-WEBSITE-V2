<?php

namespace App\Filament\Resources\Booths\Pages;

use App\Filament\Resources\Booths\BoothResource;
use App\Filament\Resources\Booths\Tables\BoothsTable;
use App\Models\Booth;
use App\Support\BoothQr;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBooths extends ListRecords
{
    protected static string $resource = BoothResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadAllPdfs')
                ->label('Download All QR Codes')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $booths = Booth::query()->get()
                        ->filter(fn (Booth $booth) => filled(BoothQr::url($booth->booth_qr)));

                    if ($booths->isEmpty()) {
                        Notification::make()
                            ->title('No booth QR codes are available.')
                            ->warning()
                            ->send();

                        return null;
                    }

                    return BoothsTable::downloadPdf($booths, 'all-booth-qr-codes.pdf');
                }),
            CreateAction::make(),
        ];
    }
}

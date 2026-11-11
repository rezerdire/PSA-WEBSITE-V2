<?php

namespace App\Filament\Resources\Registrations\Widgets;

use App\Models\RegistrationQrSend;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QrDeliveryStatsOverview extends BaseWidget
{
    protected ?string $heading = 'QR Delivery Overview';

    protected ?string $description = 'Users with a successfully recorded QR email';

    protected function getStats(): array
    {
        $usersWithQr = RegistrationQrSend::query()
            ->distinct()
            ->count('registration_id');

        return [
            Stat::make('Users with QR sent', $usersWithQr)
                ->description('Unique registrations with a successful QR send record')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('success'),
        ];
    }
}

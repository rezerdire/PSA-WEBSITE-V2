<?php

namespace App\Filament\Resources\Registrations\Widgets;

use App\Models\UnregisteredPsaRecipient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnregisteredPsaEmailStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Unregistered PSA Email Delivery';

    protected ?string $description = 'Successfully emailed unregistered recipients';

    protected function getStats(): array
    {
        $sent = UnregisteredPsaRecipient::query()->where('status', 'sent')->count();

        return [
            Stat::make('PSA IDs successfully emailed', $sent)
                ->description('Unregistered recipients marked sent')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success'),
        ];
    }
}

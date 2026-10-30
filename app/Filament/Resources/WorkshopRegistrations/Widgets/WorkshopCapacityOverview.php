<?php

namespace App\Filament\Resources\WorkshopRegistrations\Widgets;

use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkshopCapacityOverview extends BaseWidget
{
    protected ?string $heading = 'Workshop Capacity';

    protected ?string $description = 'Registered seats and remaining capacity by workshop';

    protected function getStats(): array
    {
        return Workshop::query()
            ->ordered()
            ->get()
            ->map(function (Workshop $workshop): Stat {
                $registered = WorkshopRegistration::query()
                    ->whereJsonContains('workshops', $workshop->code)
                    ->count();
                $capacity = (int) $workshop->capacity;
                $remaining = max($capacity - $registered, 0);
                $isFull = $remaining === 0;
                $isNearlyFull = $capacity > 0 && $remaining <= max(1, (int) ceil($capacity * 0.2));

                return Stat::make($workshop->name, $isFull ? 'Full' : "{$remaining} slots left")
                    ->description("{$registered} of {$capacity} slots registered")
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color($isFull ? 'danger' : ($isNearlyFull ? 'warning' : 'success'));
            })
            ->all();
    }
}

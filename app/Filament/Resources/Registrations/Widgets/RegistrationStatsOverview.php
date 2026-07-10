<?php

namespace App\Filament\Resources\Registrations\Widgets;

use App\Models\Member;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRegistered  = Registration::whereIn('status',[
            Registration::STATUS_PENDING,
            Registration::STATUS_APPROVED
        ])->count();
        $notYetRegistered = Member::whereIn('psa_mem_type', ['RM', 'LM', 'TM'])
            ->whereDoesntHave('registration')
            ->count();

        $pending  = Registration::where('status', Registration::STATUS_PENDING)->count();
        $approved = Registration::where('status', Registration::STATUS_APPROVED)->count();

        return [
            Stat::make('Total Registered', $totalRegistered)
                ->description('Members who submitted a registration')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Not Yet Registered', $notYetRegistered)
                ->description('Eligible members who haven\'t registered')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('gray'),

            Stat::make('Pending', $pending)
                ->description('Awaiting secretariat review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approved', $approved)
                ->description('Confirmed for the convention')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
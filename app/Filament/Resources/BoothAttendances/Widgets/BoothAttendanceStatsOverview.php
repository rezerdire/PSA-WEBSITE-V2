<?php

namespace App\Filament\Resources\BoothAttendances\Widgets;

use App\Models\Booth;
use App\Models\BoothAttendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BoothAttendanceStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Booth Attendance Overview';

    protected function getStats(): array
    {
        $totalBooths = Booth::count();

        $completedAttendees = $totalBooths > 0
            ? DB::query()
                ->fromSub(
                    BoothAttendance::query()
                        ->select('member_id_no')
                        ->groupBy('member_id_no')
                        ->havingRaw('COUNT(DISTINCT booth_id) >= ?', [$totalBooths]),
                    'completed_attendees'
                )
                ->count()
            : 0;

        return [
            Stat::make('Completed Attendance', $completedAttendees)
                ->description("Attendees who visited all {$totalBooths} booths")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}

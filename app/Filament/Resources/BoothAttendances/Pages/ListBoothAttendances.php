<?php

namespace App\Filament\Resources\BoothAttendances\Pages;

use App\Filament\Resources\BoothAttendances\BoothAttendanceResource;
use App\Filament\Resources\BoothAttendances\Widgets\BoothAttendanceStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoothAttendances extends ListRecords
{
    protected static string $resource = BoothAttendanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BoothAttendanceStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

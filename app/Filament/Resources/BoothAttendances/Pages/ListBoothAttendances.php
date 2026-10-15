<?php

namespace App\Filament\Resources\BoothAttendances\Pages;

use App\Filament\Resources\BoothAttendances\BoothAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoothAttendances extends ListRecords
{
    protected static string $resource = BoothAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

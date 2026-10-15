<?php

namespace App\Filament\Resources\BoothAttendances\Pages;

use App\Filament\Resources\BoothAttendances\BoothAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBoothAttendance extends EditRecord
{
    protected static string $resource = BoothAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

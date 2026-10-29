<?php

namespace App\Filament\Resources\WorkshopRegistrations\Pages;

use App\Filament\Resources\WorkshopRegistrations\WorkshopRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkshopRegistration extends EditRecord
{
    protected static string $resource = WorkshopRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\WorkshopRegistrations\Pages;

use App\Filament\Resources\WorkshopRegistrations\WorkshopRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkshopRegistrations extends ListRecords
{
    protected static string $resource = WorkshopRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

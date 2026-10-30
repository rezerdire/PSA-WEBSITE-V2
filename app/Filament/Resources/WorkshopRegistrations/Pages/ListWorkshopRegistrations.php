<?php

namespace App\Filament\Resources\WorkshopRegistrations\Pages;

use App\Filament\Resources\WorkshopRegistrations\WorkshopRegistrationResource;
use App\Filament\Resources\WorkshopRegistrations\Widgets\WorkshopCapacityOverview;
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

    protected function getHeaderWidgets(): array
    {
        return [
            WorkshopCapacityOverview::class,
        ];
    }
}

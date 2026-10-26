<?php

namespace App\Filament\Resources\WorkshopRegisrations\Pages;

use App\Filament\Resources\WorkshopRegisrations\WorkshopRegisrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkshopRegisrations extends ListRecords
{
    protected static string $resource = WorkshopRegisrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

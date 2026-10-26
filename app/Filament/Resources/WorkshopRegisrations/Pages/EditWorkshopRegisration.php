<?php

namespace App\Filament\Resources\WorkshopRegisrations\Pages;

use App\Filament\Resources\WorkshopRegisrations\WorkshopRegisrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkshopRegisration extends EditRecord
{
    protected static string $resource = WorkshopRegisrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

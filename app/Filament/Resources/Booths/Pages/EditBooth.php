<?php

namespace App\Filament\Resources\Booths\Pages;

use App\Filament\Resources\Booths\BoothResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBooth extends EditRecord
{
    protected static string $resource = BoothResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\MasterLists\Pages;

use App\Filament\Resources\MasterLists\MasterListResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterList extends EditRecord
{
    protected static string $resource = MasterListResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

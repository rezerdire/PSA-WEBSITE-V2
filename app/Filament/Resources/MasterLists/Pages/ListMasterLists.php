<?php

namespace App\Filament\Resources\MasterLists\Pages;

use App\Filament\Resources\MasterLists\MasterListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterLists extends ListRecords
{
    protected static string $resource = MasterListResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getSubheading(): ?string
    {
        return 'CSV imports replace non-VIP entries and update matching VIP records; VIP details are retained.';
    }
}

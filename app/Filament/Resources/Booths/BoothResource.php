<?php

namespace App\Filament\Resources\Booths;

use App\Filament\Resources\Booths\Pages\CreateBooth;
use App\Filament\Resources\Booths\Pages\EditBooth;
use App\Filament\Resources\Booths\Pages\ListBooths;
use App\Filament\Resources\Booths\Schemas\BoothForm;
use App\Filament\Resources\Booths\Tables\BoothsTable;
use App\Models\Booth;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoothResource extends Resource
{
    protected static ?string $model = Booth::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Booths';

    public static function form(Schema $schema): Schema
    {
        return BoothForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoothsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBooths::route('/'),
            'create' => CreateBooth::route('/create'),
            'edit' => EditBooth::route('/{record}/edit'),
        ];
    }
}

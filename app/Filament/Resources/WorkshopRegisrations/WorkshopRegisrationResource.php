<?php

namespace App\Filament\Resources\WorkshopRegisrations;

use App\Filament\Resources\WorkshopRegisrations\Pages\CreateWorkshopRegisration;
use App\Filament\Resources\WorkshopRegisrations\Pages\EditWorkshopRegisration;
use App\Filament\Resources\WorkshopRegisrations\Pages\ListWorkshopRegisrations;
use App\Filament\Resources\WorkshopRegisrations\Schemas\WorkshopRegisrationForm;
use App\Filament\Resources\WorkshopRegisrations\Tables\WorkshopRegisrationsTable;
use App\Models\WorkshopRegisration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkshopRegisrationResource extends Resource
{
    protected static ?string $model = WorkshopRegisration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'WorkshopRegistration';

    public static function form(Schema $schema): Schema
    {
        return WorkshopRegisrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkshopRegisrationsTable::configure($table);
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
            'index' => ListWorkshopRegisrations::route('/'),
            'create' => CreateWorkshopRegisration::route('/create'),
            'edit' => EditWorkshopRegisration::route('/{record}/edit'),
        ];
    }
}

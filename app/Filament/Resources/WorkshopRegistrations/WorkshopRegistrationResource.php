<?php

namespace App\Filament\Resources\WorkshopRegistrations;

use App\Filament\Resources\WorkshopRegistrations\Pages\CreateWorkshopRegistration;
use App\Filament\Resources\WorkshopRegistrations\Pages\EditWorkshopRegistration;
use App\Filament\Resources\WorkshopRegistrations\Pages\ListWorkshopRegistrations;
use App\Filament\Resources\WorkshopRegistrations\Schemas\WorkshopRegistrationForm;
use App\Filament\Resources\WorkshopRegistrations\Tables\WorkshopRegistrationsTable;
use App\Models\WorkshopRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkshopRegistrationResource extends Resource
{
    protected static ?string $model = WorkshopRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'WorkshopRegistration';

    public static function form(Schema $schema): Schema
    {
        return WorkshopRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkshopRegistrationsTable::configure($table);
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
            'index' => ListWorkshopRegistrations::route('/'),
            'create' => CreateWorkshopRegistration::route('/create'),
            'edit' => EditWorkshopRegistration::route('/{record}/edit'),
        ];
    }
}

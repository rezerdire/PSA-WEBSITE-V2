<?php

namespace App\Filament\Resources\BoothAttendances;

use App\Filament\Resources\BoothAttendances\Pages\CreateBoothAttendance;
use App\Filament\Resources\BoothAttendances\Pages\EditBoothAttendance;
use App\Filament\Resources\BoothAttendances\Pages\ListBoothAttendances;
use App\Filament\Resources\BoothAttendances\Schemas\BoothAttendanceForm;
use App\Filament\Resources\BoothAttendances\Tables\BoothAttendancesTable;
use App\Models\BoothAttendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoothAttendanceResource extends Resource
{
    protected static ?string $model = BoothAttendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'BoothAttendance';

    public static function form(Schema $schema): Schema
    {
        return BoothAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoothAttendancesTable::configure($table);
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
            'index' => ListBoothAttendances::route('/'),
            'create' => CreateBoothAttendance::route('/create'),
            'edit' => EditBoothAttendance::route('/{record}/edit'),
        ];
    }
}

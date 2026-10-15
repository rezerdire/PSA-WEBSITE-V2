<?php

namespace App\Filament\Resources\BoothAttendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BoothAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('member_id_no')
                    ->required(),
                TextInput::make('booth_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('visited_at')
                    ->required(),
            ]);
    }
}

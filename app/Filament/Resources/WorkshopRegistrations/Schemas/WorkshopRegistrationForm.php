<?php

namespace App\Filament\Resources\WorkshopRegistrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkshopRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('psa_id'),
                TextInput::make('mem_last_name')
                    ->required(),
                TextInput::make('mem_first_name')
                    ->required(),
                TextInput::make('mem_middle_name')
                    ->required(),
                TextInput::make('mem_type'),
                TextInput::make('prc_number')
                    ->required(),
                TextInput::make('hospital')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('workshops')
                    ->required(),
            ]);
    }
}

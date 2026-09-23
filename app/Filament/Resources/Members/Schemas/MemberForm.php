<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('psa_chapter_code')
                    ->required(),
                TextInput::make('psa_mem_type')
                    ->required(),
                TextInput::make('mem_stat')
                    ->default(null),
                TextInput::make('mem_last_name')
                    ->required(),
                TextInput::make('mem_first_name')
                    ->required(),
                TextInput::make('mem_middle_name')
                    ->default(null),
                Textarea::make('mem_home_address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('mem_mobile_no1')
                    ->default(null),
                TextInput::make('mem_email_address')
                    ->email()
                    ->default(null),
                TextInput::make('mem_gender')
                    ->default(null),
                TextInput::make('mem_prc_no')
                    ->default(null),
            ]);
    }
}

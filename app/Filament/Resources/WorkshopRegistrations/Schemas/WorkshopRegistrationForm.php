<?php

namespace App\Filament\Resources\WorkshopRegistrations\Schemas;

use App\Models\Workshop;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkshopRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registrant identity')
                    ->description('Member details and PSA classification for this workshop registration.')
                    ->icon('heroicon-o-identification')
                    ->compact()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('psa_id')
                                ->label('PSA ID')
                                ->maxLength(20)
                                ->disabledOn('edit')
                                ->dehydrated()
                                ->helperText('Assigned member or non-member workshop ID.'),

                            Select::make('mem_type')
                                ->label('Member type')
                                ->options([
                                    'Regular Member' => 'Regular Member',
                                    'Life Member' => 'Life Member',
                                    'Trainee Member' => 'Trainee Member',
                                    'Non-member' => 'Non-member',
                                ])
                                ->native(false)
                                ->required(),

                            TextInput::make('prc_number')
                                ->label('PRC number')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('mem_first_name')
                                ->label('First name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('mem_middle_name')
                                ->label('Middle name')
                                ->maxLength(255),

                            TextInput::make('mem_last_name')
                                ->label('Last name')
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Contact details')
                    ->description('Keep the registrant contact information complete and up to date.')
                    ->icon('heroicon-o-envelope')
                    ->compact()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Email address')
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Phone number')
                                ->tel()
                                ->required()
                                ->maxLength(20),

                            TextInput::make('hospital')
                                ->label('Hospital / institution')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Workshop selection')
                    ->description('Choose one or more workshops for this registrant. Public registration and admin edits use the same workshop catalog.')
                    ->icon('heroicon-o-academic-cap')
                    ->compact()
                    ->schema([
                        Select::make('workshops')
                            ->label('Workshops')
                            ->options(fn (): array => Workshop::optionMap())
                            ->searchable()
                            ->native(false)
                            ->multiple()
                            ->minItems(1)
                            ->required()
                            ->rules(['array', 'min:1'])
                            ->helperText('Select every workshop this registrant will attend. Workshops are managed from the Workshops menu.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

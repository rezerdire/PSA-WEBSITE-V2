<?php

namespace App\Filament\Resources\Workshops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkshopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Workshop details')
                    ->description('These values control the workshop choices shown on the public registration form.')
                    ->icon('heroicon-o-academic-cap')
                    ->compact()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->disabledOn('edit')
                                ->dehydrateStateUsing(fn ($state): string => strtoupper(trim((string) $state)))
                                ->helperText('Short unique value stored with each registration. It cannot be changed after creation; for example RA or POCUS.'),

                            TextInput::make('name')
                                ->label('Display name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('session')
                                ->label('Schedule')
                                ->maxLength(255)
                                ->placeholder('e.g. 9:00 AM – 12:00 Noon'),

                            TextInput::make('venue')
                                ->label('Venue')
                                ->maxLength(255)
                                ->placeholder('e.g. Executive 5'),

                            TextInput::make('capacity')
                                ->label('Capacity')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->required()
                                ->default(60),

                            TextInput::make('sort_order')
                                ->label('Display order')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->required()
                                ->default(0)
                                ->helperText('Lower numbers appear first.'),
                        ]),

                        Toggle::make('is_active')
                            ->label('Available for public registration')
                            ->default(true)
                            ->helperText('Turn this off to hide the workshop without deleting its existing registration history.'),

                    ]),
            ]);
    }
}

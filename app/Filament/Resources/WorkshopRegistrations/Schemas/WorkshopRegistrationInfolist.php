<?php

namespace App\Filament\Resources\WorkshopRegistrations\Schemas;

use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class WorkshopRegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::components());
    }

    public static function components(): array
    {
        $workshops = Workshop::query()->ordered()->get();
        $catalogCodes = $workshops->pluck('code')->all();

        $workshopCards = $workshops->map(function (Workshop $workshop): Section {
            $key = str($workshop->code)->slug();

            return Section::make($workshop->name)
                ->description("Workshop code: {$workshop->code}")
                ->icon('heroicon-o-academic-cap')
                ->compact()
                ->visible(fn (WorkshopRegistration $record): bool => in_array(
                    $workshop->code,
                    $record->workshops ?? [],
                    true,
                ))
                ->schema([
                    TextEntry::make("workshop_{$key}_schedule")
                        ->label('Schedule')
                        ->state($workshop->session ?: 'To be announced'),

                    TextEntry::make("workshop_{$key}_venue")
                        ->label('Venue')
                        ->state($workshop->venue ?: 'To be announced'),

                    TextEntry::make("workshop_{$key}_capacity")
                        ->label('Capacity')
                        ->state($workshop->capacity ? "{$workshop->capacity} slots" : 'Not set'),
                ]);
        })->all();

        return [
            Tabs::make('Registration details')
                ->tabs([
                    Tab::make('Registrant')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Registrant details')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('full_name')
                                        ->label('Full name')
                                        ->weight('bold')
                                        ->columnSpanFull(),

                                    TextEntry::make('psa_id')
                                        ->label('PSA ID')
                                        ->badge()
                                        ->placeholder('—')
                                        ->fontFamily('mono')
                                        ->color(fn ($state): string => str_starts_with((string) $state, 'NMW_')
                                            ? 'danger'
                                            : 'primary'),

                                    TextEntry::make('mem_type')
                                        ->label('Member type')
                                        ->badge()
                                        ->color(fn ($state): string => match ((string) $state) {
                                            'Non-member' => 'danger',
                                            'Regular Member' => 'info',
                                            'Life Member' => 'success',
                                            'Trainee Member' => 'warning',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('prc_number')
                                        ->label('PRC number')
                                        ->badge()
                                        ->color('warning'),

                                    TextEntry::make('email')
                                        ->label('Email address')
                                        ->icon('heroicon-m-envelope'),

                                    TextEntry::make('phone')
                                        ->label('Phone')
                                        ->icon('heroicon-m-phone'),

                                    TextEntry::make('hospital')
                                        ->label('Hospital / institution')
                                        ->icon('heroicon-m-building-office-2')
                                        ->columnSpanFull(),

                                    TextEntry::make('created_at')
                                        ->label('Registered at')
                                        ->dateTime('F d, Y g:i A')
                                        ->timezone('Asia/Manila')
                                        ->icon('heroicon-m-calendar')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Workshops')
                        ->icon('heroicon-o-academic-cap')
                        ->badge(fn (WorkshopRegistration $record): string => (string) count($record->workshops ?? []))
                        ->schema([
                            Grid::make(2)
                                ->schema($workshopCards),

                            Section::make('Archived workshop selections')
                                ->icon('heroicon-o-archive-box')
                                ->compact()
                                ->visible(fn (WorkshopRegistration $record): bool => collect($record->workshops ?? [])
                                    ->contains(fn (string $code): bool => ! in_array($code, $catalogCodes, true)))
                                ->schema([
                                    TextEntry::make('uncatalogued_workshops')
                                        ->label('Workshop codes')
                                        ->state(fn (WorkshopRegistration $record): array => collect($record->workshops ?? [])
                                            ->reject(fn (string $code): bool => in_array($code, $catalogCodes, true))
                                            ->values()
                                            ->all())
                                        ->listWithLineBreaks()
                                        ->badge(),
                                ]),
                        ]),
                ]),
        ];
    }
}

<?php

namespace App\Filament\Resources\Booths\Schemas;

use App\Models\Booth;
use App\Support\BoothQr;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BoothForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booth_type')
                    ->label('Category')
                    ->placeholder('e.g. Regular Booths')
                    ->datalist([
                        'Hospital Suites',
                        'Prime Foyer',
                        'Prime Booths',
                        'Regular Booths',
                        'Regular Tables',
                    ])
                    ->required(),

                TextInput::make('booth_code')
                    ->label('Booth code')
                    ->placeholder('e.g. Suite 1, T1, RB 24, RB 25')
                    ->required()
                    ->helperText('For one booth covering several spots, separate them with commas (e.g. RB 24, RB 25).')
                    ->live(onBlur: true)
                    ->dehydrateStateUsing(fn ($state) => trim(preg_replace('/\s+/', ' ', (string) $state)))
                    ->afterStateUpdated(fn (Get $get, Set $set, string $operation) => self::syncGeneratedCode($get, $set, $operation)),

                TextInput::make('location')
                    ->placeholder('e.g. Ballroom C'),

                TextInput::make('booth_name')
                    ->label('Booth name')
                    ->placeholder('e.g. BD Philippines')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, string $operation) => self::syncGeneratedCode($get, $set, $operation)),

                TextInput::make('booth_generated_code')
                    ->label('Generated code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
                    ->helperText('Auto-generated from booth code + initials of the booth name (T1 + ALAGAO SCRUBSUIT = T1AS). This is what the QR contains. You can edit it if there is a conflict.'),

                TextInput::make('booth_qr')
                    ->label('Booth QR (file path)')
                    ->unique(ignoreRecord: true)
                    ->readOnly()
                    ->live()
                    ->suffixAction(
                        Action::make('generateQr')
                            ->label('Generate QR')
                            ->icon('heroicon-m-qr-code')
                            ->action(function (Get $get, Set $set) {
                                $code = strtoupper(trim((string) $get('booth_generated_code')));

                                if ($code === '') {
                                    Notification::make()
                                        ->title('Enter the booth code and name first.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $set('booth_qr', BoothQr::generate($code));
                            })
                    ),

                ImageEntry::make('qr_preview')
                    ->label('QR Preview')
                    ->state(fn (Get $get) => BoothQr::url($get('booth_qr')))
                    ->height(220)
                    ->visible(fn (Get $get) => filled(BoothQr::url($get('booth_qr')))),
            ]);
    }

    /**
     * Fill the generated code once the booth code and name are both entered.
     * Only on create, so editing a booth never silently changes a printed QR.
     */
    protected static function syncGeneratedCode(Get $get, Set $set, string $operation): void
    {
        if ($operation !== 'create') {
            return;
        }

        $code = trim((string) $get('booth_code'));
        $name = trim((string) $get('booth_name'));

        if ($code !== '' && $name !== '') {
            $set('booth_generated_code', Booth::makeGeneratedCode($code, $name));
        }
    }
}
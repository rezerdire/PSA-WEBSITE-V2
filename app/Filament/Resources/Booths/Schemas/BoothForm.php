<?php

namespace App\Filament\Resources\Booths\Schemas;

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
                    ->placeholder('e.g. Suite 1')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::syncCode($get, $set)),

                TextInput::make('booth_name')
                    ->placeholder('e.g. BD Philippines')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::syncCode($get, $set)),

                TextInput::make('location'),

                TextInput::make('booth_code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state)))
                    ->helperText('Auto-generated from booth type + booth name. You can edit it if there is a conflict.'),

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
                                $code = strtoupper(trim((string) $get('booth_code')));

                                if ($code === '') {
                                    Notification::make()
                                        ->title('Enter the booth type and name first.')
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

    protected static function syncCode(Get $get, Set $set): void
    {
        $type = $get('booth_type');
        $name = $get('booth_name');

        if (filled($type) && filled($name)) {
            $set('booth_code', self::generateCode($type, $name));
        }
    }

    /** "Suite 1" + "BD Philippines" => S1BDP */
    public static function generateCode(string $type, string $name): string
    {
        $words = fn (string $v) => preg_split('/\s+/', trim(preg_replace('/[^A-Za-z0-9\s]/', '', $v)), -1, PREG_SPLIT_NO_EMPTY);

        $typePart = collect($words($type))
            ->map(fn ($w) => ctype_digit($w) ? $w : mb_substr($w, 0, 1))
            ->implode('');

        $namePart = collect($words($name))
            ->map(fn ($w) => mb_strlen($w) <= 3 ? $w : mb_substr($w, 0, 1))
            ->implode('');

        return strtoupper($typePart . $namePart);
    }
}
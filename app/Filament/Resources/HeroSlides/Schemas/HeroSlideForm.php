<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero content')
                    ->description('Write the text shown on the public homepage hero slide.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('eyebrow')
                                ->label('Eyebrow')
                                ->maxLength(255)
                                ->helperText('Short label above the main heading.'),

                            TextInput::make('sort_order')
                                ->label('Display order')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->required()
                                ->default(0)
                                ->helperText('Lower numbers appear first.'),
                        ]),

                        TextInput::make('title')
                            ->label('Main title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('highlight')
                            ->label('Highlighted title text')
                            ->maxLength(255)
                            ->helperText('Optional second line displayed in the PSA blue accent color.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Hero images')
                    ->description('Optional. Choose whether this slide should display uploaded images.')
                    ->schema([
                        Toggle::make('show_image')
                            ->label('Show image on this slide')
                            ->default(true)
                            ->live()
                            ->helperText('Turn this off to use a text-only hero slide. Your uploaded images will be kept for later use.')
                            ->columnSpanFull(),

                        FileUpload::make('image_paths')
                            ->label('Images')
                            ->disk('uploads')
                            ->directory('hero-slides')
                            ->multiple()
                            ->maxFiles(6)
                            ->reorderable()
                            ->panelLayout('grid')
                            ->image()
                            ->imageEditor()
                            ->maxSize(10240)
                            ->visible(fn (Get $get): bool => (bool) $get('show_image'))
                            ->dehydratedWhenHidden()
                            ->helperText('Upload up to 6 images.'),

                        TextInput::make('image_alt')
                            ->label('Image alternative text')
                            ->visible(fn (Get $get): bool => (bool) $get('show_image'))
                            ->dehydratedWhenHidden()
                            ->required(fn (Get $get): bool => (bool) $get('show_image') && filled($get('image_paths')))
                            ->maxLength(255)
                            ->helperText('Required only when an image is displayed.'),
                    ]),

                Section::make('Supporting details')
                    ->description('Add compact information cards such as dates, venues, deadlines, or statistics.')
                    ->schema([
                        Repeater::make('details')
                            ->label('Details')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('label')
                                        ->label('Label')
                                        ->required()
                                        ->maxLength(80),

                                    TextInput::make('value')
                                        ->label('Value')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            ])
                            ->default([])
                            ->maxItems(4)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Buttons')
                    ->description('Both buttons are optional. For an internal link, enter a named Laravel route such as home or Event-Registration. URLs, paths, mail links, and anchors are also supported.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('primary_label')
                                ->label('Primary button label')
                                ->maxLength(80),

                            TextInput::make('primary_url')
                                ->label('Primary button link')
                                ->maxLength(2048)
                                ->helperText('Example route name: home. The route name is resolved automatically.'),

                            TextInput::make('secondary_label')
                                ->label('Secondary button label')
                                ->maxLength(80),

                            TextInput::make('secondary_url')
                                ->label('Secondary button link')
                                ->maxLength(2048)
                                ->helperText('Example: Event-Registration, /about, https://example.com, or #section.'),
                        ]),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Published on the homepage')
                            ->default(true)
                            ->helperText('Turn this off to keep the slide in the CMS without showing it publicly.'),
                    ]),
            ]);
    }
}

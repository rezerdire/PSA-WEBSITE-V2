<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Models\HeroSlide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Preview')
                    ->state(function (HeroSlide $record): ?string {
                        $sources = $record->imageSources();

                        if ($sources === []) {
                            return null;
                        }

                        $url = $record->image_url;

                        return filter_var($url, FILTER_VALIDATE_URL)
                            ? $url
                            : url(ltrim($url, '/'));
                    })
                    ->height(64)
                    ->width(110)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('title')
                    ->label('Title')
                    ->description(fn (HeroSlide $record): ?string => $record->highlight)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('eyebrow')
                    ->label('Eyebrow')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Published' : 'Hidden')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Published',
                        0 => 'Hidden',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

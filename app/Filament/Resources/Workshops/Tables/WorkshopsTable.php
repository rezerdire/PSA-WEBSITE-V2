<?php

namespace App\Filament\Resources\Workshops\Tables;

use App\Models\Workshop;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class WorkshopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Workshop')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('session')
                    ->label('Schedule')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('venue')
                    ->label('Venue')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Public'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

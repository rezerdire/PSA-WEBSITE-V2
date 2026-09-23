<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_id_no')
                    ->searchable(),
                TextColumn::make('psa_chapter_code')
                    ->searchable(),
                TextColumn::make('psa_mem_type')
                    ->searchable(),
                TextColumn::make('mem_stat')
                    ->searchable(),
                TextColumn::make('mem_last_name')
                    ->searchable(),
                TextColumn::make('mem_first_name')
                    ->searchable(),
                TextColumn::make('mem_middle_name')
                    ->searchable(),
                TextColumn::make('mem_mobile_no1')
                    ->searchable(),
                TextColumn::make('mem_email_address')
                    ->searchable(),
                TextColumn::make('mem_gender')
                    ->searchable(),
                TextColumn::make('mem_prc_no')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

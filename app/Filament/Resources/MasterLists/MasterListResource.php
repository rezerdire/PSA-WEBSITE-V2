<?php

namespace App\Filament\Resources\MasterLists;

use App\Filament\Resources\MasterLists\Pages\CreateMasterList;
use App\Filament\Resources\MasterLists\Pages\EditMasterList;
use App\Filament\Resources\MasterLists\Pages\ListMasterLists;
use App\Models\MasterList;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterListResource extends Resource
{
    protected static ?string $model = MasterList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Evaluation Master List';

    protected static ?string $modelLabel = 'master list entry';

    protected static ?string $pluralModelLabel = 'Evaluation Master List';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Master list entry')
                ->description('CSV imports replace non-VIP entries. VIP details are retained.')
                ->schema([
                    TextInput::make('psa_id')->label('PSA ID')
                        ->required(fn (Get $get): bool => $get('record_type') !== 'vip')
                        ->helperText('VIPs without a PSA ID cannot access the evaluation form until an ID is added.')
                        ->maxLength(100),
                    Select::make('record_type')
                        ->label('Type')
                        ->options(['member' => 'Member', 'nonmember' => 'Nonmember', 'vip' => 'VIP'])
                        ->live()
                        ->required()
                        ->native(false),
                    TextInput::make('first_name')->required()->maxLength(255),
                    TextInput::make('middle_name')->maxLength(255),
                    TextInput::make('last_name')->required()->maxLength(255),
                    TextInput::make('payment_ref_no')->label('Payment reference number')->maxLength(255),
                    Textarea::make('vip_role')->label('VIP role')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->columns([
                TextColumn::make('psa_id')->label('PSA ID')->searchable()->sortable()->placeholder('No PSA ID'),
                TextColumn::make('first_name')->searchable()->sortable(),
                TextColumn::make('middle_name')->searchable()->placeholder('—'),
                TextColumn::make('last_name')->searchable()->sortable(),
                TextColumn::make('record_type')->label('Type')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'member' => 'Member',
                        'nonmember' => 'Nonmember',
                        'vip' => 'VIP',
                        default => $state,
                    }),
                TextColumn::make('payment_ref_no')->label('Payment reference')->searchable()->placeholder('—'),
                TextColumn::make('vip_role')->label('VIP role')->searchable()->wrap()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('record_type')->label('Type')
                    ->options(['member' => 'Member', 'nonmember' => 'Nonmember', 'vip' => 'VIP']),
                Filter::make('vip')->label('All VIPs')
                    ->query(fn (Builder $query): Builder => $query->where(fn (Builder $q): Builder => $q->whereNotNull('vip_name')->orWhere('record_type', 'vip'))),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterLists::route('/'),
            'create' => CreateMasterList::route('/create'),
            'edit' => EditMasterList::route('/{record}/edit'),
        ];
    }
}

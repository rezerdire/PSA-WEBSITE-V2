<?php

namespace App\Filament\Resources\Members;

use App\Services\MemberIdCardService;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\Members\Pages;
use App\Models\Member;
use App\Services\MemberQrService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $slug = 'members';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('member_id_no')
                ->required()
                ->disabledOn('edit')
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('psa_chapter_code')->required(),
            Forms\Components\TextInput::make('psa_mem_type')->required(),
            Forms\Components\TextInput::make('mem_stat'),

            Forms\Components\TextInput::make('mem_last_name')->required(),
            Forms\Components\TextInput::make('mem_first_name')->required(),
            Forms\Components\TextInput::make('mem_middle_name'),

            Forms\Components\Textarea::make('mem_home_address')->columnSpanFull(),
            Forms\Components\TextInput::make('mem_mobile_no1')->tel(),
            Forms\Components\TextInput::make('mem_email_address')->email(),
            Forms\Components\TextInput::make('mem_gender'),
            Forms\Components\TextInput::make('mem_prc_no'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('full_name')->label('Name')->searchable(['mem_first_name', 'mem_last_name']),
                Tables\Columns\TextColumn::make('mem_email_address'),

            ])
            ->filters([
                //
            ])
          ->recordActions([


    Actions\Action::make('generateQr')
        ->label('Generate QR')
        ->icon('heroicon-o-qr-code')
        ->color('primary')
        ->requiresConfirmation()
        ->action(fn (Member $record) => app(MemberQrService::class)->generate($record))
        ->successNotificationTitle('QR generated'),

   Actions\Action::make('previewQr')
    ->label('View ID Card')
    ->icon('heroicon-o-eye')
    ->visible(fn (Member $record) => $record->qr !== null)
    ->modalHeading(fn (Member $record) => "ID Card — {$record->full_name}")
    ->modalContent(fn (Member $record) => view('filament.members.qr-modal', [
        'url' => app(MemberQrService::class)->url($record->qr),
        'member' => $record,
    ]))
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Close'),

    
    Actions\Action::make('downloadIdCard')
        ->label('Download ID Card')
        ->icon('heroicon-o-identification')
        ->visible(fn (Member $record) => $record->qr !== null)
        ->action(fn (Member $record) => response()->download(
            Storage::disk('members_qr')->path($record->qr->qr_path)
        )),
])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),

                    Actions\BulkAction::make('generateQrBulk')
                        ->label('Generate QR for selected')
                        ->icon('heroicon-o-qr-code')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $service = app(MemberQrService::class);
                            foreach ($records as $member) {
                                $service->generate($member);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}'),
        ];
    }
}
<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Member;
use App\Models\Registration;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction('view_record')
            ->recordUrl(null)
            ->columns([

                TextColumn::make('id')
                    ->label('Ref #')
                    ->width('90px')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('psa_id')
                    ->label('PSA ID')
                    ->width('110px')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->grow()
                    ->searchable(['first_name', 'last_name', 'middle_name'])
                    ->sortable(['last_name', 'first_name']),

                TextColumn::make('membership')
                    ->label('Membership Type')
                    ->badge()
                     ->formatStateUsing(fn ($state) => match ($state) {
                        'RM'    => 'Regular Member',
                        'LM'    => 'Life Member',
                        'TM'    => 'Trainee Member',
                        default => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                        'RM'    => 'info',
                        'LM'    => 'success',
                        'TM'    => 'warning',
                        default => 'gray',
                        }),
                    

                TextColumn::make('member.mem_email_address')
                    ->label('Email (PSA Record)')
                    ->width('220px')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('proof_payment')
                    ->label('Payment')
                    ->disk('uploads')
                    ->height(48)
                    ->width(64)
                    ->placeholder('No Uploaded Photo')
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover cursor-pointer'])
                    // table photo
                    ->getStateUsing(fn ($record) => $record->proof_payment
                    ? asset('uploads/' . $record->proof_payment)
                    : null)
                    // whe the admin click the photo it will pop up a modal to show the full image
                    ->action(
                        Action::make('previewPayment')
                            ->modalHeading('Proof of Payment')
                            ->modalContent(fn (Registration $record) => new \Illuminate\Support\HtmlString(
                                $record->proof_payment
                                    ? '<div class="flex justify-center p-2">
                                        <img src="' . asset('uploads/' . $record->proof_payment) . '"
                                            class="max-h-[70vh] r ounded-lg object-contain" />
                                    </div>'
                                    : '<p class="text-center text-gray-400 py-6">No Uploaded Photo</p>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('lg')
                    ),


                ImageColumn::make('discount_id')
                    ->label('Discount ID')
                    ->disk('uploads')
                    ->height(48)
                    ->width(64)
                    ->placeholder('No Uploaded Photo')
                    ->toggleable()
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover cursor-pointer'])
                    ->getStateUsing(fn ($record) => $record->discount_id
                    ? asset('uploads/' . $record->discount_id)
                    : null)
                    ->action(
                        Action::make('previewDiscountId')
                            ->modalHeading('Senior Discount ID')
                            ->modalContent(fn (Registration $record) => new \Illuminate\Support\HtmlString(
                                $record->discount_id
                                    ? '<div class="flex justify-center p-2">
                                        <img src="' . asset('uploads/' . $record->discount_id) . '"
                                                class="max-h-[70vh] rounded-lg object-contain" />
                                    </div>'
                                    : '<p class="text-center text-gray-400 py-6">No Uploaded Photo</p>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('3xl')
                    ),

                TextColumn::make('status')
                    ->width('120px')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Pending'  => 'warning',
                        default    => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'Approved' => 'heroicon-m-check-circle',
                        'Rejected' => 'heroicon-m-x-circle',
                        'Pending'  => 'heroicon-m-clock',
                        default    => null,
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->width('160px')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pending'  => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->default('Pending'),

                SelectFilter::make('membership')
                    ->label('Membership Type')
                    ->options([
                        'RM' => 'Regular Member',
                        'LM' => 'Life Member',
                        'TM' => 'Trainee Member',
                    ]),

                SelectFilter::make('chapter')
                    ->label('Chapter')
                    ->options(fn () => Member::query()
                        ->whereNotNull('psa_chapter_code')
                        ->distinct()
                        ->orderBy('psa_chapter_code')
                        ->pluck('psa_chapter_code', 'psa_chapter_code')
                        ->toArray()
                    )
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn ($q, $value) => $q
                            ->whereHas('member', fn ($q2) => $q2->where('psa_chapter_code', $value))
                        )
                    ),

                Filter::make('has_discount')
                    ->label('Has Senior Discount')
                    ->query(fn (Builder $q) => $q->whereNotNull('discount_id')),

                Filter::make('ref_range')
                    ->label('Reference # Range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('ref_from')
                                ->label('From  #')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('e.g. 1'),

                            TextInput::make('ref_to')
                                ->label('To  #')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('e.g. 50'),
                        ]),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['ref_from'] ?? null, fn ($q, $v) => $q->where('id', '>=', (int) $v))
                        ->when($data['ref_to']   ?? null, fn ($q, $v) => $q->where('id', '<=', (int) $v))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['ref_from'] ?? null;
                        $to   = $data['ref_to']   ?? null;

                        if ($from && $to)  return "Ref #{$from} – #{$to}";
                        if ($from)         return "Ref # from #{$from}";
                        if ($to)           return "Ref # up to #{$to}";

                        return null;
                    }),

            ])

            ->recordActions([


                // Action::make('view_record')
                //     ->label('View')
                //     ->icon('heroicon-o-eye')
                //     ->modalHeading(fn (Registration $r) => '' . $r->full_name) 
                //     ->modalWidth('3xl')
                //     ->modalSubmitAction(false)
                //     ->modalCancelActionLabel('Close')
                //     ->infolist([

                //         Section::make('Personal Information')
                //             ->columns(2)
                //             ->schema([
                //                 TextEntry::make('psa_id')
                //                     ->label('PSA ID')
                //                     ->badge()
                //                     ->color('primary')
                //                     ->fontFamily('mono'),

                //                 TextEntry::make('status')
                //                     ->label('Status')
                //                     ->badge()
                //                     ->color(fn ($state) => match ($state) {
                //                         'Approved' => 'success',
                //                         'Rejected' => 'danger',
                //                         'Pending'  => 'warning',
                //                         default    => 'gray',
                //                     }),

                //                 TextEntry::make('full_name')
                //                     ->label('Full Name')
                //                     ->columnSpanFull(),

                //                 TextEntry::make('membership')
                //                     ->label('Membership Type')
                //                     ->badge()
                //                     ->formatStateUsing(fn ($state) => match ($state) {
                //                         'RM'    => 'Regular Member',
                //                         'LM'    => 'Life Member',
                //                         'TM'    => 'Trainee Member',
                //                         default => $state,
                //                     })
                //                     ->color(fn ($state) => match ($state) {
                //                         'RM'    => 'info',
                //                         'LM'    => 'success',
                //                         'TM'    => 'warning',
                //                         default => 'gray',
                //                     }),

                //                 TextEntry::make('email')
                //                     ->label('Email Address')
                //                     ->icon('heroicon-m-envelope'),

                //                 TextEntry::make('contact_number')
                //                     ->label('Contact Number')
                //                     ->icon('heroicon-m-phone'),

                //                 TextEntry::make('created_at')
                //                     ->label('Submitted At')
                //                     ->dateTime('F d, Y g:i A')
                //                     ->icon('heroicon-m-calendar')
                //                     ->columnSpanFull(),

                //                 TextEntry::make('rejection_title')
                //                     ->label('Rejection Title')
                //                     ->visible(fn (Registration $r) => $r->status === Registration::STATUS_REJECTED && filled($r->rejection_title))
                //                     ->columnSpanFull(),

                //                 TextEntry::make('rejection_reason')
                //                     ->label('Rejection Reason')
                //                     ->html()
                //                     ->visible(fn (Registration $r) => $r->status === Registration::STATUS_REJECTED && filled($r->rejection_reason))
                //                     ->columnSpanFull(),
                //             ]),
                //     // uploads
                //         Section::make('Attachments')
                //             ->columns(2)
                //             ->schema([
                //                 ImageEntry::make('proof_payment')
                //                     ->disk('uploads')
                //                     ->label('Proof of Payment')
                //                     ->height(200)
                //                     ->placeholder('No Uploaded Photo')
                //                     ->extraImgAttributes(['class' => 'rounded-lg object-cover w-full'])
                //                     // photo in the table
                //                        ->getStateUsing(fn ($record) => $record->proof_payment
                //                         ? asset('uploads/' . $record->proof_payment)
                //                         : null)
                //                         // pop up modal for image preview when the admin click the photo
                //                     ->action(
                //                         // pop up modal for image preview
                //                         Action::make('previewPayment')
                //                             ->modalHeading('Proof of Payment')
                //                             ->modalContent(fn (Registration $record) => new \Illuminate\Support\HtmlString(
                //                                 $record->proof_payment
                //                                     ? '<div class="flex justify-center p-2">
                //                                         <img src="' . asset('uploads/' . $record->proof_payment) . '"
                //                                             class="max-h-[70vh] rounded-lg object-contain" />
                //                                     </div>'
                //                                     : '<p class="text-center text-gray-400 py-6">No Uploaded Photo</p>'
                //                             ))
                //                             ->modalSubmitAction(false)
                //                             ->modalCancelActionLabel('Close')
                //                             ->modalWidth('lg')
                //                     ),

                //                 ImageEntry::make('discount_id')
                //                     ->disk('uploads')
                //                     ->label('Senior Discount ID')
                //                     ->height(200)
                //                     ->extraImgAttributes(['class' => 'rounded-lg object-cover cursor-pointer'])
                //                     ->placeholder('No Uploaded Photo')
                //                        ->getStateUsing(fn ($record) => $record->discount_id
                //                         ? asset('uploads/' . $record->discount_id)
                //                         : null)

                //                     ->action(
                //                         Action::make('previewDiscountId')
                //                             ->modalHeading('Senior Discount ID')
                //                             ->modalContent(fn (Registration $record) => new \Illuminate\Support\HtmlString(
                //                                 $record->discount_id
                //                                     ? '<div class="flex justify-center p-2">
                //                                         <img src="' . asset('uploads/' . $record->discount_id) . '"
                //                                                 class="max-h-[70vh] rounded-lg object-contain" />
                //                                     </div>'
                //                                     : '<p class="text-center text-gray-400 py-6">No Uploaded Photo</p>'
                //                             ))
                //                             ->modalSubmitAction(false)
                //                             ->modalCancelActionLabel('Close')
                //                             ->modalWidth('3xl')
                //                     ),
                //             ]),
                //     ])
                //     ->action(fn () => null),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Registration $r) => $r->isPending())
                    ->requiresConfirmation()
                    ->action(function (Registration $r): void {
                        $r->update(['status' => Registration::STATUS_APPROVED]);
                        Notification::make()->title('Registration approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Registration $r) => $r->isPending()) //it show this action if the status is pending otherwise it will not how
                    ->requiresConfirmation()
                    ->modalHeading('Reject Registration')
                    ->modalDescription('Are you sure you want to reject this registration?')
                    ->modalSubmitActionLabel('Reject')
                    ->schema([
                        Toggle::make('write_message')
                            ->label('Write a custom rejection message?')
                            ->helperText('If off, the registrant will receive a standard rejection email with no specific reason.')
                            ->live()
                            ->default(false),

                        TextInput::make('rejection_title')
                            ->label('Message Title')
                            ->placeholder('e.g. Incomplete Requirements')
                            ->visible(fn (Get $get) => $get('write_message'))
                            ->required(fn (Get $get) => $get('write_message')),

                        RichEditor::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->placeholder('Explain why this registration is being rejected...')
                            ->visible(fn (Get $get) => $get('write_message'))
                            ->required(fn (Get $get) => $get('write_message'))
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
                    ])
                    ->action(function (Registration $r, array $data): void {
                        $r->update([
                            'status'           => Registration::STATUS_REJECTED,
                            'rejection_title'  => $data['write_message'] ? ($data['rejection_title']  ?? null) : null,
                            'rejection_reason' => $data['write_message'] ? ($data['rejection_reason'] ?? null) : null,
                        ]);
                        Notification::make()->title('Registration rejected.')->danger()->send();
                    }),

            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => Registration::STATUS_APPROVED])),

                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Toggle::make('write_message')
                                ->label('Write a custom rejection message?')
                                ->helperText('Applies the same title and reason to all selected registrations.')
                                ->live()
                                ->default(false),

                            TextInput::make('rejection_title')
                                ->label('Message Title')
                                ->placeholder('e.g. Incomplete Requirements')
                                ->visible(fn (Get $get) => $get('write_message'))
                                ->required(fn (Get $get) => $get('write_message')),

                            RichEditor::make('rejection_reason')
                                ->label('Reason for Rejection')
                                ->placeholder('Explain why these registrations are being rejected...')
                                ->visible(fn (Get $get) => $get('write_message'))
                                ->required(fn (Get $get) => $get('write_message'))
                                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
                        ])
                        // bulk actions logic with ternary operator if the user wants to write message/title it will store to var if not 
                        // it will return null 
                        ->action(fn ($records, array $data) => $records->each->update([
                            'status'           => Registration::STATUS_REJECTED,
                            'rejection_title'  => $data['write_message'] ? ($data['rejection_title']  ?? null) : null,
                            'rejection_reason' => $data['write_message'] ? ($data['rejection_reason'] ?? null) : null,
                        ])),
                    // action for selecting multiple data
                    BulkAction::make('export_selected_pdf')
                        ->label('Export Selected to PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Export Selected Registrations')
                        ->modalDescription('This will generate a PDF with one page per selected registration, including their proof of payment.')
                        ->modalSubmitActionLabel('Generate PDF')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->sort()->values();
                            $url = route('admin.registrations.export-pdf', [
                                'ids' => $ids->implode(','),
                            ]);
                            return redirect()->away($url);
                        }),

                ]),

                // PDF Convertion   
                Action::make('export_pdf_range')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->modalHeading('Export Registrations to PDF')
                    ->modalDescription('Enter a reference number range to export those registrations as a PDF, one record per page.')
                    ->modalSubmitActionLabel('Generate PDF')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('ref_from')
                                ->label('Reference # From')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->prefix('#')
                                ->placeholder('e.g. 000001'),

                            TextInput::make('ref_to')
                                ->label('Reference # To')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->prefix('#')
                                ->placeholder('e.g. 000016'),
                        ]),
                    ])
                    // if the user create a mistake of inputting in the max input into ref_from it will still works in a reverse order
                    // the code automatically swaps the values so the range still works.
                    ->action(function (array $data) {
                        $from = min((int) $data['ref_from'], (int) $data['ref_to']); //convertion string to int 
                        $to   = max((int) $data['ref_from'], (int) $data['ref_to']);

                        $url = route('admin.registrations.export-pdf', [
                            'from' => $from,
                            'to'   => $to,
                        ]);  //redirect to laraveldompdf
                        return redirect()->away($url);
                    }),
            ]);
    }
}
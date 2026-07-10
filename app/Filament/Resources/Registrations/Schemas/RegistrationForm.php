<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('PSA Member Reference')
                    ->description('Links this registration to a verified PSA member record.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('psa_id')
                                ->label('PSA ID')
                                ->required()
                                ->maxLength(4)
                                ->disabledOn('edit')
                                ->dehydrated(),

                            TextInput::make('prc_number')
                                ->label('PRC Number')
                                ->numeric()
                                ->required()
                                ->maxLength(7),

                            Select::make('membership')
                                ->label('Membership Type')
                                ->options([
                                    'RM' => 'Regular Member',
                                    'LM' => 'Life Member',
                                    'TM' => 'Trainee Member',
                                ])
                                ->required(),
                        ]),
                    ]),

                Section::make('Personal Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('first_name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('last_name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('middle_name')
                                ->maxLength(255),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('gender')
                                ->options([
                                    'Male'   => 'Male',
                                    'Female' => 'Female',
                                    'Other'  => 'Other',
                                ])
                                ->required(),

                            TextInput::make('country')
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Contact Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('contact_number')
                                ->label('Contact Number')
                                ->tel()
                                ->required()
                                ->maxLength(11),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('hospital_name')
                                ->label('Hospital / Institution Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('hospital_address')
                                ->label('Hospital Address')
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Uploaded Documents')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('proof_payment')
                                ->label('Payment Proof')
                                ->disk('public')
                                ->directory('registrations/payment-proofs')
                                ->image()
                                ->imageEditor()
                                ->maxSize(5120)
                                ->required(),

                            FileUpload::make('discount_id')
                                ->label('Senior Citizen Discount ID')
                                ->disk('public')
                                ->directory('registrations/discount-ids')
                                ->image()
                                ->imageEditor()
                                ->maxSize(5120)
                                ->nullable(),
                        ]),
                    ]),

                Section::make('Registration Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                Registration::STATUS_PENDING  => 'Pending',
                                Registration::STATUS_APPROVED => 'Approved',
                                Registration::STATUS_REJECTED => 'Rejected',
                            ])
                            ->required()
                            ->native(false),
                    ]),

            ]);
    }
}
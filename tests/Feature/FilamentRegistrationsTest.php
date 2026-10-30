<?php

use App\Mail\WorkshopRegistrationConfirmation;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function (): void {
    foreach (['registrations', 'workshop_registrations', 'workshops', 'members', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('members', function (Blueprint $table): void {
        $table->string('member_id_no')->primary();
        $table->string('psa_chapter_code')->nullable();
        $table->string('mem_email_address')->nullable();
    });

    Schema::create('registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id', 100)->nullable();
        $table->integer('prc_number')->nullable();
        $table->string('last_name')->nullable();
        $table->string('first_name')->nullable();
        $table->string('middle_name')->nullable();
        $table->string('hospital_name')->nullable();
        $table->string('hospital_address')->nullable();
        $table->string('email')->nullable();
        $table->string('contact_number')->nullable();
        $table->string('membership')->nullable();
        $table->string('discount_id')->nullable();
        $table->string('proof_payment')->nullable();
        $table->string('status')->nullable();
        $table->string('rejection_title')->nullable();
        $table->text('rejection_reason')->nullable();
        $table->string('country')->nullable();
        $table->timestamps();
    });

    Schema::create('workshops', function (Blueprint $table): void {
        $table->id();
        $table->string('code', 50)->unique();
        $table->string('name');
        $table->string('session')->nullable();
        $table->string('venue')->nullable();
        $table->unsignedInteger('capacity')->default(60);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('workshop_registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id')->nullable()->unique();
        $table->string('mem_last_name');
        $table->string('mem_first_name');
        $table->string('mem_middle_name')->nullable();
        $table->string('mem_type')->nullable();
        $table->string('prc_number')->unique();
        $table->string('hospital');
        $table->string('email');
        $table->string('phone', 20);
        $table->json('workshops');
        $table->timestamps();
    });

    Workshop::create([
        'code' => 'RA',
        'name' => 'Regional Anesthesia Workshop',
        'session' => '9:00 AM – 12:00 Noon',
        'venue' => 'Executive 5',
        'capacity' => 60,
        'sort_order' => 10,
    ]);

    WorkshopRegistration::create([
        'psa_id' => 'NMW_0001',
        'mem_last_name' => 'Tester',
        'mem_first_name' => 'Registrant',
        'mem_middle_name' => '',
        'mem_type' => 'Non-member',
        'prc_number' => '900001',
        'hospital' => 'Test Hospital',
        'email' => 'registrant@example.test',
        'phone' => '09171234567',
        'workshops' => ['RA'],
    ]);

    Registration::create([
        'psa_id' => '2312',
        'prc_number' => 3213131,
        'last_name' => 'BUCCAT',
        'first_name' => 'MARY ANN',
        'middle_name' => 'Q.',
        'hospital_name' => 'UPGH',
        'hospital_address' => 'Test Address',
        'email' => 'member@example.test',
        'contact_number' => '09171234567',
        'membership' => 'RM',
        'status' => Registration::STATUS_PENDING,
        'country' => 'Philippines',
    ]);
});

test('the Filament annual registration list renders records with a valid key', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => Hash::make('password'),
    ]);
    config(['app.env' => 'local']);

    $this->actingAs($user)
        ->get('/admin/registrations')
        ->assertOk()
        ->assertSee('PSA ID');
});

test('the Filament workshop list exposes the resend confirmation action', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => Hash::make('password'),
    ]);
    config(['app.env' => 'local']);

    $this->actingAs($user)
        ->get('/admin/workshop-registrations')
        ->assertOk()
        ->assertSee('Resend confirmation');
});

test('Filament can resend a workshop confirmation email', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => Hash::make('password'),
    ]);
    config(['app.env' => 'local']);
    Mail::fake();

    Livewire::actingAs($user)
        ->test(App\Filament\Resources\WorkshopRegistrations\Pages\ListWorkshopRegistrations::class)
        ->callTableAction('resend_confirmation', WorkshopRegistration::query()->first()->getKey());

    Mail::assertSent(WorkshopRegistrationConfirmation::class, function (WorkshopRegistrationConfirmation $mail): bool {
        return $mail->registration->email === 'registrant@example.test'
            && $mail->workshops[0]['code'] === 'RA';
    });
});

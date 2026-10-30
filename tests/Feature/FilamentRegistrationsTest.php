<?php

use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    foreach (['registrations', 'members', 'users'] as $table) {
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

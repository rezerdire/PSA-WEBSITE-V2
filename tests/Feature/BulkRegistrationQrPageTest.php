<?php

use App\Models\RegistrationQrBatch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    foreach (['registration_qr_batch_recipients', 'registration_qr_batches', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('registration_qr_batches', function (Blueprint $table): void {
        $table->id();
        $table->string('status')->default('queued');
        $table->unsignedInteger('requested_limit');
        $table->unsignedInteger('total_count')->default(0);
        $table->unsignedInteger('processed_count')->default(0);
        $table->unsignedInteger('sent_count')->default(0);
        $table->unsignedInteger('failed_count')->default(0);
        $table->unsignedInteger('skipped_count')->default(0);
        $table->unsignedInteger('not_selected_count')->default(0);
        $table->unsignedBigInteger('current_registration_id')->nullable();
        $table->string('current_name')->nullable();
        $table->string('current_email')->nullable();
        $table->string('current_status')->nullable();
        $table->text('error_message')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::create('registration_qr_batch_recipients', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('batch_id');
        $table->unsignedBigInteger('registration_id');
        $table->unsignedInteger('position');
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('status')->default('pending');
        $table->text('error_message')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamp('failed_at')->nullable();
        $table->timestamps();
    });
});

test('bulk QR monitoring page shows the active batch', function (): void {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);
    $batch = RegistrationQrBatch::create([
        'status' => RegistrationQrBatch::STATUS_RUNNING,
        'requested_limit' => 50,
        'total_count' => 1,
        'processed_count' => 1,
        'sent_count' => 1,
        'current_name' => 'Current Recipient',
        'current_email' => 'current@example.test',
        'current_status' => 'sent',
    ]);
    config(['app.env' => 'local']);
    $this->actingAs($user)
        ->get(route('filament.admin.resources.registrations.bulk-qr', ['batch' => $batch->id]))
        ->assertOk()
        ->assertSee('Bulk QR sending #' . $batch->id)
        ->assertSee('Current Recipient');
});

test('bulk QR history route renders without a selected batch', function (): void {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);
    config(['app.env' => 'local']);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.registrations.bulk-qr'))
        ->assertOk()
        ->assertSee('No bulk QR batch selected');
});

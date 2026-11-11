<?php

use App\Jobs\SendRegistrationQrBatchRecipient;
use App\Models\Registration;
use App\Models\Member;
use App\Models\RegistrationQrBatch;
use App\Models\RegistrationQrBatchRecipient;
use App\Models\User;
use App\Services\BulkRegistrationQrService;
use App\Services\MemberQrService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    foreach (['registration_qr_batch_recipients', 'registration_qr_batches', 'registration_qr_sends', 'registrations', 'members_qr', 'members', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('members', function (Blueprint $table): void {
        $table->string('member_id_no')->primary();
        $table->timestamps();
    });

    Schema::create('members_qr', function (Blueprint $table): void {
        $table->id();
        $table->string('member_id_no');
        $table->string('qr_path');
        $table->string('qr_hash')->nullable();
        $table->timestamps();
    });

    Schema::create('registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id')->nullable();
        $table->string('last_name')->nullable();
        $table->string('first_name')->nullable();
        $table->string('middle_name')->nullable();
        $table->string('email')->nullable();
        $table->string('membership')->nullable();
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::create('registration_qr_sends', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('registration_id');
        $table->timestamp('sent_at');
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

test('bulk QR selection is oldest-first and respects the requested limit', function (): void {
    Bus::fake();

    Registration::create([
        'psa_id' => 'NON_MEMBER',
        'first_name' => 'Non',
        'last_name' => 'Member',
        'email' => 'nonmember@example.test',
        'membership' => 'NM',
        'status' => Registration::STATUS_APPROVED,
    ]);
    $oldest = Registration::create([
        'psa_id' => 'OLD',
        'first_name' => 'Oldest',
        'last_name' => 'Registrant',
        'email' => 'oldest@example.test',
        'membership' => 'RM',
        'status' => Registration::STATUS_APPROVED,
    ]);
    $next = Registration::create([
        'psa_id' => 'NEXT',
        'first_name' => 'Next',
        'last_name' => 'Registrant',
        'email' => 'next@example.test',
        'membership' => 'RM',
        'status' => Registration::STATUS_APPROVED,
    ]);
    Registration::create([
        'psa_id' => 'PENDING',
        'first_name' => 'Pending',
        'last_name' => 'Registrant',
        'email' => 'pending@example.test',
        'membership' => 'RM',
        'status' => Registration::STATUS_PENDING,
    ]);

    $batch = app(BulkRegistrationQrService::class)->start(1);

    expect($batch->total_count)->toBe(1)
        ->and($batch->not_selected_count)->toBe(1)
        ->and($batch->recipients()->where('status', RegistrationQrBatchRecipient::STATUS_PENDING)->value('registration_id'))
        ->toBe($oldest->id)
        ->and($batch->recipients()->where('status', RegistrationQrBatchRecipient::STATUS_NOT_SELECTED_LIMIT)->value('registration_id'))
        ->toBe($next->id);
});

test('a bad email is recorded as a failed recipient and advances progress', function (): void {
    $registration = Registration::create([
        'psa_id' => 'BAD',
        'first_name' => 'Bad',
        'last_name' => 'Email',
        'email' => 'not-an-email',
        'status' => Registration::STATUS_APPROVED,
    ]);
    $batch = RegistrationQrBatch::create([
        'status' => RegistrationQrBatch::STATUS_QUEUED,
        'requested_limit' => 1,
        'total_count' => 1,
    ]);
    $recipient = $batch->recipients()->create([
        'registration_id' => $registration->id,
        'position' => 1,
        'name' => $registration->full_name,
        'email' => $registration->email,
    ]);

    $job = new SendRegistrationQrBatchRecipient($batch->id, $recipient->id);
    $job->handle(Mockery::mock(MemberQrService::class));

    expect($recipient->fresh()->status)->toBe(RegistrationQrBatchRecipient::STATUS_FAILED)
        ->and($recipient->fresh()->error_message)->toContain('Invalid or missing email')
        ->and($batch->fresh()->processed_count)->toBe(1)
        ->and($batch->fresh()->failed_count)->toBe(1);
});

test('bulk QR export includes the limit-excluded recipient note', function (): void {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);
    $batch = RegistrationQrBatch::create([
        'status' => RegistrationQrBatch::STATUS_COMPLETED_WITH_FAILURES,
        'requested_limit' => 1,
        'total_count' => 1,
        'not_selected_count' => 1,
    ]);
    $registration = Registration::create([
        'psa_id' => 'EXCLUDED',
        'first_name' => 'Excluded',
        'last_name' => 'Registrant',
        'email' => 'excluded@example.test',
        'status' => Registration::STATUS_APPROVED,
    ]);
    $batch->recipients()->create([
        'registration_id' => $registration->id,
        'position' => 2,
        'name' => $registration->full_name,
        'email' => $registration->email,
        'status' => RegistrationQrBatchRecipient::STATUS_NOT_SELECTED_LIMIT,
        'error_message' => 'Not selected because the bulk send limit was reached.',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.registrations.qr-batches.export', $batch));

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($response->getStatusCode())->toBe(200)
        ->and($content)
        ->toContain('Not selected because the bulk send limit was reached.');
});

test('bulk QR jobs skip registrations that are no longer approved', function (): void {
    $registration = Registration::create([
        'psa_id' => 'PENDING',
        'first_name' => 'Pending',
        'last_name' => 'Registrant',
        'email' => 'pending@example.test',
        'status' => Registration::STATUS_PENDING,
    ]);
    $batch = RegistrationQrBatch::create([
        'status' => RegistrationQrBatch::STATUS_QUEUED,
        'requested_limit' => 1,
        'total_count' => 1,
    ]);
    $recipient = $batch->recipients()->create([
        'registration_id' => $registration->id,
        'position' => 1,
        'name' => $registration->full_name,
        'email' => $registration->email,
    ]);

    (new SendRegistrationQrBatchRecipient($batch->id, $recipient->id))
        ->handle(Mockery::mock(MemberQrService::class));

    expect($recipient->fresh()->status)->toBe(RegistrationQrBatchRecipient::STATUS_SKIPPED)
        ->and($recipient->fresh()->error_message)->toContain('no longer Approved')
        ->and($batch->fresh()->sent_count)->toBe(0);
});

test('bulk QR marks a missing generated card as failed without sending email', function (): void {
    Mail::fake();

    $member = Member::create(['member_id_no' => 'NO_QR']);
    $registration = Registration::create([
        'psa_id' => $member->member_id_no,
        'first_name' => 'Missing',
        'last_name' => 'QR',
        'email' => 'missing-qr@example.test',
        'membership' => 'RM',
        'status' => Registration::STATUS_APPROVED,
    ]);
    $batch = RegistrationQrBatch::create([
        'status' => RegistrationQrBatch::STATUS_QUEUED,
        'requested_limit' => 1,
        'total_count' => 1,
    ]);
    $recipient = $batch->recipients()->create([
        'registration_id' => $registration->id,
        'position' => 1,
        'name' => $registration->full_name,
        'email' => $registration->email,
    ]);

    $qrService = Mockery::mock(MemberQrService::class);
    $qrService->shouldReceive('generate')->once()->with(Mockery::type(Member::class));

    (new SendRegistrationQrBatchRecipient($batch->id, $recipient->id))
        ->handle($qrService);

    expect($recipient->fresh()->status)->toBe(RegistrationQrBatchRecipient::STATUS_FAILED)
        ->and($recipient->fresh()->error_message)->toContain('QR/ID card was not generated')
        ->and($batch->fresh()->sent_count)->toBe(0);

    Mail::assertNothingSent();
});

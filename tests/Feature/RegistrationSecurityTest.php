<?php

use App\Mail\RegistrationEmailVerification;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function registrationAttributes(array $overrides = []): array
{
    return array_merge([
        'psa_id' => '0123',
        'prc_number' => 12345,
        'last_name' => 'Member',
        'first_name' => 'Test',
        'middle_name' => '',
        'hospital_name' => 'Hospital',
        'hospital_address' => 'Address',
        'email' => 'test@example.com',
        'contact_number' => '09123456789',
        'membership' => 'RM',
        'discount_id' => null,
        'proof_payment' => null,
        'status' => Registration::STATUS_PENDING,
        'country' => 'Philippines',
    ], $overrides);
}

function guestRegistrationComponent()
{
    return Livewire::test('event-registration.event-registration-guest')
        ->set('firstName', 'Guest')
        ->set('lastName', 'Applicant')
        ->set('prcNumber', '54321')
        ->set('email', 'guest@example.com')
        ->set('contactNumber', '09123456789')
        ->set('hospitalName', 'Hospital')
        ->set('hospitalAddress', 'Address')
        ->set('country', 'Philippines')
        ->set('paymentProof', UploadedFile::fake()->createWithContent('proof.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')));
}

test('active PRC numbers are unique but become reusable after rejection', function () {
    $first = Registration::create(registrationAttributes());

    expect($first->active_prc_number)->toBe(12345);
    expect(fn () => Registration::create(registrationAttributes(['psa_id' => '0124'])))
        ->toThrow(QueryException::class);

    $first->status = Registration::STATUS_REJECTED;
    $first->save();

    expect($first->fresh()->active_prc_number)->toBeNull();

    $resubmission = Registration::create(registrationAttributes(['psa_id' => '0124']));
    expect($resubmission->active_prc_number)->toBe(12345);
});

test('member submission re-reads membership and names from the database', function () {
    Storage::fake('local');
    Mail::fake();

    DB::table('members')->insert([
        'member_id_no' => '0123',
        'psa_chapter_code' => 'ABC',
        'psa_mem_type' => 'RM',
        'mem_stat' => 'Active',
        'mem_last_name' => 'Database',
        'mem_first_name' => 'Member',
        'mem_middle_name' => 'Original',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test('event-registration.event-reg-form')
        ->set('psaId', '0123')
        ->call('verify')
        ->assertSet('membership', 'RM')
        ->set('firstName', 'Tampered')
        ->set('lastName', 'Tampered')
        ->set('prcNumber', '54321')
        ->set('email', 'member@example.com')
        ->set('contactNumber', '09123456789')
        ->set('hospitalName', 'Hospital')
        ->set('hospitalAddress', 'Address')
        ->set('country', 'Philippines')
        ->set('paymentProof', UploadedFile::fake()->image('proof.png'))
        ->call('submit')
        ->assertSet('submitted', true);

    $registration = Registration::sole();
    expect($registration->first_name)->toBe('Member')
        ->and($registration->last_name)->toBe('Database')
        ->and($registration->middle_name)->toBe('Original')
        ->and($registration->membership)->toBe('RM');
});

test('guest email verification blocks registration until the emailed code is submitted', function () {
    Storage::fake('local');
    Mail::fake();

    $component = guestRegistrationComponent()
        ->call('reviewSubmission')
        ->call('submit')
        ->assertSee('Email verification code')
        ->assertSee('Send a new code');

    expect(Registration::count())->toBe(0);

    $code = null;
    Mail::assertSent(RegistrationEmailVerification::class, function (RegistrationEmailVerification $mail) use (&$code): bool {
        $code = $mail->code;
        return true;
    });
    expect($code)->toMatch('/^\d{6}$/');

    $component->set('emailVerificationCode', $code)
        ->call('submit')
        ->assertSet('submitted', true);

    $registration = Registration::sole();
    expect($registration->email)->toBe('guest@example.com')
        ->and($registration->psa_id)->toBe('NM_0001');
    Storage::disk('local')->assertExists($registration->proof_payment);
});

test('a code cannot verify a changed guest email address', function () {
    Storage::fake('local');
    Mail::fake();

    $component = guestRegistrationComponent()
        ->call('reviewSubmission')
        ->call('submit');

    $code = null;
    Mail::assertSent(RegistrationEmailVerification::class, function (RegistrationEmailVerification $mail) use (&$code): bool {
        $code = $mail->code;
        return true;
    });

    $component->set('email', 'different@example.com')
        ->set('emailVerificationCode', $code)
        ->call('submit');

    expect(Registration::count())->toBe(0);
});

test('registration attachments require authentication and are served from private storage', function () {
    Storage::fake('local');
    $path = 'Registration/ProofofPayment/proof.png';
    Storage::disk('local')->put($path, 'private attachment content');
    $registration = Registration::create(registrationAttributes(['proof_payment' => $path]));
    $url = route('admin.registrations.attachment', [$registration, 'proof-payment']);

    $this->getJson($url)->assertUnauthorized();

    $response = $this->actingAs(User::factory()->create())->get($url)->assertOk();
    ob_start();
    $response->baseResponse->sendContent();
    $body = ob_get_clean();
    expect($body)->toBe('private attachment content');
});

test('document migration verifies private copies before removing public files', function () {
    Storage::fake('uploads');
    Storage::fake('local');
    $path = 'Registration/ProofofPayment/proof.png';
    Storage::disk('uploads')->put($path, 'legacy attachment content');

    expect(Artisan::call('registrations:privatize-documents', [
        '--delete-public' => true,
        '--force' => true,
    ]))->toBe(0);

    Storage::disk('local')->assertExists($path);
    Storage::disk('uploads')->assertMissing($path);
});

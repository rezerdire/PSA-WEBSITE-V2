<?php

use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('registrations', function ($table) {
        $table->id();
        $table->string('psa_id');
        $table->string('status');
    });

    config([
        'convention.evaluation_form_url' => 'https://forms.google.test/evaluation',
        'convention.evaluation_responses_csv_url' => 'https://sheets.google.test/responses.csv',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('registrations');
});

function createConventionRegistration(string $psaId, string $status = Registration::STATUS_APPROVED): void
{
    DB::table('registrations')->insert(['psa_id' => $psaId, 'status' => $status]);
}

test('unregistered PSA IDs cannot access the evaluation link', function () {
    Http::fake();

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-404'])
        ->assertOk()
        ->assertSee('not find an active Annual Convention registration')
        ->assertDontSee('Open Evaluation Form');

    Http::assertNothingSent();
});

test('registered PSA IDs get a configuration message when Google URLs are missing', function () {
    createConventionRegistration('PSA-123');
    config([
        'convention.evaluation_form_url' => null,
        'convention.evaluation_responses_csv_url' => null,
    ]);
    Http::fake();

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('Your registration is verified, but the evaluation form is not configured yet')
        ->assertDontSee('Open Evaluation Form');

    Http::assertNothingSent();
});

test('registered PSA IDs get the evaluation link only when absent from the response sheet', function () {
    createConventionRegistration('PSA-123');
    Http::fake([
        'sheets.google.test/*' => Http::response("Timestamp,PSA ID No. \n2026-11-13,PSA-OTHER\n", 200),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => ' psa-123 '])
        ->assertOk()
        ->assertSee('Your registration is verified')
        ->assertSee('https://forms.google.test/evaluation')
        ->assertSee('Open Evaluation Form')
        ->assertDontSee('Download Certificate of Attendance');
});

test('a matching PSA ID shows the completion message and a visual-only certificate button', function () {
    createConventionRegistration('PSA-123');
    Http::fake([
        'sheets.google.test/*' => Http::response("Timestamp,PSA ID No. \n2026-11-13,PSA-123\n", 200),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('already completed the Annual Convention evaluation')
        ->assertDontSee('Open Evaluation Form')
        ->assertSee('Download Certificate of Attendance')
        ->assertSee('Certificate file · PDF')
        ->assertSee('type="button"', false)
        ->assertDontSee('annual-convention-evaluation.certificate');
});

test('the evaluation link stays hidden when the response sheet cannot be checked', function () {
    createConventionRegistration('PSA-123');
    Http::fake([
        'sheets.google.test/*' => Http::response('unavailable', 503),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('could not verify the evaluation response sheet')
        ->assertDontSee('Open Evaluation Form')
        ->assertDontSee('Download Certificate of Attendance');
});

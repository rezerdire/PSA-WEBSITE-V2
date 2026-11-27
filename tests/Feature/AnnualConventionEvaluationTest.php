<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('master_lists', function ($table) {
        $table->id();
        $table->string('psa_id', 100);
        $table->string('record_type', 20);
        $table->string('first_name');
        $table->string('last_name');
        $table->string('middle_name')->nullable();
        $table->string('payment_ref_no')->nullable();
        $table->timestamps();
    });

    config([
        'convention.evaluation_form_url' => 'https://forms.google.test/evaluation',
        'convention.evaluation_responses_csv_url' => 'https://sheets.google.test/responses.csv',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('master_lists');
});

function createMasterListRecord(string $psaId, string $recordType = 'member'): void
{
    DB::table('master_lists')->insert([
        'psa_id' => $psaId,
        'record_type' => $recordType,
        'first_name' => 'Test',
        'middle_name' => 'M.',
        'last_name' => 'Person',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('PSA IDs absent from the master list cannot access the evaluation link', function () {
    Http::fake();

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-404'])
        ->assertOk()
        ->assertSee('We could not find this PSA ID in our system')
        ->assertDontSee('Open Evaluation Form');

    Http::assertNothingSent();
});

test('a master-list PSA ID gets a configuration message when Google URLs are missing', function () {
    createMasterListRecord('PSA-123');
    config([
        'convention.evaluation_form_url' => null,
        'convention.evaluation_responses_csv_url' => null,
    ]);
    Http::fake();

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('Your PSA ID is verified, but the evaluation form is not configured yet')
        ->assertSee('Test M. Person')
        ->assertDontSee('Open Evaluation Form');

    Http::assertNothingSent();
});

test('nonmember master-list IDs get the evaluation link when absent from the response sheet', function () {
    createMasterListRecord('PSA-123', 'nonmember');
    Http::fake([
        'sheets.google.test/*' => Http::response("Timestamp,PSA ID No. \n2026-11-13,PSA-OTHER\n", 200),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => ' psa-123 '])
        ->assertOk()
        ->assertSee('Your PSA ID is verified, and no evaluation response was found')
        ->assertSee('Test M. Person')
        ->assertSee('https://forms.google.test/evaluation')
        ->assertSee('Open Evaluation Form')
        ->assertDontSee('Download Certificate of Attendance');
});

test('a matching PSA ID shows the completion message and certificate availability notice', function () {
    createMasterListRecord('PSA-123');
    Http::fake([
        'sheets.google.test/*' => Http::response("Timestamp,PSA ID No. \n2026-11-13,PSA-123\n", 200),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('already completed the Annual Convention evaluation')
        ->assertSee('Test M. Person')
        ->assertDontSee('Open Evaluation Form')
        ->assertDontSee('Download Certificate of Attendance')
        ->assertSee('Certificate update')
        ->assertSee('The certificate will be announced on Facebook once it is available.')
        ->assertSee('https://www.facebook.com/philippinesocietyofanesthesiologists')
        ->assertSee('Visit our Facebook page')
        ->assertDontSee('annual-convention-evaluation.certificate');
});

test('the evaluation link stays hidden when the response sheet cannot be checked', function () {
    createMasterListRecord('PSA-123');
    Http::fake([
        'sheets.google.test/*' => Http::response('unavailable', 503),
    ]);

    $this->post(route('annual-convention-evaluation.check'), ['psa_id' => 'PSA-123'])
        ->assertOk()
        ->assertSee('could not verify the evaluation response sheet')
        ->assertSee('Test M. Person')
        ->assertDontSee('Open Evaluation Form')
        ->assertDontSee('Download Certificate of Attendance');
});

<?php

use App\Models\Member;
use App\Models\WorkshopRegistration;
use Illuminate\Database\UniqueConstraintViolationException;
use Livewire\Livewire;

function makeWorkshopMember(array $attributes = []): Member
{
    return Member::create(array_merge([
        'member_id_no' => 'T' . fake()->unique()->numerify('#####'),
        'psa_chapter_code' => 'TEST',
        'psa_mem_type' => 'RM',
        'mem_last_name' => 'Tester',
        'mem_first_name' => 'Member',
        'mem_middle_name' => 'M.',
        'mem_email_address' => 'member@example.test',
        'mem_prc_no' => '123456',
    ], $attributes));
}

function validWorkshopRegistrationData(array $overrides = []): array
{
    return array_merge([
        'lastName' => 'Visitor',
        'firstName' => 'Workshop',
        'middleName' => '',
        'prcNumber' => 'PRC-' . fake()->unique()->numerify('#####'),
        'hospital' => 'Test Hospital',
        'email' => 'visitor@example.test',
        'phone' => '09171234567',
        'workshop' => 'RA',
        'privacyConsent' => true,
    ], $overrides);
}

function workshopRegistrationRecordData(array $overrides = []): array
{
    return array_merge([
        'psa_id' => null,
        'mem_last_name' => 'Existing',
        'mem_first_name' => 'Registrant',
        'mem_middle_name' => '',
        'mem_type' => 'Non-member',
        'prc_number' => 'EXISTING-' . fake()->unique()->numerify('#####'),
        'hospital' => 'Existing Hospital',
        'email' => 'existing@example.test',
        'phone' => '09171234567',
        'workshops' => ['RA'],
        'privacy_consented_at' => now(),
    ], $overrides);
}

it('serves the workshop registration page with all workshop choices', function () {
    $this->get(route('workshop'))
        ->assertOk()
        ->assertSee('Workshop Registration')
        ->assertSee('Regional Anesthesia Workshop (RA)')
        ->assertSee('POCUS')
        ->assertSee('Airway')
        ->assertSee('Privacy notice and consent');
});


it('fills verified member data and clears the stale PSA input', function () {
    $member = makeWorkshopMember(['member_id_no' => '0001']);

    Livewire::test('workshop.workshop-registration')
        ->set('psaIdInput', '9999')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->assertSet('psaId', '0001')
        ->assertSet('psaIdInput', '')
        ->assertSet('lastName', 'Tester')
        ->assertSet('firstName', 'Member')
        ->assertSet('memType', 'Regular Member')
        ->assertSet('prcNumber', '123456')
        ->call('clearMember')
        ->assertSet('psaId', '')
        ->assertSet('psaIdInput', '')
        ->assertSet('lastName', '');
});

it('rejects unknown PSA IDs', function () {
    Livewire::test('workshop.workshop-registration')
        ->call('fillFromMember', '9999')
        ->assertHasErrors(['psaId' => 'PSA ID not found.']);
});

it('requires privacy consent and the registration fields', function () {
    Livewire::test('workshop.workshop-registration')
        ->call('submit')
        ->assertHasErrors([
            'lastName',
            'firstName',
            'prcNumber',
            'hospital',
            'email',
            'phone',
            'workshop',
            'privacyConsent',
        ]);
});

it('rejects invalid contact details through Livewire validation', function () {
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'email' => 'not-an-email',
            'phone' => '08171234567',
            'privacyConsent' => true,
        ]))
        ->call('submit')
        ->assertHasErrors(['email', 'phone'])
        ->assertSet('submitted', false);
});

it('persists a valid registration with a reference number and consent timestamp', function () {
    $data = validWorkshopRegistrationData(['prcNumber' => 'PRC-SUCCESS-001']);

    Livewire::test('workshop.workshop-registration')
        ->set($data)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('referenceNo', 'WR-000001')
        ->assertSee('Registration Complete')
        ->assertSee('WR-000001');

    $registration = WorkshopRegistration::where('prc_number', 'PRC-SUCCESS-001')->firstOrFail();

    expect($registration->reference_number)->toBe('WR-000001')
        ->and($registration->privacy_consented_at)->not->toBeNull();
});

it('rejects an already registered PSA ID inside the registration lock', function () {
    $member = makeWorkshopMember(['member_id_no' => '0002']);
    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => $member->getKey(),
        'mem_type' => 'Regular Member',
        'prc_number' => 'PRC-EXISTING-002',
    ]));

    Livewire::test('workshop.workshop-registration')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->set(validWorkshopRegistrationData([
            'lastName' => 'Tester',
            'firstName' => 'Member',
            'prcNumber' => 'PRC-NEW-002',
        ]))
        ->call('submit')
        ->assertHasErrors(['psaId' => 'This PSA ID is already registered.'])
        ->assertSet('submitted', false);
});

it('rejects duplicate PRC numbers', function () {
    WorkshopRegistration::create(workshopRegistrationRecordData(['prc_number' => 'PRC-DUPLICATE-001']));

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => 'PRC-DUPLICATE-001']))
        ->call('submit')
        ->assertHasErrors(['prcNumber' => 'This PRC number is already registered.']);
});

it('rejects a workshop after its capacity is reached', function () {
    foreach (range(1, 60) as $index) {
        WorkshopRegistration::create(workshopRegistrationRecordData([
            'prc_number' => 'PRC-FULL-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
        ]));
    }

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => 'PRC-OVER-CAPACITY']))
        ->call('submit')
        ->assertHasErrors(['workshop' => 'Sorry, this workshop is already full. Please choose another one.'])
        ->assertSet('workshop', '')
        ->assertSet('submitted', false);
});

it('enforces the nullable PSA ID unique index at the database boundary', function () {
    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => '0003',
        'prc_number' => 'PRC-UNIQUE-001',
    ]));

    expect(fn () => WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => '0003',
        'prc_number' => 'PRC-UNIQUE-002',
    ])))->toThrow(UniqueConstraintViolationException::class);
});

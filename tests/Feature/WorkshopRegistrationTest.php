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
        'prcNumber' => fake()->unique()->numerify('######'),
        'hospital' => 'Test Hospital',
        'email' => 'visitor@example.test',
        'phone' => '09171234567',
        'workshop' => 'RA',
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
    ], $overrides);
}

it('serves the workshop registration page with all workshop choices', function () {
    $this->get(route('workshop'))
        ->assertOk()
        ->assertSee('Workshop Registration')
        ->assertSee('Regional Anesthesia Workshop (RA)')
        ->assertSee('POCUS')
        ->assertSee('Airway')
        ->assertSee('0 / 60 registered');
});


it('fills verified member data without populating the PRC number', function () {
    $member = makeWorkshopMember(['member_id_no' => '0001']);

    Livewire::test('workshop.workshop-registration')
        ->set('psaIdInput', '9999')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->assertSet('psaId', '0001')
        ->assertSet('psaIdInput', '')
        ->assertSet('lastName', 'Tester')
        ->assertSet('firstName', 'Member')
        ->assertSet('memType', 'Regular Member')
        ->assertSet('prcNumber', '')
        ->assertSet('prcFromMember', false)
        ->set('prcNumber', '654321')
        ->assertSet('prcNumber', '654321')
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

it('requires the registration fields', function () {
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
        ]);
});

it('rejects invalid contact details through Livewire validation', function () {
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'email' => 'not-an-email',
            'phone' => '08171234567',
        ]))
        ->call('submit')
        ->assertHasErrors(['email', 'phone'])
        ->assertSet('submitted', false);
});

it('persists a valid non-member registration with a workshop PSA ID', function () {
    $data = validWorkshopRegistrationData(['prcNumber' => '900001']);

    Livewire::test('workshop.workshop-registration')
        ->set($data)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', 'NMW_0001')
        ->assertSee("You're successfully registered")
        ->assertDontSee('NMW_0001');

    $registration = WorkshopRegistration::where('prc_number', '900001')->firstOrFail();

    expect($registration->psa_id)->toBe('NMW_0001')
        ->and($registration->mem_type)->toBe('Non-member');
});

it('continues the four-digit non-member PSA ID sequence', function () {
    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => 'NMW_0007',
        'prc_number' => '900002',
    ]));

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'prcNumber' => '900003',
            'workshop' => 'POCUS',
        ]))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', 'NMW_0008');
});

it('rejects an already registered PSA ID inside the registration lock', function () {
    $member = makeWorkshopMember(['member_id_no' => '0002']);
    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => $member->getKey(),
        'mem_type' => 'Regular Member',
        'prc_number' => '900004',
    ]));

    Livewire::test('workshop.workshop-registration')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->set(validWorkshopRegistrationData([
            'lastName' => 'Tester',
            'firstName' => 'Member',
            'prcNumber' => '900005',
        ]))
        ->call('submit')
        ->assertHasErrors(['psaId' => 'This PSA ID is already registered.'])
        ->assertSet('submitted', false);
});

it('rejects duplicate PRC numbers', function () {
    WorkshopRegistration::create(workshopRegistrationRecordData(['prc_number' => '900006']));

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => '900006']))
        ->call('submit')
        ->assertHasErrors(['prcNumber' => 'This PRC number is already registered.']);
});

it('rejects a workshop after its capacity is reached', function () {
    foreach (range(1, 60) as $index) {
        WorkshopRegistration::create(workshopRegistrationRecordData([
            'prc_number' => '91' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
        ]));
    }

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => '920001']))
        ->call('submit')
        ->assertHasErrors(['workshop' => 'Sorry, this workshop is already full. Please choose another one.'])
        ->assertSet('workshop', '')
        ->assertSet('submitted', false);
});

it('keeps the 60-slot limit separate for each workshop', function () {
    foreach (range(1, 60) as $index) {
        WorkshopRegistration::create(workshopRegistrationRecordData([
            'prc_number' => '93' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
        ]));
    }

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'prcNumber' => '940001',
            'workshop' => 'POCUS',
        ]))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', 'NMW_0001');

    expect(WorkshopRegistration::whereJsonContains('workshops', 'POCUS')->count())->toBe(1);
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

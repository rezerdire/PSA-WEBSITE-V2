<?php

use App\Models\Member;
use App\Models\Registration;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function (): void {
    foreach (['workshop_registrations', 'registrations', 'members', 'workshops'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('members', function (Blueprint $table): void {
        $table->string('member_id_no')->primary();
        $table->string('psa_chapter_code');
        $table->string('psa_mem_type');
        $table->string('mem_last_name');
        $table->string('mem_first_name');
        $table->string('mem_middle_name')->nullable();
        $table->string('mem_email_address')->nullable();
        $table->string('mem_prc_no')->nullable();
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

    Workshop::create([
        'code' => 'RA',
        'name' => 'Regional Anesthesia Workshop (RA)',
        'session' => '9:00 AM – 12:00 Noon (Workshop Session)',
        'venue' => 'Executive 5',
        'capacity' => 60,
        'sort_order' => 10,
    ]);

    Workshop::create([
        'code' => 'POCUS',
        'name' => 'POCUS',
        'session' => '8:30 AM – 12:00 Noon',
        'venue' => 'Executive 5',
        'capacity' => 60,
        'sort_order' => 20,
    ]);

    Workshop::create([
        'code' => 'AIRWAY',
        'name' => 'Airway',
        'session' => '1:30 – 4:30 PM',
        'venue' => 'Executive 5',
        'capacity' => 60,
        'sort_order' => 30,
    ]);

    Schema::create('workshop_registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id')->nullable()->unique();
        $table->string('mem_last_name');
        $table->string('mem_first_name');
        $table->string('mem_middle_name');
        $table->string('mem_type')->nullable();
        $table->string('prc_number')->unique();
        $table->string('hospital');
        $table->string('email');
        $table->string('phone', 20);
        $table->json('workshops');
        $table->timestamps();
    });
    Schema::create('registrations', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id', 100);
        $table->integer('prc_number');
        $table->string('last_name');
        $table->string('first_name');
        $table->string('middle_name');
        $table->string('hospital_name');
        $table->string('hospital_address');
        $table->string('email');
        $table->string('contact_number');
        $table->string('membership');
        $table->string('discount_id')->nullable();
        $table->string('proof_payment')->nullable();
        $table->string('status');
        $table->string('country');
        $table->timestamps();
    });

});

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
        'workshops' => ['RA'],
    ], $overrides);
}

function makeAnnualWorkshopRegistration(array $overrides = []): Registration
{
    return Registration::create(array_merge([
        'psa_id' => 'NM_0001',
        'prc_number' => 900001,
        'last_name' => 'Visitor',
        'first_name' => 'Workshop',
        'middle_name' => '',
        'hospital_name' => 'Test Hospital',
        'hospital_address' => 'Test Address',
        'email' => 'visitor@example.test',
        'contact_number' => '09171234567',
        'membership' => 'NM',
        'discount_id' => null,
        'proof_payment' => null,
        'status' => Registration::STATUS_PENDING,
        'country' => 'Philippines',
    ], $overrides));
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

it('requires an annual registration for a verified member', function () {
    $member = makeWorkshopMember([
        'member_id_no' => '0002',
        'mem_prc_no' => '900010',
    ]);

    Livewire::test('workshop.workshop-registration')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->set(validWorkshopRegistrationData(['prcNumber' => '900010']))
        ->call('submit')
        ->assertHasErrors([
            'psaId' => 'Please complete your Annual Convention registration before registering for a workshop.',
        ])
        ->assertSet('submitted', false);

    expect(WorkshopRegistration::count())->toBe(0);
});

it('allows an eligible member to register for a workshop', function () {
    $member = makeWorkshopMember([
        'member_id_no' => '0003',
        'mem_prc_no' => '900011',
    ]);

    makeAnnualWorkshopRegistration([
        'psa_id' => '0003',
        'prc_number' => 900011,
        'membership' => 'RM',
    ]);

    Livewire::test('workshop.workshop-registration')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->set(validWorkshopRegistrationData(['prcNumber' => '900011']))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', '0003');

    expect(WorkshopRegistration::where('psa_id', '0003')->value('mem_type'))
        ->toBe('Regular Member');
});

it('requires an annual registration for a non-member PRC number', function () {
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => '900012']))
        ->call('submit')
        ->assertHasErrors([
            'prcNumber' => 'Please complete your Annual Convention registration before registering for a workshop.',
        ])
        ->assertSet('submitted', false);
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
            'workshops',
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

it('rejects overlong identity fields before attempting the database insert', function () {
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'lastName' => str_repeat('A', 256),
        ]))
        ->call('submit')
        ->assertHasErrors(['lastName'])
        ->assertSet('submitted', false);

    expect(WorkshopRegistration::count())->toBe(0);
});

it('persists a valid non-member registration with a workshop PSA ID', function () {
    makeAnnualWorkshopRegistration(['prc_number' => 900001]);
    $data = validWorkshopRegistrationData(['prcNumber' => '900001']);
    Livewire::test('workshop.workshop-registration')
        ->set($data)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', 'NMW_0001')
        ->assertSee("You're successfully registered", escape: false)
        ->assertDontSee('NMW_0001');

    $registration = WorkshopRegistration::where('prc_number', '900001')->firstOrFail();

    expect($registration->psa_id)->toBe('NMW_0001')
        ->and($registration->mem_type)->toBe('Non-member')
        ->and($registration->workshops)->toBe(['RA']);
});

it('normalizes identity and PRC details while preserving email and hospital casing', function () {
    makeAnnualWorkshopRegistration(['prc_number' => 900005]);

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'lastName' => 'visitor',
            'firstName' => 'workshop',
            'middleName' => 'm.',
            'prcNumber' => ' 900005 ',
            'hospital' => "St. Luke's Medical Center",
            'email' => 'Visitor@Example.test',
        ]))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('lastName', 'VISITOR')
        ->assertSet('firstName', 'WORKSHOP')
        ->assertSet('middleName', 'M.')
        ->assertSet('prcNumber', '900005');

    $registration = WorkshopRegistration::where('prc_number', '900005')->firstOrFail();

    expect($registration->mem_last_name)->toBe('VISITOR')
        ->and($registration->mem_first_name)->toBe('WORKSHOP')
        ->and($registration->mem_middle_name)->toBe('M.')
        ->and($registration->hospital)->toBe("St. Luke's Medical Center")
        ->and($registration->email)->toBe('Visitor@Example.test');
});

it('persists multiple workshops for one registrant', function () {
    makeAnnualWorkshopRegistration(['prc_number' => 900007]);
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'prcNumber' => '900007',
            'workshops' => ['RA', 'POCUS'],
        ]))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('workshops', ['RA', 'POCUS']);

    $registration = WorkshopRegistration::where('prc_number', '900007')->firstOrFail();

    expect($registration->workshops)->toBe(['RA', 'POCUS'])
        ->and(WorkshopRegistration::whereJsonContains('workshops', 'RA')->count())->toBe(1)
        ->and(WorkshopRegistration::whereJsonContains('workshops', 'POCUS')->count())->toBe(1);
});

it('continues the four-digit non-member PSA ID sequence', function () {
    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => 'NMW_0007',
        'prc_number' => '900002',
    ]));
    makeAnnualWorkshopRegistration(['prc_number' => 900003]);

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'prcNumber' => '900003',
            'workshops' => ['POCUS'],
        ]))
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('psaId', 'NMW_0008');
});

it('rejects an already registered PSA ID inside the registration lock', function () {
    $member = makeWorkshopMember(['member_id_no' => '0002']);

    $component = Livewire::test('workshop.workshop-registration')
        ->dispatch('psa-id-selected', psaId: $member->getKey())
        ->set(validWorkshopRegistrationData([
            'lastName' => 'Tester',
            'firstName' => 'Member',
            'workshops' => ['RA'],
        ]));

    WorkshopRegistration::create(workshopRegistrationRecordData([
        'psa_id' => $member->getKey(),
        'mem_type' => 'Regular Member',
        'prc_number' => '900004',
    ]));

    $component
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
    makeAnnualWorkshopRegistration(['prc_number' => 920001]);

    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData(['prcNumber' => '920001']))
        ->call('submit')
        ->assertHasErrors(['workshops' => 'Sorry, Regional Anesthesia Workshop (RA) is already full. Please choose another workshop.'])
        ->assertSet('workshops', [])
        ->assertSet('submitted', false);
});

it('keeps the 60-slot limit separate for each workshop', function () {
    foreach (range(1, 60) as $index) {
        WorkshopRegistration::create(workshopRegistrationRecordData([
            'prc_number' => '93' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
        ]));
    }

    makeAnnualWorkshopRegistration(['prc_number' => 940001]);
    Livewire::test('workshop.workshop-registration')
        ->set(validWorkshopRegistrationData([
            'prcNumber' => '940001',
            'workshops' => ['POCUS'],
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

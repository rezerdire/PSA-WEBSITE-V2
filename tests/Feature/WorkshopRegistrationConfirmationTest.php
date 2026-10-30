<?php

use App\Mail\WorkshopRegistrationConfirmation;
use App\Models\WorkshopRegistration;

it('renders all selected workshops in the confirmation email', function () {
    $registration = new WorkshopRegistration([
        'psa_id' => 'NMW_0001',
        'mem_first_name' => 'Workshop',
        'mem_middle_name' => 'M.',
        'prc_number' => '123456',
        'hospital' => 'Test Hospital',
        'email' => 'tester@example.test',
        'phone' => '09171234567',
        'workshops' => ['RA', 'POCUS'],
    ]);

    $mail = new WorkshopRegistrationConfirmation(
        $registration,
        [
            [
                'code' => 'RA',
                'label' => 'Regional Anesthesia Workshop (RA)',
                'session' => '9:00 AM – 12:00 Noon',
                'venue' => 'Executive 5',
            ],
            [
                'code' => 'POCUS',
                'label' => 'POCUS',
                'session' => '8:30 AM – 12:00 Noon',
                'venue' => 'Executive 5',
            ],
        ],
    );

    $rendered = $mail->render();

    expect($mail->envelope()->subject)->toBe('PSA Workshop Registration Confirmed')
        ->and($rendered)
        ->toContain('Workshop registration confirmed')
        ->toContain('Regional Anesthesia Workshop (RA)')
        ->toContain('POCUS')
        ->toContain('9:00 AM – 12:00 Noon')
        ->toContain('8:30 AM – 12:00 Noon')
        ->toContain('Executive 5')
        ->toContain('tester@example.test')
        ->not->toContain('PSA ID')
        ->not->toContain('NMW_0001');
});

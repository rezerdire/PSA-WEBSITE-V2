<?php

use App\Models\Registration;

test('the QR email uses the simple confirmation-style body', function (): void {
    $registration = new Registration([
        'psa_id' => 'PSA-0397',
        'last_name' => 'CAÑETE',
    ]);

    $html = view('emails.registration-qr', [
        'registration' => $registration,
        'hasIdCard' => true,
        'idCardUrl' => null,
    ])->render();

    expect($html)
        ->toContain('Your PSA ID with QR code')
        ->toContain('attached PNG ID card')
        ->toContain('PSA ID:</strong> PSA-0397')
        ->not->toContain('Convention ID Details')
        ->not->toContain('border-radius:12px');
});

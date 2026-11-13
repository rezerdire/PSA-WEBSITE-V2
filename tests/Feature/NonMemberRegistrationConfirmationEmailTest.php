<?php

use App\Models\Registration;

it('shows the assigned Convention ID in both non-member confirmation emails', function (): void {
    $registration = new Registration([
        'membership' => 'NM',
        'psa_id' => 'NM_0042',
        'last_name' => 'Guest',
    ]);

    foreach (['emails.registration-auto-reply', 'emails.registration-confirmed'] as $emailView) {
        $html = view($emailView, ['registration' => $registration])->render();

        expect($html)
            ->toContain('Your assigned')
            ->toContain('Convention ID')
            ->toContain('NM_0042')
            ->not->toContain('PSA ID with QR Code');
    }
});

it('keeps PSA ID instructions for member confirmations', function (): void {
    $registration = new Registration([
        'membership' => 'RM',
        'psa_id' => 'PSA-0397',
        'last_name' => 'Member',
    ]);

    $autoReply = view('emails.registration-auto-reply', ['registration' => $registration])->render();
    $confirmed = view('emails.registration-confirmed', ['registration' => $registration])->render();

    expect($autoReply)
        ->toContain('PSA ID with QC Code')
        ->not->toContain('Your assigned');

    expect($confirmed)
        ->toContain('PSA ID with QR Code')
        ->not->toContain('Your assigned');
});

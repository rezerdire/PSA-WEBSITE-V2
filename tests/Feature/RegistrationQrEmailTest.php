<?php

use App\Models\Registration;

test('the QR email includes the Annual Convention reminder bullets', function (): void {
    $registration = new Registration([
        'psa_id' => 'PSA-0397',
        'last_name' => 'CAÑETE',
    ]);

    $html = view('emails.registration-qr', [
        'registration' => $registration,
        'hasIdCard' => true,
        'idCardUrl' => null,
    ])->render();
    $introPosition = strpos($html, 'Thank you for joining us at the PSA 58th Annual Convention');
    $visitPosition = strpos($html, 'We would also like to remind you to complete the Visit-a-Booth');
    $reminderPosition = strpos($html, 'Important Reminder:');
    $closingPosition = strpos($html, "Thank you once again for being part of this year's convention");
    $signOffPosition = strpos($html, 'Best regards,');


    expect($html)
        ->toContain('PSA 58th Annual Convention and Postgraduate Course')
        ->toContain('Important Reminder:')
        ->toContain('color:#dc2626;font-weight:700;text-decoration:underline')
        ->toContain('PSA Annual Convention’s Evaluation Form')
        ->toContain('LOST CONVENTION ID: Php1,000.00')
        ->toContain('We would also like to remind you to complete the Visit-a-Booth to qualify for the PSA Raffle, which will take place during the Closing Ceremony.')
        ->toContain("Thank you once again for being part of this year's convention. We look forward to seeing you at the event activities!")
        ->toContain('Best regards,')
        ->toContain('“NO ID, NO ENTRY”')
        ->not->toContain('PSA Midyear Convention')
        ->not->toContain('Convention ID Details');
    expect($introPosition < $visitPosition)->toBeTrue()
        ->and($visitPosition)->toBeLessThan($reminderPosition)
        ->and($reminderPosition)->toBeLessThan($signOffPosition)
        ->and($closingPosition)->toBeLessThan($reminderPosition);
});

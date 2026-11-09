<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSA Annual Convention QR Code</title>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;font-family:'Helvetica Neue',Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 24px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;">
                    <tr>
                        <td style="font-size:16px;color:#111827;line-height:1.7;">
                            <p style="margin:0 0 18px 0;">Dear Dr. {{ $registration->last_name }},</p>

                            @if ($hasIdCard ?? false)
                                <p style="margin:0 0 18px 0;">
                                    Thank you for joining us at the PSA 58th Annual Convention and Postgraduate Course! We sincerely appreciate your participation and hope you will have a meaningful and enriching convention experience.
                                </p>

                                <p style="margin:0 0 18px 0;">
                                    Please see the PSA ID with QR Code attached, which you may use to claim your Convention ID. You may proceed to the Pre-registration Area on the 2nd Floor of the Marriott Grand Ballroom to claim your ID.
                                </p>

                                <p style="margin:0 0 18px 0;">
                                    We would also like to remind you to complete the Visit-a-Booth to qualify for the PSA Raffle, which will take place during the Closing Ceremony.
                                </p>

                                <p style="margin:0 0 18px 0;">
                                    Thank you once again for being part of this year's convention. We look forward to seeing you at the event activities!
                                </p>

                                <p style="margin:0 0 8px 0;color:#dc2626;font-weight:700;text-decoration:underline;">Important Reminder:</p>
                                <ul style="margin:0 0 18px 22px;padding:0;color:#111827;line-height:1.7;">
                                    <li style="margin:0 0 8px 0;"><strong>Barcoding</strong> will be done once a day for <strong>PRC-CPD units and attendance</strong> during the convention.</li>
                                    <li style="margin:0 0 8px 0;">The <strong>PSA Annual Convention’s Evaluation Form</strong> will be available on the website at <strong>1:00 PM on the 3rd day of the convention.</strong> The Evaluation Form is a requirement for the issuance of the Convention’s <strong>Certificate of Attendance.</strong></li>
                                    <li style="margin:0 0 8px 0;"><strong>DELEGATES</strong> are encouraged to visit all the <strong>PSA Pharmaceutical Partners</strong> to complete the <strong>“Visit-A-Booth” QR code registration</strong> to qualify in the <strong>PSA raffle.</strong> Exciting prizes will be given during the Closing ceremony.</li>
                                    <li style="margin:0 0 8px 0;"><strong>LOST CONVENTION ID: Php1,000.00</strong></li>
                                    <li>A <strong>“NO ID, NO ENTRY”</strong> policy will be implemented.</li>
                                </ul>

                            @else
                                <p style="margin:0 0 18px 0;">
                                    We were unable to attach your ID card. Please contact the PSA secretariat and
                                    reference your PSA ID <strong>{{ $registration->psa_id }}</strong>.
                                </p>
                            @endif

                           

                            <p style="margin:0 0 40px 0;">Best regards,</p>

                            <p style="margin:0;">
                                <strong>PSA Secretariat</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
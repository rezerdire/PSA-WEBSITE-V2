<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSA Convention QR Code</title>
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
                                    Thank you for joining us at the PSA 58th Annual Convention and Postgraduate Course! We sincerely appreciate your participation and hope you are having a meaningful and enriching convention experience.
                                    Please see the QR code below, which you may use to claim your Convention ID. You may proceed to the Pre-registration Area on the 2nd Floor of the Marriott Grand Ballroom to claim your ID.
                                    We would also like to remind you to complete the Visit-a-Booth to qualify for the PSA Raffle, which will take place during the Closing Ceremony.
                                    Thank you once again for being part of this year's convention. We look forward to seeing you at the event activities!                   
                                </p>

                                
                     

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
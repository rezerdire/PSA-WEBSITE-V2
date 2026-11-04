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

                            <p style="margin:0 0 18px 0;">Greetings!</p>

                            <p style="margin:0 0 18px 0;">
                                Your PSA QR ID for the
                                <strong>PSA 58th Annual Convention</strong> is attached to this email.
                            </p>

                            @if ($hasIdCard ?? false)
                                <p style="margin:0 0 18px 0;">
                                    IDK
                                </p>
                            @else
                                <p style="margin:0 0 18px 0;">
                                    We were unable to attach your ID card. Please contact the PSA secretariat and
                                    reference your PSA ID <strong>{{ $registration->psa_id }}</strong>.
                                </p>
                            @endif

                           

                            <p style="margin:0 0 40px 0;">Best regards,</p>

                            <p style="margin:0;">
                                <strong>PSA 58th Annual Convention Secretariat</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
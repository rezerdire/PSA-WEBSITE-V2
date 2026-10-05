<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your PSA Convention ID</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Helvetica Neue',Arial,sans-serif;">

    @php
        $fullName = $registration->first_name
            . ($registration->middle_name ? ' ' . $registration->middle_name : '')
            . ' ' . $registration->last_name;
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 16px;">
        <tr>
            <td align="center" style="text-align:center;">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 28px;">
                            <p style="margin:0;font-size:11px;color:#6b7280;letter-spacing:0.05em;text-transform:uppercase;font-weight:600;text-align:center;">
                                Philippine Society of Anesthesiologists
                            </p>
                        </td>
                    </tr>

                    {{-- Icon --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 20px;">
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td align="center" valign="middle"
                                        style="width:72px;height:72px;border-radius:50%;
                                            background-color:#2563eb;text-align:center;
                                            vertical-align:middle;font-size:34px;
                                            font-weight:700;color:#ffffff;line-height:1;">
                                        &#128274;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 6px;">
                            <h1 style="margin:0;font-size:22px;font-weight:800;color:#374151;text-align:center;">
                                Your PSA Convention ID is Ready
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 28px;">
                            <p style="margin:0 auto;font-size:13px;color:#9ca3af;max-width:400px;line-height:1.6;text-align:center;">
                                Hello <strong style="color:#374151;">{{ $registration->first_name }}</strong>,
                                your PSA Convention ID card with QR code for the
                                <strong style="color:#374151;">PSA 58th Annual Convention</strong>
                                is attached to this email.
                            </p>
                        </td>
                    </tr>

                    {{-- Summary Card --}}
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;background:#ffffff;">

                                <tr>
                                    <td colspan="2"
                                        style="background-color:#1d4ed8;padding:10px 20px;
                                               font-size:11px;font-weight:700;color:white;
                                               text-transform:uppercase;letter-spacing:0.08em;">
                                        Convention ID Details
                                    </td>
                                </tr>

                                @foreach ([['Full Name', $fullName], ['PSA ID', $registration->psa_id]] as $index => [$label, $value])
                                    <tr style="border-top: {{ $index === 0 ? 'none' : '1px solid #f9fafb' }};">
                                        <td style="padding:11px 20px;font-size:11px;color:#9ca3af;width:110px;vertical-align:top;">
                                            {{ $label }}
                                        </td>
                                        <td style="padding:11px 20px 11px 0;font-size:13px;font-weight:600;color:#374151;vertical-align:top;">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach

                            </table>
                        </td>
                    </tr>

                    {{-- Attachment notice / fallback --}}
                    @if ($hasIdCard ?? false)
                        <tr>
                            <td style="padding-bottom: 16px;">
                                <table width="100%" cellpadding="0" cellspacing="0"
                                       style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;">
                                    <tr>
                                        <td style="padding:14px 18px;font-size:12px;color:#1d4ed8;line-height:1.7;">
                                            &#128206; Your ID card is attached to this email as a downloadable image file (PNG).
                                            Please present the QR code when claiming your physical Convention ID onsite.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td style="padding-bottom: 16px;">
                                <table width="100%" cellpadding="0" cellspacing="0"
                                       style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;">
                                    <tr>
                                        <td style="padding:14px 18px;font-size:12px;color:#c2410c;line-height:1.7;">
                                            We were unable to attach your ID card to this email. Please contact the
                                            PSA secretariat and reference your PSA ID <strong>{{ $registration->psa_id }}</strong>.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-top: 12px;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
                                &copy; {{ date('Y') }} Philippine Society of Anesthesiologists. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
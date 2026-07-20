<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Helvetica Neue',Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Logo / Header --}}
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <p style="margin:0;font-size:13px;color:#6b7280;letter-spacing:0.05em;text-transform:uppercase;font-weight:600;">
                                Philippine Society of Anesthesiologists
                            </p>
                        </td>
                    </tr>
            {{-- Green Check Icon --}}
            <tr>
                <td align="center" style="padding-bottom: 20px;">
                    <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                        <tr>
                            <td align="center" valign="middle"
                                style="width:72px;height:72px;border-radius:50%;
                                    background-color:#2e7d32;text-align:center;
                                    vertical-align:middle;font-size:38px;
                                    font-weight:700;color:#ffffff;line-height:1;">
                                &#10003;
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
                                {{-- Title --}}
                    <tr align ="center">
                        <td align="center" style="padding-bottom: 6px;">
                            <h1 style="margin:0;font-size:22px;font-weight:800;color:#374151;">
                                Registration Submitted!
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <p style="margin:0;font-size:13px;color:#9ca3af;max-width:400px;line-height:1.6;">
                                Your registration for <strong style="color:#374151;">PSA Midyear Convention 2026</strong>
                                has been received and is currently pending review.
                            </p>
                        </td>
                    </tr>

                    {{-- Summary Card --}}
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;background:#ffffff;">

                                {{-- Card Header --}}
                                <tr>
                                    <td colspan="2"
                                        style="background-color:#1d4ed8;padding:10px 20px;
                                               font-size:11px;font-weight:700;color:white;
                                               text-transform:uppercase;letter-spacing:0.08em;">
                                        Registration Summary
                                    </td>
                                </tr>

                                @php
                                    $memMap = ['RM' => 'Regular Member', 'LM' => 'Life Member', 'TM' => 'Trainee Member'];
                                    $fullName = $registration->first_name
                                        . ($registration->middle_name ? ' ' . $registration->middle_name : '')
                                        . ' ' . $registration->last_name;
                                    $rows = [
                                        // ['Reference No.', '#' . str_pad($registration->id, 6, '0', STR_PAD_LEFT)],
                                        ['Full Name',     $fullName],
                                        ['PSA ID',        $registration->psa_id],
                                        ['Membership',    $memMap[$registration->membership] ?? $registration->membership],
                                        ['Email',         $registration->email],
                                        ['Contact No.',   $registration->contact_number],
                                        ['Hospital',      $registration->hospital_name],
                                        ['Status',        'Pending Review'],
                                    ];
                                @endphp

                                @foreach ($rows as $index => [$label, $value])
                                    <tr style="border-top: {{ $index === 0 ? 'none' : '1px solid #f9fafb' }};">
                                        <td style="padding:11px 20px;font-size:11px;color:#9ca3af;width:110px;vertical-align:top;">
                                            {{ $label }}
                                        </td>
                                        <td style="padding:11px 20px 11px 0;font-size:13px;font-weight:600;
                                                   color:{{ $label === 'Status' ? '#d97706' : '#374151' }};
                                                   vertical-align:top;">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach

                            </table>
                        </td>
                    </tr>

                    {{-- Info Box --}}
                    <tr>
                        <td style="padding-bottom: 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 18px;font-size:12px;color:#1d4ed8;line-height:1.7;">
                                        {{-- Please take note of your <strong>Reference No.</strong> above. --}}
                                        The PSA secretariat will review your submission and update your registration status.
                                        You may follow up using your PSA ID <strong>{{ $registration->psa_id }}</strong>.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
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
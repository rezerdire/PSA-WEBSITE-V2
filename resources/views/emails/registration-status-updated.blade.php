<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration {{ $registration->status }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Helvetica Neue',Arial,sans-serif;">

    @php
        $isApproved = $registration->status === \App\Models\Registration::STATUS_APPROVED;
        $iconColor  = $isApproved ? '#2e7d32' : '#dc2626';
        $icon       = $isApproved ? '&#10003;' : '&#10005;';
        $title      = $isApproved ? 'Registration Approved!' : 'Registration Update';
        $headerBg   = $isApproved ? '#15803d' : '#b91c1c';
        $boxBg      = $isApproved ? '#f0fdf4' : '#fef2f2';
        $boxBorder  = $isApproved ? '#bbf7d0' : '#fecaca';
        $boxText    = $isApproved ? '#15803d' : '#b91c1c';

        $memMap = ['RM' => 'Regular Member', 'LM' => 'Life Member', 'TM' => 'Trainee Member'];
        $fullName = $registration->first_name
            . ($registration->middle_name ? ' ' . $registration->middle_name : '')
            . ' ' . $registration->last_name;
        $rows = [
            ['Reference No.', '#' . str_pad($registration->id, 6, '0', STR_PAD_LEFT)],
            ['Full Name',     $fullName],
            ['PSA ID',        $registration->psa_id],
            ['Membership',    $memMap[$registration->membership] ?? $registration->membership],
            ['Email',         $registration->email],
            ['Contact No.',   $registration->contact_number],
            ['Hospital',      $registration->hospital_name],
            ['Status',        $registration->status],
        ];

        // Consistent type scale used throughout this email:
        // 11px = eyebrow/labels, 12px = small body / info box, 13px = body / table values,
        // 14px = card sub-heading, 22px = main heading
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 16px;">
        <tr>
            <td align="center" style="text-align:center;">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Logo / Header --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 28px;">
                            <p style="margin:0;font-size:11px;color:#6b7280;letter-spacing:0.05em;text-transform:uppercase;font-weight:600;text-align:center;">
                                Philippine Society of Anesthesiologists
                            </p>
                        </td>
                    </tr>

                    {{-- Status Icon --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 20px;">
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td align="center" valign="middle"
                                        style="width:72px;height:72px;border-radius:50%;
                                            background-color:{{ $iconColor }};text-align:center;
                                            vertical-align:middle;font-size:38px;
                                            font-weight:700;color:#ffffff;line-height:1;">
                                        {!! $icon !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 6px;">
                            <h1 style="margin:0;font-size:22px;font-weight:800;color:#374151;text-align:center;">
                                {{ $title }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="text-align:center;padding-bottom: 28px;">
                            <p style="margin:0 auto;font-size:13px;color:#9ca3af;max-width:400px;line-height:1.6;text-align:center;">
                                @if ($isApproved)
                                    Great news, {{ $registration->first_name }}! Your registration for
                                    <strong style="color:#374151;">PSA Midyear Convention 2026</strong>
                                    has been approved. We look forward to seeing you at the convention.
                                @else
                                    Hello {{ $registration->first_name }}, we're sorry to inform you that your
                                    registration for <strong style="color:#374151;">PSA Midyear Convention 2026</strong>
                                    could not be approved at this time.
                                @endif
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
                                        style="background-color:{{ $headerBg }};padding:10px 20px;
                                               font-size:11px;font-weight:700;color:white;
                                               text-transform:uppercase;letter-spacing:0.08em;">
                                        Registration Summary
                                    </td>
                                </tr>

                                @foreach ($rows as $index => [$label, $value])
                                    <tr style="border-top: {{ $index === 0 ? 'none' : '1px solid #f9fafb' }};">
                                        <td style="padding:11px 20px;font-size:11px;color:#9ca3af;width:110px;vertical-align:top;">
                                            {{ $label }}
                                        </td>
                                        <td style="padding:11px 20px 11px 0;font-size:13px;font-weight:600;
                                                   color:{{ $label === 'Status' ? $boxText : '#374151' }};
                                                   vertical-align:top;">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach

                            </table>
                        </td>
                    </tr>

                    {{-- Rejection Title + Reason Card (only shown if admin wrote a custom message) --}}
                    @if (!$isApproved && ($registration->rejection_title || $registration->rejection_reason))
                        <tr>
                            <td style="padding-bottom: 16px;">
                                <table width="100%" cellpadding="0" cellspacing="0"
                                       style="border:1px solid {{ $boxBorder }};border-radius:16px;overflow:hidden;background:#ffffff;">

                                    @if ($registration->rejection_title)
                                        <tr>
                                            <td style="background-color:{{ $boxBg }};padding:12px 20px;border-bottom:1px solid {{ $boxBorder }};">
                                                <p style="margin:0;font-size:13px;font-weight:700;color:{{ $boxText }};">
                                                    {{ $registration->rejection_title }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($registration->rejection_reason)
                                        <tr>
                                            <td style="padding:16px 20px;font-size:13px;color:#374151;line-height:1.7;">
                                                {!! $registration->rejection_reason !!}
                                            </td>
                                        </tr>
                                    @endif

                                </table>
                            </td>
                        </tr>
                    @endif

                    {{-- Info Box --}}
                    <tr>
                        <td style="padding-bottom: 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:{{ $boxBg }};border:1px solid {{ $boxBorder }};border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 18px;font-size:12px;color:{{ $boxText }};line-height:1.7;">
                                        @if ($isApproved)
                                            Please keep your <strong>Reference No.</strong> above for your records.
                                            We'll see you at the convention venue — further details will be shared
                                            closer to the event date.
                                        @else
                                            You're welcome to update your details and resubmit your registration
                                            using the same PSA ID. If you have questions about this decision,
                                            please reach out to the PSA secretariat and reference your PSA ID
                                            <strong>{{ $registration->psa_id }}</strong>.
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="text-align:center;">
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
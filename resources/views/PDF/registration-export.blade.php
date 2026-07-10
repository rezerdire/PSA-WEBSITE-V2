<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PSA Registrations Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 16mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #ffffff;
            margin: 50px;
        }


        .page {
            width: 100%;
        }

        .page-break {
            page-break-after: always;
        }

        .event-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 0.2px;
        }

        .event-sub {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        .header-divider {
            border-top: 2px solid #1e3a5f;
            margin-top: 14px;
            margin-bottom: 14px;
        }

        .ref-block {
            text-align: center;
        }

        .ref-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            display: block;
            margin-bottom: 3px;
        }

        .ref-value {
            font-size: 17px;
            font-weight: bold;
            color: #1e3a5f;
        }

        .ref-divider {
            border-top: 2px solid #1e3a5f;
            margin-top: 14px;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1e3a5f;
            border-bottom: 1.5px solid #1e3a5f;
            padding-bottom: 4px;
            margin-bottom: 12px;
            margin-top: 18px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 7px 10px;
            vertical-align: top;
            font-size: 11px;
        }

        .info-grid .label {
            color: #6b7280;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            width: 30%;
            white-space: nowrap;
        }

        .info-grid .value {
            color: #111827;
            font-size: 11.5px;
            width: 70%;
        }

        .info-grid tr:nth-child(odd) td {
            background-color: #f8fafc;
        }

        .info-grid tr:nth-child(even) td {
            background-color: #ffffff;
        }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge-pending {
            background: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde68a;
        }

        .badge-rm {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .badge-lm {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-tm {
            background: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde68a;
        }

        .psa-id-mono {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 0.5px;
        }

        .payment-container {
            margin-top: 6px;
            text-align: center;
            padding: 10px 0;
        }

        .payment-container img {
            width: 280px;
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .payment-no-image {
            color: #9ca3af;
            font-size: 11px;
            padding: 50px 0;
            font-style: italic;
        }

        .page-footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            display: table;
            width: 100%;
            color: #9ca3af;
            font-size: 8.5px;
        }

        .page-footer .footer-left {
            display: table-cell;
            text-align: left;
        }

        .page-footer .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

@foreach ($registrations as $index => $reg)
<div class="page{{ !$loop->last ? ' page-break' : '' }}">

    <div class="event-name">PSA Midyear Convention 2026</div>
    <div class="event-sub">Philippine Society of Anesthesiologists &nbsp;·&nbsp; Registration Record</div>

    <div class="header-divider"></div>

    <div class="ref-block">
        <span class="ref-label">Reference No.</span>
        <span class="ref-value">#{{ str_pad($reg->id, 6, '0', STR_PAD_LEFT) }}</span>
    </div>

    <div class="ref-divider"></div>

    <div class="section-title">Registrant Information</div>

    <table class="info-grid">
        <tr>
            <td class="label">PSA ID</td>
            <td class="value">
                <span class="psa-id-mono">{{ $reg->psa_id ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">Full Name</td>
            <td class="value" style="font-weight:bold; font-size:13px;">{{ $reg->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Membership Type</td>
            <td class="value">
                @php
                    $memMap   = ['RM' => 'Regular Member', 'LM' => 'Life Member', 'TM' => 'Trainee Member'];
                    $badgeMap = ['RM' => 'rm', 'LM' => 'lm', 'TM' => 'tm'];
                    $memLabel = $memMap[$reg->membership] ?? $reg->membership;
                    $memClass = 'badge-' . strtolower($badgeMap[$reg->membership] ?? 'rm');
                @endphp
                <span class="badge {{ $memClass }}">{{ $memLabel }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">Email Address</td>
            <td class="value">{{ $reg->email ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Contact Number</td>
            <td class="value">{{ $reg->contact_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Registration Date</td>
            <td class="value" style="font-family: 'Courier New', monospace; font-size: 11px;">
                {{ $reg->created_at ? $reg->created_at->format('Y/m/d  H:i:s') : '—' }}
            </td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">
                @php
                    $statusClass = match(strtolower($reg->status ?? '')) {
                        'approved' => 'badge-approved',
                        'rejected' => 'badge-rejected',
                        default    => 'badge-pending',
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ ucfirst($reg->status ?? 'Pending') }}</span>
            </td>
        </tr>
    </table>

    @if ($reg->status === 'Rejected' && ($reg->rejection_title || $reg->rejection_reason))
        <div class="section-title" style="color:#991b1b; border-color:#991b1b;">Rejection Details</div>
        <table class="info-grid">
            @if ($reg->rejection_title)
            <tr>
                <td class="label">Title</td>
                <td class="value" style="font-weight:bold;">{{ $reg->rejection_title }}</td>
            </tr>
            @endif
            @if ($reg->rejection_reason)
            <tr>
                <td class="label">Reason</td>
                <td class="value">{!! strip_tags($reg->rejection_reason) !!}</td>
            </tr>
            @endif
        </table>
    @endif

    <div class="section-title">Proof of Payment</div>

    <div class="payment-container">
        @if ($reg->proof_payment_base64)
            <img src="{{ $reg->proof_payment_base64 }}" alt="Proof of Payment">
        @else
            <div class="payment-no-image">No proof of payment uploaded.</div>
        @endif
    </div>

    <div class="page-footer">
        <span class="footer-left">PSA Midyear Convention 2026 &nbsp;·&nbsp; Registration Records Export</span>
        <span class="footer-right">Generated: {{ $generatedAt }} &nbsp;·&nbsp; Page {{ $index + 1 }} of {{ $registrations->count() }}</span>
    </div>

</div>
@endforeach

</body>
</html>
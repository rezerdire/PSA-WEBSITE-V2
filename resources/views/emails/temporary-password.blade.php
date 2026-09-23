<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your PSA Account Temporary Password</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:520px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#1d4ed8; padding:28px 32px;">
                            <p style="margin:0; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#bfdbfe;">
                                PSA Member Account
                            </p>
                            <h1 style="margin:8px 0 0; font-size:20px; color:#ffffff;">
                                Account Activated
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#334155; line-height:1.6;">
                                Hi {{ $firstName }} {{ $lastName }},
                            </p>

                            <p style="margin:0 0 20px; font-size:14px; color:#334155; line-height:1.6;">
                                Your PSA online account (Member ID <strong>{{ $psaId }}</strong>) has been
                                activated. Use the temporary password below to sign in. You will be asked
                                to set a new password on your first login.
                            </p>

                            <div style="margin:0 0 24px; padding:16px 20px; background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; text-align:center;">
                                 <p style="margin:0 0 6px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#1d4ed8;">
                                    PSA ID
                                </p>
                                <p style="margin:0; font-family: 'Courier New', monospace; font-size:20px; font-weight:700; color:#0f172a; letter-spacing:0.05em;">
                                    {{ $psaId }}
                                </p>
                                
                                <p style="margin:0 0 6px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#1d4ed8;">
                                    Temporary Password
                                </p>
                                <p style="margin:0; font-family: 'Courier New', monospace; font-size:20px; font-weight:700; color:#0f172a; letter-spacing:0.05em;">
                                    {{ $temporaryPassword }}
                                </p>
                            </div>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td style="border-radius:10px; background-color:#1d4ed8;">
                                        <a href="{{ $loginUrl }}"
                                           style="display:inline-block; padding:12px 28px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">
                                            Sign In Now
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:12px; color:#94a3b8; line-height:1.6;">
                                For your security, please change this password immediately after logging in.
                                If you did not request this account activation, please contact the PSA
                                Secretariat right away at
                                <a href="mailto:psainc_sec@yahoo.com" style="color:#1d4ed8;">psainc_sec@yahoo.com</a>.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px 32px; background-color:#f8fafc; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:11px; color:#94a3b8; text-align:center;">
                                This is an automated message. Please do not reply directly to this email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@php
    $bg = '#F5F7FB';
    $card = '#FFFFFF';
    $text = '#0F172A';
    $muted = '#475569';
    $line = '#E2E8F0';
    $accent = '#10b981';

    $logo = asset('images/nri-logo.png');

    if (isset($message) && isset($logoPath) && file_exists($logoPath)) {
        $logo = $message->embed($logoPath);
    }
@endphp

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset</title>
</head>

<body style="margin:0; padding:0; background: {{ $bg }}; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding:30px 12px;">
        <tr>
            <td align="center">

                <table width="640" cellpadding="0" cellspacing="0" border="0"
                    style="background:{{ $card }}; border-radius:16px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="padding:24px 28px; border-bottom:1px solid {{ $line }};">
                            <img src="{{ $logo }}" alt="Company Logo" style="height:36px; display:block;">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px 28px;">
                            <h1 style="margin:0 0 14px; font-size:22px; color:{{ $text }};">
                                Reset Your Password
                            </h1>

                            <p style="margin:0 0 18px; color:{{ $muted }}; font-size:14px; line-height:1.6;">
                                Hello {{ $user->name ?? 'there' }},
                            </p>

                            <p style="margin:0 0 22px; color:{{ $muted }}; font-size:14px; line-height:1.6;">
                                We received a request to reset your password.
                                Click the button below to set a new password.
                            </p>

                            <!-- Button -->
                            <p style="margin:0 0 24px;">
                                <a href="{{ $resetUrl }}"
                                    style="display:inline-block;
                                      background:{{ $accent }};
                                      color:#ffffff;
                                      text-decoration:none;
                                      padding:12px 18px;
                                      border-radius:8px;
                                      font-weight:600;
                                      font-size:14px;">
                                    Reset Password
                                </a>
                            </p>

                            <p style="margin:0; color:{{ $muted }}; font-size:12px; line-height:1.6;">
                                This link will expire shortly for security reasons.
                                If you did not request a password reset, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding:18px 28px; border-top:1px solid {{ $line }};
                               color:{{ $muted }}; font-size:12px;">
                            © {{ date('Y') }} Nigeria Risk Intelligence. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>

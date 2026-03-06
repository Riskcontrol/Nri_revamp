@php
    $bg = '#F5F7FB';
    $card = '#FFFFFF';
    $text = '#0F172A';
    $muted = '#475569';
    $muted2 = '#64748B';
    $line = '#E2E8F0';
    $dark = '#0a1628';
    $accent = '#1D4ED8'; // blue-700 — matches the welcome email CTA

    // Logo: embedded when actually sending, falls back to asset URL for preview
    $logo = asset('images/nri-logo.png');
    if (isset($message) && !empty($logoPath) && file_exists($logoPath)) {
        $logo = $message->embed($logoPath);
    }

    $confirmUrl = route('newsletter.confirm', ['token' => $subscriber->token]);
    $unsubscribeUrl = route('newsletter.unsubscribe', ['token' => $subscriber->token]);
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm your subscription</title>
</head>

<body style="margin:0; padding:0; background:{{ $bg }}; font-family: Arial, Helvetica, sans-serif;">

    {{-- Preheader (hidden preview text in inbox) --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        One click to confirm your Nigeria Risk Index subscription.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background:{{ $bg }}; padding:28px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
                    style="width:640px; max-width:640px;">

                    {{-- Logo above card --}}
                    <tr>
                        <td align="left" style="padding:0 0 16px 0;">
                            <img src="{{ $logo }}" alt="Nigeria Risk Index" width="160"
                                style="display:block; height:auto; border:0;">
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td
                            style="background:{{ $card }}; border:1px solid {{ $line }}; border-radius:0; overflow:hidden;">

                            {{-- Dark header bar --}}
                            <div style="background:{{ $dark }}; padding:16px 24px;">
                                <p style="margin:0; font-size:13px; color:#ffffff; letter-spacing:0.2px;">
                                    Subscription Confirmation
                                </p>
                            </div>

                            {{-- Body --}}
                            <div style="padding:28px 24px;">

                                <h1
                                    style="margin:0 0 16px 0; font-size:22px; line-height:1.3; color:{{ $text }}; font-weight:700;">
                                    Confirm your subscription
                                </h1>

                                <p
                                    style="margin:0 0 12px 0; font-size:14px; line-height:1.7; color:{{ $muted }};">
                                    Hi there,
                                </p>

                                <p
                                    style="margin:0 0 14px 0; font-size:14px; line-height:1.7; color:{{ $muted }};">
                                    Thanks for signing up for <strong style="color:{{ $text }};">Nigeria Risk
                                        Index</strong>
                                    alerts. Click the button below to confirm your email address and start
                                    receiving data-driven security intelligence.
                                </p>

                                <p
                                    style="margin:0 0 24px 0; font-size:14px; line-height:1.7; color:{{ $muted }};">
                                    Once confirmed, you'll get:
                                </p>

                                {{-- Feature list --}}
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                    style="margin:0 0 28px 0;">
                                    @php
                                        $features = [
                                            ['icon' => '📰', 'text' => 'Weekly security briefings'],
                                            ['icon' => '📍', 'text' => 'Real-time risk assessments across Nigeria'],
                                            ['icon' => '📈', 'text' => 'Trend analysis & emerging threat forecasts'],
                                        ];
                                    @endphp
                                    @foreach ($features as $f)
                                        <tr>
                                            <td style="padding:6px 0; vertical-align:top;">
                                                <table role="presentation" cellspacing="0" cellpadding="0"
                                                    border="0">
                                                    <tr>
                                                        <td style="width:34px; vertical-align:top; padding-top:1px;">
                                                            <div
                                                                style="width:28px; height:28px; border-radius:8px;
                                                                        background:#EFF6FF; text-align:center;
                                                                        line-height:28px; font-size:14px;">
                                                                {{ $f['icon'] }}
                                                            </div>
                                                        </td>
                                                        <td style="padding-left:10px; vertical-align:middle;">
                                                            <p
                                                                style="margin:0; font-size:14px; line-height:1.6; color:{{ $text }}; font-weight:600;">
                                                                {{ $f['text'] }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>

                                {{-- CTA button --}}
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    style="margin:0 0 24px 0;">
                                    <tr>
                                        <td>
                                            <a href="{{ $confirmUrl }}"
                                                style="display:inline-block;
                                                       background:{{ $accent }};
                                                       color:#ffffff;
                                                       text-decoration:none;
                                                       font-weight:700;
                                                       font-size:14px;
                                                       padding:13px 24px;
                                                       border-radius:10px;
                                                       font-family:Arial, sans-serif;
                                                       letter-spacing:0.02em;">
                                                Confirm Subscription
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Fallback link --}}
                                <p
                                    style="margin:0 0 20px 0; font-size:12px; line-height:1.6; color:{{ $muted2 }};">
                                    Button not working? Copy and paste this link into your browser:<br>
                                    <a href="{{ $confirmUrl }}"
                                        style="color:{{ $accent }}; word-break:break-all;">{{ $confirmUrl }}</a>
                                </p>

                                {{-- Divider --}}
                                <div style="height:1px; background:{{ $line }}; margin:20px 0;"></div>

                                <p style="margin:0; font-size:12px; line-height:1.6; color:{{ $muted2 }};">
                                    If you didn't sign up for Nigeria Risk Index alerts, you can safely ignore
                                    this email — you won't receive anything further.
                                </p>

                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="padding:16px 6px 0 6px; font-size:12px; color:{{ $muted2 }};
                                   text-align:center; line-height:1.6; font-family:Arial, sans-serif;">
                            <p style="margin:0 0 6px 0;">
                                <span style="color:#94A3B8;">
                                    © {{ date('Y') }} Risk Control Services Nigeria Ltd. All Rights Reserved.
                                </span>
                            </p>
                            <p style="margin:0;">
                                <a href="{{ $unsubscribeUrl }}"
                                    style="color:#94A3B8; text-decoration:underline;">Unsubscribe</a>
                                &nbsp;·&nbsp;
                                <a href="{{ config('app.url') }}"
                                    style="color:#94A3B8; text-decoration:underline;">Nigeria Risk Index</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>

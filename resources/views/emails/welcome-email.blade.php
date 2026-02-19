@php
    $bg = '#F5F7FB';
    $card = '#FFFFFF';
    $text = '#0F172A';
    $muted = '#475569';
    $muted2 = '#64748B';
    $line = '#E2E8F0';

    $iconBg = '#E6FFFB';
    $iconFg = '#0F766E';

    $firstName = $data['first_name'] ?? ($data['name'] ?? 'there');
    $ctaUrl = $data['cta_url'] ?? config('app.url');

    $logo = asset('images/nri-logo.png');
    if (isset($message) && !empty($logoPath) && file_exists($logoPath)) {
        $logo = $message->embed($logoPath);
    }
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
</head>

<body style="margin:0; padding:0; background: {{ $bg }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Welcome to Nigeria Risk Intelligence — here’s what you can expect.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background: {{ $bg }}; padding: 28px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
                    style="width:640px; max-width:640px;">

                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding: 6px 0 14px 0;">
                            <img src="{{ $logo }}" alt="Nigeria Risk Index" width="180"
                                style="display:block; height:auto; border:0;">
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td
                            style="background: {{ $card }}; border: 1px solid {{ $line }};
                   border-radius: 14px; overflow:hidden;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding: 26px; font-family: Arial, sans-serif;">

                                        {{-- Headline --}}
                                        <h1
                                            style="margin:0 0 14px 0; font-size: 26px; line-height: 1.25;
                                   color: {{ $text }}; font-weight: 700; text-align:center;">
                                            Welcome to Nigeria’s<br>
                                            Premier Security Intelligence
                                        </h1>

                                        {{-- Greeting --}}
                                        <p
                                            style="margin: 12px 0 10px 0; font-size: 15px;
                                  line-height: 1.7; color: {{ $muted }};">
                                            Hi <strong style="color: {{ $text }};">{{ $firstName }}</strong>,
                                        </p>

                                        <p
                                            style="margin: 0 0 12px 0; font-size: 15px;
                                  line-height: 1.7; color: {{ $muted }};">
                                            Welcome to the Nigeria Risk Intelligence community!
                                        </p>

                                        <p
                                            style="margin: 0 0 16px 0; font-size: 15px;
                                  line-height: 1.7; color: {{ $muted }};">
                                            You’ve just joined <strong
                                                style="color: {{ $text }};">5,000+</strong>
                                            security professionals who rely on our data-driven insights
                                            to make informed decisions about Nigeria’s security landscape.
                                        </p>

                                        {{-- What to Expect --}}
                                        <p
                                            style="margin: 0 0 12px 0; font-size: 15px;
                                  line-height: 1.7; color: {{ $text }};
                                  font-weight: 600;">
                                            Here’s what you can expect:
                                        </p>

                                        @php
                                            $features = [
                                                ['icon' => '📰', 'text' => 'Weekly Security Briefings'],
                                                ['icon' => '📍', 'text' => 'Real-time Risk Assessments'],
                                                ['icon' => '🗺️', 'text' => 'Interactive Security Maps'],
                                                ['icon' => '📈', 'text' => 'Trend Analysis & Forecasts'],
                                            ];
                                        @endphp

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                            border="0" style="margin: 0 0 18px 0;">
                                            @foreach ($features as $f)
                                                <tr>
                                                    <td style="padding: 7px 0; vertical-align: top;">
                                                        <table role="presentation" cellspacing="0" cellpadding="0"
                                                            border="0">
                                                            <tr>
                                                                <td style="width: 34px;">
                                                                    <div
                                                                        style="width: 28px; height: 28px;
                                                        border-radius: 8px;
                                                        background: {{ $iconBg }};
                                                        text-align:center;
                                                        line-height: 28px;
                                                        font-size: 14px;
                                                        color: {{ $iconFg }};">
                                                                        {{ $f['icon'] }}
                                                                    </div>
                                                                </td>
                                                                <td style="padding-left: 10px;">
                                                                    <p
                                                                        style="margin:0; font-size: 15px;
                                                              line-height: 1.6;
                                                              color: {{ $text }};
                                                              font-weight: 600;">
                                                                        {{ $f['text'] }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>

                                        {{-- Divider --}}
                                        <div style="height:1px; background: {{ $line }}; margin: 20px 0;">
                                        </div>

                                        {{-- Static Second Section --}}
                                        <p
                                            style="margin: 0 0 14px 0; font-size: 15px;
                                  line-height: 1.7; color: {{ $muted }};">
                                            You've just joined numerous security conscious professionals,
                                            researchers, and business leaders who rely on our data-driven
                                            intelligence to stay ahead of emerging threats.
                                        </p>

                                        <p
                                            style="margin: 0 0 6px 0; font-size: 15px;
                                  color: {{ $text }}; font-weight: 600;">
                                            Your First Exclusive Resource
                                        </p>

                                        <p
                                            style="margin: 0 0 10px 0; font-size: 16px;
                                  color: {{ $text }}; font-weight: 600;">
                                            2025 Annual Security Report
                                        </p>

                                        <ul
                                            style="margin: 0 0 18px 18px; padding: 0;
                                   color: {{ $muted }};
                                   font-size: 14px; line-height: 1.7;">
                                            <li style="margin: 6px 0;">
                                                Violent Threats now dominate
                                                <strong style="color: {{ $text }};">52.8%</strong>
                                                of all incidents (up from 30.6% in 2024)
                                            </li>
                                            <li style="margin: 6px 0;">
                                                Kidnapping epidemic:
                                                <strong style="color: {{ $text }};">734</strong>
                                                cases in 2025 (+22.3% from 600 in 2024)
                                            </li>
                                            <li style="margin: 6px 0;">
                                                Terrorism emerged as a distinct category with
                                                <strong style="color: {{ $text }};">317</strong>
                                                documented incidents
                                            </li>
                                            <li style="margin: 6px 0;">
                                                Geographic concentration intensified:
                                                Top 10 states account for
                                                <strong style="color: {{ $text }};">56.3%</strong>
                                                of national incidents
                                            </li>
                                        </ul>

                                        {{-- CTA --}}
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td>
                                                    <a href="{{ $ctaUrl }}"
                                                        style="display:inline-block;
                                              background:#1D4ED8;
                                              color:#ffffff;
                                              text-decoration:none;
                                              font-weight: 500;
                                              font-size: 13px;
                                              letter-spacing: .06em;
                                              padding: 12px 16px;
                                              border-radius: 10px;
                                              font-family: Arial, sans-serif;
                                              text-transform: uppercase;">
                                                        Explore the Hub
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="padding: 16px 6px 0 6px;
                   font-family: Arial, sans-serif;
                   color: {{ $muted2 }};
                   font-size: 12px;
                   text-align: center;">
                            <p style="margin:0;">
                                <span style="color:#94A3B8;">
                                    © {{ date('Y') }} Risk Control Services Nigeria Ltd.
                                    All Rights Reserved.
                                </span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>

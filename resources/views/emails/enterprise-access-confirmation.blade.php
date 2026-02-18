@php
    $primary = '#1e2d3d';
    $dark = '#0a1628';
    $accent = '#10b981';
    $bg = '#f8fafc';
    $muted = '#64748b'; // slate-500-ish
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Premium Access Request</title>
</head>

<body style="margin:0; padding:0; background: {{ $bg }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        We received your Premium Access request — our team will reach out shortly.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background: {{ $bg }}; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
                    style="width:640px; max-width:640px;">
                    {{-- Header --}}
                    <tr>
                        <td style="padding: 0 0 14px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="left" style="padding: 0;">
                                        @php
                                            $logo = asset('images/nri-logo.png'); // preview fallback
                                            if (isset($message) && !empty($logoPath) && file_exists($logoPath)) {
                                                $logo = $message->embed($logoPath); // ✅ embed only when sending
                                            }
                                        @endphp

                                        <img src="{{ $logo }}" alt="Nigeria Risk Index" width="160"
                                            style="display:block; height:auto;">


                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e2e8f0;  overflow:hidden;">
                            {{-- Top bar --}}
                            <div style="background: {{ $primary }}; padding: 16px 20px;">
                                <p
                                    style="margin:0; font-family: Arial, sans-serif; font-size: 14px; color:#ffffff; letter-spacing: .2px;">
                                    Premium Access Request
                                </p>
                            </div>

                            <div style="padding: 22px 20px; font-family: Arial, sans-serif;">
                                <h1
                                    style="margin:0 0 10px 0; font-size: 20px; line-height: 1.3; color: {{ $dark }};">
                                    Hi {{ $data['contact_name'] }},
                                </h1>

                                <p
                                    style="margin: 0 0 14px 0; font-size: 14px; line-height: 1.6; color: {{ $muted }};">
                                    Thanks for requesting Premium Access. We’ve received your details and our team will
                                    reach out shortly.
                                </p>

                                {{-- Summary box --}}
                                <div
                                    style="background: #f1f5f9; border:1px solid #e2e8f0; padding: 14px 14px; margin: 14px 0 18px 0;">
                                    <p
                                        style="margin:0 0 10px 0; font-size: 13px; font-weight: 700; color: {{ $dark }};">
                                        Summary
                                    </p>

                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                        border="0" style="font-size: 13px; color: {{ $dark }};">
                                        <tr>
                                            <td style="padding: 6px 0; width: 160px; color: {{ $muted }};">
                                                Organization</td>
                                            <td style="padding: 6px 0; font-weight: 600;">
                                                {{ $data['organization_name'] }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 6px 0; width: 160px; color: {{ $muted }};">Use
                                                case</td>
                                            <td style="padding: 6px 0; font-weight: 600;">
                                                {{ $data['primary_use_case'] }}
                                                @if (($data['primary_use_case'] ?? '') === 'Other' && !empty($data['primary_use_case_other']))
                                                    — {{ $data['primary_use_case_other'] }}
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 6px 0; width: 160px; color: {{ $muted }};">
                                                Preferred contact</td>
                                            <td style="padding: 6px 0; font-weight: 600;">
                                                {{ $data['preferred_contact_method'] }}</td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- CTA button (optional) --}}
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                    style="margin: 0 0 16px 0;">
                                    <tr>
                                        <td>
                                            <a href="{{ config('app.url') }}"
                                                style="display:inline-block; background: {{ $accent }}; color:#ffffff; text-decoration:none; font-weight:700; font-size:14px; padding: 12px 16px; border-radius: 10px; font-family: Arial, sans-serif;">
                                                Visit Nigeria Risk Index
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 0; font-size: 12px; line-height: 1.6; color: {{ $muted }};">
                                    If you didn’t request Enterprise Access, you can ignore this email.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="padding: 14px 6px 0 6px; font-family: Arial, sans-serif; color: {{ $muted }}; font-size: 12px; line-height: 1.6; align-items: center; text-align: center;">
                            <p style="margin:0;">
                                <span style="color:#94a3b8;">© 2026 Risk Control Services Nigeria Ltd. All Rights
                                    Reserved.</span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>

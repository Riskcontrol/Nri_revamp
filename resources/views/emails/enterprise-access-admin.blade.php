@php
    $primary = '#1e2d3d';
    $dark = '#0a1628';
    $accent = '#10b981';
    $bg = '#f8fafc';
    $muted = '#64748b';

    $logo = asset('images/nri-logo.png');
    if (isset($message) && file_exists(public_path('images/nri-logo.png'))) {
        $logo = $message->embed(public_path('images/nri-logo.png'));
    }

    $geographicFocus = implode(', ', $data['geographic_focus'] ?? []);
    $focusStates = implode(', ', $data['focus_states'] ?? []);
    $features = implode(', ', $data['features_of_interest'] ?? []);

    $isNationwide = in_array('Nationwide coverage', $data['geographic_focus'] ?? []);
@endphp


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Premium Request</title>
</head>

<body style="margin:0; padding:0; background: {{ $bg }};">

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="padding:24px 12px; background:{{ $bg }};">
        <tr>
            <td align="center">

                <table width="680" cellpadding="0" cellspacing="0" border="0" style="max-width:680px;">

                    {{-- Header --}}
                    <tr>
                        <td style="padding-bottom:16px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left">
                                        <img src="{{ $logo }}" width="160" alt="Nigeria Risk Index"
                                            style="display:block;">
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff;border:1px solid #e2e8f0;overflow:hidden;">

                            {{-- Top Bar --}}
                            <div style="background:{{ $primary }};padding:16px 20px;">
                                <p style="margin:0;font-family:Arial,sans-serif;font-size:14px;color:#ffffff;">
                                    New Premium Access Request
                                </p>
                            </div>

                            <div style="padding:22px 20px;font-family:Arial,sans-serif;">

                                {{-- Contact Section --}}
                                <h3 style="margin:0 0 10px 0;color:{{ $dark }};font-size:16px;">Contact
                                    Information</h3>

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="font-size:13px;margin-bottom:20px;">
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Name</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['contact_name'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Email</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['contact_email'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Phone</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['contact_phone'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Preferred Method</td>
                                        <td style="padding:6px 0;font-weight:600;">
                                            {{ $data['preferred_contact_method'] }}</td>
                                    </tr>
                                </table>

                                {{-- Organization Section --}}
                                <h3 style="margin:0 0 10px 0;color:{{ $dark }};font-size:16px;">Organization
                                </h3>

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="font-size:13px;margin-bottom:20px;">
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Name</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['organization_name'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Industry Type</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['organization_type'] }}
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Industry</td>
                                        <td style="padding:6px 0;font-weight:600;">
                                            {{ $data['industry_sector'] ?? '-' }}</td>
                                    </tr> --}}
                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Company Size</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $data['company_size'] ?? '-' }}
                                        </td>
                                    </tr>
                                </table>

                                {{-- Use Case Section --}}
                                {{-- Use Case Section --}}
                                <h3 style="margin:0 0 10px 0;color:{{ $dark }};font-size:16px;">Use Case
                                    Details</h3>

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="font-size:13px;margin-bottom:20px;">

                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Primary Use Case</td>
                                        <td style="padding:6px 0;font-weight:600;">
                                            {{ $data['primary_use_case'] }}
                                            @if (($data['primary_use_case'] ?? '') === 'Other' && !empty($data['primary_use_case_other']))
                                                — {{ $data['primary_use_case_other'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Geographic Focus</td>
                                        <td style="padding:6px 0;font-weight:600;">
                                            {{ $geographicFocus ?: '-' }}
                                        </td>
                                    </tr>

                                    @if (!$isNationwide)

                                        @if (!empty($focusStates))
                                            <tr>
                                                <td style="padding:6px 0;color:{{ $muted }};">States</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $focusStates }}</td>
                                            </tr>
                                        @endif

                                        @if (!empty($data['focus_sectors_regions']))
                                            <tr>
                                                <td style="padding:6px 0;color:{{ $muted }};">Sectors/Regions
                                                </td>
                                                <td style="padding:6px 0;font-weight:600;">
                                                    {{ $data['focus_sectors_regions'] }}
                                                </td>
                                            </tr>
                                        @endif

                                        @if (!empty($data['focus_cities_lgas']))
                                            <tr>
                                                <td style="padding:6px 0;color:{{ $muted }};">Cities/LGAs</td>
                                                <td style="padding:6px 0;font-weight:600;">
                                                    {{ $data['focus_cities_lgas'] }}
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr>
                                            <td style="padding:6px 0;color:{{ $muted }};">Coverage</td>
                                            <td style="padding:6px 0;font-weight:600;color:#10b981;">
                                                Nationwide (All States)
                                            </td>
                                        </tr>

                                    @endif

                                    <tr>
                                        <td style="padding:6px 0;color:{{ $muted }};">Features</td>
                                        <td style="padding:6px 0;font-weight:600;">{{ $features ?: '-' }}</td>
                                    </tr>

                                </table>


                                {{-- Attribution --}}


                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="padding:16px 6px 0 6px;font-family:Arial,sans-serif;font-size:12px;color:{{ $muted }}; align-items:center;text-align:center;line-height:1.6;">
                            © 2026 Risk Control Services Nigeria Ltd. All Rights
                            Reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>

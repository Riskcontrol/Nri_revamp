<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>NRI Risk Assessment Report</title>
    <style>
        /* BASE STYLES */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        /* HEADER DESIGN */
        .header-banner {
            background-color: #1E2D3D;
            /* Dark Navy Background */
            padding: 30px 40px;
            color: #ffffff;
            border-bottom: 5px solid #047857;
            /* Green Bottom Border */
            margin-bottom: 30px;
        }

        /* 1. LOGO AREA */
        .header-logo {
            margin-bottom: 15px;
            /* Space below logo */
            display: block;
        }

        .logo-img {
            height: 80px;
            width: auto;
        }

        /* 2. REPORT TITLE ("NRI REPORT") */
        .header-title {
            font-size: 26px;
            font-weight: 800;
            /* Extra Bold */
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 5px 0;
            color: #ffffff;
        }


        /* 3. LOCATION LINE ("Location Assessment: ...") */
        .header-location {
            font-size: 16px;
            font-weight: 400;
            color: #e2e8f0;
            margin-bottom: 8px;
        }

        .header-location strong {
            color: #fff;
            font-weight: 700;
        }

        /* 4. META LINE (Scope & Date) */
        .header-meta {
            font-size: 11px;
            color: #94a3b8;
            /* Muted Grey */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        /* TYPOGRAPHY & SECTIONS */
        h1.main-title {
            margin: 0;
            margin-top: 10px;
            font-size: 22px;
            font-weight: 400;
            color: #f8fafc;
        }

        h2 {
            font-size: 15px;
            color: #047857;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
            margin-top: 35px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        /* ADVISORY BOX (Clean Design - No Side Border) */
        .advisory {
            background: #ecfdf5;
            /* Very light green bg */
            padding: 20px;
            font-size: 13px;
            color: #064e3b;
            /* Dark green text */
            margin: 0 40px 30px 40px;
            border-radius: 6px;
            border: 1px solid #a7f3d0;
            /* Subtle full border instead of thick side border */
        }

        .advisory-title {
            color: #047857;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 5px;
            display: block;
        }

        /* STATS */
        .grid-container {
            margin: 0 40px;
            width: calc(100% - 80px);
        }

        .stat-table {
            width: 100%;
            border-spacing: 15px 0;
            margin-left: -15px;
            /* Compensate for spacing */
        }

        .stat-box {
            background: #ffffff;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-val {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            display: block;
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-top: 5px;
            display: block;
        }

        /* TABLES */
        .content-wrap {
            padding: 0 40px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        table.data-table th {
            text-align: left;
            background: #f8fafc;
            padding: 10px 8px;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            border-bottom: 2px solid #e2e8f0;
        }

        table.data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        /* UTILS */
        .page-break {
            page-break-before: always;
        }

        /* INCIDENT LOG */
        .incident-item {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .incident-item:last-child {
            border-bottom: none;
        }

        .incident-header {
            margin-bottom: 6px;
        }

        .incident-date {
            color: #047857;
            font-weight: 700;
            font-size: 12px;
            margin-right: 10px;
        }

        .incident-badge {
            background: #e2e8f0;
            color: #475569;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* CTA */
        .cta-section {
            background-color: #1E2D3D;
            /* Matches Header */
            color: white;
            padding: 35px;
            border-radius: 8px;
            margin: 40px 40px 20px 40px;
            text-align: center;
        }

        .cta-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #34d399;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            font-size: 10px;
            color: #cbd5e1;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>

    @php
        // Pre-calculate values to avoid Blade re-processing
        $maxRiskCount = $riskDistribution->first()->count ?? 1;
        $hasHotspots = $hotspots->count() > 0;
    @endphp

    <div class="header-banner">

        <div class="header-logo">

            <img src="{{ public_path('images/nri-logo.png') }}" class="logo-img" alt="NRI Logo">

        </div>

        <div class="header-title">
            NRI Report
        </div>

        <div class="header-location">
            Location Assessment: <strong>{{ $lga }}, {{ $state }}</strong>
        </div>

        <div class="header-meta">
            Scope: {{ $year }} &nbsp;|&nbsp; Generated: {{ date('d M Y') }}
        </div>

    </div>

    <div class="advisory">
        <span class="advisory-title">Executive Security Advisory</span>
        {{ $advisory }}
    </div>

    <div class="grid-container">
        <table class="stat-table">
            <tr>
                <td>
                    <div class="stat-box">
                        <span class="stat-val">{{ number_format($casualties->deaths) }}</span>
                        <span class="stat-label"> Fatalities</span>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <span class="stat-val">{{ number_format($casualties->kidnaps) }}</span>
                        <span class="stat-label">Victims</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrap">
        <h2>Threat Profile (Top Indicators)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Risk Indicator</th>
                    <th style="text-align: right;">Frequency</th>
                    <th style="width: 40%;">Intensity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($riskDistribution as $risk)
                    <tr>
                        <td>{{ $risk->riskindicators }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $risk->count }}</td>
                        <td>
                            <div style="background: #e2e8f0; height: 6px; width: 100%; border-radius: 3px;">
                                <div
                                    style="background: #334155; height: 6px; width: {{ min(100, ($risk->count / $maxRiskCount) * 100) }}%; border-radius: 3px;">
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($hotspots->count() > 0)
            <h2>Identified Flashpoints</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Neighborhood / Area</th>
                        <th style="text-align: right;">Incident Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hotspots as $spot)
                        <tr>
                            <td>{{ $spot->neighbourhood_name }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $spot->incidents }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- <div class="page-break"></div> --}}

    <div style="padding: 20px 40px; border-bottom: 2px solid #f1f5f9; margin-bottom: 30px;">
        <h2 style="margin: 0; padding: 0; border: none; font-size: 18px; color: #1E2D3D;">Recent Incident Log</h2>
        <div style="font-size: 12px; color: #64748b;">Detailed narrative of significant security events</div>
    </div>

    <div class="content-wrap">
        @foreach ($incidents->take(5) as $incident)
            <div class="incident-item">
                <div class="incident-header">
                    <span
                        class="incident-date">{{ Carbon\Carbon::parse($incident->eventdate)->format('M d, Y') }}</span>
                    <span class="incident-badge">{{ $incident->riskindicators }}</span>
                    @if ($incident->neighbourhood_name)
                        <span style="font-size: 11px; color: #94a3b8;"> &bull;
                            {{ $incident->neighbourhood_name }}</span>
                    @endif
                </div>
                <div style="font-size: 12px; line-height: 1.6; color: #334155;">
                    {{-- Pre-strip tags and limit in controller instead of here --}}
                    {{ Str::limit($incident->add_notes, 300) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="cta-section">
        <div class="cta-title">Strategic Intelligence for Critical Decisions</div>
        <p style="font-size: 12px; opacity: 0.8; margin: 0;">
            Request a bespoke analysis or schedule a consultation with our Security Operations Center.
            <br>
            <strong style="color: #34d399; margin-top: 5px; display: inline-block;">info@riskcontrolnigeria.com</strong>
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Risk Control Services Nigeria Ltd. All Rights Reserved.
    </div>

</body>

</html>

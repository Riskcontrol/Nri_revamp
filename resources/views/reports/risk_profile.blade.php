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
            padding: 30px 40px;
            color: #ffffff;
            border-bottom: 5px solid #047857;
            margin-bottom: 30px;
            /* CRITICAL: Allows us to position the badge absolutely inside this block */
            position: relative;
        }

        .header-logo {
            margin-bottom: 15px;
            display: block;
        }

        .logo-img {
            height: 80px;
            width: auto;
        }

        .header-title {
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 5px 0;
            color: #ffffff;
        }

        .header-location {
            font-size: 16px;
            font-weight: 400;
            color: #e2e8f0;
            margin-bottom: 8px;
            /* Ensure text doesn't overlap badge if location is long */
            max-width: 65%;
        }

        .header-location strong {
            color: #fff;
            font-weight: 700;
        }

        .header-meta {
            font-size: 11px;
            color: #94a3b8;
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

        /* NEW SUMMARY STYLE */
        .summary-text {
            font-size: 14px;
            color: #334155;
            margin-bottom: 30px;
            line-height: 1.6;
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

        /* PROGRESS BAR */
        .progress-bg {
            background: #e2e8f0;
            height: 6px;
            width: 100%;
            border-radius: 3px;
        }

        .progress-fill {
            background: #334155;
            height: 6px;
            border-radius: 3px;
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

        .page-break {
            page-break-before: always;
        }

        /* ... existing styles ... */

        /* UPDATED: Static container for Table Layout */
        .impact-badge-container {
            text-align: right;
            margin: 0;
            padding: 0;
        }

        .impact-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 5px;
        }

        .impact-tag {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            color: #fff;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>

    @php
        $maxRiskCount = $riskDistribution->first()->count ?? 1;
        $hasHotspots = $hotspots->count() > 0;
        $currentYear = date('Y');
        $generatedDate = date('d M Y');

        $formattedDeaths = number_format($casualties->deaths);
        $formattedKidnaps = number_format($casualties->kidnaps);

        // --- CALCULATE IMPACT LEVEL ---
        $totalImpact = $casualties->deaths + $casualties->kidnaps;

        if ($totalImpact >= 50) {
            $impactText = 'CRITICAL';
            $impactColor = '#dc2626'; // Red
        } elseif ($totalImpact >= 20) {
            $impactText = 'HIGH';
            $impactColor = '#f97316'; // Orange
        } elseif ($totalImpact >= 5) {
            $impactText = 'MODERATE';
            $impactColor = '#eab308'; // Yellow
        } else {
            $impactText = 'LOW';
            $impactColor = '#10b981'; // Emerald
        }
    @endphp

    <div class="header-banner">
        <table width="100%" style="border-collapse: collapse; border: none;">
            <tr>
                <td style="vertical-align: top; text-align: left;">
                    <div class="header-logo">
                        <img src="{{ $logoSrc }}" class="logo-img" alt="NRI Logo">
                    </div>

                    <div class="header-title">NRI Report</div>

                    <div class="header-location">
                        Location Assessment: <strong>{{ $lga }}, {{ $state }}</strong>
                    </div>

                    <div class="header-meta">
                        Scope: {{ $year }} &nbsp;|&nbsp; Generated: {{ $generatedDate }}
                    </div>
                </td>

                <td style="vertical-align: bottom; text-align: right; width: 200px;">
                    <div class="impact-badge-container">
                        <span class="impact-label">Calculated Risk Level</span>
                        <span class="impact-tag" style="background-color: {{ $impactColor }};">
                            {{ $impactText }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrap">
        <h2>Executive Security Advisory</h2>
        <div class="summary-text">
            {{ $advisory }}
        </div>
    </div>

    <div class="grid-container">
        <table class="stat-table">
            <tr>
                <td>
                    <div class="stat-box">
                        <span class="stat-val">{{ $formattedDeaths }}</span>
                        <span class="stat-label">Fatalities</span>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <span class="stat-val">{{ $formattedKidnaps }}</span>
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
                    @php
                        $barWidth = min(100, ($risk->count / $maxRiskCount) * 100);
                    @endphp
                    <tr>
                        <td>{{ $risk->riskindicators }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $risk->count }}</td>
                        <td>
                            <div class="progress-bg">
                                <div class="progress-fill" style="width: {{ $barWidth }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($hasHotspots)
            <div class="page-break"></div>
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

    <div style="padding: 20px 40px; border-bottom: 2px solid #f1f5f9; margin-bottom: 30px;">
        <h2 style="margin: 0; padding: 0; border: none; font-size: 18px;">Recent Incident Log</h2>
        <div style="font-size: 12px; color: #64748b;">Detailed narrative of significant security events</div>
    </div>

    <div class="content-wrap">
        @foreach ($incidents as $incident)
            @php
                $formattedDate = \Carbon\Carbon::parse($incident->eventdateToUse)->format('M d, Y');
            @endphp
            <div class="incident-item">
                <div class="incident-header">
                    <span class="incident-date">{{ $formattedDate }}</span>
                    <span class="incident-badge">{{ $incident->riskindicators }}</span>
                    @if ($incident->neighbourhood_name)
                        <span style="font-size: 11px; color: #94a3b8;"> &bull;
                            {{ $incident->neighbourhood_name }}</span>
                    @endif
                </div>
                <div style="font-size: 12px; line-height: 1.6; color: #334155;">
                    {{ $incident->add_notes }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="cta-section">
        <div class="cta-title">Do you have a need for Strategic Intelligence?</div>
        <p style="font-size: 12px; opacity: 0.8; margin: 0; line-height: 1.6;">
            Request a bespoke analysis or schedule a consultation with our Security Analysts today.
            <br>

            {{-- Email --}}
            <span style="display: block; margin-top: 8px;">
                <strong style="color: #34d399;">info@riskcontrolnigeria.com</strong>
            </span>

            {{-- Websites --}}
            <span style="display: block; margin-top: 4px; font-size: 11px; opacity: 0.9;">
                <a href="https://nigeriariskindex.com"
                    style="color: #fff; text-decoration: none;">nigeriariskindex.com</a>
                <span style="margin: 0 5px; color: #64748b;">|</span>
                <a href="https://riskcontrolnigeria.com"
                    style="color: #fff; text-decoration: none;">riskcontrolnigeria.com</a>
            </span>
        </p>
    </div>

    <div class="footer">
        &copy; {{ $currentYear }} Risk Control Services Nigeria Ltd. All Rights Reserved.
    </div>

</body>

</html>

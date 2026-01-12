<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Risk Assessment Report</title>
    <style>
        body {
            font-family: sans-serif;
            color: #334155;
            line-height: 1.5;
        }

        .header {
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .meta {
            color: #64748b;
            font-size: 12px;
            margin-top: 5px;
        }

        h1 {
            font-size: 22px;
            color: #0f172a;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 16px;
            color: #10b981;
            text-transform: uppercase;
            margin-top: 30px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .grid {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }

        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .stat-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-right: 15px;
        }

        .stat-val {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            display: block;
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        th {
            text-align: left;
            background: #f1f5f9;
            padding: 8px;
            color: #475569;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .incident {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .incident-date {
            color: #10b981;
            font-weight: bold;
            font-size: 11px;
        }

        .incident-type {
            background: #0f172a;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 10px;
        }

        .incident-desc {
            font-size: 12px;
            margin-top: 4px;
            text-align: justify;
        }

        .advisory {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 15px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo">NRI Risk Watch</div>
        <h1>Location Assessment: {{ $lga }}, {{ $state }}</h1>
        <div class="meta">Report generated on {{ date('d M Y') }} | Scope: {{ $year }}</div>
    </div>

    <div class="advisory">
        <strong>EXECUTIVE SECURITY ADVISORY:</strong><br>
        {{ $advisory }}
    </div>

    <div class="grid">
        <div class="col">
            <div class="stat-box">
                <span class="stat-val text-red-600">{{ number_format($casualties->deaths) }}</span>
                <span class="stat-label">Verified Fatalities</span>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <span class="stat-val">{{ number_format($casualties->kidnaps) }}</span>
                <span class="stat-label">Kidnap Victims</span>
            </div>
        </div>
    </div>

    <h2>Threat Profile (Top Indicators)</h2>
    <p style="font-size: 12px; color: #64748b; margin-bottom: 10px;">Breakdown of incident types reported in this
        location over the selected period.</p>
    <table>
        <thead>
            <tr>
                <th>Risk Indicator</th>
                <th style="text-align: right;">Frequency</th>
                <th style="width: 50%;">Intensity Bar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($riskDistribution as $risk)
                <tr>
                    <td>{{ $risk->riskindicators }}</td>
                    <td style="text-align: right;">{{ $risk->count }}</td>
                    <td>
                        <div style="background: #e2e8f0; height: 6px; width: 100%; border-radius: 3px;">
                            <div
                                style="background: #0f172a; height: 6px; width: {{ min(100, ($risk->count / $riskDistribution->first()->count) * 100) }}%; border-radius: 3px;">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($hotspots->count() > 0)
        <h2>Identified Flashpoints</h2>
        <p style="font-size: 12px; color: #64748b;">Neighborhoods with the highest concentration of reported incidents.
        </p>
        <table>
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

    <h2>Recent Incident Log</h2>
    @foreach ($incidents as $incident)
        <div class="incident">
            <div>
                <span class="incident-date">{{ \Carbon\Carbon::parse($incident->eventdate)->format('M d, Y') }}</span>
                <span class="incident-type">{{ $incident->riskindicators }}</span>
                @if ($incident->neighbourhood_name)
                    <span style="font-size: 10px; color: #64748b;"> | {{ $incident->neighbourhood_name }}</span>
                @endif
            </div>
            <div class="incident-desc">
                {{ Str::limit(strip_tags($incident->add_notes), 250) }}
            </div>
        </div>
    @endforeach

    <div class="footer">
        Generated by NRI Project Risk Engine. This report is for informational purposes only. <br>
        &copy; {{ date('Y') }} Risk Control Services Nigeria Ltd.
    </div>

</body>

</html>

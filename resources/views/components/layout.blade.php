<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $title ?? config('app.name', 'Nigeria Risk Index') }}</title>

    <meta name="description" content="{{ $description ?? 'Nigeria Risk Index' }}">
    <meta name="keywords" content="{{ $keywords ?? 'risk, index, Nigeria' }}">
    <meta name="author" content="Nigeria Risk Index">

    <link rel="icon" type="image/x-icon" href="{{ asset('images/nri-logo.png') }}">
    <meta property="og:image" content="{{ asset('images/nri-logo.png') }}">
    <meta property="og:image:alt" content="Nigeria Risk Index Logo">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-K3NGJH469J"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-K3NGJH469J');
    </script>
</head>

<body class="font-sans bg-[#0A1628] text-white">

    {{-- ════════════════════════════════════════════════════════════════════
     GLOBAL ANNOUNCEMENT BANNER
     Source: Cache::get('site_announcement') — set via admin dashboard.
     Active only when active = true. Per-session dismiss via sessionStorage.
     Banner ID = updated_at timestamp so a new alert always shows fresh.
════════════════════════════════════════════════════════════════════ --}}
    @php
        $announcement = \Illuminate\Support\Facades\Cache::get('site_announcement');
        $showBanner = !empty($announcement['active']);
        $hasLinkedIncident = $showBanner && !empty($announcement['eventid']);
        $annId = $showBanner ? $announcement['updated_at'] ?? 'nri-announcement' : '';

        if ($showBanner) {
            // Headline: add_notes from linked incident first, fallback to typed headline
            $bannerHeadline = !empty($announcement['incident']['add_notes'])
                ? $announcement['incident']['add_notes']
                : $announcement['headline'] ?? '';

            // Truncate — full text shown in the modal
            $bannerHeadlineDisplay = \Illuminate\Support\Str::limit(ucfirst(strtolower($bannerHeadline)), 110, '…');
            $modalBadgeBg = match ($announcement['impact_level'] ?? 'critical') {
                'critical' => '#B91C1C',
                'high' => '#C2410C',
                'medium' => '#B45309',
                default => '#B91C1C',
            };
            $inc = $announcement['incident'] ?? null;
        }
    @endphp

    @if ($showBanner)

        {{-- ── Inline styles — scoped only to the banner ──────────────────────── --}}
        <style>
            /* ── Animations ─────────────────────────────────────────────────────────── */
            @keyframes bnSlideIn {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            @keyframes bnShimmer {
                0% {
                    background-position: 200% center;
                }

                100% {
                    background-position: -200% center;
                }
            }

            @keyframes bnPing {
                0% {
                    transform: scale(1);
                    opacity: 0.75;
                }

                80% {
                    transform: scale(2.5);
                    opacity: 0;
                }

                100% {
                    opacity: 0;
                }
            }

            @keyframes bnFlicker {

                0%,
                93%,
                100% {
                    opacity: 1;
                }

                95% {
                    opacity: 0.8;
                }

                97% {
                    opacity: 1;
                }
            }

            @keyframes adModalIn {
                from {
                    transform: translateY(18px) scale(0.97);
                    opacity: 0;
                }

                to {
                    transform: translateY(0) scale(1);
                    opacity: 1;
                }
            }

            @keyframes adPulseDot {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.6;
                    transform: scale(1.3);
                }
            }

            /* ── Banner wrapper ─────────────────────────────────────────────────────── */
            #nri-banner {
                position: relative;
                z-index: 9999;
                background: linear-gradient(135deg,
                        #5a0a0a 0%,
                        #7f1d1d 40%,
                        #991b1b 100%);
                overflow: hidden;
                animation: bnSlideIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            /* shimmer scan */
            #nri-banner::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(105deg, transparent 35%, rgba(255, 255, 255, 0.07) 50%, transparent 65%);
                background-size: 400% 100%;
                animation: bnShimmer 4s linear infinite;
                pointer-events: none;
            }

            /* ── Inner container ────────────────────────────────────────────────────── */
            .bn-inner {
                position: relative;
                z-index: 1;
                max-width: 88rem;
                margin: 0 auto;
                padding: 10px 16px;
            }

            /* ── ROW 1: badge · headline · dismiss ──────────────────────────────────── */
            .bn-row1 {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                min-width: 0;
            }

            /* pulse dot */
            .bn-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #fff;
                position: relative;
                flex-shrink: 0;
                margin-top: 1px;
                display: inline-block;
            }

            .bn-dot::before {
                content: '';
                position: absolute;
                inset: -3px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.45);
                animation: bnPing 1.2s ease-out infinite;
            }

            /* badge */
            .bn-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 2px 7px;
                border-radius: 3px;
                background: rgba(0, 0, 0, 0.35);
                white-space: nowrap;
                flex-shrink: 0;
            }

            .bn-badge-text {
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.08em;
                color: #fff;
                text-transform: uppercase;
            }

            /* headline */
            .bn-headline {
                flex: 1;
                min-width: 0;
                font-size: 14px;
                font-weight: 600;
                color: #fff;
                line-height: 1.45;
                /* increased for readability */
                letter-spacing: 0.01em;
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            /* dismiss button */
            .bn-dismiss {
                flex-shrink: 0;
                background: transparent;
                border: none;
                cursor: pointer;
                color: rgba(255, 255, 255, 0.7);
                font-size: 18px;
                line-height: 1;
                padding: 2px 4px;
                border-radius: 4px;
                transition: color 0.15s, background 0.15s;
                margin-top: -1px;
            }

            .bn-dismiss:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.12);
            }

            /* ── ROW 2: meta · buttons ─────────────────────────────────────────────── */
            .bn-row2 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 7px;
            }

            .bn-meta {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
                font-size: 11px;
                color: rgba(255, 255, 255, 0.75);
            }

            .bn-meta-item {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                white-space: nowrap;
            }

            .bn-actions {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: nowrap;
            }

            /* shared button */
            .bn-btn {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 12px;
                border-radius: 5px;
                font-size: 11px;
                font-weight: 700;
                color: #fff;
                white-space: nowrap;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid rgba(255, 255, 255, 0.35);
                background: rgba(255, 255, 255, 0.14);
                transition: background 0.15s, border-color 0.15s;
                flex-shrink: 0;
            }

            .bn-btn:hover {
                background: rgba(255, 255, 255, 0.26);
                border-color: rgba(255, 255, 255, 0.6);
            }

            .bn-btn-sub {
                background: rgba(0, 0, 0, 0.25);
                border-color: rgba(255, 255, 255, 0.2);
            }

            .bn-btn-sub:hover {
                background: rgba(0, 0, 0, 0.4);
            }

            /* ── MOBILE ≤ 640px ─────────────────────────────────────────────────────── */
            @media (max-width: 640px) {
                .bn-inner {
                    padding: 8px 12px;
                }

                .bn-badge-text {
                    display: none;
                }

                /* keep dot, hide long label text */
                .bn-headline {
                    font-size: 12px;
                    line-height: 1.35;
                }

                .bn-meta-time,
                .bn-meta-update {
                    display: none;
                }

                /* only location on mobile */
                .bn-btn-sub-text {
                    display: none;
                }

                /* hide "Subscribe to Critical Alerts", keep icon */
                .bn-actions {
                    gap: 5px;
                }

                .bn-btn {
                    padding: 4px 10px;
                    font-size: 10px;
                }
            }

            /* ── MOBILE ≤ 400px ─────────────────────────────────────────────────────── */
            @media (max-width: 400px) {
                .bn-meta {
                    display: none;
                }

                /* hide all meta on very small screens */
                .bn-row2 {
                    justify-content: flex-end;
                }
            }
        </style>

        {{-- ── Banner HTML ─────────────────────────────────────────────────────── --}}
        <div id="nri-banner" role="alert" aria-live="assertive">
            <div class="bn-inner">

                {{-- ROW 1: badge · headline · ✕ --}}
                <div class="bn-row1">

                    {{-- Badge --}}
                    <span class="bn-badge">
                        <span class="bn-dot"></span>
                        <span class="bn-badge-text">🔴 Breaking </span>
                    </span>


                    {{-- Headline --}}
                    <strong class="bn-headline">{{ $bannerHeadlineDisplay }}</strong>

                    {{-- Dismiss --}}
                    {{-- <button class="bn-dismiss" onclick="dismissBanner('{{ $annId }}')"
                        aria-label="Dismiss alert">✕</button> --}}
                </div>

                {{-- ROW 2: meta · action buttons --}}
                <div class="bn-row2">

                    {{-- Meta --}}
                    <div class="bn-meta">
                        @if (!empty($announcement['location']))
                            <span class="bn-meta-item">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                Location: {{ $announcement['location'] }}
                            </span>
                        @endif

                        @if (!empty($announcement['time']))
                            <span class="bn-meta-item bn-meta-time">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                Time: {{ $announcement['time'] }}
                            </span>
                        @endif

                        <span class="bn-meta-item bn-meta-update">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <polyline points="23 4 23 10 17 10" />
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                            </svg>
                            Last Update: {{ \Carbon\Carbon::parse($announcement['updated_at'])->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Action buttons --}}
                    <div class="bn-actions">
                        @if ($hasLinkedIncident)
                            <button type="button" class="bn-btn" onclick="openAlertDetailModal()">
                                View Full Alert Details
                            </button>
                        @else
                            <a href="{{ route('news') }}" class="bn-btn">
                                View Full Alert Details
                            </a>
                        @endif

                        <a href="{{ route('news') }}#newsletter" class="bn-btn bn-btn-sub">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span class="bn-btn-sub-text">Subscribe to Critical Alerts</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Dismiss script — must run immediately before DOMContentLoaded ─────── --}}
        <script>
            (function() {
                // Hide instantly if already dismissed in this session
                if (sessionStorage.getItem('nri_banner_dismissed') === {{ json_encode($annId) }}) {
                    const el = document.getElementById('nri-banner');
                    if (el) el.style.display = 'none';
                }
            })();

            function dismissBanner(id) {
                sessionStorage.setItem('nri_banner_dismissed', id);
                const el = document.getElementById('nri-banner');
                if (!el) return;
                el.style.maxHeight = el.offsetHeight + 'px';
                el.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    el.style.transition = 'max-height 0.35s cubic-bezier(0.4,0,1,1), opacity 0.25s ease';
                    el.style.maxHeight = '0';
                    el.style.opacity = '0';
                });
                setTimeout(() => el.remove(), 380);
            }
        </script>

        {{-- ── Alert Detail Modal — only rendered when an incident is linked ─────── --}}
        @if ($hasLinkedIncident)
            <div id="alertDetailModal" role="dialog" aria-modal="true" aria-label="Alert Details"
                style="display:none;position:fixed;inset:0;z-index:100000;
            background:rgba(0,0,0,0.82);backdrop-filter:blur(8px);
            align-items:center;justify-content:center;padding:16px">

                <div id="alertDetailPanel"
                    style="width:100%;max-width:680px;max-height:90vh;
                display:flex;flex-direction:column;
                background:#0D1627;border:1px solid rgba(255,255,255,0.1);
                border-radius:16px;box-shadow:0 32px 80px rgba(0,0,0,0.7);overflow:hidden">

                    {{-- Modal header --}}
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;
                    padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);flex-shrink:0">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span
                                style="display:inline-flex;align-items:center;gap:6px;
                             padding:3px 10px;border-radius:4px;background:{{ $modalBadgeBg }};
                             font-size:9px;font-weight:900;letter-spacing:0.12em;
                             color:#fff;text-transform:uppercase">
                                <span
                                    style="width:6px;height:6px;border-radius:50%;background:#fff;
                                 animation:adPulseDot 1.5s ease-in-out infinite;
                                 display:inline-block"></span>
                                {{ strtoupper($announcement['impact_level'] ?? 'critical') }} ALERT
                            </span>
                            <span style="font-size:11px;color:rgba(255,255,255,0.4)">
                                Active Security Alert
                            </span>
                        </div>
                        <button onclick="closeAlertDetailModal()" aria-label="Close"
                            style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);
                           border-radius:8px;color:rgba(255,255,255,0.6);cursor:pointer;
                           width:32px;height:32px;display:flex;align-items:center;
                           justify-content:center;font-size:15px;transition:all 0.15s"
                            onmouseover="this.style.background='rgba(255,255,255,0.14)';this.style.color='#fff'"
                            onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.color='rgba(255,255,255,0.6)'">
                            ✕
                        </button>
                    </div>

                    {{-- Modal body --}}
                    <div style="overflow-y:auto;flex:1;padding:20px 24px">

                        {{-- Full headline --}}
                        <h2
                            style="font-size:clamp(16px,3vw,22px);font-weight:800;color:#fff;
                       line-height:1.25;margin:0 0 12px;letter-spacing:-0.01em">
                            {{ strtoupper($announcement['headline']) }}
                        </h2>

                        {{-- Meta chips --}}
                        <div
                            style="display:flex;flex-wrap:wrap;gap:14px;font-size:11px;
                        color:rgba(255,255,255,0.5);margin-bottom:20px">
                            @if (!empty($announcement['location']))
                                <span style="display:inline-flex;align-items:center;gap:4px">
                                    <svg width="10" height="10" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    {{ $announcement['location'] }}
                                </span>
                            @endif
                            @if (!empty($announcement['time']))
                                <span style="display:inline-flex;align-items:center;gap:4px">
                                    <svg width="10" height="10" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $announcement['time'] }}
                                </span>
                            @endif
                            @if (!empty($inc['riskfactors']))
                                <span style="display:inline-flex;align-items:center;gap:4px">
                                    <svg width="10" height="10" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    </svg>
                                    {{ $inc['riskfactors'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Content sections --}}
                        @foreach ([['Summary', $inc['add_notes'] ?? null], ['Detailed Intelligence', $inc['weekly_summary'] ?? null], ['Risk Assessment', $inc['associated_risks'] ?? null], ['Business Advisory', $inc['business_advisory'] ?? null]] as [$sLabel, $sValue])
                            @if ($sValue)
                                <div
                                    style="margin-bottom:14px;padding:14px 16px;
                        background:rgba(255,255,255,0.04);
                        border:1px solid rgba(255,255,255,0.07);border-radius:10px">
                                    <p
                                        style="font-size:9px;font-weight:900;letter-spacing:0.16em;
                          color:rgba(255,255,255,0.3);text-transform:uppercase;margin:0 0 7px">
                                        {{ $sLabel }}
                                    </p>
                                    <p style="font-size:13.5px;line-height:1.75;color:rgba(255,255,255,0.8);margin:0">
                                        {{ $sValue }}
                                    </p>
                                </div>
                            @endif
                        @endforeach

                        {{-- Fact chips --}}
                        @if ($inc)
                            @php
                                $chips = array_filter(
                                    [
                                        ['State', $inc['location'] ?? null],
                                        ['LGA', $inc['lga'] ?? null],
                                        ['Indicator', $inc['riskindicators'] ?? null],
                                        [
                                            'Casualties',
                                            !empty($inc['Casualties_count'])
                                                ? $inc['Casualties_count'] . ' reported'
                                                : null,
                                        ],
                                        ['Industry', $inc['affected_industry'] ?? null],
                                    ],
                                    fn($c) => !empty($c[1]),
                                );
                            @endphp
                            @if (count($chips))
                                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px">
                                    @foreach ($chips as [$cLabel, $cValue])
                                        <div
                                            style="padding:7px 12px;border-radius:8px;
                            background:rgba(255,255,255,0.05);
                            border:1px solid rgba(255,255,255,0.08)">
                                            <p
                                                style="font-size:8px;font-weight:800;letter-spacing:0.12em;
                              color:rgba(255,255,255,0.3);text-transform:uppercase;margin:0 0 2px">
                                                {{ $cLabel }}
                                            </p>
                                            <p style="font-size:12px;color:rgba(255,255,255,0.8);margin:0">
                                                {{ $cValue }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                    </div>{{-- /modal body --}}

                    {{-- Modal footer --}}
                    <div
                        style="padding:12px 20px;border-top:1px solid rgba(255,255,255,0.08);
                    display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
                        <a href="{{ route('security-alert.show', $announcement['eventid']) }}" target="_blank"
                            style="font-size:11px;color:rgba(255,255,255,0.4);text-decoration:none;
                      display:inline-flex;align-items:center;gap:5px;transition:color 0.15s"
                            onmouseover="this.style.color='rgba(255,255,255,0.75)'"
                            onmouseout="this.style.color='rgba(255,255,255,0.4)'">
                            <svg width="11" height="11" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            Open full page
                        </a>
                        <button onclick="closeAlertDetailModal()"
                            style="padding:7px 20px;border-radius:8px;
                           border:1px solid rgba(255,255,255,0.15);
                           background:rgba(255,255,255,0.07);
                           color:rgba(255,255,255,0.7);
                           font-size:12px;font-weight:600;cursor:pointer;transition:all 0.15s"
                            onmouseover="this.style.background='rgba(255,255,255,0.13)';this.style.color='#fff'"
                            onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.color='rgba(255,255,255,0.7)'">
                            Close
                        </button>
                    </div>

                </div>{{-- /alertDetailPanel --}}
            </div>{{-- /alertDetailModal --}}

            <style>
                @keyframes adModalIn {
                    from {
                        transform: translateY(18px) scale(0.97);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0) scale(1);
                        opacity: 1;
                    }
                }

                @keyframes adPulseDot {

                    0%,
                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }

                    50% {
                        opacity: 0.6;
                        transform: scale(1.3);
                    }
                }
            </style>

            <script>
                function openAlertDetailModal() {
                    const modal = document.getElementById('alertDetailModal');
                    const panel = document.getElementById('alertDetailPanel');
                    modal.style.display = 'flex';
                    panel.style.animation = 'adModalIn 0.3s cubic-bezier(0.16,1,0.3,1) both';
                    document.body.style.overflow = 'hidden';
                }

                function closeAlertDetailModal() {
                    const modal = document.getElementById('alertDetailModal');
                    const panel = document.getElementById('alertDetailPanel');
                    panel.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                    panel.style.transform = 'translateY(10px) scale(0.98)';
                    panel.style.opacity = '0';
                    setTimeout(() => {
                        modal.style.display = 'none';
                        panel.style.transform = '';
                        panel.style.opacity = '';
                        panel.style.transition = '';
                        panel.style.animation = '';
                    }, 220);
                    document.body.style.overflow = '';
                }

                document.getElementById('alertDetailModal')?.addEventListener('click', function(e) {
                    if (e.target === this) closeAlertDetailModal();
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        const modal = document.getElementById('alertDetailModal');
                        if (modal && modal.style.display === 'flex') closeAlertDetailModal();
                    }
                });
            </script>
        @endif {{-- hasLinkedIncident --}}

    @endif {{-- showBanner --}}

    {{-- ════════════════════════════════════════════════════════════════════
     HEADER — unconditionally rendered, always below the banner
════════════════════════════════════════════════════════════════════ --}}
    <x-header />

    <main>
        {{ $slot }}
    </main>

    <x-footer />

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('menu').classList.toggle('hidden');
        });
    </script>
    <script src="{{ asset('js/map.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-geo@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</body>

</html>

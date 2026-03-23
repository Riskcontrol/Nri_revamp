<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $title ?? config('app.name', 'Nigeria Risk Index') }}</title>

    <meta name="description" content="{{ $description ?? 'Nigeria Risk Index' }}">
    <meta name="keywords" content="{{ $keywords ?? 'risk, index, Nigeria' }}">
    <meta name="author" content="Nigeria Risk Index">

    <link rel="icon" type="image/x-icon" href="{{ asset('images/nri-logo.ico') }}">
    <meta property="og:image" content="{{ asset('images/nri-logo.ico') }}">
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

    {{-- ══════════════════════════════════════════════════════════════
         GLOBAL ANNOUNCEMENT BANNER
         Reads Cache::get('site_announcement') set by the admin dashboard.
         Rendered only when active = true. Dismissed per-session via
         sessionStorage so it does not reappear after the user closes it.
         The banner id is the ISO timestamp of when it was last set, so a
         new announcement always shows even if the previous one was dismissed.
    ══════════════════════════════════════════════════════════════ --}}
    @php
        $announcement = \Illuminate\Support\Facades\Cache::get('site_announcement');
        $showBanner = !empty($announcement['active']);

        $bannerColors = [
            'critical' => [
                'label' => '🔴 BREAKING SECURITY ALERT',
                'gradient' => 'linear-gradient(135deg,#7F1D1D 0%,#991B1B 40%,#B91C1C 100%)',
            ],
            'high' => [
                'label' => '🟠 HIGH ALERT',
                'gradient' => 'linear-gradient(135deg,#7C2D12 0%,#9A3412 40%,#C2410C 100%)',
            ],
            'medium' => [
                'label' => '⚠ SECURITY ALERT',
                'gradient' => 'linear-gradient(135deg,#78350F 0%,#92400E 40%,#B45309 100%)',
            ],
        ];
        $bClr = $bannerColors[$announcement['impact_level'] ?? 'critical'] ?? $bannerColors['critical'];
        $annId = $announcement['updated_at'] ?? 'nri-announcement';

        // true only when the admin linked a specific uploaded incident
        $hasLinkedIncident = !empty($announcement['eventid']);
    @endphp

    @if ($showBanner)

        {{-- ── Banner styles ──────────────────────────────────────────────────── --}}
        <style>
            #nri-banner {
                position: relative;
                z-index: 9999;
                background: {{ $bClr['gradient'] }};
                overflow: hidden;
                animation: bannerSlideIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            @keyframes bannerSlideIn {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            /* shimmer scan line */
            #nri-banner::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(105deg,
                        transparent 30%,
                        rgba(255, 255, 255, 0.06) 50%,
                        transparent 70%);
                background-size: 250% 100%;
                animation: bannerShimmer 3.5s linear infinite;
                pointer-events: none;
            }

            @keyframes bannerShimmer {
                0% {
                    background-position: 150% center;
                }

                100% {
                    background-position: -50% center;
                }
            }

            .bn-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #fff;
                position: relative;
                flex-shrink: 0;
            }

            .bn-dot::before {
                content: '';
                position: absolute;
                inset: -3px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.45);
                animation: bnPing 1.2s ease-out infinite;
            }

            @keyframes bnPing {
                0% {
                    transform: scale(1);
                    opacity: 0.7;
                }

                80% {
                    transform: scale(2.4);
                    opacity: 0;
                }

                100% {
                    opacity: 0;
                }
            }

            .bn-label {
                animation: bnFlicker 4s ease-in-out infinite;
            }

            @keyframes bnFlicker {

                0%,
                94%,
                100% {
                    opacity: 1;
                }

                95% {
                    opacity: 0.82;
                }

                97% {
                    opacity: 1;
                }
            }

            .bn-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 14px;
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
                border-color: rgba(255, 255, 255, 0.55);
            }

            .bn-btn-bell {
                background: rgba(0, 0, 0, 0.22);
                border-color: rgba(255, 255, 255, 0.2);
            }

            .bn-btn-bell:hover {
                background: rgba(0, 0, 0, 0.35);
            }

            .bn-dismiss {
                background: transparent;
                border: none;
                cursor: pointer;
                color: rgba(255, 255, 255, 0.6);
                font-size: 14px;
                padding: 2px 6px;
                flex-shrink: 0;
                transition: color 0.15s;
                line-height: 1;
                border-radius: 4px;
            }

            .bn-dismiss:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.1);
            }
        </style>

        {{-- ── Banner bar ──────────────────────────────────────────────────────── --}}
        <div id="nri-banner" role="alert" aria-live="assertive">
            <div
                style="max-width:88rem;margin:0 auto;padding:0 20px;
                    display:flex;align-items:center;gap:10px;flex-wrap:nowrap;
                    min-height:52px;position:relative;z-index:1">

                {{-- Pulse label badge --}}
                <span class="bn-label"
                    style="display:inline-flex;align-items:center;gap:7px;
                         padding:3px 10px 3px 8px;border-radius:4px;
                         background:rgba(0,0,0,0.28);white-space:nowrap;flex-shrink:0">
                    <span class="bn-dot"></span>
                    <span
                        style="font-size:10px;font-weight:900;letter-spacing:0.12em;
                             color:#fff;text-transform:uppercase">
                        {{ $bClr['label'] }}
                    </span>
                </span>

                {{-- Headline --}}
                <strong
                    style="font-size:clamp(13px,1.6vw,16px);font-weight:800;color:#fff;
                           letter-spacing:0.01em;flex:1;min-width:0;
                           white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ strtoupper($announcement['headline']) }}
                </strong>

                {{-- Meta — location / time / last update --}}
                <div
                    style="display:flex;align-items:center;gap:14px;font-size:11px;
                        color:rgba(255,255,255,0.78);flex-shrink:0;flex-wrap:nowrap">
                    @if (!empty($announcement['location']))
                        <span style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            Location: {{ $announcement['location'] }}
                        </span>
                    @endif
                    @if (!empty($announcement['time']))
                        <span style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            Time: {{ $announcement['time'] }}
                        </span>
                    @endif
                    <span style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap">
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <polyline points="23 4 23 10 17 10" />
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                        </svg>
                        Last Update: {{ \Carbon\Carbon::parse($announcement['updated_at'])->diffForHumans() }}
                    </span>
                </div>

                {{--
                View Full Alert Details:
                - Linked incident → opens detail modal (no page navigation)
                - Manual headline → navigates to /news
            --}}
                @if ($hasLinkedIncident)
                    <button type="button" class="bn-btn" onclick="openAlertDetailModal()">
                        View Full Alert Details
                    </button>
                @else
                    <a href="{{ route('news') }}" class="bn-btn">
                        View Full Alert Details
                    </a>
                @endif

                {{-- Subscribe to Critical Alerts --}}
                <a href="{{ route('news') }}#newsletter" class="bn-btn bn-btn-bell">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    Subscribe to Critical Alerts
                </a>

                {{-- Dismiss ✕ --}}
                <button class="bn-dismiss" onclick="dismissBanner('{{ $annId }}')" aria-label="Dismiss alert">
                    ✕
                </button>
            </div>
        </div>

        {{-- Dismiss JS — inline so it runs before DOMContentLoaded --}}
        <script>
            (function() {
                if (sessionStorage.getItem('nri_banner_dismissed') === {{ json_encode($annId) }}) {
                    const el = document.getElementById('nri-banner');
                    if (el) el.style.display = 'none';
                }
            })();

            function dismissBanner(id) {
                sessionStorage.setItem('nri_banner_dismissed', id);
                const el = document.getElementById('nri-banner');
                if (!el) return;
                const h = el.offsetHeight;
                el.style.maxHeight = h + 'px';
                el.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    el.style.transition = 'max-height 0.35s cubic-bezier(0.4,0,1,1), opacity 0.25s ease';
                    el.style.maxHeight = '0';
                    el.style.opacity = '0';
                });
                setTimeout(() => el.remove(), 380);
            }
        </script>

        {{-- ── Alert Detail Modal (only when an incident is linked) ───────────── --}}
        @if ($hasLinkedIncident)
            @php
                $modalBadgeBg = match ($announcement['impact_level'] ?? 'critical') {
                    'critical' => '#B91C1C',
                    'high' => '#C2410C',
                    'medium' => '#B45309',
                    default => '#B91C1C',
                };
                $inc = $announcement['incident'] ?? null;
            @endphp

            <div id="alertDetailModal" role="dialog" aria-modal="true" aria-label="Alert Details"
                style="display:none;position:fixed;inset:0;z-index:100000;
                background:rgba(0,0,0,0.82);backdrop-filter:blur(8px);
                align-items:center;justify-content:center;padding:16px">

                <div id="alertDetailPanel"
                    style="width:100%;max-width:680px;max-height:90vh;
                    display:flex;flex-direction:column;
                    background:#0D1627;border:1px solid rgba(255,255,255,0.1);
                    border-radius:16px;box-shadow:0 32px 80px rgba(0,0,0,0.7);
                    overflow:hidden">

                    {{-- Modal header --}}
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;
                        padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.08);
                        flex-shrink:0">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span
                                style="display:inline-flex;align-items:center;gap:6px;
                                 padding:3px 10px;border-radius:4px;
                                 background:{{ $modalBadgeBg }};
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
                            style="background:rgba(255,255,255,0.07);
                               border:1px solid rgba(255,255,255,0.1);
                               border-radius:8px;color:rgba(255,255,255,0.6);
                               cursor:pointer;width:32px;height:32px;
                               display:flex;align-items:center;justify-content:center;
                               font-size:15px;transition:all 0.15s"
                            onmouseover="this.style.background='rgba(255,255,255,0.14)';this.style.color='#fff'"
                            onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.color='rgba(255,255,255,0.6)'">
                            ✕
                        </button>
                    </div>

                    {{-- Modal body — scrollable --}}
                    <div style="overflow-y:auto;flex:1;padding:24px">

                        {{-- Headline --}}
                        <h2
                            style="font-size:clamp(17px,3vw,22px);font-weight:800;color:#fff;
                           line-height:1.25;margin:0 0 14px;letter-spacing:-0.01em">
                            {{ strtoupper($announcement['headline']) }}
                        </h2>

                        {{-- Meta chips --}}
                        <div
                            style="display:flex;flex-wrap:wrap;gap:16px;font-size:11px;
                            color:rgba(255,255,255,0.5);margin-bottom:24px">
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

                        {{-- Content sections — only render non-empty fields --}}
                        @foreach ([['Summary', $inc['add_notes'] ?? null], ['Detailed Intelligence', $inc['weekly_summary'] ?? null], ['Risk Assessment', $inc['associated_risks'] ?? null], ['Business Advisory', $inc['business_advisory'] ?? null]] as [$sLabel, $sValue])
                            @if ($sValue)
                                <div
                                    style="margin-bottom:16px;padding:16px;
                            background:rgba(255,255,255,0.04);
                            border:1px solid rgba(255,255,255,0.07);
                            border-radius:10px">
                                    <p
                                        style="font-size:9px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(255,255,255,0.3);text-transform:uppercase;margin:0 0 8px">
                                        {{ $sLabel }}
                                    </p>
                                    <p
                                        style="font-size:13.5px;line-height:1.75;
                              color:rgba(255,255,255,0.8);margin:0">
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
                                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px">
                                    @foreach ($chips as [$cLabel, $cValue])
                                        <div
                                            style="padding:8px 12px;border-radius:8px;
                                background:rgba(255,255,255,0.05);
                                border:1px solid rgba(255,255,255,0.08)">
                                            <p
                                                style="font-size:8px;font-weight:800;letter-spacing:0.12em;
                                  color:rgba(255,255,255,0.3);text-transform:uppercase;
                                  margin:0 0 3px">
                                                {{ $cLabel }}</p>
                                            <p style="font-size:12px;color:rgba(255,255,255,0.8);margin:0">
                                                {{ $cValue }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                    </div>{{-- /modal body --}}

                    {{-- Modal footer --}}
                    <div
                        style="padding:14px 24px;border-top:1px solid rgba(255,255,255,0.08);
                        display:flex;align-items:center;justify-content:space-between;
                        flex-shrink:0">
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
                               font-size:12px;font-weight:600;
                               cursor:pointer;transition:all 0.15s"
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
                        transform: translateY(20px) scale(0.97);
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

    {{-- ════════════════════════════════════════════════════════════
         HEADER — always renders, completely outside the banner @if block
    ════════════════════════════════════════════════════════════ --}}
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

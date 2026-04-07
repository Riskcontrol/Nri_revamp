{{--
    partials/news/risk-map-panel.blade.php
    ──────────────────────────────────────────────────────────────────────────────
    Two-column dark panel:
      LEFT  (~320 px) — scrollable active security alerts sidebar
      RIGHT (flex-1)  — Leaflet map, dark basemap, choropleth state overlay
                         (High = red, Medium = amber, Low = green) +
                         incident markers colour-coded by risk factor

    Variables expected (all passed from SecurityHubController via news.blade.php):
      $activeAlerts  — Collection of Breaking News rows (from fetchActiveAlerts())
      $totalIncidents, $highRiskAlerts, $statesAffected — stat counters
--}}

{{-- Leaflet CSS --}}
@once
    @push('head')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"
            crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css"
            crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css"
            crossorigin="anonymous" />
    @endpush
@endonce

<style>
    /* ── Panel wrapper ────────────────────────────────────────────────────────── */
    #nri-risk-panel {
        position: relative;
        z-index: 0;
        background: #0d1b2a;
    }

    /* ── Two-column layout ────────────────────────────────────────────────────── */
    #nri-panel-inner {
        display: flex;
        align-items: stretch;
        height: 600px;
    }

    /* Tablet: stack vertically */
    @media (max-width: 1023px) {
        #nri-panel-inner {
            flex-direction: column;
            height: auto;
        }

        #nri-alerts-sidebar {
            width: 100% !important;
            height: 300px !important;
            border-right: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        #nri-map-col {
            height: 420px;
        }
    }

    /* Mobile: tighter heights */
    @media (max-width: 640px) {
        #nri-alerts-sidebar {
            height: 260px !important;
        }

        #nri-map-col {
            height: 340px;
        }

        #nri-panel-popup {
            width: calc(100vw - 24px) !important;
            max-width: 340px;
        }
    }

    /* ── LEFT SIDEBAR ─────────────────────────────────────────────────────────── */
    #nri-alerts-sidebar {
        width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        background: #0d1b2a;
        overflow: hidden;
    }

    #nri-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    #nri-sidebar-header h3 {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.5);
        margin: 0 0 10px;
    }

    .nri-stat-row {
        display: flex;
        gap: 6px;
    }

    .nri-stat-chip {
        flex: 1;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 7px 8px;
        text-align: center;
    }

    .nri-stat-chip .val {
        display: block;
        font-size: clamp(16px, 4vw, 20px);
        font-weight: 700;
        color: #fff;
        line-height: 1.1;
    }

    .nri-stat-chip .lbl {
        display: block;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.35);
        margin-top: 2px;
    }

    /* Scrollable alerts list */
    #nri-alerts-scroll {
        flex: 1;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
    }

    #nri-alerts-scroll::-webkit-scrollbar {
        width: 4px;
    }

    #nri-alerts-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 2px;
    }

    /* Individual alert item */
    .nri-alert-item {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: background 0.15s;
    }

    .nri-alert-item:hover {
        background: rgba(255, 255, 255, 0.04);
    }

    .nri-alert-item:last-child {
        border-bottom: none;
    }

    .nri-alert-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        color: #fff;
        margin-bottom: 7px;
    }

    .nri-alert-badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        display: inline-block;
        flex-shrink: 0;
    }

    .nri-alert-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #f0f4f8;
        line-height: 1.35;
        margin: 0 0 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nri-alert-meta {
        font-size: 10.5px;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .nri-alert-summary {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.4);
        line-height: 1.5;
        margin: 6px 0 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nri-alert-btn {
        display: block;
        width: 100%;
        padding: 6px;
        border-radius: 6px;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        text-align: center;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
    }

    .nri-alert-btn:hover {
        opacity: 0.85;
    }

    .nri-alert-btn:active {
        transform: scale(0.98);
    }

    /* Empty state */
    .nri-sidebar-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 24px;
        color: rgba(255, 255, 255, 0.25);
        text-align: center;
    }

    /* ── RIGHT MAP COLUMN ─────────────────────────────────────────────────────── */
    #nri-map-col {
        flex: 1;
        position: relative;
        min-width: 0;
    }

    /* Desktop: height:100% works because parent #nri-panel-inner has explicit 600px */
    #nri-risk-map {
        width: 100%;
        height: 100%;
    }

    /* Tablet/mobile: height:100% fails when parent collapses to height:auto.
   Mirror the col heights explicitly so Leaflet always gets a real px value. */
    @media (max-width: 1023px) {
        #nri-risk-map {
            height: 420px;
        }
    }

    @media (max-width: 640px) {
        #nri-risk-map {
            height: 340px;
        }
    }

    /* ── MAP HEADER OVERLAY ───────────────────────────────────────────────────── */
    #nri-map-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 400;
        padding: 12px 16px 10px;
        background: linear-gradient(to bottom, rgba(13, 27, 42, 0.95) 0%, rgba(13, 27, 42, 0) 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        pointer-events: none;
    }

    #nri-map-title {
        font-size: clamp(12px, 3vw, 14px);
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.01em;
    }

    #nri-map-subtitle {
        font-size: clamp(9px, 2vw, 10px);
        color: rgba(255, 255, 255, 0.45);
        font-weight: 500;
    }

    /* ── MAP LOADING OVERLAY ──────────────────────────────────────────────────── */
    #nri-map-loader {
        position: absolute;
        inset: 0;
        background: rgba(13, 27, 42, 0.9);
        z-index: 500;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: opacity 0.3s;
    }

    #nri-map-loader.hidden {
        opacity: 0;
        pointer-events: none;
    }

    #nri-map-status {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.4);
        font-weight: 500;
    }

    /* ── FLOATING POPUP CARD ──────────────────────────────────────────────────── */
    #nri-panel-popup {
        position: fixed;
        z-index: 1200;
        width: 320px;
        background: #1e2d3d;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6), 0 4px 16px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        pointer-events: auto;
        opacity: 0;
        transform: scale(0.92) translateY(8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        display: none;
    }

    #nri-panel-popup.open {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    #nri-panel-popup-header {
        padding: 10px 40px 10px 14px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #fff;
        position: relative;
    }

    #nri-panel-popup-close {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    #nri-panel-popup-close:hover {
        background: rgba(255, 255, 255, 0.28);
    }

    #nri-panel-popup-body {
        padding: 12px 16px 14px;
    }

    #nri-panel-popup-title {
        font-size: 13px;
        font-weight: 600;
        color: #f8fafc;
        margin: 0 0 7px;
        line-height: 1.4;
    }

    .nri-panel-popup-meta {
        font-size: 11px;
        color: #94a3b8;
        margin-bottom: 3px;
        display: flex;
        align-items: flex-start;
        gap: 5px;
    }

    #nri-panel-popup-casualties {
        margin-top: 7px;
        font-size: 11px;
        font-weight: 600;
        color: #fca5a5;
        display: none;
    }

    #nri-panel-popup-summary {
        margin-top: 9px;
        font-size: 12px;
        color: #cbd5e1;
        line-height: 1.6;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        padding-top: 9px;
        display: none;
    }

    /* ── LEAFLET OVERRIDES ────────────────────────────────────────────────────── */
    #nri-risk-map .leaflet-bar a,
    #nri-risk-map .leaflet-bar a:hover {
        display: block !important;
        width: 28px !important;
        height: 28px !important;
        line-height: 28px !important;
        text-align: center !important;
        text-decoration: none !important;
        color: #1e2d3d !important;
        background: #ffffff !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    #nri-risk-map .leaflet-bar a:hover {
        background: #f1f5f9 !important;
    }

    #nri-risk-map .leaflet-bar a:first-child {
        border-radius: 6px 6px 0 0 !important;
    }

    #nri-risk-map .leaflet-bar a:last-child {
        border-radius: 0 0 6px 6px !important;
        border-bottom: none !important;
    }

    #nri-risk-map .leaflet-bar {
        border-radius: 6px !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5) !important;
        border: none !important;
        overflow: hidden !important;
    }

    .nri-marker-dot {
        cursor: pointer;
        transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .nri-marker-dot:hover {
        transform: scale(1.4);
    }
</style>

{{-- ── FLOATING POPUP CARD ──────────────────────────────────────────────── --}}
<div id="nri-panel-popup" role="dialog" aria-modal="true" aria-label="Incident details">
    <div id="nri-panel-popup-header">
        <span id="nri-panel-popup-factor"></span>
        <button id="nri-panel-popup-close" aria-label="Close" onclick="nriPanelClosePopup()">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.8" stroke-linecap="round" />
            </svg>
        </button>
    </div>
    <div id="nri-panel-popup-body">
        <p id="nri-panel-popup-title"></p>
        <p class="nri-panel-popup-meta">
            <span>📍</span><span id="nri-panel-popup-location"></span>
        </p>
        <p class="nri-panel-popup-meta">
            <span>📅</span><span id="nri-panel-popup-date"></span>
        </p>
        <p id="nri-panel-popup-casualties"></p>
        <p id="nri-panel-popup-summary"></p>
    </div>
</div>

{{-- ── RISK MAP PANEL ────────────────────────────────────────────────────── --}}
<section id="nri-risk-panel" class="px-4 sm:px-6 lg:px-16 py-8 sm:py-10">
    <div class="max-w-7xl mx-auto">

        {{-- ── SECTION INTRO — dedicated block separate from the panel ─────────── --}}
        <div class="mb-6 pb-6 border-b border-white/[0.07]">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>

                    <h2 class="text-2xl sm:text-3xl font-medium text-white leading-tight tracking-tight">
                        Nigeria Risk Map
                    </h2>
                    <p class="text-sm text-gray-400 mt-2 max-w-xl leading-relaxed">
                        Access simplified, up-to-date risk information across states. Monitor emerging threats and stay
                        informed with verified alerts.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <label for="nri-panel-filter"
                        class="text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap hidden sm:block">Filter</label>
                    <select id="nri-panel-filter"
                        class="text-sm rounded-lg border border-gray-700 bg-[#111e30] text-white
                                   px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/20
                                   shadow-sm cursor-pointer w-full sm:w-auto sm:min-w-[150px]"
                        style="appearance:auto;">
                        <option value="all">All risk factors</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Two-column panel --}}
        <div class="rounded-2xl overflow-hidden border border-white/[0.08] shadow-2xl">
            <div id="nri-panel-inner">

                {{-- ── LEFT: Alerts Sidebar ────────────────────────────────── --}}
                <div id="nri-alerts-sidebar">

                    {{-- Sidebar header with stats --}}
                    <div id="nri-sidebar-header">
                        <h3>
                            <span
                                class="inline-block w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 align-middle
                                         animate-pulse"></span>
                            Active Security Alerts
                        </h3>

                    </div>

                    {{-- Scrollable alert list --}}
                    <div id="nri-alerts-scroll">

                        @if ($activeAlerts->isNotEmpty())
                            @php
                                $sidebarLevelConfig = [
                                    'Critical' => [
                                        'badge_bg' => '#7f1d1d',
                                        'btn_bg' => '#991b1b',
                                    ],
                                    'High' => [
                                        'badge_bg' => '#dc2626',
                                        'btn_bg' => '#dc2626',
                                    ],
                                    'Medium' => [
                                        'badge_bg' => '#d97706',
                                        'btn_bg' => '#d97706',
                                    ],
                                    'Low' => [
                                        'badge_bg' => '#059669',
                                        'btn_bg' => '#059669',
                                    ],
                                ];
                            @endphp

                            @foreach ($activeAlerts as $i => $alert)
                                @php
                                    $sCfg = $sidebarLevelConfig[$alert->impact_label] ?? $sidebarLevelConfig['Low'];
                                @endphp
                                <div class="nri-alert-item" onclick="openAlertModal({{ $i }})">

                                    {{-- Level badge --}}
                                    <span class="nri-alert-badge" style="background: {{ $sCfg['badge_bg'] }};">
                                        <span class="nri-alert-badge-dot"></span>
                                        {{ $alert->impact_label }}
                                    </span>

                                    {{-- Context fragment --}}
                                    @if (isset($alert->header_fragment) && $alert->header_fragment)
                                        <p class="text-[10.5px] text-white/40 font-medium mb-1 truncate">
                                            {{ $alert->header_fragment }}
                                        </p>
                                    @endif

                                    {{-- Title --}}
                                    <h4 class="nri-alert-title">{{ $alert->card_title }}</h4>

                                    {{-- Location --}}
                                    <div class="nri-alert-meta">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <span class="truncate">{{ $alert->location_display }}</span>
                                    </div>

                                    {{-- Date --}}
                                    <div class="nri-alert-meta">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2"
                                                ry="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        {{ $alert->formatted_date }}
                                    </div>

                                    {{-- Summary --}}
                                    <p class="nri-alert-summary">{{ $alert->add_notes ?: 'No summary available.' }}
                                    </p>

                                    {{-- View Details button --}}
                                    <button class="nri-alert-btn" style="background: {{ $sCfg['btn_bg'] }};"
                                        onclick="event.stopPropagation(); openAlertModal({{ $i }})">
                                        View Details
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="nri-sidebar-empty">
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <p class="text-xs font-medium">No active alerts</p>
                                <p class="text-[10px] opacity-60">Breaking news alerts will appear here</p>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ── RIGHT: Map column ──────────────────────────────────── --}}
                <div id="nri-map-col">

                    {{-- Map header gradient overlay --}}
                    <div id="nri-map-header">
                        <div>
                            <div id="nri-map-title">Nigeria Risk Map</div>
                            <div id="nri-map-subtitle">Colour-coded by incident risk level</div>
                        </div>
                    </div>

                    {{-- Leaflet map --}}
                    <div id="nri-risk-map"></div>

                    {{-- Loading overlay --}}
                    <div id="nri-map-loader">
                        <svg class="animate-spin h-7 w-7 text-white/40" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span id="nri-map-status" class="text-white/40 text-sm font-medium">
                            Loading map data…
                        </span>
                    </div>

                </div>
                {{-- /map col --}}

            </div>{{-- /panel-inner --}}
        </div>{{-- /panel --}}

    </div>
</section>

{{-- Leaflet JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"
    crossorigin="anonymous"></script>

<script>
    (function() {
        'use strict';

        /* ── Floating popup ─────────────────────────────────────────────────────── */
        var popup = document.getElementById('nri-panel-popup');
        var riskMap;

        window.nriPanelClosePopup = function() {
            popup.classList.remove('open');
            setTimeout(function() {
                popup.style.display = 'none';
            }, 200);
        };

        function positionPopup(latlng) {
            var pt = riskMap.latLngToContainerPoint(latlng);
            var mapEl = document.getElementById('nri-map-col');
            var rect = mapEl.getBoundingClientRect();
            var w = 320;
            var h = popup.offsetHeight || 200;
            var left = rect.left + pt.x - w / 2;
            var top = rect.top + pt.y - h - 20;
            left = Math.max(12, Math.min(left, window.innerWidth - w - 12));
            if (top < 60) top = rect.top + pt.y + 24;
            popup.style.left = left + 'px';
            popup.style.top = top + 'px';
        }

        function openPopup(p, latlng) {
            var factor = (p.factor_label || '').toUpperCase();
            if (p.indicator && p.indicator !== p.factor_label) {
                factor += ' · ' + p.indicator.toUpperCase();
            }
            document.getElementById('nri-panel-popup-factor').textContent = factor;
            document.getElementById('nri-panel-popup-header').style.background = p.factor_color || '#1e3a5f';
            document.getElementById('nri-panel-popup-title').textContent = p.caption || 'Security Incident';
            document.getElementById('nri-panel-popup-location').textContent = [p.lga, p.state].filter(Boolean).join(
                ', ') || 'Nigeria';
            document.getElementById('nri-panel-popup-date').textContent = p.date || '';

            var casEl = document.getElementById('nri-panel-popup-casualties');
            if (p.casualties > 0 || p.injuries > 0) {
                var parts = [];
                if (p.casualties > 0) parts.push(p.casualties + ' killed');
                if (p.injuries > 0) parts.push(p.injuries + ' injured');
                casEl.textContent = '⚠ ' + parts.join(' · ');
                casEl.style.display = 'block';
            } else {
                casEl.style.display = 'none';
            }

            var sumEl = document.getElementById('nri-panel-popup-summary');
            if (p.summary) {
                sumEl.textContent = p.summary.substring(0, 200) + (p.summary.length > 200 ? '…' : '');
                sumEl.style.display = 'block';
            } else {
                sumEl.style.display = 'none';
            }

            popup.style.display = 'block';
            popup.classList.remove('open');
            requestAnimationFrame(function() {
                positionPopup(latlng);
                requestAnimationFrame(function() {
                    popup.classList.add('open');
                });
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') window.nriPanelClosePopup();
        });
        document.addEventListener('click', function(e) {
            if (popup.style.display === 'block' &&
                !popup.contains(e.target) &&
                !e.target.closest('.nri-marker-dot')) {
                window.nriPanelClosePopup();
            }
        });

        /* ── Map init ───────────────────────────────────────────────────────────────
           Deferred to window 'load' so the browser has fully painted the layout
           and Leaflet can read real pixel dimensions from the container.
           On mobile, running L.map() before paint gives 0×0 → blank grey box.
        ────────────────────────────────────────────────────────────────────────── */
        var NG_BOUNDS = L.latLngBounds(L.latLng(4.0, 3.0), L.latLng(14.0, 15.1));

        function initMap() {
            riskMap = L.map('nri-risk-map', {
                center: [9.082, 8.675],
                zoom: 6,
                minZoom: 5,
                maxZoom: 14,
                maxBounds: NG_BOUNDS,
                maxBoundsViscosity: 1.0,
                scrollWheelZoom: false,
                zoomControl: true,
            });

            riskMap.on('click', function() {
                riskMap.scrollWheelZoom.enable();
            });
            riskMap.on('mouseout', function() {
                riskMap.scrollWheelZoom.disable();
            });
            riskMap.on('movestart zoomstart', function() {
                window.nriPanelClosePopup();
            });

            /* Dark basemap tile layer */
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> &copy; <a href="https://carto.com/" target="_blank">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19,
            }).addTo(riskMap);

            /* Safety net: force Leaflet to recalculate its size after paint.
               Handles cases where the container reported 0px at init time
               (common on mobile Chrome/Safari). */
            setTimeout(function() {
                riskMap.invalidateSize();
            }, 100);

            /* Load data after map is ready */
            loadStateGeoJSON();
            loadIncidents();

            /* Refresh incidents every 10 min */
            setInterval(loadIncidents, 10 * 60 * 1000);
        }

        /* Run after full page load so all CSS is applied and heights are real */
        if (document.readyState === 'complete') {
            initMap();
        } else {
            window.addEventListener('load', initMap);
        }

        /* ── Choropleth: colour states by risk level ────────────────────────────── */
        /* Risk level → fill colour (matches reference image palette)               */
        var CHOROPLETH_COLORS = {
            'High': {
                fill: '#ef4444',
                opacity: 0.55
            }, // red
            'Medium': {
                fill: '#f59e0b',
                opacity: 0.50
            }, // amber
            'Low': {
                fill: '#10b981',
                opacity: 0.40
            }, // green
            'default': {
                fill: '#1e3a5f',
                opacity: 0.30
            }, // dark blue fallback
        };

        var stateRiskData = {}; // populated after GeoJSON incidents load
        var choroplethLayer = null;

        function getChoroplethStyle(feature) {
            var stateName = (feature.properties.name || feature.properties.state || '').trim();
            var level = stateRiskData[stateName] || 'default';
            var cfg = CHOROPLETH_COLORS[level] || CHOROPLETH_COLORS['default'];
            return {
                fillColor: cfg.fill,
                fillOpacity: cfg.opacity,
                color: 'rgba(255,255,255,0.12)',
                weight: 1,
                opacity: 1,
            };
        }

        function loadStateGeoJSON() {
            fetch('/data/nigeria-state.geojson')
                .then(function(r) {
                    return r.json();
                })
                .then(function(geo) {
                    choroplethLayer = L.geoJSON(geo, {
                        style: getChoroplethStyle,
                        onEachFeature: function(feature, layer) {
                            var name = feature.properties.name || feature.properties.state || '';
                            if (name) {
                                layer.bindTooltip(name, {
                                    className: '',
                                    sticky: true,
                                    direction: 'top',
                                    offset: [0, -4],
                                });
                            }
                        },
                    }).addTo(riskMap);
                    // render below markers
                    choroplethLayer.bringToBack();
                })
                .catch(function(err) {
                    console.warn('[NRI choropleth] Could not load state GeoJSON:', err);
                });
        }

        /* Recompute state risk levels from incident features */
        function buildStateRisk(features) {
            var counts = {}; // { stateName: { High:n, Medium:n, Low:n } }

            features.forEach(function(f) {
                var state = (f.properties.state || f.properties.location || '').trim();
                if (!state) return;
                var lvl = f.properties.impact_label || 'Low'; // Critical treated as High
                if (lvl === 'Critical') lvl = 'High';
                if (!counts[state]) counts[state] = {
                    High: 0,
                    Medium: 0,
                    Low: 0
                };
                if (counts[state][lvl] !== undefined) counts[state][lvl]++;
            });

            var result = {};
            Object.keys(counts).forEach(function(state) {
                var c = counts[state];
                if (c.High > 0) result[state] = 'High';
                else if (c.Medium > 0) result[state] = 'Medium';
                else result[state] = 'Low';
            });
            return result;
        }

        /* ── Marker cluster ─────────────────────────────────────────────────────── */
        var clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 55,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            iconCreateFunction: function(cluster) {
                var n = cluster.getChildCount();
                var size = n > 30 ? 44 : n > 10 ? 36 : 28;
                return L.divIcon({
                    html: '<div style="background:#0d1b2a;border:2px solid rgba(255,255,255,0.22);' +
                        'border-radius:50%;width:' + size + 'px;height:' + size + 'px;' +
                        'display:flex;align-items:center;justify-content:center;' +
                        'font-weight:700;font-size:11px;color:#fff;' +
                        'box-shadow:0 3px 10px rgba(0,0,0,0.6);">' + n + '</div>',
                    className: '',
                    iconSize: [size, size],
                });
            },
        });

        function makeMarkerIcon(color, isBreaking) {
            var r = isBreaking ? 9 : 7;
            var vb = isBreaking ? 28 : 20;
            var cx = vb / 2;
            var pulse = isBreaking ?
                '<circle cx="' + cx + '" cy="' + cx + '" r="' + (r + 3) + '" fill="' + color + '" opacity="0.25">' +
                '<animate attributeName="r" values="' + (r + 2) + ';' + (r + 7) + ';' + (r + 2) +
                '" dur="1.8s" repeatCount="indefinite"/>' +
                '<animate attributeName="opacity" values="0.25;0;0.25" dur="1.8s" repeatCount="indefinite"/>' +
                '</circle>' :
                '';
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + vb + '" height="' + vb +
                '" viewBox="0 0 ' + vb + ' ' + vb + '">' +
                pulse +
                '<circle cx="' + cx + '" cy="' + cx + '" r="' + r + '" fill="' + color +
                '" stroke="#fff" stroke-width="2" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.7));"/>' +
                '</svg>';
            return L.divIcon({
                className: 'nri-marker-dot',
                html: svg,
                iconSize: [vb, vb],
                iconAnchor: [cx, cx],
            });
        }

        /* ── Data & rendering ───────────────────────────────────────────────────── */
        var allFeatures = [];
        var activeFactor = 'all';

        function buildFilterDropdown(legend, present) {
            var sel = document.getElementById('nri-panel-filter');
            while (sel.options.length > 1) sel.remove(1);
            Object.keys(legend).forEach(function(key) {
                if (!present.has(key)) return;
                var opt = document.createElement('option');
                opt.value = key;
                opt.textContent = legend[key].label;
                sel.appendChild(opt);
            });
        }

        function renderMarkers() {
            clusterGroup.clearLayers();
            window.nriPanelClosePopup();

            var subset = activeFactor === 'all' ?
                allFeatures :
                allFeatures.filter(function(f) {
                    return f.properties.factor === activeFactor;
                });

            subset.forEach(function(feature) {
                var p = feature.properties;
                var ll = L.latLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                var marker = L.marker(ll, {
                    icon: makeMarkerIcon(p.factor_color, p.is_breaking),
                    title: p.caption || p.factor_label,
                });
                marker.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    openPopup(p, ll);
                });
                clusterGroup.addLayer(marker);
            });

            riskMap.addLayer(clusterGroup);
            // ensure choropleth stays below markers
            if (choroplethLayer) choroplethLayer.bringToBack();
        }

        document.getElementById('nri-panel-filter').addEventListener('change', function() {
            activeFactor = this.value;
            renderMarkers();
        });

        function loadIncidents() {
            fetch('{{ route('incidents.geojson') }}')
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(geojson) {
                    var loader = document.getElementById('nri-map-loader');
                    if (loader) loader.classList.add('hidden');

                    allFeatures = geojson.features || [];
                    var legend = geojson.legend || {};

                    if (allFeatures.length === 0) {
                        document.getElementById('nri-map-status').textContent =
                            'No incidents in the past 7 days.';
                        return;
                    }

                    /* Build choropleth state-risk lookup, then refresh layer */
                    stateRiskData = buildStateRisk(allFeatures);
                    if (choroplethLayer) {
                        choroplethLayer.setStyle(getChoroplethStyle);
                    }

                    var present = new Set(allFeatures.map(function(f) {
                        return f.properties.factor;
                    }));
                    buildFilterDropdown(legend, present);
                    renderMarkers();
                })
                .catch(function(err) {
                    var loader = document.getElementById('nri-map-loader');
                    if (loader) loader.classList.add('hidden');
                    document.getElementById('nri-map-status').textContent =
                        'Map data unavailable — please refresh.';
                    console.error('[NRI panel map]', err);
                });
        }

        /* Reposition popup on resize; also invalidate map size in case viewport changed */
        window.addEventListener('resize', function() {
            if (popup.style.display === 'block') window.nriPanelClosePopup();
            if (riskMap) {
                riskMap.invalidateSize();
            }
        });

    })();
</script>

{{-- ══════════════════════════════════════════════════════════
     ALERT DETAIL MODAL
     Moved here from news.blade.php so it is always present
     regardless of whether $activeAlerts is non-empty.
══════════════════════════════════════════════════════════ --}}
<div id="alertModal" class="fixed inset-0 z-[9999] hidden items-center justify-center px-4 py-8"
    style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px)">

    <div
        class="w-full max-w-lg bg-[#0E1B2C] rounded-2xl border border-white/10 shadow-2xl overflow-hidden
                max-h-[90vh] flex flex-col">

        {{-- Modal header --}}
        <div class="flex items-start justify-between p-6 border-b border-white/10 flex-shrink-0">
            <div class="flex-1 min-w-0 pr-4">
                <span id="modal-level-badge"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3">
                </span>
                <h3 id="modal-title" class="text-white font-bold text-lg leading-snug"></h3>
            </div>
            <button onclick="closeAlertModal()"
                class="text-gray-400 hover:text-white transition-colors flex-shrink-0 p-1 rounded-lg hover:bg-white/5"
                aria-label="Close">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Modal body — scrollable --}}
        <div class="overflow-y-auto flex-1 p-6 space-y-5">

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Incident Type</p>
                    <p id="modal-incident-type" class="text-white text-[13px] font-medium"></p>
                </div>
                <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Location</p>
                    <p id="modal-location" class="text-white text-[13px] font-medium"></p>
                </div>
                <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Date</p>
                    <p id="modal-datetime" class="text-white text-[13px] font-medium"></p>
                </div>
                <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Severity</p>
                    <p id="modal-severity" class="text-[13px] font-bold"></p>
                </div>
            </div>

            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Summary</p>
                <p id="modal-description" class="text-gray-300 text-[13.5px] leading-relaxed"></p>
            </div>

            <div id="modal-risk-section" class="hidden">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Risk Assessment</p>
                <p id="modal-risk" class="text-gray-300 text-[13.5px] leading-relaxed"></p>
            </div>

            <div id="modal-source-section" class="hidden">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Source</p>
                <a id="modal-source-link" href="#" target="_blank" rel="noopener noreferrer"
                    class="text-blue-400 hover:text-blue-300 text-[13px] underline break-all transition-colors"></a>
            </div>
        </div>

        {{-- Modal footer --}}
        <div class="px-6 py-4 border-t border-white/10 flex-shrink-0">
            <button onclick="closeAlertModal()"
                class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-[12px] font-semibold transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        const ALERTS = @json($activeAlerts ?? []);

        const LEVEL_COLORS = {
            Critical: {
                badge: 'bg-red-800 text-white',
                severity: 'text-red-500'
            },
            High: {
                badge: 'bg-red-600 text-white',
                severity: 'text-red-500'
            },
            Medium: {
                badge: 'bg-orange-500 text-white',
                severity: 'text-orange-400'
            },
            Low: {
                badge: 'bg-emerald-500 text-white',
                severity: 'text-emerald-400'
            },
        };

        window.openAlertModal = function(index) {
            const a = ALERTS[index];
            if (!a) return;

            const cfg = LEVEL_COLORS[a.impact_label] ?? LEVEL_COLORS.Low;

            const badge = document.getElementById('modal-level-badge');
            badge.className =
                `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3 ${cfg.badge}`;
            badge.innerHTML =
                `<span class="w-1.5 h-1.5 rounded-full bg-white/70 inline-block"></span>${a.impact_label}`;

            document.getElementById('modal-title').textContent = a.card_title ?? '';
            document.getElementById('modal-incident-type').textContent = a.riskindicators ?? '—';
            document.getElementById('modal-location').textContent = a.location_display ?? a.location ?? '—';
            document.getElementById('modal-datetime').textContent = a.formatted_date ?? 'Date unavailable';

            const severityEl = document.getElementById('modal-severity');
            severityEl.textContent = a.impact_label;
            severityEl.className = `text-[13px] font-bold ${cfg.severity}`;

            document.getElementById('modal-description').textContent = a.add_notes || '—';

            const riskSection = document.getElementById('modal-risk-section');
            const riskEl = document.getElementById('modal-risk');
            if (a.associated_risks) {
                riskEl.textContent = a.associated_risks;
                riskSection.classList.remove('hidden');
            } else {
                riskSection.classList.add('hidden');
            }

            const sourceSection = document.getElementById('modal-source-section');
            const sourceLink = document.getElementById('modal-source-link');
            if (a.link1) {
                sourceLink.href = a.link1;
                sourceLink.textContent = a.link1;
                sourceSection.classList.remove('hidden');
            } else {
                sourceSection.classList.add('hidden');
            }

            const modal = document.getElementById('alertModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        window.closeAlertModal = function() {
            const modal = document.getElementById('alertModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        };

        document.getElementById('alertModal').addEventListener('click', function(e) {
            if (e.target === this) window.closeAlertModal();
        });

        // Close on Escape (alongside any other Escape handlers in the page)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') window.closeAlertModal();
        });
    })();
</script>

{{-- INCIDENT MAP PARTIAL — resources/views/partials/news/incident-map.blade.php --}}

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
    /* ─── SECTION: sits below the sticky header (z-[1001]) ─────────────────── */
    /* position:relative + z-index:0 creates a new stacking context that is    */
    /* explicitly BELOW the header, so the map can never overlap it.           */
    #nri-incident-map-section {
        position: relative;
        z-index: 0;
    }

    /* ─── MAP CONTAINER ────────────────────────────────────────────────────── */
    /* overflow:visible on the wrapper so the floating popup card can          */
    /* extend beyond the map boundary. The map div itself has overflow:hidden  */
    /* to clip tile edges at the rounded corners.                              */
    #nri-map-wrapper {
        position: relative;
        overflow: visible;
        /* allows popup card to escape the box */
    }

    #incident-map {
        border-radius: 1rem;
        overflow: hidden;
    }

    /* ─── LEAFLET PANE Z-INDEXES ────────────────────────────────────────────── */
    /* We disable Leaflet's native popup entirely and use our own floating     */
    /* card (#nri-popup-card). These pane overrides ensure markers and         */
    /* clusters paint correctly above the tile layer.                          */
    #incident-map .leaflet-map-pane {
        z-index: 200 !important;
    }

    #incident-map .leaflet-tile-pane {
        z-index: 200 !important;
    }

    #incident-map .leaflet-overlay-pane {
        z-index: 201 !important;
    }

    #incident-map .leaflet-shadow-pane {
        z-index: 202 !important;
    }

    #incident-map .leaflet-marker-pane {
        z-index: 203 !important;
    }

    #incident-map .leaflet-tooltip-pane {
        z-index: 204 !important;
    }

    #incident-map .leaflet-control-container {
        z-index: 210 !important;
    }

    /* ─── RESTORE LEAFLET ZOOM BUTTONS (Tailwind preflight breaks them) ────── */
    #incident-map .leaflet-bar a,
    #incident-map .leaflet-bar a:hover {
        display: block !important;
        width: 30px !important;
        height: 30px !important;
        line-height: 30px !important;
        text-align: center !important;
        text-decoration: none !important;
        color: #1e2d3d !important;
        background: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    #incident-map .leaflet-bar a:hover {
        background: #f1f5f9 !important;
    }

    #incident-map .leaflet-bar a:first-child {
        border-radius: 6px 6px 0 0 !important;
    }

    #incident-map .leaflet-bar a:last-child {
        border-radius: 0 0 6px 6px !important;
        border-bottom: none !important;
    }

    #incident-map .leaflet-bar {
        border-radius: 6px !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.45) !important;
        border: none !important;
        overflow: hidden !important;
    }

    /* ─── FLOATING POPUP CARD ───────────────────────────────────────────────── */
    /* This replaces Leaflet's native popup entirely. It is a fixed-position   */
    /* card that sits above everything on the page (z-index:1200), with a      */
    /* smooth scale+fade animation on open, and is never clipped by the map.   */
    #nri-popup-card {
        position: fixed;
        z-index: 1200;
        /* above header (1001), above everything */
        width: 340px;
        background: #1e2d3d;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6), 0 4px 16px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        pointer-events: auto;
        transform-origin: bottom center;

        /* Hidden state */
        opacity: 0;
        transform: scale(0.92) translateY(8px);
        transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1),
            transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        display: none;
        /* toggled by JS */
    }

    #nri-popup-card.is-open {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    /* Header band */
    #nri-popup-header {
        padding: 11px 44px 11px 14px;
        /* right pad for close btn */
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.07em;
        color: #fff;
        position: relative;
        line-height: 1.3;
    }

    /* Close button */
    #nri-popup-close {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
        flex-shrink: 0;
        padding: 0;
    }

    #nri-popup-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    #nri-popup-close svg {
        display: block;
    }

    /* Body */
    #nri-popup-body {
        padding: 14px 16px 16px;
    }

    #nri-popup-title {
        font-size: 14px;
        font-weight: 600;
        color: #f8fafc;
        margin: 0 0 8px;
        line-height: 1.4;
    }

    .nri-popup-meta {
        font-size: 11.5px;
        color: #94a3b8;
        margin: 0 0 3px;
        display: flex;
        align-items: flex-start;
        gap: 5px;
    }

    #nri-popup-casualties {
        margin-top: 8px;
        font-size: 11.5px;
        font-weight: 600;
        color: #fca5a5;
        display: none;
    }

    #nri-popup-summary {
        margin-top: 10px;
        font-size: 12.5px;
        color: #cbd5e1;
        line-height: 1.65;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        padding-top: 10px;
        display: none;
    }

    #nri-popup-breaking {
        margin-top: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #fca5a5;
        display: none;
    }

    /* Connector line from card down toward the marker */
    #nri-popup-stem {
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 2px;
        height: 10px;
        background: rgba(255, 255, 255, 0.2);
        display: none;
        /* shown dynamically */
    }

    /* ─── MARKER HOVER EFFECT ───────────────────────────────────────────────── */
    .nri-marker-div {
        cursor: pointer;
        transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .nri-marker-div:hover {
        transform: scale(1.35);
    }

    /* ─── FILTER SELECT ─────────────────────────────────────────────────────── */
    #map-factor-filter {
        appearance: auto;
    }
</style>

{{-- ═══ FLOATING POPUP CARD (rendered in page body, outside map DOM) ═══════ --}}
{{-- Because this is a sibling of the map section — not a child — it can     --}}
{{-- never be clipped by the map container's overflow. Fixed positioning     --}}
{{-- means it's relative to the viewport, always visible, always on top.     --}}
<div id="nri-popup-card" role="dialog" aria-modal="true" aria-label="Incident details">
    <div id="nri-popup-header">
        <span id="nri-popup-factor"></span>
        <button id="nri-popup-close" aria-label="Close" onclick="nriClosePopup()">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.8" stroke-linecap="round" />
            </svg>
        </button>
    </div>
    <div id="nri-popup-body">
        <p id="nri-popup-title"></p>
        <p class="nri-popup-meta"><span>📍</span><span id="nri-popup-location"></span></p>
        <p class="nri-popup-meta"><span>📅</span><span id="nri-popup-date"></span></p>
        <p id="nri-popup-casualties"></p>
        <p id="nri-popup-summary"></p>
        <p id="nri-popup-breaking">🔴 Breaking News</p>
    </div>
    <div id="nri-popup-stem"></div>
</div>

<section id="nri-incident-map-section" class="max-w-7xl mx-auto px-6 lg:px-16 py-10">

    {{-- Header row --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl font-semibold text-primary">
                Live Incident Map
                <span class="ml-2 text-xs font-normal text-gray-400 tracking-wide uppercase">— past 7 days</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Markers are colour-coded by risk factor category. Click any marker for details.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <label for="map-factor-filter"
                class="text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">
                Filter
            </label>
            <select id="map-factor-filter"
                class="text-sm rounded-lg border border-gray-200 bg-white text-primary
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30
                           shadow-sm cursor-pointer min-w-[160px]">
                <option value="all">All factors</option>
            </select>
        </div>
    </div>

    {{-- Map wrapper — overflow:visible so popup card can escape the box --}}
    <div id="nri-map-wrapper" class="rounded-2xl border border-gray-200 shadow-md" style="background:#0a1628;">

        <div id="incident-map" style="height:480px;width:100%;position:relative;"></div>

        {{-- Loading overlay --}}
        <div id="map-loading-overlay"
            class="absolute inset-0 flex flex-col items-center justify-center gap-3 rounded-2xl"
            style="position:absolute;inset:0;background:rgba(10,22,40,0.88);z-index:220;border-radius:1rem;">
            <svg class="animate-spin h-8 w-8 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white/60 text-sm font-medium tracking-wide">Loading incidents…</span>
        </div>
    </div>

    {{-- Footer: legend + count --}}
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
        <div id="map-legend" class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-gray-600">
        </div>
        <p id="map-status" class="text-xs text-gray-400 shrink-0 sm:text-right">&nbsp;</p>
    </div>

</section>

{{-- Leaflet JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"
    crossorigin="anonymous"></script>

<script>
    (function() {
        'use strict';

        /* ── Floating popup helpers ─────────────────────────────────────────────── */
        var popupCard = document.getElementById('nri-popup-card');
        var activeMarkerEl = null; // track the last clicked marker DOM element

        // Position the fixed popup card so it appears just above the clicked marker.
        function nriPositionPopup(markerLatLng) {
            var pt = map.latLngToContainerPoint(markerLatLng);
            var mapEl = document.getElementById('incident-map');
            var rect = mapEl.getBoundingClientRect();

            var cardW = 340;
            var cardH = popupCard.offsetHeight || 220;

            // Default: centred above the marker
            var left = rect.left + pt.x - cardW / 2;
            var top = rect.top + pt.y - cardH - 24; // 24px gap above marker

            // Clamp horizontally within viewport with 12px margin
            left = Math.max(12, Math.min(left, window.innerWidth - cardW - 12));

            // If it would go above viewport, flip below the marker instead
            if (top < 60) { // 60px = safe clearance below sticky header
                top = rect.top + pt.y + 28;
            }

            popupCard.style.left = left + 'px';
            popupCard.style.top = top + 'px';
        }

        window.nriClosePopup = function() {
            popupCard.classList.remove('is-open');
            // After transition completes, hide from layout
            setTimeout(function() {
                popupCard.style.display = 'none';
            }, 220);
            activeMarkerEl = null;
        };

        function nriOpenPopup(p, markerLatLng) {
            // Populate header
            var factorText = p.factor_label.toUpperCase();
            if (p.indicator && p.indicator !== p.factor_label) {
                factorText += ' · ' + p.indicator.toUpperCase();
            }
            document.getElementById('nri-popup-factor').textContent = factorText;
            document.getElementById('nri-popup-header').style.background = p.factor_color;

            // Populate body
            document.getElementById('nri-popup-title').textContent =
                p.caption || 'Security Incident';

            document.getElementById('nri-popup-location').textContent = [p.lga, p.state].filter(Boolean).join(
                ', ') || 'Nigeria';

            document.getElementById('nri-popup-date').textContent = p.date || '';

            // Casualties
            var casEl = document.getElementById('nri-popup-casualties');
            if (p.casualties > 0 || p.injuries > 0) {
                var parts = [];
                if (p.casualties > 0) parts.push(p.casualties + ' killed');
                if (p.injuries > 0) parts.push(p.injuries + ' injured');
                casEl.textContent = '⚠ ' + parts.join(' · ');
                casEl.style.display = 'block';
            } else {
                casEl.style.display = 'none';
            }

            // Summary
            var sumEl = document.getElementById('nri-popup-summary');
            if (p.summary) {
                sumEl.textContent = p.summary.substring(0, 220) + (p.summary.length > 220 ? '…' : '');
                sumEl.style.display = 'block';
            } else {
                sumEl.style.display = 'none';
            }

            // Breaking news
            var brkEl = document.getElementById('nri-popup-breaking');
            brkEl.style.display = p.is_breaking ? 'block' : 'none';

            // Show card first (invisible) so offsetHeight is measurable, then position
            popupCard.style.display = 'block';
            popupCard.classList.remove('is-open');

            // Use rAF to let browser calculate layout before reading offsetHeight
            requestAnimationFrame(function() {
                nriPositionPopup(markerLatLng);
                requestAnimationFrame(function() {
                    popupCard.classList.add('is-open');
                });
            });
        }

        /* ── Close popup on Escape key ─────────────────────────────────────────── */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') window.nriClosePopup();
        });

        /* ── Map init ───────────────────────────────────────────────────────────── */
        var NG_BOUNDS = L.latLngBounds(L.latLng(4.0, 3.0), L.latLng(14.0, 15.1));

        var map = L.map('incident-map', {
            center: [9.082, 8.675],
            zoom: 6,
            minZoom: 5,
            maxZoom: 14,
            maxBounds: NG_BOUNDS,
            maxBoundsViscosity: 1.0,
            scrollWheelZoom: false,
            zoomControl: true,
        });

        map.on('click', function() {
            map.scrollWheelZoom.enable();
        });
        map.on('mouseout', function() {
            map.scrollWheelZoom.disable();
        });

        // Close popup when map is panned/zoomed (keeps UX clean)
        map.on('movestart zoomstart', function() {
            window.nriClosePopup();
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> &copy; <a href="https://carto.com/" target="_blank">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19,
        }).addTo(map);

        /* ── Cluster group ──────────────────────────────────────────────────────── */
        var clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 55,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            iconCreateFunction: function(cluster) {
                var n = cluster.getChildCount();
                var size = n > 30 ? 46 : n > 10 ? 38 : 30;
                return L.divIcon({
                    html: '<div style="background:#0a1628;border:2px solid rgba(255,255,255,0.25);' +
                        'border-radius:50%;width:' + size + 'px;height:' + size + 'px;' +
                        'display:flex;align-items:center;justify-content:center;' +
                        'font-weight:700;font-size:12px;color:#fff;' +
                        'box-shadow:0 3px 12px rgba(0,0,0,0.6);">' + n + '</div>',
                    className: '',
                    iconSize: [size, size],
                });
            },
        });

        /* ── Marker icon ────────────────────────────────────────────────────────── */
        function makeIcon(color, isBreaking) {
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
                '" stroke="#fff" stroke-width="2"' +
                ' style="filter:drop-shadow(0 2px 5px rgba(0,0,0,0.7));"/>' +
                '</svg>';
            return L.divIcon({
                className: 'nri-marker-div',
                html: svg,
                iconSize: [vb, vb],
                iconAnchor: [cx, cx],
                popupAnchor: [0, -(cx + 4)],
            });
        }

        /* ── State ──────────────────────────────────────────────────────────────── */
        var allFeatures = [];
        var activeFactor = 'all';

        /* ── Legend + filter ────────────────────────────────────────────────────── */
        function buildLegend(legend, present) {
            var legendEl = document.getElementById('map-legend');
            var filterEl = document.getElementById('map-factor-filter');
            legendEl.innerHTML = '';
            while (filterEl.options.length > 1) filterEl.remove(1);

            Object.keys(legend).forEach(function(key) {
                if (!present.has(key)) return;
                var cfg = legend[key];

                var pill = document.createElement('span');
                pill.className = 'flex items-center gap-1.5';
                pill.innerHTML =
                    '<span style="display:inline-block;width:11px;height:11px;border-radius:50%;' +
                    'background:' + cfg.color + ';flex-shrink:0;' +
                    'box-shadow:0 0 0 2px rgba(255,255,255,0.15);"></span>' +
                    '<span>' + cfg.label + '</span>';
                legendEl.appendChild(pill);

                var opt = document.createElement('option');
                opt.value = key;
                opt.textContent = cfg.label;
                filterEl.appendChild(opt);
            });
        }

        /* ── Render markers — uses custom floating popup, NOT Leaflet bindPopup ── */
        function renderMarkers() {
            clusterGroup.clearLayers();
            window.nriClosePopup();

            var subset = activeFactor === 'all' ?
                allFeatures :
                allFeatures.filter(function(f) {
                    return f.properties.factor === activeFactor;
                });

            subset.forEach(function(feature) {
                var p = feature.properties;
                var ll = L.latLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]);

                var marker = L.marker(ll, {
                    icon: makeIcon(p.factor_color, p.is_breaking),
                    title: p.caption || p.factor_label,
                });

                // Click → open floating popup card above the marker
                marker.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    nriOpenPopup(p, ll);
                });

                clusterGroup.addLayer(marker);
            });

            map.addLayer(clusterGroup);
        }

        /* ── Filter ─────────────────────────────────────────────────────────────── */
        document.getElementById('map-factor-filter').addEventListener('change', function() {
            activeFactor = this.value;
            renderMarkers();
            var shown = activeFactor === 'all' ?
                allFeatures.length :
                allFeatures.filter(function(f) {
                    return f.properties.factor === activeFactor;
                }).length;
            document.getElementById('map-status').textContent =
                shown + ' incident' + (shown !== 1 ? 's' : '') + ' shown';
        });

        /* ── Click outside popup closes it ─────────────────────────────────────── */
        document.addEventListener('click', function(e) {
            if (popupCard.style.display === 'block' &&
                !popupCard.contains(e.target) &&
                !e.target.closest('.nri-marker-div')) {
                window.nriClosePopup();
            }
        });

        /* ── Reposition popup on window resize ──────────────────────────────────── */
        window.addEventListener('resize', function() {
            if (popupCard.style.display === 'block' && activeMarkerEl) {
                window.nriClosePopup();
            }
        });

        /* ── Load data ──────────────────────────────────────────────────────────── */
        function loadMapData() {
            fetch('{{ route('incidents.geojson') }}')
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(geojson) {
                    var overlay = document.getElementById('map-loading-overlay');
                    if (overlay) overlay.style.display = 'none';

                    allFeatures = geojson.features || [];
                    var legend = geojson.legend || {};

                    if (allFeatures.length === 0) {
                        document.getElementById('map-status').textContent =
                            'No incidents recorded in the past 7 days.';
                        return;
                    }

                    var present = new Set(allFeatures.map(function(f) {
                        return f.properties.factor;
                    }));
                    buildLegend(legend, present);
                    renderMarkers();

                    document.getElementById('map-status').textContent =
                        allFeatures.length + ' incident' + (allFeatures.length !== 1 ? 's' : '') +
                        ' in the past 7 days';
                })
                .catch(function(err) {
                    var overlay = document.getElementById('map-loading-overlay');
                    if (overlay) overlay.style.display = 'none';
                    document.getElementById('map-status').textContent =
                    'Map data unavailable — please refresh.';
                    console.error('[NRI map]', err);
                });
        }

        loadMapData();
        setInterval(loadMapData, 10 * 60 * 1000);

    })();
</script>

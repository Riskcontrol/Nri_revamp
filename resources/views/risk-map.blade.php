<x-layout title="Nigeria Risk Map">

    {{-- Add Chart.js for the dynamic charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-full mx-auto p-4 lg:p-8 space-y-6">

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-2/3 bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6">
                <div class="flex flex-col md:flex-row md:items-end gap-6">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            Risk Filters
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Year Filter --}}
                            <div class="relative">
                                <label for="filter-year"
                                    class="block text-xs font-medium text-gray-400 mb-1 uppercase">Year</label>
                                <select id="filter-year" name="year"
                                    class="block w-full bg-gray-700 border-gray-600 text-white rounded-md p-2 text-sm focus:ring-blue-500 cursor-pointer">
                                    @php
                                        $currentYear = date('Y');
                                        $startYear = 2018;
                                    @endphp
                                    @for ($year = $currentYear; $year >= $startYear; $year--)
                                        <option value="{{ $year }}"
                                            @if ($year == $currentYear) selected @endif>{{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Risk Index Filter --}}
                            <div class="relative">
                                <label for="filter-risk"
                                    class="block text-xs font-medium text-gray-400 mb-1 uppercase">Risk Index</label>
                                <select id="filter-risk" name="risk_type"
                                    class="block w-full bg-gray-700 border-gray-600 text-white rounded-md p-2 text-sm focus:ring-blue-500 cursor-pointer">
                                    @php
                                        $tier = auth()->user()->tier ?? 0;
                                        $tier2 = $tier === 1; // your Tier2/free lock logic
                                    @endphp

                                    <option value="Terrorism" data-premium="0">Terrorism</option>

                                    <option value="Kidnapping" data-premium="1">Kidnapping (Premium)</option>
                                    <option value="Crime" data-premium="1">Operational Risk (Premium)</option>
                                    <option value="Property-Risk" data-premium="1">Property Risk (Premium)</option>
                                </select>

                            </div>

                            {{-- Comparison Mode --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">Comparison
                                    Mode</label>
                                <div class="flex items-center space-x-3 bg-gray-700 p-2 rounded-md">
                                    <button id="toggle-comparison"
                                        class="px-3 py-1 text-xs font-bold text-white bg-gray-600 rounded hover:bg-gray-500 transition">OFF</button>
                                    <button id="clear-selection"
                                        class="hidden text-xs text-red-400 hover:text-red-300 underline">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="md:w-48">
                        <button id="apply-filters"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 text-sm">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="lg:w-1/3 bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6 flex flex-col justify-center">
                <h3 class="text-xs font-medium text-gray-400 uppercase tracking-wide">Active Threat Groups</h3>
                <p id="card-top-threats" class="text-lg font-semibold text-white mt-2 leading-relaxed">
                    Loading...
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-2/3">
                <section class="bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 id="map-title" class="text-xl font-bold text-white">Nigeria Risk Map</h2>
                        <p class="text-xs text-yellow-400 italic animate-pulse">Click a state for details</p>
                    </div>

                    <div id="risk-map" class="w-full"
                        style="height: 600px; background-color: #1E2D3D; border-radius: 8px;">
                        <div id="map-loader" class="flex items-center justify-center h-full">
                            <p class="text-white text-lg">Loading Map...</p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="lg:w-1/3">
                <section id="comparison-container"
                    class="hidden h-full bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">State Comparison</h3>
                    <div class="relative" style="height: 500px;">
                        <canvas id="comparisonBarChart"></canvas>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-4">Select multiple states in Comparison Mode to populate this
                        chart.</p>
                </section>

                <div id="chart-placeholder"
                    class="flex items-center justify-center h-full border-2 border-dashed border-gray-700 rounded-lg p-6 text-center">
                    <p class="text-gray-500 text-sm">Select states to view comparison data</p>
                </div>
            </div>
        </div>
    </div>
    <x-auth-required-modal />
    <x-tier-lock-modal />



    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {



            const IS_AUTH = @json(auth()->check());
            const REGISTER_URL = @json(route('register'));

            function showAuthModalAndRedirect() {
                const modal = document.getElementById('authRequiredModal');
                const closeBtn = document.getElementById('authModalClose');
                const registerBtn = document.getElementById('authModalRegisterBtn');

                if (!modal) {
                    window.location.href = REGISTER_URL;
                    return;
                }

                // show
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                const cleanup = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    closeBtn?.removeEventListener('click', onClose);
                    registerBtn?.removeEventListener('click', onRegister);
                    modal?.removeEventListener('click', onBackdrop);
                    document.removeEventListener('keydown', onEsc);
                };

                const onClose = () => cleanup();

                const onRegister = () => {
                    window.location.href = REGISTER_URL;
                };

                // close when clicking backdrop (optional)
                const onBackdrop = (e) => {
                    if (e.target === modal) cleanup();
                };

                const onEsc = (e) => {
                    if (e.key === 'Escape') cleanup();
                };

                closeBtn?.addEventListener('click', onClose);
                registerBtn?.addEventListener('click', onRegister);
                modal?.addEventListener('click', onBackdrop);
                document.addEventListener('keydown', onEsc);
            }

            // ========= Tier Lock Modal (Risk Map) =========
            function openTierLockModal(payload = {}) {
                const modal = document.getElementById("tierLockModal");
                if (!modal) return;

                const titleEl = document.getElementById("tierLockTitle");
                const subtitleEl = document.getElementById("tierLockSubtitle");
                const msgEl = document.getElementById("tierLockMessage");

                const label1El = document.getElementById("tierLockLabel1");
                const label2El = document.getElementById("tierLockLabel2");
                const lockedEl = document.getElementById("tierLockLocation");
                const whenEl = document.getElementById("tierLockWhen");

                const footerEl = document.getElementById("tierLockFooterText");
                const ctaEl = document.getElementById("tierLockCta");

                // Defaults that match your modal copy
                const title = payload.title || "Locked";
                const subtitle = payload.subtitle || "This option is not available on your plan.";
                const message = payload.message || "";

                // These map to your modal fields
                const label1 = payload.label1 || "Locked item";
                const label2 = payload.label2 || "Next switch";
                const lockedItem = payload.locked_item || payload.locked_location || "";
                const whenText = payload.when || payload.switch_available_at || "Upgrade to unlock";

                const footer = payload.footer || "Upgrade to unlock premium access.";
                const ctaUrl = payload.cta_url || "#";

                if (titleEl) titleEl.textContent = title;
                if (subtitleEl) subtitleEl.textContent = subtitle;
                if (msgEl) msgEl.textContent = message;

                if (label1El) label1El.textContent = label1;
                if (label2El) label2El.textContent = label2;
                if (lockedEl) lockedEl.textContent = lockedItem;
                if (whenEl) whenEl.textContent = whenText;

                if (footerEl) footerEl.textContent = footer;
                if (ctaEl) ctaEl.href = ctaUrl;

                modal.classList.remove("hidden");
                modal.classList.add("flex");
                document.body.style.overflow = "hidden";
            }

            function closeTierLockModal() {
                const modal = document.getElementById("tierLockModal");
                if (!modal) return;

                modal.classList.add("hidden");
                modal.classList.remove("flex");
                document.body.style.overflow = "";
            }

            // wire close buttons/backdrop/esc
            document.getElementById("tierLockClose")?.addEventListener("click", closeTierLockModal);
            document.getElementById("tierLockOk")?.addEventListener("click", closeTierLockModal);

            document.getElementById("tierLockModal")?.addEventListener("click", function(e) {
                if (e.target === this) closeTierLockModal();
            });

            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") closeTierLockModal();
            });

            // If you flashed a tier lock from server session, open it automatically
            if (window.__TIER_LOCK_FLASH__) {
                openTierLockModal(window.__TIER_LOCK_FLASH__);
            }



            // Generic guard wrapper
            function requireAuth(actionFn) {
                return function(...args) {
                    if (!IS_AUTH) {
                        showAuthModalAndRedirect();
                        return;
                    }
                    return actionFn.apply(this, args);
                };
            }


            // --- 1. SET UP STATE ---
            let isComparisonMode = false;
            let selectedStates = [];
            let comparisonChart = null;
            let geoJsonLayer = null;
            let legend = null;
            let chartInstances = {};

            // --- 2. SELECT ELEMENTS ---
            const toggleBtn = document.getElementById('toggle-comparison');
            const clearBtn = document.getElementById('clear-selection');
            const comparisonContainer = document.getElementById('comparison-container');
            const mapContainer = document.getElementById('risk-map');
            const loader = document.getElementById('map-loader');
            const yearSelect = document.getElementById('filter-year');
            const riskSelect = document.getElementById('filter-risk');
            const applyButton = document.getElementById('apply-filters');
            const threatCard = document.getElementById('card-top-threats');

            const USER_TIER = @json(auth()->user()?->tier); // null if guest
            const IS_TIER2 = USER_TIER !== null && parseInt(USER_TIER, 10) === 1; // your tier lock rule

            let lastAllowedRisk = riskSelect?.value || "Terrorism";
            let lastAllowedYear = yearSelect?.value || "";

            // helper: build tier-lock payload that your modal expects
            function openRiskTierLock() {
                const selectedText =
                    riskSelect.options[riskSelect.selectedIndex]?.text || "Premium Risk Index";

                openTierLockModal({
                    title: "Premium Feature",
                    subtitle: "This Risk Map is locked on your plan.",
                    message: "Upgrade to Premium to access this Risk Index on the Risk Map.",

                    label1: "Locked risk map",
                    locked_item: selectedText,

                    label2: "How to unlock",
                    when: "Upgrade to Premium",

                    footer: "Premium unlocks Kidnapping, Crime and Property Risk on the Risk Map.",
                    // Put your real route here when ready:
                    cta_url: "/enterprise-access"
                });
            }

            // IMPORTANT: if user tries premium, revert + stop loading states
            function revertRiskSelection() {
                if (riskSelect) riskSelect.value = lastAllowedRisk;
                if (yearSelect) yearSelect.value = lastAllowedYear;
                // ensure button/loader aren’t stuck
                setLoading(false);
            }

            // Risk select lock logic
            riskSelect?.addEventListener("change", function() {
                const selectedOption = riskSelect.options[riskSelect.selectedIndex];
                const isPremium = selectedOption?.dataset?.premium === "1";

                // guest: show auth modal + revert
                if (!IS_AUTH && isPremium) {
                    showAuthModalAndRedirect();
                    revertRiskSelection();
                    return;
                }

                // tier2/free: allow Terrorism only, lock premium options
                if (IS_AUTH && IS_TIER2 && isPremium) {
                    openRiskTierLock();
                    revertRiskSelection();
                    return;
                }

                // ✅ allowed
                lastAllowedRisk = riskSelect.value;
            });

            // Optional: also lock year switching for Tier2 if you want “current year only”
            yearSelect?.addEventListener("change", function() {
                const selectedYear = parseInt(yearSelect.value, 10);

                if (!IS_AUTH) {
                    // guests can still view preview; if you want to block year changes for guests:
                    // showAuthModalAndRedirect(); yearSelect.value = lastAllowedYear; return;
                    lastAllowedYear = yearSelect.value;
                    return;
                }

                if (IS_TIER2) {
                    // If Tier2 should only see current year, block others:
                    // Replace 2026 with your computed server year if needed
                    const CURRENT_YEAR = @json((int) date('Y'));
                    if (selectedYear !== CURRENT_YEAR) {
                        if (typeof openTierLockModal === "function") {
                            openTierLockModal({
                                upgrade: true,
                                message: "Historical years are available on Premium.",
                                locked_location: "Historical Data",
                                switch_available_at: "Upgrade to unlock",
                                allowed: {
                                    year: CURRENT_YEAR
                                }
                            });
                        }
                        yearSelect.value = lastAllowedYear; // revert
                        setLoading(false);
                        return;
                    }
                }

                lastAllowedYear = yearSelect.value;
            });


            // --- 3. INITIALIZE MAP ---
            var map = L.map('risk-map').setView([9.0820, 8.6753], 6);
            map.scrollWheelZoom.disable();
            map.on('click', () => map.scrollWheelZoom.enable());
            map.on('mouseout', () => map.scrollWheelZoom.disable());

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            // --- 4. COMPARISON LOGIC ---
            toggleBtn.addEventListener('click', requireAuth(() => {
                isComparisonMode = !isComparisonMode;
                toggleBtn.textContent = isComparisonMode ? 'ON' : 'OFF';
                toggleBtn.classList.toggle('bg-blue-600', isComparisonMode);
                toggleBtn.classList.toggle('bg-gray-600', !isComparisonMode);
                if (!isComparisonMode) resetComparison();
            }));

            function resetComparison() {
                selectedStates = [];
                comparisonContainer.classList.add('hidden');
                clearBtn.classList.add('hidden');
                if (comparisonChart) {
                    comparisonChart.destroy();
                    comparisonChart = null;
                }
                if (geoJsonLayer) {
                    geoJsonLayer.eachLayer(layer => geoJsonLayer.resetStyle(layer));
                }
            }

            clearBtn.addEventListener('click', resetComparison);

            // Function moved INSIDE scope to access selectedStates
            function updateComparisonChart() {
                const placeholder = document.getElementById('chart-placeholder');
                if (selectedStates.length < 1 || !isComparisonMode) {
                    comparisonContainer.classList.add('hidden');
                    placeholder.classList.remove('hidden'); // Show the dashed box
                    clearBtn.classList.add('hidden');
                    return;
                }
                placeholder.classList.add('hidden');
                comparisonContainer.classList.remove('hidden');
                clearBtn.classList.remove('hidden');

                const riskText = riskSelect.options[riskSelect.selectedIndex].text;
                const comparisonTitle = document.querySelector('#comparison-container h3');
                comparisonTitle.textContent = `${riskText} Comparison - ${yearSelect.value}`;

                const ctx = document.getElementById('comparisonBarChart').getContext('2d');
                const labels = selectedStates.map(s => s.name);
                const data = selectedStates.map(s => s.score);

                if (comparisonChart) {
                    comparisonChart.data.labels = labels;
                    comparisonChart.data.datasets[0].data = data;
                    comparisonChart.update();
                } else {
                    comparisonChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Risk Score',
                                data: data,
                                backgroundColor: '#3b82f6',
                                borderRadius: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#374151'
                                    },
                                    ticks: {
                                        color: '#9ca3af',
                                        callback: function(value) {
                                            return Number(value).toFixed(2);
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#fff'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                    titleColor: '#fff',
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyColor: '#d1d5db',
                                    bodyFont: {
                                        size: 12
                                    },
                                    padding: 12,
                                    cornerRadius: 8,
                                    borderColor: '#4b5563',
                                    borderWidth: 1,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            const item = selectedStates[context.dataIndex];
                                            return ` Risk Score: ${item.score.toFixed(2)}`;
                                        },
                                        afterBody: function(context) {
                                            const item = selectedStates[context[0].dataIndex];
                                            return [
                                                ` Tracked Incidents: ${item.count}`,
                                                ` Top Affected LGA: ${item.lga}`,
                                                ` Prev. Year: ${item.prevCount}`
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // --- 5. STYLING & HELPERS ---
            function getColor(riskLevel) {
                const colors = {
                    4: '#B10026',
                    3: '#E31A1C',
                    2: '#FEB24C',
                    1: '#FFEDA0'
                };
                return colors[riskLevel] || '#969696';
            }

            function style(feature) {
                return {
                    fillColor: getColor(feature.properties.risk_level),
                    weight: 1,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.7
                };
            }

            function onEachFeature(feature, layer) {
                const props = feature.properties;
                const stateName = props.name || 'Unknown State';

                // 1. Label/Tooltip Styling
                layer.bindTooltip(stateName, {
                    permanent: true,
                    direction: 'center',
                    className: 'state-label'
                });

                // 2. Trend Calculations for Popup
                const safeId = stateName.replace(/[^a-zA-Z0-9]/g, '-');
                const chartCanvasId = `chart-${safeId}`;

                let trendIcon = '';
                let trendClass = 'text-gray-400';
                let trendText = 'No Change';

                if (props.incidents_count > props.incidents_prev_year) {
                    trendIcon = '▲';
                    trendClass = 'text-red-500';
                    trendText = 'Increased';
                } else if (props.incidents_count < props.incidents_prev_year) {
                    trendIcon = '▼';
                    trendClass = 'text-green-500';
                    trendText = 'Decreased';
                }

                let filterName = props.filter_risk_type === 'All' ? 'Composite' :
                    props.filter_risk_type === 'Property-Risk' ? 'Property' : props.filter_risk_type;

                // 3. Refined Popup HTML - Always show (Auth removed)
                const popupContent = `
        <div class="p-4 antialiased text-gray-200 bg-[#1E2D3D] rounded-lg shadow-2xl border border-gray-700" style="width: 320px;">
            <div class="flex items-center justify-between mb-3 border-b border-gray-700 pb-2">
                <h3 class="text-lg font-bold text-white tracking-tight">${stateName}</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-700 text-gray-300">
                    ${props.current_year} Data
                </span>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-1 space-y-3">
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-gray-500 tracking-wider mb-0.5">${filterName} Score</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-white">${props.composite_index_score.toFixed(2)}</span>
                            <span class="text-[11px] font-bold ${trendClass}">${trendIcon} ${trendText}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-semibold text-gray-500 tracking-wider mb-0.5">Most Affected LGA</p>
                        <p class="text-sm font-medium text-blue-400">${props.most_affected_lga || 'N/A'}</p>
                    </div>
                </div>

               <div class="space-y-3 border-l border-gray-700 pl-4">
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-gray-500 tracking-wider mb-0.5">Incidents (${props.current_year})</p>
                        <p class="text-xl font-bold text-white">${props.incidents_count}</p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-semibold text-gray-500 tracking-wider mb-0.5">Prev. Year (${props.previous_year})</p>
                        <p class="text-xl font-bold text-gray-400">${props.incidents_prev_year}</p>
                    </div>
                </div>
            </div>

            ${isComparisonMode ?
                `<p class="mt-3 text-[10px] text-center text-emerald-400 italic font-medium animate-pulse">Click to toggle comparison</p>` :
                ''
            }
        </div>
    `;

                layer.bindPopup(popupContent, {
                    autoPan: false, // Prevents map from panning to show popup
                    closeButton: false,
                    offset: L.point(0, -10),
                    className: 'custom-leaflet-popup',
                    autoPanPadding: [0, 0], // No padding
                    keepInView: false // Don't force popup to stay in view
                });

                // 4. Click Logic & Selection Styling
                // --- HOVER POPUP (Normal mode) ---
                layer.on('mouseover', function() {
                    if (!isComparisonMode) {
                        layer.setStyle({
                            weight: 3,
                            color: '#60a5fa',
                            fillOpacity: 0.85,
                            dashArray: ''
                        });
                        layer.bringToFront();
                        layer.openPopup();
                    }
                });

                layer.on('mouseout', function() {
                    if (!isComparisonMode) {
                        geoJsonLayer.resetStyle(layer);
                        layer.closePopup();
                    }
                });

                // --- CLICK (Comparison mode only) ---
                layer.on('click', function(e) {
                    if (!IS_AUTH) {
                        showAuthModalAndRedirect();
                        return;
                    }
                    if (!isComparisonMode) return;

                    layer.closePopup();
                    L.DomEvent.stopPropagation(e);

                    const index = selectedStates.findIndex(s => s.name === stateName);

                    if (index > -1) {
                        selectedStates.splice(index, 1);
                        geoJsonLayer.resetStyle(layer);
                    } else {
                        selectedStates.push({
                            name: stateName,
                            score: Number(props.composite_index_score || 0),
                            count: props.incidents_count,
                            lga: props.most_affected_lga || 'N/A',
                            prevCount: props.incidents_prev_year
                        });

                        layer.setStyle({
                            weight: 4,
                            color: '#10b981',
                            fillColor: '#059669',
                            fillOpacity: 0.8,
                            dashArray: ''
                        });

                        layer.bringToFront();
                    }

                    updateComparisonChart();
                });

            }

            // --- 6. DATA FETCHING ---
            function setLoading(isLoading) {
                loader.style.display = isLoading ? 'flex' : 'none';
                applyButton.disabled = isLoading;
                applyButton.textContent = isLoading ? 'Applying...' : 'Apply Filters';
            }

            function fetchMapData() {
                resetComparison();
                setLoading(true);

                const year = yearSelect.value;
                const risk = riskSelect.value;

                const mapApiUrl = `/api/risk-map-data?year=${year}&risk_type=${risk}`;
                const cardApiUrl = `/api/risk-map-card-data?year=${year}&risk_type=${risk}`;

                // MAP DATA
                fetch(mapApiUrl)
                    .then(async (res) => {
                        if (res.status === 401) {
                            await res.json().catch(() => null);
                            showAuthModalAndRedirect();
                            throw new Error('Auth required');
                        }
                        if (!res.ok) throw new Error(`Map API error: ${res.status}`);
                        return res.json(); // geojson object
                    })
                    .then((geojsonData) => {
                        if (geoJsonLayer) map.removeLayer(geoJsonLayer);

                        geoJsonLayer = L.geoJson(geojsonData, {
                            style: style,
                            onEachFeature: onEachFeature,
                        }).addTo(map);

                        setLoading(false);
                    })
                    .catch((err) => {
                        console.error('[risk-map] map fetch failed:', err);
                        setLoading(false);
                    });

                // CARD DATA
                fetch(cardApiUrl)
                    .then(async (res) => {
                        if (res.status === 401) {
                            await res.json().catch(() => null);
                            showAuthModalAndRedirect();
                            throw new Error('Auth required');
                        }
                        if (!res.ok) throw new Error(`Card API error: ${res.status}`);
                        return res.json();
                    })
                    .then((cardData) => {
                        threatCard.textContent = cardData?.topThreatGroups ?? 'N/A';
                    })
                    .catch((err) => {
                        console.error('[risk-map] card fetch failed:', err);
                        threatCard.textContent = 'Data Unavailable';
                    });
            }

            function renderGeojson(geojsonData) {
                if (geoJsonLayer) map.removeLayer(geoJsonLayer);

                geoJsonLayer = L.geoJson(geojsonData, {
                    style: style,
                    onEachFeature: onEachFeature,
                }).addTo(map);

                // Optional: fit bounds once
                try {
                    map.fitBounds(geoJsonLayer.getBounds(), {
                        padding: [10, 10]
                    });
                } catch (e) {}
            }

            function fetchPreview() {
                resetComparison();
                setLoading(true);

                // Optional: set dropdowns to preview defaults visually
                // yearSelect.value = new Date().getFullYear();
                // riskSelect.value = 'All'; // you don't have 'All' option, so keep Terrorism or add All option if needed

                const previewMapUrl = `/api/risk-map-preview`;
                const previewCardUrl = `/api/risk-map-preview-card`;

                fetch(previewMapUrl)
                    .then(res => {
                        if (!res.ok) throw new Error(`Preview map failed: ${res.status}`);
                        return res.json();
                    })
                    .then(geojson => {
                        renderGeojson(geojson);
                        setLoading(false);
                    })
                    .catch(err => {
                        console.error('[risk-map] preview map failed:', err);
                        setLoading(false);
                    });

                fetch(previewCardUrl)
                    .then(res => {
                        if (!res.ok) throw new Error(`Preview card failed: ${res.status}`);
                        return res.json();
                    })
                    .then(cardData => {
                        threatCard.textContent = cardData?.topThreatGroups ?? 'N/A';
                    })
                    .catch(err => {
                        console.error('[risk-map] preview card failed:', err);
                        threatCard.textContent = 'Data Unavailable';
                    });
            }



            applyButton.addEventListener("click", function() {
                const selectedOption = riskSelect.options[riskSelect.selectedIndex];
                const isPremium = selectedOption?.dataset?.premium === "1";

                // guest trying premium
                if (!IS_AUTH && isPremium) {
                    showAuthModalAndRedirect();
                    revertRiskSelection();
                    return;
                }

                // tier2/free trying premium
                if (IS_AUTH && IS_TIER2 && isPremium) {
                    openRiskTierLock();
                    revertRiskSelection();
                    return;
                }

                // ✅ allowed
                fetchMapData();
            });

            if (IS_AUTH) {
                fetchMapData();
            } else {
                fetchPreview();
            }

        });
    </script>

    {{-- CSS for legend and state labels --}}
    <style>
        .info.legend {
            background: rgba(30, 45, 61, 0.8);
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            line-height: 1.5;
            z-index: 999;
        }

        .info.legend i {
            width: 18px;
            height: 18px;
            float: left;
            margin-right: 8px;
            opacity: 0.9;
            border: 1px solid #fff;
        }

        .state-label {
            background: transparent;
            border: none;
            box-shadow: none;
            color: #ffffff;
            font-size: 10px;
            font-weight: 500;
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
            pointer-events: none !important;
        }


        .leaflet-tooltip-bottom:before,
        .leaflet-tooltip-top:before,
        .leaflet-tooltip-left:before,
        .leaflet-tooltip-right:before {
            display: none !important;
        }

        .leaflet-popup-content-wrapper {
            background: rgba(30, 45, 61, 0.95);
            color: #fff;
            border-radius: 8px;
            padding: 0;
        }

        .leaflet-popup-content {
            margin: 0;
        }

        .leaflet-popup-tip {
            background: rgba(30, 45, 61, 0.95);
        }

        .leaflet-container {
            transform: translate3d(0, 0, 0);
        }

        /* =====================================================
            LEAFLET POPUP VISIBILITY FIX
            Ensures popups are never clipped while preserving controls
            ===================================================== */

        /* Elevate popup pane only */
        .leaflet-popup-pane {
            z-index: 10000 !important;
        }

        /* Ensure popup wrapper is above everything else */
        .leaflet-popup {
            z-index: 10001 !important;
        }

        /* Prevent parent containers from clipping popups */
        #risk-map {
            overflow: visible !important;
        }

        /* Restore and elevate Leaflet controls (zoom, attribution) */
        .leaflet-top,
        .leaflet-bottom {
            z-index: 11000 !important;
        }

        /* Ensure controls remain interactive */
        .leaflet-control {
            z-index: 11001 !important;
            pointer-events: auto;
        }

        /* Avoid stacking context issues caused by transforms */
        .leaflet-container {
            transform: none !important;
        }
    </style>

</x-layout>

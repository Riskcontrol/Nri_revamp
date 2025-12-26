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
                                <select id="filter-year" name="year" onmousedown="return checkAccess(event)"
                                    class="block w-full bg-gray-700 border-gray-600 text-white rounded-md p-2 text-sm focus:ring-blue-500 cursor-pointer">
                                    @php
                                        $currentYear = 2025;
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
                                <select id="filter-risk" name="risk_type" onmousedown="return checkAccess(event)"
                                    class="block w-full bg-gray-700 border-gray-600 text-white rounded-md p-2 text-sm focus:ring-blue-500 cursor-pointer">
                                    <option value="Terrorism">Terrorism</option>
                                    <option value="Kidnapping">Kidnapping</option>
                                    <option value="Crime">Crime</option>
                                    <option value="Homicide">Homicide</option>
                                    <option value="Property-Risk">Property Risk</option>
                                </select>

                            </div>

                            {{-- Comparison Mode --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">Comparison
                                    Mode</label>
                                <div class="flex items-center space-x-3 bg-gray-700 p-2 rounded-md">
                                    <button id="toggle-comparison" onclick="checkAccess(event)"
                                        class="px-3 py-1 text-xs font-bold text-white bg-gray-600 rounded hover:bg-gray-500 transition">OFF</button>
                                    <button id="clear-selection"
                                        class="hidden text-xs text-red-400 hover:text-red-300 underline">Clear</button>
                                    @guest <i class="fa-solid fa-lock text-[10px] text-gray-500"></i> @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="md:w-48">
                        <button id="apply-filters" onclick="checkAccess(event)"
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

    <div id="auth-modal"
        class="fixed inset-0 z-[2000] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div
            class="bg-[#1E2D3D] border border-white/10 w-full max-w-md rounded-3xl p-8 shadow-2xl relative text-center">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <div id="modal-register-state" class="{{ Auth::check() ? 'hidden' : '' }}">
                <h3 class="text-2xl font-bold text-white mb-4">Interactive Map Locked</h3>
                <p class="text-gray-400 mb-8">Granular LGA data and comparison filters are reserved for professional
                    organizations.</p>
                <a href="{{ url('/register') }}"
                    class="block w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl">Register for
                    Access</a>
            </div>

            <div id="modal-success-state" class="{{ Auth::check() && Auth::user()->access_level < 1 ? '' : 'hidden' }}">
                <h3 class="text-2xl font-bold text-white mb-4">Verification Pending</h3>
                <p class="text-gray-400">Our analysts are reviewing your organization's request. We will contact you
                    shortly to authorize full map access.</p>
                <button onclick="closeAuthModal()"
                    class="mt-8 w-full border border-white/10 text-white py-3 rounded-xl">Continue Browsing</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        function hasProfessionalAccess() {
            // These values are injected by Blade from the server session
            const isAuth = {{ Auth::check() ? 'true' : 'false' }};
            const accessLevel = {{ Auth::check() ? Auth::user()->access_level : 0 }};
            return isAuth && accessLevel >= 1;
        }

        function checkAccess(event) {
            if (!hasProfessionalAccess()) {
                // Stop the click/mousedown from completing
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // Show your custom modal
                openAuthModal();

                // Return false to stop dropdowns specifically
                return false;
            }
            return true;
        }

        function openAuthModal() {
            document.getElementById('auth-modal').classList.remove('hidden');
        }

        // Function to close modal
        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }
        document.addEventListener("DOMContentLoaded", function() {

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

            [yearSelect, riskSelect, toggleBtn].forEach(el => {
                el.addEventListener('mousedown', (e) => {
                    if (!hasProfessionalAccess()) {
                        e.preventDefault(); // Stop the dropdown from opening
                        openAuthModal();
                    }
                });
            });

            // --- 4. COMPARISON LOGIC ---
            toggleBtn.addEventListener('click', () => {
                isComparisonMode = !isComparisonMode;
                toggleBtn.textContent = isComparisonMode ? 'ON' : 'OFF';
                toggleBtn.classList.toggle('bg-blue-600', isComparisonMode);
                toggleBtn.classList.toggle('bg-gray-600', !isComparisonMode);
                if (!isComparisonMode) resetComparison();
            });

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
                const data = selectedStates.map(s => s.count);

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
                                label: 'Number of Incidents',
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
                                        color: '#9ca3af'
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
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)', // Dark tailwind-style grey
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
                                    displayColors: false, // Removes the little square icon
                                    callbacks: {
                                        // Change the main label text
                                        label: function(context) {
                                            const item = selectedStates[context.dataIndex];
                                            return ` Current Tracked Incidents: ${item.count}`;
                                        },
                                        // Add extra info at the bottom of the tooltip
                                        afterBody: function(context) {
                                            const item = selectedStates[context[0].dataIndex];
                                            return [
                                                ` Risk Score: ${item.score.toFixed(2)}`,
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

                if (hasProfessionalAccess()) {
                    // 3. Refined Popup HTML
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
                        closeButton: false,
                        offset: L.point(0, -10),
                        className: 'custom-leaflet-popup'
                    });
                }

                // 4. Click Logic & Selection Styling
                layer.on('click', function(e) {
                    if (!hasProfessionalAccess()) {
                        L.DomEvent.stopPropagation(e); // Stop the map from zooming
                        openAuthModal();
                        return;
                    }
                    if (isComparisonMode) {
                        layer.closePopup();
                        L.DomEvent.stopPropagation(e);

                        const index = selectedStates.findIndex(s => s.name === stateName);
                        if (index > -1) {
                            // DESELECT STYLE
                            selectedStates.splice(index, 1);
                            geoJsonLayer.resetStyle(layer);
                        } else {
                            // SELECT STYLE (Professional Highlight)
                            selectedStates.push({
                                name: stateName,
                                count: props.incidents_count,
                                score: props.composite_index_score, // Pass the score
                                lga: props.most_affected_lga || 'N/A', // Pass the LGA
                                prevCount: props.incidents_prev_year
                            });
                            layer.setStyle({
                                weight: 4,
                                color: '#10b981', // Emerald 500
                                fillColor: '#059669', // Emerald 600
                                fillOpacity: 0.8,
                                dashArray: ''
                            });
                            layer.bringToFront(); // Bring selected state border to top
                        }
                        updateComparisonChart();
                    }
                });


            }

            // --- 6. DATA FETCHING ---
            function fetchMapData() {
                resetComparison(); // Clear chart if we change filters
                loader.style.display = 'flex';
                applyButton.disabled = true;
                applyButton.textContent = 'Applying...';

                const year = yearSelect.value;
                const risk = riskSelect.value;
                const mapApiUrl = `/api/risk-map-data?year=${year}&risk_type=${risk}`;
                const cardApiUrl = `/api/risk-map-card-data?year=${year}&risk_type=${risk}`;

                fetch(mapApiUrl)
                    .then(res => res.json())
                    .then(geojsonData => {
                        if (geoJsonLayer) map.removeLayer(geoJsonLayer);
                        geoJsonLayer = L.geoJson(geojsonData, {
                            style: style,
                            onEachFeature: onEachFeature
                        }).addTo(map);

                        loader.style.display = 'none';
                        applyButton.disabled = false;
                        applyButton.textContent = 'Apply Filters';
                    });

                fetch(cardApiUrl)
                    .then(res => res.json())
                    .then(cardData => {
                        threatCard.textContent = cardData.topThreatGroups;
                    });
            }

            applyButton.addEventListener('click', fetchMapData);
            fetchMapData(); // Initial load
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
            pointer-events: none;
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
    </style>

</x-layout>

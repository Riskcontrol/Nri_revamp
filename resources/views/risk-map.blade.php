<x-layout title="Nigeria Risk Map">

    {{-- Add Chart.js for the dynamic charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="lg:flex max-w-full mx-auto p-4 lg:p-8 space-y-4 lg:space-y-0 lg:space-x-8">

        <div class="lg:w-1/3 bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6 h-full">
            <h2 class="text-2xl font-bold text-white mb-6">Risk Filters</h2>
            <div class="pt-4 border-t border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-300">Comparison Mode</label>
                    <button id="toggle-comparison"
                        class="px-3 py-1 text-xs font-bold text-white bg-gray-600 rounded hover:bg-gray-500 transition">
                        OFF
                    </button>
                </div>
                <p class="text-[10px] text-gray-400">Enable to select multiple states and compare data.</p>
                <button id="clear-selection"
                    class="hidden w-full mt-2 text-xs text-red-400 hover:text-red-300 underline">
                    Clear Selection
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <label for="filter-year" class="block text-sm font-medium text-gray-300 mb-2">Year</label>
                    <select id="filter-year" name="year"
                        class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                        @php
                            $currentYear = 2025; // Set your current year
                            $startYear = 2018; // Set the earliest year
                        @endphp
                        @for ($year = $currentYear; $year >= $startYear; $year--)
                            <option value="{{ $year }}" @if ($year == $currentYear) selected @endif>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="filter-risk" class="block text-sm font-medium text-gray-300 mb-2">Risk Index</label>
                    <select id="filter-risk" name="risk_type"
                        class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                        {{-- <option value="All">Composite Risk Index</option> --}}
                        <option value="Terrorism">Terrorism</option>
                        <option value="Kidnapping">Kidnapping</option>
                        <option value="Crime">Crime</option>
                        <option value="Homicide">Homicide</option>
                        <option value="Property-Risk">Property Risk</option>
                    </select>
                </div>

                <button id="apply-filters"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                    Apply Filters
                </button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700">
                <h3 class="text-sm font-medium text-white uppercase tracking-wide">
                    Active Threat Groups
                </h3>
                <p id="card-top-threats" class="text-lg font-semibold text-gray-100 mt-4" style="line-height: 1.6;">
                    Loading...
                </p>
            </div>
        </div>

        <div class="lg:w-2/3">
            <section class="bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6">
                <div class="space-y-2 text-center mb-6">
                    <h2 id="map-title" class="text-3xl font-bold text-white">Nigeria Risk Map</h2>
                    {{-- <p id="map-subtitle" class="text-sm font-medium text-gray-300">Composite Risk - {{ date('Y') }}
                    </p> --}}

                    {{-- ADDED INSTRUCTION HERE --}}
                    <p class="text-xs text-yellow-400 italic mt-1 animate-pulse">
                        (Click on a state to view detailed risk analysis)
                    </p>
                </div>

                <div id="risk-map" class="w-full"
                    style="height: 600px; background-color: #1E2D3D; border-radius: 8px;">
                    <div id="map-loader" class="flex items-center justify-center h-full">
                        <p class="text-white text-lg">Loading Map...</p>
                    </div>
                </div>
            </section>

            <section id="comparison-container"
                class="hidden mt-6 bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">State Comparison: Incidents</h3>
                <div style="height: 300px;">
                    <canvas id="comparisonBarChart"></canvas>
                </div>
            </section>
        </div>
    </div>


    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
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
                if (selectedStates.length < 1) {
                    comparisonContainer.classList.add('hidden');
                    clearBtn.classList.add('hidden');
                    return;
                }

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

            // Pop-up Trend Chart
            function createIncidentChart(canvasId, currentYear, previousYear, currentIncidents, previousIncidents) {
                if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
                const ctx = document.getElementById(canvasId);
                if (!ctx) return;

                chartInstances[canvasId] = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [previousYear, currentYear],
                        datasets: [{
                            label: 'Trend',
                            data: [previousIncidents, currentIncidents],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#FFFFFF',
                                    size: 10
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                display: false,
                                beginAtZero: true
                            }
                        }
                    }
                });
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

                <div class="w-24 flex-shrink-0">
                    <p class="text-[10px] uppercase font-semibold text-gray-500 tracking-wider mb-2 text-center">Trend</p>
                    <div style="height: 60px;">
                        <canvas id="${chartCanvasId}"></canvas>
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

                // 4. Click Logic & Selection Styling
                layer.on('click', function(e) {
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

                layer.on('popupopen', () => {
                    if (!isComparisonMode) {
                        createIncidentChart(chartCanvasId, props.current_year, props.previous_year, props
                            .incidents_count, props.incidents_prev_year);
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

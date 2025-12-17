<x-layout title="Risk Map Intelligence">
    {{-- Leaflet CSS/JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-900 text-gray-100">

        {{-- SIDEBAR: SIMPLIFIED FILTERS --}}
        <aside class="w-full md:w-72 bg-[#1E2D3D] shadow-2xl p-6 border-r border-gray-800 z-20 flex-shrink-0">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-white tracking-wide">Risk Map</h2>
                <div class="h-1 w-10 bg-blue-500 mt-2 rounded"></div>
            </div>

            <form id="map-filters" onsubmit="event.preventDefault(); updateMapData();" class="space-y-6">

                {{-- 1. YEAR --}}
                <div class="flex flex-col">
                    <label class="text-xs font-bold uppercase text-gray-400 mb-2">Year</label>
                    <select id="year"
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded text-sm text-white focus:border-blue-500 outline-none">
                        @foreach ($options['years'] as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. STATE --}}
                <div class="flex flex-col">
                    <label class="text-xs font-bold uppercase text-gray-400 mb-2">State</label>
                    <select id="state"
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded text-sm text-white focus:border-blue-500 outline-none">
                        <option value="">All Nigeria</option>
                        @foreach ($options['states'] as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. RISK INDICATOR --}}
                <div class="flex flex-col">
                    <label class="text-xs font-bold uppercase text-gray-400 mb-2">Risk Indicator</label>
                    <select id="indicator"
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded text-sm text-white focus:border-blue-500 outline-none">
                        <option value="">All Indicators</option>
                        @foreach ($options['indicators'] as $i)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded shadow transition-all mt-4">
                    Update Analysis
                </button>
            </form>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 flex flex-col relative h-screen overflow-hidden">

            {{-- Loading Overlay --}}
            <div id="loader" class="absolute inset-0 bg-gray-900/90 z-50 flex flex-col items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                <span class="text-blue-400 font-bold tracking-wider">LOADING INTELLIGENCE...</span>
            </div>

            {{-- TOP: MAP (60% Height) --}}
            <div class="h-[60%] relative border-b border-gray-700">
                <div id="map" class="w-full h-full z-10"></div>

                {{-- Map Legend Overlay --}}
                <div
                    class="absolute bottom-6 right-6 bg-[#1E2D3D]/90 backdrop-blur p-4 rounded border border-gray-600 z-[1000] text-xs">
                    <h4 class="font-bold mb-2 text-white">Risk Severity</h4>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-[#ef4444]"></span>
                        Critical</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-[#f97316]"></span>
                        High</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-[#eab308]"></span>
                        Medium</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#10b981]"></span> Low
                    </div>
                </div>
            </div>

            {{-- BOTTOM: CHART (40% Height) --}}
            <div class="h-[40%] bg-[#1A202C] p-4 flex flex-col">
                <div class="flex justify-between items-center mb-2 px-2">
                    <h3 class="text-sm font-bold text-gray-300 uppercase">Monthly Incident Trend</h3>
                    <span class="text-xs text-gray-500" id="chart-subtitle">For Selected Filters</span>
                </div>
                <div id="trend-chart" class="flex-1 w-full"></div>
            </div>

        </main>
    </div>

    <script>
        // --- GLOBAL VARIABLES ---
        let map, geoJsonLayer;
        let trendChart;

        // --- MAP CONFIG ---
        function initMap() {
            map = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView([9.0820, 8.6753], 6); // Nigeria Center

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(map);
        }

        // Color Logic based on your Risk Levels (1-4)
        function getColor(level) {
            return level === 4 ? '#ef4444' : // Critical (Red)
                level === 3 ? '#f97316' : // High (Orange)
                level === 2 ? '#eab308' : // Medium (Yellow)
                '#10b981'; // Low (Green)
        }

        function styleMap(feature) {
            return {
                fillColor: getColor(feature.properties.risk_level),
                weight: 1,
                opacity: 1,
                color: '#374151', // Border color
                fillOpacity: 0.7
            };
        }

        function onEachFeature(feature, layer) {
            // Tooltip on Hover
            const props = feature.properties;
            const popupContent = `
                <div class="text-gray-800 p-1">
                    <h4 class="font-bold text-sm border-b border-gray-300 pb-1 mb-1">${props.name}</h4>
                    <p class="text-xs"><strong>Score:</strong> ${props.risk_score}</p>
                    <p class="text-xs"><strong>Incidents:</strong> ${props.incident_count}</p>
                </div>
            `;
            layer.bindTooltip(popupContent, {
                sticky: true,
                className: 'custom-tooltip'
            });

            // Click to filter chart by state (Optional UX enhancement)
            layer.on('click', function() {
                document.getElementById('state').value = props.name;
                updateMapData();
            });
        }

        // --- CHART CONFIG ---
        function initChart() {
            const options = {
                chart: {
                    type: 'area',
                    height: '100%',
                    fontFamily: 'inherit',
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                colors: ['#3b82f6'],
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: [],
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: '#9ca3af'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af'
                        }
                    }
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 4
                },
                series: [],
                tooltip: {
                    theme: 'dark'
                }
            };

            trendChart = new ApexCharts(document.querySelector("#trend-chart"), options);
            trendChart.render();
        }

        // --- DATA FETCHING ---
        async function updateMapData() {
            const loader = document.getElementById('loader');
            loader.style.display = 'flex';

            const year = document.getElementById('year').value;
            const state = document.getElementById('state').value;
            const indicator = document.getElementById('indicator').value;

            // Update Chart Subtitle
            document.getElementById('chart-subtitle').innerText =
                `${state || 'All Nigeria'} • ${indicator || 'All Risks'} • ${year}`;

            try {
                const url = `/risk-map-analytics/data?year=${year}&state=${state}&indicator=${indicator}`;
                const res = await fetch(url);
                const data = await res.json();

                // 1. Update Map
                if (geoJsonLayer) map.removeLayer(geoJsonLayer);

                geoJsonLayer = L.geoJson(data.geoJson, {
                    style: styleMap,
                    onEachFeature: onEachFeature
                }).addTo(map);

                // Zoom logic
                if (state) {
                    // Find the layer for this state and zoom to it
                    const layers = geoJsonLayer.getLayers();
                    const stateLayer = layers.find(l => l.feature.properties.name === state);
                    if (stateLayer) map.fitBounds(stateLayer.getBounds());
                } else {
                    // Reset to Nigeria view
                    map.setView([9.0820, 8.6753], 6);
                }

                // 2. Update Chart
                trendChart.updateOptions({
                    xaxis: {
                        categories: data.chart.categories
                    }
                });
                trendChart.updateSeries(data.chart.series);

            } catch (err) {
                console.error("Failed to load data", err);
                alert("Error loading data.");
            } finally {
                loader.style.display = 'none';
            }
        }

        // --- INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            initChart();
            updateMapData(); // Load initial data
        });
    </script>
</x-layout>

<x-layout title="Nigeria Risk Map">

    {{-- Add Chart.js for the dynamic charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="lg:flex max-w-full mx-auto p-4 lg:p-8 space-y-4 lg:space-y-0 lg:space-x-8">

        <div class="lg:w-1/3 bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl p-6 h-full">
            <h2 class="text-2xl font-bold text-white mb-6">Risk Filters</h2>

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
                        <option value="All">Composite Risk Index</option>
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
                    <p id="map-subtitle" class="text-sm font-medium text-gray-300">Composite Risk - {{ date('Y') }}
                    </p>
                </div>

                <div id="risk-map" class="w-full"
                    style="height: 600px; background-color: #1E2D3D; border-radius: 8px;">
                    <div id="map-loader" class="flex items-center justify-center h-full">
                        <p class="text-white text-lg">Loading Map...</p>
                    </div>
                </div>
            </section>
        </div>
    </div>


    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ... (All your elements: mapContainer, loader, etc.) ...
            const mapContainer = document.getElementById('risk-map');
            const loader = document.getElementById('map-loader');
            const yearSelect = document.getElementById('filter-year');
            const riskSelect = document.getElementById('filter-risk');
            const applyButton = document.getElementById('apply-filters');
            // const mapTitle = document.getElementById('map-title');
            const mapSubtitle = document.getElementById('map-subtitle');

            var map = L.map('risk-map').setView([9.0820, 8.6753], 6);

            // ... (Scroll wheel fix) ...
            map.scrollWheelZoom.disable();
            map.on('click', function() {
                map.scrollWheelZoom.enable();
            });
            map.on('mouseout', function() {
                map.scrollWheelZoom.disable();
            });

            var geoJsonLayer = null;
            var legend = null;
            var chartInstances = {};

            // ... (TileLayer) ...
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            // ... (All your helper functions: getColor, style, createIncidentChart, onEachFeature, createLegend) ...
            function getColor(riskLevel) {
                switch (riskLevel) {
                    case 4:
                        return '#B10026';
                    case 3:
                        return '#E31A1C';
                    case 2:
                        return '#FEB24C';
                    case 1:
                        return '#FFEDA0';
                    default:
                        return '#969696';
                }
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


            // --- CHART RENDERING FUNCTION (MODIFIED FOR POPUP) ---
            // --- CHART RENDERING FUNCTION (MODIFIED FOR POPUP) ---
            function createIncidentChart(canvasId, currentYear, previousYear, currentIncidents, previousIncidents) {
                if (chartInstances[canvasId]) {
                    chartInstances[canvasId].destroy();
                }

                const ctx = document.getElementById(canvasId);
                if (!ctx) {
                    console.error("Canvas element not found for chart:", canvasId);
                    return;
                }

                chartInstances[canvasId] = new Chart(ctx.getContext('2d'), {
                    type: 'line', // <-- 1. SET TO 'line'
                    data: {
                        labels: [previousYear, currentYear],
                        datasets: [{
                            label: 'Incidents Trend Over Time',
                            data: [previousIncidents, currentIncidents],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.3,
                            fill: true, // <-- 2. Fill under the line
                            pointRadius: 3 // <-- 3. Show data points
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            }, // Hide legend
                            tooltip: {
                                enabled: true
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#FFFFFF', // <-- 4. Set color to white
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                display: false, // Hide Y-axis
                                beginAtZero: true,
                                // 5. Set min/max to give it padding
                                min: 0,
                                max: Math.max(previousIncidents, currentIncidents) * 1.2 // Add 20% padding
                            }
                        }
                    }
                });
            }

            function onEachFeature(feature, layer) {
                if (feature.properties) {
                    const stateName = feature.properties.name || 'Unknown State';
                    layer.bindTooltip(stateName, {
                        permanent: true,
                        direction: 'center',
                        className: 'state-label'
                    });
                    const props = feature.properties;
                    const safeId = stateName.replace(/[^a-zA-Z0-9]/g, '-');
                    const chartCanvasId = `chart-${safeId}`;
                    let trendIcon = '';
                    let trendColor = '#ccc';
                    if (props.incidents_count > props.incidents_prev_year) {
                        trendIcon = '<span class="text-red-500">&#9650;</span>';
                        trendColor = 'text-red-500';
                    } else if (props.incidents_count < props.incidents_prev_year) {
                        trendIcon = '<span class="text-green-500">&#9660;</span>';
                        trendColor = 'text-green-500';
                    }
                    let filterName = props.filter_risk_type;
                    if (filterName === 'All') {
                        filterName = 'Composite';
                    } else if (filterName === 'Property-Risk') {
                        filterName = 'Property';
                    }
                    const popupContent = `
<div class="p-3 text-sm text-gray-200 bg-gray-800 rounded-md shadow-lg" style="width: 500px;">
                                                <h3 class="font-bold text-base text-white mb-3 pb-2 border-b border-gray-700">${stateName}</h3>

                                                <div class="flex gap-4">

                                                        <div class="flex-1 space-y-2 text-xs">
                                <div>
                                    <strong class="text-gray-400 uppercase">${filterName} Score</strong>
                                    <p class="text-lg font-semibold text-white">${props.composite_index_score.toFixed(2)}</p>
                                </div>
                                <div>
                                    <strong class="text-gray-400 uppercase">Most Affected LGA</strong>
                                    <p class="text-base text-white">${props.most_affected_lga || 'N/A'}</p>
                 </div>
                            </div>

                                                        <div class="w-50 flex-shrink-0" style="height: 100px;">
                           <canvas id="${chartCanvasId}"></canvas>
                            </div>
                  </div>
                    </div>
                    `;
                    layer.bindPopup(popupContent, {
                        closeButton: true,
                        offset: L.point(0, -10)
                    });
                    layer.on('popupopen', function() {
                        createIncidentChart(
                            chartCanvasId,
                            props.current_year,
                            props.previous_year,
                            props.incidents_count,
                            props.incidents_prev_year
                        );
                    });
                    layer.on('popupclose', function() {
                        if (chartInstances[chartCanvasId]) {
                            chartInstances[chartCanvasId].destroy();
                            delete chartInstances[chartCanvasId];
                        }
                    });
                }
            }

            function createLegend() {
                if (legend) {
                    legend.remove();
                }
                legend = L.control({
                    position: 'bottomright'
                });
                legend.onAdd = function(map) {
                    var div = L.DomUtil.create('div', 'info legend'),
                        grades = [0, 1, 2, 3, 4],
                        labels = ['No/Low Data', 'Low', 'Moderate', 'Elevated', 'High'];
                    div.innerHTML = '<strong>Risk Level</strong><br>';
                    for (var i = 0; i < grades.length; i++) {
                        div.innerHTML += '<i style="background:' + getColor(grades[i]) + '"></i> ' + labels[i] +
                            '<br>';
                    }
                    return div;
                };
                legend.addTo(map);
            }

            /**
             * Main data fetch function (Corrected)
             */
            function fetchMapData() {
                loader.style.display = 'flex';
                applyButton.disabled = true;
                applyButton.textContent = 'Applying...';

                const year = yearSelect.value;
                const risk = riskSelect.value;
                const riskText = riskSelect.options[riskSelect.selectedIndex].text;

                const mapApiUrl = `/api/risk-map-data?year=${year}&risk_type=${risk}`;
                const cardApiUrl = `/api/risk-map-card-data?year=${year}&risk_type=${risk}`;

                // --- 2. This element is NOW valid because you added the HTML ---
                const threatCard = document.getElementById('card-top-threats');
                threatCard.textContent = 'Loading...';

                // --- 3. Fetch for the Map ---
                const mapPromise = fetch(mapApiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error(`Map HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(geojsonData => {
                        if (geoJsonLayer) map.removeLayer(geoJsonLayer);
                        if (!geojsonData || !geojsonData.features) throw new Error(
                            "Invalid GeoJSON object received");

                        geoJsonLayer = L.geoJson(geojsonData, {
                            style: style,
                            onEachFeature: onEachFeature
                        }).addTo(map);
                        createLegend();
                        // mapTitle.innerText = `Nigeria ${riskText} Map`;
                        mapSubtitle.innerText = `${riskText} - ${year}`;
                        loader.style.display = 'none';
                        console.log('Map updated successfully.');
                    });

                // --- 4. Fetch for the Card ---
                const cardPromise = fetch(cardApiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error(`Card HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(cardData => {
                        threatCard.textContent = cardData.topThreatGroups;
                        console.log('Card updated successfully.');
                    });

                // --- 5. Handle all promises ---
                Promise.all([mapPromise, cardPromise])
                    .then(() => {
                        applyButton.disabled = false;
                        applyButton.textContent = 'Apply Filters';
                    })
                    .catch(error => {
                        console.error('Error loading page data:', error);
                        // --- 3. Bug fixes in error handling ---
                        mapContainer.innerHTML =
                            `<p class="text-white text-center p-4">Error loading map data: ${error.message}. Please try again.</p>`; // Added missing backtick
                        threatCard.textContent = 'Error'; // Also show error in card

                        applyButton.disabled = false;
                        applyButton.textContent = 'Apply Filters'; // Removed double semicolon
                    });
            }

            applyButton.addEventListener('click', fetchMapData);
            fetchMapData();
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

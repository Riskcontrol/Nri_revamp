<x-layout title="Location Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria.">

    <div class="container mx-auto px-4 py-10">
        <h1 class="text-center text-2xl md:text-3xl font-bold text-white mb-8">
            Location Intelligence for <span id="state-name">{{ $state }}</span> in <span
                id="current-year">{{ $year }}</span>
        </h1>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 text-center mb-10">
            {{-- State Select --}}
            <div class="flex items-center space-x-2">
                <label for="state-select" class="text-sm font-medium text-gray-400">State:</label>
                <select id="state-select"
                    class="bg-[#131C27] text-white text-sm py-2 px-4 border border-gray-600 rounded-md focus:outline-none focus:border-emerald-500 hover:border-gray-500 transition-colors cursor-pointer">
                    @foreach ($getStates as $s)
                        <option value="{{ $s }}" {{ $s == $state ? 'selected' : '' }}>{{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Year Select --}}
            <div class="flex items-center space-x-2">
                <label for="year-select" class="text-sm font-medium text-gray-400">Year:</label>
                <select id="year-select"
                    class="bg-[#131C27] text-white text-sm py-2 px-4 border border-gray-600 rounded-md focus:outline-none focus:border-emerald-500 hover:border-gray-500 transition-colors cursor-pointer">
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div id="total-incidents" class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                <h3 id="total-incidents-title" class="text-lg md:text-xl font-semibold text-white mb-3">Tracked
                    Incidents
                    ({{ $year }})</h3>
                <p class="text-2xl md:text-3xl font-medium text-white mt-2">{{ $total_incidents }}</p>
            </div>

            <div id="most-frequent-risk" class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                <h3 class="text-lg md:text-xl font-semibold text-white mb-3">Most Prevalent Risk</h3>
                <div id="most-frequent-risk-content" class="text-base md:text-lg text-white mt-2">
                    <p>{{ $mostFrequentRisk->pluck('riskindicators')->implode(', ') ?: 'No data available' }}</p>
                </div>
            </div>

            <div id="most-affected-lga" class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                <h3 class="text-lg md:text-xl font-semibold text-white mb-3">Most Affected LGA</h3>
                <p class="text-base md:text-lg text-white mt-2">
                    @if ($mostAffectedLGA)
                        {{ $mostAffectedLGA->lga }}
                    @else
                        No data available
                    @endif
                </p>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-white mb-4">Insights</h3>
            <div id="insights-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($automatedInsights as $insight)
                    <div class="bg-[#1E2D3D] p-4 rounded shadow-md">
                        @php
                            $titleColor = 'text-gray-400';
                            if ($insight['type'] == 'Velocity') {
                                $titleColor = 'text-blue-400';
                            }
                            if ($insight['type'] == 'Emerging Threat') {
                                $titleColor = 'text-red-400';
                            }
                            if ($insight['type'] == 'Lethality') {
                                $titleColor = 'text-orange-400';
                            }
                            if ($insight['type'] == 'Forecast') {
                                $titleColor = 'text-green-400';
                            }
                        @endphp
                        <h4 class="text-xs font-bold {{ $titleColor }} uppercase mb-1 tracking-wider">
                            {{ $insight['type'] }}
                        </h4>
                        <p class="text-white text-md">
                            {{ $insight['text'] }}
                        </p>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 text-center text-gray-500 italic py-4">
                        Insufficient data pattern to generate strategic insights for this period.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 mb-8">
            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-center text-lg md:text-xl font-semibold text-white mb-4">Incidents Over the Past 12
                    Months</h3>
                <div class="relative h-64 md:h-80">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
                    <h3 class="text-center sm:text-left text-lg md:text-xl font-semibold text-white">Prevalent Risk</h3>
                    <select id="prevalent-compare-select"
                        class="bg-[#131C27] text-white text-xs py-1 px-3 border border-gray-600 rounded hover:border-gray-400 focus:outline-none focus:border-emerald-500 transition-colors w-full sm:w-auto">
                        <option value="" selected>Compare...</option>
                        @foreach ($getStates as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative h-64 md:h-80">
                    <canvas id="myChart2"></canvas>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 mb-8">
            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-center text-lg md:text-xl font-semibold text-white mb-4" id="map-title">
                    High Impact Incidents in {{ $state }} ({{ $year }})
                </h3>
                <div id="map" style="height: 300px; width: 100%; border-radius: 8px;" class="md:h-[400px]"></div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-center text-lg md:text-xl font-semibold text-white mb-4">Incidents by Actors</h3>
                <div class="relative h-64 md:h-80">
                    <canvas id="attackChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <h4 class="text-sm font-medium text-gray-400 mb-1">Crime Index Score</h4>
            <p id="crime-index-score" class="text-3xl md:text-4xl font-bold text-white">{{ $stateCrimeIndexScore }}</p>
            <p class="text-xs text-gray-500">(State's weighted contribution to national crime)</p>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-6 mb-12 overflow-hidden">
            <h4 class="text-lg font-semibold text-white mb-4">Crime Indicator Breakdown</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-700 text-left text-xs text-gray-400 uppercase">
                            <th class="py-3 px-4 whitespace-nowrap">Indicator</th>
                            <th class="py-3 px-4 whitespace-nowrap">Incidents (Current)</th>
                            <th class="py-3 px-4 whitespace-nowrap">Incidents (Prev)</th>
                            <th class="py-3 px-4 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody id="crime-table-body" class="text-gray-200">
                        @forelse ($crimeTable as $item)
                            <tr class="border-b border-gray-700">
                                <td class="py-4 px-4 font-medium whitespace-nowrap">{{ $item['indicator_name'] }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">{{ $item['incident_count'] }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">{{ $item['previous_year_count'] }}</td>
                                @php
                                    $statusColorClass = 'text-blue-400';
                                    if ($item['status'] === 'Escalating') {
                                        $statusColorClass = 'text-red-500';
                                    } elseif ($item['status'] === 'Improving') {
                                        $statusColorClass = 'text-green-500';
                                    }
                                @endphp
                                <td class="py-4 px-4 font-semibold {{ $statusColorClass }} whitespace-nowrap">
                                    {{ $item['status'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 px-4 text-center text-gray-500">
                                    No crime index data found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // 1. Global Variables
        let myChart, myChart2, attackChart;
        let map, geojsonLayer, info;
        let lgaGeoJsonData;
        let lgaIncidentData = {};

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            const options = {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            };
            return date.toLocaleDateString('en-US', options).replace(/,/, '');
        };

        // 2. Initialize Charts
        async function initializeCharts() {
            try {
                const response = await fetch('/nigeria-lga.json');
                lgaGeoJsonData = await response.json();
                console.log("GeoJSON loaded successfully.");
            } catch (e) {
                console.error("CRITICAL: Could not load nigeria-lga.json.", e);
                document.getElementById('map-title').textContent = "Map failed to load.";
                return;
            }

            // --- Get Default State Name from DOM ---
            const defaultState = document.getElementById('state-name').textContent || 'Primary State';
            const defaultYear = document.getElementById('current-year').textContent;

            // Chart 1: Incidents Over Past Months
            const ctx1 = document.getElementById('myChart').getContext('2d');
            myChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Incidents occurred in a month',
                        data: @json($incidentCounts),
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,
                        borderColor: '#10b981',
                        backgroundColor: '#10b981',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });

            // Chart 2: Prevalent Risk Indicators
            const ctx2 = document.getElementById('myChart2').getContext('2d');
            myChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: @json($topRiskLabels),
                    datasets: [{
                        label: defaultState, // Uses State Name instead of "Top 5 Risk..."
                        data: @json($topRiskCounts),
                        backgroundColor: [
                            'rgba(27, 158, 133, 0.7)', 'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });

            // Chart 3: Incidents by Actors
            const attackCtx = document.getElementById('attackChart').getContext('2d');
            attackChart = new Chart(attackCtx, {
                type: 'pie',
                data: {
                    labels: @json($attackLabels),
                    datasets: [{
                        label: 'Attack Occurrences',
                        data: @json($attackCounts),
                        backgroundColor: [
                            'rgba(27, 158, 133, 0.7)', 'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)', 'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });

            // Initialize Map
            map = L.map('map').setView([9.0820, 8.6753], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            geojsonLayer = L.geoJson(null, {
                style: styleFeature,
                onEachFeature: onEachFeature
            }).addTo(map);

            info = L.control();
            info.onAdd = function(map) {
                this._div = L.DomUtil.create('div', 'info');
                this.update();
                return this._div;
            };
            info.update = function(props) {
                const count = props ? (lgaIncidentData[props.NAME_2] || 0) : null;
                this._div.innerHTML = '<h4>Incident Count by LGA</h4>' + (props ? '<b>' + props.NAME_2 +
                    '</b><br />' + (count !== null ? count : 0) + ' Incidents' : 'Hover over an LGA');
            };
            info.addTo(map);

            const legend = L.control({
                position: 'bottomright'
            });
            legend.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'info legend');
                const grades = [0, 10, 20, 50, 100, 200, 500];
                for (let i = 0; i < grades.length; i++) {
                    div.innerHTML += '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' + grades[i] + (
                        grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
                }
                return div;
            };
            legend.addTo(map);

            updateMap(defaultState, defaultYear);
        }

        // Prevalent Risk Comparison Logic
        function updatePrevalentComparison() {
            const compareState = document.getElementById('prevalent-compare-select').value;
            const year = document.getElementById('year-select').value;

            if (!compareState) {
                if (myChart2.data.datasets.length > 1) {
                    myChart2.data.datasets.pop();
                    myChart2.update();
                }
                return;
            }

            fetch(`/get-top-5-risks/${compareState}/${year}`)
                .then(response => response.json())
                .then(data => {
                    const primaryLabels = myChart2.data.labels;
                    const alignedCounts = primaryLabels.map(label => {
                        const index = data.labels.indexOf(label);
                        return index !== -1 ? data.counts[index] : 0;
                    });

                    const comparisonDataset = {
                        label: compareState,
                        data: alignedCounts,
                        backgroundColor: '#3b82f6',
                        borderColor: '#3b82f6',
                        borderWidth: 1
                    };

                    if (myChart2.data.datasets.length > 1) {
                        myChart2.data.datasets[1] = comparisonDataset;
                    } else {
                        myChart2.data.datasets.push(comparisonDataset);
                    }

                    myChart2.update();
                })
                .catch(error => console.error('Error fetching prevalent risk comparison:', error));
        }

        async function updateMap(state, year) {
            document.getElementById('map-title').textContent = `Incident Density in ${state} (${year})`;
            try {
                const response = await fetch(`/get-lga-incident-counts/${state}/${year}`);
                lgaIncidentData = await response.json();
            } catch (e) {
                console.error("Could not fetch LGA incident data", e);
                lgaIncidentData = {};
            }

            const stateFeatures = lgaGeoJsonData.features.filter(feature =>
                feature.properties.NAME_1.toLowerCase().trim() === state.toLowerCase().trim()
            );

            if (stateFeatures.length === 0) {
                console.warn(`No GeoJSON features found for state: ${state}`);
                geojsonLayer.clearLayers();
                map.setView([9.0820, 8.6753], 6);
                return;
            }

            geojsonLayer.clearLayers();
            geojsonLayer.addData({
                type: "FeatureCollection",
                features: stateFeatures
            });
            map.fitBounds(geojsonLayer.getBounds(), {
                padding: [20, 20]
            });
        }

        function getColor(count) {
            return count > 500 ? '#7F1D1D' : count > 200 ? '#991B1B' : count > 100 ? '#B91C1C' :
                count > 50 ? '#DC2626' : count > 20 ? '#EF4444' : count > 10 ? '#F87171' : count > 0 ? '#FCA5A5' :
                '#FCA5A5';
        }

        function styleFeature(feature) {
            const lgaName = feature.properties.NAME_2;
            const count = lgaIncidentData[lgaName] || 0;
            return {
                fillColor: getColor(count),
                weight: 2,
                opacity: 1,
                color: 'white',
                dashArray: '3',
                fillOpacity: 0.7
            };
        }

        function onEachFeature(feature, layer) {
            layer.on({
                mouseover: function(e) {
                    const layer = e.target;
                    layer.setStyle({
                        weight: 4,
                        color: '#666',
                        dashArray: '',
                        fillOpacity: 0.9
                    });
                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
                    info.update(layer.feature.properties);
                },
                mouseout: function(e) {
                    geojsonLayer.resetStyle(e.target);
                    info.update();
                },
                click: function(e) {
                    map.fitBounds(e.target.getBounds());
                }
            });
        }

        function updateMainDashboard(primaryState, selectedYear) {
            if (!primaryState || !selectedYear) return;
            if (typeof updateMap === "function") updateMap(primaryState, selectedYear);

            // Reset comparison on filter change
            const prevalentDropdown = document.getElementById('prevalent-compare-select');
            if (prevalentDropdown) {
                prevalentDropdown.value = "";
                if (typeof myChart2 !== 'undefined' && myChart2.data.datasets.length > 1) {
                    myChart2.data.datasets.pop();
                    myChart2.update();
                }
            }

            const safeSetText = (id, text) => {
                const el = document.getElementById(id);
                if (el) el.querySelector('p') ? el.querySelector('p').textContent = text : el.textContent = text;
            };
            const safeSetHTML = (id, html) => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = html;
            };

            safeSetText('total-incidents', '...');
            safeSetHTML('most-frequent-risk-content', '<p>Loading...</p>');
            safeSetText('most-affected-lga', '...');
            safeSetHTML('insights-container', '<p class="text-gray-400 p-4">Analyzing data patterns...</p>');
            safeSetText('crime-index-score', '...');
            safeSetHTML('crime-table-body',
                '<tr><td colspan="4" class="py-6 px-4 text-center text-gray-500">Loading...</td></tr>');

            fetch(`/get-state-data/${primaryState}/${selectedYear}`)
                .then(response => response.json())
                .then(data => {
                    safeSetText('total-incidents', data.total_incidents);
                    const totalTitle = document.getElementById('total-incidents-title');
                    if (totalTitle) totalTitle.textContent = `Total Incidents (${selectedYear})`;

                    const riskContent = document.getElementById('most-frequent-risk-content');
                    if (riskContent) {
                        let riskText = 'No data available';
                        if (data.mostFrequentRisk && data.mostFrequentRisk.length > 0) riskText = data.mostFrequentRisk
                            .map(risk => risk.riskindicators).join(', ');
                        riskContent.innerHTML = `<p>${riskText}</p>`;
                    }

                    safeSetText('most-affected-lga', data.mostAffectedLGA ? data.mostAffectedLGA.lga : 'None');

                    if (typeof myChart !== 'undefined') {
                        myChart.data.labels = data.chartLabels;
                        myChart.data.datasets[0].data = data.incidentCounts;
                        myChart.update();
                    }

                    if (typeof myChart2 !== 'undefined') {
                        myChart2.data.labels = data.topRiskLabels;
                        myChart2.data.datasets[0].label =
                            primaryState; // <--- UPDATED: Set dynamic label on filter change
                        myChart2.data.datasets[0].data = data.topRiskCounts;
                        myChart2.update();
                    }

                    if (typeof attackChart !== 'undefined') {
                        attackChart.data.labels = data.attackLabels;
                        attackChart.data.datasets[0].data = data.attackCounts;
                        attackChart.update();
                    }

                    safeSetText('crime-index-score', data.stateCrimeIndexScore);

                    const crimeTableBody = document.getElementById('crime-table-body');
                    if (crimeTableBody) {
                        let crimeTableHtml = '';
                        if (data.crimeTable && data.crimeTable.length > 0) {
                            data.crimeTable.forEach(item => {
                                let statusColorClass = 'text-blue-400';
                                if (item.status === 'Escalating') statusColorClass = 'text-red-500';
                                else if (item.status === 'Improving') statusColorClass = 'text-green-500';
                                crimeTableHtml +=
                                    `<tr class="border-b border-gray-700"><td class="py-4 px-4 font-medium whitespace-nowrap">${item.indicator_name}</td><td class="py-4 px-4 whitespace-nowrap">${item.incident_count}</td><td class="py-4 px-4 whitespace-nowrap">${item.previous_year_count}</td><td class="py-4 px-4 font-semibold ${statusColorClass} whitespace-nowrap">${item.status}</td></tr>`;
                            });
                        } else {
                            crimeTableHtml =
                                `<tr><td colspan="4" class="py-6 px-4 text-center text-gray-500">No crime index data found.</td></tr>`;
                        }
                        crimeTableBody.innerHTML = crimeTableHtml;
                    }

                    if (typeof renderInsights === "function") renderInsights(data.automatedInsights);
                })
                .catch(error => {
                    console.error('Error fetching primary data:', error);
                    const errContainer = document.getElementById('insights-container');
                    if (errContainer) errContainer.innerHTML = '<p class="text-red-400 p-4">Error loading data.</p>';
                });
        }

        function handleFilterChange() {
            const primaryState = document.getElementById('state-select').value;
            const selectedYear = document.getElementById('year-select').value;
            document.getElementById('state-name').textContent = primaryState;
            document.getElementById('current-year').textContent = selectedYear;
            updateMainDashboard(primaryState, selectedYear);
        }

        function renderInsights(insights) {
            const container = document.getElementById('insights-container');
            container.innerHTML = '';
            if (!insights || insights.length === 0) {
                container.innerHTML =
                    '<div class="col-span-1 md:col-span-2 text-center text-gray-500 italic py-4">Insufficient data pattern to generate strategic insights for this period.</div>';
                return;
            }
            insights.forEach(insight => {
                let titleColor = 'text-gray-400';
                if (insight.type === 'Velocity') titleColor = 'text-blue-400';
                if (insight.type === 'Emerging Threat') titleColor = 'text-red-400';
                if (insight.type === 'Lethality') titleColor = 'text-orange-400';
                if (insight.type === 'Forecast') titleColor = 'text-green-400';
                container.innerHTML +=
                    `<div class="bg-[#1E2D3D] p-4 rounded shadow-md"><h4 class="text-xs font-bold ${titleColor} uppercase mb-1 tracking-wider">${insight.type}</h4><p class="text-white text-md">${insight.text}</p></div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', initializeCharts);
        document.getElementById('state-select').addEventListener('change', handleFilterChange);
        document.getElementById('year-select').addEventListener('change', handleFilterChange);
        document.getElementById('prevalent-compare-select').addEventListener('change', updatePrevalentComparison);
    </script>
</x-layout>

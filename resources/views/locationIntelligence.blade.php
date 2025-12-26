<x-layout title="Location Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria.">

    {{-- 1. Load ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-center text-2xl md:text-3xl font-bold text-white mb-8">
            Location Intelligence for <span id="state-name">{{ $state }}</span> in <span
                id="current-year">{{ $year }}</span>
        </h1>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 text-center mb-10">
            {{-- State Select --}}
            <div class="flex items-center space-x-2">
                <label for="state-select" class="text-sm font-medium text-gray-400">State:</label>
                <div class="relative">
                    <select id="state-select" {{-- Add a data attribute to check auth status later --}} data-auth="{{ Auth::check() ? 'true' : 'false' }}"
                        data-access="{{ Auth::check() && Auth::user()->access_level >= 1 ? 'true' : 'false' }}"
                        class="bg-[#131C27] text-white text-sm py-2 px-4 border border-gray-600 rounded-md focus:outline-none focus:border-emerald-500 hover:border-gray-500 transition-colors cursor-pointer pr-8">
                        @foreach ($getStates as $s)
                            <option value="{{ $s }}" {{ $s == $state ? 'selected' : '' }}>{{ $s }}
                            </option>
                        @endforeach
                    </select>
                    @guest
                        <i class="fa-solid fa-lock absolute right-2 top-3 text-[10px] text-gray-500"></i>
                    @endguest
                </div>
            </div>

            {{-- Year Select --}}
            <div class="flex items-center space-x-2">
                <label for="year-select" class="text-sm font-medium text-gray-400">Year:</label>
                <div class="relative">
                    <select id="year-select"
                        class="bg-[#131C27] text-white text-sm py-2 px-4 border border-gray-600 rounded-md focus:outline-none focus:border-emerald-500 hover:border-gray-500 transition-colors cursor-pointer pr-8">
                        @foreach ($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endforeach
                    </select>
                    @guest
                        <i class="fa-solid fa-lock absolute right-2 top-3 text-[10px] text-gray-500"></i>
                    @endguest
                </div>
            </div>
        </div>

        {{-- Updated Grid: 1 col on mobile, 2 on tablet, 4 on desktop --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            {{-- 1. Total Incidents Card --}}
            <div id="total-incidents"
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg text-center border border-white/5 flex flex-col justify-center min-h-[140px]">
                <h3 id="total-incidents-title"
                    class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Tracked Incidents ({{ $year }})
                </h3>
                <p class="text-md md:text-md font-medium text-white tracking-tight">
                    {{ $total_incidents }}
                </p>
            </div>

            {{-- 2. Most Prevalent Risk Card --}}
            <div id="most-frequent-risk"
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border text-center border-white/5 flex flex-col justify-center min-h-[140px]">
                <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Most Prevalent Risk
                </h3>
                <div id="most-frequent-risk-content"
                    class="text-md md:text-md font-medium text-white leading-tight px-2">
                    <p>{{ $mostFrequentRisk->pluck('riskindicators')->implode(', ') ?: 'No data available' }}</p>
                </div>
            </div>

            {{-- 3. Most Affected LGA Card --}}
            <div id="most-affected-lga"
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg text-center border border-white/5 flex flex-col justify-center min-h-[140px]">
                <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Most Affected LGA
                </h3>
                <p class="text-md md:text-md font-medium text-white tracking-wide">
                    @if ($mostAffectedLGA)
                        {{ $mostAffectedLGA->lga }}
                    @else
                        <span class="text-gray-500 font-normal italic">No data available</span>
                    @endif
                </p>
            </div>

            {{-- 4. Crime Index Score Card --}}
            <div
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg text-center border border-white/5 relative group flex flex-col justify-center min-h-[140px]">
                <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Crime Index Score
                </h3>

                {{-- Flex row to place Rank beside the Score --}}
                <div class="flex items-center justify-center gap-3">
                    <p id="crime-index-score" class="text-md md:text-md font-medium text-white tracking-tight">
                        {{ $stateCrimeIndexScore }}
                    </p>

                    <span id="rank-container"
                        class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                        Ranked <span id="state-rank" class="ml-1">{{ $stateRank }}</span><sup
                            id="state-rank-ordinal" class="lowercase">{{ $stateRankOrdinal }}</sup>
                    </span>
                </div>

                <div
                    class="absolute inset-x-0 bottom-2 text-[9px] text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 uppercase font-medium">
                    Weighted Contribution
                </div>
            </div>

        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-white mb-4">Insights</h3>
            <div id="insights-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($automatedInsights as $insight)
                    <div class="bg-[#1E2D3D] p-4 rounded shadow-md">
                        @php
                            $titleColor = 'text-gray-400';
                            $friendlyTitle = $insight['type']; // Fallback

                            switch ($insight['type']) {
                                case 'Velocity':
                                    $friendlyTitle = 'Activity Pace';
                                    break;
                                case 'Emerging Threat':
                                    $friendlyTitle = 'Rising Risk';
                                    break;
                                case 'Lethality':
                                    $friendlyTitle = 'Severity Level';
                                    break;
                                case 'Forecast':
                                    $friendlyTitle = 'Future Outlook';
                                    break;
                            }
                        @endphp

                        <h4 class="text-xs font-semibold {{ $titleColor }} uppercase mb-1 tracking-wider">
                            {{ $friendlyTitle }}
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
            {{-- Chart 1: Incidents Over 12 Months (APEXCHARTS) --}}
            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-center text-lg md:text-xl font-semibold text-gray-400 mb-4">Incidents Over the Past 12
                    Months</h3>
                {{-- ApexChart Container --}}
                <div id="incidentsTrendChart" style="min-height: 320px;"></div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
                    <h3 class="text-center sm:text-left text-lg md:text-xl font-semibold  text-gray-400">Prevalent Risk
                    </h3>
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
                <h3 class="text-center text-lg md:text-xl font-semibold  text-gray-400 mb-4" id="map-title">
                    High Impact Incidents in {{ $state }} ({{ $year }})
                </h3>
                <div id="map" style="height: 300px; width: 100%; border-radius: 8px;" class="md:h-[400px]"></div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-center text-lg md:text-xl font-semibold  text-gray-400 mb-4">Incidents by Actors</h3>
                <div class="relative h-64 md:h-80">
                    <canvas id="attackChart"></canvas>
                </div>
            </div>
        </div>


        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-6 mb-12 overflow-hidden">
            <h4 class="text-lg font-semibold  text-gray-400 mb-4">Crime Index</h4>
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
                                    // Default blue pill for stable/neutral
                                    $pillClasses = 'bg-blue text-white';

                                    if ($item['status'] === 'Escalating') {
                                        $pillClasses = 'bg-red-500 text-white border-red-500';
                                    } elseif ($item['status'] === 'Improving') {
                                        $pillClasses = 'bg-green-500 text-white border-green-500';
                                    }
                                @endphp

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold lowercase tracking-wider border {{ $pillClasses }}">
                                        {{ $item['status'] }}
                                    </span>
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

    <div id="auth-modal"
        class="fixed inset-0 z-[2000] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-[#1E2D3D] border border-white/10 w-full max-w-md rounded-3xl p-8 shadow-2xl relative">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            {{-- State 1: Need Registration --}}
            <div id="modal-register-state">
                <div class="text-center mb-8">
                    <div
                        class="w-16 h-16 bg-blue-500/10 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-shield-halved text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white">Unlock Full Intelligence</h3>
                    <p class="text-gray-400 mt-2 text-sm leading-relaxed">
                        Granular state-level analysis and custom date filtering are reserved for verified organizations.
                    </p>
                </div>

                <div class="space-y-4">
                    <a href="{{ url('/register') }}"
                        class="block w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl text-center transition-all">
                        Register for Access
                    </a>
                    <p class="text-center text-xs text-gray-500">
                        Already requested a demo? <a href="{{ url('/login') }}"
                            class="text-blue-400 hover:underline">Login here</a>
                    </p>
                </div>
            </div>

            {{-- State 2: Demo Pending (Success) --}}
            <div id="modal-success-state" class="hidden">
                <div class="text-center">
                    <div
                        class="w-16 h-16 bg-emerald-500/10 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-envelope-circle-check text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white">Request Received</h3>
                    <p class="text-gray-400 mt-4 text-sm leading-relaxed">
                        Thank you! We have sent a confirmation to your email. Our team will contact you shortly to
                        authorize your account and set up your professional demo.
                    </p>
                    <button onclick="closeAuthModal()"
                        class="mt-8 w-full border border-white/10 text-white py-3 rounded-xl hover:bg-white/5 transition-all text-sm font-bold uppercase tracking-widest">
                        Continue Browsing
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        let incidentsTrendChart;
        let myChart2, attackChart;
        let map, geojsonLayer, info;
        let lgaGeoJsonData;
        let lgaIncidentData = {};

        // 2. Initialize Charts
        async function initializeCharts() {
            try {
                const response = await fetch('/nigeria-lga.json');
                lgaGeoJsonData = await response.json();
            } catch (e) {
                console.error("CRITICAL: Could not load nigeria-lga.json.", e);
                document.getElementById('map-title').textContent = "Map failed to load.";
                return;
            }

            const defaultState = document.getElementById('state-name').textContent || 'Primary State';
            const defaultYear = document.getElementById('current-year').textContent;

            // --- Chart 1: Incidents Over Past 12 Months (ApexCharts) ---
            const trendOptions = {
                series: [{
                    name: 'Incidents',
                    data: @json($incidentCounts)
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    fontFamily: 'inherit'
                },
                colors: ['#10B981'], // Emerald 500
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    categories: @json($chartLabels),
                    labels: {
                        style: {
                            colors: '#9CA3AF' // Gray-400
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9CA3AF' // Gray-400
                        }
                    }
                },
                grid: {
                    borderColor: '#374151', // Gray-700
                    strokeDashArray: 4,
                },
                theme: {
                    mode: 'dark'
                },
                tooltip: {
                    theme: 'dark'
                }
            };

            incidentsTrendChart = new ApexCharts(document.querySelector("#incidentsTrendChart"), trendOptions);
            incidentsTrendChart.render();

            // --- Chart 2: Prevalent Risk (Chart.js) ---
            const ctx2 = document.getElementById('myChart2').getContext('2d');
            myChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: @json($topRiskLabels),
                    datasets: [{
                        label: defaultState,
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

            // --- Chart 3: Incidents by Actors (Chart.js) ---
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

            const primaryLabels = myChart2.data.labels;
            const params = new URLSearchParams();
            params.append('state', compareState);
            params.append('year', year);
            primaryLabels.forEach(label => params.append('indicators[]', label));

            fetch(`/get-comparison-risk-counts?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    const comparisonDataset = {
                        label: compareState,
                        data: data.counts,
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

            // Reset UI placeholders
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
                    // 1. Update Basic Stats
                    safeSetText('total-incidents', data.total_incidents);
                    const totalTitle = document.getElementById('total-incidents-title');
                    if (totalTitle) totalTitle.textContent = `Tracked Incidents (${selectedYear})`;

                    // 2. Update Most Frequent Risk Text
                    const riskContent = document.getElementById('most-frequent-risk-content');
                    if (riskContent) {
                        let riskText = 'No data available';
                        if (data.mostFrequentRisk && data.mostFrequentRisk.length > 0) {
                            riskText = data.mostFrequentRisk.map(risk => risk.riskindicators).join(', ');
                        }
                        riskContent.innerHTML = `<p>${riskText}</p>`;
                    }

                    // 3. Update Most Affected LGA
                    safeSetText('most-affected-lga', data.mostAffectedLGA ? data.mostAffectedLGA.lga : 'None');

                    // 4. Update Charts

                    // --- UPDATE APEX CHART (Trend) ---
                    if (incidentsTrendChart) {
                        incidentsTrendChart.updateOptions({
                            xaxis: {
                                categories: data.chartLabels
                            }
                        });
                        incidentsTrendChart.updateSeries([{
                            name: 'Incidents',
                            data: data.incidentCounts
                        }]);
                    }

                    if (typeof myChart2 !== 'undefined') {
                        myChart2.data.labels = data.topRiskLabels;
                        myChart2.data.datasets[0].label = primaryState;
                        myChart2.data.datasets[0].data = data.topRiskCounts;
                        myChart2.update();
                    }

                    if (typeof attackChart !== 'undefined') {
                        attackChart.data.labels = data.attackLabels;
                        attackChart.data.datasets[0].data = data.attackCounts;
                        attackChart.update();
                    }

                    // 5. Update Crime Score & Rank
                    safeSetText('crime-index-score', data.stateCrimeIndexScore);
                    const rankSpan = document.getElementById('state-rank');
                    const ordinalSup = document.getElementById('state-rank-ordinal');
                    if (rankSpan) rankSpan.textContent = data.stateRank;
                    if (ordinalSup) ordinalSup.textContent = data.stateRankOrdinal;

                    // 6. Update Crime Table
                    const crimeTableBody = document.getElementById('crime-table-body');
                    if (crimeTableBody) {
                        let crimeTableHtml = '';
                        if (data.crimeTable && data.crimeTable.length > 0) {
                            data.crimeTable.forEach(item => {
                                // Match the logic used in your PHP @php block
                                let pillClasses = 'bg-blue-500 text-white border-blue-500'; // Default

                                if (item.status === 'Escalating') {
                                    pillClasses = 'bg-red-500 text-white border-red-500';
                                } else if (item.status === 'Improving') {
                                    pillClasses = 'bg-green-500 text-white border-green-500';
                                }

                                crimeTableHtml += `
                <tr class="border-b border-gray-700">
                    <td class="py-4 px-4 font-medium whitespace-nowrap">${item.indicator_name}</td>
                    <td class="py-4 px-4 whitespace-nowrap">${item.incident_count}</td>
                    <td class="py-4 px-4 whitespace-nowrap">${item.previous_year_count}</td>
                    <td class="py-4 px-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold lowercase tracking-wider border ${pillClasses}">
                            ${item.status}
                        </span>
                    </td>
                </tr>`;
                            });
                        } else {
                            crimeTableHtml =
                                `<tr><td colspan="4" class="py-6 px-4 text-center text-gray-500">No crime index data found.</td></tr>`;
                        }
                        crimeTableBody.innerHTML = crimeTableHtml;
                    }

                    // 7. Update Automated Insights
                    if (typeof renderInsights === "function") renderInsights(data.automatedInsights);
                })
                .catch(error => {
                    console.error('Error fetching primary data:', error);
                    const errContainer = document.getElementById('insights-container');
                    if (errContainer) errContainer.innerHTML = '<p class="text-red-400 p-4">Error loading data.</p>';
                });
        }

        function openAuthModal() {
            document.getElementById('auth-modal').classList.remove('hidden');
        }

        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }

        function handleFilterChange() {
            const stateSelect = document.getElementById('state-select');
            const isAuth = stateSelect.getAttribute('data-auth') === 'true';
            const hasAccess = stateSelect.getAttribute('data-access') === 'true';


            if (!isAuth) {
                stateSelect.value = "{{ $state }}";
                openAuthModal();
                return;
            }

            if (isAuth && !hasAccess) {
                stateSelect.value = "{{ $state }}";
                document.getElementById('modal-register-state').classList.add('hidden');
                document.getElementById('modal-success-state').classList.remove('hidden');
                openAuthModal();
                return;
            }

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
                // MATCHING THE INITIAL RENDER LOGIC: Use text-gray-400 for all
                let titleColor = 'text-gray-400';
                let friendlyTitle = insight.type;

                // Map technical types to friendly titles as seen in your Blade switch
                if (insight.type === 'Velocity') {
                    friendlyTitle = 'Activity Pace';
                } else if (insight.type === 'Emerging Threat') {
                    friendlyTitle = 'Rising Risk';
                } else if (insight.type === 'Lethality') {
                    friendlyTitle = 'Severity Level';
                } else if (insight.type === 'Forecast') {
                    friendlyTitle = 'Future Outlook';
                }

                container.innerHTML += `
            <div class="bg-[#1E2D3D] p-4 rounded shadow-md">
                <h4 class="text-xs font-semibold ${titleColor} uppercase mb-1 tracking-wider">
                    ${friendlyTitle}
                </h4>
                <p class="text-white text-md">
                    ${insight.text}
                </p>
            </div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();

            @if (session('show_demo_popup'))
                document.getElementById('modal-register-state').classList.add('hidden');
                document.getElementById('modal-success-state').classList.remove('hidden');
                openAuthModal();
            @endif
        });
        document.getElementById('state-select').addEventListener('change', handleFilterChange);
        document.getElementById('year-select').addEventListener('change', handleFilterChange);
        document.getElementById('prevalent-compare-select').addEventListener('change', updatePrevalentComparison);
    </script>

</x-layout>

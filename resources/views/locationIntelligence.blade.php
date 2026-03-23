<x-layout title="Location Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria.">

    {{-- 1. Load ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-center text-2xl font-semibold text-white mb-8">
            Location Intelligence - <span id="state-name">{{ $state }}</span> State
        </h1>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 text-center mb-10">
            {{-- State Select --}}
            <div class="flex items-center space-x-2">
                <label for="state-select" class="text-sm font-medium text-gray-400">State:</label>
                <div class="relative">
                    <select id="state-select"
                        class="bg-[#131C27] text-white text-sm py-2 px-4 border border-gray-600 rounded-md focus:outline-none focus:border-emerald-500 hover:border-gray-500 transition-colors cursor-pointer pr-8">
                        @foreach ($getStates as $s)
                            <option value="{{ $s }}" {{ $s == $state ? 'selected' : '' }}>{{ $s }}
                            </option>
                        @endforeach
                    </select>

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
                <p class="text-lg md:text-lg font-light text-white tracking-tight">
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
                    class="text-lg md:text-lg font-light text-white leading-tight px-2">
                    <p>{{ $mostFrequentRisk->pluck('riskindicators')->implode(', ') ?: 'No data available' }}</p>
                </div>
            </div>

            {{-- 3. Most Affected LGA Card --}}
            <div id="most-affected-lga"
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg text-center border border-white/5 flex flex-col justify-center min-h-[140px]">
                <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Most Affected LGA
                </h3>
                <p class="text-lg md:text-lg font-light text-white tracking-wide">
                    @if ($mostAffectedLGA)
                        {{ $mostAffectedLGA->lga }}
                    @else
                        <span class="text-gray-500 font-light italic">No data available</span>
                    @endif
                </p>
            </div>

            {{-- 4. Crime Index Score Card --}}
            <div
                class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg text-center border border-white/5 relative group flex flex-col justify-center min-h-[140px]">
                <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    Operational Score
                </h3>

                {{-- Flex row to place Rank beside the Score --}}
                <div class="flex items-center justify-center gap-3">
                    <p id="crime-index-score" class="text-lg md:text-lg font-light text-white tracking-tight">
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

                        <p class="text-white text-base font-light">
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
                <h3 id="trend-title" class="text-center text-lg md:text-xl font-semibold text-gray-400 mb-4"> Incidents
                    in {{ $year }} (Monthly)</h3>
                {{-- ApexChart Container --}}
                <div id="incidentsTrendChart" style="min-height: 320px;"></div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1E2D3D] p-4 md:p-6 rounded-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
                    <h3 class="text-center sm:text-left text-lg md:text-xl font-semibold text-gray-400">Prevalent Risk
                    </h3>

                    <div class="relative w-full sm:w-auto">
                        <select id="prevalent-compare-select"
                            class="bg-[#131C27] text-white text-xs py-1 px-3 border border-gray-600 rounded hover:border-gray-400 focus:outline-none focus:border-emerald-500 transition-colors w-full pr-8 cursor-pointer">
                            <option value="" selected>Compare...</option>
                            @foreach ($getStates as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>


                    </div>
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
            <h4 class="text-lg font-semibold  text-gray-400 mb-4">Operational Risk </h4>
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
                                    $pillClasses = 'bg-blue text-white uppercase';

                                    if ($item['status'] === 'Escalating') {
                                        $pillClasses = 'bg-red-500 text-white border-red-500 uppercase';
                                    } elseif ($item['status'] === 'Improving') {
                                        $pillClasses = 'bg-green-500 text-white border-green-500 uppercase';
                                    }
                                @endphp

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wider border {{ $pillClasses }}">
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
    <x-auth-required-modal />
    <x-tier-lock-modal />


    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // ✅ Auth flag (server-side)
        const IS_AUTH = @json(auth()->check());

        // ✅ Modal helpers (works with your #authRequiredModal markup)
        function openAuthModal() {
            const modal = document.getElementById("authRequiredModal");
            if (!modal) return;

            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.style.overflow = "hidden";
        }

        function closeAuthModal() {
            const modal = document.getElementById("authRequiredModal");
            if (!modal) return;

            modal.classList.add("hidden");
            modal.classList.remove("flex");
            document.body.style.overflow = "";
        }

        function openTierLockModal(payload = {}) {
            const modal = document.getElementById("tierLockModal");
            if (!modal) return;

            // elements
            const title = document.getElementById("tierLockTitle");
            const subtitle = document.getElementById("tierLockSubtitle");
            const msg = document.getElementById("tierLockMessage");
            const loc = document.getElementById("tierLockLocation");
            const when = document.getElementById("tierLockWhen");
            const label1 = document.getElementById("tierLockLabel1");
            const label2 = document.getElementById("tierLockLabel2");
            const footer = document.getElementById("tierLockFooterText");
            const cta = document.getElementById("tierLockCta");

            // Decide context
            // Example payloads:
            // { context:"location", locked_location:"Lagos", switch_available_at:"2026-03-01", message:"..." }
            // { context:"risk", locked_index:"Kidnapping Index", allowed:{index_type:"Composite Risk Index", year:2026}, message:"..." }
            const context = payload.context || (payload.locked_location ? "location" : "risk");

            if (context === "location") {
                if (title) title.textContent = "Location Locked";
                if (subtitle) subtitle.textContent = "You can explore only one location on the free plan.";
                if (label1) label1.textContent = "Locked location";
                if (label2) label2.textContent = "Next switch";
                if (footer) footer.textContent = "Unlock all States and 774 LGAs with premium access.";
                if (loc) loc.textContent = (payload.locked_location || "").toString().toUpperCase();
                if (when) when.textContent = payload.switch_available_at || "—";
            } else {
                // Risk analysis locking (index/year)
                if (title) title.textContent = "Premium Feature";
                if (subtitle) subtitle.textContent = "This analysis option is locked on your plan.";
                if (label1) label1.textContent = "Locked option";
                if (label2) label2.textContent = "Allowed now";
                if (footer) footer.textContent = "Upgrade to access all indices and historical years.";

                const lockedOpt =
                    payload.locked_index ||
                    payload.locked_year ||
                    payload.locked_item ||
                    payload.locked_location || "";

                const allowedNow = payload.allowed ?
                    `${payload.allowed.index_type || "Composite Risk Index"} • ${payload.allowed.year || "Current Year"}` :
                    "Composite Risk Index • Current Year";

                if (loc) loc.textContent = lockedOpt || "Premium option";
                if (when) when.textContent = allowedNow;
            }

            if (msg) msg.textContent = payload.message || "This option is locked on your plan.";

            // Optional: set CTA link if you want
            if (cta && payload.cta_url) cta.href = payload.cta_url;

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




        // ✅ Guard wrapper: block action + open modal
        function requireAuth(actionFn) {
            return function(...args) {
                if (!IS_AUTH) {
                    openAuthModal();
                    return;
                }
                return actionFn.apply(this, args);
            };
        }

        let incidentsTrendChart;
        let myChart2, attackChart;
        let map, geojsonLayer, info;
        let lgaGeoJsonData;
        let lgaIncidentData = {};

        let lastAllowedState = null;
        let lastAllowedYear = null;
        let lastAllowedCompare = "";

        // 1) Initialize Charts + Map
        async function initializeCharts() {
            try {
                const response = await fetch("/nigeria-lga.json");
                lgaGeoJsonData = await response.json();
            } catch (e) {
                console.error("CRITICAL: Could not load nigeria-lga.json.", e);
                const t = document.getElementById("map-title");
                if (t) t.textContent = "Map failed to load.";
                return;
            }

            const defaultState =
                (document.getElementById("state-name")?.textContent || "").trim() || "Primary State";
            const defaultYear = document.getElementById("year-select")?.value;

            // --- ApexCharts Trend ---
            const trendOptions = {
                series: [{
                    name: "Incidents",
                    data: @json($incidentCounts)
                }],
                chart: {
                    type: "area",
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    background: "transparent",
                    fontFamily: "inherit",
                },
                colors: ["#10B981"],
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: 2
                },
                xaxis: {
                    categories: @json($chartLabels),
                    labels: {
                        style: {
                            colors: "#9CA3AF"
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
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: "#9CA3AF"
                        }
                    }
                },
                grid: {
                    borderColor: "#374151",
                    strokeDashArray: 4
                },
                theme: {
                    mode: "dark"
                },
                tooltip: {
                    theme: "dark"
                },
            };

            incidentsTrendChart = new ApexCharts(document.querySelector("#incidentsTrendChart"), trendOptions);
            incidentsTrendChart.render();

            // --- Chart.js Prevalent Risk ---
            const ctx2 = document.getElementById("myChart2")?.getContext("2d");
            if (ctx2) {
                myChart2 = new Chart(ctx2, {
                    type: "bar",
                    data: {
                        labels: @json($topRiskLabels),
                        datasets: [{
                            label: defaultState,
                            data: @json($topRiskCounts),
                            backgroundColor: ["rgba(27, 158, 133, 0.7)"],
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
                                    color: "white"
                                },
                                grid: {
                                    color: "rgba(255,255,255,0.1)"
                                }
                            },
                            x: {
                                ticks: {
                                    color: "white"
                                },
                                grid: {
                                    color: "rgba(255,255,255,0.1)"
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: "white"
                                }
                            }
                        }
                    }
                });
            }

            // --- Chart.js Actors ---
            const attackCtx = document.getElementById("attackChart")?.getContext("2d");
            if (attackCtx) {
                attackChart = new Chart(attackCtx, {
                    type: "pie",
                    data: {
                        labels: @json($attackLabels),
                        datasets: [{
                            label: "Attack Occurrences",
                            data: @json($attackCounts),
                            backgroundColor: [
                                "rgba(27, 158, 133, 0.7)",
                                "rgba(54, 162, 235, 0.7)",
                                "rgba(255, 206, 86, 0.7)",
                                "rgba(75, 192, 192, 0.7)",
                                "rgba(153, 102, 255, 0.7)",
                                "rgba(255, 99, 132, 0.7)",
                                "rgba(255, 159, 64, 0.7)",
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: "top",
                                labels: {
                                    color: "white"
                                }
                            }
                        }
                    }
                });
            }

            // --- Leaflet Map ---
            map = L.map("map").setView([9.082, 8.6753], 6);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; OpenStreetMap",
            }).addTo(map);

            geojsonLayer = L.geoJson(null, {
                style: styleFeature,
                onEachFeature
            }).addTo(map);

            info = L.control();
            info.onAdd = function() {
                this._div = L.DomUtil.create("div", "info");
                this.update();
                return this._div;
            };
            info.update = function(props) {
                const count = props ? (lgaIncidentData[props.NAME_2] || 0) : null;
                this._div.innerHTML =
                    "<h4>Incident Count by LGA</h4>" +
                    (props ? `<b>${props.NAME_2}</b><br />${count ?? 0} Incidents` : "Hover over an LGA");
            };
            info.addTo(map);

            // ✅ IMPORTANT: load full dashboard on first render too
            if (defaultYear) {
                updateMap(defaultState, defaultYear); // map can update for everyone
                if (IS_AUTH) updateMainDashboard(defaultState, defaultYear);
            }
        }

        // 2) Comparison
        function updatePrevalentComparison() {
            const compareState = document.getElementById("prevalent-compare-select")?.value;
            const year = document.getElementById("year-select")?.value;

            if (!compareState || !myChart2) {
                if (myChart2?.data?.datasets?.length > 1) {
                    myChart2.data.datasets.pop();
                    myChart2.update();
                }
                return;
            }

            const primaryLabels = myChart2.data.labels;
            const params = new URLSearchParams();
            params.append("state", compareState);
            params.append("year", year);
            primaryLabels.forEach((label) => params.append("indicators[]", label));

            fetch(`/get-comparison-risk-counts?${params.toString()}`)
                .then((r) => r.json())
                .then((data) => {
                    const comparisonDataset = {
                        label: compareState,
                        data: data.counts,
                        backgroundColor: "#3b82f6",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                    };

                    if (myChart2.data.datasets.length > 1) myChart2.data.datasets[1] = comparisonDataset;
                    else myChart2.data.datasets.push(comparisonDataset);

                    myChart2.update();
                })
                .catch((err) => console.error("Error fetching prevalent risk comparison:", err));
        }

        // 3) Map update
        async function updateMap(state, year) {
            const title = document.getElementById("map-title");
            if (title) title.textContent = `Incident Density in ${state} (${year})`;

            try {
                const response = await fetch(`/get-lga-incident-counts/${state}/${year}`);
                lgaIncidentData = await response.json();
            } catch (e) {
                console.error("Could not fetch LGA incident data", e);
                lgaIncidentData = {};
            }

            if (!lgaGeoJsonData?.features?.length) return;

            const stateFeatures = lgaGeoJsonData.features.filter(
                (feature) => feature.properties.NAME_1.toLowerCase().trim() === state.toLowerCase().trim()
            );

            if (!geojsonLayer || !map) return;

            if (stateFeatures.length === 0) {
                geojsonLayer.clearLayers();
                map.setView([9.082, 8.6753], 6);
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
            return count > 500 ? "#7F1D1D" :
                count > 200 ? "#991B1B" :
                count > 100 ? "#B91C1C" :
                count > 50 ? "#DC2626" :
                count > 20 ? "#EF4444" :
                count > 10 ? "#F87171" :
                count > 0 ? "#FCA5A5" : "#FCA5A5";
        }

        function styleFeature(feature) {
            const lgaName = feature.properties.NAME_2;
            const count = lgaIncidentData[lgaName] || 0;
            return {
                fillColor: getColor(count),
                weight: 2,
                opacity: 1,
                color: "white",
                dashArray: "3",
                fillOpacity: 0.7
            };
        }

        function onEachFeature(feature, layer) {
            layer.on({
                mouseover: function(e) {
                    const l = e.target;
                    l.setStyle({
                        weight: 4,
                        color: "#666",
                        dashArray: "",
                        fillOpacity: 0.9
                    });
                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) l.bringToFront();
                    info.update(l.feature.properties);
                },
                mouseout: function(e) {
                    geojsonLayer.resetStyle(e.target);
                    info.update();
                },
                click: function(e) {
                    map.fitBounds(e.target.getBounds());
                },
            });
        }

        // ✅ 4) THIS is what updates EVERYTHING (cards + charts + table + insights)
        function updateMainDashboard(primaryState, selectedYear) {
            if (!primaryState || !selectedYear) return;

            updateMap(primaryState, selectedYear);

            const trendTitle = document.getElementById("trend-title");
            if (trendTitle) trendTitle.textContent = `Incidents in ${selectedYear} (Monthly)`;

            // reset compare
            const prevalentDropdown = document.getElementById("prevalent-compare-select");
            if (prevalentDropdown) {
                prevalentDropdown.value = "";
                if (myChart2?.data?.datasets?.length > 1) {
                    myChart2.data.datasets.pop();
                    myChart2.update();
                }
            }

            const safeSetText = (id, text) => {
                const el = document.getElementById(id);
                if (!el) return;
                const p = el.querySelector("p");
                if (p) p.textContent = text;
                else el.textContent = text;
            };

            const safeSetHTML = (id, html) => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = html;
            };

            safeSetText("total-incidents", "...");
            safeSetHTML("most-frequent-risk-content", "<p>Loading...</p>");
            safeSetText("most-affected-lga", "...");
            safeSetHTML("insights-container", '<p class="text-gray-400 p-4">Analyzing data patterns...</p>');
            safeSetText("crime-index-score", "...");
            safeSetHTML("crime-table-body",
                '<tr><td colspan="4" class="py-6 px-4 text-center text-gray-500">Loading...</td></tr>');

            fetch(`/get-state-data/${primaryState}/${selectedYear}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(async (r) => {
                    // always attempt JSON, but don't crash if it's not JSON
                    const data = await r.json().catch(() => ({}));

                    if (!r.ok) {
                        // ✅ Tier2 lock case
                        if (r.status === 403 && data?.upgrade) {
                            openTierLockModal(data);

                            // rollback dropdowns + state title + URL
                            const stateSelect = document.getElementById("state-select");
                            const yearSelect = document.getElementById("year-select");

                            if (stateSelect && lastAllowedState) stateSelect.value = lastAllowedState;
                            if (yearSelect && lastAllowedYear) yearSelect.value = lastAllowedYear;

                            const stateName = document.getElementById("state-name");
                            if (stateName) stateName.textContent = lastAllowedState || primaryState;

                            // restore URL (no new history entry)
                            if (lastAllowedState && lastAllowedYear) {
                                const backUrl = `/location-intelligence/${lastAllowedState}/${lastAllowedYear}`;
                                window.history.replaceState({
                                        state: lastAllowedState,
                                        year: lastAllowedYear
                                    },
                                    "",
                                    backUrl
                                );

                                // reload allowed dashboard
                                updateMainDashboard(lastAllowedState, lastAllowedYear);
                            }

                            return null; // stop normal success flow
                        }

                        // other failures
                        throw new Error(data?.message || `Request failed (${r.status})`);
                    }

                    return data;
                })
                .then((data) => {
                    if (!data) return; // handled above

                    // ✅ mark this as the last allowed selection ONLY after success
                    lastAllowedState = primaryState;
                    lastAllowedYear = selectedYear;


                    // ✅ keep ALL your existing success logic as-is from here ↓
                    safeSetText("total-incidents", data.total_incidents);
                    const totalTitle = document.getElementById("total-incidents-title");
                    if (totalTitle) totalTitle.textContent = `Tracked Incidents (${selectedYear})`;

                    const riskContent = document.getElementById("most-frequent-risk-content");
                    if (riskContent) {
                        let riskText = "No data available";
                        if (data.mostFrequentRisk?.length) {
                            riskText = data.mostFrequentRisk.map((r) => r.riskindicators).join(", ");
                        }
                        riskContent.innerHTML = `<p>${riskText}</p>`;
                    }

                    safeSetText("most-affected-lga", data.mostAffectedLGA ? data.mostAffectedLGA.lga : "None");

                    if (incidentsTrendChart) {
                        incidentsTrendChart.updateOptions({
                            xaxis: {
                                categories: data.chartLabels
                            }
                        });
                        incidentsTrendChart.updateSeries([{
                            name: "Incidents",
                            data: data.incidentCounts
                        }]);
                    }

                    if (myChart2) {
                        myChart2.data.labels = data.topRiskLabels;
                        myChart2.data.datasets[0].label = primaryState;
                        myChart2.data.datasets[0].data = data.topRiskCounts;
                        myChart2.update();
                    }

                    if (attackChart) {
                        attackChart.data.labels = data.attackLabels;
                        attackChart.data.datasets[0].data = data.attackCounts;
                        attackChart.update();
                    }

                    safeSetText("crime-index-score", data.stateCrimeIndexScore);
                    const rankSpan = document.getElementById("state-rank");
                    const ordinalSup = document.getElementById("state-rank-ordinal");
                    if (rankSpan) rankSpan.textContent = data.stateRank;
                    if (ordinalSup) ordinalSup.textContent = data.stateRankOrdinal;

                    const crimeTableBody = document.getElementById("crime-table-body");
                    if (crimeTableBody) {
                        let html = "";
                        if (data.crimeTable?.length) {
                            data.crimeTable.forEach((item) => {
                                let pill = "bg-blue-500 text-white border-blue-500 uppercase";
                                if (item.status === "Escalating") pill =
                                    "bg-red-500 text-white border-red-500 uppercase";
                                else if (item.status === "Improving") pill =
                                    "bg-green-500 text-white border-green-500 uppercase";

                                html += `
                    <tr class="border-b border-gray-700">
                        <td class="py-4 px-4 font-medium whitespace-nowrap">${item.indicator_name}</td>
                        <td class="py-4 px-4 whitespace-nowrap">${item.incident_count}</td>
                        <td class="py-4 px-4 whitespace-nowrap">${item.previous_year_count}</td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wider border ${pill}">
                                ${item.status}
                            </span>
                        </td>
                    </tr>`;
                            });
                        } else {
                            html =
                                `<tr><td colspan="4" class="py-6 px-4 text-center text-gray-500">No crime index data found.</td></tr>`;
                        }
                        crimeTableBody.innerHTML = html;
                    }

                    if (typeof renderInsights === "function") renderInsights(data.automatedInsights);
                })
                .catch((err) => {
                    console.error("Error fetching primary data:", err);
                    const c = document.getElementById("insights-container");
                    if (c) c.innerHTML = '<p class="text-red-400 p-4">Error loading data.</p>';
                });

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
                <p class="text-white text-md font-light">
                    ${insight.text}
                </p>
            </div>`;
            });
        }

        // ✅ Visitor-block: prevent opening select
        function blockSelectOpen(e, selectEl, fallbackValueGetter) {
            if (IS_AUTH) return;

            const fallback = typeof fallbackValueGetter === "function" ? fallbackValueGetter() : "";
            selectEl.value = fallback;

            e.preventDefault();
            e.stopPropagation();

            openAuthModal();
            selectEl.blur();
        }

        function handleFilterChange() {
            const stateSelect = document.getElementById("state-select");
            const yearSelect = document.getElementById("year-select");

            const primaryState = stateSelect.value;
            const selectedYear = yearSelect.value;



            document.getElementById("state-name").textContent = primaryState;

            const newUrl = `/location-intelligence/${primaryState}/${selectedYear}`;
            window.history.pushState({
                state: primaryState,
                year: selectedYear
            }, "", newUrl);

            // ✅ IMPORTANT: this refreshes the full dashboard
            updateMainDashboard(primaryState, selectedYear);
        }

        function handleCompareChange() {
            lastAllowedCompare = document.getElementById("prevalent-compare-select")?.value || "";
            updatePrevalentComparison();
        }

        window.addEventListener("popstate", function(event) {
            if (event.state?.state && event.state?.year) {
                const stateSelect = document.getElementById("state-select");
                const yearSelect = document.getElementById("year-select");

                stateSelect.value = event.state.state;
                yearSelect.value = event.state.year;

                updateMainDashboard(event.state.state, event.state.year);
            }
        });

        document.addEventListener("DOMContentLoaded", function() {

            document.getElementById("tierLockClose")?.addEventListener("click", closeTierLockModal);
            document.getElementById("tierLockOk")?.addEventListener("click", closeTierLockModal);
            document.getElementById("tierLockModal")?.addEventListener("click", function(e) {
                if (e.target === this) closeTierLockModal();
            });
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") closeTierLockModal();
            });

            // last allowed values
            lastAllowedState = document.getElementById("state-select")?.value;
            lastAllowedYear = document.getElementById("year-select")?.value;
            lastAllowedCompare = document.getElementById("prevalent-compare-select")?.value || "";

            // modal close wiring
            document.getElementById("authModalClose")?.addEventListener("click", closeAuthModal);
            document.getElementById("authRequiredModal")?.addEventListener("click", function(e) {
                if (e.target === this) closeAuthModal();
            });
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") closeAuthModal();
            });

            initializeCharts();
            if (window.__TIER_LOCK_FLASH__) {
                openTierLockModal(window.__TIER_LOCK_FLASH__);
            }


            const stateSelect = document.getElementById("state-select");
            const yearSelect = document.getElementById("year-select");
            const compareSelect = document.getElementById("prevalent-compare-select");

            // ✅ Only block opening for visitors
            if (!IS_AUTH) {
                if (stateSelect) {
                    stateSelect.addEventListener("pointerdown", (e) => blockSelectOpen(e, stateSelect, () =>
                        lastAllowedState));
                    stateSelect.addEventListener("mousedown", (e) => blockSelectOpen(e, stateSelect, () =>
                        lastAllowedState));
                    stateSelect.addEventListener("focus", (e) => blockSelectOpen(e, stateSelect, () =>
                        lastAllowedState));
                }
                if (yearSelect) {
                    yearSelect.addEventListener("pointerdown", (e) => blockSelectOpen(e, yearSelect, () =>
                        lastAllowedYear));
                    yearSelect.addEventListener("mousedown", (e) => blockSelectOpen(e, yearSelect, () =>
                        lastAllowedYear));
                    yearSelect.addEventListener("focus", (e) => blockSelectOpen(e, yearSelect, () =>
                        lastAllowedYear));
                }
                if (compareSelect) {
                    compareSelect.addEventListener("pointerdown", (e) => blockSelectOpen(e, compareSelect, () =>
                        lastAllowedCompare));
                    compareSelect.addEventListener("mousedown", (e) => blockSelectOpen(e, compareSelect, () =>
                        lastAllowedCompare));
                    compareSelect.addEventListener("focus", (e) => blockSelectOpen(e, compareSelect, () =>
                        lastAllowedCompare));
                }
            }

            // ✅ Change handlers (guarded)
            stateSelect?.addEventListener("change", requireAuth(handleFilterChange));
            yearSelect?.addEventListener("change", requireAuth(handleFilterChange));
            compareSelect?.addEventListener("change", requireAuth(handleCompareChange));
        });
    </script>




</x-layout>

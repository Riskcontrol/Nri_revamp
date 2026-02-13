<div class="max-w-7xl mx-auto">

    <form id="filter-form">
        <section class="max-w-7xl mx-auto bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl py-8 px-6">

            <div class="space-y-2 text-center">
                <h1 id="main-title" class="text-2xl font-semibold text-white mt-2">Nigeria Composite Risk Index</h1>
                <p class="text-sm font-medium text-gray-300"> A Data-Driven Approach to Understanding Nigeria's Security
                    Threats
                </p>
            </div>
            @guest
                <div class="mt-4 bg-blue-900/30 border border-blue-500 rounded-lg p-4 text-center">
                    <p class="text-blue-200 text-sm">
                        📊 <strong>Preview Mode:</strong> Showing current year data only.
                        <a href="{{ route('login') }}" class="underline hover:text-blue-100">Sign in</a>
                        to access full historical data and analytics.
                    </p>
                </div>
            @endguest

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-center sm:space-x-4 space-y-4 sm:space-y-0">

                <div>
                    <div class="relative mt-1">
                        <select id="index_type" name="index_type"
                            class="block w-full sm:w-60 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option selected>Composite Risk Index</option>
                            <option>Terrorism Index</option>
                            <option>Kidnapping Index</option>

                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 12.586l-4.293-4.293a1 1 0 011.414-1.414L10 9.758l3.879-3.879a1 1 0 111.414 1.414L10 12.586z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>


                <div>
                    <div class="relative mt-1">
                        <select id="year" name="year"
                            class="block w-full sm:w-40 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">

                            @php
                                $currentYear = date('Y');
                                $startYear = 2018;
                            @endphp

                            @foreach (range($currentYear, $startYear) as $y)
                                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 12.586l-4.293-4.293a1 1 0 011.414-1.414L10 9.758l3.879-3.879a1 1 0 111.414 1.414L10 12.586z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </form>

    {{-- UPDATED GRID: Changed to md:grid-cols-2 since we removed one card --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Threat Level Card REMOVED --}}

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Tracked Incidents</h3>
            <div class="mt-4">
                <p id="card-risk-index" class="text-md font-medium text-white">...</p>
            </div>
        </div>

        {{-- 2. NEW: Fatalities Card --}}
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Fatalities</h3>
            <div class="mt-4">
                <p id="card-fatalities" class="text-md font-medium text-gray-100">...</p>
            </div>
        </div>

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">ACTIVE THREAT GROUPS</h3>
            <p id="card-top-threats" class="text-md font-medium text-gray-100 mt-4">
                .....
            </p>
        </div>
    </div>

    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md ">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            {{-- <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                Top Insights
            </h3> --}}
            <span id="insight-badge"
                class="mt-2 sm:mt-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-900 text-indigo-200 border border-indigo-700">
                Loading Analysis...
            </span>
        </div>

        <div class="relative">
            <div id="insight-loading"
                class="hidden absolute inset-0 bg-[#1E2D3D] z-10 flex items-center justify-center">
                <p class="text-gray-400 text-sm animate-pulse">Updating intelligence...</p>
            </div>

            <ul id="insight-list" class="space-y-4">
            </ul>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6">

        {{-- Geographic Analysis Chart --}}
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-400">Geographic Analysis</h3>
            <div class="mt-4">
                <div id="treemap-chart"></div>
            </div>
        </div>

        {{-- Fatality Trend Line Chart --}}
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 id="line-chart-title" class="text-xl font-semibold text-gray-400">Fatality Trend</h3>
            <div class="mt-4">
                <div id="fatality-line-chart"></div>
            </div>
        </div>

    </div>

    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md lg:col-span-3">
        <h3 id="table-title" class="text-xl font-semibold text-gray-400 mb-4">State Risk Ranking</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-700 text-left text-xs text-gray-400 uppercase">
                        <th class="py-3 px-4">State</th>
                        <th class="py-3 px-4">Risk Score</th>
                        <th class="py-3 px-4">Risk Level</th>
                        <th class="py-3 px-4">Rank (Current)</th>
                        <th class="py-3 px-4">Rank (Previous)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tracked Incidents</th>
                    </tr>
                </thead>
                <tbody id="risk-table-body" class="text-gray-200">
                    <tr>
                        <td colspan="7" class="py-10 px-4 text-center text-gray-500">
                            Loading risk table...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
<x-auth-required-modal />


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // ✅ Auth flag (server-side)
    const IS_AUTH = @json(auth()->check());

    // ---------------------------
    // Modal open/close
    // ---------------------------
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

    // ✅ Guard wrapper
    function requireAuth(actionFn) {
        return function(...args) {
            if (!IS_AUTH) {
                openAuthModal();
                return;
            }
            return actionFn.apply(this, args);
        };
    }

    // ✅ Visitor-block: prevent opening select UI
    function blockSelectOpen(e, selectEl, fallbackGetter) {
        if (IS_AUTH) return;

        const fallback = typeof fallbackGetter === "function" ? fallbackGetter() : "";
        if (selectEl) selectEl.value = fallback;

        e.preventDefault();
        e.stopPropagation();

        openAuthModal();
        selectEl?.blur();
    }

    // ---------------------------
    // Helpers (global)
    // ---------------------------
    function getRiskCategory(value) {
        if (value <= 1.5) return "Low";
        if (value <= 3.5) return "Medium";
        if (value <= 7.0) return "High";
        return "Very High";
    }

    function getRiskLevelClass(riskLevel) {
        switch (riskLevel) {
            case "Low":
                return "bg-green-600 text-white";
            case "Medium":
                return "bg-yellow-500 text-white";
            case "High":
                return "bg-[#fc4444] text-white";
            case "Very High":
                return "bg-red-700 text-white";
            default:
                return "bg-gray-500 text-white";
        }
    }

    function showInsightsLoading() {
        const overlay = document.getElementById("insight-loading");
        const badge = document.getElementById("insight-badge");
        const list = document.getElementById("insight-list");

        if (badge) badge.textContent = "Generating insights...";
        if (overlay) overlay.classList.remove("hidden");

        if (list) {
            list.style.opacity = "0.6";
            list.innerHTML = `<li class="text-gray-400 text-sm">Updating intelligence…</li>`;
        }
    }

    function hideInsightsLoading() {
        const overlay = document.getElementById("insight-loading");
        const list = document.getElementById("insight-list");
        if (overlay) overlay.classList.add("hidden");
        if (list) list.style.opacity = "1";
    }

    function renderAiInsights(aiInsights, selectedType) {
        const listContainer = document.getElementById("insight-list");
        const badge = document.getElementById("insight-badge");
        if (!listContainer || !badge) return;

        badge.textContent = `${selectedType} AI Insights`;

        let htmlContent = "";
        (aiInsights || []).slice(0, 3).forEach((item) => {
            htmlContent += `
        <li class="bg-[#2b3a4a] p-4 rounded border-l-4 border-indigo-500 transition-colors duration-300">
          <div class="flex flex-col">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-1">${item.title ?? "Insight"}</span>
            <p class="text-gray-200 text-sm leading-relaxed whitespace-pre-line">${item.text ?? ""}</p>
          </div>
        </li>
      `;
        });

        listContainer.style.opacity = "0";
        setTimeout(() => {
            listContainer.innerHTML =
                htmlContent ||
                `<li class="text-gray-400 text-sm">No AI insights available.</li>`;
            listContainer.style.opacity = "1";
            hideInsightsLoading();
        }, 150);
    }

    function updateRiskTable(tableData) {
        const tableBody = document.getElementById("risk-table-body");
        if (!tableBody) return;

        if (!tableData || tableData.length === 0) {
            tableBody.innerHTML =
                `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">No data available for this filter.</td></tr>`;
            return;
        }

        let tableHtml = "";
        tableData.forEach((state) => {
            let statusColorClass = "text-gray-400";
            if (state.status === "Escalating") statusColorClass = "text-red-500";
            else if (state.status === "Improving") statusColorClass = "text-green-500";

            tableHtml += `
        <tr class="border-b border-gray-700 hover:bg-gray-700">
          <td class="py-3 px-4 font-medium">${state.state}</td>
          <td class="py-3 px-4">${state.risk_score}%</td>
          <td class="py-3 px-4">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${getRiskLevelClass(state.risk_level)}">
              ${state.risk_level}
            </span>
          </td>
          <td class="py-3 px-4">${state.rank_current}</td>
          <td class="py-3 px-4">${state.rank_previous}</td>
          <td class="py-3 px-4 font-semibold ${statusColorClass}">${state.status}</td>
          <td class="py-3 px-4">${state.incidents}</td>
        </tr>
      `;
        });

        tableBody.innerHTML = tableHtml;
    }

    // ---------------------------
    // Main
    // ---------------------------
    document.addEventListener("DOMContentLoaded", function() {
        // Modal close wiring
        document.getElementById("authModalClose")?.addEventListener("click", closeAuthModal);
        document.getElementById("authRequiredModal")?.addEventListener("click", function(e) {
            if (e.target === this) closeAuthModal();
        });
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") closeAuthModal();
        });

        const indexSelect = document.getElementById("index_type");
        const yearSelect = document.getElementById("year");

        // Track last allowed values (for guests)
        let lastAllowedIndex = indexSelect?.value ?? "Composite Risk Index";
        let lastAllowedYear = yearSelect?.value ?? "";

        // Create charts FIRST
        const treemapEl = document.querySelector("#treemap-chart");
        const lineEl = document.querySelector("#fatality-line-chart");

        if (!treemapEl || !lineEl) {
            console.error("Missing chart containers (#treemap-chart or #fatality-line-chart).");
            return;
        }

        const treemapOptions = {
            series: [],
            chart: {
                height: 400,
                type: "treemap",
                toolbar: {
                    show: false
                }
            },
            title: {
                text: "Geographic Risk Analysis by State",
                align: "center",
                style: {
                    fontSize: "16px",
                    fontWeight: "bold",
                    color: "#FFFFFF"
                }
            },
            plotOptions: {
                treemap: {
                    enableShades: false,
                    colorScale: {
                        ranges: [{
                                from: 0,
                                to: 1.7,
                                color: "#10b981"
                            },
                            {
                                from: 1.71,
                                to: 2.8,
                                color: "#FFB020"
                            },
                            {
                                from: 2.81,
                                to: 7.0,
                                color: "#fc4444"
                            },
                            {
                                from: 7.01,
                                to: 100,
                                color: "#c40000"
                            }
                        ]
                    },
                    dataLabels: {
                        style: {
                            colors: ["#000"]
                        }
                    }
                }
            },
            tooltip: {
                theme: "dark",
                y: {
                    formatter: function(value) {
                        const category = getRiskCategory(value);
                        const formattedValue = parseFloat(value).toFixed(2);
                        return formattedValue + "% Risk (" + category + ")";
                    }
                }
            },
            noData: {
                text: "Loading Risk Data..."
            }
        };

        const chart = new ApexCharts(treemapEl, treemapOptions);
        chart.render();

        const lineOptions = {
            series: [{
                name: "Fatalities",
                data: []
            }],
            chart: {
                height: 350,
                type: "line",
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true
                }
            },
            stroke: {
                curve: "smooth",
                width: 3
            },
            colors: ["#ef4444"],
            xaxis: {
                categories: [],
                labels: {
                    style: {
                        colors: "#94a3b8"
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: "#94a3b8"
                    }
                }
            },
            grid: {
                borderColor: "#334155"
            },
            tooltip: {
                theme: "dark"
            }
        };

        const fatalityChart = new ApexCharts(lineEl, lineOptions);
        fatalityChart.render();

        // ✅ Update function - NOW WORKS FOR BOTH AUTH AND GUEST
        const updateChartData = function() {
            const titleElement = document.getElementById("main-title");
            const tableTitleElement = document.getElementById("table-title");

            const selectedIndexText = indexSelect?.options?.[indexSelect.selectedIndex]?.text ??
                "Composite Risk Index";
            const selectedYear = yearSelect?.value ?? "";

            if (titleElement) titleElement.textContent = `${selectedIndexText} - ${selectedYear}`;
            if (tableTitleElement) tableTitleElement.textContent =
                `${selectedIndexText}: State Risk Ranking`;

            const indexType = indexSelect?.value ?? "Composite Risk Index";
            const year = selectedYear;

            showInsightsLoading();

            chart.updateOptions({
                noData: {
                    text: "Loading filtered data..."
                }
            });
            document.getElementById("risk-table-body").innerHTML =
                `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">Loading risk table...</td></tr>`;

            document.getElementById("card-risk-index").textContent = "...";
            document.getElementById("card-top-threats").textContent = "Loading...";
            document.getElementById("card-fatalities").textContent = "...";

            // ✅ Choose endpoint based on auth status
            const endpoint = IS_AUTH ?
                `/risk-treemap-data?year=${encodeURIComponent(year)}&index_type=${encodeURIComponent(indexType)}` :
                '/risk-preview-data';

            fetch(endpoint, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then((r) => r.json())
                .then((data) => {
                    chart.updateSeries(data.treemapSeries || []);
                    updateRiskTable(data.tableData || []);

                    document.getElementById("card-risk-index").textContent = data?.cardData
                        ?.totalTrackedIncidents ?? 0;
                    document.getElementById("card-top-threats").textContent = data?.cardData
                        ?.topThreatGroups ?? "N/A";
                    document.getElementById("card-fatalities").textContent = new Intl.NumberFormat()
                        .format(
                            data?.cardData?.totalFatalities ?? 0
                        );

                    if (data?.trendSeries?.labels) {
                        fatalityChart.updateOptions({
                            xaxis: {
                                categories: data.trendSeries.labels
                            }
                        });
                        fatalityChart.updateSeries([{
                            name: "Fatalities",
                            data: data.trendSeries.data || []
                        }]);

                        const lineTitle = document.getElementById("line-chart-title");
                        if (lineTitle) lineTitle.textContent = `${selectedIndexText} Fatality Trend`;
                    }

                    renderAiInsights(data.aiInsights || [], selectedIndexText);

                    const badge = document.getElementById("insight-badge");
                    if (badge) {
                        badge.textContent =
                            data?.aiMeta?.source === "groq" ?
                            `${selectedIndexText} Analysis` :
                            `${selectedIndexText} ${IS_AUTH ? 'Fallback' : 'Preview'} Insights`;
                    }
                })
                .catch((error) => {
                    console.error("Error fetching chart data:", error);
                    chart.updateOptions({
                        noData: {
                            text: "Failed to load data."
                        }
                    });

                    document.getElementById("risk-table-body").innerHTML =
                        `<tr><td colspan="7" class="py-10 px-4 text-center text-red-500">Failed to load table data.</td></tr>`;

                    document.getElementById("card-risk-index").textContent = "N/A";
                    document.getElementById("card-top-threats").textContent = "Error";
                    document.getElementById("card-fatalities").textContent = "N/A";
                    hideInsightsLoading();
                });
        };

        // ✅ Guest: block select opening
        if (!IS_AUTH) {
            indexSelect?.addEventListener("pointerdown", (e) => blockSelectOpen(e, indexSelect, () =>
                lastAllowedIndex));
            indexSelect?.addEventListener("mousedown", (e) => blockSelectOpen(e, indexSelect, () =>
                lastAllowedIndex));
            indexSelect?.addEventListener("focus", (e) => blockSelectOpen(e, indexSelect, () =>
                lastAllowedIndex));

            yearSelect?.addEventListener("pointerdown", (e) => blockSelectOpen(e, yearSelect, () =>
                lastAllowedYear));
            yearSelect?.addEventListener("mousedown", (e) => blockSelectOpen(e, yearSelect, () =>
                lastAllowedYear));
            yearSelect?.addEventListener("focus", (e) => blockSelectOpen(e, yearSelect, () => lastAllowedYear));
        }

        // ✅ Change handlers - WRAPPED for authenticated users only
        const guardedChange = requireAuth(function() {
            lastAllowedIndex = indexSelect?.value ?? lastAllowedIndex;
            lastAllowedYear = yearSelect?.value ?? lastAllowedYear;
            updateChartData();
        });

        indexSelect?.addEventListener("change", guardedChange);
        yearSelect?.addEventListener("change", guardedChange);

        // ✅ ALWAYS LOAD INITIAL DATA (removed auth check)
        updateChartData();
    });
</script>

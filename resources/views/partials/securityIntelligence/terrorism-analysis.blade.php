<div class="max-w-7xl mx-auto">

    <form id="filter-form">
        <section class="max-w-7xl mx-auto bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl py-8 px-6">

            <div class="space-y-2 text-center">
                <h1 id="main-title" class="text-3xl font-bold text-white mt-2">Nigeria Composite Risk Index</h1>
                <p class="text-sm font-medium text-gray-300"> A Data-Driven Approach to Understanding Nigeria's Security
                    Threats
                </p>
            </div>

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
                            <option selected>2025</option>
                            <option>2024</option>
                            <option>2023</option>
                            <option>2022</option>
                            <option>2021</option>
                            <option>2020</option>
                            <option>2019</option>
                            <option>2018</option>
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
            <h3 class="text-sm font-medium text-white uppercase tracking-wide">Tracked Incidents</h3>
            <div class="mt-4">
                <p id="card-risk-index" class="text-4xl font-semibold text-white">...</p>
            </div>
        </div>

        {{-- 2. NEW: Fatalities Card --}}
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-white uppercase tracking-wide">Fatalities</h3>
            <div class="mt-4">
                <p id="card-fatalities" class="text-4xl font-semibold text-gray-100">...</p>
            </div>
        </div>

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-white uppercase tracking-wide">ACTIVE THREAT GROUPS</h3>
            <p id="card-top-threats" class="text-lg font-semibold text-gray-100 mt-4">
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
            <h3 class="text-xl font-semibold text-white">Geographic Analysis</h3>
            <div class="mt-4">
                <div id="treemap-chart"></div>
            </div>
        </div>

        {{-- Fatality Trend Line Chart --}}
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 id="line-chart-title" class="text-xl font-semibold text-white">Fatality Trend</h3>
            <div class="mt-4">
                <div id="fatality-line-chart"></div>
            </div>
        </div>

    </div>

    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md lg:col-span-3">
        <h3 id="table-title" class="text-xl font-semibold text-white mb-4">State Risk Ranking</h3>
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

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const insightData = {
            'Composite Risk Index': [{
                    title: "Fastest Growing Threats",
                    text: "While Borno retains the highest absolute incident count, Katsina State is currently exhibiting 'Escalating' traits with a 120% year-over-year growth rate, identifying it as the fastest-growing volatility hotspot."
                },
                {
                    title: "Zonal Risk",
                    text: "The 'North West' zone has an aggregated composite risk score of 45.8, making it approximately 3x more dangerous than the 'South West' (15.2), despite lower national media coverage on specific rural banditry incidents."
                },
                {
                    title: "New Conflict Zones",
                    text: "Although 'Communal Clashes' rank 7th in total volume, they have shown a 300% surge in the 'North Central' zone, marking a critical shift from criminal-economic violence to resource-based conflict."
                }
            ],
            'Terrorism Index': [{
                    title: "Fewer Attacks, More Death",
                    text: "Terrorism incidents have numerically stabilized (-5% volume), but the Lethality Rate has risen sharply. The average casualties per incident is now 8.2, compared to 6.5 in the previous period, indicating more sophisticated IED usage."
                },
                {
                    title: "The Violence is Moving",
                    text: "While risk scores in Zamfara are 'Improving' (Rank 5 → 8), neighboring Niger State is 'Escalating'. This suggests that kinetic military operations in Zamfara are successfully displacing threat actors southwards rather than neutralizing them."
                },
                {
                    title: "Dry Season Dangers",
                    text: "Attacks show a strong correlation (>0.85) with the Dry Season (Jan-Mar), driven by increased mobility for heavy trucks and motorcycles in the Sambisa and Lake Chad marshlands."
                }
            ],
            'Kidnapping Index': [{
                    title: "End-of-Year Spikes",
                    text: "Kidnapping incidents show a distinct 40% spike nationally in Q4 (Oct-Dec). Historical data links this to increased travel density and liquidity demands of criminal groups during the holiday season."
                },
                {
                    title: "High-Risk Highways",
                    text: "In Kaduna, the top 3 affected LGAs (Chikun, Kajuru, Birnin Gwari) are contiguous. This clustering indicates a established threat corridor along the main highway arteries, rather than opportunistic, isolated attacks."
                },
                {
                    title: "It's About Money",
                    text: "In states where Kidnapping is the #1 risk, the primary actor is 'Bandits' (75%) and the documented motive is 'Ransom' (88%), confirming the threat is driven by economic opportunism rather than ideology."
                }
            ]
        };

        function updateInsights(selectedType) {
            const listContainer = document.getElementById('insight-list');
            const badge = document.getElementById('insight-badge');

            // Set Badge
            badge.textContent = `${selectedType} Analysis`;

            // Get Data (Fallback to Composite if undefined)
            const insights = insightData[selectedType] || insightData['Composite Risk Index'];

            // Build HTML
            let htmlContent = '';
            insights.forEach(item => {
                htmlContent += `
                    <li class="bg-[#2b3a4a] p-4 rounded border-l-4 border-gray-600 hover:border-indigo-500 transition-colors duration-300">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">${item.title}</span>
                            <p class="text-gray-300 text-sm leading-relaxed">${item.text}</p>
                        </div>
                    </li>
                `;
            });

            // Fade Effect
            listContainer.style.opacity = '0';
            setTimeout(() => {
                listContainer.innerHTML = htmlContent;
                listContainer.style.opacity = '1';
            }, 200);
        }

        function getRiskCategory(value) {
            if (value <= 1.7) return 'Low';
            if (value <= 2.8) return 'Medium';
            if (value <= 7.0) return 'High';
            return 'Critical';
        }

        var options = {
            series: [],
            chart: {
                height: 400,
                type: 'treemap',
                toolbar: {
                    show: false
                }
            },
            title: {
                text: 'Geographic Risk Analysis by State',
                align: 'center',
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold',
                    color: '#FFFFFF'
                }
            },
            plotOptions: {
                treemap: {
                    enableShades: false,
                    colorScale: {
                        ranges: [{
                                from: 0,
                                to: 1.7,
                                color: '#10b981'
                            },
                            {
                                from: 1.71,
                                to: 2.8,
                                color: '#FFB020'
                            },
                            {
                                from: 2.81,
                                to: 7.0,
                                color: '#fc4444'
                            },
                            {
                                from: 7.01,
                                to: 100,
                                color: '#c40000'
                            }
                        ]
                    },
                    dataLabels: {
                        style: {
                            colors: ['#000']
                        }
                    }
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function(value) {
                        var category = getRiskCategory(value);
                        var formattedValue = parseFloat(value).toFixed(2);
                        return formattedValue + "% Risk (" + category + ")";
                    }
                }
            },
            noData: {
                text: 'Loading Risk Data...'
            }
        };

        var chart = new ApexCharts(document.querySelector("#treemap-chart"), options);
        chart.render();

        var lineOptions = {
            series: [{
                name: 'Fatalities',
                data: []
            }],
            chart: {
                height: 350,
                type: 'line',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#ef4444'], // Red color for fatalities
            xaxis: {
                categories: [],
                labels: {
                    style: {
                        colors: '#94a3b8'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#94a3b8'
                    }
                }
            },
            grid: {
                borderColor: '#334155'
            },
            tooltip: {
                theme: 'dark'
            }
        };

        var fatalityChart = new ApexCharts(document.querySelector("#fatality-line-chart"), lineOptions);
        fatalityChart.render();

        function updateRiskTable(tableData) {
            const tableBody = document.getElementById('risk-table-body');
            if (!tableData || tableData.length === 0) {
                tableBody.innerHTML =
                    `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">No data available for this filter.</td></tr>`;
                return;
            }

            let tableHtml = '';
            tableData.forEach(state => {
                let statusColorClass = 'text-gray-400';
                if (state.status === 'Escalating') statusColorClass = 'text-red-500';
                else if (state.status === 'Improving') statusColorClass = 'text-green-500';

                tableHtml += `
                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                        <td class="py-3 px-4 font-medium">${state.state}</td>
                        <td class="py-3 px-4">${state.risk_score}%</td>
                        <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${getRiskLevelClass(state.risk_level)}">${state.risk_level}</span></td>
                        <td class="py-3 px-4">${state.rank_current}</td>
                        <td class="py-3 px-4">${state.rank_previous}</td>
                        <td class="py-3 px-4 font-semibold ${statusColorClass}">${state.status}</td>
                        <td class="py-3 px-4">${state.incidents}</td>
                    </tr>`;
            });
            tableBody.innerHTML = tableHtml;
        }

        function updateChartData() {
            const indexSelect = document.getElementById('index_type');
            const yearSelect = document.getElementById('year');
            const titleElement = document.getElementById('main-title');
            const tableTitleElement = document.getElementById('table-title');
            const selectedIndexText = indexSelect.options[indexSelect.selectedIndex].text;
            const selectedYear = yearSelect.value;

            titleElement.textContent = `${selectedIndexText} - ${selectedYear}`;
            tableTitleElement.textContent = `${selectedIndexText}: State Risk Ranking`;

            const indexType = indexSelect.value;
            const year = selectedYear;

            updateInsights(indexType);

            // Removed: document.getElementById('prev-year-label').textContent = ...

            chart.updateOptions({
                noData: {
                    text: 'Loading filtered data...'
                }
            });
            document.getElementById('risk-table-body').innerHTML =
                `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">Loading risk table...</td></tr>`;

            document.getElementById('card-risk-index').textContent = '...';
            document.getElementById('card-top-threats').textContent = 'Loading...';

            fetch(`/risk-treemap-data?year=${year}&index_type=${indexType}`)
                .then(response => response.json())
                .then(data => {
                    chart.updateSeries(data.treemapSeries);
                    updateRiskTable(data.tableData);

                    document.getElementById('card-risk-index').textContent = data.cardData
                        .totalTrackedIncidents;
                    document.getElementById('card-top-threats').textContent = data.cardData.topThreatGroups;
                    document.getElementById('card-fatalities').textContent = new Intl.NumberFormat().format(
                        data.cardData.totalFatalities);

                    if (data.trendSeries && data.trendSeries.labels) {
                        fatalityChart.updateOptions({
                            xaxis: {
                                categories: data.trendSeries.labels
                            }
                        });

                        fatalityChart.updateSeries([{
                            name: "Fatalities",
                            data: data.trendSeries.data
                        }]);

                        document.getElementById('line-chart-title').textContent =
                            `${selectedIndexText} Fatality Trend`;
                    } else {
                        console.error(
                            "Trend data is missing from the server response. Check Controller scope and clear cache."
                        );
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    chart.updateOptions({
                        noData: {
                            text: 'Failed to load data.'
                        }
                    });
                    document.getElementById('risk-table-body').innerHTML =
                        `<tr><td colspan="7" class="py-10 px-4 text-center text-red-500">Failed to load table data.</td></tr>`;
                    document.getElementById('card-risk-index').textContent = 'N/A';
                    document.getElementById('card-top-threats').textContent = 'Error';
                    document.getElementById('card-fatalities').textContent = 'N/A';
                });
        }

        const indexSelect = document.getElementById('index_type');
        const yearSelect = document.getElementById('year');
        indexSelect.addEventListener('change', updateChartData);
        yearSelect.addEventListener('change', updateChartData);
        updateChartData();
    });

    function getRiskLevelClass(riskLevel) {
        switch (riskLevel) {
            case 'Low':
                return 'bg-green-600 text-white';
            case 'Medium':
                return 'bg-yellow-500 text-black';
            case 'High':
                return 'bg-[#fc4444] text-white';
            case 'Critical':
                return 'bg-red-700 text-white';
            default:
                return 'bg-gray-500 text-white';
        }
    }
</script>

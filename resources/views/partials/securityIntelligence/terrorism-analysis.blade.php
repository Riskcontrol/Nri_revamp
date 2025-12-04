{{-- <div class="bg-slate-50 min-h-screen p-8"> --}}
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
                    {{-- <label for="index_type" class="block text-sm font-medium text-gray-300">Index Type</label> --}}
                    <div class="relative mt-1">
                        <select id="index_type" name="index_type"
                            class="block w-full sm:w-60 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option selected>Terrorism Index</option>
                            <option>Kidnapping Index</option>
                            <option>Composite Risk Index</option>
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
                    {{-- <label for="year" class="block text-sm font-medium text-gray-300">Year</label> --}}
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
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <span class="bg-red-600 text-black text-xs font-bold uppercase px-3 py-1 rounded-full">Threat Level</span>
            <p id="card-threat-level" class="text-3xl font-semibold text-white mt-3">...</p>
        </div>

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-white uppercase tracking-wide">Total Tracked Incidents</h3>

            <div class="mt-4">
                <div class="flex items-baseline space-x-4">
                    <p id="card-risk-index" class="text-4xl font-semibold text-white">...</p>
                    <span id="card-incident-trend" class="text-sm font-semibold text-gray-400">
                        ...
                    </span>
                </div>

                <div class="mt-2 flex items-center text-xs text-gray-400">
                    <span id="prev-year-label" class="font-medium text-gray-500">2024</span>

                    <svg class="w-3 h-3 mx-1.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>

                    <span id="prev-year-count" class="font-mono text-gray-300">...</span>
                </div>
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
            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                Top Insights
            </h3>
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

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md lg:col-span-1">
            <h3 class="text-xl font-semibold text-white">Recent Activities</h3>
            <ul class="mt-4 list-disc list-inside space-y-2 text-gray-200">
                <li>60+ killed in attack on Darajamal, Borno</li>
                <li>100+ civilians abducted in Kairu, Zamfara</li>
                <li>30+ killed in Katsina mosque attack</li>
                <li>20 women abducted by JAS in Borno</li>
                <li>17 killed in Plateau-Kaduna border attacks</li>
            </ul>
        </div> --}}

        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md lg:col-span-2">
            <h3 class="text-xl font-semibold text-white">Geographic Analysis</h3>
            <div class="mt-4 bg-[#1E2D3D]">
                <div id="treemap-chart"></div>
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
                    text: "While Borno retains the highest absolute incident count, <span class='text-white font-medium'>Katsina State</span> is currently exhibiting 'Escalating' traits with a <span class='text-red-400 font-bold'>120% year-over-year growth rate</span>, identifying it as the fastest-growing volatility hotspot."
                },
                {
                    title: "Zonal Risk",
                    text: "The <span class='text-indigo-300 font-medium'>'North West'</span> zone has an aggregated composite risk score of 45.8, making it approximately <span class='text-white font-bold'>3x more dangerous</span> than the 'South West' (15.2), despite lower national media coverage on specific rural banditry incidents."
                },
                {
                    title: "New Conflict Zones",
                    text: "Although <span class='text-white font-medium'>'Communal Clashes'</span> rank 7th in total volume, they have shown a <span class='text-yellow-400 font-bold'>300% surge</span> in the 'North Central' zone, marking a critical shift from criminal-economic violence to resource-based conflict."
                }
            ],
            'Terrorism Index': [{
                    title: "Fewer Attacks, More Death",
                    text: "Terrorism incidents have numerically stabilized (-5% volume), but the <span class='text-red-400 font-bold'>Lethality Rate</span> has risen sharply. The average casualties per incident is now <span class='text-white font-bold'>8.2</span>, compared to 6.5 in the previous period, indicating more sophisticated IED usage."
                },
                {
                    title: "The Violence is Moving",
                    text: "While risk scores in Zamfara are 'Improving' (Rank 5 → 8), neighboring <span class='text-white font-medium'>Niger State</span> is 'Escalating'. This suggests that kinetic military operations in Zamfara are successfully <span class='text-indigo-300 font-medium'>displacing threat actors</span> southwards rather than neutralizing them."
                },
                {
                    title: "Dry Season Dangers",
                    text: "Attacks show a strong correlation (>0.85) with the <span class='text-yellow-400 font-bold'>Dry Season (Jan-Mar)</span>, driven by increased mobility for heavy trucks and motorcycles in the Sambisa and Lake Chad marshlands."
                }
            ],
            'Kidnapping Index': [{
                    title: "End-of-Year Spikes",
                    text: "Kidnapping incidents show a distinct <span class='text-red-400 font-bold'>40% spike</span> nationally in <span class='text-white font-bold'>Q4 (Oct-Dec)</span>. Historical data links this to increased travel density and liquidity demands of criminal groups during the holiday season."
                },
                {
                    title: "High-Risk Highways",
                    text: "In Kaduna, the top 3 affected LGAs (Chikun, Kajuru, Birnin Gwari) are contiguous. This clustering indicates a established <span class='text-indigo-300 font-medium'>threat corridor</span> along the main highway arteries, rather than opportunistic, isolated attacks."
                },
                {
                    title: "It's About Money",
                    text: "In states where Kidnapping is the #1 risk, the primary actor is <span class='text-white font-medium'>'Bandits' (75%)</span> and the documented motive is <span class='text-yellow-400 font-bold'>'Ransom' (88%)</span>, confirming the threat is driven by economic opportunism rather than ideology."
                }
            ]
        };

        // --- 2. DEFINE UPDATE FUNCTION ---
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

        // ... (getRiskCategory function) ...
        function getRiskCategory(value) {
            if (value <= 1.7) return 'Low';
            if (value <= 2.8) return 'Medium';
            if (value <= 7.0) return 'High';
            return 'Critical';
        }

        // ... (options variable) ...
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
                            colors: ['#000'] // Use white color for labels
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

        function updateRiskTable(tableData) {
            const tableBody = document.getElementById('risk-table-body');

            if (!tableData || tableData.length === 0) {
                tableBody.innerHTML =
                    `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">No data available for this filter.</td></tr>`;
                return;
            }

            let tableHtml = '';
            tableData.forEach(state => {
                let statusColorClass = 'text-gray-400'; // Stable
                if (state.status === 'Escalating') {
                    statusColorClass = 'text-red-500'; // Worse rank
                } else if (state.status === 'Improving') {
                    statusColorClass = 'text-green-500'; // Better rank
                }

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

        // --- THIS IS THE UPDATED PART ---

        function updateChartData() {
            // ... (Your existing code to get dropdowns and update title) ...
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

            document.getElementById('prev-year-label').textContent = parseInt(selectedYear) - 1;

            // Set loading states for BOTH chart and table
            chart.updateOptions({
                noData: {
                    text: 'Loading filtered data...'
                }
            });
            document.getElementById('risk-table-body').innerHTML =
                `<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">Loading risk table...</td></tr>`;

            document.getElementById('card-threat-level').textContent = '...';
            document.getElementById('card-risk-index').textContent = '...';
            document.getElementById('card-top-threats').textContent = 'Loading...';
            // Use the correct URL from your routes file
            fetch(`/risk-treemap-data?year=${year}&index_type=${indexType}`)
                .then(response => response.json())
                .then(data => {

                    chart.updateSeries(data.treemapSeries);
                    updateRiskTable(data.tableData);

                    document.getElementById('card-threat-level').textContent = data.cardData
                        .nationalThreatLevel;
                    document.getElementById('card-risk-index').textContent = data.cardData
                        .totalTrackedIncidents;
                    document.getElementById('card-top-threats').textContent = data.cardData.topThreatGroups;

                    const trendSpan = document.getElementById('card-incident-trend');
                    const status = data.cardData.incidentTrendStatus;
                    const diff = data.cardData.incidentTrendDifference; // We still need this for the check

                    let arrowIcon = '';
                    let colorClass = 'text-gray-400'; // Default for Stable

                    if (status === 'Escalating') {
                        // Red Up Arrow: More incidents (Bad)
                        colorClass = 'text-red-500';
                        arrowIcon =
                            `<svg class="w-4 h-4 mr-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>`;
                    } else if (status === 'Improving') {
                        // Green Down Arrow: Fewer incidents (Good)
                        colorClass = 'text-green-500';
                        arrowIcon =
                            `<svg class="w-4 h-4 mr-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
                    }

                    // Build the HTML
                    trendSpan.className =
                        `text-sm font-semibold flex items-center ${colorClass}`; // Set the color

                    if (status === 'Stable') {
                        trendSpan.innerHTML = `(No change)`;
                    } else {

                        trendSpan.innerHTML = `${arrowIcon} ${status}`;
                    }

                    const currentCount = data.cardData.totalTrackedIncidents;
                    document.getElementById('card-risk-index').textContent = currentCount;

                    // Remove commas to do math (e.g. "1,200" -> 1200)
                    const currentVal = parseInt(String(currentCount).replace(/,/g, '')) || 0;
                    // Get the difference (e.g. +50 or -20)
                    const diffVal = parseInt(data.cardData.incidentTrendDifference) || 0;

                    // Logic: Previous = Current - Difference
                    const prevVal = currentVal - diffVal;

                    document.getElementById('prev-year-count').textContent = prevVal.toLocaleString();
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    chart.updateOptions({
                        noData: {
                            text: 'Failed to load data.'
                        }
                    });
                    // Also update the table on error
                    document.getElementById('risk-table-body').innerHTML =
                        `<tr><td colspan="7" class="py-10 px-4 text-center text-red-500">Failed to load table data.</td></tr>`;
                    document.getElementById('card-threat-level').textContent = 'N/A';
                    document.getElementById('card-risk-index').textContent = 'N/A';
                    document.getElementById('card-top-threats').textContent = 'Error';
                });
        }

        // ... (Your event listeners and initial updateChartData() call remain the same) ...
        const indexSelect = document.getElementById('index_type');
        const yearSelect = document.getElementById('year');
        indexSelect.addEventListener('change', updateChartData);
        yearSelect.addEventListener('change', updateChartData);
        updateChartData(); // Load initial data
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

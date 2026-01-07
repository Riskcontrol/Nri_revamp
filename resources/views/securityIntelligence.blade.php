<x-layout title="Security Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria's regions. Leverage our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">

    {{-- Add ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="container mx-auto max-w-7xl px-4 py-12" x-data="{ activeTab: 'overview' }">

        <div class="text-center mb-10">
            <h2 class="text-2xl font-semibold text-white">Risk Intelligence Database</h2>
            {{-- Display Date Range: 2018 - Present Year --}}
            <p class="text-xl text-gray-400 mt-2">{{ $startYear }} – {{ $currentYear }}</p>
        </div>


        <div class="border-b border-gray-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">

                {{-- LEFT: TABS (Auth Removed) --}}
                <nav class="flex space-x-8 -mb-px overflow-x-auto" aria-label="Tabs">
                    <a href="#" @click.prevent="activeTab = 'overview'"
                        :class="{
                            'border-emerald-500 border-b-2 text-base font-semibold text-emerald-400': activeTab === 'overview',
                            'border-transparent border-b-2 text-base font-medium text-gray-400 hover:border-gray-500 hover:text-gray-200': activeTab !== 'overview'
                        }"
                        class="px-1 pb-4 whitespace-nowrap transition-colors duration-200">
                        Overview
                    </a>

                    {{-- Analysis Tab - Direct Access --}}
                    <a href="#" @click.prevent="activeTab = 'analysis'"
                        :class="{
                            'border-emerald-500 border-b-2 text-base font-semibold text-emerald-400': activeTab === 'analysis',
                            'border-transparent border-b-2 text-base font-medium text-gray-400 hover:border-gray-500 hover:text-gray-200': activeTab !== 'analysis'
                        }"
                        class="px-1 pb-4 whitespace-nowrap transition-colors duration-200 flex items-center gap-2">
                        Risk Index Analysis
                    </a>
                </nav>

                {{-- RIGHT: ACTION BUTTONS (Auth Removed) --}}
                <div class="flex items-center space-x-4 mt-4 md:mt-0 mb-3 md:mb-2">
                    <a href="{{ route('risk-map.show') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-700 border border-gray-600 rounded-md hover:bg-gray-600 transition-all shadow-sm">
                        <i class="fa-solid fa-map-location-dot mr-2 text-gray-300"></i>
                        Risk Map
                    </a>
                </div>
            </div>
        </div>

        {{-- TAB CONTENT: OVERVIEW --}}
        <div x-show="activeTab === 'overview'" class="pt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                {{-- Card 1: Total Incidents --}}
                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Tracked Security Incidents
                    </h3>
                    <p class="text-base md:text-base font-normal text-white tracking-tight">
                        {{ number_format($totalIncidents) }}
                    </p>
                </div>

                {{-- Card 2: Fatalities --}}
                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Fatalities
                    </h3>
                    <p class="text-base md:text-base font-normal text-white tracking-tight">
                        {{ number_format($totalDeaths) }}
                    </p>
                </div>

                {{-- Card 3: Recurring Risk --}}
                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Recurring Risk
                    </h3>
                    <p class="text-base md:text-base font-normal text-white leading-tight">
                        {{ $prominentRisks }}
                    </p>
                </div>

                {{-- Card 4: Hot Zones --}}
                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Hot Zones
                    </h3>
                    <div class="text-base md:text-base font-normal text-white leading-tight">
                        @forelse ($activeRegions as $region)
                            <span>{{ $region['zone'] }}</span>
                            @if (!$loop->last)
                                ,
                            @endif
                        @empty
                            <span class="text-gray-500 font-normal italic text-sm">No regional data available</span>
                        @endforelse
                    </div>
                </div>

            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="space-y-6">
                    {{-- Chart 1: Trend Line (APEX CHART) --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">Fatalities Trend
                            ({{ $startYear }}–{{ $currentYear }})</h3>
                        <div id="incidentTrendChart" style="height: 300px;"></div>
                    </div>

                    {{-- Chart 2: Regional Pie (Chart.js) --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">Fatalities by Region</h3>
                        <div class="relative" style="height: 350px;">
                            <canvas id="regionPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Chart 3: Risk Indicators Bar (Chart.js) --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">Reoccuring Risk</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="indicatorBarChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md mt-6">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">State Contribution to Recurring Risks</h3>
                        <div class="relative" style="height: 350px;">
                            <canvas id="contributionChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <script>
                // --- APEX CHART FOR TREND ---
                document.addEventListener('DOMContentLoaded', () => {
                    const trendOptions = {
                        series: [{
                            name: 'Fatalities',
                            data: @json($trendData)
                        }],
                        chart: {
                            type: 'area',
                            height: 300,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#10B981'],
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
                            categories: @json($trendLabels),
                            labels: {
                                style: {
                                    colors: '#9CA3AF'
                                }
                            },
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#9CA3AF'
                                }
                            }
                        },
                        grid: {
                            borderColor: '#374151',
                            strokeDashArray: 4,
                        },
                        theme: {
                            mode: 'dark'
                        }
                    };

                    const trendChart = new ApexCharts(document.querySelector("#incidentTrendChart"), trendOptions);
                    trendChart.render();
                });

                // --- CHART.JS FOR OTHERS ---
                let regionPieChartInstance = null;
                let indicatorBarChartInstance = null;
                const chartColors = ['#DC2626', '#F59E0B', '#10B981', '#3B82F6', '#ff9f63', '#8B5CF6', '#0047d6'];

                document.addEventListener('DOMContentLoaded', () => {
                    const ctxRegionPie = document.getElementById('regionPieChart').getContext('2d');
                    const ctxIndicatorBar = document.getElementById('indicatorBarChart').getContext('2d');
                    const ctxContribution = document.getElementById('contributionChart').getContext('2d');

                    if (Chart.getChart(ctxRegionPie)) Chart.getChart(ctxRegionPie).destroy();
                    if (Chart.getChart(ctxIndicatorBar)) Chart.getChart(ctxIndicatorBar).destroy();

                    // 2. Pie Chart
                    regionPieChartInstance = new Chart(ctxRegionPie, {
                        type: 'pie',
                        data: {
                            labels: @json($regionChartLabels),
                            datasets: [{
                                label: 'Fatalities by Region',
                                data: @json($regionChartData),
                                backgroundColor: chartColors,
                                hoverOffset: 4,
                                borderColor: '#1E2D3D',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15,
                                        color: 'white'
                                    }
                                }
                            }
                        }
                    });

                    // 3. Bar Chart (Risk Indicators)
                    indicatorBarChartInstance = new Chart(ctxIndicatorBar, {
                        type: 'bar',
                        data: {
                            labels: @json($riskIndicatorLabels),
                            datasets: [{
                                label: 'Frequency',
                                data: @json($riskIndicatorData),
                                backgroundColor: '#10b981',
                                borderColor: '#10b981',
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#9CA3AF'
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.05)'
                                    }
                                },
                                y: {
                                    ticks: {
                                        color: 'white'
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });

                    // 4. Stacked Contribution Chart
                    new Chart(ctxContribution, {
                        type: 'bar',
                        data: {
                            labels: @json($riskLabels),
                            datasets: @json($contributionDatasets)
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: {
                                        color: '#9CA3AF'
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#9CA3AF'
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.05)'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: 'white',
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return (context.dataset.label || '') + ': ' + context.raw;
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>

        {{-- TAB CONTENT: ANALYSIS --}}
        <div x-show="activeTab === 'analysis'" class="pt-10 text-white">
            @include('partials.securityIntelligence.terrorism-analysis')
        </div>

    </div>
</x-layout>

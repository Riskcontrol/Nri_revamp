<x-layout title="Security Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">

    <div class="container mx-auto max-w-7xl px-4 py-12" x-data="{ activeTab: 'overview' }">

        <div class="text-center mb-10">
            <h2 class="text-2xl font-semibold text-white">Comprehensive Interactive Database</h2>
            {{-- Display Date Range: 2018 - Present Year --}}
            <p class="text-xl text-gray-400 mt-2">{{ $startYear }} – {{ $currentYear }}</p>
        </div>


        <div class="border-b border-gray-700">
            {{-- Flex container to split Tabs (Left) and Buttons (Right) --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">

                {{-- LEFT: TABS --}}
                <nav class="flex space-x-8 -mb-px overflow-x-auto" aria-label="Tabs">
                    <a href="#" @click.prevent="activeTab = 'overview'"
                        :class="{
                            'border-emerald-500 border-b-2 text-base font-semibold text-emerald-400': activeTab === 'overview',
                            'border-transparent border-b-2 text-base font-medium text-gray-400 hover:border-gray-500 hover:text-gray-200': activeTab !== 'overview'
                        }"
                        class="px-1 pb-4 whitespace-nowrap transition-colors duration-200">
                        Overview
                    </a>
                    <a href="#" @click.prevent="activeTab = 'analysis'"
                        :class="{
                            'border-emerald-500 border-b-2 text-base font-semibold text-emerald-400': activeTab === 'analysis',
                            'border-transparent border-b-2 text-base font-medium text-gray-400 hover:border-gray-500 hover:text-gray-200': activeTab !== 'analysis'
                        }"
                        class="px-1 pb-4 whitespace-nowrap transition-colors duration-200">
                        Risk Index Analysis
                    </a>
                </nav>

                {{-- RIGHT: ACTION BUTTONS --}}
                <div class="flex items-center space-x-4 mt-4 md:mt-0 mb-3 md:mb-2">

                    {{-- Button 1: Location Intelligence / Risk Map --}}
                    <a href="{{ route('risk-map.show') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-700 border border-gray-600 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-gray-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                            </path>
                        </svg>
                        Risk Map
                    </a>

                    {{-- Button 2: Comprehensive Database --}}
                    <a href="{{ route('analytics.view') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-emerald-500 transition-all shadow-md shadow-emerald-900/20">
                        <svg class="w-4 h-4 mr-2 text-emerald-100" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                            </path>
                        </svg>
                        Database Visualization
                    </a>

                </div>

            </div>
        </div>

        {{-- TAB CONTENT: OVERVIEW --}}
        <div x-show="activeTab === 'overview'" class="pt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                {{-- Card 1: Total Incidents (Cumulative) --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-sm font-medium text-gray-300">Tracked Security Incidents</h3>
                    <p class="text-5xl font-bold text-white my-3">{{ number_format($totalIncidents) }}</p>
                </div>

                {{-- Card 2: High Risk States (Cumulative) --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-sm font-medium text-gray-300">Total Fatalities</h3>
                    <p class="text-5xl font-bold text-white my-3">{{ number_format($totalDeaths) }}</p>
                    <span class="text-red-400 flex items-center text-sm">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Confirmed Deaths</span>
                    </span>
                </div>

                {{-- Card 3: Prominent Risks (Cumulative) --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-base font-semibold text-white mb-4">Reoccuring Risk</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-200 leading-relaxed">{{ $prominentRisks }}</p>
                    </div>
                </div>

                {{-- Card 4: Risk Regions (Cumulative) --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-base font-semibold text-white mb-4">Hot Zones</h3>

                    {{-- Stacked Layout (Flex Column) --}}
                    <div class="flex flex-col space-y-4">
                        @forelse ($activeRegions as $region)
                            <div class=" pl-3">
                                <span class="font-medium text-white text-sm block mb-1">{{ $region['zone'] }}</span>
                                <span
                                    class="text-gray-400 text-xs block uppercase tracking-wide">{{ $region['top_risk'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No regional data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="space-y-6">
                    {{-- Chart 1: Trend Line --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Security Incidents Trend
                            ({{ $startYear }}–{{ $currentYear }})</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="incidentChart"></canvas>
                        </div>
                    </div>

                    {{-- Chart 2: Regional Pie --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Incidents by Region (Cumulative)</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="regionPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">

                    {{-- List: Top 5 States --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-1">Active States</h3>
                        {{-- <p class="text-xs text-gray-400 mb-4"> (% Change {{ $startYear }} vs
                            {{ $currentYear }})</p> --}}

                        <div class="relative" style="height: 300px;">
                            <canvas id="stateChangeChart"></canvas>
                        </div>
                    </div>

                    {{-- Chart 3: Risk Indicators Bar --}}
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Risk Indicators</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="indicatorBarChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <script>
                let incidentChartInstance = null;
                let regionPieChartInstance = null;
                let indicatorBarChartInstance = null;
                let stateChangeChartInstance = null; // New Instance

                const chartColors = [
                    '#DC2626', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#6B7280'
                ];

                document.addEventListener('DOMContentLoaded', () => {
                    const ctxIncident = document.getElementById('incidentChart').getContext('2d');
                    const ctxRegionPie = document.getElementById('regionPieChart').getContext('2d');
                    const ctxIndicatorBar = document.getElementById('indicatorBarChart').getContext('2d');
                    const ctxStateChange = document.getElementById('stateChangeChart').getContext('2d'); // New Context

                    // Destroy existing charts
                    if (Chart.getChart(ctxIncident)) Chart.getChart(ctxIncident).destroy();
                    if (Chart.getChart(ctxRegionPie)) Chart.getChart(ctxRegionPie).destroy();
                    if (Chart.getChart(ctxIndicatorBar)) Chart.getChart(ctxIndicatorBar).destroy();
                    if (Chart.getChart(ctxStateChange)) Chart.getChart(ctxStateChange).destroy();

                    // 1. Line Chart
                    incidentChartInstance = new Chart(ctxIncident, {
                        type: 'line',
                        data: {
                            labels: @json($trendLabels),
                            datasets: [{
                                label: 'Incidents',
                                data: @json($trendData),
                                borderColor: '#10b981',
                                backgroundColor: '#10b981',
                                fill: true,
                                tension: 0.1
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

                    // 2. Pie Chart
                    regionPieChartInstance = new Chart(ctxRegionPie, {
                        type: 'pie',
                        data: {
                            labels: @json($regionChartLabels),
                            datasets: [{
                                label: 'Incidents by Region',
                                data: @json($regionChartData),
                                backgroundColor: chartColors,
                                hoverOffset: 4
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

                    // 3. NEW Diverging Bar Chart (State Change)
                    stateChangeChartInstance = new Chart(ctxStateChange, {
                        type: 'bar',
                        data: {
                            labels: @json($stateChangeLabels),
                            datasets: [{
                                label: '% Change',
                                data: @json($stateChangeData),
                                // Dynamic coloring script
                                backgroundColor: (context) => {
                                    const value = context.raw;
                                    // Red (#EF4444) for Escalation (+), Green (#10B981) for Improvement (-)
                                    return value >= 0 ? '#EF4444' : '#10B981';
                                },
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            indexAxis: 'y', // Horizontal Chart
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: {
                                        color: 'white',
                                        callback: function(value) {
                                            return value + '%'
                                        } // Add % symbol
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)',
                                        zeroLineColor: 'white',
                                        zeroLineWidth: 2
                                    }
                                },
                                y: {
                                    ticks: {
                                        color: 'white',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.raw + '% Change';
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // 4. Bar Chart (Risk Indicators)
                    indicatorBarChartInstance = new Chart(ctxIndicatorBar, {
                        type: 'bar',
                        data: {
                            labels: @json($riskIndicatorLabels),
                            datasets: [{
                                label: 'Frequency',
                                data: @json($riskIndicatorData),
                                backgroundColor: '#10b981',
                                borderColor: '#10b981',
                                borderWidth: 1
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
                                        color: 'white'
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    }
                                },
                                y: {
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
                                    display: false
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

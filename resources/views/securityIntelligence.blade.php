<x-layout title="Security Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk
    analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage
    our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">

    <div class="container mx-auto max-w-7xl px-4 py-12" x-data="{ activeTab: 'overview' }">

        <div class="text-center mb-10">
            <h2 class="text-2xl font-semibold text-white">Comprehensive Interactive Database</h2>
            {{-- <p class="text-xl text-white mt-2">2018–{{ date('Y') }}</p> --}}
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
        <div x-show="activeTab === 'overview'" class="pt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                {{-- Card 1: Total Security Incidents --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-sm font-medium text-gray-300">Total Security Incidents ({{ date('Y') }})</h3>
                    <p class="text-5xl font-bold text-white my-3">{{ $totalIncidents }}</p>
                </div>

                {{-- Card 2: High Risk State --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-sm font-medium text-gray-300">High Risk State({{ date('Y') }})</h3>
                    <p class="text-5xl font-bold text-white my-3">{{ $activeRiskZones }}</p>
                    <span class="text-red-400 flex items-center"> {{-- Used red-400 for better visibility on dark background --}}
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Escalating</span>
                    </span>
                </div>

                {{-- Card 3: Most Prominent Risks --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-base font-semibold text-white mb-4">Most Prominent Risks ({{ date('Y') }})</h3>
                    <div class="space-y-2">
                        <p class="text-base text-gray-200">{{ $prominentRisks }}</p>
                    </div>
                </div>

                {{-- Card 4: Risk Regions --}}
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md h-full">
                    <h3 class="text-base font-semibold text-white mb-4">Risk Regions ({{ date('Y') }})</h3>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        @forelse ($activeRegions as $region)
                            <div class="text-sm">
                                <span class="font-semibold text-white">{{ $region['zone'] }}:</span>
                                <span class="text-gray-200 block">{{ $region['top_risk'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-200 col-span-2">No regional data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="space-y-6">
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Security Incidents
                            (2018–{{ date('Y') }})</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="incidentChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Incidents by Region ({{ date('Y') }})
                        </h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="regionPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">

                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Top 5 Active States ({{ date('Y') }})
                        </h3>
                        <div class="space-y-4 relative overflow-y-auto" style="height: 300px;">
                            @php $maxIncidents = $top5States->first()->total_incidents ?? 0; @endphp
                            @forelse($top5States as $state)
                                <div>
                                    <div class="flex justify-between text-base text-white mb-1">
                                        <span class="font-medium">{{ $loop->iteration }}.
                                            {{ $state->location }}</span>
                                        <span class="text-white">{{ $state->total_incidents }} incidents</span>
                                    </div>
                                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                                        @php
                                            $width =
                                                $maxIncidents > 0 ? ($state->total_incidents / $maxIncidents) * 100 : 0;
                                        @endphp
                                        <div class="bg-[#10b981] h-1.5 rounded-full"
                                            style="width: {{ $width }}%">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-base text-white">No state data available.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-white mb-4">Risk Indicators ({{ date('Y') }})</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="indicatorBarChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <script>
                // Renamed instances for clarity
                let incidentChartInstance = null;
                let regionPieChartInstance = null;
                let indicatorBarChartInstance = null;

                const chartColors = [
                    '#DC2626', // red-600
                    '#F59E0B', // amber-500
                    '#10B981', // emerald-500
                    '#3B82F6', // blue-500
                    '#6366F1', // indigo-500
                    '#8B5CF6', // violet-500
                    '#6B7280', // gray-500 (For 'Others')
                ];

                document.addEventListener('DOMContentLoaded', () => {
                    // Updated contexts
                    const ctxIncident = document.getElementById('incidentChart').getContext('2d');
                    const ctxRegionPie = document.getElementById('regionPieChart').getContext('2d');
                    const ctxIndicatorBar = document.getElementById('indicatorBarChart').getContext('2d');

                    // Clear old charts if they exist
                    if (Chart.getChart(ctxIncident)) {
                        Chart.getChart(ctxIncident).destroy();
                    }
                    if (Chart.getChart(ctxRegionPie)) {
                        Chart.getChart(ctxRegionPie).destroy();
                    }
                    if (Chart.getChart(ctxIndicatorBar)) {
                        Chart.getChart(ctxIndicatorBar).destroy();
                    }

                    // --- Line Chart (Unchanged) ---
                    incidentChartInstance = new Chart(ctxIncident, {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                label: 'Number of Incidents',
                                data: @json($chartData),
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

                    // --- NEW: Region Pie Chart (Was Bar Chart) ---
                    regionPieChartInstance = new Chart(ctxRegionPie, {
                        type: 'pie',
                        data: {
                            // Uses data from the OLD bar chart
                            labels: @json($barChartLabels),
                            datasets: [{
                                label: 'Incidents by Region',
                                // Uses data from the OLD bar chart
                                data: @json($barChartData),
                                backgroundColor: chartColors,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right', // Move legend to the side
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15,
                                        color: 'white'
                                    }
                                }
                            }
                        }
                    });


                    indicatorBarChartInstance = new Chart(ctxIndicatorBar, {
                        type: 'bar',
                        data: {
                            labels: @json($pieChartLabels),
                            datasets: [{
                                label: 'Risk Indicators',
                                data: @json($pieChartData),
                                backgroundColor: '#10b981',
                                borderColor: '#10b981',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y', // Make it horizontal
                            scales: {
                                x: { // This is your horizontal value axis
                                    beginAtZero: true,
                                    ticks: {
                                        color: 'white' // --- ADD THIS LINE ---
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)' // --- ADD THIS LINE (faint white grid) ---
                                    }
                                },
                                y: { // This is your vertical category axis
                                    ticks: {
                                        color: 'white' // --- ADD THIS LINE ---
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)' // --- ADD THIS LINE (faint white grid) ---
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false,
                                    labels: {
                                        color: 'white'
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>

        <div x-show="activeTab === 'analysis'" class="pt-10 text-white" style="display: none;">
            @include('partials.securityIntelligence.terrorism-analysis')
        </div>


    </div>
</x-layout>

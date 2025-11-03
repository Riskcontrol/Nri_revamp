<x-layout title="Security Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk
    analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage
    our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">

    <div class="container mx-auto max-w-7xl px-4 py-12" x-data="{ activeTab: 'overview' }">

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900">Comprehensive Security Intelligence</h1>
            <p class="text-xl text-gray-500 mt-2">2018–{{ date('Y') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-lg shadow-md h-full">
                <h3 class="text-sm font-medium text-gray-500">Total Security Incidents ({{ date('Y') }})</h3>
                <p class="text-5xl font-bold text-gray-900 my-3">{{ $totalIncidents }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md h-full">
                <h3 class="text-sm font-medium text-gray-500">Active Risk Zones ({{ date('Y') }})</h3>
                <p class="text-5xl font-bold text-gray-900 my-3">{{ $activeRiskZones }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md h-full">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Most Prominent Risks ({{ date('Y') }})</h3>
                <div class="space-y-2">
                    <p class="text-base text-gray-700">{{ $prominentRisks }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md h-full">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Active Risk Regions ({{ date('Y') }})</h3>
                <div class="space-y-2">
                    @forelse ($activeRegions as $region)
                        <p class="text-base text-gray-700">
                            {{ $region['zone'] }} – {{ $region['top_risk'] }}
                        </p>
                    @empty
                        <p class="text-base text-gray-700">No regional data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 -mb-px" aria-label="Tabs">
                <a href="#" @click.prevent="activeTab = 'overview'"
                    :class="{
                        'border-black border-b-2 text-base font-semibold text-gray-900': activeTab === 'overview',
                        'border-transparent border-b-2 text-base font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'overview'
                    }"
                    class="px-1 pb-4">
                    Overview
                </a>
                <a href="#" @click.prevent="activeTab = 'analysis'"
                    :class="{
                        'border-black border-b-2 text-base font-semibold text-gray-900': activeTab === 'analysis',
                        'border-transparent border-b-2 text-base font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'analysis'
                    }"
                    class="px-1 pb-4">
                    Risk Index Analysis
                </a>
                <a href="#" @click.prevent="activeTab = 'map'"
                    :class="{
                        'border-black border-b-2 text-base font-semibold text-gray-900': activeTab === 'map',
                        'border-transparent border-b-2 text-base font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'map'
                    }"
                    class="px-1 pb-4">
                    Risk Map
                </a>
                <a href="#" @click.prevent="activeTab = 'database'"
                    :class="{
                        'border-black border-b-2 text-base font-semibold text-gray-900': activeTab === 'database',
                        'border-transparent border-b-2 text-base font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'database'
                    }"
                    class="px-1 pb-4">
                    Comprehensive Interactive Database
                </a>
            </nav>
        </div>

        <div x-show="activeTab === 'overview'" class="pt-10">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Incidents
                            (2018–{{ date('Y') }})</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="incidentChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Incidents by Region ({{ date('Y') }})
                        </h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Active States ({{ date('Y') }})
                        </h3>
                        <div class="space-y-4">
                            @php $maxIncidents = $top5States->first()->total_incidents ?? 0; @endphp
                            @forelse($top5States as $state)
                                <div>
                                    <div class="flex justify-between text-base text-gray-700 mb-1">
                                        <span class="font-medium">{{ $loop->iteration }}.
                                            {{ $state->location }}</span>
                                        <span class="text-gray-500">{{ $state->total_incidents }} incidents</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        @php
                                            $width =
                                                $maxIncidents > 0 ? ($state->total_incidents / $maxIncidents) * 100 : 0;
                                        @endphp
                                        <div class="bg-red-600 h-1.5 rounded-full" style="width: {{ $width }}%">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-base text-gray-700">No state data available.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Indicators ({{ date('Y') }})</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                let incidentChartInstance = null;
                let barChartInstance = null;
                let pieChartInstance = null;

                const pieChartColors = [
                    '#DC2626', // red-600
                    '#F59E0B', // amber-500
                    '#10B981', // emerald-500
                    '#3B82F6', // blue-500
                    '#6366F1', // indigo-500
                    '#8B5CF6', // violet-500
                    '#6B7280', // gray-500 (For 'Others')
                ];

                document.addEventListener('DOMContentLoaded', () => {
                    const ctxIncident = document.getElementById('incidentChart').getContext('2d');
                    const ctxBar = document.getElementById('barChart').getContext('2d');
                    const ctxPie = document.getElementById('pieChart').getContext('2d');

                    if (Chart.getChart(ctxIncident)) {
                        Chart.getChart(ctxIncident).destroy();
                    }
                    if (Chart.getChart(ctxBar)) {
                        Chart.getChart(ctxBar).destroy();
                    }
                    if (Chart.getChart(ctxPie)) {
                        Chart.getChart(ctxPie).destroy();
                    }

                    // --- Line Chart ---
                    incidentChartInstance = new Chart(ctxIncident, {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                label: 'Number of Incidents',
                                data: @json($chartData),
                                borderColor: 'rgb(220, 38, 38)',
                                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // --- Bar Chart ---
                    barChartInstance = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: @json($barChartLabels),
                            datasets: [{
                                label: 'Incidents by Region',
                                data: @json($barChartData),
                                backgroundColor: 'rgba(220, 38, 38, 0.7)',
                                borderColor: 'rgb(220, 38, 38)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false // Hide legend for bar chart
                                }
                            }
                        }
                    });

                    // --- Pie Chart ---
                    pieChartInstance = new Chart(ctxPie, {
                        type: 'pie',
                        data: {
                            labels: @json($pieChartLabels),
                            datasets: [{
                                label: 'Risk Indicators',
                                data: @json($pieChartData),
                                backgroundColor: pieChartColors,
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
                                        padding: 15
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>

        <div x-show="activeTab === 'analysis'" class="pt-10" style="display: none;">
            <h2 class="text-2xl font-semibold">Risk Index Analysis Content</h2>
            <p>Your content for the Risk Index Analysis tab goes here...</p>
        </div>
        <div x-show="activeTab === 'map'" class="pt-10" style="display: none;">
            <h2 class="text-2xl font-semibold">Risk Map Content</h2>
            <p>Your content for the Risk Map tab goes here...</p>
        </div>
        <div x-show="activeTab === 'database'" class="pt-10" style="display: none;">
            <h2 class="text-2xl font-semibold">Comprehensive Interactive Database Content</h2>
            <p>Your content for the Database tab goes here...</p>
        </div>

    </div>
</x-layout>

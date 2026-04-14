<x-layout title="Security Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria's regions. Leverage our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">

    {{-- Add ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="container mx-auto max-w-7xl px-4 py-12" x-data="{ activeTab: 'overview' }">

        <div class="text-center mb-10">
            <h2 class="text-2xl font-semibold text-white">Risk Intelligence Database</h2>
            <p class="text-xl text-gray-400 mt-2">{{ $startYear }} – {{ $currentYear }}</p>
        </div>

        <div class="border-b border-gray-700">
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
                        class="px-1 pb-4 whitespace-nowrap transition-colors duration-200 flex items-center gap-2">
                        Risk Index Analysis
                    </a>
                </nav>

                {{-- RIGHT: ACTION BUTTONS --}}
                <div class="flex items-center space-x-4 mt-4 md:mt-0 mb-3 md:mb-2">
                    <a href="{{ route('risk-map.show') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-700 border border-gray-600 rounded-md hover:bg-gray-600 transition-all shadow-sm">
                        <i class="fa-solid fa-map-location-dot mr-2 text-gray-300"></i>
                        Risk Map
                    </a>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             TAB CONTENT: OVERVIEW
        ═══════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'overview'" class="pt-10">

            {{-- Summary Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Tracked Security Incidents
                    </h3>
                    <p class="text-base font-normal text-white tracking-tight">
                        {{ number_format($totalIncidents) }}
                    </p>
                </div>

                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Fatalities
                    </h3>
                    <p class="text-base font-normal text-white tracking-tight">
                        {{ number_format($totalDeaths) }}
                    </p>
                </div>

                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Recurring Risk
                    </h3>
                    <p class="text-base font-normal text-white leading-tight">
                        {{ $prominentRisks }}
                    </p>
                </div>

                <div
                    class="bg-[#1E2D3D] p-6 rounded-xl shadow-lg border border-white/5 flex flex-col justify-center min-h-[160px]">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">
                        Hot Zones
                    </h3>
                    <div class="text-base font-normal text-white leading-tight">
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

            {{-- Overview Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="space-y-6">
                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">Fatalities Trend
                            ({{ $startYear }}–{{ $currentYear }})</h3>
                        <div id="incidentTrendChart" style="height: 300px;"></div>
                    </div>

                    <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-400 mb-4">Fatalities by Region</h3>
                        <div class="relative" style="height: 350px;">
                            <canvas id="regionPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
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

            {{-- ═══════════════════════════════════════════════════════════════
                 COMPOSITE RISK INDEX
                 ─ Freely accessible to ALL users (guest, Tier2, Premium).
                 ─ index_type is fixed = "Composite Risk Index" — NO dropdown.
                 ─ All historical years are selectable by everyone.
                 ─ Guests use /risk-preview-data (returns composite preview).
                 ─ Auth users use /risk-treemap-data with any selected year.
            ═══════════════════════════════════════════════════════════════ --}}
            <div class="mt-10">
                <div class="max-w-7xl mx-auto">

                    {{-- Header: title + year-only filter (no index dropdown) --}}
                    <section
                        class="max-w-7xl mx-auto bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl py-8 px-6">

                        <div class="space-y-2 text-center">
                            <h1 id="cri-main-title" class="text-2xl font-semibold text-white mt-2">Composite
                                Risk Index</h1>
                            <p class="text-sm font-medium text-gray-300">A Data-Driven Approach to Understanding
                                Nigeria's Security Threats</p>
                        </div>

                        {{-- Year selector — open to all users, no restrictions --}}
                        <div class="mt-6 flex justify-center">
                            @php
                                $cri_currentYear = (int) date('Y');
                                $cri_startYear = 2018;
                            @endphp
                            <div class="relative">
                                <select id="cri_year" name="year"
                                    class="block w-full sm:w-40 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                    @foreach (range($cri_currentYear, $cri_startYear) as $y)
                                        <option value="{{ $y }}"
                                            {{ $y == $cri_currentYear ? 'selected' : '' }}>
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
                    </section>

                    {{-- Stat Cards --}}
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Tracked Incidents</h3>
                            <div class="mt-4">
                                <p id="cri-card-incidents" class="text-md font-medium text-white">...</p>
                            </div>
                        </div>
                        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Fatalities</h3>
                            <div class="mt-4">
                                <p id="cri-card-fatalities" class="text-md font-medium text-gray-100">...</p>
                            </div>
                        </div>
                        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Active Threat Groups
                            </h3>
                            <p id="cri-card-threats" class="text-md font-medium text-gray-100 mt-4">.....</p>
                        </div>
                    </div>

                    {{-- AI Insights --}}
                    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                            <span id="cri-insight-badge"
                                class="mt-2 sm:mt-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-900 text-indigo-200 border border-indigo-700">
                                Loading Analysis...
                            </span>
                        </div>
                        <div class="relative">
                            <div id="cri-insight-loading"
                                class="hidden absolute inset-0 bg-[#1E2D3D] z-10 flex items-center justify-center">
                                <p class="text-gray-400 text-sm animate-pulse">Updating intelligence...</p>
                            </div>
                            <ul id="cri-insight-list" class="space-y-4"></ul>
                        </div>
                    </div>

                    {{-- Charts --}}
                    <div class="mt-8 grid grid-cols-1 gap-6">
                        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold text-gray-400">Geographic Analysis</h3>
                            <div class="mt-4">
                                <div id="cri-treemap-chart"></div>
                            </div>
                        </div>
                        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
                            <h3 id="cri-line-chart-title" class="text-xl font-semibold text-gray-400">Composite Risk
                                Index Fatality Trend</h3>
                            <div class="mt-4">
                                <div id="cri-fatality-line-chart"></div>
                            </div>
                        </div>
                    </div>

                    {{-- State Risk Ranking Table --}}
                    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md" x-data="{ open: false }">

                        <!-- Header with Tooltip -->
                        <div class="flex items-center gap-2 mb-1">
                            <h3 id="cri-table-title" class="text-xl font-semibold text-gray-400">
                                Early Warning Indicator System
                            </h3>

                            <!-- Info Icon -->
                            <button @click="open = !open" @click.outside="open = false"
                                class="text-gray-500 hover:text-gray-300 text-sm border border-gray-600
                   rounded-full w-5 h-5 flex items-center justify-center focus:outline-none">
                                i
                            </button>

                            <!-- Tooltip -->
                            <div x-show="open" x-transition
                                class="absolute mt-10 w-72 sm:w-80 bg-gray-900 text-gray-300 text-xs
                   rounded-lg p-4 shadow-lg z-50">
                                NRI aggregates and analyses incident data from multiple verified sources, applying
                                structured classification and trend analysis to identify emerging risks.
                                These insights are translated into risk ratings, alerts and advisories, and trend
                                indicators.
                                <br><br>
                                All outputs are simplified for public access while maintaining analytical integrity.

                                <!-- Arrow -->
                                <div class="absolute -top-1 left-6 w-3 h-3 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>

                        <p class="mb-3 font-medium text-base text-gray-300">
                            Risk ranking for subnationals
                        </p>

                        <!-- Table -->
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
                                <tbody id="cri-risk-table-body" class="text-gray-200">
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
            {{-- END: Composite Risk Index --}}
            <script src="//unpkg.com/alpinejs" defer></script>
            {{-- Static overview charts script --}}
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // 1. Fatalities Trend — ApexCharts area
                    new ApexCharts(document.querySelector('#incidentTrendChart'), {
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
                            strokeDashArray: 4
                        },
                        theme: {
                            mode: 'dark'
                        }
                    }).render();

                    // Chart.js charts
                    const chartColors = ['#DC2626', '#F59E0B', '#10B981', '#3B82F6', '#ff9f63', '#8B5CF6', '#0047d6'];
                    const ctxPie = document.getElementById('regionPieChart').getContext('2d');
                    const ctxBar = document.getElementById('indicatorBarChart').getContext('2d');
                    const ctxContr = document.getElementById('contributionChart').getContext('2d');

                    if (Chart.getChart(ctxPie)) Chart.getChart(ctxPie).destroy();
                    if (Chart.getChart(ctxBar)) Chart.getChart(ctxBar).destroy();

                    // 2. Regional pie
                    new Chart(ctxPie, {
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

                    // 3. Risk indicators bar
                    new Chart(ctxBar, {
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
                                        color: 'rgba(255,255,255,0.05)'
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

                    // 4. Stacked contribution
                    new Chart(ctxContr, {
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
                                        color: 'rgba(255,255,255,0.05)'
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
                                        label: ctx => (ctx.dataset.label || '') + ': ' + ctx.raw
                                    }
                                }
                            }
                        }
                    });
                });
            </script>

            {{-- ─── Composite Risk Index — Interactive JS ──────────────────────────────
                 Design decisions reflected here:
                   1. CRI_INDEX_TYPE is a constant — never changes, no dropdown needed.
                   2. All years accessible to all users — year select has zero restrictions.
                   3. Guests   → /risk-preview-data (existing preview, returns composite).
                   4. Auth     → /risk-treemap-data?year=Y&index_type=Composite+Risk+Index
                   5. No tier-lock modal, no auth modal, no rollback — nothing to gate.
                   6. _criLock flag prevents re-entrant fetch calls.
            ──────────────────────────────────────────────────────────────────────── --}}
            <script>
                (function() {
                    'use strict';

                    const CRI_IS_AUTH = @json(auth()->check());
                    const CRI_CURRENT_YEAR = @json((int) date('Y'));
                    const CRI_INDEX_TYPE = 'Composite Risk Index'; // fixed forever

                    // ── Helpers ──────────────────────────────────────────────────────────
                    function criRiskCategory(v) {
                        if (v <= 1.5) return 'Low';
                        if (v <= 3.5) return 'Medium';
                        if (v <= 7.0) return 'High';
                        return 'Very High';
                    }

                    function criRiskLevelClass(level) {
                        const m = {
                            Low: 'bg-green-600 text-white',
                            Medium: 'bg-yellow-500 text-white',
                            High: 'bg-[#fc4444] text-white',
                            'Very High': 'bg-red-700 text-white'
                        };
                        return m[level] || 'bg-gray-500 text-white';
                    }

                    function criShowLoading() {
                        const el = document.getElementById('cri-insight-loading');
                        const badge = document.getElementById('cri-insight-badge');
                        const list = document.getElementById('cri-insight-list');
                        if (el) el.classList.remove('hidden');
                        if (badge) badge.textContent = 'Generating insights...';
                        if (list) {
                            list.style.opacity = '0.6';
                            list.innerHTML = '<li class="text-gray-400 text-sm">Updating intelligence…</li>';
                        }
                    }

                    function criHideLoading() {
                        const el = document.getElementById('cri-insight-loading');
                        const list = document.getElementById('cri-insight-list');
                        if (el) el.classList.add('hidden');
                        if (list) list.style.opacity = '1';
                    }

                    function criSetPlaceholders() {
                        const tbody = document.getElementById('cri-risk-table-body');
                        if (tbody) tbody.innerHTML =
                            '<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">Loading risk table...</td></tr>';
                        ['cri-card-incidents', 'cri-card-fatalities', 'cri-card-threats'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.textContent = '...';
                        });
                    }

                    function criRenderInsights(insights, label) {
                        const list = document.getElementById('cri-insight-list');
                        const badge = document.getElementById('cri-insight-badge');
                        if (!list || !badge) return;
                        badge.textContent = label + ' AI Insights';
                        const html = (insights || []).slice(0, 3).map(item =>
                            `<li class="bg-[#2b3a4a] p-4 rounded border-l-4 border-indigo-500 transition-colors duration-300">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-1">${item.title ?? 'Insight'}</span>
                                    <p class="text-gray-200 text-sm leading-relaxed whitespace-pre-line">${item.text ?? ''}</p>
                                </div>
                            </li>`
                        ).join('');
                        list.style.opacity = '0';
                        setTimeout(() => {
                            list.innerHTML = html || '<li class="text-gray-400 text-sm">No AI insights available.</li>';
                            list.style.opacity = '1';
                            criHideLoading();
                        }, 150);
                    }

                    function criUpdateTable(rows) {
                        const tbody = document.getElementById('cri-risk-table-body');
                        if (!tbody) return;
                        if (!rows || !rows.length) {
                            tbody.innerHTML =
                                '<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">No data available for this filter.</td></tr>';
                            return;
                        }
                        tbody.innerHTML = rows.map(s => {
                            const sc = s.status === 'Escalating' ? 'text-red-500' : s.status === 'Improving' ?
                                'text-green-500' : 'text-gray-400';
                            return `<tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="py-3 px-4 font-medium">${s.state}</td>
                                <td class="py-3 px-4">${s.risk_score}%</td>
                                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${criRiskLevelClass(s.risk_level)}">${s.risk_level}</span></td>
                                <td class="py-3 px-4">${s.rank_current}</td>
                                <td class="py-3 px-4">${s.rank_previous}</td>
                                <td class="py-3 px-4 font-semibold ${sc}">${s.status}</td>
                                <td class="py-3 px-4">${s.incidents}</td>
                            </tr>`;
                        }).join('');
                    }

                    // ── Main ─────────────────────────────────────────────────────────────
                    document.addEventListener('DOMContentLoaded', function() {
                        const yearSelect = document.getElementById('cri_year');
                        const treemapEl = document.getElementById('cri-treemap-chart');
                        const lineEl = document.getElementById('cri-fatality-line-chart');
                        if (!treemapEl || !lineEl) return;

                        // Build treemap chart
                        const criTreemap = new ApexCharts(treemapEl, {
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
                                    formatter: v => parseFloat(v).toFixed(2) + '% Risk (' + criRiskCategory(v) +
                                        ')'
                                }
                            },
                            noData: {
                                text: 'Loading Risk Data...'
                            }
                        });
                        criTreemap.render();

                        // Build line chart
                        const criLine = new ApexCharts(lineEl, {
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
                            colors: ['#ef4444'],
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
                        });
                        criLine.render();

                        // Re-render on tab open
                        window.addEventListener('risk-analysis:open', () => {
                            setTimeout(() => {
                                try {
                                    criTreemap.resize();
                                } catch (e) {}
                                try {
                                    criLine.resize();
                                } catch (e) {}
                            }, 50);
                        });

                        // ── Fetch logic — no gates, no modals ────────────────
                        let _criLock = false;

                        function loadCRI() {
                            if (_criLock) return;
                            _criLock = true;

                            const year = yearSelect?.value ?? String(CRI_CURRENT_YEAR);

                            criShowLoading();
                            criTreemap.updateOptions({
                                noData: {
                                    text: 'Loading filtered data...'
                                }
                            });
                            criSetPlaceholders();

                            // Guests → preview endpoint (composite, no year param needed).
                            // Auth   → full endpoint with selected year.
                            const endpoint = CRI_IS_AUTH ?
                                `/risk-treemap-data?year=${encodeURIComponent(year)}&index_type=${encodeURIComponent(CRI_INDEX_TYPE)}` :
                                '/risk-preview-data';

                            fetch(endpoint, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(async r => {
                                    const data = await r.json().catch(() => ({}));
                                    if (!r.ok) throw new Error(data?.message || 'Request failed');
                                    return data;
                                })
                                .then(data => {
                                    _criLock = false;
                                    if (!data) return;

                                    criTreemap.updateSeries(data.treemapSeries || []);
                                    criUpdateTable(data.tableData || []);

                                    const inc = document.getElementById('cri-card-incidents');
                                    const fat = document.getElementById('cri-card-fatalities');
                                    const thr = document.getElementById('cri-card-threats');
                                    if (inc) inc.textContent = data?.cardData?.totalTrackedIncidents ?? 0;
                                    if (fat) fat.textContent = new Intl.NumberFormat().format(data?.cardData
                                        ?.totalFatalities ?? 0);
                                    if (thr) thr.textContent = data?.cardData?.topThreatGroups ?? 'N/A';

                                    if (data?.trendSeries?.labels) {
                                        criLine.updateOptions({
                                            xaxis: {
                                                categories: data.trendSeries.labels
                                            }
                                        });
                                        criLine.updateSeries([{
                                            name: 'Fatalities',
                                            data: data.trendSeries.data || []
                                        }]);
                                    }

                                    criRenderInsights(data.aiInsights || [], CRI_INDEX_TYPE);

                                    const badge = document.getElementById('cri-insight-badge');
                                    if (badge) {
                                        badge.textContent = data?.aiMeta?.source === 'groq' ?
                                            CRI_INDEX_TYPE + ' Analysis' :
                                            CRI_INDEX_TYPE + (CRI_IS_AUTH ? ' Fallback' : ' Preview') + ' Insights';
                                    }
                                })
                                .catch(err => {
                                    _criLock = false;
                                    console.error('[CRI] fetch failed:', err);
                                    criTreemap.updateOptions({
                                        noData: {
                                            text: 'Failed to load data.'
                                        }
                                    });
                                    const tbody = document.getElementById('cri-risk-table-body');
                                    if (tbody) tbody.innerHTML =
                                        '<tr><td colspan="7" class="py-10 px-4 text-center text-red-500">Failed to load table data.</td></tr>';
                                    ['cri-card-incidents', 'cri-card-fatalities', 'cri-card-threats'].forEach(
                                        id => {
                                            const el = document.getElementById(id);
                                            if (el) el.textContent = 'N/A';
                                        });
                                    criHideLoading();
                                });
                        }

                        // Year change — always allowed, zero restrictions
                        yearSelect?.addEventListener('change', loadCRI);

                        // Initial load
                        loadCRI();
                    });
                })();
            </script>

        </div>{{-- end overview tab --}}

        {{-- ═══════════════════════════════════════════════════════════════
             TAB CONTENT: RISK INDEX ANALYSIS
             (Terrorism Index & Kidnapping Index — tier-gated)
        ═══════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'analysis'" class="pt-10 text-white">
            @include('partials.securityIntelligence.terrorism-analysis')
        </div>

    </div>
</x-layout>

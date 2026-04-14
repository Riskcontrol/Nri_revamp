<div class="max-w-7xl mx-auto">

    <form id="analysis-filter-form">
        <section class="max-w-7xl mx-auto bg-gradient-to-br from-[#1E2D3D] to-[#111f3a] rounded-lg shadow-xl py-8 px-6">

            <div class="space-y-2 text-center">
                <h1 id="analysis-main-title" class="text-2xl font-semibold text-white mt-2">Risk Index Analysis</h1>
                <p class="text-sm font-medium text-gray-300">Detailed breakdown of Terrorism and Kidnapping risk
                    indicators across Nigeria</p>
            </div>

            @guest
                {{-- Guests see a Terrorism Index preview — prompt them to sign in for full access --}}
                <div class="mt-4 bg-blue-900/30 border border-blue-500 rounded-lg p-4 text-center">
                    <p class="text-blue-200 text-sm">
                        You are viewing a <strong>Terrorism Index preview</strong> for the current year.
                        <a href="{{ route('login') }}" class="underline hover:text-blue-100">Sign in</a>
                        for full access including historical data and the Kidnapping Index.
                    </p>
                </div>
            @endguest

            @auth
                @php $isTier2 = auth()->user() && (int) auth()->user()->tier === 1; @endphp
                @if ($isTier2)
                    <div class="mt-4 bg-amber-900/30 border border-amber-500 rounded-lg p-4 text-center">
                        <p class="text-amber-200 text-sm">
                            You are viewing the <strong>Terrorism Index</strong> for the current year.
                            Upgrade to Premium for historical data and the Kidnapping Index.
                        </p>
                    </div>
                @endif
            @endauth

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-center sm:space-x-4 space-y-4 sm:space-y-0">

                {{-- Index type selector --}}
                <div>
                    <div class="relative mt-1">
                        @php
                            $user = auth()->user();
                            $isTier2 = $user && (int) $user->tier === 1;
                            $currentYear = (int) date('Y');
                            $startYear = 2018;
                        @endphp

                        <select id="analysis_index_type" name="index_type"
                            class="block w-full sm:w-60 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            {{-- Terrorism Index is the default and the free/preview index --}}
                            <option value="Terrorism Index" selected>Terrorism Index</option>
                            <option value="Kidnapping Index">Kidnapping Index (Premium)</option>
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

                {{-- Year selector --}}
                <div>
                    <div class="relative mt-1">
                        <select id="analysis_year" name="year"
                            class="block w-full sm:w-40 appearance-none rounded-md border border-gray-600 bg-[#2b3a4a] py-3 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            @foreach (range($currentYear, $startYear) as $y)
                                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                    {{ $y }}{!! $isTier2 && $y != $currentYear ? ' (Premium)' : '' !!}
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

    {{-- Stat Cards --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Tracked Incidents</h3>
            <div class="mt-4">
                <p id="analysis-card-risk-index" class="text-md font-medium text-white">...</p>
            </div>
        </div>
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Fatalities</h3>
            <div class="mt-4">
                <p id="analysis-card-fatalities" class="text-md font-medium text-gray-100">...</p>
            </div>
        </div>
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide">Active Threat Groups</h3>
            <p id="analysis-card-top-threats" class="text-md font-medium text-gray-100 mt-4">.....</p>
        </div>
    </div>

    {{-- AI Insights --}}
    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <span id="analysis-insight-badge"
                class="mt-2 sm:mt-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-900 text-indigo-200 border border-indigo-700">
                Loading Analysis...
            </span>
        </div>
        <div class="relative">
            <div id="analysis-insight-loading"
                class="hidden absolute inset-0 bg-[#1E2D3D] z-10 flex items-center justify-center">
                <p class="text-gray-400 text-sm animate-pulse">Updating intelligence...</p>
            </div>
            <ul id="analysis-insight-list" class="space-y-4"></ul>
        </div>
    </div>

    {{-- Charts --}}
    <div class="mt-8 grid grid-cols-1 gap-6">
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-400">Geographic Analysis</h3>
            <div class="mt-4">
                <div id="analysis-treemap-chart"></div>
            </div>
        </div>
        <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-md">
            <h3 id="analysis-line-chart-title" class="text-xl font-semibold text-gray-400">Fatality Trend</h3>
            <div class="mt-4">
                <div id="analysis-fatality-line-chart"></div>
            </div>
        </div>
    </div>

    {{-- State Risk Ranking Table --}}
    <div class="mt-8 bg-[#1E2D3D] p-6 rounded-lg shadow-md lg:col-span-3">
        <h3 id="analysis-table-title" class="text-xl font-semibold text-gray-400 mb-4">State Risk Ranking</h3>
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
                <tbody id="analysis-risk-table-body" class="text-gray-200">
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
<x-tier-lock-modal />

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // ── Analysis tab constants ───────────────────────────────────────────────
    const ANALYSIS_IS_AUTH = @json(auth()->check());
    const ANALYSIS_USER_TIER = @json(auth()->user()?->tier);
    const ANALYSIS_IS_TIER2 = ANALYSIS_USER_TIER !== null && parseInt(ANALYSIS_USER_TIER, 10) === 1;
    const ANALYSIS_CURRENT_YEAR = @json((int) date('Y'));

    // ── Tier-lock preview rules ──────────────────────────────────────────────
    //   Guest    → Terrorism Index, current year, via /risk-preview-data
    //   Tier2    → Terrorism Index only, current year only, via /risk-treemap-data
    //   Premium  → Any index, any year, via /risk-treemap-data

    // ── Auth modal ───────────────────────────────────────────────────────────
    function analysisOpenAuthModal() {
        const modal = document.getElementById('authRequiredModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function analysisCloseAuthModal() {
        const modal = document.getElementById('authRequiredModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // ── Tier-lock modal ──────────────────────────────────────────────────────
    function analysisOpenTierLockModal(payload = {}) {
        const modal = document.getElementById('tierLockModal');
        if (!modal) return;

        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };
        const setHref = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.href = val;
        };

        set('tierLockTitle', 'Premium Feature');
        set('tierLockSubtitle', 'This analysis option is locked on your plan.');
        set('tierLockLabel1', 'Locked option');
        set('tierLockLabel2', 'Allowed now');
        set('tierLockFooterText', 'Upgrade to access all indices and historical years.');
        set('tierLockMessage', payload.message || 'This option is locked on your plan.');

        const lockedOpt = payload.locked_index || payload.locked_year || payload.locked_item || '';
        const allowedNow = payload.allowed ?
            `${payload.allowed.index_type || 'Terrorism Index'} • ${payload.allowed.year || 'Current Year'}` :
            'Terrorism Index • Current Year';

        set('tierLockLocation', lockedOpt || 'Premium option');
        set('tierLockWhen', allowedNow);
        if (payload.cta_url) setHref('tierLockCta', payload.cta_url);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function analysisCloseTierLockModal() {
        const modal = document.getElementById('tierLockModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // ── Shared helpers ───────────────────────────────────────────────────────
    function analysisGetRiskCategory(v) {
        if (v <= 1.5) return 'Low';
        if (v <= 3.5) return 'Medium';
        if (v <= 7.0) return 'High';
        return 'Very High';
    }

    function analysisGetRiskLevelClass(level) {
        const m = {
            Low: 'bg-green-600 text-white',
            Medium: 'bg-yellow-500 text-white',
            High: 'bg-[#fc4444] text-white',
            'Very High': 'bg-red-700 text-white'
        };
        return m[level] || 'bg-gray-500 text-white';
    }

    function analysisShowInsightsLoading() {
        const overlay = document.getElementById('analysis-insight-loading');
        const badge = document.getElementById('analysis-insight-badge');
        const list = document.getElementById('analysis-insight-list');
        if (badge) badge.textContent = 'Generating insights...';
        if (overlay) overlay.classList.remove('hidden');
        if (list) {
            list.style.opacity = '0.6';
            list.innerHTML = '<li class="text-gray-400 text-sm">Updating intelligence…</li>';
        }
    }

    function analysisHideInsightsLoading() {
        const overlay = document.getElementById('analysis-insight-loading');
        const list = document.getElementById('analysis-insight-list');
        if (overlay) overlay.classList.add('hidden');
        if (list) list.style.opacity = '1';
    }

    function analysisRenderAiInsights(aiInsights, selectedType) {
        const list = document.getElementById('analysis-insight-list');
        const badge = document.getElementById('analysis-insight-badge');
        if (!list || !badge) return;
        badge.textContent = `${selectedType} AI Insights`;
        const html = (aiInsights || []).slice(0, 3).map(item =>
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
            analysisHideInsightsLoading();
        }, 150);
    }

    function analysisUpdateRiskTable(tableData) {
        const tbody = document.getElementById('analysis-risk-table-body');
        if (!tbody) return;
        if (!tableData || !tableData.length) {
            tbody.innerHTML =
                '<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">No data available for this filter.</td></tr>';
            return;
        }
        tbody.innerHTML = tableData.map(s => {
            const sc = s.status === 'Escalating' ? 'text-red-500' : s.status === 'Improving' ?
                'text-green-500' : 'text-gray-400';
            return `<tr class="border-b border-gray-700 hover:bg-gray-700">
                <td class="py-3 px-4 font-medium">${s.state}</td>
                <td class="py-3 px-4">${s.risk_score}%</td>
                <td class="py-3 px-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${analysisGetRiskLevelClass(s.risk_level)}">${s.risk_level}</span></td>
                <td class="py-3 px-4">${s.rank_current}</td>
                <td class="py-3 px-4">${s.rank_previous}</td>
                <td class="py-3 px-4 font-semibold ${sc}">${s.status}</td>
                <td class="py-3 px-4">${s.incidents}</td>
            </tr>`;
        }).join('');
    }

    // ── DOMContentLoaded ─────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {

        // Modal close wiring
        document.getElementById('authModalClose')?.addEventListener('click', analysisCloseAuthModal);
        document.getElementById('authRequiredModal')?.addEventListener('click', function(e) {
            if (e.target === this) analysisCloseAuthModal();
        });
        document.getElementById('tierLockClose')?.addEventListener('click', analysisCloseTierLockModal);
        document.getElementById('tierLockOk')?.addEventListener('click', analysisCloseTierLockModal);
        document.getElementById('tierLockModal')?.addEventListener('click', function(e) {
            if (e.target === this) analysisCloseTierLockModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                analysisCloseAuthModal();
                analysisCloseTierLockModal();
            }
        });

        const indexSelect = document.getElementById('analysis_index_type');
        const yearSelect = document.getElementById('analysis_year');

        // Tracks the last values that were successfully loaded
        // — used only for rollback when a locked option is chosen.
        let lastAllowedIndex = 'Terrorism Index';
        let lastAllowedYear = String(ANALYSIS_CURRENT_YEAR);

        // ── Build ApexCharts ──────────────────────────────────────────────────
        const treemapEl = document.querySelector('#analysis-treemap-chart');
        const lineEl = document.querySelector('#analysis-fatality-line-chart');
        if (!treemapEl || !lineEl) {
            console.error('Missing analysis chart containers.');
            return;
        }

        const analysisChart = new ApexCharts(treemapEl, {
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
                    formatter: v => parseFloat(v).toFixed(2) + '% Risk (' + analysisGetRiskCategory(v) +
                        ')'
                }
            },
            noData: {
                text: 'Loading Risk Data...'
            }
        });
        analysisChart.render();

        const analysisFatalityChart = new ApexCharts(lineEl, {
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
        analysisFatalityChart.render();

        window.addEventListener('risk-analysis:open', () => {
            setTimeout(() => {
                try {
                    analysisChart.resize();
                } catch (e) {}
                try {
                    analysisFatalityChart.resize();
                } catch (e) {}
            }, 50);
        });

        // ── Placeholders ──────────────────────────────────────────────────────
        function setAnalysisLoadingPlaceholders() {
            const tbody = document.getElementById('analysis-risk-table-body');
            if (tbody) tbody.innerHTML =
                '<tr><td colspan="7" class="py-10 px-4 text-center text-gray-500">Loading risk table...</td></tr>';
            ['analysis-card-risk-index', 'analysis-card-top-threats', 'analysis-card-fatalities'].forEach(
            id => {
                const el = document.getElementById(id);
                if (el) el.textContent = '...';
            });
        }

        // ── Gate logic ────────────────────────────────────────────────────────
        //  Returns { allowed: bool, usePreview: bool } so the caller knows which
        //  endpoint to hit without needing a recursive call.
        //
        //  Rules:
        //    Guest  → always use preview; rollback selects to Terrorism/current year.
        //    Tier2  → Terrorism Index + current year only; anything else → rollback + modal.
        //    Premium→ full access, no restrictions.
        //
        function evaluateAccess() {
            const chosenIndex = indexSelect?.value ?? 'Terrorism Index';
            const chosenYear = parseInt(yearSelect?.value ?? String(ANALYSIS_CURRENT_YEAR), 10);

            // ── Guest ────────────────────────────────────────────────────────
            if (!ANALYSIS_IS_AUTH) {
                // Guests always see Terrorism Index preview (current year).
                // Silently rollback any selection attempt — auth modal is shown
                // only when they actually interact with the selects (see below).
                if (indexSelect) indexSelect.value = 'Terrorism Index';
                if (yearSelect) yearSelect.value = String(ANALYSIS_CURRENT_YEAR);
                return {
                    allowed: true,
                    usePreview: true
                };
            }

            // ── Tier2 ────────────────────────────────────────────────────────
            if (ANALYSIS_IS_TIER2) {
                const indexOk = chosenIndex === 'Terrorism Index';
                const yearOk = chosenYear === ANALYSIS_CURRENT_YEAR;

                if (indexOk && yearOk) return {
                    allowed: true,
                    usePreview: false
                };

                // Rollback selects first — prevents modal re-fire on next call
                if (indexSelect) indexSelect.value = lastAllowedIndex;
                if (yearSelect) yearSelect.value = String(ANALYSIS_CURRENT_YEAR);

                analysisHideInsightsLoading();
                setAnalysisLoadingPlaceholders();

                if (!indexOk) {
                    analysisOpenTierLockModal({
                        message: 'The Kidnapping Index is available on Premium.',
                        locked_index: chosenIndex,
                        allowed: {
                            index_type: 'Terrorism Index',
                            year: ANALYSIS_CURRENT_YEAR
                        }
                    });
                } else {
                    analysisOpenTierLockModal({
                        message: 'Historical data is available on Premium.',
                        locked_year: chosenYear,
                        allowed: {
                            index_type: 'Terrorism Index',
                            year: ANALYSIS_CURRENT_YEAR
                        }
                    });
                }

                return {
                    allowed: false,
                    usePreview: false
                };
            }

            // ── Premium (full access) ────────────────────────────────────────
            return {
                allowed: true,
                usePreview: false
            };
        }

        // ── Main update function ──────────────────────────────────────────────
        let _isUpdating = false; // re-entrancy guard

        const analysisUpdateChartData = function() {
            if (_isUpdating) return;
            _isUpdating = true;

            const access = evaluateAccess();

            if (!access.allowed) {
                // Selects already rolled back by evaluateAccess(); do not fetch.
                _isUpdating = false;
                return;
            }

            // Determine what to display after access check (selects may have been reset)
            const selectedIndexText = indexSelect?.options?.[indexSelect.selectedIndex]?.text ??
                'Terrorism Index';
            const selectedYear = yearSelect?.value ?? String(ANALYSIS_CURRENT_YEAR);
            const indexType = indexSelect?.value ?? 'Terrorism Index';

            const titleEl = document.getElementById('analysis-main-title');
            const tableTitleEl = document.getElementById('analysis-table-title');
            if (titleEl) titleEl.textContent = `${selectedIndexText} - ${selectedYear}`;
            if (tableTitleEl) tableTitleEl.textContent = `${selectedIndexText}: State Risk Ranking`;

            analysisShowInsightsLoading();
            analysisChart.updateOptions({
                noData: {
                    text: 'Loading filtered data...'
                }
            });
            setAnalysisLoadingPlaceholders();

            // ── Decide endpoint ────────────────────────────────────────────────
            // Guests and Tier2 (current year terrorism) → preview endpoint.
            // Premium or Tier2 passing the gate → full endpoint.
            const endpoint = access.usePreview ?
                '/risk-preview-data' :
                `/risk-treemap-data?year=${encodeURIComponent(selectedYear)}&index_type=${encodeURIComponent(indexType)}`;

            fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async r => {
                    const data = await r.json().catch(() => ({}));

                    if (!r.ok) {
                        // Server-side 403: rollback selects and show modal
                        if (r.status === 403 && data?.upgrade) {
                            if (data.allowed?.index_type && indexSelect) indexSelect.value = data
                                .allowed.index_type;
                            if (data.allowed?.year && yearSelect) yearSelect.value = data.allowed
                                .year;
                            lastAllowedIndex = indexSelect?.value ?? lastAllowedIndex;
                            lastAllowedYear = yearSelect?.value ?? lastAllowedYear;
                            analysisOpenTierLockModal(data);
                            analysisHideInsightsLoading();
                            setAnalysisLoadingPlaceholders();
                            _isUpdating = false;
                            return null;
                        }
                        throw new Error(data?.message || 'Request failed');
                    }
                    return data;
                })
                .then(data => {
                    if (!data) {
                        _isUpdating = false;
                        return;
                    }

                    // Update "last allowed" only after a successful fetch
                    lastAllowedIndex = indexSelect?.value ?? lastAllowedIndex;
                    lastAllowedYear = yearSelect?.value ?? lastAllowedYear;

                    analysisChart.updateSeries(data.treemapSeries || []);
                    analysisUpdateRiskTable(data.tableData || []);

                    const inc = document.getElementById('analysis-card-risk-index');
                    const fat = document.getElementById('analysis-card-fatalities');
                    const thr = document.getElementById('analysis-card-top-threats');
                    if (inc) inc.textContent = data?.cardData?.totalTrackedIncidents ?? 0;
                    if (fat) fat.textContent = new Intl.NumberFormat().format(data?.cardData
                        ?.totalFatalities ?? 0);
                    if (thr) thr.textContent = data?.cardData?.topThreatGroups ?? 'N/A';

                    if (data?.trendSeries?.labels) {
                        analysisFatalityChart.updateOptions({
                            xaxis: {
                                categories: data.trendSeries.labels
                            }
                        });
                        analysisFatalityChart.updateSeries([{
                            name: 'Fatalities',
                            data: data.trendSeries.data || []
                        }]);
                        const lineTitle = document.getElementById('analysis-line-chart-title');
                        if (lineTitle) lineTitle.textContent = `${selectedIndexText} Fatality Trend`;
                    }

                    analysisRenderAiInsights(data.aiInsights || [], selectedIndexText);

                    const badge = document.getElementById('analysis-insight-badge');
                    if (badge) {
                        badge.textContent = data?.aiMeta?.source === 'groq' ?
                            `${selectedIndexText} Analysis` :
                            `${selectedIndexText} ${access.usePreview ? 'Preview' : (ANALYSIS_IS_AUTH ? 'Fallback' : 'Preview')} Insights`;
                    }
                    _isUpdating = false;
                })
                .catch(err => {
                    console.error('[Analysis] fetch failed:', err);
                    analysisChart.updateOptions({
                        noData: {
                            text: 'Failed to load data.'
                        }
                    });
                    const tbody = document.getElementById('analysis-risk-table-body');
                    if (tbody) tbody.innerHTML =
                        '<tr><td colspan="7" class="py-10 px-4 text-center text-red-500">Failed to load table data.</td></tr>';
                    ['analysis-card-risk-index', 'analysis-card-top-threats',
                        'analysis-card-fatalities'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = 'N/A';
                    });
                    analysisHideInsightsLoading();
                    _isUpdating = false;
                });
        };

        // ── Guest interaction blocking ─────────────────────────────────────────
        // Guests can see the preview but clicking the selects should prompt sign-in.
        if (!ANALYSIS_IS_AUTH) {
            function blockForGuest(e, selectEl, fallback) {
                selectEl.value = fallback;
                e.preventDefault();
                e.stopPropagation();
                analysisOpenAuthModal();
                selectEl.blur();
            }
            const blockIndex = e => blockForGuest(e, indexSelect, 'Terrorism Index');
            const blockYear = e => blockForGuest(e, yearSelect, String(ANALYSIS_CURRENT_YEAR));

            ['pointerdown', 'mousedown', 'focus'].forEach(evt => {
                indexSelect?.addEventListener(evt, blockIndex);
                yearSelect?.addEventListener(evt, blockYear);
            });
        }

        // ── Change handlers ───────────────────────────────────────────────────
        indexSelect?.addEventListener('change', analysisUpdateChartData);
        yearSelect?.addEventListener('change', analysisUpdateChartData);

        // ── Initial load ──────────────────────────────────────────────────────
        analysisUpdateChartData();
    });
</script>

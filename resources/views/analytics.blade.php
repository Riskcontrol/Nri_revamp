<x-layout title="Analytics Dashboard">
    <script src="//unpkg.com/alpinejs" defer></script>

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-900 text-gray-100 font-sans">

        <aside
            class="w-full md:w-80 bg-[#1E2D3D] shadow-2xl p-6 border-r border-gray-800 overflow-y-auto h-screen sticky top-0 custom-scrollbar z-20">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-white tracking-wide">Threat Insight</h2>
                <div class="h-1 w-10 bg-emerald-500 mt-2 rounded"></div>
            </div>

            <form id="analytics-filters" onsubmit="event.preventDefault(); updateDashboard();" class="space-y-5">

                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col">
                        <label class="text-xs font-bold uppercase text-gray-400 mb-1.5">Start Year</label>
                        <select id="start_year"
                            class="w-full p-2.5 bg-gray-800 border border-gray-700 rounded text-sm text-gray-200 focus:border-emerald-500 outline-none"></select>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold uppercase text-gray-400 mb-1.5">End Year</label>
                        <select id="end_year"
                            class="w-full p-2.5 bg-gray-800 border border-gray-700 rounded text-sm text-gray-200 focus:border-emerald-500 outline-none"></select>
                    </div>
                </div>

                <div x-data="{ mode: 'state' }">
                    <div class="flex flex-col mb-4">
                        <label class="text-xs font-bold uppercase text-gray-400 mb-2">Compare By:</label>
                        <div class="flex bg-gray-800 p-1 rounded border border-gray-700">
                            <button type="button" @click="mode = 'state'; updateMultiSelectMode('state')"
                                :class="mode === 'state' ? 'bg-emerald-600 text-white shadow' :
                                    'text-gray-400 hover:text-white'"
                                class="flex-1 py-1.5 text-xs font-bold rounded transition-all">States</button>
                            <button type="button" @click="mode = 'region'; updateMultiSelectMode('region')"
                                :class="mode === 'region' ? 'bg-emerald-600 text-white shadow' :
                                    'text-gray-400 hover:text-white'"
                                class="flex-1 py-1.5 text-xs font-bold rounded transition-all">Regions</button>
                        </div>
                        <input type="hidden" id="dimension" :value="mode">
                    </div>

                    <div x-data="multiSelect()" x-init="init()" @click.away="open = false"
                        class="relative flex flex-col">
                        <label class="text-xs font-bold uppercase text-gray-400 mb-1.5"><span
                                x-text="mode === 'state' ? 'Select States' : 'Select Regions'"></span></label>
                        <input type="hidden" id="selection" :value="selected.join(',')">
                        <button type="button" @click="open = !open"
                            class="w-full p-2.5 bg-gray-800 border border-gray-700 rounded text-sm text-gray-200 focus:border-emerald-500 outline-none text-left flex justify-between items-center hover:bg-gray-750 transition-colors">
                            <span x-text="selected.length ? selected.length + ' Selected' : 'Choose...'"></span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                            class="absolute top-full left-0 w-full mt-1 bg-gray-800 border border-gray-700 rounded shadow-xl z-50 max-h-60 overflow-y-auto custom-scrollbar"
                            style="display: none;">
                            <template x-for="option in options" :key="option">
                                <div @click="toggle(option)"
                                    class="p-2 flex items-center cursor-pointer hover:bg-gray-700 group">
                                    <div class="w-4 h-4 border rounded flex items-center justify-center transition-colors"
                                        :class="selected.includes(option) ? 'bg-emerald-600 border-emerald-600' :
                                            'border-gray-500 bg-gray-900'">
                                        <svg x-show="selected.includes(option)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-200 group-hover:text-white"
                                        x-text="option"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                @foreach (['risk_factor' => 'Risk Factors', 'risk_indicator' => 'Risk Indicator', 'attack_group_id' => 'Attack Group', 'weapon_id' => 'Weapon', 'motive_id' => 'Motive'] as $id => $label)
                    <div class="flex flex-col">
                        <label class="text-xs font-bold uppercase text-gray-400 mb-1.5">{{ $label }}</label>
                        <select id="{{ $id }}"
                            class="w-full p-2.5 bg-gray-800 border border-gray-700 rounded text-sm text-gray-200 focus:border-emerald-500 outline-none">
                            <option value="">{{ 'All ' . $label . 's' }}</option>
                        </select>
                    </div>
                @endforeach

                <div class="pt-4 space-y-3">
                    <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded shadow transition-transform active:scale-95">Apply
                        Filters</button>
                    <button type="button" onclick="resetAllFilters()"
                        class="w-full bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium py-2 rounded text-sm transition-colors">Reset
                        Dashboard</button>
                </div>
            </form>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto relative">
            <div id="loading-overlay"
                class="absolute inset-0 bg-gray-900/80 z-50 flex flex-col items-center justify-center backdrop-blur-sm"
                style="display: none;">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-emerald-500 mb-4"></div>
                <p class="text-emerald-400 font-bold text-sm tracking-wider animate-pulse">ANALYZING DATA...</p>
            </div>

            <div class="mb-6 flex flex-wrap items-center gap-2 min-h-[32px]" id="active-filters-container"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-2">Total Deaths</p>
                    <h3 class="text-5xl font-black text-white" id="stat-deaths">0</h3>
                </div>
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-2">Victims</p>
                    <h3 class="text-5xl font-black text-white" id="stat-victims">0</h3>
                </div>
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg text-center">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-2">Injuries</p>
                    <h3 class="text-5xl font-black text-white" id="stat-injuries">0</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg col-span-1 lg:col-span-2">
                    <h4 class="font-bold text-white text-lg mb-4">Comparison Timeline</h4>
                    <div id="chart-timeline" class="h-96 w-full"></div>
                </div>

                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg col-span-1 lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h4 id="title-factors" class="font-bold text-white text-lg">Risk Factor Breakdown</h4>
                        <span class="text-xs text-gray-400 italic">Analysis</span>
                    </div>
                    <div id="chart-factors" class="h-80"></div>
                </div>

                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg col-span-1 lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h4 id="title-risks" class="font-bold text-white text-lg">Indicator Breakdown</h4>
                        <span class="text-xs text-gray-400 italic">Analysis</span>
                    </div>
                    <div id="chart-risks" class="h-80"></div>
                </div>

                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h4 id="title-motives" class="font-bold text-white text-lg">Motives</h4>
                    </div>
                    <div id="chart-motives" class="h-64 flex justify-center items-center"></div>
                </div>

                <div class="bg-[#1E2D3D] p-6 rounded-lg shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h4 id="title-weapons" class="font-bold text-white text-lg">Weapons</h4>
                    </div>
                    <div id="chart-weapons" class="h-64 flex justify-center items-center"></div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1E2D3D;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4B5563;
            border-radius: 3px;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        let rawData = {
            states: [],
            regions: []
        };
        let charts = {};

        // ALPINE
        function multiSelect() {
            return {
                open: false,
                options: [],
                selected: [],
                init() {
                    window.multiSelectComponent = this;
                    if (rawData.states.length > 0) this.populateOptions('state');
                },
                populateOptions(mode) {
                    this.selected = [];
                    this.options = (mode === 'state') ? rawData.states : rawData.regions;
                    if (this.options.length) this.selected = [this.options[0]];
                },
                toggle(option) {
                    if (this.selected.includes(option)) this.selected = this.selected.filter(i => i !== option);
                    else {
                        if (this.selected.length >= 5) {
                            alert("Max 5 items.");
                            return;
                        }
                        this.selected.push(option);
                    }
                },
                remove(option) {
                    this.selected = this.selected.filter(i => i !== option);
                    updateDashboard();
                }
            }
        }

        function updateMultiSelectMode(mode) {
            if (window.multiSelectComponent) window.multiSelectComponent.populateOptions(mode);
        }

        // CHART SETUP
        function initCharts() {
            const common = {
                chart: {
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                theme: {
                    mode: 'dark'
                },
                grid: {
                    borderColor: '#374151'
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: '#9CA3AF'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9CA3AF'
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: '#D1D5DB'
                    },
                    position: 'top'
                }
            };
            const colors = ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#F43F5E'];

            // Timeline
            charts.timeline = new ApexCharts(document.querySelector("#chart-timeline"), {
                ...common,
                chart: {
                    type: 'line',
                    height: 380,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                colors: colors,
                series: [],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            colors: '#9CA3AF'
                        }
                    }
                }
            });
            charts.timeline.render();

            // Stacked Bar Inits
            charts.factors = new ApexCharts(document.querySelector("#chart-factors"), {
                ...common,
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    stacked: true,
                    toolbar: {
                        show: false
                    }
                },
                colors: colors,
                series: [],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    }
                }
            });
            charts.factors.render();

            charts.risks = new ApexCharts(document.querySelector("#chart-risks"), {
                ...common,
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    stacked: true,
                    toolbar: {
                        show: false
                    }
                },
                colors: colors,
                series: [],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 4
                    }
                }
            });
            charts.risks.render();

            // Donut Inits
            const donutConfig = {
                ...common,
                chart: {
                    type: 'donut',
                    height: 280,
                    background: 'transparent'
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: '#D1D5DB'
                    }
                }
            };
            charts.motives = new ApexCharts(document.querySelector("#chart-motives"), {
                ...donutConfig,
                colors: ['#F59E0B', '#EF4444', '#10B981', '#6366F1', '#8B5CF6'],
                series: [],
                labels: []
            });
            charts.motives.render();
            charts.weapons = new ApexCharts(document.querySelector("#chart-weapons"), {
                ...donutConfig,
                colors: ['#EC4899', '#8B5CF6', '#64748B', '#06B6D4'],
                series: [],
                labels: []
            });
            charts.weapons.render();
        }

        // UTILS
        function toggleLoading(show) {
            document.getElementById('loading-overlay').style.display = show ? 'flex' : 'none';
        }

        function resetAllFilters() {
            document.querySelectorAll('select').forEach(s => s.value = "");
            document.getElementById('end_year').value = new Date().getFullYear();
            if (window.multiSelectComponent) window.multiSelectComponent.populateOptions('state');
            updateDashboard();
        }

        function addFilterTag(container, label, colorClass, onRemove) {
            const div = document.createElement('div');
            div.className =
                `flex items-center text-xs font-bold px-3 py-1 rounded-full mr-2 mb-2 shadow-sm border border-gray-700 transition-all hover:bg-gray-700 ${colorClass}`;
            div.innerHTML = `<span>${label}</span>`;
            if (onRemove) {
                const btn = document.createElement('button');
                btn.className = "ml-2 text-gray-400 hover:text-white focus:outline-none";
                btn.innerHTML =
                    `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                btn.onclick = onRemove;
                div.appendChild(btn);
            }
            container.appendChild(div);
        }

        // INIT
        async function loadFilters() {
            toggleLoading(true);
            try {
                const res = await fetch('/analytics/options');
                const data = await res.json();
                rawData.states = data.states;
                rawData.regions = data.regions;
                const fill = (id, arr) => arr.forEach(v => document.getElementById(id).innerHTML +=
                    `<option value="${v}">${v}</option>`);
                fill('start_year', data.years);
                fill('end_year', data.years);
                document.getElementById('end_year').value = new Date().getFullYear();
                const fillObj = (id, obj) => {
                    const el = document.getElementById(id);
                    for (const [k, v] of Object.entries(obj)) el.innerHTML += `<option value="${k}">${v}</option>`;
                };
                fillObj('risk_factor', data.factors);
                fill('risk_indicator', data.indicators);
                fillObj('motive_id', data.motives);
                fillObj('attack_group_id', data.attack_groups);
                fillObj('weapon_id', data.weapons);
                if (window.multiSelectComponent) window.multiSelectComponent.populateOptions('state');
                setTimeout(updateDashboard, 500);
            } catch (err) {
                console.error(err);
                toggleLoading(false);
            }
        }

        // UPDATE DASHBOARD
        async function updateDashboard() {
            toggleLoading(true);
            // Inputs
            const ids = ['risk_factor', 'risk_indicator', 'attack_group_id', 'weapon_id', 'motive_id'];
            const vals = {};
            ids.forEach(id => vals[id] = document.getElementById(id)); // Store elements

            // Tags
            const container = document.getElementById('active-filters-container');
            container.innerHTML = '';
            addFilterTag(container,
                `${document.getElementById('start_year').value} - ${document.getElementById('end_year').value}`,
                'bg-gray-800 text-gray-300', null);
            if (window.multiSelectComponent && window.multiSelectComponent.selected.length > 0)
                window.multiSelectComponent.selected.forEach(loc => addFilterTag(container, loc,
                    'bg-gray-800 text-emerald-400', () => window.multiSelectComponent.remove(loc)));

            ids.forEach(id => {
                const el = vals[id];
                if (el.value) {
                    const text = el.options[el.selectedIndex].text;
                    addFilterTag(container, text, 'bg-gray-800 text-blue-400', () => {
                        el.value = "";
                        updateDashboard();
                    });
                }
            });

            // Fetch
            const filters = {
                start_year: document.getElementById('start_year').value,
                end_year: document.getElementById('end_year').value,
                dimension: document.getElementById('dimension').value,
                selection: document.getElementById('selection').value,
                risk_factor: vals.risk_factor.value,
                risk_indicator: vals.risk_indicator.value,
                attack_group_id: vals.attack_group_id.value,
                weapon_id: vals.weapon_id.value,
                motive_id: vals.motive_id.value
            };

            try {
                const res = await fetch(`/analytics/data?` + new URLSearchParams(filters), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                // Timeline
                charts.timeline.updateOptions({
                    xaxis: {
                        categories: data.timeline.categories
                    }
                });
                charts.timeline.updateSeries(data.timeline.series);

                // --- SMART CHART UPDATES ---
                // Helper to switch chart modes (Stacked vs Simple)
                const updateSmartChart = (chartObj, titleId, dataObj, filterVal, titleBase) => {
                    const titleEl = document.getElementById(titleId);
                    const isDonut = chartObj.w.config.chart.type === 'donut';

                    if (dataObj.is_drilldown) {
                        // DRILLDOWN
                        titleEl.innerText = `Distribution of Selected ${titleBase}`;
                        if (isDonut) {
                            // Donut -> Bar
                            chartObj.updateOptions({
                                chart: {
                                    type: 'bar'
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        columnWidth: '40%'
                                    }
                                },
                                xaxis: {
                                    categories: dataObj.categories
                                }
                            });
                        } else {
                            // Stacked Bar -> Simple Bar
                            chartObj.updateOptions({
                                chart: {
                                    stacked: false
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        columnWidth: '40%'
                                    }
                                },
                                xaxis: {
                                    categories: dataObj.categories
                                }
                            });
                        }
                    } else {
                        // OVERVIEW
                        titleEl.innerText = `${titleBase} Overview`;
                        if (isDonut) {
                            // Bar -> Donut (Revert)
                            // Note: We need sum logic for donut here, but for simplicity we kept stacked bars in controller logic.
                            // If we want donut overview, we usually just sum.
                            // Reverting to Donut type:
                            chartObj.updateOptions({
                                chart: {
                                    type: 'donut'
                                },
                                labels: dataObj.categories
                            });
                            // Donut needs 1D array. Flatten series data.
                            let totals = new Array(dataObj.categories.length).fill(0);
                            dataObj.series.forEach(s => s.data.forEach((v, i) => totals[i] += v));
                            chartObj.updateSeries(totals);
                            return; // Exit special handling for donut
                        } else {
                            // Simple Bar -> Stacked Bar
                            chartObj.updateOptions({
                                chart: {
                                    stacked: true
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        columnWidth: '50%'
                                    }
                                },
                                xaxis: {
                                    categories: dataObj.categories
                                }
                            });
                        }
                    }
                    chartObj.updateSeries(dataObj.series);
                };

                updateSmartChart(charts.factors, 'title-factors', data.factors, vals.risk_factor.value, 'Risk Factor');

                // Indicators: Keep horizontal orientation
                if (data.risks.is_drilldown) {
                    document.getElementById('title-risks').innerText = "Distribution of Selected Indicator";
                    charts.risks.updateOptions({
                        chart: {
                            stacked: false
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '40%'
                            }
                        },
                        xaxis: {
                            categories: data.risks.categories
                        }
                    });
                } else {
                    document.getElementById('title-risks').innerText = "Top Indicators";
                    charts.risks.updateOptions({
                        chart: {
                            stacked: true
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '60%'
                            }
                        },
                        xaxis: {
                            categories: data.risks.categories
                        }
                    });
                }
                charts.risks.updateSeries(data.risks.series);

                updateSmartChart(charts.motives, 'title-motives', data.motives, vals.motive_id.value, 'Motive');
                updateSmartChart(charts.weapons, 'title-weapons', data.weapons, vals.weapon_id.value, 'Weapon');

                // Cards
                const fmt = (n) => n ? n.toLocaleString() : '0';
                document.getElementById('stat-deaths').innerText = fmt(data.impact.deaths);
                document.getElementById('stat-victims').innerText = fmt(data.impact.victims);
                document.getElementById('stat-injuries').innerText = fmt(data.impact.injuries);

            } catch (err) {
                console.error(err);
            } finally {
                setTimeout(() => toggleLoading(false), 300);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            loadFilters();
        });
    </script>
</x-layout>

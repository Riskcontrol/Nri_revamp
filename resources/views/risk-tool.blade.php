<x-layout>
    <div x-data="riskTool()" class="min-h-screen bg-[#0F172A] text-slate-200 pb-20">

        <header class="sticky top-0 z-[1000] bg-[#1E293B] border-b border-slate-700 py-4 px-6 shadow-2xl">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded flex items-center justify-center font-bold">R</div>
                    <h1 class="font-bold text-xl tracking-tight">RISK<span class="text-blue-400">INTEL</span> DASHBOARD
                    </h1>
                </div>
                <a href="/"
                    class="text-xs uppercase font-bold tracking-widest text-slate-400 hover:text-white transition">Exit
                    Portal</a>
            </div>
        </header>

        <div class="max-w-6xl mx-auto mt-10 px-4">
            <div class="bg-[#1E293B] rounded-2xl p-8 border border-slate-700 shadow-lg mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Select State</label>
                        <select x-model="state" @change="updateLgas()"
                            class="w-full bg-[#0F172A] border-slate-600 rounded-lg text-white focus:ring-blue-500">
                            <option value="">-- State --</option>
                            @foreach ($states as $name => $lgas)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Select LGA</label>
                        <select x-model="lga" :disabled="!state"
                            class="w-full bg-[#0F172A] border-slate-600 rounded-lg text-white focus:ring-blue-500">
                            <option value="">-- LGA --</option>
                            <template x-for="item in currentLgas" :key="item">
                                <option :value="item" x-text="item"></option>
                            </template>
                        </select>
                    </div>
                    <button @click="fetchAnalysis()" :disabled="!lga || loading"
                        class="w-full h-[42px] bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">GENERATE FULL REPORT</span>
                        <span x-show="loading" class="animate-pulse">PROCESSING DATA...</span>
                    </button>
                </div>
            </div>

            <div x-show="reportVisible" x-cloak class="space-y-6 animate-in fade-in zoom-in duration-500">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-[#1E293B] p-6 rounded-xl border border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Incidents</p>
                        <h3 class="text-3xl font-black text-white" x-text="data.total"></h3>
                    </div>
                    <div class="bg-[#1E293B] p-6 rounded-xl border border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Trend (YoY)</p>
                        <h3 class="text-3xl font-black" :class="data.trend > 0 ? 'text-red-400' : 'text-green-400'"
                            x-text="(data.trend > 0 ? '+' : '') + data.trend + '%'"></h3>
                    </div>
                    <div class="bg-[#1E293B] p-6 rounded-xl border border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Casualties</p>
                        <h3 class="text-3xl font-black text-white" x-text="data.casualties"></h3>
                    </div>
                    <div class="bg-red-500/10 p-6 rounded-xl border border-red-500/50">
                        <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Risk Grade</p>
                        <h3 class="text-3xl font-black text-red-500" x-text="data.score + '/100'"></h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-[#1E293B] p-6 rounded-xl border border-slate-700">
                        <h4 class="text-sm font-bold mb-6 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span> INCIDENT TYPE DISTRIBUTION
                        </h4>
                        <div class="h-64">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-[#1E293B] rounded-xl border border-slate-700 overflow-hidden relative z-0">
                        <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/50">
                            <span class="text-[10px] font-bold uppercase tracking-widest">Incident Density</span>
                            <span class="px-2 py-0.5 bg-red-500 text-[9px] font-bold rounded"
                                x-text="data.impact_level + ' IMPACT'"></span>
                        </div>
                        <div class="h-full min-h-[250px] bg-slate-900 flex items-center justify-center p-6 text-center">
                            <div>
                                <div
                                    class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400 leading-relaxed italic"
                                    x-text="'Visualizing incident clusters for ' + lga"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1E293B] rounded-xl border border-slate-700 overflow-hidden">
                    <div class="p-4 bg-slate-800/50 border-b border-slate-700">
                        <h3 class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Intelligence advisory summary
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                        <div>
                            <h5 class="text-blue-400 text-[10px] font-bold uppercase mb-2">Strategic Advisory</h5>
                            <p class="text-slate-300 leading-relaxed text-sm font-light" x-text="data.advisory"></p>
                        </div>
                        <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700/50">
                            <h5 class="text-slate-500 text-[10px] font-bold uppercase mb-2">Internal Analyst Notes</h5>
                            <p class="text-slate-400 italic text-sm" x-text="data.recent_note"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function riskTool() {
            return {
                state: '',
                lga: '',
                loading: false,
                reportVisible: false,
                statesData: @json($states),
                currentLgas: [],
                data: {},
                chart: null,

                updateLgas() {
                    this.currentLgas = this.statesData[this.state] || [];
                    this.lga = '';
                },

                async fetchAnalysis() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/risk-analysis?state=${this.state}&lga=${this.lga}`);
                        this.data = await response.json();
                        this.reportVisible = true;
                        this.$nextTick(() => this.renderChart());
                    } catch (e) {
                        console.error(e);
                    }
                    this.loading = false;
                },

                renderChart() {
                    const ctx = document.getElementById('riskChart').getContext('2d');
                    if (this.chart) this.chart.destroy();

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.data.distribution.map(i => i.label),
                            datasets: [{
                                label: 'Incident Frequency',
                                data: this.data.distribution.map(i => i.value),
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                                barThickness: 20
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    grid: {
                                        color: '#334155'
                                    },
                                    ticks: {
                                        color: '#94a3b8'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#94a3b8'
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
</x-layout>

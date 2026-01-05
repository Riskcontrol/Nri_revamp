<x-layout>
    <div x-data="riskTool()" class="min-h-screen bg-gray-50 pb-20">

        <header class="sticky top-0 z-[1000] bg-slate-900 text-white shadow-lg py-4 px-6">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <h1 class="font-bold text-xl">RiskIntel <span class="text-blue-400">Magnet</span></h1>
                <a href="/" class="text-sm opacity-80 hover:opacity-100">Portal Home</a>
            </div>
        </header>

        <div class="max-w-5xl mx-auto mt-12 px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-slate-800">Local Risk Assessment</h2>
                    <p class="text-slate-500">Select your location to generate a localized risk profile.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">State</label>
                        <select x-model="state" @change="updateLgas()"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500">
                            <option value="">Select State</option>
                            @foreach ($states as $stateName => $lgas)
                                <option value="{{ $stateName }}">{{ $stateName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">LGA</label>
                        <select x-model="lga" :disabled="!state"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500">
                            <option value="">Select LGA</option>
                            <template x-for="item in currentLgas" :key="item">
                                <option :value="item" x-text="item"></option>
                            </template>
                        </select>
                    </div>
                    <button @click="fetchAnalysis()" :disabled="!lga || loading"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition disabled:opacity-50">
                        <span x-show="!loading">Generate Report</span>
                        <span x-show="loading">Analyzing Data...</span>
                    </button>
                </div>
            </div>

            <div x-show="reportVisible" x-cloak class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase">Risk Score</p>
                        <h3 class="text-4xl font-black text-red-600" x-text="data.score + '/100'"></h3>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase">Annual Trend</p>
                        <h3 class="text-4xl font-black text-slate-800"
                            :class="data.trend > 0 ? 'text-red-500' : 'text-green-500'" x-text="data.trend + '%'"></h3>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase">Casualties (All-Time)</p>
                        <h3 class="text-4xl font-black text-slate-800" x-text="data.casualties"></h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="font-bold mb-4">Incident Distribution</h4>
                        <canvas id="riskChart" height="200"></canvas>
                    </div>

                    <div
                        class="bg-slate-200 rounded-xl overflow-hidden relative z-10 min-h-[300px] flex items-center justify-center border border-gray-300">
                        <div class="text-center p-6">
                            <p class="text-slate-500 font-medium">Incident Density Map</p>
                            <p class="text-xs text-slate-400" x-text="'Visualizing ' + lga"></p>
                        </div>
                    </div>
                </div>

                <div class="relative mt-12">
                    <div x-show="!unlocked"
                        class="absolute inset-0 z-20 backdrop-blur-md bg-white/40 flex items-center justify-center rounded-2xl border-2 border-dashed border-blue-300">
                        <div
                            class="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full text-center border border-gray-100">
                            <div
                                class="mb-4 inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-600 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Unlock Security Advisory</h3>
                            <p class="text-sm text-gray-500 mb-6">Enter your email to see specific business risk
                                mitigation strategies for this LGA.</p>

                            <form @submit.prevent="submitLead()">
                                <input type="email" required x-model="leadEmail" placeholder="Work email address"
                                    class="w-full mb-3 rounded-lg border-gray-300">
                                <button type="submit"
                                    class="w-full bg-slate-900 text-white py-3 rounded-lg font-bold hover:bg-black transition">Get
                                    Full Access</button>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl border border-gray-200 shadow-sm"
                        :class="!unlocked ? 'select-none' : ''">
                        <h3 class="font-bold text-xl mb-4">Strategic Advisory for <span x-text="lga"></span></h3>
                        <div class="prose prose-slate max-w-none">
                            <p x-text="data.advisory"></p>
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
                unlocked: false,
                leadEmail: '',
                data: {},
                statesData: @json($states), // Pass your PHP array to JS
                currentLgas: [],
                chartInstance: null,

                updateLgas() {
                    this.currentLgas = this.statesData[this.state] || [];
                    this.lga = '';
                },

                async fetchAnalysis() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/risk-tool/analyze?state=${this.state}&lga=${this.lga}`);
                        this.data = await response.json();
                        this.reportVisible = true;
                        this.renderChart(this.data.distribution);
                    } catch (e) {
                        console.error(e);
                    }
                    this.loading = false;
                },

                renderChart(data) {
                    const ctx = document.getElementById('riskChart').getContext('2d');
                    if (this.chartInstance) this.chartInstance.destroy();

                    this.chartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.map(i => i.label),
                            datasets: [{
                                label: 'Incident Count',
                                data: data.map(i => i.value),
                                backgroundColor: '#3b82f6',
                                borderRadius: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                },

                async submitLead() {
                    // Send leadEmail to your backend leads table
                    this.unlocked = true;
                }
            }
        }
    </script>
</x-layout>

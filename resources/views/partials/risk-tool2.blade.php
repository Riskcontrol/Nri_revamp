<section class="bg-[#f8fafc] py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h6 class="text-sm md:text-base font-bold tracking-tight text-[#10b981] mb-6">
            Nigeria Risk Assessment Calculator
        </h6>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-semibold tracking-tight text-[#0f172a] mb-8">
            Calculate Your Organization's Risk Score
        </h1>
        <p class="text-lg md:text-xl text-slate-600 mb-12 max-w-3xl mx-auto">
            Get a personalized risk assessment for your location and industry. Our interactive tool analyzes security
            data to provide actionable intelligence.
        </p>

        <button onclick="toggleRiskModal(true)"
            class="inline-flex items-center gap-2 bg-card hover:bg-[#111a2e] text-white font-bold text-sm px-6 py-6 rounded-lg shadow-md transition-all duration-300 hover:-translate-y-0.5 active:scale-95 uppercase tracking-wider">
            <span>Start Risk Assessment</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </div>
</section>

<div id="risk-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" onclick="toggleRiskModal(false)">
    </div>

    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
        <div
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-6xl border border-gray-200">

            <button onclick="toggleRiskModal(false)"
                class="absolute top-4 right-4 z-50 p-2 text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div class="flex flex-col md:flex-row min-h-[600px]">

                {{-- Left Side: Input Form --}}
                <div class="w-full md:w-3/5 bg-[#1A2B3C] p-6 md:p-10 text-white">
                    <p class="text-xs md:text-sm text-emerald-400 font-medium mb-1">Nigeria Risk Assessment Calculator
                    </p>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-2 uppercase">Interactive Security
                        Analysis Tool</h1>
                    <div class="flex items-center text-gray-300 font-light pb-6 border-b border-gray-700/50 mb-8">
                        Get Personalized Risk Insights
                        <svg class="w-5 h-5 ml-1 text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            </path>
                        </svg>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        {{-- Location --}}
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider mb-2">1. LOCATION</h3>
                            <label class="block text-gray-400 text-xs mb-1">Select State</label>
                            <select id="calc_state"
                                class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 outline-none text-white">
                                <option value="" disabled selected>Choose Location...</option>
                                @foreach ($calculatorStates as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Industry --}}
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider mb-2">2. ORGANIZATION</h3>
                            <label class="block text-gray-400 text-xs mb-1">Select Business Type</label>
                            <select id="calc_industry"
                                class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 outline-none text-white">
                                <option value="" disabled selected>Choose Industry...</option>
                                @foreach ($calculatorIndustries as $industry)
                                    <option value="{{ $industry }}">{{ $industry }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Security Measures --}}
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-bold uppercase tracking-wider mb-2">3. CURRENT SECURITY MEASURES
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach (['personnel' => 'Security Personnel', 'cctv' => 'CCTV Systems', 'access' => 'Access Control', 'protocols' => 'SOPs / Protocols'] as $val => $label)
                                    <label
                                        class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-[#2A3F50] border border-transparent hover:border-gray-600 transition">
                                        <input type="checkbox"
                                            class="risk-measure form-checkbox text-emerald-500 rounded bg-gray-700 border-gray-600"
                                            value="{{ $val }}">
                                        <span class="text-sm text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Button --}}
                        <div class="md:col-span-2 mt-4">
                            <button onclick="calculateRisk()" id="btn-calculate"
                                class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-md uppercase transition-all shadow-lg flex justify-center items-center gap-2">
                                <span>CALCULATE RISK SCORE</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Results Visualization --}}
                <div
                    class="w-full md:w-2/5 bg-white p-6 md:p-10 text-gray-900 flex flex-col justify-center relative min-h-[400px]">
                    <div id="result-overlay"
                        class="absolute inset-0 bg-gray-50 z-10 flex flex-col items-center justify-center text-center p-6 transition-opacity duration-300">
                        <div
                            class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-gray-900 font-bold text-lg">Ready to Analyze</h3>
                        <p class="text-gray-500 text-sm mt-2">Select parameters to generate your risk profile.</p>
                    </div>

                    <div id="result-content" class="opacity-0 transition-opacity duration-500">
                        <h2 class="text-lg font-bold uppercase tracking-wider text-gray-700 mb-6 text-center">YOUR RISK
                            ASSESSMENT</h2>

                        <div class="relative w-40 h-40 mx-auto mb-6">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="80" cy="80" r="70" stroke="#f3f4f6" stroke-width="12"
                                    fill="none" />
                                <circle id="score-ring" cx="80" cy="80" r="70" stroke="#10b981"
                                    stroke-width="12" fill="none" stroke-dasharray="440" stroke-dashoffset="440"
                                    stroke-linecap="round" class="transition-all duration-1000 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p id="result-score" class="text-5xl font-black text-gray-900">0</p>
                                <p class="text-xs text-gray-500 font-bold uppercase mt-1">/ 100</p>
                            </div>
                        </div>

                        <div class="text-center mb-8">
                            <p class="text-sm text-gray-500 mb-1">Estimated Risk Level</p>
                            <h2 id="result-label" class="text-3xl font-extrabold text-emerald-600 uppercase">LOW</h2>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-blue-700 font-medium">Mitigation Impact</span>
                                <span
                                    class="text-sm text-blue-900 font-bold bg-white px-2 py-1 rounded shadow-sm">-<span
                                        id="result-savings">0</span> pts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Toggles the visibility of the risk assessment modal.
     */
    function toggleRiskModal(show) {
        const modal = document.getElementById('risk-modal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Lock background scroll
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Unlock background scroll
        }
    }

    /**
     * Handles the calculation logic by communicating with the Laravel backend.
     */
    async function calculateRisk() {
        const btn = document.getElementById('btn-calculate');
        const overlay = document.getElementById('result-overlay');
        const content = document.getElementById('result-content');

        // Validation
        const state = document.getElementById('calc_state').value;
        const industry = document.getElementById('calc_industry').value;

        if (!state || !industry) {
            alert('Please select both a Location and an Industry.');
            return;
        }

        // Set Loading State
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">ANALYZING DATA...</span>';
        btn.disabled = true;

        // Gather checked mitigation measures
        const measures = [];
        document.querySelectorAll('.risk-measure:checked').forEach(c => measures.push(c.value));

        try {
            const response = await fetch("{{ route('api.calc-risk') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    state,
                    industry,
                    measures
                })
            });

            const data = await response.json();

            if (data.success) {
                // 1. Reveal results panel
                overlay.classList.add('opacity-0', 'pointer-events-none');
                content.classList.remove('opacity-0');

                // 2. Update text fields
                document.getElementById('result-score').innerText = data.final_score;
                document.getElementById('result-label').innerText = data.label;
                document.getElementById('result-savings').innerText = data.savings;

                // 3. Animate the SVG Gauge Ring
                const ring = document.getElementById('score-ring');
                const circumference = 440; // Approx 2 * PI * r
                const offset = circumference - ((data.final_score / 100) * circumference);

                // Determine color based on severity
                let color = '#10b981'; // Low
                let textClass = 'text-emerald-600';

                if (data.final_score >= 50) {
                    color = '#f59e0b';
                    textClass = 'text-yellow-600';
                } // Moderate
                if (data.final_score >= 75) {
                    color = '#ef4444';
                    textClass = 'text-red-600';
                } // High

                ring.style.stroke = color;
                ring.style.strokeDashoffset = offset;
                document.getElementById('result-label').className =
                    `text-3xl font-extrabold uppercase ${textClass}`;
            }
        } catch (error) {
            console.error("Calculation failed:", error);
            alert('An error occurred during risk analysis.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Global listener to close modal on 'Escape'
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') toggleRiskModal(false);
    });
</script>

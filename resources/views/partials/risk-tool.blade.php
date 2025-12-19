<div class="py-10 md:py-14 bg-white" id="risk-calculator-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="shadow-2xl rounded-2xl overflow-hidden flex flex-col md:flex-row border border-gray-200">

            {{-- Left Side: Calculator Form --}}
            <div class="w-full md:w-3/5 bg-[#1A2B3C] p-6 md:p-10 text-white">

                <p class="text-xs md:text-sm text-emerald-400 font-medium mb-1">
                    Nigeria Risk Assessment Calculator
                </p>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2 uppercase">
                    INTERACTIVE SECURITY ANALYSIS TOOL
                </h1>
                <div
                    class="flex items-center text-base md:text-lg text-gray-300 font-light pb-6 border-b border-gray-700/50 mb-8">
                    Get Personalized Risk Insights
                    <svg class="w-5 h-5 ml-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">

                    {{-- Step 1: Location --}}
                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2 flex items-center">
                            1. LOCATION
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 text-xs md:text-sm mb-1">Select State</label>
                                <select id="calc_state"
                                    class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 outline-none text-white">
                                    <option value="" disabled selected>Choose Location...</option>
                                    @foreach ($calculatorStates as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Industry --}}
                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2">
                            2. ORGANIZATION
                        </h3>
                        <div>
                            <label class="block text-gray-400 text-xs md:text-sm mb-1">Select Business Type</label>
                            <select id="calc_industry"
                                class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 outline-none text-white">
                                <option value="" disabled selected>Choose Industry...</option>
                                @foreach ($calculatorIndustries as $industry)
                                    <option value="{{ $industry }}">{{ $industry }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Step 3: Mitigation --}}
                    <div class="md:col-span-2">
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2">
                            3. CURRENT SECURITY MEASURES
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-[#2A3F50] border border-transparent hover:border-gray-600 transition">
                                <input type="checkbox"
                                    class="risk-measure form-checkbox text-emerald-500 rounded bg-gray-700 border-gray-600"
                                    value="personnel">
                                <span class="text-sm text-gray-300">Security Personnel</span>
                            </label>
                            <label
                                class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-[#2A3F50] border border-transparent hover:border-gray-600 transition">
                                <input type="checkbox"
                                    class="risk-measure form-checkbox text-emerald-500 rounded bg-gray-700 border-gray-600"
                                    value="cctv">
                                <span class="text-sm text-gray-300">CCTV Systems</span>
                            </label>
                            <label
                                class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-[#2A3F50] border border-transparent hover:border-gray-600 transition">
                                <input type="checkbox"
                                    class="risk-measure form-checkbox text-emerald-500 rounded bg-gray-700 border-gray-600"
                                    value="access">
                                <span class="text-sm text-gray-300">Access Control</span>
                            </label>
                            <label
                                class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-[#2A3F50] border border-transparent hover:border-gray-600 transition">
                                <input type="checkbox"
                                    class="risk-measure form-checkbox text-emerald-500 rounded bg-gray-700 border-gray-600"
                                    value="protocols">
                                <span class="text-sm text-gray-300">SOPs / Protocols</span>
                            </label>
                        </div>
                    </div>

                    {{-- Calculate Button --}}
                    <div class="md:col-span-2 mt-4">
                        <button onclick="calculateRisk()" id="btn-calculate"
                            class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-md uppercase transition-all shadow-lg hover:shadow-emerald-900/50 flex justify-center items-center gap-2">
                            <span>CALCULATE RISK SCORE</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            {{-- Right Side: Results --}}
            <div
                class="w-full md:w-2/5 bg-white p-6 md:p-10 text-gray-900 border-t md:border-t-0 md:border-l border-gray-200 flex flex-col justify-center relative min-h-[400px]">

                {{-- Overlay: Shown before calculation --}}
                <div id="result-overlay"
                    class="absolute inset-0 bg-gray-50 z-10 flex flex-col items-center justify-center text-center p-6 transition-opacity duration-300">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4 text-gray-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-gray-900 font-bold text-lg">Ready to Analyze</h3>
                    <p class="text-gray-500 text-sm mt-2">Select your parameters on the left to see your personalized
                        risk profile.</p>
                </div>

                {{-- Results Content --}}
                <div id="result-content" class="opacity-0 transition-opacity duration-500">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-gray-700 mb-6 text-center md:text-left">
                        YOUR RISK ASSESSMENT
                    </h2>

                    {{-- SVG Gauge --}}
                    <div class="relative w-40 h-40 mx-auto mb-6">
                        <svg class="w-full h-full transform -rotate-90">
                            {{-- Background Circle --}}
                            <circle cx="80" cy="80" r="70" stroke="#f3f4f6" stroke-width="12"
                                fill="none" />
                            {{-- Foreground Circle (Animated) --}}
                            <circle id="score-ring" cx="80" cy="80" r="70" stroke="#10b981"
                                stroke-width="12" fill="none" stroke-dasharray="440" stroke-dashoffset="440"
                                stroke-linecap="round" class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p id="result-score" class="text-5xl font-black text-gray-900 leading-none">0</p>
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
                            <span class="text-sm text-blue-900 font-bold bg-white px-2 py-1 rounded shadow-sm">
                                -<span id="result-savings">0</span> pts
                            </span>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">Your security measures actively reduced your risk score.
                        </p>
                    </div>

                    <div class="mt-8 space-y-3">
                        <button
                            class="w-full py-3 bg-[#2196F3] hover:bg-blue-600 text-white font-medium rounded-md shadow-md transition-colors text-sm">
                            View Detailed Analysis
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    async function calculateRisk() {
        const btn = document.getElementById('btn-calculate');
        const overlay = document.getElementById('result-overlay');
        const content = document.getElementById('result-content');

        const state = document.getElementById('calc_state').value;
        const industry = document.getElementById('calc_industry').value;

        if (!state || !industry) {
            alert('Please select both a Location and an Industry.');
            return;
        }

        // Loading State
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">ANALYZING...</span>';
        btn.disabled = true;

        // Gather Checkboxes
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
                // 1. Hide Overlay / Show Content
                overlay.classList.add('opacity-0', 'pointer-events-none');
                content.classList.remove('opacity-0');

                // 2. Update Text
                document.getElementById('result-score').innerText = data.final_score;
                document.getElementById('result-label').innerText = data.label;
                document.getElementById('result-savings').innerText = data.savings;

                // 3. Animate Gauge
                const ring = document.getElementById('score-ring');
                const circumference = 440; // 2 * PI * 70
                const offset = circumference - ((data.final_score / 100) * circumference);

                // Set Color
                let color = '#10b981'; // Green
                let textClass = 'text-emerald-600';

                if (data.final_score >= 50) {
                    color = '#f59e0b';
                    textClass = 'text-yellow-600';
                }
                if (data.final_score >= 75) {
                    color = '#ef4444';
                    textClass = 'text-red-600';
                }

                ring.style.stroke = color;
                ring.style.strokeDashoffset = offset;

                const label = document.getElementById('result-label');
                label.className = `text-3xl font-extrabold uppercase ${textClass}`;
            }

        } catch (error) {
            console.error(error);
            alert('Error calculating risk.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

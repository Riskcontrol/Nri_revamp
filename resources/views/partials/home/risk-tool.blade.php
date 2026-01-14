<section class="bg-[#f8fafc] py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h6 class="text-sm md:text-base font-semibold tracking-tight text-[#10b981] mb-6">
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
            class="inline-flex items-center gap-2 bg-card hover:bg-[#111a2e] text-white font-semibold text-sm px-6 py-6 rounded-lg shadow-md transition-all duration-300 hover:-translate-y-0.5 active:scale-95 uppercase tracking-wider">
            <span>Start Risk Assessment</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </div>
</section>

<div id="risk-modal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" role="dialog">
    <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-md" onclick="toggleRiskModal(false)"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            class="relative transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all w-full max-w-6xl border border-gray-100">
            <button onclick="toggleRiskModal(false)"
                class="absolute top-4 right-4 z-50 text-slate-400 hover:text-red-500 bg-white/10 hover:bg-white/20 rounded-full p-2 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
            <div class="flex flex-col md:flex-row min-h-[650px]">

                {{-- Left Side: Selection Panel (33%) --}}
                <div class="w-full md:w-1/3 bg-[#0f172a] p-8 text-white flex flex-col">
                    <div>
                        <h2 class="text-emerald-400 text-xs font-bold uppercase tracking-widest mb-2">Filters</h2>
                        <h1 class="text-2xl font-semibold mb-8 leading-tight">Define Parameters</h1>
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] text-slate-400 font-bold uppercase">State</label>
                                <select id="calc_state" onchange="updateLGAs()"
                                    class="w-full mt-1 bg-slate-800 border-slate-700 rounded-lg p-3 text-sm focus:ring-2 focus:ring-emerald-500">
                                    <option value="" disabled selected>Choose State...</option>
                                    @foreach ($states as $stateName => $lgas)
                                        <option value="{{ $stateName }}">{{ $stateName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 font-semibold uppercase">LGA</label>
                                <select id="calc_lga" disabled
                                    class="w-full mt-1 bg-slate-800 border-slate-700 rounded-lg p-3 text-sm disabled:opacity-50">
                                    <option value="">Select State first</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 font-semibold uppercase">Year</label>
                                <select id="calc_year"
                                    class="w-full mt-1 bg-slate-800 border-slate-700 rounded-lg p-3 text-sm">
                                    @for ($y = date('Y'); $y >= 2018; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <button onclick="calculateRisk()" id="btn-calculate"
                        class="w-full mt-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-[#0f172a] font-medium rounded-xl flex justify-center items-center gap-2">
                        <span>GENERATE REPORT</span>
                    </button>
                </div>

                {{-- Right Side: Results & Lead Magnet (66%) --}}
                <div class="w-full md:w-2/3 bg-slate-50 p-8 md:p-12 overflow-y-auto">
                    <div id="result-placeholder" class="h-full flex flex-col items-center justify-center text-center">
                        <i class="fa-solid fa-chart-line text-4xl text-slate-300 mb-4"></i>
                        <h3 class="text-slate-800 font-bold text-xl">Assessment Results</h3>
                        <p class="text-slate-500 max-w-xs">Select a location and year to generate your risk profile.
                        </p>
                    </div>

                    <div id="result-data" class="hidden">
                        {{-- Comparative Benchmark --}}
                        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-sm">
                            <p class="text-xs text-blue-800">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                Incident activity in this LGA is <span id="res-comparison" class="font-bold"></span>
                                than the state average of <span id="res-state-avg" class="font-bold"></span>
                                incidents.
                            </p>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div
                                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center justify-center">
                                <div class="relative w-24 h-24 flex items-center justify-center mb-2">
                                    <svg class="absolute inset-0 w-full h-full -rotate-90">
                                        <circle cx="48" cy="48" r="40" stroke="#f1f5f9" stroke-width="8"
                                            fill="none" />
                                        <circle id="score-ring" cx="48" cy="48" r="40" stroke="#10b981"
                                            stroke-width="8" fill="none" stroke-dasharray="251"
                                            stroke-dashoffset="251" class="transition-all duration-1000" />
                                    </svg>
                                    <span id="res-score" class="text-3xl font-black">0</span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Risk Range</span>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-2 gap-4">
                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase">Impact</p>
                                    <p id="res-impact" class="text-xl font-semibold">LOW</p>
                                </div>
                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Prevalent Risk</p>
                                    <p id="res-indicator" class="text-sm font-bold text-slate-700 truncate">N/A</p>
                                </div>
                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Tracked Incidents</p>
                                    <p id="res-incidents" class="text-xl font-black text-slate-800">0</p>
                                </div>
                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Casualties</p>
                                    <p id="res-casualties" class="text-xl font-black text-slate-800">0</p>
                                </div>
                            </div>
                        </div>


                        {{-- Lead Magnet Section --}}
                        <div
                            class="bg-slate-900 rounded-3xl p-8 text-white mb-8 text-center shadow-lg relative overflow-hidden">

                            {{-- Background Decoration --}}
                            <div
                                class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl">
                            </div>

                            <h3 class="text-xl font-bold mb-2 relative z-10">Download Full Security Advisory</h3>
                            <p class="text-slate-400 text-sm mb-6 relative z-10">
                                Get a deep-dive analysis of local threats and expert mitigation strategies for this
                                region.
                            </p>

                            {{-- STATE 1: Input Form --}}
                            <div id="lead-input-container" class="relative z-10 transition-all duration-300">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <input type="email" id="lead-email" placeholder="Your work email"
                                        class="flex-1 bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-white placeholder-slate-500 transition-all">
                                    <button onclick="submitLead()"
                                        class="bg-emerald-500 hover:bg-emerald-400 text-[#0f172a] font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                                        Get Full Report
                                    </button>
                                </div>
                            </div>

                            {{-- STATE 2: Processing / Success --}}
                            <div id="lead-process-container"
                                class="hidden flex-col items-center justify-center py-2 relative z-10 animate-fade-in">

                                {{-- Loading Icon --}}
                                <div id="process-icon-loading">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-emerald-500 mb-3"></i>
                                </div>

                                {{-- Success Icon --}}
                                <div id="process-icon-success" class="hidden">
                                    <div
                                        class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center mb-3 shadow-lg shadow-emerald-500/30">
                                        <i class="fa-solid fa-check text-slate-900 text-xl font-bold"></i>
                                    </div>
                                </div>

                                <h4 id="process-title" class="font-bold text-lg text-white">Generating Report...</h4>
                                <p id="process-desc" class="text-xs text-slate-400 mt-1">
                                    Analyzing <span id="process-year"></span> incident data for <span
                                        id="process-loc"></span>...
                                </p>

                                {{-- Reset Button --}}
                                <button id="btn-reset-lead" onclick="resetLeadForm()"
                                    class="hidden mt-4 text-emerald-400 hover:text-white text-xs font-bold uppercase tracking-wider underline decoration-emerald-500/50 hover:decoration-white transition-all">
                                    Generate Another Report
                                </button>
                            </div>
                        </div>
                        {{--
                        <div class="bg-blue-900 text-white p-6 rounded-2xl">
                            <h3 class="text-xs font-bold text-blue-300 uppercase mb-2">How this score is calculated
                            </h3>
                            <p class="text-xs leading-relaxed opacity-90">Our algorithm weighs <strong>Incident
                                    Frequency (40%)</strong>, <strong>Lethality (40%)</strong>, and <strong>Yearly
                                    Trends (20%)</strong>. Benchmark comparison evaluates local activity against the
                                state's average per-LGA incident rate.</p>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- CUSTOM POPUP: NO DATA / WARNING --}}
<div id="no-data-modal" class="fixed inset-0 z-[10000] hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeNoDataModal()"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div
            class="relative transform overflow-hidden rounded-2xl bg-[#0f172a] border border-slate-700 px-4 pb-4 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">

            {{-- Warning Icon --}}
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 mb-5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xl"></i>
            </div>

            {{-- Text Content --}}
            <div class="mt-3 text-center sm:mt-0">
                <h3 class="text-lg font-bold leading-6 text-white" id="no-data-title">Insufficient Data</h3>
                <div class="mt-2">
                    <p class="text-sm text-slate-400" id="no-data-message">
                        No security incidents were tracked in this location for the selected year.
                    </p>
                </div>
            </div>

            {{-- Action Button --}}
            <div class="mt-5 sm:mt-6">
                <button type="button" onclick="closeNoDataModal()"
                    class="inline-flex w-full justify-center rounded-xl bg-slate-800 px-3 py-3 text-sm font-bold text-white shadow-sm hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600 transition-all uppercase tracking-wide">
                    Okay, Try Another
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const stateData = @json($states);

    function toggleRiskModal(show) {
        document.getElementById('risk-modal').classList.toggle('hidden', !show);
        document.body.style.overflow = show ? 'hidden' : 'auto';
    }

    function updateLGAs() {
        const state = document.getElementById('calc_state').value;
        const lgaSelect = document.getElementById('calc_lga');
        lgaSelect.innerHTML = '<option value="" disabled selected>Choose LGA...</option>';
        lgaSelect.disabled = false;
        if (stateData[state]) {
            stateData[state].forEach(lga => {
                const opt = document.createElement('option');
                opt.value = lga;
                opt.textContent = lga;
                lgaSelect.appendChild(opt);
            });
        }
    }

    // NEW: Open the custom popup
    function showNoDataModal(lga, year) {
        const modal = document.getElementById('no-data-modal');
        const message = document.getElementById('no-data-message');

        message.innerHTML =
            `No security incidents were tracked in <strong class="text-white">${lga}</strong> during <strong class="text-white">${year}</strong>.<br><br>A valid risk report cannot be generated for a zero-incident profile.`;

        modal.classList.remove('hidden');
    }

    // NEW: Close the custom popup
    function closeNoDataModal() {
        document.getElementById('no-data-modal').classList.add('hidden');
    }

    async function calculateRisk() {
        const state = document.getElementById('calc_state').value;
        const lga = document.getElementById('calc_lga').value;
        const year = document.getElementById('calc_year').value;
        const btn = document.getElementById('btn-calculate');

        if (!state || !lga) return alert("Select location");

        btn.disabled = true;
        btn.innerText = "PROCESSING...";

        try {
            const response = await fetch("{{ route('risk-tool.analyze') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    state,
                    lga,
                    year
                })
            });

            const data = await response.json();

            if (data.total == 0 || data.total === "0") {
                showNoDataModal(lga, year)

                // Reset button and exit function
                btn.disabled = false;
                btn.innerText = "GENERATE REPORT";
                return;
            }
            // ----------------------------
            const impactElement = document.getElementById('res-impact');
            const scoreRing = document.getElementById('score-ring');
            impactElement.innerText = data.impact_level;
            impactElement.className = 'text-xl font-semibold';

            // Define colors
            const colorCritical = '#dc2626'; // red-600
            const colorHigh = '#f97316'; // orange-500
            const colorModerate = '#eab308'; // yellow-500
            const colorLow = '#059669'; // emerald-600

            // Apply color based on impact level to both TEXT and RING
            if (data.score >= 75) {
                // CRITICAL
                impactElement.classList.add('text-red-600');
                scoreRing.style.stroke = colorCritical;
            } else if (data.score >= 50) {
                // HIGH
                impactElement.classList.add('text-orange-500');
                scoreRing.style.stroke = colorHigh;
            } else if (data.score >= 25) {
                // MODERATE
                impactElement.classList.add('text-yellow-500');
                scoreRing.style.stroke = colorModerate;
            } else {
                // LOW
                impactElement.classList.add('text-emerald-600');
                scoreRing.style.stroke = colorLow;
            }

            document.getElementById('result-placeholder').classList.add('hidden');
            document.getElementById('result-data').classList.remove('hidden');

            document.getElementById('res-score').innerText = data.score;
            document.getElementById('res-impact').innerText = data.impact_level;
            document.getElementById('res-indicator').innerText = data.top_indicator;
            document.getElementById('res-incidents').innerText = data.total;
            document.getElementById('res-casualties').innerText = data.casualties;
            document.getElementById('res-state-avg').innerText = data.state_avg;
            document.getElementById('res-comparison').innerText = data.comparison;

            const ring = document.getElementById('score-ring');
            ring.style.strokeDashoffset = 251 - (251 * (data.score / 100));

        } catch (e) {
            console.error(e);
        } finally {
            btn.disabled = false;
            btn.innerText = "GENERATE REPORT";
        }
    }

    async function submitLead() {
        const email = document.getElementById('lead-email').value;
        const state = document.getElementById('calc_state').value;
        const lga = document.getElementById('calc_lga').value;
        const year = document.getElementById('calc_year').value;

        if (!email.includes('@')) {
            alert("Please enter a valid email address.");
            return;
        }

        // Switch UI immediately
        const inputContainer = document.getElementById('lead-input-container');
        const processContainer = document.getElementById('lead-process-container');
        inputContainer.classList.add('hidden');
        processContainer.classList.remove('hidden');
        processContainer.classList.add('flex');

        // Set initial state
        document.getElementById('process-icon-loading').classList.remove('hidden');
        document.getElementById('process-icon-success').classList.add('hidden');
        document.getElementById('btn-reset-lead').classList.add('hidden');
        document.getElementById('process-title').innerText = "Generating Report...";
        document.getElementById('process-year').innerText = year;
        document.getElementById('process-loc').innerText = lga;

        try {
            // ADD TIMEOUT to prevent hanging
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 50000); // 30 second timeout

            const response = await fetch("{{ route('report.download') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/pdf'
                },
                body: JSON.stringify({
                    email,
                    state,
                    lga,
                    year
                }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);
            const contentType = response.headers.get('content-type');
            console.log('Response Status:', response.status);
            console.log('Content-Type:', contentType);

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Generation failed");
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Risk_Report_${lga}_${year}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);

            // Success state
            document.getElementById('process-icon-loading').classList.add('hidden');
            document.getElementById('process-icon-success').classList.remove('hidden');
            document.getElementById('process-title').innerText = "Download Started!";
            document.getElementById('process-desc').innerText = "Check your downloads folder.";
            document.getElementById('btn-reset-lead').classList.remove('hidden');

        } catch (error) {
            console.error(error);

            // Show specific error message
            if (error.name === 'AbortError') {
                alert("Request timed out. Please try again.");
            } else {
                alert(error.message || "Error generating report. Please try again.");
            }

            resetLeadForm();
        }
    }

    // UPDATED: Function to fully reset the tool
    function resetLeadForm() {
        const inputContainer = document.getElementById('lead-input-container');
        const processContainer = document.getElementById('lead-process-container');

        // 1. Reset Lead Magnet UI (Bottom Right)
        processContainer.classList.add('hidden');
        processContainer.classList.remove('flex');
        inputContainer.classList.remove('hidden');

        // 2. Reset Parameters Form (Left Side)
        const stateSelect = document.getElementById('calc_state');
        stateSelect.selectedIndex = 0; // Select first option ("Choose State...")

        const lgaSelect = document.getElementById('calc_lga');
        lgaSelect.innerHTML = '<option value="">Select State first</option>'; // Clear options
        lgaSelect.disabled = true; // Disable it

        // 3. Reset Results UI (Right Side)
        // Hide the data, show the empty placeholder again
        document.getElementById('result-data').classList.add('hidden');
        document.getElementById('result-placeholder').classList.remove('hidden');

        // 4. Reset Score Ring Animation
        const ring = document.getElementById('score-ring');
        if (ring) ring.style.strokeDashoffset = 251; // Reset ring to empty
    }
</script>

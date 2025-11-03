<x-layout title="Home"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk
    analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage
    our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">
    {{-- The header is NOT included here because it's in the x-layout component --}}

    <main class="min-h-screen bg-slate-100">
        <!-- HERO SECTION -->
        <div class="relative w-full text-white overflow-hidden"
            style="
       background-image:
         radial-gradient(1200px 600px at 30% -10%, rgba(39,64,78,0.3) 0%, transparent 55%), /* reduced opacity from 0.6 → 0.3 */
         linear-gradient(180deg, rgba(20,33,43,0.4), rgba(14,26,35,0.4)), /* reduced opacity from 0.8 → 0.4 */
         url('/images/map2.png');
       background-size: cover, cover, cover;
       background-repeat: no-repeat, no-repeat, no-repeat;
       background-position: center, center, center;
     ">




            <!-- CONTENT CONTAINER -->
            <div
                class="relative max-w-[1250px] mx-auto grid gap-8 md:gap-12 lg:gap-16 md:grid-cols-3 p-6 sm:p-8 lg:p-10">
                <div class="space-y-4 md:col-span-2">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight tracking-tight">
                        Nigeria’s Premier<br class="hidden sm:block" /> Risk Intelligence Portal
                    </h1>
                    <p class="text-[#cfe7f3] text-sm sm:text-lg leading-relaxed mt-3">
                        Transform complex data into actionable security and business intelligence for informed
                        decision-making
                    </p>
                    <p class="text-white-300 text-sm sm:text-base mt-5 font-bold">
                        Join 500+ security professionals accessing real-time Nigeria intelligence across various
                        industries
                    </p>
                    <div class="flex items-center gap-4 mt-6">
                        <button class="px-5 py-3 rounded-lg text-white font-semibold transition hover:brightness-110"
                            style="background-color: #33a88f;">
                            Start Free Trial
                        </button>

                        <button
                            class="px-6 py-3 rounded-lg border-2 border-white bg-transparent text-white font-bold transition hover:bg-white/10">
                            Assess Your Risk
                        </button>
                    </div>
                </div>

                {{-- MODIFIED: Reduced padding, overall font sizes --}}
                <div class="grid gap-4 content-start"> {{-- Reduced gap from gap-6 to gap-4 --}}
                    <div class="bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-gray-700 font-bold tracking-wide text-base sm:text-lg">Total Incidents 2024</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-700"> {{-- Reduced bold number font --}}
                            {{ number_format($totalIncidents) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-gray-700 font-bold tracking-wide text-base sm:text-lg">High Risk States</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-700">{{ $highRiskStateCount }}
                        </div> {{-- Reduced bold number font --}}
                    </div>
                    <div class="bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-gray-700 font-bold tracking-wide text-base sm:text-lg">Most Affected</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-700"> {{-- Reduced bold number font --}}
                            {{ $top3HighRiskStates }}
                        </div>
                    </div>
                </div>
            </div>


            <!-- QUICK STATS BAND -->
            <div class="relative max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <h4 class="text-slate-200/90 font-extrabold tracking-wide text-lg sm:text-xl mt-2">QUICK STATS</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-3">
                    <div
                        class="relative z-10 bg-white text-gray-900 rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-gray-700 mb-2">Current Threat Level</div>
                        <p class="text-sm text-gray-700">🟥 {{ $currentThreatLevel }}</p>
                        <p class="text-sm text-gray-700">🗺️ {{ $assessmentScope }}</p>
                        <p class="text-sm text-gray-700">
                            📅 <span class="text-red-500 font-bold">Valid Until:</span> {{ $validUntil }}
                        </p>
                        <p class="text-sm text-gray-700">
                            ⚠️ <span class="text-gray-800 font-bold">Key Concerns: </span> {{ $keyConcerns }}
                        </p>
                        {{-- <p class="text-sm text-gray-700">⚠️ Key Concerns: {{ $keyConcerns }}</p> --}}
                    </div>

                    <!-- Recent Incidents (24h) -->
                    <div
                        class="relative z-10 bg-white text-gray-900 rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-gray-700 mb-2">Recent Incidents (24h)</div>

                        <!-- Increased font size for the incident count -->
                        <div class="text-5xl font-extrabold">8</div>

                        <!-- Display the last audited time -->
                        <div class="text-sm text-gray-700 mt-2">Last Updated: {{ $auditedTime }}</div>
                    </div>


                    <!-- Trending Risk Factors -->
                    <div
                        class="relative z-10 bg-white text-gray-900 rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-gray-700 mb-2">Trending Risk Factors</div>
                        <ul class="list-disc text-sm text-gray-700">
                            @foreach ($trendingRiskFactors as $factor)
                                <li class="mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    {{ $factor->riskindicators }}
                                </li>
                            @endforeach
                        </ul>
                    </div>


                    <!-- Risk Intelligence Database -->
                    <div
                        class="relative z-10 bg-white text-gray-900 rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4 flex flex-col gap-2">
                        <div class="font-extrabold text-gray-700">Risk Intelligence Database</div>
                        <div class="text-sm text-gray-700">Nigeria Kidnapping Index </div>
                        <div class="text-sm text-gray-700">Nigeria Terrorism Index </div>
                        <div class="text-sm text-gray-700">Nigeria Crime Index </div>
                        <div class="text-sm text-gray-700">Composite Risk Index </div>
                        {{-- <div class="ml-auto font-extrabold text-indigo-600 text-xl">→</div> --}}
                    </div>

                </div>
            </div>
        </div>
        <!-- /HERO SECTION -->
    </main>

    @include('partials.location-intelligence')
    @include('partials.terrorism-index')
    {{-- @include('partials.risk-heat-map') --}}
    @include('partials.insight-section')

</x-layout>

<x-layout title="Home"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk
    analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage
    our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">
    {{-- The header is NOT included here because it's in the x-layout component --}}

    <main class="min-h-screen bg-primary">
        <div class="relative w-full text-white overflow-hidden"
            style="
       background-image:

         url('/images/background.png');
       background-size: cover, cover, cover;
       background-repeat: no-repeat, no-repeat, no-repeat;
       background-position: center, center, center;
     ">
            <div class="absolute inset-0 bg-gradient-to-b from-[#0A1628]/70 via-[#0A1628]/85 to-[#0A1628]"></div>


            <!-- CONTENT CONTAINER -->
            <div
                class="relative max-w-[1250px] mx-auto grid gap-8 md:gap-12 lg:gap-16 md:grid-cols-3 p-6 sm:p-8 lg:p-10">
                <div class="space-y-6 md:col-span-2">

                    <h1 class="text-4xl sm:text-5xl lg:text-5xl font-bold leading-tight tracking-tight">
                        Nigeria’s Premier<br class="hidden sm:block" /> Risk Intelligence Portal
                    </h1>

                    <p class="text-[#fff] text-xl sm:text-2xl leading-relaxed mt-4">
                        Transform complex data into actionable security and business intelligence for informed
                        decision-making
                    </p>

                    <p class="text-gray-300 text-lg sm:text-xl mt-6 font-semibold">
                        Join 500+ security professionals accessing real-time Nigeria intelligence across various
                        industries
                    </p>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-8">
                        <button
                            class="w-full sm:w-auto px-8 py-4 rounded-lg text-white text-lg font-semibold transition hover:brightness-110"
                            style="background-color: #2196F3;">
                            Start Free Trial
                        </button>

                        <button
                            class="w-full sm:w-auto px-8 py-4 rounded-lg border-2 border-white bg-transparent text-white text-lg font-bold transition hover:bg-white/10">
                            Assess Your Risk
                        </button>
                    </div>

                </div>

                {{-- MODIFIED: Reduced padding, overall font sizes --}}
                <div class="grid gap-4 content-start"> {{-- Reduced gap from gap-6 to gap-4 --}}
                    <div class="bg-[#fff] rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-black font-bold tracking-wide text-base sm:text-lg">Total Incidents 2024</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-black"> {{-- Reduced bold number font --}}
                            {{ number_format($totalIncidents) }}</div>
                    </div>
                    <div class="bg-[#fff] rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-black font-bold tracking-wide text-base sm:text-lg">High Risk States</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-black">{{ $highRiskStateCount }}
                        </div> {{-- Reduced bold number font --}}
                    </div>
                    <div class="bg-[#fff] rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.18)] p-3 sm:p-4">
                        {{-- Reduced padding --}}
                        <h3 class="text-black font-bold tracking-wide text-base sm:text-lg">Most Affected</h3>
                        {{-- Adjusted h3 font --}}
                        <div class="text-lg sm:text-xl lg:text-2xl font-bold text-black"> {{-- Reduced bold number font --}}
                            {{ $top3HighRiskStates }}
                        </div>
                    </div>
                </div>
            </div>


            <!-- QUICK STATS BAND -->
            <div class="relative max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <h4 class="text-slate-200/90 font-extrabold tracking-wide text-lg sm:text-xl mt-2">QUICK STATS</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-3">
                    {{-- Card 1: Current Threat Level --}}
                    <div
                        class="relative z-10 bg-[#1E2D3D] text-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-white mb-2">Current Threat Level</div>
                        <p class="text-sm text-white">🟥 {{ $currentThreatLevel }}</p>
                        <p class="text-sm text-white">🗺️ {{ $assessmentScope }}</p>
                        <p class="text-sm text-white">
                            📅 <span class="text-red-400 font-bold">Valid Until:</span> {{ $validUntil }}
                        </p>
                        <p class="text-sm text-white">
                            ⚠️ <span class="text-white font-bold">Key Concerns: </span> {{ $keyConcerns }}
                        </p>
                    </div>

                    {{-- Card 2: Recent Incidents (24h) --}}
                    <div
                        class="relative z-10 bg-[#1E2D3D] text-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-white mb-2">Recent Incidents (24h)</div>
                        <div class="text-5xl font-extrabold text-white">8</div>
                        <div class="text-sm text-white mt-2">Last Updated: {{ $auditedTime }}</div>
                    </div>

                    {{-- Card 3: Trending Risk Factors --}}
                    <div
                        class="relative z-10 bg-[#1E2D3D] text-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4">
                        <div class="font-extrabold text-white mb-2">Trending Risk Factors</div>
                        <ul class="list-disc text-sm text-white">
                            @foreach ($trendingRiskFactors as $factor)
                                <li class="mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-400"></span> {{-- Changed to red-400 for better contrast --}}
                                    {{ $factor->riskindicators }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Card 4: Risk Intelligence Database --}}
                    <div
                        class="relative z-10 bg-[#1E2D3D] text-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,.12)] p-4 flex flex-col gap-2">
                        <div class="font-extrabold text-white">Risk Intelligence Database</div>
                        <div class="text-sm text-white">Nigeria Kidnapping Index </div>
                        <div class="text-sm text-white">Nigeria Terrorism Index </div>
                        <div class="text-sm text-white">Nigeria Crime Index </div>
                        <div class="text-sm text-white">Composite Risk Index </div>
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


    <section class=" py-8 sm:py-16">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <div class="flex justify-center lg:justify-end">
                <img src="{{ asset('images/mobile.png') }}" alt="Risk Track app" class="max-w-xl w-full h-auto">
            </div>

            <div class="text-center lg:text-left">


                <h2 class="text-6xl font-bold tracking-tight text-white sm:text-3xl mt-4">
                    Safety in Your Pocket
                </h2>


                <p class="mt-4 text-2xl font-medium leading-8 text-white max-w-xl mx-auto lg:mx-0">
                    See Risks Nearby, Monitor live Incidents in Real Time
                </p>
                <p class="mt-6 text-xl font-bold leading-8 text-white max-w-xl mx-auto lg:mx-0">
                    Download the app
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="#" target="_blank" class="block">
                        <img src="images/google.svg" alt="Get it on Google Play" class="h-14">
                    </a>
                    <a href="#" target="_blank" class="block">
                        <img src="images/apple.svg" alt="Download on the App Store" class="h-14">
                    </a>
                </div>
            </div>

        </div>
    </section>
</x-layout>

<x-layout title="Home"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">
    {{-- The header is NOT included here because it's in the x-layout component --}}

    <main class="min-h-screen bg-primary">
        <div class="relative w-full text-white overflow-hidden"
            style="
       background-image:
         url('/images/background-Edited.png');
       background-size: cover, cover, cover;
       background-repeat: no-repeat, no-repeat, no-repeat;
       background-position: center, center, center;
     ">
            <div class="absolute inset-0 bg-gradient-to-b from-[#0A1628]/80 via-[#0A1628]/85 to-[#0A1628]"></div>


            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-20 grid gap-8 md:gap-12 lg:gap-16 md:grid-cols-3">

                {{-- LEFT COLUMN: Text & Buttons --}}
                <div class="space-y-6 md:col-span-2">

                    <h1 class="text-4xl sm:text-3xl lg:text-5xl font-bold leading-tight tracking-tight">
                        Nigeria’s Most Comprehensive <br class="hidden sm:block" />Security Intelligence Platform
                    </h1>

                    <p class="text-white text-xl sm:text-xl leading-relaxed mt-4">
                        Get real-time alerts on security incidents across all 36 states.<br> Transform threat data into
                        strategic decisions with predictive analytics, <br> location-based risk assessments, and
                        verified
                        incident reports.
                    </p>

                    <p class="text-[#FDA557] text-lg sm:text-xl mt-6 font-semibold">
                        Trusted by 500+ security professionals across <br> banking, energy, telecom, and logistics
                        sector
                    </p>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-8">
                        <a href="{{ route('register') }}"
                            class="w-full sm:w-auto px-8 py-4 rounded-lg text-white text-lg font-semibold transition hover:brightness-110 inline-block text-center"
                            style="background-color: #2196F3;">
                            Start Free Trial
                        </a>

                        {{-- <button
                            class="w-full sm:w-auto px-8 py-4 rounded-lg border-2 border-white bg-transparent text-white text-lg font-bold transition hover:bg-white/10">
                            Assess Your Risk
                        </button> --}}
                    </div>

                </div>

                {{-- RIGHT COLUMN: White Stats Cards (Compact Version) --}}
                <div class="grid gap-3 content-start">

                    {{-- 1. National Threat Level --}}
                    <div class="bg-primary rounded-xl shadow-lg p-3 border border-white">
                        <h3 class="text-gray-400 font-semibold text-sm uppercase tracking-wide">National Threat Outlook
                            (2025)
                        </h3>

                        @php
                            $level = strtolower($currentThreatLevel);

                            if (str_contains($level, 'high') || str_contains($level, 'critical')) {
                                $pillClasses = 'bg-red-100 text-red-700 border-red-200';
                            } elseif (str_contains($level, 'medium')) {
                                $pillClasses = 'bg-orange-100 text-orange-700 border-orange-200';
                            } else {
                                // Normal solid green for Low
                                $pillClasses = 'bg-green-600 text-white border-transparent';
                            }
                        @endphp

                        <div class="flex items-center mt-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border {{ $pillClasses }}">
                                {{ $currentThreatLevel }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. Total Incidents --}}
                    <div class="bg-primary rounded-xl shadow-lg p-3 border border-white">
                        <h3 class="text-gray-400 font-semibold text-sm uppercase tracking-wide">Total Incidents
                        </h3>
                        <div class="text-xl sm:text-2xl font-medium text-white mt-0.5">
                            {{ number_format($totalIncidents) }}
                        </div>
                    </div>

                    {{-- 3. High Risk States --}}
                    <div class="bg-primary rounded-xl shadow-lg p-3 border border-white">
                        <h3 class="text-gray-400 font-semibold text-sm uppercase tracking-wide">High Risk States</h3>
                        <div class="text-xl sm:text-2xl font-medium text-white mt-0.5">
                            {{ $highRiskStateCount }}
                        </div>
                    </div>

                    {{-- 4. Most Affected --}}
                    <div class="bg-primary rounded-xl shadow-lg p-3 border border-white">
                        <h3 class="text-gray-400 font-semibold text-sm uppercase tracking-wide">Most Affected</h3>
                        <div class="text-lg sm:text-xl font-medium text-white mt-0.5 truncate">
                            {{ $top3HighRiskStates }}
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    @include('partials.home.location-intelligence')
    @include('partials.home.security-index')
    @include('partials.home.app-section')

    @include('partials.home.insight-section')
    {{-- @include('partials.home.risk-tool2') --}}


</x-layout>

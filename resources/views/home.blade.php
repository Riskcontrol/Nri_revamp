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

                    <h1 class="text-4xl sm:text-5xl lg:text-5xl font-bold leading-tight tracking-tight">
                        Nigeria’s Premier<br class="hidden sm:block" /> Risk Intelligence Portal
                    </h1>

                    <p class="text-[#fff] text-xl sm:text-2xl leading-relaxed mt-4">
                        Transform complex data into actionable security <br> and business intelligence for informed
                        decision-making
                    </p>

                    <p class="text-[#FDA557] text-lg sm:text-xl mt-6 font-semibold">
                        Join 500+ security professionals accessing real-time <br>Nigeria intelligence across various
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

                {{-- RIGHT COLUMN: White Stats Cards (Compact Version) --}}
                <div class="grid gap-3 content-start">

                    {{-- 1. National Threat Level --}}
                    <div class="bg-white rounded-xl shadow-lg p-3">
                        <h3 class="text-gray-800 font-bold text-sm uppercase tracking-wide">National Threat Outlook</h3>
                        <div class="text-xl sm:text-2xl font-bold text-black lowercase mt-0.5">
                            {{ $currentThreatLevel }}
                        </div>
                    </div>

                    {{-- 2. Total Incidents --}}
                    <div class="bg-white rounded-xl shadow-lg p-3">
                        <h3 class="text-gray-800 font-bold text-sm uppercase tracking-wide">Total Incidents 2024</h3>
                        <div class="text-xl sm:text-2xl font-bold text-black mt-0.5">
                            {{ number_format($totalIncidents) }}
                        </div>
                    </div>

                    {{-- 3. High Risk States --}}
                    <div class="bg-white rounded-xl shadow-lg p-3">
                        <h3 class="text-gray-800 font-bold text-sm uppercase tracking-wide">High Risk States</h3>
                        <div class="text-xl sm:text-2xl font-bold text-black mt-0.5">
                            {{ $highRiskStateCount }}
                        </div>
                    </div>

                    {{-- 4. Most Affected --}}
                    <div class="bg-white rounded-xl shadow-lg p-3">
                        <h3 class="text-gray-800 font-bold text-sm uppercase tracking-wide">Most Affected</h3>
                        <div class="text-lg sm:text-xl font-bold text-black mt-0.5 truncate">
                            {{ $top3HighRiskStates }}
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    @include('partials.location-intelligence')
    @include('partials.terrorism-index')
    <section class="py-12 sm:py-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">

                {{-- Left Side: Phone Image --}}
                <div class="flex justify-center lg:justify-end order-last lg:order-first relative">
                    {{-- Optional decorative blob behind phone (Adjusted opacity for white bg) --}}
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl -z-10">
                    </div>

                    <img src="{{ asset('images/mobile.png') }}" alt="Risk Track app interface"
                        class="max-w-sm sm:max-w-md lg:max-w-lg w-full h-auto drop-shadow-2xl">
                </div>

                {{-- Right Side: Content --}}
                <div class="text-center lg:text-left">
                    {{-- Changed text-white to text-gray-900 --}}
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-gray-900 mb-6">
                        Safety in Your Pocket
                    </h2>

                    {{-- Changed text-gray-300 to text-gray-600 --}}
                    <p
                        class="text-lg sm:text-xl text-gray-600 font-medium leading-relaxed max-w-xl mx-auto lg:mx-0 mb-10">
                        See risks nearby and monitor live incidents in real-time.
                        Stay ahead of threats with instant alerts delivered directly to your device.
                    </p>

                    <div class="space-y-6">
                        {{-- Blue text usually works fine on white, kept as is, or change to text-blue-600 for darker contrast --}}
                        <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                            Scan code or click to download
                        </p>

                        {{-- Download Options Grid --}}
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6">

                            {{-- Option 1: Apple App Store --}}
                            {{-- Changed container style from translucent white to solid white with gray border/shadow --}}
                            <div
                                class="p-4 flex items-center gap-4 hover:shadow-md hover:border-gray-300 transition-all group">
                                {{-- QR Code container --}}
                                <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                    <img src="{{ asset('images/appstore-qr-code.png') }}" alt="Scan for iOS"
                                        class="w-20 h-20 object-contain">
                                </div>
                                {{-- Button & Text --}}
                                <div class="text-left">
                                    {{-- Changed text-gray-400 to text-gray-500 --}}
                                    <span class="block text-xs text-gray-500 mb-2">For iOS Devices</span>
                                    <a href="https://apps.apple.com/app/risktrack/id6739492527" target="_blank"
                                        class="block transition-transform hover:scale-105">
                                        <img src="images/apple.svg" alt="Download on the App Store" class="h-10 w-auto">
                                    </a>
                                </div>
                            </div>

                            {{-- Option 2: Google Play Store --}}
                            {{-- Changed container style --}}
                            <div
                                class="p-4 flex items-center gap-4 hover:shadow-md hover:border-gray-300 transition-all group">
                                {{-- QR Code container --}}
                                <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                    <img src="{{ asset('images/playstore-qr-code.png') }}" alt="Scan for Android"
                                        class="w-20 h-20 object-contain">
                                </div>
                                {{-- Button & Text --}}
                                <div class="text-left">
                                    {{-- Changed text-gray-400 to text-gray-500 --}}
                                    <span class="block text-xs text-gray-500 mb-2">For Android Devices</span>
                                    <a href="https://play.google.com/store/apps/details?id=com.risktrack.risktrack"
                                        target="_blank" class="block transition-transform hover:scale-105">
                                        <img src="images/google.svg" alt="Get it on Google Play" class="h-10 w-auto">
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include('partials.insight-section')
    @include('partials.risk-tool2')


</x-layout>

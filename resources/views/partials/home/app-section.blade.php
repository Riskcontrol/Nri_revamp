<section class="py-12 sm:py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">

            <!-- Left Side: Phone Image -->
            <div class="flex justify-center lg:justify-end order-last lg:order-first relative">

                <!-- Decorative blob -->
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                            w-80 sm:w-96 h-80 sm:h-96 bg-blue-600/10 rounded-full blur-3xl -z-10">
                </div>

                <img src="{{ asset('images/mobile3.png') }}" alt="Risk Track app interface"
                    class="max-w-sm sm:max-w-md lg:max-w-lg w-full h-auto drop-shadow-2xl">
            </div>

            <!-- Right Side: Content -->
            <div class="text-center lg:text-left">

                <h2 class="text-2xl sm:text-3xl md:text-4xl font-medium tracking-tight text-gray-900 mb-6">
                    Safety in Your Pocket
                </h2>

                <p
                    class="text-base sm:text-lg text-gray-600 font-semibold leading-relaxed
                          max-w-xl mx-auto lg:mx-0 mb-4">
                    Real-time security intelligence. Instant emergency alerts. Your community watching over you
                </p>

                <!-- Features -->
                <div class="space-y-3">

                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-emerald-500 mb-1">
                            ONE-TAP SOS
                        </h3>
                        <p class="text-sm sm:text-base text-gray-600">
                            Emergency contacts get your exact location, live tracking, and audio recording instantly.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-emerald-500 mb-1">
                            PRIVATE COMMUNITIES
                        </h3>
                        <p class="text-sm sm:text-base text-gray-600">
                            Create safety networks for family, church, workplace, or estate. See each other's locations
                            and respond together.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-emerald-500 mb-1">
                            LIVE INCIDENT MAP
                        </h3>
                        <p class="text-sm sm:text-base text-gray-600">
                            Community-verified threats in real-time. Know what's happening on your street right now.
                        </p>
                    </div>

                </div>

                <!-- Website Link CTA -->
                <div class="mt-8 mb-6">
                    <a href="https://www.risktrack.co/" target="_blank"
                        class="inline-flex items-center gap-2 text-sm sm:text-base font-semibold
                               text-blue-600 hover:text-blue-700 transition-all group">

                        <span>Explore the full RiskTrack platform</span>

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>

                <!-- Download Section -->
                <div class="space-y-6">

                    <p class="text-sm font-medium uppercase tracking-widest text-blue-600">
                        Scan code or click to download
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6">

                        <!-- Apple -->
                        <div class="p-4 flex items-center gap-4 hover:shadow-md transition-all">

                            <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                <img src="{{ asset('images/appstore-qr-code.png') }}" alt="Scan for iOS"
                                    class="w-20 h-20 object-contain">
                            </div>

                            <div class="text-left">
                                <span class="block text-xs text-gray-500 mb-2">For iOS Devices</span>
                                <a href="https://apps.apple.com/app/risktrack/id6739492527" target="_blank"
                                    class="block transition-transform hover:scale-105">
                                    <img src="images/apple.svg" alt="Download on the App Store" class="h-10 w-auto">
                                </a>
                            </div>
                        </div>

                        <!-- Google -->
                        <div class="p-4 flex items-center gap-4 hover:shadow-md transition-all">

                            <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                <img src="{{ asset('images/playstore-qr-code.png') }}" alt="Scan for Android"
                                    class="w-20 h-20 object-contain">
                            </div>

                            <div class="text-left">
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

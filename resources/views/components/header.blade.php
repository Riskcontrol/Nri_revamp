<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-[1001] border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-30 md:h-30 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: Desktop Nav + Login --}}
            <div class="hidden md:flex items-center gap-8">
                {{-- Home --}}
                <a href="{{ url('/') }}"
                    class="text-md font-medium text-gray-300 hover:text-white transition-colors">
                    Home
                </a>

                {{-- Location Intelligence (Broken) --}}
                <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Location</span>
                    <span>Intelligence</span>
                </a>

                {{-- Risk Index (Broken) --}}
                <a href="{{ route('securityIntelligence') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span>
                    <span>Index</span>
                </a>

                {{-- Risk Map (Broken) --}}
                <a href="{{ route('risk-map.show') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span>
                    <span>Map</span>
                </a>

                {{-- News & Insight (Broken) --}}
                <a href="{{ route('news') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>News &</span>
                    <span>Insight</span>
                </a>

                {{-- Login --}}
                <a href="{{ url('/login') }}"
                    class="inline-block rounded-lg bg-transparent border border-white px-8 py-3 text-md text-white font-semibold hover:bg-blue-500 transition shadow-md">
                    Login
                </a>
            </div>

            {{-- Hamburger (mobile) using Font Awesome Icon instead of SVG --}}
            <button id="menu-toggle"
                class="md:hidden text-gray-300 p-2 rounded hover:bg-white/10 focus:outline-none transition-colors"
                aria-controls="mobile-menu" aria-expanded="false" type="button">
                <i class="fa-solid fa-bars text-2xl"></i>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden pb-6 border-t border-white/10 bg-primary">
            <div class="flex flex-col gap-1 pt-4">
                <a href="{{ url('/') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Home
                </a>
                <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Location Intelligence
                </a>
                <a href="{{ route('securityIntelligence') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Risk Index
                </a>
                <a href="{{ route('risk-map.show') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Risk Map
                </a>
                <a href="{{ route('news') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    News & Insight
                </a>

                <div class="px-4 mt-4">
                    <a href="{{ url('/login') }}"
                        class="block w-full rounded-lg bg-transparent border border-white px-4 py-3 text-white font-medium text-center hover:bg-blue-500 transition shadow-md">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

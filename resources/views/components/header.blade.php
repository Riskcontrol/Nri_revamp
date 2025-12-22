<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-50 border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-6">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-20 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: Desktop Nav + Login --}}
            <div class="hidden md:flex items-center gap-8">
                {{-- Home --}}
                <a href="{{ url('/') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Home
                </a>

                {{-- Location Intelligence --}}
                <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Location Intelligence
                </a>

                {{-- Risk Index --}}
                <a href="{{ route('securityIntelligence') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Risk Index
                </a>

                {{-- Risk Map --}}
                <a href="{{ route('risk-map.show') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Risk Map
                </a>

                {{-- News & Insight --}}
                <a href="{{ route('securityIntelligence') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    News & Insight
                </a>

                {{-- Login --}}
                <a href="{{ url('/login') }}"
                    class="inline-block rounded-lg bg-blue-600 px-8 py-3 text-sm text-white font-semibold hover:bg-blue-500 transition shadow-md">
                    Login
                </a>
            </div>

            {{-- Hamburger (mobile) --}}
            <button id="menu-toggle"
                class="md:hidden text-gray-300 p-2 rounded hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                aria-controls="mobile-menu" aria-expanded="false" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
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
                <a href="{{ route('securityIntelligence') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    News & Insight
                </a>

                <div class="px-4 mt-4">
                    <a href="{{ url('/login') }}"
                        class="block w-full rounded-lg bg-blue-600 px-4 py-3 text-white font-medium text-center hover:bg-blue-500 transition shadow-md">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

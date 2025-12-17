<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-50 border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- CHANGED: Increased vertical padding from py-3 to py-6 --}}
        <div class="flex items-center justify-between py-6">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-24 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: Desktop Nav + Login --}}
            <div class="hidden md:flex items-center gap-8">
                {{-- Home --}}
                <a href="{{ url('/') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    Home
                </a>

                {{-- NEW: Risk Intelligence Dropdown --}}
                {{-- The 'group' class allows the child menu to show when hovering over this parent div --}}
                <div class="relative group">
                    <button
                        class="flex items-center gap-1 text-sm font-medium text-gray-300 hover:text-white transition-colors focus:outline-none py-2">
                        Threat Insight
                        {{-- Chevron Down Icon --}}
                        <svg class="w-4 h-4 ml-0.5 text-gray-400 group-hover:text-white transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu (Hidden by default, shown on group hover) --}}
                    {{-- Added padding top (pt-2) to create a safe hover gap --}}
                    <div class="absolute left-0 top-full pt-2 w-56 hidden group-hover:block hover:block z-50">
                        <div class="bg-[#1a2234] border border-white/10 rounded-lg shadow-xl overflow-hidden py-1">
                            <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                                class="block px-4 py-3 text-sm text-gray-300 hover:bg-blue-600 hover:text-white transition-colors">
                                Location Intelligence
                            </a>
                            <a href="{{ route('securityIntelligence') }}"
                                class="block px-4 py-3 text-sm text-gray-300 hover:bg-blue-600 hover:text-white transition-colors">
                                Risk Index
                            </a>
                            {{-- Optional: Included Risk Map here too as it fits the category --}}
                            <a href="{{ route('risk-map.show') }}"
                                class="block px-4 py-3 text-sm text-gray-300 hover:bg-blue-600 hover:text-white transition-colors">
                                Risk Map
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Other Links --}}
                <a href="#" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    News & Insight
                </a>

                {{-- Login --}}
                <a href="{{ url('/login') }}"
                    class="inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm text-white font-semibold hover:bg-blue-500 transition shadow-md">
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

                {{-- Mobile Grouping: Visually separated --}}
                <div class="px-4 py-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 block">Threat
                        Insight</span>
                    <div class="space-y-1 border-l-2 border-white/10 ml-1 pl-3">
                        <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                            class="block py-2 text-sm text-gray-300 hover:text-white transition-colors">
                            Location Intelligence
                        </a>
                        <a href="{{ route('securityIntelligence') }}"
                            class="block py-2 text-sm text-gray-300 hover:text-white transition-colors">
                            Risk Index
                        </a>
                        <a href="{{ route('risk-map.show') }}"
                            class="block py-2 text-sm text-gray-300 hover:text-white transition-colors">
                            Risk Map
                        </a>
                    </div>
                </div>

                <a href="#"
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

<script>
    (function() {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.toggle('hidden') === false;
                menuToggle.setAttribute('aria-expanded', String(isOpen));
            });
        }
    })();
</script>

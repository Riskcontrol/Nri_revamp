<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-50 border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between py-3">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo-white.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-20 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: desktop nav + login --}}
            <div class="hidden md:flex items-center gap-8">
                {{-- Primary links --}}
                <a href="{{ url('/') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Home</a>
                <a href="#" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Our
                    Story</a>
                <a href="#" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">News &
                    Insights</a>

                {{-- Use Cases dropdown (desktop) --}}
                <div class="relative group">
                    <button id="ucases-toggle-desktop"
                        class="flex items-center gap-1 text-sm font-medium text-gray-300 hover:text-white transition-colors focus:outline-none"
                        type="button" aria-haspopup="true" aria-expanded="false" aria-controls="ucases-menu-desktop">
                        Threat Insights
                        <svg class="h-4 w-4 text-gray-400 group-hover:text-white transition-colors" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="ucases-menu-desktop"
                        class="invisible opacity-0 transform scale-95 group-hover:visible group-hover:opacity-100 group-hover:scale-100 absolute right-0 mt-2 w-64 rounded-xl border border-gray-200 bg-white shadow-xl transition-all duration-200 origin-top-right z-50">
                        <div class="py-1">
                            <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                                class="block px-4 py-2.5 text-sm text-primary hover:bg-gray-50 hover:text-blue-600 font-medium">
                                Location Intelligence
                            </a>
                            <a href="{{ route('securityIntelligence') }}"
                                class="block px-4 py-2.5 text-sm text-primary hover:bg-gray-50 hover:text-blue-600 font-medium">
                                Risk Database
                            </a>
                            <a href="#"
                                class="block px-4 py-2.5 text-sm text-primary hover:bg-gray-50 hover:text-blue-600 font-medium">
                                Business Risk Insight
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Login --}}
                <a href="{{ url('/login') }}"
                    class="inline-block rounded-lg bg-blue-600 px-5 py-2 text-sm text-white font-semibold hover:bg-blue-500 transition shadow-md">
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
        <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-white/10 bg-primary">
            <div class="flex flex-col gap-1 pt-4">
                <a href="{{ url('/') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Home</a>
                <a href="#"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">About</a>
                <a href="#"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Insights</a>

                {{-- Use Cases accordion (mobile) --}}
                <div class="px-2">
                    <button id="ucases-toggle-mobile"
                        class="w-full flex items-center justify-between px-2 py-3 text-left text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                        type="button" aria-controls="ucases-panel-mobile" aria-expanded="false">
                        <span>Use Cases</span>
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="ucases-panel-mobile" class="hidden pl-4 mt-1 space-y-1 border-l border-white/10 ml-4">
                        <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                            class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded transition-colors">Location
                            Intelligence</a>
                        <a href="{{ route('securityIntelligence') }}"
                            class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded transition-colors">Risk
                            Database</a>
                        <a href="#"
                            class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded transition-colors">Business
                            Risk Insight</a>
                    </div>
                </div>

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

{{-- Minimal toggle script (no dependencies) --}}
<script>
    (function() {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        // Mobile specific toggles
        const uMobileBtn = document.getElementById('ucases-toggle-mobile');
        const uMobilePanel = document.getElementById('ucases-panel-mobile');

        // Note: Desktop dropdown is handled via CSS hover state (group-hover) for smoother UX,
        // but JS logic can remain if click-to-toggle is preferred.
        // Below handles Mobile Main Menu
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.toggle('hidden') === false;
                menuToggle.setAttribute('aria-expanded', String(isOpen));
            });
        }

        // Mobile Use Cases accordion
        if (uMobileBtn && uMobilePanel) {
            uMobileBtn.addEventListener('click', () => {
                const isHidden = uMobilePanel.classList.toggle('hidden');
                uMobileBtn.setAttribute('aria-expanded', String(!isHidden));
                // Rotate icon if needed (optional)
                const svg = uMobileBtn.querySelector('svg');
                if (svg) {
                    svg.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
                    svg.style.transition = 'transform 0.2s';
                }
            });
        }
    })();
</script>

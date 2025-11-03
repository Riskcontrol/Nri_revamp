<header class="bg-white/95 backdrop-blur shadow-sm sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between py-3">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-16 w-full object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: desktop nav + login --}}
            <div class="hidden md:flex items-center gap-6">
                {{-- Primary links --}}
                <a href="{{ url('/') }}" class="text-gray-800 hover:text-blue-600">Home</a>
                <a href="{{ url('/about') }}" class="text-gray-800 hover:text-blue-600">About</a>
                <a href="{{ url('/insights') }}" class="text-gray-800 hover:text-blue-600">Insights</a>

                {{-- Use Cases dropdown (desktop) --}}
                <div class="relative">
                    <button id="ucases-toggle-desktop" class="flex items-center gap-1 text-gray-800 hover:text-blue-600"
                        type="button" aria-haspopup="true" aria-expanded="false" aria-controls="ucases-menu-desktop">
                        Use Cases
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="ucases-menu-desktop"
                        class="invisible opacity-0 pointer-events-none absolute right-0 mt-2 w-64 rounded-xl border border-gray-200 bg-white shadow-lg transition duration-150"
                        role="menu" aria-labelledby="ucases-toggle-desktop">
                        <a href="{{ url('/use-cases/location-intelligence') }}"
                            class="block px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50" role="menuitem">
                            Location Intelligence
                        </a>
                        <a href="{{ url('/use-cases/risk-database') }}"
                            class="block px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50" role="menuitem">
                            Risk Database
                        </a>
                        <a href="{{ url('/use-cases/business-risk-insight') }}"
                            class="block px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50" role="menuitem">
                            Business Risk Insight
                        </a>
                    </div>
                </div>

                {{-- Login --}}
                <a href="{{ url('/login') }}"
                    class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-700 transition">
                    Login
                </a>
            </div>

            {{-- Hamburger (mobile) --}}
            <button id="menu-toggle"
                class="md:hidden text-gray-800 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-controls="mobile-menu" aria-expanded="false" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-gray-100">
            <div class="flex flex-col gap-1 pt-4">
                <a href="{{ url('/') }}" class="px-2 py-2.5 rounded text-gray-800 hover:bg-gray-50">Home</a>
                <a href="{{ url('/about') }}" class="px-2 py-2.5 rounded text-gray-800 hover:bg-gray-50">About</a>
                <a href="{{ url('/insights') }}"
                    class="px-2 py-2.5 rounded text-gray-800 hover:bg-gray-50">Insights</a>

                {{-- Use Cases accordion (mobile) --}}
                <div class="px-2">
                    <button id="ucases-toggle-mobile"
                        class="w-full flex items-center justify-between py-2.5 text-left text-gray-800 hover:text-blue-600"
                        type="button" aria-controls="ucases-panel-mobile" aria-expanded="false">
                        <span>Use Cases</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="ucases-panel-mobile" class="hidden pl-3">
                        <a href="{{ url('/use-cases/location-intelligence') }}"
                            class="block py-2 text-gray-800 hover:bg-gray-50 rounded">Location Intelligence</a>
                        <a href="{{ url('/use-cases/risk-database') }}"
                            class="block py-2 text-gray-800 hover:bg-gray-50 rounded">Risk Database</a>
                        <a href="{{ url('/use-cases/business-risk-insight') }}"
                            class="block py-2 text-gray-800 hover:bg-gray-50 rounded">Business Risk Insight</a>
                    </div>
                </div>

                <a href="{{ url('/login') }}"
                    class="mt-2 mx-2 inline-block rounded-lg bg-blue-600 px-4 py-2 text-white font-medium text-center hover:bg-blue-700 transition">
                    Login
                </a>
            </div>
        </div>
    </nav>
</header>

{{-- Minimal toggle script (no dependencies) --}}
<script>
    (function() {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        const uDesktopBtn = document.getElementById('ucases-toggle-desktop');
        const uDesktopMenu = document.getElementById('ucases-menu-desktop');

        const uMobileBtn = document.getElementById('ucases-toggle-mobile');
        const uMobilePanel = document.getElementById('ucases-panel-mobile');

        // Mobile main menu toggle
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.toggle('hidden') === false;
                menuToggle.setAttribute('aria-expanded', String(isOpen));
            });
        }

        // Desktop dropdown toggle (click)
        function closeDesktopMenu() {
            uDesktopMenu.classList.add('invisible', 'opacity-0', 'pointer-events-none');
            uDesktopBtn.setAttribute('aria-expanded', 'false');
        }

        function openDesktopMenu() {
            uDesktopMenu.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
            uDesktopBtn.setAttribute('aria-expanded', 'true');
        }

        if (uDesktopBtn && uDesktopMenu) {
            uDesktopBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const expanded = uDesktopBtn.getAttribute('aria-expanded') === 'true';
                expanded ? closeDesktopMenu() : openDesktopMenu();
            });

            // Click outside to close
            document.addEventListener('click', (e) => {
                if (!uDesktopMenu.contains(e.target) && !uDesktopBtn.contains(e.target)) {
                    closeDesktopMenu();
                }
            });

            // ESC to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeDesktopMenu();
            });
        }

        // Mobile Use Cases accordion
        if (uMobileBtn && uMobilePanel) {
            uMobileBtn.addEventListener('click', () => {
                const isHidden = uMobilePanel.classList.toggle('hidden');
                uMobileBtn.setAttribute('aria-expanded', String(!isHidden));
            });
        }
    })();
</script>

<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-[1001] border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-3">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-20 md:h-24 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: Desktop Nav + Auth --}}
            <div class="hidden md:flex items-center gap-6 lg:gap-8">
                <a href="{{ url('/') }}"
                    class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Home</a>

                {{-- Location Intelligence Dropdown — desktop CSS hover --}}
                <div class="relative group h-full flex items-center">
                    <a href="#" onclick="return false;"
                        class="flex items-center gap-1.5 text-sm font-medium text-gray-300 hover:text-white transition-colors cursor-default select-none">
                        <div class="flex flex-col text-right leading-tight">
                            <span>Location</span>
                            <span>Intelligence</span>
                        </div>
                        <i
                            class="fa-solid fa-chevron-down text-xs transition-transform duration-200 group-hover:rotate-180"></i>
                    </a>

                    {{-- Dropdown panel --}}
                    <div
                        class="absolute left-1/2 -translate-x-1/2 top-full pt-3 w-56
                                opacity-0 invisible group-hover:opacity-100 group-hover:visible
                                transition-all duration-200 ease-out translate-y-1 group-hover:translate-y-0 z-50">
                        <div class="bg-[#1E2D3D] border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
                            <div
                                class="px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest
                                        border-b border-gray-700/60 bg-[#16222E]">
                                Select a State
                            </div>
                            <div class="max-h-72 overflow-y-auto"
                                style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.15) transparent;">
                                @if (isset($headerStates) && count($headerStates) > 0)
                                    @foreach ($headerStates as $state)
                                        <a href="{{ route('locationIntelligence', ['state' => $state]) }}"
                                            class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-[#253646] hover:text-white
                                                   transition-colors border-b border-gray-700/30 last:border-0">
                                            {{ $state }}
                                        </a>
                                    @endforeach
                                @else
                                    <a href="{{ route('locationIntelligence', ['state' => 'Lagos']) }}"
                                        class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-[#253646] hover:text-white transition-colors border-b border-gray-700/30">Lagos</a>
                                    <a href="{{ route('locationIntelligence', ['state' => 'Abuja']) }}"
                                        class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-[#253646] hover:text-white transition-colors">Abuja
                                        (FCT)</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('securityIntelligence') }}"
                    class="flex flex-col text-sm font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span><span>Index</span>
                </a>

                <a href="{{ route('risk-map.show') }}"
                    class="flex flex-col text-sm font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span><span>Map</span>
                </a>

                <a href="{{ route('news') }}"
                    class="flex flex-col text-sm font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Public</span><span>Safety</span>
                </a>

                {{-- Auth — Desktop --}}
                @guest
                    <a href="{{ url('/login') }}"
                        class="inline-block rounded-lg bg-transparent border border-white px-6 py-2.5 text-sm text-white
                               font-semibold hover:bg-blue-500 hover:border-blue-500 transition shadow-md whitespace-nowrap">
                        Login
                    </a>
                @else
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-block rounded-lg bg-red-600/10 border border-red-500/50 px-6 py-2.5 text-sm
                                   text-red-400 font-semibold hover:bg-red-600 hover:text-white transition shadow-md whitespace-nowrap">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>

            {{-- Hamburger (mobile) --}}
            <button id="menu-toggle"
                class="md:hidden text-gray-300 p-2 rounded-lg hover:bg-white/10 focus:outline-none
                       focus:ring-2 focus:ring-white/20 transition-colors"
                aria-controls="mobile-menu" aria-expanded="false" type="button" aria-label="Toggle navigation menu">
                <i id="menu-icon" class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        {{-- ── MOBILE MENU ──────────────────────────────────────────────────────── --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-white/10">
            <div class="flex flex-col py-3 gap-0.5">

                <a href="{{ url('/') }}"
                    class="block px-4 py-3 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Home
                </a>

                {{-- Location Intelligence — mobile accordion --}}
                <div>
                    <button id="mob-loc-toggle" type="button" aria-expanded="false" aria-controls="mob-loc-panel"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-sm text-gray-300
                               hover:text-white hover:bg-white/10 transition-colors text-left">
                        <span>Location Intelligence</span>
                        <i id="mob-loc-icon"
                            class="fa-solid fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>

                    <div id="mob-loc-panel" class="hidden">
                        <div class="ml-4 border-l border-white/10 pl-3 pb-1 max-h-56 overflow-y-auto"
                            style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.15) transparent;">
                            @if (isset($headerStates) && count($headerStates) > 0)
                                @foreach ($headerStates as $state)
                                    <a href="{{ route('locationIntelligence', ['state' => $state]) }}"
                                        class="block px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/10
                                               rounded-lg transition-colors">
                                        {{ $state }}
                                    </a>
                                @endforeach
                            @else
                                <a href="{{ route('locationIntelligence', ['state' => 'Lagos']) }}"
                                    class="block px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">Lagos</a>
                                <a href="{{ route('locationIntelligence', ['state' => 'Abuja']) }}"
                                    class="block px-3 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">Abuja
                                    (FCT)</a>
                            @endif
                        </div>
                    </div>
                </div>

                <a href="{{ route('securityIntelligence') }}"
                    class="block px-4 py-3 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Risk Index
                </a>

                <a href="{{ route('risk-map.show') }}"
                    class="block px-4 py-3 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Risk Map
                </a>

                <a href="{{ route('news') }}"
                    class="block px-4 py-3 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Public Safety
                </a>

                {{-- Auth — Mobile --}}
                <div class="px-4 pt-3 pb-2 border-t border-white/10 mt-1">
                    @guest
                        <a href="{{ url('/login') }}"
                            class="block w-full rounded-lg bg-transparent border border-white px-4 py-3 text-sm text-white
                                   font-semibold text-center hover:bg-blue-500 hover:border-blue-500 transition shadow-md">
                            Login
                        </a>
                    @else
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="block w-full rounded-lg bg-red-600 border border-red-600 px-4 py-3 text-sm
                                       text-white font-semibold text-center shadow-md hover:bg-red-700 transition">
                                Logout
                            </button>
                        </form>
                    @endguest
                </div>

            </div>
        </div>
    </nav>

    <script>
        (function() {
            // ── Hamburger toggle ──────────────────────────────────────────────────
            const toggle = document.getElementById('menu-toggle');
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');

            toggle.addEventListener('click', function() {
                const isOpen = !menu.classList.contains('hidden');
                menu.classList.toggle('hidden', isOpen);
                toggle.setAttribute('aria-expanded', String(!isOpen));
                icon.className = isOpen ? 'fa-solid fa-bars text-xl' : 'fa-solid fa-xmark text-xl';
            });

            // ── Location Intelligence mobile accordion ────────────────────────────
            const locBtn = document.getElementById('mob-loc-toggle');
            const locPanel = document.getElementById('mob-loc-panel');
            const locIcon = document.getElementById('mob-loc-icon');

            locBtn.addEventListener('click', function() {
                const isOpen = !locPanel.classList.contains('hidden');
                locPanel.classList.toggle('hidden', isOpen);
                locBtn.setAttribute('aria-expanded', String(!isOpen));
                locIcon.style.transform = isOpen ? '' : 'rotate(180deg)';
            });

            // Close mobile menu on resize back to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    menu.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    icon.className = 'fa-solid fa-bars text-xl';
                    locPanel.classList.add('hidden');
                    locBtn.setAttribute('aria-expanded', 'false');
                    locIcon.style.transform = '';
                }
            });
        })();
    </script>
</header>

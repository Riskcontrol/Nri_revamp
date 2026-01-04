<header class="bg-primary backdrop-blur shadow-lg sticky top-0 z-[1001] border-b border-white/10">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                    class="h-28 md:h-28 w-auto object-contain">
                <span class="sr-only">Nigeria Risk Index</span>
            </a>

            {{-- Right cluster: Desktop Nav + Auth --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ url('/') }}"
                    class="text-md font-medium text-gray-300 hover:text-white transition-colors">Home</a>

                {{-- Location Intelligence Dropdown --}}
                <div class="relative group h-full flex items-center">
                    {{-- Trigger --}}
                    <a href="#" onclick="return false;"
                        class="flex items-center gap-2 text-md font-medium text-gray-300 hover:text-white transition-colors cursor-default">

                        {{-- Preserved Broken Text Format --}}
                        <div class="flex flex-col text-right leading-tight">
                            <span>Location</span>
                            <span>Intelligence</span>
                        </div>

                        {{-- FontAwesome Icon --}}
                        <i
                            class="fa-solid fa-chevron-down text-xs transition-transform duration-200 group-hover:rotate-180"></i>
                    </a>

                    {{-- Dropdown Menu --}}
                    <div class="absolute left-0 top-full pt-4 w-56 hidden group-hover:block z-50">
                        <div class="bg-[#1E2D3D] border border-gray-700 rounded-xl shadow-xl overflow-hidden py-1">
                            <div
                                class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-700/50 bg-[#16222E]">
                                Select a State
                            </div>

                            <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                                @if (isset($headerStates) && count($headerStates) > 0)
                                    @foreach ($headerStates as $state)
                                        <a href="{{ route('locationIntelligence', ['state' => $state]) }}"
                                            class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-[#253646] hover:text-white transition-colors border-b border-gray-700/50 last:border-0">
                                            {{ $state }}
                                        </a>
                                    @endforeach
                                @else
                                    {{-- Fallback if provider isn't set up yet --}}
                                    <a href="{{ route('locationIntelligence', ['state' => 'Lagos']) }}"
                                        class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/10">Lagos</a>
                                    <a href="{{ route('locationIntelligence', ['state' => 'Abuja']) }}"
                                        class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/10">Abuja (FCT)</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('securityIntelligence') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span>
                    <span>Index</span>
                </a>

                <a href="{{ route('risk-map.show') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Risk</span>
                    <span>Map</span>
                </a>

                <a href="{{ route('news') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>News &</span>
                    <span>Insight</span>
                </a>

                {{-- Auth Logic for Desktop --}}
                @guest
                    <a href="{{ url('/login') }}"
                        class="inline-block rounded-lg bg-transparent border border-white px-8 py-3 text-md text-white font-semibold hover:bg-blue-500 transition shadow-md">
                        Login
                    </a>
                @else
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-block rounded-lg bg-red-600/10 border border-red-500/50 px-8 py-3 text-md text-red-500 font-semibold hover:bg-red-600 hover:text-white transition shadow-md">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>

            {{-- Hamburger (mobile) --}}
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
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Home</a>

                {{-- Mobile: Requires selection logic (simplified here to prevent default error) --}}
                <a href="#"
                    onclick="alert('Please use the desktop version to select a specific state.'); return false;"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                    Location Intelligence
                </a>

                <a href="{{ route('securityIntelligence') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Risk
                    Index</a>
                <a href="{{ route('risk-map.show') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Risk
                    Map</a>
                <a href="{{ route('news') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">News
                    & Insight</a>


                {{-- Auth Logic for Mobile --}}
                <div class="px-4 mt-4">
                    @guest
                        <a href="{{ url('/login') }}"
                            class="block w-full rounded-lg bg-transparent border border-white px-4 py-3 text-white font-medium text-center hover:bg-blue-500 transition shadow-md">
                            Login
                        </a>
                    @else
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="block w-full rounded-lg bg-red-600 border border-red-600 px-4 py-3 text-white font-medium text-center shadow-md">
                                Logout
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            menu.classList.toggle('hidden');
            this.setAttribute('aria-expanded', !isExpanded);
        });
    </script>
</header>

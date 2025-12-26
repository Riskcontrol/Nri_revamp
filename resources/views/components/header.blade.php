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

                <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
                    <span>Location</span>
                    <span>Intelligence</span>
                </a>

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
                    <span>News</span>
                </a>

                <a href="{{ route('insights.index') }}"
                    class="flex flex-col text-md font-medium text-gray-300 hover:text-white transition-colors leading-tight">
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
                <a href="{{ route('locationIntelligence', ['state' => 'lagos']) }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Location
                    Intelligence</a>
                <a href="{{ route('securityIntelligence') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Risk
                    Index</a>
                <a href="{{ route('risk-map.show') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Risk
                    Map</a>
                <a href="{{ route('news') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">News</a>
                <a href="{{ route('insights.index') }}"
                    class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">Insight</a>

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

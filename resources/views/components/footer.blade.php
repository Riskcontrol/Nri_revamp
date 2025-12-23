<footer class="bg-[#0a1628] text-white border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 md:px-8 py-16">
        {{-- Main Grid --}}
        <div class="grid gap-12 lg:grid-cols-4">

            {{-- Brand Column --}}
            <div class="space-y-8">
                <a href="{{ url('/') }}" class="flex items-center group">
                    <img src="{{ asset('images/nri-logo-white.png') }}" alt="Nigeria Risk Index Logo"
                        class="h-16 w-auto object-contain transition-transform group-hover:scale-105" />
                </a>

                <p class="text-base leading-relaxed text-gray-300 max-w-xs">
                    Comprehensive business risk intelligence for operations in Nigeria. We provide data-driven
                    insights to help businesses protect their assets and personnel.
                </p>

                {{-- Social Links - Rounded Square style from image --}}
                <div class="flex gap-3">
                    @php
                        $socials = [
                            ['icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
                            ['icon' => 'fa-brands fa-x-twitter', 'label' => 'X'],
                            ['icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
                            ['icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
                        ];
                    @endphp
                    @foreach ($socials as $social)
                        <a href="#"
                            class="h-10 w-10 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-gray-300 hover:bg-blue-600 hover:text-white transition-all">
                            <i class="{{ $social['icon'] }} text-sm"></i>
                            <span class="sr-only">{{ $social['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Use Cases --}}
            <div>
                <h3 class="text-sm font-bold tracking-[0.15em] text-white uppercase mb-8">Use Cases</h3>
                <ul class="space-y-4 text-base">
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Location
                            Intelligence</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Risk Database</a>
                    </li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Business Insight</a>
                    </li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Custom Reports</a>
                    </li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="text-sm font-bold tracking-[0.15em] text-white uppercase mb-8">Company</h3>
                <ul class="space-y-4 text-base">
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">About Us</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Insights</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Methodology</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Contact</a></li>
                </ul>
            </div>

            {{-- Newsletter & Contact --}}
            <div class="space-y-8">
                <div>
                    <h3 class="text-sm font-bold tracking-[0.15em] text-white uppercase mb-6">Stay Updated</h3>
                    <p class="text-gray-400 mb-6">Get the latest risk alerts in your inbox.</p>

                    <form action="#" method="POST" class="relative group">
                        @csrf
                        <div
                            class="flex items-center overflow-hidden rounded-lg border border-white/10 bg-white/5 focus-within:border-blue-500 transition-all">
                            <input type="email" placeholder="Enter your email" required
                                class="w-full bg-transparent px-4 py-3 text-sm text-white outline-none placeholder:text-gray-500" />
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-500 px-6 py-3 text-sm font-bold text-white transition-colors">
                                Subscribe
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Contact Info with FontAwesome --}}
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 text-gray-400 group">
                        <i class="fa-solid fa-envelope text-gray-500 group-hover:text-blue-500 transition-colors"></i>
                        <a href="mailto:info@riskcontrolnigeria.com"
                            class="hover:text-white transition-colors">info@riskcontrolnigeria.com</a>
                    </li>
                    <li class="flex items-center gap-3 text-gray-400">
                        <i class="fa-solid fa-location-dot text-gray-500"></i>
                        <span>Lagos, Nigeria</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div
            class="mt-20 pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-6 text-sm text-gray-500">
            <p>&copy; {{ now()->year }} Nigeria Risk Index. All rights reserved.</p>
            <nav class="flex gap-8">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-white transition-colors">Cookie Settings</a>
            </nav>
        </div>
    </div>
</footer>

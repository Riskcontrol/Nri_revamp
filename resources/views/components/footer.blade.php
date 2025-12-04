<footer class="bg-card text-white border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 md:px-8 py-12 lg:py-16">
        <div class="grid gap-10 lg:gap-8 md:grid-cols-2 lg:grid-cols-5">

            <div class="lg:col-span-2 space-y-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/nri-logo-white.png') }}" alt="Nigeria Risk Index Logo"
                        class="h-12 md:h-12 w-auto object-contain" />
                    {{-- <span class="text-xl font-bold tracking-tight text-white">Nigeria Risk Index</span> --}}
                </a>
                <p class="max-w-sm text-sm leading-relaxed text-gray-400">
                    Comprehensive business risk intelligence for operations in Nigeria. We provide data-driven insights
                    to help businesses protect their assets and personnel.
                </p>

                <div class="flex gap-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <span class="sr-only">LinkedIn</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <span class="sr-only">Twitter</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold tracking-wider text-white uppercase">Use Cases</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ url('/use-cases/location-intelligence') }}">Location Intelligence</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ url('/use-cases/risk-database') }}">Risk Database</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ url('/use-cases/business-risk-insight') }}">Business Insight</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Custom Reports</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold tracking-wider text-white uppercase">Company</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="{{ url('/about') }}">About
                            Us</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ url('/insights') }}">Insights</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Methodology</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Contact</a></li>
                </ul>
            </div>

            <div class="md:col-span-2 lg:col-span-1">
                <h3 class="text-sm font-semibold tracking-wider text-white uppercase">Stay Updated</h3>
                <p class="mt-2 text-sm text-gray-400">Get the latest risk alerts in your inbox.</p>

                <form class="mt-4" action="#" method="POST" onsubmit="event.preventDefault()">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <label for="newsletter-email" class="sr-only">Email address</label>
                        <input id="newsletter-email" name="email" type="email" placeholder="Enter your email"
                            class="w-full rounded-lg border border-white/10 bg-primary/50 px-4 py-2.5 text-sm text-white placeholder:text-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                            required />
                        <button type="submit"
                            class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                            Subscribe
                        </button>
                    </div>
                </form>

                <ul class="mt-6 space-y-3 text-sm text-gray-400">
                    <li class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-gray-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:info@nigeriariskindex.com"
                            class="hover:text-white transition-colors">info@nigeriariskindex.com</a>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-gray-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Victoria Island, Lagos, Nigeria</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="mt-12 border-white/10" />

        <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <p>&copy; {{ now()->year }} Nigeria Risk Index. All rights reserved.</p>
            <nav class="flex gap-6">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-white transition-colors">Cookie Settings</a>
            </nav>
        </div>
    </div>
</footer>

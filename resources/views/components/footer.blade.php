<footer class="bg-neutral-900/95 text-white">
    <div class="max-w-7xl mx-auto px-6 md:px-8 py-12">
        <div class="grid gap-10 md:grid-cols-4 lg:grid-cols-5">
            <!-- Brand / Summary -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index Logo"
                        class="h-12 md:h-14 w-auto object-contain" />
                    <span class="sr-only">Nigeria Risk Index</span>
                </div>
                <p class="mt-4 max-w-xs text-white/70">
                    Comprehensive business risk intelligence for operations in Nigeria. Helping businesses protect their
                    assets and personnel since 2020.
                </p>
            </div>

            <!-- Platform -->
            <div>
                <h3 class="text-sm font-semibold tracking-wide text-white">PLATFORM</h3>
                <ul class="mt-4 space-y-3">
                    <li><a class="text-white/80 hover:text-white" href="#">Enterprise Security</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Government Solutions</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">API Integration</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Custom Reports</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Risk Consulting</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="text-sm font-semibold tracking-wide text-white">COMPANY</h3>
                <ul class="mt-4 space-y-3">
                    <li><a class="text-white/80 hover:text-white" href="#">About</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Careers</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Blog</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Press</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Partners</a></li>
                    <li><a class="text-white/80 hover:text-white" href="#">Support</a></li>
                </ul>
            </div>

            <!-- Subscribe + Contact in same column -->
            <div>
                <h3 class="text-sm font-semibold tracking-wide text-white">Stay updated</h3>
                <p class="mt-2 text-sm text-white/70">Get intelligence insights in your inbox.</p>

                <form class="mt-4 flex items-center gap-3" action="#" method="POST"
                    onsubmit="event.preventDefault()">
                    @csrf
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" name="email" type="email" placeholder="Enter your email"
                        class="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-white/50 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-400/20"
                        required />
                    <button type="submit"
                        class="rounded-xl bg-blue-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-400/20 active:bg-blue-700">
                        Subscribe
                    </button>
                </form>

                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-white/80">
                        <svg class="h-5 w-5 text-white/50" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 5l9 6 9-6" stroke="currentColor" stroke-width="1.5" />
                            <path d="M3 19h18V5" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        <a href="mailto:info@nigeriariskindex.com"
                            class="hover:text-white">info@nigeriariskindex.com</a>
                    </li>
                    <li class="flex items-center gap-3 text-white/80">
                        <svg class="h-5 w-5 text-white/50" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2 4h5l2 5-3 1a11 11 0 006 6l1-3 5 2v5c0 1-1 2-2 2A16 16 0 012 6c0-1 1-2 2-2z"
                                stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        <span>+234 700 000 0000</span>
                    </li>
                    <li class="flex items-center gap-3 text-white/80">
                        <svg class="h-5 w-5 text-white/50" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 21s7-4.35 7-10A7 7 0 105 11c0 5.65 7 10 7 10z" stroke="currentColor"
                                stroke-width="1.5" />
                            <circle cx="12" cy="11" r="2.5" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        <span>Victoria Island</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="mt-10 border-white/10" />

        <div
            class="mt-6 flex flex-col items-start justify-between gap-4 text-sm text-white/70 md:flex-row md:items-center">
            <p>© {{ now()->year }} Nigeria Risk Index</p>
            <nav class="flex items-center gap-6">
                <a href="#" class="hover:text-white">Privacy</a>
                <a href="#" class="hover:text-white">Terms</a>
                <a href="#" class="hover:text-white">Cookies</a>
            </nav>
        </div>
    </div>
</footer>

<section class="relative w-full bg-primary" aria-label="Expert Analysis">
    {{-- ADDED: max-w-7xl to align with Header and Hero --}}
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">

        <div class="max-w-3xl">
            <h2 class="text-2xl sm:text-2xl md:text-3xl font-semibold tracking-tight text-white">
                Stay Informed with Expert Analysis
            </h2>
            <p class="mt-4 md:mt-5 text-sm sm:text-base leading-relaxed text-400-600">
                Access the latest expert insights and in-depth analysis covering Nigeria's evolving security
                landscape. Our research team provides comprehensive coverage of emerging threats.
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">

            <article
                class="relative flex flex-col justify-between p-6 rounded-2xl bg-card shadow-lg hover:shadow-xl hover:bg-[#253646] transition-all duration-300 min-h-[280px] border border-white/5 group">

                <div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider mb-3 block">Insights</span>
                    <h3
                        class="text-gray-400 font-medium text-xl leading-snug group-hover:text-blue-400 transition-colors">
                        Northern Nigeria's Peace Deals
                    </h3>
                    <p class="mt-3 text-white text-sm leading-relaxed line-clamp-3">
                        Analysis of bandit negotiations and their implications for long-term security stability across
                        affected regions.
                    </p>
                </div>

                <div class="mt-6">
                    {{-- The 'after:absolute' classes make this link cover the whole <article> --}}
                    <a href="{{ url('/news-insight') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white group-hover:bg-[#1976D2] group-hover:scale-110 transition-all shadow-md after:absolute after:inset-0 after:content-['']">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </article>

            <article
                class="flex flex-col justify-between p-6 rounded-2xl bg-card shadow-lg hover:shadow-xl hover:bg-[#253646] transition-all duration-300 min-h-[280px] border border-white/5 group">
                <div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider mb-3 block">Insights</span>
                    <h3
                        class="text-gray-400 font-medium text-xl leading-snug group-hover:text-blue-400 transition-colors">
                        Tax Policy Changes
                    </h3>
                    <p class="mt-3 text-white text-sm leading-relaxed line-clamp-3">
                        Impact assessment of Nigeria's mandatory TIN policy on business operations and compliance
                        requirements.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ url('/news-insight') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white group-hover:bg-[#1976D2] group-hover:scale-110 transition-all shadow-md after:absolute after:inset-0 after:content-['']">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </article>

            <article
                class="flex flex-col justify-between p-6 rounded-2xl bg-card shadow-lg hover:shadow-xl hover:bg-[#253646] transition-all duration-300 min-h-[280px] border border-white/5 group">
                <div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider mb-3 block">Insights</span>
                    <h3
                        class="text-gray-400 font-medium text-xl leading-snug group-hover:text-blue-400 transition-colors">
                        Land Administration Issues
                    </h3>
                    <p class="mt-3 text-white text-sm leading-relaxed line-clamp-3">
                        Federal-state controversies affecting property rights, business investments, and development
                        projects.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ url('/news-insight') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white group-hover:bg-[#1976D2] group-hover:scale-110 transition-all shadow-md after:absolute after:inset-0 after:content-['']">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </article>

            <article
                class="flex flex-col justify-between p-6 rounded-2xl bg-card shadow-lg hover:shadow-xl hover:bg-[#253646] transition-all duration-300 min-h-[280px] border border-white/5 group">
                <div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider mb-3 block">Podcasts</span>
                    <h3
                        class="text-gray-400 font-medium text-xl leading-snug group-hover:text-emerald-400 transition-colors">
                        Cross-Border Security Trends
                    </h3>
                    <p class="mt-3 text-white text-sm leading-relaxed line-clamp-3">
                        Monitoring regional spillovers, migration routes, and supply-chain disruptions shaping
                        operational risk.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ url('/news-insight') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white group-hover:bg-[#1976D2] group-hover:scale-110 transition-all shadow-md after:absolute after:inset-0 after:content-['']">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </article>

        </div>

        <div class="mt-12 flex justify-center">
            <a href="#"
                class="inline-flex items-center justify-center rounded-full bg-[#2196F3] px-8 py-3 text-white font-semibold shadow-lg hover:bg-[#1976D2] hover:-translate-y-0.5 transition-all duration-300">
                View More Insights
            </a>
        </div>
    </div>
</section>

<section class="relative w-full bg-primary" aria-label="Expert Analysis">
    {{-- <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14"> --}}

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">

        {{-- Centered Heading Section --}}
        <div class="max-w-3xl mx-auto text-center mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl font-semibold tracking-tight text-white">
                Stay Informed with Expert Analysis
            </h2>
            <p class="mt-4 text-base md:text-lg leading-relaxed text-gray-400 max-w-2xl mx-auto">
                Access the latest expert insights and in-depth analysis covering Nigeria's evolving security
                landscape. Our research team provides comprehensive coverage of emerging threats.
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">

            {{-- Reusable Article Component Logic --}}
            @php
                $insights = [
                    [
                        'title' => "Northern Nigeria's Peace Deals",
                        'tag' => 'Insights',
                        'desc' =>
                            'Analysis of bandit negotiations and their implications for long-term security stability across affected regions.',
                    ],
                    [
                        'title' => 'Tax Policy Changes',
                        'tag' => 'Insights',
                        'desc' =>
                            "Impact assessment of Nigeria's mandatory TIN policy on business operations and compliance requirements.",
                    ],
                    [
                        'title' => 'Land Administration Issues',
                        'tag' => 'Insights',
                        'desc' =>
                            'Federal-state controversies affecting property rights, business investments, and development projects.',
                    ],
                    [
                        'title' => 'Cross-Border Security Trends',
                        'tag' => 'Podcasts',
                        'desc' =>
                            'Monitoring regional spillovers, migration routes, and supply-chain disruptions shaping operational risk.',
                    ],
                ];
            @endphp

            @foreach ($insights as $item)
                <article
                    class="relative flex flex-col justify-between p-6 rounded-2xl bg-card shadow-lg border border-white/5 overflow-hidden transition-all duration-300 transform-gpu hover:shadow-2xl hover:bg-[#253646] group min-h-[300px]">

                    <div class="relative z-10 pointer-events-none">
                        <span
                            class="text-xs font-bold text-white uppercase tracking-wider mb-3 block opacity-80">{{ $item['tag'] }}</span>
                        <h3
                            class="text-gray-200 font-medium text-xl leading-snug group-hover:text-blue-400 transition-colors duration-300">
                            {{ $item['title'] }}
                        </h3>
                        <p
                            class="mt-3 text-gray-400 text-sm leading-relaxed line-clamp-3 group-hover:text-white transition-colors duration-300">
                            {{ $item['desc'] }}
                        </p>
                    </div>

                    <div class="mt-6 relative z-10">
                        {{-- The 'after:inset-0' stretches the click area to the whole card WITHOUT causing bounce --}}
                        <a href="{{ url('/news-insight') }}"
                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white transition-all duration-300 shadow-md group-hover:bg-[#1976D2] group-hover:scale-110 after:absolute after:inset-0 after:content-['']">

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach

        </div>

        <div class="mt-12 flex justify-center">
            <a href="#"
                class="inline-flex items-center gap-3 border-2 border-gray-500 bg-transparent text-gray-500 font-semibold text-base px-8 py-3.5 rounded-xl transition-all duration-300 hover:bg-white hover:text-gray-600 hover:shadow-xl hover:-translate-y-1 active:translate-y-0 active:scale-95">
                View More Insights
            </a>
        </div>
    </div>
</section>

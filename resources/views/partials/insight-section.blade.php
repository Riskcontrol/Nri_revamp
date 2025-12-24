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

            @foreach ($homeInsights as $insight)
                <article
                    class="relative flex flex-col p-6 rounded-2xl bg-card shadow-lg border border-white/5 overflow-hidden transition-all duration-300 transform-gpu hover:shadow-2xl hover:bg-[#253646] group min-h-[220px]">
                    {{-- Reduced min-h from 300px to 220px --}}

                    <div class="relative z-10 pointer-events-none">
                        <span class="text-xs font-bold text-white uppercase tracking-wider mb-2 block opacity-80">
                            {{ $insight->category->name ?? 'Uncategorized' }}
                        </span>

                        <h3
                            class="text-gray-200 font-medium text-lg leading-snug group-hover:text-blue-400 transition-colors duration-300">
                            {{ $insight->title }}
                        </h3>
                    </div>

                    {{-- Reduced mt-2 to mt-6 to bring the button closer to the text --}}
                    <div class="mt-6 relative z-10">
                        <a href="{{ route('insight.show', $insight->slug ?? $insight->id) }}"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary text-white transition-all duration-300 shadow-md group-hover:bg-[#1976D2] group-hover:scale-110 after:absolute after:inset-0 after:content-['']">
                            <i class="fa-solid fa-arrow-right text-base"></i>
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

<x-layout title="{{ $post->title }} – Nigeria Risk Index">

    {{-- 1. SPLIT HEADER (Text Left / Image Right) --}}
    <header class="bg-[#0E1B2C] border-b border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[400px]">

                {{-- LEFT COLUMN: Text Content --}}
                <div class="flex flex-col justify-center p-10 md:p-14 lg:p-20 bg-primary">

                    {{-- Dynamic Category Label --}}
                    <div class="flex items-center gap-3 mb-8">
                        <span class="h-px w-10 bg-emerald-500"></span>
                        <span class="text-white font-semibold uppercase tracking-[0.2em] text-xs">
                            {{ $post->category->name ?? 'Insight' }}
                        </span>
                    </div>

                    {{-- Dynamic Heading --}}
                    <h1 class="text-3xl md:text-3xl lg:text-3xl font-semibold text-white leading-tight mb-8">
                        {{ $post->title }}
                    </h1>

                    {{-- Author/Meta Data --}}
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-400 font-medium mt-auto lg:mt-0">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2">
                                <i class="fa-regular fa-clock text-emerald-500"></i>
                                {{-- Simple read time calculation --}}
                                {{ ceil(str_word_count(strip_tags($post->content)) / 200) }} Min Read
                            </span>
                            <span class="text-gray-700">&bull;</span>
                            <time>{{ $post->created_at->format('M Y') }}</time>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Dynamic Image --}}
                <div class="relative h-30 lg:h-auto w-full">
                    <img src="{{ asset('storage/uploads/' . $post->featureimage) }}" alt="{{ $post->title }}"
                        class="absolute inset-0 w-full h-full object-cover">
                </div>

            </div>
        </div>
    </header>

    {{-- 2. ARTICLE BODY --}}
    <div class="bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">

            <div class="max-w-4xl mx-auto">
                <article class="text-gray-800 leading-relaxed text-lg">

                    {{-- Lead Paragraph (Using the 'description' or 'excerpt' field if you have one) --}}
                    @if ($post->description)
                        <p
                            class="text-xl md:text-2xl text-gray-900 font-medium leading-relaxed border-b border-gray-200 pb-10 mb-10">
                            {{ $post->description }}
                        </p>
                    @endif

                    {{-- TinyMCE Content Area --}}
                    {{-- Note: 'prose' ensures HTML from TinyMCE is styled correctly --}}
                    <div
                        class="prose prose-lg prose-blue max-w-none
                                prose-img:rounded-2xl prose-headings:text-gray-900">
                        {!! $post->content !!}
                    </div>

                </article>
            </div>

            {{-- 3. RELATED INSIGHTS --}}
            <div class="mt-24 pt-12 border-t border-gray-100">

                {{-- Header Section: Flexbox to push items to edges --}}
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-2xl font-medium text-gray-900 tracking-wide">
                        Related Insights
                    </h3>

                    <a href="{{ route('insights.index') }}"
                        class="group flex items-center gap-2 text-sm font-medium text-primary uppercase tracking-wider hover:opacity-80 transition-all">
                        View More Insights
                        <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>

                {{-- Grid Content --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                    @foreach ($relatedCategoryPost as $related)
                        <article
                            class="relative flex flex-col p-6 rounded-2xl bg-card shadow-lg border border-white/5 overflow-hidden transition-all duration-300 transform-gpu hover:shadow-2xl hover:bg-[#253646] group min-h-[220px]">

                            <div class="relative z-10 pointer-events-none">
                                <span
                                    class="text-xs font-bold text-white uppercase tracking-wider mb-2 block opacity-80">
                                    {{ $related->category->name ?? 'Uncategorized' }}
                                </span>

                                <h3
                                    class="text-gray-200 font-medium text-lg leading-snug group-hover:text-blue-400 transition-colors duration-300">
                                    {{ $related->title }}
                                </h3>
                            </div>

                            <div class="mt-6 relative z-10">
                                <a href="{{ route('insight.show', $related->slug ?? $related->id) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary text-white transition-all duration-300 shadow-md group-hover:bg-[#1976D2] group-hover:scale-110 after:absolute after:inset-0 after:content-['']">
                                    <i class="fa-solid fa-arrow-right text-base"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach

                </div>
            </div>

        </div>
    </div>

</x-layout>

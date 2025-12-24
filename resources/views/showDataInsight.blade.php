<x-layout title="{{ $post->title }} – Nigeria Risk Index">

    {{-- 1. SPLIT HEADER (Text Left / Image Right) --}}
    <header class="bg-[#0E1B2C] border-b border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[400px]">

                {{-- LEFT COLUMN: Text Content --}}
                <div class="flex flex-col justify-center p-10 md:p-14 lg:p-20 bg-[#0E1B2C]">

                    {{-- Dynamic Category Label --}}
                    <div class="flex items-center gap-3 mb-8">
                        <span class="h-px w-10 bg-emerald-500"></span>
                        <span class="text-emerald-400 font-bold uppercase tracking-[0.2em] text-xs">
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
                <h3 class="text-2xl font-bold text-gray-900 mb-10 uppercase tracking-wide">Related Insights</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                    @foreach ($relatedCategoryPost as $related)
                        <article
                            class="flex flex-col justify-between p-6 rounded-2xl bg-[#1E2D3D] shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 min-h-[280px] border border-white/5 group relative">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4 block">
                                    {{ $related->category->name ?? 'Insight' }}
                                </span>
                                <h3
                                    class="text-gray-100 font-medium text-lg leading-snug group-hover:text-blue-400 transition-colors">
                                    {{ $related->title }}
                                </h3>
                                <p class="mt-4 text-gray-400 text-sm leading-relaxed line-clamp-3">
                                    {{ $related->description }}
                                </p>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('insight.show', $related->slug ?? $related->id) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white group-hover:bg-blue-600 transition-colors after:absolute after:inset-0">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach

                </div>
            </div>

        </div>
    </div>

</x-layout>

<x-layout title="Our Latest Insights – Nigeria Risk Index">

    {{-- Refined Header --}}
    <header class="bg-[#0E1B2C] py-20 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
            {{-- Decorative Label --}}

            <h1 class="text-4xl md:text-5xl font-semibold text-white mb-6">Our Latest Insights</h1>

            <p class="text-gray-400 max-w-2xl mx-auto text-lg leading-relaxed">
                Strategic analysis and deep dives into the security, economic, and geopolitical landscape of Nigeria.
            </p>
        </div>
    </header>

    {{-- Main Content Grid: Now on White Background --}}
    <main class="bg-white min-h-screen py-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
                @foreach ($insights as $item)
                    <article
                        class="group flex flex-col justify-between h-full p-2 bg-white transition-all duration-300 relative">
                        <div>
                            {{-- Category Label --}}
                            <span class="block text-sm font-medium text-gray-900 uppercase tracking-wider mb-4">
                                {{ $item->category->name ?? 'Insights' }}
                            </span>

                            {{-- Title: Clean and authoritative --}}
                            <h3
                                class="text-xl font-semibold text-primary leading-[1.4] transition-colors duration-300 group-hover:text-blue-600">
                                <a href="{{ route('insight.show', $item->slug ?? $item->id) }}">
                                    {{ $item->title }}
                                </a>
                            </h3>

                            {{-- Description Removed as requested --}}
                        </div>

                        {{-- Action Button: Maintaining your blue brand color --}}
                        <div class="mt-8">
                            <a href="{{ route('insight.show', $item->slug ?? $item->id) }}"
                                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white transition-all duration-300 shadow-md group-hover:bg-[#1976D2] group-hover:scale-110 after:absolute after:inset-0">
                                <i class="fa-solid fa-arrow-right text-lg"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-20 pt-10 border-t border-gray-100">
                {{ $insights->links() }}
            </div>

        </div>
    </main>

</x-layout>

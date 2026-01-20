<x-layout title="All Reports – Nigeria Risk Index">

    {{-- 1. HEADER SECTION (Kept Dark for Contrast) --}}
    <section class="relative bg-[#0F172A] py-16 px-6 overflow-hidden border-b border-white/5">
        <div class="relative z-10 max-w-7xl mx-auto text-center">

            <h1 class="text-3xl md:text-4xl font-semibold text-white mb-4">
                Security Reports & Analysis
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto text-base md:text-lg leading-relaxed">
                Access verified data-driven reports on Nigeria's security landscape.
            </p>
        </div>

        {{-- Background Glow --}}
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-4xl bg-blue-600/5 blur-3xl -z-0 pointer-events-none">
        </div>
    </section>

    {{-- 2. ALL REPORTS GRID (White Background) --}}
    <section class="bg-white py-16 px-6 min-h-screen">
        <div class="max-w-7xl mx-auto">

            {{-- Section Title --}}
            <div class="flex items-center justify-between mb-10  pb-4">
                <h3 class="text-xl text-gray-900 font-semibold">Available Reports</h3>
                <span class="text-sm text-gray-500">{{ count($reports) }} Documents</span>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($reports as $report)
                    <article
                        class="bg-white rounded-md overflow-hidden shadow-sm border border-gray-200 hover:shadow-lg hover:border-card/50 transition-all duration-300 group flex flex-col h-full">

                        {{-- Card Image --}}
                        <div class="h-56 overflow-hidden relative bg-gray-100 border-b border-gray-100">
                            @if (isset($report['image']))
                                <img src="{{ asset($report['image']) }}" alt="{{ $report['title'] }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <i class="fa-regular fa-file-pdf text-5xl"></i>
                                </div>
                            @endif

                            {{-- PDF Badge --}}
                            <div
                                class="absolute top-4 right-4 bg-white/90 backdrop-blur text-gray-800 text-[10px] font-bold px-2 py-1 rounded shadow-sm border border-gray-200">
                                PDF
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="mb-4">
                                <span
                                    class="text-emerald-600 text-xs font-semibold uppercase tracking-wider block mb-2">
                                    {{ $report['period'] }}
                                </span>
                                <h4
                                    class="text-lg font-bold text-gray-900 mb-3 leading-tight group-hover:text-emerald-700 transition-colors">
                                    {{ $report['title'] }}
                                </h4>
                                <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
                                    {{ $report['description'] }}
                                </p>
                            </div>

                            {{-- Footer / Action --}}
                            <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">
                                <a href="{{ $report['download_link'] }}" target="_blank"
                                    class="text-sm font-semibold text-gray-700 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                    <span>Download Report</span>
                                    <i class="fa-solid fa-arrow-down-long"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if (empty($reports))
                <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-regular fa-folder-open text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No reports available at the moment.</p>
                </div>
            @endif
        </div>
    </section>

</x-layout>

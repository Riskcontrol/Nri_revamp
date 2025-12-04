<main class="min-h-screen grid place-items-center py-6 md:py-10 bg-primary">
    <section class="w-[min(1250px,96vw)]">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="pt-8 pb-2 md:pt-10 md:pb-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-y-4">
                    <div class="space-y-2">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight text-white">
                            Nigeria Terrorism Index
                        </h1>
                        <p class="text-sm sm:text-base text-gray-400">
                            An analysis of terrorist incidents and risk levels in Nigeria.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 sm:px-6 lg:px-8 pb-12 pt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">

            <article class="relative lg:col-span-7 bg-card rounded-xl border border-white/10 shadow-lg overflow-hidden">
                <div class="p-2 sm:p-3">
                    <div
                        class="relative h-[400px] sm:h-[500px] rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">

                        <div class="w-full h-full p-4 flex justify-center items-center">
                            <img src="{{ asset('images/map_risk.png') }}" alt="Risk Map of Nigeria"
                                class="max-h-full max-w-full object-contain">
                        </div>

                        <div
                            class="absolute right-3 bottom-3 bg-white/95 backdrop-blur px-4 py-2 rounded-md border border-slate-200 shadow-sm z-10">
                            <div class="font-semibold text-slate-700 text-xs uppercase tracking-wider">Incidents</div>
                            <div class="mt-1 flex items-center gap-2 text-sm text-slate-600">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span class="text-xs">Low</span>
                                <div
                                    class="h-1 w-8 bg-gradient-to-r from-green-500 via-yellow-500 to-red-500 rounded-full">
                                </div>
                                <span class="text-xs">High</span>
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            </div>
                        </div>

                        <div class="absolute bottom-5 left-4 z-20">
                            <a href="{{ route('securityIntelligence') }}"
                                class="inline-flex items-center gap-2 bg-primary/90 hover:bg-black text-white backdrop-blur-md font-semibold border border-white/20 rounded-lg px-5 py-3 shadow-xl transition-all hover:scale-105 active:scale-95 group">
                                Access Risk Intelligence Database
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <aside
                class="lg:col-span-5 bg-card rounded-xl border border-white/10 shadow-lg overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-white/10 flex justify-between items-center bg-primary/30">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>📊</span> Risk State Data
                    </h3>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full text-left">
                        <thead class="bg-primary/50 text-gray-300 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 font-semibold">State</th>
                                <th class="py-3 px-4 font-semibold text-center">Incidents</th>
                                <th class="py-3 px-4 font-semibold text-center">NTI Score</th>
                                <th class="py-3 px-4 font-semibold text-right">Risk Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-sm text-gray-200">
                            @php
                                $incidentNo = $total_ratio = 0;
                            @endphp

                            @foreach ($dataByState as $stateId => $report)
                                <tr class="hover:bg-white/5 transition-colors duration-150">
                                    <td class="py-3 px-4 font-medium text-white">{{ $report['location'] }}</td>

                                    <td class="py-3 px-4 text-center text-gray-400 font-mono">
                                        {{ $report['incident_count'] }}
                                    </td>

                                    <td class="py-3 px-4 text-center font-mono">
                                        {{ number_format((float) ($report['total_ratio'] == 0 ? 0.01 : $report['total_ratio']), 2, '.', '') }}%
                                    </td>

                                    <td class="py-3 px-4 text-right">
                                        {{-- Using standard Tailwind colors for badges --}}
                                        @if ($report['total_ratio'] > 7)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                                High
                                            </span>
                                        @elseif($report['total_ratio'] > 3)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                                Moderate
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                                Low
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Empty State Check --}}
                            @if (count($dataByState) == 0)
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        No data available for this period.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </aside>

        </div>
    </section>
</main>

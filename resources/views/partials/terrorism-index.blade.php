<main class="min-h-screen py-10 md:py-16 bg-primary">
    {{-- Replaced arbitrary width with standard max-w-7xl --}}
    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Part --}}
        <div class="pb-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-y-4">
                <div class="space-y-2">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight text-white">
                        Real-time Security Intelligence
                    </h1>
                    <p class="text-sm sm:text-base text-gray-400 max-w-2xl">
                        Track terrorism, armed robbery, and security incidents across Nigeria with actionable
                        intelligence
                    </p>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- Left Column: Image Card --}}
            <article
                class="relative lg:col-span-7 bg-card rounded-xl border border-white/10 shadow-lg overflow-hidden flex flex-col">
                <div class="relative h-[400px] sm:h-[500px] bg-white/5 flex items-center justify-center p-6 group">
                    <img src="{{ asset('images/risk-database.png') }}" alt="Risk Map of Nigeria"
                        class="max-h-full max-w-full object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-[1.02]">

                    <div class="absolute bottom-4 right-4 z-20">
                        <a href="{{ route('securityIntelligence') }}"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2.5 rounded-lg shadow-lg transition-all duration-300 hover:shadow-blue-500/30 hover:-translate-y-0.5 active:translate-y-0 active:scale-95">
                            <span>Access Database</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>

            {{-- Right Column: Data Table --}}
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
                                        @if ($report['total_ratio'] > 7)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">High</span>
                                        @elseif($report['total_ratio'] > 3)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Moderate</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">Low</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

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

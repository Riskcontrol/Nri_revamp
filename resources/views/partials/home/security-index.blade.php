<main class="min-h-screen py-10 md:py-16 bg-primary">
    {{-- Replaced arbitrary width with standard max-w-7xl --}}
    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Part: Updated to center alignment --}}
        <div class="pb-12 text-center"> {{-- Added text-center here --}}
            <div class="flex flex-col items-center justify-center gap-y-4"> {{-- Changed flex logic --}}
                <div class="space-y-3">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-medium tracking-tight text-white">
                        Real-time Security Intelligence
                    </h1>
                    {{-- Added mx-auto to center the max-w-2xl paragraph --}}
                    <p class="text-base sm:text-lg text-gray-400 mx-auto leading-relaxed">
                        Track terrorism, armed robbery, and security incidents across Nigeria with actionable
                        intelligence
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- Left Column: Image Card --}}
            <article
                class="relative lg:col-span-7 bg-card rounded-xl border border-white/10 shadow-lg overflow-hidden flex flex-col">
                <div class="relative h-[400px] sm:h-[500px] bg-white/5 flex items-center justify-center p-6 group">
                    <img src="{{ asset('images/risk-database.png') }}" alt="Risk Map of Nigeria"
                        class="max-h-full max-w-full object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-[1.02]">
                </div>
            </article>

            {{-- Right Column: Data Table --}}
            <aside
                class="lg:col-span-5 bg-card rounded-xl border border-white/10 shadow-lg overflow-hidden flex flex-col max-h-[500px]">
                <div class="px-5 py-4 border-b border-white/10 flex justify-between items-center bg-primary/30">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>📊</span> Risk State Data
                    </h3>
                </div>

                @php
                    $pillClass = fn($level) => match (strtolower($level)) {
                        'very high' => 'bg-red-700/10 text-red-700 border border-red-700/20',
                        'high' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                        'medium' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                        default => 'bg-green-500/10 text-green-400 border border-green-500/20',
                    };
                @endphp

                <div class="overflow-y-auto flex-1"> {{-- Changed to overflow-y-auto to allow scrolling within fixed height --}}
                    <table class="min-w-full text-left">
                        <thead class="sticky top-0 z-10 bg-[#1a1f2e] text-gray-300 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 font-semibold">State</th>
                                <th class="py-3 px-4 font-semibold text-center">Incidents</th>
                                <th class="py-3 px-4 font-semibold text-center">NCI Score</th>
                                <th class="py-3 px-4 font-semibold text-right">Risk Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-sm text-gray-200">
                            @foreach ($securityIndexRows as $report)
                                <tr class="hover:bg-white/5 transition-colors duration-150">
                                    <td class="py-3 px-4 font-medium text-white">
                                        {{ $report['location'] }}
                                    </td>

                                    <td class="py-3 px-4 text-center text-gray-400 font-mono">
                                        {{ $report['incident_count'] }}
                                    </td>

                                    <td class="py-3 px-4 text-center font-mono text-white">
                                        {{ number_format((float) $report['nci_score'], 2) }}%
                                    </td>

                                    <td class="py-3 px-4 text-right">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pillClass($report['risk_level']) }}">
                                            {{ $report['risk_level'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </aside>
        </div>

        {{-- NEW: Button Section below the grid --}}
        <div class="mt-10 flex justify-center">
            <a href="{{ route('securityIntelligence') }}"
                class="inline-flex items-center gap-3 border-2 border-gray-500 bg-transparent text-gray-500 font-semibold text-base px-8 py-3.5 rounded-xl transition-all duration-300 hover:bg-white hover:text-gray-600 hover:shadow-xl hover:-translate-y-1 active:translate-y-0 active:scale-95">
                <span>Access Security Database</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>
</main>

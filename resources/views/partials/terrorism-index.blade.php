<main class="min-h-screen grid place-items-center py-6 md:py-10">
    <section class="w-[min(1250px,96vw)]">


        <!-- Title row -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Top spacing -->
            <div class="pt-8 pb-2 md:pt-10 md:pb-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-y-4">
                    <div class="space-y-2">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight text-[#185451]">
                            Nigeria Terrorism Index
                        </h1>
                        <p class="text-sm sm:text-base text-slate-600/80">
                            An analysis of terrorist incidents and risk levels in Nigeria.
                        </p>
                    </div>


                </div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="px-4 sm:px-6 lg:px-8 pb-12 pt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Map card (image) -->
            <article
                class="relative lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-sm overflow-visible">
                <div class="p-2 sm:p-3">
                    <div class="relative h-[360px] sm:h-[420px] rounded-lg overflow-hidden bg-white">
                        <div class="card p-6 mb-8 flex justify-center items-center bg-white" style="height: 500px;">

                            <img src="{{ asset('images/map_risk.png') }}" alt="">
                        </div>

                        <!-- Legend chip -->
                        <div
                            class="absolute right-3 bottom-3 bg-white/95 backdrop-blur px-4 py-3 rounded-md border border-slate-200 shadow-sm">
                            <div class="font-semibold text-slate-700">Incidents</div>
                            <div class="mt-1 flex items-center gap-2 text-sm text-slate-600">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span> Low — High
                            </div>
                        </div>

                        <!-- CTA with frosted glass effect -->
                        <button
                            class="absolute bg-black text-white bottom-5 left-4 backdrop-blur-md font-semibold border-2 border-slate-900 rounded px-4 py-2 shadow hover:bg-white/90">
                            Access Risk Intelligence Database
                        </button>
                    </div>
                </div>
            </article>


            <!-- Right: Risk table -->
            <aside class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold">Risk State</h3>

                    <!-- Filter Button -->
                    {{-- <button
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow-md hover:bg-indigo-700 transition duration-200">Filter</button> --}}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="text-slate-500 text-sm">
                            <tr class="border-b border-slate-200">
                                <th class="py-3 px-4">State</th>
                                <th class="py-3 px-4">Incidents</th>
                                <th class="py-3 px-4">NTI<br />Score</th>
                                <th class="py-3 px-4">Risk Level</th>
                                {{-- <th class="py-3 px-4">Ranking</th>
                                <th class="py-3 px-4">Previous Ranking</th>
                                <th class="py-3 px-4">Y-on-Y Change</th>
                                <th class="py-3 px-4">Status</th> --}}
                            </tr>
                        </thead>
                        <tbody class="text-[15px]">
                            @php
                                $incidentNo = $total_ratio = 0;
                                $pincidentNo = $ptotal_ratio = 0;
                            @endphp
                            @foreach ($dataByState as $stateId => $report)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-medium">{{ $report['location'] }}</td>
                                    <td class="py-3 px-4">{{ $report['incident_count'] }}</td>
                                    <td class="py-3 px-4">
                                        {{ number_format((float) ($report['total_ratio'] == 0 ? 0.01 : $report['total_ratio']), 2, '.', '') . '%' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $report['total_ratio'] > 7 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $report['total_ratio'] > 7 ? 'High' : 'Moderate' }}
                                        </span>
                                    </td>
                                    {{-- <td class="py-3 px-4">{{ $report['ranking'] }}</td> --}}
                                    {{--
                                    @php
                                        $previousReport = $PreviousYearDataByState[$stateId] ?? null;
                                        $overAllChange = 0;

                                        if ($previousReport) {
                                            $overAllChange = number_format(
                                                (float) $report['total_ratio'] - (float) $previousReport['total_ratio'],
                                                2,
                                                '.',
                                                '',
                                            );
                                        } else {
                                            $overAllChange = number_format((float) $report['total_ratio'], 2, '.', '');
                                        }

                                        // Calculate momentum factor for ranking
                                        if ($overAllChange > 0) {
                                            $momentumFactor = 1 + ($overAllChange / 100) * 0.1;
                                        } elseif ($overAllChange < 0) {
                                            $momentumFactor = 1 - ($overAllChange / 100) * 0.1;
                                        } else {
                                            $momentumFactor = 1;
                                        }
                                        $newOverAllChange = $overAllChange * $momentumFactor;
                                    @endphp

                                    <td class="py-3 px-4">
                                        {{ $previousDataByLocation[$report['location']]['ranking'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        {{ abs(number_format((float) $newOverAllChange, 2, '.', '')) }}%</td>

                                    <td class="fw-bold">
                                        @if ($overAllChange > 0)
                                            <span class="escalating"><i class="fas fa-arrow-up ml-1"></i>
                                                Escalating</span>
                                        @elseif ($overAllChange < 0)
                                            <span class="improving"><i class="fas fa-arrow-down ml-1"></i>
                                                Improving</span>
                                        @else
                                            <span class="no-change"><i class="fas fa-minus ml-1"></i> No Change</span>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </aside>

        </div>

    </section>
</main>

<x-layout title="Security Intelligence Hub">

    <section class="min-h-screen bg-[#f8fafc] font-sans antialiased">
        <div class="bg-gradient-to-b from-[#0a1628] to-[#111e2f] text-white px-6 py-16 lg:px-16 shadow-inner">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-end items-center space-x-3 mb-10">
                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-[0.2em]">Data Controls</span>
                    <button
                        class="bg-white/5 border border-white/10 px-4 py-1.5 rounded-full text-xs font-semibold hover:bg-white/10 transition flex items-center gap-2">
                        <i class="fa-solid fa-earth-africa text-blue-400"></i> Region
                    </button>
                    <button
                        class="bg-white/5 border border-white/10 px-4 py-1.5 rounded-full text-xs font-semibold hover:bg-white/10 transition flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-400"></i> Timeline
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
                    <div class="lg:w-1/2 space-y-4">
                        <h1 class="text-4xl lg:text-6xl font-black tracking-tight leading-[1.1] text-white">
                            Nigeria Security <br /> Intelligence Hub</span>
                        </h1>
                        <p class="text-gray-400 text-lg leading-relaxed max-w-md font-medium">
                            Premium risk data and forensic security analysis for professional intelligence
                            organizations.
                        </p>
                    </div>

                    <div
                        class="lg:w-5/12 w-full bg-[#1e2d3d]/40 backdrop-blur-md rounded-3xl p-8 border border-white/10 shadow-2xl">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-sm font-bold text-blue-400 uppercase tracking-widest">Active Monitoring</h2>
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-6 text-center">
                            <div class="space-y-1">
                                <span
                                    class="block text-4xl font-black text-white">{{ number_format($totalIncidents) }}</span>
                                <span
                                    class="text-[9px] uppercase text-gray-400 font-extrabold tracking-widest">Incidents</span>
                            </div>
                            <div class="space-y-1 border-x border-white/5">
                                <span
                                    class="block text-4xl font-black text-blue-500">{{ number_format($highRiskAlerts) }}</span>
                                <span
                                    class="text-[9px] uppercase text-gray-400 font-extrabold tracking-widest">Critical</span>
                            </div>
                            <div class="space-y-1">
                                <span
                                    class="block text-4xl font-black text-white">{{ number_format($statesAffected) }}</span>
                                <span
                                    class="text-[9px] uppercase text-gray-400 font-extrabold tracking-widest">States</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 px-6 lg:px-16 -mt-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 text-[#0a1628] uppercase text-[10px] font-black tracking-[0.15em]">
                                    <th class="px-6 py-5 border-b border-gray-100">No</th>
                                    <th class="px-6 py-5 border-b border-gray-100">State</th>
                                    <th class="px-6 py-5 border-b border-gray-100">Neighbourhood</th>
                                    <th class="px-6 py-5 border-b border-gray-100">Date</th>
                                    <th class="px-6 py-5 border-b border-gray-100">Incident</th>
                                    <th class="px-6 py-5 border-b border-gray-100">Associated Risk</th>
                                    <th class="px-6 py-5 border-b border-gray-100 text-center">Impact</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-600 divide-y divide-gray-50">
                                @foreach ($incidents as $index => $incident)
                                    <tr class="hover:bg-blue-50/40 transition-all group">
                                        <td class="px-6 py-6 font-mono text-gray-400 text-xs">
                                            {{ $incidents->firstItem() + $index }}</td>
                                        <td class="px-6 py-6 font-bold text-[#0a1628]">{{ $incident->location }}</td>
                                        <td class="px-6 py-6 text-gray-500 font-medium">
                                            {{ $incident->lga ?? 'Unknown' }}</td>
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <span
                                                class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-[11px] font-bold">
                                                {{ \Carbon\Carbon::parse($incident->eventdateToUse)->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-6 max-w-sm leading-relaxed text-gray-500">
                                            {{ $incident->add_notes }}
                                        </td>
                                        <td class="px-6 py-6 italic text-gray-500">
                                            {{ $incident->associated_risks }}
                                        </td>
                                        <td class="px-6 py-6 text-center">
                                            @php
                                                $casualties = $incident->Casualties_count ?? 0;
                                                $victims = $incident->victim ?? 0;

                                                if ($casualties > 2 || $victims > 5) {
                                                    $bgClass = 'bg-red-600';
                                                    $label = 'High Impact';
                                                } elseif ($casualties > 0 || $victims > 0) {
                                                    $bgClass = 'bg-orange-500';
                                                    $label = 'Medium';
                                                } else {
                                                    $bgClass = 'bg-emerald-500';
                                                    $label = 'Low';
                                                }
                                            @endphp
                                            <span
                                                class="inline-block px-4 py-1.5 rounded-full {{ $bgClass }} text-white text-[10px] font-black uppercase tracking-widest shadow-sm">
                                                {{ $label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-20 bg-[#0a1628] rounded-[2rem] p-12 text-center relative overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 p-10 opacity-10">
                        <i class="fa-solid fa-shield-halved text-9xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-white relative z-10">Professional Security Access</h3>
                    <p class="text-gray-400 mt-3 max-w-lg mx-auto relative z-10 font-medium">Join 500+ security
                        directors receiving daily forensic updates and critical regional threat assessments.</p>
                    <div
                        class="flex flex-wrap justify-center gap-8 mt-8 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] relative z-10">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-500"></i> Real-time
                            alerts</span>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-500"></i> Advanced
                            Analytics</span>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-check text-blue-500"></i> Expert
                            Insights</span>
                    </div>
                    <button
                        class="mt-10 bg-blue-600 hover:bg-blue-500 text-white font-black py-4 px-12 rounded-xl shadow-xl uppercase tracking-widest text-xs transition-all hover:scale-105 active:scale-95 relative z-10">
                        Claim Professional Access
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-[#f8fafc] py-20 px-6 lg:px-16 border-t border-gray-100">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="group bg-white p-10 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-blue-500/30 hover:shadow-xl transition-all">
                    <div
                        class="h-14 w-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                        <i class="fa-solid fa-fingerprint text-2xl text-blue-600 group-hover:text-white"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#0a1628]">Security Intelligence</h4>
                    <p class="text-gray-500 text-sm mt-3 leading-relaxed">Weekly deep-dives into regional insurgencies
                        and organized crime patterns.</p>
                    <button
                        class="mt-8 w-full bg-[#0a1628] text-white py-4 rounded-xl flex items-center justify-center gap-3 font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all">
                        <i class="fa-solid fa-lock text-[10px]"></i> Get Weekly Brief
                    </button>
                </div>

                <div
                    class="group bg-white p-10 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-blue-500/30 hover:shadow-xl transition-all">
                    <div
                        class="h-14 w-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                        <i class="fa-solid fa-chart-line text-2xl text-blue-600 group-hover:text-white"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#0a1628]">Risk Assessment</h4>
                    <p class="text-gray-500 text-sm mt-3 leading-relaxed">Dynamic vulnerability scoring for corporate
                        operations and logistical routes.</p>
                    <button
                        class="mt-8 w-full border-2 border-gray-100 text-[#0a1628] py-4 rounded-xl flex items-center justify-center gap-3 font-bold text-xs uppercase tracking-widest hover:bg-gray-50 transition-all">
                        <i class="fa-solid fa-file-export text-[10px]"></i> Download Template
                    </button>
                </div>

                <div
                    class="group bg-white p-10 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-blue-500/30 hover:shadow-xl transition-all">
                    <div
                        class="h-14 w-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                        <i class="fa-solid fa-vault text-2xl text-blue-600 group-hover:text-white"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#0a1628]">Intelligence Reports</h4>
                    <p class="text-gray-500 text-sm mt-3 leading-relaxed">Archived historical data on security trends
                        spanning the last decade.</p>
                    <button
                        class="mt-8 w-full border-2 border-gray-100 text-[#0a1628] py-4 rounded-xl flex items-center justify-center gap-3 font-bold text-xs uppercase tracking-widest hover:bg-gray-50 transition-all">
                        Access Premium Archive
                    </button>
                </div>
            </div>
        </div>
    </section>

</x-layout>

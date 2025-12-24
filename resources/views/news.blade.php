<x-layout title="Security Intelligence Hub">

    <section class="min-h-screen bg-[#f8fafc] font-sans antialiased">
        <div class="bg-white text-primary px-6 py-16 lg:px-16 shadow-inner">
            <div class="max-w-7xl mx-auto">


                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
                    <div class="lg:w-1/2 space-y-4">
                        <h1 class="text-3xl lg:text-4xl font-medium tracking-tight leading-[1.1] text-primary">
                            Nigeria Security <br /> Intelligence Hub</span>
                        </h1>
                        <p class="text-gray-900 text-xl leading-relaxed max-w-md font-medium">
                            Comprehensive security intelligence platform for professionals and organization
                        </p>
                    </div>

                    <div class="lg:w-5/12 w-full bg-white rounded-2xl p-8 border-2 border-primary shadow-md">

                        {{-- Header --}}
                        <div class="flex flex-col items-center justify-center mb-8">
                            <h2 class="text-sm font-semibold text-primary uppercase tracking-widest text-center">
                                Real time Security Dashboard
                            </h2>

                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-3 gap-6 text-center">

                            {{-- Total Incidents --}}
                            <div class="space-y-1">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($totalIncidents) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-bold tracking-widest">
                                    Incidents this week
                                </span>
                            </div>

                            {{-- High Risk Alerts --}}
                            <div class="space-y-1 border-x border-gray-100">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($highRiskAlerts) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-bold tracking-widest">
                                    High risk alerts
                                </span>
                            </div>

                            {{-- States Affected --}}
                            <div class="space-y-1">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($statesAffected) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-bold tracking-widest">
                                    States affected
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 px-6 lg:px-16 -mt-8 bg-primary">
            <div class="max-w-7xl mx-auto">
                <div class="bg-primary border border-white/10 overflow-hidden rounded-xl shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse bg-primary">
                            <thead>
                                <tr
                                    class="bg-white/5 text-gray-300 uppercase text-[10px] font-black tracking-[0.15em] border-b border-white/10">
                                    <th class="px-6 py-5 border-r border-white/10 text-center">No</th>
                                    <th class="px-6 py-5 border-r border-white/10">State</th>
                                    <th class="px-6 py-5 border-r border-white/10">Neighbourhood</th>
                                    <th class="px-6 py-5 border-r border-white/10">Date</th>
                                    <th class="px-6 py-5 border-r border-white/10">Incident</th>
                                    <th class="px-6 py-5 border-r border-white/10">Associated Risk</th>
                                    <th class="px-6 py-5 text-center">Impact</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-300 divide-y divide-white/10">
                                @foreach ($incidents as $index => $incident)
                                    <tr class="hover:bg-white/5 transition-all group">
                                        <td
                                            class="px-6 py-6 font-mono text-gray-500 text-xs border-r border-white/10 text-center">
                                            {{ $incidents->firstItem() + $index }}
                                        </td>
                                        <td class="px-6 py-6 font-bold text-white border-r border-white/10">
                                            {{ $incident->location }}
                                        </td>
                                        <td class="px-6 py-6 font-semibold border-r border-white/10 text-blue-400">
                                            {{ $incident->proper_lga }}
                                        </td>
                                        <td class="px-6 py-6 whitespace-nowrap border-r border-white/10">
                                            <span
                                                class="bg-white/10 border border-white/20 text-gray-100 px-3 py-1 rounded-md text-[11px] font-bold">
                                                {{ \Carbon\Carbon::parse($incident->eventdateToUse)->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-6 max-w-sm leading-relaxed border-r border-white/10 text-gray-400">
                                            {{ $incident->add_notes }}
                                        </td>
                                        <td class="px-6 py-6 italic border-r border-white/10 text-gray-300">
                                            {{ $incident->display_risk }}
                                        </td>
                                        <td class="px-6 py-6 text-center">
                                            <span
                                                class="inline-block px-4 py-1.5 rounded-full {{ $incident->impact_class }} text-white text-[10px] font-black uppercase tracking-widest shadow-sm border border-white/10">
                                                {{ $incident->impact_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination Area --}}
                <div class="bg-white/5 border-t border-white/10 px-6 py-4 rounded-b-xl">
                    <div class="custom-pagination">
                        {{ $incidents->links() }}
                    </div>
                </div>
            </div>
        </div>

        <section class="relative bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-16 py-20 lg:py-28">
                <div class="flex flex-col lg:flex-row items-center gap-16">

                    {{-- Content Side --}}
                    <div class="lg:w-1/2 space-y-8 relative z-10">
                        <div class="space-y-4">
                            {{-- Font normal and gray-900 for contrast on white --}}
                            <h2 class="text-gray-900 text-3xl lg:text-4xl font-normal leading-[1.1] tracking-tight">
                                Nigeria Security Landscape <br>
                                <span class="text-gray-900">2018 – 2024 Report</span>
                            </h2>
                        </div>

                        {{-- Font normal and gray-600 for optimal readability --}}
                        <p class="text-gray-700 text-md lg:text-md leading-relaxed font-medium">
                            Nigeria's security landscape from 2018 to 2024 reveals a counterintuitive reality -
                            incidents increased significantly by 160.8%,
                            yet annual deaths have fallen by 41.0%.
                            This report, based on 25,945 verified incidents,
                            documents how Nigeria's insecurity has transformed from concentrated, high-casualty
                            terrorism to widespread, lower-intensity criminality.
                        </p>

                        <div class="flex flex-wrap gap-4 pt-4">
                            {{-- Updated button for white background --}}
                            <a href="{{ route('reports.download') }}" target="_blank"
                                class="inline-flex items-center border border-gray-800 gap-3 bg-transparent hover:bg-gray-800 hover:text-white text-gray-800 font-normal py-4 px-10 rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 uppercase tracking-widest text-sm">
                                <span>Download Full Report</span>
                                <i class="fa-solid fa-cloud-arrow-down animate-bounce"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Image/Graphic Side --}}
                    <div class="lg:w-1/2 relative flex justify-center">
                        {{-- Added max-w-sm to reduce the container size --}}
                        <div
                            class="relative rounded-md overflow-hidden border border-gray-100 shadow-xl group max-w-sm">
                            {{-- Softened gradient for white theme --}}
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-white/30 via-transparent to-transparent z-10">
                            </div>

                            {{-- Height reduced from 450px to 320px --}}
                            <img src="{{ asset('images/download.png') }}" alt="Security Analysis Visual"
                                class="w-full h-[320px] object-cover transition-transform duration-700 group-hover:scale-110">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Background Decoration: Subtly lightened for the white background --}}

        </section>

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
    <style>
        /* Targeting Laravel's default Tailwind classes to match your theme */
        .custom-pagination nav div div span,
        .custom-pagination nav div div a {
            @apply border-black text-[11px] font-semibold uppercase tracking-widest transition-all;
        }

        /* Active Page Style */
        .custom-pagination nav div div span[aria-current="page"] span {
            @apply bg-[#0a1628] border-black text-white !important;
        }

        /* Hover States for links */
        .custom-pagination nav div div a:hover {
            @apply bg-blue-600 text-white border-black !important;
        }

        /* Mobile handling for the "Previous" and "Next" buttons */
        .custom-pagination nav flex:first-child a,
        .custom-pagination nav flex:first-child span {
            @apply border-black bg-white text-black font-black uppercase text-[10px];
        }
    </style>
</x-layout>

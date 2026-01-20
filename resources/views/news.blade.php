<x-layout title="Security Intelligence Hub">

    <section class="min-h-screen bg-[#f8fafc] font-sans antialiased">
        <div class="bg-white text-primary px-6 py-16 lg:px-16 shadow-inner">
            <div class="max-w-7xl mx-auto">


                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
                    <div class="lg:w-1/2 space-y-4">
                        <h1 class="text-3xl md:text-4xl font-medium tracking-tight leading-[1.1] text-primary">
                            Nigeria Security News Hub</span>
                        </h1>
                        <p class="text-gray-500 text-lg leading-relaxed max-w-md font-medium">
                            Stay informed on the latest security development across Nigeria.
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
                                <span class="text-[9px] uppercase text-gray-500 font-semibold tracking-widest">
                                    Incidents this week
                                </span>
                            </div>

                            {{-- High Risk Alerts --}}
                            <div class="space-y-1 border-x border-gray-100">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($highRiskAlerts) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-semibold tracking-widest">
                                    High risk alerts
                                </span>
                            </div>

                            {{-- States Affected --}}
                            <div class="space-y-1">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($statesAffected) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-semibold tracking-widest">
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
                <div class="mb-6 flex justify-start">
                    <form method="GET" action="{{ url()->current() }}" class="relative">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fa-solid fa-filter text-gray-400"></i>
                            </div>

                            <select name="region" onchange="this.form.submit()"
                                class="block w-full p-3 pl-10 text-sm text-white border border-white/10 rounded-lg bg-[#162536] focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                                <option value="">All Regions</option>

                                {{-- Loop through the Region Map Keys --}}
                                @foreach (array_keys($regionMap) as $regionName)
                                    <option value="{{ $regionName }}"
                                        {{ request('region') == $regionName ? 'selected' : '' }}>
                                        {{ $regionName }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </form>
                </div>
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



        <section class="relative w-full min-h-[200px] flex items-center bg-cover bg-center overflow-hidden"
            style="background-image: url('{{ asset('images/banner.png') }}');">

            <div class="absolute inset-0 bg-[#0F172A]/50 mix-blend-multiply"></div>

            <div class="relative z-10 container mx-auto px-6 py-16">
                <div class="max-w-3xl mx-auto space-y-6">

                    <div class="space-y-2">
                        <span class="block text-white/90 font-semibold tracking-[0.2em] text-xs md:text-sm uppercase">
                            2018 – 2024 Report
                        </span>

                        <h1 class="text-2xl md:text-3xl font-semibold text-white leading-tight">
                            Security In Nigeria Over the Past 7 Years
                        </h1>
                    </div>

                    <div class="text-base text-white font-medium leading-relaxed space-y-2">
                        <p>
                            Nigeria's security landscape from 2018 to 2024 reveals a
                            counterintuitive reality - incidents increased significantly by 160.8%,
                            yet annual deaths have fallen by 41.0%. This report, based
                            on 25,945 verified incidents, documents how Nigeria's insecurity
                            has transformed from concentrated, high-casualty terrorism
                            to widespread, lower-intensity criminality.
                        </p>

                    </div>

                    <div class="pt-4 flex flex-wrap justify-start gap-6">

                        {{-- Button 1: Download Report --}}
                        <a href="{{ route('reports.download') }}" target="_blank"
                            class="inline-flex items-center gap-4 group">
                            <div
                                class="w-10 h-10 rounded-full bg-white flex items-center justify-center transition-transform group-hover:scale-110">
                                <i class="fa-solid fa-arrow-right text-black text-lg"></i>
                            </div>

                            <span class="text-white font-medium tracking-wide uppercase text-sm group-hover:underline">
                                Download full report
                            </span>
                        </a>

                        {{-- Button 2: View All Reports (New) --}}
                        {{-- Replace 'reports.index' with your actual route name --}}
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-4 group">
                            {{-- Secondary style: Transparent with white border --}}
                            <div
                                class="w-10 h-10 rounded-full border border-white/40 bg-white/10 flex items-center justify-center transition-transform group-hover:scale-110 group-hover:bg-white group-hover:border-white">
                                {{-- Icon changes color on hover for effect --}}
                                <i
                                    class="fa-solid fa-list text-white text-lg group-hover:text-black transition-colors"></i>
                            </div>

                            <span class="text-white font-medium tracking-wide uppercase text-sm group-hover:underline">
                                View all reports
                            </span>
                        </a>

                    </div>

                </div>
            </div>
        </section>
        {{-- NEW INSIGHTS SECTION --}}
        <section class="bg-white py-20 px-6 lg:px-16">
            <div class="max-w-7xl mx-auto">

                {{-- Section Header --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div class="max-w-2xl">

                        <h2 class="text-3xl md:text-4xl font-medium text-primary leading-tight">
                            Latest Insights
                        </h2>
                        <p class="text-gray-500 mt-4 text-lg">
                            In-depth analysis and expert perspectives on Nigeria's strategic security trends.
                        </p>
                    </div>

                    <a href="{{ route('insights.index') }}"
                        class="text-sm font-semibold text-gray-600 hover:text-primary transition-colors flex items-center gap-2 group">
                        View All Insights
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                {{-- Insights Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($Insights as $insight)
                        <article
                            class="relative flex flex-col p-6 rounded-3xl bg-card shadow-md border border-white/5 overflow-hidden transition-all duration-300 transform-gpu hover:shadow-2xl hover:bg-[#162536] group min-h-[200px]">

                            {{-- Text Content --}}
                            <div class="relative z-10 pointer-events-none flex-grow">
                                <span
                                    class="text-[10px] font-medium text-gray-400 uppercase tracking-[0.2em] mb-3 block">
                                    {{ $insight->category->name ?? 'Uncategorized' }}
                                </span>

                                <h3
                                    class="text-gray-300 font-medium text-lg leading-snug group-hover:text-blue-400 transition-colors duration-300">
                                    {{ $insight->title }}
                                </h3>
                            </div>

                            {{-- Button - Now tightly coupled with the text --}}
                            <div class="mt-6 relative z-10">
                                <a href="{{ route('insight.show', $insight->slug ?? $insight->id) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white transition-all duration-300 shadow-md group-hover:bg-blue-500 group-hover:scale-110 after:absolute after:inset-0 after:content-['']">
                                    <i class="fa-solid fa-arrow-right text-base"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

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

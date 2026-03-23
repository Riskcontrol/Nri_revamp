<x-layout title="Security Intelligence Hub">

    <section class="min-h-screen bg-[#f8fafc] font-sans antialiased">

        {{-- ══════════════════════════════════════════════════════════
             WHITE HERO — stats dashboard
        ══════════════════════════════════════════════════════════ --}}
        <div class="bg-white text-primary px-6 py-16 lg:px-16 shadow-inner">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-12">
                    <div class="lg:w-1/2 space-y-4">
                        <h1 class="text-3xl md:text-4xl font-medium tracking-tight leading-[1.1] text-primary">
                            Nigeria Security News Hub
                        </h1>
                        <p class="text-gray-500 text-lg leading-relaxed max-w-md font-medium">
                            Stay informed on the latest security development across Nigeria.
                        </p>
                    </div>

                    <div class="lg:w-5/12 w-full bg-white rounded-2xl p-8 border-2 border-primary shadow-md">
                        <div class="flex flex-col items-center justify-center mb-8">
                            <h2 class="text-sm font-semibold text-primary uppercase tracking-widest text-center">
                                Real time Security Dashboard
                            </h2>
                        </div>
                        <div class="grid grid-cols-3 gap-6 text-center">
                            <div class="space-y-1">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($totalIncidents) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-semibold tracking-widest">
                                    Incidents this week
                                </span>
                            </div>
                            <div class="space-y-1 border-x border-gray-100">
                                <span class="block text-4xl font-semibold text-primary">
                                    {{ number_format($highRiskAlerts) }}
                                </span>
                                <span class="text-[9px] uppercase text-gray-500 font-semibold tracking-widest">
                                    High risk alerts
                                </span>
                            </div>
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

        {{-- ══════════════════════════════════════════════════════════
             ACTIVE SECURITY ALERTS
             Only rendered when admin has toggled at least one incident
             as Breaking News via /admin/incidents.
             ≤4 alerts: static 4-column grid
             5–8 alerts: auto-scrolling touch slider with dot pagination
        ══════════════════════════════════════════════════════════ --}}
        @if ($activeAlerts->isNotEmpty())
            @php
                /*
                 * $activeAlerts — Collection from SecurityHubController::fetchActiveAlerts().
                 * Limit: 8 rows. Source: tblweeklydataentry WHERE news='Yes' JOIN tbldataentry.
                 *
                 * Each row exposes:
                 *   ->impact_label      Critical | High | Medium | Low
                 *   ->impact_class      Tailwind bg class (matches table badges)
                 *   ->card_title        caption or truncated add_notes
                 *   ->location_display  "State, LGA"
                 *   ->formatted_date    "Jan 15, 2026"
                 *   ->add_notes         brief description (card + modal summary)
                 *   ->riskindicators    incident type label
                 *   ->associated_risks  risk assessment text
                 *   ->link1             source URL
                 *   ->header_fragment   "Kidnapping in Zamfara, Anka" — shown on each card
                 */
                $alertCount = $activeAlerts->count();
                $useSlider = $alertCount > 4;

                $levelConfig = [
                    'Critical' => [
                        'badge_bg' => 'bg-red-800',
                        'badge_text' => 'text-white',
                        'btn_bg' => 'bg-red-800 hover:bg-red-700',
                        'btn_text' => 'text-white',
                        'icon_color' => 'text-red-400',
                    ],
                    'High' => [
                        'badge_bg' => 'bg-red-600',
                        'badge_text' => 'text-white',
                        'btn_bg' => 'bg-red-600 hover:bg-red-500',
                        'btn_text' => 'text-white',
                        'icon_color' => 'text-red-400',
                    ],
                    'Medium' => [
                        'badge_bg' => 'bg-orange-500',
                        'badge_text' => 'text-white',
                        'btn_bg' => 'bg-orange-500 hover:bg-orange-700',
                        'btn_text' => 'text-white',
                        'icon_color' => 'text-orange-400',
                    ],
                    'Low' => [
                        'badge_bg' => 'bg-emerald-500',
                        'badge_text' => 'text-white',
                        'btn_bg' => 'bg-emerald-500 hover:bg-emerald-600',
                        'btn_text' => 'text-white',
                        'icon_color' => 'text-emerald-400',
                    ],
                ];
            @endphp

            <div class="bg-primary px-6 pt-12 pb-6 lg:px-16">
                <div class="max-w-7xl mx-auto">

                    {{-- ── Section header ────────────────────────────────────────── --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-70"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                            </span>
                            <h2 class="text-white font-black text-[11px] uppercase tracking-[0.22em]">
                                Active Security Alerts
                            </h2>
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded-full bg-white/[0.07] text-gray-400">
                                {{ $alertCount }}
                            </span>
                        </div>

                        <a href="#incidents-table"
                            class="flex-shrink-0 text-gray-400 hover:text-white transition-colors text-[11px] font-semibold uppercase tracking-widest flex items-center gap-1.5 group">
                            View All
                            <i
                                class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                        </a>
                    </div>

                    {{-- ── Cards: static grid (≤4) or slider (5–8) ───────────────── --}}
                    @if ($useSlider)
                        {{-- SLIDER — wrapper clips overflow, track scrolls horizontally --}}
                        <div class="relative">
                            <div id="alertsTrack"
                                class="flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-3"
                                style="scrollbar-width:none;-ms-overflow-style:none;">

                                @foreach ($activeAlerts as $i => $alert)
                                    @php $cfg = $levelConfig[$alert->impact_label] ?? $levelConfig['Low']; @endphp
                                    <div
                                        class="snap-start flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[calc(25%-12px)]
                                            flex flex-col rounded-2xl overflow-hidden
                                            bg-[#111E30] border border-white/10
                                            transition-all duration-300 hover:-translate-y-0.5">
                                        @include('partials.alert-card', [
                                            'alert' => $alert,
                                            'cfg' => $cfg,
                                            'i' => $i,
                                        ])
                                    </div>
                                @endforeach

                            </div>

                            {{-- Prev / Next arrow buttons --}}
                            <button onclick="slideAlerts(-1)"
                                class="hidden lg:flex absolute -left-5 top-1/2 -translate-y-1/2
                                       w-9 h-9 rounded-full bg-[#111E30] border border-white/10
                                       items-center justify-center text-gray-400 hover:text-white
                                       hover:border-white/30 transition-all z-10"
                                aria-label="Previous">
                                <svg width="14" height="14" fill="none" stroke="currentColor"
                                    stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>
                            <button onclick="slideAlerts(1)"
                                class="hidden lg:flex absolute -right-5 top-1/2 -translate-y-1/2
                                       w-9 h-9 rounded-full bg-[#111E30] border border-white/10
                                       items-center justify-center text-gray-400 hover:text-white
                                       hover:border-white/30 transition-all z-10"
                                aria-label="Next">
                                <svg width="14" height="14" fill="none" stroke="currentColor"
                                    stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>

                        {{-- Dot pagination --}}
                        <div class="flex items-center justify-center gap-1.5 mt-4" id="alertsDots">
                            @foreach ($activeAlerts as $i => $alert)
                                <button onclick="goToSlide({{ $i }})"
                                    class="alerts-dot w-1.5 h-1.5 rounded-full transition-all duration-200
                                           {{ $i === 0 ? 'bg-white/70 w-3' : 'bg-white/20' }}"
                                    data-index="{{ $i }}" aria-label="Go to alert {{ $i + 1 }}">
                                </button>
                            @endforeach
                        </div>
                    @else
                        {{-- STATIC GRID — ≤4 alerts, standard 4-column layout --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($activeAlerts as $i => $alert)
                                @php $cfg = $levelConfig[$alert->impact_label] ?? $levelConfig['Low']; @endphp
                                <div
                                    class="flex flex-col rounded-2xl overflow-hidden
                                        bg-[#111E30] border border-white/10
                                        transition-all duration-300 hover:-translate-y-0.5">
                                    @include('partials.alert-card', [
                                        'alert' => $alert,
                                        'cfg' => $cfg,
                                        'i' => $i,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        @endif {{-- $activeAlerts->isNotEmpty() --}}

        {{-- ══════════════════════════════════════════════════════════
             ALERT DETAIL MODAL
        ══════════════════════════════════════════════════════════ --}}
        <div id="alertModal" class="fixed inset-0 z-[9999] hidden items-center justify-center px-4 py-8"
            style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px)">

            <div
                class="w-full max-w-lg bg-[#0E1B2C] rounded-2xl border border-white/10 shadow-2xl overflow-hidden
                        max-h-[90vh] flex flex-col">

                {{-- Modal header --}}
                <div class="flex items-start justify-between p-6 border-b border-white/10 flex-shrink-0">
                    <div class="flex-1 min-w-0 pr-4">
                        <span id="modal-level-badge"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3">
                        </span>
                        <h3 id="modal-title" class="text-white font-bold text-lg leading-snug"></h3>
                    </div>
                    <button onclick="closeAlertModal()"
                        class="text-gray-400 hover:text-white transition-colors flex-shrink-0 p-1 rounded-lg hover:bg-white/5"
                        aria-label="Close">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal body — scrollable --}}
                <div class="overflow-y-auto flex-1 p-6 space-y-5">

                    {{-- Meta grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Incident Type
                            </p>
                            <p id="modal-incident-type" class="text-white text-[13px] font-medium"></p>
                        </div>
                        <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Location</p>
                            <p id="modal-location" class="text-white text-[13px] font-medium"></p>
                        </div>
                        <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Date</p>
                            <p id="modal-datetime" class="text-white text-[13px] font-medium"></p>
                        </div>
                        <div class="bg-white/[0.04] rounded-xl p-3 border border-white/[0.06]">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1">Severity</p>
                            <p id="modal-severity" class="text-[13px] font-bold"></p>
                        </div>
                    </div>

                    {{-- Summary — always add_notes --}}
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Summary</p>
                        <p id="modal-description" class="text-gray-300 text-[13.5px] leading-relaxed"></p>
                    </div>

                    {{-- Risk assessment — shown only when associated_risks is present --}}
                    <div id="modal-risk-section" class="hidden">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Risk Assessment
                        </p>
                        <p id="modal-risk" class="text-gray-300 text-[13.5px] leading-relaxed"></p>
                    </div>

                    {{-- Source link — shown only when link1 is present --}}
                    <div id="modal-source-section" class="hidden">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-2">Source</p>
                        <a id="modal-source-link" href="#" target="_blank" rel="noopener noreferrer"
                            class="text-blue-400 hover:text-blue-300 text-[13px] underline break-all transition-colors"></a>
                    </div>
                </div>

                {{-- Modal footer --}}
                <div class="px-6 py-4 border-t border-white/10 flex-shrink-0">
                    <button onclick="closeAlertModal()"
                        class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-[12px] font-semibold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             INCIDENTS TABLE
        ══════════════════════════════════════════════════════════ --}}
        <div id="incidents-table" class="py-10 px-6 lg:px-16 bg-primary">
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

                <div class="bg-white/5 border-t border-white/10 px-6 py-4 rounded-b-xl">
                    <div class="custom-pagination">
                        {{ $incidents->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             REPORT DOWNLOAD BANNER
        ══════════════════════════════════════════════════════════ --}}
        @if ($featuredReport)
            @php
                $userTier = auth()->check() ? (int) auth()->user()->tier : 0;
                $isPremium = $featuredReport->min_tier > 1;
                $isLocked = auth()->check() && $userTier < $featuredReport->min_tier;
            @endphp

            <section class="relative w-full min-h-[200px] flex items-center bg-cover bg-center overflow-hidden"
                style="background-image: url('{{ asset('images/banner.png') }}');">
                <div class="absolute inset-0 bg-[#0F172A]/50 mix-blend-multiply"></div>
                <div class="relative z-10 container mx-auto px-6 py-16">
                    <div class="max-w-3xl mx-auto space-y-6">
                        <div class="space-y-2">
                            <span
                                class="block text-white/90 font-semibold tracking-[0.2em] text-xs md:text-sm uppercase">
                                {{ $featuredReport->period }} Report
                            </span>
                            <h1 class="text-2xl md:text-3xl font-semibold text-white leading-tight">
                                {{ $featuredReport->title }}
                            </h1>
                        </div>
                        <div class="text-base text-white font-medium leading-relaxed">
                            <p>{{ $featuredReport->description }}</p>
                        </div>
                        <div class="pt-4 flex flex-wrap justify-start gap-6">
                            @guest
                                <button type="button" data-report-title="{{ $featuredReport->title }}"
                                    onclick="showGuestDownloadModal(this)"
                                    class="inline-flex items-center gap-4 group cursor-pointer bg-transparent border-none p-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-white flex items-center justify-center transition-transform group-hover:scale-110">
                                        <i class="fa-solid fa-arrow-right text-black text-lg"></i>
                                    </div>
                                    <span
                                        class="text-white font-medium tracking-wide uppercase text-sm group-hover:underline">
                                        Download full report
                                    </span>
                                </button>
                            @endguest
                            @auth
                                @if ($isLocked)
                                    <button type="button" data-report-title="{{ $featuredReport->title }}"
                                        data-min-tier="{{ $featuredReport->min_tier }}" onclick="showUpgradeModal(this)"
                                        class="inline-flex items-center gap-4 group cursor-pointer bg-transparent border-none p-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-amber-400 flex items-center justify-center transition-transform group-hover:scale-110">
                                            <i class="fa-solid fa-lock text-black text-lg"></i>
                                        </div>
                                        <span
                                            class="text-amber-300 font-medium tracking-wide uppercase text-sm group-hover:underline">
                                            Upgrade to Download
                                        </span>
                                    </button>
                                @else
                                    <a href="{{ route('reports.download', $featuredReport->id) }}"
                                        class="inline-flex items-center gap-4 group">
                                        <div
                                            class="w-10 h-10 rounded-full bg-white flex items-center justify-center transition-transform group-hover:scale-110">
                                            <i class="fa-solid fa-arrow-right text-black text-lg"></i>
                                        </div>
                                        <span
                                            class="text-white font-medium tracking-wide uppercase text-sm group-hover:underline">
                                            Download full report
                                        </span>
                                    </a>
                                @endif
                            @endauth
                            @if ($isPremium)
                                <span
                                    class="self-center text-xs font-bold text-amber-300 bg-amber-500/20 border border-amber-400/30 px-3 py-1 rounded-full flex items-center gap-1">
                                    <i class="fa-solid fa-star text-[9px]"></i> Premium Report
                                </span>
                            @endif
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-4 group">
                                <div
                                    class="w-10 h-10 rounded-full border border-white/40 bg-white/10 flex items-center justify-center transition-transform group-hover:scale-110 group-hover:bg-white group-hover:border-white">
                                    <i
                                        class="fa-solid fa-list text-white text-lg group-hover:text-black transition-colors"></i>
                                </div>
                                <span
                                    class="text-white font-medium tracking-wide uppercase text-sm group-hover:underline">
                                    View all reports
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             INSIGHTS SECTION
        ══════════════════════════════════════════════════════════ --}}
        <section class="bg-white py-20 px-6 lg:px-16">
            <div class="max-w-7xl mx-auto">
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

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($Insights as $insight)
                        <article
                            class="relative flex flex-col p-6 rounded-3xl bg-card shadow-md border border-white/5 overflow-hidden transition-all duration-300 transform-gpu hover:shadow-2xl hover:bg-[#162536] group min-h-[200px]">
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

    {{-- Tier lock modal --}}
    <x-tier-lock-modal />

    {{-- Guest modal --}}
    <div id="guestDownloadModal"
        class="fixed inset-0 z-[9999] hidden bg-black/60 backdrop-blur-sm items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl bg-[#0F1720] border border-white/10 shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-white/10 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-white text-lg font-semibold">Sign in to Download</h3>
                    <p id="guestModalReportTitle" class="text-gray-400 text-sm mt-1"></p>
                </div>
                <button onclick="closeGuestModal()" class="text-gray-400 hover:text-white transition"
                    aria-label="Close">✕</button>
            </div>
            <div class="p-5">
                <p class="text-gray-300 text-sm leading-relaxed">
                    Create a free account to access security reports and data-driven insights on Nigeria's risk
                    landscape.
                </p>
            </div>
            <div class="p-5 border-t border-white/10 flex gap-3">
                <a href="{{ route('login') }}"
                    class="flex-1 rounded-lg bg-white/10 hover:bg-white/15 text-white text-sm font-semibold py-2.5 transition text-center">
                    Log In
                </a>
                <a href="{{ route('register') }}"
                    class="flex-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 transition text-center">
                    Register Free
                </a>
            </div>
        </div>
    </div>

    <style>
        /* Hide scrollbar on the slider track across all browsers */
        #alertsTrack::-webkit-scrollbar {
            display: none;
        }

        #alertsTrack {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .custom-pagination nav div div span,
        .custom-pagination nav div div a {
            @apply border-black text-[11px] font-semibold uppercase tracking-widest transition-all;
        }

        .custom-pagination nav div div span[aria-current="page"] span {
            @apply bg-[#0a1628] border-black text-white !important;
        }

        .custom-pagination nav div div a:hover {
            @apply bg-blue-600 text-white border-black !important;
        }
    </style>

    <script>
        const ENTERPRISE_URL = @json(route('enterprise-access.create'));

        // ── Active alerts data (PHP → JS) ─────────────────────────────────────
        const ALERTS = @json($activeAlerts ?? []);

        const LEVEL_COLORS = {
            Critical: {
                badge: 'bg-red-800 text-white',
                severity: 'text-red-500'
            },
            High: {
                badge: 'bg-red-600 text-white',
                severity: 'text-red-500'
            },
            Medium: {
                badge: 'bg-orange-500 text-white',
                severity: 'text-orange-400'
            },
            Low: {
                badge: 'bg-emerald-500 text-white',
                severity: 'text-emerald-400'
            },
        };

        // ── Slider ────────────────────────────────────────────────────────────
        const track = document.getElementById('alertsTrack');
        let currentSlide = 0;

        function getCardWidth() {
            if (!track) return 0;
            const first = track.querySelector(':scope > *');
            return first ? first.offsetWidth + 16 : 0; // card + gap-4 (16px)
        }

        function goToSlide(index) {
            if (!track) return;
            const total = ALERTS.length;
            currentSlide = Math.max(0, Math.min(index, total - 1));
            track.scrollTo({
                left: currentSlide * getCardWidth(),
                behavior: 'smooth'
            });
            updateDots(currentSlide);
        }

        function slideAlerts(dir) {
            goToSlide(currentSlide + dir);
        }

        function updateDots(active) {
            document.querySelectorAll('.alerts-dot').forEach((dot, i) => {
                dot.classList.toggle('bg-white/70', i === active);
                dot.classList.toggle('w-3', i === active);
                dot.classList.toggle('bg-white/20', i !== active);
                dot.classList.toggle('w-1.5', i !== active);
            });
        }

        // Update active dot on native scroll (touch/trackpad)
        if (track) {
            track.addEventListener('scroll', () => {
                const w = getCardWidth();
                if (w > 0) {
                    currentSlide = Math.round(track.scrollLeft / w);
                    updateDots(currentSlide);
                }
            }, {
                passive: true
            });
        }

        // ── Alert modal ───────────────────────────────────────────────────────
        function openAlertModal(index) {
            const a = ALERTS[index];
            if (!a) return;

            const cfg = LEVEL_COLORS[a.impact_label] ?? LEVEL_COLORS.Low;

            // Level badge
            const badge = document.getElementById('modal-level-badge');
            badge.className =
                `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3 ${cfg.badge}`;
            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-white/70 inline-block"></span>${a.impact_label}`;

            // Core fields
            document.getElementById('modal-title').textContent = a.card_title ?? '';
            document.getElementById('modal-incident-type').textContent = a.riskindicators ?? '—';
            document.getElementById('modal-location').textContent = a.location_display ?? a.location ?? '—';
            document.getElementById('modal-datetime').textContent = a.formatted_date ?? 'Date unavailable';

            // Severity
            const severityEl = document.getElementById('modal-severity');
            severityEl.textContent = a.impact_label;
            severityEl.className = `text-[13px] font-bold ${cfg.severity}`;

            // Summary — always add_notes (the brief field from the upload)
            document.getElementById('modal-description').textContent = a.add_notes || '—';

            // Risk assessment — show only when populated
            const riskSection = document.getElementById('modal-risk-section');
            const riskEl = document.getElementById('modal-risk');
            if (a.associated_risks) {
                riskEl.textContent = a.associated_risks;
                riskSection.classList.remove('hidden');
            } else {
                riskSection.classList.add('hidden');
            }

            // Source link — show only when populated
            const sourceSection = document.getElementById('modal-source-section');
            const sourceLink = document.getElementById('modal-source-link');
            if (a.link1) {
                sourceLink.href = a.link1;
                sourceLink.textContent = a.link1;
                sourceSection.classList.remove('hidden');
            } else {
                sourceSection.classList.add('hidden');
            }

            const modal = document.getElementById('alertModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
            document.getElementById('alertModal').classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.getElementById('alertModal').addEventListener('click', function(e) {
            if (e.target === this) closeAlertModal();
        });

        // ── Guest modal ───────────────────────────────────────────────────────
        function showGuestDownloadModal(btn) {
            document.getElementById('guestModalReportTitle').textContent = 'Report: ' + btn.getAttribute(
                'data-report-title');
            const modal = document.getElementById('guestDownloadModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeGuestModal() {
            const modal = document.getElementById('guestDownloadModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.getElementById('guestDownloadModal').addEventListener('click', function(e) {
            if (e.target === this) closeGuestModal();
        });

        // ── Tier lock modal ───────────────────────────────────────────────────
        function openTierLockModal(payload = {}) {
            const modal = document.getElementById('tierLockModal');
            if (!modal) return;
            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };
            const setHref = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.href = val;
            };
            set('tierLockTitle', payload.title || 'Premium Access Required');
            set('tierLockSubtitle', payload.subtitle || 'This report is locked on your current plan.');
            set('tierLockMessage', payload.message || '');
            set('tierLockLabel1', payload.label1 || 'Locked report');
            set('tierLockLabel2', payload.label2 || 'Required tier');
            set('tierLockLocation', payload.locked_item || '');
            set('tierLockWhen', payload.when || '');
            set('tierLockFooterText', payload.footer || 'Contact us to upgrade your plan.');
            setHref('tierLockCta', payload.cta_url || ENTERPRISE_URL);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeTierLockModal() {
            const modal = document.getElementById('tierLockModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function showUpgradeModal(btn) {
            openTierLockModal({
                title: 'Premium Access Required',
                subtitle: 'This report is not available on your current plan.',
                message: 'Upgrade your plan to download premium security reports.',
                label1: 'Locked report',
                locked_item: btn.getAttribute('data-report-title'),
                label2: 'Required tier',
                when: 'Tier ' + btn.getAttribute('data-min-tier') + '+',
                footer: 'Contact us to upgrade your plan.',
                cta_url: ENTERPRISE_URL,
            });
        }

        document.getElementById('tierLockClose')?.addEventListener('click', closeTierLockModal);
        document.getElementById('tierLockOk')?.addEventListener('click', closeTierLockModal);
        document.getElementById('tierLockModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeTierLockModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAlertModal();
                closeTierLockModal();
                closeGuestModal();
            }
        });

        if (window.__TIER_LOCK_FLASH__) {
            openTierLockModal(window.__TIER_LOCK_FLASH__);
        }
    </script>

</x-layout>

<x-layout title="All Reports – Nigeria Risk Index">

    {{-- 1. HEADER SECTION --}}
    <section class="relative bg-[#0F172A] py-16 px-6 overflow-hidden border-b border-white/5">
        <div class="relative z-10 max-w-7xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-semibold text-white mb-4">
                Security Reports
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto text-base md:text-lg leading-relaxed">
                Access verified data-driven reports on Nigeria's security landscape.
            </p>
        </div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-4xl bg-blue-600/5 blur-3xl -z-0 pointer-events-none">
        </div>
    </section>

    {{-- 2. REPORTS GRID --}}
    <section class="bg-white py-16 px-6 min-h-screen">
        <div class="max-w-7xl mx-auto">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($reports as $report)
                    @php
                        $isPremium = $report->min_tier > 1;
                        $userTier = auth()->check() ? (int) auth()->user()->tier : 0;
                        $isLocked = auth()->check() && $userTier < $report->min_tier;
                    @endphp

                    <article
                        class="bg-white rounded-md overflow-hidden shadow-sm border border-gray-200 hover:shadow-lg hover:border-card/50 transition-all duration-300 group flex flex-col h-full">

                        {{-- Card Image --}}
                        <div class="h-56 overflow-hidden relative bg-gray-100 border-b border-gray-100">
                            @if ($report->image_path)
                                <img src="{{ asset($report->image_path) }}" alt="{{ $report->title }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 {{ $isLocked ? 'opacity-70' : '' }}">
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

                            {{-- Premium lock overlay on image --}}
                            @if ($isPremium)
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none">
                                </div>
                                <div
                                    class="absolute top-4 left-4 flex items-center gap-1 bg-amber-500/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">
                                    <i class="fa-solid fa-lock text-[9px]"></i>
                                    PREMIUM
                                </div>
                            @endif
                        </div>

                        {{-- Card Content --}}
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="mb-4">
                                <span
                                    class="text-emerald-600 text-xs font-semibold uppercase tracking-wider block mb-2">
                                    {{ $report->period }}
                                </span>
                                <h4
                                    class="text-lg font-bold text-gray-900 mb-3 leading-tight group-hover:text-emerald-700 transition-colors">
                                    {{ $report->title }}
                                </h4>
                                <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
                                    {{ $report->description }}
                                </p>
                            </div>

                            {{-- Footer / Action --}}
                            <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">

                                @guest
                                    {{-- Guest: show register modal --}}
                                    <button type="button" data-report-title="{{ $report->title }}"
                                        onclick="showGuestDownloadModal(this)"
                                        class="text-sm font-semibold text-gray-700 hover:text-emerald-600 flex items-center gap-2 transition-colors cursor-pointer bg-transparent border-none p-0">
                                        <span>Download Report</span>
                                        <i class="fa-solid fa-arrow-down-long"></i>
                                    </button>
                                @endguest

                                @auth
                                    @if ($isLocked)
                                        {{-- Authenticated but tier too low: show upgrade modal --}}
                                        <button type="button" data-report-title="{{ $report->title }}"
                                            data-min-tier="{{ $report->min_tier }}" onclick="showUpgradeModal(this)"
                                            class="text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-2 transition-colors cursor-pointer bg-transparent border-none p-0">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                            <span>Upgrade to Download</span>
                                        </button>
                                    @else
                                        {{-- Authenticated with correct tier: direct download --}}
                                        <a href="{{ route('reports.download', $report->id) }}"
                                            class="text-sm font-semibold text-gray-700 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                            <span>Download Report</span>
                                            <i class="fa-solid fa-arrow-down-long"></i>
                                        </a>
                                    @endif
                                @endauth

                                {{-- Tier badge --}}
                                @if ($isPremium)
                                    <span
                                        class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full flex items-center gap-1">
                                        <i class="fa-solid fa-star text-[9px]"></i> Premium
                                    </span>
                                @else
                                    <span
                                        class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                                        Free
                                    </span>
                                @endif

                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($reports->isEmpty())
                <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-regular fa-folder-open text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No reports available at the moment.</p>
                </div>
            @endif

        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TIER LOCK MODAL (reuses existing component + same JS pattern) --}}
    {{-- ============================================================ --}}
    <x-tier-lock-modal />

    {{-- ============================================================ --}}
    {{-- GUEST MODAL                                                  --}}
    {{-- ============================================================ --}}
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

    <script>
        // ─── Constants (same pattern as Risk Map / Location Intelligence pages) ───
        const USER_TIER = @json(auth()->user()?->tier); // null if guest
        const ENTERPRISE_URL = @json(route('enterprise-access.create'));

        // ─── Guest Modal ─────────────────────────────────────────────────────────
        function showGuestDownloadModal(btn) {
            const title = btn.getAttribute('data-report-title');
            document.getElementById('guestModalReportTitle').textContent = 'Report: ' + title;
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

        // ─── Tier Lock Modal (reuses the same openTierLockModal pattern) ─────────
        function openTierLockModal(payload = {}) {
            const modal = document.getElementById('tierLockModal');
            if (!modal) return;

            const titleEl = document.getElementById('tierLockTitle');
            const subtitleEl = document.getElementById('tierLockSubtitle');
            const msgEl = document.getElementById('tierLockMessage');
            const label1El = document.getElementById('tierLockLabel1');
            const label2El = document.getElementById('tierLockLabel2');
            const lockedEl = document.getElementById('tierLockLocation');
            const whenEl = document.getElementById('tierLockWhen');
            const footerEl = document.getElementById('tierLockFooterText');
            const ctaEl = document.getElementById('tierLockCta');

            if (titleEl) titleEl.textContent = payload.title || 'Premium Access Required';
            if (subtitleEl) subtitleEl.textContent = payload.subtitle || 'This report is locked on your current plan.';
            if (msgEl) msgEl.textContent = payload.message || '';
            if (label1El) label1El.textContent = payload.label1 || 'Locked report';
            if (label2El) label2El.textContent = payload.label2 || 'Required tier';
            if (lockedEl) lockedEl.textContent = payload.locked_item || '';
            if (whenEl) whenEl.textContent = payload.when || '';
            if (footerEl) footerEl.textContent = payload.footer || 'Contact us to upgrade your plan.';
            if (ctaEl) ctaEl.href = payload.cta_url || ENTERPRISE_URL;

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

        // Called by the "Upgrade to Download" button on each locked card
        function showUpgradeModal(btn) {
            const reportTitle = btn.getAttribute('data-report-title');
            const minTier = btn.getAttribute('data-min-tier');

            openTierLockModal({
                title: 'Premium Access Required',
                subtitle: 'This report is not available on your current plan.',
                message: 'Upgrade your plan to download premium security reports and unlock full access to Nigeria Risk Index data.',
                label1: 'Locked report',
                locked_item: reportTitle,
                label2: 'Required tier',
                when: 'Tier ' + minTier + '+',
                footer: 'Contact us to upgrade your plan and gain immediate access.',
                cta_url: ENTERPRISE_URL,
            });
        }

        // Wire tier lock close buttons (same pattern as other pages)
        document.getElementById('tierLockClose')?.addEventListener('click', closeTierLockModal);
        document.getElementById('tierLockOk')?.addEventListener('click', closeTierLockModal);
        document.getElementById('tierLockModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeTierLockModal();
        });

        // Escape key closes whichever modal is open
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTierLockModal();
                closeGuestModal();
            }
        });

        // If the server redirected back with a tier_lock flash (e.g. direct URL access),
        // open the modal automatically — same pattern as Risk Map / Location Intelligence
        if (window.__TIER_LOCK_FLASH__) {
            openTierLockModal(window.__TIER_LOCK_FLASH__);
        }
    </script>

</x-layout>

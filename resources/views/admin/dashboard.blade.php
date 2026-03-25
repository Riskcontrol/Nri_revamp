<x-admin-layout>
    <x-slot:title>Dashboard</x-slot:title>

    {{-- ══════════════════════════════════════════════════════════════════════
     SECTION: Page header
══════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Operations Overview</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                Nigeria Risk Index — {{ now()->format('l, j F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-import.index') }}"
                class="flex items-center gap-2 text-sm px-4 py-2 rounded-lg font-semibold transition"
                style="background:var(--amber);color:#080E1A">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                Import Data
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
     SECTION: Global Announcement Banner Control
     Saves to Cache::forever('site_announcement'). The public layout reads
     this key and renders the red breaking-alert strip when active=true.
     No DB migration needed — cache is the single source of truth.
══════════════════════════════════════════════════════════════════════ --}}
    <div id="announcement-card" class="rounded-xl p-5 mb-6"
        style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Card header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-2.5 w-2.5">
                    @if (!empty($announcement['active']))
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-70"
                            style="background:var(--red)"></span>
                    @endif
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                        style="background:{{ !empty($announcement['active']) ? 'var(--red)' : 'var(--text-dim)' }}"></span>
                </span>
                <h2 class="font-semibold text-[14px]" style="color:var(--text-white)">Global Announcement Banner</h2>
                <span class="font-mono text-[10px] px-2 py-0.5 rounded-full"
                    style="background:{{ !empty($announcement['active']) ? 'var(--red-dim)' : 'var(--bg-raised)' }};
                         color:{{ !empty($announcement['active']) ? 'var(--red)' : 'var(--text-dim)' }}">
                    {{ !empty($announcement['active']) ? 'LIVE' : 'OFF' }}
                </span>
            </div>
            @if (!empty($announcement['active']))
                <button type="button" id="disable-banner-btn"
                    class="flex items-center gap-1.5 text-[12px] font-semibold px-3 py-1.5 rounded-lg transition"
                    style="background:var(--red-dim);color:var(--red);border:1px solid rgba(248,113,113,0.2)">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                    </svg>
                    Disable Banner
                </button>
            @endif
        </div>

        {{-- Current banner preview (shown when active) --}}
        @if (!empty($announcement['active']))
            <div class="rounded-lg px-4 py-3 mb-4 text-[12px]"
                style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)">
                <p class="font-mono text-[9px] uppercase tracking-widest mb-1" style="color:var(--red)">
                    Currently live on the site
                </p>
                <p class="font-semibold" style="color:var(--text-white)">{{ $announcement['headline'] }}</p>
                @if ($announcement['location'] || $announcement['time'])
                    <p class="font-mono text-[10px] mt-1" style="color:var(--text-muted)">
                        @if ($announcement['location'])
                            📍 {{ $announcement['location'] }}
                        @endif
                        @if ($announcement['time'])
                            · 🕐 {{ $announcement['time'] }}
                        @endif
                    </p>
                @endif
                <p class="font-mono text-[9px] mt-1.5" style="color:var(--text-dim)">
                    Set by {{ $announcement['updated_by'] ?? 'Admin' }}
                    · {{ \Carbon\Carbon::parse($announcement['updated_at'])->diffForHumans() }}
                </p>
            </div>
        @endif

        {{-- Banner compose form --}}

        {{-- Source mode toggle --}}
        <div class="flex items-center gap-2 mb-4">
            <span class="font-mono text-[9px] tracking-widest uppercase" style="color:var(--text-dim)">Source:</span>
            <button type="button" id="mode-incident" class="px-3 py-1 rounded-lg text-[11px] font-bold transition"
                style="background:var(--amber-dim);color:var(--amber);border:1px solid rgba(253,165,87,0.3)">
                Pick Incident
            </button>
            <button type="button" id="mode-manual" class="px-3 py-1 rounded-lg text-[11px] font-bold transition"
                style="background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border)">
                Manual Entry
            </button>
        </div>

        {{-- Incident picker panel --}}
        <div id="incident-picker-panel" class="mb-4 rounded-xl overflow-hidden"
            style="background:var(--bg-raised);border:1px solid var(--border)">

            <div class="p-3 border-b" style="border-color:var(--border)">
                <input type="text" id="inc-search" placeholder="Search by caption, location, event ID…"
                    class="w-full px-3 py-2 rounded-lg text-[12px] outline-none"
                    style="background:var(--bg-base);border:1px solid var(--border);color:var(--text-head)">
            </div>

            <div id="inc-list" style="max-height:220px;overflow-y:auto">
                @php
                    $recentForBanner = \Illuminate\Support\Facades\DB::table('tbldataentry')
                        ->select(['ID', 'eventid', 'caption', 'location', 'lga', 'impact', 'eventdateToUse'])
                        ->whereNotNull('caption')
                        ->where('caption', '!=', '')
                        ->orderByDesc('ID')
                        ->limit(30)
                        ->get();
                @endphp

                {{-- Empty-search placeholder — hidden once user types --}}
                <div id="inc-placeholder" class="px-4 py-8 text-center text-[12px]" style="color:var(--text-dim)">
                    Type above to search incidents…
                </div>

                @forelse($recentForBanner as $inc)
                    {{-- Rows start hidden; JS reveals matching ones on search input --}}
                    <div class="inc-row px-4 py-3 cursor-pointer transition border-b"
                        style="border-color:var(--border);display:none" data-eventid="{{ $inc->eventid }}"
                        data-caption="{{ $inc->caption }}"
                        data-location="{{ $inc->location }}{{ $inc->lga ? ', ' . $inc->lga : '' }}"
                        data-impact="{{ strtolower($inc->impact ?? 'critical') }}"
                        onmouseover="this.style.background='var(--bg-card)'"
                        onmouseout="this.style.background='transparent'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-[12.5px] font-semibold truncate" style="color:var(--text-white)">
                                    {{ $inc->caption }}</p>
                                <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">
                                    {{ $inc->location }}{{ $inc->lga ? ' · ' . $inc->lga : '' }}
                                    @if ($inc->eventdateToUse)
                                        · {{ \Carbon\Carbon::parse($inc->eventdateToUse)->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                            <span class="font-mono text-[9px] px-2 py-0.5 rounded flex-shrink-0"
                                style="{{ match (strtolower($inc->impact ?? '')) {
                                    'high' => 'background:var(--red-dim);color:var(--red)',
                                    'medium' => 'background:rgba(251,191,36,0.1);color:#FBBF24',
                                    'low' => 'background:var(--green-dim);color:var(--green)',
                                    default => 'background:var(--bg-raised);color:var(--text-dim)',
                                } }}">
                                {{ $inc->impact ?? '—' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-[12px]" style="color:var(--text-dim)">No incidents found.</p>
                @endforelse
            </div>

            <div id="inc-selected-bar" class="hidden px-4 py-2.5"
                style="background:var(--amber-dim);border-top:1px solid rgba(253,165,87,0.2)">
                <p class="font-mono text-[10px]" style="color:var(--amber)">
                    ✓ Selected: <span id="inc-selected-caption" class="font-semibold"></span>
                </p>
            </div>
        </div>

        {{-- Hidden eventid -- stores the linked incident --}}
        <input type="hidden" id="ann-eventid" value="{{ $announcement['eventid'] ?? '' }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

            {{-- Headline --}}
            <div class="lg:col-span-2">
                <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                    style="color:var(--text-dim)">
                    Alert Headline *
                </label>
                <input type="text" id="ann-headline" maxlength="160"
                    value="{{ $announcement['headline'] ?? '' }}"
                    placeholder="e.g. Active Terrorist Attack — Maiduguri, Borno State"
                    class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                    style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
            </div>

            {{-- Impact level --}}
            <div>
                <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                    style="color:var(--text-dim)">
                    Impact Level
                </label>
                <select id="ann-impact" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                    style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                    <option value="critical"
                        {{ ($announcement['impact_level'] ?? 'critical') === 'critical' ? 'selected' : '' }}>Critical
                    </option>
                    <option value="high" {{ ($announcement['impact_level'] ?? '') === 'high' ? 'selected' : '' }}>
                        High
                    </option>
                    <option value="medium" {{ ($announcement['impact_level'] ?? '') === 'medium' ? 'selected' : '' }}>
                        Medium
                    </option>
                </select>
            </div>

            {{-- Location --}}
            <div>
                <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                    style="color:var(--text-dim)">
                    Location (optional)
                </label>
                <input type="text" id="ann-location" maxlength="100"
                    value="{{ $announcement['location'] ?? '' }}" placeholder="e.g. Maiduguri, Borno State, Nigeria"
                    class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                    style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
            </div>

            {{-- Time --}}
            <div>
                <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                    style="color:var(--text-dim)">
                    Time (optional)
                </label>
                <input type="text" id="ann-time" maxlength="60" value="{{ $announcement['time'] ?? '' }}"
                    placeholder="e.g. 14:30 WAT (13:30 UTC)"
                    class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                    style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
            </div>

            {{-- Go Live button --}}
            <div class="flex items-end">
                <button type="button" id="save-banner-btn"
                    class="w-full px-4 py-2 rounded-lg text-[13px] font-bold transition flex items-center justify-center gap-2"
                    style="background:var(--red);color:#fff">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                    </svg>
                    Go Live
                </button>
            </div>
        </div>

        {{-- Toast --}}
        <div id="ann-toast" class="hidden mt-3 rounded-lg px-3 py-2 text-[12px] font-medium"
            style="border:1px solid var(--border)"></div>
    </div>

    <script>
        (function() {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            // ── Mode toggle ──────────────────────────────────────────────────────────
            const modeIncBtn = document.getElementById('mode-incident');
            const modeManBtn = document.getElementById('mode-manual');
            const pickerPanel = document.getElementById('incident-picker-panel');
            const active =
                'background:var(--amber-dim);color:var(--amber);border:1px solid rgba(253,165,87,0.3);padding:4px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer';
            const passive =
                'background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border);padding:4px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer';

            function setMode(mode) {
                const isInc = mode === 'incident';
                pickerPanel.style.display = isInc ? '' : 'none';
                if (!isInc) {
                    document.getElementById('ann-eventid').value = '';
                    document.getElementById('inc-selected-bar').classList.add('hidden');
                    document.querySelectorAll('.inc-row').forEach(r => r.style.outline = '');
                } else {
                    // Reset search when opening picker
                    const searchEl = document.getElementById('inc-search');
                    if (searchEl) searchEl.value = '';
                    document.querySelectorAll('.inc-row').forEach(r => r.style.display = 'none');
                    const ph = document.getElementById('inc-placeholder');
                    if (ph) {
                        ph.textContent = 'Type above to search incidents…';
                        ph.style.display = '';
                    }
                }
                modeIncBtn.style.cssText = isInc ? active : passive;
                modeManBtn.style.cssText = !isInc ? active : passive;
            }
            modeIncBtn?.addEventListener('click', () => setMode('incident'));
            modeManBtn?.addEventListener('click', () => setMode('manual'));

            // ── Incident search filter ───────────────────────────────────────────────
            document.getElementById('inc-search')?.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                const placeholder = document.getElementById('inc-placeholder');

                if (!term) {
                    // Empty — hide all rows, restore placeholder
                    document.querySelectorAll('.inc-row').forEach(row => row.style.display = 'none');
                    if (placeholder) {
                        placeholder.textContent = 'Type above to search incidents…';
                        placeholder.style.display = '';
                    }
                    return;
                }

                if (placeholder) placeholder.style.display = 'none';
                let anyVisible = false;
                document.querySelectorAll('.inc-row').forEach(row => {
                    const haystack = (row.dataset.caption + ' ' + row.dataset.location + ' ' + row
                        .dataset.eventid).toLowerCase();
                    const show = haystack.includes(term);
                    row.style.display = show ? '' : 'none';
                    if (show) anyVisible = true;
                });

                if (!anyVisible && placeholder) {
                    placeholder.textContent = 'No incidents match your search.';
                    placeholder.style.display = '';
                }
            });

            // ── Incident row click — auto-fills headline / location / impact ─────────
            document.querySelectorAll('.inc-row').forEach(row => {
                row.addEventListener('click', function() {
                    document.querySelectorAll('.inc-row').forEach(r => r.style.outline = '');
                    this.style.outline = '2px solid var(--amber)';

                    document.getElementById('ann-eventid').value = this.dataset.eventid;
                    document.getElementById('ann-headline').value = this.dataset.caption;
                    document.getElementById('ann-location').value = this.dataset.location;

                    const imp = this.dataset.impact;
                    document.getElementById('ann-impact').value = ['high', 'medium', 'low'].includes(
                        imp) ? imp : 'critical';

                    document.getElementById('inc-selected-caption').textContent = this.dataset.caption;
                    document.getElementById('inc-selected-bar').classList.remove('hidden');
                });
            });

            // ── Toast ────────────────────────────────────────────────────────────────
            function annToast(msg, ok) {
                const el = document.getElementById('ann-toast');
                el.textContent = msg;
                el.style.background = ok ? 'var(--green-dim)' : 'var(--red-dim)';
                el.style.color = ok ? 'var(--green)' : 'var(--red)';
                el.style.borderColor = ok ? 'rgba(52,211,153,0.3)' : 'rgba(248,113,113,0.3)';
                el.classList.remove('hidden');
                clearTimeout(el._t);
                el._t = setTimeout(() => el.classList.add('hidden'), 5000);
            }

            // ── Go Live ──────────────────────────────────────────────────────────────
            document.getElementById('save-banner-btn')?.addEventListener('click', async function() {
                const headline = document.getElementById('ann-headline')?.value.trim();
                if (!headline) {
                    annToast('Headline is required.', false);
                    return;
                }

                const btn = this;
                btn.disabled = true;
                btn.textContent = 'Saving…';

                try {
                    const res = await fetch('{{ route('admin.announcement.update') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            headline,
                            location: document.getElementById('ann-location')?.value.trim(),
                            time: document.getElementById('ann-time')?.value.trim(),
                            impact_level: document.getElementById('ann-impact')?.value,
                            eventid: document.getElementById('ann-eventid')?.value.trim() ||
                                null,
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        annToast('✓ Banner is now live. Updating…', true);
                        // Reload after short delay so admin sees the toast, then the
                        // page re-renders with the LIVE badge, preview block, and
                        // Disable button — all driven by the fresh PHP cache read.
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        annToast(data.message ?? 'Failed to save.', false);
                        btn.disabled = false;
                        btn.innerHTML =
                            `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Go Live`;
                    }
                } catch (e) {
                    annToast('Request failed: ' + e.message, false);
                    btn.disabled = false;
                    btn.innerHTML =
                        `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Go Live`;
                }
            });

            // ── Disable ──────────────────────────────────────────────────────────────
            document.getElementById('disable-banner-btn')?.addEventListener('click', async function() {
                const btn = this;
                btn.disabled = true;
                btn.textContent = 'Disabling…';

                try {
                    const res = await fetch('{{ route('admin.announcement.delete') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        annToast('✓ Banner removed. Updating…', true);
                        // Reload so the page re-renders with OFF state — no preview
                        // block, no Disable button, blank inputs.
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        annToast(data.message ?? 'Failed.', false);
                        btn.disabled = false;
                        btn.textContent = 'Disable Banner';
                    }
                } catch (e) {
                    annToast('Request failed: ' + e.message, false);
                    btn.disabled = false;
                    btn.textContent = 'Disable Banner';
                }
            });
        })();
    </script>


    {{-- ══════════════════════════════════════════════════════════════════════
     SECTION: Stat cards (row 1)
══════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Total incidents --}}
        <div class="stat-card" style="--accent-color:var(--amber)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Total
                Incidents</p>
            <p class="text-3xl font-bold font-mono" style="color:var(--text-white)">
                {{ number_format($totalIncidents) }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <span class="font-mono text-[11px]" style="color:var(--text-muted)">
                    {{ number_format($incidentsThisMonth) }} this month
                </span>
                @if (!is_null($incidentDelta))
                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded"
                        style="background:{{ $incidentDelta >= 0 ? 'var(--red-dim)' : 'var(--green-dim)' }};
                             color:{{ $incidentDelta >= 0 ? 'var(--red)' : 'var(--green)' }}">
                        {{ $incidentDelta >= 0 ? '↑' : '↓' }}{{ abs($incidentDelta) }}%
                    </span>
                @endif
            </div>
            {{-- Sparkline --}}
            <div class="mt-4 flex items-end gap-0.5 h-8">
                @php $sparkMax = max($sparklineData) ?: 1; @endphp
                @foreach ($sparklineData as $i => $val)
                    @php
                        $h = $sparkMax > 0 ? round(($val / $sparkMax) * 100) : 4;
                        $h = max($h, 4);
                        $isCurrentMonth = $i + 1 == now()->month;
                    @endphp
                    <div class="flex-1 rounded-sm transition-all"
                        style="height:{{ $h }}%;
                            background:{{ $isCurrentMonth ? 'var(--amber)' : 'rgba(253,165,87,0.2)' }}">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Registered users --}}
        <div class="stat-card" style="--accent-color:var(--blue)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Registered
                Users</p>
            <p class="text-3xl font-bold font-mono" style="color:var(--text-white)">
                {{ number_format($totalUsers) }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <span class="font-mono text-[11px]" style="color:var(--text-muted)">
                    +{{ $newUsersThisMonth }} this month
                </span>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                    <div class="h-full rounded-full"
                        style="width:{{ min(($newUsersThisMonth / max($totalUsers, 1)) * 100 * 10, 100) }}%;background:var(--blue)">
                    </div>
                </div>
                <span class="font-mono text-[10px]" style="color:var(--text-dim)">growth</span>
            </div>
        </div>

        {{-- Insights published --}}
        <div class="stat-card" style="--accent-color:var(--green)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Published
                Insights</p>
            <p class="text-3xl font-bold font-mono" style="color:var(--text-white)">
                {{ number_format($totalInsights) }}
            </p>
            <div class="mt-2">
                <a href="{{ route('admin.insights.index') }}" class="font-mono text-[11px] transition"
                    style="color:var(--green)">
                    Manage insights →
                </a>
            </div>
            <div class="mt-4 w-8 h-8 rounded-full flex items-center justify-center"
                style="background:var(--green-dim)">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" style="color:var(--green)">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
            </div>
        </div>

        {{-- Enterprise / access requests --}}
        <div class="stat-card"
            style="--accent-color:{{ $pendingEnterpriseRequests > 0 ? 'var(--amber)' : 'var(--border-lit)' }}">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Enterprise
                Requests</p>
            <p class="text-3xl font-bold font-mono"
                style="color:{{ $pendingEnterpriseRequests > 0 ? 'var(--amber)' : 'var(--text-white)' }}">
                {{ $pendingEnterpriseRequests }}
            </p>
            <div class="mt-2">
                <span class="font-mono text-[11px]" style="color:var(--text-muted)">
                    {{ $pendingEnterpriseRequests === 0 ? 'No pending requests' : 'Awaiting review' }}
                </span>
            </div>
            @if ($pendingEnterpriseRequests > 0)
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full animate-pulse"
                        style="background:var(--amber)"></span>
                    <span class="font-mono text-[10px]" style="color:var(--amber)">Action required</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
     SECTION: Two-column middle row
══════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- Top states (takes 1 col) --}}
        <div class="stat-card" style="--accent-color:var(--blue-dim)">
            <div class="flex items-center justify-between mb-5">
                <p class="font-mono text-[10px] tracking-widest uppercase" style="color:var(--text-dim)">
                    Top States — {{ $currentYear }}
                </p>
                <span class="font-mono text-[10px]" style="color:var(--text-dim)">Incidents</span>
            </div>

            @forelse($topStates as $row)
                @php $pct = $topStates->first()->total > 0 ? round(($row->total / $topStates->first()->total) * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[13px] font-medium"
                            style="color:var(--text-head)">{{ $row->state }}</span>
                        <span class="font-mono text-[12px]"
                            style="color:var(--text-muted)">{{ number_format($row->total) }}</span>
                    </div>
                    <div class="h-1 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                        <div class="h-full rounded-full" style="width:{{ $pct }}%;background:var(--blue)">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm" style="color:var(--text-dim)">No incidents recorded for {{ $currentYear }}.</p>
            @endforelse
        </div>

        {{-- Top risk factors (takes 1 col) --}}
        <div class="stat-card" style="--accent-color:var(--red-dim)">
            <div class="flex items-center justify-between mb-5">
                <p class="font-mono text-[10px] tracking-widest uppercase" style="color:var(--text-dim)">
                    Risk Factors — {{ $currentYear }}
                </p>
                <span class="font-mono text-[10px]" style="color:var(--text-dim)">Count</span>
            </div>

            @forelse($topFactors as $row)
                @php $pct = $maxFactor > 0 ? round(($row->total / $maxFactor) * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[12.5px] font-medium truncate max-w-[70%]"
                            style="color:var(--text-head)">{{ $row->factor }}</span>
                        <span class="font-mono text-[12px]"
                            style="color:var(--text-muted)">{{ number_format($row->total) }}</span>
                    </div>
                    <div class="h-1 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                        <div class="h-full rounded-full" style="width:{{ $pct }}%;background:var(--red)">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm" style="color:var(--text-dim)">No data for {{ $currentYear }}.</p>
            @endforelse
        </div>

        {{-- Recent imports (takes 1 col) --}}
        <div class="stat-card" style="--accent-color:var(--green-dim)">
            <div class="flex items-center justify-between mb-5">
                <p class="font-mono text-[10px] tracking-widest uppercase" style="color:var(--text-dim)">Recent
                    Imports</p>
                <a href="{{ route('admin.data-import.index') }}" class="font-mono text-[10px] transition"
                    style="color:var(--green)">
                    All imports →
                </a>
            </div>

            @forelse($recentImports as $imp)
                <div class="flex items-start gap-3 pb-3 mb-3" style="border-bottom:1px solid var(--border)">
                    {{-- Status dot --}}
                    <div class="mt-0.5 w-2 h-2 rounded-full flex-shrink-0"
                        style="background:{{ $imp->status === 'completed' && $imp->rows_failed == 0 ? 'var(--green)' : ($imp->status === 'completed' ? 'var(--amber)' : 'var(--red)') }};
                        margin-top:4px">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12.5px] font-medium truncate" style="color:var(--text-head)"
                            title="{{ $imp->sheet_name }}">
                            {{ $imp->sheet_name }}
                        </p>
                        <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">
                            {{ $imp->created_at ? \Carbon\Carbon::parse($imp->created_at)->diffForHumans() : '—' }}
                            &bull;
                            <span style="color:var(--green)">{{ $imp->rows_inserted }}↑</span>
                            @if ($imp->rows_failed > 0)
                                &bull; <span style="color:var(--red)">{{ $imp->rows_failed }}✕</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.data-import.show', $imp->id) }}"
                        class="text-[11px] flex-shrink-0 transition" style="color:var(--text-dim)">→</a>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-[13px]" style="color:var(--text-dim)">No imports yet.</p>
                    <a href="{{ route('admin.data-import.index') }}" class="text-[12px] mt-2 inline-block"
                        style="color:var(--amber)">
                        Upload your first file →
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
     SECTION: Recent incidents table
══════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border)">
            <div>
                <h2 class="font-semibold text-[15px]" style="color:var(--text-white)">Recent Incidents</h2>
                <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">Latest entries from
                    tblweeklydataentry</p>
            </div>
            <a href="{{ route('admin.data-import.index') }}"
                class="flex items-center gap-1.5 text-[12px] px-3 py-1.5 rounded-lg transition"
                style="color:var(--text-muted);background:var(--bg-raised);border:1px solid var(--border)">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                Import More
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full data-table">
                <thead>
                    <tr>
                        <th class="text-left">Event ID</th>
                        <th class="text-left">Caption</th>
                        <th class="text-left">State</th>
                        <th class="text-left">Risk Factor</th>
                        <th class="text-left">Date</th>
                        <th class="text-right">Deaths</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentIncidents as $incident)
                        <tr>
                            <td>
                                <span class="font-mono text-[11px]" style="color:var(--text-dim)">
                                    {{ substr($incident->eventid, 0, 14) }}
                                </span>
                            </td>
                            <td>
                                <span class="block max-w-xs truncate text-[13px]" style="color:var(--text-head)"
                                    title="{{ $incident->caption }}">
                                    {{ $incident->caption }}
                                </span>
                            </td>
                            <td>
                                <span class="text-[13px]"
                                    style="color:var(--text-body)">{{ $incident->location }}</span>
                            </td>
                            <td>
                                @if ($incident->riskfactor)
                                    <span class="inline-block font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--blue-dim);color:var(--blue)">
                                        {{ Str::limit($incident->riskfactor, 22) }}
                                    </span>
                                @else
                                    <span style="color:var(--text-dim)">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-mono text-[12px]" style="color:var(--text-muted)">
                                    {{ $incident->datecorrected }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if ($incident->Casualties_count)
                                    <span class="font-mono text-[12px]" style="color:var(--red)">
                                        {{ $incident->Casualties_count }}
                                    </span>
                                @else
                                    <span class="font-mono text-[12px]" style="color:var(--text-dim)">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12" style="color:var(--text-dim)">
                                <p class="text-sm mb-2">No incidents in the database yet.</p>
                                <a href="{{ route('admin.data-import.index') }}" style="color:var(--amber)"
                                    class="text-sm">
                                    Import incident data →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>

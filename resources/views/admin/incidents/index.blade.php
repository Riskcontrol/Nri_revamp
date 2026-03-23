<x-admin-layout>
    <x-slot:title>All Incidents</x-slot:title>

    {{-- ══════════════════════════════════════════════════════════════
     TOAST
══════════════════════════════════════════════════════════════ --}}
    <div id="toast"
        class="fixed bottom-6 right-6 z-[9999] hidden items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium transition-all"
        style="background:var(--bg-raised);border:1px solid var(--border);min-width:300px;max-width:420px">
        <span id="toast-icon" class="text-base flex-shrink-0"></span>
        <span id="toast-msg" class="leading-snug" style="color:var(--text-head)"></span>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
     CONFIRM MODAL
══════════════════════════════════════════════════════════════ --}}
    <div id="confirmModal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-4"
        style="background:rgba(0,0,0,0.72);backdrop-filter:blur(4px)">
        <div class="w-full max-w-md rounded-2xl overflow-hidden"
            style="background:var(--bg-card);border:1px solid var(--border-lit);box-shadow:0 24px 64px rgba(0,0,0,0.6)">

            <div class="flex items-start gap-4 p-6" style="border-bottom:1px solid var(--border)">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:var(--red-dim)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" style="color:var(--red)">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v6" />
                        <path d="M14 11v6" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="font-bold text-[16px]" style="color:var(--text-white)">Confirm Delete
                    </h3>
                    <p id="modal-subtitle" class="text-[13px] mt-0.5" style="color:var(--text-muted)"></p>
                </div>
            </div>

            <div class="p-6">
                <p id="modal-body" class="text-[13px] leading-relaxed mb-4" style="color:var(--text-body)"></p>
                <div class="rounded-xl p-4 font-mono text-[12px]"
                    style="background:var(--bg-raised);border:1px solid var(--border)">
                    <div class="flex justify-between mb-1">
                        <span style="color:var(--text-dim)">tbldataentry</span>
                        <span id="modal-count-data" class="font-semibold" style="color:var(--red)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--text-dim)">tblweeklydataentry</span>
                        <span id="modal-count-weekly" class="font-semibold" style="color:var(--red)"></span>
                    </div>
                </div>
                <p class="font-mono text-[11px] mt-3" style="color:var(--text-dim)">⚠ This cannot be undone.</p>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Cancel
                </button>
                <button type="button" id="modal-confirm-btn"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition"
                    style="background:var(--red);color:#080E1A">
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">All Incidents</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                <span class="font-mono">{{ number_format($totalIncidents) }}</span> total rows ·
                <span class="font-mono" style="color:var(--amber)">{{ number_format($breakingNewsCount) }}</span>
                breaking news
            </p>
        </div>
        {{-- Bulk action bar — visible only when rows selected --}}
        <div id="bulk-bar" class="hidden items-center gap-3 px-4 py-2 rounded-xl"
            style="background:var(--bg-card);border:1px solid var(--border)">
            <span id="bulk-count" class="font-mono text-[12px]" style="color:var(--amber)">0 selected</span>
            <button type="button" onclick="bulkDelete()"
                class="flex items-center gap-1.5 text-[12px] font-semibold px-3 py-1.5 rounded-lg transition"
                style="background:var(--red-dim);color:var(--red);border:1px solid rgba(248,113,113,0.2)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14H6L5 6" />
                </svg>
                Delete Selected
            </button>
            <button type="button" onclick="clearSelection()" class="text-[12px] transition"
                style="color:var(--text-dim)">
                Clear
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
     FILTERS
══════════════════════════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('admin.incidents.index') }}"
        class="rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end"
        style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Search --}}
        <div class="flex-1 min-w-[200px]">
            <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5" style="color:var(--text-dim)">
                Search
            </label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2" width="12" height="12" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim)">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Caption, location, event ID, risk factor…"
                    class="w-full pl-8 pr-3 py-2 rounded-lg text-[13px] outline-none transition"
                    style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
            </div>
        </div>

        {{-- State --}}
        <div class="min-w-[140px]">
            <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5" style="color:var(--text-dim)">
                State
            </label>
            <select name="location" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                <option value="">All states</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>
                        {{ $loc }}</option>
                @endforeach
            </select>
        </div>

        {{-- Year --}}
        <div class="min-w-[100px]">
            <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5" style="color:var(--text-dim)">
                Year
            </label>
            <select name="year" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                <option value="">All years</option>
                @foreach ($years as $yr)
                    <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>
                        {{ $yr }}</option>
                @endforeach
            </select>
        </div>

        {{-- Impact --}}
        <div class="min-w-[110px]">
            <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5" style="color:var(--text-dim)">
                Impact
            </label>
            <select name="impact" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                <option value="">Any</option>
                <option value="High" {{ request('impact') === 'High' ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ request('impact') === 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ request('impact') === 'Low' ? 'selected' : '' }}>Low</option>
            </select>
        </div>

        {{-- Affected Industry --}}
        <div class="min-w-[160px]">
            <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5" style="color:var(--text-dim)">
                Industry
            </label>
            <select name="affected_industry" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                <option value="">All industries</option>
                @foreach ($industries as $ind)
                    <option value="{{ $ind }}"
                        {{ request('affected_industry') === $ind ? 'selected' : '' }}>
                        {{ Str::limit($ind, 32) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Breaking news only --}}
        <div class="flex items-center gap-2 pb-0.5">
            <input type="checkbox" name="breaking_news" id="breaking_news" value="1"
                {{ request('breaking_news') === '1' ? 'checked' : '' }} class="w-4 h-4 rounded accent-amber-400">
            <label for="breaking_news" class="text-[13px] cursor-pointer" style="color:var(--text-muted)">
                Breaking only
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg text-[13px] font-semibold transition"
                style="background:var(--amber);color:#080E1A">
                Filter
            </button>
            @if (request()->hasAny(['search', 'location', 'year', 'impact', 'affected_industry', 'breaking_news', 'import_id']))
                <a href="{{ route('admin.incidents.index') }}" class="px-4 py-2 rounded-lg text-[13px] transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ══════════════════════════════════════════════════════════════
     DATA TABLE
══════════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Table header row --}}
        <div class="flex items-center justify-between px-5 py-3" style="border-bottom:1px solid var(--border)">
            <div class="flex items-center gap-3">
                {{-- Select all checkbox --}}
                <input type="checkbox" id="select-all" class="w-4 h-4 rounded cursor-pointer accent-amber-400"
                    title="Select all on this page">
                <p class="font-mono text-[10px]" style="color:var(--text-dim)">
                    @if ($incidents->total() > 0)
                        Showing {{ $incidents->firstItem() }}–{{ $incidents->lastItem() }}
                        of {{ number_format($incidents->total()) }} rows
                    @else
                        No rows match your filters
                    @endif
                </p>
            </div>
            <p class="font-mono text-[10px]" style="color:var(--text-dim)">
                Both tables shown · eventid links them
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full data-table" id="incidentsTable">
                <thead>
                    <tr>
                        <th class="text-center w-10">
                            <span class="sr-only">Select</span>
                        </th>
                        <th class="text-left">Event ID</th>
                        <th class="text-left">Date</th>
                        <th class="text-left">State · LGA</th>
                        <th class="text-left">Risk Factor</th>
                        <th class="text-left">Industry</th>
                        <th class="text-center">Impact</th>
                        <th class="text-center">Deaths</th>
                        <th class="text-left" style="max-width:240px">Caption</th>
                        <th class="text-center">Weekly Link</th>
                        <th class="text-center">Breaking</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    @forelse($incidents as $row)
                        <tr id="row-{{ $row->eventid }}" data-eventid="{{ $row->eventid }}"
                            class="incident-row {{ $row->news === 'Yes' ? 'breaking-row' : '' }}">

                            {{-- Checkbox --}}
                            <td class="text-center" style="padding:10px 8px">
                                <input type="checkbox"
                                    class="row-checkbox w-4 h-4 rounded cursor-pointer accent-amber-400"
                                    value="{{ $row->eventid }}">
                            </td>

                            {{-- Event ID --}}
                            <td>
                                <span class="font-mono text-[11px]" style="color:var(--text-dim)">
                                    {{ substr($row->eventid, 0, 14) }}
                                </span>
                                @if ($row->import_id)
                                    <a href="{{ route('admin.data-import.show', $row->import_id) }}"
                                        class="block font-mono text-[9px] mt-0.5 transition"
                                        style="color:var(--text-dim)" title="Import #{{ $row->import_id }}">
                                        imp#{{ $row->import_id }}
                                    </a>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="font-mono text-[11px] whitespace-nowrap" style="color:var(--text-muted)">
                                {{ $row->eventdate }}
                            </td>

                            {{-- State · LGA --}}
                            <td>
                                <span class="block text-[13px]"
                                    style="color:var(--text-head)">{{ $row->location }}</span>
                                @if ($row->lga)
                                    <span class="font-mono text-[10px]"
                                        style="color:var(--text-dim)">{{ $row->lga }}</span>
                                @endif
                            </td>

                            {{-- Risk Factor --}}
                            <td>
                                @if ($row->riskfactors)
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--blue-dim);color:var(--blue)">
                                        {{ Str::limit($row->riskfactors, 22) }}
                                    </span>
                                    @if ($row->riskindicators)
                                        <span class="block font-mono text-[9px] mt-0.5" style="color:var(--text-dim)">
                                            {{ Str::limit($row->riskindicators, 28) }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color:var(--text-dim)">—</span>
                                @endif
                            </td>

                            {{-- Affected Industry --}}
                            <td style="max-width:180px">
                                @if (!empty($row->affected_industry))
                                    <span class="block truncate font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--bg-raised);color:var(--text-muted)"
                                        title="{{ $row->affected_industry }}">
                                        {{ $row->affected_industry }}
                                    </span>
                                @else
                                    <span style="color:var(--text-dim)">—</span>
                                @endif
                            </td>

                            {{-- Impact --}}
                            <td class="text-center">
                                @php
                                    $impStyle = match ($row->impact) {
                                        'High' => 'background:var(--red-dim);color:var(--red)',
                                        'Medium' => 'background:rgba(251,191,36,0.1);color:#FBBF24',
                                        'Low' => 'background:var(--green-dim);color:var(--green)',
                                        default => 'background:var(--bg-raised);color:var(--text-dim)',
                                    };
                                @endphp
                                <span class="font-mono text-[10px] px-2 py-0.5 rounded" style="{{ $impStyle }}">
                                    {{ $row->impact ?? '—' }}
                                </span>
                            </td>

                            {{-- Deaths --}}
                            <td class="text-center font-mono text-[12px]"
                                style="color:{{ ($row->Casualties_count ?? 0) > 0 ? 'var(--red)' : 'var(--text-dim)' }}">
                                {{ $row->Casualties_count ?? '—' }}
                            </td>

                            {{-- Caption --}}
                            <td style="max-width:240px">
                                <span class="block truncate text-[13px]" style="color:var(--text-head)"
                                    title="{{ $row->caption }}">{{ $row->caption }}</span>
                            </td>

                            {{-- Weekly link status --}}
                            <td class="text-center">
                                @if ($row->weekly_id)
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--green-dim);color:var(--green)"
                                        title="tblweeklydataentry ID: {{ $row->weekly_id }}">
                                        Linked
                                    </span>
                                @else
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--bg-raised);color:var(--text-dim)"
                                        title="No row in tblweeklydataentry for this eventid">
                                        No weekly
                                    </span>
                                @endif
                            </td>

                            {{-- Breaking News toggle --}}
                            <td class="text-center">
                                <button type="button" data-eventid="{{ $row->eventid }}"
                                    data-is-news="{{ $row->news === 'Yes' ? '1' : '0' }}"
                                    onclick="toggleBreaking(this)"
                                    class="breaking-btn font-mono text-[10px] px-2 py-1 rounded-lg transition"
                                    style="{{ $row->news === 'Yes'
                                        ? 'background:var(--amber-dim);color:var(--amber);border:1px solid rgba(253,165,87,0.3)'
                                        : 'background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border)' }}"
                                    title="{{ $row->news === 'Yes' ? 'Click to remove from Breaking News' : 'Click to mark as Breaking News' }}">
                                    {{ $row->news === 'Yes' ? '★ News' : '☆ News' }}
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <button type="button"
                                    onclick="confirmDelete('{{ $row->eventid }}', {{ $row->weekly_id ? '1' : '0' }})"
                                    class="font-mono text-[11px] px-2 py-1 rounded transition"
                                    style="background:var(--red-dim);color:var(--red)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state">
                            <td colspan="12" class="text-center py-16" style="color:var(--text-dim)">
                                <svg class="mx-auto mb-3" width="28" height="28" fill="none"
                                    stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"
                                    style="color:var(--text-dim)">
                                    <path
                                        d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                    <rect x="9" y="3" width="6" height="4" rx="1" />
                                </svg>
                                <p class="text-sm">No incidents match your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="px-6 py-4" style="border-top:1px solid var(--border)">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Toast ──────────────────────────────────────────────────────────────────
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const text = document.getElementById('toast-msg');
            icon.textContent = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
            text.textContent = msg;
            t.style.borderColor = type === 'success' ? 'var(--green)' : (type === 'error' ? 'var(--red)' : 'var(--amber)');
            t.style.color = type === 'success' ? 'var(--green)' : (type === 'error' ? 'var(--red)' : 'var(--amber)');
            t.classList.remove('hidden');
            t.classList.add('flex');
            clearTimeout(t._timer);
            t._timer = setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('flex');
            }, 4000);
        }

        // ── Modal ──────────────────────────────────────────────────────────────────
        let _pendingAction = null;

        function openModal(title, subtitle, body, countData, countWeekly, action) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-subtitle').textContent = subtitle;
            document.getElementById('modal-body').textContent = body;
            document.getElementById('modal-count-data').textContent = countData;
            document.getElementById('modal-count-weekly').textContent = countWeekly;
            _pendingAction = action;

            const m = document.getElementById('confirmModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.getElementById('confirmModal').classList.remove('flex');
            _pendingAction = null;
        }

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('modal-confirm-btn').addEventListener('click', async function() {
            if (!_pendingAction) return;
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            try {
                await _pendingAction();
            } finally {
                btn.disabled = false;
                btn.textContent = 'Delete';
                closeModal();
            }
        });

        // ── Single row delete ──────────────────────────────────────────────────────
        function confirmDelete(eventid, hasWeekly) {
            openModal(
                'Delete Incident',
                `Event: ${eventid}`,
                'This will permanently remove the incident from both database tables. The row will disappear immediately without a page refresh.',
                '1 row',
                hasWeekly ? '1 row' : 'none',
                async () => {
                    const res = await fetch('/admin/incidents/row', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            eventid
                        }),
                    });
                    const data = await res.json();

                    if (data.success) {
                        // Instant DOM removal — no reload needed
                        const row = document.getElementById('row-' + eventid);
                        if (row) {
                            row.style.transition = 'opacity 0.25s, transform 0.25s';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(12px)';
                            setTimeout(() => row.remove(), 250);
                        }
                        // Remove from selection state
                        selectedEventids.delete(eventid);
                        updateBulkBar();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message ?? 'Delete failed.', 'error');
                    }
                }
            );
        }

        // ── Breaking news toggle ───────────────────────────────────────────────────
        async function toggleBreaking(btn) {
            const eventid = btn.dataset.eventid;
            const isNews = btn.dataset.isNews === '1';

            btn.disabled = true;
            btn.textContent = '…';

            try {
                const res = await fetch('/admin/incidents/breaking', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        eventid
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    const nowIsNews = data.is_news;
                    btn.dataset.isNews = nowIsNews ? '1' : '0';
                    btn.textContent = nowIsNews ? '★ News' : '☆ News';
                    btn.style.cssText = nowIsNews ?
                        'background:var(--amber-dim);color:var(--amber);border:1px solid rgba(253,165,87,0.3);font-family:IBM Plex Mono,monospace;font-size:10px;padding:4px 8px;border-radius:8px;transition:all 0.15s;cursor:pointer' :
                        'background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border);font-family:IBM Plex Mono,monospace;font-size:10px;padding:4px 8px;border-radius:8px;transition:all 0.15s;cursor:pointer';

                    // Highlight or de-highlight the row
                    const row = document.getElementById('row-' + eventid);
                    if (row) row.classList.toggle('breaking-row', nowIsNews);

                    showToast(data.message, 'success');
                } else {
                    showToast(data.message ?? 'Toggle failed.', 'error');
                }
            } catch (err) {
                showToast('Request failed: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
            }
        }

        // ── Bulk select ────────────────────────────────────────────────────────────
        const selectedEventids = new Set();

        function updateBulkBar() {
            const bar = document.getElementById('bulk-bar');
            const count = document.getElementById('bulk-count');
            const n = selectedEventids.size;
            count.textContent = `${n} selected`;
            if (n > 0) {
                bar.classList.remove('hidden');
                bar.classList.add('flex');
            } else {
                bar.classList.add('hidden');
                bar.classList.remove('flex');
            }
        }

        // Select-all checkbox
        document.getElementById('select-all').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = checked;
                const eid = cb.value;
                if (checked) selectedEventids.add(eid);
                else selectedEventids.delete(eid);
            });
            updateBulkBar();
        });

        // Individual row checkboxes
        document.getElementById('tbody').addEventListener('change', function(e) {
            if (!e.target.classList.contains('row-checkbox')) return;
            const eid = e.target.value;
            if (e.target.checked) selectedEventids.add(eid);
            else selectedEventids.delete(eid);
            updateBulkBar();
        });

        function clearSelection() {
            selectedEventids.clear();
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all').checked = false;
            updateBulkBar();
        }

        // ── Bulk delete ────────────────────────────────────────────────────────────
        function bulkDelete() {
            const eventids = [...selectedEventids];
            if (eventids.length === 0) return;

            openModal(
                `Delete ${eventids.length} Incidents`,
                'Bulk delete — both tables',
                `This will permanently remove ${eventids.length} selected incident${eventids.length > 1 ? 's' : ''} from tbldataentry and their linked rows from tblweeklydataentry. Rows will disappear immediately.`,
                `${eventids.length} rows`,
                `up to ${eventids.length} rows`,
                async () => {
                    const res = await fetch('/admin/incidents/bulk', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            eventids
                        }),
                    });
                    const data = await res.json();

                    if (data.success) {
                        // Remove each deleted row from the DOM instantly
                        eventids.forEach(eid => {
                            const row = document.getElementById('row-' + eid);
                            if (row) {
                                row.style.transition = 'opacity 0.2s, transform 0.2s';
                                row.style.opacity = '0';
                                row.style.transform = 'translateX(12px)';
                                setTimeout(() => row.remove(), 220);
                            }
                        });
                        clearSelection();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message ?? 'Bulk delete failed.', 'error');
                    }
                }
            );
        }
    </script>

    <style>
        /* Subtle amber left-border on breaking news rows */
        .breaking-row td:first-child {
            box-shadow: inset 3px 0 0 var(--amber);
        }
    </style>

</x-admin-layout>

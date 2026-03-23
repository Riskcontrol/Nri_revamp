<x-admin-layout>
    <x-slot:title>Import Details</x-slot:title>

    {{-- ── Back + header ── --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('admin.data-import.index') }}"
            class="flex items-center gap-1.5 text-[12px] px-3 py-1.5 rounded-lg transition"
            style="color:var(--text-muted);background:var(--bg-raised);border:1px solid var(--border)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
            </svg>
            All Imports
        </a>
        <div class="h-4 w-px" style="background:var(--border)"></div>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Import Details</h1>
            <p class="font-mono text-[11px] mt-0.5" style="color:var(--text-dim)">
                Batch #{{ $import->id }} — {{ $import->sheet_name }}
            </p>
        </div>
    </div>

    {{-- ── Toast ── --}}
    <div id="toast"
        class="fixed bottom-6 right-6 z-[9999] hidden items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium"
        style="background:var(--bg-raised);border:1px solid var(--border);min-width:280px">
        <span id="toast-icon" class="text-lg flex-shrink-0"></span>
        <span id="toast-msg" style="color:var(--text-head)"></span>
    </div>

    {{-- ══ Stat cards ══ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl p-5"
            style="background:var(--bg-card);border:1px solid var(--border);border-top:2px solid var(--blue)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-2" style="color:var(--text-dim)">Total Rows</p>
            <p class="text-3xl font-bold font-mono" style="color:var(--text-white)">
                {{ number_format($import->total_rows) }}</p>
        </div>
        <div class="rounded-xl p-5"
            style="background:var(--bg-card);border:1px solid var(--border);border-top:2px solid var(--green)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-2" style="color:var(--text-dim)">Inserted</p>
            <p class="text-3xl font-bold font-mono" id="stat-inserted" style="color:var(--green)">
                {{ number_format($import->rows_inserted) }}</p>
        </div>
        <div class="rounded-xl p-5"
            style="background:var(--bg-card);border:1px solid var(--border);border-top:2px solid {{ $import->rows_failed > 0 ? 'var(--red)' : 'var(--border-lit)' }}">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-2" style="color:var(--text-dim)">Failed</p>
            <p class="text-3xl font-bold font-mono"
                style="color:{{ $import->rows_failed > 0 ? 'var(--red)' : 'var(--text-muted)' }}">
                {{ number_format($import->rows_failed) }}
            </p>
        </div>
        <div class="rounded-xl p-5"
            style="background:var(--bg-card);border:1px solid var(--border);border-top:2px solid var(--amber)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-2" style="color:var(--text-dim)">Status</p>
            @if ($import->status === 'completed' && $import->rows_failed == 0)
                <span class="font-mono text-[11px] px-2 py-1 rounded"
                    style="background:var(--green-dim);color:var(--green)">Complete</span>
            @elseif ($import->status === 'completed')
                <span class="font-mono text-[11px] px-2 py-1 rounded"
                    style="background:rgba(251,191,36,0.1);color:#FBBF24">Partial</span>
            @else
                <span class="font-mono text-[11px] px-2 py-1 rounded"
                    style="background:var(--red-dim);color:var(--red)">{{ ucfirst($import->status) }}</span>
            @endif
        </div>
    </div>

    {{-- ══ Meta + progress + actions ══ --}}
    <div class="rounded-xl p-6 mb-6" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="flex flex-wrap gap-6 text-[13px] mb-5" style="color:var(--text-body)">
            <div>
                <span class="font-mono text-[10px] uppercase tracking-wider block mb-0.5"
                    style="color:var(--text-dim)">Imported on</span>
                {{ $import->created_at->format('D, M j Y — H:i:s') }}
            </div>
            @if ($import->processing_time)
                <div>
                    <span class="font-mono text-[10px] uppercase tracking-wider block mb-0.5"
                        style="color:var(--text-dim)">Processing time</span>
                    {{ $import->processing_time }}s
                </div>
            @endif
            <div>
                <span class="font-mono text-[10px] uppercase tracking-wider block mb-0.5"
                    style="color:var(--text-dim)">Imported by</span>
                {{ $import->user->name ?? 'System' }}
            </div>
            <div>
                <span class="font-mono text-[10px] uppercase tracking-wider block mb-0.5"
                    style="color:var(--text-dim)">Weekly rows</span>
                {{ number_format($weeklyCount) }}
            </div>
        </div>

        @if ($import->total_rows > 0)
            @php $rate = round(($import->rows_inserted / $import->total_rows) * 100, 1); @endphp
            <div class="mb-5">
                <div class="flex justify-between mb-1">
                    <span class="font-mono text-[10px]" style="color:var(--text-dim)">Success rate</span>
                    <span class="font-mono text-[10px]" style="color:var(--text-muted)">{{ $rate }}%</span>
                </div>
                <div class="h-2 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                    <div class="h-full rounded-full transition-all"
                        style="width:{{ $rate }}%;background:{{ $rate == 100 ? 'var(--green)' : ($rate >= 50 ? '#FBBF24' : 'var(--red)') }}">
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            @if ($import->rows_inserted > 0)
                <a href="{{ route('admin.data-import.export-incidents', $import->id) }}"
                    class="flex items-center gap-2 text-[12px] font-semibold px-4 py-2 rounded-lg transition"
                    style="background:var(--blue-dim);color:var(--blue);border:1px solid rgba(96,165,250,0.2)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Export CSV
                </a>
            @endif

            @if ($import->rows_failed > 0)
                <a href="{{ route('admin.data-import.download-failed', $import->id) }}"
                    class="flex items-center gap-2 text-[12px] font-semibold px-4 py-2 rounded-lg transition"
                    style="background:var(--red-dim);color:var(--red);border:1px solid rgba(248,113,113,0.2)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Failed Rows CSV
                </a>
            @endif

            @if ($import->rows_inserted > 0)
                <button type="button"
                    onclick="openModal('delete-data', {{ $import->id }}, {{ $import->rows_inserted }}, {{ $weeklyCount }})"
                    class="flex items-center gap-2 text-[12px] font-semibold px-4 py-2 rounded-lg transition"
                    style="background:rgba(251,191,36,0.08);color:#FBBF24;border:1px solid rgba(251,191,36,0.2)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v6" />
                        <path d="M14 11v6" />
                    </svg>
                    Delete Data Only
                </button>
            @endif

            <button type="button"
                onclick="openModal('delete-import', {{ $import->id }}, {{ $import->rows_inserted }}, {{ $weeklyCount }})"
                class="flex items-center gap-2 text-[12px] font-semibold px-4 py-2 rounded-lg transition"
                style="background:var(--red-dim);color:var(--red);border:1px solid rgba(248,113,113,0.2)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14H6L5 6" />
                    <path d="M10 11v6" />
                    <path d="M14 11v6" />
                    <path d="M8 6V4h8v2" />
                </svg>
                Delete Import + Data
            </button>
        </div>
    </div>

    {{-- ══ Failed rows ══ --}}
    @if ($import->rows_failed > 0 && !empty($failedRows))
        <div class="rounded-xl overflow-hidden mb-6"
            style="background:var(--bg-card);border:1px solid rgba(248,113,113,0.2)">
            <div class="flex items-center gap-3 px-6 py-4" style="border-bottom:1px solid rgba(248,113,113,0.15)">
                <div class="w-2 h-2 rounded-full" style="background:var(--red)"></div>
                <h3 class="font-semibold text-[14px]" style="color:var(--red)">Failed Rows ({{ count($failedRows) }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            <th class="text-left w-20">Row #</th>
                            <th class="text-left">Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($failedRows as $err)
                            <tr>
                                <td class="font-mono text-[12px]"
                                    style="color:var(--text-muted);vertical-align:top;padding-top:14px">
                                    {{ $err['row_num'] ?? 'N/A' }}
                                </td>
                                <td>
                                    @if (is_array($err))
                                        <ul class="space-y-1">
                                            @foreach ($err as $field => $msg)
                                                @if ($field !== 'row_num')
                                                    <li class="text-[12.5px]">
                                                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded mr-2"
                                                            style="background:var(--red-dim);color:var(--red)">{{ $field }}</span>
                                                        <span
                                                            style="color:var(--text-body)">{{ $msg }}</span>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-[13px]"
                                            style="color:var(--text-body)">{{ $err }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══ Inserted incidents ══ --}}
    @if ($import->rows_inserted > 0)
        <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4"
                style="border-bottom:1px solid var(--border)">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full" style="background:var(--green)"></div>
                    <h3 class="font-semibold text-[14px]" style="color:var(--text-white)">
                        Inserted Incidents (<span
                            id="visible-count">{{ number_format($import->rows_inserted) }}</span>)
                    </h3>
                </div>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2" width="12" height="12"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        style="color:var(--text-dim)">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" id="searchIncidents" placeholder="Search caption, location, event ID…"
                        class="pl-8 pr-4 py-1.5 rounded-lg text-[12px] w-64 outline-none transition"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full data-table" id="incidentsTable">
                    <thead>
                        <tr>
                            <th class="text-left">Event ID</th>
                            <th class="text-left">Date</th>
                            <th class="text-left">Location</th>
                            <th class="text-left">Risk Factor</th>
                            <th class="text-center">Impact</th>
                            <th class="text-center">Deaths</th>
                            <th class="text-left">Caption</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="incidents-tbody">
                        @forelse($importedIncidents as $incident)
                            <tr id="row-{{ $incident->eventid }}" data-eventid="{{ $incident->eventid }}">
                                <td class="font-mono text-[11px]" style="color:var(--text-dim)">
                                    {{ substr($incident->eventid, 0, 14) }}
                                </td>
                                <td class="font-mono text-[11px] whitespace-nowrap" style="color:var(--text-muted)">
                                    {{ $incident->eventdate }}
                                </td>
                                <td class="text-[13px]" style="color:var(--text-body)">
                                    {{ $incident->location }}{{ $incident->lga ? ', ' . $incident->lga : '' }}
                                </td>
                                <td>
                                    @if ($incident->riskfactors)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                            style="background:var(--blue-dim);color:var(--blue)">
                                            {{ Str::limit($incident->riskfactors, 24) }}
                                        </span>
                                    @else
                                        <span style="color:var(--text-dim)">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $impStyle = match ($incident->impact) {
                                            'High' => 'background:var(--red-dim);color:var(--red)',
                                            'Medium' => 'background:rgba(251,191,36,0.1);color:#FBBF24',
                                            'Low' => 'background:var(--green-dim);color:var(--green)',
                                            default => 'background:var(--bg-raised);color:var(--text-dim)',
                                        };
                                    @endphp
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="{{ $impStyle }}">
                                        {{ $incident->impact ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-center font-mono text-[12px]"
                                    style="color:{{ ($incident->Casualties_count ?? 0) > 0 ? 'var(--red)' : 'var(--text-dim)' }}">
                                    {{ $incident->Casualties_count ?? '—' }}
                                </td>
                                <td style="max-width:260px">
                                    <span class="block truncate text-[13px]" style="color:var(--text-head)"
                                        title="{{ $incident->caption }}">{{ $incident->caption }}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        onclick="openModal('delete-row', {{ $import->id }}, 0, 0, '{{ $incident->eventid }}')"
                                        class="font-mono text-[11px] transition px-2 py-1 rounded"
                                        style="color:var(--red);background:var(--red-dim)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="8" class="text-center py-10" style="color:var(--text-dim)">
                                    No incident records found for this import batch.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($importedIncidents->hasPages())
                <div class="px-6 py-4" style="border-top:1px solid var(--border)">
                    {{ $importedIncidents->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ══ CONFIRM MODAL ══ --}}
    <div id="confirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
        style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)">
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
                <div id="modal-impact" class="rounded-xl p-4 text-[12px] font-mono"
                    style="background:var(--bg-raised);border:1px solid var(--border)">
                    <div class="flex items-center justify-between mb-1">
                        <span style="color:var(--text-dim)">tbldataentry rows</span>
                        <span id="impact-data" class="font-semibold" style="color:var(--red)">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--text-dim)">tblweeklydataentry rows</span>
                        <span id="impact-weekly" class="font-semibold" style="color:var(--red)">0</span>
                    </div>
                </div>
                <p class="font-mono text-[11px] mt-3" style="color:var(--text-dim)">⚠ This action cannot be undone.
                </p>
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
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        let currentAction = null;
        let currentImportId = null;
        let currentEventId = null;

        // ── Toast ──────────────────────────────────────────────────────────────────
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const text = document.getElementById('toast-msg');
            icon.textContent = type === 'success' ? '✓' : '✕';
            text.textContent = msg;
            t.style.borderColor = type === 'success' ? 'var(--green)' : 'var(--red)';
            t.style.color = type === 'success' ? 'var(--green)' : 'var(--red)';
            t.classList.remove('hidden');
            t.classList.add('flex');
            clearTimeout(t._timer);
            t._timer = setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('flex');
            }, 4000);
        }

        // ── Animated row removal ───────────────────────────────────────────────────
        // Fades + slides the row out, then removes from DOM. No page reload needed.
        function fadeRemoveRow(eventid, onDone) {
            const row = document.getElementById('row-' + eventid);
            if (!row) {
                onDone && onDone();
                return;
            }
            row.style.transition = 'opacity 0.22s ease, transform 0.22s ease, max-height 0.3s ease';
            row.style.overflow = 'hidden';
            row.style.maxHeight = row.offsetHeight + 'px';
            // Trigger animation on next frame
            requestAnimationFrame(() => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(10px)';
                row.style.maxHeight = '0';
                row.style.padding = '0';
            });
            setTimeout(() => {
                row.remove();
                onDone && onDone();
            }, 310);
        }

        // ── Modal ──────────────────────────────────────────────────────────────────
        function openModal(action, importId, dataRows, weeklyRows, eventId = null) {
            currentAction = action;
            currentImportId = importId;
            currentEventId = eventId;

            const title = document.getElementById('modal-title');
            const subtitle = document.getElementById('modal-subtitle');
            const body = document.getElementById('modal-body');
            const impData = document.getElementById('impact-data');
            const impWkly = document.getElementById('impact-weekly');

            if (action === 'delete-data') {
                title.textContent = 'Delete Imported Data';
                subtitle.textContent = 'Import history record will be kept.';
                body.textContent =
                    `This will permanently remove all ${dataRows.toLocaleString()} inserted incident rows from both tables. The import history record (#${importId}) will remain.`;
                impData.textContent = dataRows.toLocaleString();
                impWkly.textContent = weeklyRows.toLocaleString();
            } else if (action === 'delete-import') {
                title.textContent = 'Delete Import + All Data';
                subtitle.textContent = 'Permanently removes everything for this batch.';
                body.textContent =
                    `This will delete the import history record (#${importId}) AND all ${dataRows.toLocaleString()} associated incident rows from both tables.`;
                impData.textContent = dataRows.toLocaleString();
                impWkly.textContent = weeklyRows.toLocaleString();
            } else if (action === 'delete-row') {
                title.textContent = 'Delete Single Incident';
                subtitle.textContent = `Event ID: ${eventId}`;
                body.textContent =
                    'This will permanently delete this incident row from both tbldataentry and tblweeklydataentry. The row will disappear immediately.';
                impData.textContent = '1';
                impWkly.textContent = '1';
            }

            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.getElementById('confirmModal').classList.remove('flex');
            currentAction = currentImportId = currentEventId = null;
        }

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // ── Confirm handler ────────────────────────────────────────────────────────
        document.getElementById('modal-confirm-btn').addEventListener('click', async function() {
            if (!currentAction) return;

            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            // ── CRITICAL: capture before closeModal() resets all three to null ────
            const action = currentAction;
            const importId = currentImportId;
            const eventId = currentEventId;

            let url, body = null;

            if (action === 'delete-data') {
                url = `/admin/data-import/${importId}/data`;
            } else if (action === 'delete-import') {
                url = `/admin/data-import/${importId}`;
            } else if (action === 'delete-row') {
                url = `/admin/data-import/${importId}/incident`;
                body = JSON.stringify({
                    eventid: eventId
                });
            }

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body,
                });
                const data = await res.json();

                closeModal(); // safe to call now — we already captured the values above

                if (data.success) {
                    showToast(data.message, 'success');

                    if (action === 'delete-import') {
                        // Brief pause so the toast is visible, then redirect
                        setTimeout(() => {
                            window.location.href = '/admin/data-import';
                        }, 1600);

                    } else if (action === 'delete-data') {
                        // Fade out every visible row, then show empty state
                        const rows = document.querySelectorAll('#incidentsTable tbody tr[id^="row-"]');
                        let delay = 0;
                        rows.forEach(row => {
                            setTimeout(() => {
                                row.style.transition = 'opacity 0.18s ease';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 190);
                            }, delay);
                            delay += 30; // stagger 30ms per row
                        });
                        setTimeout(() => {
                            const tbody = document.getElementById('incidents-tbody');
                            if (tbody) {
                                tbody.innerHTML =
                                    `<tr id="empty-row"><td colspan="8" class="text-center py-12" style="color:var(--text-dim)">All data deleted — import history preserved.</td></tr>`;
                            }
                            const statEl = document.getElementById('stat-inserted');
                            if (statEl) statEl.textContent = '0';
                            const countEl = document.getElementById('visible-count');
                            if (countEl) countEl.textContent = '0';
                        }, delay + 220);

                    } else if (action === 'delete-row' && eventId) {
                        // Fade the row out immediately — no page reload needed
                        fadeRemoveRow(eventId, () => {
                            const countEl = document.getElementById('visible-count');
                            if (countEl) {
                                const n = parseInt(countEl.textContent.replace(/,/g, ''), 10);
                                if (!isNaN(n) && n > 0) countEl.textContent = (n - 1).toLocaleString();
                            }
                            const remaining = document.querySelectorAll(
                                '#incidentsTable tbody tr[id^="row-"]');
                            if (remaining.length === 0) {
                                const tbody = document.getElementById('incidents-tbody');
                                if (tbody) {
                                    tbody.innerHTML =
                                        `<tr id="empty-row"><td colspan="8" class="text-center py-12" style="color:var(--text-dim)">All rows on this page have been deleted.</td></tr>`;
                                }
                            }
                        });
                    }
                } else {
                    showToast(data.message ?? 'Delete failed.', 'error');
                }
            } catch (err) {
                closeModal();
                showToast('Request failed: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Confirm Delete';
            }
        });

        // ── Search ────────────────────────────────────────────────────────────────
        document.getElementById('searchIncidents')?.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#incidentsTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>

</x-admin-layout>

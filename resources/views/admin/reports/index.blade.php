<x-admin-layout>
    <x-slot:title>Reports</x-slot:title>

    {{-- ── Toast ──────────────────────────────────────────────────────────────── --}}
    <div id="rpt-toast"
        class="fixed bottom-6 right-6 z-[9999] hidden items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-[13px] font-medium"
        style="background:var(--bg-card);border:1px solid var(--border);min-width:260px;max-width:400px">
        <span id="rpt-toast-icon" class="text-base flex-shrink-0"></span>
        <span id="rpt-toast-msg" style="color:var(--text-head)"></span>
    </div>

    {{-- ── Delete confirm modal ────────────────────────────────────────────────── --}}
    <div id="deleteModal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-4"
        style="background:rgba(0,0,0,0.72);backdrop-filter:blur(4px)">
        <div class="w-full max-w-sm rounded-2xl overflow-hidden"
            style="background:var(--bg-card);border:1px solid var(--border-lit);box-shadow:0 24px 64px rgba(0,0,0,0.6)">
            <div class="flex items-start gap-4 p-5" style="border-bottom:1px solid var(--border)">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:var(--red-dim)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" style="color:var(--red)">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v6" />
                        <path d="M14 11v6" />
                        <path d="M9 6V4h6v2" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-[15px]" style="color:var(--text-white)">Delete Report</h3>
                    <p id="delete-modal-title" class="text-[12px] mt-0.5 truncate" style="color:var(--text-dim)"></p>
                </div>
            </div>
            <div class="px-5 py-4">
                <p class="text-[13px] leading-relaxed" style="color:var(--text-body)">
                    This permanently deletes the PDF file and thumbnail from storage.
                    This action cannot be undone.
                </p>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Cancel
                </button>
                <button type="button" id="confirm-delete-btn"
                    class="flex-1 py-2.5 rounded-xl text-[13px] font-bold transition"
                    style="background:var(--red);color:#fff">
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Reports</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                Manage downloadable PDF reports. PDFs are stored privately and served only to authenticated users with
                the correct tier.
            </p>
        </div>
        <a href="{{ route('admin.reports.create') }}"
            class="flex items-center gap-2 text-sm px-4 py-2 rounded-lg font-semibold transition"
            style="background:var(--amber);color:#080E1A">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Upload Report
        </a>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-5 px-4 py-3 rounded-xl text-[13px] font-medium"
            style="background:var(--green-dim);color:var(--green);border:1px solid rgba(52,211,153,0.3)">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- ── Reports table ────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid var(--border)">
            <p class="font-mono text-[10px]" style="color:var(--text-dim)">
                {{ $reports->count() }} report{{ $reports->count() !== 1 ? 's' : '' }}
            </p>
            <a href="{{ route('reports.index') }}" target="_blank"
                class="font-mono text-[10px] transition flex items-center gap-1" style="color:var(--text-dim)">
                View public page
                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                    <polyline points="15 3 21 3 21 9" />
                    <line x1="10" y1="14" x2="21" y2="3" />
                </svg>
            </a>
        </div>

        @if ($reports->isEmpty())
            <div class="text-center py-16" style="color:var(--text-dim)">
                <svg class="mx-auto mb-3 opacity-30" width="36" height="36" fill="none" stroke="currentColor"
                    stroke-width="1.25" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <p class="text-sm mb-3">No reports uploaded yet.</p>
                <a href="{{ route('admin.reports.create') }}" style="color:var(--amber)" class="text-sm font-semibold">
                    Upload your first report →
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Report</th>
                            <th class="text-left">Period</th>
                            <th class="text-center">Min Tier</th>
                            <th class="text-center">Status</th>
                            <th class="text-left">Uploaded</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr id="rpt-row-{{ $report->id }}">

                                {{-- Report title + thumbnail --}}
                                <td style="max-width:300px">
                                    <div class="flex items-center gap-3">
                                        {{-- Thumbnail preview --}}
                                        @if ($report->image_path)
                                            <img src="{{ asset($report->image_path) }}" alt=""
                                                class="w-9 h-11 object-cover rounded flex-shrink-0"
                                                style="border:1px solid var(--border)">
                                        @else
                                            <div class="w-9 h-11 rounded flex-shrink-0 flex items-center justify-center"
                                                style="background:var(--bg-raised);border:1px solid var(--border)">
                                                <svg width="13" height="13" fill="none"
                                                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                                    style="color:var(--text-dim)">
                                                    <path
                                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                    <polyline points="14 2 14 8 20 8" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-semibold truncate"
                                                style="color:var(--text-white)" title="{{ $report->title }}">
                                                {{ $report->title }}
                                            </p>
                                            <p class="font-mono text-[10px] mt-0.5 truncate"
                                                style="color:var(--text-dim)" title="{{ $report->file_path }}">
                                                {{ $report->file_path }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Period --}}
                                <td>
                                    <span class="text-[12px]" style="color:var(--text-body)">
                                        {{ $report->period ?: '—' }}
                                    </span>
                                </td>

                                {{-- Tier --}}
                                <td class="text-center">
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="{{ $report->min_tier >= 2
                                            ? 'background:var(--amber-dim);color:var(--amber)'
                                            : 'background:var(--bg-raised);color:var(--text-dim)' }}">
                                        Tier {{ $report->min_tier }}{{ $report->min_tier === 1 ? ' · Free' : '+' }}
                                    </span>
                                </td>

                                {{-- Published toggle --}}
                                <td class="text-center">
                                    <button type="button" id="pub-btn-{{ $report->id }}"
                                        onclick="togglePublish({{ $report->id }})"
                                        class="font-mono text-[10px] px-3 py-1 rounded-lg transition"
                                        style="{{ $report->is_published
                                            ? 'background:var(--green-dim);color:var(--green);border:1px solid rgba(52,211,153,0.25);cursor:pointer'
                                            : 'background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border);cursor:pointer' }}">
                                        {{ $report->is_published ? '● Live' : '○ Draft' }}
                                    </button>
                                </td>

                                {{-- Uploaded date --}}
                                <td>
                                    <span class="font-mono text-[11px]" style="color:var(--text-dim)">
                                        {{ $report->created_at->format('M j, Y') }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.reports.edit', $report) }}"
                                            class="font-mono text-[11px] px-2.5 py-1 rounded-lg transition"
                                            style="background:var(--blue-dim);color:var(--blue)">
                                            Edit
                                        </a>
                                        <button type="button"
                                            onclick="openDeleteModal({{ $report->id }}, '{{ addslashes($report->title) }}')"
                                            class="font-mono text-[11px] px-2.5 py-1 rounded-lg transition"
                                            style="background:var(--red-dim);color:var(--red)">
                                            Delete
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Toast ────────────────────────────────────────────────────────────────────
        function showToast(msg, ok = true) {
            const t = document.getElementById('rpt-toast');
            const icon = document.getElementById('rpt-toast-icon');
            const text = document.getElementById('rpt-toast-msg');
            icon.textContent = ok ? '✓' : '✕';
            text.textContent = msg;
            t.style.borderColor = ok ? 'rgba(52,211,153,0.4)' : 'rgba(248,113,113,0.4)';
            icon.style.color = ok ? 'var(--green)' : 'var(--red)';
            t.classList.remove('hidden');
            t.classList.add('flex');
            clearTimeout(t._t);
            t._t = setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('flex');
            }, 4000);
        }

        // ── Publish toggle ───────────────────────────────────────────────────────────
        async function togglePublish(id) {
            const btn = document.getElementById('pub-btn-' + id);
            if (!btn) return;
            btn.disabled = true;

            try {
                const res = await fetch(`/admin/reports/${id}/toggle-publish`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                });
                const data = await res.json();
                if (data.success) {
                    const isLive = data.is_published;
                    btn.textContent = isLive ? '● Live' : '○ Draft';
                    btn.style.cssText = isLive ?
                        'background:var(--green-dim);color:var(--green);border:1px solid rgba(52,211,153,0.25);cursor:pointer;font-family:monospace;font-size:10px;padding:2px 12px;border-radius:8px;transition:all 0.15s' :
                        'background:var(--bg-raised);color:var(--text-dim);border:1px solid var(--border);cursor:pointer;font-family:monospace;font-size:10px;padding:2px 12px;border-radius:8px;transition:all 0.15s';
                    showToast(data.message, true);
                } else {
                    showToast(data.message ?? 'Toggle failed.', false);
                }
            } catch (e) {
                showToast('Request failed: ' + e.message, false);
            } finally {
                btn.disabled = false;
            }
        }

        // ── Delete modal ─────────────────────────────────────────────────────────────
        let _deleteId = null;

        function openDeleteModal(id, title) {
            _deleteId = id;
            document.getElementById('delete-modal-title').textContent = title;
            const m = document.getElementById('deleteModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            _deleteId = null;
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
            if (!_deleteId) return;
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            try {
                const res = await fetch(`/admin/reports/${_deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById('rpt-row-' + _deleteId);
                    if (row) {
                        row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(16px)';
                        setTimeout(() => row.remove(), 260);
                    }
                    showToast(data.message, true);
                    closeDeleteModal();
                } else {
                    showToast(data.message ?? 'Delete failed.', false);
                }
            } catch (e) {
                showToast('Request failed: ' + e.message, false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Delete';
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>

</x-admin-layout>

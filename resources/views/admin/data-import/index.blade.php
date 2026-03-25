<x-admin-layout>
    <x-slot:title>Bulk Import</x-slot:title>

    {{-- ── Page header ── --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Bulk Incident Import</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">Upload an Excel spreadsheet to insert records into
                both incident tables.</p>
        </div>
        <a href="{{ route('admin.file-processor.index') }}"
            class="hidden sm:flex items-center gap-2 text-[12px] px-3 py-2 rounded-lg transition"
            style="color:var(--text-muted);background:var(--bg-raised);border:1px solid var(--border)">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.75"
                viewBox="0 0 24 24">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                <polyline points="13 2 13 9 20 9" />
            </svg>
            AI Pre-processor
        </a>
    </div>

    {{-- ── Toast ── --}}
    <div id="toast"
        class="fixed bottom-6 right-6 z-[9999] hidden items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium"
        style="background:var(--bg-raised);border:1px solid var(--border);min-width:280px">
        <span id="toast-icon" class="text-lg flex-shrink-0"></span>
        <span id="toast-msg" style="color:var(--text-head)"></span>
    </div>

    {{-- ── Flash alerts ── --}}
    @if (session('successAlert'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--green-dim);border:1px solid rgba(52,211,153,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--green)">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--green)">{{ session('successAlert') }}</p>
        </div>
    @endif

    @if (session('errorAlert'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--red-dim);border:1px solid rgba(248,113,113,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--red)">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--red)">{{ session('errorAlert') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl mb-6" style="background:var(--red-dim);border:1px solid rgba(248,113,113,0.25)">
            <p class="text-sm font-semibold mb-2" style="color:var(--red)">Please fix the following:</p>
            <ul class="space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li class="text-sm" style="color:var(--red)">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════════════════════
     Form + column guide
══════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

        {{-- Upload form (2/3) --}}
        <div class="lg:col-span-2 rounded-xl p-6" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-5" style="color:var(--text-dim)">Upload File
            </p>

            <form action="{{ route('admin.data-import.store') }}" method="POST" enctype="multipart/form-data"
                id="uploadForm">
                @csrf

                <div id="dropZone" class="rounded-xl p-8 text-center cursor-pointer mb-5 transition-all"
                    style="border:2px dashed var(--border-lit);background:var(--bg-surface)"
                    onclick="document.getElementById('incident_file').click()" ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">

                    <input type="file" name="incident_file" id="incident_file" accept=".xlsx,.xls,.xlsm,.xlsb"
                        class="hidden" required>

                    <div id="dropPrompt">
                        <svg class="mx-auto mb-3" width="32" height="32" fill="none" stroke="currentColor"
                            stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-dim)">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <p class="text-sm font-medium mb-1" style="color:var(--text-head)">
                            Drop your file here or <span style="color:var(--amber)">click to browse</span>
                        </p>
                        <p class="font-mono text-[11px]" style="color:var(--text-dim)">XLSX · XLS · XLSM · XLSB — max
                            10 MB</p>
                    </div>

                    <div id="fileSelectedState" class="hidden">
                        <svg class="mx-auto mb-3" width="32" height="32" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--green)">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <polyline points="9 15 11 17 15 13" />
                        </svg>
                        <p class="text-sm font-semibold" id="selectedFileName" style="color:var(--text-white)"></p>
                        <p class="font-mono text-[11px] mt-1" id="selectedFileSize" style="color:var(--text-dim)">
                        </p>
                        <button type="button" onclick="clearFile(event)" class="mt-3 font-mono text-[11px]"
                            style="color:var(--red)">
                            ✕ Remove file
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" id="submitBtn"
                        class="flex items-center gap-2 text-sm font-bold px-6 py-2.5 rounded-lg transition"
                        style="background:var(--amber);color:#080E1A">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <span id="submitBtnText">Upload and Import</span>
                    </button>
                    <p id="processingMsg" class="hidden text-sm animate-pulse" style="color:var(--text-muted)">
                        Processing… this may take a moment for large files.
                    </p>
                </div>
            </form>
        </div>

        {{-- Column guide (1/3) --}}
        <div class="rounded-xl p-5 flex flex-col gap-4"
            style="background:var(--bg-card);border:1px solid var(--border)">
            <div>
                <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Required
                    Columns</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['year', 'month', 'day', 'state', 'lga', 'risk_factor', 'risk_indicator', 'impact', 'caption'] as $col)
                        <span class="font-mono text-[10px] px-2 py-1 rounded"
                            style="background:var(--amber-dim);color:var(--amber)">{{ $col }}</span>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Optional
                    Columns</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['neighbourhood', 'latitude', 'longitude', 'weekly_summary', 'source_link', 'hashtag', 'deaths_count', 'injuries_count', 'victim', 'accused', 'motive', 'motive_specific', 'target_type', 'target_subtype', 'weapon_type', 'weapon_subtype', 'attack_group', 'attack_group_sub', 'day_period', 'ransom', 'amount', 'image', 'add_notes', 'affected_industry', 'business_report', 'business_advisory', 'associated_risks', 'impact_level', 'impact_rationale', 'similar_news_link'] as $col)
                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded"
                            style="background:var(--bg-raised);color:var(--text-dim)">{{ $col }}</span>
                    @endforeach
                </div>
            </div>

            <div class="mt-auto p-3 rounded-lg" style="background:var(--bg-raised);border:1px solid var(--border)">
                <p class="font-mono text-[10px] leading-relaxed" style="color:var(--text-muted)">
                    💡 Run the <a href="{{ route('admin.file-processor.index') }}" style="color:var(--amber)">AI File
                        Processor</a> first to auto-fill AI columns before importing.
                </p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════
     Import history table
══════════════════════════ --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border)">
            <div>
                <h2 class="font-semibold" style="color:var(--text-white)">Import History</h2>
                <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">All batches, newest first</p>
            </div>
        </div>

        @if ($recentImports->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full data-table" id="historyTable">
                    <thead>
                        <tr>
                            <th class="text-left">#</th>
                            <th class="text-left">Date</th>
                            <th class="text-left">File</th>
                            <th class="text-left">By</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Inserted</th>
                            <th class="text-center">Failed</th>
                            <th class="text-center">Time (s)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentImports as $import)
                            <tr id="history-row-{{ $import->id }}">
                                <td class="font-mono text-[11px]" style="color:var(--text-dim)">{{ $import->id }}
                                </td>
                                <td class="whitespace-nowrap font-mono text-[11px]" style="color:var(--text-muted)">

                                    {{ $import->created_at ? $import->created_at->format('M d, Y H:i') : '—' }}
                                </td>
                                <td style="max-width:180px">
                                    <span class="block truncate text-[13px]" style="color:var(--text-head)"
                                        title="{{ $import->sheet_name }}">{{ $import->sheet_name }}</span>
                                </td>
                                <td class="text-[12px]" style="color:var(--text-body)">
                                    {{ $import->user->name ?? '—' }}
                                </td>
                                <td class="text-center">
                                    @if ($import->status === 'completed' && $import->rows_failed == 0)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                            style="background:var(--green-dim);color:var(--green)">Complete</span>
                                    @elseif ($import->status === 'completed' && $import->rows_failed > 0)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                            style="background:rgba(251,191,36,0.1);color:#FBBF24">Partial</span>
                                    @elseif ($import->status === 'failed')
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                            style="background:var(--red-dim);color:var(--red)">Failed</span>
                                    @else
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                            style="background:var(--bg-raised);color:var(--text-dim)">{{ ucfirst($import->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center font-mono text-[12px]" style="color:var(--text-muted)">
                                    {{ $import->total_rows }}
                                </td>
                                <td class="text-center font-mono text-[12px] font-semibold"
                                    style="color:var(--green)">
                                    {{ $import->rows_inserted }}
                                </td>
                                <td class="text-center font-mono text-[12px]">
                                    @if ($import->rows_failed > 0)
                                        <span style="color:var(--red)"
                                            class="font-semibold">{{ $import->rows_failed }}</span>
                                    @else
                                        <span style="color:var(--text-dim)">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-mono text-[12px]" style="color:var(--text-dim)">
                                    {{ $import->processing_time ?? '—' }}
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.data-import.show', $import->id) }}"
                                            class="font-mono text-[11px] transition"
                                            style="color:var(--amber)">View</a>
                                        <span style="color:var(--border-lit)">·</span>
                                        <button type="button"
                                            onclick="openIndexModal({{ $import->id }}, {{ $import->rows_inserted }}, '{{ addslashes($import->sheet_name) }}')"
                                            class="font-mono text-[11px] transition" style="color:var(--red)">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4" style="border-top:1px solid var(--border)">
                {{ $recentImports->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <svg class="mx-auto mb-4" width="32" height="32" fill="none" stroke="currentColor"
                    stroke-width="1" viewBox="0 0 24 24" style="color:var(--text-dim)">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                <p class="text-sm mb-1" style="color:var(--text-muted)">No imports yet.</p>
                <p class="font-mono text-[11px]" style="color:var(--text-dim)">Upload your first file above to get
                    started.</p>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════
     CONFIRM DELETE MODAL (index page variant)
═══════════════════════════════════════════ --}}
    <div id="indexConfirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
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
                        <path d="M8 6V4h8v2" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[16px]" style="color:var(--text-white)">Delete Import + All Data</h3>
                    <p id="idx-modal-subtitle" class="text-[13px] mt-0.5" style="color:var(--text-muted)"></p>
                </div>
            </div>

            <div class="p-6">
                <p id="idx-modal-body" class="text-[13px] leading-relaxed mb-4" style="color:var(--text-body)"></p>
                <div class="rounded-xl p-4 text-[12px] font-mono"
                    style="background:var(--bg-raised);border:1px solid var(--border)">
                    <div class="flex items-center justify-between mb-1">
                        <span style="color:var(--text-dim)">Import history records</span>
                        <span class="font-semibold" style="color:var(--red)">1</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--text-dim)">Incident data rows (both tables)</span>
                        <span id="idx-impact-rows" class="font-semibold" style="color:var(--red)">—</span>
                    </div>
                </div>
                <p class="font-mono text-[11px] mt-3" style="color:var(--text-dim)">⚠ This action cannot be undone.
                </p>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeIndexModal()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Cancel
                </button>
                <button type="button" id="idx-confirm-btn"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition"
                    style="background:var(--red);color:#080E1A">
                    Delete Permanently
                </button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        let idxImportId = null;

        // ── Toast ------------------------------------------------------------------
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
            setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('flex');
            }, 4000);
        }

        // ── Index modal -----------------------------------------------------------
        function openIndexModal(importId, rows, fileName) {
            idxImportId = importId;
            document.getElementById('idx-modal-subtitle').textContent = `File: ${fileName}`;
            document.getElementById('idx-modal-body').textContent =
                `This will permanently delete import #${importId} and all ${rows.toLocaleString()} associated incident rows from tbldataentry and tblweeklydataentry.`;
            document.getElementById('idx-impact-rows').textContent = rows.toLocaleString() + ' × 2 tables';
            const modal = document.getElementById('indexConfirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeIndexModal() {
            document.getElementById('indexConfirmModal').classList.add('hidden');
            document.getElementById('indexConfirmModal').classList.remove('flex');
            idxImportId = null;
        }

        document.getElementById('idx-confirm-btn').addEventListener('click', async function() {
            if (!idxImportId) return;
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            try {
                const res = await fetch(`/admin/data-import/${idxImportId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                });
                const data = await res.json();
                closeIndexModal();

                if (data.success) {
                    showToast(data.message, 'success');
                    const row = document.getElementById('history-row-' + idxImportId);
                    if (row) row.remove();
                } else {
                    showToast(data.message ?? 'Delete failed.', 'error');
                }
            } catch (err) {
                closeIndexModal();
                showToast('Request failed: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Delete Permanently';
            }
        });

        document.getElementById('indexConfirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeIndexModal();
        });

        // ── File drop zone --------------------------------------------------------
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('incident_file');
        const dropPrompt = document.getElementById('dropPrompt');
        const fileSelectedState = document.getElementById('fileSelectedState');

        function showFile(file) {
            document.getElementById('selectedFileName').textContent = file.name;
            document.getElementById('selectedFileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            dropPrompt.classList.add('hidden');
            fileSelectedState.classList.remove('hidden');
            dropZone.style.borderColor = 'var(--green)';
            dropZone.style.background = 'rgba(52,211,153,0.04)';
        }

        fileInput.addEventListener('change', function() {
            if (this.files[0]) showFile(this.files[0]);
        });

        function clearFile(e) {
            e.stopPropagation();
            fileInput.value = '';
            dropPrompt.classList.remove('hidden');
            fileSelectedState.classList.add('hidden');
            dropZone.style.borderColor = 'var(--border-lit)';
            dropZone.style.background = 'var(--bg-surface)';
        }

        function handleDragOver(e) {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--amber)';
            dropZone.style.background = 'var(--amber-dim)';
        }

        function handleDragLeave() {
            dropZone.style.borderColor = fileInput.files.length ? 'var(--green)' : 'var(--border-lit)';
            dropZone.style.background = fileInput.files.length ? 'rgba(52,211,153,0.04)' : 'var(--bg-surface)';
        }

        function handleDrop(e) {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showFile(file);
        }

        document.getElementById('uploadForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.style.opacity = '0.65';
            document.getElementById('submitBtnText').textContent = 'Importing…';
            document.getElementById('processingMsg').classList.remove('hidden');
        });
    </script>

</x-admin-layout>

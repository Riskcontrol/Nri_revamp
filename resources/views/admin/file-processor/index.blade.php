<x-admin-layout>
    <x-slot:title>AI File Processor</x-slot:title>

    {{-- ── Page header ── --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">AI File Processor</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                Upload your incident spreadsheet to auto-generate business reports, impact levels, and risk advisories
                using AI.
            </p>
        </div>
        <a href="{{ route('admin.data-import.index') }}"
            class="hidden sm:flex items-center gap-2 text-[12px] px-3 py-2 rounded-lg transition"
            style="color:var(--text-muted);background:var(--bg-raised);border:1px solid var(--border)">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.75"
                viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
            Bulk Import
        </a>
    </div>

    {{-- ── Alerts ── --}}
    @if (session('success'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--green-dim);border:1px solid rgba(52,211,153,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--green)">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--green)">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--red-dim);border:1px solid rgba(248,113,113,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--red)">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--red)">{{ session('error') }}</p>
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

    {{-- ══════════════════════════════════
     Two-column layout
══════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Upload card (2/3) --}}
        <div class="lg:col-span-2 rounded-xl p-6" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="font-mono text-[10px] tracking-widest uppercase mb-5" style="color:var(--text-dim)">Upload
                Spreadsheet</p>

            <form action="{{ route('admin.file-processor.process') }}" method="POST" enctype="multipart/form-data"
                id="processorForm">
                @csrf

                {{-- Drop zone --}}
                <div id="dropZone" class="rounded-xl p-10 text-center cursor-pointer mb-6 transition-all"
                    style="border:2px dashed var(--border-lit);background:var(--bg-surface)"
                    onclick="document.getElementById('data_file').click()" ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">

                    <input type="file" name="data_file" id="data_file" accept=".xlsx,.xls,.csv" class="hidden"
                        required>

                    {{-- Idle state --}}
                    <div id="dropPrompt">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                            style="background:var(--amber-dim)">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24" style="color:var(--amber)">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold mb-1" style="color:var(--text-head)">
                            Drop your spreadsheet here or <span style="color:var(--amber)">browse files</span>
                        </p>
                        <p class="font-mono text-[11px]" style="color:var(--text-dim)">XLSX · XLS · CSV — max 10 MB
                        </p>
                    </div>

                    {{-- File selected state --}}
                    <div id="fileSelectedState" class="hidden">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                            style="background:var(--green-dim)">
                            <svg width="24" height="24" fill="none" stroke="currentColor"
                                stroke-width="1.75" viewBox="0 0 24 24" style="color:var(--green)">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <polyline points="9 15 11 17 15 13" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold" id="selectedFileName" style="color:var(--text-white)"></p>
                        <p class="font-mono text-[11px] mt-1" id="selectedFileSize" style="color:var(--text-dim)">
                        </p>
                        <button type="button" onclick="clearFile(event)" class="mt-3 font-mono text-[11px]"
                            style="color:var(--red)">
                            ✕ Remove file
                        </button>
                    </div>
                </div>

                {{-- Submit + processing indicator --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <button type="submit" id="submitBtn"
                        class="flex items-center gap-2.5 text-sm font-bold px-8 py-3 rounded-xl transition"
                        style="background:var(--amber);color:#080E1A">
                        <svg id="btnIcon" width="15" height="15" fill="none" stroke="currentColor"
                            stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" opacity="0.15" />
                            <path d="m9 12 2 2 4-4" />
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                        <span id="submitBtnText">Generate AI Report</span>
                    </button>

                    {{-- Processing state (hidden until submit) --}}
                    <div id="processingState" class="hidden flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full border-2 animate-spin"
                            style="border-color:var(--amber);border-top-color:transparent"></div>
                        <div>
                            <p class="text-sm font-medium" style="color:var(--amber)">Processing with AI…</p>
                            <p class="font-mono text-[11px]" style="color:var(--text-dim)">
                                Analysing each row's risk indicators. This may take a few minutes for large files.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Info panel (1/3) --}}
        <div class="flex flex-col gap-4">

            {{-- What it does --}}
            <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                <p class="font-mono text-[10px] tracking-widest uppercase mb-4" style="color:var(--text-dim)">What
                    This Does</p>
                <ul class="space-y-3">
                    @foreach ([['color' => 'var(--amber)', 'text' => 'Reads each row\'s risk indicator and weekly summary'], ['color' => 'var(--blue)', 'text' => 'Generates a business impact report via Groq AI'], ['color' => 'var(--green)', 'text' => 'Identifies affected industries and impact levels'], ['color' => 'var(--green)', 'text' => 'Adds business advisory and associated risks'], ['color' => 'var(--amber)', 'text' => 'Finds similar news links for corroboration'], ['color' => 'var(--blue)', 'text' => 'Returns a new enriched spreadsheet for download']] as $item)
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0"
                                style="background:{{ $item['color'] }}"></div>
                            <span class="text-[13px]" style="color:var(--text-body)">{{ $item['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Required columns --}}
            <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                <p class="font-mono text-[10px] tracking-widest uppercase mb-3" style="color:var(--text-dim)">Key
                    Input Columns</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['risk_indicator', 'weekly_summary', 'source_link', 'add_notes', 'link2', 'link3'] as $col)
                        <span class="font-mono text-[10px] px-2 py-1 rounded"
                            style="background:var(--amber-dim);color:var(--amber)">{{ $col }}</span>
                    @endforeach
                </div>
                <p class="font-mono text-[10px] mt-3 leading-relaxed" style="color:var(--text-dim)">
                    Rows without a risk_indicator are skipped and passed through unchanged.
                </p>
            </div>

            {{-- Workflow tip --}}
            <div class="rounded-xl p-4" style="background:var(--bg-raised);border:1px solid var(--border)">
                <p class="font-mono text-[10px] tracking-widest uppercase mb-2" style="color:var(--text-dim)">Workflow
                </p>
                <div class="flex items-center gap-2 text-[12px]" style="color:var(--text-body)">
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                        style="background:var(--amber-dim);color:var(--amber)">1</span>
                    Process here
                </div>
                <div class="w-px h-4 ml-3 my-1" style="background:var(--border)"></div>
                <div class="flex items-center gap-2 text-[12px]" style="color:var(--text-body)">
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                        style="background:var(--blue-dim);color:var(--blue)">2</span>
                    Download enriched file
                </div>
                <div class="w-px h-4 ml-3 my-1" style="background:var(--border)"></div>
                <div class="flex items-center gap-2 text-[12px]" style="color:var(--text-body)">
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                        style="background:var(--green-dim);color:var(--green)">3</span>
                    <a href="{{ route('admin.data-import.index') }}" style="color:var(--green)">Import into
                        database</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('data_file');
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

        document.getElementById('processorForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.style.opacity = '0.65';
            document.getElementById('submitBtnText').textContent = 'Sending to AI…';
            document.getElementById('processingState').classList.remove('hidden');
            document.getElementById('processingState').classList.add('flex');
        });
    </script>

</x-admin-layout>

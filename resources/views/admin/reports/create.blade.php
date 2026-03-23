<x-admin-layout>
    <x-slot:title>Upload Report</x-slot:title>

    {{-- Page header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.reports.index') }}"
            class="flex items-center justify-center w-8 h-8 rounded-lg transition"
            style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-dim)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Upload Report</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                Add a new PDF report to the reports page.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 px-4 py-3 rounded-xl text-[13px]"
            style="background:var(--red-dim);color:var(--red);border:1px solid rgba(248,113,113,0.3)">
            <p class="font-semibold mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.reports.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ── LEFT COLUMN — main fields ─────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- PDF upload --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">PDF File *</label>

                    <div id="pdf-drop" class="rounded-xl border-2 border-dashed transition cursor-pointer"
                        style="border-color:var(--border);padding:32px 20px;text-align:center"
                        onclick="document.getElementById('pdf_file').click()"
                        ondragover="event.preventDefault();this.style.borderColor='var(--amber)'"
                        ondragleave="this.style.borderColor='var(--border)'" ondrop="handleDrop(event)">
                        <svg class="mx-auto mb-3 opacity-40" width="32" height="32" fill="none"
                            stroke="currentColor" stroke-width="1.25" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="12" x2="12" y2="18" />
                            <polyline points="9 15 12 12 15 15" />
                        </svg>
                        <p id="pdf-label" class="text-[13px] font-medium" style="color:var(--text-dim)">
                            Drop PDF here or <span style="color:var(--amber)">click to browse</span>
                        </p>
                        <p class="font-mono text-[10px] mt-1" style="color:var(--text-dim)">PDF only · max 50 MB</p>
                    </div>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="hidden"
                        onchange="updatePdfLabel(this)">
                </div>

                {{-- Title --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Report Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        placeholder="e.g. H1 2025 Security Report"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)"
                        required>
                </div>

                {{-- Period --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Report Period</label>
                    <input type="text" name="period" value="{{ old('period') }}"
                        placeholder="e.g. H1 2025 (January – June)"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                    <p class="font-mono text-[10px] mt-1.5" style="color:var(--text-dim)">
                        Shown as the label on the public report card.
                    </p>
                </div>

                {{-- Description --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Description</label>
                    <textarea name="description" rows="4" placeholder="Summarise what this report covers…"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition resize-none"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">{{ old('description') }}</textarea>
                    <p class="font-mono text-[10px] mt-1.5" style="color:var(--text-dim)">
                        Shown on the public report card. Max 2000 characters.
                    </p>
                </div>

            </div>

            {{-- ── RIGHT COLUMN — settings + thumbnail ────────────────────────── --}}
            <div class="space-y-4">

                {{-- Tier --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Minimum Tier</label>
                    <select name="min_tier" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                        <option value="1" {{ old('min_tier', '1') == '1' ? 'selected' : '' }}>
                            Tier 1 — Free (all registered users)
                        </option>
                        <option value="2" {{ old('min_tier') == '2' ? 'selected' : '' }}>
                            Tier 2+ — Standard & above
                        </option>
                        <option value="3" {{ old('min_tier') == '3' ? 'selected' : '' }}>
                            Tier 3 — Premium only
                        </option>
                    </select>
                    <p class="font-mono text-[10px] mt-1.5" style="color:var(--text-dim)">
                        Users below this tier see a lock and are prompted to upgrade.
                    </p>
                </div>

                {{-- Publish status --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">Publish Status</label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1"
                            {{ old('is_published', '1') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded accent-amber-400">
                        <span class="text-[13px]" style="color:var(--text-head)">
                            Publish immediately
                        </span>
                    </label>
                    <p class="font-mono text-[10px] mt-2" style="color:var(--text-dim)">
                        Uncheck to save as a draft — hidden from the public page.
                    </p>
                </div>

                {{-- Thumbnail --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">Cover Thumbnail (optional)</label>

                    {{-- Preview area --}}
                    <div id="thumb-preview" class="hidden mb-3 rounded-lg overflow-hidden"
                        style="border:1px solid var(--border)">
                        <img id="thumb-img" src="" alt="Thumbnail preview" class="w-full object-cover"
                            style="max-height:140px">
                    </div>

                    <div class="rounded-xl border-2 border-dashed transition cursor-pointer"
                        style="border-color:var(--border);padding:20px;text-align:center"
                        onclick="document.getElementById('thumbnail').click()"
                        ondragover="event.preventDefault();this.style.borderColor='var(--amber)'"
                        ondragleave="this.style.borderColor='var(--border)'" ondrop="handleThumbDrop(event)">
                        <svg class="mx-auto mb-2 opacity-40" width="24" height="24" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <p id="thumb-label" class="text-[12px]" style="color:var(--text-dim)">
                            <span style="color:var(--amber)">Browse</span> or drop image
                        </p>
                        <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">
                            JPG, PNG, WebP · max 5 MB
                        </p>
                    </div>
                    <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp"
                        class="hidden" onchange="previewThumb(this)">
                </div>

                {{-- Submit --}}
                <button type="submit" id="submit-btn"
                    class="w-full py-3 rounded-xl text-[14px] font-bold transition flex items-center justify-center gap-2"
                    style="background:var(--amber);color:#080E1A">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    Upload Report
                </button>

            </div>
        </div>
    </form>

    <script>
        function updatePdfLabel(input) {
            const label = document.getElementById('pdf-label');
            if (input.files && input.files[0]) {
                const name = input.files[0].name;
                const mb = (input.files[0].size / 1024 / 1024).toFixed(1);
                label.innerHTML = `<span style="color:var(--text-white);font-weight:600">${name}</span>
                           <span style="color:var(--text-dim);font-size:11px;margin-left:6px">${mb} MB</span>`;
                document.getElementById('pdf-drop').style.borderColor = 'var(--green)';
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('pdf-drop').style.borderColor = 'var(--border)';
            const file = e.dataTransfer.files[0];
            if (!file || file.type !== 'application/pdf') {
                alert('Please drop a PDF file.');
                return;
            }
            const input = document.getElementById('pdf_file');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            updatePdfLabel(input);
        }

        function previewThumb(input) {
            if (input.files && input.files[0]) {
                const label = document.getElementById('thumb-label');
                const name = input.files[0].name;
                label.innerHTML = `<span style="color:var(--text-white);font-weight:600">${name}</span>`;

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumb-img').src = e.target.result;
                    document.getElementById('thumb-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleThumbDrop(e) {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const input = document.getElementById('thumbnail');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            previewThumb(input);
        }

        // Disable submit button while uploading to prevent double submit
        document.getElementById('upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Uploading…';
        });
    </script>

</x-admin-layout>

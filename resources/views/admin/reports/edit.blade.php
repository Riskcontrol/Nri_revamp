<x-admin-layout>
    <x-slot:title>Edit Report</x-slot:title>

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
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">Edit Report</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                {{ $report->title }}
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

    <form action="{{ route('admin.reports.update', $report) }}" method="POST" enctype="multipart/form-data"
        id="edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ── LEFT COLUMN ─────────────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Current PDF info --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">PDF File</label>

                    {{-- Current file --}}
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-3"
                        style="background:var(--bg-raised);border:1px solid var(--border)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <span class="font-mono text-[11px] truncate" style="color:var(--text-dim)">
                            {{ $report->file_path }}
                        </span>
                        <span class="font-mono text-[10px] ml-auto flex-shrink-0" style="color:var(--green)">
                            Current
                        </span>
                    </div>

                    <div id="pdf-drop" class="rounded-xl border-2 border-dashed transition cursor-pointer"
                        style="border-color:var(--border);padding:24px 20px;text-align:center"
                        onclick="document.getElementById('pdf_file').click()"
                        ondragover="event.preventDefault();this.style.borderColor='var(--amber)'"
                        ondragleave="this.style.borderColor='var(--border)'" ondrop="handleDrop(event)">
                        <p id="pdf-label" class="text-[12px]" style="color:var(--text-dim)">
                            <span style="color:var(--amber)">Upload new PDF</span> to replace
                            <span class="font-mono text-[10px] block mt-0.5">Leave empty to keep current · PDF only ·
                                max 50 MB</span>
                        </p>
                    </div>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="hidden"
                        onchange="updatePdfLabel(this)">
                </div>

                {{-- Title --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Report Title *</label>
                    <input type="text" name="title" value="{{ old('title', $report->title) }}"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)"
                        required>
                </div>

                {{-- Period --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Report Period</label>
                    <input type="text" name="period" value="{{ old('period', $report->period) }}"
                        placeholder="e.g. H1 2025 (January – June)"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                </div>

                {{-- Description --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 rounded-lg text-[13px] outline-none transition resize-none"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">{{ old('description', $report->description) }}</textarea>
                </div>

            </div>

            {{-- ── RIGHT COLUMN ─────────────────────────────────────────────────── --}}
            <div class="space-y-4">

                {{-- Tier --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-1.5"
                        style="color:var(--text-dim)">Minimum Tier</label>
                    <select name="min_tier" class="w-full px-3 py-2 rounded-lg text-[13px] outline-none"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-head)">
                        <option value="1" {{ old('min_tier', $report->min_tier) == 1 ? 'selected' : '' }}>
                            Tier 1 — Free
                        </option>
                        <option value="2" {{ old('min_tier', $report->min_tier) == 2 ? 'selected' : '' }}>
                            Tier 2+ — Standard & above
                        </option>
                        <option value="3" {{ old('min_tier', $report->min_tier) == 3 ? 'selected' : '' }}>
                            Tier 3 — Premium only
                        </option>
                    </select>
                </div>

                {{-- Publish status --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">Publish Status</label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1"
                            {{ old('is_published', $report->is_published ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded accent-amber-400">
                        <span class="text-[13px]" style="color:var(--text-head)">Published (visible on site)</span>
                    </label>
                </div>

                {{-- Thumbnail --}}
                <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                    <label class="font-mono text-[9px] tracking-widest uppercase block mb-3"
                        style="color:var(--text-dim)">Cover Thumbnail</label>

                    {{-- Current thumbnail --}}
                    @if ($report->image_path)
                        <div class="mb-3">
                            <img src="{{ asset($report->image_path) }}" alt="Current thumbnail"
                                class="w-full rounded-lg object-cover"
                                style="max-height:120px;border:1px solid var(--border)">
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="remove_thumbnail" value="1"
                                    class="w-3.5 h-3.5 accent-red-500">
                                <span class="text-[12px]" style="color:var(--red)">Remove thumbnail</span>
                            </label>
                        </div>
                    @endif

                    {{-- New thumbnail drop area --}}
                    <div class="rounded-xl border-2 border-dashed transition cursor-pointer"
                        style="border-color:var(--border);padding:16px;text-align:center"
                        onclick="document.getElementById('thumbnail').click()"
                        ondragover="event.preventDefault();this.style.borderColor='var(--amber)'"
                        ondragleave="this.style.borderColor='var(--border)'" ondrop="handleThumbDrop(event)">
                        <div id="thumb-preview" class="hidden mb-2 rounded overflow-hidden">
                            <img id="thumb-img" src="" alt="" class="w-full object-cover"
                                style="max-height:100px">
                        </div>
                        <p id="thumb-label" class="text-[12px]" style="color:var(--text-dim)">
                            <span style="color:var(--amber)">Upload new thumbnail</span>
                            <span class="font-mono text-[10px] block mt-0.5">JPG, PNG, WebP · max 5 MB</span>
                        </p>
                    </div>
                    <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp"
                        class="hidden" onchange="previewThumb(this)">
                </div>

                {{-- Save --}}
                <button type="submit" id="submit-btn"
                    class="w-full py-3 rounded-xl text-[14px] font-bold transition flex items-center justify-center gap-2"
                    style="background:var(--amber);color:#080E1A">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    Save Changes
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
                label.innerHTML =
                    `<span style="color:var(--text-white);font-weight:600">${name}</span>
                           <span style="color:var(--text-dim);font-size:11px;margin-left:6px">${mb} MB · will replace current PDF</span>`;
                document.getElementById('pdf-drop').style.borderColor = 'var(--amber)';
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
                label.innerHTML = `<span style="color:var(--text-white);font-weight:600">${input.files[0].name}</span>`;
                const reader = new FileReader();
                reader.onload = e => {
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

        document.getElementById('edit-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Saving…';
        });
    </script>

</x-admin-layout>

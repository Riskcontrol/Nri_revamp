<x-admin-layout>
    <x-slot:title>Create Insight</x-slot:title>

    {{-- TinyMCE --}}
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.key') }}/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <div class="max-w-4xl mx-auto">

        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.insights.index') }}"
                class="text-gray-400 hover:text-white transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back
            </a>
            <h2 class="text-2xl font-bold text-white">Create New Insight</h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded mb-6">
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5 text-sm">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-[#1E2D3D] border border-white/5 rounded-xl p-6 shadow-xl">
            <form action="{{ route('admin.insights.store') }}" method="POST" enctype="multipart/form-data"
                id="create-insight-form">
                @csrf

                <div class="space-y-6">

                    {{-- Title --}}
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                            Title *
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            placeholder="e.g. Security Analysis: Borno State Q1 2025"
                            class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white
                                  focus:border-blue-500 focus:outline-none transition"
                            required>
                    </div>

                    {{-- State + Category --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                                State / Scope *
                            </label>
                            <select name="state"
                                class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white
                                       focus:border-blue-500 focus:outline-none transition">
                                <option value="National" {{ old('state') === 'National' ? 'selected' : '' }}>
                                    National
                                </option>
                                @foreach ($states as $state)
                                    <option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                                Category
                            </label>
                            <select name="category_id"
                                class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white
                                       focus:border-blue-500 focus:outline-none transition">
                                <option value="">— No category —</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Short description --}}
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                            Short Description / Excerpt *
                        </label>
                        <textarea name="description" rows="3" placeholder="A brief summary shown on listing pages and cards…"
                            class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white
                                     focus:border-blue-500 focus:outline-none transition">{{ old('description') }}</textarea>
                        <p class="text-gray-500 text-xs mt-1">Max 1000 characters.</p>
                    </div>

                    {{-- Full content --}}
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                            Full Content *
                        </label>
                        <textarea id="insight-content" name="content">{{ old('content') }}</textarea>
                    </div>

                    {{-- Keywords --}}
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                            Keywords (optional)
                        </label>
                        <input type="text" name="keywords" value="{{ old('keywords') }}"
                            placeholder="e.g. terrorism, Borno, kidnapping, northeast"
                            class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white
                                  focus:border-blue-500 focus:outline-none transition">
                        <p class="text-gray-500 text-xs mt-1">Comma-separated keywords for SEO.</p>
                    </div>

                    {{-- Feature image --}}
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">
                            Feature Image (optional)
                        </label>

                        <div id="img-drop"
                            class="border-2 border-dashed border-white/10 rounded-xl p-6 text-center cursor-pointer
                                hover:border-blue-500/50 transition"
                            onclick="document.getElementById('featureimage').click()"
                            ondragover="event.preventDefault();this.style.borderColor='rgba(59,130,246,0.5)'"
                            ondragleave="this.style.borderColor='rgba(255,255,255,0.1)'" ondrop="handleImgDrop(event)">
                            <div id="img-preview" class="hidden mb-3">
                                <img id="img-preview-src" src="" alt=""
                                    class="max-h-36 mx-auto rounded object-cover">
                            </div>
                            <svg class="mx-auto mb-2 opacity-30" width="28" height="28" fill="none"
                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                            <p id="img-label" class="text-gray-400 text-sm">
                                Drop image here or <span class="text-blue-400">click to browse</span>
                            </p>
                            <p class="text-gray-600 text-xs mt-1">JPG, PNG, WebP · max 4 MB</p>
                        </div>
                        <input type="file" id="featureimage" name="featureimage" accept="image/*" class="hidden"
                            onchange="previewImg(this)">
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <a href="{{ route('admin.insights.index') }}"
                            class="px-6 py-2 rounded text-gray-400 hover:text-white transition">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn"
                            class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded
                                   font-bold shadow-lg shadow-blue-500/20 transition">
                            Publish Insight
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        // TinyMCE
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#insight-content',
                height: 500,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent | removeformat | help',
                skin: 'oxide-dark',
                content_css: 'dark',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; ' +
                    'background-color: #0F172A; color: #e2e8f0; } a { color: #3b82f6; }',
            });
        });

        // Image preview
        function previewImg(input) {
            if (input.files && input.files[0]) {
                document.getElementById('img-label').innerHTML =
                    '<span class="text-blue-400">' + input.files[0].name + '</span>';
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('img-preview-src').src = e.target.result;
                    document.getElementById('img-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleImgDrop(e) {
            e.preventDefault();
            document.getElementById('img-drop').style.borderColor = 'rgba(255,255,255,0.1)';
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const input = document.getElementById('featureimage');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            previewImg(input);
        }

        // Disable button during submit
        document.getElementById('create-insight-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Publishing…';
        });
    </script>

</x-admin-layout>

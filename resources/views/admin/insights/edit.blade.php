<x-admin-layout>
    <x-slot:title>Edit Insight</x-slot:title>

    {{-- 1. Load TinyMCE from CDN --}}
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

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
            <h2 class="text-2xl font-bold text-white">Edit Insight</h2>
        </div>

        <div class="bg-[#1E2D3D] border border-white/5 rounded-xl p-6 shadow-xl">
            <form action="{{ route('admin.insights.update', $insight->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">Title</label>
                        <input type="text" name="title" value="{{ old('title', $insight->title) }}"
                            class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white focus:border-blue-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">State</label>
                            <select name="state"
                                class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white focus:border-blue-500 focus:outline-none transition">
                                <option value="National" {{ $insight->state == 'National' ? 'selected' : '' }}>National
                                </option>
                                <option value="Lagos" {{ $insight->state == 'Lagos' ? 'selected' : '' }}>Lagos</option>
                                <option value="Abuja" {{ $insight->state == 'Abuja' ? 'selected' : '' }}>Abuja</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">Category</label>
                            <select name="category_id"
                                class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white focus:border-blue-500 focus:outline-none transition">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $insight->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">Short
                            Description (Excerpt)</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-[#0F172A] border border-white/10 rounded p-3 text-white focus:border-blue-500 focus:outline-none transition">{{ old('description', $insight->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold tracking-wider mb-2">Full
                            Content</label>

                        {{-- The ID 'insight-content' is targeted by the script below --}}
                        <textarea id="insight-content" name="content">{{ old('content', $insight->content) }}</textarea>

                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <a href="{{ route('admin.insights.index') }}"
                            class="px-6 py-2 rounded text-gray-400 hover:text-white transition">Cancel</a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold shadow-lg shadow-blue-500/20 transition">
                            Update Insight
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Initialize TinyMCE with Dark Mode Configuration --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: '#insight-content',
                height: 500,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',

                // --- DARK MODE SETTINGS ---
                skin: 'oxide-dark',
                content_css: 'dark',
                // This ensures the editor body background matches your app roughly
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; background-color: #0F172A; color: #e2e8f0; } a { color: #3b82f6; }',
            });
        });
    </script>

</x-admin-layout>

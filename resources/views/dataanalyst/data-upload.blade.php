<x-layout title="Data Upload">

    <div class="container mx-auto px-4 py-8">

        {{-- Alert Messages --}}
        @if (session('successAlert'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('successAlert') }}</p>
            </div>
        @endif

        @if (session('errorAlert'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('errorAlert') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <p class="font-bold">Please fix the following errors:</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upload Form --}}
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-8">
            <h2 class="text-2xl font-bold mb-6">Upload Incident Data</h2>

            <form action="{{ route('data.import.store') }}" method="POST" enctype="multipart/form-data"
                id="uploadForm">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="incident_file">
                        Select Excel File (XLSX, XLS, XLSM)
                    </label>
                    <input type="file" name="incident_file" id="incident_file" accept=".xlsx,.xls,.xlsm,.xlsb"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                    <p class="text-gray-600 text-xs mt-2">Maximum file size: 10MB</p>
                </div>

                <div id="fileInfo" class="mb-4 hidden">
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm"><strong>File:</strong> <span id="fileName"></span></p>
                        <p class="text-sm"><strong>Size:</strong> <span id="fileSize"></span></p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline"
                        id="submitBtn">
                        Upload and Process
                    </button>
                </div>

                <div id="uploadProgress" class="mt-6 hidden">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"
                            id="progressBar"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2 text-center">Processing file... Please wait.</p>
                </div>
            </form>
        </div>

        {{-- Import History --}}
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Import History</h2>
            </div>

            @if ($recentImports->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-sm leading-normal">
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">File Name</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Success</th>
                                <th class="py-3 px-4 text-center">Failed</th>
                                <th class="py-3 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @foreach ($recentImports as $import)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-4">
                                        {{ $import->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4">{{ $import->sheet_name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($import->isSuccessful())
                                            <span
                                                class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs font-semibold">Complete</span>
                                        @elseif($import->isPartial())
                                            <span
                                                class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs font-semibold">Partial</span>
                                        @else
                                            <span
                                                class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs font-semibold">Failed</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="text-green-600 font-semibold">{{ $import->rows_inserted }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($import->rows_failed > 0)
                                            <span class="text-red-600 font-semibold">{{ $import->rows_failed }}</span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('data.import.show', $import->id) }}"
                                            class="text-blue-600 hover:text-blue-800 font-medium">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $recentImports->links() }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="text-lg">No imports yet.</p>
                    <p>Upload your first file above to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('incident_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                document.getElementById('fileInfo').classList.remove('hidden');
            }
        });

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            document.getElementById('uploadProgress').classList.remove('hidden');

            // Simulate progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 5;
                if (progress <= 90) {
                    document.getElementById('progressBar').style.width = progress + '%';
                } else {
                    clearInterval(interval);
                }
            }, 500);
        });
    </script>
</x-layout>

<x-layout title="Import Details">
    <div class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('data.import.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Import History
            </a>
        </div>

        <!-- Import Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Import Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded">
                    <p class="text-sm text-gray-600">File Name</p>
                    <p class="text-lg font-semibold">{{ $import->sheet_name }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Successful Imports</p>
                    <p class="text-2xl font-bold text-green-600">{{ $import->rows_inserted }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Failed Imports</p>
                    <p class="text-2xl font-bold text-red-600">{{ $import->rows_failed }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <p class="text-sm text-gray-600">
                    <strong>Import Date:</strong>
                    {{ \Carbon\Carbon::parse($import->created_at)->format('M d, Y H:i:s') }}
                </p>
                @if ($import->rows_inserted > 0)
                    <a href="{{ route('data.import.export-incidents', $import->id) }}"
                        class="text-blue-600 hover:text-blue-800">
                        📥 Export Imported Data
                    </a>
                @endif
            </div>

            <!-- Success Rate Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Success Rate</span>
                    <span>{{ $import->rows_inserted > 0 ? round(($import->rows_inserted / ($import->rows_inserted + $import->rows_failed)) * 100, 1) : 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-green-500 h-3 rounded-full"
                        style="width: {{ $import->rows_inserted > 0 ? ($import->rows_inserted / ($import->rows_inserted + $import->rows_failed)) * 100 : 0 }}%">
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Rows Section -->
        @if ($import->rows_failed > 0 && !empty($failedRows))
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-red-600">Failed Rows ({{ count($failedRows) }})</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('data.import.download-failed', $import->id) }}"
                            class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                            Download Failed Rows
                        </a>
                        {{-- <form action="{{ route('data.import.reprocess', $import->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Reprocess Failed Rows
                            </button>
                        </form> --}}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error
                                    Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($failedRows as $error)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $error['row_num'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-red-600">
                                        @if (is_array($error))
                                            <ul class="list-disc list-inside">
                                                @foreach ($error as $key => $value)
                                                    @if ($key !== 'row_num')
                                                        <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $error }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Successfully Imported Incidents -->
        @if ($import->rows_inserted > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4 text-green-600">Successfully Imported Incidents</h3>

                <!-- Filters -->
                <div class="mb-4 flex gap-4">
                    <input type="text" id="searchIncidents" placeholder="Search by Event ID, Location, Caption..."
                        class="px-3 py-2 border rounded flex-1">
                    <select id="filterRiskFactor" class="px-3 py-2 border rounded">
                        <option value="">All Risk Factors</option>
                        <!-- Populate dynamically -->
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event ID
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Factor
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Impact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Casualties
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Caption</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($importedIncidents as $incident)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $incident->eventid }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $incident->eventdate }}</td>
                                    <td class="px-4 py-3">{{ $incident->location }}, {{ $incident->lga }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                            {{ $incident->riskfactors }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-1 rounded text-xs
                                        {{ $incident->impact == 'High' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $incident->impact == 'Medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $incident->impact == 'Low' ? 'bg-green-100 text-green-800' : '' }}">
                                            {{ $incident->impact }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{ $incident->Casualties_count ?? 0 }}
                                    </td>
                                    <td class="px-4 py-3 max-w-xs truncate" title="{{ $incident->caption }}">
                                        {{ $incident->caption }}
                                    </td>
                                    {{-- <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('incidents.show', $incident->eventid) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            View
                                        </a>
                                        <span class="text-gray-300 mx-1">|</span>
                                        <a href="{{ route('incidents.edit', $incident->eventid) }}"
                                            class="text-green-600 hover:text-green-800">
                                            Edit
                                        </a>
                                    </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        No incidents found for this import.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $importedIncidents->links() }}
                </div>
            </div>
        @endif
    </div>

    <script>
        // Search functionality
        document.getElementById('searchIncidents')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</x-layout>

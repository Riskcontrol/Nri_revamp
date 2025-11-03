@if ($results->isEmpty())
    <div class="text-white text-center">No data found for this state.</div>
@else
    <div id="state-title" class="mb-4 text-xl font-bold text-white">
        Showing results for <span class="text-green-400">{{ $stateName ?? 'Selected State' }}</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($results as $item)
            <div class="bg-gray-900 rounded-lg shadow p-4 text-white report-card">


                <div class=" flex items-center gap-2 mb-2">

                    <p class="text-lg font-semi-bold text-white">{{ $item->industry_risk_type }}
                        ({{ $item->industry_subtype }})
                    </p>
                </div>

                @php
                    $level = strtolower($item->level);
                    $badgeClass = match ($level) {
                        'low' => 'bg-green-500',
                        'medium' => 'bg-blue-400',
                        'high' => 'bg-purple-700',
                        'critical' => 'bg-red-600',
                        default => 'bg-gray-600',
                    };
                @endphp

                <div class="flex items-center gap-2 mt-2">
                    <p class="text-sm text-gray-400 font-semibold mb-0">Impact Level:</p>
                    <span class="inline-block px-3 py-1 {{ $badgeClass }} text-white text-sm rounded">
                        {{ ucfirst($item->level) }}
                    </span>
                </div>

                {{-- <p class="mt-2 text-sm font-medium">
                    {{ $item->description ?? 'No description available.' }}
                </p> --}}
            </div>
        @endforeach
    </div>


    <div class="mt-6 p-4 bg-gray-800 rounded-lg text-white text-center">
        <p class="text-lg font-semibold mb-3">
            It's a great idea to compare business risks across Nigerian states.
        </p>
        <a href="{{ route('stateComparisonNew', ['state' => $stateName ?? '']) }}"
            class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold px-5 py-2 rounded transition duration-200">
            Compare States
        </a>
    </div>

@endif

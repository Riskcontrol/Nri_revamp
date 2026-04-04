{{-- File: resources/views/admin/whatsapp/log.blade.php --}}

<x-admin-layout title="WhatsApp Alert Log">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">WhatsApp Delivery Log</h1>
                <p class="text-sm text-gray-400 mt-1">All outbound alert messages, newest first.</p>
            </div>
            <a href="{{ route('admin.whatsapp.subscribers') }}"
                class="text-sm text-gray-400 hover:text-white transition-colors underline">
                ← Subscribers
            </a>
        </div>

        <div class="bg-[#1e2d3d] rounded-xl border border-white/10 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 text-xs text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Sent At</th>
                        <th class="px-5 py-3 text-left">Event ID</th>
                        <th class="px-5 py-3 text-left">To</th>
                        <th class="px-5 py-3 text-left">Risk Level</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Twilio SID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-300">{{ $log->eventid }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-300">{{ $log->phone_number }}</td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold
                                    {{ $log->risk_level === 'Critical' ? 'bg-red-900/50 text-red-300' : 'bg-orange-900/50 text-orange-300' }}">
                                    {{ $log->risk_level }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusClass = match ($log->status) {
                                        'delivered' => 'text-green-400',
                                        'sent' => 'text-blue-400',
                                        'failed' => 'text-red-400',
                                        default => 'text-gray-400',
                                    };
                                @endphp
                                <span class="text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                @if ($log->error_message)
                                    <p class="text-[10px] text-red-400 mt-0.5">{{ Str::limit($log->error_message, 60) }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-mono text-[11px] text-gray-500">
                                {{ $log->twilio_sid ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 text-sm">
                                No alerts sent yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>

</x-admin-layout>

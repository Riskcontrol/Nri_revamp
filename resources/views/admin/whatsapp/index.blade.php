{{-- File: resources/views/admin/whatsapp/index.blade.php --}}

<x-admin-layout title="WhatsApp Subscribers">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">WhatsApp Alert Subscribers</h1>
                <p class="text-sm text-gray-400 mt-1">
                    {{ $subscribers->total() }} total · {{ $subscribers->where('is_active', true)->count() }} active on
                    this page
                </p>
            </div>
            <a href="{{ route('admin.whatsapp.log') }}"
                class="text-sm text-gray-400 hover:text-white transition-colors underline">
                View delivery log →
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-900/40 border border-green-700 px-4 py-3 text-sm text-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-[#1e2d3d] rounded-xl border border-white/10 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 text-xs text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Phone</th>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Tier</th>
                        <th class="px-5 py-3 text-left">States</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Opted In</th>
                        <th class="px-5 py-3 text-left">Alerts Sent</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($subscribers as $sub)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-gray-200">{{ $sub->phone_number }}</td>
                            <td class="px-5 py-3 text-gray-300">{{ $sub->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold
                                    {{ $sub->subscription_tier === 'critical' ? 'bg-red-900/50 text-red-300' : 'bg-blue-900/50 text-blue-300' }}">
                                    {{ $sub->subscription_tier === 'critical' ? 'Critical only' : 'High + Critical' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">
                                {{ $sub->state_filter ? implode(', ', $sub->state_filter) : 'All states' }}
                            </td>
                            <td class="px-5 py-3">
                                @if ($sub->is_active)
                                    <span class="inline-flex items-center gap-1 text-green-400 text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span> Active
                                    </span>
                                @else
                                    <span class="text-gray-500 text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">
                                {{ $sub->opted_in_at ? $sub->opted_in_at->format('d M Y') : '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-300 text-xs">
                                {{ $logCounts[$sub->phone_number] ?? 0 }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($sub->is_active)
                                    <form method="POST"
                                        action="{{ route('admin.whatsapp.subscribers.destroy', $sub) }}"
                                        onsubmit="return confirm('Deactivate this subscriber?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs text-red-400 hover:text-red-300 transition-colors">
                                            Deactivate
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-500 text-sm">
                                No subscribers yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $subscribers->links() }}
    </div>

</x-admin-layout>

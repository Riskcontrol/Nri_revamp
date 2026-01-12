<x-admin-layout>
    <x-slot:title>Insights Management</x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Data Insights</h2>
        <a href="#"
            class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded text-sm font-medium transition">
            + Create New Insight
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500 text-green-500 p-4 rounded mb-4">{{ session('success') }}
        </div>
    @endif

    <div class="bg-[#1E2D3D] border border-white/5 rounded-xl overflow-hidden">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-[#0E1B2C] text-gray-400 uppercase text-[10px] tracking-widest">
                <tr>
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">State</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Last Updated</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($insights as $insight)
                    <tr class="hover:bg-white/[0.02]">
                        <td class="px-6 py-4 font-medium text-white max-w-xs truncate">{{ $insight->title }}</td>
                        <td class="px-6 py-4">{{ $insight->state ?? 'National' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-blue-500/10 text-blue-400 rounded text-xs">
                                {{ $insight->category->name ?? 'General' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $insight->updated_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right flex justify-end gap-3">
                            <a href="{{ route('admin.insights.edit', $insight->id) }}"
                                class="text-blue-400 hover:text-blue-300 transition">Edit</a>
                            <form action="{{ route('admin.insights.destroy', $insight->id) }}" method="POST"
                                onsubmit="return confirm('Delete this insight permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-400 hover:text-red-300 transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No insights found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-white/5">
            {{ $insights->links() }}
        </div>
    </div>
</x-admin-layout>

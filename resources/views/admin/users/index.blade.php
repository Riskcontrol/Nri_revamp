<x-admin-layout>
    <x-slot:title>User Management</x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Registered Users</h2>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500 text-green-500 p-4 rounded mb-4">{{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-500/10 border border-red-500 text-red-500 p-4 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-[#1E2D3D] border border-white/5 rounded-xl overflow-hidden">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-[#0E1B2C] text-gray-400 uppercase text-[10px] tracking-widest">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Organization</th>
                    <th class="px-6 py-4">Role/Access</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach ($users as $user)
                    <tr class="hover:bg-white/[0.02]">
                        <td class="px-6 py-4 font-medium text-white">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">{{ $user->organization }}</td>
                        <td class="px-6 py-4">
                            @if ($user->access_level >= 1)
                                <span class="px-2 py-1 bg-green-500/10 text-green-500 rounded text-xs">Verified</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-500/10 text-yellow-500 rounded text-xs">Demo
                                    User</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-400 hover:text-red-300 transition text-xs uppercase font-bold tracking-wider">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4 border-t border-white/5">
            {{ $users->links() }}
        </div>
    </div>
</x-admin-layout>

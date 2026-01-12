<x-admin-layout>
    <x-slot:title>Main Dashboard</x-slot:title>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#1E2D3D] border border-white/5 p-5 rounded-xl shadow-sm">
            <p class="text-gray-400 text-xs uppercase font-bold tracking-wider">Total Entries</p>
            <h3 class="text-2xl font-bold mt-1">42,890</h3>
            <span class="text-green-500 text-xs font-medium">↑ 12% from last month</span>
        </div>
        <div class="bg-[#1E2D3D] border border-white/5 p-5 rounded-xl shadow-sm">
            <p class="text-gray-400 text-xs uppercase font-bold tracking-wider">Pending Review</p>
            <h3 class="text-2xl font-bold mt-1 text-[#FDA557]">128</h3>
            <span class="text-gray-500 text-xs">Awaiting moderation</span>
        </div>
        <div class="bg-[#1E2D3D] border border-white/5 p-5 rounded-xl shadow-sm">
            <p class="text-gray-400 text-xs uppercase font-bold tracking-wider">Active Users</p>
            <h3 class="text-2xl font-bold mt-1">1,204</h3>
            <span class="text-green-500 text-xs font-medium">Online now</span>
        </div>
        <div class="bg-[#1E2D3D] border border-white/5 p-5 rounded-xl shadow-sm">
            <p class="text-gray-400 text-xs uppercase font-bold tracking-wider">System Health</p>
            <h3 class="text-2xl font-bold mt-1 text-blue-400">99.9%</h3>
            <span class="text-gray-500 text-xs">Operational</span>
        </div>
    </div>

    <div class="bg-[#1E2D3D] border border-white/5 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex justify-between items-center">
            <h2 class="font-semibold text-gray-200">Recent Incident Logs</h2>
            <button class="text-xs bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md transition">Export CSV</button>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-[#0E1B2C] text-gray-400 uppercase text-[10px] tracking-widest">
                <tr>
                    <th class="px-6 py-3">State</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Intensity</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-6 py-4 font-medium">Borno</td>
                    <td class="px-6 py-4">Insecurity</td>
                    <td class="px-6 py-4"><span class="text-red-400 font-bold">High</span></td>
                    <td class="px-6 py-4 text-gray-400">2026-01-08</td>
                    <td class="px-6 py-4 text-right"><span
                            class="px-2 py-1 rounded bg-green-500/10 text-green-500 text-[10px]">Verified</span></td>
                </tr>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-6 py-4 font-medium">Lagos</td>
                    <td class="px-6 py-4">Economic</td>
                    <td class="px-6 py-4 text-yellow-400 font-bold">Medium</td>
                    <td class="px-6 py-4 text-gray-400">2026-01-07</td>
                    <td class="px-6 py-4 text-right"><span
                            class="px-2 py-1 rounded bg-yellow-500/10 text-yellow-500 text-[10px]">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</x-admin-layout>

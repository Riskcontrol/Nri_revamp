<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0A1628] border-r border-white/10 lg:translate-x-0" id="sidebar">
    <div class="flex items-center justify-between h-16 px-6 bg-[#0E1B2C]">
        <span class="text-white font-bold tracking-tight uppercase">Admin Portal</span>
    </div>

    <nav class="mt-6 px-4 space-y-1">
        <x-admin.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="dashboard">
            Dashboard
        </x-admin.nav-link>

        <div class="text-xs font-semibold text-gray-500 uppercase mt-8 mb-2 px-2">Data Management</div>
        <x-admin.nav-link href="#" icon="database">Incidents</x-admin.nav-link>
        <x-admin.nav-link href="#" icon="database">Risk Factors</x-admin.nav-link>
    </nav>
</aside>

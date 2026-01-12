<aside
    class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0A1628] border-r border-white/10 transition-transform lg:translate-x-0"
    id="sidebar">
    <div class="flex items-center justify-between h-16 px-6 bg-[#0E1B2C]">
        <a href="/" class="flex items-center gap-2">
            <img src="{{ asset('images/nri-logo-white.png') }}" alt="Logo" class="h-8">
            <span class="text-white font-bold tracking-tight">Admin Portal</span>
        </a>
    </div>

    <nav class="mt-6 px-4 space-y-1">
        <x-admin.nav-link href="#" :active="true" icon="dashboard">Dashboard</x-admin.nav-link>

        <x-admin.nav-link href="{{ route('admin.insights.index') }}" :active="request()->routeIs('admin.insights.*')" icon="file-text">
            Insights / Reports
        </x-admin.nav-link>

        <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="users">
            User Management
        </x-admin.nav-link>
    </nav>
</aside>

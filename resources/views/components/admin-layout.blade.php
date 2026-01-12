<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0F172A]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Dashboard' }} | NRI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-white">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-[#0A1628] border-r border-white/10 hidden lg:flex flex-col fixed inset-y-0">
            <div class="h-16 flex items-center px-6 bg-[#0E1B2C] border-b border-white/5">
                <span class="text-[#FDA557] font-bold tracking-widest text-lg">NRI <span
                        class="text-white/50 font-light">ADMIN</span></span>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-[#1E2D3D] text-white">
                    <span>📊</span> Dashboard
                </a>
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management
                </div>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition">
                    <span>🗄️</span> Incident Logs
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition">
                    <span>📍</span> Risk Factors
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition">
                    <span>👥</span> User Roles
                </a>
            </nav>
        </aside>

        <div class="lg:pl-64 flex flex-col flex-1">
            <header
                class="h-16 bg-[#0A1628]/80 backdrop-blur border-b border-white/10 flex items-center justify-between px-8 sticky top-0 z-10">
                <h1 class="text-sm font-medium text-gray-400">System Overview</h1>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs font-bold text-white">System Admin</p>
                        <p class="text-[10px] text-gray-500 uppercase">Superuser</p>
                    </div>
                    <div
                        class="h-8 w-8 rounded bg-[#FDA557] flex items-center justify-center text-[#0A1628] font-bold text-xs">
                        SA
                    </div>
                </div>
            </header>

            <main class="p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>

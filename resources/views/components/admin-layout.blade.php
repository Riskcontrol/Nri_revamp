<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — NRI Control Centre</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Syne:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg-base: #080E1A;
            --bg-surface: #0D1627;
            --bg-card: #111E30;
            --bg-raised: #16243A;
            --border: rgba(255, 255, 255, 0.06);
            --border-lit: rgba(255, 255, 255, 0.12);
            --text-dim: #4B6080;
            --text-muted: #6B849E;
            --text-body: #94A8BF;
            --text-head: #CBD8E8;
            --text-white: #EEF3F9;
            --amber: #FDA557;
            --amber-dim: rgba(253, 165, 87, 0.12);
            --green: #34D399;
            --green-dim: rgba(52, 211, 153, 0.1);
            --red: #F87171;
            --red-dim: rgba(248, 113, 113, 0.1);
            --blue: #60A5FA;
            --blue-dim: rgba(96, 165, 250, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg-base);
            color: var(--text-white);
            font-family: 'Syne', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        /* Sidebar */
        #sidebar {
            width: 248px;
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            transition: transform 0.22s cubic-bezier(.4, 0, .2, 1);
        }

        .nav-section-label {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.18em;
            color: var(--text-dim);
            text-transform: uppercase;
            padding: 20px 16px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            margin: 1px 8px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }

        .nav-item:hover {
            background: var(--bg-raised);
            color: var(--text-head);
        }

        .nav-item.active {
            background: var(--amber-dim);
            color: var(--amber);
        }

        .nav-item.active .nav-icon {
            opacity: 1;
        }

        .nav-icon {
            width: 16px;
            height: 16px;
            opacity: 0.5;
            flex-shrink: 0;
        }

        .nav-item.active .nav-icon {
            opacity: 1;
        }

        .nav-badge {
            margin-left: auto;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 10px;
            padding: 1px 7px;
            border-radius: 99px;
            background: var(--bg-raised);
            color: var(--text-muted);
        }

        .nav-item.active .nav-badge {
            background: var(--amber-dim);
            color: var(--amber);
        }

        /* Topbar */
        #topbar {
            height: 56px;
            background: rgba(8, 14, 26, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        /* Stat cards */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent-color, var(--border-lit));
            border-radius: 12px 12px 0 0;
        }

        /* Table */
        .data-table th {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 9.5px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--text-dim);
            padding: 10px 20px;
            background: var(--bg-surface);
            font-weight: 500;
            white-space: nowrap;
        }

        .data-table td {
            padding: 13px 20px;
            font-size: 13px;
            color: var(--text-body);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background: rgba(255, 255, 255, 0.015);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-lit);
            border-radius: 4px;
        }

        /* Mobile overlay */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
        }

        @media (max-width: 1023px) {
            #sidebar {
                position: fixed;
                inset-y: 0;
                left: 0;
                z-index: 50;
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>

<body class="h-full flex">

    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
        class="flex flex-col flex-shrink-0 inset-y-0 left-0 lg:sticky lg:top-0 lg:h-screen overflow-y-auto">

        {{-- Logo --}}
        <div class="flex items-center justify-between h-16 px-4 flex-shrink-0"
            style="border-bottom:1px solid var(--border)">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/nri-logo.png') }}" alt="Nigeria Risk Index"
                    class="h-10 w-auto object-contain flex-shrink-0">
                {{-- <span class="font-mono text-[9px] tracking-widest uppercase leading-none"
                    style="color:var(--text-dim)">Control<br>Centre</span> --}}
            </a>
            <button onclick="closeSidebar()" class="lg:hidden p-1 rounded" style="color:var(--text-dim)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-3">
            <div class="nav-section-label">Overview</div>

            <a href="{{ route('admin.dashboard') }}"
                class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
                Dashboard
            </a>

            <div class="nav-section-label">Data Management</div>

            {{-- All Incidents — the new data table page --}}
            <a href="{{ route('admin.incidents.index') }}"
                class="nav-item {{ request()->routeIs('admin.incidents.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                    <line x1="9" y1="12" x2="15" y2="12" />
                    <line x1="9" y1="16" x2="12" y2="16" />
                </svg>
                All Incidents
            </a>

            <a href="{{ route('admin.data-import.index') }}"
                class="nav-item {{ request()->routeIs('admin.data-import.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                Bulk Import
            </a>

            <a href="{{ route('admin.file-processor.index') }}"
                class="nav-item {{ request()->routeIs('admin.file-processor.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                    <polyline points="13 2 13 9 20 9" />
                    <line x1="9" y1="14" x2="15" y2="14" />
                    <line x1="9" y1="18" x2="12" y2="18" />
                </svg>
                AI File Processor
            </a>

            <div class="nav-section-label">Content</div>

            <a href="{{ route('admin.insights.index') }}"
                class="nav-item {{ request()->routeIs('admin.insights.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                Insights & Reports
            </a>

            <div class="nav-section-label">Access</div>

            <a href="{{ route('admin.users.index') }}"
                class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Users
            </a>

        </nav>

        {{-- Bottom: logged-in admin --}}
        <div class="px-4 py-4 flex-shrink-0" style="border-top:1px solid var(--border)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-bold flex-shrink-0"
                    style="background:var(--amber-dim);color:var(--amber)">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[12px] font-semibold truncate" style="color:var(--text-head)">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </p>
                    <p class="font-mono text-[10px]" style="color:var(--text-dim)">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ml-auto flex-shrink-0">
                    @csrf
                    <button type="submit" title="Log out" class="p-1.5 rounded transition hover:opacity-100"
                        style="color:var(--text-dim);opacity:0.6">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN COLUMN --}}
    <div class="flex flex-col flex-1 min-w-0 lg:h-screen lg:overflow-y-auto">

        <header id="topbar" class="sticky top-0 z-30 flex items-center justify-between px-6 flex-shrink-0">
            <button onclick="openSidebar()" class="lg:hidden p-1.5 rounded mr-3"
                style="color:var(--text-muted);background:var(--bg-raised)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>

            <div class="flex items-center gap-2" style="color:var(--text-dim)">
                <img src="{{ asset('images/nri-logo.png') }}" alt="NRI"
                    class="h-6 w-auto object-contain opacity-70">
                <span class="font-mono text-[11px]" style="opacity:0.3">/</span>
                <span class="font-mono text-[11px]" style="color:var(--text-muted)">{{ $title ?? 'Admin' }}</span>
            </div>

            <div class="flex items-center gap-3 ml-auto">
                <span id="live-time" class="font-mono text-[11px] hidden sm:block"
                    style="color:var(--text-dim)"></span>
                <a href="{{ route('home') }}" target="_blank"
                    class="hidden sm:flex items-center gap-1.5 text-[12px] px-3 py-1.5 rounded-lg transition"
                    style="color:var(--text-muted);background:var(--bg-raised);border:1px solid var(--border)">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    View Site
                </a>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sidebar-overlay').classList.add('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebar-overlay').classList.remove('show');
        }

        function updateClock() {
            const el = document.getElementById('live-time');
            if (!el) return;
            const now = new Date();
            el.textContent = now.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }) + ' WAT';
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>

</html>

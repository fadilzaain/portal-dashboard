<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal RS') — RSUD</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (ganti dengan Vite build di production) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0a0f1e;
            --sidebar-border: rgba(255,255,255,.06);
            --sidebar-hover: rgba(255,255,255,.05);
            --sidebar-active-bg: rgba(20,184,166,.12);
            --sidebar-active-border: #14b8a6;
            --accent: #14b8a6;
            --accent-dim: rgba(20,184,166,.15);
            --accent-glow: rgba(20,184,166,.25);
            --text-main: #f1f5f9;
            --text-muted: #64748b;
            --text-dim: #94a3b8;
            --page-bg: #f4f7fb;
            --card-bg: #ffffff;
            --font: 'Sora', sans-serif;
            --mono: 'DM Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { font-family: var(--font); background: var(--page-bg); color: #1e293b; }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            border-right: 1px solid var(--sidebar-border);
        }

        /* Subtle noise texture overlay */
        #sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: .4;
        }

        /*  Brand dan  Logo  */
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.25rem;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), #0891b2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 0 16px var(--accent-glow);
        }
        .sidebar-brand-name {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -.01em;
            line-height: 1.2;
        }
        .sidebar-brand-sub {
            font-size: .65rem;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        /* Nav Section Label */
        .nav-label {
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 1.25rem 1.25rem .4rem;
        }

        /* Nav Item */
        .nav-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .62rem 1.25rem;
            margin: .1rem .75rem;
            border-radius: 10px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 500;
            position: relative;
            transition: all .18s ease;
            border: 1px solid transparent;
        }
        .nav-item:hover {
            background: var(--sidebar-hover);
            color: var(--text-main);
        }
        .nav-item.active {
            background: var(--sidebar-active-bg);
            color: var(--accent);
            border-color: var(--sidebar-active-border);
            border-opacity: .3;
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -1px; top: 25%; bottom: 25%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
            box-shadow: 0 0 8px var(--accent);
        }
        .nav-icon {
            width: 16px; height: 16px;
            flex-shrink: 0;
            opacity: .7;
        }
        .nav-item.active .nav-icon { opacity: 1; }
        .nav-badge {
            margin-left: auto;
            font-family: var(--mono);
            font-size: .6rem;
            font-weight: 500;
            background: var(--accent-dim);
            color: var(--accent);
            padding: .1rem .45rem;
            border-radius: 999px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--sidebar-border);
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem .75rem;
            border-radius: 10px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--sidebar-border);
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .user-name  { font-size: .78rem; font-weight: 600; color: var(--text-main); }
        .user-role  { font-size: .65rem; color: var(--text-muted); }

        .logout-btn {
            margin-left: auto;
            background: none; border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: .25rem;
            border-radius: 6px;
            transition: color .15s;
            display: flex; align-items: center;
        }
        .logout-btn:hover { color: #f87171; }

        /* Main Content */
        #main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        #topbar {
            background: white;
            border-bottom: 1px solid #e8edf4;
            padding: 0 2rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .topbar-title {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .topbar-breadcrumb {
            font-size: .75rem;
            color: #94a3b8;
            font-weight: 400;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .topbar-date {
            font-size: .75rem;
            color: #94a3b8;
            font-family: var(--mono);
        }
        .topbar-notif {
            position: relative;
            width: 34px; height: 34px;
            border-radius: 9px;
            background: #f4f7fb;
            border: 1px solid #e8edf4;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #64748b;
            transition: background .15s;
        }
        .topbar-notif:hover { background: #e8edf4; }

        /* Page Content */
        #page-content {
            padding: 2rem;
            flex: 1;
        }

        /* Mobile Toggle */
        #sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 1.5rem; right: 1.5rem;
            width: 48px; height: 48px;
            background: var(--accent);
            border-radius: 14px;
            border: none;
            color: white;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 4px 20px var(--accent-glow);
            align-items: center; justify-content: center;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main-wrap { margin-left: 0; }
            #sidebar-toggle { display: flex; }
            #page-content { padding: 1.25rem; }
        }

        /* Page Transition */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up {
            animation: fadeUp .4s cubic-bezier(.4,0,.2,1) both;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════════ Sidebar ═══════════════ --}}
<aside id="sidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <div class="sidebar-brand-name">Portal RSUD</div>
            <div class="sidebar-brand-sub">Sistem Informasi RS</div>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;overflow-y:auto;padding:.75rem 0;">

        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Beranda Portal
        </a>

        <div class="nav-label" style="margin-top:.5rem">Dashboard</div>

        <a href="{{ route('portal.pelayananpasien') }}"
           class="nav-item {{ request()->routeIs('portal.pelayananpasien') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            Pelayanan Pasien
            <span class="nav-badge">↗</span>
        </a>

        <a href="{{ route('portal.keuangan') }}"
            class="nav-item {{ request()->routeIs('portal.keuangan') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Keuangan
            <span class="nav-badge">↗</span>
        </a>

        <a href="{{ route('sdm.portal.sdm') }}" class="nav-item {{ request()->routeIs('sdm.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            SDM
            <span class="nav-badge">↗</span>
        </a>

        <a href="#" class="nav-item {{ request()->routeIs('mutu.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Indikator Mutu
            <span class="nav-badge">↗</span>
        </a>

        <a href="{{ route('portal.klaimbpjs') }}" class="nav-item {{ request()->routeIs('portal.klaimbpjs') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Klaim BPJS
            <span class="nav-badge">↗</span>
        </a>

    </nav>

    {{-- Footer user --}}
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div class="user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ auth()->user()->name ?? 'User' }}
                </div>
                <div class="user-role">{{ auth()->user()->email ?? '' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ═══════════════ Main ═══════════════ --}}
<div id="main-wrap">

    {{-- Top Bar --}}
    <header id="topbar">
        <div class="topbar-title">
            @yield('page_title', 'Dashboard')
            @hasSection('page_subtitle')
                <span class="topbar-breadcrumb">/ @yield('page_subtitle')</span>
            @endif
        </div>
        <div class="topbar-right">
            <span class="topbar-date" id="clock"></span>
            <div class="topbar-notif">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <main id="page-content">
        @yield('content')
    </main>

</div>

{{-- Mobile toggle --}}
<button id="sidebar-toggle" onclick="toggleSidebar()">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

{{-- Overlay mobile --}}
<div id="sidebar-overlay"
     onclick="toggleSidebar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:49;backdrop-filter:blur(2px)">
</div>

<script>
    // Jams
    function updateClock() {
        const now = new Date();
        const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit', hour12: false };
        document.getElementById('clock').textContent = now.toLocaleDateString('id-ID', opts);
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Mobile sidebar toggle
    function toggleSidebar() {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebar-overlay');
        const isOpen = sb.classList.toggle('open');
        ov.style.display = isOpen ? 'block' : 'none';
    }
</script>

@stack('scripts')
</body>
</html>
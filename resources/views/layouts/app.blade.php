    <!DOCTYPE html>
    <html lang="id" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Portal RS') — RSUD</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

        <script src="https://cdn.tailwindcss.com"></script>

        {{-- Inject theme sebelum render untuk hindari flash --}}
        <script>
            (function() {
                const saved = localStorage.getItem('dash-theme') || 'light';
                document.documentElement.setAttribute('data-theme', saved);
            })();
        </script>

        <style>
            /* ═════════════════════════
            CSS VARIABLES — SIDEBAR
            ════════════════════════════ */
            :root {
                --sidebar-w:              260px;
                --sidebar-bg:             #0a0f1e;
                --sidebar-border:         rgba(255,255,255,.06);
                --sidebar-hover:          rgba(255,255,255,.05);
                --sidebar-active-bg:      rgba(20,184,166,.12);
                --sidebar-active-border:  #14b8a6;
                --accent:                 #14b8a6;
                --accent-dim:             rgba(20,184,166,.15);
                --accent-glow:            rgba(20,184,166,.25);
                --text-main:              #f1f5f9;
                --text-muted:             #64748b;
                --text-dim:               #94a3b8;
                --font:                   'Sora', sans-serif;
                --mono:                   'DM Mono', monospace;

                /* page variable */
                --page-bg:        #0d1117;
                --page-text:      #e2e8f0;
                --card-bg:        #161b27;
                --card-border:    rgba(255,255,255,.07);
                --topbar-bg:      #111827;
                --topbar-border:  rgba(255,255,255,.07);
                --topbar-text:    #e2e8f0;
                --topbar-muted:   #64748b;
                --topbar-btn-bg:  rgba(255,255,255,.06);
                --topbar-btn-brd: rgba(255,255,255,.1);
            }

            /* ── LIGHT MODE ── */
            [data-theme="light"] {
                --page-bg:        #f4f7fb;
                --page-text:      #1e293b;
                --card-bg:        #ffffff;
                --card-border:    #e8edf4;
                --topbar-bg:      #ffffff;
                --topbar-border:  #e8edf4;
                --topbar-text:    #1e293b;
                --topbar-muted:   #94a3b8;
                --topbar-btn-bg:  #f4f7fb;
                --topbar-btn-brd: #e8edf4;
            }

            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            html, body { height: 100%; }
            body {
                font-family: var(--font);
                background: var(--page-bg);
                color: var(--page-text);
                transition: background .3s ease, color .3s ease;
            }

            /* ── Overlay backdrop (mobile) ── */
            #sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 49;
            }
            body.sidebar-open #sidebar-backdrop { display: block; }

            /* ── Hover Zone (desktop) ── */
            #sidebar-hover-zone {
                position: fixed;
                top: 0; left: 0; bottom: 0;
                width: 16px;
                z-index: 51;
            }

            /* ── Peek indicator ── */
            #sidebar-peek {
                position: fixed;
                top: 0; left: 0; bottom: 0;
                width: 4px;
                background: var(--accent);
                opacity: .45;
                border-radius: 0 3px 3px 0;
                z-index: 52;
                pointer-events: none;
                transition: opacity .2s ease;
            }

            /* ── Sidebar ── */
            #sidebar {
                position: fixed;
                top: 0; left: 0; bottom: 0;
                width: var(--sidebar-w);
                background: var(--sidebar-bg);
                display: flex;
                flex-direction: column;
                z-index: 50;
                border-right: 1px solid var(--sidebar-border);
                transform: translateX(calc(-1 * var(--sidebar-w)));
                transition: transform .28s cubic-bezier(.4,0,.2,1), box-shadow .28s ease;
            }

            #sidebar::before {
                content: '';
                position: absolute; inset: 0;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
                pointer-events: none;
                opacity: .4;
            }

            body.sidebar-open #sidebar {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0,0,0,.35);
            }

            @media (min-width: 769px) {
                body.sidebar-open #main-wrap { margin-left: var(--sidebar-w); }
                body.sidebar-open #sidebar-peek { opacity: 0; }
            }

            /* Brand */
            .sidebar-brand {
                padding: 1.5rem 1.25rem 1.25rem;
                border-bottom: 1px solid var(--sidebar-border);
                display: flex; align-items: center; gap: .75rem;
            }
            .sidebar-brand-name { font-size: .95rem; font-weight: 700; color: var(--text-main); letter-spacing: -.01em; line-height: 1.2; }
            .sidebar-brand-sub  { font-size: .65rem; color: var(--text-muted); font-weight: 400; letter-spacing: .05em; text-transform: uppercase; }

            /* Nav */
            .nav-label {
                font-size: .6rem; font-weight: 700; letter-spacing: .12em;
                text-transform: uppercase; color: var(--text-muted);
                padding: 1.25rem 1.25rem .4rem;
            }
            .nav-item {
                display: flex; align-items: center; gap: .75rem;
                padding: .62rem 1.25rem; margin: .1rem .75rem;
                border-radius: 10px; color: var(--text-dim);
                text-decoration: none; font-size: .82rem; font-weight: 500;
                position: relative; transition: all .18s ease;
                border: 1px solid transparent; white-space: nowrap;
            }
            .nav-item:hover { background: var(--sidebar-hover); color: var(--text-main); }
            .nav-item.active {
                background: var(--sidebar-active-bg);
                color: var(--accent);
                border-color: rgba(20,184,166,.3);
            }
            .nav-item.active::before {
                content: '';
                position: absolute; left: -1px; top: 25%; bottom: 25%;
                width: 3px; background: var(--accent);
                border-radius: 0 3px 3px 0;
                box-shadow: 0 0 8px var(--accent);
            }
            .nav-icon { width: 16px; height: 16px; flex-shrink: 0; opacity: .7; }
            .nav-item.active .nav-icon { opacity: 1; }
            .nav-badge {
                margin-left: auto; font-family: var(--mono); font-size: .6rem; font-weight: 500;
                background: var(--accent-dim); color: var(--accent);
                padding: .1rem .45rem; border-radius: 999px;
            }

            /* Sidebar Footer */
            .sidebar-footer {
                margin-top: auto; padding: 1rem 1.25rem;
                border-top: 1px solid var(--sidebar-border);
            }
            .user-card {
                display: flex; align-items: center; gap: .75rem;
                padding: .6rem .75rem; border-radius: 10px;
                background: rgba(255,255,255,.04);
                border: 1px solid var(--sidebar-border);
            }
            .user-avatar {
                width: 32px; height: 32px; border-radius: 8px;
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                display: flex; align-items: center; justify-content: center;
                font-size: .75rem; font-weight: 700; color: white; flex-shrink: 0;
            }
            .user-name { font-size: .78rem; font-weight: 600; color: var(--text-main); }
            .user-role { font-size: .65rem; color: var(--text-muted); }
            .logout-btn {
                margin-left: auto; background: none; border: none;
                color: var(--text-muted); cursor: pointer; padding: .25rem;
                border-radius: 6px; transition: color .15s;
                display: flex; align-items: center;
            }
            .logout-btn:hover { color: #f87171; }

            /* ── Main Wrap ── */
            #main-wrap {
                margin-left: 0; min-height: 100vh;
                display: flex; flex-direction: column;
                transition: margin-left .28s cubic-bezier(.4,0,.2,1);
            }

            @media (min-width: 769px) {
                #sidebar-hover-zone:hover ~ #main-wrap { margin-left: var(--sidebar-w); }
            }

            /* ── Top Bar ── */
            #topbar {
                background: var(--topbar-bg);
                border-bottom: 1px solid var(--topbar-border);
                padding: 0 2rem; height: 60px;
                display: flex; align-items: center; justify-content: space-between;
                position: sticky; top: 0; z-index: 40;
                transition: background .3s ease, border-color .3s ease;
            }
            .topbar-left  { display: flex; align-items: center; gap: .75rem; }
            .topbar-title { font-size: .9rem; font-weight: 600; color: var(--topbar-text); display: flex; align-items: center; gap: .5rem; }
            .topbar-breadcrumb { font-size: .75rem; color: var(--topbar-muted); font-weight: 400; }
            .topbar-right { display: flex; align-items: center; gap: .75rem; }
            .topbar-date  { font-size: .75rem; color: var(--topbar-muted); font-family: var(--mono); }

            .topbar-icon-btn {
                position: relative; width: 34px; height: 34px; border-radius: 9px;
                background: var(--topbar-btn-bg);
                border: 1px solid var(--topbar-btn-brd);
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; color: var(--topbar-muted);
                transition: background .15s, color .15s;
            }
            .topbar-icon-btn:hover {
                background: var(--accent-dim);
                color: var(--accent);
                border-color: rgba(20,184,166,.3);
            }

            /* ── Dark/Light toggle ── */
            #theme-toggle .icon-sun  { display: none; }
            #theme-toggle .icon-moon { display: block; }
            [data-theme="light"] #theme-toggle .icon-sun  { display: block; }
            [data-theme="light"] #theme-toggle .icon-moon { display: none; }

            /* Hamburger button */
            #mobile-menu-btn {
                display: none;
                align-items: center; justify-content: center;
                width: 34px; height: 34px; border-radius: 9px;
                background: var(--topbar-btn-bg);
                border: 1px solid var(--topbar-btn-brd);
                cursor: pointer; color: var(--topbar-muted); flex-shrink: 0;
                transition: background .15s;
            }
            #mobile-menu-btn:hover { background: var(--accent-dim); color: var(--accent); }

            /* Page Content */
            #page-content { padding: 2rem; flex: 1; }

            /* Fade animation */
            @keyframes fadeUp {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .fade-up { animation: fadeUp .4s cubic-bezier(.4,0,.2,1) both; }

            /* ════════════════════
            MOBILE
            ═══════════════════════ */
            @media (max-width: 768px) {
                #sidebar-hover-zone { display: none; }
                #sidebar-peek       { display: none; }
                #sidebar { z-index: 50; display: flex; }
                body.sidebar-open #main-wrap { margin-left: 0 !important; }
                #topbar { padding: 0 1rem; }
                #mobile-menu-btn { display: flex; }
                .topbar-date { display: none; }
                .topbar-title { font-size: .8rem; }
                .topbar-breadcrumb { display: none; }
                #page-content { padding: 1rem; }
            }

            /* ══════════════
            SMALL MOBILE 
            ═════════════════ */
            @media (max-width: 480px) {
                :root { --sidebar-w: 85vw; }
                #topbar { height: 52px; }
                #page-content { padding: .75rem; }
            }

            /* ══════════════════════════════════════
            TV / MONITOR BESAR — 1920px+
            ══════════════════════════════════════ */
            @media (min-width: 1920px) {
            #page-content {
                padding: .75rem 2rem;
                height: calc(100vh - 60px);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            /* Greeting card jauh lebih compact */
            .greeting-card {
                padding: .6rem 1.5rem;
                margin-bottom: .6rem;
                border-radius: 14px;
            }
            .greeting-card::before,
            .greeting-card::after { display: none; }
            .greeting-time   { font-size: .6rem; margin-bottom: .1rem; }
            .greeting-text   { font-size: 1.1rem; }
            .greeting-sub    { font-size: .72rem; margin-top: .1rem; }
            .greeting-stats  { margin-top: .5rem; gap: 1rem; }
            .g-stat-val      { font-size: .82rem; }
            .g-stat-lbl      { font-size: .6rem; }
            .greeting-inner  { gap: .4rem; }
            .filter-select   { font-size: .72rem; padding: .35rem .75rem; }

            /* Section header */
            .section-header  { margin-bottom: .5rem; }

            /* Grid mengisi sisa tinggi */
            .app-grid {
                flex: 1;
                min-height: 0;
                align-items: stretch;
                align-content: stretch;
                grid-template-rows: 1fr 1fr;
                overflow: hidden;
                gap: .75rem;
            }

            .app-card {
                min-height: 0;
                overflow: hidden;
                gap: .5rem;
                padding: .9rem 1.1rem;
            }

            /* Kecilkan elemen dalam card */
            .app-icon         { width: 36px; height: 36px; }
            .card-title-wrap .app-name { font-size: .82rem; }
            .card-title-wrap .app-sub  { font-size: .6rem; }
            .klaim-val        { font-size: 1.1rem; }
            .ks-val           { font-size: .95rem; }
            .sdm-big-num      { font-size: 1.8rem; }
            .mutu-box-val     { font-size: 1.2rem; }
            .stat-box-val     { font-size: .9rem; }
            .fin-val          { font-size: .78rem; }
            .fin-label        { font-size: .62rem; }
            .card-footer      { padding-top: .4rem; }
        }
        </style>
        @stack('styles')
    </head>
    <body>

    {{-- Backdrop (mobile overlay) --}}
    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>

    {{-- Hover Zone (desktop only) --}}
    <div id="sidebar-hover-zone"></div>

    {{-- Sidebar --}}
    <aside id="sidebar">

        <div class="sidebar-brand">
            <img src="{{ asset('images/logo-rsud-jombang.png') }}"
                alt="Logo RSUD"
                style="width:36px;height:36px;border-radius:10px;object-fit:contain;flex-shrink:0;">
            <div>
                <div class="sidebar-brand-name">DASH - i</div>
                <div class="sidebar-brand-sub">Dashboard Integrasi RSUD Jombang</div>
            </div>
        </div>

        <nav style="flex:1;overflow-y:auto;padding:.75rem 0;">

            <div class="nav-label">Menu Utama</div>

            <a href="{{ route('dashboard') }}"
            class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="home" class="nav-icon" />
                Beranda Portal
            </a>

            <div class="nav-label" style="margin-top:.5rem">Dashboard</div>

            <a href="{{ route('portal.pelayananpasien') }}"
            class="nav-item {{ request()->routeIs('portal.pelayananpasien') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="heart" class="nav-icon" />
                Pelayanan Pasien
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.keuangan') }}"
            class="nav-item {{ request()->routeIs('portal.keuangan') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="currency-dollar" class="nav-icon" />
                Keuangan
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('sdm.portal.sdm') }}"
            class="nav-item {{ request()->routeIs('sdm.*') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="users" class="nav-icon" />
                SDM
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.indikatormutu') }}"
            class="nav-item {{ request()->routeIs('portal.indikatormutu.*') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="document-text" class="nav-icon" />
                Indikator Mutu
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.klaimbpjs') }}"
            class="nav-item {{ request()->routeIs('portal.klaimbpjs') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <x-icon name="shield-check" class="nav-icon" />
                Klaim BPJS
                <span class="nav-badge">↗</span>
            </a>

        </nav>

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
                        <x-icon name="arrow-right-on-rectangle" width="15" height="15" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Peek indicator (desktop) --}}
    <div id="sidebar-peek"></div>

    {{-- ═══════════════ Main ═══════════════ --}}
    <div id="main-wrap">

        <header id="topbar">
            <div class="topbar-left">
                {{-- Hamburger — mobile only --}}
                <button id="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Buka menu">
                    <x-icon name="bars-3" width="18" height="18" />
                </button>

                <img src="{{ asset('images/logo-rsud-jombang.png') }}"
                    alt="Logo RSUD"
                    style="width:28px;height:28px;border-radius:8px;object-fit:contain;flex-shrink:0;">

                <div style="width:1px;height:20px;background:rgba(255,255,255,.08);"></div>

                <div class="topbar-title">
                    @yield('page_title', 'Dashboard')
                    @hasSection('page_subtitle')
                        <span class="topbar-breadcrumb">/ @yield('page_subtitle')</span>
                    @endif
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-date" id="clock"></span>

                {{-- Dark / Light mode toggle --}}
                <button class="topbar-icon-btn" id="theme-toggle" onclick="toggleTheme()" aria-label="Ganti tema">
                    {{-- Sun = tampil saat light mode aktif --}}
                    <x-icon name="sun" width="16" height="16" class="icon-sun" />
                    {{-- Moon = tampil saat dark mode aktif --}}
                    <x-icon name="moon" width="16" height="16" class="icon-moon" />
                </button>

                {{-- Notifikasi --}}
                <div class="topbar-icon-btn">
                    <x-icon name="bell" width="16" height="16" />
                </div>
            </div>
        </header>

        <main id="page-content">
            @yield('content')
        </main>

    </div>

    <script>
        /* jam */
        function updateClock() {
            const now     = new Date();
            const tanggal = now.toLocaleDateString('id-ID', { weekday:'short', day:'numeric', month:'short' });
            const waktu   = now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12: false });
            document.getElementById('clock').textContent = `${tanggal} · ${waktu}`;
        }
        updateClock();
        setInterval(updateClock, 1000);

        /* sidebar */
        const zone    = document.getElementById('sidebar-hover-zone');
        const sidebar = document.getElementById('sidebar');
        const isMobile = () => window.innerWidth <= 768;

        function openSidebar()   { document.body.classList.add('sidebar-open'); }
        function closeSidebar()  { document.body.classList.remove('sidebar-open'); }
        function toggleSidebar() { document.body.classList.toggle('sidebar-open'); }
        function closeSidebarOnMobile() { if (isMobile()) closeSidebar(); }

        if (zone) {
            zone.addEventListener('mouseenter', () => { if (!isMobile()) openSidebar(); });
            sidebar.addEventListener('mouseenter', () => { if (!isMobile()) openSidebar(); });

            function handleLeave(e) {
                if (isMobile()) return;
                const to = e.relatedTarget;
                if (!zone.contains(to) && !sidebar.contains(to) && to !== zone && to !== sidebar) {
                    closeSidebar();
                }
            }
            zone.addEventListener('mouseleave', handleLeave);
            sidebar.addEventListener('mouseleave', handleLeave);
        }

        /* ── Swipe mobile ── */
        let touchStartX = 0;
        document.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        document.addEventListener('touchend', e => {
            if (!isMobile()) return;
            const dx = e.changedTouches[0].clientX - touchStartX;
            if (touchStartX < 30 && dx > 60) openSidebar();
            if (dx < -60) closeSidebar();
        }, { passive: true });

        /* tema mode */
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next    = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('dash-theme', next);
        }
    </script>

    @stack('scripts')
    </body>
    </html>
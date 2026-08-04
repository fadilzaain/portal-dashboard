    <!DOCTYPE html>
    <html lang="id" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Dash-i') RSUD Jombang</title>

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

        @vite(['resources/css/layout/app-shell.css'])
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
                <button id="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Buka menu">
                    <x-icon name="bars-3" width="18" height="18" />
                </button>

                <div class="topbar-logo-badge">
                    <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD">
                </div>

                <div class="topbar-divider"></div>

                <div class="topbar-title">
                    @yield('page_title', 'Dashboard')
                    @hasSection('page_subtitle')
                        <span class="topbar-breadcrumb">@yield('page_subtitle')</span>
                    @endif
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date-pill">
                    <x-icon name="clock" width="13" height="13" />
                    <span id="clock"></span>
                </div>

                <button class="topbar-icon-btn" id="theme-toggle" onclick="toggleTheme()" aria-label="Ganti tema">
                    <x-icon name="sun" width="16" height="16" class="icon-sun" />
                    <x-icon name="moon" width="16" height="16" class="icon-moon" />
                </button>

                <div class="topbar-icon-btn">
                    <x-icon name="bell" width="16" height="16" />
                    <span class="topbar-notif-dot"></span>
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
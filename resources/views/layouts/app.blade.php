<!DOCTYPE html>
    <html lang="id" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Dash-i') RSUD Jombang</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&family=JetBrains+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

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

    {{-- Background layer: aurora blur + vignette + noise, statis di belakang semua konten --}}
    <div class="bg-aurora"><i></i></div>
    <div class="bg-vignette"></div>
    <div class="bg-noise"></div>

    {{-- Backdrop (mobile overlay) --}}
    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>

    {{-- Sidebar — floating pill, icon-only, expand pas di-hover (desktop) / drawer (mobile) --}}
    <aside id="sidebar">

        <div class="sidebar-brand">
            <div class="sidebar-brand-mark">
                <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD">
            </div>
            <div class="sidebar-brand-word">
                <div class="sidebar-brand-name">DASH - i</div>
                <div class="sidebar-brand-sub">RSUD Jombang</div>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-label">Menu Utama</div>

            <a href="{{ route('dashboard') }}"
            class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="home" class="nav-icon" /></span>
                <span class="nav-txt">Beranda Portal</span>
            </a>

            <div class="nav-label">Dashboard</div>

            <a href="{{ route('portal.pelayananpasien') }}"
            class="nav-item {{ request()->routeIs('portal.pelayananpasien') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="heart" class="nav-icon" /></span>
                <span class="nav-txt">Pelayanan Pasien</span>
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.keuangan') }}"
            class="nav-item {{ request()->routeIs('portal.keuangan') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="currency-dollar" class="nav-icon" /></span>
                <span class="nav-txt">Keuangan</span>
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('sdm.portal.sdm') }}"
            class="nav-item {{ request()->routeIs('sdm.*') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="users" class="nav-icon" /></span>
                <span class="nav-txt">SDM</span>
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.indikatormutu') }}"
            class="nav-item {{ request()->routeIs('portal.indikatormutu.*') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="document-text" class="nav-icon" /></span>
                <span class="nav-txt">Indikator Mutu</span>
                <span class="nav-badge">↗</span>
            </a>

            <a href="{{ route('portal.klaimbpjs') }}"
            class="nav-item {{ request()->routeIs('portal.klaimbpjs') ? 'active' : '' }}"
            onclick="closeSidebarOnMobile()">
                <span class="nav-icon-box"><x-icon name="shield-check" class="nav-icon" /></span>
                <span class="nav-txt">Klaim BPJS</span>
                <span class="nav-badge">↗</span>
            </a>

        </nav>

        <div class="sidebar-footer">

            <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" aria-label="Ganti tema">
                <span class="nav-icon-box">
                    <span class="icon-sun"><x-icon name="sun" width="16" height="16" /></span>
                    <span class="icon-moon"><x-icon name="moon" width="16" height="16" /></span>
                </span>
                <span class="nav-txt">Ganti tema</span>
            </button>

            <div class="user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
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

        /* sidebar — desktop: hover murni CSS (lihat #sidebar:hover di app-shell.css)
           mobile : toggle manual via hamburger + swipe + backdrop */
        const isMobile = () => window.innerWidth <= 768;

        function openSidebar()   { document.body.classList.add('sidebar-open'); }
        function closeSidebar()  { document.body.classList.remove('sidebar-open'); }
        function toggleSidebar() { document.body.classList.toggle('sidebar-open'); }
        function closeSidebarOnMobile() { if (isMobile()) closeSidebar(); }

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
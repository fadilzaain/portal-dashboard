<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dash-i') | RSUD Jombang</title>

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

    @php
        $pengguna = auth()->user();
        $namaUser = $pengguna->name ?? 'User';

        // Khusus admin: jumlah pendaftar yang menunggu persetujuan, ditampilkan sebagai angka di menu Kelola Pengguna
        $jumlahMenunggu = $pengguna?->isAdmin()
            ? \App\Models\User::where('status', \App\Models\User::STATUS_MENUNGGU)->count()
            : 0;

        // Daftar menu dibuat SATU kali di sini, lalu digambar lewat perulangan (tidak ditulis berulang).
        // rute = nama route, cocok = pola route yang menandai menu aktif,
        // luar = true kalau menu membuka dashboard modul (muncul panah ↗ di kanan)
        // admin = true kalau menu hanya untuk admin (disembunyikan dari akun lain)
        $menu = [
            'Menu Utama' => [
                ['rute' => 'dashboard',              'cocok' => 'dashboard',                'nama' => 'Beranda Portal',   'ikon' => 'home',            'luar' => false],
            ],
            'Dashboard' => [
                ['rute' => 'portal.pelayananpasien', 'cocok' => 'portal.pelayananpasien',   'nama' => 'Pelayanan Pasien', 'ikon' => 'heart',           'luar' => true],
                ['rute' => 'portal.keuangan',        'cocok' => 'portal.keuangan',          'nama' => 'Keuangan',         'ikon' => 'currency-dollar', 'luar' => true],
                ['rute' => 'sdm.portal.sdm',         'cocok' => 'sdm.*',                    'nama' => 'SDM',              'ikon' => 'users',           'luar' => true],
                ['rute' => 'portal.indikatormutu',   'cocok' => 'portal.indikatormutu.*',   'nama' => 'Indikator Mutu',   'ikon' => 'document-text',   'luar' => true],
                ['rute' => 'portal.iku',             'cocok' => 'portal.iku*',              'nama' => 'Indikator Kerja Utama',    'ikon' => 'gauge',           'luar' => true],
                ['rute' => 'portal.klaimbpjs',       'cocok' => 'portal.klaimbpjs',         'nama' => 'Klaim BPJS',       'ikon' => 'shield-check',    'luar' => true],
            ],
            'Akun' => [
                ['rute' => 'pengguna',               'cocok' => 'pengguna*',                'nama' => 'Kelola Pengguna',  'ikon' => 'user',            'luar' => false, 'admin' => true],
                ['rute' => 'akun.password',          'cocok' => 'akun.password*',           'nama' => 'Ganti Password',   'ikon' => 'lock',            'luar' => false],
            ],
        ];

        // Nomor urut dipakai untuk jeda animasi masuk tiap item sidebar (muncul satu per satu)
        $urut = 0;
    @endphp

    {{-- Background layer --}}
    <div class="bg-aurora"><i></i></div>
    <div class="bg-vignette"></div>
    <div class="bg-noise"></div>

    {{-- Backdrop (tirai gelap saat sidebar kebuka lewat tombol/swipe) --}}
    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>

    {{-- Tombol buka sidebar — cuma keliatan di device yang gak bisa hover --}}
    <button id="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Buka menu">
        <x-icon name="bars-3" width="18" height="18" />
    </button>

    {{-- Hover-zone: strip tipis nempel di tepi kiri layar (gagang kecilnya digambar lewat CSS) --}}
    <div id="sidebar-hover-zone" aria-hidden="true"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" aria-label="Menu samping">

        <div class="sidebar-brand masuk" style="--i: {{ $urut++ }}">
            <div class="sidebar-brand-mark">
                <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD">
            </div>
            <div class="sidebar-brand-word">
                <div class="sidebar-brand-name">DASH - i</div>
                <div class="sidebar-brand-sub">RSUD Jombang</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @foreach ($menu as $grup => $butir)
                @php
                    // Menu khusus admin disembunyikan dari akun lain (di route tetap dijaga juga lewat middleware role)
                    $butir = array_filter($butir, fn ($m) => empty($m['admin']) || $pengguna?->isAdmin());
                @endphp
                @continue(empty($butir))

                <div class="nav-label masuk" style="--i: {{ $urut++ }}">{{ $grup }}</div>

                @foreach ($butir as $m)
                    @php $aktif = request()->routeIs($m['cocok']); @endphp
                    <a href="{{ route($m['rute']) }}"
                       class="nav-item masuk {{ $aktif ? 'active' : '' }}"
                       style="--i: {{ $urut++ }}"
                       @if ($aktif) aria-current="page" @endif
                       onclick="closeSidebarOnMobile()">
                        <span class="nav-icon-box"><x-icon :name="$m['ikon']" class="nav-icon" /></span>
                        <span class="nav-txt">{{ $m['nama'] }}</span>
                        @if ($m['luar'])
                            <span class="nav-badge">↗</span>
                        @endif
                        @if ($m['rute'] === 'pengguna' && $jumlahMenunggu > 0)
                            <span class="nav-badge nav-badge--angka" title="Pendaftar menunggu persetujuan">{{ $jumlahMenunggu }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="sidebar-footer masuk" style="--i: {{ $urut++ }}">

            <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" aria-label="Ganti tema">
                <span class="nav-icon-box">
                    <span class="icon-sun"><x-icon name="sun" width="16" height="16" /></span>
                    <span class="icon-moon"><x-icon name="moon" width="16" height="16" /></span>
                </span>
                <span class="nav-txt">Tema</span>
            </button>

            <div class="user-card">
                <div class="user-avatar">
                    {{ mb_strtoupper(mb_substr($namaUser, 0, 2)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $namaUser }}</div>
                    <div class="user-role">{{ $pengguna->email ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout" aria-label="Logout">
                        <x-icon name="arrow-right-on-rectangle" width="15" height="15" />
                    </button>
                </form>
            </div>

        </div>
    </aside>

    {{-- ═══════════════ Main  ═══════════════ --}}
    <div id="main-wrap">
        <main id="page-content">
            @yield('content')
        </main>
    </div>

    <script>

        /* Device yang gak bisa "hover" (HP/tablet/layar sentuh) — dipakai buat
           nentuin kapan sidebar perlu ditutup manual & kapan swipe aktif */
        const isTouchDevice = () => window.matchMedia('(hover: none), (pointer: coarse)').matches;

        function openSidebar()   { document.body.classList.add('sidebar-open'); }
        function closeSidebar()  { document.body.classList.remove('sidebar-open'); }
        function toggleSidebar() { document.body.classList.toggle('sidebar-open'); }
        function closeSidebarOnMobile() { if (isTouchDevice()) closeSidebar(); }

        /* Tombol Esc nutup sidebar yang kebuka lewat tombol menu */
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });

        /* ── Swipe buat device touch ── */
        let touchStartX = 0;
        document.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        document.addEventListener('touchend', e => {
            if (!isTouchDevice()) return;
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
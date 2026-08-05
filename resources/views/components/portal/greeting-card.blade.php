{{--
    Hero card halaman Beranda Portal (dulu bernama "greeting-card").
    Style-nya ada di resources/css/portal/dashboard.css — komponen ini
    murni markup + data, biar gampang dikembangkan tanpa nyari-nyari CSS.
--}}
@props(['bulan', 'tahun', 'bulanLabel'])

<div class="hero">
    <div class="hero-glow"></div>

    <div class="hero-top">
        <div>
            <p class="hero-eyebrow" id="greeting-time">—</p>
            <h1 class="hero-title">
                Selamat datang, <span class="hero-name-accent">{{ explode(' ', auth()->user()->name ?? 'Pengguna')[0] }}</span>
            </h1>
            <p class="hero-desc">Ringkasan Dashboard Integrasi RSUD Jombang.</p>
        </div>

        {{-- Jam (pindahan dari topbar) + Filter Bulan & Tahun --}}
        <div class="hero-filter">
            <div class="hero-clock">
                <x-icon name="clock" width="13" height="13" />
                <span id="hero-clock-text">—</span>
            </div>
            <select class="hero-select" id="filter-bulan" onchange="applyFilter()">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                    <option value="{{ $i + 1 }}" {{ $bulan == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
            <select class="hero-select" id="filter-tahun" onchange="applyFilter()">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="hero-stats">
        <div class="hstat">
            <div class="hstat-val">5</div>
            <div class="hstat-lbl">Total Dashboard</div>
        </div>
        <div class="hstat">
            <div class="hstat-val" style="color:var(--champagne)">Aktif</div>
            <div class="hstat-lbl">Status Sinkron</div>
        </div>
    </div>
</div>
@props(['bulan', 'tahun', 'bulanLabel'])

<div class="greeting-card">
    <div class="greeting-inner">
        <div>
            <p class="greeting-time" id="greeting-time">—</p>
            <h1 class="greeting-text">
                Selamat datang, <span style="color:#14b8a6">{{ explode(' ', auth()->user()->name ?? 'Pengguna')[0] }}</span> 
            </h1>
            <p class="greeting-sub">Ringkasan Dashboard Integrasi RSUD Jombang.</p>
        </div>

        {{-- Filter Bulan & Tahun --}}
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <div class="filter-group">
                <span class="filter-label">Bulan</span>
                <select class="filter-select" id="filter-bulan" onchange="applyFilter()">
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                        <option value="{{ $i + 1 }}" {{ $bulan == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Tahun</span>
                <select class="filter-select" id="filter-tahun" onchange="applyFilter()">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <div class="greeting-stats">
        <div class="g-stat">
            <span class="g-stat-val">5</span>
            <span class="g-stat-lbl">Total Dashboard</span>
        </div>
        <div class="g-divider"></div>
        <div class="g-stat">
            <span class="g-stat-val" id="gs-date">—</span>
            <span class="g-stat-lbl">Tanggal</span>
        </div>
        <div class="g-divider"></div>
        <div class="g-stat">
            <span class="g-stat-val" id="gs-time">—</span>
            <span class="g-stat-lbl">Waktu</span>
        </div>
    </div>
</div>

@props(['bulan', 'tahun', 'bulanLabel'])

<div class="greeting-card">
    <div class="greeting-inner">
        <div class="greeting-text-wrap">
            <p class="greeting-time" id="greeting-time">—</p>
            <h1 class="greeting-text">
                Selamat datang, <span style="color:#14b8a6">{{ explode(' ', auth()->user()->name ?? 'Pengguna')[0] }}</span>
            </h1>
            <p class="greeting-sub">Ringkasan Dashboard Integrasi RSUD Jombang.</p>
        </div>

        {{-- Filter Bulan & Tahun --}}
        <div class="greeting-filter">
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

@once
@push('styles')
<style>
    /* Responsive overrides untuk greeting card */
    .greeting-filter {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    @media (max-width: 640px) {
        .greeting-card { padding: 1.25rem 1rem; margin-bottom: 1.25rem; }

        .greeting-inner {
            flex-direction: column;
            align-items: stretch;
            gap: .85rem;
        }

        .greeting-text { font-size: 1.15rem; }
        .greeting-sub  { font-size: .78rem; }

        .greeting-filter {
            width: 100%;
            gap: .5rem;
        }
        .greeting-filter .filter-group {
            flex: 1;
            min-width: 0;
        }
        .greeting-filter .filter-select {
            width: 100%;
        }

        .greeting-stats {
            gap: 0;
            justify-content: space-between;
        }
        .g-divider { display: none; }
        .g-stat { flex: 1; text-align: center; }
        .g-stat-val { font-size: .85rem; }
    }

    @media (max-width: 380px) {
        .greeting-text { font-size: 1rem; }
        .greeting-stats { flex-wrap: wrap; gap: .5rem; }
        .g-stat { flex: 0 0 calc(50% - .25rem); }
    }
</style>
@endpush
@endonce
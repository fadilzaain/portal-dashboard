@props(['keuangan', 'bulanLabel', 'tahun'])

@php
    $pendapatan  = $keuangan['pendapatan']  ?? 0;
    $belanja     = $keuangan['belanja']     ?? 0;
    $bulanAkhir  = $keuangan['bulan_akhir'] ?? now()->month;
    $selisih     = $pendapatan - $belanja;
    $isSurplus   = $selisih >= 0;
    $pctBelanja  = $pendapatan > 0 ? min(100, round($belanja / $pendapatan * 100)) : 0;

    $bulanNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $labelPeriode = 'Jan – ' . $bulanNames[$bulanAkhir]; //
    $fmtRupiah = function($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 1, ',', '.') . ' Jt';
        return 'Rp ' . number_format($n, 0, ',', '.');
    };
@endphp

<a href="{{ route('portal.keuangan') }}" class="app-card theme-blue">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-blue">
                <x-icon name="currency-dollar" width="20" height="20" stroke-width="1.8" />
            </div>
            <div class="card-title-wrap">
                <div class="app-name">Keuangan</div>
                <div class="app-sub">pendapatan &amp; belanja</div>
            </div>
        </div>
            <span class="card-month-badge month-blue">{{ $labelPeriode }} {{ $tahun }}</span>
    </div>

    <div class="fin-row">
        <div class="fin-item">
            <span class="fin-label">Pendapatan</span>
            <span class="fin-val up">{{ $fmtRupiah($pendapatan) }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label">Belanja</span>
            <span class="fin-val down">{{ $fmtRupiah($belanja) }}</span>
        </div>
        <div class="fin-selisih">
            <span style="font-family:'DM Mono',monospace;font-size:.8rem;font-weight:600;color:{{ $isSurplus ? '#34d399' : '#fb7185' }}">
                {{ $isSurplus ? '+' : '-' }} {{ $fmtRupiah(abs($selisih)) }}
            </span>
            @if($isSurplus)
                <span class="surplus-badge">Surplus</span>
            @else
                <span class="defisit-badge">Defisit</span>
            @endif
        </div>
    </div>

    <div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:{{ $pctBelanja }}%"></div>
        </div>
            <div class="progress-pct">{{ $pctBelanja }}% belanja dari pendapatan · {{ $tahun }}</div>
    </div>

    <div class="card-footer">
        <span class="card-open-btn">
            Lihat Detail
            <x-icon name="chevron-right" width="14" height="14" stroke-width="2.5" />
        </span>
        <span class="card-status-dot"></span>
    </div>
</a>

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

<a href="{{ route('portal.keuangan') }}" class="app-card theme-green">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-green">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="card-title-wrap">
                <div class="app-name">Keuangan</div>
                <div class="app-sub">Ringkasan pendapatan &amp; belanja</div>
            </div>
        </div>
            <span class="card-month-badge month-green">{{ $labelPeriode }} {{ $tahun }}</span>
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
            <span style="font-family:'DM Mono',monospace;font-size:.8rem;font-weight:600;color:{{ $isSurplus ? '#22c55e' : '#f43f5e' }}">
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
            <div class="progress-pct">{{ $pctBelanja }}% belanja dari pendapatan · YTD {{ $tahun }}</div>
    </div>

    <div class="card-footer">
        <span class="card-open-btn">
            Lihat Detail
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </span>
        <span class="card-status-dot"></span>
    </div>
</a>

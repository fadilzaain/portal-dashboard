@props(['keuangan', 'bulanLabel', 'tahun'])

@php
    $pendapatan  = $keuangan['pendapatan']  ?? 0;
    $belanja     = $keuangan['belanja']     ?? 0;
    $bulanAkhir  = $keuangan['bulan_akhir'] ?? now()->month;
    $selisih     = $pendapatan - $belanja;
    $isSurplus   = $selisih >= 0;
    $pctBelanja  = $pendapatan > 0 ? min(100, round($belanja / $pendapatan * 100)) : 0;

    $bulanNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $labelPeriode = 'Jan – ' . $bulanNames[$bulanAkhir];
    $fmtRupiah = function($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 1, ',', '.') . ' Jt';
        return 'Rp ' . number_format($n, 0, ',', '.');
    };
@endphp

<a href="{{ route('portal.keuangan') }}" class="app-card theme-blue">
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="blue"
        icon="currency-dollar"
        title="Keuangan"
        subtitle="pendapatan & belanja"
        :badge="$labelPeriode . ' ' . $tahun"
    />

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
            <span class="fin-selisih-amount {{ $isSurplus ? 'text-success' : 'text-danger' }}">
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
        <div class="progress-pct" data-count-target="{{ $pctBelanja }}">
            <span class="count-num">{{ $pctBelanja }}</span>% belanja dari pendapatan · {{ $tahun }}
        </div>
    </div>

    <x-portal.card-footer />
</a>
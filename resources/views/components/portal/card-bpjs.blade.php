@props(['bpjs', 'bulanLabel', 'tahun'])

@php
    $bpjsRI  = $bpjs['rawat_inap']  ?? 0;
    $bpjsRJ  = $bpjs['rawat_jalan'] ?? 0;
    $bpjsTB  = $bpjs['terbayar']    ?? 0;
    $bpjsPD  = $bpjs['pending']      ?? 0;
    $bpjsTL  = $bpjs['tidak_layak'] ?? 0;
    $bpjsTot = $bpjsTB + $bpjsPD + $bpjsTL;

    $p1 = $bpjsTot > 0 ? round($bpjsTB / $bpjsTot * 100) : 0;
    $p2 = $bpjsTot > 0 ? round($bpjsPD / $bpjsTot * 100) : 0;
    $p3 = max(0, 100 - $p1 - $p2);
    $juta = fn(float $n): string =>
        $n >= 1_000_000_000
            ? 'Rp ' . number_format($n / 1_000_000_000, 2) . ' M'
            : 'Rp ' . number_format($n / 1_000_000, 2) . ' jt';

    $nominalRI  = $bpjs['nominal_rinap']  ?? 0;
    $nominalRJ  = $bpjs['nominal_rjalan'] ?? 0;
@endphp

<a href="{{ route('portal.klaimbpjs') }}" class="app-card theme-amber" style="grid-column: span 2;">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-amber">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="card-title-wrap">
                <div class="app-name">Klaim BPJS</div>
                <div class="app-sub">Ringkasan pengajuan klaim</div>
            </div>
        </div>
        <span class="card-month-badge month-amber">{{ $bulanLabel }} {{ $tahun }}</span>
    </div>

    <div class="klaim-row">
        <div class="klaim-box">
            <div>
                <div class="klaim-label">Rawat Inap (RI)</div>
                <div class="klaim-val">{{ $juta($nominalRI) }}</div>
            </div>
            <div class="klaim-icon icon-blue">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </div>
        <div class="klaim-box">
            <div>
                <div class="klaim-label">Rawat Jalan (RJ)</div>
                <div class="klaim-val">{{ $juta($nominalRJ) }}</div>
            </div>
            <div class="klaim-icon icon-amber">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <div>
        <div style="font-size:.6rem;color:#475569;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Status Klaim</div>
        <div class="klaim-status">
            <div class="ks-box">
                <div class="ks-label">Terbayar</div>
                <div class="ks-val" style="color:#22c55e">{{ number_format($bpjsTB) }}</div>
            </div>
            <div class="ks-box">
                <div class="ks-label">Pending</div>
                <div class="ks-val" style="color:#f59e0b">{{ number_format($bpjsPD) }}</div>
            </div>
            <div class="ks-box">
                <div class="ks-label">Tdk Layak</div>
                <div class="ks-val" style="color:#f43f5e">{{ number_format($bpjsTL) }}</div>
            </div>
        </div>

        <div class="klaim-bar">
            <div class="kb-seg" style="width:{{ $p1 }}%;background:#22c55e"></div>
            <div class="kb-seg" style="width:{{ $p2 }}%;background:#f59e0b"></div>
            <div class="kb-seg" style="width:{{ $p3 }}%;background:#f43f5e"></div>
        </div>
        <div class="klaim-pct-row">
            <span style="font-size:.55rem;color:#22c55e;font-family:'DM Mono',monospace">{{ $p1 }}%</span>
            <span style="font-size:.55rem;color:#f59e0b;font-family:'DM Mono',monospace">{{ $p2 }}%</span>
            <span style="font-size:.55rem;color:#f43f5e;font-family:'DM Mono',monospace">{{ $p3 }}%</span>
        </div>
    </div>

    <div class="card-footer">
        <span class="card-open-btn">
            Lihat Detail
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </span>
        <span class="card-status-dot"></span>
    </div>
</a>

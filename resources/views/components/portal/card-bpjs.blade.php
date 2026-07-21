@props(['bpjs', 'bulanLabel', 'tahun'])

@php
    $bpjsRI  = $bpjs['rawat_inap']  ?? 0;
    $bpjsRJ  = $bpjs['rawat_jalan'] ?? 0;
    $bpjsTB  = $bpjs['terbayar']    ?? 0;
    $bpjsPD  = $bpjs['pending']      ?? 0;
    $bpjsTL  = $bpjs['tidak_layak'] ?? 0;
    $bpjsTot = $bpjsTB + $bpjsPD + $bpjsTL;
    $nominalTB = $bpjs['nominal_terbayar']    ?? 0;
    $nominalPD = $bpjs['nominal_pending']     ?? 0;
    $nominalTL = $bpjs['nominal_tidak_layak'] ?? 0;

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

<a href="{{ route('portal.klaimbpjs') }}" class="app-card theme-amber bpjs-card">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-amber">
                <x-icon name="shield-check" width="20" height="20" stroke-width="1.8" />
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
                <x-icon name="home" width="16" height="16" stroke="#3b82f6" stroke-width="1.8" />
            </div>
        </div>
        <div class="klaim-box">
            <div>
                <div class="klaim-label">Rawat Jalan (RJ)</div>
                <div class="klaim-val">{{ $juta($nominalRJ) }}</div>
            </div>
            <div class="klaim-icon icon-amber">
                <x-icon name="user" width="16" height="16" stroke="#f59e0b" stroke-width="1.8" />
            </div>
        </div>
    </div>

    <div>
        <div style="font-size:.6rem;color:#475569;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Status Klaim</div>
        <div class="klaim-status">
    <div class="ks-box">
        <div class="ks-label">Terbayar</div>
        <div class="ks-val" style="color:#22c55e">{{ $juta($nominalTB) }}</div>
        <div style="font-size:.6rem;color:#22c55e;opacity:.7;font-family:'DM Mono',monospace;margin-top:.2rem;letter-spacing:.03em;">{{ number_format($bpjsTB) }}</div>
    </div>
    <div class="ks-box">
        <div class="ks-label">Pending</div>
        <div class="ks-val" style="color:#f59e0b">{{ $juta($nominalPD) }}</div>
        <div style="font-size:.6rem;color:#f59e0b;opacity:.7;font-family:'DM Mono',monospace;margin-top:.2rem;letter-spacing:.03em;">{{ number_format($bpjsPD) }}</div>
    </div>
    <div class="ks-box">
        <div class="ks-label">Tdk Layak</div>
        <div class="ks-val" style="color:#f43f5e">{{ $juta($nominalTL) }}</div>
        <div style="font-size:.6rem;color:#f43f5e;opacity:.7;font-family:'DM Mono',monospace;margin-top:.2rem;letter-spacing:.03em;">{{ number_format($bpjsTL) }}</div>
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
            <x-icon name="chevron-right" width="14" height="14" stroke-width="2.5" />
        </span>
        <span class="card-status-dot"></span>
    </div>
</a>

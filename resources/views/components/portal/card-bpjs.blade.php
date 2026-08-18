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
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="amber"
        icon="shield-check"
        title="Klaim BPJS"
        subtitle="Ringkasan pengajuan klaim"
        :badge="$bulanLabel . ' ' . $tahun"
    />

    <div class="klaim-row">
        <div class="klaim-box">
            <div>
                <div class="klaim-label">Rawat Inap (RI)</div>
                <div class="klaim-val">{{ $juta($nominalRI) }}</div>
            </div>
            <div class="klaim-icon icon-blue">
                <x-icon name="home" width="16" height="16" stroke-width="1.8" />
            </div>
        </div>
        <div class="klaim-box">
            <div>
                <div class="klaim-label">Rawat Jalan (RJ)</div>
                <div class="klaim-val">{{ $juta($nominalRJ) }}</div>
            </div>
            <div class="klaim-icon icon-amber">
                <x-icon name="user" width="16" height="16" stroke-width="1.8" />
            </div>
        </div>
    </div>

    <div>
        <div class="klaim-section-label">Status Klaim</div>
        <div class="klaim-status">
            <div class="ks-box">
                <div class="ks-label">Terbayar</div>
                <div class="ks-val text-success">{{ $juta($nominalTB) }}</div>
                <div class="ks-count text-success" data-count-target="{{ $bpjsTB }}">
                    <span class="count-num">{{ number_format($bpjsTB) }}</span>
                </div>
            </div>
            <div class="ks-box">
                <div class="ks-label">Pending</div>
                <div class="ks-val text-warning">{{ $juta($nominalPD) }}</div>
                <div class="ks-count text-warning" data-count-target="{{ $bpjsPD }}">
                    <span class="count-num">{{ number_format($bpjsPD) }}</span>
                </div>
            </div>
            <div class="ks-box">
                <div class="ks-label">Tdk Layak</div>
                <div class="ks-val text-danger">{{ $juta($nominalTL) }}</div>
                <div class="ks-count text-danger" data-count-target="{{ $bpjsTL }}">
                    <span class="count-num">{{ number_format($bpjsTL) }}</span>
                </div>
            </div>
        </div>

        <div class="klaim-bar">
            <div class="kb-seg is-success" style="width:{{ $p1 }}%"></div>
            <div class="kb-seg is-warning" style="width:{{ $p2 }}%"></div>
            <div class="kb-seg is-danger"  style="width:{{ $p3 }}%"></div>
        </div>
        <div class="klaim-pct-row">
            <span class="pct-label text-success" data-count-target="{{ $p1 }}"><span class="count-num">{{ $p1 }}</span>%</span>
            <span class="pct-label text-warning" data-count-target="{{ $p2 }}"><span class="count-num">{{ $p2 }}</span>%</span>
            <span class="pct-label text-danger"  data-count-target="{{ $p3 }}"><span class="count-num">{{ $p3 }}</span>%</span>
        </div>
    </div>

    <x-portal.card-footer />
</a>
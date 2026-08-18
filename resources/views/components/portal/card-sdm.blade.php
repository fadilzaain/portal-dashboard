@props(['sdm', 'tahun'])

@php
    $totalSdm = $sdm['total']     ?? 0;
    $medisSdm = $sdm['medis']     ?? 0;
    $nonSdm   = $sdm['non_medis'] ?? 0;
    $totalSdm = $totalSdm ?: ($medisSdm + $nonSdm);

    $shiftPagi  = $sdm['shift_pagi']  ?? 0;
    $shiftSiang = $sdm['shift_siang'] ?? 0;
    $shiftMalam = $sdm['shift_malam'] ?? 0;

    $pctMedis = $totalSdm > 0 ? round($medisSdm / $totalSdm * 100) : 0;

    $r     = 28;
    $circ  = round(2 * M_PI * $r, 1);
    $dash1 = round($circ * $pctMedis / 100, 1);
    $dash2 = round($circ - $dash1, 1);
@endphp

<a href="{{ route('sdm.portal.sdm') }}" class="app-card theme-purple">
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="purple"
        icon="users"
        title="SDM"
        subtitle="Ringkasan Pegawai"
        :badge="$tahun"
    />

    {{-- Baris atas: angka + donut + legend --}}
    <div class="sdm-row">
        <div>
            <div class="sdm-big-num" data-count-target="{{ $totalSdm }}">
                <span class="count-num">{{ number_format($totalSdm) }}</span>
            </div>
            <div class="sdm-orang">Orang</div>
        </div>

        <div class="donut-wrap">
            <svg viewBox="0 0 64 64" width="64" height="64">
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="var(--wash-2)" stroke-width="8"/>
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="var(--sky)" stroke-width="8"
                    stroke-dasharray="{{ $dash1 }} {{ $dash2 }}"
                    stroke-linecap="round"/>
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="var(--violet)" stroke-width="8"
                    stroke-dasharray="{{ $dash2 }} {{ $dash1 }}"
                    stroke-dashoffset="-{{ $dash1 }}"
                    stroke-linecap="round"/>
            </svg>
            <div class="donut-center" data-count-target="{{ $pctMedis }}">
                <span class="count-num">{{ $pctMedis }}</span>%
            </div>
        </div>

        <div class="sdm-legend">
            <div class="legend-row">
                <span class="legend-dot legend-dot-sky"></span>
                Medis
                <span class="legend-val" data-count-target="{{ $medisSdm }}">
                    <span class="count-num">{{ number_format($medisSdm) }}</span>
                </span>
            </div>
            <div class="legend-row">
                <span class="legend-dot legend-dot-violet"></span>
                Non Medis
                <span class="legend-val" data-count-target="{{ $nonSdm }}">
                    <span class="count-num">{{ number_format($nonSdm) }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Baris bawah: shift --}}
    <div class="sdm-shift-grid">
        <div class="stat-box stat-box-compact">
            <div class="stat-box-label shift-pagi">Pagi</div>
            <div class="stat-box-val">{{ number_format($shiftPagi) }}</div>
        </div>
        <div class="stat-box stat-box-compact">
            <div class="stat-box-label shift-siang">Siang</div>
            <div class="stat-box-val">{{ number_format($shiftSiang) }}</div>
        </div>
        <div class="stat-box stat-box-compact">
            <div class="stat-box-label shift-malam">Malam</div>
            <div class="stat-box-val">{{ number_format($shiftMalam) }}</div>
        </div>
    </div>

    <x-portal.card-footer />
</a>
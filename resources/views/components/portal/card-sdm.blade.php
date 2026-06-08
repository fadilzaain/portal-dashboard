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
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-purple">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="card-title-wrap">
                <div class="app-name">SDM</div>
                <div class="app-sub">Ringkasan Pegawai</div>
            </div>
        </div>
        <span class="card-month-badge month-purple">{{ $tahun }}</span>
    </div>

    {{-- Baris atas: angka + donut + legend --}}
    <div class="sdm-row">
        <div>
            <div class="sdm-big-num">{{ number_format($totalSdm) }}</div>
            <div class="sdm-orang">Orang</div>
        </div>

        <div class="donut-wrap">
            <svg viewBox="0 0 64 64" width="64" height="64">
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="rgba(139,92,246,.15)" stroke-width="8"/>
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="#3b82f6" stroke-width="8"
                    stroke-dasharray="{{ $dash1 }} {{ $dash2 }}"
                    stroke-linecap="round"/>
                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                    stroke="#8b5cf6" stroke-width="8"
                    stroke-dasharray="{{ $dash2 }} {{ $dash1 }}"
                    stroke-dashoffset="-{{ $dash1 }}"
                    stroke-linecap="round"/>
            </svg>
            <div class="donut-center">{{ $pctMedis }}%</div>
        </div>

        <div class="sdm-legend">
            <div class="legend-row">
                <span class="legend-dot" style="background:#3b82f6"></span>
                Medis
                <span class="legend-val">{{ number_format($medisSdm) }}</span>
            </div>
            <div class="legend-row">
                <span class="legend-dot" style="background:#8b5cf6"></span>
                Non Medis
                <span class="legend-val">{{ number_format($nonSdm) }}</span>
            </div>
        </div>
    </div>{{-- tutup .sdm-row --}}

    {{-- Baris bawah: shift --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;">
        <div class="stat-box" style="text-align:center;">
            <div class="stat-box-label" style="color:#f59e0b">Pagi</div>
            <div class="stat-box-val" style="font-size:.85rem">{{ number_format($shiftPagi) }}</div>
        </div>
        <div class="stat-box" style="text-align:center;">
            <div class="stat-box-label" style="color:#fb923c">Siang</div>
            <div class="stat-box-val" style="font-size:.85rem">{{ number_format($shiftSiang) }}</div>
        </div>
        <div class="stat-box" style="text-align:center;">
            <div class="stat-box-label" style="color:#38bdf8">Malam</div>
            <div class="stat-box-val" style="font-size:.85rem">{{ number_format($shiftMalam) }}</div>
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
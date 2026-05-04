@props(['mutu', 'bulanLabel', 'tahun'])

@php
    $mutuTotal    = $mutu['total']          ?? 0;
    $mutuTercapai = $mutu['tercapai']       ?? 0;
    $mutuTidak    = $mutu['tidak_tercapai'] ?? 0;
    $mutuPct      = $mutuTotal > 0 ? round($mutuTercapai / $mutuTotal * 100) : 0;
@endphp

<a href="{{ route('portal.indikatormutu') }}" class="app-card theme-rose">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-rose">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="card-title-wrap">
                <div class="app-name">Indikator Mutu</div>
                <div class="app-sub">Capaian indikator mutu</div>
            </div>
        </div>
        <span class="card-month-badge month-rose">{{ $bulanLabel }} {{ $tahun }}</span>
    </div>

    <div class="mutu-stats">
        <div class="mutu-box">
            <div class="mutu-box-label">Total</div>
            <div class="mutu-icon-wrap" style="background:rgba(245,158,11,.15)">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                </svg>
            </div>
            <div class="mutu-box-val" style="color:#f59e0b">{{ $mutuTotal }}</div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tercapai</div>
            <div class="mutu-icon-wrap" style="background:rgba(34,197,94,.15)">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="mutu-box-val" style="color:#22c55e">{{ $mutuTercapai }}</div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tidak</div>
            <div class="mutu-icon-wrap" style="background:rgba(244,63,94,.15)">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#f43f5e" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div class="mutu-box-val" style="color:#f43f5e">{{ $mutuTidak }}</div>
        </div>
    </div>

    <div>
        <div class="mutu-progress-label">
            <span>Capaian</span>
            <span style="color:#f59e0b;font-weight:700;font-family:'DM Mono',monospace">{{ $mutuPct }}%</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:{{ $mutuPct }}%;background:#f59e0b"></div>
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

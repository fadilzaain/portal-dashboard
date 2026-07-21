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
                <x-icon name="document-text" width="20" height="20" stroke-width="1.8" />
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
                <x-icon name="exclamation-circle" width="14" height="14" stroke="#f59e0b" stroke-width="2" />
            </div>
            <div class="mutu-box-val" style="color:#f59e0b">{{ $mutuTotal }}</div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tercapai</div>
            <div class="mutu-icon-wrap" style="background:rgba(34,197,94,.15)">
                <x-icon name="check" width="14" height="14" stroke="#22c55e" stroke-width="2.5" />
            </div>
            <div class="mutu-box-val" style="color:#22c55e">{{ $mutuTercapai }}</div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tidak</div>
            <div class="mutu-icon-wrap" style="background:rgba(244,63,94,.15)">
                <x-icon name="x-mark" width="14" height="14" stroke="#f43f5e" stroke-width="2.5" />
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
            <x-icon name="chevron-right" width="14" height="14" stroke-width="2.5" />
        </span>
        <span class="card-status-dot"></span>
    </div>
</a>

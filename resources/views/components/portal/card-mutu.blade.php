@props(['mutu', 'bulanLabel', 'tahun'])

@php
    $mutuTotal    = $mutu['total']          ?? 0;
    $mutuTercapai = $mutu['tercapai']       ?? 0;
    $mutuTidak    = $mutu['tidak_tercapai'] ?? 0;
    $mutuPct      = $mutuTotal > 0 ? round($mutuTercapai / $mutuTotal * 100) : 0;
@endphp

<a href="{{ route('portal.indikatormutu') }}" class="app-card theme-rose">
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="rose"
        icon="document-text"
        title="Indikator Mutu"
        subtitle="Capaian indikator mutu"
        :badge="$bulanLabel . ' ' . $tahun"
    />

    <div class="mutu-stats">
        <div class="mutu-box">
            <div class="mutu-box-label">Total</div>
            <div class="mutu-icon-wrap icon-warning">
                <x-icon name="exclamation-circle" width="14" height="14" stroke-width="2" />
            </div>
            <div class="mutu-box-val text-warning" data-count-target="{{ $mutuTotal }}">
                <span class="count-num">{{ $mutuTotal }}</span>
            </div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tercapai</div>
            <div class="mutu-icon-wrap icon-success">
                <x-icon name="check" width="14" height="14" stroke-width="2.5" />
            </div>
            <div class="mutu-box-val text-success" data-count-target="{{ $mutuTercapai }}">
                <span class="count-num">{{ $mutuTercapai }}</span>
            </div>
        </div>
        <div class="mutu-box">
            <div class="mutu-box-label">Tidak</div>
            <div class="mutu-icon-wrap icon-danger">
                <x-icon name="x-mark" width="14" height="14" stroke-width="2.5" />
            </div>
            <div class="mutu-box-val text-danger" data-count-target="{{ $mutuTidak }}">
                <span class="count-num">{{ $mutuTidak }}</span>
            </div>
        </div>
    </div>

    <div>
        <div class="mutu-progress-label">
            <span>Capaian</span>
            <span class="mutu-progress-value text-warning" data-count-target="{{ $mutuPct }}">
                <span class="count-num">{{ $mutuPct }}</span>%
            </span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill progress-warning" style="width:{{ $mutuPct }}%"></div>
        </div>
    </div>

    <x-portal.card-footer />
</a>
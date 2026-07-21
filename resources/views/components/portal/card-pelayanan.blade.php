@props(['pelayanan', 'bulanLabel', 'tahun'])

@php
    $borVal = $pelayanan['bor'] ?? 0;
    $losVal = $pelayanan['los'] ?? 0;
    $toiVal = $pelayanan['toi'] ?? 0;
    $btoVal = $pelayanan['bto'] ?? 0;

    $borStatus = ($borVal >= 60 && $borVal <= 85) ? 'badge-ideal' : 'badge-warn';
    $borLabel  = ($borVal >= 60 && $borVal <= 85) ? 'Ideal' : ($borVal < 60 ? 'Rendah' : 'Tinggi');

    $losStatus = ($losVal >= 6 && $losVal <= 9) ? 'badge-baik' : 'badge-warn';
    $losLabel  = ($losVal >= 6 && $losVal <= 9) ? 'Baik' : 'Periksa';

    $toiStatus = ($toiVal >= 1 && $toiVal <= 3) ? 'badge-baik' : 'badge-warn';
    $toiLabel  = ($toiVal >= 1 && $toiVal <= 3) ? 'Baik' : 'Periksa';

    $btoStatus = ($btoVal >= 40 && $btoVal <= 50) ? 'badge-ideal' : 'badge-warn';
    $btoLabel  = ($btoVal >= 40 && $btoVal <= 50) ? 'Ideal' : 'Periksa';
@endphp

<a href="{{ route('portal.pelayananpasien') }}" class="app-card theme-blue">
    <div class="card-header-row">
        <div class="card-header-left">
            <div class="app-icon icon-blue">
                <x-icon name="heart" width="20" height="20" stroke-width="1.8" />
            </div>
            <div class="card-title-wrap">
                <div class="app-name">Pelayanan Pasien</div>
                <div class="app-sub">Ringkasan Pelayanan Pasien</div>
            </div>
        </div>
            <span class="card-month-badge month-blue">{{ $bulanLabel }} {{ $tahun }}</span>
    </div>

    <div class="card-stats">
        <div class="stat-box">
            <div class="stat-box-label">BOR</div>
            <div class="stat-box-val">
                {{ $borVal }}%
                <span class="stat-box-badge {{ $borStatus }}">{{ $borLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">LOS</div>
            <div class="stat-box-val">
                {{ $losVal }} hr
                <span class="stat-box-badge {{ $losStatus }}">{{ $losLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">TOI</div>
            <div class="stat-box-val">
                {{ $toiVal }} hr
                <span class="stat-box-badge {{ $toiStatus }}">{{ $toiLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">BTO</div>
            <div class="stat-box-val">
                {{ $btoVal }}
                <span class="stat-box-badge {{ $btoStatus }}">{{ $btoLabel }}</span>
            </div>
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

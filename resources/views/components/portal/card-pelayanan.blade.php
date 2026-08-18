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

<a href="{{ route('portal.pelayananpasien') }}" class="app-card theme-teal">
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="teal"
        icon="heart"
        title="Pelayanan Pasien"
        subtitle="Ringkasan Pelayanan Pasien"
        :badge="$bulanLabel . ' ' . $tahun"
    />

    <div class="card-stats">
        <div class="stat-box">
            <div class="stat-box-label">BOR</div>
            <div class="stat-box-val" data-count-target="{{ $borVal }}" data-count-decimal="1">
                <span class="count-num">{{ number_format($borVal, 1, ',', '.') }}</span>%
                <span class="stat-box-badge {{ $borStatus }}">{{ $borLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">LOS</div>
            <div class="stat-box-val" data-count-target="{{ $losVal }}" data-count-decimal="1">
                <span class="count-num">{{ number_format($losVal, 1, ',', '.') }}</span> hr
                <span class="stat-box-badge {{ $losStatus }}">{{ $losLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">TOI</div>
            <div class="stat-box-val" data-count-target="{{ $toiVal }}" data-count-decimal="1">
                <span class="count-num">{{ number_format($toiVal, 1, ',', '.') }}</span> hr
                <span class="stat-box-badge {{ $toiStatus }}">{{ $toiLabel }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-label">BTO</div>
            <div class="stat-box-val" data-count-target="{{ $btoVal }}" data-count-decimal="1">
                <span class="count-num">{{ number_format($btoVal, 1, ',', '.') }}</span>
                <span class="stat-box-badge {{ $btoStatus }}">{{ $btoLabel }}</span>
            </div>
        </div>
    </div>

    <x-portal.card-footer />
</a>
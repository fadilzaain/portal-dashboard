@extends('layouts.app')

@section('title', 'SDM')
@section('page_title', 'SDM')
@section('page_subtitle', 'Monitoring Pegawai')

@push('styles')
<style>
    /* Stat Card */
    .sdm-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .sdm-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e9ecef;
        border-top: 4px solid transparent;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .sdm-stat-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .sdm-stat-card.blue   { border-top-color: #3b82f6; }
    .sdm-stat-card.teal   { border-top-color: #14b8a6; }
    .sdm-stat-card.amber  { border-top-color: #f59e0b; }
    .sdm-stat-card.rose   { border-top-color: #f43f5e; }

    .sdm-stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sdm-stat-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
    }
    .sdm-stat-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .sdm-stat-icon.blue   { background: #eff6ff; }
    .sdm-stat-icon.teal   { background: #f0fdfa; }
    .sdm-stat-icon.amber  { background: #fffbeb; }
    .sdm-stat-icon.rose   { background: #fff1f2; }

    .sdm-stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }
    .sdm-stat-sub {
        font-size: 12px;
        color: #9ca3af;
    }

    /* Charts Row */
    .sdm-charts-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    .sdm-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
    }
    .sdm-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .sdm-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }
    .sdm-card-sub {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }
    .sdm-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .sdm-badge-blue  { background: #eff6ff; color: #3b82f6; }
    .sdm-badge-teal  { background: #f0fdfa; color: #14b8a6; }
    .sdm-badge-amber { background: #fffbeb; color: #f59e0b; }

    /* Pns Honorer Summary */
    .status-boxes {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 14px;
    }
    .status-box {
        border-radius: 8px;
        padding: 14px;
        text-align: center;
    }
    .status-box.pns     { background: #eff6ff; border: 1px solid #bfdbfe; }
    .status-box.honorer { background: #fffbeb; border: 1px solid #fde68a; }
    .status-box .sb-val {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .status-box.pns     .sb-val { color: #3b82f6; }
    .status-box.honorer .sb-val { color: #f59e0b; }
    .status-box .sb-lbl {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
    }
    .status-box .sb-pct {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* Progress bar */
    .sdm-progress-wrap {
        background: #f3f4f6;
        border-radius: 99px;
        height: 8px;
        overflow: hidden;
        margin: 4px 0 16px;
    }
    .sdm-progress-bar {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, #3b82f6, #14b8a6);
        transition: width 1s ease;
    }

    /* Wrap Biar gak Terlalu Tinggi */
    .doughnut-wrap {
        max-width: 320px;
        margin: 0 auto;
    }

    /* Full Width Bar Chart */
    .sdm-card-full {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }

    /* Gender Row*/
    .sdm-gender-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 8px;
    }
    .sdm-gender-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .sdm-gender-emoji { font-size: 32px; }
    .sdm-gender-val {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }
    .sdm-gender-lbl { font-size: 12px; color: #9ca3af; margin-top: 3px; }

    /* Tampilan Responsive */
    @media (max-width: 1024px) {
        .sdm-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .sdm-stats-grid  { grid-template-columns: 1fr; }
        .sdm-gender-row  { grid-template-columns: 1fr; }
        .status-boxes    { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="sdm-stats-grid">
    <div class="sdm-stat-card blue">
        <div class="sdm-stat-header">
            <span class="sdm-stat-label">Total Pegawai</span>
            <div class="sdm-stat-icon blue">👥</div>
        </div>
        <div class="sdm-stat-value">{{ number_format($totalPegawai) }}</div>
        <div class="sdm-stat-sub">Seluruh data pegawai terdaftar</div>
    </div>

    <div class="sdm-stat-card teal">
        <div class="sdm-stat-header">
            <span class="sdm-stat-label">Aktif</span>
            <div class="sdm-stat-icon teal">✅</div>
        </div>
        <div class="sdm-stat-value">{{ number_format($totalAktif) }}</div>
        <div class="sdm-stat-sub">Pegawai sedang aktif bertugas</div>
    </div>

    <div class="sdm-stat-card amber">
        <div class="sdm-stat-header">
            <span class="sdm-stat-label">Pensiun</span>
            <div class="sdm-stat-icon amber">🏖️</div>
        </div>
        <div class="sdm-stat-value">{{ number_format($totalPensiun) }}</div>
        <div class="sdm-stat-sub">Pegawai telah pensiun</div>
    </div>

    <div class="sdm-stat-card rose">
        <div class="sdm-stat-header">
            <span class="sdm-stat-label">Keluar</span>
            <div class="sdm-stat-icon rose">🚪</div>
        </div>
        <div class="sdm-stat-value">{{ number_format($totalKeluar) }}</div>
        <div class="sdm-stat-sub">Pegawai tidak aktif / keluar</div>
    </div>
</div>

{{-- Status Pns v Honorer --}}
<div class="sdm-charts-row">
    <div class="sdm-card">
        <div class="sdm-card-header">
            <div>
                <div class="sdm-card-title">Status Kepegawaian</div>
                <div class="sdm-card-sub">Pegawai aktif — PNS vs Honorer</div>
            </div>
            <span class="sdm-badge sdm-badge-teal">Status Aktif</span>
        </div>

        @php
            $totalAktifStatus = $totalPns + $totalHonorer;
            $pctPns     = $totalAktifStatus > 0 ? round(($totalPns / $totalAktifStatus) * 100, 1) : 0;
            $pctHonorer = $totalAktifStatus > 0 ? round(($totalHonorer / $totalAktifStatus) * 100, 1) : 0;
        @endphp

        <div class="status-boxes">
            <div class="status-box pns">
                <div class="sb-val">{{ number_format($totalPns) }}</div>
                <div class="sb-lbl">PNS</div>
                <div class="sb-pct">{{ $pctPns }}% dari aktif</div>
            </div>
            <div class="status-box honorer">
                <div class="sb-val">{{ number_format($totalHonorer) }}</div>
                <div class="sb-lbl">Honorer</div>
                <div class="sb-pct">{{ $pctHonorer }}% dari aktif</div>
            </div>
        </div>

        <div style="font-size:12px; color:#9ca3af; margin-bottom:4px;">Proporsi PNS</div>
        <div class="sdm-progress-wrap">
            <div class="sdm-progress-bar" style="width: {{ $pctPns }}%"></div>
        </div>

        <div class="doughnut-wrap">
            <canvas id="statusDoughnut" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Distribusi Unit Kerja --}}
<div class="sdm-card-full">
    <div class="sdm-card-header">
        <div>
            <div class="sdm-card-title">Distribusi Pegawai per Unit Kerja</div>
            <div class="sdm-card-sub">Top 10 unit kerja dengan pegawai aktif terbanyak</div>
        </div>
        <span class="sdm-badge sdm-badge-blue">Top 10</span>
    </div>
    <canvas id="unitBarChart" height="110"></canvas>
</div>

{{-- Gender --}}
<div class="sdm-gender-row">
    <div class="sdm-gender-card">
        <div class="sdm-gender-emoji">👨</div>
        <div>
            <div class="sdm-gender-val">{{ number_format($lakiLaki) }}</div>
            <div class="sdm-gender-lbl">Laki-laki (pegawai aktif)</div>
        </div>
    </div>
    <div class="sdm-gender-card">
        <div class="sdm-gender-emoji">👩</div>
        <div>
            <div class="sdm-gender-val">{{ number_format($perempuan) }}</div>
            <div class="sdm-gender-lbl">Perempuan (pegawai aktif)</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
//Doughnut Pns v Honorer
new Chart(document.getElementById('statusDoughnut'), {
    type: 'doughnut',
    data: {
        labels: ['PNS', 'Honorer'],
        datasets: [{
            data: [{{ $totalPns }}, {{ $totalHonorer }}],
            backgroundColor: ['#3b82f6', '#f59e0b'],
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 20, font: { size: 12 } }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('id-ID')} orang`
                }
            }
        }
    }
});

// Horizontal bar Distrubsi Kerja
new Chart(document.getElementById('unitBarChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($unitLabels) !!},
        datasets: [{
            label: 'Jumlah Pegawai Aktif',
            data: {!! json_encode($unitData) !!},
            backgroundColor: [
                '#3b82f6','#14b8a6','#8b5cf6','#f59e0b','#f43f5e',
                '#06b6d4','#f97316','#10b981','#a855f7','#ec4899',
            ],
            borderColor: 'transparent',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        scales: {
            x: {
                grid: { color: '#f3f4f6' },
                beginAtZero: true,
                ticks: { callback: val => val.toLocaleString('id-ID') }
            },
            y: { grid: { display: false } }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.parsed.x.toLocaleString('id-ID')} pegawai`
                }
            }
        }
    }
});
</script>
@endpush
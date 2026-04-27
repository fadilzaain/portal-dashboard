{{-- resources/views/portal/pelayanan-pasien.blade.php --}}
@extends('layouts.app') {{-- Sesuaikan dengan layout utama dashboard Anda --}}

@section('title', 'Portal Pelayanan Pasien')

@push('styles')
<style>
  /* Design Token */
  :root {
    --pp-bg:          #0d1117;
    --pp-surface:     #161b22;
    --pp-surface2:    #1c2330;
    --pp-border:      rgba(48,54,61,0.8);
    --pp-text:        #e6edf3;
    --pp-text-muted:  #7d8590;
    --pp-text-dim:    #484f58;
    --pp-accent:      #2563eb;
    --pp-accent-glow: rgba(37,99,235,0.15);
    --pp-green:       #22c55e;
    --pp-yellow:      #f59e0b;
    --pp-red:         #ef4444;
    --pp-cyan:        #06b6d4;
    --pp-purple:      #a78bfa;
    --pp-font:        'DM Sans', system-ui, sans-serif;
    --pp-mono:        'JetBrains Mono', monospace;
  }

  .pp-wrap { font-family: var(--pp-font); color: var(--pp-text); }

  /* Header */
  .pp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
  }
  .pp-header-left h1 {
    font-size: 22px;
    font-weight: 600;
    letter-spacing: -0.3px;
    margin: 0;
  }
  .pp-header-left p {
    font-size: 13px;
    color: var(--pp-text-muted);
    margin: 4px 0 0;
  }
  .pp-badge-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(34,197,94,0.12);
    border: 1px solid rgba(34,197,94,0.3);
    color: var(--pp-green);
    font-size: 12px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 500;
  }
  .pp-badge-live::before {
    content: '';
    width: 6px; height: 6px;
    background: var(--pp-green);
    border-radius: 50%;
    animation: pulse-dot 1.5s ease-in-out infinite;
  }
  @keyframes pulse-dot {
    0%,100% { opacity:1; }
    50% { opacity:0.3; }
  }

  /* Filter Bar */
  .pp-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    background: var(--pp-surface);
    border: 1px solid var(--pp-border);
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 24px;
  }
  .pp-filter-bar label { font-size: 12px; color: var(--pp-text-muted); font-weight: 500; }
  .pp-filter-bar input[type="date"],
  .pp-filter-bar select {
    background: var(--pp-surface2);
    border: 1px solid var(--pp-border);
    border-radius: 8px;
    color: var(--pp-text);
    font-size: 13px;
    padding: 7px 12px;
    outline: none;
    font-family: var(--pp-font);
  }
  .pp-filter-bar input[type="date"]:focus,
  .pp-filter-bar select:focus {
    border-color: var(--pp-accent);
    box-shadow: 0 0 0 3px var(--pp-accent-glow);
  }
  .pp-btn {
    background: var(--pp-accent);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    padding: 8px 18px;
    cursor: pointer;
    font-family: var(--pp-font);
    transition: opacity .15s;
  }
  .pp-btn:hover { opacity: .85; }
  .pp-btn-ghost {
    background: transparent;
    border: 1px solid var(--pp-border);
    color: var(--pp-text-muted);
  }
  .pp-btn-ghost:hover { background: var(--pp-surface2); }

  /* Indikator Mutu Cards */
  .pp-indikator-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
  }
  @media (max-width: 1100px) { .pp-indikator-grid { grid-template-columns: repeat(2,1fr); } }
  @media (max-width: 600px)  { .pp-indikator-grid { grid-template-columns: 1fr; } }

  .pp-indikator-card {
    background: var(--pp-surface);
    border: 1px solid var(--pp-border);
    border-radius: 14px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: border-color .2s, box-shadow .2s;
  }
  .pp-indikator-card:hover {
    border-color: rgba(37,99,235,0.5);
    box-shadow: 0 0 0 1px rgba(37,99,235,0.1), 0 4px 24px rgba(0,0,0,0.3);
  }
  .pp-indikator-card .glow-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    border-radius: 14px 14px 0 0;
  }
  .pp-indikator-card .ic-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--pp-text-muted);
    margin-bottom: 8px;
  }
  .pp-indikator-card .ic-value {
    font-size: 36px;
    font-weight: 700;
    letter-spacing: -1px;
    line-height: 1;
    font-family: var(--pp-mono);
  }
  .pp-indikator-card .ic-unit {
    font-size: 14px;
    font-weight: 400;
    color: var(--pp-text-muted);
    margin-left: 4px;
  }
  .pp-indikator-card .ic-standar {
    font-size: 11px;
    color: var(--pp-text-muted);
    margin-top: 10px;
  }
  .pp-indikator-card .ic-progress {
    margin-top: 10px;
    height: 4px;
    background: var(--pp-surface2);
    border-radius: 4px;
    overflow: hidden;
  }
  .pp-indikator-card .ic-progress-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 1s ease;
  }
  .pp-indikator-card .ic-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 500;
    margin-top: 8px;
    padding: 3px 8px;
    border-radius: 6px;
  }
  .status-baik    { background: rgba(34,197,94,0.12); color: var(--pp-green); }
  .status-waspada { background: rgba(245,158,11,0.12); color: var(--pp-yellow); }
  .status-buruk   { background: rgba(239,68,68,0.12);  color: var(--pp-red); }

  /* Unit Cards (ranap-rajal-IGD) */
  .pp-unit-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
  }
  @media (max-width: 900px)  { .pp-unit-grid { grid-template-columns: 1fr; } }

  .pp-unit-card {
    background: var(--pp-surface);
    border: 1px solid var(--pp-border);
    border-radius: 14px;
    padding: 20px;
  }
  .pp-unit-card .uc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }
  .pp-unit-card .uc-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--pp-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }
  .pp-unit-card .uc-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 500;
  }
  .pp-unit-card .uc-main-value {
    font-size: 42px;
    font-weight: 700;
    letter-spacing: -1.5px;
    line-height: 1;
    font-family: var(--pp-mono);
    margin-bottom: 4px;
  }
  .pp-unit-card .uc-main-label {
    font-size: 12px;
    color: var(--pp-text-muted);
    margin-bottom: 16px;
  }
  .pp-unit-card .uc-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding-top: 14px;
    border-top: 1px solid var(--pp-border);
  }
  .pp-unit-card .uc-stat-item .uc-stat-val {
    font-size: 20px;
    font-weight: 600;
    font-family: var(--pp-mono);
  }
  .pp-unit-card .uc-stat-item .uc-stat-lbl {
    font-size: 11px;
    color: var(--pp-text-muted);
    margin-top: 2px;
  }

  /* Chart Session */
  .pp-chart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
  }
  @media (max-width: 900px) { .pp-chart-grid { grid-template-columns: 1fr; } }

  .pp-chart-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
  }
  @media (max-width: 1100px) { .pp-chart-grid-3 { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 700px)  { .pp-chart-grid-3 { grid-template-columns: 1fr; } }

  /* Card Canvas */
  .pp-chart-card .chart-container {
    position: relative;
    height: 240px;   
    width: 100%;
  }
    .pp-chart-card .chart-container-sm {
    position: relative;
    height: 200px;
    width: 100%;
  }
  .pp-chart-card {
    background: var(--pp-surface);
    border: 1px solid var(--pp-border);
    border-radius: 14px;
    padding: 20px;
  }
  .pp-chart-card .cc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }
  .pp-chart-card .cc-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--pp-text);
  }
  .pp-chart-card .cc-subtitle {
    font-size: 11px;
    color: var(--pp-text-muted);
    margin-top: 2px;
  }
  /* Wrapper */
  .chart-canvas-wrap {
    position: relative;
    height: 240px;
    width: 100%;
  }
  .chart-canvas-wrap-sm {
    position: relative;
    height: 210px;
    width: 100%;
  }
  canvas {
    max-height: 100% !important;
  }

  /* Tabel Poli */
  .pp-table-wrap { overflow-x: auto; }
  .pp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }
  .pp-table th {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--pp-text-muted);
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid var(--pp-border);
  }
  .pp-table td {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(48,54,61,0.5);
    color: var(--pp-text);
    vertical-align: middle;
  }
  .pp-table tr:last-child td { border-bottom: none; }
  .pp-table tr:hover td { background: var(--pp-surface2); }
  .pp-poli-bar-wrap { display:flex; align-items:center; gap:8px; }
  .pp-poli-bar {
    flex: 1;
    height: 6px;
    background: var(--pp-surface2);
    border-radius: 6px;
    overflow: hidden;
    max-width: 100px;
  }
  .pp-poli-bar-fill {
    height: 100%;
    background: var(--pp-accent);
    border-radius: 6px;
    transition: width 1s ease;
  }

  /* Section tittle */
  .pp-section-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--pp-text-muted);
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pp-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--pp-border);
  }

  /* Load Skeleton */
  .pp-skeleton {
    background: linear-gradient(90deg, var(--pp-surface) 25%, var(--pp-surface2) 50%, var(--pp-surface) 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s infinite;
    border-radius: 6px;
  }
  @keyframes skeleton-shimmer { to { background-position: -200% 0; } }
</style>

{{-- Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush


@section('content')
<div class="pp-wrap">
  {{-- Banner dummy data (hilang otomatis saat DB sudah terkoneksi) --}}
@if($isDummy)
<div style="
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.35);
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 20px;
    font-size: 12px;
    color: #f59e0b;
    display: flex;
    align-items: center;
    gap: 8px;
">
    ⚠ <strong></strong> — Menampilkan data dummy. Koneksi ke database <code style="background:rgba(245,158,11,0.15);padding:1px 6px;border-radius:4px;">erm_rs</code> belum tersedia.
</div>
@endif

  {{-- HEADER --}}
  <div class="pp-header">
    <div class="pp-header-left">
      <h1>Portal Pelayanan Pasien</h1>
      <p>Indikator mutu & kunjungan pasien — periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <span class="pp-badge-live">Live Data</span>
      <a href="{{ route('portal.pelayananpasien.ranap', request()->query()) }}" class="pp-btn pp-btn-ghost" style="font-size:12px; padding:7px 14px;">
        Detail Ranap →
      </a>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <form method="GET" action="{{ route('portal.pelayananpasien') }}" class="pp-filter-bar">
    <label>Dari</label>
    <input type="date" name="dari" value="{{ $tanggalMulai }}">
    <label>Sampai</label>
    <input type="date" name="sampai" value="{{ $tanggalSelesai }}">
    <label>Tahun BOR</label>
    <select name="tahun">
      @for ($y = now()->year; $y >= now()->year - 4; $y--)
        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
      @endfor
    </select>
    <button type="submit" class="pp-btn">Tampilkan</button>
    <a href="{{ route('portal.pelayananpasien') }}" class="pp-btn pp-btn-ghost">Reset</a>
  </form>

  {{-- SECTION: INDIKATOR MUTU --}}
  <p class="pp-section-title">Indikator Mutu Pelayanan Rawat Inap</p>

  <div class="pp-indikator-grid">

    @php
      $indikators = [
        [
          'key'    => 'BOR',
          'label'  => 'Bed Occupancy Rate',
          'nilai'  => $bor,
          'unit'   => '%',
          'min'    => $standar['bor_min'],
          'max'    => $standar['bor_max'],
          'color'  => '#2563eb',
          'desc'   => 'Pemakaian tempat tidur',
          'pct'    => min(round($bor), 100),
        ],
        [
          'key'    => 'LOS',
          'label'  => 'Length of Stay',
          'nilai'  => $los,
          'unit'   => 'hari',
          'min'    => $standar['los_min'],
          'max'    => $standar['los_max'],
          'color'  => '#a78bfa',
          'desc'   => 'Rata-rata lama dirawat',
          'pct'    => min(round(($los / 15) * 100), 100),
        ],
        [
          'key'    => 'TOI',
          'label'  => 'Turn Over Interval',
          'nilai'  => $toi,
          'unit'   => 'hari',
          'min'    => $standar['toi_min'],
          'max'    => $standar['toi_max'],
          'color'  => '#06b6d4',
          'desc'   => 'Interval antar pasien',
          'pct'    => min(round(($toi / 10) * 100), 100),
        ],
        [
          'key'    => 'BTO',
          'label'  => 'Bed Turn Over',
          'nilai'  => $bto,
          'unit'   => 'kali',
          'min'    => $standar['bto_min'],
          'max'    => $standar['bto_max'],
          'color'  => '#f59e0b',
          'desc'   => 'Frekuensi pemakaian TT',
          'pct'    => min(round(($bto / 60) * 100), 100),
        ],
      ];

      foreach ($indikators as &$ind) {
        if ($ind['nilai'] >= $ind['min'] && $ind['nilai'] <= $ind['max']) {
          $ind['status'] = 'baik';
          $ind['status_label'] = '✓ Ideal';
        } elseif ($ind['nilai'] < $ind['min'] * 0.8 || $ind['nilai'] > $ind['max'] * 1.2) {
          $ind['status'] = 'buruk';
          $ind['status_label'] = '✗ Di luar standar';
        } else {
          $ind['status'] = 'waspada';
          $ind['status_label'] = '⚠ Perlu perhatian';
        }
      }
    @endphp

    @foreach ($indikators as $ind)
    <div class="pp-indikator-card">
      <div class="glow-bar" style="background: {{ $ind['color'] }};"></div>
      <div class="ic-label">{{ $ind['key'] }} — {{ $ind['label'] }}</div>
      <div class="ic-value" style="color: {{ $ind['color'] }}">
        {{ number_format($ind['nilai'], 1) }}<span class="ic-unit">{{ $ind['unit'] }}</span>
      </div>
      <div class="ic-standar">Standar: {{ $ind['min'] }}–{{ $ind['max'] }} {{ $ind['unit'] }}</div>
      <div class="ic-progress">
        <div class="ic-progress-bar" style="width: {{ $ind['pct'] }}%; background: {{ $ind['color'] }};"></div>
      </div>
      <span class="ic-status status-{{ $ind['status'] }}">{{ $ind['status_label'] }}</span>
    </div>
    @endforeach

  </div>

  {{-- SECTION: UNIT PELAYANAN --}}
  <p class="pp-section-title">Statistik Unit Pelayanan</p>

  <div class="pp-unit-grid">

    {{-- RANAP --}}
    <div class="pp-unit-card">
      <div class="uc-header">
        <span class="uc-title">Rawat Inap</span>
        <span class="uc-badge" style="background:rgba(37,99,235,.15);color:#60a5fa;">RANAP</span>
      </div>
      <div class="uc-main-value" style="color:#60a5fa;">{{ number_format($ringkasanRanap['total_masuk']) }}</div>
      <div class="uc-main-label">Total pasien masuk periode ini</div>
      <div class="uc-stats">
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#22c55e;">{{ number_format($ringkasanRanap['total_keluar']) }}</div>
          <div class="uc-stat-lbl">Keluar / pulang</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#f59e0b;">{{ number_format($ringkasanRanap['masih_dirawat']) }}</div>
          <div class="uc-stat-lbl">Masih dirawat</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#ef4444;">{{ number_format($ringkasanRanap['total_meninggal']) }}</div>
          <div class="uc-stat-lbl">Meninggal</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#a78bfa;">{{ $los }} hr</div>
          <div class="uc-stat-lbl">Rata-rata LOS</div>
        </div>
      </div>
    </div>

    {{-- RAJAL --}}
    <div class="pp-unit-card">
      <div class="uc-header">
        <span class="uc-title">Rawat Jalan</span>
        <span class="uc-badge" style="background:rgba(6,182,212,.15);color:#22d3ee;">RAJAL</span>
      </div>
      <div class="uc-main-value" style="color:#22d3ee;">{{ number_format($ringkasanRajal->sum('total_kunjungan')) }}</div>
      <div class="uc-main-label">Total kunjungan semua poli</div>
      <div class="uc-stats">
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#22c55e;">{{ number_format($ringkasanRajal->sum('pasien_baru')) }}</div>
          <div class="uc-stat-lbl">Pasien baru</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#60a5fa;">{{ number_format($ringkasanRajal->sum('pasien_lama')) }}</div>
          <div class="uc-stat-lbl">Pasien lama</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#34d399;">{{ number_format($ringkasanRajal->sum('bpjs')) }}</div>
          <div class="uc-stat-lbl">BPJS</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#fbbf24;">{{ number_format($ringkasanRajal->sum('umum')) }}</div>
          <div class="uc-stat-lbl">Umum</div>
        </div>
      </div>
    </div>

    {{-- IGD --}}
    <div class="pp-unit-card">
      <div class="uc-header">
        <span class="uc-title">IGD</span>
        <span class="uc-badge" style="background:rgba(239,68,68,.15);color:#f87171;">IGD</span>
      </div>
      <div class="uc-main-value" style="color:#f87171;">{{ number_format($ringkasanIGD['total']) }}</div>
      <div class="uc-main-label">Total kunjungan IGD</div>
      <div class="uc-stats">
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#22c55e;">{{ number_format($ringkasanIGD['pulang']) }}</div>
          <div class="uc-stat-lbl">Pulang</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#60a5fa;">{{ number_format($ringkasanIGD['rawat_inap']) }}</div>
          <div class="uc-stat-lbl">Rawat inap</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#ef4444;">{{ number_format($ringkasanIGD['meninggal']) }}</div>
          <div class="uc-stat-lbl">Meninggal</div>
        </div>
        <div class="uc-stat-item">
          <div class="uc-stat-val" style="color:#f59e0b;">{{ number_format($ringkasanIGD['avg_waktu_tunggu']) }} mt</div>
          <div class="uc-stat-lbl">Rata-rata tunggu</div>
        </div>
      </div>
    </div>

  </div>

{{-- SECTION: CHART TREND KUNJUNGAN + BOR --}}
<p class="pp-section-title">Grafik & Tren</p>

<div class="pp-chart-grid">

    {{-- Chart: Trend Harian --}}
    <div class="pp-chart-card">
        <div class="cc-header">
            <div>
                <div class="cc-title">Tren Kunjungan Harian</div>
                <div class="cc-subtitle">Ranap · Rajal · IGD</div>
            </div>
        </div>
        <div class="chart-canvas-wrap">
            <canvas id="chartTrendHarian"></canvas>
        </div>
    </div>

    {{-- Chart: BOR Bulanan --}}
    <div class="pp-chart-card">
        <div class="cc-header">
            <div>
                <div class="cc-title">BOR Bulanan {{ $tahun }}</div>
                <div class="cc-subtitle">Target 60–85%</div>
            </div>
        </div>
        <div class="chart-canvas-wrap">
            <canvas id="chartBOR"></canvas>
        </div>
    </div>

</div>

<div class="pp-chart-grid-3">

    {{-- Chart: Rajal per Poli (bar) --}}
    <div class="pp-chart-card">
        <div class="cc-header">
            <div>
                <div class="cc-title">Kunjungan per Poli</div>
                <div class="cc-subtitle">Top poli rawat jalan</div>
            </div>
        </div>
        <div class="chart-canvas-wrap-sm">
            <canvas id="chartRajal"></canvas>
        </div>
    </div>

    {{-- Chart: IGD per Triage (doughnut) --}}
    <div class="pp-chart-card">
        <div class="cc-header">
            <div>
                <div class="cc-title">IGD per Triage</div>
                <div class="cc-subtitle">Distribusi kategori</div>
            </div>
        </div>
        <div class="chart-canvas-wrap-sm">
            <canvas id="chartTriage"></canvas>
        </div>
    </div>

    {{-- Tabel Rajal per Poli --}}
    <div class="pp-chart-card">
        <div class="cc-header">
            <div>
                <div class="cc-title">Tabel Rawat Jalan</div>
                <div class="cc-subtitle">Semua poli</div>
            </div>
        </div>
        <div class="pp-table-wrap">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Poli</th>
                        <th>Kunjungan</th>
                        <th>Proporsi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalRajal = $ringkasanRajal->sum('total_kunjungan') ?: 1; @endphp
                    @foreach ($ringkasanRajal->take(8) as $poli)
                    @php $pct = round(($poli->total_kunjungan / $totalRajal) * 100, 1); @endphp
                    <tr>
                        <td>{{ $poli->nama_poli }}</td>
                        <td><strong style="font-family: var(--pp-mono);">{{ number_format($poli->total_kunjungan) }}</strong></td>
                        <td>
                            <div class="pp-poli-bar-wrap">
                                <div class="pp-poli-bar">
                                    <div class="pp-poli-bar-fill" style="width:{{ $pct }}%;"></div>
                                </div>
                                <span style="font-size:11px; color:var(--pp-text-muted); white-space:nowrap;">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

</div>{{-- .pp-wrap --}}
@endsection


@push('scripts')
<script>
// ======================================================
// DATA dari PHP 
// ======================================================
const trendData  = {!! $chartTrend  !!};
const borData    = {!! $chartBOR    !!};
const rajalData  = {!! $chartRajal  !!};
const triageData = {!! $chartTriage !!};

// Warna
const COLORS = {
  ranap  : '#2563eb',
  rajal  : '#06b6d4',
  igd    : '#ef4444',
  bor    : '#a78bfa',
  target : 'rgba(34,197,94,0.25)',
  triage : ['#ef4444','#f59e0b','#22c55e','#3b82f6','#a78bfa'],
};

// Chart defaults
Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#7d8590';

// ======================================================
// 1. CHART TREND HARIAN
// ======================================================
new Chart(document.getElementById('chartTrendHarian'), {
  type: 'line',
  data: {
    labels  : trendData.map(d => d.tanggal),
    datasets: [
      {
        label          : 'Ranap',
        data           : trendData.map(d => d.ranap),
        borderColor    : COLORS.ranap,
        backgroundColor: 'rgba(37,99,235,0.08)',
        borderWidth    : 2,
        fill           : true,
        tension        : 0.4,
        pointRadius    : 0,
        pointHoverRadius: 4,
      },
      {
        label          : 'Rajal',
        data           : trendData.map(d => d.rajal),
        borderColor    : COLORS.rajal,
        backgroundColor: 'rgba(6,182,212,0.08)',
        borderWidth    : 2,
        fill           : true,
        tension        : 0.4,
        pointRadius    : 0,
        pointHoverRadius: 4,
      },
      {
        label          : 'IGD',
        data           : trendData.map(d => d.igd),
        borderColor    : COLORS.igd,
        backgroundColor: 'rgba(239,68,68,0.08)',
        borderWidth    : 2,
        fill           : true,
        tension        : 0.4,
        pointRadius    : 0,
        pointHoverRadius: 4,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: true, position: 'top', labels: { boxWidth: 8, padding: 14 } },
      tooltip: { backgroundColor: '#1c2330', borderColor: '#30363d', borderWidth: 1 },
    },
    scales: {
      x: { grid: { color: 'rgba(48,54,61,0.6)' }, ticks: { maxTicksLimit: 10 } },
      y: { grid: { color: 'rgba(48,54,61,0.6)' }, beginAtZero: true },
    },
  },
});

// ======================================================
// 2. CHART BOR BULANAN
// ======================================================
new Chart(document.getElementById('chartBOR'), {
  type: 'bar',
  data: {
    labels  : borData.map(d => d.bulan),
    datasets: [
      {
        label          : 'BOR (%)',
        data           : borData.map(d => d.bor),
        backgroundColor: borData.map(d =>
          d.bor >= 60 && d.bor <= 85 ? 'rgba(167,139,250,0.8)' :
          d.bor < 60 ? 'rgba(245,158,11,0.7)' : 'rgba(239,68,68,0.7)'
        ),
        borderRadius   : 6,
        borderSkipped  : false,
      },
      {
        label       : 'Target Min (60%)',
        data        : borData.map(() => 60),
        type        : 'line',
        borderColor : 'rgba(34,197,94,0.5)',
        borderDash  : [4, 4],
        borderWidth : 1.5,
        pointRadius : 0,
        fill        : false,
      },
      {
        label       : 'Target Max (85%)',
        data        : borData.map(() => 85),
        type        : 'line',
        borderColor : 'rgba(239,68,68,0.5)',
        borderDash  : [4, 4],
        borderWidth : 1.5,
        pointRadius : 0,
        fill        : false,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend  : { display: false },
      tooltip : { backgroundColor: '#1c2330', borderColor: '#30363d', borderWidth: 1 },
    },
    scales: {
      x: { grid: { display: false } },
      y: {
        grid   : { color: 'rgba(48,54,61,0.6)' },
        min    : 0,
        max    : 100,
        ticks  : { callback: v => v + '%' },
      },
    },
  },
});

// ======================================================
// 3. CHART RAJAL PER POLI (horizontal bar)
// ======================================================
const rajalTop = rajalData.sort((a,b) => b.total_kunjungan - a.total_kunjungan).slice(0, 8);
new Chart(document.getElementById('chartRajal'), {
  type: 'bar',
  data: {
    labels  : rajalTop.map(d => d.nama_poli),
    datasets: [{
      label          : 'Kunjungan',
      data           : rajalTop.map(d => d.total_kunjungan),
      backgroundColor: 'rgba(6,182,212,0.75)',
      borderRadius   : 4,
    }],
  },
  options: {
    indexAxis  : 'y',
    responsive : true,
    maintainAspectRatio: false,
    plugins: {
      legend  : { display: false },
      tooltip : { backgroundColor: '#1c2330', borderColor: '#30363d', borderWidth: 1 },
    },
    scales: {
      x: { grid: { color: 'rgba(48,54,61,0.6)' } },
      y: { grid: { display: false }, ticks: { font: { size: 11 } } },
    },
  },
});

// ======================================================
// 4. CHART TRIAGE IGD (doughnut)
// ======================================================
new Chart(document.getElementById('chartTriage'), {
  type: 'doughnut',
  data: {
    labels  : triageData.map(d => d.kategori_triage || 'Tidak Diketahui'),
    datasets: [{
      data           : triageData.map(d => d.jumlah),
      backgroundColor: COLORS.triage,
      borderColor    : '#161b22',
      borderWidth    : 3,
      hoverOffset    : 6,
    }],
  },
  options: {
    responsive : true,
    maintainAspectRatio: false,
    cutout     : '65%',
    plugins: {
      legend  : { position: 'bottom', labels: { boxWidth: 10, padding: 12 } },
      tooltip : { backgroundColor: '#1c2330', borderColor: '#30363d', borderWidth: 1 },
    },
  },
});
</script>
@endpush
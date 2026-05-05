@extends('layouts.app')
@section('title', 'Portal Pelayanan Pasien')

@push('styles')
<style>
  :root {
    --pp-bg:          #0d1117;
    --pp-surface:     #161b22;
    --pp-surface2:    #1c2330;
    --pp-border:      rgba(48,54,61,0.8);
    --pp-text:        #e6edf3;
    --pp-text-muted:  #7d8590;
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

  * { box-sizing: border-box; }
  .pp-wrap { font-family: var(--pp-font); color: var(--pp-text); }

  /* ── Header ─────────────────────────────────────────────────── */
  .pp-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
  }
  .pp-header h1 { font-size: 20px; font-weight: 700; margin: 0; letter-spacing: -0.3px; color: #060e1e; }

  .pp-badge-live {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3);
    color: var(--pp-green); font-size: 11px; padding: 3px 10px;
    border-radius: 20px; font-weight: 600;
  }
  .pp-badge-live::before {
    content:''; width:6px; height:6px; background:var(--pp-green);
    border-radius:50%; animation: pulse-dot 1.5s ease-in-out infinite;
  }
  @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.3} }

  /* ── Filter Bar ─────────────────────────────────────────────── */
  .pp-filter-bar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: var(--pp-surface); border: 1px solid var(--pp-border);
    border-radius: 10px; padding: 10px 16px; margin-bottom: 20px;
  }
  .pp-filter-bar label { font-size: 12px; color: var(--pp-text-muted); font-weight: 500; }
  .pp-filter-bar input[type="date"],
  .pp-filter-bar select {
    background: var(--pp-surface2); border: 1px solid var(--pp-border);
    border-radius: 7px; color: var(--pp-text); font-size: 13px;
    padding: 6px 11px; outline: none; font-family: var(--pp-font);
  }
  .pp-filter-bar input[type="date"]:focus,
  .pp-filter-bar select:focus { border-color: var(--pp-accent); box-shadow: 0 0 0 3px var(--pp-accent-glow); }
  .pp-btn {
    background: var(--pp-accent); border: none; border-radius: 7px;
    color: #fff; font-size: 13px; font-weight: 600; padding: 7px 18px;
    cursor: pointer; font-family: var(--pp-font); transition: opacity .15s;
  }
  .pp-btn:hover { opacity:.85; }
  .pp-btn-ghost {
    background: transparent; border: 1px solid var(--pp-border);
    color: var(--pp-text-muted); border-radius: 7px; font-size: 13px;
    padding: 7px 14px; cursor: pointer; font-family: var(--pp-font); transition: all .15s;
    text-decoration: none; display: inline-flex; align-items: center;
  }
  .pp-btn-ghost:hover { background: var(--pp-surface2); color: var(--pp-text); }

  /* ── Top 4 Cards ─────────────────────────────────────────────── */
  .pp-top-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
  }
  @media(max-width:1100px){ .pp-top-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:600px) { .pp-top-grid{ grid-template-columns:1fr; } }

  .pp-top-card {
    background: var(--pp-surface);
    border: 1px solid var(--pp-border);
    border-radius: 14px;
    padding: 20px 20px 16px;
    position: relative;
    overflow: hidden;
    transition: border-color .2s, transform .2s;
  }
  .pp-top-card:hover { border-color: rgba(37,99,235,0.4); transform: translateY(-1px); }

  .pp-top-card .tc-header {
    display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;
  }
  .pp-top-card .tc-label {
    font-size: 12px; font-weight: 600; color: var(--pp-text-muted);
    letter-spacing: 0.3px;
  }
  .pp-top-card .tc-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .pp-top-card .tc-value {
    font-size: 38px; font-weight: 700; letter-spacing: -1.5px;
    line-height: 1; font-family: var(--pp-mono); margin-bottom: 4px;
  }
  .pp-top-card .tc-unit {
    font-size: 16px; font-weight: 500; margin-left: 3px; opacity: .7;
  }
  .pp-top-card .tc-standar {
    font-size: 11px; color: var(--pp-text-muted); margin-bottom: 10px;
  }
  .pp-top-card .tc-progress {
    height: 4px; background: rgba(255,255,255,.07); border-radius: 4px; overflow: hidden; margin-bottom: 8px;
  }
  .pp-top-card .tc-progress-fill { height: 100%; border-radius: 4px; transition: width 1.2s ease; }
  .pp-top-card .tc-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px;
  }
  .badge-ideal { background: rgba(34,197,94,0.12);  color: #22c55e; }
  .badge-warn  { background: rgba(245,158,11,0.12); color: #f59e0b; }
  .badge-over  { background: rgba(239,68,68,0.12);  color: #ef4444; }

  /* card kunjungan hari ini */
  .pp-top-card .tc-delta {
    font-size: 12px; font-weight: 500; margin-top: 6px;
    display: flex; align-items: center; gap: 4px;
  }
  .delta-up   { color: #22c55e; }
  .delta-down { color: #ef4444; }

  /* ── Sumber badge ──────────────────────────────────────────── */
  .src-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 600; letter-spacing: 0.4px;
    padding: 2px 7px; border-radius: 20px; text-transform: uppercase;
  }
  .src-api   { background:rgba(34,197,94,0.12); color:#22c55e; border:1px solid rgba(34,197,94,0.2); }
  .src-dummy { background:rgba(245,158,11,0.12);color:#f59e0b; border:1px solid rgba(245,158,11,0.2); }

  /* ── Chart Row 1: Tren + BOR ─────────────────────────────────── */
  .pp-chart-row {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 14px;
    margin-bottom: 20px;
  }
  @media(max-width:900px){ .pp-chart-row{ grid-template-columns:1fr; } }

  /* ── Chart Row 2: Poli + Triage + Tabel ─────────────────────── */
  .pp-chart-row3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
  }
  @media(max-width:1100px){ .pp-chart-row3{ grid-template-columns:1fr 1fr; } }
  @media(max-width:700px) { .pp-chart-row3{ grid-template-columns:1fr; } }

  /* ── Chart Row 3: AVLOS + TOI (dari API) ──────────────────────── */
  .pp-chart-row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
  }
  @media(max-width:900px){ .pp-chart-row2{ grid-template-columns:1fr; } }

  /* ── Card Chart ─────────────────────────────────────────────── */
  .pp-card {
    background: var(--pp-surface); border: 1px solid var(--pp-border);
    border-radius: 14px; padding: 18px 20px;
  }
  .pp-card-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 14px;
  }
  .pp-card-title   { font-size: 14px; font-weight: 600; color: var(--pp-text); }
  .pp-card-subtitle{ font-size: 11px; color: var(--pp-text-muted); margin-top: 2px; }

  .chart-wrap    { position: relative; height: 240px; width: 100%; }
  .chart-wrap-sm { position: relative; height: 210px; width: 100%; }
  canvas { max-height: 100% !important; }

  /* ── Tabel Rawat Jalan ─────────────────────────────────────── */
  .pp-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
  .pp-tbl th {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--pp-text-muted);
    padding: 6px 10px; text-align: left; border-bottom: 1px solid var(--pp-border);
  }
  .pp-tbl td { padding: 8px 10px; border-bottom: 1px solid rgba(48,54,61,0.4); color: var(--pp-text); vertical-align: middle; }
  .pp-tbl tr:last-child td { border-bottom: none; }
  .pp-tbl tr:hover td { background: var(--pp-surface2); }
  .pp-bar-wrap { display: flex; align-items: center; gap: 6px; }
  .pp-bar { flex:1; height:5px; background:rgba(255,255,255,.07); border-radius:4px; overflow:hidden; max-width:80px; }
  .pp-bar-fill { height:100%; background:var(--pp-accent); border-radius:4px; transition:width 1s ease; }

  /* ── Banner dummy ───────────────────────────────────────────── */
  .pp-banner-dummy {
    background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.3);
    border-radius:8px; padding:9px 14px; margin-bottom:16px;
    font-size:12px; color:#f59e0b; display:flex; align-items:center; gap:8px;
  }
</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush


@section('content')
<div class="pp-wrap">

  @if($isDummy)
  <div class="pp-banner-dummy">
    ⚠ <strong>Sebagian data masih dummy</strong> — BOR/AVLOS/TOI sudah dari API. Rajal, IGD, Ranap menunggu endpoint.
  </div>
  @endif

  {{-- ── Header ── --}}
<div class="pp-header">
  <div style="display:flex;align-items:center;gap:12px;">

    {{-- Tombol Home --}}
    <a href="{{ route('dashboard') }}" class="pp-btn-ghost" style="padding:6px 10px;" title="Kembali ke Home">
      <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </a>

    <h1>Portal Pelayanan Pasien</h1>
    @if(!$isDummy)
      <span class="pp-badge-live">Live Data</span>
    @endif
  </div>
</div>

  {{-- ── Filter ── --}}
  <form method="GET" action="{{ route('portal.pelayananpasien') }}" class="pp-filter-bar">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--pp-text-muted);flex-shrink:0">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
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
    <a href="{{ route('portal.pelayananpasien') }}" class="pp-btn-ghost">Reset</a>
  </form>

  {{-- ══════════════════════════════════════════════════════════
       ROW 1: BOR · LOS · TOI · Kunjungan Hari Ini
  ══════════════════════════════════════════════════════════ --}}
  @php
    $topCards = [
      [
        'label'   => 'BOR (Bed Occupancy Rate)',
        'nilai'   => $bor,
        'unit'    => '%',
        'standar' => 'Standar 60 – 85%',
        'color'   => '#2563eb',
        'icon_bg' => 'rgba(37,99,235,0.15)',
        'pct'     => min($bor, 100),
        'status'  => ($bor >= 60 && $bor <= 85) ? 'ideal' : ($bor < 60 ? 'warn' : 'over'),
        'label_s' => ($bor >= 60 && $bor <= 85) ? '✓ Ideal' : ($bor < 60 ? '↓ Rendah' : '↑ Tinggi'),
        'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
      ],
      [
        'label'   => 'LOS (Length of Stay)',
        'nilai'   => $los,
        'unit'    => 'hari',
        'standar' => 'Standar 6 – 9 hari',
        'color'   => '#a78bfa',
        'icon_bg' => 'rgba(167,139,250,0.15)',
        'pct'     => min(round(($los / 15) * 100), 100),
        'status'  => ($los >= 6 && $los <= 9) ? 'ideal' : 'warn',
        'label_s' => ($los >= 6 && $los <= 9) ? '✓ Ideal' : '⚠ Periksa',
        'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      ],
      [
        'label'   => 'TOI (Turn Over Interval)',
        'nilai'   => $toi,
        'unit'    => 'hari',
        'standar' => 'Standar 1 – 3 hari',
        'color'   => '#ef4444',
        'icon_bg' => 'rgba(239,68,68,0.15)',
        'pct'     => min(round(($toi / 6) * 100), 100),
        'status'  => ($toi >= 1 && $toi <= 3) ? 'ideal' : 'warn',
        'label_s' => ($toi >= 1 && $toi <= 3) ? '✓ Ideal' : '⚠ Periksa',
        'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
      ],
      [
        'label'   => 'Kunjungan Hari Ini',
        'nilai'   => $ringkasanRajal->sum('total_kunjungan'),
        'unit'    => '',
        'standar' => 'Total semua poli rawat jalan',
        'color'   => '#06b6d4',
        'icon_bg' => 'rgba(6,182,212,0.15)',
        'pct'     => 0,
        'status'  => 'ideal',
        'label_s' => '',
        'is_kunjungan' => true,
        'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
      ],
    ];
  @endphp

  <div class="pp-top-grid">
    @foreach($topCards as $tc)
    <div class="pp-top-card">
      <div class="tc-header">
        <div class="tc-label">{{ $tc['label'] }}</div>
        <div class="tc-icon" style="background:{{ $tc['icon_bg'] }};color:{{ $tc['color'] }}">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            {!! $tc['icon'] !!}
          </svg>
        </div>
      </div>

      <div class="tc-value" style="color:{{ $tc['color'] }}">
        {{ is_float($tc['nilai']) ? number_format($tc['nilai'], 1) : number_format($tc['nilai']) }}
        @if($tc['unit'])<span class="tc-unit">{{ $tc['unit'] }}</span>@endif
      </div>

      <div class="tc-standar">{{ $tc['standar'] }}</div>

      @if(!isset($tc['is_kunjungan']))
        <div class="tc-progress">
          <div class="tc-progress-fill" style="width:{{ $tc['pct'] }}%;background:{{ $tc['color'] }};"></div>
        </div>
        <span class="tc-badge badge-{{ $tc['status'] }}">{{ $tc['label_s'] }}</span>
      @else
        <div class="tc-delta delta-up">
          ▲ dari kemarin
        </div>
      @endif
    </div>
    @endforeach
  </div>
   {{-- ══════════════════════════════════════════════════════════
       ROW 2: AVLOS + TOI Bulanan (data real dari Google Sheet API)
  ══════════════════════════════════════════════════════════ --}}
  {{-- ══════════════════════════════════════════════════════════
     ROW 2: Barber-Johnson (data real dari API)
══════════════════════════════════════════════════════════ --}}
<div class="pp-card" style="margin-bottom: 20px;">
    <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Grafik {{ $tahun }}</div>
          <div class="pp-card-subtitle">Standar Depkes RI · BOR, AVLOS, TOI</div>
        </div>
      <span class="src-badge src-api">✓ API</span>
    </div>
  <div class="chart-wrap" style="height: 420px;">
    <canvas id="chartBJ"></canvas>
  </div>
  <div style="margin-top:12px; padding:9px 14px; background:rgba(37,99,235,0.06); border:1px solid rgba(37,99,235,0.2); border-radius:8px; font-size:11px; color:var(--pp-text-muted); display:flex; gap:16px; flex-wrap:wrap;">
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;"></span> Zona efisien (AVLOS 6–9 hr, TOI 1–3 hr)</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#2563eb;display:inline-block;"></span> Di luar standar</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:2px;background:rgba(34,197,94,0.6);display:inline-block;border-top:2px dashed rgba(34,197,94,0.6);"></span> BOR 60%</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:2px;display:inline-block;border-top:2px dashed rgba(37,99,235,0.6);"></span> BOR 75%</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:2px;display:inline-block;border-top:2px dashed rgba(239,68,68,0.6);"></span> BOR 85%</span>
  </div>
</div>


  {{-- ══════════════════════════════════════════════════════════
       ROW 3: Tren Kunjungan Harian (kiri) + BOR Bulanan (kanan)
  ══════════════════════════════════════════════════════════ --}}
  <div class="pp-chart-row">

    {{-- Tren Harian --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Tren Kunjungan Harian</div>
          <div class="pp-card-subtitle">
            Periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }}
            — {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}
          </div>
        </div>
        <span class="src-badge src-dummy">⚠ Dummy</span>
      </div>
      <div class="chart-wrap">
        <canvas id="chartTrendHarian"></canvas>
      </div>
    </div>

    {{-- BOR Bulanan --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">BOR Bulanan {{ $tahun }}</div>
          <div class="pp-card-subtitle">Target 60 – 85%</div>
        </div>
        <span class="src-badge {{ $isDummy ? 'src-dummy' : 'src-api' }}">
          {{ $isDummy ? '⚠ Dummy' : '✓ API' }}
        </span>
      </div>
      <div class="chart-wrap">
        <canvas id="chartBOR"></canvas>
      </div>
    </div>

  </div>

  {{-- ══════════════════════════════════════════════════════════
       ROW 4: Kunjungan per Poli + IGD Triage + Tabel Rawat Jalan
  ══════════════════════════════════════════════════════════ --}}
  <div class="pp-chart-row3">

    {{-- Kunjungan per Poli --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Kunjungan per Poli</div>
          <div class="pp-card-subtitle">Top poli rawat jalan</div>
        </div>
        <span class="src-badge src-dummy">⚠ Dummy</span>
      </div>
      <div class="chart-wrap-sm">
        <canvas id="chartRajal"></canvas>
      </div>
    </div>

    {{-- IGD per Triage --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">IGD per Triage</div>
          <div class="pp-card-subtitle">Distribusi kategori</div>
        </div>
        <span class="src-badge src-dummy">⚠ Dummy</span>
      </div>
      <div class="chart-wrap-sm" style="position:relative;">
        <canvas id="chartTriage"></canvas>
        {{-- Total di tengah doughnut --}}
        <div id="triage-center" style="
          position:absolute; top:50%; left:50%; transform:translate(-50%,-55%);
          text-align:center; pointer-events:none;
        ">
          <div style="font-size:22px;font-weight:700;font-family:var(--pp-mono);color:var(--pp-text);" id="triage-total">–</div>
          <div style="font-size:10px;color:var(--pp-text-muted);font-weight:600;">Pasien</div>
        </div>
      </div>
    </div>

    {{-- Tabel Rawat Jalan --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Tabel Rawat Jalan</div>
          <div class="pp-card-subtitle">Semua poli</div>
        </div>
        <span class="src-badge src-dummy">⚠ Dummy</span>
      </div>
      <div style="overflow-x:auto;overflow-y:auto;max-height:230px;">
        <table class="pp-tbl">
          <thead>
            <tr>
              <th>Poli</th>
              <th style="text-align:right;">Kunjungan</th>
              <th>Proporsi</th>
            </tr>
          </thead>
          <tbody>
            @php $totalRajal = $ringkasanRajal->sum('total_kunjungan') ?: 1; @endphp
            @foreach($ringkasanRajal as $poli)
              @php $pct = round(($poli->total_kunjungan / $totalRajal) * 100, 1); @endphp
              <tr>
                <td>{{ $poli->nama_poli }}</td>
                <td style="text-align:right;font-family:var(--pp-mono);font-weight:600;">
                  {{ number_format($poli->total_kunjungan) }}
                </td>
                <td>
                  <div class="pp-bar-wrap">
                    <div class="pp-bar">
                      <div class="pp-bar-fill" style="width:{{ $pct }}%;"></div>
                    </div>
                    <span style="font-size:10px;color:var(--pp-text-muted);white-space:nowrap;">{{ $pct }}%</span>
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
// ── Data dari PHP ────────────────────────────────────────────────────
const trendData  = {!! $trendHarian->toJson()    !!};
const borData    = {!! $chartBOR->toJson()        !!};
const avlosData  = {!! $chartAvlos->toJson()      !!};
const rajalData  = {!! $ringkasanRajal->toJson()  !!};
const triageData = {!! $triageIGD->toJson()       !!};

Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#7d8590';

const TOOLTIP_STYLE = { backgroundColor:'#1c2330', borderColor:'rgba(48,54,61,0.8)', borderWidth:1, padding:10, titleColor:'#e6edf3', bodyColor:'#7d8590' };

// ── 1. Tren Kunjungan Harian ─────────────────────────────────────────
new Chart(document.getElementById('chartTrendHarian'), {
  type: 'line',
  data: {
    labels: trendData.map(d => d.tanggal),
    datasets: [
      { label:'Ranap', data:trendData.map(d=>d.ranap), borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.08)',  borderWidth:2, fill:true, tension:0.4, pointRadius:0, pointHoverRadius:4 },
      { label:'Rajal', data:trendData.map(d=>d.rajal), borderColor:'#06b6d4', backgroundColor:'rgba(6,182,212,0.08)',  borderWidth:2, fill:true, tension:0.4, pointRadius:0, pointHoverRadius:4 },
      { label:'IGD',   data:trendData.map(d=>d.igd),   borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.06)',  borderWidth:2, fill:true, tension:0.4, pointRadius:0, pointHoverRadius:4 },
    ],
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    interaction:{ mode:'index', intersect:false },
    plugins:{ legend:{ display:true, position:'top', labels:{ boxWidth:8, padding:16, usePointStyle:true } }, tooltip: TOOLTIP_STYLE },
    scales:{
      x:{ grid:{ color:'rgba(48,54,61,0.5)' }, ticks:{ maxTicksLimit:8 } },
      y:{ grid:{ color:'rgba(48,54,61,0.5)' }, beginAtZero:true },
    },
  },
});

// ── 2. BOR Bulanan ───────────────────────────────────────────────────
new Chart(document.getElementById('chartBOR'), {
  type: 'bar',
  data: {
    labels: borData.map(d => d.bulan),
    datasets: [
      {
        label: 'BOR (%)',
        data : borData.map(d => d.bor),
        backgroundColor: borData.map(d =>
          d.bor === 0              ? 'rgba(48,54,61,0.35)' :
          d.bor >= 60 && d.bor <= 85 ? 'rgba(167,139,250,0.85)' :
          d.bor < 60               ? 'rgba(245,158,11,0.75)' : 'rgba(239,68,68,0.75)'
        ),
        borderRadius:5, borderSkipped:false,
      },
      { label:'Min 60%', data:borData.map(()=>60), type:'line', borderColor:'rgba(34,197,94,0.45)', borderDash:[5,4], borderWidth:1.5, pointRadius:0, fill:false },
      { label:'Max 85%', data:borData.map(()=>85), type:'line', borderColor:'rgba(239,68,68,0.45)', borderDash:[5,4], borderWidth:1.5, pointRadius:0, fill:false },
    ],
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{
      legend:{ display:false },
      tooltip:{ ...TOOLTIP_STYLE, callbacks:{ label: ctx => ctx.raw === 0 ? 'Belum ada data' : `BOR: ${ctx.raw}%` } },
    },
    scales:{
      x:{ grid:{ display:false } },
      y:{ grid:{ color:'rgba(48,54,61,0.5)' }, min:0, max:100, ticks:{ callback: v => v+'%' } },
    },
  },
});

// ── 3. Kunjungan per Poli ─────────────────────────────────────────────
const rajalTop = [...rajalData].sort((a,b)=>b.total_kunjungan-a.total_kunjungan).slice(0,8);
new Chart(document.getElementById('chartRajal'), {
  type: 'bar',
  data:{
    labels: rajalTop.map(d=>d.nama_poli),
    datasets:[{ label:'Kunjungan', data:rajalTop.map(d=>d.total_kunjungan), backgroundColor:'rgba(6,182,212,0.75)', borderRadius:4 }],
  },
  options:{
    indexAxis:'y', responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{ display:false }, tooltip: TOOLTIP_STYLE },
    scales:{
      x:{ grid:{ color:'rgba(48,54,61,0.5)' }, ticks:{ maxTicksLimit:5 } },
      y:{ grid:{ display:false }, ticks:{ font:{ size:11 } } },
    },
  },
});

// ── 4. IGD Triage (doughnut + total di tengah) ───────────────────────
const triageTotal = triageData.reduce((s,d) => s + d.jumlah, 0);
document.getElementById('triage-total').textContent = triageTotal.toLocaleString('id-ID');

new Chart(document.getElementById('chartTriage'), {
  type: 'doughnut',
  data:{
    labels: triageData.map(d=>d.kategori_triage||'Tidak Diketahui'),
    datasets:[{
      data: triageData.map(d=>d.jumlah),
      backgroundColor:['#ef4444','#f59e0b','#22c55e','#3b82f6','#a78bfa'],
      borderColor:'#161b22', borderWidth:3, hoverOffset:6,
    }],
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'62%',
    plugins:{
      legend:{ position:'right', labels:{ boxWidth:10, padding:10, font:{ size:11 },
        generateLabels(chart) {
          const data = chart.data;
          return data.labels.map((label, i) => {
            const val = data.datasets[0].data[i];
            const pct = triageTotal > 0 ? ((val / triageTotal) * 100).toFixed(1) : 0;
            return {
              text: `${label}  ${val} (${pct}%)`,
              fillStyle: data.datasets[0].backgroundColor[i],
              strokeStyle: '#161b22',
              lineWidth: 2,
              index: i,
            };
          });
        }
      }},
      tooltip: TOOLTIP_STYLE,
    },
  },
});

// ── 5. Barber-Johnson ────────────────────────────────────────────────
(function () {
  const bjData = avlosData; // dari PHP: {bulan, avlos, toi, bor}

  const bjZonePlugin = {
    id: 'bjZone',
    beforeDatasetsDraw(chart) {
      const { ctx, scales: { x, y } } = chart;
      const x1 = x.getPixelForValue(6),  x2 = x.getPixelForValue(9);
      const y1 = y.getPixelForValue(3),  y2 = y.getPixelForValue(1);
      ctx.save();
      ctx.fillStyle   = 'rgba(34,197,94,0.08)';
      ctx.strokeStyle = 'rgba(34,197,94,0.5)';
      ctx.lineWidth   = 1.5;
      ctx.setLineDash([5, 4]);
      ctx.beginPath();
      ctx.rect(x1, y1, x2 - x1, y2 - y1);
      ctx.fill();
      ctx.stroke();
      ctx.setLineDash([]);
      ctx.font      = '10px DM Sans, system-ui';
      ctx.fillStyle = 'rgba(34,197,94,0.85)';
      ctx.fillText('Zona Efisien', x1 + 6, y1 + 14);
      ctx.restore();
    }
  };

  const borCurvesPlugin = {
    id: 'borCurves',
    afterDatasetsDraw(chart) {
      const { ctx, scales: { x, y } } = chart;
      [
        { bor: 60, color: 'rgba(34,197,94,0.55)',  label: 'BOR 60%' },
        { bor: 75, color: 'rgba(37,99,235,0.55)',  label: 'BOR 75%' },
        { bor: 85, color: 'rgba(239,68,68,0.55)',  label: 'BOR 85%' },
      ].forEach(({ bor, color, label }) => {
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth   = 1.5;
        ctx.setLineDash([5, 4]);
        ctx.beginPath();
        let first = true;
        for (let i = 0; i <= 120; i++) {
          const los = 1 + (i / 120) * 13;
          const toi = los * (100 - bor) / bor;
          if (toi < 0.05 || toi > 9) { first = true; continue; }
          const px = x.getPixelForValue(los);
          const py = y.getPixelForValue(toi);
          if (first) { ctx.moveTo(px, py); first = false; }
          else ctx.lineTo(px, py);
        }
        ctx.stroke();

        // label di ujung kurva
        const losLbl = bor <= 65 ? 11.5 : bor <= 80 ? 10 : 8.5;
        const toiLbl = losLbl * (100 - bor) / bor;
        if (toiLbl > 0.1 && toiLbl < 8.5) {
          ctx.setLineDash([]);
          ctx.font      = '10px DM Sans, system-ui';
          ctx.fillStyle = color.replace('0.55', '1');
          ctx.fillText(label, x.getPixelForValue(losLbl) + 4, y.getPixelForValue(toiLbl) - 4);
        }
        ctx.restore();
      });
    }
  };

  const pointLabelPlugin = {
  id: 'pointLabels',
  afterDraw(chart) {
    const { ctx } = chart;
    ctx.save();
    ctx.globalAlpha = 1;
    ctx.globalCompositeOperation = 'source-over';

    chart.data.datasets.forEach((ds, di) => {
      const meta = chart.getDatasetMeta(di);
      ds.data.forEach((pt, pi) => {
        if (!pt.x || pt.x === 0) return;
        const el   = meta.data[pi];
        const text = pt.label;

        ctx.font = '700 11px Arial, sans-serif';
        const tw = ctx.measureText(text).width;
        const bx = el.x - tw / 2 - 6;
        const by = el.y - 32;
        const bw = tw + 12;
        const bh = 18;
        const r  = 4;

        // background pill
        ctx.beginPath();
        ctx.moveTo(bx + r, by);
        ctx.lineTo(bx + bw - r, by);
        ctx.quadraticCurveTo(bx + bw, by, bx + bw, by + r);
        ctx.lineTo(bx + bw, by + bh - r);
        ctx.quadraticCurveTo(bx + bw, by + bh, bx + bw - r, by + bh);
        ctx.lineTo(bx + r, by + bh);
        ctx.quadraticCurveTo(bx, by + bh, bx, by + bh - r);
        ctx.lineTo(bx, by + r);
        ctx.quadraticCurveTo(bx, by, bx + r, by);
        ctx.closePath();
        ctx.fillStyle = pt.efisien ? '#16a34a' : '#1d4ed8';
        ctx.fill();

        // border tipis
        ctx.strokeStyle = pt.efisien ? '#bbf7d0' : '#bfdbfe';
        ctx.lineWidth   = 1;
        ctx.stroke();

        // teks
        ctx.fillStyle    = '#ffffff';
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, el.x, by + bh / 2);
      });
    });

    ctx.restore();
  }
};

  const pts = bjData
    .filter(d => d.avlos > 0 || d.toi > 0)
    .map(d => ({
      x:       d.avlos,
      y:       d.toi,
      label:   d.bulan,
      bor:     d.bor,
      efisien: d.avlos >= 6 && d.avlos <= 9 && d.toi >= 1 && d.toi <= 3 && d.bor >= 60 && d.bor <= 85,
    }));

  new Chart(document.getElementById('chartBJ'), {
    type: 'scatter',
    data: {
      datasets: [{
        label:           'Bulanan',
        data:            pts,
        backgroundColor: pts.map(p => p.efisien ? 'rgba(34,197,94,0.85)' : 'rgba(37,99,235,0.85)'),
        borderColor:     pts.map(p => p.efisien ? '#16a34a' : '#1d4ed8'),
        borderWidth:     2,
        pointRadius:     9,
        pointHoverRadius: 12,
      }],
    },
    options: {
      responsive:           true,
      maintainAspectRatio:  false,
      animation:            { duration: 700 },
      plugins: {
        legend: { display: false },
        tooltip: {
          ...TOOLTIP_STYLE,
          callbacks: {
            title: ctx  => ctx[0].raw.label,
            label: ctx  => [
              `AVLOS : ${ctx.raw.x} hari`,
              `TOI   : ${ctx.raw.y} hari`,
              `BOR   : ${ctx.raw.bor}%`,
              ctx.raw.efisien ? '✓ Dalam zona efisien' : '⚠ Di luar standar',
            ],
          },
        },
      },
      scales: {
        x: {
          title: { display: true, text: 'AVLOS — Rata-rata lama dirawat (hari)', font: { size: 12 }, color: '#7d8590' },
          min: 0, max: 15,
          grid:  { color: 'rgba(48,54,61,0.5)' },
          ticks: { color: '#7d8590', callback: v => v + ' hr' },
        },
        y: {
          title: { display: true, text: 'TOI — Interval antar pasien (hari)', font: { size: 12 }, color: '#7d8590' },
          min: 0, max: 9,
          grid:  { color: 'rgba(48,54,61,0.5)' },
          ticks: { color: '#7d8590', callback: v => v + ' hr' },
        },
      },
    },
    plugins: [bjZonePlugin, borCurvesPlugin, pointLabelPlugin],
  });
})();
</script>
@endpush
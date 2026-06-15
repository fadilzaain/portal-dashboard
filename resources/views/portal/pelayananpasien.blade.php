@extends('layouts.app')
@section('title', 'Portal Pelayanan Pasien')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/portal/pelayananpasien.css'])
@vite(['resources/css/portal/bor-modal.css'])
@endpush

@section('content')
<div class="pp-wrap">

  {{-- ═══════════════════════════════════════════
        NAVBAR 
  ════════════════════════════════════════════ --}}
  <nav class="pp-navbar">
    <div class="pp-navbar-inner">

      {{-- Kiri: back + breadcrumb --}}
      <div class="pp-navbar-left">
        <a href="{{ route('dashboard') }}" class="pp-nav-back" title="Kembali ke Dashboard">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </a>
        <div class="pp-breadcrumb">
          <a href="{{ route('dashboard') }}" class="pp-breadcrumb-link">Dashboard</a>
          <span class="pp-breadcrumb-sep">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
            </svg>
          </span>
          <span class="pp-breadcrumb-current">Portal Pelayanan Pasien</span>
        </div>
      </div>

      {{-- Kanan: filter pill + live badge --}}
      <div class="pp-navbar-right">
        <form method="GET" action="{{ route('portal.pelayananpasien') }}" id="filterForm">
          @php $namaBulanNav = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
          <div class="pp-filter-pill">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" class="pp-filter-icon">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <select name="bulan" onchange="document.getElementById('filterForm').submit()" class="pp-nav-select">
              @foreach($namaBulanNav as $i => $nb)
                <option value="{{ $i + 1 }}" @selected(($i + 1) == $bulan)>{{ $nb }}</option>
              @endforeach
            </select>
            <span class="pp-filter-sep"></span>
            <select name="tahun" onchange="document.getElementById('filterForm').submit()" class="pp-nav-select">
              @for($y = now()->year; $y >= now()->year - 4; $y--)
                <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
              @endfor
            </select>
          </div>
        </form>
        <span class="pp-badge-live">Live</span>
      </div>

    </div>
  </nav>

  {{-- ═══════════════════════════════════════════
       TOP 4 KPI CARDS
  ════════════════════════════════════════════ --}}
  @php
    $topCards = [
      [
        'label'    => 'BOR (Bed Occupancy Rate)',
        'nilai'    => $bor,
        'unit'     => '%',
        'standar'  => 'Standar 60 – 85%',
        'color'    => '#2563eb',
        'icon_bg'  => 'rgba(37,99,235,0.15)',
        'pct'      => min($bor, 100),
        'status'   => ($bor >= 60 && $bor <= 85) ? 'ideal' : ($bor < 60 ? 'warn' : 'over'),
        'label_s'  => ($bor >= 60 && $bor <= 85) ? '✓ Ideal' : ($bor < 60 ? '↓ Rendah' : '↑ Tinggi'),
        'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
      ],
      [
        'label'    => 'LOS (Length of Stay)',
        'nilai'    => $los,
        'unit'     => 'hari',
        'standar'  => 'Standar 3 – 12 hari',
        'color'    => '#a78bfa',
        'icon_bg'  => 'rgba(167,139,250,0.15)',
        'pct'      => min(round(($los / 15) * 100), 100),
        'status'   => ($los >= 3 && $los <= 12) ? 'ideal' : 'warn',
        'label_s'  => ($los >= 3 && $los <= 12) ? '✓ Ideal' : '⚠ Periksa',
        'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      ],
      [
        'label'    => 'TOI (Turn Over Interval)',
        'nilai'    => $toi,
        'unit'     => 'hari',
        'standar'  => 'Standar 1 – 3 hari',
        'color'    => '#ef4444',
        'icon_bg'  => 'rgba(239,68,68,0.15)',
        'pct'      => min(round(($toi / 6) * 100), 100),
        'status'   => ($toi >= 1 && $toi <= 3) ? 'ideal' : 'warn',
        'label_s'  => ($toi >= 1 && $toi <= 3) ? '✓ Ideal' : '⚠ Periksa',
        'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
      ],
      [
        'label'   => 'BTO (Bed Turn Over)',
        'nilai'   => $bto,
        'unit'    => 'kali',
        'standar' => 'Standar 40 – 50 kali/tahun',
        'color'   => '#06b6d4',
        'icon_bg' => 'rgba(6,182,212,0.15)',
        'pct'     => min(round(($bto / 60) * 100), 100),
        'status'  => ($bto >= 40 && $bto <= 50) ? 'ideal' : 'warn',
        'label_s' => ($bto >= 40 && $bto <= 50) ? '✓ Ideal' : '⚠ Periksa',
        'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
      ],
    ];
  @endphp

  <div class="pp-top-grid">
    @foreach($topCards as $idx => $tc)
    <div class="pp-top-card" @if($idx === 0) id="borKpiCard" style="cursor:pointer" title="Klik untuk detail BOR per ruangan" @endif>
      <div class="tc-header">
        <div class="tc-label">{{ $tc['label'] }}</div>
        <div class="tc-icon" style="background:{{ $tc['icon_bg'] }};color:{{ $tc['color'] }}">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $tc['icon'] !!}</svg>
        </div>
      </div>

      @if($tc['nilai'] > 0)
        <div class="tc-value" style="color:{{ $tc['color'] }}">
          {{ is_float($tc['nilai']) ? number_format($tc['nilai'], 2) : number_format($tc['nilai']) }}
          @if($tc['unit'])<span class="tc-unit">{{ $tc['unit'] }}</span>@endif
        </div>
        <div class="tc-standar">{{ $tc['standar'] }}</div>
        <div class="tc-progress">
          <div class="tc-progress-fill" style="width:{{ $tc['pct'] }}%;background:{{ $tc['color'] }}"></div>
        </div>
        <span class="tc-badge badge-{{ $tc['status'] }}">{{ $tc['label_s'] }}</span>
      @else
        <div class="tc-value" style="color:var(--pp-muted);font-size:24px">—</div>
        <div class="tc-standar">{{ $tc['standar'] }}</div>
        <span class="tc-badge" style="background:rgba(125,133,144,0.12);color:var(--pp-muted)">Belum ada data</span>
      @endif
    </div>
    @endforeach
  </div>

  {{-- ═══════════════════════════════════════════
       GRAFIK BARBER-JOHNSON
  ════════════════════════════════════════════ --}}
  <div class="pp-card" style="margin-bottom:20px">
    <div class="pp-card-header">
      <div>
        <div class="pp-card-title">Grafik Barber-Johnson {{ $tahun }}</div>
        <div class="pp-card-subtitle">Standar Depkes RI</div>
      </div>
      <span class="src-badge src-api">✓ API</span>
    </div>

    <div class="bj-filter">
      <label>Pilih Bulan:</label>
      <select id="bjBulanSelect">
        @php $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
        @foreach($namaBulan as $i => $nb)
          <option value="{{ $i }}">{{ $nb }}</option>
        @endforeach
      </select>
      <span id="bjStatusBadge"></span> 
      <button id="bjDownloadBtn" class="pp-btn-ghost" style="margin-left:auto;display:flex;align-items:center;gap:6px;font-size:12px;padding:5px 12px">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
      </button>
    </div>

    <div class="bj-kpi-row">
      @foreach([['BOR','%'],['BTO',''],['AVLOS','hr'],['TOI','hr']] as [$lbl,$unit])
      <div class="bj-kpi-card">
        <div class="bj-kpi-label">{{ $lbl }}</div>
        <div class="bj-kpi-val" id="bjKpi{{ $lbl }}">—<span class="bj-kpi-unit"> {{ $unit }}</span></div>
        <div class="bj-kpi-coord" id="bjCoord{{ $lbl }}">—</div>
      </div>
      @endforeach
    </div>

    <div style="position:relative;height:460px;width:100%">
      <canvas id="chartBJ" role="img" aria-label="Grafik Barber-Johnson bulanan">Grafik Barber-Johnson.</canvas>
    </div>

    <div class="bj-legend" id="bjLegendBar" style="margin-top:12px;padding:9px 14px;background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.2);border-radius:8px"></div>
  </div>

  {{-- ═══════════════════════════════════════════
     ROW: Tren Kunjungan + BOR Bulanan
════════════════════════════════════════════ --}}
@php
  // Hitung total & rata-rata dari data tren untuk summary cards
  $totalKunjungan  = $trendKunjungan->sum('jml_kunjungan');
  $rataKunjungan   = $trendKunjungan->where('jml_kunjungan', '>', 0)->avg('jml_rata_rata');
  $presentaseRerata= $trendKunjungan->where('jml_kunjungan', '>', 0)->avg('presentase');
  $totalHari       = $trendKunjungan->sum('jml_hari');
  $bulanAktif      = $trendKunjungan->where('jml_kunjungan', '>', 0)->count();
@endphp

<div class="pp-chart-row">

  {{-- Tren Kunjungan ──────────────────────────────────────── --}}
  <div class="pp-card">
    <div class="pp-card-header">
      <div>
        <div class="pp-card-title">Tren Kunjungan {{ $tahun }}</div>
        <div class="pp-card-subtitle">
           data s/d
          {{ \Carbon\Carbon::create($tahun, $trendKunjungan->where('jml_kunjungan', '>', 0)->keys()->last() + 1 ?? now()->month, 1)->format('M Y') }}
        </div>
      </div>
      <span class="src-badge src-api">✓ API</span>
    </div>

    {{-- Summary cards ──────────────────────────────────────── --}}
    <div class="trend-summary-grid">
      <div class="trend-summary-card">
        <div class="trend-sum-lbl">Total Kunjungan</div>
        <div class="trend-sum-val trend-sum-kunjungan">
          {{ number_format($totalKunjungan, 0, ',', '.') }}
        </div>
        <div class="trend-sum-sub"></div>
      </div>
      <div class="trend-summary-card">
        <div class="trend-sum-lbl">Rata-rata/hari</div>
        <div class="trend-sum-val trend-sum-rata">
          {{ number_format($rataKunjungan, 0, ',', '.') }}
        </div>
        <div class="trend-sum-sub">pasien per hari</div>
      </div>
      <div class="trend-summary-card">
        <div class="trend-sum-lbl">Presentase</div>
        <div class="trend-sum-val trend-sum-presentase">
          {{ number_format($presentaseRerata, 1, ',', '.') }}<span style="font-size:13px;font-weight:500;color:var(--pp-muted)">%</span>
        </div>
        <div class="trend-sum-sub">rata-rata bulanan</div>
      </div>
      <div class="trend-summary-card">
          <div class="trend-sum-lbl">Hari Aktif</div>
          <div class="trend-sum-val trend-sum-hari">{{ $totalHari }}</div>
          <div class="trend-sum-sub">hari dari {{ $bulanAktif }} bulan</div>
        </div>
      </div>

    {{-- Chart ──────────────────────────────────────────────── --}}
    <div class="chart-wrap" style="height:260px">
      <canvas id="chartTrendHarian" role="img" aria-label="Tren kunjungan bulanan {{ $tahun }}"></canvas>
    </div>

    {{-- Legend ─────────────────────────────────────────────── --}}
    <div class="trend-legend">
        <div class="trend-legend-item">
          <span class="trend-legend-bar" style="background:rgba(37,99,235,0.75)"></span>
          Jumlah Kunjungan
        </div>
        <div class="trend-legend-item">
          <span class="trend-legend-dot" style="background:#a78bfa"></span>
          Rata-rata/hari
        </div>
        <div class="trend-legend-item">
          <span class="trend-legend-dash" style="border-color:#06b6d4"></span>
          Presentase (%)
        </div>
      </div>
    </div>

  {{-- BOR Bulanan ─────────────────────────────────────────── --}}
  <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">BOR Bulanan {{ $tahun }}</div>
        </div>
        <span class="src-badge src-api">✓ API</span>
      </div>
      <div class="chart-wrap">
        <canvas id="chartBOR"></canvas>
      </div>
    </div>

  </div>

{{-- ═══════════════════════════════════════════
     MONITORING IGD
════════════════════════════════════════════ --}}
@php
    $igd         = $monitoringIGD;
    $igdTotalBed = 30;
    $igdTerisi   = $igd['terisi'];
    $igdKosong   = max($igdTotalBed - $igdTerisi - $igd['antri'], 0);
    $igdPct      = $igdTotalBed > 0 ? round(($igdTerisi / $igdTotalBed) * 100) : 0;
    $igdBarColor = $igdPct >= 90 ? 'cap-danger' : ($igdPct >= 70 ? 'cap-warn' : 'cap-aman');
    $igdBadgeCls = $igdPct >= 90 ? 'badge-over' : ($igdPct >= 70 ? 'badge-warn' : 'badge-aman');
    $igdStatus   = $igdPct >= 90
        ? ['label' => '⚠ IGD penuh',        'cls' => 'igd-status-penuh']
        : ($igdPct >= 70
            ? ['label' => '◑ Kapasitas siaga', 'cls' => 'igd-status-siaga']
            : ['label' => '✓ Kapasitas aman',  'cls' => 'igd-status-aman']);
    $igdDiperbarui = $igd['diperbarui']
        ? \Carbon\Carbon::parse($igd['diperbarui'])->format('d M Y, H:i')
        : null;

    $triageRows = [
        ['key' => 'p1', 'label' => 'Kritis',  'cls' => 'igd-pill-p1'],
        ['key' => 'p2', 'label' => 'Gawat',   'cls' => 'igd-pill-p2'],
        ['key' => 'p3', 'label' => 'Darurat', 'cls' => 'igd-pill-p3'],
    ];
@endphp

  <div class="pp-card" style="margin-bottom:20px"
     id="igdMonitoringCard"
     data-igd-url="{{ route('portal.pelayananpasien.igd_live') }}">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--pp-red)">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
      </svg>
      <span style="font-size:14px;font-weight:600;color:var(--pp-text)">Monitoring IGD</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span class="pp-igd-status-badge {{ $igdStatus['cls'] }}">{{ $igdStatus['label'] }}</span>
      <span id="igdLastUpdate" style="font-size:11px;color:var(--pp-muted)">
        {{ $igdDiperbarui ? 'Data per: ' . $igdDiperbarui : 'Belum ada data' }}
      </span>
    </div>
  </div>

  {{-- Banner menunggu triage --}}
    <div class="igd-antri-banner" id="igdAntriBanner"
        style="{{ $igd['antri'] === 0 ? 'display:none;' : '' }}display:flex;align-items:center;justify-content:space-between;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:14px 20px;margin-bottom:14px;gap:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:36px;height:36px;border-radius:50%;background:rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;color:var(--pp-yellow);flex-shrink:0">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--pp-yellow)">Menunggu triage</div>
          <div style="font-size:11px;color:var(--pp-muted);margin-top:2px">Pasien belum dikategorikan</div>
        </div>
      </div>
      <div style="font-size:48px;font-weight:700;font-family:var(--pp-mono);color:var(--pp-yellow);line-height:1;letter-spacing:-2px"
          data-igd="antri">{{ $igd['antri'] }}</div>
    </div>

  {{-- KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
      <div class="pp-igd-kpi-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <div class="igd-kpi-lbl">Bed terisi</div>
            <div style="width:28px;height:28px;border-radius:7px;background:rgba(239,68,68,0.12);display:flex;align-items:center;justify-content:center;color:var(--pp-red)">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 6h18M3 18h18"/></svg>
      </div>
    </div>
      <div class="igd-kpi-val" style="color:var(--pp-red)" data-igd="terisi">{{ $igdTerisi }}</div>
        <div class="igd-kpi-sub">dari {{ $igdTotalBed }} bed total</div>
      <div class="pp-igd-kpi-bar"><div class="pp-igd-kpi-bar-fill" style="width:{{ $igdPct }}%;background:var(--pp-red)"></div></div>
    </div>

    <div class="pp-igd-kpi-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div class="igd-kpi-lbl">Bed kosong</div>
        <div style="width:28px;height:28px;border-radius:7px;background:rgba(34,197,94,0.12);display:flex;align-items:center;justify-content:center;color:var(--pp-green)">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
      </div>
      <div class="igd-kpi-val" style="color:var(--pp-green)" data-igd="kosong">{{ $igdKosong }}</div>
      <div class="igd-kpi-sub">tersedia sekarang</div>
      <div class="pp-igd-kpi-bar"><div class="pp-igd-kpi-bar-fill" style="width:{{ 100 - $igdPct }}%;background:var(--pp-green)"></div></div>
    </div>

    <div class="pp-igd-kpi-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div class="igd-kpi-lbl">Masuk hari ini</div>
        <div style="width:28px;height:28px;border-radius:7px;background:rgba(37,99,235,0.12);display:flex;align-items:center;justify-content:center;color:var(--pp-accent)">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
      </div>
      <div class="igd-kpi-val" style="color:var(--pp-text)" data-igd="masuk">{{ $igd['masuk'] }}</div>
      <div class="igd-kpi-sub">total kunjungan</div>
      <div class="pp-igd-kpi-bar" style="opacity:0"></div>
    </div>
  </div>

  {{-- Kapasitas bar --}}
  <div style="margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:12px;font-weight:600;color:var(--pp-text)">Kapasitas bed IGD</span>
      <span class="tc-badge {{ $igdBadgeCls }}" data-igd="pct">{{ $igdPct }}%</span>
    </div>
    <div class="pp-igd-bar-track">
      <div class="pp-igd-bar-fill" data-igd="bar" style="width:{{ $igdPct }}%;background:{{ $igdPct >= 90 ? 'var(--pp-red)' : ($igdPct >= 70 ? 'var(--pp-yellow)' : 'var(--pp-green)') }}"></div>
    </div>
    <div class="pp-igd-bar-labels">
      <span>0</span><span>15</span><span>{{ $igdTotalBed }} bed</span>
    </div>
  </div>

  <div style="height:1px;background:var(--pp-border);margin-bottom:16px"></div>

  {{-- Triage cards --}}
  <div style="margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
      <span style="font-size:12px;font-weight:600;color:var(--pp-text)">Status triage</span>
      <span class="src-badge src-live">● Live</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
      @foreach($triageRows as $tr)
      <div class="pp-igd-triage-card igd-t-{{ $tr['key'] }}">
        <div class="igd-t-lbl">{{ $tr['label'] }}</div>
        <div class="igd-t-code">{{ strtoupper($tr['key']) }}</div>
        <div class="igd-t-val" data-igd="triage_{{ $tr['key'] }}">{{ $igd['triage'][$tr['key']] ?? 0 }}</div>
        <div class="igd-t-dot igd-dot-{{ $tr['key'] }}"></div>
      </div>
      @endforeach
    </div>
  </div>

  <div style="height:1px;background:var(--pp-border);margin-bottom:16px"></div>

  {{-- Tabel ringkasan masuk hari ini --}}
  <div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
      <div>
        <div style="font-size:12px;font-weight:600;color:var(--pp-text)">Ringkasan masuk hari ini</div>
        <div style="font-size:11px;color:var(--pp-muted);margin-top:2px">
          Total <span data-igd="masuk">{{ $igd['masuk'] }}</span> pasien per kategori triage
        </div>
      </div>
      <span class="src-badge src-live">● Live</span>
    </div>

    @if($igd['masuk'] === 0)
      <div class="pp-empty" style="height:100px">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>Belum ada kunjungan IGD hari ini</span>
      </div>
    @else
      <table class="pp-tbl" id="igdRingkasanTbl">
        <thead>
          <tr>
            <th>Triage</th>
            <th>Kategori</th>
            <th style="text-align:right">Jumlah</th>
            <th style="text-align:right">Proporsi</th>
            <th>Bar</th>
          </tr>
        </thead>
        <tbody>
          @foreach($triageRows as $tr)
          @php
            $jml = $igd['triage'][$tr['key']] ?? 0;
            $pct = $igd['masuk'] > 0 ? round(($jml / $igd['masuk']) * 100) : 0;
          @endphp
          <tr>
            <td><span class="igd-pill {{ $tr['cls'] }}">{{ strtoupper($tr['key']) }}</span></td>
            <td style="font-size:12px;color:var(--pp-text)">{{ $tr['label'] }}</td>
            <td style="text-align:right;font-family:var(--pp-mono);font-weight:600;font-size:14px">{{ $jml }}</td>
            <td style="text-align:right;font-size:11px;color:var(--pp-muted)">{{ $pct }}%</td>
            <td style="width:80px">
              <div class="pp-bar">
                <div class="pp-bar-fill" style="width:{{ $pct }}%;background:{{ $tr['key'] === 'p1' ? 'var(--pp-red)' : ($tr['key'] === 'p2' ? 'var(--pp-yellow)' : 'var(--pp-green)') }}"></div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>{{-- end pp-card IGD --}}
@endsection

@push('scripts')
<script>
window.PP_DATA = {
  trendKunjungan : {!! $trendKunjungan->toJson() !!},
  borData    : {!! $chartBOR->toJson()       !!},
  avlosData  : {!! $chartAvlos->toJson()     !!},
  rajalData  : {!! $ringkasanRajal->toJson() !!},
  triageData : {!! $triageIGD->toJson()      !!},
  bulan      : {{ $bulan }},
  tahun      : {{ $tahun }},
};
</script>
@vite(['resources/js/portal/pelayananpasien.js'])
@vite(['resources/js/portal/bor-modal.js'])
@endpush
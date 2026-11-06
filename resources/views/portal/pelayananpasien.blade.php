@extends('layouts.app')
@section('title', 'Pelayanan Pasien')

@push('styles')
@vite(['resources/css/portal/portal-navbar.css'])
@vite(['resources/css/portal/pelayananpasien.css'])
@vite(['resources/css/portal/bor-modal.css'])
@vite(['resources/css/portal/igd-capacity-tabs.css'])
@endpush

@section('content')
<div class="pp-wrap">

  <x-portal.navbar title="Portal Pelayanan Pasien">
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
  </x-portal.navbar>

  {{-- ═══════════════════════════════════════════
       TOP 4 KPI CARDS  (komponen bersama <x-portal.kpi-card>)
  ════════════════════════════════════════════ --}}
  @php
    // status: tercapai (hijau) | perhatian (kuning) | belum (merah) -> kelas .pc-badge--*
    $topCards = [
      [
        'label'   => 'BOR (Bed Occupancy Rate)',
        'nilai'   => $bor,
        'unit'    => '%',
        'standar' => 'Standar 60 – 85%',
        'color'   => 'sky',
        'icon'    => 'bed',
        'pct'     => min($bor, 100),
        'status'  => ($bor >= 60 && $bor <= 85) ? 'tercapai' : ($bor < 60 ? 'perhatian' : 'belum'),
        'label_s' => ($bor >= 60 && $bor <= 85) ? '✓ Ideal' : ($bor < 60 ? '↓ Rendah' : '↑ Tinggi'),
      ],
      [
        'label'   => 'LOS (Length of Stay)',
        'nilai'   => $los,
        'unit'    => 'hari',
        'standar' => 'Standar 3 – 12 hari',
        'color'   => 'violet',
        'icon'    => 'clock',
        'pct'     => min(round(($los / 15) * 100), 100),
        'status'  => ($los >= 3 && $los <= 12) ? 'tercapai' : 'perhatian',
        'label_s' => ($los >= 3 && $los <= 12) ? '✓ Ideal' : '⚠ Periksa',
      ],
      [
        'label'   => 'TOI (Turn Over Interval)',
        'nilai'   => $toi,
        'unit'    => 'hari',
        'standar' => 'Standar 1 – 3 hari',
        'color'   => 'rose',
        'icon'    => 'refresh',
        'pct'     => min(round(($toi / 6) * 100), 100),
        'status'  => ($toi >= 1 && $toi <= 3) ? 'tercapai' : 'perhatian',
        'label_s' => ($toi >= 1 && $toi <= 3) ? '✓ Ideal' : '⚠ Periksa',
      ],
      [
        'label'   => 'BTO (Bed Turn Over)',
        'nilai'   => $bto,
        'unit'    => 'kali',
        'standar' => '3-5 kali per Bulan',
        'color'   => 'teal',
        'icon'    => 'trend-up',
        'pct'     => min(round(($bto / 60) * 100), 100),
        'status'  => null,      // BTO tidak punya badge status
        'label_s' => null,
      ],
    ];
  @endphp

  <div class="pc-kpi-grid">
    @foreach($topCards as $idx => $tc)
      @php $ada = $tc['nilai'] > 0; @endphp
      {{-- Kartu pertama (BOR) bisa diklik: #borKpiCard dipakai bor-modal.js --}}
      <x-portal.kpi-card
        :label="$tc['label']"
        :icon="$tc['icon']"
        :color="$tc['color']"
        :sub="$tc['standar']"
        :count="$ada ? $tc['nilai'] : null"
        :value="$ada ? null : '—'"
        :unit="$ada ? $tc['unit'] : null"
        :decimal="is_float($tc['nilai']) ? 2 : 0"
        :i="$idx"
        :id="$idx === 0 ? 'borKpiCard' : null"
        :title="$idx === 0 ? 'Klik untuk detail BOR per ruangan' : null"
        :class="$idx === 0 ? 'pc-kpi--klik' : ''">

        @if($ada)
          <div class="pc-bar pc-bar--kartu" style="--bar: var(--k); --w: {{ $tc['pct'] }}%"><span></span></div>
          @if($tc['status'])
            <div class="pp-tc-foot">
              <span class="pc-badge pc-badge--{{ $tc['status'] }}">{{ $tc['label_s'] }}</span>
              @if($idx === 0)
                <button type="button" class="pp-link-btn" onclick="BORModal.open()">Detail →</button>
              @endif
            </div>
          @endif
        @else
          <div class="pp-tc-foot"><span class="pc-badge pc-badge--kosong">Belum ada data</span></div>
        @endif
      </x-portal.kpi-card>
    @endforeach
  </div>

  {{-- ═══════════════════════════════════════════
       MONITORING IGD + KAPASITAS TT (Tab Card)
       Data Monitoring = $monitoringIGD
       Data Kapasitas TT = fetch realtime via infottProxy (igd-capacity-tabs.js)
  ════════════════════════════════════════════ --}}
  @php
      $igd         = $monitoringIGD;
      $igdTotalBed = 100;
      $igdTerisi   = $igd['terisi'] + $igd['antri'];
      $igdKosong   = max($igdTotalBed - $igdTerisi, 0);
      $igdPct      = $igdTotalBed > 0 ? round(($igdTerisi / $igdTotalBed) * 100) : 0;
      $igdBadgeCls = $igdPct >= 90 ? 'badge-over' : ($igdPct >= 70 ? 'badge-warn' : 'badge-aman');
      $igdBarWarna = $igdPct >= 90 ? 'var(--pp-red)' : ($igdPct >= 70 ? 'var(--pp-yellow)' : 'var(--pp-green)');
      $igdStatus   = $igdPct >= 90
          ? ['label' => '⚠ IGD penuh',        'cls' => 'igd-status-penuh']
          : ($igdPct >= 70
              ? ['label' => '◑ Kapasitas siaga', 'cls' => 'igd-status-siaga']
              : ['label' => '✓ Kapasitas aman',  'cls' => 'igd-status-aman']);
      $igdDiperbarui = $igd['diperbarui']
          ? \Carbon\Carbon::parse($igd['diperbarui'])->format('d M Y, H:i')
          : null;

      $triageRows = [
          ['key' => 'p1', 'label' => 'Kritis',  'cls' => 'igd-pill-p1', 'warna' => 'var(--pp-red)'],
          ['key' => 'p2', 'label' => 'Gawat',   'cls' => 'igd-pill-p2', 'warna' => 'var(--pp-yellow)'],
          ['key' => 'p3', 'label' => 'Darurat', 'cls' => 'igd-pill-p3', 'warna' => 'var(--pp-green)'],
      ];

      // Tile angka IGD; key = nilai data-igd yang di-update polling (pelayananpasien.js)
      $igdTiles = [
          ['key' => 'terisi', 'label' => 'Bed terisi',     'nilai' => $igdTerisi,      'sub' => "dari {$igdTotalBed} bed", 'warna' => 'var(--danger)',  'ikon' => 'bed'],
          ['key' => 'kosong', 'label' => 'Bed kosong',     'nilai' => $igdKosong,      'sub' => 'tersedia',                'warna' => 'var(--success)', 'ikon' => 'check'],
          ['key' => 'masuk',  'label' => 'Masuk hari ini', 'nilai' => $igd['masuk'],   'sub' => 'pasien',                  'warna' => 'var(--sky)',     'ikon' => 'plus'],
      ];
  @endphp

  <x-portal.panel class="igd-tabcard" id="igdTabCard" :i="4"
      :data-igd-url="route('portal.pelayananpasien.igd_live')"
      :data-tt-proxy-url="route('portal.pelayananpasien.infott_proxy')">

    {{-- ── Header: judul + tab ── --}}
    <div class="igd-tabhead">
      <div class="igd-tabhead-left">
        <div class="igd-tabhead-title">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          <span>Monitoring IGD</span>
        </div>

        <div class="igd-tabs" data-role="tabs">
          <span class="igd-tab-indicator" data-role="tab-indicator"></span>
          <button class="igd-tab-btn is-active" data-tab="monitoring" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13a9 9 0 1118 0M3 13a2 2 0 002 2h14a2 2 0 002-2M3 13l5-7m9 7l-5-7"/>
            </svg>
            Monitoring
          </button>
          <button class="igd-tab-btn" data-tab="kapasitas" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kapasitas TT
          </button>
        </div>
      </div>

      <div class="igd-tabhead-meta">
        <span class="pp-igd-status-badge {{ $igdStatus['cls'] }}" id="igdStatusBadge">{{ $igdStatus['label'] }}</span>
        <span id="igdLastUpdate">{{ $igdDiperbarui ? 'Data per: ' . $igdDiperbarui : 'Belum ada data' }}</span>
      </div>
    </div>

    <div class="igd-tabpanels">

      {{-- ════════════════════════════════════════
          PANEL 1: MONITORING
      ════════════════════════════════════════ --}}
      <div class="igd-panel is-active" data-panel="monitoring">

        {{-- Banner menunggu triage --}}
        <div class="pp-antri igd-antri-banner" id="igdAntriBanner">
          <div class="pp-antri-info">
            <div class="pp-antri-icon"><x-icon name="clock" width="16" height="16" /></div>
            <div>
              <div class="pp-antri-title">Menunggu triage</div>
              <div class="pp-antri-sub">Pasien belum dikategorikan</div>
            </div>
          </div>
          <div class="pp-antri-val" data-igd="antri">{{ $igd['antri'] }}</div>
        </div>

        {{-- Tile angka --}}
        <div class="pc-stat-grid pp-igd-stats" style="--cols: 3">
          @foreach($igdTiles as $tile)
            <div class="pc-stat" style="--k: {{ $tile['warna'] }}">
              <div class="pc-stat-head">
                <span class="pc-stat-label">{{ $tile['label'] }}</span>
                <span class="pc-stat-icon"><x-icon :name="$tile['ikon']" width="14" height="14" /></span>
              </div>
              <div class="pc-stat-value" data-igd="{{ $tile['key'] }}"
                   data-count-target="{{ $tile['nilai'] }}" data-count-decimal="0">
                <span class="count-num">{{ $tile['nilai'] }}</span>
              </div>
              <div class="pc-stat-sub">{{ $tile['sub'] }}</div>
            </div>
          @endforeach
        </div>

        {{-- Kapasitas bar --}}
        <div class="pp-block">
          <div class="pp-sect-head">
            <span class="pp-sect-title">Kapasitas bed IGD</span>
            <span class="tc-badge {{ $igdBadgeCls }}" data-igd="pct">{{ $igdPct }}%</span>
          </div>
          <div class="pp-igd-bar-track">
            <div class="pp-igd-bar-fill" data-igd="bar" style="width:{{ $igdPct }}%;background:{{ $igdBarWarna }}"></div>
          </div>
          <div class="pp-igd-bar-labels">
            <span>0</span><span>30</span><span>{{ $igdTotalBed }} bed</span>
          </div>
        </div>

        <div class="pp-divider"></div>

        {{-- Triage cards --}}
        <div class="pp-block">
          <div class="pp-sect-head">
            <span class="pp-sect-title">Status triage</span>
            <span class="src-badge src-live">● Live</span>
          </div>
          <div class="pp-triage-grid">
            @foreach($triageRows as $tr)
              <div class="pp-igd-triage-card igd-t-{{ $tr['key'] }}">
                <div class="igd-t-lbl">{{ $tr['label'] }}</div>
                <div class="igd-t-code">{{ strtoupper($tr['key']) }}</div>
                <div class="igd-t-val" data-igd="triage_{{ $tr['key'] }}"
                     data-count-target="{{ $igd['triage'][$tr['key']] ?? 0 }}" data-count-decimal="0">
                  <span class="count-num">{{ $igd['triage'][$tr['key']] ?? 0 }}</span>
                </div>
                <div class="igd-t-dot"></div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="pp-divider"></div>

        {{-- Tabel ringkasan masuk hari ini --}}
        <div>
          <div class="pp-sect-head">
            <div>
              <div class="pp-sect-title">Ringkasan masuk hari ini</div>
              <div class="pp-sect-sub">
                Total <span data-igd="masuk">{{ $igd['masuk'] }}</span> pasien per kategori triage
              </div>
            </div>
            <span class="src-badge src-live">● Live</span>
          </div>

          <table class="pp-tbl" id="igdRingkasanTbl">
            <thead>
              <tr>
                <th>Triage</th>
                <th>Kategori</th>
                <th class="pp-note">Jumlah</th>
                <th class="pp-note">Proporsi</th>
                <th>Bar</th>
              </tr>
            </thead>
            <tbody>
              @foreach($triageRows as $tr)
                @php
                  $jml   = $igd['triage'][$tr['key']] ?? 0;
                  $pctTr = $igd['masuk'] > 0 ? round(($jml / $igd['masuk']) * 100) : 0;
                @endphp
                <tr>
                  <td><span class="igd-pill {{ $tr['cls'] }}">{{ strtoupper($tr['key']) }}</span></td>
                  <td>{{ $tr['label'] }}</td>
                  <td class="pp-num">{{ $jml }}</td>
                  <td class="pp-note">{{ $pctTr }}%</td>
                  <td style="width:80px">
                    <div class="pp-bar">
                      <div class="pp-bar-fill" style="width:{{ $pctTr }}%;background:{{ $tr['warna'] }}"></div>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>{{-- /panel monitoring --}}

      {{-- ════════════════════════════════════════
           PANEL 2: KAPASITAS TT
      ════════════════════════════════════════ --}}
      <div class="igd-panel" data-panel="kapasitas">
        <div class="igd-tt-toolbar">
          <div class="igd-tt-stats">
            <div class="igd-tt-stat">
              <span class="igd-tt-stat-lbl">Ruangan</span>
              <span class="igd-tt-stat-val" data-role="tt-stat-ruangan">—</span>
            </div>
            <div class="igd-tt-stat">
              <span class="igd-tt-stat-lbl">Total Bed</span>
              <span class="igd-tt-stat-val" data-role="tt-stat-total">—</span>
            </div>
            <div class="igd-tt-stat">
              <span class="igd-tt-stat-lbl">Terisi</span>
              <span class="igd-tt-stat-val" style="color:var(--pp-red)" data-role="tt-stat-terisi">—</span>
            </div>
            <div class="igd-tt-stat">
              <span class="igd-tt-stat-lbl">Kosong</span>
              <span class="igd-tt-stat-val" style="color:var(--pp-green)" data-role="tt-stat-kosong">—</span>
            </div>
          </div>
          <button class="igd-tt-refresh" type="button" data-role="refresh-btn">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span data-role="tt-updated">Refresh</span>
          </button>
        </div>

        <div class="igd-tt-breadcrumb" data-role="breadcrumb">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          <span data-role="breadcrumb-label">Semua Ruangan</span>
        </div>

        <div class="igd-tt-grid" data-role="grid">
          {{-- diisi oleh igd-capacity-tabs.js --}}
          <div class="igd-skeleton" style="height:190px;border-radius:14px"></div>
          <div class="igd-skeleton" style="height:190px;border-radius:14px"></div>
          <div class="igd-skeleton" style="height:190px;border-radius:14px"></div>
        </div>

      </div>{{-- /panel kapasitas --}}

    </div>{{-- /igd-tabpanels --}}
  </x-portal.panel>


  {{-- ═══════════════════════════════════════════
       GRAFIK BARBER-JOHNSON
  ════════════════════════════════════════════ --}}
  <x-portal.panel :title="'Grafik Barber-Johnson ' . $tahun" subtitle="Standar Depkes RI" :i="5">
    <x-slot:actions><span class="src-badge src-api">✓ API</span></x-slot:actions>

    <div class="pc-panel-body">
      <div class="bj-filter">
        <label>Pilih Bulan:</label>
        <select id="bjBulanSelect">
          @php $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
          @foreach($namaBulan as $i => $nb)
            <option value="{{ $i }}">{{ $nb }}</option>
          @endforeach
        </select>
        <span id="bjStatusBadge"></span>
        <button id="bjDownloadBtn" class="pp-btn-ghost">
          <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Download PDF
        </button>
      </div>

      {{-- Id bjKpi{BOR|BTO|AVLOS|TOI} dan bjCoord{...} dipakai pelayananpasien.js --}}
      <div class="pc-stat-grid" style="--cols: 4">
        @foreach([['BOR','%','--sky'],['BTO','','--teal'],['AVLOS','hr','--violet'],['TOI','hr','--rose']] as [$lbl,$unit,$warna])
          <div class="pc-stat" style="--k: var({{ $warna }})">
            <div class="pc-stat-label">{{ $lbl }}</div>
            <div class="pc-stat-value" id="bjKpi{{ $lbl }}">—<span class="bj-kpi-unit"> {{ $unit }}</span></div>
            <div class="pc-stat-sub" id="bjCoord{{ $lbl }}">—</div>
          </div>
        @endforeach
      </div>

      <div class="pp-bj-canvas">
        <canvas id="chartBJ" role="img" aria-label="Grafik Barber-Johnson bulanan">Grafik Barber-Johnson.</canvas>
      </div>

      <div class="bj-legend" id="bjLegendBar"></div>
    </div>
  </x-portal.panel>

  {{-- ═══════════════════════════════════════════
     ROW: Tren Kunjungan + BOR Bulanan
════════════════════════════════════════════ --}}
  @php
    // Hitung total & rata-rata dari data tren untuk tile ringkasan
    $totalKunjungan   = $trendKunjungan->sum('jml_kunjungan');
    $rataKunjungan    = $trendKunjungan->where('jml_kunjungan', '>', 0)->avg('jml_rata_rata');
    $presentaseRerata = $trendKunjungan->where('jml_kunjungan', '>', 0)->avg('presentase');
    $totalHari        = $trendKunjungan->sum('jml_hari');
    $bulanAktif       = $trendKunjungan->where('jml_kunjungan', '>', 0)->count();
    $sdBulan          = \Carbon\Carbon::create($tahun, $trendKunjungan->where('jml_kunjungan', '>', 0)->keys()->last() + 1 ?? now()->month, 1)->format('M Y');

    $trendTiles = [
      ['label' => 'Total Kunjungan', 'nilai' => $totalKunjungan,   'dec' => 0, 'sub' => 'pasien tahun ini',                 'warna' => '--sky',    'unit' => ''],
      ['label' => 'Rata-rata/hari',  'nilai' => $rataKunjungan,    'dec' => 0, 'sub' => 'pasien per hari',                 'warna' => '--violet', 'unit' => ''],
      ['label' => 'Presentase',      'nilai' => $presentaseRerata, 'dec' => 1, 'sub' => 'rata-rata bulanan',               'warna' => '--teal',   'unit' => '%'],
      ['label' => 'Hari Aktif',      'nilai' => $totalHari,        'dec' => 0, 'sub' => "hari dari {$bulanAktif} bulan",   'warna' => '--success', 'unit' => ''],
    ];
  @endphp

  <div class="pp-chart-row">

    {{-- Tren Kunjungan --}}
    <x-portal.panel :title="'Tren Kunjungan ' . $tahun" :subtitle="'data s/d ' . $sdBulan" :i="6">
      <x-slot:actions><span class="src-badge src-api">✓ API</span></x-slot:actions>

      <div class="pc-panel-body">
        <div class="pc-stat-grid" style="--cols: 4">
          @foreach($trendTiles as $t)
            <div class="pc-stat" style="--k: var({{ $t['warna'] }})">
              <div class="pc-stat-label">{{ $t['label'] }}</div>
              <div class="pc-stat-value" data-count-target="{{ $t['nilai'] }}" data-count-decimal="{{ $t['dec'] }}">
                <span class="count-num">{{ number_format($t['nilai'], $t['dec'], ',', '.') }}</span>@if($t['unit'])<span class="pc-kpi-unit">{{ $t['unit'] }}</span>@endif
              </div>
              <div class="pc-stat-sub">{{ $t['sub'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="chart-wrap" style="height:260px">
          <canvas id="chartTrendHarian" role="img" aria-label="Tren kunjungan bulanan {{ $tahun }}"></canvas>
        </div>

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
    </x-portal.panel>

    {{-- BOR Bulanan.
         Kelas pp-card di body = HOOK pelayananpasien.js: grid bulan & legend BOR disisipkan ke elemen ini --}}
    <x-portal.panel :title="'BOR Bulanan ' . $tahun" :i="7">
      <x-slot:actions><span class="src-badge src-api">✓ API</span></x-slot:actions>
      <div class="pc-panel-body pp-card">
        <div class="chart-wrap" style="overflow:visible">
          <canvas id="chartBOR"></canvas>
        </div>
      </div>
    </x-portal.panel>

  </div>{{-- /pp-chart-row --}}

</div>{{-- /pp-wrap --}}
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
@vite(['resources/js/portal/igd-capacity-tabs.js'])
@endpush
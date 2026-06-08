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

  {{-- Header --}}
  <div class="pp-header">
    <div style="display:flex;align-items:center;gap:12px">
      <a href="{{ route('dashboard') }}" class="pp-btn-ghost" style="padding:6px 10px" title="Kembali ke Home">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>
      <h1>Portal Pelayanan Pasien</h1>
      <span class="pp-badge-live">Live Data</span>
    </div>
  </div>

  {{-- Filter --}}
  <form method="GET" action="{{ route('portal.pelayananpasien') }}" class="pp-filter-bar">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--pp-muted);flex-shrink:0">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
      <line x1="16" y1="2" x2="16" y2="6"/>
      <line x1="8"  y1="2" x2="8"  y2="6"/>
      <line x1="3"  y1="10" x2="21" y2="10"/>
    </svg>

    <label>Bulan</label>
    <select name="bulan">
      @php $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
      @foreach($namaBulan as $i => $nb)
        <option value="{{ $i + 1 }}" @selected(($i + 1) == $bulan)>{{ $nb }}</option>
      @endforeach
    </select>

    <label>Tahun</label>
    <select name="tahun">
      @for($y = now()->year; $y >= now()->year - 4; $y--)
        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
      @endfor
    </select>

    <button type="submit" class="pp-btn">Tampilkan</button>
    <a href="{{ route('portal.pelayananpasien') }}" class="pp-btn-ghost">Reset</a>
  </form>

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
        'label'       => 'Kunjungan Hari Ini',
        'nilai'       => $ringkasanRajal->sum('total_kunjungan'),
        'unit'        => '',
        'standar'     => 'Total semua poli rawat jalan',
        'color'       => '#06b6d4',
        'icon_bg'     => 'rgba(6,182,212,0.15)',
        'is_kunjungan'=> true,
        'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
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
          {{ is_float($tc['nilai']) ? number_format($tc['nilai'], 1) : number_format($tc['nilai']) }}
          @if($tc['unit'])<span class="tc-unit">{{ $tc['unit'] }}</span>@endif
        </div>
        <div class="tc-standar">{{ $tc['standar'] }}</div>
        @if(!isset($tc['is_kunjungan']))
          <div class="tc-progress">
            <div class="tc-progress-fill" style="width:{{ $tc['pct'] }}%;background:{{ $tc['color'] }}"></div>
          </div>
          <span class="tc-badge badge-{{ $tc['status'] }}">{{ $tc['label_s'] }}</span>
        @else
          <div class="tc-delta delta-up">▲ semua poli aktif</div>
        @endif
      @else
        {{-- Belum ada data --}}
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
        <div class="pp-card-subtitle"> Standar Depkes RI</div>
      </div>
      <span class="src-badge src-api">✓ API</span>
    </div>

    {{-- Filter bulan --}}
    <div class="bj-filter">
      <label>Pilih Bulan:</label>
      <select id="bjBulanSelect">
        @php $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
        @foreach($namaBulan as $i => $nb)
          <option value="{{ $i }}">{{ $nb }}</option>
        @endforeach
      </select>
      <span id="bjStatusBadge"></span>
    </div>

    {{-- KPI row --}}
    <div class="bj-kpi-row">
      @foreach([['BOR','%'],['BTO',''],['AVLOS','hr'],['TOI','hr']] as [$lbl,$unit])
      <div class="bj-kpi-card">
        <div class="bj-kpi-label">{{ $lbl }}</div>
        <div class="bj-kpi-val" id="bjKpi{{ $lbl }}">—<span class="bj-kpi-unit"> {{ $unit }}</span></div>
        <div class="bj-kpi-coord" id="bjCoord{{ $lbl }}">—</div>
      </div>
      @endforeach
    </div>

    {{-- Chart --}}
    <div style="position:relative;height:460px;width:100%">
      <canvas id="chartBJ" role="img" aria-label="Grafik Barber-Johnson bulanan">Grafik Barber-Johnson.</canvas>
    </div>

    {{-- Legend --}}
    <div class="bj-legend" id="bjLegendBar" style="margin-top:12px;padding:9px 14px;background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.2);border-radius:8px"></div>
  </div>

  {{-- ═══════════════════════════════════════════
       ROW: Tren Harian + BOR Bulanan
  ════════════════════════════════════════════ --}}
  <div class="pp-chart-row">
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Tren Kunjungan Harian</div>
          <div class="pp-card-subtitle">
            Periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }}
            — {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}
          </div>
        </div>
        <span class="src-badge src-live">● Live</span>
      </div>
      <div class="chart-wrap">
        <canvas id="chartTrendHarian"></canvas>
      </div>
    </div>

    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">BOR Bulanan {{ $tahun }}</div>
          <!-- <div class="pp-card-subtitle">Target 60 – 85%</div> -->
        </div>
        <span class="src-badge src-api">✓ API</span>
      </div>
      <div class="chart-wrap">
        <canvas id="chartBOR"></canvas>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════
       ROW: Poli + Triage + Tabel Rajal
  ════════════════════════════════════════════ --}}
  <div class="pp-chart-row3">

    {{-- Kunjungan per Poli --}}
    <div class="pp-card">
      <div class="pp-card-header">
        <div>
          <div class="pp-card-title">Kunjungan per Poli</div>
          <div class="pp-card-subtitle">Top poli rawat jalan</div>
        </div>
        <span class="src-badge src-live">● Live</span>
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
        <span class="src-badge src-live">● Live</span>
      </div>
      <div class="chart-wrap-sm" style="position:relative">
        <canvas id="chartTriage"></canvas>
        <div id="triage-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-55%);text-align:center;pointer-events:none">
          <div id="triage-total" style="font-size:22px;font-weight:700;font-family:var(--pp-mono);color:var(--pp-text)">–</div>
          <div style="font-size:10px;color:var(--pp-muted);font-weight:600">Pasien</div>
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
        <span class="src-badge src-live">● Live</span>
      </div>
      @if($ringkasanRajal->isEmpty())
        <div class="pp-empty" style="height:200px">
          <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span>Belum ada data kunjungan</span>
        </div>
      @else
        <div style="overflow-x:auto;overflow-y:auto;max-height:230px">
          <table class="pp-tbl">
            <thead>
              <tr>
                <th>Poli</th>
                <th style="text-align:right">Kunjungan</th>
                <th>Proporsi</th>
              </tr>
            </thead>
            <tbody>
              @php $totalRajal = $ringkasanRajal->sum('total_kunjungan') ?: 1 @endphp
              @foreach($ringkasanRajal as $poli)
                @php $pct = round(($poli->total_kunjungan / $totalRajal) * 100, 1) @endphp
                <tr>
                  <td>{{ $poli->nama_poli }}</td>
                  <td style="text-align:right;font-family:var(--pp-mono);font-weight:600">
                    {{ number_format($poli->total_kunjungan) }}
                  </td>
                  <td>
                    <div class="pp-bar-wrap">
                      <div class="pp-bar">
                        <div class="pp-bar-fill" style="width:{{ $pct }}%"></div>
                      </div>
                      <span style="font-size:10px;color:var(--pp-muted);white-space:nowrap">{{ $pct }}%</span>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

  </div>{{-- end pp-chart-row3 --}}

</div>{{-- end pp-wrap --}}
@endsection


@push('scripts')
<script>
window.PP_DATA = {
  trendData  : {!! $trendHarian->toJson()   !!},
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
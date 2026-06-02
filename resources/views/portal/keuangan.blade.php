{{-- ============================================================
     resources/views/portal/keuangan.blade.php
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Dashboard Keuangan')

@push('styles')
    @vite('resources/css/portal/keuangan.css')
@endpush

@section('content')
<div class="dash-wrap">

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <span class="filter-label">Tahun</span>
        <select id="tahunSelect" class="filter-select">
            @forelse($tahunList as $t)
                <option value="{{ $t }}" @selected((int)$t===(int)$tahun)>{{ $t }}</option>
            @empty
                <option value="{{ $tahun }}">{{ $tahun }}</option>
            @endforelse
        </select>

        <span class="filter-label" style="margin-left:6px">Bulan</span>
        <select id="bulanSelect" class="filter-select">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" @selected($i === (int) now()->month)>
                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                </option>
            @endfor
        </select>

        <a href="{{ url('/') }}"
           title="Kembali ke Home"
           style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:transparent;border:1px solid rgba(255,255,255,.07);border-radius:8px;color:#7D8590;font-size:11px;text-decoration:none;transition:all .15s"
           onmouseover="this.style.cssText+=';background:#1e2438;color:#e2e8f0'"
           onmouseout="this.style.cssText+=';background:transparent;color:#7D8590'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Home
        </a>
    </div>

    {{-- KPI CARDS --}}
    <section class="kpi-row">
        <div class="kpi-card kpi-pendapatan">
            <div class="kpi-label">Pendapatan Realisasi</div>
            <div class="kpi-value" id="kpiPendapatan">Rp —</div>
            <div class="kpi-delta" id="kpiPendapatanMom">—</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60"><path d="M10 50 Q30 10 50 50" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="30" cy="25" r="8" fill="currentColor"/></svg>
        </div>
        <div class="kpi-card kpi-belanja">
            <div class="kpi-label">Belanja Realisasi</div>
            <div class="kpi-value" id="kpiBelanja">Rp —</div>
            <div class="kpi-delta" id="kpiBelanjaMom">—</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60"><rect x="10" y="20" width="40" height="25" rx="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M20 20v-5a10 10 0 0 1 20 0v5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        </div>
        <div class="kpi-card kpi-surplus">
            <div class="kpi-label">Net (P − B)</div>
            <div class="kpi-value" id="kpiSurplus">Rp —</div>
            <div class="kpi-delta" id="kpiAvg">Rata-rata kinerja — %</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60"><path d="M15 45 L25 30 L35 38 L50 15" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="kpi-card kpi-margin">
            <div class="kpi-label">Kinerja Anggaran</div>
            <div class="kpi-value" id="kpiMargin">— %</div>
            <div class="kpi-delta" id="kpiMarginSub">—</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60"><circle cx="30" cy="30" r="20" stroke="currentColor" stroke-width="2" fill="none"/><path d="M30 10 A20 20 0 0 1 50 30" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/></svg>
        </div>
    </section>

    {{-- CHARTS 2×2 --}}
    <section class="charts-grid">

        {{-- Trend Tahunan --}}
        <div class="chart-card" style="grid-column:1;grid-row:1">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Trend Keuangan Tahunan</h2>
                    <p class="chart-sub">Akumulasi Pendapatan vs Belanja – <span id="trendYearLabel">{{ $tahun }}</span></p>
                </div>
                <div class="legend-row">
                    <span class="legend-dot" style="background:#2DD4BF"></span><span>Pendapatan</span>
                    <span class="legend-dot" style="background:#F59E0B"></span><span>Belanja</span>
                </div>
            </div>
            <div class="amchart-body"><div id="chartTrendAm"></div></div>
        </div>

        {{-- Data Harian --}}
        <div class="chart-card" style="grid-column:1;grid-row:2">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Data Harian</h2>
                    <p class="chart-sub">Pendapatan &amp; Belanja per hari – <span id="harianBulanLabel">—</span> <span id="harianTahunLabel">{{ $tahun }}</span></p>
                </div>
                <div class="legend-row">
                    <span class="legend-dot" style="background:#34D399"></span><span>Pendapatan</span>
                    <span class="legend-dot" style="background:#FBBF24"></span><span>Belanja</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartHarian"></canvas>
                <div class="empty-state" id="emptyHarian" style="display:none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Belum ada data harian untuk bulan ini</span>
                </div>
            </div>
        </div>

        {{-- Unit Table --}}
        <div class="chart-card" style="grid-column:2;grid-row:1">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Realisasi per Unit / Divisi</h2>
                    <p class="chart-sub">Proporsi belanja bulan <span id="unitBulanLabel">—</span></p>
                </div>
                <div style="font-size:8.5px;color:var(--muted);text-align:right;line-height:1.5">Total<br>
                    <span id="unitTotalBulan" style="font-size:10.5px;font-weight:700;color:var(--text);font-family:'DM Mono',monospace">—</span>
                </div>
            </div>
            <div class="unit-table-wrap" id="unitTableWrap">
                <div class="unit-table-header">
                    <div>#</div><div>Unit / Divisi</div>
                    <div style="text-align:right">Realisasi</div>
                    <div style="text-align:right">Proporsi</div>
                </div>
                <div class="unit-table-body" id="unitTableBody"></div>
            </div>
            <div class="empty-state" id="emptyUnit" style="display:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Belum ada data unit untuk bulan ini</span>
            </div>
        </div>

        {{-- Rekap Keuangan --}}
        <div class="chart-card" style="grid-column:2;grid-row:2">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Rekap Keuangan</h2>
                    <p class="chart-sub">Pendapatan &amp; Belanja – <span id="rekapTahunLabel">{{ $tahun }}</span></p>
                </div>
            </div>
            <div class="rekap-body">
                <div class="rekap-insight" id="rekapInsight">—</div>
                <div class="rekap-tbl-header">
                    <div>Bln</div><div></div>
                    <div style="text-align:right;color:#2DD4BF">Pndptn</div>
                    <div style="text-align:right;color:#FBBF24">Blnja</div>
                    <div style="text-align:right">Net</div><div></div>
                </div>
                <div class="rekap-rows" id="rekapRows"></div>
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
    {{-- amCharts tetap pakai CDN karena lisensi & ukuran bundle-nya besar --}}
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

    @vite('resources/js/portal/keuangan.js')
@endpush
@extends('layouts.app')
@section('title', 'Keuangan')

@push('styles')
    @vite('resources/css/portal/keuangan.css')
@endpush

@section('content')
<div class="dash-wrap">

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <a href="{{ url('/') }}" class="keu-back-btn" title="Kembali ke Dashboard">
            <x-icon name="chevron-left" width="14" height="14" />
            <span>Dashboard</span>
        </a>

        <div class="filter-group">
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
        </div>
    </div>

    {{-- KPI CARDS --}}
    <section class="kpi-row">
        <div class="kpi-card kpi-pendapatan">
            <div class="kpi-label">Pendapatan Realisasi</div>
            <div class="kpi-value" id="kpiPendapatan">Rp —</div>
            <div class="kpi-delta" id="kpiPendapatanMom">—</div>
            <x-icon name="trend-arc" class="kpi-bg-icon" viewBox="0 0 60 60" />
        </div>
        <div class="kpi-card kpi-belanja">
            <div class="kpi-label">Belanja Realisasi</div>
                <div class="kpi-value" id="kpiBelanja">Rp —</div>
                    <div class="kpi-delta" id="kpiBelanjaMom">—</div>
            <x-icon name="bag" class="kpi-bg-icon" viewBox="0 0 60 60" />
        </div>
        <div class="kpi-card kpi-surplus">
            <div class="kpi-label">Net (P − B)</div>
                <div class="kpi-value" id="kpiSurplus">Rp —</div>
                    <div class="kpi-delta" id="kpiAvg">Rata-rata kinerja — %</div>
            <x-icon name="trend-line" class="kpi-bg-icon" viewBox="0 0 60 60" />
        </div>
        <div class="kpi-card kpi-margin">
            <div class="kpi-label">Kinerja Anggaran</div>
                <div class="kpi-value" id="kpiMargin">— %</div>
                    <div class="kpi-delta" id="kpiMarginSub">—</div>
            <x-icon name="progress-ring" class="kpi-bg-icon" viewBox="0 0 60 60" />
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
                    <x-icon name="document-text" stroke-width="1.5" />
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
                <x-icon name="document-text" stroke-width="1.5" />
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
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

    @vite('resources/js/portal/keuangan.js')
@endpush
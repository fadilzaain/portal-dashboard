@extends('layouts.app')
@section('title', 'Dashboard Keuangan')

@push('styles')
<style>
    /* Fit Screen Override */
    #main-wrap    { height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
    #page-content { flex: 1; min-height: 0; padding: 0 !important; overflow: hidden; display: flex; flex-direction: column; }

    /* Css Vars */
    :root {
        --gap    : 8px;
        --radius : 10px;
        --surface: #161b2e;
        --surface2: #1e2438;
        --border : rgba(255,255,255,.07);
        --text   : #e2e8f0;
        --text-muted: #7D8590;
        --blue   : #3B82F6;
    }

    /* Wrapper */
    .dash-wrap {
        display: flex;
        flex-direction: column;
        gap: var(--gap);
        padding: var(--gap) 14px 10px;
        height: 100%;
        overflow: hidden;
        background: #0f1322;
    }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .filter-label {
        font-size: 10.5px;
        color: var(--text-muted);
        letter-spacing: .5px;
    }
    .filter-select {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        padding: 4px 9px;
        cursor: pointer;
        outline: none;
    }
    .filter-select:focus { border-color: var(--blue); }
    .filter-select option { background: var(--surface2); }

    /* Kpi Row */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--gap);
        flex-shrink: 0;
    }
    .kpi-card {
        position: relative;
        overflow: hidden;
        border-radius: var(--radius);
        padding: 11px 14px 9px;
        display: flex;
        flex-direction: column;
        gap: 2px;
        animation: fadeUp .5s ease both;
    }
    .kpi-card:nth-child(1) { animation-delay: .04s; }
    .kpi-card:nth-child(2) { animation-delay: .09s; }
    .kpi-card:nth-child(3) { animation-delay: .14s; }
    .kpi-card:nth-child(4) { animation-delay: .19s; }
    .kpi-pendapatan { background: linear-gradient(135deg,#1D4ED8,#3B82F6); }
    .kpi-belanja    { background: linear-gradient(135deg,#991B1B,#EF4444); }
    .kpi-surplus    { background: linear-gradient(135deg,#065F46,#10B981); }
    .kpi-margin     { background: linear-gradient(135deg,#0E7490,#06B6D4); }
    .kpi-label {
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: rgba(255,255,255,.68);
    }
    .kpi-value {
        font-size: 19px;
        font-weight: 700;
        color: #fff;
        line-height: 1.15;
        letter-spacing: -.4px;
    }
    .kpi-delta {
        font-size: 10px;
        color: rgba(255,255,255,.80);
        margin-top: 2px;
        display: flex;
        flex-direction: column;
        gap: 2px;
        width: 100%;
    }
    .kpi-delta-row { display: flex; align-items: center; gap: 4px; }
    .kpi-delta-bar { height: 3px; background: rgba(255,255,255,0.15); border-radius: 2px; overflow: hidden; width: 100%; }
    .kpi-delta-bar-inner { height: 100%; border-radius: 2px; background: rgba(255,255,255,0.55); transition: width .6s ease; }
    .kpi-bg-icon {
        position: absolute; right: 12px; bottom: 8px;
        width: 38px; height: 38px;
        color: rgba(255,255,255,.13);
        pointer-events: none;
    }

    /* Chart Grid (2x2) */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        grid-template-rows: 1fr 1fr;
        gap: var(--gap);
        flex: 1;
        min-height: 0;
        animation: fadeUp .6s ease .22s both;
    }
    .chart-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }
    .chart-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 9px 13px 6px;
        flex-shrink: 0;
        border-bottom: 1px solid var(--border);
    }
    .chart-title { font-size: 11.5px; font-weight: 600; color: var(--text); }
    .chart-sub   { font-size: 9.5px; color: var(--text-muted); margin-top: 2px; }
    .chart-body  { flex: 1; min-height: 0; padding: 7px 11px 6px; position: relative; }
    .legend-row  { display: flex; align-items: center; gap: 7px; font-size: 10px; color: var(--text-muted); flex-shrink: 0; }
    .legend-dot  { display: inline-block; width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* amCharts body */
    .amchart-body { flex: 1; min-height: 0; position: relative; }
    #chartTrendAm { width: 100%; height: 100%; }

    /* Unit Table */
    .unit-table-wrap {
        flex: 1; min-height: 0;
        display: flex; flex-direction: column;
        padding: 5px 11px 7px;
        overflow: hidden;
    }
    .unit-table-header {
        display: grid;
        grid-template-columns: 18px 1fr 88px 66px;
        gap: 6px;
        padding: 3px 4px 4px;
        border-bottom: 1px solid var(--border);
        font-size: 8.5px; font-weight: 600; letter-spacing: .7px;
        text-transform: uppercase; color: var(--text-muted);
        flex-shrink: 0;
    }
    .unit-table-body { flex: 1; min-height: 0; overflow: hidden; position: relative; }
    .unit-table-body::before,.unit-table-body::after {
        content:''; position: absolute; left:0; right:0; height:16px; z-index:2; pointer-events:none;
    }
    .unit-table-body::before { top:0; background: linear-gradient(to bottom, var(--surface), transparent); }
    .unit-table-body::after  { bottom:0; background: linear-gradient(to top, var(--surface), transparent); }
    .unit-scroll-track { display: flex; flex-direction: column; }
    .unit-scroll-track.looping { animation: unitScrollUp var(--unit-duration, 12s) linear infinite; }
    .unit-table-body:hover .unit-scroll-track { animation-play-state: paused; }
    
    @keyframes unitScrollUp {
        0%   { transform: translateY(0); }
        100% { transform: translateY(var(--unit-offset, -50%)); }
    }
    .unit-row {
        display: grid; grid-template-columns: 18px 1fr 88px 66px;
        gap: 6px; align-items: center;
        padding: 4px 4px; border-radius: 5px;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        transition: background .15s;
    }
    .unit-row:last-child { border-bottom: none; }
    .unit-row.rank-1 { background: rgba(251,191,36,0.06); }
    .unit-rank { font-size: 9px; font-weight: 700; color: var(--text-muted); text-align: center; }
    .unit-rank.top { color: #FBBF24; }
    .unit-name { font-size: 9.5px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unit-realisasi { font-size: 9px; font-weight: 600; font-family: 'DM Mono', monospace; color: var(--text); text-align: right; white-space: nowrap; }
    .unit-pct-cell { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
    .unit-pct-bar-wrap { width: 100%; height: 3px; background: rgba(255,255,255,0.07); border-radius: 2px; overflow: hidden; }
    .unit-pct-bar-fill { height: 100%; border-radius: 2px; transition: width .5s ease; }
    .unit-pct-label { font-size: 8.5px; font-weight: 700; font-family: 'DM Mono', monospace; }

    /* Rekap */
    .rekap-body {
        flex: 1; min-height: 0;
        display: flex; flex-direction: column;
        padding: 5px 11px 7px;
        overflow: hidden; gap: 0;
    }
    .rekap-insight {
        font-size: 9px; color: var(--text-muted);
        padding: 3px 7px;
        background: rgba(255,255,255,0.03);
        border-radius: 4px;
        border-left: 2px solid var(--blue);
        flex-shrink: 0; margin-bottom: 5px; line-height: 1.5;
    }
    .rekap-insight b { color: var(--text); font-weight: 600; }
    .rekap-tbl-header {
        display: grid; grid-template-columns: 28px 1fr 64px 64px 44px 16px;
        gap: 4px; padding: 3px 4px 4px;
        border-bottom: 1px solid var(--border);
        font-size: 8.5px; font-weight: 600; letter-spacing: .7px;
        text-transform: uppercase; color: var(--text-muted); flex-shrink: 0;
    }
    .rekap-rows { flex: 1; min-height: 0; overflow: hidden; position: relative; }
    .rekap-rows::before,.rekap-rows::after {
        content:''; position: absolute; left:0; right:0; height:14px; z-index:2; pointer-events:none;
    }
    .rekap-rows::before { top:0; background: linear-gradient(to bottom, var(--surface), transparent); }
    .rekap-rows::after  { bottom:0; background: linear-gradient(to top, var(--surface), transparent); }
    .rekap-scroll-track { display: flex; flex-direction: column; }
    .rekap-scroll-track.looping { animation: rekapScrollUp var(--rekap-duration, 14s) linear infinite; }
    .rekap-rows:hover .rekap-scroll-track { animation-play-state: paused; }
    @keyframes rekapScrollUp {
        0%   { transform: translateY(0); }
        100% { transform: translateY(var(--rekap-offset, -50%)); }
    }
    .rekap-row {
        display: grid; grid-template-columns: 28px 1fr 64px 64px 44px 16px;
        gap: 4px; align-items: center;
        padding: 3px 4px; border-bottom: 1px solid rgba(255,255,255,0.03); border-radius: 3px;
    }
    .rekap-row:last-child { border-bottom: none; }
    .rekap-row.is-now    { background: rgba(255,255,255,0.05); }
    .rekap-row.is-future { opacity: .28; }
    .rekap-bulan { font-size: 9.5px; color: var(--text); white-space: nowrap; }
    .rekap-bulan.bold { font-weight: 600; }
    .rekap-bar-cell { display: flex; flex-direction: column; gap: 2px; }
    .rekap-bar-track { height: 3px; background: rgba(255,255,255,0.06); border-radius: 2px; overflow: hidden; }
    .rekap-bar-fill { height: 100%; border-radius: 2px; transition: width .4s ease; }
    .rekap-amt { font-size: 9px; font-family: 'DM Mono', monospace; text-align: right; white-space: nowrap; }
    .net-pill {
        font-size: 8.5px; font-weight: 700; font-family: 'DM Mono', monospace;
        padding: 1px 3px; border-radius: 4px; text-align: right;
        display: block; white-space: nowrap;
    }
    .net-pos   { background: rgba(52,211,153,0.14);  color: #34D399; }
    .net-neg   { background: rgba(248,113,113,0.14); color: #F87171; }
    .net-empty { color: var(--text-muted); opacity: .35; }
    .rekap-trend { display: flex; justify-content: flex-end; align-items: center; }

    /* Empty State*/
    .empty-state {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 6px; color: var(--text-muted); font-size: 11px;
    }
    .empty-state svg { width: 26px; height: 26px; opacity: .3; }

    /* Animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="dash-wrap">

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <span class="filter-label">Tahun</span>
        <select id="tahunSelect" class="filter-select">
            @forelse($tahunList as $itemTahun)
                <option value="{{ $itemTahun }}" @selected((int)$itemTahun===(int)$tahun)>{{ $itemTahun }}</option>
            @empty
                <option value="{{ $tahun }}">{{ $tahun }}</option>
            @endforelse
        </select>

        <span class="filter-label" style="margin-left:6px">Bulan</span>
        <select id="bulanSelect" class="filter-select">
            @for($i=1;$i<=12;$i++)
                <option value="{{ $i }}" @selected($i===(int)now()->month)>
                    {{ DateTime::createFromFormat('!m',$i)->format('F') }}
                </option>
            @endfor
        </select>
    </div>

    {{-- KPI CARDS --}}
    <section class="kpi-row">

        <div class="kpi-card kpi-pendapatan">
            <div class="kpi-label">Pendapatan Realisasi</div>
            <div class="kpi-value" id="kpiPendapatan">Rp —</div>
            <div class="kpi-delta" id="kpiPendapatanMom">—</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60">
                <path d="M10 50 Q30 10 50 50" stroke="currentColor" stroke-width="2" fill="none"/>
                <circle cx="30" cy="25" r="8" fill="currentColor"/>
            </svg>
        </div>

        <div class="kpi-card kpi-belanja">
            <div class="kpi-label">Belanja Realisasi</div>
            <div class="kpi-value" id="kpiBelanja">Rp —</div>
            <div class="kpi-delta" id="kpiBelanjaMom">—</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60">
                <rect x="10" y="20" width="40" height="25" rx="4" stroke="currentColor" stroke-width="2" fill="none"/>
                <path d="M20 20v-5a10 10 0 0 1 20 0v5" stroke="currentColor" stroke-width="2" fill="none"/>
            </svg>
        </div>

        <div class="kpi-card kpi-surplus">
            <div class="kpi-label">Net (P − B)</div>
            <div class="kpi-value" id="kpiSurplus">Rp —</div>
            <div class="kpi-delta" id="kpiAvg">Rata-rata kinerja — %</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60">
                <path d="M15 45 L25 30 L35 38 L50 15" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <div class="kpi-card kpi-margin">
            <div class="kpi-label">Rata-rata Kinerja</div>
            <div class="kpi-value" id="kpiMargin">— %</div>
            <div class="kpi-delta" id="kpiMarginSub">Gabungan P &amp; B</div>
            <svg class="kpi-bg-icon" viewBox="0 0 60 60">
                <circle cx="30" cy="30" r="20" stroke="currentColor" stroke-width="2" fill="none"/>
                <path d="M30 10 A20 20 0 0 1 50 30" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
            </svg>
        </div>

    </section>

    {{-- CHARTS 2×2 GRID --}}
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
            <div class="amchart-body">
                <div id="chartTrendAm"></div>
            </div>
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
            <div class="chart-body" style="position:relative">
                <canvas id="chartHarian"></canvas>
                <div class="empty-state" id="emptyHarian" style="display:none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Belum ada data harian untuk bulan ini</span>
                </div>
            </div>
        </div>

        {{-- Realisasi per Unit --}}
        <div class="chart-card" style="grid-column:2;grid-row:1">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Realisasi per Unit / Divisi</h2>
                    <p class="chart-sub">Proporsi belanja bulan <span id="unitBulanLabel">—</span></p>
                </div>
                <div style="font-size:8.5px;color:var(--text-muted);text-align:right;line-height:1.5">
                    Total<br>
                    <span id="unitTotalBulan" style="font-size:10.5px;font-weight:700;color:var(--text);font-family:'DM Mono',monospace">—</span>
                </div>
            </div>
            <div class="unit-table-wrap" id="unitTableWrap">
                <div class="unit-table-header">
                    <div>#</div>
                    <div>Unit / Divisi</div>
                    <div style="text-align:right">Realisasi</div>
                    <div style="text-align:right">Proporsi</div>
                </div>
                <div class="unit-table-body" id="unitTableBody"></div>
            </div>
            <div class="empty-state" id="emptyUnit" style="display:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
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
                    <div>Bln</div>
                    <div></div>
                    <div style="text-align:right;color:#2DD4BF">Pndptn</div>
                    <div style="text-align:right;color:#FBBF24">Blnja</div>
                    <div style="text-align:right">Net</div>
                    <div></div>
                </div>
                <div class="rekap-rows" id="rekapRows"></div>
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let chartHarian = null;
    let amRoot      = null;

    const idrShort = v => {
        v = Number(v || 0);
        const neg = v < 0, abs = Math.abs(v);
        let str;
        if      (abs >= 1e9) str = `Rp ${(abs/1e9).toFixed(1).replace('.',',')} M`;
        else if (abs >= 1e6) str = `Rp ${(abs/1e6).toFixed(1).replace('.',',')} Jt`;
        else if (abs >= 1e3) str = `Rp ${(abs/1e3).toFixed(0)} Rb`;
        else                 str = `Rp ${abs}`;
        return neg ? `- ${str}` : str;
    };
    const idrShortNoPrefix = v => {
        v = Number(v || 0);
        const neg = v < 0, abs = Math.abs(v);
        let str;
        if      (abs >= 1e9) str = `${(abs/1e9).toFixed(1).replace('.',',')} M`;
        else if (abs >= 1e6) str = `${(abs/1e6).toFixed(1).replace('.',',')} Jt`;
        else if (abs >= 1e3) str = `${(abs/1e3).toFixed(0)} Rb`;
        else                 str = `${abs}`;
        return neg ? `- ${str}` : `+${str}`;
    };
    const idrAxisAm = v => {
        v = Number(v || 0);
        if (v === 0) return "";
        if (v >= 1e9) return (v/1e9).toFixed(1).replace('.', ',')+' M';
        if (v >= 1e6) return (v/1e6).toFixed(1).replace('.', ',')+' Jt';
        if (v >= 1e3) return (v/1e3).toFixed(0)+' Rb';
        return String(v);
    };
    const idrAxis = v => {
        const abs = Math.abs(v);
        if (abs >= 1e9) return (v/1e9).toFixed(1).replace('.',',') + ' M';
        if (abs >= 1e6) return (v/1e6).toFixed(1).replace('.',',') + ' Jt';
        if (abs >= 1e3) return (v/1e3).toFixed(0) + ' Rb';
        return String(v);
    };

    const pct     = (r, t) => t > 0 ? (r/t)*100 : 0;
    const setText = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };
    const show    = id => { const e = document.getElementById(id); if (e) e.style.display = 'flex'; };
    const hide    = id => { const e = document.getElementById(id); if (e) e.style.display = 'none'; };

    const bulanNama  = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const bulanShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    Chart.defaults.color       = '#7D8590';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
    Chart.defaults.font.family = 'DM Sans';

    const tt = {
        backgroundColor : '#1E2430',
        borderColor     : 'rgba(255,255,255,0.1)',
        borderWidth     : 1,
        titleColor      : '#E6EDF3',
        bodyColor       : '#7D8590',
        padding         : 10,
    };

    function momDelta(now, last, elId) {
        const el = document.getElementById(elId);
        if (!el) return;
        if (!last || last === 0) {
            el.innerHTML = `<div class="kpi-delta-row"><span style="font-size:9px;opacity:.5">— data bulan lalu belum tersedia</span></div>`;
            return;
        }
        const delta  = now - last;
        const pctVal = (delta / last) * 100;
        const naik   = delta >= 0;
        const barPct = Math.min(100, (now / Math.max(now, last)) * 100).toFixed(1);
        const ikonNaik  = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.95)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>`;
        const ikonTurun = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.95)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`;
        el.innerHTML = `
            <div class="kpi-delta-row">
                ${naik ? ikonNaik : ikonTurun}
                <span style="font-size:10.5px;font-weight:600">${Math.abs(pctVal).toFixed(1).replace('.',',')}%</span>
                <span style="font-size:9px;opacity:.65">vs bulan lalu (${idrShort(last)})</span>
            </div>
            <div class="kpi-delta-bar">
                <div class="kpi-delta-bar-inner" style="width:${barPct}%"></div>
            </div>
        `;
    }

    function renderRekap(pRows, bRows, tahun) {
        setText('rekapTahunLabel', tahun);
        const totalP  = pRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
        const totalB  = bRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
        const active  = pRows.filter(r => Number(r.realisasi || 0) > 0);
        const avgP    = active.length ? totalP / active.length : 0;
        const bestRow = pRows.reduce((a, r) => Number(r.realisasi||0) > Number(a.realisasi||0) ? r : a, pRows[0] || {});
        const defBulan = pRows.filter((r, i) => {
            const p = Number(r.realisasi || 0);
            const b = Number(bRows[i]?.realisasi || 0);
            return p > 0 && b > p;
        });
        const ratio = totalP > 0 ? Math.round((totalB / totalP) * 100) : 0;
        const parts = [];
        if (bestRow?.label && Number(bestRow.realisasi||0) > 0)
            parts.push(`Tertinggi <b>${bestRow.label}</b> (${idrShort(bestRow.realisasi)})`);
        if (avgP > 0)
            parts.push(`rata-rata <b>${idrShort(Math.round(avgP))}/bln</b>`);
        if (defBulan.length > 0)
            parts.push(`<b>${defBulan.length} bln</b> defisit`);
        else if (active.length > 0)
            parts.push(`semua bln aktif <b>surplus</b>`);
        if (ratio > 0)
            parts.push(`rasio belanja <b>${ratio}%</b>`);
        const insightEl = document.getElementById('rekapInsight');
        if (insightEl) insightEl.innerHTML = parts.join(' · ');

        const maxP      = Math.max(...pRows.map(r => Number(r.realisasi || 0)), 1);
        const maxB      = Math.max(...bRows.map(r => Number(r.realisasi || 0)), 1);
        const bulanSkrg = parseInt(document.getElementById('bulanSelect').value) || 0;
        const container = document.getElementById('rekapRows');
        if (!container) return;

        const trendIcon = (cur, prev) => {
            if (!prev) return '';
            if (cur > prev) return `<svg viewBox="0 0 12 12" fill="none" style="width:12px;height:12px"><path d="M2 9L6 3l4 6" stroke="#34D399" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
            if (cur < prev) return `<svg viewBox="0 0 12 12" fill="none" style="width:12px;height:12px"><path d="M2 3l4 6 4-6" stroke="#F87171" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
            return `<svg viewBox="0 0 12 12" fill="none" style="width:12px;height:12px"><path d="M2 6h8" stroke="rgba(255,255,255,.25)" stroke-width="1.5" stroke-linecap="round"/></svg>`;
        };

        const buildRows = () => {
            let html = '';
            for (let i = 0; i < 12; i++) {
                const pr  = pRows[i] || {}, br = bRows[i] || {};
                const p   = Number(pr.realisasi || 0), b = Number(br.realisasi || 0);
                const net = p - b;
                const isNow    = (i + 1) === bulanSkrg;
                const isFuture = p === 0 && b === 0 && (i + 1) > bulanSkrg;
                const hasData  = p > 0 || b > 0;
                const label    = pr.label || bulanShort[i];
                const pW  = p > 0 ? ((p / maxP) * 100).toFixed(1) : 0;
                const bW  = b > 0 ? ((b / maxB) * 100).toFixed(1) : 0;
                const prevP = i > 0 ? Number(pRows[i-1]?.realisasi || 0) : 0;
                const trend = hasData ? trendIcon(p, prevP) : '';
                const netCls = !hasData ? 'net-empty' : net >= 0 ? 'net-pos' : 'net-neg';
                const netTxt = !hasData ? '—' : (net >= 0 ? '+' : '-') + idrShortNoPrefix(Math.abs(net)).replace('+','').replace('-','');
                html += `
                    <div class="rekap-row${isNow ? ' is-now' : ''}${isFuture ? ' is-future' : ''}">
                        <div class="rekap-bulan${isNow ? ' bold' : ''}">${label}${isNow ? '◀' : ''}</div>
                        <div class="rekap-bar-cell">
                            <div class="rekap-bar-track"><div class="rekap-bar-fill" style="width:${pW}%;background:#2DD4BF"></div></div>
                            <div class="rekap-bar-track"><div class="rekap-bar-fill" style="width:${bW}%;background:#FBBF24"></div></div>
                        </div>
                        <div class="rekap-amt" style="color:#2DD4BF">${p > 0 ? idrShort(p) : '—'}</div>
                        <div class="rekap-amt" style="color:#FBBF24">${b > 0 ? idrShort(b) : '—'}</div>
                        <div style="text-align:right"><span class="net-pill ${netCls}">${netTxt}</span></div>
                        <div class="rekap-trend">${trend}</div>
                    </div>`;
            }
            return html;
        };

        const ROW_HEIGHT  = 30;
        const containerH  = container.clientHeight || 200;
        const visibleRows = Math.floor(containerH / ROW_HEIGHT);
        const needsScroll = 12 > visibleRows;
        if (needsScroll) {
            const duration = Math.max(10, 12 * 2.5);
            container.innerHTML = `<div class="rekap-scroll-track looping" style="--rekap-duration:${duration}s;--rekap-offset:-50%;">${buildRows()}${buildRows()}</div>`;
        } else {
            container.innerHTML = `<div class="rekap-scroll-track" style="height:100%;display:flex;flex-direction:column;justify-content:space-between;">${buildRows()}</div>`;
        }
    }

    function renderTrendAmCharts(labels, pendapatan, belanja) {
        if (amRoot) { amRoot.dispose(); amRoot = null; }
        const am5 = window.am5, am5xy = window.am5xy;
        const rawP = pendapatan.map(v => Number(v || 0));
        const rawB = belanja.map(v => Number(v || 0));
        const data = labels.map((label, i) => ({ label, pendapatan: rawP[i], belanja: rawB[i], rawP: rawP[i], rawB: rawB[i] }));

        const root  = am5.Root.new('chartTrendAm');
        amRoot = root;
        root.setThemes([window.am5themes_Animated.new(root)]);
        root._logo?.dispose();

        const chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false, panY: false, wheelX: 'none', wheelY: 'none',
            layout: root.verticalLayout,
            paddingTop: 4, paddingRight: 10, paddingBottom: 0, paddingLeft: 0,
        }));
        chart.plotContainer.set('background', am5.Rectangle.new(root, { fill: am5.color(0x000000), fillOpacity: 0 }));

        const xRenderer = am5xy.AxisRendererX.new(root, { minGridDistance: 28, cellStartLocation: 0.1, cellEndLocation: 0.9 });
        xRenderer.grid.template.set('visible', false);
        xRenderer.labels.template.setAll({ fill: am5.color(0x7D8590), fontSize: 10, fontFamily: 'DM Sans' });
        const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, { categoryField: 'label', renderer: xRenderer }));
        xAxis.data.setAll(data);

        const yRenderer = am5xy.AxisRendererY.new(root, { inside: false });
        yRenderer.grid.template.setAll({ stroke: am5.color(0xffffff), strokeOpacity: 0.05, strokeDasharray: [2,3] });
        yRenderer.labels.template.setAll({ fill: am5.color(0x7D8590), fontSize: 9, fontFamily: 'DM Mono, monospace' });
        const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: yRenderer, min: 0, strictMinMax: true, maxDeviation: 0.05, extraMax: 0.18,
            numberFormatter: am5.NumberFormatter.new(root, {
                numberFormat: "#a",
                bigNumberPrefixes: [
                    { number: 1e3, suffix: " Rb" }, { number: 1e6, suffix: " Jt" },
                    { number: 1e9, suffix: " M"  }, { number: 1e12, suffix: " T" },
                ],
                smallNumberPrefixes: [],
            }),
        }));

        const makeTooltip = () => {
            const tp = am5.Tooltip.new(root, { getFillFromSprite: false, autoTextColor: false, pointerOrientation: 'vertical' });
            tp.get('background').setAll({ fill: am5.color(0x1E2430), stroke: am5.color(0xffffff), strokeOpacity: 0.10, cornerRadiusBL: 6, cornerRadiusBR: 6, cornerRadiusTL: 6, cornerRadiusTR: 6 });
            tp.label.setAll({ fill: am5.color(0xE6EDF3), fontSize: 11, fontFamily: 'DM Sans' });
            return tp;
        };

        const seriesP = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: 'Pendapatan', xAxis, yAxis, valueYField: 'pendapatan', categoryXField: 'label',
            clustered: false, tooltip: makeTooltip(),
        }));
        seriesP.columns.template.setAll({
            width: am5.percent(80), cornerRadiusTL: 5, cornerRadiusTR: 5, cornerRadiusBL: 0, cornerRadiusBR: 0,
            fillGradient: am5.LinearGradient.new(root, { stops: [{ color: am5.color(0x2DD4BF), opacity: 0.92 }, { color: am5.color(0x0D9488), opacity: 0.58 }], rotation: 90 }),
            strokeOpacity: 0,
        });
        seriesP.columns.template.adapters.add('tooltipText', (text, target) => {
            const d = target.dataItem?.dataContext;
            if (!d) return text;
            return [`[bold #E6EDF3]${d.label}[/]`, `[#7D8590]Pendapatan:[/] [#2DD4BF]${idrShort(d.pendapatan)}[/]`].join('\n');
        });
        seriesP.data.setAll(data);
        seriesP.bullets.push(function () {
            const label = am5.Label.new(root, { fill: am5.color(0xffffff), fontSize: 9, fontFamily: 'DM Mono, monospace', centerX: am5.percent(50), centerY: am5.percent(50), populateText: true, text: "" });
            label.adapters.add("text", function (text, target) {
                const d = target.dataItem?.dataContext;
                return (!d || !d.rawP) ? "" : idrAxisAm(d.rawP);
            });
            return am5.Bullet.new(root, { locationY: 0.5, sprite: label });
        });

        const seriesB = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: 'Belanja', xAxis, yAxis, valueYField: 'belanja', categoryXField: 'label',
            clustered: false, tooltip: makeTooltip(),
        }));
        seriesB.columns.template.setAll({
            width: am5.percent(42), cornerRadiusTL: 4, cornerRadiusTR: 4, cornerRadiusBL: 0, cornerRadiusBR: 0,
            fillGradient: am5.LinearGradient.new(root, { stops: [{ color: am5.color(0xFBBF24), opacity: 0.92 }, { color: am5.color(0xB45309), opacity: 0.58 }], rotation: 90 }),
            strokeOpacity: 0,
        });
        seriesB.columns.template.adapters.add('tooltipText', (text, target) => {
            const d = target.dataItem?.dataContext;
            if (!d) return text;
            return [`[bold #E6EDF3]${d.label}[/]`, `[#7D8590]Belanja :[/] [#FBBF24]${idrShort(d.belanja)}[/]`, `[#7D8590]Net     :[/] [#34D399]${idrShort(d.pendapatan - d.belanja)}[/]`].join('\n');
        });
        seriesB.data.setAll(data);
        seriesB.bullets.push(function () {
            const label = am5.Label.new(root, { fill: am5.color(0xFBBF24), fontSize: 9, fontFamily: 'DM Mono, monospace', centerX: am5.percent(50), centerY: am5.percent(100), dy: -4, populateText: true, text: "" });
            label.adapters.add("text", function (text, target) {
                const d = target.dataItem?.dataContext;
                return (!d || !d.rawB) ? "" : idrAxisAm(d.rawB);
            });
            return am5.Bullet.new(root, { locationY: 1, sprite: label });
        });

        chart.set('cursor', am5xy.XYCursor.new(root, { behavior: 'none', xAxis }));
        chart.get('cursor').lineY.set('visible', false);
        chart.get('cursor').lineX.setAll({ stroke: am5.color(0xffffff), strokeOpacity: 0.15, strokeDasharray: [3,4] });
        seriesB.appear(1000, 100);
        seriesP.appear(1000, 200);
        chart.appear(1000, 100);
    }

    function renderHarian(json) {
        setText('harianBulanLabel', bulanNama[(json.bulan || 1) - 1]);
        setText('harianTahunLabel', json.tahun);
        hide('emptyHarian');
        const ctx = document.getElementById('chartHarian').getContext('2d');
        if (chartHarian) chartHarian.destroy();
        const dataP = json.hari.map(h => h.pendapatan || 0);
        const dataB = json.hari.map(h => h.belanja    || 0);
        const validB  = dataB.filter(v => v > 0);
        const sortedB = [...validB].sort((a,b) => a - b);
        const p90B    = sortedB[Math.floor(sortedB.length * 0.9)] || Math.max(...validB, 1);
        const minB    = validB.length ? Math.min(...validB) * 0.80 : 0;
        const maxB    = p90B * 1.35;

        const datalabelPlugin = {
            id: 'customLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const occupied = [];
                const isTooClose = (x, y) => occupied.some(pos => Math.abs(pos.x - x) < 38 && Math.abs(pos.y - y) < 14);
                chart.data.datasets.forEach((dataset, di) => {
                    const meta  = chart.getDatasetMeta(di);
                    const isBar = dataset.type === 'bar';
                    const indexed = dataset.data.map((val, i) => ({ val, i, el: meta.data[i] })).filter(d => d.val > 0).sort((a, b) => b.val - a.val);
                    indexed.forEach(({ val, i, el }) => {
                        if (!el) return;
                        const abs = Math.abs(val);
                        let label;
                        if      (abs >= 1e9) label = (val/1e9).toFixed(1).replace('.',',') + ' M';
                        else if (abs >= 1e6) label = (val/1e6).toFixed(1).replace('.',',') + ' Jt';
                        else if (abs >= 1e3) label = (val/1e3).toFixed(0) + ' Rb';
                        else                 label = String(val);
                        const cp = el.getCenterPoint ? el.getCenterPoint() : el;
                        const x = cp.x;
                        let y = isBar ? el.y - 5 : cp.y - 8;
                        if (isTooClose(x, y)) y = isBar ? el.y - 5 : cp.y + 14;
                        if (isTooClose(x, y)) return;
                        occupied.push({ x, y });
                        ctx.save();
                        ctx.font = '600 8px DM Mono, monospace';
                        ctx.textAlign = 'center'; ctx.textBaseline = 'bottom';
                        const tw = ctx.measureText(label).width;
                        ctx.fillStyle = 'rgba(15,20,30,0.55)';
                        ctx.fillRect(x - tw/2 - 2, y - 10, tw + 4, 11);
                        ctx.fillStyle = isBar ? '#FBBF24' : '#34D399';
                        ctx.fillText(label, x, y);
                        ctx.restore();
                    });
                });
            }
        };

        chartHarian = new Chart(ctx, {
            plugins: [datalabelPlugin],
            data: {
                labels: json.hari.map(h => h.label),
                datasets: [
                    {
                        type: 'bar', label: 'Belanja', data: dataB,
                        backgroundColor: dataB.map(v => v > 0 ? 'rgba(251,191,36,0.75)' : 'transparent'),
                        borderColor:     dataB.map(v => v > 0 ? '#FBBF24' : 'transparent'),
                        borderWidth: 1, borderRadius: 3, borderSkipped: false,
                        yAxisID: 'y1', order: 2,
                    },
                    {
                        type: 'line', label: 'Pendapatan', data: dataP,
                        borderColor: '#34D399', backgroundColor: 'rgba(52,211,153,0.08)',
                        borderWidth: 2.5,
                        pointRadius: dataP.map(v => v > 0 ? 4 : 2),
                        pointHoverRadius: 6,
                        pointBackgroundColor: dataP.map(v => v > 0 ? '#34D399' : 'rgba(52,211,153,0.3)'),
                        tension: 0.35, fill: true, yAxisID: 'y', order: 1,
                    },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, animation: { duration: 500 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { ...tt,
                        callbacks: {
                            title: items => `Hari ke-${items[0].label}`,
                            label: c => {
                                const val = c.parsed.y;
                                if (val === 0 && c.dataset.type === 'bar') return null;
                                return ` ${c.dataset.label}: ${idrShort(val)}`;
                            },
                            afterBody: items => {
                                const i = items[0].dataIndex, p = dataP[i] || 0, b = dataB[i] || 0, net = p - b;
                                if (p === 0 && b === 0) return [];
                                return [``, ` Net: ${net >= 0 ? '+' : ''}${idrShort(net)}`];
                            },
                        }
                    }
                },
                scales: {
                    x: { ticks: { font: { size: 9 }, color: '#7D8590' }, grid: { display: false }, border: { display: false } },
                    y: { type: 'logarithmic', position: 'left', ticks: { font: { size: 9, family: 'DM Mono' }, color: '#34D399', callback: v => idrAxis(v), maxTicksLimit: 5 }, grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false } },
                    y1: { type: 'linear', position: 'right', min: minB, max: maxB, ticks: { font: { size: 9, family: 'DM Mono' }, color: '#FBBF24', callback: v => idrAxis(v), maxTicksLimit: 5 }, grid: { drawOnChartArea: false }, border: { display: false } },
                }
            }
        });
    }

    function renderUnit(units, bulan, totalBulan) {
        setText('unitBulanLabel', bulanNama[(bulan || 1) - 1]);
        const wrap = document.getElementById('unitTableWrap');
        const body = document.getElementById('unitTableBody');
        if (!units || units.length === 0) {
            if (wrap) wrap.style.display = 'none';
            show('emptyUnit'); return;
        }
        hide('emptyUnit');
        if (wrap) wrap.style.display = 'flex';
        const total = totalBulan || units.reduce((a, u) => a + Number(u.realisasi || 0), 0);
        setText('unitTotalBulan', idrShort(total));
        const palette = ['#FBBF24','#60A5FA','#F87171','#A78BFA','#22D3EE','#FB923C','#34D399','#E879F9','#F472B6','#94A3B8'];
        const buildRows = () => units.map((u, i) => {
            const rank = i + 1, real = Number(u.realisasi || 0), pctV = Number(u.pct_dari_total || 0);
            const color = palette[i % palette.length], isTop = rank === 1;
            return `<div class="unit-row${isTop ? ' rank-1' : ''}">
                <div class="unit-rank${isTop ? ' top' : ''}">${rank}</div>
                <div class="unit-name" title="${u.unit}">${u.unit || '—'}</div>
                <div class="unit-realisasi">${idrShort(real)}</div>
                <div class="unit-pct-cell">
                    <div class="unit-pct-bar-wrap"><div class="unit-pct-bar-fill" style="width:${pctV}%;background:${color}"></div></div>
                    <div class="unit-pct-label" style="color:${color}">${pctV.toFixed(1).replace('.',',')}%</div>
                </div>
            </div>`;
        }).join('');
        const ROW_HEIGHT = 34, containerH = body.clientHeight || 200;
        const visibleRows = Math.floor(containerH / ROW_HEIGHT);
        const needsScroll = units.length > visibleRows;
        if (needsScroll) {
            const duration = Math.max(8, units.length * 2), totalPx = units.length * ROW_HEIGHT;
            body.innerHTML = `<div class="unit-scroll-track looping" style="--unit-duration:${duration}s;--unit-offset:-${totalPx}px;">${buildRows()}${buildRows()}</div>`;
        } else {
            body.innerHTML = `<div class="unit-scroll-track" style="height:100%;justify-content:space-between;display:flex;flex-direction:column;">${buildRows()}</div>`;
        }
    }

    let isLoading = false, abortCtrl = null;

    async function loadDashboard() {
        if (isLoading) return;
        isLoading = true;
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();

        const year  = document.getElementById('tahunSelect').value;
        const month = document.getElementById('bulanSelect').value;
        const opts  = { cache: 'no-store', signal: abortCtrl.signal, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };

        setText('trendYearLabel', year);

        try {
            const [rTrend, rHarian, rUnit] = await Promise.all([
                fetch(`/api/dashboard-trend?tahun=${year}`, opts),
                fetch(`/api/dashboard-harian?tahun=${year}&bulan=${month}`, opts),
                fetch(`/api/dashboard-unit?tahun=${year}&bulan=${month}`, opts),
            ]);
            const [jTrend, jHarian, jUnit] = await Promise.all([
                rTrend.ok  ? rTrend.json()  : null,
                rHarian.ok ? rHarian.json() : null,
                rUnit.ok   ? rUnit.json()   : null,
            ]);

            if (jTrend) {
                const pRows = Array.isArray(jTrend.pendapatan) ? jTrend.pendapatan : [];
                const bRows = Array.isArray(jTrend.belanja)    ? jTrend.belanja    : [];
                const totalPReal   = pRows.reduce((a,r) => a + Number(r.realisasi||0), 0);
                const totalBReal   = bRows.reduce((a,r) => a + Number(r.realisasi||0), 0);
                const totalPTarget = pRows.reduce((a,r) => a + Number(r.target||0), 0);
                const totalBTarget = bRows.reduce((a,r) => a + Number(r.target||0), 0);
                const pPct = pct(totalPReal, totalPTarget);
                const bPct = pct(totalBReal, totalBTarget);
                const avg  = (pPct + bPct) / 2;

                setText('kpiPendapatan', idrShort(totalPReal));
                setText('kpiBelanja',    idrShort(totalBReal));
                setText('kpiSurplus',    idrShort(totalPReal - totalBReal));
                setText('kpiAvg',        `Rata-rata kinerja ${avg.toFixed(1).replace('.',',')}%`);
                setText('kpiMargin',     `${avg.toFixed(1).replace('.',',')}%`);
                setText('kpiMarginSub',  `P ${pPct.toFixed(1)}% | B ${bPct.toFixed(1)}%`);

                if (jTrend.mom) {
                    momDelta(jTrend.mom.pendapatan_bulan_ini, jTrend.mom.pendapatan_bulan_lalu, 'kpiPendapatanMom');
                    momDelta(jTrend.mom.belanja_bulan_ini,    jTrend.mom.belanja_bulan_lalu,    'kpiBelanjaMom');
                }

                const labels = pRows.map((r,i) => r.label || bulanShort[i] || `Bln ${i+1}`);
                renderTrendAmCharts(labels, pRows.map(r => Number(r.realisasi||0)), bRows.map(r => Number(r.realisasi||0)));
                renderRekap(pRows, bRows, year);
            }

            if (jHarian && Array.isArray(jHarian.hari) && jHarian.hari.length > 0) {
                renderHarian(jHarian);
            } else {
                if (chartHarian) { chartHarian.destroy(); chartHarian = null; }
                setText('harianBulanLabel', bulanNama[(parseInt(month)||1) - 1]);
                show('emptyHarian');
            }

            if (jUnit) renderUnit(jUnit.units || [], parseInt(month), jUnit.total_bulan || 0);

        } catch (err) {
            if (err.name !== 'AbortError') console.error('[Dashboard] fetch error:', err.message);
        } finally {
            isLoading = false;
        }
    }

    async function loadSilent() {
        const year  = document.getElementById('tahunSelect').value;
        const month = document.getElementById('bulanSelect').value;
        const opts  = { cache: 'no-store', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
        try {
            const [rTrend, rHarian, rUnit] = await Promise.all([
                fetch(`/api/dashboard-trend?tahun=${year}`, opts),
                fetch(`/api/dashboard-harian?tahun=${year}&bulan=${month}`, opts),
                fetch(`/api/dashboard-unit?tahun=${year}&bulan=${month}`, opts),
            ]);
            const [jTrend, jHarian, jUnit] = await Promise.all([
                rTrend.ok  ? rTrend.json()  : null,
                rHarian.ok ? rHarian.json() : null,
                rUnit.ok   ? rUnit.json()   : null,
            ]);
            if (jTrend) {
                const pRows = Array.isArray(jTrend.pendapatan) ? jTrend.pendapatan : [];
                const bRows = Array.isArray(jTrend.belanja)    ? jTrend.belanja    : [];
                const totalPReal   = pRows.reduce((a,r) => a + Number(r.realisasi||0), 0);
                const totalBReal   = bRows.reduce((a,r) => a + Number(r.realisasi||0), 0);
                const totalPTarget = pRows.reduce((a,r) => a + Number(r.target||0), 0);
                const totalBTarget = bRows.reduce((a,r) => a + Number(r.target||0), 0);
                const pPct = pct(totalPReal, totalPTarget), bPct = pct(totalBReal, totalBTarget), avg = (pPct + bPct) / 2;
                setText('kpiPendapatan', idrShort(totalPReal));
                setText('kpiBelanja',    idrShort(totalBReal));
                setText('kpiSurplus',    idrShort(totalPReal - totalBReal));
                setText('kpiAvg',        `Rata-rata kinerja ${avg.toFixed(1).replace('.',',')}%`);
                setText('kpiMargin',     `${avg.toFixed(1).replace('.',',')}%`);
                setText('kpiMarginSub',  `P ${pPct.toFixed(1)}% | B ${bPct.toFixed(1)}%`);
                if (jTrend.mom) {
                    momDelta(jTrend.mom.pendapatan_bulan_ini, jTrend.mom.pendapatan_bulan_lalu, 'kpiPendapatanMom');
                    momDelta(jTrend.mom.belanja_bulan_ini,    jTrend.mom.belanja_bulan_lalu,    'kpiBelanjaMom');
                }
                if (amRoot) {
                    const rawP = pRows.map(r => Number(r.realisasi||0)), rawB = bRows.map(r => Number(r.realisasi||0));
                    const lbls = pRows.map((r,i) => r.label || bulanShort[i] || `Bln ${i+1}`);
                    const newData = lbls.map((label, i) => ({ label, pendapatan: rawP[i], belanja: rawB[i], rawP: rawP[i], rawB: rawB[i] }));
                    const chart = amRoot.container.children.getIndex(0);
                    if (chart) { chart.series.each(s => s.data.setAll(newData)); chart.xAxes.getIndex(0)?.data.setAll(newData); }
                }
                renderRekap(pRows, bRows, year);
            }
            if (jUnit) renderUnit(jUnit.units || [], parseInt(month), jUnit.total_bulan || 0);
        } catch (err) {
            if (err.name !== 'AbortError') console.error('[Silent] error:', err.message);
        }
    }

    document.getElementById('tahunSelect').addEventListener('change', loadDashboard);
    document.getElementById('bulanSelect').addEventListener('change', loadDashboard);

    loadDashboard();
    setInterval(loadSilent, 300_000);
});
</script>
@endpush
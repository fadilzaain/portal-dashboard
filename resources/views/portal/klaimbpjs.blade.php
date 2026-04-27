@extends('layouts.app')

@section('title', 'Klaim BPJS')
@section('page_title', 'Klaim BPJS')
@section('page_subtitle', 'Data Pengajuan & Pembayaran')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root {
        --bpjs-teal:    #14b8a6;
        --bpjs-green:   #22c55e;
        --bpjs-amber:   #f59e0b;
        --bpjs-red:     #ef4444;
        --bpjs-blue:    #3b82f6;
        --bpjs-indigo:  #6366f1;
        --card-radius:  14px;
        --shadow-sm:    0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
        --shadow-md:    0 4px 24px rgba(0,0,0,.08);
    }

    .period-bar {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .period-btn {
        font-family: 'DM Mono', monospace;
        font-size: .72rem;
        font-weight: 500;
        padding: .42rem .9rem;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        background: white;
        color: #64748b;
        cursor: pointer;
        transition: all .15s;
    }
    .period-btn:hover { border-color: var(--bpjs-teal); color: var(--bpjs-teal); }
    .period-btn.active {
        background: var(--bpjs-teal);
        border-color: var(--bpjs-teal);
        color: white;
        box-shadow: 0 2px 8px rgba(20,184,166,.3);
    }
    .period-divider { width: 1px; height: 24px; background: #e2e8f0; }

    .date-input {
        font-family: 'DM Mono', monospace;
        font-size: .72rem;
        padding: .42rem .75rem;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        background: white;
        cursor: pointer;
        outline: none;
        transition: border-color .15s;
    }
    .date-input:focus { border-color: var(--bpjs-teal); }

    .filter-apply-btn {
        font-size: .76rem;
        font-weight: 600;
        padding: .42rem 1.1rem;
        border-radius: 8px;
        background: var(--bpjs-teal);
        color: white;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
    }
    .filter-apply-btn:hover { opacity: .85; }

    /* 5 stat cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
    }
    @media (max-width: 1400px) { .stat-grid { grid-template-columns: repeat(3,1fr); } }
    @media (max-width: 900px)  { .stat-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 640px)  { .stat-grid { grid-template-columns: 1fr; } }

    .stat-card {
        background: white;
        border-radius: var(--card-radius);
        padding: 1.25rem 1.4rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: .6rem;
        position: relative;
        overflow: hidden;
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--card-radius) var(--card-radius) 0 0;
    }
    .stat-card.teal::before   { background: var(--bpjs-teal); }
    .stat-card.green::before  { background: var(--bpjs-green); }
    .stat-card.amber::before  { background: var(--bpjs-amber); }
    .stat-card.red::before    { background: var(--bpjs-red); }
    .stat-card.indigo::before { background: var(--bpjs-indigo); }

    .stat-header { display: flex; align-items: center; justify-content: space-between; }
    .stat-label {
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .stat-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon.teal   { background: rgba(20,184,166,.1);  color: var(--bpjs-teal); }
    .stat-icon.green  { background: rgba(34,197,94,.1);   color: var(--bpjs-green); }
    .stat-icon.amber  { background: rgba(245,158,11,.1);  color: var(--bpjs-amber); }
    .stat-icon.red    { background: rgba(239,68,68,.1);   color: var(--bpjs-red); }
    .stat-icon.indigo { background: rgba(99,102,241,.1);  color: var(--bpjs-indigo); }

    .stat-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -.03em;
        line-height: 1;
        font-family: 'Sora', sans-serif;
    }
    .stat-sub { font-family: 'DM Mono', monospace; font-size: .68rem; color: #64748b; }
    .stat-rupiah { font-size: .82rem; font-weight: 600; color: #334155; font-family: 'DM Mono', monospace; }
    .stat-delta {
        font-size: .68rem;
        font-weight: 600;
        font-family: 'DM Mono', monospace;
        padding: .15rem .45rem;
        border-radius: 6px;
    }
    .stat-delta.up   { background: rgba(34,197,94,.1);  color: var(--bpjs-green); }
    .stat-delta.down { background: rgba(239,68,68,.1);  color: var(--bpjs-red); }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1rem;
    }
    @media (max-width: 1100px) { .charts-grid { grid-template-columns: 1fr; } }

    .chart-card {
        background: white;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid #f1f5f9;
        padding: 1.4rem 1.6rem;
    }
    .chart-card-title {
        font-size: .85rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .chart-card-title span { font-size: .7rem; font-weight: 500; color: #94a3b8; font-family: 'DM Mono', monospace; }

    .bar-chart-wrap { position: relative; height: 220px; }
    canvas { width: 100% !important; }

    .donut-wrap { display: flex; flex-direction: column; align-items: center; gap: 1.25rem; }
    .donut-canvas-wrap { position: relative; width: 180px; height: 180px; }
    .donut-center {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        pointer-events: none;
    }
    .donut-center-num { font-size: 1.5rem; font-weight: 800; color: #0f172a; font-family: 'Sora', sans-serif; line-height: 1; }
    .donut-center-label { font-size: .6rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; margin-top: 2px; }
    .legend-list { width: 100%; display: flex; flex-direction: column; gap: .55rem; }
    .legend-item { display: flex; align-items: center; justify-content: space-between; font-size: .76rem; }
    .legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .legend-name { color: #475569; font-weight: 500; display: flex; align-items: center; gap: .45rem; }
    .legend-val  { font-family: 'DM Mono', monospace; font-size: .72rem; color: #334155; font-weight: 600; }

    .table-card { background: white; border-radius: var(--card-radius); box-shadow: var(--shadow-sm); border: 1px solid #f1f5f9; overflow: hidden; }
    .table-header {
        padding: 1.2rem 1.6rem;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-title { font-size: .85rem; font-weight: 700; color: #0f172a; }

    .search-box {
        display: flex; align-items: center; gap: .5rem;
        background: #f8fafc; border: 1.5px solid #e8edf4;
        border-radius: 9px; padding: .38rem .75rem;
    }
    .search-box input {
        border: none; background: none; outline: none;
        font-size: .78rem; color: #334155;
        font-family: 'Sora', sans-serif; width: 180px;
    }
    .search-box input::placeholder { color: #94a3b8; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        font-size: .68rem; font-weight: 700; letter-spacing: .07em;
        text-transform: uppercase; color: #94a3b8;
        padding: .75rem 1.6rem; text-align: left;
        background: #fafbfc; border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid #f8fafc; transition: background .12s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fffe; }
    tbody td { padding: .85rem 1.6rem; font-size: .8rem; color: #334155; vertical-align: middle; }
    .td-sep { font-family: 'DM Mono', monospace; font-size: .75rem; color: #0f172a; font-weight: 600; }
    .td-name { font-weight: 600; color: #1e293b; }
    .td-nominal { font-family: 'DM Mono', monospace; font-size: .77rem; color: #0f172a; font-weight: 600; }
    .td-date { font-family: 'DM Mono', monospace; font-size: .74rem; color: #64748b; }

    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .67rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; padding: .22rem .6rem; border-radius: 6px;
    }
    .badge-dot { width: 5px; height: 5px; border-radius: 50%; }
    .badge.terbayar    { background: rgba(34,197,94,.1);   color: #16a34a; }
    .badge.pending     { background: rgba(245,158,11,.1);  color: #d97706; }
    .badge.tidak_layak { background: rgba(239,68,68,.1);   color: #dc2626; }
    .badge.diproses    { background: rgba(99,102,241,.1);  color: #4f46e5; }

    .jenis-badge {
        font-size: .62rem; font-weight: 600; padding: .15rem .4rem;
        border-radius: 5px; font-family: 'DM Mono', monospace;
    }
    .jenis-badge.rinap  { background: rgba(20,184,166,.1);  color: #0f766e; }
    .jenis-badge.rjalan { background: rgba(99,102,241,.1);  color: #4338ca; }

    .pagination-bar {
        padding: .9rem 1.6rem;
        display: flex; align-items: center; justify-content: space-between;
        border-top: 1px solid #f1f5f9;
        font-size: .75rem; color: #94a3b8;
    }
    .pag-btns { display: flex; gap: .35rem; }
    .pag-btn {
        font-family: 'DM Mono', monospace; font-size: .72rem;
        padding: .3rem .65rem; border-radius: 7px;
        border: 1.5px solid #e2e8f0; background: white;
        color: #475569; cursor: pointer; transition: all .12s;
    }
    .pag-btn:hover, .pag-btn.active { background: var(--bpjs-teal); border-color: var(--bpjs-teal); color: white; }
    .pag-btn:disabled { opacity: .4; cursor: not-allowed; }

    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 800px 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 6px;
    }

    .empty-state { padding: 3rem; text-align: center; color: #94a3b8; font-size: .82rem; }
    .empty-state svg { margin: 0 auto 1rem; opacity: .3; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp .35s cubic-bezier(.4,0,.2,1) both; }
    .fade-up-1 { animation-delay: .05s; }
    .fade-up-2 { animation-delay: .1s; }
    .fade-up-3 { animation-delay: .15s; }
    .fade-up-4 { animation-delay: .2s; }
    .fade-up-5 { animation-delay: .25s; }
    .fade-up-6 { animation-delay: .3s; }
</style>
@endpush

@section('content')

{{-- FILTER BAR --}}
<div class="fade-up" style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.15rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;">Dashboard Klaim BPJS</h1>
        <p style="font-size:.75rem;color:#94a3b8;margin-top:.15rem;">Rawat Inap & Rawat Jalan — data dari sistem BPJS</p>
    </div>

    <div class="period-bar">
        <button class="period-btn active" data-label="per Minggu"  onclick="setPeriod(this,'weekly')">Mingguan</button>
        <button class="period-btn"        data-label="per Bulan"   onclick="setPeriod(this,'monthly')">Bulanan</button>
        <button class="period-btn"        data-label="per Tahun"   onclick="setPeriod(this,'yearly')">Tahunan</button>
        <div class="period-divider"></div>
        <input type="date" id="date-from" class="date-input">
        <span style="color:#94a3b8;font-size:.75rem;">—</span>
        <input type="date" id="date-to" class="date-input">
        <button class="filter-apply-btn" onclick="applyCustomRange()">Filter</button>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="stat-grid" style="margin-bottom:1.25rem;">

    <div class="stat-card teal fade-up fade-up-1">
        <div class="stat-header">
            <span class="stat-label">Total Pengajuan</span>
            <div class="stat-icon teal">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="val-pengajuan"><span class="skeleton" style="width:60px;height:36px;display:block;"></span></div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-sub" id="sub-pengajuan">— kasus</span>
            <span class="stat-delta up" id="delta-pengajuan">+–%</span>
        </div>
        <div class="stat-rupiah" id="rp-pengajuan">Rp –</div>
    </div>

    <div class="stat-card green fade-up fade-up-2">
        <div class="stat-header">
            <span class="stat-label">Terbayar</span>
            <div class="stat-icon green">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="val-terbayar"><span class="skeleton" style="width:60px;height:36px;display:block;"></span></div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-sub" id="sub-terbayar">— kasus</span>
            <span class="stat-delta up" id="delta-terbayar">+–%</span>
        </div>
        <div class="stat-rupiah" id="rp-terbayar">Rp –</div>
    </div>

    <div class="stat-card amber fade-up fade-up-3">
        <div class="stat-header">
            <span class="stat-label">Pending</span>
            <div class="stat-icon amber">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="val-pending"><span class="skeleton" style="width:60px;height:36px;display:block;"></span></div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-sub" id="sub-pending">— kasus</span>
            <span class="stat-delta down" id="delta-pending">+–%</span>
        </div>
        <div class="stat-rupiah" id="rp-pending">Rp –</div>
    </div>

    <div class="stat-card red fade-up fade-up-4">
        <div class="stat-header">
            <span class="stat-label">Tidak Layak</span>
            <div class="stat-icon red">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="val-tidaklayak"><span class="skeleton" style="width:60px;height:36px;display:block;"></span></div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-sub" id="sub-tidaklayak">— kasus</span>
            <span class="stat-delta down" id="delta-tidaklayak">+–%</span>
        </div>
        <div class="stat-rupiah" id="rp-tidaklayak">Rp –</div>
    </div>

    <div class="stat-card indigo fade-up fade-up-5">
        <div class="stat-header">
            <span class="stat-label">Diproses</span>
            <div class="stat-icon indigo">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
        </div>
        <div class="stat-value" id="val-diproses"><span class="skeleton" style="width:60px;height:36px;display:block;"></span></div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-sub" id="sub-diproses">— kasus</span>
            <span class="stat-delta up" id="delta-diproses">+–%</span>
        </div>
        <div class="stat-rupiah" id="rp-diproses">Rp –</div>
    </div>

</div>

{{-- CHARTS ROW --}}
<div class="charts-grid fade-up fade-up-5" style="margin-bottom:1.25rem;">

    <div class="chart-card">
        <div class="chart-card-title">
            Tren Klaim
            <span id="chart-period-label">per Minggu</span>
        </div>
        <div class="bar-chart-wrap">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-card-title">Komposisi Status</div>
        <div class="donut-wrap">
            <div class="donut-canvas-wrap">
                <canvas id="donutChart" width="180" height="180"></canvas>
                <div class="donut-center">
                    <div class="donut-center-num" id="donut-total">–</div>
                    <div class="donut-center-label">Total</div>
                </div>
            </div>
            <div class="legend-list">
                <div class="legend-item">
                    <span class="legend-name"><span class="legend-dot" style="background:#22c55e"></span>Terbayar</span>
                    <span class="legend-val" id="leg-terbayar">–</span>
                </div>
                <div class="legend-item">
                    <span class="legend-name"><span class="legend-dot" style="background:#f59e0b"></span>Pending</span>
                    <span class="legend-val" id="leg-pending">–</span>
                </div>
                <div class="legend-item">
                    <span class="legend-name"><span class="legend-dot" style="background:#ef4444"></span>Tidak Layak</span>
                    <span class="legend-val" id="leg-tidaklayak">–</span>
                </div>
                <div class="legend-item">
                    <span class="legend-name"><span class="legend-dot" style="background:#6366f1"></span>Diproses</span>
                    <span class="legend-val" id="leg-diproses">–</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- DATA TABLE --}}
<div class="table-card fade-up fade-up-6">
    <div class="table-header">
        <span class="table-title">Data Klaim BPJS</span>
        <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
            <div class="search-box">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="search-input" placeholder="No SEP / Nama Pasien" oninput="filterTable()">
            </div>
            <select id="status-filter" class="date-input" onchange="filterTable()" style="cursor:pointer;">
                <option value="">Semua Status</option>
                <option value="terbayar">Terbayar</option>
                <option value="pending">Pending</option>
                <option value="tidak_layak">Tidak Layak</option>
                <option value="diproses">Diproses</option>
            </select>
            <select id="jenis-filter" class="date-input" onchange="filterTable()" style="cursor:pointer;">
                <option value="">Semua Jenis</option>
                <option value="rinap">Rawat Inap</option>
                <option value="rjalan">Rawat Jalan</option>
            </select>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>No SEP</th>
                    <th>Pasien</th>
                    <th>Diagnosa</th>
                    <th>Tgl Pengajuan</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th style="text-align:right;">Nominal</th>
                    <th style="text-align:right;">Terbayar</th>
                </tr>
            </thead>
            <tbody id="table-body">
                @for ($i = 0; $i < 6; $i++)
                <tr>
                    <td colspan="8"><span class="skeleton" style="width:100%;height:18px;display:block;border-radius:4px;"></span></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="pagination-bar">
        <span id="pag-info" style="font-size:.73rem;color:#94a3b8;font-family:'DM Mono',monospace;">—</span>
        <div class="pag-btns" id="pag-btns"></div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_BASE  = '/bpjs';
const PER_PAGE  = 10;
const CARD_KEYS = ['pengajuan', 'terbayar', 'pending', 'tidaklayak', 'diproses'];
const API_MAP   = { pengajuan: 'pengajuan', terbayar: 'terbayar', pending: 'pending', tidaklayak: 'tidak_layak', diproses: 'diproses' };
const STATUS_MAP = {
    terbayar:    ['Terbayar',    '#22c55e'],
    pending:     ['Pending',     '#f59e0b'],
    tidak_layak: ['Tidak Layak', '#ef4444'],
    diproses:    ['Diproses',    '#6366f1'],
};

let currentPeriod = 'weekly';
let dateFrom = null, dateTo = null;
let allRows = [], filteredRows = [];
let currentPage = 1;
let barChartInst = null, donutChartInst = null;

//Helpers
const fmtRp  = n => (n == null || isNaN(n)) ? 'Rp –' : 'Rp ' + Number(n).toLocaleString('id-ID');
const fmtNum = n => (n == null || isNaN(n)) ? '–'    : Number(n).toLocaleString('id-ID');
const $      = id => document.getElementById(id);

function emptyState(msg) {
    return `<tr><td colspan="8"><div class="empty-state">
        <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg><p>${msg}</p></div></td></tr>`;
}

function skeletonRows(n) {
    const cells = Array(8).fill('<td><span class="skeleton" style="width:80px;height:14px;display:block;border-radius:4px;"></span></td>').join('');
    return Array(n).fill(`<tr>${cells}</tr>`).join('');
}

async function apiFetch(endpoint, params) {
    const res = await fetch(`${API_BASE}/${endpoint}?${params}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) throw new Error(res.status);
    return res.json();
}

//Filter Periode
function setPeriod(btn, period) {
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPeriod = period;
    dateFrom = dateTo = null;
    $('chart-period-label').textContent = btn.dataset.label;
    loadAll();
}

function applyCustomRange() {
    dateFrom = $('date-from').value;
    dateTo   = $('date-to').value;
    if (!dateFrom || !dateTo) { alert('Pilih tanggal awal dan akhir'); return; }
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    currentPeriod = 'custom';
    $('chart-period-label').textContent = `${dateFrom} → ${dateTo}`;
    loadAll();
}

function buildParams() {
    const p = new URLSearchParams();
    currentPeriod !== 'custom' ? p.set('period', currentPeriod) : (p.set('from', dateFrom), p.set('to', dateTo));
    return p.toString();
}

async function loadAll() {
    const params = buildParams();
    await Promise.all([loadSummary(params), loadChart(params), loadTable(params)]);
}

//Summary Cards
async function loadSummary(params) {
    try {
        const d = await apiFetch('summary', params);
        CARD_KEYS.forEach(k => setCard(k, d[API_MAP[k]]));
    } catch (e) {
        console.error('Summary error:', e);
        CARD_KEYS.forEach(k => { $('val-' + k).textContent = '–'; });
    }
}

function setCard(key, obj) {
    if (!obj) return;
    $('val-'   + key).textContent = fmtNum(obj.count);
    $('sub-'   + key).textContent = fmtNum(obj.count) + ' kasus';
    $('rp-'    + key).textContent = fmtRp(obj.nominal);
    const d = parseFloat(obj.delta);
    if (!isNaN(d)) {
        const el = $('delta-' + key);
        el.textContent = (d >= 0 ? '+' : '') + d.toFixed(1) + '%';
        el.className   = 'stat-delta ' + (d >= 0 ? 'up' : 'down');
    }
}

//Charts
async function loadChart(params) {
    try {
        const d = await apiFetch('chart', params);
        renderBarChart(d);
        renderDonut(d.summary);
    } catch (e) { console.error('Chart error:', e); }
}

function renderBarChart(d) {
    const ctx = $('barChart').getContext('2d');
    if (barChartInst) barChartInst.destroy();
    barChartInst = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: d.labels || [],
            datasets: [
                { label: 'Terbayar',    data: d.terbayar    || [], backgroundColor: 'rgba(34,197,94,.75)',  borderRadius: 6, borderSkipped: false },
                { label: 'Pending',     data: d.pending     || [], backgroundColor: 'rgba(245,158,11,.7)',  borderRadius: 6, borderSkipped: false },
                { label: 'Tidak Layak', data: d.tidak_layak || [], backgroundColor: 'rgba(239,68,68,.7)',   borderRadius: 6, borderSkipped: false },
                { label: 'Diproses',    data: d.diproses    || [], backgroundColor: 'rgba(99,102,241,.6)',  borderRadius: 6, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Sora', size: 11 }, boxWidth: 10, boxHeight: 10, borderRadius: 4, useBorderRadius: true, padding: 16 } },
                tooltip: { backgroundColor: '#0f172a', titleFont: { family: 'Sora', size: 12, weight: '700' }, bodyFont: { family: 'DM Mono', size: 11 }, padding: 10, cornerRadius: 8 }
            },
            scales: {
                x: { stacked: false, grid: { display: false }, ticks: { font: { family: 'DM Mono', size: 10 }, color: '#94a3b8' } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'DM Mono', size: 10 }, color: '#94a3b8' }, beginAtZero: true }
            }
        }
    });
}

function renderDonut(s) {
    if (!s) return;
    const total = (s.terbayar||0) + (s.pending||0) + (s.tidak_layak||0) + (s.diproses||0);
    $('donut-total').textContent    = fmtNum(total);
    $('leg-terbayar').textContent   = fmtNum(s.terbayar);
    $('leg-pending').textContent    = fmtNum(s.pending);
    $('leg-tidaklayak').textContent = fmtNum(s.tidak_layak);
    $('leg-diproses').textContent   = fmtNum(s.diproses);

    const ctx = $('donutChart').getContext('2d');
    if (donutChartInst) donutChartInst.destroy();
    donutChartInst = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Terbayar','Pending','Tidak Layak','Diproses'],
            datasets: [{
                data: [s.terbayar||0, s.pending||0, s.tidak_layak||0, s.diproses||0],
                backgroundColor: ['#22c55e','#f59e0b','#ef4444','#6366f1'],
                borderWidth: 0, hoverOffset: 6,
            }]
        },
        options: {
            cutout: '68%', responsive: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0f172a', titleFont: { family: 'Sora', size: 12, weight: '700' }, bodyFont: { family: 'DM Mono', size: 11 }, cornerRadius: 8, padding: 10 }
            }
        }
    });
}

//Table
async function loadTable(params) {
    $('table-body').innerHTML = skeletonRows(6);
    try {
        const d = await apiFetch('list', params);
        allRows = Array.isArray(d) ? d : (d.data || []);
        currentPage = 1;
        filterTable();
    } catch (e) {
        console.error('Table error:', e);
        $('table-body').innerHTML = emptyState('Gagal memuat data');
    }
}

function filterTable() {
    const q      = $('search-input').value.toLowerCase();
    const status = $('status-filter').value;
    const jenis  = $('jenis-filter').value;

    filteredRows = allRows.filter(r =>
        (!q      || (r.no_sep||'').toLowerCase().includes(q) || (r.nama_pasien||'').toLowerCase().includes(q)) &&
        (!status || r.status === status) &&
        (!jenis  || r.jenis  === jenis)
    );
    currentPage = 1;
    renderTable();
}

function renderTable() {
    const start    = (currentPage - 1) * PER_PAGE;
    const pageRows = filteredRows.slice(start, start + PER_PAGE);
    const total    = filteredRows.length;
    const pages    = Math.ceil(total / PER_PAGE);

    if (!pageRows.length) {
        $('table-body').innerHTML = emptyState('Tidak ada data ditemukan');
        $('pag-info').textContent = '0 data';
        $('pag-btns').innerHTML   = '';
        return;
    }

    $('table-body').innerHTML = pageRows.map(r => `
        <tr>
            <td class="td-sep">${r.no_sep || '–'}</td>
            <td class="td-name">${r.nama_pasien || '–'}</td>
            <td style="font-size:.76rem;color:#475569;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${r.diagnosa||''}">${r.diagnosa || '–'}</td>
            <td class="td-date">${formatDate(r.tgl_pengajuan)}</td>
            <td><span class="jenis-badge ${r.jenis}">${r.jenis === 'rinap' ? 'Rawat Inap' : 'Rawat Jalan'}</span></td>
            <td>${statusBadge(r.status)}</td>
            <td class="td-nominal" style="text-align:right;">${fmtRp(r.nominal)}</td>
            <td class="td-nominal" style="text-align:right;color:${r.status==='terbayar'?'#16a34a':'#94a3b8'}">
                ${r.status === 'terbayar' ? fmtRp(r.terbayar) : '–'}
            </td>
        </tr>
    `).join('');

    $('pag-info').textContent = `Menampilkan ${start+1}–${Math.min(start+PER_PAGE, total)} dari ${total} data`;

    let btns = `<button class="pag-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
    for (let i = 1; i <= pages; i++) {
        if (pages > 7 && i > 2 && i < pages-1 && Math.abs(i-currentPage) > 1) {
            if (i === 3 || i === pages-2) btns += `<button class="pag-btn" disabled>…</button>`;
            continue;
        }
        btns += `<button class="pag-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
    }
    btns += `<button class="pag-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>›</button>`;
    $('pag-btns').innerHTML = btns;
}

function goPage(p) {
    const pages = Math.ceil(filteredRows.length / PER_PAGE);
    if (p < 1 || p > pages) return;
    currentPage = p;
    renderTable();
    document.querySelector('.table-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function statusBadge(s) {
    const [label, color] = STATUS_MAP[s] ?? ['–', '#94a3b8'];
    return `<span class="badge ${s||''}"><span class="badge-dot" style="background:${color}"></span>${label}</span>`;
}

function formatDate(str) {
    if (!str) return '–';
    try { return new Date(str).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }); }
    catch { return str; }
}

//Init
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const meta = await apiFetch('meta', '');
        dateFrom = meta.default_from;
        dateTo   = meta.default_to;
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        currentPeriod = 'custom';
        $('chart-period-label').textContent = `${dateFrom} → ${dateTo}`;
    } catch {
        dateFrom = '2026-02-01';
        dateTo   = '2026-02-28';
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;
        currentPeriod = 'custom';
    }
    loadAll();
    setInterval(loadAll, 5 * 60 * 1000);
});
</script>
@endpush
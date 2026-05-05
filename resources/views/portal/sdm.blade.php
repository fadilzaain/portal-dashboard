@extends('layouts.app')

@section('title', 'SDM')
@section('page_title', 'SDM')
@section('page_subtitle', 'Monitoring Pegawai')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

*, *::before, *::after { box-sizing: border-box; }

:root {
    --navy-950: #050d1a;
    --navy-900: #0a1628;
    --navy-800: #0f2040;
    --navy-700: #162b55;
    --navy-600: #1e3a6e;
    --navy-500: #264d91;
    --accent-blue:  #38bdf8;
    --accent-green: #34d399;
    --accent-amber: #f59e0b;
    --accent-red:   #f87171;
    --accent-purple:#a78bfa;
    --accent-cyan:  #22d3ee;
    --accent-orange:#fb923c;
    --accent-rose:  #fb7185;
    --accent-teal:  #2dd4bf;
    --text-primary: #e2e8f0;
    --text-muted:   #94a3b8;
    --border:       rgba(56,189,248,0.15);
}

body { background: var(--navy-950); color: var(--text-primary); }

.sdm-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 4px 0;
    background: var(--navy-950);
    min-height: 100vh;
}

/* ── DATE BAR ── */
.sdm-datebar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}
.sdm-datebar-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--navy-900);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
}
.sdm-datebar-btn svg { color: var(--text-muted); }

/* ── STAT GRID ── */
.sdm-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}
.sdm-stat-grid-2 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.sdm-sc {
    background: var(--navy-900);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow 0.2s, transform 0.2s;
}
.sdm-sc:hover {
    box-shadow: 0 4px 20px rgba(56,189,248,0.1);
    transform: translateY(-1px);
    border-color: rgba(56,189,248,0.3);
}
.sdm-sc-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sdm-sc-icon img, .sdm-sc-icon svg { width: 28px; height: 28px; }
.sdm-sc-icon.ic-blue   { background: rgba(56,189,248,0.12); }
.sdm-sc-icon.ic-indigo { background: rgba(99,102,241,0.12); }
.sdm-sc-icon.ic-teal   { background: rgba(45,212,191,0.12); }
.sdm-sc-icon.ic-sky    { background: rgba(14,165,233,0.12); }
.sdm-sc-icon.ic-amber  { background: rgba(245,158,11,0.12); }
.sdm-sc-icon.ic-orange { background: rgba(251,146,60,0.12); }
.sdm-sc-icon.ic-emerald{ background: rgba(52,211,153,0.12); }
.sdm-sc-icon.ic-purple { background: rgba(167,139,250,0.12); }
.sdm-sc-icon.ic-rose   { background: rgba(251,113,133,0.12); }
.sdm-sc-icon.ic-cyan   { background: rgba(34,211,238,0.12); }

.sdm-sc-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--text-muted);
    margin-bottom: 3px;
}
.sdm-sc-val {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 2px;
}
.sdm-sc-val span {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
}
.sdm-sc-pct {
    font-size: 11px;
    color: var(--text-muted);
}

/* ── CHART ROW ── */
.sdm-chart-row {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 16px;
    margin-bottom: 20px;
}
.sdm-panel {
    background: var(--navy-900);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}
.sdm-panel-hd {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 18px;
}
.sdm-panel-title {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent-blue);
}

/* Doughnut legend */
.doughnut-layout {
    display: flex;
    align-items: center;
    gap: 20px;
}
.doughnut-canvas-wrap { flex: 0 0 160px; }
.doughnut-legend { flex: 1; }
.dl-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid rgba(56,189,248,0.08);
    gap: 8px;
}
.dl-row:last-child { border-bottom: none; }
.dl-dot {
    width: 10px; height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
}
.dl-name {
    font-size: 11.5px;
    color: var(--text-muted);
    font-weight: 500;
    flex: 1;
    margin-left: 6px;
}
.dl-count { font-size: 12px; font-weight: 700; color: var(--text-primary); }
.dl-pct   { font-size: 11px; color: var(--text-muted); min-width: 42px; text-align: right; }

.sdm-total-badge {
    display: inline-block;
    background: rgba(56,189,248,0.1);
    border: 1px solid rgba(56,189,248,0.25);
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent-blue);
}

/* ── BOTTOM ROW ── */
.sdm-bottom-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Table bezetting */
.sdm-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.sdm-table thead th {
    padding: 8px 10px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    text-align: left;
    background: var(--navy-800);
}
.sdm-table thead th.right { text-align: right; }
.sdm-table tbody td {
    padding: 10px 10px;
    border-bottom: 1px solid rgba(56,189,248,0.06);
    color: var(--text-primary);
    font-weight: 500;
    vertical-align: middle;
}
.sdm-table tbody tr:hover { background: rgba(56,189,248,0.04); }
.sdm-table tbody tr:last-child td { border-bottom: none; }
.sdm-table tbody td.right { text-align: right; }
.sdm-table tbody td.center { text-align: center; }

.kekurangan-badge {
    display: inline-block;
    background: rgba(248,113,113,0.15);
    color: var(--accent-red);
    border: 1px solid rgba(248,113,113,0.3);
    font-weight: 700;
    border-radius: 6px;
    padding: 2px 10px;
    font-size: 13px;
}

.sdm-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--accent-blue);
    text-decoration: none;
    margin-top: 14px;
}
.sdm-link:hover { text-decoration: underline; }

/* Shift panel */
.shift-list { display: flex; flex-direction: column; gap: 12px; }
.shift-item {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
}
.shift-side {
    flex: 0 0 100px;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.shift-side.pagi   { background: rgba(245,158,11,0.1); }
.shift-side.siang  { background: rgba(251,146,60,0.1); }
.shift-side.malam  { background: rgba(56,189,248,0.1); }

.shift-name {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1;
    margin-bottom: 3px;
}
.shift-side.pagi  .shift-name  { color: var(--accent-amber); }
.shift-side.siang .shift-name  { color: var(--accent-orange); }
.shift-side.malam .shift-name  { color: var(--accent-blue); }
.shift-time { font-size: 10px; color: var(--text-muted); font-weight: 500; }

.shift-detail {
    flex: 1;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 20px;
    background: var(--navy-800);
}
.shift-prof-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.shift-prof-name { font-size: 11.5px; color: var(--text-muted); }
.shift-prof-val  { font-size: 12px; font-weight: 700; color: var(--text-primary); }

.shift-total-bubble {
    flex: 0 0 70px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--navy-700);
    padding: 10px;
}
.stb-val {
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
}
.stb-val.pagi  { color: var(--accent-amber); }
.stb-val.siang { color: var(--accent-orange); }
.stb-val.malam { color: var(--accent-blue); }
.stb-label { font-size: 10px; color: var(--text-muted); margin-top: 2px; font-weight: 600; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
    .sdm-stat-grid, .sdm-stat-grid-2 { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
    .sdm-chart-row  { grid-template-columns: 1fr; }
    .sdm-bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .sdm-stat-grid, .sdm-stat-grid-2 { grid-template-columns: repeat(2, 1fr); }
    .doughnut-layout { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="sdm-wrap" style="padding: 1.5rem;">

    {{-- ── DATE BAR ── --}}
    <div class="sdm-wrap" style="padding: 1.5rem;">

    {{-- ── DATE BAR ── --}}
    <div class="sdm-datebar" style="display:flex; align-items:center; justify-content:space-between;">
        
        <a href="{{ url('dashboard') }}" class="btn-home">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Home
        </a>

        <div class="sdm-datebar-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>

    </div>

    {{-- ── STAT ROW 1 ── --}}
    @php
        $pctPns       = $totalAktif > 0 ? round($totalPns / $totalAktif * 100, 2) : 0;
        $pctP3k       = $totalAktif > 0 ? round($totalP3k / $totalAktif * 100, 2) : 0;
        $pctP3kParuh  = $totalAktif > 0 ? round($totalP3kParuhWaktu / $totalAktif * 100, 2) : 0;
        $pctCpns      = $totalAktif > 0 ? round($totalCpns / $totalAktif * 100, 2) : 0;
        $pctKontrak   = $totalAktif > 0 ? round($totalKontrak / $totalAktif * 100, 2) : 0;
        $pctTetap     = $totalAktif > 0 ? round($totalTetap / $totalAktif * 100, 2) : 0;
        $pctOrientasi = $totalAktif > 0 ? round($totalOrientasi / $totalAktif * 100, 2) : 0;
        $pctMedis     = $totalAktif > 0 ? round($totalMedis / $totalAktif * 100, 2) : 0;
        $pctNonMedis  = $totalAktif > 0 ? round($totalNonMedis / $totalAktif * 100, 2) : 0;
    @endphp

    <div class="sdm-stat-grid">
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Total Pegawai</div>
                <div class="sdm-sc-val">{{ number_format($totalPegawai) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">100% dari keseluruhan</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-teal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">PNS</div>
                <div class="sdm-sc-val">{{ number_format($totalPns) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctPns }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-indigo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">P3K</div>
                <div class="sdm-sc-val">{{ number_format($totalP3k) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctP3k }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-sky">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">P3K Paruh Waktu</div>
                <div class="sdm-sc-val">{{ number_format($totalP3kParuhWaktu) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctP3kParuh }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-amber">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">CPNS</div>
                <div class="sdm-sc-val">{{ number_format($totalCpns) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctCpns }}% dari total</div>
            </div>
        </div>
    </div>

    {{-- STAT ROW 2 --}}
    <div class="sdm-stat-grid-2">
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fb923c" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Kontrak</div>
                <div class="sdm-sc-val">{{ number_format($totalKontrak) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctKontrak }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-emerald">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Tetap</div>
                <div class="sdm-sc-val">{{ number_format($totalTetap) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctTetap }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Orientasi</div>
                <div class="sdm-sc-val">{{ number_format($totalOrientasi) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctOrientasi }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-rose">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fb7185" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Medis</div>
                <div class="sdm-sc-val">{{ number_format($totalMedis) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctMedis }}% dari total</div>
            </div>
        </div>

        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-cyan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">Non Medis</div>
                <div class="sdm-sc-val">{{ number_format($totalNonMedis) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctNonMedis }}% dari total</div>
            </div>
        </div>
    </div>

    {{-- ── CHART ROW ── --}}
    <div class="sdm-chart-row">

        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div>
                    <div class="sdm-panel-title">Distribusi Pegawai Berdasarkan Profesi</div>
                </div>
                <span class="sdm-total-badge">Total {{ number_format($totalAktif) }} Orang</span>
            </div>
            <div class="doughnut-layout">
                <div class="doughnut-canvas-wrap">
                    <canvas id="profesiDoughnut" width="160" height="160"></canvas>
                </div>
                <div class="doughnut-legend" id="profesiLegend"></div>
            </div>
        </div>

        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div>
                    <div class="sdm-panel-title">Distribusi Pegawai Berdasarkan Status Kepegawaian</div>
                </div>
            </div>
            <canvas id="statusBarChart" height="160"></canvas>
        </div>
    </div>

    {{-- ── BOTTOM ROW ── --}}
    <div class="sdm-bottom-row">

        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div>
                    <div class="sdm-panel-title">Unit dengan Kekurangan SDM</div>
                </div>
            </div>
            <table class="sdm-table">
                <thead>
                    <tr>
                        <th style="width:30px">No</th>
                        <th>Unit Kerja</th>
                        <th class="right">Kebutuhan</th>
                        <th class="right">Tersedia</th>
                        <th class="center">Kekurangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bezettingData as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->unit_kerja }}</td>
                        <td class="right">{{ $row->kebutuhan }}</td>
                        <td class="right">{{ $row->tersedia }}</td>
                        <td class="center">
                            <span class="kekurangan-badge">{{ $row->kekurangan }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#94a3b8; padding: 24px 0;">
                            Tidak ada data kekurangan SDM
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <a href="#" class="sdm-link">
                Lihat detail bezetting
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div>
                    <div class="sdm-panel-title">Shift Hari Ini</div>
                </div>
            </div>
            <div class="shift-list">
                @php $shiftClasses = ['PAGI' => 'pagi', 'SIANG' => 'siang', 'MALAM' => 'malam']; @endphp
                @foreach (['PAGI', 'SIANG', 'MALAM'] as $shift)
                @php
                    $cls     = $shiftClasses[$shift];
                    $summary = $shiftSummary[$shift] ?? ['total' => 0, 'detail' => []];
                    $time    = $shiftTimes[$shift] ?? '';
                @endphp
                <div class="shift-item">
                    <div class="shift-side {{ $cls }}">
                        <div class="shift-name">{{ $shift }}</div>
                        <!-- <div class="shift-time">{{ $time }}</div> -->
                    </div>
                    <div class="shift-detail">
                        @forelse ($summary['detail'] as $profesi => $jumlah)
                            <div class="shift-prof-row">
                                <span class="shift-prof-name">{{ $profesi }}</span>
                                <span class="shift-prof-val">{{ $jumlah }}</span>
                            </div>
                        @empty
                            <div style="font-size:12px; color:#94a3b8; grid-column:1/-1; align-self:center;">
                                Detail profesi belum tersedia
                            </div>
                        @endforelse
                    </div>
                    <div class="shift-total-bubble">
                        <div class="stb-val {{ $cls }}">{{ number_format($summary['total']) }}</div>
                        <div class="stb-label">Orang</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const profesiLabels = {!! json_encode($profesiLabels) !!};
const profesiValues = {!! json_encode($profesiValues) !!};
const statusLabels  = {!! json_encode($statusLabels) !!};
const statusValues  = {!! json_encode($statusValues) !!};
const totalAktif    = {{ $totalAktif }};

const palette = [
    '#38bdf8','#2dd4bf','#f59e0b','#a78bfa','#fb7185','#22d3ee',
    '#fb923c','#34d399','#818cf8','#f472b6',
];

const doughnutCtx = document.getElementById('profesiDoughnut').getContext('2d');
new Chart(doughnutCtx, {
    type: 'doughnut',
    data: {
        labels: profesiLabels,
        datasets: [{
            data: profesiValues,
            backgroundColor: palette,
            borderColor: '#0a1628',
            borderWidth: 3,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: false,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0a1628',
                titleColor: '#e2e8f0',
                bodyColor: '#94a3b8',
                borderColor: 'rgba(56,189,248,.25)',
                borderWidth: 1,
                callbacks: {
                    label: ctx => ` ${ctx.parsed.toLocaleString('id-ID')} orang`
                }
            }
        }
    },
    plugins: [{
        id: 'centerText',
        afterDraw(chart) {
            const { width, height, ctx } = chart;
            ctx.restore();
            ctx.font = 'bold 20px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = '#e2e8f0';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(totalAktif.toLocaleString('id-ID'), width / 2, height / 2 - 8);
            ctx.font = '500 11px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = '#94a3b8';
            ctx.fillText('Orang', width / 2, height / 2 + 14);
            ctx.save();
        }
    }]
});

const legendEl = document.getElementById('profesiLegend');
const totalSum = profesiValues.reduce((a, b) => a + b, 0);
profesiLabels.forEach((label, i) => {
    const pct = totalSum > 0 ? ((profesiValues[i] / totalSum) * 100).toFixed(2) : '0.00';
    legendEl.innerHTML += `
        <div class="dl-row">
            <span class="dl-dot" style="background:${palette[i]}"></span>
            <span class="dl-name">${label}</span>
            <span class="dl-count">${profesiValues[i].toLocaleString('id-ID')}</span>
            <span class="dl-pct">(${pct}%)</span>
        </div>`;
});

new Chart(document.getElementById('statusBarChart'), {
    type: 'bar',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: 'rgba(56,189,248,0.7)',
            borderColor: '#38bdf8',
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans' } }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: {
                    color: '#94a3b8',
                    callback: val => val.toLocaleString('id-ID'),
                    font: { size: 11, family: 'Plus Jakarta Sans' }
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0a1628',
                titleColor: '#e2e8f0',
                bodyColor: '#94a3b8',
                borderColor: 'rgba(56,189,248,.25)',
                borderWidth: 1,
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} orang`
                }
            }
        }
    },
    plugins: [{
        id: 'barTopLabel',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((bar, index) => {
                    const val = dataset.data[index];
                    if (val === 0) return;
                    ctx.save();
                    ctx.font = 'bold 12px Plus Jakarta Sans, sans-serif';
                    ctx.fillStyle = '#94a3b8';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(val.toLocaleString('id-ID'), bar.x, bar.y - 4);
                    ctx.restore();
                });
            });
        }
    }]
});
</script>
@endpush
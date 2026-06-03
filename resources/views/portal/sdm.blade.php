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
    --accent-blue:   #38bdf8;
    --accent-green:  #34d399;
    --accent-amber:  #f59e0b;
    --accent-red:    #f87171;
    --accent-purple: #a78bfa;
    --accent-cyan:   #22d3ee;
    --accent-orange: #fb923c;
    --accent-rose:   #fb7185;
    --accent-teal:   #2dd4bf;
    --text-primary:  #e2e8f0;
    --text-muted:    #94a3b8;
    --border:        rgba(56,189,248,0.15);
}

body { background: var(--navy-950); color: var(--text-primary); }

.sdm-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 1.5rem;
    background: var(--navy-950);
    min-height: 100vh;
}

/* ── DATE BAR ── */
.sdm-datebar {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
}

/* ── STAT GRID ── */
.sdm-stat-grid, .sdm-stat-grid-2 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}
.sdm-stat-grid-2 { margin-bottom: 24px; }

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
.sdm-sc-icon svg { width: 28px; height: 28px; }
.ic-blue    { background: rgba(56,189,248,0.12); }
.ic-indigo  { background: rgba(99,102,241,0.12); }
.ic-teal    { background: rgba(45,212,191,0.12); }
.ic-sky     { background: rgba(14,165,233,0.12); }
.ic-amber   { background: rgba(245,158,11,0.12); }
.ic-orange  { background: rgba(251,146,60,0.12); }
.ic-emerald { background: rgba(52,211,153,0.12); }
.ic-purple  { background: rgba(167,139,250,0.12); }
.ic-rose    { background: rgba(251,113,133,0.12); }
.ic-cyan    { background: rgba(34,211,238,0.12); }
.sdm-sc-label {
    font-size: 10.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.6px;
    color: var(--text-muted); margin-bottom: 3px;
}
.sdm-sc-val {
    font-size: 22px; font-weight: 800;
    color: var(--text-primary); line-height: 1; margin-bottom: 2px;
}
.sdm-sc-val span { font-size: 13px; font-weight: 600; color: var(--text-muted); }
.sdm-sc-pct { font-size: 11px; color: var(--text-muted); }

/* ── BAR CHART PANEL ── */
.sdm-panel {
    background: var(--navy-900);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}
.sdm-panel-title {
    font-size: 12px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px;
    color: var(--accent-blue);
}
.sdm-panel-hd {
    display: flex; align-items: flex-start;
    justify-content: space-between; margin-bottom: 18px;
}
.sdm-bar-panel { margin-bottom: 20px; }

/* ── BOTTOM ROW ── */
.sdm-bottom-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* ── BEZETTING PANEL ── */
.bez-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 14px;
}
.bez-sc {
    background: var(--navy-800);
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
}
.bez-sc-label { font-size: 10px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
.bez-sc-val   { font-size: 18px; font-weight: 800; line-height: 1; color: var(--text-primary); }
.bez-sc-val.red   { color: var(--accent-red); }
.bez-sc-val.teal  { color: var(--accent-teal); }
.bez-sc-val.amber { color: var(--accent-amber); }

/* Tab bar */
.bez-tabs {
    display: flex; gap: 6px; margin-bottom: 12px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 0;
}
.bez-tab {
    font-size: 12px; font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    border: 1px solid transparent;
    border-bottom: none;
    color: var(--text-muted);
    background: transparent;
    position: relative; bottom: -1px;
    transition: all 0.15s;
    display: flex; align-items: center; gap: 6px;
}
.bez-tab.active {
    background: var(--navy-900);
    border-color: var(--border);
    color: var(--text-primary);
}
.bez-tab .cnt {
    font-size: 10px; font-weight: 700;
    padding: 1px 6px; border-radius: 99px;
}
.bez-tab.t-kurang .cnt { background: rgba(248,113,113,0.15); color: var(--accent-red); }
.bez-tab.t-cukup  .cnt { background: rgba(52,211,153,0.15);  color: var(--accent-green); }
.bez-tab.t-lebih  .cnt { background: rgba(56,189,248,0.15);  color: var(--accent-blue); }

/* Search */
.bez-search {
    display: flex; gap: 6px; margin-bottom: 10px;
}
.bez-search input, .bez-search select {
    background: var(--navy-800);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 12px;
    padding: 6px 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.bez-search input { flex: 1; }
.bez-search input::placeholder { color: var(--text-muted); }

/* Table */
.bez-table-wrap { max-height: 340px; overflow-y: auto; }
.bez-table-wrap::-webkit-scrollbar { width: 4px; }
.bez-table-wrap::-webkit-scrollbar-track { background: transparent; }
.bez-table-wrap::-webkit-scrollbar-thumb { background: rgba(56,189,248,0.2); border-radius: 2px; }

.bez-table { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
.bez-table thead th {
    padding: 7px 8px;
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.4px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    text-align: left;
    background: var(--navy-800);
    position: sticky; top: 0; z-index: 1;
}
.bez-table thead th.r { text-align: right; }
.bez-table thead th.c { text-align: center; }
.bez-table tbody td {
    padding: 9px 8px;
    border-bottom: 1px solid rgba(56,189,248,0.05);
    color: var(--text-primary);
    vertical-align: middle;
}
.bez-table tbody tr:hover td { background: rgba(56,189,248,0.03); }
.bez-table tbody tr:last-child td { border-bottom: none; }
.bez-table td.r { text-align: right; }
.bez-table td.c { text-align: center; }

.kat-badge {
    display: inline-block; font-size: 9px; font-weight: 700;
    padding: 1px 5px; border-radius: 4px; margin-top: 2px;
    text-transform: uppercase; letter-spacing: 0.3px;
}
.kat-dokter  { background: rgba(56,189,248,0.12);  color: var(--accent-blue); }
.kat-perawat { background: rgba(45,212,191,0.12);  color: var(--accent-teal); }
.kat-farmasi { background: rgba(245,158,11,0.12);  color: var(--accent-amber); }
.kat-medis   { background: rgba(167,139,250,0.12); color: var(--accent-purple); }
.kat-lainnya { background: rgba(148,163,184,0.12); color: var(--text-muted); }

.prog-wrap { width: 100%; height: 5px; background: var(--navy-700); border-radius: 99px; overflow: hidden; margin-top: 3px; }
.prog-bar  { height: 100%; border-radius: 99px; }
.prog-red   { background: var(--accent-red); }
.prog-green { background: var(--accent-green); }
.prog-blue  { background: var(--accent-blue); }

.delta-badge {
    display: inline-block; font-size: 11px; font-weight: 700;
    padding: 2px 8px; border-radius: 6px;
}
.delta-red   { background: rgba(248,113,113,0.15); color: var(--accent-red); }
.delta-green { background: rgba(52,211,153,0.15);  color: var(--accent-green); }
.delta-blue  { background: rgba(56,189,248,0.15);  color: var(--accent-blue); }

.bez-empty {
    text-align: center; padding: 24px;
    color: var(--text-muted); font-size: 12px;
}

/* ── SHIFT ── */
.shift-list { display: flex; flex-direction: column; gap: 12px; }
.shift-item {
    display: flex; align-items: stretch;
    border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden;
}
.shift-side { flex: 0 0 90px; padding: 14px 12px; display: flex; flex-direction: column; justify-content: center; }
.shift-side.pagi  { background: rgba(245,158,11,0.1); }
.shift-side.siang { background: rgba(251,146,60,0.1); }
.shift-side.malam { background: rgba(56,189,248,0.1); }
.shift-name { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1; margin-bottom: 3px; }
.shift-side.pagi  .shift-name { color: var(--accent-amber); }
.shift-side.siang .shift-name { color: var(--accent-orange); }
.shift-side.malam .shift-name { color: var(--accent-blue); }
.shift-detail {
    flex: 1; padding: 12px 14px;
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 4px 16px; background: var(--navy-800);
}
.shift-prof-row { display: flex; align-items: center; justify-content: space-between; }
.shift-prof-name { font-size: 11px; color: var(--text-muted); }
.shift-prof-val  { font-size: 12px; font-weight: 700; color: var(--text-primary); }
.shift-total-bubble {
    flex: 0 0 64px;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: var(--navy-700); padding: 10px;
}
.stb-val { font-size: 20px; font-weight: 800; line-height: 1; }
.stb-val.pagi  { color: var(--accent-amber); }
.stb-val.siang { color: var(--accent-orange); }
.stb-val.malam { color: var(--accent-blue); }
.stb-label { font-size: 10px; color: var(--text-muted); margin-top: 2px; font-weight: 600; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
    .sdm-stat-grid, .sdm-stat-grid-2 { grid-template-columns: repeat(3, 1fr); }
    .bez-summary { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .sdm-bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .sdm-stat-grid, .sdm-stat-grid-2 { grid-template-columns: repeat(2, 1fr); }
    .bez-summary { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')
<div class="sdm-wrap">

    {{-- DATE BAR --}}
    <div class="sdm-datebar">
        <a href="{{ url('dashboard') }}" class="btn-home">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Home
        </a>
        <div class="sdm-datebar-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
        </div>
    </div>

    {{-- STAT ROW 1 --}}
    <div class="sdm-stat-grid">
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Total Pegawai</div>
                <div class="sdm-sc-val">{{ number_format($totalPegawai) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">100% dari keseluruhan</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">PNS</div>
                <div class="sdm-sc-val">{{ number_format($totalPns) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctPns }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-indigo">
                <svg viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">P3K</div>
                <div class="sdm-sc-val">{{ number_format($totalP3k) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctP3k }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-sky">
                <svg viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">P3K Paruh Waktu</div>
                <div class="sdm-sc-val">{{ number_format($totalP3kParuhWaktu) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctP3kParuh }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="#fb923c" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Kontrak</div>
                <div class="sdm-sc-val">{{ number_format($totalKontrak) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctKontrak }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-emerald">
                <svg viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Tetap</div>
                <div class="sdm-sc-val">{{ number_format($totalTetap) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctTetap }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Orientasi</div>
                <div class="sdm-sc-val">{{ number_format($totalOrientasi) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctOrientasi }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-rose">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fb7185" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Medis</div>
                <div class="sdm-sc-val">{{ number_format($totalMedis) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctMedis }}% dari total</div>
            </div>
        </div>
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-cyan">
                <svg viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
            </div>
            <div>
                <div class="sdm-sc-label">Non Medis</div>
                <div class="sdm-sc-val">{{ number_format($totalNonMedis) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $pctNonMedis }}% dari total</div>
            </div>
        </div>
    </div>

    {{-- BEZETTING FULL WIDTH --}}
    <div class="sdm-panel sdm-bar-panel">
        <div class="sdm-panel-hd" style="margin-bottom:12px">
            <div class="sdm-panel-title">Bezetting SDM</div>
            <span style="font-size:11px; color:var(--text-muted);">Diperbarui tiap 1 jam</span>
        </div>

            {{-- Summary cards --}}
            <div class="bez-summary">
                <div class="bez-sc">
                    <div class="bez-sc-label">Total Jabatan</div>
                    <div class="bez-sc-val">{{ $bezSummary['total'] }}</div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-label">Kekurangan</div>
                    <div class="bez-sc-val red">{{ $bezSummary['totalKurang'] }}</div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-label">Total Orang Kurang</div>
                    <div class="bez-sc-val red">{{ $bezSummary['totalOrangKurang'] }}</div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-label">Surplus</div>
                    <div class="bez-sc-val teal">{{ $bezSummary['totalLebih'] }}</div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bez-tabs">
                <div class="bez-tab t-kurang active" onclick="bezSetTab('kurang', this)">
                    Kekurangan <span class="cnt">{{ $bezSummary['totalKurang'] }}</span>
                </div>
                <div class="bez-tab t-cukup" onclick="bezSetTab('cukup', this)">
                    Cukup <span class="cnt">{{ $bezSummary['totalCukup'] }}</span>
                </div>
                <div class="bez-tab t-lebih" onclick="bezSetTab('lebih', this)">
                    Surplus <span class="cnt">{{ $bezSummary['totalLebih'] }}</span>
                </div>
            </div>

            {{-- Search + filter --}}
            <div class="bez-search">
                <input type="text" id="bez-search" placeholder="Cari jabatan..." oninput="bezRender()">
                <select id="bez-kat" onchange="bezRender()" style="min-width:110px">
                    <option value="">Semua</option>
                    <option>Dokter</option>
                    <option>Perawat</option>
                    <option>Farmasi</option>
                    <option>Medis Lainnya</option>
                    <option>Lainnya</option>
                </select>
            </div>

            {{-- Tabel full width --}}
            <div class="bez-table-wrap">
                <table class="bez-table">
                    <thead>
                        <tr>
                            <th style="width:28px">#</th>
                            <th>Jabatan</th>
                            <th class="r" style="width:62px">Butuh</th>
                            <th style="width:130px">Tersedia</th>
                            <th class="c" style="width:60px">Delta</th>
                        </tr>
                    </thead>
                    <tbody id="bez-tbody"></tbody>
                </table>
            </div>
        </div>

    {{-- BOTTOM ROW: BAR CHART + SHIFT --}}
    <div class="sdm-bottom-row">

        {{-- Bar Chart Distribusi Status Kepegawaian --}}
        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div class="sdm-panel-title">Distribusi Pegawai Berdasarkan Status Kepegawaian</div>
            </div>
            <canvas id="statusBarChart" height="200"></canvas>
        </div>

        {{-- Shift Hari Ini --}}
        <div class="sdm-panel">
            <div class="sdm-panel-hd">
                <div class="sdm-panel-title">Shift Hari Ini</div>
            </div>
            <div class="shift-list">
                @php $shiftCls = ['PAGI' => 'pagi', 'SIANG' => 'siang', 'MALAM' => 'malam']; @endphp
                @foreach (['PAGI', 'SIANG', 'MALAM'] as $shift)
                @php
                    $cls     = $shiftCls[$shift];
                    $summary = $shiftSummary[$shift] ?? ['total' => 0, 'detail' => []];
                @endphp
                <div class="shift-item">
                    <div class="shift-side {{ $cls }}">
                        <div class="shift-name">{{ $shift }}</div>
                    </div>
                    <div class="shift-detail">
                        @forelse ($summary['detail'] as $profesi => $jumlah)
                            <div class="shift-prof-row">
                                <span class="shift-prof-name">{{ $profesi }}</span>
                                <span class="shift-prof-val">{{ $jumlah }}</span>
                            </div>
                        @empty
                            <div style="font-size:11px; color:#94a3b8; grid-column:1/-1; align-self:center;">
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
// ── BAR CHART ─────────────────────────────────────────────────────────────
new Chart(document.getElementById('statusBarChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($statusLabels) !!},
        datasets: [{
            data: {!! json_encode($statusValues) !!},
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
                    callback: v => v.toLocaleString('id-ID'),
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
                callbacks: { label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} orang` }
            }
        }
    },
    plugins: [{
        id: 'topLabel',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            chart.data.datasets.forEach((ds, i) => {
                chart.getDatasetMeta(i).data.forEach((bar, idx) => {
                    const v = ds.data[idx];
                    if (!v) return;
                    ctx.save();
                    ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';
                    ctx.fillStyle = '#94a3b8';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(v.toLocaleString('id-ID'), bar.x, bar.y - 4);
                    ctx.restore();
                });
            });
        }
    }]
});

// ── BEZETTING TABLE ────────────────────────────────────────────────────────
const BEZ = {
    kurang: {!! json_encode($bezSummary['kurang']->map(fn($r) => ['j'=>$r->jabatan,'k'=>$r->kebutuhan,'t'=>$r->tersedia,'d'=>$r->delta,'p'=>$r->pct,'kat'=>$r->kategori])->values()) !!},
    cukup:  {!! json_encode($bezSummary['cukup']->map(fn($r) => ['j'=>$r->jabatan,'k'=>$r->kebutuhan,'t'=>$r->tersedia,'d'=>$r->delta,'p'=>$r->pct,'kat'=>$r->kategori])->values()) !!},
    lebih:  {!! json_encode($bezSummary['lebih']->map(fn($r) => ['j'=>$r->jabatan,'k'=>$r->kebutuhan,'t'=>$r->tersedia,'d'=>$r->delta,'p'=>$r->pct,'kat'=>$r->kategori])->values()) !!},
};

let bezTab = 'kurang';

function bezSetTab(tab, el) {
    bezTab = tab;
    document.querySelectorAll('.bez-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    bezRender();
}

function katClass(kat) {
    const m = {'Dokter':'kat-dokter','Perawat':'kat-perawat','Farmasi':'kat-farmasi','Medis Lainnya':'kat-medis'};
    return m[kat] || 'kat-lainnya';
}

function bezRender() {
    const q   = document.getElementById('bez-search').value.toLowerCase();
    const kat = document.getElementById('bez-kat').value;
    const rows = BEZ[bezTab].filter(r =>
        (!q   || r.j.toLowerCase().includes(q)) &&
        (!kat || r.kat === kat)
    );

    const tbody = document.getElementById('bez-tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="bez-empty">Tidak ada data</td></tr>`;
        return;
    }

    const progCls  = bezTab === 'kurang' ? 'prog-red'   : bezTab === 'cukup' ? 'prog-green' : 'prog-blue';
    const deltaCls = bezTab === 'kurang' ? 'delta-red'  : bezTab === 'cukup' ? 'delta-green': 'delta-blue';

    tbody.innerHTML = rows.map((r, i) => {
        const sign = r.d > 0 ? '+' : r.d === 0 ? '=' : '';
        return `<tr>
            <td style="color:var(--text-muted);font-size:11px">${i+1}</td>
            <td>
                <div style="font-size:12px;line-height:1.3">${r.j}</div>
                <span class="kat-badge ${katClass(r.kat)}">${r.kat}</span>
            </td>
            <td class="r" style="font-weight:600">${r.k}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <span style="font-weight:600;min-width:24px">${r.t}</span>
                    <div style="flex:1">
                        <div class="prog-wrap"><div class="prog-bar ${progCls}" style="width:${r.p}%"></div></div>
                        <div style="font-size:9px;color:var(--text-muted);margin-top:1px">${r.p}%</div>
                    </div>
                </div>
            </td>
            <td class="c"><span class="delta-badge ${deltaCls}">${sign}${r.d}</span></td>
        </tr>`;
    }).join('');
}

bezRender();
</script>
@endpush
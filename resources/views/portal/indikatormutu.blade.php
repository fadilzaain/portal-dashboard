@extends('layouts.app')
@section('title', 'Dashboard Indikator Mutu')

@push('styles')
<style>
    /* ── Palette Dark Navy ─────────────────────────────────────── */
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
        --text-primary: #e2e8f0;
        --text-muted:   #94a3b8;
        --border:       rgba(56, 189, 248, 0.15);
    }

    body { background: var(--navy-950); color: var(--text-primary); }

    /* ── Card ──────────────────────────────────────────────────── */
    .im-card {
        background: var(--navy-900);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
    .im-card-title {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--accent-blue);
        margin-bottom: .25rem;
    }
    .im-card-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    /* ── Filter Bar ─────────────────────────────────────────────── */
    .filter-bar {
        background: var(--navy-900);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
    .filter-label {
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .35rem;
        display: block;
    }
    .filter-select {
        background: var(--navy-800);
        border: 1px solid var(--border);
        color: var(--text-primary);
        border-radius: 8px;
        padding: .45rem .75rem;
        font-size: .85rem;
        width: 100%;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .6rem center;
        padding-right: 2rem;
        cursor: pointer;
        transition: border-color .2s;
    }
    .filter-select:focus {
        outline: none;
        border-color: var(--accent-blue);
    }
    .filter-select option { background: var(--navy-800); }

    .btn-filter {
        background: linear-gradient(135deg, var(--navy-600), var(--navy-500));
        border: 1px solid var(--accent-blue);
        color: var(--accent-blue);
        border-radius: 8px;
        padding: .48rem 1.25rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
    }
    .btn-filter:hover {
        background: var(--accent-blue);
        color: var(--navy-900);
    }
    .btn-reset {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-muted);
        border-radius: 8px;
        padding: .48rem .85rem;
        font-size: .85rem;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-reset:hover { border-color: var(--accent-red); color: var(--accent-red); }

    /* ── Badge Jenis Mutu ───────────────────────────────────────── */
    .badge-nasional {
        background: rgba(56,189,248,.15);
        color: var(--accent-blue);
        border: 1px solid rgba(56,189,248,.3);
        border-radius: 20px;
        padding: .15rem .6rem;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        white-space: nowrap;
    }
    .badge-prioritas {
        background: rgba(245,158,11,.15);
        color: var(--accent-amber);
        border: 1px solid rgba(245,158,11,.3);
        border-radius: 20px;
        padding: .15rem .6rem;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        white-space: nowrap;
    }
    .badge-tercapai {
        background: rgba(52,211,153,.15);
        color: var(--accent-green);
        border: 1px solid rgba(52,211,153,.3);
        border-radius: 6px;
        padding: .15rem .5rem;
        font-size: .72rem;
        font-weight: 700;
    }
    .badge-belum {
        background: rgba(248,113,113,.15);
        color: var(--accent-red);
        border: 1px solid rgba(248,113,113,.3);
        border-radius: 6px;
        padding: .15rem .5rem;
        font-size: .72rem;
        font-weight: 700;
    }

    /* ── Tabel ──────────────────────────────────────────────────── */
    .im-table-wrapper {
        background: var(--navy-900);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }
    .im-table-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .im-table-header h2 {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: .5rem;
        margin: 0;
    }
    .im-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .82rem;
    }
    .im-table thead th {
        background: var(--navy-800);
        color: var(--text-muted);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: .65rem .85rem;
        white-space: nowrap;
        text-align: center;
        border-bottom: 1px solid var(--border);
    }
    .im-table thead th:first-child { text-align: left; }
    .im-table tbody tr {
        border-bottom: 1px solid rgba(56,189,248,.06);
        transition: background .15s;
    }
    .im-table tbody tr:hover { background: rgba(56,189,248,.04); }
    .im-table tbody tr:last-child { border-bottom: none; }
    .im-table td {
        padding: .75rem .85rem;
        color: var(--text-primary);
        text-align: center;
        vertical-align: middle;
    }
    .im-table td:first-child { text-align: left; }
    .im-table td.num { font-variant-numeric: tabular-nums; }

    .indikator-nama { font-weight: 600; color: var(--text-primary); line-height: 1.35; }
    .indikator-kode { font-size: .7rem; color: var(--accent-blue); font-weight: 700; margin-bottom: .2rem; }

    .target-cell {
        font-weight: 700;
        color: var(--accent-amber);
    }
    .capaian-cell { font-weight: 700; }
    .capaian-ok   { color: var(--accent-green); }
    .capaian-fail { color: var(--accent-red); }
    .capaian-null { color: var(--text-muted); font-style: italic; font-size: .75rem; }

    .bulan-group-header {
        background: var(--navy-800) !important;
        color: var(--accent-blue) !important;
        font-size: .7rem !important;
        padding: .4rem .5rem !important;
    }

    /* ── Progress bar dalam sel ─────────────────────────────────── */
    .progress-bar-wrap {
        background: rgba(255,255,255,.08);
        border-radius: 4px;
        height: 4px;
        margin-top: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 4px;
        border-radius: 4px;
        transition: width .4s ease;
    }

    /* ── Grafik ─────────────────────────────────────────────────── */
    .chart-wrapper {
        background: var(--navy-900);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
    .chart-title {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    /* ── Loading Overlay ────────────────────────────────────────── */
    .loading-overlay {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(5,13,26,.75);
        border-radius: 12px;
        z-index: 10;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .75rem;
    }
    .loading-overlay.active { display: flex; }
    .spinner {
        width: 36px;
        height: 36px;
        border: 3px solid rgba(56,189,248,.2);
        border-top-color: var(--accent-blue);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Breadcrumb ─────────────────────────────────────────────── */
    .page-header { margin-bottom: 1.5rem; }
    .page-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: .6rem;
    }
    .page-subtitle { font-size: .82rem; color: var(--text-muted); margin-top: .2rem; }

    /* ── Responsive ─────────────────────────────────────────────── */
    .table-scroll { overflow-x: auto; }
    @media (max-width: 768px) {
        .filter-grid { flex-direction: column; }
        .im-card-value { font-size: 1.4rem; }
    }

    /* ── Scrollbar ──────────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: var(--navy-950); }
    ::-webkit-scrollbar-thumb { background: var(--navy-600); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--navy-500); }

    /* ── Periode badge ─────────────────────────────────────────── */
    .periode-badge {
        background: rgba(56,189,248,.1);
        border: 1px solid rgba(56,189,248,.25);
        color: var(--accent-blue);
        border-radius: 6px;
        padding: .2rem .65rem;
        font-size: .75rem;
        font-weight: 600;
    }

    /* ── Summary row ───────────────────────────────────────────── */
    .summary-row td {
        background: var(--navy-800) !important;
        font-size: .75rem;
        color: var(--text-muted);
        font-style: italic;
    }
</style>
@endpush

@section('content')
<div style="min-height:100vh; background: var(--navy-950); padding: 1.5rem;">

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--accent-blue)">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Dashboard Indikator Mutu
        </div>
        <div class="page-subtitle">Monitoring capaian indikator mutu nasional dan prioritas rumah sakit</div>
    </div>

    {{-- ── Summary Cards ───────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="im-card">
            <div class="im-card-title">Total Indikator</div>
            <div class="im-card-value" id="meta-total">–</div>
        </div>
        <div class="im-card">
            <div class="im-card-title">Tercapai</div>
            <div class="im-card-value" id="meta-tercapai" style="color:var(--accent-green)">–</div>
        </div>
        <div class="im-card">
            <div class="im-card-title">Belum Tercapai</div>
            <div class="im-card-value" id="meta-belum" style="color:var(--accent-red)">–</div>
        </div>
        <div class="im-card">
            <div class="im-card-title">Periode</div>
            <div id="meta-periode" style="margin-top:.35rem">
                <span class="periode-badge">– / –</span>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────────────────── --}}
    <div class="filter-bar" style="margin-bottom:1.5rem;">
        <div style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--accent-blue); margin-bottom:1rem; display:flex; align-items:center; gap:.4rem;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filter Data
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;" class="filter-grid">

            {{-- Jenis Mutu --}}
            <div style="min-width:140px; flex:1;">
                <label class="filter-label">Jenis Mutu</label>
                <select id="filter-jenis" class="filter-select">
                    <option value="">Semua</option>
                    <option value="nasional">Nasional</option>
                    <option value="prioritas">Prioritas</option>
                </select>
            </div>

            {{-- Triwulan --}}
            <div style="min-width:140px; flex:1;">
                <label class="filter-label">Triwulan</label>
                <select id="filter-triwulan" class="filter-select">
                    <option value="">Semua Triwulan</option>
                    <option value="1">Triwulan I (Jan–Mar)</option>
                    <option value="2">Triwulan II (Apr–Jun)</option>
                    <option value="3">Triwulan III (Jul–Sep)</option>
                    <option value="4">Triwulan IV (Okt–Des)</option>
                </select>
            </div>

            {{-- Tahun --}}
            <div style="min-width:120px; flex:1;">
                <label class="filter-label">Tahun</label>
                <select id="filter-tahun" class="filter-select">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $filters['tahun'] ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div style="display:flex; gap:.5rem; padding-bottom:0;">
                <button class="btn-filter" onclick="loadData()">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan
                </button>
                <button class="btn-reset" onclick="resetFilter()">Reset</button>
            </div>
        </div>
    </div>

    {{-- ── Tabel Indikator ─────────────────────────────────────────── --}}
    <div style="position:relative; margin-bottom:1.5rem;">
        <div class="loading-overlay" id="loading-tabel">
            <div class="spinner"></div>
            <span style="font-size:.8rem; color:var(--text-muted);">Memuat data…</span>
        </div>

        <div class="im-table-wrapper">
            <div class="im-table-header">
                <h2>
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--accent-blue)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Data Capaian Indikator Mutu
                </h2>
                <span id="periode-label" class="periode-badge" style="font-size:.75rem;"></span>
            </div>

            <div class="table-scroll">
                <table class="im-table" id="tabel-indikator">
                    <thead>
                        <tr id="thead-row">
                            <th style="min-width:260px; text-align:left;">Indikator</th>
                            <th>Target</th>
                            {{-- kolom bulan bisa render via js --}}
                        </tr>
                    </thead>
                    <tbody id="tbody-indikator">
                        <tr>
                            <td colspan="20" style="text-align:center; color:var(--text-muted); padding:2rem;">
                                Pilih filter dan klik <strong style="color:var(--accent-blue)">Tampilkan</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Grafik ──────────────────────────────────────────────────── --}}
    <div style="position:relative;">
        <div class="loading-overlay" id="loading-grafik">
            <div class="spinner"></div>
            <span style="font-size:.8rem; color:var(--text-muted);">Memuat grafik…</span>
        </div>

        <div class="chart-wrapper">
            <div class="chart-title">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--accent-blue)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                Tren Capaian Indikator Mutu
            </div>
            <div style="position:relative; height:340px;">
                <canvas id="grafikIndikator"></canvas>
                <div id="grafik-empty" style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text-muted); font-size:.85rem; flex-direction:column; gap:.5rem;">
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="opacity:.4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Grafik akan muncul setelah data dimuat
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
// ─── State & Chart Instance ────────────────────────────────────────────────
let grafikInstance = null;

// ─── Load Data ─────────────────────────────────────────────────────────────
async function loadData() {
    const params = new URLSearchParams({
        jenis_mutu: document.getElementById('filter-jenis').value,
        triwulan:   document.getElementById('filter-triwulan').value,
        tahun:      document.getElementById('filter-tahun').value,
    });

    setLoading(true);

    try {
        const res  = await fetch(`{{ route('portal.indikatormutu.data') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();

        if (!json.success) throw new Error('Gagal memuat data');

        renderMeta(json.meta, json.filters);
        renderTabel(json.tabel, json.filters);
        renderGrafik(json.grafik);

    } catch (err) {
        console.error(err);
        document.getElementById('tbody-indikator').innerHTML =
            `<tr><td colspan="20" style="text-align:center;color:var(--accent-red);padding:2rem;">
                Gagal memuat data. Coba lagi.
            </td></tr>`;
    } finally {
        setLoading(false);
    }
}

// ─── Render Summary Cards ──────────────────────────────────────────────────
function renderMeta(meta, filters) {
    document.getElementById('meta-total').textContent    = meta.total_indikator ?? '–';
    document.getElementById('meta-tercapai').textContent = meta.tercapai ?? '–';
    document.getElementById('meta-belum').textContent    = meta.belum_tercapai ?? '–';

    const triwulanLabel = filters.triwulan ? `Triwulan ${toRoman(filters.triwulan)}` : 'Semua';
    const periodeText   = `${triwulanLabel} / ${filters.tahun}`;
    document.getElementById('meta-periode').innerHTML =
        `<span class="periode-badge">${periodeText}</span>`;
    document.getElementById('periode-label').textContent = periodeText;
}

// ─── Render Tabel ──────────────────────────────────────────────────────────
function renderTabel(tabelData, filters) {
    if (!tabelData.length) {
        document.getElementById('tbody-indikator').innerHTML =
            `<tr><td colspan="20" style="text-align:center;color:var(--text-muted);padding:2rem;">Tidak ada data untuk filter yang dipilih.</td></tr>`;
        return;
    }

    // header kolom bulan dari data indikator pertama
    const firstBulanData = tabelData[0].bulan_data ?? [];
    const theadRow = document.getElementById('thead-row');
    theadRow.innerHTML = `<th style="min-width:260px; text-align:left;">Indikator</th><th>Target</th>`;
    firstBulanData.forEach(b => {
        theadRow.innerHTML += `
            <th colspan="3" class="bulan-group-header">${b.nama_bulan}</th>`;
    });
    theadRow.innerHTML += `<th>Rata-rata</th><th>Status</th>`;

    // Sub-header (Num, Denum, Capaian) per bulan
    let subheadHtml = `<tr style="background:var(--navy-800);">
        <th style="text-align:left; font-size:.65rem; color:var(--text-muted);"></th>
        <th></th>`;
    firstBulanData.forEach(() => {
        subheadHtml += `
            <th style="font-size:.62rem; color:var(--text-muted); padding:.4rem .5rem;">Num</th>
            <th style="font-size:.62rem; color:var(--text-muted); padding:.4rem .5rem;">Den</th>
            <th style="font-size:.62rem; color:var(--text-muted); padding:.4rem .5rem;">%</th>`;
    });
    subheadHtml += `<th></th><th></th></tr>`;

    // Body rows
    let bodyHtml = subheadHtml;
    tabelData.forEach(ind => {
        const isLower   = ind.is_lower_better;
        const target    = ind.target;
        const statusHtml = ind.status
            ? `<span class="badge-${ind.status}">${ind.status === 'tercapai' ? '✓ Tercapai' : '✗ Belum'}</span>`
            : `<span style="color:var(--text-muted); font-size:.75rem;">–</span>`;

        const rataClass = ind.rata_capaian !== null
            ? (isCapaianOk(ind.rata_capaian, target, isLower) ? 'capaian-ok' : 'capaian-fail')
            : 'capaian-null';

        let rowHtml = `<tr>
            <td>
                <div class="indikator-kode">${ind.kode}</div>
                <div class="indikator-nama">${ind.nama}</div>
                <div style="margin-top:.3rem;">
                    <span class="badge-${ind.jenis_mutu}">${ind.label_jenis}</span>
                </div>
            </td>
            <td class="target-cell">${target}%</td>`;

        ind.bulan_data.forEach(b => {
            const capClass = b.capaian !== null
                ? (isCapaianOk(b.capaian, target, isLower) ? 'capaian-ok' : 'capaian-fail')
                : 'capaian-null';
            const pct       = b.capaian !== null ? Math.min(b.capaian / (isLower ? target * 2 : target) * 100, 100) : 0;
            const barColor  = b.capaian !== null ? (isCapaianOk(b.capaian, target, isLower) ? 'var(--accent-green)' : 'var(--accent-red)') : 'transparent';

            rowHtml += `
                <td class="num">${b.numerator !== null ? formatNum(b.numerator) : '<span class="capaian-null">–</span>'}</td>
                <td class="num">${b.denominator !== null ? formatNum(b.denominator) : '<span class="capaian-null">–</span>'}</td>
                <td>
                    ${b.capaian !== null
                        ? `<div class="${capClass} capaian-cell">${b.capaian}%</div>
                           <div class="progress-bar-wrap">
                               <div class="progress-bar-fill" style="width:${pct}%;background:${barColor}"></div>
                           </div>`
                        : `<span class="capaian-null">–</span>`}
                </td>`;
        });

        rowHtml += `
            <td class="${rataClass} capaian-cell">${ind.rata_capaian !== null ? ind.rata_capaian + '%' : '–'}</td>
            <td>${statusHtml}</td>
        </tr>`;

        bodyHtml += rowHtml;
    });

    document.getElementById('tbody-indikator').innerHTML = bodyHtml;
}

// ─── Render Grafik ─────────────────────────────────────────────────────────
function renderGrafik(grafikData) {
    document.getElementById('grafik-empty').style.display = 'none';

    if (grafikInstance) grafikInstance.destroy();

    const ctx = document.getElementById('grafikIndikator').getContext('2d');

    grafikInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels:   grafikData.labels,
            datasets: grafikData.datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        boxWidth: 14,
                        padding: 16,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: '#0a1628',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(56,189,248,.25)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        afterBody(items) {
                            const ds  = grafikData.datasets[items[0].datasetIndex];
                            return ds.target !== undefined ? [`Target: ${ds.target}%`] : [];
                        }
                    }
                },
            },
            scales: {
                x: {
                    grid:  { color: 'rgba(255,255,255,.05)' },
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                },
                y: {
                    min:  0,
                    max:  105,
                    grid:  { color: 'rgba(255,255,255,.05)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        callback: v => v + '%',
                    },
                },
            },
        },
    });

    // Garis target per dataset (afterDraw)
    // Tambah chartjs-plugin-annotation jika perlu
}

// ─── Helpers ───────────────────────────────────────────────────────────────
function isCapaianOk(capaian, target, isLower) {
    return isLower ? capaian <= target : capaian >= target;
}
function formatNum(n) {
    return Number.isInteger(n) ? n : parseFloat(n).toFixed(1);
}
function toRoman(n) {
    return ['I','II','III','IV'][n-1] ?? n;
}
function setLoading(show) {
    document.getElementById('loading-tabel').classList.toggle('active', show);
    document.getElementById('loading-grafik').classList.toggle('active', show);
}
function resetFilter() {
    document.getElementById('filter-jenis').value     = '';
    document.getElementById('filter-triwulan').value  = '';
    document.getElementById('filter-tahun').value     = '{{ $filters['tahun'] }}';
    document.getElementById('tbody-indikator').innerHTML =
        `<tr><td colspan="20" style="text-align:center;color:var(--text-muted);padding:2rem;">Pilih filter dan klik <strong style="color:var(--accent-blue)">Tampilkan</strong></td></tr>`;
    if (grafikInstance) {
        grafikInstance.destroy();
        grafikInstance = null;
        document.getElementById('grafik-empty').style.display = 'flex';
    }
    document.getElementById('meta-total').textContent    = '–';
    document.getElementById('meta-tercapai').textContent = '–';
    document.getElementById('meta-belum').textContent    = '–';
}

// Load otomatis saat halaman dibuka
document.addEventListener('DOMContentLoaded', loadData);
</script>
@endpush
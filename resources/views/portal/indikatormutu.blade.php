@extends('layouts.app')
@section('title', 'Dashboard Indikator Mutu')

@push('styles')
<style>
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
        --text-primary: #e2e8f0;
        --text-muted:   #94a3b8;
        --border:       rgba(56,189,248,.15);
    }

    body { background: var(--navy-950); color: var(--text-primary); }

    /* ── Filter Bar ─── */
    .filter-bar { background:var(--navy-900); border:1px solid var(--border); border-radius:12px; padding:1.25rem 1.5rem; }
    .filter-label { font-size:.68rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--text-muted); margin-bottom:.3rem; display:block; }
    .filter-select {
        background:var(--navy-800); border:1px solid var(--border); color:var(--text-primary);
        border-radius:8px; padding:.45rem .75rem; font-size:.85rem; width:100%;
        appearance:none;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right .6rem center; padding-right:2rem;
        cursor:pointer; transition:border-color .2s;
    }
    .filter-select:focus { outline:none; border-color:var(--accent-blue); }
    .filter-select option { background:var(--navy-800); }

    .btn-filter {
        background:linear-gradient(135deg,var(--navy-600),var(--navy-500));
        border:1px solid var(--accent-blue); color:var(--accent-blue);
        border-radius:8px; padding:.48rem 1.25rem; font-size:.85rem; font-weight:600;
        cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:.4rem; white-space:nowrap;
    }
    .btn-filter:hover { background:var(--accent-blue); color:var(--navy-900); }
    .btn-reset { background:transparent; border:1px solid var(--border); color:var(--text-muted); border-radius:8px; padding:.48rem .85rem; font-size:.85rem; cursor:pointer; transition:all .2s; }
    .btn-reset:hover { border-color:var(--accent-red); color:var(--accent-red); }

    /* ── Badges ─── */
    .badge-nasional  { background:rgba(56,189,248,.15);  color:var(--accent-blue);   border:1px solid rgba(56,189,248,.3);  border-radius:20px; padding:.15rem .6rem; font-size:.67rem; font-weight:700; letter-spacing:.04em; white-space:nowrap; }
    .badge-prioritas { background:rgba(245,158,11,.15);  color:var(--accent-amber);  border:1px solid rgba(245,158,11,.3);  border-radius:20px; padding:.15rem .6rem; font-size:.67rem; font-weight:700; letter-spacing:.04em; white-space:nowrap; }
    .badge-tercapai  { background:rgba(52,211,153,.15);  color:var(--accent-green);  border:1px solid rgba(52,211,153,.3);  border-radius:6px;  padding:.15rem .5rem; font-size:.72rem; font-weight:700; }
    .badge-belum     { background:rgba(248,113,113,.15); color:var(--accent-red);    border:1px solid rgba(248,113,113,.3); border-radius:6px;  padding:.15rem .5rem; font-size:.72rem; font-weight:700; }

    /* ── Tabel ─── */
    .im-table-wrapper { background:var(--navy-900); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .im-table-header  { padding:1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
    .im-table-header h2 { font-size:.95rem; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:.5rem; margin:0; }

    .im-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .im-table thead th {
        background:var(--navy-800); color:var(--text-muted);
        font-size:.67rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
        padding:.65rem .85rem; white-space:nowrap; text-align:center;
        border-bottom:1px solid var(--border);
    }
    .im-table thead th:first-child { text-align:left; }
    .im-table tbody tr { border-bottom:1px solid rgba(56,189,248,.06); transition:background .15s; }
    .im-table tbody tr:hover { background:rgba(56,189,248,.04); }
    .im-table tbody tr:last-child { border-bottom:none; }
    .im-table td { padding:.75rem .85rem; color:var(--text-primary); text-align:center; vertical-align:middle; }
    .im-table td:first-child { text-align:left; }

    .indikator-nama { font-weight:600; color:var(--text-primary); line-height:1.4; }
    .target-cell    { font-weight:700; color:var(--accent-amber); }
    .capaian-ok     { color:var(--accent-green); font-weight:700; }
    .capaian-fail   { color:var(--accent-red);   font-weight:700; }
    .capaian-null   { color:var(--text-muted);   font-style:italic; font-size:.75rem; }

    /* progress bar */
    .pb-wrap { background:rgba(255,255,255,.07); border-radius:4px; height:4px; margin-top:3px; overflow:hidden; }
    .pb-fill  { height:4px; border-radius:4px; transition:width .4s ease; }

    /* ── Grafik ─── */
    .chart-wrap { background:var(--navy-900); border:1px solid var(--border); border-radius:12px; overflow:hidden; }

    /* Toggle NDR */
    .ndr-toggle-group { display:flex; flex-wrap:wrap; gap:.4rem; padding:.75rem 1.25rem; border-top:1px solid var(--border); }
    .ndr-toggle-btn {
        background:var(--navy-800); border:1px solid var(--border); color:var(--text-muted);
        border-radius:20px; padding:.2rem .65rem; font-size:.72rem; font-weight:600;
        cursor:pointer; transition:all .2s; user-select:none;
    }
    .ndr-toggle-btn.active { border-color:var(--accent-red); color:var(--accent-red); background:rgba(248,113,113,.1); }
    .ndr-toggle-btn.active-total { border-color:var(--accent-red); color:var(--accent-red); background:rgba(248,113,113,.15); }

    /* Insight bar */
    .insight-bar { margin:0 1.25rem 1rem; padding:.65rem 1rem; border-radius:8px; font-size:.78rem; color:var(--text-muted); display:flex; align-items:center; gap:.5rem; }
    .insight-capaian { background:rgba(56,189,248,.06); border:1px solid rgba(56,189,248,.15); }
    .insight-ndr     { background:rgba(248,113,113,.06); border:1px solid rgba(248,113,113,.15); }

    /* Loading */
    .loading-overlay { display:none; position:absolute; inset:0; background:rgba(5,13,26,.75); border-radius:12px; z-index:10; align-items:center; justify-content:center; flex-direction:column; gap:.75rem; }
    .loading-overlay.active { display:flex; }
    .spinner { width:36px; height:36px; border:3px solid rgba(56,189,248,.2); border-top-color:var(--accent-blue); border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* Scrollbar */
    ::-webkit-scrollbar { width:6px; height:6px; }
    ::-webkit-scrollbar-track { background:var(--navy-950); }
    ::-webkit-scrollbar-thumb { background:var(--navy-600); border-radius:3px; }
    ::-webkit-scrollbar-thumb:hover { background:var(--navy-500); }

    .table-scroll { overflow-x:auto; }
    @media (max-width:768px) { .filter-grid { flex-direction:column; } }

    /* Triwulan tab */
    .tw-tabs { display:flex; gap:.4rem; }
    .tw-tab {
        background:var(--navy-800); border:1px solid var(--border); color:var(--text-muted);
        border-radius:8px; padding:.3rem .75rem; font-size:.78rem; font-weight:600;
        cursor:pointer; transition:all .2s;
    }
    .tw-tab.active { background:rgba(56,189,248,.1); border-color:var(--accent-blue); color:var(--accent-blue); }
    .tw-tab:hover:not(.active) { border-color:rgba(56,189,248,.35); color:var(--text-primary); }
</style>
@endpush

@section('content')
<div style="min-height:100vh; background:#050d1a; padding:1.5rem; font-family:'DM Sans',sans-serif;">

  {{-- ── PAGE HEADER ── --}}
  <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:.75rem;">
      <div style="background:rgba(56,189,248,.15); border-radius:10px; padding:.6rem; display:flex;">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--accent-blue)" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <div style="font-size:1.5rem; font-weight:800; color:#e2e8f0; letter-spacing:-.3px;">Dashboard Indikator Mutu</div>
        <div style="font-size:.82rem; color:#94a3b8; margin-top:.1rem;">Monitoring capaian PMKP nasional dan prioritas rumah sakit</div>
      </div>
    </div>
    <a href="{{ url('dashboard') }}" style="background:rgba(56,189,248,.08); border:1px solid rgba(56,189,248,.2); color:var(--accent-blue); border-radius:8px; padding:.4rem .9rem; font-size:.8rem; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:.4rem;">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Home
    </a>
  </div>

  {{-- ── SUMMARY CARDS ── --}}
  <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div style="background:#0a1628; border:1px solid rgba(56,189,248,.15); border-radius:14px; padding:1.25rem 1.5rem;">
      <div style="font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#38bdf8; margin-bottom:.5rem;">TOTAL INDIKATOR</div>
      <div id="meta-total" style="font-size:2.2rem; font-weight:800; color:#e2e8f0; line-height:1;">–</div>
    </div>
    <div style="background:#0a1628; border:1px solid rgba(56,189,248,.15); border-radius:14px; padding:1.25rem 1.5rem;">
      <div style="font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#34d399; margin-bottom:.5rem;">TERCAPAI</div>
      <div id="meta-tercapai" style="font-size:2.2rem; font-weight:800; color:#34d399; line-height:1;">–</div>
    </div>
    <div style="background:#0a1628; border:1px solid rgba(56,189,248,.15); border-radius:14px; padding:1.25rem 1.5rem;">
      <div style="font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#f87171; margin-bottom:.5rem;">BELUM TERCAPAI</div>
      <div id="meta-belum" style="font-size:2.2rem; font-weight:800; color:#f87171; line-height:1;">–</div>
    </div>
    <div style="background:#0a1628; border:1px solid rgba(56,189,248,.15); border-radius:14px; padding:1.25rem 1.5rem;">
      <div style="font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#818cf8; margin-bottom:.5rem;">PERIODE</div>
      <div id="meta-periode" style="font-size:.9rem; font-weight:700; color:#e2e8f0; margin-top:.25rem;">–</div>
    </div>
  </div>

  {{-- ── DUA GRAFIK SIDE BY SIDE ── --}}
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">

    {{-- Grafik Tren Capaian --}}
    <div class="chart-wrap">
      <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid rgba(56,189,248,.1);">
        <div style="display:flex; align-items:center; gap:.5rem;">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#38bdf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          <span style="font-size:.9rem; font-weight:700; color:#e2e8f0;">Tren Capaian Rata-rata</span>
        </div>
        <div class="tw-tabs" id="tw-tabs-grafik">
          <button class="tw-tab active" data-tw="1" onclick="switchTwGrafik(1)">TW I</button>
          <button class="tw-tab" data-tw="2" onclick="switchTwGrafik(2)">TW II</button>
          <button class="tw-tab" data-tw="3" onclick="switchTwGrafik(3)">TW III</button>
          <button class="tw-tab" data-tw="4" onclick="switchTwGrafik(4)">TW IV</button>
        </div>
      </div>
      <div style="padding:1rem 1.25rem;">
        <div style="position:relative; height:300px;">
          <canvas id="grafikIndikator"></canvas>
        </div>
        <div style="display:flex; justify-content:center; gap:1.5rem; margin-top:.75rem;">
          <div style="display:flex; align-items:center; gap:.4rem; font-size:.73rem; color:#94a3b8;">
            <span style="width:22px; height:2px; background:#38bdf8; display:inline-block; border-radius:2px;"></span>Capaian (%)
          </div>
          <div style="display:flex; align-items:center; gap:.4rem; font-size:.73rem; color:#94a3b8;">
            <span style="width:22px; display:inline-block; border-top:2px dashed #34d399;"></span>Target rata-rata
          </div>
        </div>
      </div>
      <div class="insight-bar insight-capaian">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#38bdf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <span id="insight-capaian-text">Memuat insight…</span>
      </div>
    </div>

    {{-- Grafik NDR ─────────────────────── --}}
    <div class="chart-wrap">
      <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid rgba(56,189,248,.1);">
        <div style="display:flex; align-items:center; gap:.5rem;">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#f87171" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          <span style="font-size:.9rem; font-weight:700; color:#e2e8f0;">Net Death Rate (NDR)</span>
        </div>
        <div class="tw-tabs" id="tw-tabs-ndr">
          <button class="tw-tab active" data-tw="1" onclick="switchTwNdr(1)">TW I</button>
          <button class="tw-tab" data-tw="2" onclick="switchTwNdr(2)">TW II</button>
          <button class="tw-tab" data-tw="3" onclick="switchTwNdr(3)">TW III</button>
          <button class="tw-tab" data-tw="4" onclick="switchTwNdr(4)">TW IV</button>
        </div>
      </div>
      <div style="padding:1rem 1.25rem;">
        <div style="position:relative; height:300px;">
          <canvas id="grafikNDR"></canvas>
        </div>
        <div style="display:flex; justify-content:center; gap:1.5rem; margin-top:.75rem;">
          <div style="display:flex; align-items:center; gap:.4rem; font-size:.73rem; color:#94a3b8;">
            <span style="width:22px; height:2px; background:#f87171; display:inline-block; border-radius:2px;"></span>Total RS
          </div>
          <div style="display:flex; align-items:center; gap:.4rem; font-size:.73rem; color:#94a3b8;">
            <span style="width:22px; display:inline-block; border-top:2px dashed #f59e0b;"></span>Target (&lt;1.5‰)
          </div>
        </div>
      </div>
      {{-- Toggle per ruangan --}}
      <div style="padding:.5rem 1.25rem; border-top:1px solid rgba(56,189,248,.08);">
        <div style="font-size:.67rem; text-transform:uppercase; letter-spacing:.07em; color:var(--text-muted); margin-bottom:.4rem;">Tampilkan ruangan:</div>
        <div id="ndr-ruangan-toggles" class="ndr-toggle-group" style="padding:0;"></div>
      </div>
      <div class="insight-bar insight-ndr">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#f87171" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <span id="insight-ndr-text">Memuat insight…</span>
      </div>
    </div>

  </div>

  {{-- ── FILTER BAR ── --}}
  <div class="filter-bar" style="margin-bottom:1.5rem;">
    <div style="font-size:.73rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#38bdf8; margin-bottom:.85rem; display:flex; align-items:center; gap:.4rem;">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
      Filter Data Tabel
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;" class="filter-grid">
      <div style="min-width:140px; flex:1;">
        <label class="filter-label">Jenis Mutu</label>
        <select id="filter-jenis" class="filter-select">
          <option value="">Semua</option>
          <option value="nasional">Nasional</option>
          <option value="prioritas">Prioritas</option>
        </select>
      </div>
      <div style="min-width:140px; flex:1;">
        <label class="filter-label">Triwulan</label>
        <select id="filter-triwulan" class="filter-select" onchange="syncTwTabs(this.value)">
          <option value="1">Triwulan I (Jan–Mar)</option>
          <option value="2">Triwulan II (Apr–Jun)</option>
          <option value="3">Triwulan III (Jul–Sep)</option>
          <option value="4">Triwulan IV (Okt–Des)</option>
        </select>
      </div>
      <div style="min-width:120px; flex:1;">
        <label class="filter-label">Tahun</label>
        <select id="filter-tahun" class="filter-select">
          @foreach($tahunList as $t)
            <option value="{{ $t }}" {{ $t == $filters['tahun'] ? 'selected' : '' }}>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex; gap:.5rem;">
        <button class="btn-filter" onclick="loadAll()">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Tampilkan
        </button>
        <button class="btn-reset" onclick="resetFilter()">Reset</button>
      </div>
    </div>
  </div>

  {{-- ── TABEL ── --}}
  <div style="position:relative; margin-bottom:1.5rem;">
    <div class="loading-overlay" id="loading-tabel">
      <div class="spinner"></div>
      <span style="font-size:.8rem; color:#94a3b8;">Memuat data…</span>
    </div>
    <div class="im-table-wrapper">
      <div class="im-table-header">
        <h2>
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#38bdf8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Data Capaian Indikator Mutu
        </h2>
        <span id="periode-label" style="background:rgba(56,189,248,.1); border:1px solid rgba(56,189,248,.25); color:#38bdf8; border-radius:6px; padding:.2rem .65rem; font-size:.73rem; font-weight:600;"></span>
      </div>
      <div class="table-scroll">
        <table class="im-table" id="tabel-indikator">
          <thead>
            <tr id="thead-row">
              <th style="min-width:280px; text-align:left;">Indikator</th>
              <th>Target</th>
              <th>Bulan 1</th>
              <th>Bulan 2</th>
              <th>Bulan 3</th>
              <th>Triwulan</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="tbody-indikator">
            <tr>
              <td colspan="7" style="text-align:center; color:#94a3b8; padding:2.5rem;">
                Klik <strong style="color:#38bdf8">Tampilkan</strong> untuk memuat data indikator mutu
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
let grafikInstance = null;
let ndrInstance    = null;
let currentNdrDatasets = [];

// ─── Sync state ───────────────────────────────────────────────────────────
function getTw()    { return parseInt(document.getElementById('filter-triwulan').value) || 1; }
function getTahun() { return document.getElementById('filter-tahun').value; }
function getJenis() { return document.getElementById('filter-jenis').value; }

// ─── Load semua (tabel + grafik capaian + NDR) ────────────────────────────
async function loadAll() {
  await Promise.all([loadData(), loadNDR()]);
}

// ─── Load Data Tabel + Grafik Capaian ─────────────────────────────────────
async function loadData() {
  const params = new URLSearchParams({
    jenis_mutu: getJenis(),
    triwulan:   getTw(),
    tahun:      getTahun(),
  });
  setLoading(true);
  try {
    const res  = await fetch(`{{ route('portal.indikatormutu.data') }}?${params}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    renderMeta(json.meta, json.filters);
    renderTabel(json.tabel, json.filters);
    renderGrafikCapaian(json.grafik);
  } catch(e) {
    document.getElementById('tbody-indikator').innerHTML =
      `<tr><td colspan="7" style="text-align:center;color:#f87171;padding:2rem;">Gagal memuat data: ${e.message}</td></tr>`;
  } finally { setLoading(false); }
}

// ─── Load NDR ─────────────────────────────────────────────────────────────
async function loadNDR() {
  const params = new URLSearchParams({ triwulan: getTw(), tahun: getTahun() });
  try {
    const res  = await fetch(`{{ route('portal.indikatormutu.ndr') }}?${params}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    renderNDR(json.grafik);
    buildRuanganToggles(json.grafik.ruangan_list, json.grafik.datasets);
  } catch(e) {
    document.getElementById('insight-ndr-text').textContent = 'Gagal memuat NDR: ' + e.message;
  }
}

// ─── Render Meta Cards ────────────────────────────────────────────────────
function renderMeta(meta, filters) {
  document.getElementById('meta-total').textContent    = meta.total_indikator ?? 0;
  document.getElementById('meta-tercapai').textContent = meta.tercapai ?? 0;
  document.getElementById('meta-belum').textContent    = meta.belum_tercapai ?? 0;
  const twLabel = `TW ${toRoman(filters.triwulan)} / ${filters.tahun}`;
  document.getElementById('meta-periode').textContent = twLabel;
  document.getElementById('periode-label').textContent = twLabel;
}

// ─── Render Grafik Capaian ─────────────────────────────────────────────────
function renderGrafikCapaian(grafikData) {
  if (grafikInstance) grafikInstance.destroy();
  const ctx = document.getElementById('grafikIndikator').getContext('2d');
  grafikInstance = new Chart(ctx, {
    type: 'line',
    data: { labels: grafikData.labels, datasets: grafikData.datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode:'index', intersect:false },
      plugins: {
        legend: { display: false },
        tooltip: { backgroundColor:'#0a1628', titleColor:'#e2e8f0', bodyColor:'#94a3b8', borderColor:'rgba(56,189,248,.25)', borderWidth:1, padding:10,
          callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.raw ?? '–'}%` }
        },
      },
      scales: {
        x: { grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11}} },
        y: { min:0, max:105, grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11},callback:v=>v+'%'} },
      },
    },
  });

  // Insight
  const vals = (grafikData.datasets[0]?.data ?? []).filter(v => v !== null);
  if (vals.length >= 2) {
    const diff  = vals[vals.length-1] - vals[0];
    const sign  = diff >= 0 ? '+' : '';
    const color = diff >= 0 ? '#34d399' : '#f87171';
    document.getElementById('insight-capaian-text').innerHTML =
      `Rata-rata capaian ${diff >= 0 ? 'naik' : 'turun'} <span style="color:${color};font-weight:700;">${sign}${diff.toFixed(1)}%</span> dari ${grafikData.labels[0]} ke ${grafikData.labels[vals.length-1]}.`;
  } else {
    document.getElementById('insight-capaian-text').textContent = 'Belum cukup data untuk analisis tren.';
  }
}

// ─── Render Grafik NDR ─────────────────────────────────────────────────────
function renderNDR(grafikData) {
  currentNdrDatasets = grafikData.datasets;
  if (ndrInstance) ndrInstance.destroy();
  const ctx = document.getElementById('grafikNDR').getContext('2d');
  ndrInstance = new Chart(ctx, {
    type: 'line',
    data: { labels: grafikData.labels, datasets: grafikData.datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode:'index', intersect:false },
      plugins: {
        legend: { display: false },
        tooltip: { backgroundColor:'#0a1628', titleColor:'#e2e8f0', bodyColor:'#94a3b8', borderColor:'rgba(248,113,113,.25)', borderWidth:1, padding:10,
          callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.raw ?? '–'}‰` }
        },
      },
      scales: {
        x: { grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11}} },
        y: { min:0, grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11},callback:v=>v.toFixed(1)+'‰'} },
      },
    },
  });

  // Insight: hitung bulan di atas target
  const totalData = grafikData.datasets[0]?.data ?? [];
  const above = totalData.filter(v => v !== null && v > 1.5).length;
  document.getElementById('insight-ndr-text').innerHTML = above > 0
    ? `NDR total RS masih di atas target (&lt;1.5‰) pada <span style="color:#f87171;font-weight:700;">${above} bulan</span>.`
    : `NDR total RS sudah di bawah target selama semua bulan. <span style="color:#34d399;font-weight:700;">Pertahankan!</span>`;
}

// ─── Toggle ruangan NDR ───────────────────────────────────────────────────
function buildRuanganToggles(ruanganList, datasets) {
  const container = document.getElementById('ndr-ruangan-toggles');
  container.innerHTML = '';

  // Tombol Total RS
  const btnTotal = document.createElement('button');
  btnTotal.className  = 'ndr-toggle-btn active';
  btnTotal.dataset.idx = 0;
  btnTotal.textContent = 'Total RS';
  btnTotal.onclick = () => toggleNdrLine(0, btnTotal);
  container.appendChild(btnTotal);

  // Tombol per ruangan (mulai index 2 — skip Total RS dan Target)
  ruanganList.forEach((nama, i) => {
    const dsIdx = i + 2; // dataset index: 0=Total, 1=Target, 2+= ruangan
    const btn = document.createElement('button');
    btn.className   = 'ndr-toggle-btn';
    btn.dataset.idx = dsIdx;
    btn.textContent = nama;
    btn.onclick = () => toggleNdrLine(dsIdx, btn);
    container.appendChild(btn);
  });
}

function toggleNdrLine(dsIdx, btn) {
  if (!ndrInstance) return;
  const ds = ndrInstance.data.datasets[dsIdx];
  if (!ds) return;
  ds.hidden = !ds.hidden;
  btn.classList.toggle('active', !ds.hidden);
  ndrInstance.update();
}

// ─── Render Tabel ──────────────────────────────────────────────────────────
function renderTabel(tabel, filters) {
  if (!tabel.length) {
    document.getElementById('tbody-indikator').innerHTML =
      `<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada data untuk filter yang dipilih.</td></tr>`;
    return;
  }

  // Header — ambil nama bulan dari baris pertama
  const b = tabel[0].bulan_data ?? [];
  document.getElementById('thead-row').innerHTML = `
    <th style="min-width:280px;text-align:left;">Indikator</th>
    <th>Target</th>
    ${b.map(bln => `<th>${bln.nama_bulan}</th>`).join('')}
    <th>Triwulan</th>
    <th>Status</th>`;

  let html = '';
  tabel.forEach(ind => {
    const statusHtml = ind.status
      ? `<span class="badge-${ind.status}">${ind.status === 'tercapai' ? '✓ Tercapai' : '✗ Belum'}</span>`
      : '<span style="color:#94a3b8;font-size:.75rem;">–</span>';

    // Sel per bulan
    const bulanCells = (ind.bulan_data ?? []).map(b => {
      if (b.capaian === null) return `<td><span class="capaian-null">–</span></td>`;
      const ok    = ind.is_lower ? b.capaian <= ind.target_num : b.capaian >= ind.target_num;
      const cls   = ok ? 'capaian-ok' : 'capaian-fail';
      const pct   = Math.min(b.capaian / (ind.target_num > 0 ? ind.target_num : 100) * 100, 100);
      const color = ok ? '#34d399' : '#f87171';
      return `<td>
        <div class="${cls}">${b.capaian}%</div>
        <div class="pb-wrap"><div class="pb-fill" style="width:${pct}%;background:${color}"></div></div>
      </td>`;
    }).join('');

    // Capaian triwulan
    const twVal = ind.triwulan !== null ? ind.triwulan : null;
    const twOk  = twVal !== null && ind.target_num !== null
      ? (ind.is_lower ? twVal <= ind.target_num : twVal >= ind.target_num) : null;
    const twHtml = twVal !== null
      ? `<div class="${twOk === true ? 'capaian-ok' : twOk === false ? 'capaian-fail' : ''}">${twVal}%</div>`
      : '<span class="capaian-null">–</span>';

    html += `<tr>
      <td>
        <div class="indikator-nama">${ind.nama_html || ind.nama}</div>
        <div style="margin-top:.3rem;"><span class="badge-${ind.jenis_mutu}">${ind.label_jenis}</span></div>
      </td>
      <td class="target-cell">${ind.target_raw}</td>
      ${bulanCells}
      <td>${twHtml}</td>
      <td>${statusHtml}</td>
    </tr>`;
  });

  document.getElementById('tbody-indikator').innerHTML = html;
}

// ─── Tab switch ───────────────────────────────────────────────────────────
function switchTwGrafik(tw) {
  document.querySelectorAll('#tw-tabs-grafik .tw-tab').forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === tw));
  document.getElementById('filter-triwulan').value = tw;
  loadData();
}
function switchTwNdr(tw) {
  document.querySelectorAll('#tw-tabs-ndr .tw-tab').forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === tw));
  document.getElementById('filter-triwulan').value = tw;
  loadNDR();
}
function syncTwTabs(tw) {
  const n = parseInt(tw);
  document.querySelectorAll('#tw-tabs-grafik .tw-tab, #tw-tabs-ndr .tw-tab').forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === n));
}

// ─── Helpers ─────────────────────────────────────────────────────────────
function toRoman(n) { return ['I','II','III','IV'][n-1] ?? n; }
function setLoading(show) { document.getElementById('loading-tabel').classList.toggle('active', show); }
function resetFilter() {
  document.getElementById('filter-jenis').value    = '';
  document.getElementById('filter-triwulan').value = '1';
  document.getElementById('filter-tahun').value    = '{{ $filters['tahun'] }}';
  syncTwTabs(1);
  loadAll();
}

// ─── Init ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => loadAll());
</script>
@endpush
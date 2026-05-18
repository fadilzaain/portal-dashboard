@extends('layouts.app')
@section('title', 'Dashboard Indikator Mutu')

@push('styles')
<style>
:root {
    --n950:#050d1a; --n900:#0a1628; --n800:#0f2040; --n700:#162b55; --n600:#1e3a6e; --n500:#264d91;
    --ab:#38bdf8; --ag:#34d399; --aa:#f59e0b; --ar:#f87171; --ap:#a78bfa;
    --tp:#e2e8f0; --tm:#94a3b8; --bd:rgba(56,189,248,.15);
}
body { background:var(--n950); color:var(--tp); }

/* Filter Bar */
.filter-bar { background:var(--n900); border:1px solid var(--bd); border-radius:12px; padding:1.25rem 1.5rem; }
.filter-label { font-size:.68rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--tm); margin-bottom:.3rem; display:block; }
.filter-select {
    background:var(--n800); border:1px solid var(--bd); color:var(--tp);
    border-radius:8px; padding:.45rem .75rem; font-size:.85rem; width:100%;
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right .6rem center; padding-right:2rem;
    cursor:pointer; transition:border-color .2s;
}
.filter-select:focus { outline:none; border-color:var(--ab); }
.filter-select option { background:var(--n800); }

.btn-filter { background:linear-gradient(135deg,var(--n600),var(--n500)); border:1px solid var(--ab); color:var(--ab); border-radius:8px; padding:.48rem 1.25rem; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:.4rem; white-space:nowrap; }
.btn-filter:hover { background:var(--ab); color:var(--n900); }
.btn-reset { background:transparent; border:1px solid var(--bd); color:var(--tm); border-radius:8px; padding:.48rem .85rem; font-size:.85rem; cursor:pointer; transition:all .2s; }
.btn-reset:hover { border-color:var(--ar); color:var(--ar); }

/* Badges */
.badge-nasional, .badge-prioritas, .badge-tercapai, .badge-belum {
    border-radius:20px; padding:.15rem .6rem; font-size:.67rem; font-weight:700; letter-spacing:.04em; white-space:nowrap;
}
.badge-nasional  { background:rgba(56,189,248,.15);  color:var(--ab); border:1px solid rgba(56,189,248,.3); }
.badge-prioritas { background:rgba(245,158,11,.15);  color:var(--aa); border:1px solid rgba(245,158,11,.3); }
.badge-tercapai  { background:rgba(52,211,153,.15);  color:var(--ag); border:1px solid rgba(52,211,153,.3);  border-radius:6px; padding:.15rem .5rem; font-size:.72rem; }
.badge-belum     { background:rgba(248,113,113,.15); color:var(--ar); border:1px solid rgba(248,113,113,.3); border-radius:6px; padding:.15rem .5rem; font-size:.72rem; }

/* Table */
.im-table-wrapper { background:var(--n900); border:1px solid var(--bd); border-radius:12px; overflow:hidden; }
.im-table-header  { padding:1rem 1.5rem; border-bottom:1px solid var(--bd); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
.im-table-header h2 { font-size:.95rem; font-weight:700; display:flex; align-items:center; gap:.5rem; margin:0; }
.im-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.im-table thead th { background:var(--n800); color:var(--tm); font-size:.67rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:.65rem .85rem; white-space:nowrap; text-align:center; border-bottom:1px solid var(--bd); }
.im-table thead th:first-child { text-align:left; }
.im-table tbody tr { border-bottom:1px solid rgba(56,189,248,.06); transition:background .15s; }
.im-table tbody tr:hover { background:rgba(56,189,248,.04); }
.im-table tbody tr:last-child { border-bottom:none; }
.im-table td { padding:.75rem .85rem; text-align:center; vertical-align:middle; }
.im-table td:first-child { text-align:left; }

.indikator-nama { font-weight:600; line-height:1.4; }
.target-cell  { font-weight:700; color:var(--aa); }
.capaian-ok   { color:var(--ag); font-weight:700; }
.capaian-fail { color:var(--ar); font-weight:700; }
.capaian-null { color:var(--tm); font-style:italic; font-size:.75rem; }
.pb-wrap { background:rgba(255,255,255,.07); border-radius:4px; height:4px; margin-top:3px; overflow:hidden; }
.pb-fill { height:4px; border-radius:4px; transition:width .4s ease; }

/* Charts */
.chart-wrap { background:var(--n900); border:1px solid var(--bd); border-radius:12px; overflow:hidden; }
.chart-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid rgba(56,189,248,.1); }
.chart-title { display:flex; align-items:center; gap:.5rem; font-size:.9rem; font-weight:700; }
.chart-legend { display:flex; justify-content:center; gap:1.5rem; margin-top:.75rem; }
.chart-legend-item { display:flex; align-items:center; gap:.4rem; font-size:.73rem; color:var(--tm); }
.legend-line  { width:22px; height:2px; display:inline-block; border-radius:2px; }
.legend-dashed { width:22px; display:inline-block; }

/* NDR Toggles */
.ndr-toggle-btn { background:var(--n800); border:1px solid var(--bd); color:var(--tm); border-radius:20px; padding:.2rem .65rem; font-size:.72rem; font-weight:600; cursor:pointer; transition:all .2s; user-select:none; }
.ndr-toggle-btn.active { border-color:var(--ar); color:var(--ar); background:rgba(248,113,113,.1); }

/* Insight Bar */
.insight-bar { margin:0 1.25rem 1rem; padding:.65rem 1rem; border-radius:8px; font-size:.78rem; color:var(--tm); display:flex; align-items:center; gap:.5rem; }
.insight-capaian { background:rgba(56,189,248,.06); border:1px solid rgba(56,189,248,.15); }
.insight-ndr     { background:rgba(248,113,113,.06); border:1px solid rgba(248,113,113,.15); }

/* Loading */
.loading-overlay { display:none; position:absolute; inset:0; background:rgba(5,13,26,.75); border-radius:12px; z-index:10; align-items:center; justify-content:center; flex-direction:column; gap:.75rem; }
.loading-overlay.active { display:flex; }
.spinner { width:36px; height:36px; border:3px solid rgba(56,189,248,.2); border-top-color:var(--ab); border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* Misc */
::-webkit-scrollbar { width:6px; height:6px; }
::-webkit-scrollbar-track { background:var(--n950); }
::-webkit-scrollbar-thumb { background:var(--n600); border-radius:3px; }
::-webkit-scrollbar-thumb:hover { background:var(--n500); }
.table-scroll { overflow-x:auto; }

/* TW Tabs */
.tw-tabs { display:flex; gap:.4rem; }
.tw-tab { background:var(--n800); border:1px solid var(--bd); color:var(--tm); border-radius:8px; padding:.3rem .75rem; font-size:.78rem; font-weight:600; cursor:pointer; transition:all .2s; }
.tw-tab.active { background:rgba(56,189,248,.1); border-color:var(--ab); color:var(--ab); }
.tw-tab:hover:not(.active) { border-color:rgba(56,189,248,.35); color:var(--tp); }

/* Summary Card */
.summary-card { background:var(--n900); border:1px solid var(--bd); border-radius:14px; padding:1.25rem 1.5rem; }
.summary-label { font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.5rem; }
.summary-value { font-size:2.2rem; font-weight:800; line-height:1; }

@media (max-width:768px) { .filter-grid { flex-direction:column; } }
</style>
@endpush

@section('content')
<div style="min-height:100vh;background:var(--n950);padding:1.5rem;font-family:'DM Sans',sans-serif;">

  {{-- Page Header --}}
  <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
      <div style="background:rgba(56,189,248,.15);border-radius:10px;padding:.6rem;display:flex;">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--ab)" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--tp);letter-spacing:-.3px;">Dashboard Indikator Mutu</div>
        <div style="font-size:.82rem;color:var(--tm);margin-top:.1rem;">Monitoring capaian PMKP nasional dan prioritas rumah sakit</div>
      </div>
    </div>
    <a href="{{ url('dashboard') }}" style="background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);color:var(--ab);border-radius:8px;padding:.4rem .9rem;font-size:.8rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:.4rem;">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Home
    </a>
  </div>

  {{-- Summary Cards --}}
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach([['meta-total','var(--ab)','TOTAL INDIKATOR'],['meta-tercapai','var(--ag)','TERCAPAI'],['meta-belum','var(--ar)','BELUM TERCAPAI']] as [$id,$color,$label])
    <div class="summary-card">
      <div class="summary-label" style="color:{{ $color }};">{{ $label }}</div>
      <div id="{{ $id }}" class="summary-value" style="color:{{ $color }};">–</div>
    </div>
    @endforeach
    <div class="summary-card">
      <div class="summary-label" style="color:#818cf8;">PERIODE</div>
      <div id="meta-periode" style="font-size:.9rem;font-weight:700;color:var(--tp);margin-top:.25rem;">–</div>
    </div>
  </div>

  {{-- Charts --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Tren Capaian --}}
    <div class="chart-wrap">
      <div class="chart-header">
        <div class="chart-title">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--ab)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          Tren Capaian Rata-rata
        </div>
        <div class="tw-tabs" id="tw-tabs-grafik">
          @foreach([1,2,3,4] as $tw)
          <button class="tw-tab{{ $tw==1?' active':'' }}" data-tw="{{ $tw }}" onclick="switchTw('grafik',{{ $tw }})">TW {{ ['I','II','III','IV'][$tw-1] }}</button>
          @endforeach
        </div>
      </div>
      <div style="padding:1rem 1.25rem;">
        <div style="position:relative;height:300px;"><canvas id="grafikIndikator"></canvas></div>
        <div class="chart-legend">
          <div class="chart-legend-item"><span class="legend-line" style="background:var(--ab);"></span>Capaian (%)</div>
          <div class="chart-legend-item"><span class="legend-dashed" style="border-top:2px dashed var(--ag);"></span>Target rata-rata</div>
        </div>
      </div>
      <div class="insight-bar insight-capaian">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="var(--ab)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <span id="insight-capaian-text">Memuat insight…</span>
      </div>
    </div>

    {{-- NDR --}}
    <div class="chart-wrap">
      <div class="chart-header">
        <div class="chart-title">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--ar)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          Net Death Rate (NDR)
        </div>
        <div class="tw-tabs" id="tw-tabs-ndr">
          @foreach([1,2,3,4] as $tw)
          <button class="tw-tab{{ $tw==1?' active':'' }}" data-tw="{{ $tw }}" onclick="switchTw('ndr',{{ $tw }})">TW {{ ['I','II','III','IV'][$tw-1] }}</button>
          @endforeach
        </div>
      </div>
      <div style="padding:1rem 1.25rem;">
        <div style="position:relative;height:300px;"><canvas id="grafikNDR"></canvas></div>
        <div class="chart-legend">
          <div class="chart-legend-item"><span class="legend-line" style="background:var(--ar);"></span>Total RS</div>
          <div class="chart-legend-item"><span class="legend-dashed" style="border-top:2px dashed var(--aa);"></span>Target (&lt;1.5‰)</div>
        </div>
      </div>
      <div style="padding:.5rem 1.25rem;border-top:1px solid rgba(56,189,248,.08);">
        <div style="font-size:.67rem;text-transform:uppercase;letter-spacing:.07em;color:var(--tm);margin-bottom:.4rem;">Tampilkan ruangan:</div>
        <div id="ndr-ruangan-toggles" style="display:flex;flex-wrap:wrap;gap:.4rem;"></div>
      </div>
      <div class="insight-bar insight-ndr">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="var(--ar)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <span id="insight-ndr-text">Memuat insight…</span>
      </div>
    </div>

  </div>

  {{-- Filter Bar --}}
  <div class="filter-bar" style="margin-bottom:1.5rem;">
    <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--ab);margin-bottom:.85rem;display:flex;align-items:center;gap:.4rem;">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
      Filter Data Tabel
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;" class="filter-grid">
      <div style="min-width:140px;flex:1;">
        <label class="filter-label">Jenis Mutu</label>
        <select id="filter-jenis" class="filter-select">
          <option value="">Semua</option>
          <option value="nasional">Nasional</option>
          <option value="prioritas">Prioritas</option>
        </select>
      </div>
      <div style="min-width:140px;flex:1;">
        <label class="filter-label">Triwulan</label>
        <select id="filter-triwulan" class="filter-select" onchange="syncTwTabs(this.value)">
          @foreach([1=>'Triwulan I (Jan–Mar)',2=>'Triwulan II (Apr–Jun)',3=>'Triwulan III (Jul–Sep)',4=>'Triwulan IV (Okt–Des)'] as $v=>$lbl)
          <option value="{{ $v }}">{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div style="min-width:120px;flex:1;">
        <label class="filter-label">Tahun</label>
        <select id="filter-tahun" class="filter-select">
          @foreach($tahunList as $t)
          <option value="{{ $t }}" {{ $t==$filters['tahun']?'selected':'' }}>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:.5rem;">
        <button class="btn-filter" onclick="loadAll()">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Tampilkan
        </button>
        <button class="btn-reset" onclick="resetFilter()">Reset</button>
      </div>
    </div>
  </div>

  {{-- Tabel --}}
  <div style="position:relative;margin-bottom:1.5rem;">
    <div class="loading-overlay" id="loading-tabel">
      <div class="spinner"></div>
      <span style="font-size:.8rem;color:var(--tm);">Memuat data…</span>
    </div>
    <div class="im-table-wrapper">
      <div class="im-table-header">
        <h2>
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ab)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Data Capaian Indikator Mutu
        </h2>
        <span id="periode-label" style="background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.25);color:var(--ab);border-radius:6px;padding:.2rem .65rem;font-size:.73rem;font-weight:600;"></span>
      </div>
      <div class="table-scroll">
        <table class="im-table" id="tabel-indikator">
          <thead>
            <tr id="thead-row">
              <th style="min-width:280px;text-align:left;">Indikator</th>
              <th>Target</th><th>Bulan 1</th><th>Bulan 2</th><th>Bulan 3</th><th>Triwulan</th><th>Status</th>
            </tr>
          </thead>
          <tbody id="tbody-indikator">
            <tr><td colspan="7" style="text-align:center;color:var(--tm);padding:2.5rem;">Klik <strong style="color:var(--ab)">Tampilkan</strong> untuk memuat data indikator mutu</td></tr>
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
let grafikInstance = null, ndrInstance = null;

const $ = id => document.getElementById(id);
const getTw     = () => parseInt($('filter-triwulan').value) || 1;
const getTahun  = () => $('filter-tahun').value;
const getJenis  = () => $('filter-jenis').value;
const toRoman   = n => ['I','II','III','IV'][n-1] ?? n;
const setLoading = show => $('loading-tabel').classList.toggle('active', show);

async function loadAll() { await Promise.all([loadData(), loadNDR()]); }

async function fetchJson(url) {
    const res = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    return json;
}

async function loadData() {
    const params = new URLSearchParams({ jenis_mutu:getJenis(), triwulan:getTw(), tahun:getTahun() });
    setLoading(true);
    try {
        const json = await fetchJson(`{{ route('portal.indikatormutu.data') }}?${params}`);
        renderMeta(json.meta, json.filters);
        renderTabel(json.tabel, json.filters);
        renderGrafikCapaian(json.grafik);
    } catch(e) {
        $('tbody-indikator').innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--ar);padding:2rem;">Gagal memuat data: ${e.message}</td></tr>`;
    } finally { setLoading(false); }
}

async function loadNDR() {
    const params = new URLSearchParams({ triwulan:getTw(), tahun:getTahun() });
    try {
        const json = await fetchJson(`{{ route('portal.indikatormutu.ndr') }}?${params}`);
        renderNDR(json.grafik);
        buildRuanganToggles(json.grafik.ruangan_list, json.grafik.datasets);
    } catch(e) { $('insight-ndr-text').textContent = 'Gagal memuat NDR: ' + e.message; }
}

function renderMeta(meta, filters) {
    $('meta-total').textContent    = meta.total_indikator ?? 0;
    $('meta-tercapai').textContent = meta.tercapai ?? 0;
    $('meta-belum').textContent    = meta.belum_tercapai ?? 0;
    const label = `TW ${toRoman(filters.triwulan)} / ${filters.tahun}`;
    $('meta-periode').textContent = $('periode-label').textContent = label;
}

function makeChartOptions(color, unitSuffix) {
    return {
        responsive:true, maintainAspectRatio:false,
        interaction:{ mode:'index', intersect:false },
        plugins:{
            legend:{ display:false },
            tooltip:{ backgroundColor:'#0a1628', titleColor:'#e2e8f0', bodyColor:'#94a3b8', borderColor:`${color}40`, borderWidth:1, padding:10,
                callbacks:{ label: ctx => `${ctx.dataset.label}: ${ctx.raw ?? '–'}${unitSuffix}` }
            },
        },
        scales:{
            x:{ grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11}} },
            y:{ min:0, grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#94a3b8',font:{size:11}} },
        },
    };
}

function renderGrafikCapaian(g) {
    if (grafikInstance) grafikInstance.destroy();
    const opts = makeChartOptions('#38bdf8','%');
    opts.scales.y.max = 105;
    opts.scales.y.ticks.callback = v => v + '%';
    grafikInstance = new Chart($('grafikIndikator').getContext('2d'), { type:'line', data:{ labels:g.labels, datasets:g.datasets }, options:opts });

    const vals = (g.datasets[0]?.data ?? []).filter(v => v !== null);
    if (vals.length >= 2) {
        const diff = vals.at(-1) - vals[0], sign = diff >= 0 ? '+' : '', color = diff >= 0 ? '#34d399' : '#f87171';
        $('insight-capaian-text').innerHTML = `Rata-rata capaian ${diff >= 0 ? 'naik' : 'turun'} <span style="color:${color};font-weight:700;">${sign}${diff.toFixed(1)}%</span> dari ${g.labels[0]} ke ${g.labels[vals.length-1]}.`;
    } else { $('insight-capaian-text').textContent = 'Belum cukup data untuk analisis tren.'; }
}

function renderNDR(g) {
    if (ndrInstance) ndrInstance.destroy();
    const opts = makeChartOptions('#f87171','‰');
    opts.scales.y.ticks.callback = v => v.toFixed(1) + '‰';
    ndrInstance = new Chart($('grafikNDR').getContext('2d'), { type:'line', data:{ labels:g.labels, datasets:g.datasets }, options:opts });

    const above = (g.datasets[0]?.data ?? []).filter(v => v !== null && v > 1.5).length;
    $('insight-ndr-text').innerHTML = above > 0
        ? `NDR total RS masih di atas target (&lt;1.5‰) pada <span style="color:#f87171;font-weight:700;">${above} bulan</span>.`
        : `NDR total RS sudah di bawah target selama semua bulan. <span style="color:#34d399;font-weight:700;">Pertahankan!</span>`;
}

function buildRuanganToggles(ruanganList, datasets) {
    const container = $('ndr-ruangan-toggles');
    container.innerHTML = '';
    const makeBtn = (idx, label, active) => {
        const btn = document.createElement('button');
        btn.className = 'ndr-toggle-btn' + (active ? ' active' : '');
        btn.dataset.idx = idx;
        btn.textContent = label;
        btn.onclick = () => { if (!ndrInstance) return; const ds = ndrInstance.data.datasets[idx]; if (!ds) return; ds.hidden = !ds.hidden; btn.classList.toggle('active', !ds.hidden); ndrInstance.update(); };
        container.appendChild(btn);
    };
    makeBtn(0, 'Total RS', true);
    ruanganList.forEach((nama, i) => makeBtn(i + 2, nama, false));
}

function renderTabel(tabel, filters) {
    if (!tabel.length) {
        $('tbody-indikator').innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--tm);padding:2rem;">Tidak ada data untuk filter yang dipilih.</td></tr>`;
        return;
    }
    const b = tabel[0].bulan_data ?? [];
    $('thead-row').innerHTML = `<th style="min-width:280px;text-align:left;">Indikator</th><th>Target</th>${b.map(bln => `<th>${bln.nama_bulan}</th>`).join('')}<th>Triwulan</th><th>Status</th>`;

    $('tbody-indikator').innerHTML = tabel.map(ind => {
        const bulanCells = (ind.bulan_data ?? []).map(b => {
            if (b.capaian === null) return `<td><span class="capaian-null">–</span></td>`;
            const ok  = ind.is_lower ? b.capaian <= ind.target_num : b.capaian >= ind.target_num;
            const pct = Math.min(b.capaian / (ind.target_num > 0 ? ind.target_num : 100) * 100, 100);
            return `<td><div class="${ok?'capaian-ok':'capaian-fail'}">${b.capaian}%</div><div class="pb-wrap"><div class="pb-fill" style="width:${pct}%;background:${ok?'#34d399':'#f87171'}"></div></div></td>`;
        }).join('');

        const twOk = ind.triwulan !== null && ind.target_num !== null ? (ind.is_lower ? ind.triwulan <= ind.target_num : ind.triwulan >= ind.target_num) : null;
        const twHtml = ind.triwulan !== null ? `<div class="${twOk===true?'capaian-ok':twOk===false?'capaian-fail':''}">${ind.triwulan}%</div>` : '<span class="capaian-null">–</span>';
        const statusHtml = ind.status ? `<span class="badge-${ind.status}">${ind.status==='tercapai'?'✓ Tercapai':'✗ Belum'}</span>` : '<span style="color:var(--tm);font-size:.75rem;">–</span>';

        return `<tr><td><div class="indikator-nama">${ind.nama_html||ind.nama}</div><div style="margin-top:.3rem;"><span class="badge-${ind.jenis_mutu}">${ind.label_jenis}</span></div></td><td class="target-cell">${ind.target_raw}</td>${bulanCells}<td>${twHtml}</td><td>${statusHtml}</td></tr>`;
    }).join('');
}

function switchTw(type, tw) {
    document.querySelectorAll(`#tw-tabs-${type} .tw-tab`).forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === tw));
    $('filter-triwulan').value = tw;
    type === 'grafik' ? loadData() : loadNDR();
}

function syncTwTabs(tw) {
    const n = parseInt(tw);
    document.querySelectorAll('#tw-tabs-grafik .tw-tab, #tw-tabs-ndr .tw-tab').forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === n));
}

function resetFilter() {
    $('filter-jenis').value    = '';
    $('filter-triwulan').value = '1';
    $('filter-tahun').value    = '{{ $filters['tahun'] }}';
    syncTwTabs(1);
    loadAll();
}

document.addEventListener('DOMContentLoaded', loadAll);
</script>
@endpush
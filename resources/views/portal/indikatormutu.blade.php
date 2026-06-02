{{-- ============================================================
     resources/views/portal/indikatormutu.blade.php
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Dashboard Indikator Mutu')

@push('styles')
    @vite('resources/css/portal/indikator-mutu.css')
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
    {{-- Inject config PHP → JS, HARUS sebelum vite script --}}
    <script>
        window.IM_CONFIG = {
            routes: {
                data: "{{ route('portal.indikatormutu.data') }}",
                ndr:  "{{ route('portal.indikatormutu.ndr') }}"
            },
            filters: @json($filters)
        };
    </script>
    @vite('resources/js/portal/indikator-mutu.js')
@endpush
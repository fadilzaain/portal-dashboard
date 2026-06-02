@extends('layouts.app')

@section('title', 'Klaim BPJS')
@section('page_title', 'Klaim BPJS')
@section('page_subtitle', 'Data Pengajuan & Pembayaran')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
@vite('resources/css/portal/klaimbpjs.css')
@endpush

@section('content')

{{-- ══════════════════════════════════════════
     FILTER BAR
══════════════════════════════════════════ --}}
<div class="filter-wrap fade-up">
    <a href="{{ url('dashboard') }}" class="btn-home" style="margin-bottom: 10px;">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Home
    </a>
    <div class="filter-heading">
        <h1>Dashboard Klaim BPJS</h1>
        <p>Rawat Inap & Rawat Jalan — data terbayar per periode</p>
    </div>
    <div class="period-bar">
        <button class="period-btn active" data-label="per Minggu"  onclick="setPeriod(this,'weekly')">Mingguan</button>
        <button class="period-btn"        data-label="per Bulan"   onclick="setPeriod(this,'monthly')">Bulanan</button>
        <button class="period-btn"        data-label="per Tahun"   onclick="setPeriod(this,'yearly')">Tahunan</button>
        <div class="period-divider"></div>
        <input type="date" id="date-from" class="date-input">
        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
        <input type="date" id="date-to"   class="date-input">
        <button class="filter-apply-btn" onclick="applyCustomRange()">Filter</button>
    </div>
</div>

{{-- ══════════════════════════════════════════
     GRAFIK 1 — RAWAT INAP
══════════════════════════════════════════ --}}
<div class="chart-card fade-up fade-up-1" id="card-rinap">
    <div class="chart-loading" id="loading-rinap"><div class="spinner"></div></div>
    <div class="chart-error"   id="error-rinap">
        <div class="chart-error-icon">⚠</div>
        <div class="chart-error-text">Gagal memuat data Rawat Inap</div>
    </div>
    <div class="chart-card-header">
        <div>
            <div class="chart-card-title">
                <span class="chart-title-dot" style="background:var(--teal);box-shadow:0 0 8px var(--accent-glow)"></span>
                Rawat Inap (RINAP)
                <span class="period-badge" id="label-rinap">per Minggu</span>
            </div>
            <div class="chart-card-sub">Jumlah kasus &amp; nominal terbayar per periode</div>
        </div>
        <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot" style="background:rgba(20,184,166,.8)"></span>Pengajuan</div>
            <div class="legend-item"><span class="legend-dot" style="background:rgba(34,197,94,.8)"></span>Terbayar</div>
            <div class="legend-item"><span class="legend-line" style="background:#f59e0b"></span>Nominal (Rp)</div>
        </div>
    </div>
    <div class="chart-canvas-wrap">
        <canvas id="chart-rinap"></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════
     GRAFIK 2 — RAWAT JALAN
══════════════════════════════════════════ --}}
<div class="chart-card fade-up fade-up-2" id="card-rjalan">
    <div class="chart-loading" id="loading-rjalan"><div class="spinner"></div></div>
    <div class="chart-error"   id="error-rjalan">
        <div class="chart-error-icon">⚠</div>
        <div class="chart-error-text">Gagal memuat data Rawat Jalan</div>
    </div>
    <div class="chart-card-header">
        <div>
            <div class="chart-card-title">
                <span class="chart-title-dot" style="background:var(--indigo);box-shadow:0 0 8px rgba(99,102,241,.3)"></span>
                Rawat Jalan (RJALAN)
                <span class="period-badge" id="label-rjalan">per Minggu</span>
            </div>
            <div class="chart-card-sub">Jumlah kasus &amp; nominal terbayar per periode</div>
        </div>
        <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot" style="background:rgba(99,102,241,.8)"></span>Pengajuan</div>
            <div class="legend-item"><span class="legend-dot" style="background:rgba(59,130,246,.8)"></span>Terbayar</div>
            <div class="legend-item"><span class="legend-line" style="background:#f59e0b"></span>Nominal (Rp)</div>
        </div>
    </div>
    <div class="chart-canvas-wrap">
        <canvas id="chart-rjalan"></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════
     BARIS BAWAH: Komposisi + Summary
══════════════════════════════════════════ --}}
<div class="bottom-row fade-up fade-up-3">

{{-- Summary Nominal --}}
    <div class="summary-card fade-up fade-up-4">
        <div class="summary-title">Ringkasan Nominal</div>
        <div class="summary-rows">

            <!-- {{-- Total Pengajuan --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(20,184,166,.12);color:var(--teal)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Total Pengajuan</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-pengajuan-rp">Rp –</div>
                    <div class="summary-row-sub" id="sum-pengajuan-kasus">– kasus</div>
                    <div id="sum-pengajuan-delta"></div>
                </div>
            </div> -->

            {{-- Rawat Inap --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(20,184,166,.12);color:var(--teal)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Rawat Inap</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-rinap-rp">Rp –</div>
                    <div class="summary-row-sub" id="sum-rinap-kasus">– kasus</div>
                    <div id="sum-rinap-delta"></div>
                </div>
            </div>

            {{-- Rawat Jalan --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(99,102,241,.12);color:var(--indigo)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Rawat Jalan</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-rjalan-rp">Rp –</div>
                    <div class="summary-row-sub" id="sum-rjalan-kasus">– kasus</div>
                    <div id="sum-rjalan-delta"></div>
                </div>
            </div>

            {{-- Terbayar --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(34,197,94,.12);color:var(--green)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Terbayar</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-terbayar-rp"   style="color:var(--green)">Rp –</div>
                    <div class="summary-row-sub" id="sum-terbayar-kasus">– kasus</div>
                    <div id="sum-terbayar-delta"></div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(245,158,11,.12);color:var(--amber)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Pending</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-pending-rp"    style="color:var(--amber)">Rp –</div>
                    <div class="summary-row-sub" id="sum-pending-kasus">– kasus</div>
                    <div id="sum-pending-delta"></div>
                </div>
            </div>

            {{-- Tidak Layak --}}
            <div class="summary-row">
                <div class="summary-row-left">
                    <div class="summary-row-icon" style="background:rgba(239,68,68,.12);color:var(--red)">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="summary-row-label">Tidak Layak</div>
                </div>
                <div>
                    <div class="summary-row-val" id="sum-tidaklayak-rp" style="color:var(--red)">Rp –</div>
                    <div class="summary-row-sub" id="sum-tidaklayak-kasus">– kasus</div>
                    <div id="sum-tidaklayak-delta"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Komposisi Status --}}
    <div class="komposisi-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">
                    <span class="chart-title-dot" style="background:var(--indigo);box-shadow:0 0 8px rgba(99,102,241,.3)"></span>
                    Komposisi Status Klaim
                    <span class="period-badge" id="label-komposisi">per Minggu</span>
                </div>
                <div class="chart-card-sub">Proporsi status seluruh klaim periode ini</div>
            </div>
        </div>
        <div class="donut-wrap">
            <div class="donut-canvas-wrap">
                <canvas id="donutChart" width="180" height="180"></canvas>
                <div class="donut-center">
                    <div class="donut-center-num"   id="donut-total">–</div>
                    <div class="donut-center-label">Total</div>
                </div>
            </div>
            <div class="legend-list">
                <div class="legend-row">
                    <span class="legend-name"><span class="legend-dot" style="background:#22c55e"></span>Terbayar</span>
                    <span>
                        <span class="legend-val" id="leg-terbayar">–</span>
                        <span class="legend-pct" id="pct-terbayar"></span>
                    </span>
                </div>
                <div class="legend-row">
                    <span class="legend-name"><span class="legend-dot" style="background:#f59e0b"></span>Pending</span>
                    <span>
                        <span class="legend-val" id="leg-pending">–</span>
                        <span class="legend-pct" id="pct-pending"></span>
                    </span>
                </div>
                <div class="legend-row">
                    <span class="legend-name"><span class="legend-dot" style="background:#ef4444"></span>Tidak Layak</span>
                    <span>
                        <span class="legend-val" id="leg-tidaklayak">–</span>
                        <span class="legend-pct" id="pct-tidaklayak"></span>
                    </span>
                </div>
                <div class="legend-row">
                    <span class="legend-name"><span class="legend-dot" style="background:#6366f1"></span>Diproses</span>
                    <span>
                        <span class="legend-val" id="leg-diproses">–</span>
                        <span class="legend-pct" id="pct-diproses"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@vite('resources/js/portal/klaimbpjs.js')
@endpush
@extends('layouts.app')

@section('title', 'Klaim BPJS')
@section('page_title', 'Klaim BPJS')
@section('page_subtitle', 'Data Pengajuan & Pembayaran')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root {
        --dk-bg:        #0a0f1e;
        --dk-surface:   #0f1629;
        --dk-card:      #131d35;
        --dk-card2:     #162040;
        --dk-border:    rgba(255,255,255,.07);
        --dk-border2:   rgba(255,255,255,.12);
        --accent:       #14b8a6;
        --accent-glow:  rgba(20,184,166,.2);
        --accent-dim:   rgba(20,184,166,.1);
        --teal:         #14b8a6;
        --green:        #22c55e;
        --amber:        #f59e0b;
        --red:          #ef4444;
        --indigo:       #6366f1;
        --blue:         #3b82f6;
        --text-main:    #f1f5f9;
        --text-dim:     #94a3b8;
        --text-muted:   #475569;
        --card-radius:  16px;
        --font:         'Sora', sans-serif;
        --mono:         'DM Mono', monospace;
    }

    /* ── Page override: dark bg ── */
    #page-content { background: var(--dk-bg); }
    #topbar { background: var(--dk-surface); border-bottom: 1px solid var(--dk-border); }
    .topbar-title      { color: var(--text-main)  !important; }
    .topbar-breadcrumb { color: var(--text-muted) !important; }
    .topbar-date       { color: var(--text-muted) !important; }
    .topbar-notif      { background: var(--dk-card) !important; border-color: var(--dk-border) !important; color: var(--text-dim) !important; }

    /* ── Filter Bar ── */
    .filter-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .75rem;
        margin-bottom: 1.5rem;
    }
    .filter-heading h1 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -.02em;
    }
    .filter-heading p {
        font-size: .74rem;
        color: var(--text-dim);
        margin-top: .15rem;
    }
    .period-bar {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .period-btn {
        font-family: var(--mono);
        font-size: .72rem;
        font-weight: 500;
        padding: .42rem .9rem;
        border-radius: 8px;
        border: 1px solid var(--dk-border2);
        background: var(--dk-card);
        color: var(--text-dim);
        cursor: pointer;
        transition: all .15s;
    }
    .period-btn:hover { border-color: var(--accent); color: var(--accent); }
    .period-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
        box-shadow: 0 2px 12px var(--accent-glow);
    }
    .period-divider { width: 1px; height: 22px; background: var(--dk-border2); }
    .date-input {
        font-family: var(--mono);
        font-size: .72rem;
        padding: .42rem .75rem;
        border-radius: 8px;
        border: 1px solid var(--dk-border2);
        color: var(--text-main);
        background: var(--dk-card);
        cursor: pointer;
        outline: none;
        transition: border-color .15s;
        color-scheme: dark;
    }
    .date-input:focus { border-color: var(--accent); }
    .filter-apply-btn {
        font-size: .76rem;
        font-weight: 600;
        padding: .42rem 1.1rem;
        border-radius: 8px;
        background: var(--accent);
        color: white;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
        box-shadow: 0 2px 12px var(--accent-glow);
    }
    .filter-apply-btn:hover { opacity: .85; }

    /* ── Loading overlay ── */
    .chart-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(10,15,30,.6);
        backdrop-filter: blur(2px);
        border-radius: var(--card-radius);
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s;
    }
    .chart-loading.show { opacity: 1; pointer-events: all; }
    .spinner {
        width: 28px; height: 28px;
        border: 3px solid var(--dk-border2);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Chart Cards ── */
    .chart-card {
        background: var(--dk-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--dk-border);
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .chart-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--accent), transparent);
    }
    .chart-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    .chart-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        position: relative;
        z-index: 1;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .chart-card-title {
        font-size: .9rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: .6rem;
    }
    .chart-title-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .chart-card-sub {
        font-size: .7rem;
        color: var(--text-dim);
        font-family: var(--mono);
        margin-top: .2rem;
    }
    .chart-legend {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: .7rem;
        color: var(--text-dim);
        font-family: var(--mono);
    }
    .legend-dot  { width: 8px;  height: 8px; border-radius: 50%; flex-shrink: 0; }
    .legend-line { width: 16px; height: 2px; border-radius: 2px; flex-shrink: 0; }

    .chart-canvas-wrap {
        position: relative;
        height: 280px;
        z-index: 1;
    }

    /* ── Bottom row ── */
    .bottom-row {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 1100px) { .bottom-row { grid-template-columns: 1fr; } }

    /* ── Komposisi card ── */
    .komposisi-card {
        background: var(--dk-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--dk-border);
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .komposisi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--indigo), transparent);
    }
    .komposisi-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    .donut-wrap {
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        z-index: 1;
        flex-wrap: wrap;
    }
    .donut-canvas-wrap {
        position: relative;
        width: 180px; height: 180px;
        flex-shrink: 0;
    }
    .donut-center {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        pointer-events: none;
    }
    .donut-center-num {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: var(--font);
        line-height: 1;
    }
    .donut-center-label {
        font-size: .6rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-dim);
        margin-top: 4px;
    }
    .legend-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: .85rem;
        min-width: 160px;
    }
    .legend-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .legend-name {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .76rem;
        color: var(--text-dim);
        font-weight: 500;
    }
    .legend-val { font-family: var(--mono); font-size: .72rem; color: var(--text-main); font-weight: 600; }
    .legend-pct { font-family: var(--mono); font-size: .62rem; color: var(--text-muted); margin-left: .25rem; }

    /* ── Summary card ── */
    .summary-card {
        background: var(--dk-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--dk-border);
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .summary-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--green), transparent);
    }
    .summary-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }
    .summary-title {
        font-size: .9rem;
        font-weight: 700;
        color: var(--text-main);
        position: relative;
        z-index: 1;
    }
    .summary-rows {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        position: relative;
        z-index: 1;
        flex: 1;
        justify-content: center;
    }
    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .75rem 1rem;
        border-radius: 10px;
        background: rgba(255,255,255,.03);
        border: 1px solid var(--dk-border);
        transition: background .15s;
    }
    .summary-row:hover { background: rgba(255,255,255,.05); }
    .summary-row-left { display: flex; align-items: center; gap: .6rem; }
    .summary-row-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem;
        flex-shrink: 0;
    }
    .summary-row-label { font-size: .75rem; color: var(--text-dim); font-weight: 500; }
    .summary-row-val   { font-family: var(--mono); font-size: .78rem; color: var(--text-main); font-weight: 600; text-align: right; }
    .summary-row-sub   { font-size: .64rem; color: var(--text-muted); text-align: right; margin-top: 1px; }

    /* ── Delta badge ── */
    .delta-badge {
        font-family: var(--mono);
        font-size: .6rem;
        font-weight: 600;
        padding: .1rem .35rem;
        border-radius: 4px;
        display: inline-block;
        margin-top: 2px;
    }
    .delta-up   { background: rgba(34,197,94,.12);  color: var(--green); }
    .delta-down { background: rgba(239,68,68,.12);  color: var(--red); }
    .delta-flat { background: rgba(148,163,184,.1); color: var(--text-muted); }

    /* ── Period badge ── */
    .period-badge {
        font-family: var(--mono);
        font-size: .62rem;
        font-weight: 500;
        padding: .18rem .55rem;
        border-radius: 5px;
        background: var(--accent-dim);
        color: var(--accent);
        border: 1px solid rgba(20,184,166,.2);
    }

    /* ── Error state ── */
    .chart-error {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .5rem;
        z-index: 5;
    }
    .chart-error.show { display: flex; }
    .chart-error-icon { font-size: 1.5rem; opacity: .4; }
    .chart-error-text { font-size: .72rem; color: var(--text-muted); font-family: var(--mono); }

    /* ── Animations ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up   { animation: fadeUp .4s cubic-bezier(.4,0,.2,1) both; }
    .fade-up-1 { animation-delay: .04s; }
    .fade-up-2 { animation-delay: .10s; }
    .fade-up-3 { animation-delay: .16s; }
    .fade-up-4 { animation-delay: .22s; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════
     FILTER BAR
══════════════════════════════════════════ --}}
<div class="filter-wrap fade-up">
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
<script>
/* ══════════════════════════════════════════════
   CONFIG
══════════════════════════════════════════════ */
const API_BASE      = '/bpjs';
const RELOAD_MS     = 5 * 60 * 1000; // auto-reload setiap 5 menit

let currentPeriod   = 'weekly';
let dateFrom        = null;
let dateTo          = null;
let chartRinap      = null;
let chartRjalan     = null;
let chartDonut      = null;
let reloadTimer     = null;

/* ══════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════ */
const fmtRp  = n => (n == null || isNaN(n)) ? 'Rp –' : 'Rp ' + Number(n).toLocaleString('id-ID');
const fmtNum = n => (n == null || isNaN(n)) ? '–'    : Number(n).toLocaleString('id-ID');
const $      = id => document.getElementById(id);

function setLoading(key, show) {
    $('loading-' + key)?.classList.toggle('show', show);
}
function setError(key, show) {
    $('error-' + key)?.classList.toggle('show', show);
}

async function apiFetch(endpoint, params) {
    const url = `${API_BASE}/${endpoint}${params ? '?' + params : ''}`;
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

/* ══════════════════════════════════════════════
   PERIOD / FILTER
══════════════════════════════════════════════ */
function setPeriod(btn, period) {
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPeriod = period;
    dateFrom = dateTo = null;

    const label = btn.dataset.label;
    ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    loadAll();
}

function applyCustomRange() {
    const f = $('date-from').value;
    const t = $('date-to').value;
    if (!f || !t) { alert('Pilih tanggal awal dan akhir terlebih dahulu.'); return; }
    if (f > t)    { alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir.'); return; }

    dateFrom = f;
    dateTo   = t;
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    currentPeriod = 'custom';

    const label = `${f} → ${t}`;
    ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    loadAll();
}

function buildParams() {
    const p = new URLSearchParams();
    if (currentPeriod === 'custom') {
        p.set('from', dateFrom);
        p.set('to',   dateTo);
    } else {
        p.set('period', currentPeriod);
    }
    return p.toString();
}

/* ══════════════════════════════════════════════
   MAIN LOAD
══════════════════════════════════════════════ */
async function loadAll() {
    // Reset auto-reload timer
    clearTimeout(reloadTimer);
    reloadTimer = setTimeout(loadAll, RELOAD_MS);

    const params = buildParams();
    await Promise.all([
        loadCharts(params),
        loadSummary(params),
    ]);
}

/* ══════════════════════════════════════════════
   CHART DEFAULTS (Chart.js)
══════════════════════════════════════════════ */
// const CHART_DEFAULTS = {
//     responsive: true,
//     maintainAspectRatio: false,
//     animation: { duration: 600, easing: 'easeInOutQuart' },
//     plugins: {
//         legend: { display: false },
//         tooltip: {
//             backgroundColor: '#0f1629',
//             borderColor: 'rgba(255,255,255,.1)',
//             borderWidth: 1,
//             titleFont: { family: 'Sora',    size: 12, weight: '700' },
//             bodyFont:  { family: 'DM Mono', size: 11 },
//             padding: 12,
//             cornerRadius: 10,
//             callbacks: {
//                 label: ctx => {
//                     if (ctx.dataset.type === 'line') return ` Nominal: ${fmtRp(ctx.raw)}`;
//                     return ` ${ctx.dataset.label}: ${fmtNum(ctx.raw)} kasus`;
//                 }
//             }
//         }
//     },
//     scales: {
//         x: {
//             grid: { display: false },
//             ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
//             border: { color: 'rgba(255,255,255,.06)' }
//         },
//         y: {
//             grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
//             ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
//             border: { dash: [4,4], color: 'transparent' },
//             beginAtZero: true
//         },
//         yRight: {
//             type: 'linear',
//             position: 'right',
//             grid: { display: false },
//             ticks: {
//                 font: { family: 'DM Mono', size: 10 },
//                 color: '#f59e0b',
//                 callback: v => {
//                     if (v >= 1e9) return (v / 1e9).toFixed(1) + 'M';
//                     if (v >= 1e6) return (v / 1e6).toFixed(1) + 'jt';
//                     if (v >= 1e3) return (v / 1e3).toFixed(0) + 'rb';
//                     return v;
//                 }
//             },
//             border: { color: 'transparent' }
//         }
//     }
// };

function makeComboChart(canvasId, data, colors) {
    const ctx = $(canvasId).getContext('2d');
    return new Chart(ctx, {
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    type: 'bar',
                    label: 'Pengajuan',
                    data: data.pengajuan || [],
                    backgroundColor: colors.pengajuan,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    type: 'bar',
                    label: 'Terbayar',
                    data: data.terbayar_count || [],
                    backgroundColor: colors.terbayar,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 3
                },
                {
                    type: 'line',
                    label: 'Nominal',
                    data: data.nominal || [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'yRight',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeInOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1629',
                    borderColor: 'rgba(255,255,255,.1)',
                    borderWidth: 1,
                    titleFont: { family: 'Sora',    size: 12, weight: '700' },
                    bodyFont:  { family: 'DM Mono', size: 11 },
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => {
                            if (ctx.dataset.type === 'line') return ` Nominal: ${fmtRp(ctx.raw)}`;
                            return ` ${ctx.dataset.label}: ${fmtNum(ctx.raw)} kasus`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
                    border: { color: 'rgba(255,255,255,.06)' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                    ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
                    border: { dash: [4,4], color: 'transparent' },
                    beginAtZero: true
                },
                yRight: {
                    type: 'linear',
                    position: 'right',
                    grid: { display: false },
                    ticks: {
                        font: { family: 'DM Mono', size: 10 },
                        color: '#f59e0b',
                        callback: v => {
                            if (v >= 1e9) return (v / 1e9).toFixed(1) + 'M';
                            if (v >= 1e6) return (v / 1e6).toFixed(1) + 'jt';
                            if (v >= 1e3) return (v / 1e3).toFixed(0) + 'rb';
                            return v;
                        }
                    },
                    border: { color: 'transparent' }
                }
            }
        }
    });
}

/* ══════════════════════════════════════════════
   LOAD CHARTS (/bpjs/chart-jenis)
══════════════════════════════════════════════ */
async function loadCharts(params) {
    setLoading('rinap',  true);
    setLoading('rjalan', true);
    setError('rinap',    false);
    setError('rjalan',   false);

    try {
        const d = await apiFetch('chart-jenis', params);

        // Rawat Inap
        if (chartRinap) chartRinap.destroy();
        chartRinap = makeComboChart('chart-rinap', d.rinap ?? {}, {
            pengajuan: 'rgba(20,184,166,.65)',
            terbayar:  'rgba(34,197,94,.65)'
        });

        // Rawat Jalan
        if (chartRjalan) chartRjalan.destroy();
        chartRjalan = makeComboChart('chart-rjalan', d.rjalan ?? {}, {
            pengajuan: 'rgba(99,102,241,.65)',
            terbayar:  'rgba(59,130,246,.65)'
        });

        // Donut komposisi
        renderDonut(d.summary ?? null);

    } catch (e) {
        console.error('[chartJenis] error:', e);
        setError('rinap',  true);
        setError('rjalan', true);

        // Fallback dummy — agar halaman tidak kosong
        const dummy = {
            labels:          ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
            pengajuan:       [40, 32, 55, 48, 60, 43, 38],
            terbayar_count:  [30, 25, 42, 38, 50, 35, 28],
            nominal:         [320e6, 260e6, 430e6, 380e6, 500e6, 350e6, 280e6]
        };
        if (chartRinap)  chartRinap.destroy();
        if (chartRjalan) chartRjalan.destroy();
        chartRinap  = makeComboChart('chart-rinap',  dummy, { pengajuan:'rgba(20,184,166,.65)', terbayar:'rgba(34,197,94,.65)' });
        chartRjalan = makeComboChart('chart-rjalan',
            { ...dummy, pengajuan:[25,20,35,28,40,30,22], terbayar_count:[18,14,28,22,32,24,16] },
            { pengajuan:'rgba(99,102,241,.65)', terbayar:'rgba(59,130,246,.65)' }
        );
    } finally {
        setLoading('rinap',  false);
        setLoading('rjalan', false);
    }
}

/* ══════════════════════════════════════════════
   DONUT CHART
══════════════════════════════════════════════ */
function renderDonut(s) {
    if (!s) return;

    const vals  = [s.terbayar ?? 0, s.pending ?? 0, s.tidak_layak ?? 0, s.diproses ?? 0];
    const total = vals.reduce((a, b) => a + b, 0);

    $('donut-total').textContent     = fmtNum(total);
    $('leg-terbayar').textContent    = fmtNum(s.terbayar);
    $('leg-pending').textContent     = fmtNum(s.pending);
    $('leg-tidaklayak').textContent  = fmtNum(s.tidak_layak);
    $('leg-diproses').textContent    = fmtNum(s.diproses);

    const pct = v => total > 0 ? `(${((v / total) * 100).toFixed(1)}%)` : '';
    $('pct-terbayar').textContent    = pct(s.terbayar);
    $('pct-pending').textContent     = pct(s.pending);
    $('pct-tidaklayak').textContent  = pct(s.tidak_layak);
    $('pct-diproses').textContent    = pct(s.diproses);

    if (chartDonut) chartDonut.destroy();
    chartDonut = new Chart($('donutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Terbayar', 'Pending', 'Tidak Layak', 'Diproses'],
            datasets: [{
                data: vals,
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#6366f1'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            cutout: '68%',
            responsive: false,
            animation: { duration: 600 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1629',
                    borderColor: 'rgba(255,255,255,.1)',
                    borderWidth: 1,
                    titleFont: { family: 'Sora',    size: 12, weight: '700' },
                    bodyFont:  { family: 'DM Mono', size: 11 },
                    cornerRadius: 10,
                    padding: 10
                }
            }
        }
    });
}

/* ══════════════════════════════════════════════
   SUMMARY NOMINAL (/bpjs/summary)
══════════════════════════════════════════════ */
function renderDelta(elId, delta) {
    const el = $(elId);
    if (!el) return;
    if (delta === null || delta === undefined) { el.innerHTML = ''; return; }

    let cls  = 'delta-flat';
    let icon = '→';
    if (delta > 0)  { cls = 'delta-up';   icon = '▲'; }
    if (delta < 0)  { cls = 'delta-down'; icon = '▼'; }

    el.innerHTML = `<span class="delta-badge ${cls}">${icon} ${Math.abs(delta)}%</span>`;
}

async function loadSummary(params) {
    try {
        // summary endpoint untuk terbayar/pending/tidak_layak
        const d = await apiFetch('summary', params);

        const set = (key, apiKey) => {
            const obj = d[apiKey];
            if (!obj) return;
            $('sum-' + key + '-rp').textContent    = fmtRp(obj.nominal);
            $('sum-' + key + '-kasus').textContent = fmtNum(obj.count) + ' kasus';
            renderDelta('sum-' + key + '-delta', obj.delta ?? null);
        };

        set('terbayar',   'terbayar');
        set('pending',    'pending');
        set('tidaklayak', 'tidak_layak');

        // rinap & rjalan dari chart-jenis, ambil lagi untuk summary box
        const c = await apiFetch('chart-jenis', params);
        const rinapTotal  = (c.rinap?.pengajuan  ?? []).reduce((a, b) => a + b, 0);
        const rjalanTotal = (c.rjalan?.pengajuan ?? []).reduce((a, b) => a + b, 0);
        const rinapNom    = (c.rinap?.nominal    ?? []).reduce((a, b) => a + b, 0);
        const rjalanNom   = (c.rjalan?.nominal   ?? []).reduce((a, b) => a + b, 0);

        $('sum-rinap-rp').textContent    = fmtRp(rinapNom);
        $('sum-rinap-kasus').textContent = fmtNum(rinapTotal) + ' kasus';
        $('sum-rjalan-rp').textContent   = fmtRp(rjalanNom);
        $('sum-rjalan-kasus').textContent = fmtNum(rjalanTotal) + ' kasus';

    } catch (e) {
        console.error('[summary] error:', e);
    }
}

/* ══════════════════════════════════════════════
   INIT
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const meta = await apiFetch('meta', '');
        dateFrom = meta.default_from;
        dateTo   = meta.default_to;
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;

        // ← PENTING: set custom SEBELUM loadAll()
        currentPeriod = 'custom';
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        const label = `${dateFrom} → ${dateTo}`;
        ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    } catch {
        const now  = new Date();
        const y    = now.getFullYear();
        const m    = String(now.getMonth() + 1).padStart(2, '0');
        const last = new Date(y, now.getMonth() + 1, 0).getDate();
        dateFrom   = `${y}-${m}-01`;
        dateTo     = `${y}-${m}-${last}`;
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;
        currentPeriod = 'custom'; // ← tambah ini
    }

    loadAll(); // ← sekarang currentPeriod sudah 'custom'
});
</script>
@endpush
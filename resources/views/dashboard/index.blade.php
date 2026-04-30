@extends('layouts.app')

@section('title', 'Beranda Portal')
@section('page_title', 'Beranda Portal')
@section('page_subtitle', 'Pilih Dashboard')

@push('styles')
<style>
    /* == Hero Greeting ============== */
    .greeting-card {
        background: linear-gradient(135deg, #0a0f1e 0%, #0f172a 50%, #0c1a2e 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .greeting-card::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(20,184,166,.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .greeting-card::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 30%;
        width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .greeting-time {
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #14b8a6;
        margin-bottom: .5rem;
        font-family: 'DM Mono', monospace;
    }
    .greeting-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: #f1f5f9;
        line-height: 1.3;
    }
    .greeting-sub {
        font-size: .82rem;
        color: #64748b;
        margin-top: .4rem;
    }
    .greeting-stats {
        display: flex;
        gap: 1.5rem;
        margin-top: 1.5rem;
        position: relative;
        z-index: 1;
    }
    .g-stat {
        display: flex;
        flex-direction: column;
        gap: .2rem;
    }
    .g-stat-val {
        font-family: 'DM Mono', monospace;
        font-size: .95rem;
        font-weight: 500;
        color: #e2e8f0;
    }
    .g-stat-lbl {
        font-size: .65rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .g-divider {
        width: 1px;
        background: rgba(255,255,255,.08);
        align-self: stretch;
    }

    /* === Section Header ====== */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .section-title {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .section-count {
        font-size: .7rem;
        color: #94a3b8;
        font-family: 'DM Mono', monospace;
        background: #f1f5f9;
        padding: .2rem .6rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }

    /* == App grid ======================== */
    .app-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 1100px) { .app-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .app-grid { grid-template-columns: 1fr; } }

    /* ==== App Card ================ */
    .app-card {
        background: white;
        border-radius: 18px;
        padding: 1.5rem;
        border: 1px solid #e8edf4;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: relative;
        overflow: hidden;
        transition: transform .22s cubic-bezier(.4,0,.2,1),
                    box-shadow .22s cubic-bezier(.4,0,.2,1),
                    border-color .22s;
        animation: cardIn .5s cubic-bezier(.4,0,.2,1) both;
    }
    .app-card:nth-child(1) { animation-delay: .05s; }
    .app-card:nth-child(2) { animation-delay: .10s; }
    .app-card:nth-child(3) { animation-delay: .15s; }
    .app-card:nth-child(4) { animation-delay: .20s; }
    .app-card:nth-child(5) { animation-delay: .25s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .app-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,.1);
    }

    .app-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 18px 18px 0 0;
        background: var(--card-accent);
        opacity: 0;
        transition: opacity .22s;
    }
    .app-card:hover::before { opacity: 1; }
    .app-card:hover .app-icon { transform: scale(1.08); }

    /* Icon */
    .app-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: transform .2s;
    }

    /* ── Card Top Row ── */
    .card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .5rem;
    }
    .card-type-badge {
        font-size: .6rem;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: .2rem .55rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .badge-internal { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-redirect { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

    /* ── Card Body ── */
    .app-name {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: .3rem;
    }
    .app-desc {
        font-size: .75rem;
        color: #94a3b8;
        line-height: 1.5;
    }

    /* ── Card Footer ── */
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: .75rem;
        border-top: 1px solid #f1f5f9;
    }
    .card-open-btn {
        font-size: .75rem;
        font-weight: 600;
        color: var(--card-accent-text, #0f172a);
        display: flex;
        align-items: center;
        gap: .3rem;
        transition: gap .2s;
    }
    .app-card:hover .card-open-btn { gap: .5rem; }
    .card-status-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 2px #dcfce7;
    }

    /* == Colour Theme ====== */
    .theme-blue   { --card-accent: #3b82f6; --card-accent-text: #1d4ed8; }
    .theme-green  { --card-accent: #10b981; --card-accent-text: #059669; }
    .theme-purple { --card-accent: #8b5cf6; --card-accent-text: #7c3aed; }
    .theme-rose   { --card-accent: #f43f5e; --card-accent-text: #e11d48; }
    .theme-amber  { --card-accent: #f59e0b; --card-accent-text: #d97706; }

    .icon-blue   { background: #eff6ff; color: #2563eb; }
    .icon-green  { background: #f0fdf4; color: #059669; }
    .icon-purple { background: #faf5ff; color: #7c3aed; }
    .icon-rose   { background: #fff1f2; color: #e11d48; }
    .icon-amber  { background: #fffbeb; color: #d97706; }

    /* === For Coming Soon ====== */
    .app-card.coming-soon {
        opacity: .6;
        cursor: default;
        pointer-events: none;
    }
    .coming-badge {
        font-size: .6rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
        padding: .2rem .55rem;
        border-radius: 999px;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ══ Greeting Hero ══ --}}
    <div class="greeting-card">
        <p class="greeting-time" id="greeting-time">—</p>
        <h1 class="greeting-text">
            Selamat datang, <span style="color:#14b8a6">{{ explode(' ', auth()->user()->name ?? 'Pengguna')[0] }}</span> 👋
        </h1>
        <p class="greeting-sub">Pilih dashboard yang ingin kamu buka dari portal di bawah ini.</p>
        <div class="greeting-stats">
            <div class="g-stat">
                <span class="g-stat-val">5</span>
                <span class="g-stat-lbl">Total Dashboard</span>
            </div>
            <div class="g-divider"></div>
            <div class="g-stat">
                <span class="g-stat-val" id="gs-date">—</span>
                <span class="g-stat-lbl">Tanggal</span>
            </div>
            <div class="g-divider"></div>
            <div class="g-stat">
                <span class="g-stat-val" id="gs-time">—</span>
                <span class="g-stat-lbl">Waktu</span>
            </div>
        </div>
    </div>

    {{-- ══ APPS ══ --}}
    <div class="section-header">
        <span class="section-title">Dashboard Tersedia</span>
        <span class="section-count">5 aplikasi</span>
    </div>

    <div class="app-grid">

        {{-- 1. Pelayanan Pasien --}}
        <a href="{{ route('portal.pelayananpasien') }}" class="app-card theme-blue">
            <div class="card-top">
                <div class="app-icon icon-blue">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="card-type-badge badge-redirect">INTERNAL</span>
            </div>
            <div>
                <div class="app-name">Pelayanan Pasien</div>
                <div class="app-desc">Rawat Jalan, Rawat Inap & IGD. Indikator BOR, LOS, TOI, BTO.</div>
            </div>
            <div class="card-footer">
                <span class="card-open-btn" style="color:#1d4ed8">
                    Buka Dashboard
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
                <span class="card-status-dot"></span>
            </div>
        </a>

        {{-- 2. Keuangan --}}
        <a href="{{ route('portal.keuangan') }}" class="app-card theme-green">
            <div class="card-top">
                <div class="app-icon icon-green">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="card-type-badge badge-redirect">Dashboard</span>
            </div>
            <div>
                <div class="app-name">Keuangan</div>
                <div class="app-desc">Laporan dan Monitoring Keuangan RSUD Jombang</div>
            </div>
            <div class="card-footer">
                <span class="card-open-btn" style="color:#059669">
                    Buka Dasboard
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </span>
                <span class="card-status-dot"></span>
            </div>
        </a>

        {{-- 3. SDM --}}
        <a href="{{ route('sdm.portal.sdm') }}" class="app-card theme-purple">
            <div class="card-top">
                <div class="app-icon icon-purple">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="card-type-badge badge-redirect">DASHBOARD</span>
            </div>
            <div>
                <div class="app-name">SDM</div>
                <div class="app-desc">Data Jumlah Pegawai RSUD Jombang.</div>
            </div>
            <div class="card-footer">
                <span class="card-open-btn" style="color:#7c3aed">
                    Buka Dashboard
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
                <span class="card-status-dot"></span>
            </div>
        </a>

        {{-- 4. Indikator Mutu --}}
        <a href="{{ route('portal.indikatormutu') }}" class="app-card theme-rose">
            <div class="card-top">
                <div class="app-icon icon-rose">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="card-type-badge badge-redirect">DASHBOARD</span>
            </div>
            <div>
                <div class="app-name">Indikator Mutu</div>
                <div class="app-desc">Monitoring indikator mutu dan keselamatan pasien rumah sakit.</div>
            </div>
            <!-- :#e11d48 -->
            <div class="card-footer">
                <span class="card-open-btn" style="color:#e11d48 ">
                    Buka Dashboard
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
                <span class="card-status-dot"></span>
            </div>
        </a>

        {{-- 5. Klaim BPJS --}}
        <a href="{{ route('portal.klaimbpjs') }}" class="app-card theme-amber">
            <div class="card-top">
                <div class="app-icon icon-amber">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="card-type-badge badge-redirect">Dashboard</span>
            </div>
            <div>
                <div class="app-name">Klaim BPJS</div>
                <div class="app-desc">Data Pasien BPJS RSUD Jombang</div>
            </div>
            <div class="card-footer">
                <span class="card-open-btn" style="color:#D97706">
                    Buka Klaim Bpjs
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
                <span class="card-status-dot"></span>
            </div>
        </a>

    </div>

    {{-- Info --}}
    <p class="text-xs text-gray-400 text-center mt-8">
        <!-- Login sebagai <strong class="text-gray-500">{{ auth()->user()->email }}</strong> &nbsp;·&nbsp; -->
        Dashboard Portal <span class="text-blue-500 font-semibold">RSUD JOMBANG</span>
        Support by <span class="text-green-600 font-semibold">IT WORKS RSUD JOMBANG</span>
    </p>

</div>
@endsection

@push('scripts')
<script>
    function updateGreeting() {
        const now = new Date();
        const h = now.getHours();
        let salam = h < 11 ? 'Selamat Pagi ☀️' : h < 15 ? 'Selamat Siang 🌤️' : h < 18 ? 'Selamat Sore 🌇' : 'Selamat Malam 🌙';

        document.getElementById('greeting-time').textContent = salam;
        document.getElementById('gs-date').textContent =
            now.toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
        document.getElementById('gs-time').textContent =
            now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
    }
    updateGreeting();
    setInterval(updateGreeting, 30000);
</script>
@endpush
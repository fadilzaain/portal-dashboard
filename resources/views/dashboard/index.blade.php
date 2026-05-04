@extends('layouts.app')

@section('title', 'Beranda Portal')
@section('page_title', 'Beranda Portal')
@section('page_subtitle', 'Pilih Dashboard')

@push('styles')
<style>
    .greeting-card {
        background: linear-gradient(135deg, #0a0f1e 0%, #0f172a 50%, #0c1a2e 100%);
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .greeting-card::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(20,184,166,.18) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .greeting-card::after {
        content: '';
        position: absolute; bottom: -40px; left: 30%;
        width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .greeting-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        position: relative;
        z-index: 1;
    }
    .greeting-time {
        font-size: .7rem; font-weight: 600; letter-spacing: .1em;
        text-transform: uppercase; color: #14b8a6; margin-bottom: .5rem;
        font-family: 'DM Mono', monospace;
    }
    .greeting-text { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; line-height: 1.3; }
    .greeting-sub  { font-size: .82rem; color: #64748b; margin-top: .4rem; }
    .greeting-stats { display: flex; gap: 1.5rem; margin-top: 1.5rem; position: relative; z-index: 1; }
    .g-stat { display: flex; flex-direction: column; gap: .2rem; }
    .g-stat-val { font-family: 'DM Mono', monospace; font-size: .95rem; font-weight: 500; color: #e2e8f0; }
    .g-stat-lbl { font-size: .65rem; color: #475569; text-transform: uppercase; letter-spacing: .08em; }
    .g-divider  { width: 1px; background: rgba(255,255,255,.08); align-self: stretch; }

    .filter-group  { display: flex; align-items: center; gap: .5rem; }
    .filter-label  { font-size: .65rem; color: #64748b; text-transform: uppercase; letter-spacing: .08em; white-space: nowrap; }
    .filter-select {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        color: #e2e8f0; font-size: .8rem; padding: .45rem .85rem;
        border-radius: 10px; cursor: pointer; font-family: 'DM Mono', monospace;
        appearance: none; min-width: 100px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .6rem center;
        padding-right: 2rem; transition: border-color .2s;
    }
    .filter-select:focus { outline: none; border-color: #14b8a6; }
    .filter-select option { background: #0f172a; }

    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
    .section-title  { font-size: .7rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8; }
    .section-count  {
        font-size: .7rem; color: #94a3b8; font-family: 'DM Mono', monospace;
        background: rgba(255,255,255,.05); padding: .2rem .6rem;
        border-radius: 999px; border: 1px solid rgba(255,255,255,.08);
    }

    .app-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    .app-grid .app-card:last-child { grid-column: span 2; }
    @media (max-width: 1100px) { .app-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .app-grid { grid-template-columns: 1fr; } }

    .app-card {
        background: #0d1526; border-radius: 18px; padding: 1.4rem 1.5rem;
        border: 1px solid rgba(255,255,255,.07); cursor: pointer; text-decoration: none;
        display: flex; flex-direction: column; gap: .85rem;
        position: relative; overflow: hidden;
        transition: transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s cubic-bezier(.4,0,.2,1), border-color .22s;
        animation: cardIn .5s cubic-bezier(.4,0,.2,1) both;
    }
    .app-card:nth-child(1) { animation-delay: .05s; }
    .app-card:nth-child(2) { animation-delay: .10s; }
    .app-card:nth-child(3) { animation-delay: .15s; }
    .app-card:nth-child(4) { animation-delay: .20s; }
    .app-card:nth-child(5) { animation-delay: .25s; }
    @keyframes cardIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .app-card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px rgba(0,0,0,.4); border-color: rgba(255,255,255,.15); }
    .app-card::before {
        content: ''; position: absolute; top:0; left:0; right:0;
        height: 3px; border-radius: 18px 18px 0 0;
        background: var(--card-accent); opacity: 0; transition: opacity .22s;
    }
    .app-card:hover::before { opacity: 1; }

    .card-header-row  { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .card-header-left { display: flex; align-items: center; gap: .75rem; }
    .app-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .card-title-wrap .app-name { font-size: .9rem; font-weight: 700; color: #f1f5f9; line-height: 1.2; }
    .card-title-wrap .app-sub  { font-size: .65rem; color: #475569; margin-top: .15rem; }
    .card-month-badge { font-size: .6rem; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; padding: .2rem .55rem; border-radius: 999px; border: 1px solid; white-space: nowrap; }

    .card-footer    { display: flex; align-items: center; justify-content: space-between; padding-top: .75rem; border-top: 1px solid rgba(255,255,255,.06); margin-top: auto; }
    .card-open-btn  { font-size: .73rem; font-weight: 600; display: flex; align-items: center; gap: .3rem; transition: gap .2s; color: var(--card-accent); }
    .app-card:hover .card-open-btn { gap: .5rem; }
    .card-status-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,.2); }

    .card-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .stat-box   { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); border-radius: 10px; padding: .6rem .75rem; }
    .stat-box-label { font-size: .6rem; color: #475569; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .25rem; }
    .stat-box-val   { font-family: 'DM Mono', monospace; font-size: .95rem; font-weight: 600; color: #e2e8f0; }
    .stat-box-badge { font-size: .55rem; font-weight: 600; padding: .1rem .4rem; border-radius: 4px; margin-left: .3rem; vertical-align: middle; }
    .badge-ideal { background: rgba(20,184,166,.15); color: #14b8a6; }
    .badge-baik  { background: rgba(34,197,94,.15);  color: #22c55e; }
    .badge-warn  { background: rgba(245,158,11,.15); color: #f59e0b; }

    .progress-bar-wrap { height: 5px; background: rgba(255,255,255,.07); border-radius: 999px; overflow: hidden; margin-top: .3rem; }
    .progress-bar-fill { height: 100%; border-radius: 999px; background: var(--card-accent); transition: width .8s ease; }
    .progress-pct      { font-family: 'DM Mono', monospace; font-size: .65rem; color: #94a3b8; text-align: right; margin-top: .2rem; }

    .fin-row { display: flex; flex-direction: column; gap: .4rem; }
    .fin-item { display: flex; justify-content: space-between; align-items: center; }
    .fin-label { font-size: .65rem; color: #64748b; }
    .fin-val   { font-family: 'DM Mono', monospace; font-size: .8rem; font-weight: 600; }
    .fin-val.up   { color: #22c55e; }
    .fin-val.down { color: #f43f5e; }
    .fin-selisih  { display: flex; justify-content: space-between; align-items: center; padding-top: .4rem; border-top: 1px solid rgba(255,255,255,.06); margin-top: .2rem; }
    .surplus-badge { background: rgba(34,197,94,.15); color: #22c55e; font-size: .6rem; font-weight: 600; padding: .15rem .5rem; border-radius: 5px; }
    .defisit-badge { background: rgba(244,63,94,.15); color: #f43f5e; font-size: .6rem; font-weight: 600; padding: .15rem .5rem; border-radius: 5px; }

    .sdm-row    { display: flex; align-items: center; gap: 1rem; }
    .donut-wrap { position: relative; width: 64px; height: 64px; flex-shrink: 0; }
    .donut-wrap svg { transform: rotate(-90deg); }
    .donut-center { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: 'DM Mono', monospace; font-size: .7rem; font-weight: 700; color: #e2e8f0; }
    .sdm-big-num { font-family: 'DM Mono', monospace; font-size: 2rem; font-weight: 700; color: #8b5cf6; line-height: 1; }
    .sdm-orang   { font-size: .65rem; color: #8b5cf6; text-transform: uppercase; letter-spacing: .07em; margin-top: .1rem; }
    .sdm-legend  { display: flex; flex-direction: column; gap: .4rem; }
    .legend-row  { display: flex; align-items: center; gap: .4rem; font-size: .65rem; color: #94a3b8; }
    .legend-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .legend-val  { font-family: 'DM Mono', monospace; font-weight: 600; color: #e2e8f0; }

    .mutu-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .mutu-box   { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); border-radius: 10px; padding: .65rem .5rem; text-align: center; }
    .mutu-box-label { font-size: .55rem; color: #475569; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .3rem; }
    .mutu-icon-wrap { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto .3rem; }
    .mutu-box-val   { font-family: 'DM Mono', monospace; font-size: 1.3rem; font-weight: 700; }
    .mutu-progress-label { display: flex; justify-content: space-between; font-size: .6rem; color: #64748b; margin-bottom: .25rem; }

    .klaim-row { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .klaim-box { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); border-radius: 10px; padding: .6rem .75rem; display: flex; align-items: center; justify-content: space-between; }
    .klaim-label { font-size: .6rem; color: #475569; text-transform: uppercase; letter-spacing: .05em; }
    .klaim-val   { font-family: 'DM Mono', monospace; font-size: 1.2rem; font-weight: 700; color: #e2e8f0; margin-top: .1rem; }
    .klaim-icon  { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .klaim-status { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; margin-top: .3rem; }
    .ks-box   { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); border-radius: 8px; padding: .5rem; }
    .ks-label { font-size: .55rem; color: #475569; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .2rem; }
    .ks-val   { font-family: 'DM Mono', monospace; font-size: 1.1rem; font-weight: 700; }
    .klaim-bar    { display: flex; height: 5px; border-radius: 999px; overflow: hidden; gap: 2px; margin-top: .5rem; }
    .kb-seg       { height: 100%; border-radius: 999px; transition: width .8s ease; }
    .klaim-pct-row { display: flex; gap: .8rem; margin-top: .3rem; }

    .theme-blue   { --card-accent: #3b82f6; }
    .theme-green  { --card-accent: #10b981; }
    .theme-purple { --card-accent: #8b5cf6; }
    .theme-rose   { --card-accent: #f43f5e; }
    .theme-amber  { --card-accent: #f59e0b; }

    .icon-blue   { background: rgba(59,130,246,.15);  color: #3b82f6; }
    .icon-green  { background: rgba(16,185,129,.15);  color: #10b981; }
    .icon-purple { background: rgba(139,92,246,.15);  color: #8b5cf6; }
    .icon-rose   { background: rgba(244,63,94,.15);   color: #f43f5e; }
    .icon-amber  { background: rgba(245,158,11,.15);  color: #f59e0b; }

    .month-blue   { color: #3b82f6; border-color: rgba(59,130,246,.3);  background: rgba(59,130,246,.08);  }
    .month-green  { color: #10b981; border-color: rgba(16,185,129,.3);  background: rgba(16,185,129,.08);  }
    .month-purple { color: #8b5cf6; border-color: rgba(139,92,246,.3);  background: rgba(139,92,246,.08);  }
    .month-rose   { color: #f43f5e; border-color: rgba(244,63,94,.3);   background: rgba(244,63,94,.08);   }
    .month-amber  { color: #f59e0b; border-color: rgba(245,158,11,.3);  background: rgba(245,158,11,.08);  }
</style>
@endpush

@section('content')
<div class="fade-up">

    <x-portal.greeting-card
        :bulan="$bulan ?? now()->month"
        :tahun="$tahun ?? now()->year"
        :bulanLabel="$bulanLabel ?? 'Mei'"
    />

    <div class="section-header">
        <span class="section-title">Dashboards Tersedia</span>
        <span class="section-count">5 aplikasi</span>
    </div>

    <div class="app-grid">
        <x-portal.card-pelayanan :pelayanan="$pelayanan" :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
        <x-portal.card-keuangan  :keuangan="$keuangan"   :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
        <x-portal.card-sdm       :sdm="$sdm"             :tahun="$tahun ?? now()->year" />
        <x-portal.card-mutu      :mutu="$mutu"            :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
        <x-portal.card-bpjs      :bpjs="$bpjs"            :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
    </div>

    <p class="text-xs text-center mt-8" style="color:#334155">
        Dashboard Portal
        <span style="color:#3b82f6;font-weight:600">RSUD JOMBANG</span>
        &nbsp;·&nbsp;
        Support by <span style="color:#10b981;font-weight:600">IT WORKS RSUD JOMBANG</span>
    </p>

</div>
@endsection

@push('scripts')
<script>
    function updateGreeting() {
        const now = new Date();
        const h   = now.getHours();
        const salam = h < 11 ? 'Selamat Pagi ☀️'
                    : h < 15 ? 'Selamat Siang 🌤️'
                    : h < 18 ? 'Selamat Sore 🌇'
                    :          'Selamat Malam 🌙';

        document.getElementById('greeting-time').textContent = salam;
        document.getElementById('gs-date').textContent =
            now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        document.getElementById('gs-time').textContent =
            now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    updateGreeting();
    setInterval(updateGreeting, 30_000);

    function applyFilter() {
        const bulan = document.getElementById('filter-bulan').value;
        const tahun = document.getElementById('filter-tahun').value;
        window.location.href = '{{ route("dashboard") }}?bulan=' + bulan + '&tahun=' + tahun;
    }
</script>
@endpush

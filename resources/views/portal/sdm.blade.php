@extends('layouts.app')

@section('title', 'SDM')
@section('page_title', 'SDM')
@section('page_subtitle', 'Monitoring Pegawai')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/portal/portal-navbar.css'])
@vite(['resources/css/portal/sdm.css'])
@endpush

@section('content')
<div class="sdm-wrap">

    <x-portal.navbar title="Portal SDM">
        <div class="pp-filter-pill">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" class="pp-filter-icon">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span class="pp-nav-date">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
        </div>
    </x-portal.navbar>

    {{-- STAT CARDS: data-driven, 1 loop buat 10 card biar gak duplikat markup --}}
    @php
        $statCards = [
            [
                'label' => 'Total Pegawai', 'value' => $totalPegawai, 'sublabel' => '100% dari keseluruhan', 'pct' => 100,
                'color' => 'blue', 'stroke' => '#38bdf8',
                'icon'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            ],
            [
                'label' => 'PNS', 'value' => $totalPns, 'sublabel' => $pctPns . '% dari total', 'pct' => $pctPns,
                'color' => 'teal', 'stroke' => '#2dd4bf',
                'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            ],
            [
                'label' => 'P3K', 'value' => $totalP3k, 'sublabel' => $pctP3k . '% dari total', 'pct' => $pctP3k,
                'color' => 'indigo', 'stroke' => '#a78bfa',
                'icon'  => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
            ],
            [
                'label' => 'P3K Paruh Waktu', 'value' => $totalP3kParuhWaktu, 'sublabel' => $pctP3kParuh . '% dari total', 'pct' => $pctP3kParuh,
                'color' => 'sky', 'stroke' => '#38bdf8',
                'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            ],
            [
                'label' => 'CPNS', 'value' => $totalCpns, 'sublabel' => $pctCpns . '% dari total', 'pct' => $pctCpns,
                'color' => 'amber', 'stroke' => '#f59e0b',
                'icon'  => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
            ],
            [
                'label' => 'Kontrak', 'value' => $totalKontrak, 'sublabel' => $pctKontrak . '% dari total', 'pct' => $pctKontrak,
                'color' => 'orange', 'stroke' => '#fb923c',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
            ],
            [
                'label' => 'Tetap', 'value' => $totalTetap, 'sublabel' => $pctTetap . '% dari total', 'pct' => $pctTetap,
                'color' => 'emerald', 'stroke' => '#34d399',
                'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            ],
            [
                'label' => 'Orientasi', 'value' => $totalOrientasi, 'sublabel' => $pctOrientasi . '% dari total', 'pct' => $pctOrientasi,
                'color' => 'purple', 'stroke' => '#a78bfa',
                'icon'  => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            ],
            [
                'label' => 'Medis', 'value' => $totalMedis, 'sublabel' => $pctMedis . '% dari total', 'pct' => $pctMedis,
                'color' => 'rose', 'stroke' => '#fb7185',
                'icon'  => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            ],
            [
                'label' => 'Non Medis', 'value' => $totalNonMedis, 'sublabel' => $pctNonMedis . '% dari total', 'pct' => $pctNonMedis,
                'color' => 'cyan', 'stroke' => '#22d3ee',
                'icon'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/>',
            ],
        ];
    @endphp
    <div class="sdm-stat-grid">
        @foreach ($statCards as $card)
        <div class="sdm-sc">
            <div class="sdm-sc-icon ic-{{ $card['color'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="{{ $card['stroke'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    {!! $card['icon'] !!}
                </svg>
            </div>
            <div class="sdm-sc-body">
                <div class="sdm-sc-label">{{ $card['label'] }}</div>
                <div class="sdm-sc-val">{{ number_format($card['value']) }} <span>Orang</span></div>
                <div class="sdm-sc-pct">{{ $card['sublabel'] }}</div>
                <div class="sdm-sc-bar">
                    <div class="sdm-sc-bar-fill bar-{{ $card['color'] }}" style="width:{{ min((int) $card['pct'], 100) }}%"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>


    {{-- ── MONITORING HARI INI ── --}}
    <div class="mon-section">

        <!-- {{-- 1. Ketersediaan SDM Hari Ini --}}
        <div class="mon-section-title">Ketersediaan SDM Hari Ini</div>
        <div class="mon-avail-grid" style="margin-bottom:20px">

            <div class="mon-avail-card">
                <div class="mon-avail-icon" style="background:rgba(56,189,248,0.12)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <div class="mon-avail-label">Total Hadir Hari Ini</div>
                    <div class="mon-avail-val" style="color:var(--accent-blue)">{{ number_format($monitoring['totalHadir']) }}</div>
                    <div class="mon-avail-sub">{{ $monitoring['pctHadir'] }}% dari total pegawai</div>
                </div>
            </div>

            <div class="mon-avail-card">
                <div class="mon-avail-icon" style="background:rgba(245,158,11,0.12)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <div class="mon-avail-label">Shift Aktif Sekarang</div>
                    <div class="mon-avail-val" style="color:var(--accent-amber)">{{ $monitoring['shiftAktifNama'] }}</div>
                    <div class="mon-avail-sub">{{ number_format($monitoring['shiftAktifTotal']) }} orang bertugas</div>
                </div>
            </div>

            <div class="mon-avail-card">
                <div class="mon-avail-icon" style="background:rgba(167,139,250,0.12)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div>
                    <div class="mon-avail-label">Rasio Kecukupan Global</div>
                    <div class="mon-avail-val" style="color:{{ $monitoring['rasioGlobal'] >= 80 ? 'var(--accent-green)' : ($monitoring['rasioGlobal'] >= 60 ? 'var(--accent-amber)' : 'var(--accent-red)') }}">
                        {{ $monitoring['rasioGlobal'] }}%
                    </div>
                    <div class="mon-avail-sub">tersedia vs kebutuhan formasi</div>
                </div>
            </div>

        </div> -->

        {{-- 2. Rasio SDM per Kategori --}}
        <div class="mon-section-title">Rasio Kecukupan SDM per Kategori</div>
        <div class="mon-rasio-grid" style="margin-bottom:20px">
            @foreach ($monitoring['rasioKategori'] as $kat)
            @php
                $statusCls = $kat['pct'] >= 80 ? 'status-aman' : ($kat['pct'] >= 60 ? 'status-waspada' : 'status-kritis');
                $fillCls   = $kat['pct'] >= 80 ? 'fill-aman'   : ($kat['pct'] >= 60 ? 'fill-waspada'   : 'fill-kritis');
                $statusTxt = $kat['pct'] >= 80 ? 'Aman'        : ($kat['pct'] >= 60 ? 'Waspada'        : 'Kritis');
            @endphp
            <div class="mon-rasio-card">
                <div class="mon-rasio-header">
                    <span class="mon-rasio-name">{{ $kat['nama'] }}</span>
                    <span class="mon-rasio-status {{ $statusCls }}">{{ $statusTxt }}</span>
                </div>
                <div class="mon-rasio-nums">
                    <span class="mon-rasio-pct" style="color:{{ $kat['pct'] >= 80 ? 'var(--accent-green)' : ($kat['pct'] >= 60 ? 'var(--accent-amber)' : 'var(--accent-red)') }}">{{ $kat['pct'] }}%</span>
                    <span class="mon-rasio-unit">terpenuhi</span>
                </div>
                <div class="mon-rasio-bar">
                    <div class="mon-rasio-fill {{ $fillCls }}" style="width:{{ min($kat['pct'], 100) }}%"></div>
                </div>
                <div class="mon-rasio-detail">{{ number_format($kat['tersedia']) }} / {{ number_format($kat['kebutuhan']) }} orang</div>
            </div>
            @endforeach
        </div>

        <!-- {{-- 3. Jabatan Kritis (rasio < 70%) --}}
        @if(count($monitoring['jabatanKritis']) > 0)
        <div class="mon-section-title" style="color:var(--accent-red)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Jabatan Kritis (rasio &lt; 70%)
        </div>
        <div class="mon-kritis-grid" style="margin-bottom:20px">
            @foreach ($monitoring['jabatanKritis'] as $item)
            <div class="mon-kritis-item">
                <div>
                    <div class="mon-kritis-name">{{ $item['jabatan'] }}</div>
                    <div class="mon-kritis-kat">{{ $item['kategori'] }}</div>
                </div>
                <div class="mon-kritis-right">
                    <div class="mon-kritis-pct">{{ $item['pct'] }}%</div>
                    <div class="mon-kritis-sub">{{ $item['tersedia'] }}/{{ $item['kebutuhan'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif -->

    </div>

    {{-- BEZETTING FULL WIDTH --}}
    <div class="sdm-panel sdm-bar-panel">
        <div class="sdm-panel-hd" style="margin-bottom:12px">
            <div class="sdm-panel-title">Bezetting SDM</div>
            <span style="font-size:11px; color:var(--text-muted);">Cache diperbarui tiap 1 jam</span>
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
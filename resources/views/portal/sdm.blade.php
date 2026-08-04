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


    {{-- ── UNIT & JABATAN PERLU PERHATIAN (2 card terpisah, sumber: API SDM Per Jenis) ── --}}
    <div class="mon-section">
        <div class="mon-section-title">Unit &amp; Jabatan Perlu Perhatian</div>
        <div class="prio-grid">

            {{-- CARD 1: Unit Perlu Perhatian → chart batang horizontal (Chart.js) --}}
            <div class="mon-card">
                <div class="mon-card-hd">
                    <span class="mon-card-title">Unit Perlu Perhatian</span>
                    <span class="mon-card-sub">Total kekurangan formasi per unit</span>
                </div>
                @if (empty($prioritasUnit))
                <div class="rk-empty-state">Tidak ada unit yang perlu perhatian saat ini.</div>
                @else
                <div class="uprio-chart-wrap">
                    <canvas id="prioUnitChart"></canvas>
                </div>
                @endif
            </div>

            {{-- CARD 2: Jabatan Perlu Perhatian → ranked list dgn track/dot (beda gaya dari chart batang biar gak monoton) --}}
            <div class="mon-card">
                <div class="mon-card-hd">
                    <span class="mon-card-title">Jabatan Perlu Perhatian</span>
                    <span class="mon-card-sub">Ranking jabatan dgn kekurangan formasi terbanyak</span>
                </div>
                @php
                    // Warna badge kategori disamain sama yang dipakai tabel Bezetting biar konsisten
                    $katCls        = ['Dokter' => 'kat-dokter', 'Perawat' => 'kat-perawat', 'Farmasi' => 'kat-farmasi', 'Medis Lainnya' => 'kat-medis'];
                    $maxKekJabatan = collect($prioritasJabatan)->max('kekurangan') ?: 1;
                @endphp
                <div class="jprio-list">
                    @forelse ($prioritasJabatan as $i => $p)
                    @php $trackPct = min((int) round($p['kekurangan'] / $maxKekJabatan * 100), 100); @endphp
                    <button type="button" class="jprio-item" onclick="sdmScrollToUnit('{{ $p['slug'] }}')">
                        <span class="jprio-rank">{{ $i + 1 }}</span>
                        <div class="jprio-body">
                            <div class="jprio-top">
                                <span class="jprio-jabatan">{{ $p['jabatan'] }}</span>
                                <span class="jprio-kekurangan">-{{ $p['kekurangan'] }}</span>
                            </div>
                            <div class="jprio-track">
                                <div class="jprio-track-fill" data-target-width="{{ $trackPct }}%" style="width:0%"></div>
                            </div>
                            <div class="jprio-meta">
                                <span class="jprio-unit">{{ $p['unit'] }}</span>
                                <span class="kat-badge {{ $katCls[$p['kategori']] ?? 'kat-lainnya' }}">{{ $p['kategori'] }}</span>
                            </div>
                        </div>
                    </button>
                    @empty
                    <div class="rk-empty-state">Tidak ada jabatan yang perlu perhatian saat ini.</div>
                    @endforelse
                </div>
            </div>

        </div>
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
                    <div class="bez-sc-icon ic-slate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>
                    </div>
                    <div>
                        <div class="bez-sc-label">Total Jabatan</div>
                        <div class="bez-sc-val">{{ $bezSummary['total'] }}</div>
                    </div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>
                    </div>
                    <div>
                        <div class="bez-sc-label">Kekurangan</div>
                        <div class="bez-sc-val red">{{ $bezSummary['totalKurang'] }}</div>
                    </div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="bez-sc-label">Total Orang Kurang</div>
                        <div class="bez-sc-val red">{{ $bezSummary['totalOrangKurang'] }}</div>
                    </div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                    </div>
                    <div>
                        <div class="bez-sc-label">Cukup</div>
                        <div class="bez-sc-val green">{{ $bezSummary['totalCukup'] }}</div>
                    </div>
                </div>
                <div class="bez-sc">
                    <div class="bez-sc-icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15V9"></path><path d="M12 15v-3"></path><path d="M6 15v-6"></path><path d="M2 9.5 8 5l4 3 8-5"></path></svg>
                    </div>
                    <div>
                        <div class="bez-sc-label">Surplus</div>
                        <div class="bez-sc-val teal">{{ $bezSummary['totalLebih'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Pill tabs dgn sliding indicator (gaya konsisten sama tab IGD) --}}
            <div class="bez-tabs" data-role="bez-tabs">
                <div class="bez-tab-indicator" data-role="bez-tab-indicator"></div>
                <button type="button" class="bez-tab t-kurang is-active" data-tone="red" onclick="bezSetTab('kurang', this)">
                    Kekurangan <span class="cnt">{{ $bezSummary['totalKurang'] }}</span>
                </button>
                <button type="button" class="bez-tab t-cukup" data-tone="green" onclick="bezSetTab('cukup', this)">
                    Cukup <span class="cnt">{{ $bezSummary['totalCukup'] }}</span>
                </button>
                <button type="button" class="bez-tab t-lebih" data-tone="blue" onclick="bezSetTab('lebih', this)">
                    Surplus <span class="cnt">{{ $bezSummary['totalLebih'] }}</span>
                </button>
            </div>

            {{-- Search + filter --}}
            <div class="bez-search">
                <div class="bez-search-input-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="bez-search" placeholder="Cari jabatan..." oninput="bezRender()">
                </div>
                <select id="bez-kat" onchange="bezRender()">
                    <option value="">Semua Kategori</option>
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
                <div class="bez-empty-state" id="bez-empty-state" style="display:none">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Tidak ada data yang cocok</span>
                </div>
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

{{-- ── DETAIL RASIO KECUKUPAN SDM PER UNIT (paling bawah, sumber: API SDM Per Jenis) ── --}}
    <div class="mon-section" style="margin-top:20px">
        <div class="mon-section-title">Detail Rasio Kecukupan SDM per Unit</div>

        @php
            // Badge status per baris jabatan (dipakai di kolom "Status" tabel detail tiap unit)
            $ketBadge = ['KURANG' => 'rk-badge-red', 'LEBIH' => 'rk-badge-blue', 'CUKUP' => 'rk-badge-green'];

            // Rekap jumlah unit per status, buat angka di pill filter toolbar di bawah
            $rkTotalUnit     = count($unitDetail);
            $rkStatusCounts  = collect($unitDetail)->countBy('status');
        @endphp

        {{-- Toolbar: cari nama unit + filter status (pola sama kayak bez-tabs/bez-search Bezetting di atas) --}}
        <div class="rk-toolbar">
            <div class="bez-search-input-wrap rk-unit-search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="rk-unit-search" placeholder="Cari nama unit / ruangan..." oninput="rkUnitRender()">
            </div>
            <div class="bez-tabs" data-role="rk-status-tabs">
                <div class="bez-tab-indicator" data-role="rk-status-indicator"></div>
                <button type="button" class="bez-tab t-semua is-active" data-tone="slate" onclick="rkSetStatusTab('', this)">
                    Semua <span class="cnt">{{ $rkTotalUnit }}</span>
                </button>
                <button type="button" class="bez-tab t-kritis" data-tone="red" onclick="rkSetStatusTab('kritis', this)">
                    Kritis <span class="cnt">{{ $rkStatusCounts->get('kritis', 0) }}</span>
                </button>
                <button type="button" class="bez-tab t-waspada" data-tone="amber" onclick="rkSetStatusTab('waspada', this)">
                    Waspada <span class="cnt">{{ $rkStatusCounts->get('waspada', 0) }}</span>
                </button>
                <button type="button" class="bez-tab t-aman" data-tone="green" onclick="rkSetStatusTab('aman', this)">
                    Aman <span class="cnt">{{ $rkStatusCounts->get('aman', 0) }}</span>
                </button>
            </div>
        </div>

        <div class="rk-grid" id="sdm-unit-grid">
            @forelse ($unitDetail as $i => $u)
            @php
                $rkRadius = 26;
                $rkRing   = 2 * M_PI * $rkRadius;
                $rkOffset = $rkRing - ($u['pct'] / 100 * $rkRing);
                $jumlahJabatan = count($u['detail']);

                // Urutin baris jabatan: KURANG paling atas (paling perlu perhatian), lalu LEBIH, baru CUKUP.
                // Di dalam status yang sama diurutin alfabetis biar gampang di-scan.
                $ketPriority   = ['KURANG' => 0, 'LEBIH' => 1, 'CUKUP' => 2];
                $sortedDetail  = collect($u['detail'])->sortBy(
                    fn($d) => sprintf('%d-%s', $ketPriority[$d['keterangan']] ?? 3, $d['jabatan'])
                )->values();
            @endphp
            <div class="rk-card status-{{ $u['status'] }}" data-rk="{{ $i }}" data-status="{{ $u['status'] }}" data-unit="{{ strtolower($u['unit']) }}" id="unit-{{ $u['slug'] }}">
                <button type="button" class="rk-card-head" onclick="rkToggle({{ $i }})" aria-expanded="false">
                    <div class="rk-ring-wrap">
                        <svg class="rk-ring" viewBox="0 0 64 64">
                            <circle class="rk-ring-track" cx="32" cy="32" r="{{ $rkRadius }}"></circle>
                            <circle class="rk-ring-fill" cx="32" cy="32" r="{{ $rkRadius }}"
                                style="stroke-dasharray:{{ $rkRing }};stroke-dashoffset:{{ $rkRing }}"
                                data-target-offset="{{ $rkOffset }}"></circle>
                        </svg>
                        <svg class="rk-ring-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
                        </svg>
                    </div>
                    <div class="rk-card-body">
                        <div class="rk-card-top">
                            <span class="rk-card-name">{{ $u['unit'] }}</span>
                            <span class="rk-status-pill">{{ ucfirst($u['status']) }}</span>
                        </div>
                        <div class="rk-card-pct">{{ $u['pct'] }}<span>%</span></div>
                        <div class="rk-card-sub">{{ number_format($u['tersedia']) }} / {{ number_format($u['kebutuhan']) }} orang &middot; {{ $jumlahJabatan }} jabatan</div>
                    </div>
                    <svg class="rk-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <div class="rk-expand">
                    <div class="rk-expand-inner">
                        <div class="rk-detail-toolbar">
                            <div class="rk-search-wrap">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input type="text" class="rk-search" placeholder="Cari jabatan..." oninput="rkFilter({{ $i }}, this.value)">
                            </div>
                            @if ($u['kurangCount'] > 0)
                            <span class="rk-kurang-tag">{{ $u['kurangCount'] }} formasi kurang</span>
                            @endif
                        </div>

                        {{-- Tabel detail per jabatan — sedetail bezetting SI-OSMAR: PNS/PPPK/PPPK-PW/Non ASN/Jumlah/Kebutuhan/Status --}}
                        <div class="rk-detail-wrap" id="rk-detail-{{ $i }}">
                            <div class="bez-table-wrap rk-table-wrap">
                                <table class="bez-table">
                                    <thead>
                                        <tr>
                                            <th>Jabatan</th>
                                            <th class="c" style="width:46px">PNS</th>
                                            <th class="c" style="width:50px">PPPK</th>
                                            <th class="c" style="width:62px">PPPK-PW</th>
                                            <th class="c" style="width:62px">Non ASN</th>
                                            <th class="c" style="width:52px">Jumlah</th>
                                            <th class="c" style="width:68px">Kebutuhan</th>
                                            <th class="c" style="width:78px">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sortedDetail as $d)
                                        <tr data-search="{{ strtolower($d['jabatan']) }}">
                                            <td>
                                                <div style="font-weight:600">{{ $d['jabatan'] }}</div>
                                                @if ($d['kualifikasi'] !== '-')
                                                <div style="font-size:10px;color:var(--sdm-text-muted);margin-top:1px">{{ $d['kualifikasi'] }}</div>
                                                @endif
                                            </td>
                                            <td class="c">{{ $d['pns'] }}</td>
                                            <td class="c">{{ $d['pppk'] }}</td>
                                            <td class="c">{{ $d['pppk_pw'] }}</td>
                                            <td class="c">{{ $d['non_asn'] }}</td>
                                            <td class="c" style="font-weight:700">{{ $d['jumlah'] }}</td>
                                            <td class="c">{{ $d['kebutuhan'] }}</td>
                                            <td class="c"><span class="rk-badge {{ $ketBadge[$d['keterangan']] ?? 'rk-badge-green' }}">{{ ucfirst(strtolower($d['keterangan'])) }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="rk-empty" id="rk-empty-{{ $i }}" style="display:none">Tidak ada jabatan yang cocok</div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="rk-empty-state">Data detail per unit belum tersedia.</div>
            @endforelse
        </div>
        <div class="rk-empty-state" id="rk-unit-empty" style="display:none">Tidak ada unit yang cocok dengan pencarian.</div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── RK CARD (dipakai bareng oleh section "Detail per Unit"): expand/collapse + search + animasi ring ──
function rkToggle(i) {
    const card = document.querySelector(`.rk-card[data-rk="${i}"]`);
    const btn  = card.querySelector('.rk-card-head');
    const open = card.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}

// Dipanggil dari card "Unit & Jabatan Perlu Perhatian" di atas: buka card unit
// yang dituju (kalau lagi ketutup) lalu smooth-scroll ke situ + kasih highlight sebentar
function sdmScrollToUnit(slug) {
    const card = document.getElementById(`unit-${slug}`);
    if (!card) return;

    if (!card.classList.contains('open')) {
        rkToggle(card.dataset.rk);
    }

    card.scrollIntoView({ behavior: 'smooth', block: 'center' });

    card.classList.add('rk-highlight');
    setTimeout(() => card.classList.remove('rk-highlight'), 1600);
}

function rkFilter(i, q) {
    q = q.trim().toLowerCase();
    const wrap = document.getElementById(`rk-detail-${i}`);
    const rows = wrap.querySelectorAll('tbody tr');
    let visible = 0;

    rows.forEach(row => {
        const match = !q || row.dataset.search.includes(q);
        row.classList.toggle('rk-row-hidden', !match);
        if (match) visible++;
    });

    document.getElementById(`rk-empty-${i}`).style.display = visible ? 'none' : 'block';
}

// ── TOOLBAR UNIT: cari nama unit + filter status (Semua/Kritis/Waspada/Aman) ──
let rkStatusFilter = '';
const rkTabsEl      = document.querySelector('[data-role="rk-status-tabs"]');
const rkIndicatorEl = document.querySelector('[data-role="rk-status-indicator"]');

function rkSetStatusTab(status, el) {
    rkStatusFilter = status;
    rkTabsEl.querySelectorAll('.bez-tab').forEach(t => t.classList.remove('is-active'));
    el.classList.add('is-active');
    moveTabIndicator(rkIndicatorEl, el);
    rkUnitRender();
}

function rkUnitRender() {
    const q = document.getElementById('rk-unit-search').value.trim().toLowerCase();
    const cards = document.querySelectorAll('#sdm-unit-grid .rk-card');
    let visible = 0;

    cards.forEach(card => {
        const match = (!rkStatusFilter || card.dataset.status === rkStatusFilter)
                   && (!q || card.dataset.unit.includes(q));
        card.classList.toggle('rk-row-hidden', !match);
        if (match) visible++;
    });

    const emptyEl = document.getElementById('rk-unit-empty');
    if (emptyEl) emptyEl.style.display = visible ? 'none' : 'block';
}

requestAnimationFrame(() => {
    moveTabIndicator(rkIndicatorEl, rkTabsEl?.querySelector('.bez-tab.is-active'));
    rkTabsEl?.classList.add('is-ready');
});

// Animasi ring progress dari 0% ke nilai aslinya begitu card kebaca browser
// (skip stagger kalau user set preferensi "reduce motion" di OS/browser-nya)
const sdmReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
document.querySelectorAll('.rk-ring-fill').forEach((ring, idx) => {
    const target = ring.dataset.targetOffset;
    if (sdmReduceMotion) {
        ring.style.strokeDashoffset = target;
        return;
    }
    requestAnimationFrame(() => {
        setTimeout(() => { ring.style.strokeDashoffset = target; }, 80 * idx);
    });
});

// Track ranking "Jabatan Perlu Perhatian" dari 0% ke nilai aslinya begitu kebaca browser
// (pola sama kayak animasi rk-ring-fill di atas biar konsisten)
document.querySelectorAll('.jprio-track-fill').forEach((el, idx) => {
    const target = el.dataset.targetWidth;
    if (sdmReduceMotion) {
        el.style.width = target;
        return;
    }
    requestAnimationFrame(() => {
        setTimeout(() => { el.style.width = target; }, 60 * idx);
    });
});

// ── CHART "UNIT PERLU PERHATIAN" (horizontal bar, gradient, klik → scroll ke unit) ─────────
const prioUnitCanvas = document.getElementById('prioUnitChart');
if (prioUnitCanvas) {
    const prioUnitSlugs = {!! json_encode(collect($prioritasUnit ?? [])->pluck('slug')) !!};
    new Chart(prioUnitCanvas, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($prioritasUnit ?? [])->pluck('unit')) !!},
            datasets: [{
                data: {!! json_encode(collect($prioritasUnit ?? [])->pluck('kekurangan')) !!},
                backgroundColor(ctx) {
                    const { chartArea, ctx: c } = ctx.chart;
                    if (!chartArea) return 'rgba(248,113,113,0.7)';
                    const g = c.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                    g.addColorStop(0, 'rgba(248,113,113,0.55)');
                    g.addColorStop(1, 'rgba(239,68,68,0.95)');
                    return g;
                },
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 16,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.08)' },
                    ticks: { color: '#94a3b8', precision: 0, font: { size: 10.5, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans', weight: '600' } }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0a1628',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(248,113,113,.3)',
                    borderWidth: 1,
                    callbacks: { label: ctx => ` ${ctx.parsed.x.toLocaleString('id-ID')} orang kurang` }
                }
            },
            onClick(evt, elements) {
                if (!elements.length) return;
                const slug = prioUnitSlugs[elements[0].index];
                if (slug) sdmScrollToUnit(slug);
            },
            onHover(evt, elements) {
                evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            }
        }
    });
}

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
const bezTabsEl      = document.querySelector('[data-role="bez-tabs"]');
const bezIndicatorEl = document.querySelector('[data-role="bez-tab-indicator"]');

// Geser pill indicator ke tombol tab yang aktif + ganti warna sesuai tone (red/green/blue)
// Geser pill indicator ke tombol tab yang aktif + ganti warna sesuai tone
// Dipakai bareng oleh bez-tabs (Bezetting) dan rk-status-tabs (Detail Rasio Kecukupan) biar gak duplikat logic
function moveTabIndicator(indicatorEl, btn) {
    if (!indicatorEl || !btn) return;
    indicatorEl.style.left   = btn.offsetLeft + 'px';
    indicatorEl.style.width  = btn.offsetWidth + 'px';
    indicatorEl.dataset.tone = btn.dataset.tone;
}

function bezMoveIndicator(btn) {
    moveTabIndicator(bezIndicatorEl, btn);
}

function bezSetTab(tab, el) {
    bezTab = tab;
    document.querySelectorAll('.bez-tab').forEach(t => t.classList.remove('is-active'));
    el.classList.add('is-active');
    bezMoveIndicator(el);
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

    const tbody     = document.getElementById('bez-tbody');
    const emptyState = document.getElementById('bez-empty-state');

    if (!rows.length) {
        tbody.innerHTML = '';
        emptyState.style.display = 'flex';
        return;
    }
    emptyState.style.display = 'none';

    const tone     = bezTab === 'kurang' ? 'red' : bezTab === 'cukup' ? 'green' : 'blue';
    const badgeCls = bezTab === 'kurang' ? 'rk-badge-red' : bezTab === 'cukup' ? 'rk-badge-green' : 'rk-badge-blue';

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
                        <div class="mini-bar"><div class="mini-fill tone-${tone}" style="width:${r.p}%"></div></div>
                        <div style="font-size:9px;color:var(--text-muted);margin-top:1px">${r.p}%</div>
                    </div>
                </div>
            </td>
            <td class="c"><span class="rk-badge ${badgeCls}">${sign}${r.d}</span></td>
        </tr>`;
    }).join('');
}

// Posisi awal indicator dan hint pulse 
requestAnimationFrame(() => {
    bezMoveIndicator(bezTabsEl?.querySelector('.bez-tab.is-active'));
    bezTabsEl?.classList.add('is-ready');
    if (!sdmReduceMotion) bezTabsEl?.classList.add('bez-tab-hint');
});

window.addEventListener('resize', () => {
    bezMoveIndicator(bezTabsEl?.querySelector('.bez-tab.is-active'));
    moveTabIndicator(rkIndicatorEl, rkTabsEl?.querySelector('.bez-tab.is-active'));
});

bezRender();
</script>

@endpush
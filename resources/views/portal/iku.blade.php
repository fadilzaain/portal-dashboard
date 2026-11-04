{{-- ============================================================
     Dashboard IKU 
     ============================================================ --}}
@extends('layouts.app')

@section('title', 'Dashboard IKU')
@section('page_title', 'Dashboard IKU')
@section('page_subtitle', 'Indikator Kinerja Utama')

@push('styles')
@vite(['resources/css/portal/portal-navbar.css', 'resources/css/portal/iku.css'])
@endpush

@section('content')
<div class="iku-wrap">

    {{-- NAVBAR + FILTER PERIODE (pakai komponen navbar yang sama dengan modul lain) --}}
    <x-portal.navbar title="Dashboard IKU">
        <form method="GET" action="{{ route('portal.iku') }}" id="ikuFilterForm">
            <div class="pp-filter-pill">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" class="pp-filter-icon">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <select name="triwulan" onchange="document.getElementById('ikuFilterForm').submit()" class="pp-nav-select" aria-label="Triwulan">
                    @foreach ($triwulanOpsi as $no => $romawi)
                        <option value="{{ $no }}" @selected($no === $triwulan)>TW {{ $romawi }}</option>
                    @endforeach
                </select>
                <span class="pp-filter-sep"></span>
                <select name="tahun" onchange="document.getElementById('ikuFilterForm').submit()" class="pp-nav-select" aria-label="Tahun">
                    @foreach ($tahunList as $th)
                        <option value="{{ $th }}" @selected($th === $tahun)>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-portal.navbar>

    {{-- JUDUL HALAMAN --}}
    <header class="iku-head">
        <div>
            <h1 class="iku-title">Indikator Kinerja Utama</h1>
            <p class="iku-sub">Dashboard Laporan pencapaian indikator kinerja utama RSUD Jombang terhadap target tahunan</p>
        </div>
        @if ($data)
            <div class="iku-periode">
                <x-icon name="calendar" width="14" height="14" />
                <span>{{ $data['periode_label'] }} <em>· s.d. {{ $data['akhir_periode'] }}</em></span>
            </div>
        @endif
    </header>

    {{-- STATE ERROR: tabel belum dimigrate / database tidak terjangkau --}}
    @if ($error)
        <div class="iku-state iku-state--error" role="alert">
            <x-icon name="alert-triangle" width="26" height="26" />
            <div class="iku-state-title">Data tidak dapat ditampilkan</div>
            <div class="iku-state-text">{{ $error }}</div>
        </div>

    {{-- STATE KOSONG: belum ada indikator sama sekali --}}
    @elseif (empty($data['baris']))
        <div class="iku-state">
            <x-icon name="clipboard" width="26" height="26" />
            <div class="iku-state-title">Belum ada indikator IKU</div>
            <div class="iku-state-text">Indikator akan tampil di sini setelah ditambahkan oleh admin.</div>
        </div>

    @else
        {{-- KARTU RINGKASAN --}}
        @php
            $r = $data['ringkasan'];
            $kartu = [
                ['label' => 'Total Indikator',        'nilai' => $r['total'],              'sub' => $r['berdata'] . ' berdata · ' . $r['belum_ada_data'] . ' belum ada data', 'ikon' => 'clipboard',    'warna' => 'sky'],
                ['label' => 'Indikator Tercapai',     'nilai' => $r['tercapai'],           'sub' => 'dari ' . $r['berdata'] . ' indikator berdata',                          'ikon' => 'check-circle', 'warna' => 'success'],
                ['label' => 'Belum Tercapai',         'nilai' => $r['belum_tercapai'],     'sub' => 'termasuk ' . $r['perlu_perhatian'] . ' perlu perhatian',                 'ikon' => 'x-circle',     'warna' => 'danger'],
                ['label' => 'Persentase Capaian IKU', 'nilai' => $r['persen_capaian_fmt'], 'sub' => 'rata-rata capaian indikator berdata',                                    'ikon' => 'gauge',        'warna' => 'teal'],
            ];
        @endphp
        <section class="iku-kpi-grid" aria-label="Ringkasan capaian IKU">
            @foreach ($kartu as $k)
                <article class="iku-kpi iku-kpi--{{ $k['warna'] }}" style="--i: {{ $loop->index }}">
                    <div class="iku-kpi-icon"><x-icon name="{{ $k['ikon'] }}" width="18" height="18" /></div>
                    <div class="iku-kpi-body">
                        <div class="iku-kpi-label">{{ $k['label'] }}</div>
                        <div class="iku-kpi-value">{{ $k['nilai'] }}</div>
                        <div class="iku-kpi-sub">{{ $k['sub'] }}</div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- TABEL INDIKATOR (baris netral; status hanya lewat badge) --}}
        <section class="iku-panel" style="--i: 4">
            <div class="iku-panel-head">
                <h2 class="iku-panel-title">Daftar Indikator Kinerja Utama</h2>
                <span class="iku-panel-meta">{{ $data['periode_singkat'] }}</span>
            </div>

            <div class="iku-table-wrap">
                <table class="iku-table">
                    <thead>
                        <tr>
                            <th>Nama Indikator</th>
                            <th>Sasaran Strategis</th>
                            <th class="iku-num">Target</th>
                            <th class="iku-num">Realisasi</th>
                            <th>Persentase Capaian</th>
                            <th>Status</th>
                            <th>Periode</th>
                            <th class="iku-col-aksi"><span class="iku-sr">Detail</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['baris'] as $b)
                            @php
                                // Link ke halaman detail, membawa filter yang sedang dipilih
                                $urlDetail = route('portal.iku.detail', ['indikator' => $b['id'], 'tahun' => $data['tahun'], 'triwulan' => $data['triwulan']]);
                            @endphp
                            <tr>
                                <td class="iku-col-nama">
                                    <a href="{{ $urlDetail }}" class="iku-nama iku-nama--link">{{ $b['nama'] }}</a>
                                    @if ($b['catatan'])
                                        <div class="iku-catatan">{{ $b['catatan'] }}</div>
                                    @endif
                                </td>
                                <td class="iku-col-sasaran">{{ $b['sasaran'] }}</td>
                                <td class="iku-num">{{ $b['target_fmt'] }}</td>
                                <td class="iku-num iku-num--strong">{{ $b['realisasi_fmt'] }}</td>
                                <td class="iku-col-capaian">
                                    <div class="iku-capaian">
                                        <div class="iku-bar iku-bar--{{ $b['status'] }}" style="--w: {{ $b['bar'] }}%"><span></span></div>
                                        <span class="iku-capaian-val">{{ $b['capaian_fmt'] }}</span>
                                    </div>
                                </td>
                                <td><span class="iku-badge iku-badge--{{ $b['status'] }}">{{ $b['status_label'] }}</span></td>
                                <td class="iku-col-periode">{{ $b['periode'] }}</td>
                                <td class="iku-col-aksi">
                                    <a href="{{ $urlDetail }}" class="iku-aksi" aria-label="Lihat detail {{ $b['nama'] }}" title="Lihat detail">
                                        <x-icon name="chevron-right" width="16" height="16" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- GRAFIK --}}
        @if ($data['grafik']['ada_data'])
            <section class="iku-grid-2">
                <div class="iku-panel" style="--i: 5">
                    <div class="iku-panel-head">
                        <h2 class="iku-panel-title">Capaian terhadap Target</h2>
                        <span class="iku-panel-meta">{{ $data['periode_singkat'] }}</span>
                    </div>
                    <div class="iku-chart-body">
                        <div class="iku-chart iku-chart--bar" style="--n: {{ count($data['grafik']['capaian']['nilai']) }}">
                            <canvas id="ikuChartCapaian" role="img" aria-label="Grafik capaian tiap indikator terhadap target"></canvas>
                        </div>
                        <p class="iku-chart-note">Garis putus-putus = target (100%). Nilai mentah target dan realisasi ada di tooltip.</p>
                    </div>
                </div>

                <div class="iku-panel" style="--i: 6">
                    <div class="iku-panel-head">
                        <h2 class="iku-panel-title">Tren Capaian per Triwulan</h2>
                        <span class="iku-panel-meta">{{ $data['tahun'] }}</span>
                    </div>
                    <div class="iku-chart-body">
                        <div class="iku-chart iku-chart--bar" style="--n: {{ count($data['grafik']['capaian']['nilai']) }}">
                            <canvas id="ikuChartTren" role="img" aria-label="Grafik tren capaian indikator per triwulan"></canvas>
                        </div>
                        <p class="iku-chart-note">Triwulan yang belum berdata dibiarkan kosong, bukan dihitung 0.</p>
                    </div>
                </div>
            </section>
        @else
            <div class="iku-state iku-state--inline" style="--i: 5">
                <x-icon name="activity" width="22" height="22" />
                <div class="iku-state-title">Belum ada realisasi pada periode ini</div>
                <div class="iku-state-text">Grafik muncul setelah ada indikator yang punya realisasi dan target pada {{ $data['periode_label'] }}.</div>
            </div>
        @endif

        {{-- REKAP PER SASARAN STRATEGIS --}}
        <section class="iku-panel" style="--i: 7">
            <div class="iku-panel-head">
                <h2 class="iku-panel-title">Rekap per Sasaran Strategis</h2>
                <span class="iku-panel-meta">{{ $data['periode_singkat'] }}</span>
            </div>
            <ul class="iku-sasaran">
                @foreach ($data['sasaran'] as $s)
                    <li class="iku-sasaran-item">
                        <div class="iku-sasaran-top">
                            <span class="iku-sasaran-nama">{{ $s['nama'] }}</span>
                            <span class="iku-sasaran-val">{{ $s['persen_fmt'] }}</span>
                        </div>
                        <div class="iku-bar iku-bar--{{ $s['status'] }}" style="--w: {{ $s['bar'] }}%"><span></span></div>
                        <div class="iku-sasaran-sub">
                            {{ $s['tercapai'] }} dari {{ $s['berdata'] }} indikator berdata tercapai
                            @if ($s['berdata'] < $s['total'])
                                · {{ $s['total'] - $s['berdata'] }} belum ada data
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

</div>
@endsection

{{-- Data grafik dikirim sebagai JSON; @json meng-escape karakter berbahaya untuk dipakai di dalam <script> --}}
@if (! $error && ! empty($data['grafik']['ada_data']))
    @push('scripts')
    <script>window.IKU_DATA = @json($data['grafik']);</script>
    @vite('resources/js/portal/iku.js')
    @endpush
@endif
{{-- ============================================================
     Detail satu indikator IKU. Semua angka datang dari IkuService::detail().
     ============================================================ --}}
@extends('layouts.app')

@section('title', $data['indikator']['nama'])
@section('page_title', 'Dashboard IKU')
@section('page_subtitle', 'Detail Indikator')

@push('styles')
@vite(['resources/css/portal/portal-navbar.css', 'resources/css/portal/iku.css'])
@endpush

@section('content')
@php
    $ind = $data['indikator'];
    $b   = $data['baris'];

    // Warna kartu mengikuti status capaian
    $warnaKartu = ['tercapai' => 'success', 'perhatian' => 'warning', 'belum' => 'danger', 'kosong' => 'muted'][$b['status']];

    // Teks yang belum diisi admin ditampilkan sebagai "Belum diisi", bukan dikosongkan
    $isi = fn ($teks) => filled($teks) ? e($teks) : '<span class="iku-kosong">Belum diisi</span>';
@endphp
<div class="iku-wrap">

    <x-portal.navbar title="Dashboard IKU">
        <form method="GET" action="{{ route('portal.iku.detail', ['indikator' => $ind['id']]) }}" id="ikuFilterForm">
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

    <a href="{{ route('portal.iku', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="iku-back">
        <x-icon name="chevron-left" width="14" height="14" />
        Kembali ke Dashboard IKU
    </a>

    {{-- JUDUL --}}
    <header class="iku-head">
        <div>
            <h1 class="iku-title">{{ $ind['nama'] }}</h1>
            <p class="iku-sub">{{ $ind['sasaran'] }}</p>
        </div>
        <div class="iku-head-meta">
            <span class="iku-periode">
                <x-icon name="calendar" width="14" height="14" />
                <span>{{ $data['periode_label'] }} <em>· s.d. {{ $data['akhir_periode'] }}</em></span>
            </span>
            <span class="iku-badge iku-badge--{{ $b['status'] }} iku-badge--lg">{{ $b['status_label'] }}</span>
        </div>
    </header>

    {{-- TARGET / REALISASI / CAPAIAN --}}
    <section class="iku-kpi-grid iku-kpi-grid--3">
        <article class="iku-kpi iku-kpi--sky" style="--i: 0">
            <div class="iku-kpi-icon"><x-icon name="clipboard" width="18" height="18" /></div>
            <div class="iku-kpi-body">
                <div class="iku-kpi-label">Target {{ $data['tahun'] }}</div>
                <div class="iku-kpi-value">{{ $b['target_fmt'] }}</div>
                <div class="iku-kpi-sub">{{ $ind['jenis_label'] }}</div>
            </div>
        </article>
        <article class="iku-kpi iku-kpi--teal" style="--i: 1">
            <div class="iku-kpi-icon"><x-icon name="activity" width="18" height="18" /></div>
            <div class="iku-kpi-body">
                <div class="iku-kpi-label">Realisasi</div>
                <div class="iku-kpi-value">{{ $b['realisasi_fmt'] }}</div>
                <div class="iku-kpi-sub">per {{ $data['akhir_periode'] }}</div>
            </div>
        </article>
        <article class="iku-kpi iku-kpi--{{ $warnaKartu }}" style="--i: 2">
            <div class="iku-kpi-icon"><x-icon name="gauge" width="18" height="18" /></div>
            <div class="iku-kpi-body">
                <div class="iku-kpi-label">Persentase Capaian</div>
                <div class="iku-kpi-value">{{ $b['capaian_fmt'] }}</div>
                <div class="iku-bar iku-bar--{{ $b['status'] }} iku-bar--kartu" style="--w: {{ $b['bar'] }}%"><span></span></div>
            </div>
        </article>
    </section>

    @if ($b['catatan'])
        <p class="iku-catatan iku-catatan--blok">{{ $b['catatan'] }}</p>
    @endif

    {{-- INFORMASI INDIKATOR --}}
    <section class="iku-panel" style="--i: 3">
        <div class="iku-panel-head">
            <h2 class="iku-panel-title">Informasi Indikator</h2>
        </div>
        <dl class="iku-info">
            <div class="iku-info-item iku-info-item--lebar">
                <dt>Definisi Indikator</dt>
                <dd class="iku-teks">{!! $isi($ind['definisi']) !!}</dd>
            </div>
            <div class="iku-info-item iku-info-item--lebar">
                <dt>Rumus Perhitungan</dt>
                <dd class="iku-teks">{!! $isi($ind['rumus']) !!}</dd>
            </div>
            <div class="iku-info-item">
                <dt>Sasaran Strategis</dt>
                <dd>{{ $ind['sasaran'] }}</dd>
            </div>
            <div class="iku-info-item">
                <dt>Satuan</dt>
                <dd>{{ $ind['satuan'] }}</dd>
            </div>
            <div class="iku-info-item">
                <dt>Periode Pengukuran</dt>
                <dd>Per triwulan, kumulatif tahun berjalan</dd>
            </div>
            <div class="iku-info-item">
                <dt>Penanggung Jawab</dt>
                <dd>{!! $isi($ind['penanggung_jawab']) !!}</dd>
            </div>
        </dl>
    </section>

    {{-- GRAFIK + RIWAYAT --}}
    <section class="iku-grid-2">
        <div class="iku-panel" style="--i: 4">
            <div class="iku-panel-head">
                <h2 class="iku-panel-title">Realisasi vs Target</h2>
                <span class="iku-panel-meta">{{ $data['tahun'] }}</span>
            </div>
            <div class="iku-chart-body">
                @if ($data['grafik']['ada_data'])
                    <div class="iku-chart"><canvas id="ikuChartDetail" role="img" aria-label="Grafik realisasi terhadap target per triwulan"></canvas></div>
                @else
                    <div class="iku-state iku-state--inline">
                        <x-icon name="activity" width="22" height="22" />
                        <div class="iku-state-title">Belum ada realisasi tahun {{ $data['tahun'] }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="iku-panel" style="--i: 5">
            <div class="iku-panel-head">
                <h2 class="iku-panel-title">Riwayat Realisasi</h2>
                <span class="iku-panel-meta">{{ $data['tahun'] }}</span>
            </div>
            <div class="iku-table-wrap">
                <table class="iku-table iku-table--ringkas">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th class="iku-num">Realisasi</th>
                            <th class="iku-num">Capaian</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['riwayat'] as $rw)
                            <tr>
                                <td class="iku-col-periode">
                                    {{ $rw['periode'] }}
                                    @if ($rw['terpilih'])<span class="iku-tag">dipilih</span>@endif
                                </td>
                                <td class="iku-num iku-num--strong">{{ $rw['realisasi_fmt'] }}</td>
                                <td class="iku-num">{{ $rw['capaian_fmt'] }}</td>
                                <td><span class="iku-badge iku-badge--{{ $rw['status'] }}">{{ $rw['status_label'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- EVALUASI & TINDAK LANJUT (periode yang dipilih) --}}
    <section class="iku-panel" style="--i: 6">
        <div class="iku-panel-head">
            <h2 class="iku-panel-title">Evaluasi dan Tindak Lanjut</h2>
            <span class="iku-panel-meta">{{ $data['periode_singkat'] }}</span>
        </div>
        <dl class="iku-info">
            <div class="iku-info-item">
                <dt>Catatan Evaluasi</dt>
                <dd class="iku-teks">{!! $isi($data['evaluasi']['catatan']) !!}</dd>
            </div>
            <div class="iku-info-item">
                <dt>Rencana Tindak Lanjut</dt>
                <dd class="iku-teks">{!! $isi($data['evaluasi']['rencana']) !!}</dd>
            </div>
        </dl>
    </section>

</div>
@endsection

@if ($data['grafik']['ada_data'])
    @push('scripts')
    <script>window.IKU_DETAIL = @json($data['grafik']);</script>
    @vite('resources/js/portal/iku.js')
    @endpush
@endif
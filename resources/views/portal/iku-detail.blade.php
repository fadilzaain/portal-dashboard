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
            <span class="pc-badge pc-badge--{{ $b['status'] }} pc-badge--lg">{{ $b['status_label'] }}</span>
        </div>
    </header>

    {{-- TARGET / REALISASI / CAPAIAN --}}
    <section class="pc-kpi-grid pc-kpi-grid--3">
        <x-portal.kpi-card :label="'Target ' . $data['tahun']" :value="$b['target_fmt']" :sub="$ind['jenis_label']" icon="clipboard" color="sky" :i="0" />
        <x-portal.kpi-card label="Realisasi" :value="$b['realisasi_fmt']" :sub="'per ' . $data['akhir_periode']" icon="activity" color="teal" :i="1" />
        <x-portal.kpi-card label="Persentase Capaian" :value="$b['capaian_fmt']" icon="gauge" :color="$warnaKartu" :i="2">
            <div class="pc-bar pc-bar--{{ $b['status'] }} pc-bar--kartu" style="--w: {{ $b['bar'] }}%"><span></span></div>
        </x-portal.kpi-card>
    </section>

    @if ($b['catatan'])
        <p class="iku-catatan iku-catatan--blok">{{ $b['catatan'] }}</p>
    @endif

    {{-- INFORMASI INDIKATOR --}}
    <x-portal.panel title="Informasi Indikator" :i="3">
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
                <dt>Pengukuran</dt>
                <dd>{{ $ind['pengukuran'] }}</dd>
                <dd>Per triwulan, kumulatif tahun berjalan</dd>
            </div>
            <div class="iku-info-item">
                <dt>Penanggung Jawab</dt>
                <dd>{!! $isi($ind['penanggung_jawab']) !!}</dd>
            </div>
        </dl>
    </x-portal.panel>

    {{-- GRAFIK + RIWAYAT --}}
    <section class="pc-grid-2">
        <x-portal.panel title="Realisasi vs Target" :meta="$data['tahun']" :i="4">
            <div class="pc-panel-body">
                @if ($data['grafik']['ada_data'])
                    <div class="iku-chart"><canvas id="ikuChartDetail" role="img" aria-label="Grafik realisasi terhadap target per triwulan"></canvas></div>
                @else
                    <div class="pc-state pc-state--inline">
                        <x-icon name="activity" width="22" height="22" />
                        <div class="pc-state-title">Belum ada realisasi tahun {{ $data['tahun'] }}</div>
                    </div>
                @endif
            </div>
        </x-portal.panel>

        <x-portal.panel title="Riwayat Realisasi" :meta="$data['tahun']" :i="5">
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
                                <td><span class="pc-badge pc-badge--{{ $rw['status'] }}">{{ $rw['status_label'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-portal.panel>
    </section>

</div>
@endsection

@if ($data['grafik']['ada_data'])
    @push('scripts')
    <script>window.IKU_DETAIL = @json($data['grafik']);</script>
    @vite('resources/js/portal/iku.js')
    @endpush
@endif
{{--
    Rombakan dashi v2 - Kartu ringkasan (KPI) bersama untuk semua halaman portal.
    Gaya ada di resources/css/layout/cards.css (.pc-kpi*).

    Nilai teks biasa:
    <x-portal.kpi-card label="Total Indikator" :value="$total" sub="12 berdata"
                       icon="clipboard" color="sky" :i="0" />

    Nilai angka yang menghitung naik (count-up) + satuan:
    <x-portal.kpi-card label="BOR" :count="72.35" :decimal="2" unit="%" sub="Standar 60 – 85%"
                       icon="bed" color="sky" :i="0" />

    Isi tambahan (progress bar, badge, tombol) taruh di dalam tag, tampil di bawah sub:
    <x-portal.kpi-card label="Capaian" :value="$persen" icon="gauge" color="teal">
        <div class="pc-bar pc-bar--tercapai pc-bar--kartu" style="--w: 80%"><span></span></div>
    </x-portal.kpi-card>

    Atribut HTML biasa ikut diteruskan (id, title, class, data-*):
    <x-portal.kpi-card ... id="borKpiCard" class="pc-kpi--klik" />

    color   : sky | success | danger | teal | warning | violet | rose | muted
    i       : urutan animasi masuk (0, 1, 2, ...)
    count   : kalau diisi, angka dihitung naik oleh utils/count-up.js; value diabaikan
    decimal : jumlah angka di belakang koma untuk count
--}}
@props(['label', 'icon', 'value' => null, 'count' => null, 'decimal' => 0, 'unit' => null, 'sub' => null, 'color' => 'sky', 'i' => 0])

@php $hitung = $count !== null; @endphp

<article {{ $attributes->merge(['class' => "pc-kpi pc-kpi--{$color}", 'style' => "--i: {$i}"]) }}>
    <div class="pc-kpi-icon"><x-icon :name="$icon" width="18" height="18" /></div>
    <div class="pc-kpi-body">
        <div class="pc-kpi-label">{{ $label }}</div>
        <div class="pc-kpi-value" @if ($hitung) data-count-target="{{ $count }}" data-count-decimal="{{ $decimal }}" @endif>
            <span class="count-num">{{ $hitung ? number_format($count, $decimal, ',', '.') : $value }}</span>@if (filled($unit))<span class="pc-kpi-unit">{{ $unit }}</span>@endif
        </div>
        @if (filled($sub))
            <div class="pc-kpi-sub">{{ $sub }}</div>
        @endif
        {{ $slot }}
    </div>
</article>
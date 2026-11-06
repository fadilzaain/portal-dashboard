{{--
    Rombakan dashi v2 - Panel bersama untuk semua halaman portal (tabel, grafik, daftar, info).
    Gaya ada di resources/css/layout/cards.css (.pc-panel*).

    jika mau makai make ini :
    <x-portal.panel title="Daftar Indikator" subtitle="Standar Depkes RI" :meta="$periode" :i="4">
        ... isi panel ...
    </x-portal.panel>

    Pojok kanan header bisa diisi bebas (badge, tombol) lewat slot actions;
    kalau dipakai, meta diabaikan:
    <x-portal.panel title="Tren Kunjungan">
        <x-slot:actions><span class="src-badge src-api">✓ API</span></x-slot:actions>
        ...
    </x-portal.panel>

    Isi panel bebas. Kalau butuh padding (grafik, teks), bungkus dengan
    <div class="pc-panel-body">; kalau tabel full-lebar, taruh langsung.
    Header otomatis hilang kalau title, meta, dan actions semuanya kosong.
    Atribut HTML biasa ikut diteruskan (id, class, data-*).
--}}
@props(['title' => null, 'subtitle' => null, 'meta' => null, 'i' => 0])

<section {{ $attributes->merge(['class' => 'pc-panel', 'style' => "--i: {$i}"]) }}>
    @if (filled($title) || filled($meta) || isset($actions))
        <div class="pc-panel-head">
            <div class="pc-panel-head-text">
                <h2 class="pc-panel-title">{{ $title }}</h2>
                @if (filled($subtitle))
                    <p class="pc-panel-sub">{{ $subtitle }}</p>
                @endif
            </div>
            @if (isset($actions))
                {{ $actions }}
            @elseif (filled($meta))
                <span class="pc-panel-meta">{{ $meta }}</span>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
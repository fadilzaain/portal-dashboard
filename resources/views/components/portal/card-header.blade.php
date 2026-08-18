{{--
    Header standar buat 5 app-card di dashboard (icon + judul + subjudul + badge periode).
    Dipakai bareng oleh card-pelayanan, card-keuangan, card-sdm, card-mutu, card-bpjs
    biar markup header gak dicopas manual di 5 file.

    Pemakaian:
        <x-portal.card-header
            theme="teal"
            icon="heart"
            title="Pelayanan Pasien"
            subtitle="Ringkasan Pelayanan Pasien"
            :badge="$bulanLabel . ' ' . $tahun"
        />

    $theme harus salah satu dari: teal | blue | purple | rose | amber
    (harus nyambung sama class .icon-{theme} & .month-{theme} yang ada di dashboard.css)
--}}
@props([
    'theme',
    'icon',
    'title',
    'subtitle',
    'badge',
])

<div class="card-header-row">
    <div class="card-header-left">
        <div class="app-icon icon-{{ $theme }}">
            <x-icon :name="$icon" width="20" height="20" stroke-width="1.8" />
        </div>
        <div class="card-title-wrap">
            <div class="app-name">{{ $title }}</div>
            <div class="app-sub">{{ $subtitle }}</div>
        </div>
    </div>
    <span class="card-month-badge month-{{ $theme }}">{{ $badge }}</span>
</div>
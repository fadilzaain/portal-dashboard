@props([
    'jabatan',
    'kualifikasi' => '-',
    'pns' => 0,
    'pppk' => 0,
    'pppkPw' => 0,
    'nonAsn' => 0,
    'jumlah' => 0,
    'kebutuhan' => 0,
    'keterangan' => 'CUKUP',
    'badgeClass' => 'rk-badge-green',
])
@php
    // Komposisi kepegawaian buat mini-bar tersegmentasi (proporsi tiap jenis, bukan angka mentah di kolom terpisah)
    $komposisi = [
        ['label' => 'PNS',      'nilai' => $pns,    'tone' => 'blue'],
        ['label' => 'PPPK',     'nilai' => $pppk,   'tone' => 'teal'],
        ['label' => 'PPPK-PW',  'nilai' => $pppkPw, 'tone' => 'amber'],
        ['label' => 'Non ASN',  'nilai' => $nonAsn, 'tone' => 'purple'],
    ];
@endphp
<div class="rk-row" data-search="{{ strtolower($jabatan) }}">
    <div class="rk-row-main">
        <div class="rk-row-name">{{ $jabatan }}</div>
        @if ($kualifikasi !== '-')
        <div class="rk-row-kualifikasi">{{ $kualifikasi }}</div>
        @endif

        <div class="rk-row-komposisi" role="img" aria-label="Komposisi kepegawaian {{ $jabatan }}">
            @foreach ($komposisi as $k)
                @if ($k['nilai'] > 0)
                <span class="rk-komposisi-seg tone-{{ $k['tone'] }}" style="flex-grow:{{ $k['nilai'] }}" title="{{ $k['label'] }}: {{ $k['nilai'] }}"></span>
                @endif
            @endforeach
        </div>
    </div>

    <div class="rk-row-side">
        <div class="rk-row-jumlah">
            <span class="rk-row-jumlah-val">{{ $jumlah }}</span>
            <span class="rk-row-jumlah-sep">/</span>
            <span class="rk-row-jumlah-target">{{ $kebutuhan }}</span>
        </div>
        <span class="rk-badge {{ $badgeClass }}">{{ ucfirst(strtolower($keterangan)) }}</span>
    </div>
</div>
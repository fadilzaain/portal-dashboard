@props(['iku'])

@php
    // Angka ringkasan (default 0 kalau SPM Track tidak terjangkau)
    $ikuTercapai  = $iku['tercapai']  ?? 0;
    $ikuPerhatian = $iku['perhatian'] ?? 0;
    $ikuBelum     = $iku['belum']     ?? 0;
    $ikuKosong    = $iku['kosong']    ?? 0;
    $ikuPersen    = $iku['persen']    ?? null;
    $ikuStatus    = $iku['status']    ?? 'kosong';
    $ikuDaftar    = $iku['daftar']    ?? [];

    // Badge periode, contoh "TW II 2026" (tanda "–" kalau belum ada data sama sekali)
    $ikuBadge = isset($iku['triwulan'], $iku['tahun'])
        ? 'TW ' . \App\Services\IkuService::ROMAWI[$iku['triwulan']] . ' ' . $iku['tahun']
        : '–';

    // Persen dibulatkan untuk angka di tengah cincin; garis cincin dibatasi 0–100 (capaian bisa lebih dari 100%)
    $ikuPct = $ikuPersen === null ? 0 : (int) round(max(0, min(100, $ikuPersen)));

    // Cincin: keliling lingkaran r=40 (2π×40). Garis terisi = persen capaian, sisanya digeser lewat dashoffset
    $keliling = 251.33;
    $offset   = round($keliling * (1 - $ikuPct / 100), 2);

    // Warna angka di tengah cincin mengikuti status (batasnya dari config/iku.php, dihitung IkuService)
    $kelasTeks = ['tercapai' => 'text-success', 'perhatian' => 'text-warning', 'belum' => 'text-danger'][$ikuStatus] ?? '';

    // Daftar per indikator: tampil 4 saja supaya tinggi card tetap seimbang dengan card lain
    $maks   = 4;
    $tampil = array_slice($ikuDaftar, 0, $maks);
    $sisa   = max(0, count($ikuDaftar) - $maks);

    // Baris legenda (baris "Belum ada data" hanya muncul kalau memang ada)
    $legenda = [
        ['kunci' => 'tercapai',  'label' => 'Tercapai',        'nilai' => $ikuTercapai],
        ['kunci' => 'perhatian', 'label' => 'Perlu perhatian', 'nilai' => $ikuPerhatian],
        ['kunci' => 'belum',     'label' => 'Belum tercapai',  'nilai' => $ikuBelum],
    ];
    if ($ikuKosong > 0) {
        $legenda[] = ['kunci' => 'kosong', 'label' => 'Belum ada data', 'nilai' => $ikuKosong];
    }
@endphp

<a href="{{ route('portal.iku') }}" class="app-card theme-green">
    <span class="card-shine"></span>

    <x-portal.card-header
        theme="green"
        icon="gauge"
        title="IKU"
        subtitle="Indikator Kinerja Utama"
        :badge="$ikuBadge"
    />

    {{-- CINCIN CAPAIAN + LEGENDA STATUS --}}
    <div class="ikc-gauge">
        <div class="ikc-ring" role="img" aria-label="Capaian rata-rata IKU {{ $ikuPersen === null ? 'belum ada data' : $ikuPct . ' persen' }}">
            <svg viewBox="0 0 100 100" aria-hidden="true">
                <circle class="ikc-ring-track" cx="50" cy="50" r="40" />
                {{-- Garis terisi tidak digambar kalau 0%, supaya ujung bulatnya tidak jadi titik kecil --}}
                @if ($ikuPct > 0)
                    <circle class="ikc-ring-fill ikc-{{ $ikuStatus }}" cx="50" cy="50" r="40"
                            style="--ring-c: {{ $keliling }}; stroke-dasharray: {{ $keliling }}; stroke-dashoffset: {{ $offset }}" />
                @endif
            </svg>
            <div class="ikc-ring-center {{ $kelasTeks }}">
                {{-- Belum ada data: tampil "–" (bukan 0%) supaya tidak terbaca sebagai capaian nol --}}
                @if ($ikuPersen === null)
                    <span>–</span>
                @else
                    <span data-count-target="{{ $ikuPct }}"><span class="count-num">{{ $ikuPct }}</span></span><small>%</small>
                @endif
            </div>
        </div>

        <ul class="ikc-legend">
            @foreach ($legenda as $l)
                <li>
                    <span class="ikc-dot ikc-dot--{{ $l['kunci'] }}"></span>
                    <span class="ikc-legend-label">{{ $l['label'] }}</span>
                    <span class="ikc-legend-val" data-count-target="{{ $l['nilai'] }}"><span class="count-num">{{ $l['nilai'] }}</span></span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- DAFTAR PER INDIKATOR --}}
    <div>
        <div class="ikc-section-label">Per Indikator</div>
        <ul class="ikc-list">
            @forelse ($tampil as $d)
                <li class="ikc-item">
                    <span class="ikc-dot ikc-dot--{{ $d['status'] }}"></span>
                    <span class="ikc-item-nama" title="{{ $d['nama'] }}">{{ $d['nama'] }}</span>
                    <span class="ikc-item-val">{{ $d['capaian_fmt'] }}</span>
                </li>
            @empty
                <li class="ikc-item">Belum ada indikator</li>
            @endforelse
        </ul>
        @if ($sisa > 0)
            <div class="ikc-more">+{{ $sisa }} indikator lainnya</div>
        @endif
    </div>

    <x-portal.card-footer />
</a>
{{--
    Navbar generik buat semua halaman modul portal (Pelayanan Pasien, SDM, dst).
    Bagian kiri (tombol home + breadcrumb) selalu sama di semua halaman.
    Bagian kanan bebas diisi lewat slot (filter pill, badge live, date badge, dll)
    biar tiap halaman gak perlu bikin ulang markup navbar-nya sendiri-sendiri.

    Cara pakai:
    <x-portal.navbar title="Portal SDM">
        ... konten kanan (opsional, bisa dikosongin) ...
    </x-portal.navbar>
--}}
@props(['title'])

<nav class="pp-navbar">
  <div class="pp-navbar-inner">

    {{-- Kiri: tombol home + breadcrumb --}}
    <div class="pp-navbar-left">
      <a href="{{ route('dashboard') }}" class="pp-nav-back" title="Kembali ke Dashboard">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="pp-breadcrumb">
        <a href="{{ route('dashboard') }}" class="pp-breadcrumb-link">Dashboard</a>
        <span class="pp-breadcrumb-sep">
          <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
          </svg>
        </span>
        <span class="pp-breadcrumb-current">{{ $title }}</span>
      </div>
    </div>

    {{-- Kanan: slot bebas per-halaman, gak dirender kalau kosong --}}
    @if($slot->isNotEmpty())
      <div class="pp-navbar-right">
        {{ $slot }}
      </div>
    @endif

  </div>
</nav>
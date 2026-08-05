@extends('layouts.app')

@section('title', 'Dash-i')
@section('page_title', 'DASH-i')
@section('page_subtitle', 'Pilih Dashboard')

@push('styles')
@vite('resources/css/portal/dashboard.css')
@endpush


@section('content')
<div class="fade-up">

    <x-portal.greeting-card
        :bulan="$bulan ?? now()->month"
        :tahun="$tahun ?? now()->year"
        :bulanLabel="$bulanLabel ?? 'Mei'"
    />

    <div class="section-header">
        <span class="section-title">Dashboards Tersedia</span>
        <span class="section-count">5 aplikasi</span>
    </div>

    <div class="app-grid">
        <x-portal.card-pelayanan :pelayanan="$pelayanan" :bulanLabel="$bulanLabelPelayanan" :tahun="$tahunPelayanan" />
        <x-portal.card-keuangan  :keuangan="$keuangan"   :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
        <x-portal.card-sdm       :sdm="$sdm"             :tahun="$tahun ?? now()->year" />
        <x-portal.card-mutu      :mutu="$mutu"            :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
        <x-portal.card-bpjs      :bpjs="$bpjs"            :bulanLabel="$bulanLabel ?? 'Mei'" :tahun="$tahun ?? now()->year" />
    </div>

    <p class="text-xs text-center mt-8" style="color:#334155">
        Dashboard Integrasi
        <span style="color:#3b82f6;font-weight:600">RSUD JOMBANG</span>
        &nbsp;·&nbsp;
        Support by <span style="color:#10b981;font-weight:600">IT WORKS RSUD JOMBANG</span>
    </p>

</div>
@endsection

@push('scripts')
<script>
    function updateGreeting() {
        const now = new Date();
        const h   = now.getHours();
        const salam = h < 11 ? 'Selamat Pagi'
                    : h < 15 ? 'Selamat Siang'
                    : h < 18 ? 'Selamat Sore'
                    :          'Selamat Malam';

        document.getElementById('greeting-time').textContent = salam;
        document.getElementById('gs-date').textContent =
            now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        document.getElementById('gs-time').textContent =
            now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    updateGreeting();
    setInterval(updateGreeting, 30_000);

    function applyFilter() {
        const bulan = document.getElementById('filter-bulan').value;
        const tahun = document.getElementById('filter-tahun').value;
        window.location.href = '{{ route("dashboard") }}?bulan=' + bulan + '&tahun=' + tahun;
    }
</script>
@endpush
@props(['u', 'i', 'ketBadge'])
@php
    $rkRadius = 26;
    $rkRing   = 2 * M_PI * $rkRadius;
    $rkOffset = $rkRing - ($u['pct'] / 100 * $rkRing);
    $jumlahJabatan = count($u['detail']);

    // Urutin baris jabatan: KURANG paling atas (paling perlu perhatian), lalu LEBIH, baru CUKUP
    $ketPriority  = ['KURANG' => 0, 'LEBIH' => 1, 'CUKUP' => 2];
    $sortedDetail = collect($u['detail'])->sortBy(
        fn($d) => sprintf('%d-%s', $ketPriority[$d['keterangan']] ?? 3, $d['jabatan'])
    )->values();
@endphp
<div class="rk-card status-{{ $u['status'] }}" data-rk="{{ $i }}" data-status="{{ $u['status'] }}" data-unit="{{ strtolower($u['unit']) }}" id="unit-{{ $u['slug'] }}">
    <button type="button" class="rk-card-head" onclick="rkToggle({{ $i }})" aria-expanded="false">
        <div class="rk-ring-wrap">
            <svg class="rk-ring" viewBox="0 0 64 64">
                <circle class="rk-ring-track" cx="32" cy="32" r="{{ $rkRadius }}"></circle>
                <circle class="rk-ring-fill" cx="32" cy="32" r="{{ $rkRadius }}"
                    style="stroke-dasharray:{{ $rkRing }};stroke-dashoffset:{{ $rkRing }}"
                    data-target-offset="{{ $rkOffset }}"></circle>
            </svg>
            <svg class="rk-ring-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
            </svg>
        </div>
        <div class="rk-card-body">
            <div class="rk-card-top">
                <span class="rk-card-name">{{ $u['unit'] }}</span>
                <span class="rk-status-pill">{{ ucfirst($u['status']) }}</span>
            </div>
            <div class="rk-card-pct">{{ $u['pct'] }}<span>%</span></div>
            <div class="rk-card-sub">{{ number_format($u['tersedia']) }} / {{ number_format($u['kebutuhan']) }} orang &middot; {{ $jumlahJabatan }} jabatan</div>
            <div class="mini-bar">
                <div class="mini-fill tone-accent" style="width:{{ min($u['pct'], 100) }}%"></div>
            </div>
        </div>
        @if ($u['kurangCount'] > 0)
        <span class="rk-kurang-chip">{{ $u['kurangCount'] }} kurang</span>
        @endif
        <svg class="rk-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <div class="rk-expand">
        <div class="rk-expand-inner">
            <div class="rk-detail-toolbar">
                <div class="rk-search-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="rk-search" placeholder="Cari jabatan..." oninput="rkFilter({{ $i }}, this.value)">
                </div>
            </div>

            <div class="rk-detail-wrap" id="rk-detail-{{ $i }}">
                <div class="rk-row-list">
                    @foreach ($sortedDetail as $d)
                    <x-portal.rk-jabatan-row
                        :jabatan="$d['jabatan']"
                        :kualifikasi="$d['kualifikasi']"
                        :pns="$d['pns']"
                        :pppk="$d['pppk']"
                        :pppk-pw="$d['pppk_pw']"
                        :non-asn="$d['non_asn']"
                        :jumlah="$d['jumlah']"
                        :kebutuhan="$d['kebutuhan']"
                        :keterangan="$d['keterangan']"
                        :badge-class="$ketBadge[$d['keterangan']] ?? 'rk-badge-green'"
                    />
                    @endforeach
                </div>
                <div class="rk-empty" id="rk-empty-{{ $i }}" style="display:none">Tidak ada jabatan yang cocok</div>
            </div>
        </div>
    </div>
</div>
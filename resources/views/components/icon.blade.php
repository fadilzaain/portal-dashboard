{{--
    Komponen icon terpusat — SEMUA icon ada di file ini (1 sumber kebenaran).
    Pemakaian:
        <x-icon name="home" class="nav-icon" />
        <x-icon name="sun" width="16" height="16" class="icon-sun" />
        <x-icon name="check" stroke="#22c55e" stroke-width="2.5" width="14" height="14" />

    Nambah icon baru: tinggal tambah 1 @case baru di @switch bawah, gak perlu bikin file lain.
--}}
@props([
    'name',
    'width' => 20,
    'height' => 20,
    'stroke' => 'currentColor',
    'strokeWidth' => 2,
])

<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'viewBox' => '0 0 24 24',
        'width' => $width,
        'height' => $height,
        'fill' => 'none',
        'stroke' => $stroke,
        'stroke-width' => $strokeWidth,
    ]) }}
>
    @switch($name)

        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            @break

        @case('heart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            @break

        @case('currency-dollar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @break

        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            @break

        @case('document-text')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            @break

        @case('shield-check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            @break

        @case('arrow-right-on-rectangle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            @break

        @case('bars-3')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            @break

        @case('sun')
            <circle cx="12" cy="12" r="5"/>
            <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            @break

        @case('moon')
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            @break

        @case('bell')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            @break

        @case('exclamation-circle')
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4m0 4h.01"/>
            @break

        @case('check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            @break

        @case('x-mark')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            @break

        @case('chevron-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            @break

        @case('user')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            @break

        @case('chevron-left')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            @break

        @case('calendar')
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
            @break

        @case('bed')
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            @break

        @case('gauge')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13a9 9 0 1118 0M3 13a2 2 0 002 2h14a2 2 0 002-2M3 13l5-7m9 7l-5-7"/>
            @break

        @case('clock')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @break

        @case('plus')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            @break

        @case('refresh')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            @break

        @case('arrow-down-tray')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            @break

        @case('team')
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            @break

        @case('person')
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            @break

        @case('monitor')
            <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>
            @break

        @case('clock-simple')
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            @break

        @case('academic-cap')
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            @break

        @case('file-text')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            @break

        @case('shield-outline')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            @break

        @case('activity')
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            @break

        @case('user-minus')
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <line x1="23" y1="11" x2="17" y2="11"/>
            @break

        @case('alert-triangle')
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            @break

        @case('trend-arc')
            <path d="M10 50 Q30 10 50 50" stroke="currentColor" stroke-width="2" fill="none"/>
            <circle cx="30" cy="25" r="8" fill="currentColor"/>
            @break

        @case('bag')
            <rect x="10" y="20" width="40" height="25" rx="4" stroke="currentColor" stroke-width="2" fill="none"/>
            <path d="M20 20v-5a10 10 0 0 1 20 0v5" stroke="currentColor" stroke-width="2" fill="none"/>
            @break

        @case('trend-line')
            <path d="M15 45 L25 30 L35 38 L50 15" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            @break

        @case('progress-ring')
            <circle cx="30" cy="30" r="20" stroke="currentColor" stroke-width="2" fill="none"/>
            <path d="M30 10 A20 20 0 0 1 50 30" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
            @break

        @case('check-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @break

        @case('x-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @break

        @case('trend-up')
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            @break

        @case('funnel')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            @break

        @case('search')
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            @break

        @case('clipboard')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            @break

        @case('envelope')
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
            @break

        @case('lock')
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            @break

        @case('eye')
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
            @break

        @default
            {{-- Fallback biar ketauan kalau ada nama icon yang salah ketik --}}
            <circle cx="12" cy="12" r="10"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>

    @endswitch
</svg>

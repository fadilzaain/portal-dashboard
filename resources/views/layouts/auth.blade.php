<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — RSUD Jombang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    {{-- Sora: font UI utama. Instrument Serif: khusus wordmark di panel kiri, biar berasa premium/editorial --}}
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Tema two-tone split (biru kiri, putih kanan) + animasi: resources/css/portal/auth.css --}}
    @vite(['resources/css/portal/auth.css'])
    @stack('styles')
</head>
<body class="h-full">
    <div class="auth-split">

        {{-- ── Panel kiri: branding, gradasi biru premium ──
             Mobile  : compact, cuma logo bar (biar form kanan langsung keliatan, gak scroll)
             Desktop : wordmark + satu baris makna, simpel & estetik --}}
        <div class="auth-left auth-left--compact lg:auth-left--full">
            <div class="auth-blob auth-blob-1" aria-hidden="true"></div>
            <div class="auth-blob auth-blob-2" aria-hidden="true"></div>
            <div class="auth-blob auth-blob-3" aria-hidden="true"></div>

            <div class="auth-left-content h-full flex flex-col lg:justify-center max-w-md mx-auto lg:mx-0">

                {{-- Logo + nama produk (selalu tampil, mobile & desktop) --}}
                <div class="flex items-center gap-3 auth-fade-up" style="animation-delay: .05s">
                    <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="w-5 h-5 lg:w-6 lg:h-6 object-contain" />
                    </div>
                    <span class="text-white font-medium tracking-tight text-sm lg:text-base">Portal Dashboard</span>
                </div>

                {{-- Wordmark + satu baris makna — cuma tampil di desktop --}}
                <div class="hidden lg:block mt-12">
                    <h2 class="auth-wordmark text-white text-6xl leading-none mb-5 auth-fade-up" style="animation-delay: .14s">
                        DASH-i
                    </h2>
                    <p class="text-sm text-white/60 max-w-[260px] leading-relaxed auth-fade-up" style="animation-delay: .22s">
                        Satu portal untuk seluruh data pelayanan, IGD, keuangan, hingga SDM RSUD Jombang.
                    </p>
                </div>

                {{-- Credit line — mendorong ke bawah lewat flex, cuma desktop --}}
                <p class="auth-left-footer hidden lg:block mt-auto pt-6 auth-fade-up" style="animation-delay: .3s">
                    &copy; {{ date('Y') }} RSUD Jombang
                </p>
            </div>
        </div>

        {{-- ── Panel kanan: putih lembut, tempat form ── --}}
        <div class="auth-right">
            @yield('content')
        </div>

    </div>

    @stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') RSUD Jombang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/portal/auth.css'])
    @stack('styles')
</head>
<body class="h-full">
    <div class="auth-split">

        {{-- ── Panel kiri ── --}}
        <div class="auth-left">
            <div class="auth-blob auth-blob-1" aria-hidden="true"></div>
            <div class="auth-blob auth-blob-2" aria-hidden="true"></div>
            <div class="auth-blob auth-blob-3" aria-hidden="true"></div>

            <div class="auth-left-content h-full flex flex-col justify-center max-w-md mx-auto lg:mx-0">

                {{-- Logo di atas tulisan --}}
                <div class="w-11 h-11 lg:w-14 lg:h-14 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md flex items-center justify-center flex-shrink-0 auth-logo-glow auth-fade-up" style="animation-delay: .05s">
                    <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="w-6 h-6 lg:w-7 lg:h-7 object-contain" />
                </div>

                <div class="auth-left-divider mt-6 lg:mt-8 auth-fade-up" style="animation-delay: .1s"></div>

                <h1 class="mt-6 lg:mt-8 text-lg lg:text-2xl font-medium tracking-tight leading-snug auth-fade-up" style="animation-delay: .14s">
                    <span class="text-white/70">Website Dashboard Integrasi</span><br>
                    <span class="text-white font-semibold">RSUD Jombang</span>
                </h1>
            </div>
        </div>

        {{-- ── Panel kanan tempat form ── --}}
        <div class="auth-right">
            @yield('content')
        </div>

    </div>

    @stack('scripts')
</body>
</html>
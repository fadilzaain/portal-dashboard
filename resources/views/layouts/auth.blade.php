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
                <div class="auth-logo-wrap auth-fade-up" style="animation-delay: .05s">
                    <div class="auth-logo-ring" aria-hidden="true"></div>
                    <div class="auth-logo-badge">
                        <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="auth-logo-img" />
                    </div>
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
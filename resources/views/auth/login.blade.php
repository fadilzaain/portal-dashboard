@extends('layouts.auth')

@section('title', 'Login — Portal Dashboard')

@section('content')
<div class="w-full max-w-sm">

    {{-- Judul --}}
    <div class="mb-5 auth-fade-up" style="animation-delay: .1s">
        <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Selamat Datang</h1>
        <p class="text-sm text-slate-500 mt-1">Masuk Dengan Akun Yang Sudah Terdaftar</p>
    </div>

    {{-- Card form --}}
    <div class="auth-card p-6 auth-fade-up" style="animation-delay: .18s">
        <form method="POST" action="{{ route('login.post') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-3.5">
                <label for="email" class="block text-sm text-slate-600 mb-1.5">Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="envelope" class="w-4 h-4" />
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        autocomplete="username" placeholder="kamu@email.com"
                        class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl outline-none transition text-slate-900 placeholder:text-slate-400
                               bg-slate-50 border
                               {{ $errors->has('email') ? 'border-red-300 bg-red-50' : 'border-slate-200' }}
                               focus:border-teal-500 focus:ring-2 focus:ring-teal-100 focus:bg-white" />
                </div>
                @error('email') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-2">
                <label for="password" class="block text-sm text-slate-600 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="lock" class="w-4 h-4" />
                    </span>
                    <input type="password" id="password" name="password" required
                        autocomplete="current-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                        class="w-full pl-9 pr-10 py-2.5 text-sm rounded-xl outline-none transition text-slate-900 placeholder:text-slate-400
                               bg-slate-50 border
                               {{ $errors->has('password') ? 'border-red-300 bg-red-50' : 'border-slate-200' }}
                               focus:border-teal-500 focus:ring-2 focus:ring-teal-100 focus:bg-white" />
                    <button type="button" onclick="togglePw()" aria-label="Tampilkan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <x-icon name="eye" id="eye-icon" class="w-4 h-4" />
                    </button>
                </div>
                @error('password') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="text-right mb-4">
                <a href="#" class="text-xs text-teal-600 hover:text-teal-700 hover:underline transition">Lupa password?</a>
            </div>

            {{-- Ingat saya --}}
            <div class="flex items-center mb-5">
                <input type="checkbox" id="remember" name="remember"
                    class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 focus:ring-offset-0" />
                <label for="remember" class="ml-2 text-sm text-slate-600">Ingat saya</label>
            </div>

            <button type="submit"
                class="w-full text-white text-sm font-medium py-2.5 rounded-xl transition auth-shine
                       bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400
                       shadow-lg shadow-teal-500/25 hover:shadow-teal-400/35 hover:-translate-y-0.5">
                Masuk
            </button>
        </form>
    </div>

    {{-- Catatan sistem private — pengganti link registrasi --}}
    <div class="flex items-center justify-center gap-1.5 mt-5 text-xs text-slate-400 auth-fade-up" style="animation-delay: .28s">
        <x-icon name="shield-check" class="w-3.5 h-3.5 text-slate-400" />
        <span>Akses akun dikelola oleh SIMRS RSUD Jombang</span>
    </div>

</div>

<script>
function togglePw() {
    const inp = document.getElementById('password');
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    document.getElementById('eye-icon').innerHTML = isText
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
}
</script>
@endsection
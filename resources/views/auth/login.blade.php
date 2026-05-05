@extends('layouts.auth')

@section('title', 'Login — Portal Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
  <div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-7">
      <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-4">
        <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo" class="w-8 h-8 object-contain" />
      </div>
      <h1 class="text-xl font-semibold text-gray-900">Selamat Datang</h1>
      <p class="text-sm text-gray-500 mt-1">Dashboard integrasi RSUD Jombang</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-7">
      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1.5">Email</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" required
              placeholder="kamu@email.com"
              class="w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg outline-none transition
                     {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}
                     focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" />
          </div>
          @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Password --}}
        <div class="mb-1">
          <label class="block text-sm text-gray-600 mb-1.5">Password</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
            </span>
            <input type="password" id="password" name="password" required
              placeholder="••••••••"
              class="w-full pl-9 pr-10 py-2.5 text-sm border rounded-lg outline-none transition
                     {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}
                     focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" />
            <button type="button" onclick="togglePw()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="text-right mb-5">
          <a href="#" class="text-xs text-indigo-500 hover:underline">Lupa password?</a>
        </div>

        {{-- Remember --}}
        <div class="flex items-center mb-5">
          <input type="checkbox" id="remember" name="remember"
            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
          <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
        </div>

        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
          Masuk
        </button>

        <div class="flex items-center gap-3 my-5">
          <div class="flex-1 h-px bg-gray-200"></div>
          <span class="text-xs text-gray-400">atau</span>
          <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <p class="text-center text-sm text-gray-500">
          Belum punya akun?
          <a href="/register" class="text-indigo-600 hover:underline font-medium">Daftar sekarang</a>
        </p>

      </form>
    </div>
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
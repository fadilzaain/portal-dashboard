@extends('layouts.app')

@section('title', 'Login — Portal Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Selamat datang</h1>
            <p class="text-sm text-gray-500 mt-1">Dashboard integrasi RSUD Jombang</p>
        </div>

        {{-- Card form --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           required
                           class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-colors
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' }}
                                  focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                           placeholder="kamu@email.com" />
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="current-password"
                           required
                           class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-colors
                                  {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' }}
                                  focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                           placeholder="••••••••" />
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Link ke register --}}
                    <p class="text-center text-sm text-gray-500 mt-4">
                        Belum punya akun?
                        <a href="/register" class="text-indigo-600 hover:underline font-medium">Daftar sekarang</a>
                    </p>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center mb-5">
                    <input type="checkbox"
                           id="remember"
                           name="remember"
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                {{-- Tombol login --}}
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                               text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                    Masuk
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
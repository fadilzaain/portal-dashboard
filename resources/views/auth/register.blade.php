@extends('layouts.auth')

@section('title', 'Register — Portal Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        {{-- Header --}}
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-rsud-jombang.png') }}" 
                alt="Logo RSUD Jombang" 
                class="w-16 h-16 mx-auto mb-4 object-contain" />
            <h1 class="text-2xl font-semibold text-gray-900">Selamat datang</h1>
            <p class="text-sm text-gray-500 mt-1">Dashboard integrasi RSUD Jombang</p>
        </div>

        {{-- Card form --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                {{-- Nama --}}
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nama lengkap
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-colors
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' }}
                                  focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                           placeholder="Nama kamu" />
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
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
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-colors
                                  {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' }}
                                  focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                           placeholder="Minimal 8 karakter" />
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="mb-5">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Konfirmasi password
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none transition-colors
                                  border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                           placeholder="Ulangi password" />
                </div>

                {{-- Tombol register --}}
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                               text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                    Daftar Sekarang
                </button>
            </form>
        </div>

        {{-- Link ke login --}}
        <p class="text-center text-sm text-gray-500 mt-4">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Masuk di sini</a>
        </p>

    </div>
</div>
@endsection
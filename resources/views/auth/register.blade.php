@extends('layouts.app')

@section('title', 'Register — Portal Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Buat akun baru</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar ke Portal Dashboard</p>
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
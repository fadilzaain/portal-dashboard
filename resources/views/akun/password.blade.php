@extends('layouts.app')

@section('title', 'Ganti Password')

@push('styles')
@vite('resources/css/portal/pengguna.css')
@endpush

@section('content')
<div class="pgn-wrap pgn-wrap--sempit fade-up">

    <div class="pgn-kepala">
        <div>
            <h1>Ganti Password</h1>
            <p class="pgn-redup">Pakai password yang kuat dan jangan dibagikan ke siapa pun.</p>
        </div>
    </div>

    {{-- Akun yang baru direset admin wajib ganti password dulu sebelum membuka menu lain --}}
    @if (auth()->user()->wajib_ganti_password)
        <div class="pgn-pesan pgn-pesan--warn">
            Kamu sedang memakai password sementara dari admin. Ganti password dulu supaya bisa membuka menu lain.
        </div>
    @endif

    @if (session('status'))
        <div class="pgn-pesan pgn-pesan--ok">{{ session('status') }}</div>
    @endif

    <div class="pgn-panel">
        <form method="POST" action="{{ route('akun.password.simpan') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="pgn-field">
                <label for="password_lama">Password lama</label>
                <input type="password" id="password_lama" name="password_lama" required autocomplete="current-password"
                       class="pgn-input {{ $errors->has('password_lama') ? 'pgn-input--salah' : '' }}">
                @error('password_lama') <p class="pgn-galat">{{ $message }}</p> @enderror
            </div>

            <div class="pgn-field">
                <label for="password">Password baru</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       class="pgn-input {{ $errors->has('password') ? 'pgn-input--salah' : '' }}">
                @error('password') <p class="pgn-galat">{{ $message }}</p> @enderror
                <p class="pgn-petunjuk">Minimal 8 karakter dan harus berbeda dari password lama.</p>
            </div>

            <div class="pgn-field">
                <label for="password_confirmation">Ulangi password baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="pgn-input">
            </div>

            <button type="submit" class="pgn-btn pgn-btn--utama">Simpan password</button>
        </form>
    </div>

</div>
@endsection
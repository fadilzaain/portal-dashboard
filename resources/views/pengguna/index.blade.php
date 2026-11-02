@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@push('styles')
@vite('resources/css/portal/pengguna.css')
@endpush

@section('content')
    @use('App\Models\User')

    @php
        $saya = auth()->user();

        // Warna badge status, dan tombol yang tampil untuk tiap status (satu daftar, digambar lewat perulangan)
        $badge = ['menunggu' => 'pgn-badge--warn', 'aktif' => 'pgn-badge--ok', 'ditolak' => 'pgn-badge--bad', 'nonaktif' => ''];
        $tombol = [
            'menunggu' => [['setujui', 'Setujui', 'pgn-btn pgn-btn--utama'], ['tolak', 'Tolak', 'pgn-btn pgn-btn--garis pgn-btn--bahaya']],
            'aktif'    => [['simpan', 'Simpan', 'pgn-btn pgn-btn--utama'], ['reset', 'Reset password', 'pgn-btn pgn-btn--garis'], ['nonaktifkan', 'Nonaktifkan', 'pgn-btn pgn-btn--garis pgn-btn--bahaya']],
            'ditolak'  => [['setujui', 'Setujui', 'pgn-btn pgn-btn--utama']],
            'nonaktif' => [['setujui', 'Aktifkan', 'pgn-btn pgn-btn--utama']],
        ];
    @endphp

    <div class="pgn-wrap fade-up">

        {{-- ===== Kepala halaman + filter status ===== --}}
        <div class="pgn-kepala">
            <div>
                <h1>Kelola Pengguna</h1>
                <p class="pgn-redup">Setujui pendaftar baru, atur role, reset password, atau nonaktifkan akun.</p>
            </div>

            <form method="GET" action="{{ route('pengguna') }}" class="pgn-filter">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status" class="pgn-input" onchange="this.form.submit()">
                    @foreach ($opsiStatus as $kode => $label)
                        <option value="{{ $kode }}" @selected($status === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- ===== Pesan ===== --}}
        @if (session('status'))
            <div class="pgn-pesan pgn-pesan--ok">{{ session('status') }}</div>
        @endif

        {{-- Password sementara hasil reset: hanya tampil sekali (flash), hilang kalau halaman dimuat ulang --}}
        @if (session('sandi_sementara'))
            @php $sandi = session('sandi_sementara'); @endphp
            <div class="pgn-pesan pgn-pesan--warn pgn-sandi">
                <strong>Password {{ $sandi['nama'] }} ({{ $sandi['email'] }}) sudah direset.</strong>
                <code>{{ $sandi['password'] }}</code>
                <button type="button" class="pgn-btn pgn-btn--garis" data-salin="{{ $sandi['password'] }}">Salin</button>
                <small>Catat sekarang dan berikan ke yang bersangkutan. Password ini hanya tampil sekali. Saat login, mereka diminta menggantinya.</small>
            </div>
        @endif

        @if ($errors->any())
            <div class="pgn-pesan pgn-pesan--bad">{{ $errors->first() }}</div>
        @endif

        {{-- ===== Daftar akun: satu form per akun ===== --}}
        <div class="pgn-panel">
            <p class="pgn-redup pgn-petunjuk-atas">
                Semua role bisa membuka semua dashboard. Yang membedakan hanya Admin: cuma Admin yang bisa membuka halaman ini.
            </p>

            <div class="pgn-daftar">
                @forelse ($daftarPengguna as $p)
                    @php $dirinya = $p->is($saya); @endphp

                    <form method="POST" action="{{ route('pengguna.perbarui', $p) }}" class="pgn-baris" style="--i: {{ $loop->index }}">
                        @csrf
                        @method('PATCH')

                        <div class="pgn-info">
                            <span class="pgn-avatar">{{ mb_strtoupper(mb_substr($p->name ?: $p->email, 0, 2)) }}</span>
                            <div>
                                <strong>{{ $p->name ?: $p->email }}</strong>
                                <small>{{ $p->email }} · daftar {{ $p->created_at?->format('d/m/Y') ?? '-' }}</small>
                            </div>
                        </div>

                        <span class="pgn-badge {{ $badge[$p->status] ?? '' }}">{{ User::LABEL_STATUS[$p->status] ?? $p->status }}</span>

                        <select name="role" class="pgn-input" aria-label="Role" @disabled($dirinya)>
                            @foreach (User::LABEL_ROLE as $kode => $label)
                                <option value="{{ $kode }}" @selected($p->role === $kode)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="pgn-aksi">
                            @if ($dirinya)
                                <small class="pgn-redup">Ini akun kamu</small>
                            @else
                                @foreach ($tombol[$p->status] ?? [] as [$aksi, $teks, $kelas])
                                    <button type="submit" name="aksi" value="{{ $aksi }}" class="{{ $kelas }}"
                                            @if ($aksi === 'reset') onclick="return confirm('Reset password akun ini? Password lamanya langsung tidak berlaku.')" @endif
                                            @if ($aksi === 'nonaktifkan') onclick="return confirm('Nonaktifkan akun ini? Akun langsung tidak bisa dipakai.')" @endif>{{ $teks }}</button>
                                @endforeach
                            @endif
                        </div>
                    </form>
                @empty
                    <p class="pgn-redup">Tidak ada akun dengan status ini.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    /* Tombol "Salin" untuk password sementara hasil reset */
    document.querySelectorAll('[data-salin]').forEach(tombol => {
        tombol.addEventListener('click', async () => {
            const teks = tombol.dataset.salin;
            try {
                await navigator.clipboard.writeText(teks);
            } catch (e) {
                /* Cadangan kalau browser menolak akses clipboard (misalnya bukan HTTPS) */
                const sementara = document.createElement('textarea');
                sementara.value = teks;
                document.body.appendChild(sementara);
                sementara.select();
                document.execCommand('copy');
                sementara.remove();
            }
            tombol.textContent = 'Tersalin';
            setTimeout(() => { tombol.textContent = 'Salin'; }, 1800);
        });
    });
</script>
@endpush
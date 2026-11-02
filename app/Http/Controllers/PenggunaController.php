<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    // Tombol di form => [status baru (null = status tetap), kata untuk pesan hasil]
    private const AKSI = [
        'setujui' => [User::STATUS_AKTIF, 'disetujui dan sudah aktif'],
        'tolak' => [User::STATUS_DITOLAK, 'ditolak'],
        'nonaktifkan' => [User::STATUS_NONAKTIF, 'dinonaktifkan'],
        'simpan' => [null, 'diperbarui'],
        'reset' => [null, 'direset'],   // ditangani khusus di resetPassword(), bukan lewat alur status
    ];

    // Daftar akun. Filter lewat ?status= (kalau tidak diisi: tampilkan yang menunggu persetujuan, atau semua kalau tidak ada)
    public function index(Request $request)
    {
        // Jumlah akun per status, ditampilkan di pilihan filter
        $jumlah = User::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status')->all();

        $status = (string) $request->query('status', ($jumlah[User::STATUS_MENUNGGU] ?? 0) > 0 ? User::STATUS_MENUNGGU : 'semua');
        if ($status !== 'semua' && ! isset(User::LABEL_STATUS[$status])) {
            $status = 'semua';
        }

        $opsiStatus = ['semua' => 'Semua ('.array_sum($jumlah).')'];
        foreach (User::LABEL_STATUS as $kode => $label) {
            $opsiStatus[$kode] = $label.' ('.($jumlah[$kode] ?? 0).')';
        }

        return view('pengguna.index', [
            'status' => $status,
            'opsiStatus' => $opsiStatus,
            'daftarPengguna' => User::when($status !== 'semua', fn ($q) => $q->where('status', $status))
                ->orderByDesc('created_at')
                ->orderBy('id')
                ->get(),
        ]);
    }

    // Setujui, tolak, nonaktifkan, reset password, atau simpan perubahan role satu akun
    public function perbarui(Request $request, User $pengguna): RedirectResponse
    {
        // Admin tidak boleh mengubah akunnya sendiri, supaya tidak terkunci dari sistem
        if ($pengguna->is($request->user())) {
            return back()->withErrors(['aksi' => 'Akun kamu sendiri tidak bisa diubah dari halaman ini.']);
        }

        $data = $request->validate([
            'aksi' => ['required', Rule::in(array_keys(self::AKSI))],
            'role' => ['nullable', Rule::in(array_keys(User::LABEL_ROLE))],
        ]);

        // Reset password punya alur sendiri (tidak mengubah status atau role)
        if ($data['aksi'] === 'reset') {
            return $this->resetPassword($pengguna);
        }

        [$statusBaru, $kataKerja] = self::AKSI[$data['aksi']];
        $ubah = $statusBaru ? ['status' => $statusBaru] : [];

        // Role hanya diubah saat menyetujui atau menyimpan (bukan saat menolak/menonaktifkan)
        if (in_array($data['aksi'], ['setujui', 'simpan'], true)) {
            $ubah += ['role' => $data['role'] ?? $pengguna->role];
        }

        $pengguna->update($ubah);

        return back()->with('status', 'Akun '.($pengguna->name ?: $pengguna->email)." {$kataKerja}.");
    }

    // Buat password sementara untuk satu akun. Passwordnya ditampilkan sekali ke admin, lalu yang punya akun wajib menggantinya.
    private function resetPassword(User $pengguna): RedirectResponse
    {
        $sementara = $this->buatPasswordSementara();

        // Password otomatis di-hash oleh model User. Token "ingat saya" diganti supaya cookie lama tidak berlaku.
        $pengguna->forceFill([
            'password' => $sementara,
            'wajib_ganti_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        // Keluarkan sesi lama akun ini (hanya kalau sesi disimpan di database)
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $pengguna->id)->delete();
        }

        return back()->with('sandi_sementara', [
            'nama' => $pengguna->name ?: $pengguna->email,
            'email' => $pengguna->email,
            'password' => $sementara,
        ]);
    }

    // 10 karakter acak yang aman (huruf yang mirip seperti I, l, O, 0, 1 sengaja dibuang supaya mudah dibaca)
    private function buatPasswordSementara(int $panjang = 10): string
    {
        $huruf = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $hasil = '';

        for ($i = 0; $i < $panjang; $i++) {
            $hasil .= $huruf[random_int(0, strlen($huruf) - 1)];
        }

        return $hasil;
    }
}
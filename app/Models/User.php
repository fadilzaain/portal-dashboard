<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Konstanta role supaya tidak salah ketik di banyak tempat.
    // Yang membedakan hak akses hanya admin vs bukan admin; semua dashboard tetap terbuka untuk semua role.
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DIREKTUR = 'direktur';
    public const ROLE_STAFF = 'staff';

    // Tulisan role untuk tampilan (satu sumber, dipakai sidebar dan halaman Kelola Pengguna)
    public const LABEL_ROLE = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_DIREKTUR => 'Direktur',
        self::ROLE_STAFF => 'Staff',
    ];

    // Status akun: pendaftar baru "menunggu" sampai disetujui admin
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_NONAKTIF = 'nonaktif';

    public const LABEL_STATUS = [
        self::STATUS_MENUNGGU => 'Menunggu',
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_DITOLAK => 'Ditolak',
        self::STATUS_NONAKTIF => 'Nonaktif',
    ];

    // Pesan di halaman login untuk akun yang belum boleh masuk
    public const PESAN_STATUS = [
        self::STATUS_MENUNGGU => 'Akun kamu masih menunggu persetujuan admin.',
        self::STATUS_DITOLAK => 'Pendaftaran kamu ditolak. Silakan hubungi admin.',
        self::STATUS_NONAKTIF => 'Akun kamu dinonaktifkan. Silakan hubungi admin.',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'wajib_ganti_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // Password otomatis di-hash saat diisi
            'password' => 'hashed',
            // Penanda password sementara dari admin (wajib diganti saat login)
            'wajib_ganti_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDirektur(): bool
    {
        return $this->role === self::ROLE_DIREKTUR;
    }

    // Hanya akun aktif yang boleh memakai sistem
    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }
}
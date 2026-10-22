<?php

namespace App\Models;

use App\Traits\HasNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $nip
 * @property string|null $whatsapp
 * @property string|null $password
 * @property string|null $role
 * @property string|null $bidang
 * @property string|null $jabatan
 * @property string|null $golongan
 * @property bool $is_admin
 * @property string $status
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, HasNotifications, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'nip',
        'whatsapp',
        'password',
        'role',
        'bidang',
        'jabatan',
        'golongan',
        'is_admin',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    // ============================================================
    // HELPER METHODS (UNTUK ROLE)
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->is_admin === true;
    }

    public function isPengelolaSurat(): bool
    {
        return $this->role === 'pengelola_surat';
    }

    public function isPengelolaAula(): bool
    {
        return $this->role === 'pengelola_aula';
    }

    public function isPengelolaAbsensi(): bool
    {
        return $this->role === 'pengelola_absensi';
    }

    public function isPegawai(): bool
    {
        return $this->status === 'aktif';
    }

    public function canLogin(): bool
    {
        return ! empty($this->username)
            && ! empty($this->password)
            && $this->status === 'aktif';
    }

    // ============================================================
    // RELASI
    // ============================================================

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function absensiDetails()
    {
        return $this->hasMany(AbsensiDetail::class, 'user_id');
    }

    public function absensiSesiDibuat()
    {
        return $this->hasMany(AbsensiSesi::class, 'created_by');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }
}
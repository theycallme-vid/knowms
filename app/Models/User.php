<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Nama tabel di MySQL adalah lowercase 'tusers'
    protected $table = 'tusers';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'npk',
        'tRoles_id',
        'is_active',
    ];

    // Kolom yang TIDAK dikembalikan saat model di-serialize (misal ke JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'password'   => 'hashed',
    ];

    /**
     * Relasi: User (staf) milik satu Role.
     * Parameter kedua 'tRoles_id' adalah nama FK di tabel tusers.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'tRoles_id');
    }

    /**
     * Relasi: User sebagai Data Entry bisa memiliki banyak FAQ yang dibuat.
     */
    public function faqsAsDataEntry()
    {
        return $this->hasMany(Faq::class, 'id_dataentry');
    }

    /**
     * Relasi: User sebagai Validator bisa memiliki banyak FAQ yang divalidasi.
     */
    public function faqsAsValidator()
    {
        return $this->hasMany(Faq::class, 'id_validator');
    }

    /**
     * Helper: mengambil nama role user saat ini.
     * Contoh penggunaan: Auth::user()->getRoleName()
     */
    public function getRoleName(): string
    {
        return $this->role?->nama ?? '';
    }

    /**
     * Helper: mengecek apakah user memiliki role tertentu.
     * Contoh penggunaan: Auth::user()->hasRole('Super Admin')
     */
    public function hasRole(string $roleName): bool
    {
        return $this->getRoleName() === $roleName;
    }

    /**
     * Helper cepat untuk cek role spesifik.
     * Contoh penggunaan di Blade: @if(auth()->user()->isAdmin())
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isValidator(): bool
    {
        return $this->hasRole('Validator');
    }

    public function isDataEntry(): bool
    {
        return $this->hasRole('Data Entry');
    }

    public function isCS(): bool
    {
        return $this->hasRole('CS');
    }
}

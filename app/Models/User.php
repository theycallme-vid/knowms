<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tusers';

    protected $fillable = [
        'superId',
        'nama',
        'email',
        'password',
        'npk',
        'tRoles_id',
        'is_active',
        'tDirektorat_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'password'   => 'hashed',
    ];

    /** Self-referencing: atasan/supervisor */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'superId');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'superId');
    }

    /** Relasi ke Role */
    public function role()
    {
        return $this->belongsTo(Role::class, 'tRoles_id');
    }

    /** Relasi ke Direktorat */
    public function direktorat()
    {
        return $this->belongsTo(Direktorat::class, 'tDirektorat_id');
    }

    /** FAQ yang dibuat sebagai Data Entry */
    public function faqsAsDataEntry()
    {
        return $this->hasMany(Faq::class, 'id_dataentry');
    }

    /** Versi FAQ yang divalidasi */
    public function faqVersionsAsValidator()
    {
        return $this->hasMany(FaqVersion::class, 'id_validator');
    }

    /** Helper: nama role */
    public function getRoleName(): string
    {
        return $this->role?->nama ?? '';
    }

    /** Helper: cek role tertentu */
    public function hasRole(string $roleName): bool
    {
        return $this->getRoleName() === $roleName;
    }

    public function isAdmin(): bool   { return $this->hasRole('Super Admin'); }
    public function isValidator(): bool { return $this->hasRole('Validator'); }
    public function isDataEntry(): bool { return $this->hasRole('Data Entry'); }
}

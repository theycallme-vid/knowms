<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tusers';

    // Nonaktifkan remember_token karena tidak ada pada ERD tUsers
    protected $rememberTokenName = null;

    protected $fillable = [
        'superId',
        'nama',
        'email',
        'password',
        'password_encrypted',
        'npk',
        'tRoles_id',
        'is_active',
        'tDirektorat_id',
    ];

    protected $hidden = [
        'password',
        'password_encrypted',
    ];

    /**
     * Ambil kata sandi terdekripsi untuk ditampilkan kepada Super Admin / Validator berwenang.
     */
    public function getViewablePassword(): string
    {
        if (empty($this->password_encrypted)) {
            return '';
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString((string) $this->password_encrypted);
        } catch (\Throwable $e) {
            return '';
        }
    }

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

    /** Riwayat import file yang dilakukan oleh user ini */
    public function riwayatImports()
    {
        return $this->hasMany(RiwayatImport::class, 'id_user');
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

    public function isAdmin(): bool     { return $this->hasRole('Super Admin'); }
    public function isValidator(): bool { return $this->hasRole('Validator'); }
    public function isDataEntry(): bool { return $this->hasRole('Data Entry'); }

    /**
     * Cek apakah pengguna berada di Direktorat Pusat (Direktorat Akademik).
     * Jika tDirektorat_id kosong (akun lama), diperlakukan sebagai Pusat agar tidak terkunci.
     */
    public function isPusat(): bool
    {
        if (empty($this->tDirektorat_id)) {
            return true;
        }

        if (!$this->relationLoaded('direktorat')) {
            $this->load('direktorat');
        }

        if ($this->direktorat) {
            if ($this->direktorat->getAttributes() && array_key_exists('is_pusat', $this->direktorat->getAttributes())) {
                return (bool) $this->direktorat->is_pusat;
            }
            return strtolower(trim((string) $this->direktorat->nama)) === 'direktorat akademik'
                || (int) $this->direktorat->id === 1;
        }

        return (int) $this->tDirektorat_id === 1;
    }

    public function isValidatorAdmik(): bool { return $this->isValidator() && $this->isPusat(); }
    public function isValidatorUnit(): bool  { return $this->isValidator() && !$this->isPusat(); }
    public function isDataEntryAdmik(): bool { return $this->isDataEntry() && $this->isPusat(); }
    public function isDataEntryUnit(): bool  { return $this->isDataEntry() && !$this->isPusat(); }

    public function getUnitName(): string
    {
        if (!$this->relationLoaded('direktorat') && !empty($this->tDirektorat_id)) {
            $this->load('direktorat');
        }

        return $this->direktorat?->nama ?: 'Direktorat Akademik';
    }

    public function getEffectiveDirektoratId(): int
    {
        return (int) ($this->tDirektorat_id ?: Direktorat::pusatId());
    }
}

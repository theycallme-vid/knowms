<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Direktorat extends Model
{
    protected $table = 'tdirektorat';

    protected $fillable = [
        'nama',
        'is_pusat',
        'is_active',
    ];

    protected $casts = [
        'is_pusat'  => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Relasi: satu direktorat memiliki banyak user */
    public function users()
    {
        return $this->hasMany(User::class, 'tDirektorat_id');
    }

    /** Relasi: satu direktorat memiliki banyak FAQ */
    public function faqs()
    {
        return $this->hasMany(Faq::class, 'tDirektorat_id');
    }

    /** Ambil ID Direktorat Pusat (Direktorat Akademik) dengan fallback ke 1 */
    public static function pusatId(): int
    {
        static $cachedId = null;
        if ($cachedId !== null) {
            return $cachedId;
        }

        try {
            $id = self::where('is_pusat', 1)->value('id')
                ?? self::whereRaw('LOWER(nama) = ?', ['direktorat akademik'])->value('id')
                ?? 1;
            $cachedId = (int) $id;
            return $cachedId;
        } catch (Throwable $e) {
            return 1;
        }
    }
}

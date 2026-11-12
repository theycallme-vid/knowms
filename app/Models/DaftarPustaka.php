<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarPustaka extends Model
{
    protected $table = 'tdaftarpustaka';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'link',
        'upload',
    ];

    /** Versi FAQ yang menggunakan pustaka ini */
    public function faqVersions()
    {
        return $this->hasMany(FaqVersion::class, 'tDaftarPustakas_id');
    }

    /** Apakah pustaka ini merupakan file dokumen yang diunggah (memiliki isi BLOB) */
    public function isUploadedFile(): bool
    {
        return !empty($this->upload);
    }

    /** Apakah pustaka ini bersifat Public (bisa dilihat mahasiswa) */
    public function isPublic(): bool
    {
        return strtolower((string) $this->type) !== 'private';
    }

    /** Label tipe standar (Public / Private) */
    public function getVisibilityLabelAttribute(): string
    {
        return $this->isPublic() ? 'Public' : 'Private';
    }

    /**
     * Pastikan kolom type pada tabel tdaftarpustaka mendukung nilai 'public' & 'private'
     * walaupun database lama belum di-migrate ulang.
     */
    public static function ensurePublicPrivateEnum(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $col = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM tdaftarpustaka LIKE 'type'");
            if ($col && isset($col->Type) && !str_contains(strtolower((string) $col->Type), 'public')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE tdaftarpustaka MODIFY type VARCHAR(20) NOT NULL DEFAULT 'public'");
                \Illuminate\Support\Facades\DB::statement("UPDATE tdaftarpustaka SET type = 'public' WHERE LOWER(type) NOT IN ('public', 'private')");
                \Illuminate\Support\Facades\DB::statement("UPDATE tdaftarpustaka SET type = LOWER(type)");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE tdaftarpustaka MODIFY type ENUM('public', 'private') NOT NULL DEFAULT 'public'");
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel belum ada
        }
    }
}

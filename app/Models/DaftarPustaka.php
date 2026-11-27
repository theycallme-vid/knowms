<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarPustaka extends Model
{
    protected $table = 'tdaftarpustaka';
    public $timestamps = false;

    protected $fillable = [
        'judul',
        'link',
        'upload',
    ];

    /** Versi FAQ yang menggunakan pustaka ini */
    public function faqVersions()
    {
        return $this->hasMany(FaqVersion::class, 'tDaftarPustakas_id');
    }

    /** Judul sumber pustaka yang ditampilkan pada tabel & pilihan */
    public function getJudulTampilAttribute(): string
    {
        if (!empty($this->judul)) {
            return (string) $this->judul;
        }
        return (string) ($this->link ?: ('Sumber Pustaka ' . $this->id));
    }

    /** Apakah pustaka ini merupakan file dokumen yang diunggah (memiliki isi BLOB) */
    public function isUploadedFile(): bool
    {
        return !empty($this->upload);
    }

    /** Normalisasi tautan eksternal: tambahkan https:// bila user tidak mengetikkan skema */
    public static function normalizeLink(?string $raw): ?string
    {
        $val = trim((string) $raw);
        if ($val === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        if (str_contains($val, '.') && !str_contains($val, ' ')) {
            return 'https://' . ltrim($val, '/');
        }
        return $val;
    }

    /** URL siap buka untuk dokumen (BLOB route) maupun tautan eksternal */
    public function getResolvedUrlAttribute(): string
    {
        if (!empty($this->upload)) {
            return route('pustaka.lihat', $this->id);
        }
        $normalized = self::normalizeLink($this->link);
        if ($normalized && filter_var($normalized, FILTER_VALIDATE_URL)) {
            return $normalized;
        }
        return '';
    }

    /** Seluruh sumber pustaka otomatis bersifat Publik (dapat dilihat mahasiswa) */
    public function isPublic(): bool
    {
        return true;
    }

    /** Label tipe standar (Publik) */
    public function getVisibilityLabelAttribute(): string
    {
        return 'Publik';
    }

    /**
     * Pastikan tabel tdaftarpustaka memiliki kolom judul, link VARCHAR(60),
     * dan menghapus kolom type (public/private) jika masih ada di database lama.
     * Di-cache permanen pada file store agar tidak menjalankan 3x SHOW COLUMNS di setiap request.
     */
    public static function ensurePublicPrivateEnum(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            if (\Illuminate\Support\Facades\Cache::store('file')->get('kms_schema_daftarpustaka_v2')) {
                return;
            }

            $judulCol = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM tdaftarpustaka LIKE 'judul'");
            if (!$judulCol) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE tdaftarpustaka ADD COLUMN judul VARCHAR(100) NULL AFTER id");
                \Illuminate\Support\Facades\DB::statement("UPDATE tdaftarpustaka SET judul = link WHERE judul IS NULL OR judul = ''");
            }

            $linkCol = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM tdaftarpustaka LIKE 'link'");
            if ($linkCol && isset($linkCol->Type) && !str_contains(strtolower((string) $linkCol->Type), '60')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE tdaftarpustaka MODIFY link VARCHAR(60) NULL");
            }

            $col = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM tdaftarpustaka LIKE 'type'");
            if ($col) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE tdaftarpustaka DROP COLUMN type");
            }

            \Illuminate\Support\Facades\Cache::store('file')->forever('kms_schema_daftarpustaka_v2', true);
        } catch (\Throwable $e) {
            // Abaikan jika tabel belum ada
        }
    }
}

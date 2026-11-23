<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'tfaqs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_dataentry',
        'id_riwayat_import',
        'pertanyaan',
    ];

    /** Data Entry yang membuat FAQ */
    public function dataEntry()
    {
        return $this->belongsTo(User::class, 'id_dataentry');
    }

    /** Riwayat import asal FAQ (jika dibuat melalui import Excel) */
    public function riwayatImport()
    {
        return $this->belongsTo(RiwayatImport::class, 'id_riwayat_import');
    }

    /** Versi-versi dari FAQ ini (diurutkan dari nomor versi tertinggi Vn..V1) */
    public function versions()
    {
        return $this->hasMany(FaqVersion::class, 'tFaqs_id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(id, '-V', -1) AS UNSIGNED) DESC")
            ->orderBy('updated_at', 'desc');
    }

    /** Versi terbaru (berdasarkan nomor versi tertinggi Vn) */
    public function latestVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(id, '-V', -1) AS UNSIGNED) DESC")
            ->orderBy('updated_at', 'desc');
    }

    /** Accessor untuk latest version yang selalu mengambil nomor versi tertinggi */
    public function getLatestVersionAttribute()
    {
        if ($this->relationLoaded('versions')) {
            return $this->versions
                ->sortByDesc(function ($v) {
                    return preg_match('/-V(\d+)$/i', (string) $v->id, $m) ? (int) $m[1] : 0;
                })
                ->first();
        }
        return $this->versions()->first();
    }

    /**
     * Scope: Filter FAQ berdasarkan status pada VERSI TERBARU (nomor -Vn tertinggi) saja,
     * sehingga satu FAQ hanya pernah memiliki 1 status aktif dan tidak pernah redundant antar halaman.
     */
    public function scopeWhereLatestVersionIn($query, array $statuses)
    {
        return $query->whereExists(function ($sub) use ($statuses) {
            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('tfaqsversion as fv_latest')
                ->whereColumn('fv_latest.tFaqs_id', 'tfaqs.id')
                ->whereIn('fv_latest.status', $statuses)
                ->whereRaw("CAST(SUBSTRING_INDEX(fv_latest.id, '-V', -1) AS UNSIGNED) = (
                    SELECT MAX(CAST(SUBSTRING_INDEX(fv_max.id, '-V', -1) AS UNSIGNED))
                    FROM tfaqsversion as fv_max
                    WHERE fv_max.tFaqs_id = tfaqs.id
                )");
        });
    }

    /** Versi terbaru yang diterima atau terbit */
    public function latestApprovedVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')
                    ->whereIn('status', ['Diterima', 'Terbit'])
                    ->latestOfMany('updated_at');
    }

    /**
     * Ambil angka urut terbesar dari seluruh kode FAQ di database (contoh: FAQ-0003 -> 3)
     */
    /**
     * Ambil angka urut terbesar dari ID FAQ berformat FAQ-XXXX (0001..9999) di database.
     */
    public static function getMaxSequenceNumber(): int
    {
        $ids = self::pluck('id');
        $maxNumber = 0;

        foreach ($ids as $id) {
            if (preg_match('/^FAQ-(\d{1,4})$/i', trim((string) $id), $matches)) {
                $num = (int) $matches[1];
                if ($num >= 1 && $num <= 9999 && $num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $maxNumber;
    }

    /**
     * Format angka urut menjadi kode FAQ standar (FAQ-0001 s.d. FAQ-9999).
     */
    public static function formatSequenceId(int $number): string
    {
        return sprintf('FAQ-%04d', min(9999, max(1, $number)));
    }

    /**
     * Generate ID FAQ otomatis dengan format FAQ-0001 s.d. FAQ-9999.
     * Melanjutkan nomor terbesar atau mengisi celah kosong jika sudah mencapai 9999.
     */
    public static function generateNextId(int $offset = 0): string
    {
        $safeOffset = max(0, $offset);
        $nextNumber = self::getMaxSequenceNumber() + 1 + $safeOffset;

        if ($nextNumber <= 9999) {
            while ($nextNumber <= 9999) {
                $candidate = self::formatSequenceId($nextNumber);
                if (!self::where('id', $candidate)->exists()) {
                    return $candidate;
                }
                $nextNumber++;
            }
        }

        // Fallback: cari celah nomor kosong dari 1..9999 jika angka tertinggi sudah 9999
        $existingIds = array_flip(self::pluck('id')->all());
        $skipped = 0;
        for ($num = 1; $num <= 9999; $num++) {
            $candidate = self::formatSequenceId($num);
            if (!isset($existingIds[$candidate])) {
                if ($skipped === $safeOffset) {
                    return $candidate;
                }
                $skipped++;
            }
        }

        throw new \RuntimeException('Kapasitas kode FAQ (FAQ-0001 s.d. FAQ-9999) sudah penuh.');
    }

    /**
     * Generate ID versi berikutnya untuk FAQ ini (contoh: FAQ-0001-V1, FAQ-0001-V2).
     * Nomor versi hanya naik dari versi terakhir yang sudah valid (Diterima / Terbit).
     */
    public function generateNextVersionId(): string
    {
        $validStatuses = ['diterima', 'terbit', 'approved', 'accepted', 'disetujui', 'active', 'publish'];
        $validVersionIds = $this->relationLoaded('versions')
            ? $this->versions
                ->filter(fn ($v) => in_array(strtolower((string) $v->status), $validStatuses, true))
                ->pluck('id')
            : $this->versions()
                ->whereIn('status', ['Diterima', 'Terbit', 'approved', 'accepted', 'disetujui', 'active', 'publish'])
                ->pluck('id');

        $maxVersion = 0;

        foreach ($validVersionIds as $vid) {
            if (preg_match('/-V(\d+)$/i', (string) $vid, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxVersion) {
                    $maxVersion = $num;
                }
            }
        }

        $nextVersion = $maxVersion + 1;
        return $this->id . '-V' . $nextVersion;
    }

    /** Tag-tag yang dimiliki FAQ ini */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'tdetail_faqs',
            'tFaqs_id',
            'tTag_id'
        );
    }

    /** Log pencarian terkait FAQ ini */
    public function logPencarian()
    {
        return $this->hasMany(LogMahasiswa::class, 'tFaqs_id');
    }
}

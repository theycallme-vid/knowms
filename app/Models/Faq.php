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
        'tDirektorat_id',
        'id_riwayat_import',
        'pertanyaan',
    ];

    /** Data Entry yang membuat FAQ */
    public function dataEntry()
    {
        return $this->belongsTo(User::class, 'id_dataentry');
    }

    /** Unit / Direktorat pemilik FAQ */
    public function direktorat()
    {
        return $this->belongsTo(Direktorat::class, 'tDirektorat_id');
    }

    /** Riwayat milestone & log validasi FAQ */
    public function validasiLogs()
    {
        return $this->hasMany(FaqValidasiLog::class, 'tFaqs_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    public function isPusatUnit(): bool
    {
        if ($this->relationLoaded('direktorat') && $this->direktorat) {
            return (bool) $this->direktorat->is_pusat;
        }
        if (empty($this->tDirektorat_id)) {
            return true;
        }
        return (int) $this->tDirektorat_id === Direktorat::pusatId();
    }

    public function getUnitNameAttribute(): string
    {
        if (!$this->relationLoaded('direktorat') && !empty($this->tDirektorat_id)) {
            $this->load('direktorat');
        }
        return $this->direktorat?->nama ?: ($this->dataEntry?->direktorat?->nama ?: 'Direktorat Akademik');
    }

    /** Riwayat import asal FAQ (jika dibuat melalui import Excel) */
    public function riwayatImport()
    {
        return $this->belongsTo(RiwayatImport::class, 'id_riwayat_import');
    }

    /** Versi-versi dari FAQ ini (diurutkan dari version_number tertinggi via index) */
    public function versions()
    {
        return $this->hasMany(FaqVersion::class, 'tFaqs_id')
            ->orderByDesc('version_number')
            ->orderByDesc('updated_at');
    }

    /** Versi terbaru (berdasarkan flag is_latest & version_number terindeks) */
    public function latestVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')
            ->where('is_latest', 1)
            ->orderByDesc('version_number');
    }

    /** Accessor untuk latest version yang selalu mengambil nomor versi tertinggi */
    public function getLatestVersionAttribute()
    {
        if ($this->relationLoaded('versions')) {
            return $this->versions
                ->sortByDesc(function ($v) {
                    return (int) ($v->version_number ?: FaqVersion::extractVersionNumber((string) $v->id));
                })
                ->first();
        }
        return $this->versions()->first();
    }

    /**
     * Scope: Filter FAQ berdasarkan status pada VERSI TERBARU menggunakan kolom terindeks is_latest
     * (menggantikan correlated MAX(SUBSTRING_INDEX) subquery yang non-sargable).
     */
    public function scopeWhereLatestVersionIn($query, array $statuses)
    {
        return $query->whereExists(function ($sub) use ($statuses) {
            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('tfaqsversion as fv_latest')
                ->whereColumn('fv_latest.tFaqs_id', 'tfaqs.id')
                ->where('fv_latest.is_latest', 1)
                ->whereIn('fv_latest.status', $statuses);
        });
    }

    /**
     * Scope: Pencarian FAQ terindeks (Prefix PK untuk kode FAQ-..., FULLTEXT untuk pertanyaan >=3 huruf,
     * dan index ttag.nama untuk kata kunci; fallback LIKE hanya untuk kata pendek <3 huruf).
     */
    public function scopeSearchKeyword($query, string $rawSearch, bool $includeTags = true)
    {
        $term = trim($rawSearch);
        if ($term === '') {
            return $query;
        }

        // 1. Jika pencarian berpola kode FAQ (mis. "FAQ-001"), pakai prefix PK index
        if (preg_match('/^faq(?:-[0-9]*)?$/i', $term)) {
            return $query->where('tfaqs.id', 'like', strtoupper($term) . '%');
        }

        // 2. Ekstrak token kata (>= 3 huruf) yang dapat memakai InnoDB FULLTEXT index
        $words = preg_split('/[^\p{L}\p{N}]+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ftWords = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 3));

        if (!empty($ftWords) && in_array(\Illuminate\Support\Facades\DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $booleanQuery = implode(' ', array_map(fn ($w) => '+' . $w . '*', $ftWords));

            $ftIds = \Illuminate\Support\Facades\DB::table('tfaqs')
                ->whereRaw('MATCH(pertanyaan) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery])
                ->pluck('id')
                ->all();

            $tagIds = [];
            if ($includeTags) {
                $tagIds = \Illuminate\Support\Facades\DB::table('tdetail_faqs as df')
                    ->join('ttag as t', 't.id', '=', 'df.tTag_id')
                    ->where('t.nama', 'like', '%' . $term . '%')
                    ->distinct()
                    ->pluck('df.tFaqs_id')
                    ->all();
            }

            $matchedIds = array_values(array_unique(array_merge($ftIds, $tagIds)));
            if (empty($matchedIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('tfaqs.id', $matchedIds);
        }

        // 3. Fallback untuk kata kunci sangat pendek (< 3 huruf seperti "TA", "KP", "S1")
        return $query->where(function ($q) use ($term, $includeTags) {
            $q->where('tfaqs.id', 'like', $term . '%')
              ->orWhere('tfaqs.pertanyaan', 'like', '%' . $term . '%');
            if ($includeTags) {
                $q->orWhereHas('tags', fn ($tq) => $tq->where('nama', 'like', '%' . $term . '%'));
            }
        });
    }

    /**
     * Hitung jumlah FAQ per status versi terbaru dalam 1 query GROUP BY terindeks (opsional filter kata kunci & unit).
     *
     * @param  string[]  $statuses
     * @return array<string, int>
     */
    public static function countLatestByStatus(array $statuses, ?string $tagName = null, ?int $direktoratId = null): array
    {
        $counts = array_fill_keys($statuses, 0);
        $hasTag = ($tagName !== null && $tagName !== '' && $tagName !== 'Semua');
        $hasDir = ($direktoratId !== null && $direktoratId > 0);

        $qb = \Illuminate\Support\Facades\DB::table('tfaqsversion as fv')
            ->join('tfaqs as f', 'f.id', '=', 'fv.tFaqs_id')
            ->where('fv.is_latest', 1)
            ->whereIn('fv.status', $statuses);

        if ($hasDir) {
            $qb->where('f.tDirektorat_id', $direktoratId);
        }

        if ($hasTag) {
            $qb->join('tdetail_faqs as df', 'df.tFaqs_id', '=', 'fv.tFaqs_id')
               ->join('ttag as t', 't.id', '=', 'df.tTag_id')
               ->where('t.nama', $tagName);
        }

        $qb->select('fv.status', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT fv.tFaqs_id) as total'));

        $rows = $qb->groupBy('fv.status')->pluck('total', 'status');

        foreach ($rows as $st => $cnt) {
            if (array_key_exists($st, $counts)) {
                $counts[$st] = (int) $cnt;
            }
        }

        return $counts;
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

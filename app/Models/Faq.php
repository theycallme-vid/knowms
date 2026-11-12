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

    /** Versi-versi dari FAQ ini */
    public function versions()
    {
        return $this->hasMany(FaqVersion::class, 'tFaqs_id')->orderBy('created_at', 'desc');
    }

    /** Versi terbaru (apapun statusnya) */
    public function latestVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')->latestOfMany('created_at');
    }

    /** Accessor untuk latest version yang aman */
    public function getLatestVersionAttribute()
    {
        if ($this->relationLoaded('versions')) {
            return $this->versions->first();
        }
        return $this->versions()->latest('created_at')->first();
    }

    /** Versi aktif/terbaru yang approved */
    public function latestApprovedVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')
                    ->whereIn('status', ['Approved', 'Active'])
                    ->latestOfMany('updated_at');
    }

    /**
     * Ambil angka urut terbesar dari seluruh kode FAQ di database (contoh: FAQ-0003 -> 3)
     */
    public static function getMaxSequenceNumber(): int
    {
        $ids = self::pluck('id');
        $maxNumber = 0;

        foreach ($ids as $id) {
            if (preg_match('/FAQ-(\d+)/i', (string) $id, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $maxNumber;
    }

    /**
     * Format angka urut menjadi kode FAQ standar (FAQ-0001, FAQ-0004, dst.)
     */
    public static function formatSequenceId(int $number): string
    {
        return sprintf('FAQ-%04d', max(1, $number));
    }

    /**
     * Generate ID FAQ otomatis dengan format FAQ-XXXX (contoh: FAQ-0001, FAQ-0002).
     * Selalu melanjutkan dari nomor terbesar di database dan tidak pernah duplikat.
     */
    public static function generateNextId(int $offset = 0): string
    {
        $nextNumber = self::getMaxSequenceNumber() + 1 + max(0, $offset);
        $candidate = self::formatSequenceId($nextNumber);

        while (self::where('id', $candidate)->exists()) {
            $nextNumber++;
            $candidate = self::formatSequenceId($nextNumber);
        }

        return $candidate;
    }

    /**
     * Generate ID versi berikutnya untuk FAQ ini (contoh: FAQ-0001-V1, FAQ-0001-V2).
     * Format sesuai varchar(15) pada tabel tfaqsversion.
     */
    public function generateNextVersionId(): string
    {
        $versions = $this->versions()->pluck('id');
        $maxVersion = 0;

        foreach ($versions as $vid) {
            if (preg_match('/-V(\d+)$/i', $vid, $matches)) {
                $num = (int)$matches[1];
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

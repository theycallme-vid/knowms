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
     * Generate ID FAQ otomatis dengan format FAQ-XXXX (contoh: FAQ-0001, FAQ-0002).
     * Jika tabel masih kosong, default mulai dari FAQ-0001.
     */
    public static function generateNextId(): string
    {
        $ids = self::pluck('id');
        $maxNumber = 0;

        foreach ($ids as $id) {
            if (preg_match('/FAQ-(\d+)/i', $id, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $nextNumber = $maxNumber + 1;
        return sprintf('FAQ-%04d', $nextNumber);
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

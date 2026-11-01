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
        'pertanyaan',
    ];

    /** Data Entry yang membuat FAQ */
    public function dataEntry()
    {
        return $this->belongsTo(User::class, 'id_dataentry');
    }

    /** Versi-versi dari FAQ ini */
    public function versions()
    {
        return $this->hasMany(FaqVersion::class, 'tFaqs_id');
    }

    /** Versi aktif/terbaru yang approved */
    public function latestApprovedVersion()
    {
        return $this->hasOne(FaqVersion::class, 'tFaqs_id')
                    ->where('status', 'approved')
                    ->latestOfMany('updated_at');
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

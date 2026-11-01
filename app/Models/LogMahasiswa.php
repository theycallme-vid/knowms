<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogMahasiswa extends Model
{
    protected $table = 'tlogmahasiswa';
    public $timestamps = false;

    protected $fillable = [
        'keyword',
        'hasil',
        'waktu_pencarian',
        'tSession_id',
        'tFaqs_id',
    ];

    protected $casts = [
        'waktu_pencarian' => 'datetime',
        'hasil'           => 'integer',
    ];

    /** Sesi pencarian */
    public function session()
    {
        return $this->belongsTo(Session::class, 'tSession_id');
    }

    /** FAQ yang ditemukan */
    public function faq()
    {
        return $this->belongsTo(Faq::class, 'tFaqs_id');
    }
}

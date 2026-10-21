<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'ttag';

    protected $fillable = [
        'nama',
    ];

    /**
     * Relasi Many-to-Many: satu Tag bisa dimiliki banyak FAQ,
     * melalui tabel pivot tdetail_faqs.
     */
    public function faqs()
    {
        return $this->belongsToMany(
            Faq::class,
            'tdetail_faqs', // nama tabel pivot
            'tTag_id',      // FK yang menunjuk ke tabel ini (ttag)
            'tFaqs_id'      // FK yang menunjuk ke tabel tujuan (tfaqs)
        );
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'ttag';

    protected $fillable = ['nama'];

    /** FAQ yang memiliki tag ini */
    public function faqs()
    {
        return $this->belongsToMany(
            Faq::class,
            'tdetail_faqs',
            'tTag_id',
            'tFaqs_id'
        );
    }
}

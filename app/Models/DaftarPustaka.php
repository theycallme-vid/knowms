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
}

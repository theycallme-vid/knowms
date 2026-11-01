<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    protected $table = 'tsnapshots';
    public $timestamps = false;

    protected $fillable = [
        'periode_awal',
        'periode_akhir',
        'total_pencarian',
        'total_sukses',
        'total_gagal',
        'total_mhs_aktif',
        'total_form_cs',
        'top_keywords',
    ];

    protected $casts = [
        'periode_awal'   => 'date',
        'periode_akhir'  => 'date',
        'created_at'     => 'datetime',
    ];
}

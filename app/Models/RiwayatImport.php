<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatImport extends Model
{
    protected $table = 'triwayatimport';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama_file',
        'file_path',
        'jenis_data',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** User yang melakukan proses import */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /** Daftar FAQ yang berasal dari riwayat import ini */
    public function faqs()
    {
        return $this->hasMany(Faq::class, 'id_riwayat_import');
    }
}


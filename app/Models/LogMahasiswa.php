<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogMahasiswa extends Model
{
    protected $table = 'tlogmahasiswa';

    // Log tidak pernah diupdate, cukup created_at
    // Namun karena kita pakai kolom 'waktu_pencarian', matikan timestamps bawaan Laravel
    public $timestamps = false;

    protected $fillable = [
        'keyword',
        'hasil',
        'waktu_pencarian',
        'tMahasiswas_id',
        'tFaqs_id',
    ];

    protected $casts = [
        'waktu_pencarian' => 'datetime',
        'hasil'           => 'integer',
    ];

    /**
     * Relasi: log ini dimiliki oleh satu Mahasiswa.
     * Nullable: bisa NULL jika yang mencari adalah Guest (bukan mahasiswa teridentifikasi).
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'tMahasiswas_id');
    }

    /**
     * Relasi: log ini merujuk ke satu FAQ yang ditemukan.
     * Nullable: bisa NULL jika pencarian tidak menemukan hasil.
     */
    public function faq()
    {
        return $this->belongsTo(Faq::class, 'tFaqs_id');
    }
}

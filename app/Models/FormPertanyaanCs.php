<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormPertanyaanCs extends Model
{
    protected $table = 'tformpertanyaancs';

    protected $fillable = [
        'pertanyaan',
        'calonTag',
        'tMahasiswas_id',
    ];

    /**
     * Relasi: pertanyaan CS ini diajukan oleh satu Mahasiswa.
     * Nullable: bisa NULL jika yang bertanya adalah Guest.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'tMahasiswas_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'tmahasiswas';

    protected $fillable = [
        'nrp',
    ];

    /**
     * Relasi: satu Mahasiswa bisa memiliki banyak log pencarian.
     */
    public function logPencarian()
    {
        return $this->hasMany(LogMahasiswa::class, 'tMahasiswas_id');
    }

    /**
     * Relasi: satu Mahasiswa bisa mengajukan banyak pertanyaan ke CS.
     */
    public function pertanyaanCs()
    {
        return $this->hasMany(FormPertanyaanCs::class, 'tMahasiswas_id');
    }
}

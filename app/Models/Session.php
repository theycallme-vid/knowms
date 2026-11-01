<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use SoftDeletes;

    protected $table = 'tsession';

    protected $fillable = ['kode_fakultas'];

    protected $dates = ['deleted_at'];

    /** Log pencarian dalam sesi ini */
    public function logMahasiswa()
    {
        return $this->hasMany(LogMahasiswa::class, 'tSession_id');
    }

    /** Form pertanyaan CS dalam sesi ini */
    public function formPertanyaanCs()
    {
        return $this->hasMany(FormPertanyaanCs::class, 'tSession_id');
    }
}

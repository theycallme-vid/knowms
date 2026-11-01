<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormPertanyaanCs extends Model
{
    protected $table = 'tformpertanyaancs';

    protected $fillable = [
        'pertanyaan',
        'calonTag',
        'tSession_id',
    ];

    /** Sesi terkait */
    public function session()
    {
        return $this->belongsTo(Session::class, 'tSession_id');
    }
}

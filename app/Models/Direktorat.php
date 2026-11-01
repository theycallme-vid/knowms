<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direktorat extends Model
{
    protected $table = 'tdirektorat';
    public $timestamps = false;

    protected $fillable = ['nama'];

    /** Relasi: satu direktorat memiliki banyak user */
    public function users()
    {
        return $this->hasMany(User::class, 'tDirektorat_id');
    }
}

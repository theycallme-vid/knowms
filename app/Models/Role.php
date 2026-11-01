<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'troles';

    protected $fillable = ['nama'];

    /** Relasi: satu role dimiliki banyak user */
    public function users()
    {
        return $this->hasMany(User::class, 'tRoles_id');
    }
}

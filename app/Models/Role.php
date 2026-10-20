<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'tRoles';

    protected $fillable = [
        'nama',
    ];

    /**
     * Relasi: satu Role memiliki banyak User
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tRoles_id');
    }
}

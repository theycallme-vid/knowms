<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Nama tabel di MySQL adalah lowercase 'troles'
    protected $table = 'troles';

    protected $fillable = [
        'nama',
    ];

    /**
     * Relasi: satu Role memiliki banyak User.
     * Parameter kedua 'tRoles_id' adalah nama FK di tabel tusers.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tRoles_id');
    }
}

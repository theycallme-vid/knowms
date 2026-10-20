<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'tUsers';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'npk',
        'tRoles_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi: User milik satu Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'tRoles_id');
    }
}

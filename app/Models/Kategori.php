<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Kategori extends Model
{
    public $timestamps = false;
    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}

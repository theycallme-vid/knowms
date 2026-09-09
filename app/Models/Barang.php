<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;

class Barang extends Model
{
    public $timestamps = false;
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}

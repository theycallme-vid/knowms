<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCall extends Model
{
    use HasFactory;

    protected $table = 'tvideocall';

    protected $fillable = [
        'room_id',
        'nama_mahasiswa',
        'fakultas',
        'status', // 'waiting', 'connected', 'ended'
        'admin_id',
        'admin_nama',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'connected']);
    }
}

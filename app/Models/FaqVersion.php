<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqVersion extends Model
{
    protected $table = 'tfaqsversion';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tDaftarPustakas_id',
        'tFaqs_id',
        'id_validator',
        'jawaban',
        'status',
        'catatan',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /** FAQ induk */
    public function faq()
    {
        return $this->belongsTo(Faq::class, 'tFaqs_id');
    }

    /** Validator yang memeriksa versi ini */
    public function validator()
    {
        return $this->belongsTo(User::class, 'id_validator');
    }

    /** Sumber pustaka terlampir */
    public function daftarPustaka()
    {
        return $this->belongsTo(DaftarPustaka::class, 'tDaftarPustakas_id');
    }

    // Scope helpers (sesuai ENUM: Draft, Submitted, Approved, Rejected, Active, InActive)
    public function scopeDraft($q)      { return $q->where('status', 'Draft'); }
    public function scopeSubmitted($q)  { return $q->where('status', 'Submitted'); }
    public function scopeApproved($q)   { return $q->where('status', 'Approved'); }
    public function scopeRejected($q)   { return $q->where('status', 'Rejected'); }
    public function scopeActive($q)     { return $q->where('status', 'Active'); }
    public function scopeInActive($q)   { return $q->where('status', 'InActive'); }
}

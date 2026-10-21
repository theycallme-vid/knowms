<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'tfaqs';

    protected $fillable = [
        'pertanyaan',
        'jawaban',
        'status',
        'id_dataentry',
        'id_validator',
        'catatanValidasi',
        'tglValidasi',
    ];

    protected $casts = [
        'tglValidasi' => 'datetime',
    ];

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Relasi: FAQ ini dibuat oleh satu User yang berperan sebagai Data Entry.
     * FK di tabel ini: id_dataentry → tusers.id
     */
    public function dataEntry()
    {
        return $this->belongsTo(User::class, 'id_dataentry');
    }

    /**
     * Relasi: FAQ ini diperiksa oleh satu User yang berperan sebagai Validator.
     * FK di tabel ini: id_validator → tusers.id (Nullable)
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'id_validator');
    }

    /**
     * Relasi Many-to-Many: satu FAQ bisa memiliki banyak Tag,
     * melalui tabel pivot tdetail_faqs.
     * Parameter: nama model tujuan, nama tabel pivot, FK lokal, FK tujuan
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'tdetail_faqs',  // nama tabel pivot
            'tFaqs_id',      // FK yang menunjuk ke tabel ini (tfaqs)
            'tTag_id'        // FK yang menunjuk ke tabel tujuan (ttag)
        );
    }

    /**
     * Relasi: satu FAQ bisa muncul di banyak log pencarian.
     */
    public function logPencarian()
    {
        return $this->hasMany(LogMahasiswa::class, 'tFaqs_id');
    }

    // =========================================================
    // SCOPE (Query Builder Shortcut)
    // =========================================================

    /**
     * Scope: hanya ambil FAQ yang sudah approved (tampil publik).
     * Contoh penggunaan: Faq::approved()->get()
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: hanya ambil FAQ dengan status draft.
     * Contoh penggunaan: Faq::draft()->get()
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: hanya ambil FAQ yang menunggu validasi (submitted).
     * Contoh penggunaan: Faq::submitted()->get()
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }
}

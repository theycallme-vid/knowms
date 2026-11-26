<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class FaqValidasiLog extends Model
{
    protected $table = 'tfaq_validasi_log';

    public $timestamps = false;

    protected $fillable = [
        'tFaqs_id',
        'tFaqsVersion_id',
        'tahap',
        'aksi',
        'id_actor',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function faq()
    {
        return $this->belongsTo(Faq::class, 'tFaqs_id');
    }

    public function version()
    {
        return $this->belongsTo(FaqVersion::class, 'tFaqsVersion_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'id_actor');
    }

    /**
     * Catat jejak proses validasi / milestone FAQ secara aman.
     */
    public static function record(
        string $faqId,
        string $versionId,
        string $tahap,
        string $aksi,
        ?int $actorId = null,
        ?string $catatan = null
    ): ?self {
        try {
            $cleanNote = $catatan !== null ? trim($catatan) : null;
            if ($cleanNote === '' || ($cleanNote !== null && str_starts_with($cleanNote, '[PUBLISH_SCHEDULE|'))) {
                $cleanNote = null;
            }

            return self::create([
                'tFaqs_id'        => $faqId,
                'tFaqsVersion_id' => $versionId,
                'tahap'           => $tahap,
                'aksi'            => $aksi,
                'id_actor'        => $actorId,
                'catatan'         => $cleanNote,
                'created_at'      => now(),
            ]);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Format baris log menjadi payload ringkas siap render di modal Milestone / Riwayat Validasi.
     */
    public function toTimelineArray(?string $unitName = null): array
    {
        $ts = $this->created_at?->timezone('Asia/Jakarta');
        $actorName = $this->actor?->nama ?? 'Sistem KMS';
        $actorUnit = $this->actor?->direktorat?->nama ?? $unitName;

        $label = match ($this->tahap . ':' . $this->aksi) {
            'pengajuan:ajukan'    => 'Diajukan oleh Data Entry',
            'pengajuan:revisi'    => 'Direvisi & Diajukan Ulang oleh Data Entry',
            'unit:ajukan'         => 'Dibuat oleh Validator Unit & Diteruskan ke Admik',
            'unit:setujui'        => 'Disetujui oleh Validator Unit' . ($actorUnit ? " ({$actorUnit})" : ''),
            'unit:tolak'          => 'Ditolak oleh Validator Unit' . ($actorUnit ? " ({$actorUnit})" : ''),
            'admik:ajukan'        => 'Dibuat Langsung oleh Validator Admik (Siap Terbit)',
            'admik:setujui'       => 'Disetujui oleh Validator Admik (Diterima)',
            'admik:tolak'         => 'Ditolak oleh Validator Admik',
            'publikasi:jadwalkan' => 'Dijadwalkan Publikasi oleh Validator Admik',
            'publikasi:terbit'    => 'Diterbitkan ke Portal Mahasiswa',
            default               => ucfirst($this->aksi) . ' (' . ucfirst($this->tahap) . ')',
        };

        $tone = match ($this->aksi) {
            'tolak'               => 'danger',
            'terbit'              => 'success',
            'setujui'             => 'primary',
            'jadwalkan'           => 'info',
            'revisi'              => 'warning',
            default               => 'neutral',
        };

        return [
            'id'         => $this->id,
            'version_id' => $this->tFaqsVersion_id,
            'tahap'      => $this->tahap,
            'aksi'       => $this->aksi,
            'label'      => $label,
            'tone'       => $tone,
            'actor'      => $actorName,
            'catatan'    => $this->catatan,
            'waktu'      => $ts ? $ts->translatedFormat('d M Y, H.i') . ' WIB' : '-',
        ];
    }
}

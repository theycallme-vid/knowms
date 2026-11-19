<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

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

    // Scope helpers (sesuai ENUM: Draf, Menunggu Validasi, Diterima, Ditolak, Terbit)
    public function scopeDraf($q)             { return $q->where('status', 'Draf'); }
    public function scopeMenungguValidasi($q) { return $q->where('status', 'Menunggu Validasi'); }
    public function scopeDiterima($q)         { return $q->where('status', 'Diterima'); }
    public function scopeDitolak($q)          { return $q->where('status', 'Ditolak'); }
    public function scopeTerbit($q)           { return $q->where('status', 'Terbit'); }

    /**
     * Format kode versi (contoh: FAQ-0001-V1 -> Versi 1).
     */
    public static function formatVersiLabel(?string $versionId): string
    {
        $id = (string) $versionId;
        if (preg_match('/-V(\d+)$/i', $id, $m)) {
            return 'Versi ' . (int) $m[1];
        }
        return 'Versi 1';
    }

    public function getVersiLabelAttribute(): string
    {
        return self::formatVersiLabel($this->id);
    }

    /**
     * Memastikan kolom enum status pada tfaqsversion sudah memakai 5 status Bahasa Indonesia
     * ('Draf', 'Menunggu Validasi', 'Diterima', 'Ditolak', 'Terbit') dan mengonversi data lama jika ada.
     */
    public static function ensureIndonesianStatusEnum(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $col = DB::selectOne("SHOW COLUMNS FROM tfaqsversion LIKE 'status'");
            $typeStr = strtolower((string) ($col->Type ?? ''));

            if (!str_contains($typeStr, 'menunggu validasi') || str_contains($typeStr, 'inactive') || str_contains($typeStr, "'active'")) {
                DB::statement("ALTER TABLE tfaqsversion MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Draf'");
                DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE LOWER(status) IN ('draft', 'draf')");
                DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi' WHERE LOWER(status) IN ('submitted', 'pending', 'menunggu validasi')");
                DB::statement("UPDATE tfaqsversion SET status = 'Diterima' WHERE LOWER(status) IN ('approved', 'accepted', 'disetujui', 'diterima')");
                DB::statement("UPDATE tfaqsversion SET status = 'Ditolak' WHERE LOWER(status) IN ('rejected', 'ditolak')");
                DB::statement("UPDATE tfaqsversion SET status = 'Terbit' WHERE LOWER(status) IN ('active', 'inactive', 'terbit', 'publish')");
                DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE status NOT IN ('Draf', 'Menunggu Validasi', 'Diterima', 'Ditolak', 'Terbit')");
                DB::statement("ALTER TABLE tfaqsversion MODIFY status ENUM('Draf', 'Menunggu Validasi', 'Diterima', 'Ditolak', 'Terbit') NOT NULL DEFAULT 'Draf'");
            }
        } catch (Throwable $e) {
            // Abaikan jika tabel belum siap
        }
    }

    /**
     * Membuat string penanda jadwal publikasi yang disimpan di kolom catatan saat status masih Diterima.
     */
    public static function encodePublishSchedule(string $date, string $time): string
    {
        $dt = Carbon::parse($date . ' ' . $time);
        $scheduledAt = $dt->format('Y-m-d H:i:s');
        $label = 'Terjadwal Terbit · ' . $dt->translatedFormat('d M Y') . ' Pukul ' . $dt->format('H.i') . ' WIB';

        return "[PUBLISH_SCHEDULE|scheduled|{$scheduledAt}|{$label}]";
    }

    /**
     * Membaca informasi jadwal publikasi dari kolom catatan (jika FAQ dijadwalkan terbit).
     */
    public function getPublishScheduleInfo(): ?array
    {
        $raw = trim((string) $this->catatan);
        if (!str_starts_with($raw, '[PUBLISH_SCHEDULE|')) {
            return null;
        }

        $trimmed = trim($raw, '[]');
        $parts = explode('|', $trimmed, 4);
        if (count($parts) < 4) {
            return null;
        }

        $mode = $parts[1];
        if ($mode === 'routine') {
            return null;
        }

        $scheduledAt = $parts[2];
        $label = $parts[3];

        $datePart = '';
        $timePart = '';
        try {
            $dt = Carbon::parse($scheduledAt);
            $datePart = $dt->format('Y-m-d');
            $timePart = $dt->format('H:i');
        } catch (Throwable $e) {
            // abaikan jika gagal parse
        }

        return [
            'is_scheduled' => true,
            'mode'         => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'date'         => $datePart,
            'time'         => $timePart,
            'label'        => $label,
        ];
    }

    /**
     * Menerbitkan otomatis FAQ berstatus Diterima yang jadwal publikasinya sudah tiba.
     */
    public static function processDueScheduledPublications(): int
    {
        $activatedCount = 0;

        try {
            self::ensureIndonesianStatusEnum();

            $scheduledVersions = self::where('status', 'Diterima')
                ->where('catatan', 'like', '[PUBLISH_SCHEDULE|%')
                ->get();

            foreach ($scheduledVersions as $ver) {
                $info = $ver->getPublishScheduleInfo();
                if (!$info || empty($info['scheduled_at'])) {
                    continue;
                }

                $targetTime = Carbon::parse($info['scheduled_at']);
                if (now()->gte($targetTime)) {
                    $ver->update([
                        'status'     => 'Terbit',
                        'catatan'    => null,
                        'updated_at' => now(),
                    ]);

                    $activatedCount++;
                }
            }
        } catch (Throwable $e) {
            // Abaikan bila database belum siap
        }

        return $activatedCount;
    }
}

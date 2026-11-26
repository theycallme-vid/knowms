<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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
        'version_number',
        'id_validator',
        'jawaban',
        'jawaban_sebelumnya',
        'status',
        'rejected_by_stage',
        'is_latest',
        'catatan',
    ];

    protected $casts = [
        'status'         => 'string',
        'version_number' => 'integer',
        'is_latest'      => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (!empty($model->id)) {
                $model->version_number = self::extractVersionNumber((string) $model->id);
            }
        });

        static::saved(function (self $model) {
            if ($model->tFaqs_id && ($model->wasRecentlyCreated || $model->wasChanged(['id', 'tFaqs_id', 'version_number']))) {
                self::syncLatestFlag((string) $model->tFaqs_id);
            }
        });

        static::deleted(function (self $model) {
            if ($model->tFaqs_id) {
                self::syncLatestFlag((string) $model->tFaqs_id);
            }
        });
    }

    /**
     * Ekstrak angka versi dari ID versi (contoh: FAQ-0001-V3 -> 3).
     */
    public static function extractVersionNumber(?string $versionId): int
    {
        if (preg_match('/-V(\d+)$/i', (string) $versionId, $m)) {
            return max(1, (int) $m[1]);
        }
        return 1;
    }

    /**
     * Sinkronisasi kolom is_latest pada semua versi milik satu FAQ berdasarkan version_number tertinggi.
     */
    public static function syncLatestFlag(string $faqId): void
    {
        if ($faqId === '') {
            return;
        }

        try {
            $maxVer = DB::table('tfaqsversion')
                ->where('tFaqs_id', $faqId)
                ->max('version_number');

            if ($maxVer === null) {
                return;
            }

            DB::table('tfaqsversion')
                ->where('tFaqs_id', $faqId)
                ->update([
                    'is_latest' => DB::raw('CASE WHEN version_number = ' . ((int) $maxVer) . ' THEN 1 ELSE 0 END'),
                ]);
        } catch (Throwable $e) {
            // Abaikan jika kolom belum tersedia sebelum migration dijalankan
        }
    }

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

    /** Riwayat log validasi khusus versi ini */
    public function validasiLogs()
    {
        return $this->hasMany(FaqValidasiLog::class, 'tFaqsVersion_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    // Scope helpers (sesuai ENUM 6 status multi-unit)
    public function scopeDraf($q)                  { return $q->where('status', 'Draf'); }
    public function scopeMenungguValidasiUnit($q)  { return $q->where('status', 'Menunggu Validasi Unit'); }
    public function scopeMenungguValidasiAdmik($q) { return $q->where('status', 'Menunggu Validasi Admik'); }
    public function scopeMenungguValidasi($q)      { return $q->whereIn('status', ['Menunggu Validasi Unit', 'Menunggu Validasi Admik']); }
    public function scopeDiterima($q)              { return $q->where('status', 'Diterima'); }
    public function scopeDitolak($q)               { return $q->where('status', 'Ditolak'); }
    public function scopeTerbit($q)                { return $q->where('status', 'Terbit'); }

    public static function isWaitingValidation(?string $status): bool
    {
        return in_array(
            strtolower(trim((string) $status)),
            ['menunggu validasi', 'menunggu validasi unit', 'menunggu validasi admik', 'submitted', 'pending'],
            true
        );
    }

    /**
     * Label penanda sumber penolakan yang jelas untuk Data Entry & Validator.
     */
    public function getRejectionBadgeLabel(?string $unitName = null): string
    {
        if ($this->rejected_by_stage === 'unit') {
            $cleanUnit = trim((string) $unitName);
            return ($cleanUnit !== '' && strtolower($cleanUnit) !== 'direktorat akademik')
                ? "Ditolak oleh Validator Unit ({$cleanUnit})"
                : 'Ditolak oleh Validator Unit';
        }

        return 'Ditolak oleh Validator Admik';
    }

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
     * Memastikan kolom enum status pada tfaqsversion sudah memakai 6 status multi-unit Bahasa Indonesia.
     * Di-cache permanen pada file store agar tidak menjalankan SHOW COLUMNS & full-table UPDATE di setiap request.
     */
    public static function ensureIndonesianStatusEnum(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            if (Cache::store('file')->get('kms_schema_faqversion_v3')) {
                return;
            }

            $prevAnsCol = DB::selectOne("SHOW COLUMNS FROM tfaqsversion LIKE 'jawaban_sebelumnya'");
            if (!$prevAnsCol) {
                DB::statement("ALTER TABLE tfaqsversion ADD COLUMN jawaban_sebelumnya TEXT NULL AFTER jawaban");
            }

            $col = DB::selectOne("SHOW COLUMNS FROM tfaqsversion LIKE 'status'");
            $typeStr = strtolower((string) ($col->Type ?? ''));

            if (!str_contains($typeStr, 'menunggu validasi unit') || !str_contains($typeStr, 'menunggu validasi admik')) {
                DB::statement("ALTER TABLE tfaqsversion MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Draf'");
                DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE LOWER(status) IN ('draft', 'draf')");
                DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi Unit' WHERE LOWER(status) = 'menunggu validasi unit'");
                DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi Admik' WHERE LOWER(status) IN ('submitted', 'pending', 'menunggu validasi', 'menunggu validasi admik')");
                DB::statement("UPDATE tfaqsversion SET status = 'Diterima' WHERE LOWER(status) IN ('approved', 'accepted', 'disetujui', 'diterima')");
                DB::statement("UPDATE tfaqsversion SET status = 'Ditolak' WHERE LOWER(status) IN ('rejected', 'ditolak')");
                DB::statement("UPDATE tfaqsversion SET status = 'Terbit' WHERE LOWER(status) IN ('active', 'inactive', 'terbit', 'publish')");
                DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE status NOT IN ('Draf', 'Menunggu Validasi Unit', 'Menunggu Validasi Admik', 'Diterima', 'Ditolak', 'Terbit')");
                DB::statement("ALTER TABLE tfaqsversion MODIFY status ENUM('Draf', 'Menunggu Validasi Unit', 'Menunggu Validasi Admik', 'Diterima', 'Ditolak', 'Terbit') NOT NULL DEFAULT 'Draf'");
            }

            $rejCol = DB::selectOne("SHOW COLUMNS FROM tfaqsversion LIKE 'rejected_by_stage'");
            if (!$rejCol) {
                DB::statement("ALTER TABLE tfaqsversion ADD COLUMN rejected_by_stage ENUM('unit', 'admik') NULL AFTER status");
            }

            Cache::store('file')->forever('kms_schema_faqversion_v3', true);
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
     * Dibatasi maksimal 1x per 60 detik via file cache agar tidak mengeksekusi query di setiap page load.
     */
    public static function processDueScheduledPublications(bool $force = false): int
    {
        $activatedCount = 0;

        try {
            if (!$force && !Cache::store('file')->add('kms_due_publish_checked', 1, 60)) {
                return 0;
            }

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
                    FaqValidasiLog::record(
                        (string) $ver->tFaqs_id,
                        (string) $ver->id,
                        'publikasi',
                        'terbit',
                        $ver->id_validator ? (int) $ver->id_validator : null,
                        'Diterbitkan otomatis sesuai jadwal publikasi.'
                    );

                    $activatedCount++;
                }
            }
        } catch (Throwable $e) {
            // Abaikan bila database belum siap
        }

        return $activatedCount;
    }
}

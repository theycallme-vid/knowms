<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

    // Scope helpers (sesuai ENUM: Draft, Submitted, Approved, Rejected, Active, InActive)
    public function scopeDraft($q)      { return $q->where('status', 'Draft'); }
    public function scopeSubmitted($q)  { return $q->where('status', 'Submitted'); }
    public function scopeApproved($q)   { return $q->where('status', 'Approved'); }
    public function scopeRejected($q)   { return $q->where('status', 'Rejected'); }
    public function scopeActive($q)     { return $q->where('status', 'Active'); }
    public function scopeInActive($q)   { return $q->where('status', 'InActive'); }

    /**
     * Konfigurasi Default Jam Publikasi Rutin (Format 24 Jam: 23 = Jam 11 Malam)
     */
    public const DEFAULT_ROUTINE_HOUR   = 23;
    public const DEFAULT_ROUTINE_MINUTE = 0;

    /**
     * Membuat string penanda jadwal publikasi yang disimpan di kolom catatan saat status masih Approved.
     */
    public static function encodePublishSchedule(string $mode, ?string $date = null, ?string $time = null): string
    {
        if ($mode === 'scheduled' && $date && $time) {
            $dt = Carbon::parse($date . ' ' . $time);
            $scheduledAt = $dt->format('Y-m-d H:i:s');
            $label = 'Sudah Dipublish · Menunggu ' . $dt->translatedFormat('d M Y') . ' Pukul ' . $dt->format('H.i') . ' WIB';
            return "[PUBLISH_SCHEDULE|scheduled|{$scheduledAt}|{$label}]";
        }

        // Default: Publikasi Rutin (menunggu jam 11 malam / 23.00 WIB atau jadwal rutin sistem)
        $now = now();
        $targetRoutine = $now->copy()->setTime(self::DEFAULT_ROUTINE_HOUR, self::DEFAULT_ROUTINE_MINUTE, 0);
        if ($now->gte($targetRoutine)) {
            $targetRoutine->addDay()->setTime(self::DEFAULT_ROUTINE_HOUR, self::DEFAULT_ROUTINE_MINUTE, 0);
        }
        $scheduledAt = $targetRoutine->format('Y-m-d H:i:s');
        $hourStr = sprintf('%02d.%02d', self::DEFAULT_ROUTINE_HOUR, self::DEFAULT_ROUTINE_MINUTE);
        $label = "Sudah Dipublish · Menunggu Jadwal Rutin ({$hourStr} WIB)";

        return "[PUBLISH_SCHEDULE|routine|{$scheduledAt}|{$label}]";
    }

    /**
     * Membaca informasi jadwal publikasi dari kolom catatan (jika FAQ sudah di-publish terjadwal/rutin).
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
            'mode'         => $mode,
            'scheduled_at' => $scheduledAt,
            'date'         => $datePart,
            'time'         => $timePart,
            'label'        => $label,
        ];
    }

    /**
     * Mengaktifkan otomatis FAQ berstatus Approved yang jadwal publikasinya sudah tiba.
     */
    public static function processDueScheduledPublications(): int
    {
        $activatedCount = 0;

        try {
            $scheduledVersions = self::whereIn('status', ['Approved', 'approved'])
                ->where('catatan', 'like', '[PUBLISH_SCHEDULE|%')
                ->get();

            foreach ($scheduledVersions as $ver) {
                $info = $ver->getPublishScheduleInfo();
                if (!$info || empty($info['scheduled_at'])) {
                    continue;
                }

                $targetTime = Carbon::parse($info['scheduled_at']);
                if (now()->gte($targetTime)) {
                    // Nonaktifkan versi Active lama pada FAQ yang sama
                    self::where('tFaqs_id', $ver->tFaqs_id)
                        ->where('id', '!=', $ver->id)
                        ->whereIn('status', ['Active', 'active'])
                        ->update([
                            'status'     => 'InActive',
                            'updated_at' => now(),
                        ]);

                    $ver->update([
                        'status'     => 'Active',
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

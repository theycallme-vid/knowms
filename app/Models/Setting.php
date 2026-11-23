<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Setting extends Model
{
    protected $table = 'tsettings';

    protected $fillable = [
        'key',
        'value',
        'keterangan',
    ];

    /**
     * Daftar konfigurasi default sistem (Bobot Rekomendasi FAQ & Durasi Idle Auto-Logout Mahasiswa/Guest).
     */
    public const DEFAULTS = [
        'idle_timeout_seconds' => [
            'value'      => '30',
            'label'      => 'Waktu Diam Sebelum Pop-up (Detik)',
            'keterangan' => 'Durasi tanpa aktivitas (detik) di halaman FAQ Mahasiswa & Guest sebelum pop-up konfirmasi muncul.',
            'group'      => 'session',
        ],
        'idle_countdown_seconds' => [
            'value'      => '10',
            'label'      => 'Waktu Hitung Mundur Pop-up (Detik)',
            'keterangan' => 'Durasi hitung mundur pada pop-up (detik) sebelum sesi berakhir dan otomatis kembali ke Portal.',
            'group'      => 'session',
        ],
        'rec_log_days' => [
            'value'      => '30',
            'label'      => 'Rentang Waktu Log Rekomendasi (Hari Terakhir)',
            'keterangan' => 'Hanya interaksi mahasiswa (View, Like, Dislike) dalam kurun waktu N hari terakhir yang dihitung oleh algoritma rekomendasi FAQ.',
            'group'      => 'log_window',
        ],
        'rec_weight_exact' => [
            'value'      => '10',
            'label'      => 'Bobot View Segmen Persis (Fakultas + Angkatan Sama)',
            'keterangan' => 'Tambahan skor untuk setiap 1x View dari mahasiswa dengan kode Fakultas dan Angkatan yang sama persis.',
            'group'      => 'recommendation',
        ],
        'rec_weight_angkatan' => [
            'value'      => '5',
            'label'      => 'Bobot View Angkatan Sama',
            'keterangan' => 'Tambahan skor untuk setiap 1x View dari mahasiswa dengan tahun Angkatan yang sama.',
            'group'      => 'recommendation',
        ],
        'rec_weight_fakultas' => [
            'value'      => '4',
            'label'      => 'Bobot View Fakultas Sama',
            'keterangan' => 'Tambahan skor untuk setiap 1x View dari mahasiswa dengan Fakultas yang sama.',
            'group'      => 'recommendation',
        ],
        'rec_weight_total' => [
            'value'      => '1',
            'label'      => 'Bobot View Global (Semua Mahasiswa/Guest)',
            'keterangan' => 'Tambahan skor untuk setiap 1x View secara keseluruhan.',
            'group'      => 'recommendation',
        ],
        'rec_weight_like' => [
            'value'      => '3',
            'label'      => 'Bobot Feedback Positif (Like / Membantu)',
            'keterangan' => 'Tambahan skor untuk setiap 1x klik tombol "Ya, membantu" pada FAQ.',
            'group'      => 'recommendation',
        ],
        'rec_weight_dislike' => [
            'value'      => '-5',
            'label'      => 'Penalti Feedback Negatif (Dislike / Belum)',
            'keterangan' => 'Pengurang skor (isi angka negatif, misal -5) untuk setiap 1x klik tombol "Belum" pada FAQ.',
            'group'      => 'recommendation',
        ],
        'rec_bonus_own_fakultas' => [
            'value'      => '15',
            'label'      => 'Bonus Kecocokan Topik Fakultas Mahasiswa',
            'keterangan' => 'Bonus skor dasar apabila kata kunci / pertanyaan FAQ sesuai dengan Fakultas mahasiswa.',
            'group'      => 'recommendation',
        ],
        'rec_bonus_umum_fakultas' => [
            'value'      => '5',
            'label'      => 'Bonus Topik FAQ Umum (Lintas Fakultas)',
            'keterangan' => 'Bonus skor dasar untuk FAQ umum yang berlaku bagi seluruh fakultas.',
            'group'      => 'recommendation',
        ],
        'rec_penalty_other_fakultas' => [
            'value'      => '-30',
            'label'      => 'Penalti Topik Fakultas Lain',
            'keterangan' => 'Pengurang skor (isi angka negatif, misal -30) apabila FAQ spesifik milik fakultas lain.',
            'group'      => 'recommendation',
        ],
        'rec_bonus_angkatan_senior' => [
            'value'      => '18',
            'label'      => 'Bonus Topik Tingkat Akhir (Semester 7+)',
            'keterangan' => 'Bonus skor untuk topik Tugas Akhir, Skripsi, Yudisium, dan Wisuda bagi mahasiswa Semester 7 ke atas.',
            'group'      => 'recommendation',
        ],
        'rec_bonus_angkatan_general' => [
            'value'      => '15',
            'label'      => 'Bonus Topik Sesuai Semester (Semester 1–6)',
            'keterangan' => 'Bonus skor untuk topik yang relevan dengan tingkat semester mahasiswa (Maba / Praktikum / KP & MBKM).',
            'group'      => 'recommendation',
        ],
        'alert_call_unit_ids' => [
            'value'      => '1',
            'label'      => 'Unit Penerima Alert Call',
            'keterangan' => 'Daftar ID Unit/Direktorat yang ditugaskan menerima notifikasi panggilan masuk dari mahasiswa (dipisahkan koma jika lebih dari satu).',
            'group'      => 'call_routing',
        ],
    ];

    /**
     * Pastikan tabel tsettings dan kolom jenis_interaksi pada tlogmahasiswa tersedia,
     * serta isi nilai default jika belum ada.
     */
    public static function ensureDefaults(): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }
        $ensured = true;

        try {
            if (!Schema::hasTable('tsettings')) {
                Schema::create('tsettings', function (Blueprint $table) {
                    $table->id();
                    $table->string('key', 100)->unique();
                    $table->string('value', 255);
                    $table->string('keterangan', 255)->nullable();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('tlogmahasiswa') && !Schema::hasColumn('tlogmahasiswa', 'jenis_interaksi')) {
                Schema::table('tlogmahasiswa', function (Blueprint $table) {
                    $table->string('jenis_interaksi', 20)->default('view')->after('id');
                });
            }

            $existingKeys = self::pluck('key')->all();
            foreach (self::DEFAULTS as $key => $meta) {
                if (!in_array($key, $existingKeys, true)) {
                    self::create([
                        'key'        => $key,
                        'value'      => $meta['value'],
                        'keterangan' => $meta['keterangan'],
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Gunakan nilai fallback DEFAULTS di memori apabila koneksi DB belum siap
        }
    }

    /**
     * Mengambil seluruh pengaturan sebagai array asosiatif [key => int].
     */
    public static function allIntValues(): array
    {
        $result = [];
        foreach (self::DEFAULTS as $key => $meta) {
            $result[$key] = (int) $meta['value'];
        }

        try {
            $rows = self::pluck('value', 'key');
            if ($rows->count() < count(self::DEFAULTS)) {
                self::ensureDefaults();
                $rows = self::pluck('value', 'key');
            }
            foreach ($rows as $k => $v) {
                if (array_key_exists($k, $result) && is_numeric($v)) {
                    $result[$k] = (int) $v;
                }
            }
        } catch (Throwable $e) {
            self::ensureDefaults();
        }

        return $result;
    }

    /**
     * Mengembalikan seluruh nilai di tabel tsettings ke nilai bawaan (DEFAULTS).
     */
    public static function resetToDefaults(): void
    {
        self::ensureDefaults();
        foreach (self::DEFAULTS as $key => $meta) {
            self::updateOrCreate(
                ['key' => $key],
                [
                    'value'      => $meta['value'],
                    'keterangan' => $meta['keterangan'],
                ]
            );
        }
    }

    /**
     * Mengambil daftar ID unit/direktorat yang berhak menerima notifikasi panggilan masuk dari tsettings.
     *
     * @return array<int>
     */
    public static function getAllowedCallUnitIds(): array
    {
        self::ensureDefaults();
        $raw = self::where('key', 'alert_call_unit_ids')->value('value');
        if ($raw === null || trim((string) $raw) === '') {
            $raw = self::DEFAULTS['alert_call_unit_ids']['value'] ?? '1';
        }

        $ids = array_filter(array_map('intval', explode(',', (string) $raw)));
        return !empty($ids) ? array_values(array_unique($ids)) : [1];
    }

    /**
     * Menyimpan daftar ID unit yang dipilih oleh Validator ke tabel tsettings.
     *
     * @param array<int>|string $unitIds
     */
    public static function setAllowedCallUnitIds($unitIds): void
    {
        self::ensureDefaults();
        if (is_array($unitIds)) {
            $cleaned = array_filter(array_map('intval', $unitIds));
            $val = implode(',', array_unique($cleaned));
        } else {
            $val = trim((string) $unitIds);
        }

        self::updateOrCreate(
            ['key' => 'alert_call_unit_ids'],
            [
                'value'      => $val,
                'keterangan' => 'Daftar ID Unit/Direktorat yang ditugaskan menerima notifikasi panggilan masuk dari mahasiswa.',
            ]
        );
    }
}

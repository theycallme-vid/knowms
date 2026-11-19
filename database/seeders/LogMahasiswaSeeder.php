<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\LogMahasiswa;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogMahasiswaSeeder extends Seeder
{
    /**
     * Isi data dummy sesi mahasiswa/guest beserta log views, likes, dan dislikes
     * pada tabel tsession & tlogmahasiswa untuk FAQ berstatus Active.
     */
    public function run(): void
    {
        Setting::ensureDefaults();

        // Hindari duplikasi log jika seeder dijalankan ulang tanpa migrate:fresh
        if (LogMahasiswa::whereNotNull('tFaqs_id')->exists()) {
            return;
        }

        $activeFaqs = Faq::with('tags')
            ->whereHas('versions', fn ($q) => $q->whereIn('status', ['Active', 'active']))
            ->orderBy('id')
            ->get();

        if ($activeFaqs->isEmpty()) {
            return;
        }

        $now = Carbon::now('Asia/Jakarta');

        // 20 sesi lintas segmen fakultas, angkatan, dan guest dalam 7 hari terakhir
        $sessionTemplates = [
            ['kode' => '16-23', 'days_ago' => 0, 'hour' => 9],
            ['kode' => '16-23', 'days_ago' => 1, 'hour' => 10],
            ['kode' => '16-23', 'days_ago' => 2, 'hour' => 14],
            ['kode' => '16-23', 'days_ago' => 4, 'hour' => 11],
            ['kode' => '16-23', 'days_ago' => 6, 'hour' => 15],
            ['kode' => '16-24', 'days_ago' => 0, 'hour' => 13],
            ['kode' => '16-24', 'days_ago' => 1, 'hour' => 16],
            ['kode' => '16-24', 'days_ago' => 3, 'hour' => 10],
            ['kode' => '16-24', 'days_ago' => 5, 'hour' => 12],
            ['kode' => '16-25', 'days_ago' => 1, 'hour' => 8],
            ['kode' => '16-25', 'days_ago' => 3, 'hour' => 14],
            ['kode' => '16-25', 'days_ago' => 5, 'hour' => 9],
            ['kode' => '16-22', 'days_ago' => 2, 'hour' => 11],
            ['kode' => '16-22', 'days_ago' => 4, 'hour' => 16],
            ['kode' => '13-24', 'days_ago' => 0, 'hour' => 15],
            ['kode' => '13-24', 'days_ago' => 3, 'hour' => 13],
            ['kode' => '12-23', 'days_ago' => 2, 'hour' => 10],
            ['kode' => '18-22', 'days_ago' => 5, 'hour' => 14],
            ['kode' => null,    'days_ago' => 0, 'hour' => 11],
            ['kode' => null,    'days_ago' => 4, 'hour' => 13],
        ];

        $sessions = [];
        foreach ($sessionTemplates as $idx => $tpl) {
            $createdAt = $now->copy()->subDays($tpl['days_ago'])->setTime($tpl['hour'], ($idx * 3) % 60, 0);
            $endedAt   = $createdAt->copy()->addMinutes(4);

            $id = DB::table('tsession')->insertGetId([
                'kode_fakultas' => $tpl['kode'],
                'created_at'    => $createdAt,
                'updated_at'    => $endedAt,
                'deleted_at'    => $endedAt,
            ]);

            $sessions[] = [
                'id'         => $id,
                'kode'       => $tpl['kode'] ?? '',
                'created_at' => $createdAt,
            ];
        }

        $facultyTagPrefix = [
            'fakultas teknik'                => '16',
            'fakultas bisnis dan ekonomika'  => '13',
            'fakultas hukum'                 => '12',
            'fakultas kedokteran'            => '18',
        ];

        $logsToInsert = [];

        foreach ($activeFaqs->values() as $idx => $faq) {
            $tagNames = strtolower($faq->tags->pluck('nama')->implode(' '));

            $targetPrefix = null;
            foreach ($facultyTagPrefix as $tagName => $prefix) {
                if (str_contains($tagNames, $tagName)) {
                    $targetPrefix = $prefix;
                    break;
                }
            }

            // Urutkan sesi agar segmen fakultas yang cocok mendapat porsi view lebih banyak
            $orderedSessions = $sessions;
            usort($orderedSessions, function ($a, $b) use ($targetPrefix, $idx) {
                $aMatch = $targetPrefix && str_starts_with($a['kode'], $targetPrefix) ? 1 : 0;
                $bMatch = $targetPrefix && str_starts_with($b['kode'], $targetPrefix) ? 1 : 0;
                if ($aMatch !== $bMatch) {
                    return $bMatch <=> $aMatch;
                }
                return (($a['id'] + $idx) % 7) <=> (($b['id'] + $idx) % 7);
            });

            $viewsCount    = 5 + (($idx * 5) % 14); // 5..18 views
            $likesCount    = 2 + (($idx * 3) % 7);  // 2..8 likes
            $dislikesCount = ($idx % 3 === 0) ? (1 + ($idx % 4)) : (($idx % 4 === 1) ? 1 : 0); // 0..4 dislikes

            $viewsCount    = min($viewsCount, count($orderedSessions));
            $likesCount    = min($likesCount, $viewsCount);
            $dislikesCount = min($dislikesCount, $viewsCount - $likesCount);

            $viewedSessions = array_slice($orderedSessions, 0, $viewsCount);

            foreach ($viewedSessions as $sIdx => $sess) {
                $viewTime = $sess['created_at']->copy()->addSeconds(15 + ($sIdx * 4));

                $logsToInsert[] = [
                    'jenis_interaksi' => 'view',
                    'keyword'         => 'buka-faq',
                    'hasil'           => 1,
                    'waktu_pencarian' => $viewTime,
                    'tSession_id'     => $sess['id'],
                    'tFaqs_id'        => $faq->id,
                ];

                if ($sIdx < $likesCount) {
                    $logsToInsert[] = [
                        'jenis_interaksi' => 'like',
                        'keyword'         => 'feedback-membantu',
                        'hasil'           => 1,
                        'waktu_pencarian' => $viewTime->copy()->addSeconds(10),
                        'tSession_id'     => $sess['id'],
                        'tFaqs_id'        => $faq->id,
                    ];
                } elseif ($sIdx < ($likesCount + $dislikesCount)) {
                    $logsToInsert[] = [
                        'jenis_interaksi' => 'dislike',
                        'keyword'         => 'feedback-belum',
                        'hasil'           => 0,
                        'waktu_pencarian' => $viewTime->copy()->addSeconds(10),
                        'tSession_id'     => $sess['id'],
                        'tFaqs_id'        => $faq->id,
                    ];
                }
            }
        }

        foreach (array_chunk($logsToInsert, 200) as $chunk) {
            DB::table('tlogmahasiswa')->insert($chunk);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FaqVersionSeeder extends Seeder
{
    /**
     * Menyelaraskan jumlah versi jawaban pada seluruh FAQ yang sudah ada di database:
     * - 3 FAQ pertama pada indeks tertentu (indeks 2, 6, 9) hanya memiliki 1 versi (V1).
     * - Seluruh FAQ sisanya memiliki 4 atau 5 versi (V1–V4 / V1–V5) dengan riwayat bertahap.
     */
    public function run(): void
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();

        $validatorId = User::where('tRoles_id', 3)->value('id') ?? User::value('id');
        $faqs = Faq::with(['versions' => fn ($q) => $q->orderBy('updated_at', 'desc')])
            ->orderBy('id', 'asc')
            ->get();

        if ($faqs->isEmpty()) {
            $this->call(FaqSeeder::class);
            return;
        }

        // Pilih tepat 3 FAQ untuk tetap 1 versi (utamakan yang berstatus Terbit jika tersedia)
        $publishedIds = $faqs->filter(function (Faq $f) {
            return $f->versions->contains(fn ($v) => strtolower((string) $v->status) === 'terbit');
        })->pluck('id')->values();

        $singleVersionIds = $publishedIds->count() >= 3
            ? $publishedIds->slice(0, 3)->all()
            : $faqs->pluck('id')->slice(0, 3)->all();

        $revisionNotes = [
            1 => ' (Edisi awal panduan akademik sebelum penyesuaian alur daring).',
            2 => ' (Pembaruan prosedur pengajuan melalui portal layanan mahasiswa).',
            3 => ' (Penyesuaian syarat administrasi dan jadwal verifikasi fakultas).',
            4 => ' (Penyempurnaan rincian dokumen pendukung dan ketentuan terbaru).',
        ];

        foreach ($faqs->values() as $idx => $faq) {
            $latestVer = $faq->versions->first();
            $baseJawaban = $latestVer?->jawaban ?: ('Informasi resmi mengenai ' . $faq->pertanyaan);
            // Bersihkan akhiran catatan revisi sebelumnya jika seeder dijalankan berulang
            $cleanBaseJawaban = preg_replace('/\s*\((Edisi awal|Pembaruan prosedur|Penyesuaian syarat|Penyempurnaan rincian|Revisi versi)[^)]*\)\.?$/u', '', trim($baseJawaban));
            $baseStatus = $latestVer?->status ?: 'Terbit';
            $basePustakaId = $latestVer?->tDaftarPustakas_id;
            $baseCatatan = $latestVer?->catatan;
            $baseTime = ($latestVer?->updated_at ?? $faq->created_at ?? Carbon::now())->copy();

            $versiTotal = in_array($faq->id, $singleVersionIds, true)
                ? 1
                : (($idx % 2 === 0) ? 5 : 4);

            $createdVersionIds = [];
            for ($v = 1; $v <= $versiTotal; $v++) {
                $versionId = $faq->id . '-V' . $v;
                $createdVersionIds[] = $versionId;
                $isLatest = ($v === $versiTotal);

                $versionJawaban = $isLatest
                    ? $cleanBaseJawaban
                    : rtrim((string) $cleanBaseJawaban, '.') . '.' . ($revisionNotes[$v] ?? " (Revisi versi {$v}).");

                $versionPustakaId = (!$isLatest && $v === 1 && ($idx % 3 === 0))
                    ? null
                    : $basePustakaId;

                $versionTime = $baseTime->copy()->subDays(($versiTotal - $v) * 14);
                $versionStatus = $isLatest
                    ? $baseStatus
                    : (strtolower((string) $baseStatus) === 'terbit' ? 'Terbit' : 'Draf');

                FaqVersion::updateOrCreate(
                    ['id' => $versionId],
                    [
                        'tDaftarPustakas_id' => $versionPustakaId,
                        'tFaqs_id'           => $faq->id,
                        'id_validator'       => ($isLatest && in_array($baseStatus, ['Diterima', 'Terbit', 'Ditolak'], true))
                            ? $validatorId
                            : (strtolower((string) $baseStatus) === 'terbit' ? $validatorId : null),
                        'jawaban'            => $versionJawaban,
                        'status'             => $versionStatus,
                        'catatan'            => $isLatest ? $baseCatatan : null,
                        'created_at'         => $versionTime,
                        'updated_at'         => $versionTime,
                    ]
                );
            }

            FaqVersion::where('tFaqs_id', $faq->id)
                ->whereNotIn('id', $createdVersionIds)
                ->delete();
            FaqVersion::syncLatestFlag($faq->id);
        }
    }
}

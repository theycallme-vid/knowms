<?php

namespace Database\Seeders;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user Data Entry & Validator dari database
        $deNadia  = User::where('email', 'nadia.putri@campus.ac.id')->first();
        $deAditya = User::where('email', 'aditya.pratama@campus.ac.id')->first();
        $deDewi   = User::where('email', 'dewi.lestari@campus.ac.id')->first();
        $deMain   = User::where('tRoles_id', 2)->first();

        $validator = User::where('tRoles_id', 3)->first();

        if (!$deMain || !$validator) {
            throw new \RuntimeException('UserSeeder harus dijalankan lebih dulu (user Data Entry / Validator belum ada).');
        }

        $validatorId = $validator->id;

        $nadiaId  = $deNadia?->id  ?? $deMain->id;
        $adityaId = $deAditya?->id ?? $deMain->id;
        $dewiId   = $deDewi?->id   ?? $deMain->id;

        // 1. Jalankan Seeder Sumber Pustaka (Link) & Kata Kunci terlebih dahulu
        $this->call([
            SumberPustakaSeeder::class,
            KataKunciSeeder::class,
        ]);

        $pustakaAkademik = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/pedoman-2026')->first()
            ?? DaftarPustaka::first();

        $pustakaBaak = DaftarPustaka::where('link', 'https://baak.kampus.ac.id/standar-operasional')->first()
            ?? $pustakaAkademik;

        // 2. Buat Tags Fakultas (sesuai PortalController::FAKULTAS_MAP) & Kategori Umum
        $tagsData = array_merge(
            ['Umum'],
            array_values(\App\Http\Controllers\PortalController::FAKULTAS_MAP),
            [
                'Akademik',
                'KRS',
                'Rencana studi',
                'Yudisium',
                'Surat akademik',
                'Layanan mahasiswa',
                'Transkrip',
                'Administrasi akademik',
                'Tugas akhir',
                'Pindah prodi',
                'Biaya Kuliah',
                'Fasilitas',
            ]
        );

        $tagMap = [];
        foreach ($tagsData as $tagName) {
            $tag = Tag::firstOrCreate(['nama' => $tagName]);
            $tagMap[$tagName] = $tag->id;
        }

        // 3. Data FAQ untuk Uji Coba (termasuk Antrean Validasi / Menunggu Validasi)
        $faqs = [
            // ── ANTREAN VALIDASI (STATUS: Menunggu Validasi) ──
            [
                'id'           => 'FAQ-0248',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Persyaratan yudisium Fakultas Teknik',
                'jawaban'      => 'Mahasiswa wajib menyelesaikan seluruh beban SKS tanpa nilai E, lulus ujian tugas akhir, bebas pinjaman perpustakaan serta laboratorium, dan mengunggah berkas sertifikat TOEFL serta transkrip akademik melalui portal Fakultas Teknik.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Akademik', 'Fakultas Teknik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 21, 10, 18, 0),
            ],
            [
                'id'           => 'FAQ-0247',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana cara melakukan pengisian KRS?',
                'jawaban'      => 'Pengisian KRS dilakukan secara daring melalui portal akademik mahasiswa sesuai jadwal perwalian. Pilih mata kuliah yang akan diambil berdasarkan jatah SKS, lalu ajukan persetujuan ke Dosen Wali.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 2,
                'tags'         => ['KRS', 'Rencana studi', 'Umum'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 21, 9, 45, 0),
            ],
            [
                'id'           => 'FAQ-0246',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan surat aktif kuliah?',
                'jawaban'      => 'Mahasiswa dapat mengajukan Surat Keterangan Aktif Kuliah melalui menu Layanan Surat di portal mahasiswa dengan melampirkan bukti pembayaran UKT semester berjalan.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Surat akademik', 'Layanan mahasiswa', 'Umum'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 15, 30, 0),
            ],
            [
                'id'           => 'FAQ-0245',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana cara mengajukan legalisasi transkrip nilai?',
                'jawaban'      => 'Ajukan permohonan legalisasi transkrip nilai melalui loket layanan fakultas atau unggah dokumen scan transkrip asli pada menu Legalisasi Dokumen untuk mendapatkan tanda tangan elektronik resmi.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Transkrip', 'Administrasi akademik', 'Umum'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 13, 12, 0),
            ],
            [
                'id'           => 'FAQ-0244',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Apa saja persyaratan pendaftaran tugas akhir?',
                'jawaban'      => 'Persyaratan meliputi perolehan minimal 120 SKS, telah lulus mata kuliah Metodologi Penelitian dan Kerja Praktik, serta memiliki IPK minimal 2.50.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 2,
                'tags'         => ['Tugas akhir', 'Teknik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 11, 20, 0),
            ],
            [
                'id'           => 'FAQ-0243',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan pindah program studi?',
                'jawaban'      => 'Pindah program studi hanya dapat diajukan setelah menempuh minimal 2 semester dan maksimal 4 semester, dengan persetujuan Ketua Program Studi asal dan tujuan serta Dekan Fakultas.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Pindah prodi', 'Akademik', 'Umum'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 8, 35, 0),
            ],
            [
                'id'           => 'FAQ-0242',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Mekanisme rekognisi SKS magang MBKM mandiri di Fakultas Ekonomi & Bisnis',
                'jawaban'      => 'Pengajuan konversi kegiatan magang mitra diajukan ke tim kurikulum departemen FEB untuk diverifikasi kesesuaian Capaian Pembelajaran Lulusan (CPL).',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Bisnis dan Ekonomika', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 14, 20, 0),
            ],
            [
                'id'           => 'FAQ-0241',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Syarat keikutsertaan peradilan semu (Moot Court) Fakultas Hukum',
                'jawaban'      => 'Terbuka untuk mahasiswa Fakultas Hukum semester 3 ke atas yang telah menempuh mata kuliah Hukum Acara Pidana dan Hukum Acara Perdata.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Hukum', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 10, 05, 0),
            ],

            // ── FAQ STATUS LAINNYA (Diterima, Terbit, Ditolak) ──
            [
                'id'           => 'FAQ-0101',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur permohonan keringanan dan cicilan UKT?',
                'jawaban'      => 'Permohonan cicilan pembayaran UKT dapat diajukan sebanyak maksimal 3 termin melalui Biro Keuangan Kampus dengan melampirkan dokumen pendukung kondisi ekonomi.',
                'status'       => 'Diterima',
                'versi_count'  => 1,
                'tags'         => ['Umum', 'Biaya Kuliah'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 15, 11, 10, 0),
            ],
            [
                'id'           => 'FAQ-0102',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Alur pendaftaran seminar proposal skripsi Fakultas Ekonomi & Bisnis',
                'jawaban'      => 'Mahasiswa FEB wajib menyelesaikan seminar proposal setelah draf bab 1-3 disetujui kedua dosen pembimbing dan telah lulus mata kuliah metodologi penelitian dengan nilai minimal B.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Bisnis dan Ekonomika', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Telah tayang untuk mahasiswa.',
                'created_at'   => Carbon::create(2026, 10, 14, 9, 30, 0),
            ],
            [
                'id'           => 'FAQ-0103',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana cara mendapatkan akses server GPU untuk riset kecerdasan buatan?',
                'jawaban'      => 'Mahasiswa Ilmu Komputer yang sedang menempuh tugas akhir dapat meminta akun cluster GPU melalui dosen pembimbing laboratorium kecerdasan komputasional.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Fasilitas'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Telah terverifikasi oleh laboratorium.',
                'created_at'   => Carbon::create(2026, 10, 13, 13, 45, 0),
            ],
            [
                'id'           => 'FAQ-0104',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Tata tertib dan persyaratan rotasi klinis kepaniteraan (koas) Fakultas Kedokteran',
                'jawaban'      => 'Mahasiswa harus telah dinyatakan lulus Sarjana Kedokteran, melunasi administrasi stase, dan mengikuti orientasi rumah sakit pendidikan jejaring.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Kedokteran', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Sesuai regulasi Konsil Kedokteran.',
                'created_at'   => Carbon::create(2026, 10, 12, 16, 00, 0),
            ],
            [
                'id'           => 'FAQ-0105',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Prosedur keselamatan kerja (K3) dan pembuangan limbah B3 laboratorium FST',
                'jawaban'      => 'Seluruh praktikan wajib mengenakan APD lengkap dan membuang sisa reagen kimia ke dalam wadah penampungan limbah khusus sesuai kode warna botol.',
                'status'       => 'Diterima',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknobiologi', 'Fasilitas'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 11, 10, 15, 0),
            ],
            [
                'id'           => 'FAQ-0106',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Ketentuan program magang industri bersertifikat kurikulum lama Fakultas Teknik',
                'jawaban'      => 'Mata kuliah magang kurikulum lama dikonversi secara ekuivalen maksimal 4 SKS dengan persetujuan ketua program studi.',
                'status'       => 'Ditolak',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Mohon perbarui agar mengacu pada kurikulum terbaru 2026.',
                'created_at'   => Carbon::create(2026, 10, 10, 8, 20, 0),
            ],
        ];

        foreach ($faqs as $item) {
            $faq = Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'id_dataentry' => $item['id_dataentry'],
                    'pertanyaan'   => $item['pertanyaan'],
                    'created_at'   => $item['created_at'],
                    'updated_at'   => $item['created_at'],
                ]
            );

            // Jika versi_count == 2, buat versi V1 terlebih dahulu lalu V2 sebagai versi terbaru
            $versiTotal = $item['versi_count'] ?? 1;
            for ($v = 1; $v <= $versiTotal; $v++) {
                $versionId = $item['id'] . '-V' . $v;
                $isLatest  = ($v === $versiTotal);

                FaqVersion::updateOrCreate(
                    ['id' => $versionId],
                    [
                        'tDaftarPustakas_id' => $item['pustaka_id'],
                        'tFaqs_id'           => $faq->id,
                        'id_validator'       => ($isLatest && in_array($item['status'], ['Diterima', 'Terbit', 'Ditolak'])) ? $validatorId : null,
                        'jawaban'            => $item['jawaban'],
                        'status'             => $isLatest ? $item['status'] : 'Draf',
                        'catatan'            => $item['catatan'],
                        'created_at'         => $item['created_at']->copy()->subHours($versiTotal - $v),
                        'updated_at'         => $item['created_at']->copy()->subHours($versiTotal - $v),
                    ]
                );
            }

            // Hubungkan Tag ke FAQ via tabel tdetail_faqs
            $tagIdsToSync = [];
            foreach ($item['tags'] as $tagName) {
                if (isset($tagMap[$tagName])) {
                    $tagIdsToSync[] = $tagMap[$tagName];
                }
            }
            $faq->tags()->sync($tagIdsToSync);
        }
    }
}

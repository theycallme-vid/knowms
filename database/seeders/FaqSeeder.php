<?php

namespace Database\Seeders;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari user Data Entry dan Validator untuk relasi
        $dataEntry = User::whereHas('role', fn ($q) => $q->where('nama', 'Data Entry'))->first()
                     ?? User::where('tRoles_id', 2)->first()
                     ?? User::first();

        $validator = User::whereHas('role', fn ($q) => $q->where('nama', 'Validator'))->first()
                     ?? User::where('tRoles_id', 3)->first()
                     ?? User::first();

        $dataEntryId = $dataEntry?->id ?? 1;
        $validatorId = $validator?->id ?? 1;

        // 1. Buat Daftar Pustaka Dummy
        $pustaka1 = DaftarPustaka::firstOrCreate(
            ['link' => 'https://akademik.kampus.ac.id/pedoman-2026'],
            ['type' => 'link']
        );

        $pustaka2 = DaftarPustaka::firstOrCreate(
            ['link' => 'https://baak.kampus.ac.id/standar-operasional'],
            ['type' => 'link']
        );

        // 2. Buat Tags Fakultas & Kategori
        $tagsData = [
            'Umum',
            'Teknik',
            'Ekonomi & Bisnis',
            'Hukum',
            'Ilmu Sosial',
            'Kedokteran',
            'Sains & Teknologi',
            'Ilmu Komputer',
            'Akademik',
            'KRS',
            'Yudisium',
            'Biaya Kuliah',
            'Fasilitas',
        ];

        $tagMap = [];
        foreach ($tagsData as $tagName) {
            $tag = Tag::firstOrCreate(['nama' => $tagName]);
            $tagMap[$tagName] = $tag->id;
        }

        // 3. Data FAQ Dummy
        $faqs = [
            // --- TEKNIK ---
            [
                'id' => 'FAQ-0248',
                'pertanyaan' => 'Bagaimana alur pendaftaran tugas akhir di Fakultas Teknik?',
                'jawaban' => 'Mahasiswa Fakultas Teknik yang telah menempuh minimal 130 SKS dapat mengajukan formulir usulan judul tugas akhir melalui portal akademik fakultas dan melampirkan transkrip nilai terakhir yang disetujui dosen wali.',
                'status' => 'Active',
                'tags' => ['Teknik', 'Akademik'],
                'catatan' => 'Informasi telah sesuai panduan akademik Fakultas Teknik edisi 2026.',
            ],
            [
                'id' => 'FAQ-0247',
                'pertanyaan' => 'Persyaratan dan kelengkapan berkas yudisium Fakultas Teknik',
                'jawaban' => 'Kelengkapan berkas mencakup bukti bebas pustaka fakultas & universitas, sertifikat TOEFL skor min. 475, lembar pengesahan skripsi berjilid, dan bukti bebas laboratorium.',
                'status' => 'Submitted',
                'tags' => ['Teknik', 'Yudisium'],
                'catatan' => null,
            ],
            [
                'id' => 'FAQ-0246',
                'pertanyaan' => 'Prosedur peminjaman alat laboratorium riset robotika & mekatronika',
                'jawaban' => 'Peminjaman alat lab harus menyertakan surat izin dari dosen pembimbing riset dan diajukan ke kepala laboratorium minimal H-3 sebelum penggunaan.',
                'status' => 'Approved',
                'tags' => ['Teknik', 'Fasilitas'],
                'catatan' => 'Disetujui untuk diterbitkan pada kalender semester genap.',
            ],
            [
                'id' => 'FAQ-0245',
                'pertanyaan' => 'Ketentuan program magang industri bersertifikat kurikulum lama Fakultas Teknik',
                'jawaban' => 'Mata kuliah magang kurikulum lama dikonversi secara ekuivalen maksimal 4 SKS dengan persetujuan ketua program studi.',
                'status' => 'InActive',
                'tags' => ['Teknik'],
                'catatan' => 'Dinonaktifkan karena berlakunya kurikulum baru 2026.',
            ],

            // --- UMUM ---
            [
                'id' => 'FAQ-0101',
                'pertanyaan' => 'Bagaimana cara dan alur pengisian Kartu Rencana Studi (KRS) online?',
                'jawaban' => 'Login ke portal mahasiswa, pilih menu Rencana Studi, pilih paket/mata kuliah sesuai jatah SKS berdasarkan IPS semester lalu, lalu submit dan konfirmasi ke dosen pembimbing akademik (dosen wali).',
                'status' => 'Active',
                'tags' => ['Umum', 'KRS', 'Akademik'],
                'catatan' => 'Sesuai kalender akademik nasional.',
            ],
            [
                'id' => 'FAQ-0102',
                'pertanyaan' => 'Bagaimana prosedur permohonan pengajuan cuti akademik?',
                'jawaban' => 'Pengajuan cuti dilakukan sebelum batas minggu ke-4 perkuliahan melalui SIM Akademik dengan melampirkan surat permohonan yang ditandatangani orang tua dan dosen wali.',
                'status' => 'Submitted',
                'tags' => ['Umum', 'Akademik'],
                'catatan' => null,
            ],
            [
                'id' => 'FAQ-0103',
                'pertanyaan' => 'Berapa cicilan maksimal dan tata cara permohonan keringanan UKT?',
                'jawaban' => 'Permohonan cicilan pembayaran UKT dapat diajukan sebanyak maksimal 3 termin melalui Biro Keuangan Kampus dengan melampirkan dokumen pendukung kondisi ekonomi.',
                'status' => 'Approved',
                'tags' => ['Umum', 'Biaya Kuliah'],
                'catatan' => 'Disetujui validator, menunggu jadwal aktivasi keuangan.',
            ],
            [
                'id' => 'FAQ-0104',
                'pertanyaan' => 'Prosedur legalisasi fotokopi ijazah secara offline di loket BAAK lama',
                'jawaban' => 'Layanan legalisasi offline lama telah dialihkan sepenuhnya secara daring melalui portal e-legalisir universitas.',
                'status' => 'InActive',
                'tags' => ['Umum'],
                'catatan' => 'Dinonaktifkan karena seluruh proses dialihkan ke digital.',
            ],

            // --- EKONOMI & BISNIS ---
            [
                'id' => 'FAQ-0301',
                'pertanyaan' => 'Alur pendaftaran seminar proposal skripsi Fakultas Ekonomi & Bisnis',
                'jawaban' => 'Mahasiswa FEB wajib menyelesaikan seminar proposal setelah draf bab 1-3 disetujui kedua dosen pembimbing dan telah lulus mata kuliah metodologi penelitian dengan nilai minimal B.',
                'status' => 'Active',
                'tags' => ['Ekonomi & Bisnis', 'Akademik'],
                'catatan' => 'Valid.',
            ],
            [
                'id' => 'FAQ-0302',
                'pertanyaan' => 'Mekanisme rekognisi SKS magang MBKM mandiri di FEB',
                'jawaban' => 'Pengajuan konversi kegiatan magang mitra non-Kementerian diajukan ke tim kurikulum departemen untuk diverifikasi kesesuaian Capaian Pembelajaran Lulusan (CPL).',
                'status' => 'Submitted',
                'tags' => ['Ekonomi & Bisnis'],
                'catatan' => null,
            ],

            // --- ILMU KOMPUTER ---
            [
                'id' => 'FAQ-0401',
                'pertanyaan' => 'Bagaimana cara mendapatkan akses server GPU untuk riset kecerdasan buatan?',
                'jawaban' => 'Mahasiswa Fasilkom yang sedang menempuh tugas akhir dapat meminta akun cluster GPU melalui dosen pembimbing laboratorium kecerdasan komputasional.',
                'status' => 'Active',
                'tags' => ['Ilmu Komputer', 'Fasilitas'],
                'catatan' => 'Telah terverifikasi oleh laboratorium.',
            ],
            [
                'id' => 'FAQ-0402',
                'pertanyaan' => 'Syarat sertifikasi internasional bidang keamanan siber dan cloud computing',
                'jawaban' => 'Fasilkom memfasilitasi voucher sertifikasi bagi mahasiswa aktif tingkat akhir yang telah lulus mata kuliah jaringan dan sistem operasi.',
                'status' => 'Submitted',
                'tags' => ['Ilmu Komputer'],
                'catatan' => null,
            ],

            // --- KEDOKTERAN ---
            [
                'id' => 'FAQ-0501',
                'pertanyaan' => 'Tata tertib dan persyaratan rotasi klinis kepaniteraan (koas) FK',
                'jawaban' => 'Mahasiswa harus telah dinyatakan lulus Sarjana Kedokteran, melunasi administrasi stase, dan mengikuti orientasi rumah sakit pendidikan jejaring.',
                'status' => 'Active',
                'tags' => ['Kedokteran', 'Akademik'],
                'catatan' => 'Sesuai regulasi Konsil Kedokteran.',
            ],
            [
                'id' => 'FAQ-0502',
                'pertanyaan' => 'Pendaftaran try-out dan bimbingan UKMPPD Fakultas Kedokteran',
                'jawaban' => 'Pendaftaran dibuka setiap triwulan melalui unit pendidikan kedokteran untuk mahasiswa koas periode berjalan.',
                'status' => 'Approved',
                'tags' => ['Kedokteran'],
                'catatan' => 'Validasi selesai.',
            ],

            // --- HUKUM ---
            [
                'id' => 'FAQ-0601',
                'pertanyaan' => 'Syarat keikutsertaan peradilan semu (Moot Court) Fakultas Hukum',
                'jawaban' => 'Terbuka untuk mahasiswa Fakultas Hukum semester 3 ke atas yang telah menempuh mata kuliah Hukum Acara Pidana dan Hukum Acara Perdata.',
                'status' => 'Submitted',
                'tags' => ['Hukum'],
                'catatan' => null,
            ],

            // --- SAINS & TEKNOLOGI ---
            [
                'id' => 'FAQ-0701',
                'pertanyaan' => 'Prosedur keselamatan kerja (K3) dan pembuangan limbah B3 laboratorium FST',
                'jawaban' => 'Seluruh praktikan wajib mengenakan APD lengkap dan membuang sisa reagen kimia ke dalam wadah penampungan limbah khusus sesuai kode warna botol.',
                'status' => 'Active',
                'tags' => ['Sains & Teknologi', 'Fasilitas'],
                'catatan' => 'Panduan wajib laboratorium sains.',
            ],
        ];

        foreach ($faqs as $item) {
            // Insert atau update FAQ
            $faq = Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'id_dataentry' => $dataEntryId,
                    'pertanyaan' => $item['pertanyaan'],
                ]
            );

            // Insert atau update FaqVersion
            $versionId = $item['id'] . '-V1';
            FaqVersion::updateOrCreate(
                ['id' => $versionId],
                [
                    'tDaftarPustakas_id' => $pustaka1->id,
                    'tFaqs_id' => $faq->id,
                    'id_validator' => in_array($item['status'], ['Approved', 'Active', 'Rejected']) ? $validatorId : null,
                    'jawaban' => $item['jawaban'],
                    'status' => $item['status'],
                    'catatan' => $item['catatan'],
                ]
            );

            // Hubungkan Tag ke FAQ via tabel tdetail_faqs
            $tagIdsToSync = [];
            foreach ($item['tags'] as $tagName) {
                if (isset($tagMap[$tagName])) {
                    $tagIdsToSync[] = $tagMap[$tagName];
                }
            }

            // Sync tags
            $faq->tags()->sync($tagIdsToSync);
        }
    }
}


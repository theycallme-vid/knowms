<?php

namespace Database\Seeders;

use App\Http\Controllers\PortalController;
use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FaqSeederReady extends Seeder
{
    /**
     * Seeder untuk SEMUA FAQ lintas fakultas yang langsung berstatus 'Active' (Sudah Terbit / ACC),
     * tanpa perlu menunggu persetujuan Validator.
     */
    public function run(): void
    {
        // 1. Ambil user Data Entry & Validator dari database
        $deNadia  = User::where('email', 'nadia.putri@campus.ac.id')->first();
        $deAditya = User::where('email', 'aditya.pratama@campus.ac.id')->first();
        $deDewi   = User::where('email', 'dewi.lestari@campus.ac.id')->first();
        $deMain   = User::where('tRoles_id', 2)->first() ?? User::first();

        $validator = User::where('tRoles_id', 3)->first() ?? User::first();

        if (!$deMain || !$validator) {
            throw new \RuntimeException('UserSeeder harus dijalankan lebih dulu (user Data Entry / Validator belum ada).');
        }

        $validatorId = $validator->id;
        $nadiaId     = $deNadia?->id  ?? $deMain->id;
        $adityaId    = $deAditya?->id ?? $deMain->id;
        $dewiId      = $deDewi?->id   ?? $deMain->id;

        // 2. Pastikan Sumber Pustaka & Kata Kunci siap
        $this->call([
            SumberPustakaSeeder::class,
            KataKunciSeeder::class,
        ]);

        $pustakaAkademik = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/pedoman-2026')->first()
            ?? DaftarPustaka::first();
        $pustakaBaak = DaftarPustaka::where('link', 'https://baak.kampus.ac.id/standar-operasional')->first()
            ?? $pustakaAkademik;
        $pustakaTa = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/pedoman-ta')->first()
            ?? $pustakaAkademik;
        $pustakaKrs = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/panduan-krs')->first()
            ?? $pustakaAkademik;
        $pustakaBeasiswa = DaftarPustaka::where('link', 'https://kemahasiswaan.kampus.ac.id/beasiswa')->first()
            ?? $pustakaAkademik;
        $pustakaKeuangan = DaftarPustaka::where('link', 'https://keuangan.kampus.ac.id/sop-ukt')->first()
            ?? $pustakaAkademik;

        // 3. Daftarkan Semua Tag Fakultas & Topik
        $allFacultyNames = array_values(PortalController::FAKULTAS_MAP);
        $tagsData = array_merge(
            ['Umum', 'Teknik', 'Fakultas Teknik'],
            $allFacultyNames,
            [
                'Akademik',
                'KRS',
                'Rencana Studi',
                'Cuti Akademik',
                'Yudisium',
                'Wisuda',
                'Surat Akademik',
                'Layanan Mahasiswa',
                'Transkrip Nilai',
                'Administrasi Akademik',
                'Tugas Akhir',
                'Pindah Prodi',
                'Biaya Kuliah',
                'Beasiswa',
                'Praktikum',
                'Laboratorium',
                'Kerja Praktik',
                'Magang MBKM',
                'Capstone Design',
                'Fasilitas Kampus',
                'Prestasi & Lomba',
                'Ujian Profesi',
            ]
        );

        $tagMap = [];
        foreach (array_unique($tagsData) as $tagName) {
            $tag = Tag::firstOrCreate(['nama' => $tagName]);
            $tagMap[$tagName] = $tag->id;
        }

        // 4. Kumpulan FAQ LENGKAP SEMUA FAKULTAS (Status: Active / Sudah Terbit Langsung)
        $faqs = [
            // =========================================================================
            // ── FAKULTAS TEKNIK (16) ────────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD01',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja persyaratan dan alur pendaftaran usulan judul Tugas Akhir (TA) di Fakultas Teknik?',
                'jawaban'    => 'Mahasiswa Fakultas Teknik yang telah menyelesaikan minimal 120 SKS dengan IPK >= 2.00 tanpa nilai E dapat mengajukan usulan judul TA melalui SIM Tugas Akhir FT. Langkahnya: (1) Mengunduh formulir pengajuan judul, (2) Melakukan konsultasi dan persetujuan calon Dosen Pembimbing, (3) Mengunggah formulir bersama transkrip nilai terakhir, (4) Menunggu verifikasi dari Koordinator TA Departemen.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD02',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Berapa batas minimal SKS yang harus diselesaikan sebelum mengambil Tugas Akhir atau Skripsi Teknik?',
                'jawaban'    => 'Batas minimal beban SKS untuk memprogram mata kuliah Tugas Akhir pada Kartu Rencana Studi (KRS) Fakultas Teknik adalah 120 SKS, telah lulus mata kuliah Metodologi Penelitian dan Kerja Praktik (KP), serta tidak memiliki tunggakan biaya perkuliahan.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'KRS'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD03',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana prosedur pendaftaran Seminar Proposal (Sempro) Tugas Akhir Fakultas Teknik?',
                'jawaban'    => 'Pendaftaran seminar proposal TA dilakukan setelah draf Bab 1-3 disetujui oleh Dosen Pembimbing Utama dan Pendamping. Berkas yang harus diunggah meliputi lembar persetujuan sempro bertanda tangan, bukti logbook bimbingan minimal 6 kali, naskah proposal format PDF, dan sertifikat kehadiran sempro mahasiswa lain minimal 5 kali.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD04',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Apa saja berkas kelengkapan untuk mendaftar Sidang Akhir atau Pendadaran Skripsi Fakultas Teknik?',
                'jawaban'    => 'Berkas pendaftaran sidang sarjana teknik meliputi: (1) Naskah lengkap laporan TA yang disetujui seluruh pembimbing, (2) Hasil cek similarity Turnitin maksimal 20%, (3) Transkrip nilai bebas nilai D berlebih dan tanpa nilai E, (4) Sertifikat TOEFL skor min. 475, (5) Bukti bebas laboratorium teknik, dan (6) Logbook bimbingan minimal 12 kali pertemuan.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Administrasi Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD05',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Berapa batas maksimal toleransi kesamaan (similarity score) Turnitin untuk naskah skripsi Fakultas Teknik?',
                'jawaban'    => 'Batas maksimal kesamaan (similarity index) Turnitin untuk draf Tugas Akhir Fakultas Teknik adalah 20% secara keseluruhan, dengan batas kesamaan pada masing-masing sumber tunggal tidak boleh melebihi 3%. Pengecekan dilakukan tanpa menyertakan daftar pustaka (exclude bibliography) dan kutipan langsung (exclude quotes).',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD06',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana aturan pelaksanaan Capstone Design Project berkelompok lintas disiplin di Fakultas Teknik?',
                'jawaban'    => 'Capstone Design Project dikerjakan dalam tim beranggotakan 3-5 mahasiswa dari program studi serumpun (Teknik Elektro, Mesin, Industri, Kimia, Informatika). Setiap kelompok wajib menghasilkan purwarupa (prototype) sistem fisik atau perangkat lunak terintegrasi yang menyelesaikan permasalahan nyata industri atau masyarakat mitra.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Capstone Design', 'Tugas Akhir'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD07',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur dan jadwal pendaftaran praktikum laboratorium semester gasal/genap FT?',
                'jawaban'    => 'Pendaftaran praktikum dibuka pada minggu pertama setelah KRS disetujui Dosen Wali. Mahasiswa login ke SIM Praktikum FT, memilih jadwal shift praktikum yang tidak bentrok dengan kuliah teori, membayar biaya modul (jika ada), dan wajib mengikuti briefing pra-praktikum yang diadakan masing-masing laboratorium.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Praktikum', 'Laboratorium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD08',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana prosedur peminjaman alat ukur (osiloskop, multimeter, sensor) dan modul lab teknik?',
                'jawaban'    => 'Peminjaman alat ukur dan instrumen laboratorium dilakukan melalui teknisi lab dengan mengisi Kartu Bon Peminjaman Alat dan menyerahkan Kartu Tanda Mahasiswa (KTM) aktif sebagai jaminan. Alat wajib dicek kondisi fisiknya saat peminjaman dan dikembalikan maksimal sebelum pukul 17.00 WIB pada hari yang sama.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD09',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa saja syarat dan tata tertib Keselamatan dan Kesehatan Kerja (K3) serta APD di laboratorium teknik?',
                'jawaban'    => 'Seluruh mahasiswa yang beraktivitas di laboratorium FT wajib: (1) Mengenakan jas laboratorium kancing tertutup, (2) Memakai sepatu tertutup berstandar keselamatan (safety shoes/sepatu tertutup), (3) Menggunakan kacamata pelindung (goggles) dan masker saat bekerja dengan bahan kimia atau permesinan, (4) Mengikat rambut panjang, dan (5) Mematuhi larangan makan/minum di area lab.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Praktikum'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD10',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Kapan pendaftaran Asisten Laboratorium (Aslab) Fakultas Teknik dibuka dan apa persyaratannya?',
                'jawaban'    => 'Rekrutmen Aslab FT dibuka setiap akhir semester genap dan gasal. Persyaratan umum: (1) Mahasiswa aktif minimal semester 5, (2) Telah menempuh mata kuliah praktikum terkait dengan nilai minimal A-, (3) IPK minimal 3.25, (4) Lulus tes tulis kompetensi materi lab, dan (5) Lulus tes microteaching serta wawancara komitmen.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD11',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja syarat kelayakan mengambil mata kuliah Kerja Praktik (KP) bagi mahasiswa Fakultas Teknik?',
                'jawaban'    => 'Mahasiswa berhak memprogram mata kuliah Kerja Praktik apabila telah menyelesaikan minimal 90 SKS, tidak berstatus cuti akademik, telah lulus mata kuliah keilmuan inti semester 1-4, dan memiliki persetujuan rencana magang dari Dosen Pembimbing Akademik.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD12',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Berapa lama durasi minimal pelaksanaan Kerja Praktik (KP) di industri yang diakui oleh Fakultas Teknik?',
                'jawaban'    => 'Durasi pelaksanaan Kerja Praktik di industri mitra minimal adalah 1 bulan penuh (minimal 160 jam kerja efektif) hingga maksimal 3 bulan untuk skema reguler. Untuk skema Magang MBKM, durasi berlangsung selama 1 semester (minimal 4-6 bulan atau setara 20 SKS).',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Magang MBKM'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD13',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana aturan sistem prasyarat mata kuliah (prerequisite chain) pada kurikulum Fakultas Teknik?',
                'jawaban'    => 'Mata kuliah yang memiliki prasyarat hanya dapat diprogram jika mahasiswa telah lulus mata kuliah prasyarat dengan nilai minimal D (atau minimal C untuk mata kuliah tertentu seperti Kalkulus dan Fisika Dasar). Sistem KRS otomatis memblokir pengambilan mata kuliah lanjutan jika prasyarat belum terpenuhi.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Akademik', 'KRS'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD14',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Berapa batas maksimal masa studi (Drop Out / DO) untuk jenjang Program Sarjana (S1) Fakultas Teknik?',
                'jawaban'    => 'Batas masa studi maksimal jenjang S1 Fakultas Teknik adalah 7 tahun akademik (14 semester), tidak termasuk masa cuti akademik resmi yang diizinkan (maksimal 2 semester). Evaluasi kelanjutan studi juga dilakukan di akhir semester 4 (minimal mengumpulkan 40 SKS lulus).',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Akademik', 'Administrasi Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD15',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa saja kelengkapan berkas yang harus diunggah untuk pendaftaran Yudisium Fakultas Teknik?',
                'jawaban'    => 'Kelengkapan berkas yudisium FT meliputi: (1) Lembar pengesahan naskah TA bertanda tangan lengkap, (2) Berita acara ujian sidang TA, (3) Sertifikat TOEFL minimal 475, (4) Bukti bebas laboratorium dari seluruh kepala lab teknik, (5) Bukti bebas pinjaman perpustakaan, (6) Ijazah SLTA legalisir, dan (7) Pasfoto resmi wisuda.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Wisuda'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD16',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur pengajuan bantuan pendanaan proposal lomba inovasi teknologi dan robotika (PKM/KRI) FT?',
                'jawaban'    => 'Tim mahasiswa dapat mengajukan bantuan dana pembinaan dan komponen riset melalui Unit Kemahasiswaan FT. Syaratnya: melampirkan proposal lomba yang disetujui Dosen Pembimbing, bukti surat undangan atau lolos tahap seleksi perlombaan, dan rincian anggaran biaya (RAB) komponen.',
                'tags'       => ['Fakultas Teknik', 'Teknik', 'Prestasi & Lomba', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaAkademik->id,
            ],

            // =========================================================================
            // ── FAKULTAS BISNIS DAN EKONOMIKA / FBE (13) ─────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD17',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana alur pendaftaran seminar proposal skripsi Fakultas Bisnis dan Ekonomika (FBE)?',
                'jawaban'    => 'Mahasiswa FBE yang telah mengumpulkan minimal 120 SKS dapat mendaftarkan seminar proposal setelah draf usulan disetujui Dosen Pembimbing. Berkas diajukan melalui SIM FBE dengan melampirkan transkrip nilai, bukti kelulusan mata kuliah Metode Penelitian Bisnis nilai min. C, dan naskah proposal lengkap.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD18',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana mekanisme rekognisi SKS magang MBKM mandiri di Fakultas Bisnis dan Ekonomika?',
                'jawaban'    => 'Kegiatan magang bersertifikat MSIB atau mitra industri FBE dapat dikonversi hingga 20 SKS mata kuliah peminatan/konsentrasi. Mahasiswa wajib mengunggah logbook mingguan, laporan akhir magang, dan evaluasi performa dari mentor perusahaan untuk diuji pada sidang konversi MBKM FBE.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Magang MBKM', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD19',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana cara mengakses fasilitas Laboratorium Pasar Modal dan Galeri Investasi BEI di FBE?',
                'jawaban'    => 'Galeri Investasi Bursa Efek Indonesia di FBE terbuka untuk seluruh mahasiswa aktif. Mahasiswa dapat memanfaatkan akun terminal riset data keuangan Bloomberg/Refinitiv, mengikuti simulasi trading saham, serta mendaftarkan akun rekening dana nasabah (RDN) resmi untuk edukasi pasar modal.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD20',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja konsentrasi penjurusan yang tersedia pada Program Studi Manajemen dan Akuntansi FBE?',
                'jawaban'    => 'Pada Program Studi Manajemen tersedia peminatan Manajemen Keuangan, Manajemen Pemasaran, Manajemen Sumber Daya Manusia, dan Manajemen Operasi. Pada Program Studi Akuntansi tersedia peminatan Akuntansi Keuangan, Auditing, Perpajakan, dan Sistem Informasi Akuntansi.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Akademik', 'Rencana Studi'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD21',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa syarat kelulusan uji kemahiran bahasa asing dan sertifikasi profesi kompetensi di FBE?',
                'jawaban'    => 'Lulusan FBE wajib memiliki skor TOEFL/EPT minimal 500 dan telah menempuh minimal 1 sertifikasi kompetensi profesi seperti Certified Risk Management Professional (CRMP), Certified Public Accountant (CPA Foundation), atau Brevet Pajak A/B terakreditasi.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Yudisium', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD22',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur pendaftaran ujian sidang komprehensif skripsi Fakultas Bisnis dan Ekonomika?',
                'jawaban'    => 'Pendaftaran sidang komprehensif skripsi dilakukan setelah laporan divalidasi oleh pembimbing, lolos Turnitin maksimal 20%, dan melampirkan bukti bebas pinjaman perpustakaan serta bukti lunas biaya pendidikan semester berjalan.',
                'tags'       => ['Fakultas Bisnis dan Ekonomika', 'Tugas Akhir', 'Yudisium'],
                'pustaka_id' => $pustakaTa->id,
            ],

            // =========================================================================
            // ── FAKULTAS FARMASI (11) ────────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD23',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana alur pendaftaran dan tata tertib praktikum steril dan farmasetika Fakultas Farmasi?',
                'jawaban'    => 'Praktikum di lingkungan Fakultas Farmasi mewajibkan mahasiswa menggunakan jas lab steril, masker, penutup kepala (hairnet), dan sarung tangan bebas serbuk. Pendaftaran praktikum dilakukan secara terpusat melalui portal akademik farmasi sesuai jadwal perwalian.',
                'tags'       => ['Fakultas Farmasi', 'Praktikum', 'Laboratorium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD24',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa saja persyaratan untuk melanjutkan ke Program Studi Profesi Apoteker (PSPA) Farmasi?',
                'jawaban'    => 'Persyaratan masuk PSPA meliputi: (1) Lulusan Sarjana Farmasi (S.Farm) dengan IPK minimal 3.00, (2) Lulus ujian seleksi masuk profesi apoteker (tes farmakologi, kimia farmasi, dan farmasetika), (3) Tes wawancara etika profesi, dan (4) Surat bebas narkoba dari rumah sakit pemerintah.',
                'tags'       => ['Fakultas Farmasi', 'Ujian Profesi', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD25',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana alur Praktek Kerja Profesi Apoteker (PKPA) di rumah sakit, industri farmasi, dan apotek?',
                'jawaban'    => 'Mahasiswa profesi apoteker wajib menjalani stase PKPA selama 1 tahun di 4 bidang: Apotek, Rumah Sakit, Industri Farmasi, dan Pemerintahan/PBF. Penempatan stase diatur oleh Unit Pengelola Praktik Kerja Farmasi bekerjasama dengan fasilitas kesehatan mitra.',
                'tags'       => ['Fakultas Farmasi', 'Kerja Praktik', 'Ujian Profesi'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD26',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana mekanisme ujian OSCE dan CBT Uji Kompetensi Mahasiswa Program Profesi Apoteker (UKMPPAI)?',
                'jawaban'    => 'UKMPPAI terdiri dari Computer-Based Test (CBT) dan Objective Structured Clinical Examination (OSCE) nasional. Mahasiswa wajib mengikuti bimbingan intensif dan try out internal fakultas sebelum terdaftar sebagai peserta ujian nasional.',
                'tags'       => ['Fakultas Farmasi', 'Ujian Profesi', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD27',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana prosedur penggunaan laboratorium instrumen analisis (HPLC, Spektrofotometri UV-Vis) di Farmasi?',
                'jawaban'    => 'Penggunaan instrumen canggih seperti HPLC, GC-MS, dan Spektro UV-Vis untuk penelitian skripsi harus didampingi laboran teknisi ahli. Mahasiswa wajib menyerahkan surat permohonan analisis sampel dari Dosen Pembimbing skripsi.',
                'tags'       => ['Fakultas Farmasi', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id' => $pustakaBaak->id,
            ],

            // =========================================================================
            // ── FAKULTAS HUKUM (12) ──────────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD28',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa saja syarat keikutsertaan peradilan semu (Moot Court) di Fakultas Hukum?',
                'jawaban'    => 'Moot Court dapat diikuti oleh mahasiswa semester 3 ke atas yang telah lulus mata kuliah Hukum Acara Pidana dan Hukum Acara Perdata dengan nilai minimal C. Mahasiswa akan berlatih menyusun berkas perkara, tuntutan, pledoi, dan berperan sebagai majelis hakim, jaksa penuntut, serta penasihat hukum.',
                'tags'       => ['Fakultas Hukum', 'Prestasi & Lomba', 'Praktikum'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD29',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur magang mahasiswa Fakultas Hukum di instansi penegak hukum dan kantor advokat?',
                'jawaban'    => 'Magang institusi hukum diajukan bagi mahasiswa semester 6 ke atas dengan minimal 100 SKS. Mahasiswa dapat memilih instansi mitra seperti Pengadilan Negeri, Kejaksaan, Kepolisian, Kantor Pertanahan (BPN), atau Law Firm terakreditasi dengan mengajukan proposal magang ke Bagian Kemahasiswaan FH.',
                'tags'       => ['Fakultas Hukum', 'Kerja Praktik', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD30',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja konsentrasi peminatan hukum yang dapat dipilih oleh mahasiswa Fakultas Hukum?',
                'jawaban'    => 'Fakultas Hukum menyediakan 4 konsentrasi keilmuan: Hukum Bisnis & Ekonomi, Hukum Pidana & Sistem Peradilan, Hukum Tata Negara & Administrasi Negara, serta Hukum Internasional & Hak Asasi Manusia.',
                'tags'       => ['Fakultas Hukum', 'Akademik', 'Rencana Studi'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD31',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana prosedur penyusunan naskah Skripsi Hukum dan batas minimal bimbingan akademik?',
                'jawaban'    => 'Penyusunan skripsi hukum mensyaratkan minimal 8 kali bimbingan intensif dengan Dosen Pembimbing yang tercatat pada logbook online. Metode penelitian dapat menggunakan pendekatan yuridis normatif maupun yuridis empiris.',
                'tags'       => ['Fakultas Hukum', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD32',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Apakah Fakultas Hukum menyelenggarakan Pendidikan Khusus Profesi Advokat (PKPA)?',
                'jawaban'    => 'Ya, FH bekerja sama dengan organisasi profesi advokat resmi (PERADI) menyelenggarakan program PKPA berkala bagi para lulusan Sarjana Hukum (S.H.) yang berminat menempuh karir sebagai advokat/pengacara.',
                'tags'       => ['Fakultas Hukum', 'Ujian Profesi', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaAkademik->id,
            ],

            // =========================================================================
            // ── FAKULTAS KEDOKTERAN (18) ─────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD33',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana tata tertib dan jadwal rotasi kepaniteraan klinik (koas) di Fakultas Kedokteran?',
                'jawaban'    => 'Rotasi klinik dokter muda berlangsung selama 2 tahun di Rumah Sakit Pendidikan Utama dan RS Jejaring. Mahasiswa harus menyelesaikan stase mayor (Penyakit Dalam, Bedah, Anak, Obgyn) dan stase minor sesuai buku logbook stase klinis.',
                'tags'       => ['Fakultas Kedokteran', 'Akademik', 'Ujian Profesi'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD34',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apa syarat kelulusan program Sarjana Kedokteran (S.Ked) sebelum masuk tahap profesi?',
                'jawaban'    => 'Mahasiswa wajib menyelesaikan seluruh modul blok kurikulum terintegrasi spiral (146 SKS), lulus seluruh ujian Objective Structured Clinical Examination (OSCE) komprehensif tahap akademik, menyelesaikan skripsi, dan lulus yudisium S.Ked.',
                'tags'       => ['Fakultas Kedokteran', 'Yudisium', 'Tugas Akhir'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD35',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana persiapan menghadapi Uji Kompetensi Mahasiswa Program Profesi Dokter (UKMPPD)?',
                'jawaban'    => 'Fakultas menyelenggarakan program karantina belajar dan try-out bertahap untuk Computer-Based Test (CBT) dan OSCE Nasional. Kelulusan UKMPPD menjadi syarat mutlak untuk memperoleh Sertifikat Profesi Dokter dan pengucapan Lafal Sumpah Dokter.',
                'tags'       => ['Fakultas Kedokteran', 'Ujian Profesi', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD36',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana tata tertib praktikum laboratorium Anatomi dan penggunaan preparat cadaver di FK?',
                'jawaban'    => 'Praktikum anatomi wajib mematuhi kode etik bioetika penghormatan terhadap cadaver. Mahasiswa dilarang keras mengambil foto/video di area laboratorium anatomi dan wajib mengenakan APD lengkap (jas lab, masker medis, sarung tangan bedah).',
                'tags'       => ['Fakultas Kedokteran', 'Praktikum', 'Laboratorium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],

            // =========================================================================
            // ── FAKULTAS PSIKOLOGI (15) ──────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD37',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana kode etik dan prosedur peminjaman alat tes psikodiagnostik di Fakultas Psikologi?',
                'jawaban'    => 'Peminjaman alat tes psikologi (seperti WAIS, WISC, TAT, Rorschach) hanya dilayani untuk keperluan mata kuliah praktikum asesmen atau penelitian skripsi berizin. Mahasiswa wajib menandatangani surat pernyataan menjaga kerahasiaan materi tes sesuai Kode Etik HIMPSI.',
                'tags'       => ['Fakultas Psikologi', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD38',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Apa saja bidang peminatan keilmuan yang dipelajari pada Fakultas Psikologi?',
                'jawaban'    => 'Fakultas Psikologi menyediakan peminatan Psikologi Klinis, Psikologi Industri & Organisasi (PIO), Psikologi Perkembangan, Psikologi Pendidikan, dan Psikologi Sosial.',
                'tags'       => ['Fakultas Psikologi', 'Akademik', 'Rencana Studi'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD39',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana alur praktikum konseling dan penggunaan ruang observasi satu arah (one-way mirror)?',
                'jawaban'    => 'Ruang observasi one-way mirror digunakan untuk latihan wawancara, observasi perilaku, dan simulasi konseling. Penggunaan ruangan dijadwalkan oleh koordinator laboratorium psikologi dan wajib di bawah supervisi dosen pembina praktikum.',
                'tags'       => ['Fakultas Psikologi', 'Praktikum', 'Laboratorium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD40',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana kelanjutan studi setelah meraih gelar Sarjana Psikologi (S.Psi) untuk berpraktik sebagai Psikolog?',
                'jawaban'    => 'Untuk berpraktik secara profesional dan memiliki Surat Tanda Registrasi (STR) Psikolog, lulusan S.Psi wajib menempuh Program Pendidikan Profesi Psikologi atau Magister Psikologi Profesi.',
                'tags'       => ['Fakultas Psikologi', 'Ujian Profesi', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],

            // =========================================================================
            // ── FAKULTAS TEKNOBIOLOGI (17) ───────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD41',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur keselamatan hayati (Biosafety) di laboratorium Fakultas Teknobiologi?',
                'jawaban'    => 'Laboratorium Teknobiologi menerapkan standar Biosafety Level 2 (BSL-2). Seluruh praktikan wajib menggunakan Biosafety Cabinet (BSC) saat menangani mikroorganisme hidup, melakukan sterilisasi autoclave sebelum dan sesudah praktikum, serta mematuhi dekontaminasi limbah biologi.',
                'tags'       => ['Fakultas Teknobiologi', 'Praktikum', 'Laboratorium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD42',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja cakupan riset unggulan pada Program Studi Bioteknologi FTB?',
                'jawaban'    => 'Riset unggulan mencakup Bioteknologi Medis & Farmasi (vaksin, diagnostik molekuler), Bioteknologi Pangan & Fermentasi, Bioteknologi Pertanian (kultur jaringan tanaman), serta Bioremediasi Lingkungan.',
                'tags'       => ['Fakultas Teknobiologi', 'Akademik', 'Tugas Akhir'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD43',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana cara mengajukan pemakaian alat Polymerase Chain Reaction (PCR) dan sekuensing DNA?',
                'jawaban'    => 'Mahasiswa TA yang membutuhkan fasilitas PCR, elektroforesis gel, atau spektrofotometer nano dapat mengajukan formulir pemakaian instrumen yang telah disetujui Dosen Pembimbing kepada teknisi laboratorium sentral biologi molekuler.',
                'tags'       => ['Fakultas Teknobiologi', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id' => $pustakaBaak->id,
            ],

            // =========================================================================
            // ── FAKULTAS INDUSTRI KREATIF / FIK (19) ─────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD44',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana ketentuan penyelenggaraan Pameran Karya Akhir (Exhibition) Tugas Akhir FIK?',
                'jawaban'    => 'Mahasiswa Desain Komunikasi Visual (DKV), Desain Fashion & Produk wajib menampilkan karya akhir purwarupa dalam pameran publik sebagai syarat penilaian sidang kelulusan. Penilaian melibatkan dosen penguji serta kurator/praktisi profesional industri kreatif.',
                'tags'       => ['Fakultas Industri Kreatif', 'Tugas Akhir', 'Yudisium'],
                'pustaka_id' => $pustakaTa->id,
            ],
            [
                'id'         => 'FAQ-RD45',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana tata cara peminjaman studio fotografi, lab animasi, dan workshop permodelan FIK?',
                'jawaban'    => 'Studio fotografi, audio visual, dan workshop permodelan 3D dapat dipinjam dengan mengajukan reservasi jadwal melalui portal kreatif FIK minimal H-1 sebelum penggunaan dengan menyerahkan kartu mahasiswa sebagai jaminan pemakaian inventaris.',
                'tags'       => ['Fakultas Industri Kreatif', 'Fasilitas Kampus', 'Laboratorium'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD46',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Apakah hasil karya desain mahasiswa FIK didaftarkan Hak Cipta / HKI secara resmi?',
                'jawaban'    => 'Ya, karya Tugas Akhir yang dinilai memiliki potensi komersial atau nilai orisinalitas tinggi akan difasilitasi oleh Sentra HKI universitas untuk pendaftaran Hak Cipta dan Desain Industri resmi ke DJKI Kemenkumham.',
                'tags'       => ['Fakultas Industri Kreatif', 'Prestasi & Lomba', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaAkademik->id,
            ],

            // =========================================================================
            // ── POLITEKNIK UBAYA (14) ────────────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD47',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana sistem pembelajaran vokasi dan jadwal praktik industri di Politeknik Ubaya?',
                'jawaban'    => 'Politeknik menerapkan sistem kurikulum berbasis praktik 60% dan teori 40%. Mahasiswa dibekali keterampilan terapan langsung di laboratorium modern, bengkel kerja, serta program magang industri bersertifikat selama minimal 1 semester penuh.',
                'tags'       => ['Politeknik Ubaya', 'Praktikum', 'Kerja Praktik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD48',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Apa saja uji sertifikasi kompetensi profesi BNSP yang wajib diikuti mahasiswa Politeknik?',
                'jawaban'    => 'Mahasiswa tingkat akhir Politeknik wajib mengikuti uji kompetensi Lembaga Sertifikasi Profesi (LSP) berlisensi BNSP sesuai bidang jurusannya (seperti Akuntansi Terapan, Manajemen Pemasaran Digital, Sekretaris Eksekutif, dan Perpajakan).',
                'tags'       => ['Politeknik Ubaya', 'Ujian Profesi', 'Yudisium'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD49',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana format Laporan Tugas Akhir Terapan bagi mahasiswa vokasi Diploma 3 (D3)?',
                'jawaban'    => 'Tugas akhir vokasi berorientasi pada penyelesaian masalah terapan (problem-solving) pada perusahaan/organisasi mitra, berupa perancangan sistem kerja terapan, analisis kepatuhan perpajakan, atau prototipe rancangan kerja nyata.',
                'tags'       => ['Politeknik Ubaya', 'Tugas Akhir', 'Akademik'],
                'pustaka_id' => $pustakaTa->id,
            ],

            // =========================================================================
            // ── UMUM / SELURUH FAKULTAS (10) ─────────────────────────────────────────
            // =========================================================================
            [
                'id'         => 'FAQ-RD50',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana cara dan panduan pengisian Kartu Rencana Studi (KRS) online setiap semester?',
                'jawaban'    => 'Login ke portal akademik mahasiswa, pilih menu Rencana Studi, pilih paket atau mata kuliah sesuai jatah SKS berdasarkan Indeks Prestasi Semester (IPS) lalu submit dan lakukan konfirmasi persetujuan ke Dosen Wali.',
                'tags'       => ['Umum', 'KRS', 'Rencana Studi', 'Akademik'],
                'pustaka_id' => $pustakaKrs->id,
            ],
            [
                'id'         => 'FAQ-RD51',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana alur permohonan pengajuan cicilan atau penundaan pembayaran UKT?',
                'jawaban'    => 'Permohonan cicilan pembayaran UKT diajukan sebelum batas akhir registrasi melalui SIM Keuangan Kampus dengan mengisi formulir permohonan dan melampirkan bukti slip penghasilan orang tua atau surat keterangan kendala ekonomi.',
                'tags'       => ['Umum', 'Biaya Kuliah', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaKeuangan->id,
            ],
            [
                'id'         => 'FAQ-RD52',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur permohonan cuti akademik sementara dan batas maksimal cuti?',
                'jawaban'    => 'Cuti akademik dapat diajukan oleh mahasiswa aktif yang telah menempuh minimal 2 semester awal. Pengajuan diajukan sebelum batas akhir registrasi semester dengan persetujuan Dosen Wali dan Dekan. Maksimal cuti akademik adalah 2 semester selama masa studi.',
                'tags'       => ['Umum', 'Cuti Akademik', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD53',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana cara mengajukan Surat Keterangan Aktif Kuliah untuk tunjangan atau beasiswa?',
                'jawaban'    => 'Mahasiswa aktif dapat mengajukan surat keterangan kuliah secara daring melalui menu Layanan Surat portal mahasiswa dengan melampirkan bukti lunas UKT semester berjalan. Surat dilengkapi tanda tangan digital resmi dan QR Code pengesahan.',
                'tags'       => ['Umum', 'Surat Akademik', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD54',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana cara mengajukan legalisasi transkrip nilai dan ijazah secara daring?',
                'jawaban'    => 'Ajukan permohonan legalisasi melalui portal e-legalisir universitas dengan mengunggah scan dokumen asli. Dokumen yang telah diverifikasi BAAK akan dibubuhi tanda tangan elektronik resmi atau dapat dicetak dan dikirimkan via kurir.',
                'tags'       => ['Umum', 'Transkrip Nilai', 'Administrasi Akademik'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD55',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Apa saja jenis beasiswa yang tersedia bagi mahasiswa dan bagaimana cara mendaftarnya?',
                'jawaban'    => 'Universitas menyediakan Beasiswa Prestasi Akademik, Beasiswa Bantuan Finansial, Beasiswa Atlet/Seni, Beasiswa Alumni, dan KIP Kuliah. Informasi jadwal seleksi dan pendaftaran diumumkan secara berkala melalui laman kemahasiswaan.',
                'tags'       => ['Umum', 'Beasiswa', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaBeasiswa->id,
            ],
            [
                'id'         => 'FAQ-RD56',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana cara mereset password akun email kampus dan portal Single Sign-On (SSO)?',
                'jawaban'    => 'Mahasiswa dapat melakukan reset mandiri melalui menu Lupa Password pada laman login SSO menggunakan email pemulihan terdaftar, atau menghubungi Helpdesk Direktorat Sistem Informasi (DSI) dengan membawa KTM fisik.',
                'tags'       => ['Umum', 'Fasilitas Kampus', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaBaak->id,
            ],
            [
                'id'         => 'FAQ-RD57',
                'de_id'      => $adityaId,
                'pertanyaan' => 'Bagaimana prosedur peminjaman buku dan akses jurnal internasional perpustakaan pusat?',
                'jawaban'    => 'Seluruh mahasiswa aktif otomatis terdaftar sebagai anggota perpustakaan menggunakan KTM. Mahasiswa dapat meminjam hingga 5 buku fisik selama 14 hari serta mengakses database jurnal ScienceDirect, IEEE, Scopus, dan Springer via jaringan kampus atau remote proxy.',
                'tags'       => ['Umum', 'Fasilitas Kampus', 'Layanan Mahasiswa'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD58',
                'de_id'      => $dewiId,
                'pertanyaan' => 'Bagaimana prosedur pindah program studi antar fakultas di lingkungan universitas?',
                'jawaban'    => 'Pindah program studi antar fakultas diajukan pada akhir semester 2 atau 4 dengan syarat: IPK minimal 3.00, memenuhi persyaratan mata pelajaran jurusan asal, lolos wawancara pada fakultas tujuan, serta disetujui oleh kedua Dekan Fakultas.',
                'tags'       => ['Umum', 'Pindah Prodi', 'Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
            [
                'id'         => 'FAQ-RD59',
                'de_id'      => $nadiaId,
                'pertanyaan' => 'Bagaimana ketentuan presensi kehadiran kuliah minimal untuk mengikuti Ujian Akhir Semester (UAS)?',
                'jawaban'    => 'Mahasiswa wajib menghadiri minimal 75% dari total tatap muka perkuliahan per mata kuliah (maksimal 3-4 kali tidak hadir). Mahasiswa yang kehadirannya kurang dari 75% otomatis dinyatakan tidak berhak mengikuti UAS dan memperoleh nilai E.',
                'tags'       => ['Umum', 'Akademik', 'Administrasi Akademik'],
                'pustaka_id' => $pustakaAkademik->id,
            ],
        ];

        // 5. Eksekusi Penyimpanan: SEMUA FAQ LANGSUNG TERBIT (3 FAQ = 1 versi, sisanya 4–5 versi)
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();

        $now = Carbon::now();
        $singleVersionIndexes = [2, 6, 9];
        $revisionNotes = [
            1 => ' (Edisi awal panduan akademik sebelum penyesuaian alur daring).',
            2 => ' (Pembaruan prosedur pengajuan melalui portal layanan mahasiswa).',
            3 => ' (Penyesuaian syarat administrasi dan jadwal verifikasi fakultas).',
            4 => ' (Penyempurnaan rincian dokumen pendukung dan ketentuan terbaru).',
        ];

        foreach (array_values($faqs) as $idx => $item) {
            $faq = Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'id_dataentry' => $item['de_id'],
                    'pertanyaan'   => $item['pertanyaan'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]
            );

            $versiTotal = in_array($idx, $singleVersionIndexes, true)
                ? 1
                : (($idx % 2 === 0) ? 5 : 4);

            $createdVersionIds = [];
            for ($v = 1; $v <= $versiTotal; $v++) {
                $versionId = $item['id'] . '-V' . $v;
                $createdVersionIds[] = $versionId;
                $isLatest = ($v === $versiTotal);

                $versionJawaban = $isLatest
                    ? $item['jawaban']
                    : rtrim((string) $item['jawaban'], '.') . '.' . ($revisionNotes[$v] ?? " (Revisi versi {$v}).");

                $versionPustakaId = (!$isLatest && $v === 1 && ($idx % 3 === 0))
                    ? null
                    : $item['pustaka_id'];

                $versionTime = $now->copy()->subDays(($versiTotal - $v) * 14);

                FaqVersion::updateOrCreate(
                    ['id' => $versionId],
                    [
                        'tDaftarPustakas_id' => $versionPustakaId,
                        'tFaqs_id'           => $faq->id,
                        'id_validator'       => $validatorId,
                        'jawaban'            => $versionJawaban,
                        'status'             => 'Terbit',
                        'catatan'            => $isLatest ? 'Terbit otomatis (Siap Pakai / Seeder Ready).' : null,
                        'created_at'         => $versionTime,
                        'updated_at'         => $versionTime,
                    ]
                );
            }

            FaqVersion::where('tFaqs_id', $faq->id)
                ->whereNotIn('id', $createdVersionIds)
                ->delete();

            // Sinkronkan tags
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

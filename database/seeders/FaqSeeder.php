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
        $deMain   = User::where('tRoles_id', 2)->first() ?? User::first();

        $validator = User::where('tRoles_id', 3)->first() ?? User::first();

        if (!$deMain || !$validator) {
            throw new \RuntimeException('UserSeeder harus dijalankan lebih dulu (user Data Entry / Validator belum ada).');
        }

        $validatorId = $validator->id;

        $nadiaId  = $deNadia?->id  ?? $deMain->id;
        $adityaId = $deAditya?->id ?? $deMain->id;
        $dewiId   = $deDewi?->id   ?? $deMain->id;

        // Pastikan skema tabel tfaqsversion & tdaftarpustaka terbaru
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();

        // 1. Jalankan Seeder Sumber Pustaka (Link) & Kata Kunci terlebih dahulu
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

        // 2. Buat Tags Fakultas & Kategori
        $tagsData = array_merge(
            ['Umum', 'Teknik', 'Fakultas Teknik'],
            array_values(\App\Http\Controllers\PortalController::FAKULTAS_MAP),
            [
                'Akademik',
                'KRS',
                'Rencana Studi',
                'Rencana studi',
                'Cuti Akademik',
                'Yudisium',
                'Wisuda',
                'Surat Akademik',
                'Surat akademik',
                'Layanan Mahasiswa',
                'Layanan mahasiswa',
                'Transkrip Nilai',
                'Transkrip',
                'Administrasi Akademik',
                'Administrasi akademik',
                'Tugas Akhir',
                'Tugas akhir',
                'Pindah Prodi',
                'Pindah prodi',
                'Biaya Kuliah',
                'Fasilitas',
                'Fasilitas Kampus',
                'Praktikum',
                'Laboratorium',
                'Kerja Praktik',
                'Magang MBKM',
                'Capstone Design',
                'Beasiswa',
                'Prestasi & Lomba',
            ]
        );

        $tagMap = [];
        foreach (array_unique($tagsData) as $tagName) {
            $tag = Tag::firstOrCreate(['nama' => $tagName]);
            $tagMap[$tagName] = $tag->id;
        }

        // 3. Kumpulan Data FAQ Lengkap (Fokus Terbanyak: Fakultas Teknik untuk Pengujian Algoritma)
        $faqs = [
            // =========================================================================
            // ── FAKULTAS TEKNIK: TUGAS AKHIR, SKRIPSI & CAPSTONE DESIGN ─────────────
            // =========================================================================
            [
                'id'           => 'FAQ-0001',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Apa saja persyaratan dan alur pendaftaran usulan judul Tugas Akhir (TA) di Fakultas Teknik?',
                'jawaban'      => 'Mahasiswa Fakultas Teknik yang telah menyelesaikan minimal 120 SKS dengan IPK >= 2.00 tanpa nilai E dapat mengajukan usulan judul TA melalui SIM Tugas Akhir FT. Langkahnya: (1) Mengunduh formulir pengajuan judul, (2) Melakukan konsultasi dan persetujuan calon Dosen Pembimbing, (3) Mengunggah formulir bersama transkrip nilai terakhir, (4) Menunggu verifikasi dari Koordinator TA Departemen.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => 'Pedoman TA Fakultas Teknik 2026.',
                'created_at'   => Carbon::create(2026, 10, 25, 9, 0, 0),
            ],
            [
                'id'           => 'FAQ-0002',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Berapa batas minimal SKS yang harus diselesaikan sebelum mengambil Tugas Akhir atau Skripsi Teknik?',
                'jawaban'      => 'Batas minimal beban SKS untuk memprogram mata kuliah Tugas Akhir pada Kartu Rencana Studi (KRS) Fakultas Teknik adalah 120 SKS, telah lulus mata kuliah Metodologi Penelitian dan Kerja Praktik (KP), serta tidak memiliki tunggakan biaya perkuliahan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'KRS'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 24, 14, 15, 0),
            ],
            [
                'id'           => 'FAQ-0003',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana prosedur pendaftaran Seminar Proposal (Sempro) Tugas Akhir Fakultas Teknik?',
                'jawaban'      => 'Pendaftaran seminar proposal TA dilakukan setelah draf Bab 1-3 disetujui oleh Dosen Pembimbing Utama dan Pendamping. Berkas yang harus diunggah meliputi lembar persetujuan sempro bertanda tangan, bukti logbook bimbingan minimal 6 kali, naskah proposal format PDF, dan sertifikat kehadiran sempro mahasiswa lain minimal 5 kali.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => 'Sudah diverifikasi validator.',
                'created_at'   => Carbon::create(2026, 10, 23, 11, 30, 0),
            ],
            [
                'id'           => 'FAQ-0004',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Apa saja berkas kelengkapan untuk mendaftar Sidang Akhir atau Pendadaran Skripsi Fakultas Teknik?',
                'jawaban'      => 'Berkas pendaftaran sidang sarjana teknik meliputi: (1) Naskah lengkap laporan TA yang disetujui seluruh pembimbing, (2) Hasil cek similarity Turnitin maksimal 20%, (3) Transkrip nilai bebas nilai D berlebih dan tanpa nilai E, (4) Sertifikat TOEFL skor min. 475, (5) Bukti bebas laboratorium teknik, dan (6) Logbook bimbingan minimal 12 kali pertemuan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Administrasi Akademik'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 22, 16, 20, 0),
            ],
            [
                'id'           => 'FAQ-0005',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana ketentuan dan alur pergantian dosen pembimbing Tugas Akhir di Fakultas Teknik?',
                'jawaban'      => 'Pergantian dosen pembimbing dapat diajukan jika dosen bersangkutan tugas belajar, sakit lebih dari 3 bulan, atau terjadi perubahan arah topik penelitian. Mahasiswa mengajukan formulir permohonan pergantian pembimbing yang diketahui dosen pembimbing lama dan baru kepada Ketua Program Studi untuk diterbitkan SK revisi Dekan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 22, 10, 45, 0),
            ],
            [
                'id'           => 'FAQ-0006',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Di mana mahasiswa dapat mengunduh format penulisan dan template LaTeX atau Word laporan TA Fakultas Teknik?',
                'jawaban'      => 'Template resmi penulisan Tugas Akhir Fakultas Teknik berstandar IEEE/APA dapat diunduh melalui portal resmi https://teknik.kampus.ac.id/unduhan/panduan-ta. Tersedia format Microsoft Word (.docx) dan bundle template LaTeX siap kompilasi (Overleaf template).',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => 'Tautan template diverifikasi aktif.',
                'created_at'   => Carbon::create(2026, 10, 21, 15, 10, 0),
            ],
            [
                'id'           => 'FAQ-0007',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Berapa batas maksimal toleransi kesamaan (similarity score) Turnitin untuk naskah skripsi Fakultas Teknik?',
                'jawaban'      => 'Batas maksimal kesamaan (similarity index) Turnitin untuk draf Tugas Akhir Fakultas Teknik adalah 20% secara keseluruhan, dengan batas kesamaan pada masing-masing sumber tunggal tidak boleh melebihi 3%. Pengecekan dilakukan tanpa menyertakan daftar pustaka (exclude bibliography) dan kutipan langsung (exclude quotes).',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 21, 13, 0, 0),
            ],
            [
                'id'           => 'FAQ-0008',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur perpanjangan masa Tugas Akhir jika tidak selesai dalam 1 semester di Fakultas Teknik?',
                'jawaban'      => 'Jika TA belum selesai dalam 1 semester, mahasiswa wajib memprogram kembali mata kuliah Perpanjangan TA pada KRS semester berikutnya dengan persetujuan Dosen Pembimbing. Mahasiswa harus mengunggah Laporan Kemajuan TA (progress report) minimal mencapai 50% untuk mendapatkan rekomendasi perpanjangan dari Koordinator TA.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'KRS'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 14, 25, 0),
            ],
            [
                'id'           => 'FAQ-0009',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Apakah mahasiswa Fakultas Teknik wajib mempublikasikan artikel ilmiah di jurnal atau prosiding untuk syarat kelulusan?',
                'jawaban'      => 'Ya, mahasiswa Program Sarjana (S1) Fakultas Teknik wajib memiliki minimal 1 naskah publikasi ilmiah dari hasil penelitian TA yang telah berstatus accepted/published pada jurnal nasional terakreditasi SINTA (minimal SINTA 4) atau prosiding seminar nasional/internasional terindeks Scopus/IEEE.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 9, 30, 0),
            ],
            [
                'id'           => 'FAQ-0010',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana aturan pelaksanaan Capstone Design Project berkelompok lintas disiplin di Fakultas Teknik?',
                'jawaban'      => 'Capstone Design Project dikerjakan dalam tim beranggotakan 3-5 mahasiswa dari program studi serumpun (Teknik Elektro, Mesin, Industri, Kimia, Informatika). Setiap kelompok wajib menghasilkan purwarupa (prototype) sistem fisik atau perangkat lunak terintegrasi yang menyelesaikan permasalahan nyata industri atau masyarakat mitra.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Capstone Design', 'Tugas Akhir'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 19, 15, 0, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: PRAKTIKUM & FASILITAS LABORATORIUM ──────────────────
            // =========================================================================
            [
                'id'           => 'FAQ-0011',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur dan jadwal pendaftaran praktikum laboratorium semester gasal/genap FT?',
                'jawaban'      => 'Pendaftaran praktikum dibuka pada minggu pertama setelah KRS disetujui Dosen Wali. Mahasiswa login ke SIM Praktikum FT, memilih jadwal shift praktikum yang tidak bentrok dengan kuliah teori, membayar biaya modul (jika ada), dan wajib mengikuti briefing pra-praktikum yang diadakan masing-masing laboratorium.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Praktikum', 'Laboratorium'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 19, 11, 20, 0),
            ],
            [
                'id'           => 'FAQ-0012',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana prosedur peminjaman alat ukur (osiloskop, multimeter, modul mikrokontroler) di laboratorium teknik?',
                'jawaban'      => 'Peminjaman alat ukur dan instrumen laboratorium dilakukan melalui teknisi lab dengan mengisi Kartu Bon Peminjaman Alat dan menyerahkan Kartu Tanda Mahasiswa (KTM) aktif sebagai jaminan. Alat wajib dicek kondisi fisiknya saat peminjaman dan dikembalikan maksimal sebelum pukul 17.00 WIB pada hari yang sama.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Fasilitas'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 18, 14, 40, 0),
            ],
            [
                'id'           => 'FAQ-0013',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Apa saja syarat dan tata tertib Keselamatan dan Kesehatan Kerja (K3) serta APD di laboratorium teknik?',
                'jawaban'      => 'Seluruh mahasiswa yang beraktivitas di laboratorium FT wajib: (1) Mengenakan jas laboratorium kancing tertutup, (2) Memakai sepatu tertutup berstandar keselamatan (safety shoes/sepatu tertutup), (3) Menggunakan kacamata pelindung (goggles) dan masker saat bekerja dengan bahan kimia atau permesinan, (4) Mengikat rambut panjang, dan (5) Mematuhi larangan makan/minum di area lab.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Praktikum'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'SOP K3 Laboratorium Fakultas Teknik.',
                'created_at'   => Carbon::create(2026, 10, 18, 10, 10, 0),
            ],
            [
                'id'           => 'FAQ-0014',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana ketentuan inhal praktikum jika mahasiswa berhalangan hadir karena sakit atau izin resmi?',
                'jawaban'      => 'Inhal (praktikum susulan) hanya diberikan kepada mahasiswa yang memiliki surat keterangan sakit resmi dokter atau surat tugas resmi kampus. Surat izin harus diserahkan kepada Kepala Laboratorium maksimal 3 hari kerja setelah ketidakhadiran. Pelaksanaan inhal diatur di akhir modul semester dengan biaya administrasi bahan habis pakai.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Praktikum', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 16, 50, 0),
            ],
            [
                'id'           => 'FAQ-0015',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana alur pengajuan izin lembur riset dan pemakaian laboratorium di luar jam operasional kampus FT?',
                'jawaban'      => 'Izin lembur laboratorium riset untuk mahasiswa TA diajukan minimal H-2 kerja sebelum kegiatan dengan mengisi formulir Surat Izin Lembur Lab. Formulir harus ditandatangani Dosen Pembimbing TA, Kepala Laboratorium, dan Wakil Dekan Bidang Operasional, serta disertai nomor kontak darurat pengawas lab.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 13, 15, 0),
            ],
            [
                'id'           => 'FAQ-0016',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur penggantian komponen atau peralatan laboratorium teknik yang mengalami kerusakan saat praktikum?',
                'jawaban'      => 'Kerusakan akibat kelalaian (human error) praktikan wajib diganti dengan spesifikasi, merk, dan tipe yang sama persis atau membayar biaya penggantian yang ditetapkan oleh kepala laboratorium dalam jangka waktu maksimal 7 hari kerja agar nilai praktikum tidak ditangguhkan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Praktikum'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 15, 40, 0),
            ],
            [
                'id'           => 'FAQ-0017',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Kapan pendaftaran Asisten Laboratorium (Aslab) Fakultas Teknik dibuka dan apa persyaratannya?',
                'jawaban'      => 'Rekrutmen Aslab FT dibuka setiap akhir semester genap dan gasal. Persyaratan umum: (1) Mahasiswa aktif minimal semester 5, (2) Telah menempuh mata kuliah praktikum terkait dengan nilai minimal A-, (3) IPK minimal 3.25, (4) Lulus tes tulis kompetensi materi lab, dan (5) Lulus tes microteaching serta wawancara komitmen.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 9, 20, 0),
            ],
            [
                'id'           => 'FAQ-0018',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana cara mengakses komputer workstation simulasi CAD/CAM/CAE dan software engineering berlisensi FT?',
                'jawaban'      => 'Akses workstation lab komputasi teknik dapat digunakan menggunakan akun SSO (Single Sign-On) universitas. Fakultas Teknik menyediakan lisensi resmi kampus untuk MATLAB, ANSYS, AutoCAD, SolidWorks, ASPEN HYSYS, dan Cisco Packet Tracer yang juga dapat dipinjam lisensinya via VPN kampus.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Fasilitas Kampus'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 15, 14, 0, 0),
            ],
            [
                'id'           => 'FAQ-0019',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Apakah Fakultas Teknik memfasilitasi program sertifikasi internasional keahlian software teknik?',
                'jawaban'      => 'Ya, FT secara berkala menyelenggarakan workshop dan sertifikasi profesional seperti Certified SolidWorks Associate (CSWA), Autodesk Certified Professional, CCNA, dan sertifikasi K3 Umum BNSP bekerjasama dengan lembaga sertifikasi terakreditasi dengan subsidi biaya untuk mahasiswa.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Akademik', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 15, 10, 30, 0),
            ],
            [
                'id'           => 'FAQ-0020',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana tata cara pembuangan limbah bahan kimia dan pelumas bekas laboratorium FT sesuai standar lingkungan?',
                'jawaban'      => 'Limbah praktikum tidak boleh dibuang ke saluran air biasa. Limbah cair pelarut organik, asam/basa pekat, dan oli mesin wajib ditampung ke dalam jerigen penampungan khusus B3 berlabel warna sesuai kategori limbah, yang kemudian akan diolah oleh Instalasi Pengolahan Air Limbah (IPAL) Fakultas Teknik.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Fasilitas'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 14, 16, 10, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: KERJA PRAKTIK (KP), MAGANG INDUSTRI & MBKM ─────────
            // =========================================================================
            [
                'id'           => 'FAQ-0021',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Apa saja syarat kelayakan mengambil mata kuliah Kerja Praktik (KP) bagi mahasiswa Fakultas Teknik?',
                'jawaban'      => 'Mahasiswa berhak memprogram mata kuliah Kerja Praktik apabila telah menyelesaikan minimal 90 SKS, tidak berstatus cuti akademik, telah lulus mata kuliah keilmuan inti semester 1-4, dan memiliki persetujuan rencana magang dari Dosen Pembimbing Akademik.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 14, 11, 45, 0),
            ],
            [
                'id'           => 'FAQ-0022',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur pembuatan surat pengantar survei industri dan permohonan magang ke perusahaan?',
                'jawaban'      => 'Mahasiswa mengajukan permohonan surat pengantar melalui loket tata usaha fakultas daring dengan mengunggah proposal singkat magang, daftar nama anggota kelompok, dan data lengkap kontak perusahaan (nama HRD/Divisi, alamat kantor, email resmi). Surat pengantar Dekan diterbitkan maksimal 2 hari kerja.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Surat Akademik'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 13, 15, 20, 0),
            ],
            [
                'id'           => 'FAQ-0023',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Berapa lama durasi minimal pelaksanaan Kerja Praktik (KP) di industri yang diakui oleh Fakultas Teknik?',
                'jawaban'      => 'Durasi pelaksanaan Kerja Praktik di industri mitra minimal adalah 1 bulan penuh (minimal 160 jam kerja efektif) hingga maksimal 3 bulan untuk skema reguler. Untuk skema Magang MBKM, durasi berlangsung selama 1 semester (minimal 4-6 bulan atau setara 20 SKS).',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Magang MBKM'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 13, 9, 30, 0),
            ],
            [
                'id'           => 'FAQ-0024',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana mekanisme penyusunan logbook harian dan seminar laporan Kerja Praktik di Fakultas Teknik?',
                'jawaban'      => 'Selama magang, mahasiswa wajib mengisi logbook harian aktivitas kerja yang ditandatangani supervisor industri mingguan. Setelah selesai, mahasiswa menyusun laporan KP dan mendaftar seminar KP maksimal 1 bulan setelah masa magang berakhir dengan melampirkan lembar penilaian resmi dari pembimbing lapangan perusahaan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 12, 14, 10, 0),
            ],
            [
                'id'           => 'FAQ-0025',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana alur konversi mata kuliah program Magang MBKM mandiri/MSIB ke dalam kurikulum program studi teknik?',
                'jawaban'      => 'Sebelum magang dimulai, mahasiswa mengajukan Learning Agreement ke Ketua Program Studi untuk memetakan Capaian Pembelajaran Lulusan (CPL) industri ke mata kuliah relevan (maksimal 20 SKS). Nilai akhir dikonversi berdasarkan evaluasi mentor industri, logbook bulanan, dan seminar laporan akhir magang di kampus.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Magang MBKM', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 12, 10, 0, 0),
            ],
            [
                'id'           => 'FAQ-0026',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Apa yang harus dilakukan jika permohonan magang atau Kerja Praktik ditolak oleh perusahaan tujuan?',
                'jawaban'      => 'Mahasiswa dapat segera melapor ke Koordinator KP Departemen dan mengajukan kembali surat pengantar baru ke perusahaan alternatif lain. Fakultas Teknik juga menyediakan daftar perusahaan mitra industri yang siap menerima mahasiswa KP.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 11, 15, 30, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: AKADEMIK, KRS, PERWALIAN & KURIKULUM ────────────────
            // =========================================================================
            [
                'id'           => 'FAQ-0027',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana alur konsultasi perwalian dan pengesahan Kartu Rencana Studi (KRS) dengan Dosen Wali FT?',
                'jawaban'      => 'Mahasiswa wajib melihat jadwal perwalian pada kalender akademik, mengisi draf mata kuliah pada SIM KRS sesuai jatah SKS berdasarkan Indeks Prestasi Semester (IPS), menghadiri sesi perwalian baik luring maupun daring, dan memastikan status KRS telah di-approve (disahkan) oleh Dosen Wali sebelum batas akhir masa KRS.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'KRS', 'Rencana Studi'],
                'pustaka_id'   => $pustakaKrs->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 11, 9, 15, 0),
            ],
            [
                'id'           => 'FAQ-0028',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana aturan sistem prasyarat mata kuliah (prerequisite chain) pada kurikulum Fakultas Teknik?',
                'jawaban'      => 'Mata kuliah yang memiliki prasyarat hanya dapat diprogram jika mahasiswa telah lulus mata kuliah prasyarat dengan nilai minimal D (atau minimal C untuk mata kuliah tertentu seperti Kalkulus dan Fisika Dasar). Sistem KRS otomatis memblokir pengambilan mata kuliah lanjutan jika prasyarat belum terpenuhi.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Akademik', 'KRS'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 10, 14, 0, 0),
            ],
            [
                'id'           => 'FAQ-0029',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Apakah mahasiswa Fakultas Teknik diperbolehkan mengambil mata kuliah di program studi teknik yang lain?',
                'jawaban'      => 'Boleh. Melalui skema Kuliah Lintas Prodi/MBKM Internal FT, mahasiswa semester 5 ke atas dapat memprogram mata kuliah pilihan di prodi teknik lain (misal mahasiswa Teknik Industri mengambil mata kuliah Data Science di Informatika) maksimal 6 SKS per semester dengan persetujuan Dosen Wali dan Kaprodi.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Akademik', 'KRS'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 10, 10, 25, 0),
            ],
            [
                'id'           => 'FAQ-0030',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Berapa batas maksimal masa studi (Drop Out / DO) untuk jenjang Program Sarjana (S1) Fakultas Teknik?',
                'jawaban'      => 'Batas masa studi maksimal jenjang S1 Fakultas Teknik adalah 7 tahun akademik (14 semester), tidak termasuk masa cuti akademik resmi yang diizinkan (maksimal 2 semester). Evaluasi kelanjutan studi juga dilakukan di akhir semester 4 (minimal mengumpulkan 40 SKS lulus).',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Akademik', 'Administrasi Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 9, 13, 50, 0),
            ],
            [
                'id'           => 'FAQ-0031',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana ketentuan pelaksanaan Semester Antara (Semester Pendek) untuk perbaikan nilai mata kuliah teknik?',
                'jawaban'      => 'Semester Antara diselenggarakan pada jeda libur semester genap ke gasal selama 6-8 minggu. Beban SKS yang dapat diambil maksimal 9 SKS dan hanya untuk mata kuliah yang pernah ditempuh (mengulang perbaikan nilai). Nilai tertinggi yang diakui dalam semester antara adalah A.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Akademik', 'KRS'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 9, 9, 0, 0),
            ],
            [
                'id'           => 'FAQ-0032',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur permohonan Surat Keterangan Masih Kuliah untuk tunjangan orang tua di FT?',
                'jawaban'      => 'Mahasiswa aktif dapat mengajukan surat keterangan kuliah secara instan melalui portal kemahasiswaan FT dengan melampirkan bukti lunas UKT semester aktif dan Kartu Keluarga (KK). Surat diterbitkan dengan Tanda Tangan Elektronik (TTE) Dekanat yang sah dan dapat diverifikasi melalui QR Code.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Surat Akademik', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 8, 14, 15, 0),
            ],
            [
                'id'           => 'FAQ-0033',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana syarat dan prosedur pengajuan cuti akademik sementara bagi mahasiswa Fakultas Teknik?',
                'jawaban'      => 'Cuti akademik dapat diajukan oleh mahasiswa yang telah menempuh minimal 2 semester awal dan tidak sedang menerima beasiswa ikatan dinas. Pengajuan diajukan sebelum batas akhir registrasi semester dengan mengisi formulir cuti di SIM Akademik, persetujuan Dosen Wali dan Dekan, serta membayar biaya pemeliharaan status mahasiswa.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Cuti Akademik', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 8, 10, 40, 0),
            ],
            [
                'id'           => 'FAQ-0034',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana alur dan syarat pengajuan pindah program studi internal di lingkungan Fakultas Teknik?',
                'jawaban'      => 'Pindah prodi internal FT (misal Teknik Mesin ke Teknik Kimia) hanya dapat diajukan pada akhir semester 2 atau 4 dengan syarat: IPK minimal 2.75, daya tampung prodi tujuan masih mencukupi, lulus tes wawancara prodi tujuan, dan disetujui oleh kedua Ketua Program Studi serta Dekan FT.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Pindah Prodi', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 7, 15, 0, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: YUDISIUM, KELULUSAN & WISUDA ────────────────────────
            // =========================================================================
            [
                'id'           => 'FAQ-0035',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Apa saja kelengkapan berkas yang harus diunggah untuk pendaftaran Yudisium Fakultas Teknik?',
                'jawaban'      => 'Kelengkapan berkas yudisium FT meliputi: (1) Lembar pengesahan naskah TA bertanda tangan lengkap, (2) Berita acara ujian sidang TA, (3) Sertifikat TOEFL minimal 475, (4) Bukti bebas laboratorium dari seluruh kepala lab teknik, (5) Bukti bebas pinjaman perpustakaan, (6) Ijazah SLTA legalisir, dan (7) Pasfoto resmi wisuda.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Wisuda'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 7, 11, 20, 0),
            ],
            [
                'id'           => 'FAQ-0036',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Berapa batas skor minimal sertifikat tes kemampuan bahasa Inggris (TOEFL/EPT) untuk syarat kelulusan FT?',
                'jawaban'      => 'Mahasiswa Program Sarjana Fakultas Teknik wajib memiliki sertifikat Institutional TOEFL (ITP) atau English Proficiency Test (EPT) dari Pusat Bahasa universitas dengan skor minimal 475 yang masih berlaku (maksimal 2 tahun sejak tanggal tes) sebagai prasyarat wajib sebelum penetapan kelulusan yudisium.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 6, 14, 45, 0),
            ],
            [
                'id'           => 'FAQ-0037',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur pengurusan bebas tanggungan laboratorium dan perpustakaan teknik sebelum yudisium?',
                'jawaban'      => 'Pengurusan bebas laboratorium teknik dilakukan secara terpadu melalui sistem Bebas Tanggungan FT. Mahasiswa memastikan tidak memiliki pinjaman alat, mengembalikan inventaris riset lab, dan mengunggah softcopy skripsi ke repositori perpustakaan untuk diverifikasi petugas.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Laboratorium'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 6, 9, 30, 0),
            ],
            [
                'id'           => 'FAQ-0038',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana cara mendapatkan Surat Keterangan Lulus (SKL) sementara sebelum ijazah asli diterbitkan?',
                'jawaban'      => 'Setelah rapat yudisium fakultas menetapkan kelulusan, SKL sementara dapat diunduh langsung melalui akun SIM Akademik mahasiswa dalam waktu 1x24 jam. SKL telah dibubuhi QR Code tanda tangan elektronik Dekan Fakultas Teknik dan berlaku sah untuk melamar pekerjaan atau mendaftar studi lanjut.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Surat Akademik'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 5, 15, 10, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: PRESTASI, LOMBA, ROBOTIKA & KEMAHASISWAAN ──────────
            // =========================================================================
            [
                'id'           => 'FAQ-0039',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan bantuan pendanaan proposal lomba inovasi teknologi dan robotika (PKM/KRI) FT?',
                'jawaban'      => 'Tim mahasiswa dapat mengajukan bantuan dana pembinaan dan komponen riset melalui Unit Kemahasiswaan FT. Syaratnya: melampirkan proposal lomba yang disetujui Dosen Pembimbing, bukti surat undangan atau lolos tahap seleksi perlombaan, dan rincian anggaran biaya (RAB) komponen.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Prestasi & Lomba', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 5, 10, 0, 0),
            ],
            [
                'id'           => 'FAQ-0040',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan dispensasi perkuliahan bagi mahasiswa delegasi kompetisi teknik nasional?',
                'jawaban'      => 'Mahasiswa mengajukan Surat Permohonan Dispensasi ke Bagian Kemahasiswaan FT minimal 3 hari sebelum kegiatan dengan melampirkan surat tugas resmi dan jadwal kompetisi. Surat dispensasi Dekanat akan diteruskan ke dosen pengampu mata kuliah agar presensi tidak dianggap alpa.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Prestasi & Lomba', 'Surat Akademik'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 4, 13, 30, 0),
            ],

            // =========================================================================
            // ── FAKULTAS TEKNIK: STATUS ANTREAN VALIDASI & ARSIP (UNTUK DASHBOARD) ────
            // =========================================================================
            [
                'id'           => 'FAQ-0041',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Persyaratan yudisium program sarjana Fakultas Teknik',
                'jawaban'      => 'Mahasiswa wajib menyelesaikan seluruh beban SKS tanpa nilai E, lulus ujian tugas akhir, bebas pinjaman perpustakaan serta laboratorium, dan mengunggah berkas sertifikat TOEFL serta transkrip akademik melalui portal Fakultas Teknik.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Yudisium', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 21, 10, 18, 0),
            ],
            [
                'id'           => 'FAQ-0042',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Apa saja persyaratan pendaftaran tugas akhir Fakultas Teknik?',
                'jawaban'      => 'Persyaratan meliputi perolehan minimal 120 SKS, telah lulus mata kuliah Metodologi Penelitian dan Kerja Praktik, serta memiliki IPK minimal 2.50.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 2,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Tugas Akhir'],
                'pustaka_id'   => $pustakaTa->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 11, 20, 0),
            ],
            [
                'id'           => 'FAQ-0043',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana cara mendapatkan akses server GPU untuk riset kecerdasan buatan di FT?',
                'jawaban'      => 'Mahasiswa Teknik yang sedang menempuh tugas akhir dapat meminta akun cluster GPU melalui dosen pembimbing laboratorium kecerdasan komputasional Fakultas Teknik.',
                'status'       => 'Diterima',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Fasilitas Kampus', 'Laboratorium'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Telah diverifikasi dan disetujui validator, siap tayang.',
                'created_at'   => Carbon::create(2026, 10, 13, 13, 45, 0),
            ],
            [
                'id'           => 'FAQ-0044',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Ketentuan program magang industri bersertifikat kurikulum lama Fakultas Teknik',
                'jawaban'      => 'Mata kuliah magang kurikulum lama dikonversi secara ekuivalen maksimal 4 SKS dengan persetujuan ketua program studi.',
                'status'       => 'Ditolak',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Teknik', 'Teknik', 'Magang MBKM'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Mohon perbarui agar mengacu pada kurikulum terbaru 2026.',
                'created_at'   => Carbon::create(2026, 10, 10, 8, 20, 0),
            ],

            // =========================================================================
            // ── FAQ UMUM & FAKULTAS LAIN (UNTUK KELENGKAPAN SISTEM) ──────────────────
            // =========================================================================
            [
                'id'           => 'FAQ-0045',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Bagaimana cara melakukan pengisian KRS semester baru?',
                'jawaban'      => 'Pengisian KRS dilakukan secara daring melalui portal akademik mahasiswa sesuai jadwal perwalian. Pilih mata kuliah yang akan diambil berdasarkan jatah SKS, lalu ajukan persetujuan ke Dosen Wali.',
                'status'       => 'Terbit',
                'versi_count'  => 2,
                'tags'         => ['Umum', 'KRS', 'Rencana Studi'],
                'pustaka_id'   => $pustakaKrs->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 21, 9, 45, 0),
            ],
            [
                'id'           => 'FAQ-0046',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan surat aktif kuliah?',
                'jawaban'      => 'Mahasiswa dapat mengajukan Surat Keterangan Aktif Kuliah melalui menu Layanan Surat di portal mahasiswa dengan melampirkan bukti pembayaran UKT semester berjalan.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Umum', 'Surat Akademik', 'Layanan Mahasiswa'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 15, 30, 0),
            ],
            [
                'id'           => 'FAQ-0047',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana cara mengajukan legalisasi transkrip nilai?',
                'jawaban'      => 'Ajukan permohonan legalisasi transkrip nilai melalui loket layanan fakultas atau unggah dokumen scan transkrip asli pada menu Legalisasi Dokumen untuk mendapatkan tanda tangan elektronik resmi.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Umum', 'Transkrip Nilai', 'Administrasi Akademik'],
                'pustaka_id'   => $pustakaBaak->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 20, 13, 12, 0),
            ],
            [
                'id'           => 'FAQ-0048',
                'id_dataentry' => $dewiId,
                'pertanyaan'   => 'Bagaimana prosedur pengajuan pindah program studi?',
                'jawaban'      => 'Pindah program studi hanya dapat diajukan setelah menempuh minimal 2 semester dan maksimal 4 semester, dengan persetujuan Ketua Program Studi asal dan tujuan serta Dekan Fakultas.',
                'status'       => 'Menunggu Validasi',
                'versi_count'  => 1,
                'tags'         => ['Umum', 'Pindah Prodi', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 17, 8, 35, 0),
            ],
            [
                'id'           => 'FAQ-0049',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Mekanisme rekognisi SKS magang MBKM mandiri di Fakultas Bisnis dan Ekonomika',
                'jawaban'      => 'Pengajuan konversi kegiatan magang mitra diajukan ke tim kurikulum departemen FBE untuk diverifikasi kesesuaian Capaian Pembelajaran Lulusan (CPL).',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Bisnis dan Ekonomika', 'Magang MBKM', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 14, 20, 0),
            ],
            [
                'id'           => 'FAQ-0050',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Syarat keikutsertaan peradilan semu (Moot Court) Fakultas Hukum',
                'jawaban'      => 'Terbuka untuk mahasiswa Fakultas Hukum semester 3 ke atas yang telah menempuh mata kuliah Hukum Acara Pidana dan Hukum Acara Perdata.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Hukum', 'Prestasi & Lomba', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => null,
                'created_at'   => Carbon::create(2026, 10, 16, 10, 5, 0),
            ],

            // ── FAQ STATUS LAINNYA (Diterima, Terbit, Ditolak) ──
            [
                'id'           => 'FAQ-0051',
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
                'id'           => 'FAQ-0052',
                'id_dataentry' => $adityaId,
                'pertanyaan'   => 'Alur pendaftaran seminar proposal skripsi Fakultas Bisnis dan Ekonomika',
                'jawaban'      => 'Mahasiswa FBE wajib menyelesaikan seminar proposal setelah draf bab 1-3 disetujui kedua dosen pembimbing dan telah lulus mata kuliah metodologi penelitian dengan nilai minimal B.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Bisnis dan Ekonomika', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Telah tayang untuk mahasiswa.',
                'created_at'   => Carbon::create(2026, 10, 14, 9, 30, 0),
            ],
            [
                'id'           => 'FAQ-0053',
                'id_dataentry' => $nadiaId,
                'pertanyaan'   => 'Tata tertib dan persyaratan rotasi klinis kepaniteraan (koas) Fakultas Kedokteran',
                'jawaban'      => 'Mahasiswa harus telah dinyatakan lulus Sarjana Kedokteran, melunasi administrasi stase, dan mengikuti orientasi rumah sakit pendidikan jejaring.',
                'status'       => 'Terbit',
                'versi_count'  => 1,
                'tags'         => ['Fakultas Kedokteran', 'Akademik'],
                'pustaka_id'   => $pustakaAkademik->id,
                'catatan'      => 'Sesuai regulasi Konsil Kedokteran.',
                'created_at'   => Carbon::create(2026, 10, 12, 16, 0, 0),
            ],
            [
                'id'           => 'FAQ-0054',
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
        ];

        // 3 FAQ tertentu cukup 1 versi saja; seluruh FAQ sisanya memiliki 4–5 versi
        $singleVersionIndexes = [2, 6, 9];
        $revisionNotes = [
            1 => ' (Edisi awal panduan akademik sebelum penyesuaian alur daring).',
            2 => ' (Pembaruan prosedur pengajuan melalui portal layanan mahasiswa).',
            3 => ' (Penyesuaian syarat administrasi dan jadwal verifikasi fakultas).',
            4 => ' (Penyempurnaan rincian dokumen pendukung dan ketentuan terbaru).',
        ];

        $userDirMap = User::pluck('tDirektorat_id', 'id')->all();
        $pusatDirId = \App\Models\Direktorat::pusatId();

        foreach (array_values($faqs) as $idx => $item) {
            $ownerDirId = (int) ($userDirMap[$item['id_dataentry']] ?? $pusatDirId);
            $faq = Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'id_dataentry'   => $item['id_dataentry'],
                    'tDirektorat_id' => $ownerDirId,
                    'pertanyaan'     => $item['pertanyaan'],
                    'created_at'     => $item['created_at'],
                    'updated_at'     => $item['created_at'],
                ]
            );

            if (($item['status'] ?? 'Terbit') !== 'Terbit') {
                $versiTotal = max(1, (int) ($item['versi_count'] ?? 1));
            } else {
                $versiTotal = in_array($idx, $singleVersionIndexes, true)
                    ? 1
                    : (($idx % 2 === 0) ? 5 : 4);
            }

            $createdVersionIds = [];
            for ($v = 1; $v <= $versiTotal; $v++) {
                $versionId = $item['id'] . '-V' . $v;
                $createdVersionIds[] = $versionId;
                $isLatest  = ($v === $versiTotal);

                $versionJawaban = $isLatest
                    ? $item['jawaban']
                    : rtrim((string) $item['jawaban'], '.') . '.' . ($revisionNotes[$v] ?? " (Revisi versi {$v}).");

                // Pada V1 untuk sebagian FAQ multi-versi, biarkan sumber pustaka kosong (opsional) sebagai variasi uji coba
                $versionPustakaId = (!$isLatest && $v === 1 && ($idx % 3 === 0))
                    ? null
                    : $item['pustaka_id'];

                $versionTime = $item['created_at']->copy()->subDays(($versiTotal - $v) * 14);

                // Versi terdahulu (sebelum versi terbaru) wajib sudah berstatus 'Terbit' karena versi baru hanya bisa ditambah setelah versi sebelumnya Diterima/Terbit
                $rawItemStatus = ($item['status'] === 'Menunggu Validasi')
                    ? ($ownerDirId === $pusatDirId ? 'Menunggu Validasi Admik' : 'Menunggu Validasi Unit')
                    : $item['status'];
                $versionStatus = $isLatest ? $rawItemStatus : 'Terbit';
                $versionValidatorId = $isLatest
                    ? (in_array($item['status'], ['Diterima', 'Terbit', 'Ditolak'], true) ? $validatorId : null)
                    : $validatorId;

                FaqVersion::updateOrCreate(
                    ['id' => $versionId],
                    [
                        'tDaftarPustakas_id' => $versionPustakaId,
                        'tFaqs_id'           => $faq->id,
                        'version_number'     => FaqVersion::extractVersionNumber($versionId),
                        'id_validator'       => $versionValidatorId,
                        'jawaban'            => $versionJawaban,
                        'status'             => $versionStatus,
                        'catatan'            => $isLatest ? $item['catatan'] : null,
                        'created_at'         => $versionTime,
                        'updated_at'         => $versionTime,
                    ]
                );
            }

            // Hapus versi berlebih jika sebelumnya pernah ada lebih banyak versi untuk FAQ ini
            FaqVersion::where('tFaqs_id', $faq->id)
                ->whereNotIn('id', $createdVersionIds)
                ->delete();
            FaqVersion::syncLatestFlag($faq->id);

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

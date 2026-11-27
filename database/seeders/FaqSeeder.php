<?php

namespace Database\Seeders;

use App\Models\DaftarPustaka;
use App\Models\Direktorat;
use App\Models\Faq;
use App\Models\FaqValidasiLog;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Seeder FAQ terstruktur:
     * - Direktorat Akademik (Admik): 10 FAQ (5 FAQ = 1 versi, 5 FAQ = rentang 2–3 versi)
     * - Masing-masing Direktorat/Unit (8 Fakultas): 5 FAQ per Unit (2 FAQ = 1 versi, 3 FAQ = rentang 2–3 versi)
     * Total = 50 FAQ.
     */
    public function run(): void
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();

        $this->call([
            DirektoratSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            SumberPustakaSeeder::class,
            KataKunciSeeder::class,
        ]);

        $dataEntry = User::where('email', 'dataentrykms@gmail.com')->first()
            ?? User::where('tRoles_id', 2)->first()
            ?? User::first();

        $validator = User::where('email', 'validatorkms@gmail.com')->first()
            ?? User::where('tRoles_id', 3)->first()
            ?? User::first();

        if (!$dataEntry || !$validator) {
            throw new \RuntimeException('UserSeeder harus dijalankan lebih dulu (akun DataEntryKMS / ValidatorKMS belum ada).');
        }

        $deId        = $dataEntry->id;
        $validatorId = $validator->id;
        $pusatDirId  = Direktorat::pusatId();

        $pustakaAkademik = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/pedoman-2026')->first() ?? DaftarPustaka::first();
        $pustakaBaak     = DaftarPustaka::where('link', 'https://baak.kampus.ac.id/standar-operasional')->first() ?? $pustakaAkademik;
        $pustakaKrs      = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/panduan-krs')->first() ?? $pustakaAkademik;
        $pustakaBeasiswa = DaftarPustaka::where('link', 'https://kemahasiswaan.kampus.ac.id/beasiswa')->first() ?? $pustakaAkademik;
        $pustakaTa       = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/pedoman-ta')->first() ?? $pustakaAkademik;
        $pustakaUkt      = DaftarPustaka::where('link', 'https://keuangan.kampus.ac.id/sop-ukt')->first() ?? $pustakaAkademik;
        $pustakaKalender = DaftarPustaka::where('link', 'https://akademik.kampus.ac.id/kalender-2026')->first() ?? $pustakaAkademik;

        $tagsPool = array_unique(array_merge(
            ['Umum', 'Teknik', 'Fakultas Teknik', 'Akademik', 'KRS', 'Rencana Studi', 'Cuti Akademik',
             'Yudisium', 'Wisuda', 'Surat Akademik', 'Layanan Mahasiswa', 'Transkrip Nilai',
             'Administrasi Akademik', 'Tugas Akhir', 'Biaya Kuliah', 'Beasiswa', 'Praktikum',
             'Laboratorium', 'Kerja Praktik', 'Magang MBKM', 'Fasilitas', 'Prestasi & Lomba'],
            array_values(\App\Http\Controllers\PortalController::FAKULTAS_MAP)
        ));

        $tagMap = [];
        foreach ($tagsPool as $tagName) {
            $tagMap[$tagName] = Tag::firstOrCreate(['nama' => $tagName])->id;
        }

        $dirByName = Direktorat::pluck('id', 'nama')->all();
        $resolveDir = fn (string $name, int $fallbackId) => (int) ($dirByName[$name] ?? $fallbackId);

        // =========================================================================
        // 1. DIREKTORAT AKADEMIK (ADMIK): 10 FAQ (5 x 1 versi, 5 x rentang 2–3 versi)
        // =========================================================================
        $admikFaqs = [
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana alur pengisian dan pengesahan Kartu Rencana Studi (KRS) mahasiswa setiap awal semester?',
                'jawaban'     => 'Pengisian KRS dilakukan daring melalui portal akademik mahasiswa sesuai jadwal perwalian. Pilih mata kuliah sesuai batas SKS dari IPS semester sebelumnya, lalu konsultasikan dan ajukan pengesahan kepada Dosen Wali sebelum masa KRS ditutup.',
                'status'      => 'Terbit',
                'versi_count' => 3,
                'tags'        => ['Umum', 'Akademik', 'KRS', 'Rencana Studi'],
                'pustaka_id'  => $pustakaKrs?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Apa saja syarat dan prosedur registrasi ulang mahasiswa aktif pada awal tahun ajaran?',
                'jawaban'     => 'Mahasiswa wajib melunasi tagihan UKT/BPP semester berjalan melalui virtual account resmi kampus, memastikan tidak ada tanggungan administrasi perpustakaan maupun laboratorium, lalu mencetak kartu aktif kuliah di portal.',
                'status'      => 'Terbit',
                'versi_count' => 3,
                'tags'        => ['Umum', 'Administrasi Akademik', 'Layanan Mahasiswa'],
                'pustaka_id'  => $pustakaBaak?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana ketentuan dan batas waktu pengajuan cuti akademik sementara bagi mahasiswa?',
                'jawaban'     => 'Cuti akademik dapat diajukan oleh mahasiswa yang telah menempuh minimal 2 semester awal melalui SIM Akademik dengan persetujuan Dosen Wali dan Dekan sebelum batas registrasi berakhir, maksimal 2 semester selama masa studi.',
                'status'      => 'Terbit',
                'versi_count' => 2,
                'tags'        => ['Umum', 'Cuti Akademik', 'Akademik'],
                'pustaka_id'  => $pustakaAkademik?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Di mana mahasiswa dapat mengunduh Kartu Hasil Studi (KHS) dan transkrip nilai sementara?',
                'jawaban'     => 'KHS dan transkrip nilai sementara dapat dilihat serta diunduh langsung pada menu Hasil Studi di portal akademik mahasiswa setelah periode input nilai dosen selesai dan divalidasi oleh BAAK.',
                'status'      => 'Terbit',
                'versi_count' => 3,
                'tags'        => ['Umum', 'Akademik', 'Transkrip Nilai'],
                'pustaka_id'  => $pustakaAkademik?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana prosedur permohonan penyesuaian atau cicilan pembayaran UKT di kampus?',
                'jawaban'     => 'Permohonan cicilan atau keringanan UKT diajukan melalui Biro Keuangan dan Direktorat Akademik paling lambat 2 minggu sebelum batas pembayaran dengan melampirkan surat permohonan orang tua/wali serta dokumen kondisi ekonomi.',
                'status'      => 'Terbit',
                'versi_count' => 2,
                'tags'        => ['Umum', 'Biaya Kuliah', 'Layanan Mahasiswa'],
                'pustaka_id'  => $pustakaUkt?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana cara mendaftar program beasiswa prestasi akademik dan bantuan biaya pendidikan universitas?',
                'jawaban'     => 'Pendaftaran beasiswa dibuka setiap awal semester melalui portal Direktorat Kemahasiswaan dengan melampirkan transkrip nilai terakhir (IPK sesuai syarat skema), surat rekomendasi dosen wali, dan sertifikat prestasi.',
                'status'      => 'Terbit',
                'versi_count' => 1,
                'tags'        => ['Umum', 'Beasiswa', 'Biaya Kuliah'],
                'pustaka_id'  => $pustakaBeasiswa?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Di mana jadwal kalender akademik perkuliahan, UTS, UAS, dan libur semester dapat diakses?',
                'jawaban'     => 'Kalender akademik resmi diterbitkan setiap tahun ajaran melalui situs Direktorat Akademik dan dapat diunduh pada tautan rujukan resmi untuk melihat tanggal penting KRS, perkuliahan, ujian, dan yudisium.',
                'status'      => 'Terbit',
                'versi_count' => 1,
                'tags'        => ['Umum', 'Akademik'],
                'pustaka_id'  => $pustakaKalender?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana prosedur pengurusan Kartu Tanda Mahasiswa (KTM) yang hilang atau rusak?',
                'jawaban'     => 'Mahasiswa melapor ke layanan BAAK dengan membawa surat keterangan kehilangan dari kepolisian (jika hilang) atau kartu lama yang rusak, disertai bukti identitas diri untuk pencetakan ulang KTM.',
                'status'      => 'Terbit',
                'versi_count' => 1,
                'tags'        => ['Umum', 'Layanan Mahasiswa', 'Administrasi Akademik'],
                'pustaka_id'  => $pustakaBaak?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Bagaimana prosedur pengajuan Surat Keterangan Aktif Kuliah untuk keperluan tunjangan atau visa?',
                'jawaban'     => 'Permohonan Surat Keterangan Aktif Kuliah diajukan secara daring di portal layanan mahasiswa dengan memilih keperluan surat dan melampirkan bukti status aktif semester berjalan. Surat bertanda tangan elektronik terbit maksimal 2 hari kerja.',
                'status'      => 'Diterima',
                'versi_count' => 1,
                'tags'        => ['Umum', 'Surat Akademik', 'Layanan Mahasiswa'],
                'pustaka_id'  => $pustakaBaak?->id,
            ],
            [
                'dir_id'      => $pusatDirId,
                'pertanyaan'  => 'Apa syarat pendaftaran wisuda universitas setelah mahasiswa dinyatakan lulus yudisium fakultas?',
                'jawaban'     => 'Calon wisudawan wajib mengunggah pasfoto resmi, mengisi formulir tracer study, menyelesaikan biaya administrasi wisuda, serta memastikan seluruh berkas bebas tanggungan perpustakaan telah tervalidasi sebelum batas pendaftaran.',
                'status'      => 'Menunggu Validasi Admik',
                'versi_count' => 1,
                'tags'        => ['Umum', 'Wisuda', 'Yudisium'],
                'pustaka_id'  => $pustakaTa?->id,
            ],
        ];

        // =========================================================================
        // 2. MASING-MASING UNIT / FAKULTAS (8 UNIT x 5 FAQ = 40 FAQ)
        //    Setiap unit: 3 FAQ rentang 2–3 versi + 2 FAQ 1 versi
        // =========================================================================
        $unitDefs = [
            [
                'nama' => 'Fakultas Teknik',
                'id'   => $resolveDir('Fakultas Teknik', 2),
                'faqs' => [
                    ['Apa saja syarat pendaftaran usulan judul Tugas Akhir (TA) dan Capstone Design Fakultas Teknik?', 'Mahasiswa FT yang telah menempuh minimal 120 SKS dengan IPK >= 2.00 tanpa nilai E dapat mengajukan usulan judul TA melalui SIM Tugas Akhir FT setelah memperoleh persetujuan calon Dosen Pembimbing.', 'Terbit', 3, ['Fakultas Teknik', 'Teknik', 'Tugas Akhir'], $pustakaTa?->id],
                    ['Bagaimana prosedur peminjaman alat ukur dan instrumen laboratorium di Fakultas Teknik?', 'Peminjaman osiloskop, multimeter, dan perangkat lab diajukan ke teknisi laboratorium dengan mengisi Kartu Bon Alat serta menitipkan KTM aktif dan mengembalikannya sebelum pukul 17.00 WIB.', 'Terbit', 3, ['Fakultas Teknik', 'Teknik', 'Laboratorium', 'Praktikum'], $pustakaBaak?->id],
                    ['Berapa durasi minimal dan syarat konversi Kerja Praktik (KP) atau Magang MBKM di Fakultas Teknik?', 'Mahasiswa minimal telah lulus 90 SKS untuk memprogram KP dengan durasi minimal 160 jam kerja (1 bulan penuh) atau 1 semester untuk skema Magang MBKM dengan persetujuan Learning Agreement Kaprodi.', 'Terbit', 2, ['Fakultas Teknik', 'Teknik', 'Kerja Praktik', 'Magang MBKM'], $pustakaAkademik?->id],
                    ['Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?', 'Pendaftaran Yudisium FT mewajibkan bebas lab seluruh laboratorium teknik, bebas perpustakaan, naskah TA dengan Turnitin maksimal 20%, serta sertifikat TOEFL/EPT minimal 475.', 'Terbit', 1, ['Fakultas Teknik', 'Teknik', 'Yudisium'], $pustakaTa?->id],
                    ['Bagaimana pengajuan surat dispensasi kuliah bagi delegasi lomba inovasi dan robotika Fakultas Teknik?', 'Mahasiswa delegasi mengajukan surat permohonan dispensasi ke Bagian Kemahasiswaan FT minimal H-3 kegiatan disertai surat tugas dan jadwal resmi lomba.', 'Menunggu Validasi Unit', 1, ['Fakultas Teknik', 'Teknik', 'Prestasi & Lomba', 'Surat Akademik'], $pustakaBaak?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Farmasi',
                'id'   => $resolveDir('Fakultas Farmasi', 3),
                'faqs' => [
                    ['Apa saja aturan Keselamatan Laboratorium dan pemakaian APD selama praktikum di Fakultas Farmasi?', 'Seluruh praktikan Farmasi wajib mengenakan jas laboratorium bersih berkancing penuh, masker medis, sarung tangan nitril saat menangani reagen, sepatu tertutup, dan menampung limbah kimia sesuai kode wadah B3.', 'Terbit', 3, ['Fakultas Farmasi', 'Praktikum', 'Laboratorium'], $pustakaAkademik?->id],
                    ['Bagaimana alur pendaftaran dan syarat masuk Program Studi Profesi Apoteker (PSPA)?', 'Lulusan Sarjana Farmasi dengan IPK >= 2.75 dapat mendaftar seleksi masuk PSPA melalui portal admisi dengan mengunggah transkrip nilai akademik, ijazah/SKL, serta mengikuti ujian kompetensi dasar kefarmasian.', 'Terbit', 3, ['Fakultas Farmasi', 'Akademik', 'Yudisium'], $pustakaAkademik?->id],
                    ['Bagaimana ketentuan Praktik Kerja Profesi Apoteker (PKPA) di rumah sakit, apotek, dan industri farmasi?', 'PKPA dilaksanakan secara bergilir di rumah sakit pendidikan, apotek jejaring, puskesmas, dan industri farmasi mitra dengan bimbingan preseptor lapangan serta evaluasi logbook harian dan ujian komprehensif.', 'Terbit', 2, ['Fakultas Farmasi', 'Kerja Praktik', 'Magang MBKM'], $pustakaBaak?->id],
                    ['Bagaimana prosedur inhal atau praktikum susulan di laboratorium kimia dan teknologi farmasi?', 'Mahasiswa yang sakit atau bertugas resmi wajib menyerahkan surat keterangan dokter/tugas maksimal 3 hari kerja kepada koordinator praktikum untuk dijadwalkan praktikum susulan di akhir modul.', 'Terbit', 1, ['Fakultas Farmasi', 'Praktikum', 'Akademik'], $pustakaAkademik?->id],
                    ['Apa syarat kelengkapan pengajuan seminar hasil penelitian dan sidang skripsi Fakultas Farmasi?', 'Mahasiswa menyerahkan draf naskah final yang ditandatangani kedua pembimbing, surat keterangan bebas alat dan bahan lab penelitian, serta hasil uji plagiarisme Turnitin sesuai ambang batas fakultas.', 'Terbit', 1, ['Fakultas Farmasi', 'Tugas Akhir', 'Yudisium'], $pustakaTa?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Bisnis & Ekonomika',
                'id'   => $resolveDir('Fakultas Bisnis & Ekonomika', 4),
                'faqs' => [
                    ['Bagaimana alur pengajuan judul dan seminar proposal skripsi di Fakultas Bisnis & Ekonomika (FBE)?', 'Mahasiswa FBE yang telah lulus minimal 110 SKS termasuk Metodologi Penelitian mengajukan proposal skripsi ke departemen terkait setelah disetujui Dosen Pembimbing untuk dijadwalkan seminar proposal.', 'Terbit', 3, ['Fakultas Bisnis & Ekonomika', 'Tugas Akhir', 'Akademik'], $pustakaTa?->id],
                    ['Bagaimana mekanisme konversi SKS kegiatan magang perusahaan dan proyek bisnis MBKM di FBE?', 'Mahasiswa mengajukan proposal konversi dan Learning Agreement sebelum program berjalan ke Tim Kurikulum Departemen FBE agar capaian kerja di perusahaan mitra dapat dikonversi hingga 20 SKS.', 'Terbit', 3, ['Fakultas Bisnis & Ekonomika', 'Kerja Praktik', 'Magang MBKM'], $pustakaAkademik?->id],
                    ['Bagaimana cara mendaftar akses ruang komputer data pasar modal (Galeri Investasi) FBE?', 'Mahasiswa FBE aktif dapat mendaftar ke pengelola Galeri Investasi Bursa Efek dengan menunjukkan KTM untuk mendapatkan akses terminal data keuangan, Bloomberg/IDX, serta pelatihan analisis pasar modal.', 'Terbit', 2, ['Fakultas Bisnis & Ekonomika', 'Fasilitas', 'Layanan Mahasiswa'], $pustakaAkademik?->id],
                    ['Apa saja jalur tugas akhir non-skripsi yang tersedia bagi mahasiswa FBE?', 'Selain skripsi konvensional, mahasiswa FBE dapat memilih jalur Artikel Ilmiah Terpublikasi di jurnal terakreditasi atau Rencana Bisnis/Business Plan terverifikasi inkubator bisnis sesuai pedoman departemen.', 'Terbit', 1, ['Fakultas Bisnis & Ekonomika', 'Tugas Akhir', 'Akademik'], $pustakaTa?->id],
                    ['Bagaimana prosedur pengajuan surat izin survei dan pengambilan data riset ke perusahaan bagi mahasiswa FBE?', 'Surat izin pengumpulan data penelitian diajukan melalui tata usaha FBE dengan melampirkan lembar persetujuan proposal dari pembimbing dan nama perusahaan/instansi tujuan.', 'Terbit', 1, ['Fakultas Bisnis & Ekonomika', 'Surat Akademik', 'Tugas Akhir'], $pustakaBaak?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Kedokteran',
                'id'   => $resolveDir('Fakultas Kedokteran', 5),
                'faqs' => [
                    ['Apa saja persyaratan mengikuti program Kepaniteraan Klinik (Koas) di rumah sakit pendidikan Fakultas Kedokteran?', 'Mahasiswa wajib telah lulus jenjang Sarjana Kedokteran (S.Ked), lulus seluruh blok klinik dasar dan OSCE komprehensif, melunasi administrasi stase, serta mengikuti orientasi keselamatan pasien rumah sakit jejaring.', 'Terbit', 3, ['Fakultas Kedokteran', 'Akademik', 'Praktikum'], $pustakaAkademik?->id],
                    ['Bagaimana aturan pengulangan dan ujian remedi blok pembelajaran atau OSCE di Fakultas Kedokteran?', 'Mahasiswa yang belum memenuhi batas nilai lulus blok atau stase OSCE berhak mengikuti ujian remedi maksimal satu kali pada periode yang dijadwalkan dengan mendaftar ke tim pengelola blok terkait.', 'Terbit', 3, ['Fakultas Kedokteran', 'Akademik', 'Layanan Mahasiswa'], $pustakaAkademik?->id],
                    ['Bagaimana ketentuan penggunaan ruang Skills Lab dan peminjaman manekin latihan klinis FK?', 'Mahasiswa dapat melakukan reservasi ruang Skills Lab mandiri bersama kelompok belajar melalui admin laboratorium keterampilan klinis minimal H-1 dengan wajib menaati tata tertib perawatan alat peraga medis.', 'Terbit', 2, ['Fakultas Kedokteran', 'Laboratorium', 'Fasilitas'], $pustakaBaak?->id],
                    ['Bagaimana alur pendaftaran Ujian Kompetensi Mahasiswa Program Profesi Dokter (UKMPPD) di FK?', 'Peserta yang telah menuntaskan seluruh rotasi stase kepaniteraan klinik mendaftar melalui koordinator profesi dokter FK dengan melampirkan buku nilai logbook stase yang telah disahkan.', 'Terbit', 1, ['Fakultas Kedokteran', 'Yudisium', 'Akademik'], $pustakaAkademik?->id],
                    ['Bagaimana prosedur pengurusan izin tidak mengikuti kegiatan blok atau stase karena kondisi darurat medis?', 'Mahasiswa melaporkan ketidakhadiran sesegera mungkin ke koordinator blok/pembimbing klinik dan menyerahkan surat keterangan rawat/dokter resmi maksimal 2x24 jam.', 'Terbit', 1, ['Fakultas Kedokteran', 'Layanan Mahasiswa', 'Administrasi Akademik'], $pustakaBaak?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Industri Kreatif',
                'id'   => $resolveDir('Fakultas Industri Kreatif', 6),
                'faqs' => [
                    ['Apa saja syarat kelulusan pameran karya dan sidang Tugas Akhir di Fakultas Industri Kreatif (FIK)?', 'Mahasiswa FIK wajib menyelesaikan purwarupa karya desain/produk kreatif beserta laporan pengantar karya, mengikuti pameran tugas akhir fakultas, dan lulus evaluasi dewan penguji studio.', 'Terbit', 3, ['Fakultas Industri Kreatif', 'Tugas Akhir', 'Yudisium'], $pustakaTa?->id],
                    ['Bagaimana prosedur peminjaman studio fotografi, ruang rekaman, dan peralatan workshop di FIK?', 'Peminjaman studio dan perangkat produksi multimedia diajukan secara daring melalui sistem peminjaman fasilitas FIK minimal 2 hari sebelum pemakaian dengan persetujuan koordinator laboratorium studio.', 'Terbit', 3, ['Fakultas Industri Kreatif', 'Laboratorium', 'Fasilitas'], $pustakaBaak?->id],
                    ['Bagaimana ketentuan pengajuan portofolio magang industri kreatif untuk konversi mata kuliah studio?', 'Mahasiswa melampirkan portofolio karya, surat penerimaan agensi/studio mitra yang disetujui dosen wali, dan menyusun laporan proyek industri di akhir semester.', 'Terbit', 2, ['Fakultas Industri Kreatif', 'Kerja Praktik', 'Magang MBKM'], $pustakaAkademik?->id],
                    ['Bagaimana aturan jam akses ruang kerja studio desain dan workshop kayu/tekstil di luar jam kuliah?', 'Penggunaan studio di luar jam kuliah reguler diperbolehkan hingga pukul 20.00 WIB dengan mencatatkan KTM pada petugas keamanan gedung FIK serta wajib menjaga kebersihan area kerja.', 'Terbit', 1, ['Fakultas Industri Kreatif', 'Fasilitas', 'Layanan Mahasiswa'], $pustakaBaak?->id],
                    ['Apakah karya Tugas Akhir FIK wajib didaftarkan Hak Kekayaan Intelektual (HKI) sebelum yudisium?', 'FIK mendorong pendaftaran Hak Cipta atau Desain Industri melalui Sentra HKI universitas; karya terpilih difasilitasi pendanaan pendaftaran HKI oleh fakultas.', 'Terbit', 1, ['Fakultas Industri Kreatif', 'Tugas Akhir', 'Yudisium'], $pustakaAkademik?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Hukum',
                'id'   => $resolveDir('Fakultas Hukum', 7),
                'faqs' => [
                    ['Apa saja persyaratan mengikuti kegiatan Kemahiran Hukum dan Peradilan Semu (Moot Court) di Fakultas Hukum?', 'Program Peradilan Semu terbuka bagi mahasiswa Fakultas Hukum semester 4 ke atas yang telah lulus Hukum Acara Pidana dan Hukum Acara Perdata dengan mendaftar ke Laboratorium Kemahiran Hukum.', 'Terbit', 3, ['Fakultas Hukum', 'Praktikum', 'Akademik'], $pustakaAkademik?->id],
                    ['Bagaimana alur pendaftaran ujian proposal dan penulisan hukum (skripsi) di Fakultas Hukum?', 'Mahasiswa FH yang telah menyelesaikan minimal 115 SKS mengajukan matriks permasalahan hukum ke Ketua Bagian Peminatan untuk penetapan dosen pembimbing dan jadwal ujian proposal.', 'Terbit', 3, ['Fakultas Hukum', 'Tugas Akhir', 'Akademik'], $pustakaTa?->id],
                    ['Bagaimana ketentuan magang di kantor advokat, pengadilan, atau kejaksaan untuk konversi SKS Fakultas Hukum?', 'Magang profesi hukum berlangsung minimal 8 minggu efektif di instansi mitra resmi yang ditetapkan fakultas, disertai logbook penanganan perkara dan laporan evaluasi pembimbing lapangan.', 'Terbit', 2, ['Fakultas Hukum', 'Kerja Praktik', 'Magang MBKM'], $pustakaAkademik?->id],
                    ['Bagaimana cara mengakses database jurnal hukum dan himpunan putusan pengadilan di Perpustakaan FH?', 'Mahasiswa login ke jaringan Wi-Fi atau VPN kampus menggunakan akun SSO untuk mengakses database hukum terlanggan serta ruang baca referensi undang-undang.', 'Terbit', 1, ['Fakultas Hukum', 'Fasilitas', 'Layanan Mahasiswa'], $pustakaBaak?->id],
                    ['Bagaimana prosedur permohonan surat pengantar penelitian lapangan ke instansi penegak hukum bagi mahasiswa FH?', 'Permohonan diajukan ke tata usaha FH dengan melampirkan lembar pengesahan proposal skripsi untuk diterbitkan surat pengantar resmi Dekanat menuju instansi tujuan.', 'Terbit', 1, ['Fakultas Hukum', 'Surat Akademik', 'Tugas Akhir'], $pustakaBaak?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Psikologi',
                'id'   => $resolveDir('Fakultas Psikologi', 8),
                'faqs' => [
                    ['Bagaimana prosedur peminjaman alat tes psikologi dan akses ruang observasi di Laboratorium Psikologi?', 'Peminjaman alat tes psikologi hanya diizinkan untuk keperluan praktikum asesmen atau penelitian skripsi di bawah supervisi dosen penanggung jawab dengan melampirkan surat rekomendasi resmi laboratorium.', 'Terbit', 3, ['Fakultas Psikologi', 'Laboratorium', 'Praktikum'], $pustakaAkademik?->id],
                    ['Apa saja persyaratan pengajuan kelaikan etik (Ethical Clearance) untuk penelitian skripsi Fakultas Psikologi?', 'Mahasiswa wajib mengunggah protokol penelitian, instrumen kuesioner/wawancara, serta formulir Informed Consent ke komite etik penelitian fakultas sebelum turun mengambil data responden.', 'Terbit', 3, ['Fakultas Psikologi', 'Tugas Akhir', 'Akademik'], $pustakaTa?->id],
                    ['Bagaimana ketentuan praktik pengabdian masyarakat dan magang asisten psikolog industri/pendidikan di F.Psi?', 'Mahasiswa semester 6 ke atas dapat mendaftar magang terstruktur di unit layanan konseling, sekolah mitra, atau divisi HRD perusahaan selama minimal 1 semester untuk konversi SKS.', 'Terbit', 2, ['Fakultas Psikologi', 'Kerja Praktik', 'Magang MBKM'], $pustakaAkademik?->id],
                    ['Bagaimana alur pendaftaran layanan konseling akademik dan kesehatan mental gratis bagi mahasiswa?', 'Mahasiswa dapat menjadwalkan sesi konseling rahasia bersama psikolog/konselor unit layanan psikologi kampus melalui portal kemahasiswaan atau datang ke loket layanan konseling.', 'Terbit', 1, ['Fakultas Psikologi', 'Layanan Mahasiswa', 'Umum'], $pustakaBaak?->id],
                    ['Bagaimana syarat pendaftaran Program Magister Profesi Psikologi setelah lulus Sarjana Psikologi?', 'Lulusan S1 Psikologi dengan IPK minimal 3.00 dapat mendaftar seleksi administrasi, TPA, dan wawancara kesesuaian minat peminatan Klinis, Pendidikan, atau Industri & Organisasi.', 'Terbit', 1, ['Fakultas Psikologi', 'Akademik', 'Yudisium'], $pustakaAkademik?->id],
                ],
            ],
            [
                'nama' => 'Fakultas Teknobiologi',
                'id'   => $resolveDir('Fakultas Teknobiologi', 9),
                'faqs' => [
                    ['Apa saja prosedur biosafety dan penggunaan Laminar Air Flow (LAF) di Laboratorium Bioteknologi?', 'Praktikan dan peneliti riset wajib melakukan sterilisasi area kerja menggunakan alkohol 70%, menyalakan lampu UV minimal 15 menit sebelum penggunaan LAF, mengenakan jas lab bersih, dan menangani kultur sesuai protokol BSL.', 'Terbit', 3, ['Fakultas Teknobiologi', 'Laboratorium', 'Praktikum'], $pustakaAkademik?->id],
                    ['Bagaimana prosedur pemakaian peralatan instrumen seperti PCR, Spektrofotometer, dan Autoklaf di Teknobiologi?', 'Mahasiswa wajib mendaftarkan jadwal penggunaan alat melalui buku log laboratorium dan memperoleh pendampingan laboran tersertifikasi saat mengoperasikan instrumen berpresisi tinggi.', 'Terbit', 3, ['Fakultas Teknobiologi', 'Laboratorium', 'Fasilitas'], $pustakaBaak?->id],
                    ['Bagaimana ketentuan Kerja Praktik dan riset tugas akhir di industri pangan, bioteknologi, atau balai riset?', 'Mahasiswa minimal menempuh 95 SKS dengan lulus praktikum dasar mikrobiologi dan biokimia untuk mengajukan penempatan magang atau kolaborasi riset skripsi di industri/lembaga penelitian mitra.', 'Terbit', 2, ['Fakultas Teknobiologi', 'Kerja Praktik', 'Tugas Akhir'], $pustakaTa?->id],
                    ['Bagaimana tata cara sterilisasi dan pemusnahan limbah kultur mikrobiologi setelah praktikum atau riset selesai?', 'Seluruh media dan wadah berisi kultur mikroba wajib disterilisasi menggunakan autoklaf pada suhu 121 C selama minimal 15 menit sebelum dibuang ke tempat pembuangan limbah biologis.', 'Terbit', 1, ['Fakultas Teknobiologi', 'Laboratorium', 'Praktikum'], $pustakaAkademik?->id],
                    ['Apa saja kelengkapan berkas bebas laboratorium sebagai syarat pendaftaran yudisium Fakultas Teknobiologi?', 'Mahasiswa menyerahkan bukti penyimpanan stok kultur/sampel penelitian, daftar inventaris alat yang telah dikembalikan dalam kondisi baik, serta tanda tangan bebas tanggungan dari kepala laboratorium.', 'Terbit', 1, ['Fakultas Teknobiologi', 'Yudisium', 'Laboratorium'], $pustakaTa?->id],
                ],
            ],
        ];

        $allItems = [];
        $seq = 1;
        $baseTime = Carbon::create(2026, 11, 20, 9, 0, 0, 'Asia/Jakarta');

        foreach ($admikFaqs as $item) {
            $allItems[] = [
                'id'          => sprintf('FAQ-%04d', $seq),
                'dir_id'      => $item['dir_id'],
                'pertanyaan'  => $item['pertanyaan'],
                'jawaban'     => $item['jawaban'],
                'status'      => $item['status'],
                'versi_count' => $item['versi_count'],
                'tags'        => $item['tags'],
                'pustaka_id'  => $item['pustaka_id'],
                'created_at'  => $baseTime->copy()->subHours($seq * 3),
            ];
            $seq++;
        }

        foreach ($unitDefs as $uDef) {
            foreach ($uDef['faqs'] as $fRow) {
                $allItems[] = [
                    'id'          => sprintf('FAQ-%04d', $seq),
                    'dir_id'      => $uDef['id'],
                    'pertanyaan'  => $fRow[0],
                    'jawaban'     => $fRow[1],
                    'status'      => $fRow[2],
                    'versi_count' => $fRow[3],
                    'tags'        => $fRow[4],
                    'pustaka_id'  => $fRow[5],
                    'created_at'  => $baseTime->copy()->subHours($seq * 3),
                ];
                $seq++;
            }
        }

        $revisionNotes = [
            1 => ' (Edisi awal panduan akademik sebelum penyesuaian prosedur daring).',
            2 => ' (Pembaruan alur layanan melalui portal sistem informasi akademik).',
        ];

        // Bersihkan FAQ berlebih di luar 50 FAQ hasil seeder baru agar jumlah persis sesuai ketentuan
        $seededIds = array_column($allItems, 'id');
        DB::table('tfaq_validasi_log')->whereNotIn('tFaqs_id', $seededIds)->delete();
        DB::table('tlogmahasiswa')->whereNotIn('tFaqs_id', $seededIds)->delete();
        DB::table('tdetail_faqs')->whereNotIn('tFaqs_id', $seededIds)->delete();
        DB::table('tfaqsversion')->whereNotIn('tFaqs_id', $seededIds)->delete();
        DB::table('tfaqs')->whereNotIn('id', $seededIds)->delete();

        foreach ($allItems as $idx => $item) {
            $faq = Faq::updateOrCreate(
                ['id' => $item['id']],
                [
                    'id_dataentry'   => $deId,
                    'tDirektorat_id' => $item['dir_id'],
                    'pertanyaan'     => $item['pertanyaan'],
                    'created_at'     => $item['created_at'],
                    'updated_at'     => $item['created_at'],
                ]
            );

            $versiTotal = max(1, min(3, (int) $item['versi_count']));
            $createdVersionIds = [];

            // Bersihkan log lama FAQ ini agar seeder idempotent
            DB::table('tfaq_validasi_log')->where('tFaqs_id', $faq->id)->delete();

            for ($v = 1; $v <= $versiTotal; $v++) {
                $versionId = $faq->id . '-V' . $v;
                $createdVersionIds[] = $versionId;
                $isLatest = ($v === $versiTotal);

                $versionJawaban = $isLatest
                    ? $item['jawaban']
                    : rtrim((string) $item['jawaban'], '.') . '.' . ($revisionNotes[$v] ?? " (Revisi versi {$v}).");

                $versionStatus = $isLatest ? $item['status'] : 'Terbit';
                $versionValidatorId = ($versionStatus === 'Menunggu Validasi Unit' || $versionStatus === 'Draf')
                    ? null
                    : $validatorId;

                $versionTime = $item['created_at']->copy()->subDays(($versiTotal - $v) * 10);

                FaqVersion::updateOrCreate(
                    ['id' => $versionId],
                    [
                        'tDaftarPustakas_id' => $item['pustaka_id'],
                        'tFaqs_id'           => $faq->id,
                        'version_number'     => $v,
                        'id_validator'       => $versionValidatorId,
                        'jawaban'            => $versionJawaban,
                        'status'             => $versionStatus,
                        'rejected_by_stage'  => null,
                        'catatan'            => null,
                        'created_at'         => $versionTime,
                        'updated_at'         => $versionTime,
                    ]
                );

                $isPusat = ((int) $item['dir_id'] === $pusatDirId);
                FaqValidasiLog::record($faq->id, $versionId, 'de', 'ajukan', $deId, 'Mengajukan versi ' . $v . '.');
                if ($versionStatus === 'Menunggu Validasi Admik' && !$isPusat) {
                    FaqValidasiLog::record($faq->id, $versionId, 'unit', 'setujui', $validatorId, 'Disetujui pada tahap Unit.');
                } elseif (in_array($versionStatus, ['Diterima', 'Terbit'], true)) {
                    if (!$isPusat) {
                        FaqValidasiLog::record($faq->id, $versionId, 'unit', 'setujui', $validatorId, 'Disetujui pada tahap Unit.');
                    }
                    FaqValidasiLog::record($faq->id, $versionId, 'admik', 'setujui', $validatorId, 'Diterima oleh Validator Admik.');
                    if ($versionStatus === 'Terbit') {
                        FaqValidasiLog::record($faq->id, $versionId, 'admik', 'terbitkan', $validatorId, 'Diterbitkan ke portal mahasiswa.');
                    }
                }
            }

            FaqVersion::where('tFaqs_id', $faq->id)
                ->whereNotIn('id', $createdVersionIds)
                ->delete();

            FaqVersion::syncLatestFlag($faq->id);

            $tagIds = [];
            foreach ($item['tags'] as $tName) {
                if (isset($tagMap[$tName])) {
                    $tagIds[] = $tagMap[$tName];
                }
            }
            $faq->tags()->sync($tagIds);
        }
    }
}

<?php

namespace App\Services;

use App\Contracts\Search\FaqRetrieverInterface;
use App\Contracts\Search\SemanticRetrieverInterface;
use App\Models\Faq;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FaqSimilarityService implements FaqRetrieverInterface
{
    // =========================================================================
    // KELOMPOK 1: KONFIGURASI, PARAMETER BM25/RRF, & KAMUS BAHASA (LEXICON)
    // Mengatur strategi ranking, kamus kata tidak baku, singkatan resmi,
    // sinonim konservatif, serta daftar kata yang dilindungi maknanya.
    // =========================================================================

    public const STRATEGY_BM25              = 'bm25';
    public const STRATEGY_BM25_TRIGRAM      = 'bm25_trigram';
    public const STRATEGY_BM25_DICE_TRIGRAM = 'bm25_dice_trigram';
    public const STRATEGY_LEGACY            = 'legacy';

    /**
     * Default strategi hasil kalibrasi benchmark (dapat di-override per panggilan untuk evaluasi A/B/C).
     */
    private string $defaultStrategy;

    private float $bm25K1;
    private float $bm25B;
    private int $rrfK;

    /**
     * Cache indeks ter-tokenisasi di memori proses untuk mencegah tokenisasi berulang atas korpus yang sama.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $preparedCorpusCache = [];

    /**
     * 1. Normalisasi bentuk tidak baku / percakapan ke bentuk kanonis tunggal (1-ke-1, aman).
     */
    private const INFORMAL_CANONICAL_MAP = [
        'gimana'      => 'bagaimana',
        'gmn'         => 'bagaimana',
        'bgmn'        => 'bagaimana',
        'bagimana'    => 'bagaimana',
        'nggak'       => 'tidak',
        'gak'         => 'tidak',
        'ga'          => 'tidak',
        'enggak'      => 'tidak',
        'tak'         => 'tidak',
        'blm'         => 'belum',
        'belom'       => 'belum',
        'sdh'         => 'sudah',
        'udh'         => 'sudah',
        'udah'        => 'sudah',
        'jgn'         => 'jangan',
        'dpt'         => 'dapat',
        'bs'          => 'bisa',
        'hrs'         => 'harus',
        'yg'          => 'yang',
        'utk'         => 'untuk',
        'dgn'         => 'dengan',
        'dr'          => 'dari',
        'pd'          => 'pada',
        'dlm'         => 'dalam',
        'krn'         => 'karena',
        'karna'       => 'karena',
        'caranya'     => 'cara',
        'syaratnya'   => 'syarat',
        'persyaratan' => 'syarat',
        'ngurus'      => 'mengurus',
        'urus'        => 'mengurus',
        'ngajuin'     => 'mengajukan',
        'ajuin'       => 'mengajukan',
        'ngisi'       => 'mengisi',
        'nyusun'      => 'menyusun',
        'ngambil'     => 'mengambil',
        'nyicil'      => 'cicilan',
        'cicil'       => 'cicilan',
        'bikin'       => 'membuat',
        'minjem'      => 'peminjaman',
        'minjam'      => 'peminjaman',
        'pinjam'      => 'peminjaman',
        'krsan'       => 'krs',
        'matkul'      => 'mata kuliah',
        'prodi'       => 'program studi',
        'jurusan'     => 'program studi',
        'lab'         => 'laboratorium',
        'sempro'      => 'seminar proposal',
        'koas'        => 'kepaniteraan klinik',
        'inhal'       => 'praktikum susulan',
        'remed'       => 'remedi',
        'remedial'    => 'remedi',
        'nonskripsi'  => 'non_skripsi',
    ];

    /**
     * 2. Singkatan akademik terverifikasi di lingkungan KMS/UBAYA (token singkatan asli tetap dipertahankan).
     *
     * @var array<string, string[]>
     */
    private const VERIFIED_ABBREVIATION_MAP = [
        'krs'    => ['kartu', 'rencana', 'studi'],
        'khs'    => ['kartu', 'hasil', 'studi'],
        'ktm'    => ['kartu', 'tanda', 'mahasiswa'],
        'ta'     => ['tugas', 'akhir'],
        'kp'     => ['kerja', 'praktik'],
        'mk'     => ['mata', 'kuliah'],
        'pspa'   => ['profesi', 'apoteker'],
        'pkpa'   => ['praktik', 'kerja', 'profesi', 'apoteker'],
        'ukmppd' => ['ujian', 'kompetensi', 'profesi', 'dokter'],
        'laf'    => ['laminar', 'air', 'flow'],
        'apd'    => ['alat', 'pelindung', 'diri'],
        'hki'    => ['hak', 'kekayaan', 'intelektual'],
        'fbe'    => ['fakultas', 'bisnis', 'ekonomika'],
        'fik'    => ['fakultas', 'industri', 'kreatif'],
        'fh'     => ['fakultas', 'hukum'],
        'fk'     => ['fakultas', 'kedokteran'],
        'ft'     => ['fakultas', 'teknik'],
        'fpsi'   => ['fakultas', 'psikologi'],
        'ftb'    => ['fakultas', 'teknobiologi'],
        'baak'   => ['administrasi', 'akademik'],
        'skl'    => ['surat', 'keterangan', 'lulus'],
    ];

    /**
     * 3. Sinonim domain konservatif (hanya diberi bobot ringan 0.45x pada BM25 agar kata asli tetap dominan).
     * Catatan: SPP vs UKT dan Wisuda vs Yudisium TIDAK disamakan langsung tanpa konfirmasi DAA UBAYA.
     *
     * @var array<string, string[]>
     */
    private const CONSERVATIVE_DOMAIN_SYNONYMS = [
        'prosedur'   => ['alur', 'mekanisme', 'cara'],
        'alur'       => ['prosedur', 'mekanisme', 'cara'],
        'mekanisme'  => ['prosedur', 'alur'],
        'tata'       => ['prosedur'],
        'skripsi'    => ['tugas', 'akhir'],
        'magang'     => ['kerja', 'praktik'],
        'legalisir'  => ['legalisasi'],
        'keringanan' => ['cicilan', 'penyesuaian'],
        'cicilan'    => ['keringanan', 'penyesuaian'],
        'ukt'        => ['biaya', 'kuliah'],
        'bpp'        => ['biaya', 'kuliah'],
        'spp'        => ['biaya', 'kuliah'],
        'skor'       => ['nilai'],
        'toefl'      => ['ept'],
        'ept'        => ['toefl'],
        'hilang'     => ['kehilangan'],
        'unduh'      => ['download'],
        'download'   => ['unduh'],
        'unggah'     => ['upload'],
        'upload'     => ['unggah'],
    ];

    /**
     * Kata hubung murni yang netral terhadap makna (AMAN dihapus dari token inti).
     * Negasi, modalitas, aspek waktu, dan batas kuantitas DILARANG masuk ke daftar ini.
     */
    private const LOW_WEIGHT_STOPWORDS = [
        'yang', 'di', 'ke', 'dari', 'pada', 'dalam', 'untuk', 'dengan', 'dan', 'atau',
        'ini', 'itu', 'adalah', 'ialah', 'merupakan', 'jika', 'kalau', 'bila', 'apabila',
        'saya', 'aku', 'kami', 'kita', 'anda', 'kamu', 'nya', 'ya', 'dong', 'sih', 'nih',
        'deh', 'kok', 'pun', 'lah', 'kah', 'secara', 'bagi', 'tentang', 'seputar', 'hal',
        'tersebut', 'para', 'oleh', 'agar', 'supaya', 'karena', 'sebagai', 'saat', 'ketika',
    ];

    /**
     * Kata tanya umum: dipisahkan dari stopword biasa agar tidak menjatuhkan skor saat kata tanya berbeda
     * (mis. "berapa" vs "bagaimana"), namun tetap tercatat untuk sinyal kesesuaian tipe pertanyaan.
     */
    private const QUESTION_WORDS = [
        'apa', 'apakah', 'bagaimana', 'berapa', 'siapa', 'kapan', 'dimana', 'kemana', 'mana', 'kenapa', 'mengapa',
    ];

    private const NEGATION_TERMS = [
        'tidak', 'bukan', 'belum', 'jangan', 'tanpa', 'dilarang', 'non', 'gagal', 'batal',
    ];

    private const MODALITY_TERMS = [
        'boleh', 'diperbolehkan', 'diizinkan', 'bisa', 'dapat', 'harus', 'wajib', 'mewajibkan', 'perlu', 'dilarang',
    ];

    private const ASPECT_TERMS = [
        'sudah', 'telah', 'belum', 'masih', 'akan', 'sebelum', 'sesudah', 'setelah', 'selama',
    ];

    private const BOUND_TERMS = [
        'maksimal', 'minimal', 'batas', 'kurang', 'lebih', 'hanya', 'semua', 'sebagian', 'kecuali', 'bebas',
    ];

    private const ACADEMIC_CODE_TERMS = [
        'krs', 'khs', 'ktm', 'ukt', 'bpp', 'spp', 'sks', 'ipk', 'ips', 'ta', 'kp', 'mk',
        'pspa', 'pkpa', 'ukmppd', 'osce', 'mbkm', 'toefl', 'ept', 'tpa', 'hki', 'laf',
        'bsl', 'pcr', 'apd', 'b3', 'k3', 'sso', 'vpn', 'skl', 'baak', 's1', 's2', 's3', 'd3', 'd4',
        'ft', 'fh', 'fk', 'fbe', 'fik', 'fpsi', 'ftb',
    ];

    private const FACULTY_CONFLICT_GROUPS = [
        'ft'        => ['teknik', 'ft'],
        'farmasi'   => ['farmasi', 'pspa', 'pkpa', 'apoteker'],
        'fbe'       => ['fbe', 'ekonomika'],
        'fk'        => ['kedokteran', 'fk', 'koas', 'ukmppd', 'osce'],
        'fik'       => ['fik', 'kreatif'],
        'fh'        => ['hukum', 'fh'],
        'psikologi' => ['psikologi', 'fpsi'],
        'ftb'       => ['teknobiologi', 'ftb', 'bioteknologi'],
    ];

    /**
     * Tag yang terlalu generik sehingga diberi bobot sangat rendah agar tidak memicu false-positive lintas topik.
     */
    private const GENERIC_TAGS = [
        'umum', 'akademik', 'layanan mahasiswa', 'administrasi akademik',
    ];

    /**
     * Pemetaan akar kata morfologis Bahasa Indonesia yang aman dan terverifikasi untuk domain akademik.
     */
    private const SAFE_ROOT_MAP = [
        'mengisi'       => 'isi',
        'pengisian'     => 'isi',
        'mengajukan'    => 'aju',
        'pengajuan'     => 'aju',
        'diajukan'      => 'aju',
        'mendaftar'     => 'daftar',
        'mendaftarkan'  => 'daftar',
        'didaftarkan'   => 'daftar',
        'pendaftaran'   => 'daftar',
        'terdaftar'     => 'daftar',
        'meminjam'      => 'pinjam',
        'peminjaman'    => 'pinjam',
        'dipinjam'      => 'pinjam',
        'membayar'      => 'bayar',
        'pembayaran'    => 'bayar',
        'dibayar'       => 'bayar',
        'mengurus'      => 'urus',
        'pengurusan'    => 'urus',
        'mengunduh'     => 'unduh',
        'diunduh'       => 'unduh',
        'mengunggah'    => 'unggah',
        'diunggah'      => 'unggah',
        'melunasi'      => 'lunas',
        'pelunasan'     => 'lunas',
        'mengikuti'     => 'ikut',
        'diikuti'       => 'ikut',
        'mengulang'     => 'ulang',
        'pengulangan'   => 'ulang',
        'memakai'       => 'pakai',
        'pemakaian'     => 'pakai',
        'menggunakan'   => 'guna',
        'penggunaan'    => 'guna',
        'digunakan'     => 'guna',
        'menyelesaikan' => 'selesai',
        'penyelesaian'  => 'selesai',
        'diselesaikan'  => 'selesai',
        'menyerahkan'   => 'serah',
        'penyerahan'    => 'serah',
        'melampirkan'   => 'lampir',
        'lampiran'      => 'lampir',
        'mencetak'      => 'cetak',
        'pencetakan'    => 'cetak',
        'membatalkan'   => 'batal',
        'pembatalan'    => 'batal',
        'dibatalkan'    => 'batal',
        'menolak'       => 'tolak',
        'ditolak'       => 'tolak',
        'penolakan'     => 'tolak',
        'menyetujui'    => 'setuju',
        'disetujui'     => 'setuju',
        'persetujuan'   => 'setuju',
        'pengesahan'    => 'sah',
        'disahkan'      => 'sah',
        'kelulusan'     => 'lulus',
        'kehilangan'    => 'hilang',
        'keringanan'    => 'ringan',
        'penyesuaian'   => 'sesuai',
        'diperbolehkan' => 'boleh',
        'memperbolehkan'=> 'boleh',
        'diizinkan'     => 'izin',
        'mengizinkan'   => 'izin',
        'perizinan'     => 'izin',
        'mewajibkan'    => 'wajib',
        'diwajibkan'    => 'wajib',
        'kewajiban'     => 'wajib',
        'memilih'       => 'pilih',
        'pemilihan'     => 'pilih',
        'mengakses'     => 'akses',
        'diakses'       => 'akses',
        'mengambil'     => 'ambil',
        'pengambilan'   => 'ambil',
        'mengumpulkan'  => 'kumpul',
        'pengumpulan'   => 'kumpul',
        'menempuh'      => 'tempuh',
        'ditempuh'      => 'tempuh',
        'membuat'       => 'buat',
        'pembuatan'     => 'buat',
    ];

    /**
     * Daftar kata yang DILARANG dipotong oleh stemmer regex karena pemotongan prefiks/sufiks
     * akan merusak kata dasar atau mengubah makna istilah akademik.
     */
    private const DO_NOT_STEM = [
        'tidak', 'bukan', 'belum', 'jangan', 'tanpa', 'dilarang', 'boleh', 'bisa', 'dapat',
        'harus', 'wajib', 'perlu', 'sudah', 'telah', 'masih', 'akan', 'sebelum', 'sesudah',
        'setelah', 'selama', 'maksimal', 'minimal', 'batas', 'kurang', 'lebih', 'hanya',
        'semua', 'sebagian', 'kecuali', 'bebas', 'belajar', 'pelajar', 'pengajar', 'mengajar',
        'pelajaran', 'pengajaran', 'peradilan', 'pengadilan', 'kejaksaan', 'kesehatan',
        'keselamatan', 'kepaniteraan', 'kemahasiswaan', 'kefarmasian', 'keuangan', 'kebersihan',
        'ketentuan', 'keterangan', 'kelaikan', 'keperluan', 'kemahiran', 'kompetensi', 'konversi',
        'konseling', 'komprehensif', 'laboratorium', 'praktikum', 'praktikan', 'penelitian',
        'pendidikan', 'peralatan', 'perangkat', 'perpustakaan', 'perusahaan', 'permohonan',
        'permasalahan', 'pertanyaan', 'perawatan', 'perwalian', 'semester', 'seminar', 'sertifikat',
        'skripsi', 'stase', 'studio', 'standar', 'sterilisasi', 'surat', 'survei', 'syarat',
        'transkrip', 'tanggungan', 'teknik', 'teknobiologi', 'terbit', 'terlambat', 'turnitin',
        'tunjangan', 'tugas', 'ujian', 'universitas', 'wisuda', 'yudisium', 'apotek', 'apoteker',
        'autoklaf', 'beasiswa', 'berkas', 'biaya', 'bisnis', 'cicilan', 'delegasi', 'departemen',
        'desain', 'dispensasi', 'dokter', 'fakultas', 'farmasi', 'fasilitas', 'galeri', 'hukum',
        'ijazah', 'industri', 'inhal', 'instansi', 'instrumen', 'investasi', 'jadwal', 'jurnal',
        'kalender', 'kampus', 'kartu', 'karya', 'klinik', 'klinis', 'komite', 'kuliah', 'kultur',
        'kurikulum', 'lapangan', 'laporan', 'layanan', 'limbah', 'logbook', 'lomba', 'magang',
        'magister', 'mahasiswa', 'manekin', 'masuk', 'media', 'medis', 'metodologi', 'mikrobiologi',
        'mitra', 'modul', 'multimeter', 'naskah', 'nilai', 'observasi', 'osiloskop', 'pameran',
        'panduan', 'pasfoto', 'pasar', 'pasien', 'pedoman', 'pekan', 'pembimbing', 'peminatan',
        'penguji', 'perdata', 'pidana', 'plagiarisme', 'politeknik', 'portal', 'portofolio',
        'praktik', 'preseptor', 'prestasi', 'profesi', 'program', 'prosedur', 'proyek', 'psikolog',
        'psikologi', 'puskesmas', 'putusan', 'reagen', 'registrasi', 'rekomendasi', 'remedi',
        'rencana', 'reservasi', 'responden', 'riset', 'robotika', 'ruang', 'rumah', 'sakit',
        'sampel', 'sarjana', 'seleksi', 'sendiri', 'sentra', 'sepatu', 'sidang', 'sistem',
        'spektrofotometer', 'studi', 'susulan', 'tahun', 'teknisi', 'tekstil', 'tempat', 'toefl',
        'tracer', 'uang', 'ulang', 'umum', 'usulan', 'validasi', 'virtual', 'vokasi', 'waktu',
        'wawancara', 'workshop',
    ];

    public function __construct(
        string $defaultStrategy = self::STRATEGY_BM25_TRIGRAM,
        float $bm25K1 = 1.2,
        float $bm25B = 0.75,
        int $rrfK = 20
    ) {
        $this->defaultStrategy = $defaultStrategy;
        $this->bm25K1 = max(0.1, $bm25K1);
        $this->bm25B  = min(1.0, max(0.0, $bm25B));
        $this->rrfK   = max(1, $rrfK);
    }

    /**
     * Ekspor kamus & aturan normalisasi untuk sinkronisasi pencarian client-side (FAQ Mahasiswa & Guest).
     *
     * @return array<string, mixed>
     */
    public static function clientLexicon(): array
    {
        return [
            'informal_map'     => self::INFORMAL_CANONICAL_MAP,
            'abbreviations'    => self::VERIFIED_ABBREVIATION_MAP,
            'domain_synonyms'  => self::CONSERVATIVE_DOMAIN_SYNONYMS,
            'stopwords'        => self::LOW_WEIGHT_STOPWORDS,
            'question_words'   => self::QUESTION_WORDS,
            'negations'        => self::NEGATION_TERMS,
            'modalities'       => self::MODALITY_TERMS,
            'aspects'          => self::ASPECT_TERMS,
            'bounds'           => self::BOUND_TERMS,
            'academic_codes'   => self::ACADEMIC_CODE_TERMS,
            'faculty_groups'   => self::FACULTY_CONFLICT_GROUPS,
            'safe_roots'       => self::SAFE_ROOT_MAP,
            'do_not_stem'      => self::DO_NOT_STEM,
            // Legacy alias key agar kompatibel bila ada pemanggil lama
            'synonyms'         => array_merge(
                self::INFORMAL_CANONICAL_MAP,
                array_map(fn (array $arr) => implode(' ', $arr), self::VERIFIED_ABBREVIATION_MAP)
            ),
            'bm25'             => ['k1' => 1.2, 'b' => 0.75, 'rrf_k' => 20],
        ];
    }

    // =========================================================================
    // KELOMPOK 2: NORMALISASI TEKS, STEMMING KONSERVATIF, & TOKENISASI
    // Membersihkan tanda baca, menjaga angka/kode, mencari kata dasar aman,
    // dan memecah kalimat menjadi token BM25 (TF utuh) + fitur terproteksi.
    // =========================================================================

    /**
     * 1. Normalisasi teks mentah secara aman:
     *    - pertahankan angka desimal (2.75 / 2,75 -> 2.75), kode tenggat (h-3, h-1), dan kata majemuk non-skripsi
     *    - hapus HTML & tanda baca lainnya
     */
    public function normalizeRaw(string $text): string
    {
        $clean = strip_tags($text);
        $clean = mb_strtolower($clean, 'UTF-8');

        // Samakan format angka desimal koma ke titik (contoh: 2,75 -> 2.75)
        $clean = preg_replace('/(\d),(\d)/u', '$1.$2', $clean) ?? $clean;

        // Pertahankan bentuk khusus ber-strip yang bermakna di aturan akademik (contoh: non-skripsi -> non_skripsi, h-3 -> h_3, s-1 -> s1)
        $clean = preg_replace('/\bnon\s*-\s*([a-z0-9]+)/u', 'non_$1', $clean) ?? $clean;
        $clean = preg_replace('/\bh\s*-\s*(\d+)\b/u', 'h_$1', $clean) ?? $clean;
        $clean = preg_replace('/\b([sd])\s*-\s*([1234])\b/u', '$1$2', $clean) ?? $clean;

        // Lindungi titik desimal di antara angka sementara tanda baca lain dibersihkan
        $clean = preg_replace('/(\d)\.(\d)/u', '$1__DEC__$2', $clean) ?? $clean;
        $clean = preg_replace('/[^\p{L}\p{N}_\s]/u', ' ', $clean) ?? $clean;
        $clean = str_replace('__DEC__', '.', $clean);
        $clean = preg_replace('/\s+/u', ' ', trim($clean)) ?? '';

        return $clean;
    }

    /**
     * Stemmer konservatif Bahasa Indonesia:
     * - mendahulukan kamus morfologi terverifikasi (SAFE_ROOT_MAP)
     * - melindungi kata dasar/istilah akademik pada DO_NOT_STEM, angka, dan kode
     * - hanya memotong imbuhan sederhana bila akar kata yang tersisa >= 5 huruf agar tidak merusak arti
     */
    public function stemConservative(string $word): string
    {
        if (isset(self::SAFE_ROOT_MAP[$word])) {
            return self::SAFE_ROOT_MAP[$word];
        }

        if (
            mb_strlen($word, 'UTF-8') <= 5 ||
            in_array($word, self::DO_NOT_STEM, true) ||
            in_array($word, self::ACADEMIC_CODE_TERMS, true) ||
            str_contains($word, '_') ||
            preg_match('/\d/', $word)
        ) {
            return $word;
        }

        $candidate = preg_replace('/(lah|kah|nya|ku|mu)$/u', '', $word) ?? $word;
        if ($candidate !== $word && mb_strlen($candidate, 'UTF-8') >= 5) {
            if (isset(self::SAFE_ROOT_MAP[$candidate])) {
                return self::SAFE_ROOT_MAP[$candidate];
            }
            return $candidate;
        }

        return $word;
    }

    /**
     * Representasi token terpisah (Section C.4):
     * - bm25_tokens: mempertahankan frekuensi kemunculan kata (TF utuh, BUKAN array_unique)
     * - domain_tokens: ekspansi sinonim domain konservatif (diberi bobot rendah pada BM25)
     * - unique_tokens: himpunan token unik untuk Sorensen-Dice
     * - protected: fitur negasi, bigram negasi, modalitas, aspek waktu, batas kuantitas, angka, dan kode akademik
     *
     * @return array{
     *     normalized: string,
     *     bm25_tokens: string[],
     *     domain_tokens: string[],
     *     unique_tokens: string[],
     *     question_words: string[],
     *     protected: array{
     *         negations: string[],
     *         negation_bigrams: string[],
     *         modalities: string[],
     *         aspects: string[],
     *         bounds: string[],
     *         numbers: string[],
     *         codes: string[]
     *     }
     * }
     */
    public function tokenizeText(string $text, bool $includeDomainSynonyms = true): array
    {
        $normalized = $this->normalizeRaw($text);
        $emptyProtected = [
            'negations'        => [],
            'negation_bigrams' => [],
            'modalities'       => [],
            'aspects'          => [],
            'bounds'           => [],
            'numbers'          => [],
            'codes'            => [],
        ];

        if ($normalized === '') {
            return [
                'normalized'     => '',
                'bm25_tokens'    => [],
                'domain_tokens'  => [],
                'unique_tokens'  => [],
                'question_words' => [],
                'protected'      => $emptyProtected,
            ];
        }

        // 1. Kanonisasi bentuk informal tanpa menghilangkan frekuensi kata
        $rawWords = explode(' ', $normalized);
        $canonicalWords = [];
        foreach ($rawWords as $rw) {
            $rw = trim($rw);
            if ($rw === '') {
                continue;
            }
            if (isset(self::INFORMAL_CANONICAL_MAP[$rw])) {
                foreach (explode(' ', self::INFORMAL_CANONICAL_MAP[$rw]) as $mapped) {
                    if ($mapped !== '') {
                        $canonicalWords[] = $mapped;
                    }
                }
            } else {
                $canonicalWords[] = $rw;
            }
        }

        $negations = [];
        $negationBigrams = [];
        $modalities = [];
        $aspects = [];
        $bounds = [];
        $numbers = [];
        $codes = [];
        $questionWords = [];

        $bm25Tokens = [];
        $domainTokens = [];

        $count = count($canonicalWords);
        for ($i = 0; $i < $count; $i++) {
            $w = $canonicalWords[$i];

            // Izinkan angka 1 digit (mis. semester 1, 2, 4, 6, 8) & kode >= 2 karakter
            $isNumber = (bool) preg_match('/^\d+(?:\.\d+)?$/', $w) || (bool) preg_match('/^h_\d+$/', $w) || (bool) preg_match('/^\d+x\d+$/', $w);
            $isAlphaNumCode = !$isNumber && (bool) preg_match('/^(?:[a-z]{1,4}\d{1,4}|[sd][1234]|b3|k3)$/u', $w);

            if (!$isNumber && mb_strlen($w, 'UTF-8') < 2) {
                continue;
            }

            if (in_array($w, self::QUESTION_WORDS, true)) {
                $questionWords[] = $w;
                continue;
            }

            // Catat fitur terproteksi (negasi, modalitas, aspek, batas, angka, kode)
            if (in_array($w, self::NEGATION_TERMS, true) || str_starts_with($w, 'non_')) {
                $negations[] = $w;
                if (str_starts_with($w, 'non_')) {
                    $negationBigrams[] = $w;
                }
                // Bentuk bigram negasi dengan kata berikutnya (contoh: tidak_boleh, tidak_wajib, belum_lulus, tanpa_skripsi)
                if (in_array($w, ['tidak', 'belum', 'tanpa', 'bukan', 'jangan', 'dilarang'], true) && isset($canonicalWords[$i + 1])) {
                    $next = $canonicalWords[$i + 1];
                    if (!in_array($next, self::LOW_WEIGHT_STOPWORDS, true) && mb_strlen($next, 'UTF-8') >= 2) {
                        $nextRoot = $this->stemConservative($next);
                        $bigram = $w . '_' . $nextRoot;
                        $negationBigrams[] = $bigram;
                        $bm25Tokens[] = $bigram;
                    }
                }
            }

            if (in_array($w, self::MODALITY_TERMS, true)) {
                $modalities[] = $this->stemConservative($w);
            }
            if (in_array($w, self::ASPECT_TERMS, true)) {
                $aspects[] = $w;
            }
            if (in_array($w, self::BOUND_TERMS, true)) {
                $bounds[] = $w;
            }
            if ($isNumber) {
                $numbers[] = $w;
            }
            if ($isAlphaNumCode || in_array($w, self::ACADEMIC_CODE_TERMS, true)) {
                $codes[] = $w;
            }

            if (in_array($w, self::LOW_WEIGHT_STOPWORDS, true)) {
                continue;
            }

            // Pertahankan token asli di urutan BM25 (TF tetap terjaga)
            $bm25Tokens[] = $w;

            // Tambahkan akar kata konservatif bila berbeda
            $root = $this->stemConservative($w);
            if ($root !== $w && mb_strlen($root, 'UTF-8') >= 3) {
                $bm25Tokens[] = $root;
            }

            // Ekspansi singkatan terverifikasi (1-ke-bentuk resmi)
            if (isset(self::VERIFIED_ABBREVIATION_MAP[$w])) {
                foreach (self::VERIFIED_ABBREVIATION_MAP[$w] as $exp) {
                    $bm25Tokens[] = $exp;
                }
            }

            // Ekspansi sinonim domain konservatif dipisah ke domain_tokens (bobot rendah)
            if ($includeDomainSynonyms && isset(self::CONSERVATIVE_DOMAIN_SYNONYMS[$w])) {
                foreach (self::CONSERVATIVE_DOMAIN_SYNONYMS[$w] as $syn) {
                    $domainTokens[] = $syn;
                }
            }
        }

        if (empty($bm25Tokens)) {
            $bm25Tokens = array_values(array_filter(
                $canonicalWords,
                fn (string $w) => mb_strlen($w, 'UTF-8') >= 2 || preg_match('/^\d+$/', $w)
            ));
        }

        return [
            'normalized'     => $normalized,
            'bm25_tokens'    => $bm25Tokens,
            'domain_tokens'  => $domainTokens,
            'unique_tokens'  => array_values(array_unique($bm25Tokens)),
            'question_words' => array_values(array_unique($questionWords)),
            'protected'      => [
                'negations'        => array_values(array_unique($negations)),
                'negation_bigrams' => array_values(array_unique($negationBigrams)),
                'modalities'       => array_values(array_unique($modalities)),
                'aspects'          => array_values(array_unique($aspects)),
                'bounds'           => array_values(array_unique($bounds)),
                'numbers'          => array_values(array_unique($numbers)),
                'codes'            => array_values(array_unique($codes)),
            ],
        ];
    }

    /**
     * Dipertahankan untuk kompatibilitas pemanggil lama yang membutuhkan daftar token unik ternormalisasi.
     *
     * @return string[]
     */
    public function extractNormalizedTokens(string $text): array
    {
        return $this->tokenizeText($text)['unique_tokens'];
    }

    // =========================================================================
    // KELOMPOK 3: PENGAMBILAN KANDIDAT FAQ (INTERNAL VS PUBLIK) & CACHE
    // Memisahkan kandidat deteksi duplikat internal (semua status) dari
    // kandidat pencarian publik Mahasiswa/Guest (HANYA versi berstatus Terbit).
    // =========================================================================

    public function formatStatusLabel(string $rawStatus): string
    {
        $st = strtolower(trim($rawStatus));
        if (in_array($st, ['active', 'terbit', 'publish'], true)) {
            return 'Terbit';
        }
        if (in_array($st, ['approved', 'accepted', 'disetujui', 'diterima'], true)) {
            return 'Diterima';
        }
        if ($st === 'menunggu validasi unit') {
            return 'Menunggu Validasi Unit';
        }
        if (in_array($st, ['submitted', 'pending', 'menunggu validasi', 'menunggu validasi admik'], true)) {
            return 'Menunggu Validasi Admik';
        }
        if (in_array($st, ['rejected', 'ditolak'], true)) {
            return 'Ditolak';
        }
        if (in_array($st, ['draft', 'draf'], true)) {
            return 'Draf';
        }

        return ucfirst($rawStatus ?: 'Draf');
    }

    /**
     * Signature ringan berbasis timestamp & jumlah baris untuk invalidasi otomatis cache kandidat
     * begitu ada FAQ/versi yang ditambah, diubah, diterbitkan, atau dicabut.
     */
    private function getCorpusCacheSignature(string $scope): string
    {
        try {
            $faqAgg = DB::table('tfaqs')
                ->selectRaw('COUNT(*) as cnt, MAX(updated_at) as max_u')
                ->first();
            $verAgg = DB::table('tfaqsversion')
                ->selectRaw('COUNT(*) as cnt, MAX(updated_at) as max_u')
                ->first();
            $tagCnt = Schema::hasTable('tdetail_faqs') ? DB::table('tdetail_faqs')->count() : 0;

            return implode(':', [
                $scope,
                (int) ($faqAgg->cnt ?? 0),
                (string) ($faqAgg->max_u ?? '0'),
                (int) ($verAgg->cnt ?? 0),
                (string) ($verAgg->max_u ?? '0'),
                $tagCnt,
            ]);
        } catch (Throwable $e) {
            return $scope . ':nocache:' . microtime(true);
        }
    }

    /**
     * USE CASE 1 (INTERNAL): Ambil kandidat FAQ lintas status untuk deteksi duplikat Data Entry, Validator, & Import.
     * Menyertakan tag dan alternatif pertanyaan (bila tabel tersedia) tanpa N+1 query.
     */
    public function getCandidateFaqs(?string $excludeFaqId = null): Collection
    {
        $loadAll = function (): array {
            $relations = [
                'direktorat:id,nama',
                'tags:id,nama',
                'versions' => function ($q) {
                    $q->select(['id', 'tFaqs_id', 'version_number', 'jawaban', 'status', 'is_latest', 'updated_at'])
                      ->orderByDesc('version_number')
                      ->orderByDesc('updated_at');
                },
            ];

            $faqs = Faq::select(['id', 'tDirektorat_id', 'pertanyaan', 'updated_at'])
                ->with($relations)
                ->get();

            $rows = [];
            foreach ($faqs as $faq) {
                $relevantVersion = $faq->versions->first();
                $status = $relevantVersion ? (string) $relevantVersion->status : 'Draf';
                $isTarget = in_array(strtolower($status), [
                    'terbit', 'diterima', 'menunggu validasi', 'menunggu validasi unit', 'menunggu validasi admik',
                    'draf', 'ditolak', 'active', 'approved', 'accepted', 'submitted', 'pending', 'draft', 'rejected',
                ], true);

                if (!$isTarget) {
                    continue;
                }

                $tags = $faq->relationLoaded('tags')
                    ? $faq->tags->pluck('nama')->map(fn ($t) => (string) $t)->values()->all()
                    : [];

                $rows[] = [
                    'id'               => (string) $faq->id,
                    'pertanyaan'       => (string) $faq->pertanyaan,
                    'jawaban'          => (string) ($relevantVersion?->jawaban ?? ''),
                    'unit_id'          => (int) ($faq->tDirektorat_id ?: 1),
                    'unit_nama'        => $faq->direktorat?->nama ?: 'Direktorat Akademik',
                    'status'           => $status,
                    'status_label'     => $this->formatStatusLabel($status),
                    'version_id'       => $relevantVersion?->id ?? ($faq->id . '-V1'),
                    'tags'             => $tags,
                    'is_target_status' => true,
                    'all_versions'     => $faq->versions->map(fn ($v) => [
                        'id'           => (string) $v->id,
                        'status'       => (string) $v->status,
                        'status_label' => $this->formatStatusLabel((string) $v->status),
                        'jawaban'      => (string) $v->jawaban,
                        'norm_jawaban' => $this->normalizeRaw((string) $v->jawaban),
                    ])->values()->all(),
                ];
            }

            return $rows;
        };

        try {
            $sig = 'kms_sim_internal_v2:' . md5($this->getCorpusCacheSignature('internal'));
            $allRows = Cache::remember($sig, 120, $loadAll);
        } catch (Throwable $e) {
            $allRows = $loadAll();
        }

        $collection = collect($allRows);
        if ($excludeFaqId !== null && $excludeFaqId !== '') {
            $collection = $collection->filter(fn (array $r) => $r['id'] !== $excludeFaqId)->values();
        }

        return $collection;
    }

    /**
     * USE CASE 2 (PUBLIK): Ambil HANYA FAQ & versi yang benar-benar telah dipublikasikan (status = 'Terbit')
     * untuk pencarian Mahasiswa dan Guest. Draf, Menunggu Validasi, Diterima (belum Terbit), Ditolak,
     * serta versi lama yang sudah digantikan versi Terbit baru tidak pernah ikut dimuat.
     */
    public function getPublishedCandidateFaqs(?int $direktoratId = null): Collection
    {
        $loadPublished = function (): array {
            $relations = [
                'direktorat:id,nama',
                'tags:id,nama',
                'versions' => function ($q) {
                    $q->select(['id', 'tFaqs_id', 'tDaftarPustakas_id', 'version_number', 'jawaban', 'status', 'updated_at'])
                      ->where('status', 'Terbit')
                      ->orderByDesc('version_number')
                      ->orderByDesc('updated_at');
                },
                'versions.daftarPustaka',
            ];

            $faqs = Faq::select(['id', 'tDirektorat_id', 'pertanyaan', 'updated_at'])
                ->whereHas('versions', fn ($q) => $q->where('status', 'Terbit'))
                ->with($relations)
                ->get();

            $rows = [];
            foreach ($faqs as $faq) {
                $publishedVersion = $faq->versions->first();
                if (!$publishedVersion || strtolower((string) $publishedVersion->status) !== 'terbit') {
                    continue;
                }

                $pustaka = $publishedVersion->daftarPustaka;
                $tags = $faq->relationLoaded('tags')
                    ? $faq->tags->pluck('nama')->map(fn ($t) => (string) $t)->values()->all()
                    : [];

                $rows[] = [
                    'id'           => (string) $faq->id,
                    'pertanyaan'   => (string) $faq->pertanyaan,
                    'jawaban'      => (string) $publishedVersion->jawaban,
                    'unit_id'      => (int) ($faq->tDirektorat_id ?: 1),
                    'unit_nama'    => $faq->direktorat?->nama ?: 'Direktorat Akademik',
                    'status'       => 'Terbit',
                    'status_label' => 'Terbit',
                    'version_id'   => (string) $publishedVersion->id,
                    'version_num'  => (int) ($publishedVersion->version_number ?: 1),
                    'tags'         => $tags,
                    'source_label' => $pustaka ? ($pustaka->judul_tampil ?: $pustaka->link) : null,
                    'source_url'   => ($pustaka && $pustaka->resolved_url !== '') ? $pustaka->resolved_url : null,
                ];
            }

            return $rows;
        };

        try {
            $sig = 'kms_sim_published_v2:' . md5($this->getCorpusCacheSignature('published'));
            $rows = Cache::remember($sig, 120, $loadPublished);
        } catch (Throwable $e) {
            $rows = $loadPublished();
        }

        $collection = collect($rows);
        if ($direktoratId !== null && $direktoratId > 0) {
            $collection = $collection->filter(fn (array $r) => (int) $r['unit_id'] === $direktoratId)->values();
        }

        return $collection;
    }

    /**
     * Cek apakah ada FAQ dengan kondisi Draf yang memiliki Pertanyaan DAN Jawaban yang sama persis.
     */
    public function findExactDraftMatch(
        string $inputQuestion,
        string $inputAnswer,
        ?string $excludeFaqId = null,
        ?Collection $preloadedCandidates = null
    ): ?array {
        $normQ = $this->normalizeRaw($inputQuestion);
        $normA = $this->normalizeRaw($inputAnswer);

        if ($normQ === '' || $normA === '') {
            return null;
        }

        $candidates = $preloadedCandidates ?? $this->getCandidateFaqs($excludeFaqId);
        foreach ($candidates as $cand) {
            if (($excludeFaqId !== null && ($cand['id'] ?? '') === $excludeFaqId) || $this->normalizeRaw((string) $cand['pertanyaan']) !== $normQ) {
                continue;
            }

            foreach (($cand['all_versions'] ?? []) as $ver) {
                $verNormA = $ver['norm_jawaban'] ?? $this->normalizeRaw((string) ($ver['jawaban'] ?? ''));
                if (in_array(strtolower((string) ($ver['status'] ?? '')), ['draf', 'draft'], true) && $verNormA === $normA) {
                    return [
                        'id'           => $cand['id'],
                        'version_id'   => $ver['id'],
                        'pertanyaan'   => $cand['pertanyaan'],
                        'jawaban'      => $ver['jawaban'],
                        'unit_nama'    => $cand['unit_nama'] ?? 'Direktorat Akademik',
                        'status'       => 'Draf',
                        'status_label' => 'Draf',
                    ];
                }
            }
        }

        return null;
    }

    // =========================================================================
    // KELOMPOK 4: PINTU MASUK UTAMA (DETEKSI DUPLIKAT & PENCARIAN PUBLIK)
    // Titik panggil bagi Data Entry, Validator, Import (analyzeQuestion),
    // serta Mahasiswa & Guest (searchPublishedFaqs / retrieve).
    // =========================================================================

    /**
     * USE CASE 1 (INTERNAL): Deteksi Duplikat & Kemiripan untuk Data Entry, Validator, dan Import.
     *
     * Definisi level hasil:
     * - exact: normalizedRaw(pertanyaan_input) === normalizedRaw(pertanyaan_kandidat) tanpa konflik negasi/angka/kode (skor = 100.0)
     * - high (near-duplicate): skor terkalibrasi >= 70.0 tanpa konflik negasi/angka/kode
     * - moderate (kandidat terkait): skor >= $candidateThreshold s.d. < 70.0
     */
    public function analyzeQuestion(
        string $inputQuestion,
        ?string $excludeFaqId = null,
        float $candidateThreshold = 30.0,
        ?string $inputAnswer = null,
        ?Collection $preloadedCandidates = null,
        ?string $strategy = null
    ): array {
        $t0 = hrtime(true);
        $normalizedInput = $this->normalizeRaw($inputQuestion);
        $normalizedAnswer = $inputAnswer !== null ? $this->normalizeRaw($inputAnswer) : '';

        if ($normalizedInput === '' || mb_strlen($normalizedInput, 'UTF-8') < 3) {
            return [
                'normalized_input'      => $normalizedInput,
                'has_exact_match'       => false,
                'exact_match'           => null,
                'has_exact_draft_match' => false,
                'exact_draft_match'     => null,
                'candidates'            => [],
                'max_similarity'        => 0.0,
                'abstained'             => true,
                'strategy'              => $strategy ?? $this->defaultStrategy,
                'timings_ms'            => ['db_ms' => 0.0, 'compute_ms' => 0.0, 'total_ms' => 0.0],
            ];
        }

        $tDbStart = hrtime(true);
        if ($preloadedCandidates !== null) {
            $candidatesDb = $excludeFaqId
                ? $preloadedCandidates->filter(fn ($c) => ($c['id'] ?? '') !== $excludeFaqId)->values()
                : $preloadedCandidates->values();
        } else {
            $candidatesDb = $this->getCandidateFaqs($excludeFaqId);
        }
        $dbMs = (hrtime(true) - $tDbStart) / 1e6;

        $tCompStart = hrtime(true);
        $exactDraftMatch = ($normalizedAnswer !== '')
            ? $this->findExactDraftMatch($inputQuestion, (string) $inputAnswer, $excludeFaqId, $candidatesDb)
            : null;

        $activeStrategy = $strategy ?? $this->defaultStrategy;
        $ranked = $this->rankCandidates(
            $inputQuestion,
            $candidatesDb,
            false,
            $activeStrategy,
            $candidateThreshold,
            5
        );

        $exactMatch = null;
        foreach ($ranked['all_evaluated'] as $item) {
            if ($item['is_exact']) {
                $exactMatch = $item;
                break;
            }
        }

        $computeMs = (hrtime(true) - $tCompStart) / 1e6;
        $totalMs = (hrtime(true) - $t0) / 1e6;

        return [
            'normalized_input'      => $normalizedInput,
            'has_exact_match'       => $exactMatch !== null,
            'exact_match'           => $exactMatch,
            'has_exact_draft_match' => $exactDraftMatch !== null,
            'exact_draft_match'     => $exactDraftMatch,
            'candidates'            => $ranked['results'],
            'max_similarity'        => $ranked['max_similarity'],
            'abstained'             => empty($ranked['results']),
            'strategy'              => $activeStrategy,
            'timings_ms'            => [
                'db_ms'      => round($dbMs, 3),
                'compute_ms' => round($computeMs, 3),
                'total_ms'   => round($totalMs, 3),
            ],
        ];
    }

    /**
     * USE CASE 2 (PUBLIK): Pencarian FAQ Mahasiswa & Guest (hanya versi berstatus 'Terbit').
     * Mendukung abstention (no_answer) ketika tidak ada kandidat yang memenuhi ambang relevansi & jangkar leksikal.
     *
     * @param  array<string, mixed>  $options
     * @return array{
     *     query: string,
     *     normalized_query: string,
     *     abstained: bool,
     *     strategy: string,
     *     results: array<int, array<string, mixed>>,
     *     timings_ms: array{db_ms: float, compute_ms: float, total_ms: float}
     * }
     */
    public function searchPublishedFaqs(string $query, array $options = []): array
    {
        return $this->retrieve($query, $options);
    }

    public function retrieve(string $query, array $options = []): array
    {
        $t0 = hrtime(true);
        $normalizedQuery = $this->normalizeRaw($query);
        $strategy = (string) ($options['strategy'] ?? $this->defaultStrategy);
        $threshold = isset($options['threshold']) ? (float) $options['threshold'] : 32.0;
        $limit = isset($options['limit']) ? max(1, (int) $options['limit']) : 10;
        $direktoratId = isset($options['direktorat_id']) ? (int) $options['direktorat_id'] : null;

        if ($normalizedQuery === '' || mb_strlen($normalizedQuery, 'UTF-8') < 2) {
            return [
                'query'            => $query,
                'normalized_query' => $normalizedQuery,
                'abstained'        => true,
                'strategy'         => $strategy,
                'results'          => [],
                'timings_ms'       => ['db_ms' => 0.0, 'compute_ms' => 0.0, 'total_ms' => 0.0],
            ];
        }

        $tDbStart = hrtime(true);
        /** @var Collection|null $preloaded */
        $preloaded = $options['preloaded_candidates'] ?? null;
        $candidates = $preloaded !== null
            ? $preloaded->filter(fn ($c) => strtolower((string) ($c['status'] ?? 'terbit')) === 'terbit')->values()
            : $this->getPublishedCandidateFaqs($direktoratId);
        $dbMs = (hrtime(true) - $tDbStart) / 1e6;

        $tCompStart = hrtime(true);
        $ranked = $this->rankCandidates($query, $candidates, true, $strategy, $threshold, $limit);
        $results = $ranked['results'];

        // Hook opsional untuk penggabungan RRF dengan SemanticRetrieverInterface di masa depan
        if (($options['semantic_retriever'] ?? null) instanceof SemanticRetrieverInterface) {
            /** @var SemanticRetrieverInterface $semRetriever */
            $semRetriever = $options['semantic_retriever'];
            $semResponse = $semRetriever->retrieve($query, ['limit' => $limit * 2]);
            $results = $this->fuseWithSemanticResults($results, $semResponse['results'] ?? [], $limit);
        }

        $computeMs = (hrtime(true) - $tCompStart) / 1e6;
        $totalMs = (hrtime(true) - $t0) / 1e6;

        return [
            'query'            => $query,
            'normalized_query' => $normalizedQuery,
            'abstained'        => empty($results),
            'strategy'         => $strategy,
            'results'          => $results,
            'timings_ms'       => [
                'db_ms'      => round($dbMs, 3),
                'compute_ms' => round($computeMs, 3),
                'total_ms'   => round($totalMs, 3),
            ],
        ];
    }

    // =========================================================================
    // KELOMPOK 5: MESIN PERANKINGAN (OKAPI BM25, TRIGRAM TYPO, DICE, & RRF)
    // Membangun indeks korpus di memori, menghitung skor Okapi BM25 multi-field,
    // memulihkan typo via Character Trigram, dan menggabungkan ranking via RRF.
    // =========================================================================

    /**
     * Mesin perankingan inti:
     * - Membangun representasi multi-field (pertanyaan utama, alternatif pertanyaan, tag spesifik, dan jawaban terbit untuk mode publik)
     * - Menghitung Okapi BM25 murni (TF utuh, panjang dokumen, avgdl, k1, b)
     * - Menghitung Character Trigram Dice (UTF-8) & per-word typo trigram
     * - Menghitung Token Sorensen-Dice (himpunan unik)
     * - Menggabungkan sinyal beda skala menggunakan Reciprocal Rank Fusion (RRF) + kalibrasi BM25 ternormalisasi query
     * - Memverifikasi proteksi negasi, aspek, angka, dan kode fakultas/program sebelum menetapkan skor akhir & level
     *
     * @return array{results: array<int, array<string, mixed>>, all_evaluated: array<int, array<string, mixed>>, max_similarity: float}
     */
    private function rankCandidates(
        string $rawQuery,
        Collection $candidates,
        bool $isPublicSearch,
        string $strategy,
        float $threshold,
        int $limit
    ): array {
        if ($candidates->isEmpty()) {
            return ['results' => [], 'all_evaluated' => [], 'max_similarity' => 0.0];
        }

        if ($strategy === self::STRATEGY_LEGACY) {
            return $this->rankLegacyBaseline($rawQuery, $candidates, $threshold, $limit);
        }

        $qTok = $this->tokenizeText($rawQuery, true);
        $qNorm = $qTok['normalized'];
        $qBm25Tokens = $qTok['bm25_tokens'];
        $qDomainTokens = $qTok['domain_tokens'];
        $qUnique = $qTok['unique_tokens'];
        $qTrigrams = $this->buildTrigrams($qNorm);

        $indexData = $this->buildOrGetPreparedCorpus($candidates, $isPublicSearch);
        $prepared = $indexData['prepared'];
        $docFreq = $indexData['docFreq'];
        $docCount = $indexData['docCount'];
        $avgDocLen = $indexData['avgDocLen'];
        $vocabTrigrams = $indexData['vocabTrigrams'];

        // Hitung Query TF (mempertahankan pengulangan kata pada query) + bobot ringan untuk sinonim domain konservatif
        $queryTermWeights = [];
        foreach ($qBm25Tokens as $tk) {
            $queryTermWeights[$tk] = ($queryTermWeights[$tk] ?? 0.0) + 1.0;
        }
        foreach ($qDomainTokens as $dtk) {
            if (!isset($queryTermWeights[$dtk])) {
                $queryTermWeights[$dtk] = 0.45;
            }
        }

        // Pemulihan typo berbasis Character Trigram per-kata untuk token OOV (out-of-vocabulary, df=0)
        // Hanya diaktifkan pada strategi yang menyertakan Trigram (Config B & C), tanpa menimpa angka/negasi.
        $queryWithTypoWeights = $queryTermWeights;
        $bestOovTypoSim = 0.0;
        if ($strategy !== self::STRATEGY_BM25) {
            foreach ($qBm25Tokens as $tk) {
                if (
                    isset($docFreq[$tk]) ||
                    mb_strlen($tk, 'UTF-8') < 4 ||
                    preg_match('/\d/', $tk) ||
                    in_array($tk, self::NEGATION_TERMS, true) ||
                    str_starts_with($tk, 'tidak_') ||
                    str_starts_with($tk, 'belum_')
                ) {
                    continue;
                }

                $tkTri = $this->buildTrigrams($tk);
                $bestTerm = null;
                $bestSim = 0.0;
                foreach ($vocabTrigrams as $vTerm => $vTri) {
                    $sim = $this->computeTrigramDiceFromSets($tkTri, $vTri);
                    if ($sim > $bestSim) {
                        $bestSim = $sim;
                        $bestTerm = $vTerm;
                    }
                }

                // Ambang >= 0.56 menangkap transposisi 5 huruf ("tofel"->"toefl" = 0.571) & kehilangan huruf ("yudisum"->"yudisium" = 0.737)
                if ($bestTerm !== null && $bestSim >= 0.56) {
                    $bestOovTypoSim = max($bestOovTypoSim, $bestSim);
                    $typoWeight = 0.85 * $bestSim;
                    if (!isset($queryWithTypoWeights[$bestTerm]) || $queryWithTypoWeights[$bestTerm] < $typoWeight) {
                        $queryWithTypoWeights[$bestTerm] = $typoWeight;
                    }
                }
            }
        }

        // Hitung batas teoretis BM25 ideal untuk query ini (mendukung abstention tegas pada query no_answer)
        $computeIdeal = function (array $weights) use ($docFreq, $docCount): float {
            $ideal = 0.0;
            foreach ($weights as $term => $qWeight) {
                $df = $docFreq[$term] ?? 0;
                $effectiveDf = $df > 0 ? $df : 1;
                $idf = log(1.0 + (($docCount - $effectiveDf + 0.5) / ($effectiveDf + 0.5)));
                $idealTfSat = (2.5 * ($this->bm25K1 + 1.0)) / (2.5 + $this->bm25K1);
                $ideal += $qWeight * max(0.15, $idf) * $idealTfSat;
            }
            return max(0.001, $ideal);
        };

        $idealBm25Exact = $computeIdeal($queryTermWeights);
        $idealBm25Typo = $computeIdeal($queryWithTypoWeights);

        $rawScores = [];
        foreach ($prepared as $idx => $p) {
            $bm25Exact = $this->computeOkapiBm25(
                $queryTermWeights,
                $p['weightedTf'],
                $p['weightedLen'],
                $avgDocLen,
                $docFreq,
                $docCount
            );

            $bm25Typo = ($strategy === self::STRATEGY_BM25)
                ? $bm25Exact
                : $this->computeOkapiBm25(
                    $queryWithTypoWeights,
                    $p['weightedTf'],
                    $p['weightedLen'],
                    $avgDocLen,
                    $docFreq,
                    $docCount
                );

            $charTrigram = $this->computeTrigramDiceFromSets($qTrigrams, $p['trigrams']);
            $tokenDice = $this->computeTokenDice($qUnique, $p['diceTokens']);

            $rawScores[$idx] = [
                'bm25'           => $bm25Typo,
                'bm25_exact'     => $bm25Exact,
                'bm25_norm'      => min(1.0, $bm25Exact / $idealBm25Exact),
                'bm25_typo_norm' => min(1.0, $bm25Typo / $idealBm25Typo),
                'trigram'        => $charTrigram,
                'dice'           => $tokenDice,
            ];
        }

        // Reciprocal Rank Fusion (RRF): menggabungkan peringkat dari metrik berbeda skala secara bebas-skala
        // RRF(d) = sum_m w_m * (1 / (k_rrf + rank_m(d)))
        $bm25Ranks = $this->computeDenseRanks($rawScores, 'bm25');
        $trigramRanks = $this->computeDenseRanks($rawScores, 'trigram');
        $diceRanks = $this->computeDenseRanks($rawScores, 'dice');

        $allEvaluated = [];
        $filteredResults = [];
        $maxSimilarity = 0.0;
        $maxRrfPossible = 1.0 / ($this->rrfK + 1);

        foreach ($prepared as $idx => $p) {
            $cand = $p['cand'];
            $sc = $rawScores[$idx];
            $docQNorm = $p['docQTok']['normalized'];

            $constraints = $this->evaluateProtectedConstraints(
                $qTok['protected'],
                $p['docQTok']['protected'],
                $p['ansTok']['protected'] ?? null,
                $isPublicSearch
            );

            $isNormalizedExact = ($qNorm !== '' && $qNorm === $docQNorm && !$constraints['has_conflict']);

            // RRF menentukan urutan ranking lintas sinyal; skor [0..100] dikalibrasi terhadap idealBm25 tanpa floor aditif gratis
            if ($strategy === self::STRATEGY_BM25) {
                $rrfNorm = (1.0 / ($this->rrfK + $bm25Ranks[$idx])) / $maxRrfPossible;
                $calibrated01 = $sc['bm25_norm'];
            } elseif ($strategy === self::STRATEGY_BM25_TRIGRAM) {
                $rrfRaw = (0.80 / ($this->rrfK + $bm25Ranks[$idx])) + (0.20 / ($this->rrfK + $trigramRanks[$idx]));
                $rrfNorm = $rrfRaw / $maxRrfPossible;
                $calibrated01 = $sc['bm25'] > 0.0
                    ? ((0.90 * $sc['bm25_typo_norm']) + (0.10 * $sc['trigram']))
                    : (0.20 * $sc['trigram']);
            } else {
                // STRATEGY_BM25_DICE_TRIGRAM (Konfigurasi C)
                $rrfRaw = (0.62 / ($this->rrfK + $bm25Ranks[$idx]))
                    + (0.22 / ($this->rrfK + $diceRanks[$idx]))
                    + (0.16 / ($this->rrfK + $trigramRanks[$idx]));
                $rrfNorm = $rrfRaw / $maxRrfPossible;
                $calibrated01 = $sc['bm25'] > 0.0
                    ? ((0.76 * $sc['bm25_typo_norm']) + (0.16 * $sc['dice']) + (0.08 * $sc['trigram']))
                    : (0.20 * $sc['trigram']);
            }

            // Bonus kecil kesesuaian kata tanya spesifik ("berapa", "kapan", "dimana", "siapa") bila ada overlap leksikal
            if ($sc['bm25'] > 0.0 && !empty($qTok['question_words']) && !empty($p['docQTok']['question_words'])) {
                $qInter = array_intersect($qTok['question_words'], $p['docQTok']['question_words']);
                if (!empty($qInter)) {
                    $calibrated01 = min(0.99, $calibrated01 + 0.03);
                }
            }

            // Bonus substring frasa langsung pada pertanyaan (misal ketikan pendek "galeri investasi" / "toefl")
            $directSubstringInQ = (mb_strlen($qNorm, 'UTF-8') >= 3 && str_contains($docQNorm, $qNorm));
            if ($directSubstringInQ && !$constraints['has_conflict']) {
                $calibrated01 = max($calibrated01, 0.58);
            }

            // Terapkan penalti ketat bila terjadi konflik negasi, aspek, angka, atau kode jenjang
            if ($constraints['has_conflict']) {
                $calibrated01 *= $constraints['multiplier'];
            }

            if ($isNormalizedExact) {
                $finalScore = 100.0;
            } else {
                $finalScore = round(min(99.0, max(0.0, $calibrated01 * 100.0)), 1);
            }

            if ($finalScore > $maxSimilarity) {
                $maxSimilarity = $finalScore;
            }

            // Jangkar relevansi minimum agar query di luar domain (no_answer) ditolak:
            $hasLexicalOrTypoAnchor = ($sc['bm25'] > 0.0) || $directSubstringInQ;

            $level = 'moderate';
            if ($isNormalizedExact) {
                $level = 'exact';
            } elseif ($finalScore >= 70.0 && !$constraints['has_conflict']) {
                $level = 'high';
            }

            $item = [
                'id'                 => $cand['id'],
                'pertanyaan'         => $cand['pertanyaan'],
                'jawaban'            => $cand['jawaban'] ?? '',
                'unit_id'            => $cand['unit_id'] ?? 1,
                'unit_nama'          => $cand['unit_nama'] ?? 'Direktorat Akademik',
                'status'             => $cand['status'],
                'status_label'       => $cand['status_label'],
                'version_id'         => $cand['version_id'],
                'tags'               => $cand['tags'] ?? [],
                'source_label'       => $cand['source_label'] ?? null,
                'source_url'         => $cand['source_url'] ?? null,
                'similarity'         => $finalScore,
                'is_exact'           => $isNormalizedExact,
                'level'              => $level,
                'rrf_score'          => round($rrfNorm, 4),
                'bm25_score'         => round($sc['bm25'], 4),
                'constraint_penalty' => $constraints['reasons'],
            ];

            $allEvaluated[] = $item;

            if ($hasLexicalOrTypoAnchor && $finalScore >= $threshold) {
                $filteredResults[] = $item;
            }
        }

        usort($filteredResults, function (array $a, array $b) {
            if ($a['is_exact'] !== $b['is_exact']) {
                return $b['is_exact'] <=> $a['is_exact'];
            }
            // Utamakan RRF ketika skor terkalibrasi berdekatan, namun hormati penalti konflik besar
            if (abs($b['similarity'] - $a['similarity']) >= 6.0) {
                return $b['similarity'] <=> $a['similarity'];
            }
            if (abs($b['rrf_score'] - $a['rrf_score']) > 0.0001) {
                return $b['rrf_score'] <=> $a['rrf_score'];
            }
            return $b['bm25_score'] <=> $a['bm25_score'];
        });

        return [
            'results'        => array_slice($filteredResults, 0, $limit),
            'all_evaluated'  => $allEvaluated,
            'max_similarity' => $maxSimilarity,
        ];
    }

    /**
     * Bangun atau ambil indeks ter-tokenisasi dari cache memori agar pencarian berulang berjalan sub-milidetik.
     *
     * @return array{
     *     prepared: array<int, array<string, mixed>>,
     *     docFreq: array<string, int>,
     *     docCount: int,
     *     avgDocLen: float,
     *     vocabTrigrams: array<string, array<string, bool>>
     * }
     */
    private function buildOrGetPreparedCorpus(Collection $candidates, bool $isPublicSearch): array
    {
        $hashParts = [$isPublicSearch ? 'pub' : 'int', (string) $candidates->count()];
        foreach ($candidates as $c) {
            $hashParts[] = ($c['id'] ?? '') . ':' . ($c['version_id'] ?? '') . ':' . strlen((string) ($c['pertanyaan'] ?? ''));
        }
        $cacheKey = md5(implode('|', $hashParts));

        if (isset(self::$preparedCorpusCache[$cacheKey])) {
            return self::$preparedCorpusCache[$cacheKey];
        }

        $prepared = [];
        $docFreq = [];
        $vocabTrigrams = [];
        $totalDocLen = 0.0;

        foreach ($candidates as $cand) {
            $qText = (string) ($cand['pertanyaan'] ?? '');
            $altList = is_array($cand['alternatives'] ?? null) ? $cand['alternatives'] : [];
            $tagList = is_array($cand['tags'] ?? null) ? $cand['tags'] : [];
            $ansText = $isPublicSearch ? (string) ($cand['jawaban'] ?? '') : '';

            $docQTok = $this->tokenizeText($qText, false);
            $altTok = !empty($altList) ? $this->tokenizeText(implode(' ', $altList), false) : null;

            $specificTags = array_values(array_filter(
                $tagList,
                fn ($t) => !in_array(mb_strtolower(trim((string) $t), 'UTF-8'), self::GENERIC_TAGS, true)
            ));
            $tagTok = !empty($specificTags) ? $this->tokenizeText(implode(' ', $specificTags), false) : null;
            $ansTok = ($isPublicSearch && $ansText !== '') ? $this->tokenizeText($ansText, false) : null;

            $weightedTf = [];
            $weightedLen = 0.0;

            foreach ($docQTok['bm25_tokens'] as $tk) {
                $weightedTf[$tk] = ($weightedTf[$tk] ?? 0.0) + 2.5;
                $weightedLen += 2.5;
            }
            if ($altTok !== null) {
                foreach ($altTok['bm25_tokens'] as $tk) {
                    $weightedTf[$tk] = ($weightedTf[$tk] ?? 0.0) + 2.0;
                    $weightedLen += 2.0;
                }
            }
            if ($tagTok !== null) {
                foreach ($tagTok['bm25_tokens'] as $tk) {
                    $weightedTf[$tk] = ($weightedTf[$tk] ?? 0.0) + 1.2;
                    $weightedLen += 1.2;
                }
            }
            if ($ansTok !== null) {
                foreach ($ansTok['bm25_tokens'] as $tk) {
                    $weightedTf[$tk] = ($weightedTf[$tk] ?? 0.0) + 0.35;
                    $weightedLen += 0.35;
                }
            }

            $totalDocLen += max(1.0, $weightedLen);
            foreach (array_keys($weightedTf) as $uniqueTerm) {
                $docFreq[$uniqueTerm] = ($docFreq[$uniqueTerm] ?? 0) + 1;
                if (
                    !isset($vocabTrigrams[$uniqueTerm]) &&
                    mb_strlen($uniqueTerm, 'UTF-8') >= 4 &&
                    !preg_match('/\d/', $uniqueTerm) &&
                    !str_contains($uniqueTerm, '_')
                ) {
                    $vocabTrigrams[$uniqueTerm] = $this->buildTrigrams($uniqueTerm);
                }
            }

            $dicePool = $docQTok['unique_tokens'];
            if ($altTok !== null) {
                $dicePool = array_values(array_unique(array_merge($dicePool, $altTok['unique_tokens'])));
            }
            if ($tagTok !== null) {
                $dicePool = array_values(array_unique(array_merge($dicePool, $tagTok['unique_tokens'])));
            }

            $trigramTargetNorm = $docQTok['normalized'];
            if ($altTok !== null && $altTok['normalized'] !== '') {
                $trigramTargetNorm .= ' ' . $altTok['normalized'];
            }

            $prepared[] = [
                'cand'        => $cand,
                'docQTok'     => $docQTok,
                'ansTok'      => $ansTok,
                'weightedTf'  => $weightedTf,
                'weightedLen' => max(1.0, $weightedLen),
                'diceTokens'  => $dicePool,
                'trigrams'    => $this->buildTrigrams($trigramTargetNorm),
            ];
        }

        $docCount = count($prepared);
        $avgDocLen = max(1.0, $totalDocLen / max(1, $docCount));

        if (count(self::$preparedCorpusCache) >= 6) {
            array_shift(self::$preparedCorpusCache);
        }

        return self::$preparedCorpusCache[$cacheKey] = [
            'prepared'      => $prepared,
            'docFreq'       => $docFreq,
            'docCount'      => $docCount,
            'avgDocLen'     => $avgDocLen,
            'vocabTrigrams' => $vocabTrigrams,
        ];
    }

    /**
     * Formula Okapi BM25 standar (Robertson / Lucene non-negative IDF):
     *   IDF(t) = ln(1 + (N - df(t) + 0.5) / (df(t) + 0.5))
     *   BM25(Q, D) = sum_{t in Q} qWeight(t) * IDF(t) * ((tf(t, D) * (k1 + 1)) / (tf(t, D) + k1 * (1 - b + b * (|D| / avgdl))))
     *
     * @param  array<string, float>  $queryTermWeights
     * @param  array<string, float>  $docWeightedTf
     * @param  array<string, int>    $docFreq
     */
    private function computeOkapiBm25(
        array $queryTermWeights,
        array $docWeightedTf,
        float $docLen,
        float $avgDocLen,
        array $docFreq,
        int $docCount
    ): float {
        if (empty($queryTermWeights) || empty($docWeightedTf)) {
            return 0.0;
        }

        $score = 0.0;
        $normFactor = $this->bm25K1 * (1.0 - $this->bm25B + ($this->bm25B * ($docLen / max(1.0, $avgDocLen))));

        foreach ($queryTermWeights as $term => $qWeight) {
            $tf = $docWeightedTf[$term] ?? 0.0;
            if ($tf <= 0.0) {
                continue;
            }

            $df = $docFreq[$term] ?? 1;
            $idf = log(1.0 + (($docCount - $df + 0.5) / ($df + 0.5)));
            $tfComponent = ($tf * ($this->bm25K1 + 1.0)) / ($tf + $normFactor);
            $score += $qWeight * $idf * $tfComponent;
        }

        return $score;
    }

    // =========================================================================
    // KELOMPOK 6: PROTEKSI MAKNA (NEGASI, MODALITAS, ASPEK, ANGKA, & KODE)
    // Memastikan kalimat positif tidak disamakan dengan negatif, serta angka
    // SKS/IPK/semester/jenjang yang berbeda tidak tertukar.
    // =========================================================================

    /**
     * Verifikasi konsistensi negasi, modalitas, aspek waktu, angka, dan kode fakultas/program.
     * Mencegah "boleh" disamakan dengan "tidak boleh", "wajib" dengan "tidak wajib", "sudah" dengan "belum",
     * maupun pencampuran angka SKS/semester/fakultas yang berbeda.
     *
     * @param  array<string, string[]>       $qProt
     * @param  array<string, string[]>       $docQProt
     * @param  array<string, string[]>|null  $docAnsProt
     * @return array{has_conflict: bool, multiplier: float, reasons: string[]}
     */
    private function evaluateProtectedConstraints(
        array $qProt,
        array $docQProt,
        ?array $docAnsProt,
        bool $isPublicSearch
    ): array {
        $reasons = [];
        $multiplier = 1.0;

        $qNegBigrams = $qProt['negation_bigrams'] ?? [];
        $dNegBigrams = $docQProt['negation_bigrams'] ?? [];
        $qNegs = $qProt['negations'] ?? [];
        $dNegs = $docQProt['negations'] ?? [];

        // 1. Konflik polaritas negasi pada modalitas ("boleh" vs "tidak_boleh"/"dilarang", "wajib" vs "tidak_wajib")
        $qForbids = !empty(array_intersect($qNegBigrams, ['tidak_boleh', 'tidak_bisa', 'tidak_dapat', 'tidak_izin'])) || in_array('dilarang', $qNegs, true);
        $dForbids = !empty(array_intersect($dNegBigrams, ['tidak_boleh', 'tidak_bisa', 'tidak_dapat', 'tidak_izin'])) || in_array('dilarang', $dNegs, true);
        $qAllows  = !$qForbids && !empty(array_intersect($qProt['modalities'] ?? [], ['boleh', 'izin', 'bisa', 'dapat']));
        $dAllows  = !$dForbids && !empty(array_intersect($docQProt['modalities'] ?? [], ['boleh', 'izin', 'bisa', 'dapat']));

        if (($qForbids && $dAllows) || ($qAllows && $dForbids)) {
            $reasons[] = 'polarity_modality_conflict';
            $multiplier *= 0.45;
        }

        $qNotMandatory = !empty(array_intersect($qNegBigrams, ['tidak_wajib', 'tidak_harus', 'tidak_perlu']));
        $dNotMandatory = !empty(array_intersect($dNegBigrams, ['tidak_wajib', 'tidak_harus', 'tidak_perlu']));
        $qMandatory    = !$qNotMandatory && !empty(array_intersect($qProt['modalities'] ?? [], ['wajib', 'harus', 'perlu']));
        $dMandatory    = !$dNotMandatory && !empty(array_intersect($docQProt['modalities'] ?? [], ['wajib', 'harus', 'perlu']));

        if (($qNotMandatory && $dMandatory) || ($qMandatory && $dNotMandatory)) {
            $reasons[] = 'mandatory_polarity_conflict';
            $multiplier *= 0.45;
        }

        // 2. Konflik negasi umum untuk deteksi duplikat internal (kalimat positif vs kalimat ber-negasi "tidak"/"bukan"/"tanpa")
        if (!$isPublicSearch && empty($reasons)) {
            $qHasHardNeg = !empty(array_intersect($qNegs, ['tidak', 'bukan', 'tanpa', 'jangan', 'dilarang', 'belum']));
            $dHasHardNeg = !empty(array_intersect($dNegs, ['tidak', 'bukan', 'tanpa', 'jangan', 'dilarang', 'belum']));
            if ($qHasHardNeg !== $dHasHardNeg) {
                $reasons[] = 'negation_mismatch';
                $multiplier *= 0.52;
            }
        }

        // 3. Konflik aspek waktu ("sudah"/"telah" vs "belum")
        $qDone = !empty(array_intersect($qProt['aspects'] ?? [], ['sudah', 'telah'])) && !in_array('belum', $qProt['aspects'] ?? [], true);
        $dDone = !empty(array_intersect($docQProt['aspects'] ?? [], ['sudah', 'telah'])) && !in_array('belum', $docQProt['aspects'] ?? [], true);
        $qNotYet = in_array('belum', $qProt['aspects'] ?? [], true);
        $dNotYet = in_array('belum', $docQProt['aspects'] ?? [], true);

        if (($qDone && $dNotYet) || ($qNotYet && $dDone)) {
            $reasons[] = 'aspect_conflict';
            $multiplier *= 0.48;
        }

        // 4. Konflik batas ("minimal" vs "maksimal") bila tidak keduanya disebut
        $qMin = in_array('minimal', $qProt['bounds'] ?? [], true) && !in_array('maksimal', $qProt['bounds'] ?? [], true);
        $qMax = in_array('maksimal', $qProt['bounds'] ?? [], true) && !in_array('minimal', $qProt['bounds'] ?? [], true);
        $dMin = in_array('minimal', $docQProt['bounds'] ?? [], true) && !in_array('maksimal', $docQProt['bounds'] ?? [], true);
        $dMax = in_array('maksimal', $docQProt['bounds'] ?? [], true) && !in_array('minimal', $docQProt['bounds'] ?? [], true);
        if (($qMin && $dMax) || ($qMax && $dMin)) {
            $reasons[] = 'bound_direction_conflict';
            $multiplier *= 0.65;
        }

        // 5. Konflik angka eksplisit (SKS, semester, skor, jam, tahun) bila keduanya menyebut angka berbeda
        $qNums = $qProt['numbers'] ?? [];
        $dNums = $docQProt['numbers'] ?? [];
        $ansNums = $docAnsProt['numbers'] ?? [];
        if (!empty($qNums) && !empty($dNums) && empty(array_intersect($qNums, $dNums))) {
            $matchedInAns = $isPublicSearch && !empty(array_intersect($qNums, $ansNums));
            if (!$matchedInAns) {
                $reasons[] = 'number_mismatch';
                $multiplier *= 0.55;
            }
        }

        // 6. Konflik kode jenjang (S1 vs S2 vs D3) atau fakultas spesifik (FT vs FH vs FK vs FBE vs FIK, dst.)
        $degreeSet = ['s1', 's2', 's3', 'd3', 'd4'];
        $qDeg = array_intersect($qProt['codes'] ?? [], $degreeSet);
        $dDeg = array_intersect($docQProt['codes'] ?? [], $degreeSet);
        if (!empty($qDeg) && !empty($dDeg) && empty(array_intersect($qDeg, $dDeg))) {
            $reasons[] = 'degree_code_mismatch';
            $multiplier *= 0.55;
        }

        return [
            'has_conflict' => !empty($reasons),
            'multiplier'   => $multiplier,
            'reasons'      => $reasons,
        ];
    }

    /**
     * Hitung Sorensen-Dice pada himpunan token unik.
     *
     * @param  string[]  $tokensA
     * @param  string[]  $tokensB
     */
    public function computeTokenDice(array $tokensA, array $tokensB): float
    {
        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $uniqueA = array_values(array_unique($tokensA));
        $uniqueB = array_values(array_unique($tokensB));
        $intersection = count(array_intersect($uniqueA, $uniqueB));

        return (2.0 * $intersection) / (count($uniqueA) + count($uniqueB));
    }

    /**
     * Hitung Sorensen-Dice pada character trigram (UTF-8 safe).
     */
    public function computeTrigramDice(string $strA, string $strB): float
    {
        return $this->computeTrigramDiceFromSets(
            $this->buildTrigrams($strA),
            $this->buildTrigrams($strB)
        );
    }

    /**
     * @param  array<string, bool>  $triA
     * @param  array<string, bool>  $triB
     */
    private function computeTrigramDiceFromSets(array $triA, array $triB): float
    {
        if (empty($triA) || empty($triB)) {
            return 0.0;
        }

        $inter = 0;
        foreach ($triA as $k => $_) {
            if (isset($triB[$k])) {
                $inter++;
            }
        }

        return (2.0 * $inter) / (count($triA) + count($triB));
    }

    /**
     * Trigram per-kata (>= 4 karakter, bukan angka) untuk menangkap typo 1-2 karakter pada kata kunci penting
     * tanpa terlarut oleh panjang kalimat FAQ.
     *
     * @param  array<int, array<string, bool>>  $qWordTrigrams
     * @param  array<int, array<string, bool>>  $docWordTrigrams
     */
    private function computeWordTypoSimilarity(array $qWordTrigrams, array $docWordTrigrams): float
    {
        if (empty($qWordTrigrams) || empty($docWordTrigrams)) {
            return 0.0;
        }

        $sumBest = 0.0;
        foreach ($qWordTrigrams as $qTri) {
            $best = 0.0;
            foreach ($docWordTrigrams as $dTri) {
                $sim = $this->computeTrigramDiceFromSets($qTri, $dTri);
                if ($sim > $best) {
                    $best = $sim;
                }
            }
            $sumBest += $best;
        }

        return $sumBest / count($qWordTrigrams);
    }

    /**
     * @return array<string, bool>
     */
    private function buildTrigrams(string $str): array
    {
        if ($str === '') {
            return [];
        }

        $padded = '  ' . $str . '  ';
        $len = mb_strlen($padded, 'UTF-8');
        $trigrams = [];

        for ($i = 0; $i <= $len - 3; $i++) {
            $trigrams[mb_substr($padded, $i, 3, 'UTF-8')] = true;
        }

        return $trigrams;
    }

    /**
     * @return array<int, array<string, bool>>
     */
    private function buildWordTrigrams(string $normalizedText): array
    {
        if ($normalizedText === '') {
            return [];
        }

        $out = [];
        foreach (explode(' ', $normalizedText) as $w) {
            if (
                mb_strlen($w, 'UTF-8') >= 4 &&
                !preg_match('/\d/', $w) &&
                !in_array($w, self::LOW_WEIGHT_STOPWORDS, true) &&
                !in_array($w, self::QUESTION_WORDS, true)
            ) {
                $out[] = $this->buildTrigrams($w);
            }
        }

        return $out;
    }

    /**
     * Hitung peringkat 1..N berdasarkan metrik tertentu (skor 0 diberi rank bawah N+1 agar tidak menyumbang RRF palsu).
     *
     * @param  array<int, array<string, float>>  $rawScores
     * @return array<int, int>
     */
    private function computeDenseRanks(array $rawScores, string $key): array
    {
        $n = count($rawScores);
        $indexed = [];
        foreach ($rawScores as $idx => $vals) {
            $indexed[$idx] = $vals[$key] ?? 0.0;
        }

        arsort($indexed, SORT_NUMERIC);
        $ranks = [];
        $pos = 1;
        foreach ($indexed as $idx => $val) {
            $ranks[$idx] = $val > 0.0001 ? $pos : ($n + 20);
            $pos++;
        }

        return $ranks;
    }

    /**
     * Penggabungan RRF opsional antara kandidat leksikal (BM25) dan kandidat semantic retriever di masa depan.
     *
     * @param  array<int, array<string, mixed>>  $lexicalResults
     * @param  array<int, array<string, mixed>>  $semanticResults
     * @return array<int, array<string, mixed>>
     */
    private function fuseWithSemanticResults(array $lexicalResults, array $semanticResults, int $limit): array
    {
        if (empty($semanticResults)) {
            return array_slice($lexicalResults, 0, $limit);
        }

        $byId = [];
        foreach ($lexicalResults as $rIdx => $item) {
            $id = (string) $item['id'];
            $byId[$id] = [
                'item' => $item,
                'rrf'  => 0.65 / ($this->rrfK + $rIdx + 1),
            ];
        }

        foreach ($semanticResults as $sIdx => $sItem) {
            $id = (string) ($sItem['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $semRrf = 0.35 / ($this->rrfK + $sIdx + 1);
            if (isset($byId[$id])) {
                $byId[$id]['rrf'] += $semRrf;
            } elseif (empty($sItem['constraint_penalty'])) {
                $byId[$id] = [
                    'item' => $sItem,
                    'rrf'  => $semRrf,
                ];
            }
        }

        $merged = array_values($byId);
        usort($merged, fn ($a, $b) => $b['rrf'] <=> $a['rrf']);

        return array_slice(array_map(fn ($m) => $m['item'], $merged), 0, $limit);
    }

    // =========================================================================
    // KELOMPOK 7: BASELINE METODE LAMA (KHUSUS PERBANDINGAN BENCHMARK)
    // Tidak dipakai di trafik produksi; hanya untuk mengukur peningkatan akurasi.
    // =========================================================================

    /**
     * Reproduksi metode lama (Baseline pra-refactor) semata-mata untuk perbandingan benchmark objektif (Section H).
     * Tidak dipakai untuk trafik produksi.
     *
     * @return array{results: array<int, array<string, mixed>>, all_evaluated: array<int, array<string, mixed>>, max_similarity: float}
     */
    private function rankLegacyBaseline(string $rawQuery, Collection $candidates, float $threshold, int $limit): array
    {
        $legacyStopwords = [
            'yang', 'di', 'ke', 'dari', 'pada', 'dalam', 'untuk', 'dengan', 'dan', 'atau',
            'ini', 'itu', 'adalah', 'ialah', 'merupakan', 'jika', 'kalau', 'bila', 'apabila',
            'saya', 'aku', 'kami', 'kita', 'mahasiswa', 'kampus', 'universitas', 'apakah',
            'apa', 'saja', 'bisa', 'dapat', 'boleh', 'harus', 'wajib', 'ingin', 'mau',
            'akan', 'sudah', 'telah', 'belum', 'ada', 'tidak', 'nggak', 'gak', 'ya', 'dong',
            'sih', 'nih', 'deh', 'kok', 'nya', 'secara', 'bagi', 'tentang', 'seputar',
        ];
        $legacySynonyms = [
            'gimana' => 'bagaimana', 'gmn' => 'bagaimana', 'caranya' => 'cara',
            'bikin' => 'membuat pengajuan', 'buat' => 'membuat pengajuan',
            'ngurus' => 'mengurus pengajuan prosedur', 'urus' => 'mengurus pengajuan prosedur',
            'daftar' => 'pendaftaran registrasi', 'isi' => 'mengisi pengisian',
            'krs' => 'krs kartu rencana studi', 'ukt' => 'ukt uang kuliah tunggal biaya',
            'spp' => 'ukt uang kuliah tunggal biaya', 'nyicil' => 'cicilan keringanan',
            'ta' => 'tugas akhir skripsi', 'skripsi' => 'tugas akhir skripsi',
            'sempro' => 'seminar proposal skripsi tugas akhir', 'lulus' => 'kelulusan yudisium wisuda',
            'prodi' => 'program studi jurusan', 'jurusan' => 'program studi jurusan',
            'matkul' => 'mata kuliah sks', 'khs' => 'kartu hasil studi transkrip nilai',
            'syarat' => 'syarat persyaratan ketentuan', 'prosedur' => 'prosedur cara alur mekanisme',
        ];

        $normFn = fn (string $t): string => preg_replace('/\s+/u', ' ', trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower(strip_tags($t), 'UTF-8')) ?? '')) ?? '';
        $tokFn = function (string $t) use ($normFn, $legacySynonyms, $legacyStopwords): array {
            $n = $normFn($t);
            if ($n === '') {
                return [];
            }
            $exp = [];
            foreach (explode(' ', $n) as $w) {
                if (mb_strlen($w, 'UTF-8') < 2) {
                    continue;
                }
                if (isset($legacySynonyms[$w])) {
                    foreach (explode(' ', $legacySynonyms[$w]) as $s) {
                        $exp[] = $s;
                    }
                } else {
                    $exp[] = $w;
                }
            }
            $f = array_values(array_filter($exp, fn ($w) => !in_array($w, $legacyStopwords, true) && mb_strlen($w, 'UTF-8') >= 2));
            return array_values(array_unique($f ?: explode(' ', $n)));
        };

        $normInput = $normFn($rawQuery);
        $inTokens = $tokFn($rawQuery);
        $docCount = max(1, $candidates->count() + 1);
        $docFreq = [];
        $docs = [];

        foreach ($candidates as $cand) {
            $cn = $normFn((string) $cand['pertanyaan']);
            $ct = $tokFn((string) $cand['pertanyaan']);
            foreach ($ct as $tk) {
                $docFreq[$tk] = ($docFreq[$tk] ?? 0) + 1;
            }
            $docs[] = ['cand' => $cand, 'norm' => $cn, 'tokens' => $ct];
        }
        foreach ($inTokens as $tk) {
            $docFreq[$tk] = ($docFreq[$tk] ?? 0) + 1;
        }

        $all = [];
        $filtered = [];
        $maxSim = 0.0;

        foreach ($docs as $d) {
            if ($d['norm'] === $normInput && $normInput !== '') {
                $score = 100.0;
            } else {
                $vocab = array_unique(array_merge($inTokens, $d['tokens']));
                $sA = array_count_values($inTokens);
                $sB = array_count_values($d['tokens']);
                $dot = 0.0; $nA = 0.0; $nB = 0.0;
                foreach ($vocab as $term) {
                    $df = $docFreq[$term] ?? 1;
                    $idf = log(1 + (($docCount - $df + 0.5) / ($df + 0.5))) + 1.0;
                    $wA = isset($sA[$term]) ? (1 + log($sA[$term])) * $idf : 0.0;
                    $wB = isset($sB[$term]) ? (1 + log($sB[$term])) * $idf : 0.0;
                    $dot += $wA * $wB; $nA += $wA * $wA; $nB += $wB * $wB;
                }
                $cos = ($nA > 0 && $nB > 0) ? ($dot / (sqrt($nA) * sqrt($nB))) : 0.0;
                $dice = $this->computeTokenDice($inTokens, $d['tokens']);
                $tri = $this->computeTrigramDice($normInput, $d['norm']);
                $comb = ($cos * 0.45) + ($dice * 0.35) + ($tri * 0.20);
                if (!empty($inTokens) && $inTokens === $d['tokens']) {
                    $comb = max($comb, 0.96);
                }
                $score = round(min(99.0, $comb * 100.0), 1);
            }

            if ($score > $maxSim) {
                $maxSim = $score;
            }
            $item = [
                'id'           => $d['cand']['id'],
                'pertanyaan'   => $d['cand']['pertanyaan'],
                'jawaban'      => $d['cand']['jawaban'] ?? '',
                'unit_id'      => $d['cand']['unit_id'] ?? 1,
                'unit_nama'    => $d['cand']['unit_nama'] ?? 'Direktorat Akademik',
                'status'       => $d['cand']['status'],
                'status_label' => $d['cand']['status_label'],
                'version_id'   => $d['cand']['version_id'],
                'similarity'   => $score,
                'is_exact'     => ($score >= 99.5),
                'level'        => $score >= 99.5 ? 'exact' : ($score >= 70.0 ? 'high' : 'moderate'),
            ];
            $all[] = $item;
            if ($score >= $threshold) {
                $filtered[] = $item;
            }
        }

        usort($filtered, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);
        return [
            'results'        => array_slice($filtered, 0, $limit),
            'all_evaluated'  => $all,
            'max_similarity' => $maxSim,
        ];
    }
}

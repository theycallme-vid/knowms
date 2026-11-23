<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Collection;

class FaqSimilarityService
{
    /**
     * Kamus normalisasi kata sehari-hari / singkatan mahasiswa ke istilah baku akademik
     */
    private const SYNONYM_MAP = [
        'gimana'       => 'bagaimana',
        'gmn'          => 'bagaimana',
        'bgmn'         => 'bagaimana',
        'bagimana'     => 'bagaimana',
        'caranya'      => 'cara',
        'bikin'        => 'membuat pengajuan',
        'buat'         => 'membuat pengajuan',
        'ngurus'       => 'mengurus pengajuan prosedur',
        'urus'         => 'mengurus pengajuan prosedur',
        'ngajuin'      => 'mengajukan pengajuan prosedur',
        'ajuin'        => 'mengajukan pengajuan prosedur',
        'daftar'       => 'pendaftaran registrasi',
        'nyusun'       => 'menyusun pengisian',
        'isi'          => 'mengisi pengisian',
        'ngisi'        => 'mengisi pengisian',
        'ambil'        => 'mengambil pengisian',
        'ngambil'      => 'mengambil pengisian',
        'krsan'        => 'krs rencana studi',
        'krs'          => 'krs kartu rencana studi',
        'ukt'          => 'ukt uang kuliah tunggal biaya',
        'spp'          => 'ukt uang kuliah tunggal biaya',
        'bayar'        => 'pembayaran biaya ukt',
        'nyicil'       => 'cicilan keringanan',
        'cicil'        => 'cicilan keringanan',
        'ta'           => 'tugas akhir skripsi',
        'skripsi'      => 'tugas akhir skripsi',
        'sempro'       => 'seminar proposal skripsi tugas akhir',
        'lulus'        => 'kelulusan yudisium wisuda',
        'wisuda'       => 'yudisium kelulusan',
        'cuti'         => 'cuti akademik berhenti sementara',
        'pindah'       => 'pindah transfer mutasi',
        'prodi'        => 'program studi jurusan',
        'jurusan'      => 'program studi jurusan',
        'matkul'       => 'mata kuliah sks',
        'mk'           => 'mata kuliah sks',
        'transkrip'    => 'transkrip nilai khs',
        'khs'          => 'kartu hasil studi transkrip nilai',
        'legalisir'    => 'legalisasi pengesahan',
        'syaratnya'    => 'syarat persyaratan ketentuan',
        'persyaratan'  => 'syarat persyaratan ketentuan',
        'syarat'       => 'syarat persyaratan ketentuan',
        'prosedur'     => 'prosedur cara alur mekanisme',
        'alur'         => 'prosedur cara alur mekanisme',
        'mekanisme'    => 'prosedur cara alur mekanisme',
    ];

    /**
     * Daftar kata hubung umum (stopwords) yang tidak membawa bobot makna utama
     */
    private const STOPWORDS = [
        'yang', 'di', 'ke', 'dari', 'pada', 'dalam', 'untuk', 'dengan', 'dan', 'atau',
        'ini', 'itu', 'adalah', 'ialah', 'merupakan', 'jika', 'kalau', 'bila', 'apabila',
        'saya', 'aku', 'kami', 'kita', 'mahasiswa', 'kampus', 'universitas', 'apakah',
        'apa', 'saja', 'bisa', 'dapat', 'boleh', 'harus', 'wajib', 'ingin', 'mau',
        'akan', 'sudah', 'telah', 'belum', 'ada', 'tidak', 'nggak', 'gak', 'ya', 'dong',
        'sih', 'nih', 'deh', 'kok', 'nya', 'secara', 'bagi', 'tentang', 'seputar',
    ];

    /**
     * 1. Normalisasi teks mentah (lowercase, hapus HTML, hapus tanda baca, rapikan spasi)
     */
    public function normalizeRaw(string $text): string
    {
        $clean = strip_tags($text);
        $clean = mb_strtolower($clean, 'UTF-8');
        // Ganti tanda hubung/garis miring menjadi spasi, hapus tanda baca lainnya
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s+/u', ' ', trim($clean)) ?? '';

        return $clean;
    }

    /**
     * Tokenisasi + ekspansi sinonim + stemming ringan Bahasa Indonesia
     *
     * @return string[]
     */
    public function extractNormalizedTokens(string $text): array
    {
        $normalized = $this->normalizeRaw($text);
        if ($normalized === '') {
            return [];
        }

        $rawWords = explode(' ', $normalized);
        $expandedWords = [];

        foreach ($rawWords as $word) {
            $word = trim($word);
            if ($word === '' || mb_strlen($word) < 2) {
                continue;
            }

            if (isset(self::SYNONYM_MAP[$word])) {
                foreach (explode(' ', self::SYNONYM_MAP[$word]) as $syn) {
                    $expandedWords[] = $syn;
                }
            } else {
                $expandedWords[] = $word;
                $stemmed = $this->stemIndonesian($word);
                if ($stemmed !== $word && mb_strlen($stemmed) >= 3) {
                    $expandedWords[] = $stemmed;
                }
            }
        }

        $filtered = [];
        foreach ($expandedWords as $w) {
            if (!in_array($w, self::STOPWORDS, true) && mb_strlen($w) >= 2) {
                $filtered[] = $w;
            }
        }

        // Jika seluruh kata terfilter sebagai stopword, gunakan kata asli dari normalizeRaw
        if (empty($filtered)) {
            $filtered = array_values(array_filter($rawWords, fn ($w) => mb_strlen($w) >= 2));
        }

        return array_values(array_unique($filtered));
    }

    /**
     * Stemming ringan Bahasa Indonesia (tanpa library eksternal)
     */
    private function stemIndonesian(string $word): string
    {
        if (mb_strlen($word) <= 4) {
            return $word;
        }

        // Hapus partikel & kepunyaan di akhir
        $word = preg_replace('/(lah|kah|tah|pun|nya|ku|mu)$/u', '', $word) ?? $word;

        // Hapus akhiran -kan, -an, -i jika kata masih cukup panjang
        if (mb_strlen($word) > 5) {
            $word = preg_replace('/(kan|an)$/u', '', $word) ?? $word;
        }

        // Hapus awalan umum (di-, ke-, se-, ber-, ter-, per-, meng-, peng-)
        if (mb_strlen($word) > 5) {
            $word = preg_replace('/^(meng|meny|men|mem|me|peng|peny|pen|pem|pe|ber|ter|per|di|ke|se)/u', '', $word) ?? $word;
        }

        return $word;
    }

    /**
     * Mengubah status database menjadi label yang mudah dipahami Data Entry / Validator
     */
    public function formatStatusLabel(string $rawStatus): string
    {
        $st = strtolower(trim($rawStatus));
        if (in_array($st, ['active', 'terbit', 'publish'], true)) {
            return 'Terbit';
        }
        if (in_array($st, ['approved', 'accepted', 'disetujui', 'diterima'], true)) {
            return 'Diterima';
        }
        if (in_array($st, ['submitted', 'pending', 'menunggu validasi'], true)) {
            return 'Menunggu Validasi';
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
     * Mengambil kandidat FAQ dari database (Termasuk Draf, Terbit, Diterima, Menunggu Validasi, Ditolak)
     * beserta jawaban dari setiap versinya untuk mendeteksi duplikat pertanyaan + jawaban Draf.
     */
    public function getCandidateFaqs(?string $excludeFaqId = null): Collection
    {
        $query = Faq::with(['versions' => function ($q) {
            $q->orderByRaw("CAST(SUBSTRING_INDEX(id, '-V', -1) AS UNSIGNED) DESC")
              ->orderBy('updated_at', 'desc');
        }]);

        if ($excludeFaqId) {
            $query->where('id', '!=', $excludeFaqId);
        }

        return $query->get()->map(function (Faq $faq) {
            // Gunakan versi terbaru (nomor versi tertinggi) sebagai status aktif FAQ
            $relevantVersion = $faq->versions->first();

            $status = $relevantVersion ? (string) $relevantVersion->status : 'Draf';

            return [
                'id'               => $faq->id,
                'pertanyaan'       => (string) $faq->pertanyaan,
                'jawaban'          => (string) ($relevantVersion?->jawaban ?? ''),
                'status'           => $status,
                'status_label'     => $this->formatStatusLabel($status),
                'version_id'       => $relevantVersion?->id ?? ($faq->id . '-V1'),
                'is_target_status' => in_array(strtolower($status), ['terbit', 'diterima', 'menunggu validasi', 'draf', 'ditolak', 'active', 'approved', 'accepted', 'submitted', 'pending', 'draft', 'rejected'], true),
                'all_versions'     => $faq->versions->map(fn ($v) => [
                    'id'           => $v->id,
                    'status'       => (string) $v->status,
                    'status_label' => $this->formatStatusLabel((string) $v->status),
                    'jawaban'      => (string) $v->jawaban,
                    'norm_jawaban' => $this->normalizeRaw((string) $v->jawaban),
                ])->values()->all(),
            ];
        })->filter(fn ($row) => $row['is_target_status'])->values();
    }

    /**
     * Cek apakah ada FAQ dengan kondisi Draf yang memiliki Pertanyaan DAN Jawaban yang sama persis.
     */
    public function findExactDraftMatch(string $inputQuestion, string $inputAnswer, ?string $excludeFaqId = null): ?array
    {
        $normQ = $this->normalizeRaw($inputQuestion);
        $normA = $this->normalizeRaw($inputAnswer);

        if ($normQ === '' || $normA === '') {
            return null;
        }

        $candidates = $this->getCandidateFaqs($excludeFaqId);
        foreach ($candidates as $cand) {
            if ($this->normalizeRaw($cand['pertanyaan']) !== $normQ) {
                continue;
            }

            foreach ($cand['all_versions'] as $ver) {
                if (in_array(strtolower($ver['status']), ['draf', 'draft'], true) && $ver['norm_jawaban'] === $normA) {
                    return [
                        'id'           => $cand['id'],
                        'version_id'   => $ver['id'],
                        'pertanyaan'   => $cand['pertanyaan'],
                        'jawaban'      => $ver['jawaban'],
                        'status'       => 'Draf',
                        'status_label' => 'Draf',
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Alur Lengkap Deteksi Duplikat & Kemiripan:
     * 1. Normalisasi data input (lowercase, bersihkan tanda baca & spasi)
     * 2. Cek duplikat identik (100%) pada FAQ di database (termasuk Draft jika jawaban juga sama persis)
     * 3. Cari kandidat FAQ yang mirip dari database
     * 4. Hitung similarity score (TF-IDF / BM25 token overlap + Character Trigram Dice)
     */
    public function analyzeQuestion(string $inputQuestion, ?string $excludeFaqId = null, float $candidateThreshold = 30.0, ?string $inputAnswer = null): array
    {
        $normalizedInput = $this->normalizeRaw($inputQuestion);
        $normalizedAnswer = $inputAnswer !== null ? $this->normalizeRaw($inputAnswer) : '';

        if ($normalizedInput === '' || mb_strlen($normalizedInput) < 3) {
            return [
                'normalized_input'      => $normalizedInput,
                'has_exact_match'       => false,
                'exact_match'           => null,
                'has_exact_draft_match' => false,
                'exact_draft_match'     => null,
                'candidates'            => [],
                'max_similarity'        => 0.0,
            ];
        }

        $inputTokens = $this->extractNormalizedTokens($inputQuestion);
        $candidatesDb = $this->getCandidateFaqs($excludeFaqId);
        $exactDraftMatch = ($normalizedAnswer !== '')
            ? $this->findExactDraftMatch($inputQuestion, (string) $inputAnswer, $excludeFaqId)
            : null;

        // Hitung IDF sederhana dari korpus kandidat (gaya TNTSearch / BM25)
        $docCount = max(1, $candidatesDb->count() + 1);
        $docFreq = [];
        $preparedDocs = [];

        foreach ($candidatesDb as $cand) {
            $candNorm = $this->normalizeRaw($cand['pertanyaan']);
            $candTokens = $this->extractNormalizedTokens($cand['pertanyaan']);
            foreach (array_unique($candTokens) as $tk) {
                $docFreq[$tk] = ($docFreq[$tk] ?? 0) + 1;
            }
            $preparedDocs[] = array_merge($cand, [
                'normalized' => $candNorm,
                'tokens'     => $candTokens,
            ]);
        }

        foreach (array_unique($inputTokens) as $tk) {
            $docFreq[$tk] = ($docFreq[$tk] ?? 0) + 1;
        }

        $exactMatch = null;
        $scoredCandidates = [];
        $maxSimilarity = 0.0;

        foreach ($preparedDocs as $doc) {
            // 2. Cek Duplikat Identik (100%)
            if ($doc['normalized'] === $normalizedInput) {
                $score = 100.0;
            } else {
                // 4. Hitung Similarity Score gabungan (TF-IDF Cosine + Token Jaccard/Dice + Trigram)
                $tfidfCosine = $this->computeTfIdfCosine($inputTokens, $doc['tokens'], $docFreq, $docCount);
                $tokenDice   = $this->computeTokenDice($inputTokens, $doc['tokens']);
                $trigramSim  = $this->computeTrigramDice($normalizedInput, $doc['normalized']);

                // Gabungan terbobot agar tangguh terhadap bahasa informal maupun parafrasa
                $combined = ($tfidfCosine * 0.45) + ($tokenDice * 0.35) + ($trigramSim * 0.20);

                // Jika seluruh token inti pertanyaan sama persis (misal hanya beda kata tanya "bagaimana" vs "gimana"), beri skor tinggi
                if (!empty($inputTokens) && $inputTokens === $doc['tokens']) {
                    $combined = max($combined, 0.96);
                }

                $score = round(min(99.0, $combined * 100), 1);
            }

            if ($score > $maxSimilarity) {
                $maxSimilarity = $score;
            }

            $item = [
                'id'           => $doc['id'],
                'pertanyaan'   => $doc['pertanyaan'],
                'status'       => $doc['status'],
                'status_label' => $doc['status_label'],
                'version_id'   => $doc['version_id'],
                'similarity'   => $score,
                'is_exact'     => ($score >= 99.5),
                'level'        => $score >= 99.5 ? 'exact' : ($score >= 70.0 ? 'high' : 'moderate'),
            ];

            if ($item['is_exact'] && $exactMatch === null) {
                $exactMatch = $item;
            }

            if ($score >= $candidateThreshold) {
                $scoredCandidates[] = $item;
            }
        }

        // Urutkan berdasarkan similarity tertinggi
        usort($scoredCandidates, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);
        $scoredCandidates = array_slice($scoredCandidates, 0, 5);

        return [
            'normalized_input'      => $normalizedInput,
            'has_exact_match'       => $exactMatch !== null,
            'exact_match'           => $exactMatch,
            'has_exact_draft_match' => $exactDraftMatch !== null,
            'exact_draft_match'     => $exactDraftMatch,
            'candidates'            => $scoredCandidates,
            'max_similarity'        => $maxSimilarity,
        ];
    }

    /**
     * Hitung Cosine Similarity berbasis bobot IDF (gaya TNTSearch)
     */
    private function computeTfIdfCosine(array $tokensA, array $tokensB, array $docFreq, int $docCount): float
    {
        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $vocab = array_unique(array_merge($tokensA, $tokensB));
        $setA = array_count_values($tokensA);
        $setB = array_count_values($tokensB);

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vocab as $term) {
            $df = $docFreq[$term] ?? 1;
            $idf = log(1 + (($docCount - $df + 0.5) / ($df + 0.5))) + 1.0;

            $wA = isset($setA[$term]) ? (1 + log($setA[$term])) * $idf : 0.0;
            $wB = isset($setB[$term]) ? (1 + log($setB[$term])) * $idf : 0.0;

            $dot   += $wA * $wB;
            $normA += $wA * $wA;
            $normB += $wB * $wB;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Hitung Sorensen-Dice coefficient pada token yang sudah dinormalisasi
     */
    private function computeTokenDice(array $tokensA, array $tokensB): float
    {
        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $uniqueA = array_unique($tokensA);
        $uniqueB = array_unique($tokensB);
        $intersection = count(array_intersect($uniqueA, $uniqueB));

        return (2.0 * $intersection) / (count($uniqueA) + count($uniqueB));
    }

    /**
     * Hitung Sorensen-Dice coefficient pada character trigram (tahan typo ringan)
     */
    private function computeTrigramDice(string $strA, string $strB): float
    {
        $triA = $this->buildTrigrams($strA);
        $triB = $this->buildTrigrams($strB);

        if (empty($triA) || empty($triB)) {
            return 0.0;
        }

        $inter = count(array_intersect($triA, $triB));
        return (2.0 * $inter) / (count($triA) + count($triB));
    }

    /**
     * @return string[]
     */
    private function buildTrigrams(string $str): array
    {
        $padded = '  ' . $str . '  ';
        $len = mb_strlen($padded, 'UTF-8');
        $trigrams = [];

        for ($i = 0; $i <= $len - 3; $i++) {
            $trigrams[] = mb_substr($padded, $i, 3, 'UTF-8');
        }

        return array_values(array_unique($trigrams));
    }
}


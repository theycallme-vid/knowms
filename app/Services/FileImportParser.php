<?php

namespace App\Services;

use SimpleXMLElement;
use ZipArchive;

class FileImportParser
{
    /**
     * Membaca file Excel (.xlsx), CSV (.csv/.txt), atau PDF (.pdf)
     * dan mengembalikan array 2 dimensi (baris & kolom) atau teks terstruktur.
     */
    public static function parseRows(string $filePath, string $extension): array
    {
        $ext = strtolower(trim($extension));

        if ($ext === 'xlsx') {
            return self::parseXlsx($filePath);
        }

        if (in_array($ext, ['csv', 'txt', 'xls'])) {
            // Coba buka sebagai XLSX terlebih dahulu jika file .xls sebenarnya adalah .xlsx
            if ($ext === 'xls') {
                $xlsxRows = self::parseXlsx($filePath);
                if (!empty($xlsxRows)) {
                    return $xlsxRows;
                }
            }
            return self::parseCsv($filePath);
        }

        if ($ext === 'pdf') {
            return self::parsePdfToRows($filePath);
        }

        return [];
    }

    /**
     * Membaca file .xlsx secara native tanpa bergantung pada ekstensi ZipArchive (menggunakan parser binary ZIP native + SimpleXMLElement).
     * Membaca xl/sharedStrings.xml dan xl/worksheets/sheet1.xml.
     */
    public static function parseXlsx(string $filePath): array
    {
        $entries = self::readZipEntries($filePath);
        if (empty($entries)) {
            return [];
        }

        // 1. Baca Shared Strings (xl/sharedStrings.xml) jika ada
        $sharedStrings = [];
        $sharedStringsXml = $entries['xl/sharedStrings.xml'] ?? false;
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml !== false && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) ($run->t ?? '');
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca Worksheet pertama (xl/worksheets/sheet1.xml)
        $sheetXml = $entries['xl/worksheets/sheet1.xml'] ?? false;
        if ($sheetXml === false) {
            foreach ($entries as $entryName => $entryContent) {
                if (str_starts_with($entryName, 'xl/worksheets/sheet') && str_ends_with($entryName, '.xml')) {
                    $sheetXml = $entryContent;
                    break;
                }
            }
        }

        if ($sheetXml === false) {
            return [];
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || !isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            $maxColIndex = -1;

            foreach ($row->c as $cell) {
                $cellRef = (string) ($cell['r'] ?? '');
                $colLetters = preg_replace('/[0-9]+/', '', $cellRef);
                $colIndex = self::columnIndexFromString($colLetters);

                $cellType = (string) ($cell['t'] ?? '');
                $value = '';

                if ($cellType === 's') {
                    // Shared string index
                    $idx = (int) ($cell->v ?? -1);
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($cellType === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $rowData[$colIndex] = trim($value);
                if ($colIndex > $maxColIndex) {
                    $maxColIndex = $colIndex;
                }
            }

            if ($maxColIndex >= 0) {
                $normalizedRow = [];
                for ($i = 0; $i <= $maxColIndex; $i++) {
                    $normalizedRow[$i] = $rowData[$i] ?? '';
                }
                // Hanya masukkan baris jika ada minimal 1 kolom yang tidak kosong
                if (count(array_filter($normalizedRow, fn($v) => $v !== '')) > 0) {
                    $rows[] = $normalizedRow;
                }
            }
        }

        return $rows;
    }

    /**
     * Konversi huruf kolom Excel (A, B, C ... Z, AA) menjadi index 0-based (0, 1, 2...).
     */
    private static function columnIndexFromString(string $colLetters): int
    {
        $colLetters = strtoupper(trim($colLetters));
        if ($colLetters === '') {
            return 0;
        }
        $length = strlen($colLetters);
        $index = 0;
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($colLetters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Membaca file CSV / TXT dengan deteksi delimiter otomatis (koma, titik koma, atau tab).
     */
    public static function parseCsv(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return [];
        }

        // Hapus UTF-8 BOM jika ada
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Deteksi delimiter dari baris pertama
        $firstLine = strtok($content, "\r\n");
        $commaCount = substr_count($firstLine, ',');
        $semiCount = substr_count($firstLine, ';');
        $tabCount = substr_count($firstLine, "\t");

        $delimiter = ',';
        if ($semiCount > $commaCount && $semiCount >= $tabCount) {
            $delimiter = ';';
        } elseif ($tabCount > $commaCount && $tabCount > $semiCount) {
            $delimiter = "\t";
        }

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $cleaned = array_map(fn($val) => trim((string) $val), $data);
            if (count(array_filter($cleaned, fn($v) => $v !== '')) > 0) {
                $rows[] = $cleaned;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Mengekstrak teks dari file PDF dan mengubahnya menjadi baris-baris data.
     * Mendukung dekompresi stream FlateDecode dan pembacaan blok teks PDF (BT...ET).
     */
    public static function parsePdfToRows(string $filePath): array
    {
        $rawText = self::extractPdfText($filePath);
        if (trim($rawText) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $cleanLines = [];
        foreach ($lines as $line) {
            $t = trim(preg_replace('/\s+/', ' ', $line));
            if ($t !== '') {
                $cleanLines[] = $t;
            }
        }

        if (empty($cleanLines)) {
            return [];
        }

        // Coba deteksi jika baris menggunakan pemisah "|" atau ";" atau Tab
        $delimitedRows = [];
        foreach ($cleanLines as $line) {
            if (str_contains($line, '|')) {
                $delimitedRows[] = array_map('trim', explode('|', $line));
            } elseif (substr_count($line, ';') >= 1) {
                $delimitedRows[] = array_map('trim', explode(';', $line));
            }
        }

        if (count($delimitedRows) >= 1) {
            return $delimitedRows;
        }

        // Deteksi format Q&A (Pertanyaan / Jawaban / Kata Kunci / Sumber)
        $qaRows = [];
        $currentQ = '';
        $currentA = '';
        $currentTags = '';
        $currentSource = '';

        foreach ($cleanLines as $line) {
            if (preg_match('/^(?:Pertanyaan|Tanya|Q|FAQ[\s\-\d]*)\s*[:\.\-]\s*(.+)$/i', $line, $m)) {
                if ($currentQ !== '') {
                    $qaRows[] = [$currentQ, $currentA, $currentTags, $currentSource];
                    $currentA = '';
                    $currentTags = '';
                    $currentSource = '';
                }
                $currentQ = trim($m[1]);
            } elseif (preg_match('/^(?:Jawaban|Jawab|A)\s*[:\.\-]\s*(.+)$/i', $line, $m)) {
                $currentA = trim($m[1]);
            } elseif (preg_match('/^(?:Kata\s*Kunci|Tag|Tags|Keyword)\s*[:\.\-]\s*(.+)$/i', $line, $m)) {
                $currentTags = trim($m[1]);
            } elseif (preg_match('/^(?:Sumber|Pustaka|Referensi|Rujukan)\s*[:\.\-]\s*(.+)$/i', $line, $m)) {
                $currentSource = trim($m[1]);
            } elseif (str_ends_with($line, '?') && ($currentQ === '' || $currentA !== '')) {
                if ($currentQ !== '') {
                    $qaRows[] = [$currentQ, $currentA, $currentTags, $currentSource];
                    $currentA = '';
                    $currentTags = '';
                    $currentSource = '';
                }
                $currentQ = preg_replace('/^\d+[\.\)]\s*/', '', $line);
            } else {
                if ($currentQ !== '') {
                    $currentA = trim($currentA . ' ' . $line);
                } else {
                    // Simpan sebagai baris 1 kolom (berguna untuk import Kata Kunci / Sumber Pustaka dari PDF)
                    $qaRows[] = [$line];
                }
            }
        }

        if ($currentQ !== '') {
            $qaRows[] = [$currentQ, $currentA, $currentTags, $currentSource];
        }

        return $qaRows;
    }

    /**
     * Ekstraktor teks native dari binary PDF.
     */
    public static function extractPdfText(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if ($content === false || $content === '') {
            return '';
        }

        $extractedTexts = [];

        // Cari semua blok stream ... endstream
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
            foreach ($matches[1] as $streamData) {
                $uncompressed = @gzuncompress($streamData);
                if ($uncompressed === false) {
                    $uncompressed = @gzinflate($streamData);
                }
                $dataToParse = ($uncompressed !== false) ? $uncompressed : $streamData;

                // Hanya proses jika mengandung operator teks PDF
                if (str_contains($dataToParse, 'Tj') || str_contains($dataToParse, 'TJ')) {
                    $text = self::extractTextFromPdfStream($dataToParse);
                    if (trim($text) !== '') {
                        $extractedTexts[] = $text;
                    }
                }
            }
        }

        // Jika tidak ada stream terkompresi yang terbaca, coba langsung dari body PDF
        if (empty($extractedTexts)) {
            $fallback = self::extractTextFromPdfStream($content);
            if (trim($fallback) !== '') {
                $extractedTexts[] = $fallback;
            }
        }

        return implode("\n", $extractedTexts);
    }

    private static function extractTextFromPdfStream(string $stream): string
    {
        $lines = [];

        // Ambil blok BT ... ET
        if (preg_match_all('/BT\s*(.*?)\s*ET/s', $stream, $btMatches)) {
            foreach ($btMatches[1] as $block) {
                $blockText = '';
                // Array TJ: [(...) -20 (...)] TJ
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArrMatches)) {
                    foreach ($tjArrMatches[1] as $tjArr) {
                        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $tjArr, $strMatches)) {
                            foreach ($strMatches[0] as $s) {
                                $blockText .= self::decodePdfString(substr($s, 1, -1));
                            }
                            $blockText .= ' ';
                        }
                    }
                }
                // String tunggal (...) Tj
                if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)\s*Tj/s', $block, $tjMatches)) {
                    foreach ($tjMatches[0] as $tj) {
                        if (preg_match('/^\((.*)\)\s*Tj$/s', $tj, $m)) {
                            $blockText .= self::decodePdfString($m[1]) . ' ';
                        }
                    }
                }
                if (trim($blockText) !== '') {
                    $lines[] = trim($blockText);
                }
            }
        }

        return implode("\n", $lines);
    }

    private static function decodePdfString(string $str): string
    {
        $replacements = [
            '\\n'  => "\n",
            '\\r'  => "\r",
            '\\t'  => "\t",
            '\\('  => '(',
            '\\)'  => ')',
            '\\\\' => '\\',
        ];
        $decoded = strtr($str, $replacements);
        // Hapus karakter non-printable selain newline/spasi
        return preg_replace('/[^\x20-\x7E\xA0-\xFF\r\n]/', '', $decoded);
    }

    /**
     * Mengonversi baris mentah hasil pembacaan file menjadi struktur item Pratinjau FAQ.
     *
     * Aturan ID & Pengecekan Duplikat / Kemiripan:
     * 1. Setiap baris FAQ yang diimport selalu mendapatkan kode FAQ terbaru secara berurutan
     *    melanjutkan nomor FAQ terakhir di database (contoh: jika sudah ada FAQ-0001 s/d FAQ-0003,
     *    maka hasil import otomatis mulai dari FAQ-0004, FAQ-0005, dst. tanpa duplikat ID).
     * 2. Setiap pertanyaan dinormalisasi dan diperiksa menggunakan FaqSimilarityService terhadap
     *    FAQ di database yang berstatus Active/Approved (Active Publish) maupun Submitted (Menunggu Validasi).
     */
    public static function mapRowsToFaqPreview(array $rows, int $startFaqNumber = 1): array
    {
        if (empty($rows)) {
            return [];
        }

        try {
            $maxDbSeq = \App\Models\Faq::getMaxSequenceNumber();
            $counter = max($startFaqNumber, $maxDbSeq + 1);
        } catch (\Throwable $e) {
            $counter = max(1, $startFaqNumber);
        }

        $similarityService = new FaqSimilarityService();
        $items = [];
        $seenNormalizedInFile = [];

        foreach ($rows as $rowIndex => $row) {
            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');

            // Skip baris header (baris pertama yang berisi judul kolom)
            if ($rowIndex === 0) {
                $headerCheck = strtolower($col0 . ' ' . $col1 . ' ' . trim($row[2] ?? '') . ' ' . trim($row[3] ?? ''));
                if (
                    str_contains($headerCheck, 'pertanyaan') ||
                    str_contains($headerCheck, 'jawaban') ||
                    str_contains($headerCheck, 'kata kunci') ||
                    str_contains($headerCheck, 'question') ||
                    str_contains($headerCheck, 'answer') ||
                    str_contains($headerCheck, 'kode') ||
                    str_contains($headerCheck, 'kolom a') ||
                    $col0 === 'no' ||
                    $col0 === 'no.'
                ) {
                    continue;
                }
            }

            // Deteksi apakah Kolom 0 adalah Nomor Urut atau Kode FAQ lama dari template
            $hasLeadingCodeOrNo = false;
            if (preg_match('/^FAQ-\d+/i', $col0) || preg_match('/^\d+$/', $col0)) {
                $hasLeadingCodeOrNo = true;
            }

            if ($hasLeadingCodeOrNo && count($row) >= 3) {
                $pertanyaan    = trim($row[1] ?? '');
                $jawaban       = trim($row[2] ?? '');
                $tagsRaw       = trim($row[3] ?? '');
                $pustakaJudul  = trim($row[4] ?? '');
                $pustakaBagian = trim($row[5] ?? '');
            } else {
                $pertanyaan    = trim($row[0] ?? '');
                $jawaban       = trim($row[1] ?? '');
                $tagsRaw       = trim($row[2] ?? '');
                $pustakaJudul  = trim($row[3] ?? '');
                $pustakaBagian = trim($row[4] ?? '');
            }

            // Jika file 1 kolom dengan format "Pertanyaan | Jawaban | Kata Kunci" atau "Pertanyaan ; Jawaban ; Kata Kunci"
            if ($pertanyaan !== '' && $jawaban === '') {
                if (str_contains($pertanyaan, '|')) {
                    $parts = array_map('trim', explode('|', $pertanyaan));
                    $pertanyaan = $parts[0] ?? '';
                    $jawaban    = $parts[1] ?? '';
                    if ($tagsRaw === '' && !empty($parts[2])) {
                        $tagsRaw = $parts[2];
                    }
                } elseif (str_contains($pertanyaan, ';')) {
                    $parts = array_map('trim', explode(';', $pertanyaan));
                    $pertanyaan = $parts[0] ?? '';
                    $jawaban    = $parts[1] ?? '';
                    if ($tagsRaw === '' && !empty($parts[2])) {
                        $tagsRaw = $parts[2];
                    }
                }
            }

            if ($pertanyaan === '' && $jawaban === '') {
                continue;
            }

            // Pecah kata kunci dengan koma, titik koma, baris baru, atau pipa (jika ada) lalu cocokkan dengan Kata Kunci sistem
            $tags = [];
            if ($tagsRaw !== '') {
                $rawTagList = array_values(array_filter(
                    array_map('trim', preg_split('/[,;\r\n|]+/', $tagsRaw)),
                    fn($t) => $t !== ''
                ));
                $tags = self::resolveMatchingSystemTags($rawTagList);
            }

            // 1. Selalu gunakan ID FAQ terbaru berurutan tanpa duplikat
            $faqId = sprintf('FAQ-%04d', $counter++);
            $versionNum = 1;
            $versionId = $faqId . '-V1';

            // 2. Analisis Duplikat Identik (100%) & Kemiripan Makna terhadap DB (Active Publish / Menunggu Validasi / Draft)
            $analysis = $similarityService->analyzeQuestion($pertanyaan, null, 30.0, $jawaban);
            $normQ = $analysis['normalized_input'] ?? '';

            $inFileDuplicateOf = null;
            if ($normQ !== '' && isset($seenNormalizedInFile[$normQ])) {
                $inFileDuplicateOf = $seenNormalizedInFile[$normQ];
            } elseif ($normQ !== '') {
                $seenNormalizedInFile[$normQ] = $faqId;
            }

            // Status 'ready' (Siap diajukan) jika Pertanyaan, Jawaban, dan Kata Kunci terisi (Sumber Pustaka bersifat opsional)
            $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags));
            $hasExactMatch = (bool) ($analysis['has_exact_match'] ?? false);
            $hasExactDraftMatch = (bool) ($analysis['has_exact_draft_match'] ?? false);

            $items[] = [
                'row_key'               => $versionId,
                'id'                    => $faqId,
                'version_id'            => $versionId,
                'version_num'           => $versionNum,
                'version'               => 'Versi ' . $versionNum,
                'pertanyaan'            => $pertanyaan,
                'jawaban'               => $jawaban,
                'tags'                  => $tags,
                'pustaka_judul'         => $pustakaJudul,
                'pustaka_bagian'        => $pustakaBagian !== '' ? $pustakaBagian : ($pustakaJudul !== '' ? 'Dokumen Rujukan Resmi' : 'Opsional'),
                'status'                => ($isReady && !$hasExactDraftMatch) ? 'ready' : 'incomplete',
                'checked'               => $isReady && !$hasExactMatch && !$hasExactDraftMatch,
                'has_exact_match'       => $hasExactMatch,
                'exact_match'           => $analysis['exact_match'] ?? null,
                'has_exact_draft_match' => $hasExactDraftMatch,
                'exact_draft_match'     => $analysis['exact_draft_match'] ?? null,
                'similar_candidates'    => $analysis['candidates'] ?? [],
                'max_similarity'        => $analysis['max_similarity'] ?? 0.0,
                'in_file_duplicate'     => $inFileDuplicateOf,
            ];
        }

        return $items;
    }

    /**
     * Kamus sinonim, singkatan, dan variasi makna kata kunci akademik kampus
     */
    private const TAG_SYNONYM_PHRASES = [
        'ft'                              => 'Fakultas Teknik',
        'fak teknik'                      => 'Fakultas Teknik',
        'fakultas tehnik'                 => 'Fakultas Teknik',
        'fak tehnik'                      => 'Fakultas Teknik',
        'tehnik'                          => 'Teknik',
        'fh'                              => 'Fakultas Hukum',
        'fak hukum'                       => 'Fakultas Hukum',
        'fbe'                             => 'Fakultas Bisnis dan Ekonomika',
        'feb'                             => 'Fakultas Bisnis dan Ekonomika',
        'fak bisnis'                      => 'Fakultas Bisnis dan Ekonomika',
        'fakultas ekonomi'                => 'Fakultas Bisnis dan Ekonomika',
        'fakultas bisnis'                 => 'Fakultas Bisnis dan Ekonomika',
        'fakultas bisnis ekonomika'       => 'Fakultas Bisnis dan Ekonomika',
        'ff'                              => 'Fakultas Farmasi',
        'fak farmasi'                     => 'Fakultas Farmasi',
        'fp'                              => 'Fakultas Psikologi',
        'fpsi'                            => 'Fakultas Psikologi',
        'fak psikologi'                   => 'Fakultas Psikologi',
        'ftb'                             => 'Fakultas Teknobiologi',
        'fak teknobiologi'                => 'Fakultas Teknobiologi',
        'fakultas bioteknologi'           => 'Fakultas Teknobiologi',
        'fik'                             => 'Fakultas Industri Kreatif',
        'fak industri kreatif'            => 'Fakultas Industri Kreatif',
        'fk'                              => 'Fakultas Kedokteran',
        'fak kedokteran'                  => 'Fakultas Kedokteran',
        'ta'                              => 'Tugas Akhir',
        'skripsi'                         => 'Tugas Akhir',
        'tugas akhir skripsi'             => 'Tugas Akhir',
        'kartu rencana studi'             => 'KRS',
        'pengisian krs'                   => 'KRS',
        'perencanaan studi'               => 'Rencana Studi',
        'cuti'                            => 'Cuti Akademik',
        'cuti kuliah'                     => 'Cuti Akademik',
        'berhenti sementara'              => 'Cuti Akademik',
        'lab'                             => 'Laboratorium',
        'praktek'                         => 'Praktikum',
        'kemahasiswaan'                   => 'Layanan Mahasiswa',
        'layanan mhs'                     => 'Layanan Mahasiswa',
        'admin akademik'                  => 'Administrasi Akademik',
        'surat keterangan'                => 'Surat Akademik',
        'uang kuliah'                     => 'Biaya Pendidikan',
        'spp'                             => 'Biaya Pendidikan',
        'ukt'                             => 'Biaya Pendidikan',
    ];

    private const TAG_WORD_NORMALIZATION = [
        'fak'          => 'fakultas',
        'fklts'        => 'fakultas',
        'fkltas'       => 'fakultas',
        'faklutas'     => 'fakultas',
        'fakults'      => 'fakultas',
        'tehnik'       => 'teknik',
        'teknk'        => 'teknik',
        'ekonomi'      => 'ekonomika',
        'mhs'          => 'mahasiswa',
        'mahasiwa'     => 'mahasiswa',
        'mahasisa'     => 'mahasiswa',
        'mhsiswa'      => 'mahasiswa',
        'admin'        => 'administrasi',
        'adm'          => 'administrasi',
        'akademk'      => 'akademik',
        'akadmik'      => 'akademik',
        'akdemik'      => 'akademik',
        'lab'          => 'laboratorium',
        'laboratrium'  => 'laboratorium',
        'praktek'      => 'praktikum',
        'praktkum'     => 'praktikum',
        'beasisw'      => 'beasiswa',
        'besiswa'      => 'beasiswa',
        'psikolog'     => 'psikologi',
        'kedoktran'    => 'kedokteran',
        'farmas'       => 'farmasi',
        'hukm'         => 'hukum',
        'kreatf'       => 'kreatif',
    ];

    /**
     * Normalisasi string kata kunci untuk perbandingan (lowercase, hapus tanda baca, ganti kata singkatan/typo umum).
     */
    private static function normalizeTagForComparison(string $tag): string
    {
        $clean = mb_strtolower(trim($tag), 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s+/u', ' ', trim($clean)) ?? '';

        if ($clean === '') {
            return '';
        }

        if (isset(self::TAG_SYNONYM_PHRASES[$clean])) {
            $mapped = mb_strtolower(self::TAG_SYNONYM_PHRASES[$clean], 'UTF-8');
            $mapped = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $mapped) ?? $mapped;
            return preg_replace('/\s+/u', ' ', trim($mapped)) ?? $mapped;
        }

        $words = explode(' ', $clean);
        $normWords = [];
        foreach ($words as $w) {
            $normWords[] = self::TAG_WORD_NORMALIZATION[$w] ?? $w;
        }

        $rebuilt = implode(' ', $normWords);
        if (isset(self::TAG_SYNONYM_PHRASES[$rebuilt])) {
            $mapped = mb_strtolower(self::TAG_SYNONYM_PHRASES[$rebuilt], 'UTF-8');
            $mapped = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $mapped) ?? $mapped;
            return preg_replace('/\s+/u', ' ', trim($mapped)) ?? $mapped;
        }

        return $rebuilt;
    }

    /**
     * Hitung skor kemiripan antara input kata kunci dengan kata kunci terdaftar di sistem (0.0 s/d 1.0).
     * Menangani perbedaan huruf besar/kecil, typo (Levenshtein & word-level), serta kemiripan makna/sinonim.
     */
    private static function calculateTagSimilarityScore(string $rawInput, string $normInput, string $sysTag, string $normSys): float
    {
        if ($normInput === '' || $normSys === '') {
            return 0.0;
        }

        // 1. Sama persis setelah normalisasi huruf kecil & sinonim
        if ($normInput === $normSys || mb_strtolower(trim($rawInput), 'UTF-8') === mb_strtolower(trim($sysTag), 'UTF-8')) {
            return 1.0;
        }

        // 2. Full-string Levenshtein similarity (untuk string <= 255 byte)
        $maxLen = max(strlen($normInput), strlen($normSys));
        $levScore = 0.0;
        if ($maxLen > 0 && strlen($normInput) <= 250 && strlen($normSys) <= 250) {
            $dist = levenshtein($normInput, $normSys);
            $levScore = max(0.0, 1.0 - ($dist / $maxLen));

            // Toleransi typo langsung: jarak edit <= 1 pada kata >= 4 huruf, atau <= 2 pada kata >= 7 huruf, atau <= 3 pada frasa >= 12 huruf
            $minLen = min(mb_strlen($normInput, 'UTF-8'), mb_strlen($normSys, 'UTF-8'));
            if (
                ($minLen >= 4 && $dist <= 1) ||
                ($minLen >= 7 && $dist <= 2) ||
                ($minLen >= 12 && $dist <= 3)
            ) {
                $levScore = max($levScore, 0.88);
            }
        }

        // 3. similar_text percentage
        $simPct = 0.0;
        similar_text($normInput, $normSys, $simPct);
        $simTextScore = $simPct / 100.0;

        // 4. Perbandingan kata demi kata (tahan typo pada masing-masing kata dalam frasa seperti "fakults tehnik" vs "Fakultas Teknik")
        $wordsA = array_values(array_filter(explode(' ', $normInput), fn($w) => $w !== ''));
        $wordsB = array_values(array_filter(explode(' ', $normSys), fn($w) => $w !== ''));
        $wordLevelScore = 0.0;

        if (!empty($wordsA) && !empty($wordsB)) {
            $sumBestA = 0.0;
            foreach ($wordsA as $wA) {
                $bestW = 0.0;
                foreach ($wordsB as $wB) {
                    if ($wA === $wB) {
                        $bestW = 1.0;
                        break;
                    }
                    $wMax = max(strlen($wA), strlen($wB));
                    if ($wMax > 0) {
                        $wDist = levenshtein($wA, $wB);
                        $wSim = max(0.0, 1.0 - ($wDist / $wMax));
                        if (min(strlen($wA), strlen($wB)) >= 5 && $wDist <= 2) {
                            $wSim = max($wSim, 0.84);
                        }
                        if ($wSim > $bestW) {
                            $bestW = $wSim;
                        }
                    }
                }
                $sumBestA += $bestW;
            }

            $sumBestB = 0.0;
            foreach ($wordsB as $wB) {
                $bestW = 0.0;
                foreach ($wordsA as $wA) {
                    if ($wB === $wA) {
                        $bestW = 1.0;
                        break;
                    }
                    $wMax = max(strlen($wA), strlen($wB));
                    if ($wMax > 0) {
                        $wDist = levenshtein($wA, $wB);
                        $wSim = max(0.0, 1.0 - ($wDist / $wMax));
                        if (min(strlen($wA), strlen($wB)) >= 5 && $wDist <= 2) {
                            $wSim = max($wSim, 0.84);
                        }
                        if ($wSim > $bestW) {
                            $bestW = $wSim;
                        }
                    }
                }
                $sumBestB += $bestW;
            }

            $avgA = $sumBestA / count($wordsA);
            $avgB = $sumBestB / count($wordsB);
            $wordLevelScore = (2 * $avgA * $avgB) / max(0.0001, $avgA + $avgB);
        }

        return max($levScore, ($simTextScore * 0.5) + ($wordLevelScore * 0.5), $wordLevelScore);
    }

    /**
     * Mencocokkan satu input Kata Kunci dengan daftar Kata Kunci yang terdaftar di sistem (tabel ttag).
     * Mengembalikan nama Kata Kunci resmi dari sistem jika cocok (termasuk beda huruf besar/kecil, typo, atau kemiripan makna).
     */
    public static function resolveSingleSystemTag(string $inputTag, ?array $systemTags = null): string
    {
        $cleanInput = substr(trim($inputTag), 0, 100);
        if ($cleanInput === '') {
            return '';
        }

        if ($systemTags === null) {
            try {
                $systemTags = \App\Models\Tag::orderBy('id', 'asc')->pluck('nama')->all();
            } catch (\Throwable $e) {
                $systemTags = [];
            }
        }

        if (empty($systemTags)) {
            $norm = self::normalizeTagForComparison($cleanInput);
            if (isset(self::TAG_SYNONYM_PHRASES[$norm])) {
                return self::TAG_SYNONYM_PHRASES[$norm];
            }
            return $cleanInput;
        }

        $normInput = self::normalizeTagForComparison($cleanInput);
        $bestMatch = null;
        $bestScore = 0.0;

        foreach ($systemTags as $sysTag) {
            $sysTagStr = trim((string) $sysTag);
            if ($sysTagStr === '') {
                continue;
            }

            $normSys = self::normalizeTagForComparison($sysTagStr);
            $score = self::calculateTagSimilarityScore($cleanInput, $normInput, $sysTagStr, $normSys);

            if ($score >= 0.999) {
                return $sysTagStr;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $sysTagStr;
            }
        }

        // Jika tingkat kemiripan >= 68% (mencakup perbedaan kapitalisasi, typo ringan/sedang, atau kemiripan penulisan/makna),
        // gunakan Kata Kunci resmi yang sudah terdaftar di sistem
        if ($bestMatch !== null && $bestScore >= 0.68) {
            return $bestMatch;
        }

        return $cleanInput;
    }

    /**
     * Mencocokkan array input Kata Kunci dari file unggahan dengan Kata Kunci yang terdaftar di sistem (tabel ttag).
     */
    public static function resolveMatchingSystemTags(array $rawTags, ?array $systemTags = null): array
    {
        if (empty($rawTags)) {
            return [];
        }

        if ($systemTags === null) {
            try {
                $systemTags = \App\Models\Tag::orderBy('id', 'asc')->pluck('nama')->all();
            } catch (\Throwable $e) {
                $systemTags = [];
            }
        }

        $resolved = [];
        $seenLower = [];

        foreach ($rawTags as $raw) {
            $matched = self::resolveSingleSystemTag((string) $raw, $systemTags);
            if ($matched === '') {
                continue;
            }
            $lower = mb_strtolower($matched, 'UTF-8');
            if (!isset($seenLower[$lower])) {
                $seenLower[$lower] = true;
                $resolved[] = $matched;
            }
        }

        return $resolved;
    }

    /**
     * Mengonversi baris mentah hasil pembacaan file menjadi daftar Kata Kunci (ttag).
     */
    public static function mapRowsToKeywords(array $rows): array
    {
        $keywords = [];
        foreach ($rows as $rowIndex => $row) {
            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');

            if ($rowIndex === 0) {
                $header = strtolower($col0 . ' ' . $col1);
                if (str_contains($header, 'kata kunci') || str_contains($header, 'tag') || str_contains($header, 'nama') || $col0 === 'no') {
                    continue;
                }
            }

            // Jika kolom 0 angka nomor urut dan kolom 1 berisi kata kunci
            $target = (preg_match('/^\d+$/', $col0) && $col1 !== '') ? $col1 : $col0;

            if ($target !== '') {
                $parts = preg_split('/[,;]+/', $target);
                foreach ($parts as $p) {
                    $clean = substr(trim($p), 0, 100);
                    if ($clean !== '' && !in_array(strtolower($clean), array_map('strtolower', $keywords))) {
                        $keywords[] = $clean;
                    }
                }
            }
        }
        return $keywords;
    }

    /**
     * Mengonversi baris mentah hasil pembacaan file Excel/CSV/PDF menjadi daftar Sumber Pustaka Khusus Tautan/Link (tdaftarpustaka).
     * Wajib membaca 3 kolom:
     * - Kolom 1: Judul Link
     * - Kolom 2: Link-nya (Maks. 60 karakter)
     * - Kolom 3: Tipe (Private / Public)
     * Sekaligus memvalidasi apakah setiap baris sudah sesuai atau belum untuk dipantau pada tabel pratinjau.
     */
    public static function mapRowsToSumberPustaka(array $rows): array
    {
        $sources = [];
        $judulColIdx = 0;
        $linkColIdx  = 1;
        $typeColIdx  = 2;

        foreach ($rows as $rowIndex => $row) {
            $col0 = trim((string) ($row[0] ?? ''));
            $col1 = trim((string) ($row[1] ?? ''));
            $col2 = trim((string) ($row[2] ?? ''));
            $col3 = trim((string) ($row[3] ?? ''));

            if ($col0 === '' && $col1 === '' && $col2 === '' && $col3 === '') {
                continue;
            }

            // Deteksi baris header untuk menentukan posisi 3 kolom: Judul Link, Link-nya, & Tipe (Private/Public)
            if ($rowIndex === 0) {
                $h0 = strtolower($col0);
                $h1 = strtolower($col1);
                $h2 = strtolower($col2);
                $h3 = strtolower($col3);
                $headerCombined = $h0 . ' ' . $h1 . ' ' . $h2 . ' ' . $h3;

                if (
                    str_contains($headerCombined, 'judul') ||
                    str_contains($headerCombined, 'link') ||
                    str_contains($headerCombined, 'sumber') ||
                    str_contains($headerCombined, 'pustaka') ||
                    str_contains($headerCombined, 'tautan') ||
                    str_contains($headerCombined, 'tipe') ||
                    $h0 === 'no' ||
                    $h0 === 'no.'
                ) {
                    if ($h0 === 'no' || $h0 === 'no.') {
                        $judulColIdx = 1;
                        $linkColIdx  = 2;
                        $typeColIdx  = 3;
                    } else {
                        $judulColIdx = 0;
                        $linkColIdx  = 1;
                        $typeColIdx  = 2;
                    }
                    continue;
                }
            }

            // Jika baris dari PDF hanya terbaca sebagai 1 kolom teks gabungan, pisahkan secara cerdas menjadi 3 bagian (Judul, Link, Tipe)
            if ($col0 !== '' && $col1 === '' && $col2 === '') {
                if (preg_match('/^(.*?)\s+(https?:\/\/\S+|\S+\.\S+\/\S*)\s+(public|private)$/i', $col0, $m)) {
                    $col0 = trim($m[1]);
                    $col1 = trim($m[2]);
                    $col2 = trim($m[3]);
                }
            }

            // Jika baris diawali nomor urut pada kolom 0
            if ($judulColIdx === 0 && preg_match('/^\d+$/', $col0) && ($col1 !== '' || $col2 !== '' || $col3 !== '')) {
                $rawJudul = $col1;
                $rawLink  = $col2;
                $rawType  = $col3;
            } else {
                $rawJudul = trim((string) ($row[$judulColIdx] ?? ($judulColIdx === 0 ? $col0 : '')));
                $rawLink  = trim((string) ($row[$linkColIdx] ?? ($linkColIdx === 1 ? $col1 : '')));
                $rawType  = trim((string) ($row[$typeColIdx] ?? ($typeColIdx === 2 ? $col2 : '')));
            }

            $issues = [];
            if ($rawJudul === '') {
                $issues[] = 'Judul Tautan kosong';
            }

            if ($rawLink === '') {
                $issues[] = 'Tautan Sumber Pustaka kosong';
            } elseif (mb_strlen($rawLink) > 60) {
                $issues[] = 'Tautan melebihi 60 karakter (dipotong otomatis)';
            }

            $cleanJudul = mb_substr($rawJudul, 0, 100);
            $cleanLink  = mb_substr($rawLink, 0, 60);
            $isValid    = ($cleanJudul !== '' && $cleanLink !== '');

            $sources[] = [
                'judul'        => $cleanJudul,
                'raw_judul'    => $rawJudul,
                'link'         => $cleanLink,
                'raw_link'     => $rawLink,
                'is_valid'     => $isValid,
                'status_label' => $isValid ? 'Sesuai' : 'Belum Sesuai',
                'notes'        => empty($issues) ? 'Judul dan Tautan sudah sesuai' : implode('; ', $issues),
            ];
        }

        return $sources;
    }

    /**
     * Membuat file Excel (.xlsx) asli tanpa memerlukan ekstensi php_zip (menggunakan pure-PHP ZIP builder)
     * agar setiap kolom terpisah rapi di masing-masing sel Excel (A, B, C, D, E) di semua komputer.
     */
    public static function generateXlsx(array $headers, array $dataRows, string $sheetTitle = 'Template'): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'kms_xlsx_');
        $files = [];

        // 1. [Content_Types].xml
        $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        // 2. _rels/.rels
        $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        // 3. xl/_rels/workbook.xml.rels
        $files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        // 4. xl/workbook.xml
        $safeTitle = htmlspecialchars(substr($sheetTitle, 0, 31), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $safeTitle . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        // 5. xl/styles.xml (Style 0 = Normal, Style 1 = Header Bold dengan background maroon KMS)
        $files['xl/styles.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF85172C"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFD1D5DB"/></left>'
            . '<right style="thin"><color rgb="FFD1D5DB"/></right>'
            . '<top style="thin"><color rgb="FFD1D5DB"/></top>'
            . '<bottom style="thin"><color rgb="FFD1D5DB"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';

        // 6. xl/worksheets/sheet1.xml
        $allRows = array_merge([$headers], $dataRows);
        $colCount = count($headers);

        $colsXml = '<cols>';
        for ($c = 1; $c <= $colCount; $c++) {
            $width = ($c === 1 || $c === 2) ? 42 : 28;
            $colsXml .= '<col min="' . $c . '" max="' . $c . '" width="' . $width . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $sheetDataXml = '<sheetData>';
        foreach ($allRows as $rIdx => $rowValues) {
            $excelRowNum = $rIdx + 1;
            $styleIndex = ($rIdx === 0) ? 1 : 0;
            $sheetDataXml .= '<row r="' . $excelRowNum . '">';

            foreach (array_values($rowValues) as $cIdx => $cellVal) {
                $colLetter = self::columnLetterFromIndex($cIdx);
                $cellRef = $colLetter . $excelRowNum;
                $escapedVal = htmlspecialchars((string) $cellVal, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $sheetDataXml .= '<c r="' . $cellRef . '" s="' . $styleIndex . '" t="inlineStr">'
                    . '<is><t>' . $escapedVal . '</t></is>'
                    . '</c>';
            }

            $sheetDataXml .= '</row>';
        }
        $sheetDataXml .= '</sheetData>';

        $files['xl/worksheets/sheet1.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $colsXml
            . $sheetDataXml
            . '</worksheet>';

        $zipBinary = self::buildZipBinary($files);
        file_put_contents($tempFile, $zipBinary);

        return $tempFile;
    }

    /**
     * Membuat binary arsip ZIP valid secara native di PHP tanpa ekstensi php_zip.
     */
    private static function buildZipBinary(array $files): string
    {
        $localHeaders = '';
        $centralDir = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $filename => $content) {
            $filename = str_replace('\\', '/', $filename);
            $compressed = gzdeflate($content, 6);
            $crc = crc32($content);
            $uncLen = strlen($content);
            $compLen = strlen($compressed);
            $fnLen = strlen($filename);

            // Local file header (30 bytes + filename + compressed data)
            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50, // Local file header signature
                20,         // Version needed to extract (2.0)
                0,          // General purpose bit flag
                8,          // Compression method (8 = Deflate)
                0,          // Last mod file time
                0,          // Last mod file date
                $crc,       // CRC-32
                $compLen,   // Compressed size
                $uncLen,    // Uncompressed size
                $fnLen,     // File name length
                0           // Extra field length
            ) . $filename . $compressed;

            // Central directory file header (46 bytes + filename)
            $centralRecord = pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50, // Central file header signature
                20,         // Version made by
                20,         // Version needed to extract
                0,          // General purpose bit flag
                8,          // Compression method (8 = Deflate)
                0,          // Last mod file time
                0,          // Last mod file date
                $crc,       // CRC-32
                $compLen,   // Compressed size
                $uncLen,    // Uncompressed size
                $fnLen,     // File name length
                0,          // Extra field length
                0,          // File comment length
                0,          // Disk number start
                0,          // Internal file attributes
                32,         // External file attributes
                $offset     // Relative offset of local header
            ) . $filename;

            $localHeaders .= $localHeader;
            $centralDir .= $centralRecord;
            $offset += strlen($localHeader);
            $count++;
        }

        // End of central directory record (22 bytes)
        $eocd = pack(
            'VvvvvVVv',
            0x06054b50,          // End of central dir signature
            0,                   // Number of this disk
            0,                   // Disk where central directory starts
            $count,              // Number of central directory records on this disk
            $count,              // Total number of central directory records
            strlen($centralDir), // Size of central directory
            $offset,             // Offset of start of central directory
            0                    // Comment length
        );

        return $localHeaders . $centralDir . $eocd;
    }

    /**
     * Membaca isi file ZIP (.xlsx) secara native di PHP (gunakan ZipArchive jika ada, atau binary parser via Central Directory).
     */
    private static function readZipEntries(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $entries = [];
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    $entries[$name] = $zip->getFromIndex($i);
                }
                $zip->close();
                return $entries;
            }
        }

        $data = @file_get_contents($filePath);
        if ($data === false || strlen($data) < 22) {
            return [];
        }

        // Cari End of Central Directory (EOCD) signature 0x06054b50 dari belakang
        $eocdPos = strrpos($data, "\x50\x4b\x05\x06");
        if ($eocdPos === false || strlen($data) - $eocdPos < 22) {
            return [];
        }

        $eocd = unpack('vcdDisk/vcdStart/vcdEntriesDisk/vcdEntriesTotal/VcdSize/VcdOffset', substr($data, $eocdPos + 4, 16));
        if (!$eocd) {
            return [];
        }

        $entries = [];
        $pos = $eocd['cdOffset'];
        $total = $eocd['cdEntriesTotal'];
        $dataLen = strlen($data);

        for ($i = 0; $i < $total; $i++) {
            if ($pos + 46 > $dataLen || substr($data, $pos, 4) !== "\x50\x4b\x01\x02") {
                break;
            }

            $cd = unpack(
                'vverMade/vverNeed/vflags/vmethod/vmodTime/vmodDate/Vcrc/VcompSize/VuncSize/vfnLen/vextraLen/vcommentLen/vdiskStart/vintAttr/VextAttr/VlocalOffset',
                substr($data, $pos + 4, 42)
            );
            if (!$cd) {
                break;
            }

            $filename = substr($data, $pos + 46, $cd['fnLen']);
            $pos += 46 + $cd['fnLen'] + $cd['extraLen'] + $cd['commentLen'];

            $localPos = $cd['localOffset'];
            if ($localPos + 30 > $dataLen || substr($data, $localPos, 4) !== "\x50\x4b\x03\x04") {
                continue;
            }

            $local = unpack('vfnLen/vextraLen', substr($data, $localPos + 26, 4));
            $dataStart = $localPos + 30 + $local['fnLen'] + $local['extraLen'];
            $compData = substr($data, $dataStart, $cd['compSize']);

            if ($cd['method'] === 0) {
                $entries[$filename] = $compData;
            } elseif ($cd['method'] === 8) {
                $inflated = @gzinflate($compData);
                if ($inflated !== false) {
                    $entries[$filename] = $inflated;
                }
            }
        }

        return $entries;
    }

    private static function columnLetterFromIndex(int $index): string
    {
        $letter = '';
        $num = $index + 1;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $num = intdiv($num - $mod, 26);
        }
        return $letter;
    }
}


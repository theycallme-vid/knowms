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
     * Membaca file .xlsx secara native menggunakan ZipArchive & SimpleXMLElement.
     * Membaca xl/sharedStrings.xml dan xl/worksheets/sheet1.xml.
     */
    public static function parseXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca Shared Strings (xl/sharedStrings.xml) jika ada
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
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
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

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
        if (!is_readable($filePath)) {
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

        // Coba deteksi jika baris menggunakan pemisah "|" atau ";"
        $delimitedRows = [];
        foreach ($cleanLines as $line) {
            if (str_contains($line, '|')) {
                $delimitedRows[] = array_map('trim', explode('|', $line));
            } elseif (substr_count($line, ';') >= 2) {
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
     * Format Kolom yang Didukung:
     * - Format Standar (4 atau 5 kolom):
     *   Kolom A: Pertanyaan FAQ
     *   Kolom B: Jawaban FAQ
     *   Kolom C: Kata Kunci (dipisahkan koma, misal: "akademik, cuti, mahasiswa")
     *   Kolom D: Sumber Pustaka / Link / Judul Dokumen
     *   Kolom E (Opsional): Bagian / Keterangan Sumber Pustaka
     *
     * - Format Dengan Kolom No/Kode di Depan (5 atau 6 kolom):
     *   Jika Kolom A berisi nomor urut ("1", "2") atau kode ("FAQ-0001"), otomatis bergeser membaca Kolom B sebagai Pertanyaan!
     */
    public static function mapRowsToFaqPreview(array $rows, int $startFaqNumber = 1): array
    {
        if (empty($rows)) {
            return [];
        }

        $items = [];
        $counter = $startFaqNumber;

        foreach ($rows as $rowIndex => $row) {
            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');

            // Skip baris header (baris pertama yang berisi judul kolom)
            if ($rowIndex === 0) {
                $headerCheck = strtolower($col0 . ' ' . $col1);
                if (
                    str_contains($headerCheck, 'pertanyaan') ||
                    str_contains($headerCheck, 'question') ||
                    str_contains($headerCheck, 'kode') ||
                    $col0 === 'no' ||
                    $col0 === 'no.'
                ) {
                    continue;
                }
            }

            // Deteksi apakah Kolom 0 adalah Nomor Urut atau Kode FAQ
            $hasLeadingCodeOrNo = false;
            if (
                preg_match('/^\d+$/', $col0) ||
                preg_match('/^FAQ-\d+/i', $col0)
            ) {
                $hasLeadingCodeOrNo = true;
            }

            if ($hasLeadingCodeOrNo && count($row) >= 2) {
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

            if ($pertanyaan === '' && $jawaban === '') {
                continue;
            }

            // Pecah kata kunci dengan koma atau titik koma
            $tags = [];
            if ($tagsRaw !== '') {
                $tags = array_values(array_filter(
                    array_map('trim', preg_split('/[,;]+/', $tagsRaw)),
                    fn($t) => $t !== ''
                ));
            }

            // Status 'ready' (Siap diajukan) jika Pertanyaan, Jawaban, Kata Kunci, dan Sumber Pustaka semuanya terisi
            $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && $pustakaJudul !== '');

            $faqId = sprintf('FAQ-%04d', $counter++);

            $items[] = [
                'id'             => $faqId,
                'version'        => 'Versi 1',
                'pertanyaan'     => $pertanyaan,
                'jawaban'        => $jawaban,
                'tags'           => $tags,
                'pustaka_judul'  => $pustakaJudul,
                'pustaka_bagian' => $pustakaBagian !== '' ? $pustakaBagian : ($pustakaJudul !== '' ? 'Dokumen Rujukan Resmi' : 'Belum diisi'),
                'status'         => $isReady ? 'ready' : 'incomplete',
                'checked'        => $isReady,
            ];
        }

        return $items;
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
     * Mengonversi baris mentah hasil pembacaan file menjadi daftar Sumber Pustaka (tdaftarpustaka).
     */
    public static function mapRowsToSumberPustaka(array $rows): array
    {
        $sources = [];
        foreach ($rows as $rowIndex => $row) {
            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');
            $col2 = trim($row[2] ?? '');

            if ($rowIndex === 0) {
                $header = strtolower($col0 . ' ' . $col1);
                if (str_contains($header, 'judul') || str_contains($header, 'link') || str_contains($header, 'sumber') || str_contains($header, 'tipe') || $col0 === 'no') {
                    continue;
                }
            }

            // Deteksi apakah kolom 0 adalah nomor urut
            if (preg_match('/^\d+$/', $col0) && ($col1 !== '' || $col2 !== '')) {
                $col0 = $col1;
                $col1 = $col2;
            }

            $type = 'link';
            $linkName = $col0;

            if (in_array(strtolower($col0), ['link', 'upload']) && $col1 !== '') {
                $type = strtolower($col0);
                $linkName = $col1;
            } elseif ($col0 !== '') {
                $linkName = $col0;
            }

            $linkName = substr(trim($linkName), 0, 45);
            if ($linkName !== '') {
                $sources[] = [
                    'type' => $type,
                    'link' => $linkName,
                ];
            }
        }
        return $sources;
    }
}


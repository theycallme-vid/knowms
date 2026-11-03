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
     * Konsep ID & Versi:
     * - Jika pertanyaan baru (atau belum ada di tabel tfaqs):
     *   ID FAQ bertambah (FAQ-0001, FAQ-0002, dst.) dan ID Versi adalah FAQ-XXXX-V1 (Versi 1).
     * - Jika pertanyaan sudah ada di tfaqs (atau di dalam batch file yang sama) atau menggunakan kode FAQ yang sudah ada:
     *   ID FAQ tetap (misal FAQ-0001) dan ID Versi bertambah menjadi FAQ-0001-V2, FAQ-0001-V3, dst.!
     */
    public static function mapRowsToFaqPreview(array $rows, int $startFaqNumber = 1): array
    {
        if (empty($rows)) {
            return [];
        }

        // Ambil daftar FAQ yang sudah ada di database beserta jumlah versinya
        $existingFaqsByCode = [];
        $existingFaqsByQuestion = [];

        try {
            $dbFaqs = \App\Models\Faq::with('versions')->get();
            foreach ($dbFaqs as $f) {
                $maxV = 0;
                foreach ($f->versions as $v) {
                    if (preg_match('/-V(\d+)$/i', $v->id, $m)) {
                        $num = (int) $m[1];
                        if ($num > $maxV) {
                            $maxV = $num;
                        }
                    }
                }
                if ($maxV === 0) {
                    $maxV = $f->versions->count();
                }

                $codeKey = strtoupper(trim($f->id));
                $qKey = mb_strtolower(trim($f->pertanyaan));

                $existingFaqsByCode[$codeKey] = [
                    'id'    => $f->id,
                    'max_v' => $maxV,
                ];
                if ($qKey !== '') {
                    $existingFaqsByQuestion[$qKey] = $codeKey;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika DB belum siap
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
            $explicitFaqCode = null;
            if (preg_match('/^FAQ-\d+/i', $col0)) {
                $hasLeadingCodeOrNo = true;
                $explicitFaqCode = strtoupper($col0);
            } elseif (preg_match('/^\d+$/', $col0)) {
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

            // Tentukan apakah ini versi baru dari FAQ yang sudah ada atau FAQ baru
            $qKey = mb_strtolower($pertanyaan);
            $matchedCode = null;

            if ($explicitFaqCode && isset($existingFaqsByCode[$explicitFaqCode])) {
                $matchedCode = $explicitFaqCode;
            } elseif ($qKey !== '' && isset($existingFaqsByQuestion[$qKey])) {
                $matchedCode = $existingFaqsByQuestion[$qKey];
            }

            if ($matchedCode !== null) {
                $faqId = $existingFaqsByCode[$matchedCode]['id'];
                $nextV = $existingFaqsByCode[$matchedCode]['max_v'] + 1;
                $existingFaqsByCode[$matchedCode]['max_v'] = $nextV;
                $versionNum = $nextV;
            } else {
                $faqId = sprintf('FAQ-%04d', $counter++);
                $versionNum = 1;
                $codeKey = strtoupper($faqId);
                $existingFaqsByCode[$codeKey] = [
                    'id'    => $faqId,
                    'max_v' => 1,
                ];
                if ($qKey !== '') {
                    $existingFaqsByQuestion[$qKey] = $codeKey;
                }
            }

            $versionId = $faqId . '-V' . $versionNum;

            // Status 'ready' (Siap diajukan) jika Pertanyaan, Jawaban, Kata Kunci, dan Sumber Pustaka semuanya terisi
            $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && $pustakaJudul !== '');

            $items[] = [
                'row_key'        => $versionId, // Unik per baris (misal FAQ-0001-V1, FAQ-0001-V2)
                'id'             => $faqId,
                'version_id'     => $versionId,
                'version_num'    => $versionNum,
                'version'        => 'Versi ' . $versionNum,
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
        if (!is_readable($filePath)) {
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


<?php

namespace App\Http\Controllers\dataentry;

use App\Http\Controllers\Controller;
use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use App\Services\FileImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class FaqController extends Controller
{
    /**
     * Menu 1: Menampilkan dashboard data entry beserta daftar FAQ dari database (tfaqs & tfaqsversion).
     * Klasifikasi FAQ menggunakan kata kunci (ttag & tdetail_faqs).
     */
    public function dashboard(Request $request)
    {
        try {
            $query = Faq::with([
                'versions.daftarPustaka',
                'versions.validator',
                'tags',
                'dataEntry'
            ])->orderBy('created_at', 'desc');

            $allFaqs = $query->get();

            $total = $allFaqs->count();
            $draftCount = 0;
            $submittedCount = 0;
            $rejectedCount = 0;
            $approvedCount = 0;

            foreach ($allFaqs as $faq) {
                $latest = $faq->latest_version;
                $st = $latest ? strtolower($latest->status) : 'draft';

                if ($st === 'draft') {
                    $draftCount++;
                } elseif ($st === 'submitted') {
                    $submittedCount++;
                } elseif ($st === 'rejected') {
                    $rejectedCount++;
                } elseif (in_array($st, ['approved', 'active'])) {
                    $approvedCount++;
                }
            }

            $tags = Tag::orderBy('nama', 'asc')->get();
            $faqs = $allFaqs;

        } catch (Throwable $e) {
            $faqs = collect();
            $total = 0;
            $draftCount = 0;
            $submittedCount = 0;
            $rejectedCount = 0;
            $approvedCount = 0;
            $tags = collect();
        }

        return view('dataentry.dashboard', compact(
            'faqs',
            'total',
            'draftCount',
            'submittedCount',
            'rejectedCount',
            'approvedCount',
            'tags'
        ));
    }

    /**
     * Menu 2 (Tampilan 1 - Input Satuan): Menampilkan form Buat FAQ dengan generate ID otomatis (FAQ-0001, dst),
     * atau membuat versi jawaban baru (FAQ-0001-V2, dst) jika memilih FAQ yang sudah ada.
     */
    public function create(Request $request)
    {
        $newFaqId = Faq::generateNextId();
        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();
        $allFaqs = Faq::with(['versions', 'tags'])->orderBy('id', 'asc')->get();

        $selectedExistingFaq = null;
        if ($request->filled('faq_id')) {
            $selectedExistingFaq = $allFaqs->firstWhere('id', $request->query('faq_id'));
        }

        return view('dataentry.buatfaq', compact(
            'newFaqId',
            'tags',
            'daftarPustaka',
            'allFaqs',
            'selectedExistingFaq'
        ));
    }

    /**
     * Menu 2 (Tampilan 2 - Pratinjau Unggahan): Menampilkan tabel pratinjau hasil konversi file Excel/CSV/PDF.
     */
    public function pratinjau(Request $request)
    {
        $items = session('uploaded_faqs', []);
        $fileName = session('uploaded_filename', 'Belum ada file diunggah');

        // Jika pengguna mengakses langsung URL pratinjau saat sesi kosong, tampilkan contoh sesuai desain
        if (empty($items)) {
            $nextIdStr = Faq::generateNextId();
            $startNum = (int) preg_replace('/\D/', '', $nextIdStr) ?: 1;

            $items = [
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum),
                    'id'             => sprintf('FAQ-%04d', $startNum),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Bagaimana prosedur pengajuan cuti akademik?',
                    'jawaban'        => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pembimbing akademik (PA) terlebih dahulu.',
                    'tags'           => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                    'pustaka_judul'  => 'Pedoman Akademik 2026/2027',
                    'pustaka_bagian' => 'Bagian Cuti Akademik',
                    'status'         => 'ready',
                    'checked'        => true,
                ],
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum + 1),
                    'id'             => sprintf('FAQ-%04d', $startNum + 1),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum + 1),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Apa syarat mengambil mata kuliah di luar program studi?',
                    'jawaban'        => 'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota tersedia.',
                    'tags'           => [],
                    'pustaka_judul'  => 'Pedoman Akademik 2026/2027',
                    'pustaka_bagian' => 'Bagian Lintas Prodi',
                    'status'         => 'incomplete',
                    'checked'        => false,
                ],
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum + 2),
                    'id'             => sprintf('FAQ-%04d', $startNum + 2),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum + 2),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Bagaimana cara mengajukan beasiswa UBAYA?',
                    'jawaban'        => 'Pendaftaran beasiswa dibuka sesuai jadwal, pastikan melengkapi berkas seperti transkrip nilai dan surat rekomendasi.',
                    'tags'           => ['beasiswa', 'syarat', 'UBAYA'],
                    'pustaka_judul'  => 'Pedoman Akademik 2026/2027',
                    'pustaka_bagian' => 'Bagian Beasiswa',
                    'status'         => 'ready',
                    'checked'        => true,
                ],
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum + 3),
                    'id'             => sprintf('FAQ-%04d', $startNum + 3),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum + 3),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Kapan jadwal semester genap 2026/2027 dimulai?',
                    'jawaban'        => 'Semester genap 2026/2027 dimulai sesuai kalender akademik yang ditetapkan. Pantau pengumuman resmi universitas.',
                    'tags'           => ['KRS', 'jadwal', 'semester genap'],
                    'pustaka_judul'  => '',
                    'pustaka_bagian' => 'Kalender Akademik',
                    'status'         => 'incomplete',
                    'checked'        => false,
                ],
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum + 4),
                    'id'             => sprintf('FAQ-%04d', $startNum + 4),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum + 4),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Mata kuliah apa saja yang sudah bisa diambil?',
                    'jawaban'        => 'Daftar mata kuliah yang tersedia tercantum di buku panduan prodi masing-masing. Konsultasikan dengan dosen wali.',
                    'tags'           => ['lintas prodi', 'syarat', 'mata kuliah'],
                    'pustaka_judul'  => '',
                    'pustaka_bagian' => 'Bagian Kurikulum',
                    'status'         => 'incomplete',
                    'checked'        => false,
                ],
            ];
            $fileName = 'FAQ_Akademik_2026.xlsx';
            session([
                'uploaded_faqs'     => $items,
                'uploaded_filename' => $fileName,
            ]);
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('dataentry.preview', compact('items', 'fileName', 'tags', 'daftarPustaka'));
    }

    /**
     * Membaca file Excel (.xlsx), CSV (.csv), atau PDF (.pdf) yang diunggah pengguna
     * menggunakan FileImportParser dan menampilkan hasilnya di halaman Pratinjau.
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_faq' => 'required|file|max:10240',
        ], [
            'file_faq.required' => 'Silakan pilih file Excel / CSV / PDF terlebih dahulu.',
            'file_faq.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('file_faq');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'pdf'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan file .XLSX, .CSV, atau .PDF.');
        }

        // Hitung nomor urut ID FAQ berikutnya dari database
        $nextIdStr = Faq::generateNextId();
        $startNum = (int) preg_replace('/\D/', '', $nextIdStr) ?: 1;

        // Baca isi file sebenarnya menggunakan FileImportParser
        $rawRows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $faqs = FileImportParser::mapRowsToFaqPreview($rawRows, $startNum);

        if (empty($faqs)) {
            return redirect()->back()->with(
                'error',
                "File {$filename} tidak berisi baris pertanyaan & jawaban yang dapat dibaca. Pastikan mengikuti format kolom: Pertanyaan | Jawaban | Kata Kunci | Sumber Pustaka."
            );
        }

        session([
            'uploaded_filename' => $filename,
            'uploaded_faqs'     => $faqs,
        ]);

        return redirect()->route('dataentry.pratinjau')
            ->with('success', 'Berhasil membaca ' . count($faqs) . " baris FAQ dari file {$filename}!");
    }

    /**
     * Memperbarui satu baris item di dalam sesi Pratinjau Unggahan (via AJAX / Form).
     */
    public function updatePreviewItem(Request $request)
    {
        $rowId = $request->input('id');
        $pertanyaan = trim((string) $request->input('pertanyaan', ''));
        $jawaban = trim((string) $request->input('jawaban', ''));
        $tagsRaw = trim((string) $request->input('tags', ''));
        $pustakaJudul = trim((string) $request->input('pustaka_judul', ''));
        $pustakaBagian = trim((string) $request->input('pustaka_bagian', ''));

        $tags = array_values(array_filter(
            array_map('trim', preg_split('/[,;]+/', $tagsRaw)),
            fn($t) => $t !== ''
        ));

        $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && $pustakaJudul !== '');
        $status = $isReady ? 'ready' : 'incomplete';

        $items = session('uploaded_faqs', []);
        $updatedItem = null;

        foreach ($items as &$item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if ($itemKey === $rowId || $item['id'] === $rowId) {
                $item['pertanyaan']     = $pertanyaan;
                $item['jawaban']        = $jawaban;
                $item['tags']           = $tags;
                $item['pustaka_judul']  = $pustakaJudul;
                $item['pustaka_bagian'] = $pustakaBagian !== '' ? $pustakaBagian : 'Dokumen Rujukan Resmi';
                $item['status']         = $status;
                $item['checked']        = $isReady;
                $updatedItem = $item;
                break;
            }
        }
        unset($item);

        session(['uploaded_faqs' => $items]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'item'   => $updatedItem,
            ]);
        }

        return redirect()->route('dataentry.pratinjau')->with('success', "Baris {$rowId} berhasil diperbarui.");
    }

    /**
     * Menghapus satu baris dari sesi Pratinjau Unggahan.
     */
    public function deletePreviewItem(Request $request)
    {
        $rowId = $request->input('id');
        $items = session('uploaded_faqs', []);

        $items = array_values(array_filter($items, function ($item) use ($rowId) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            return $itemKey !== $rowId && $item['id'] !== $rowId;
        }));
        session(['uploaded_faqs' => $items]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('dataentry.pratinjau')->with('success', "Baris {$rowId} dihapus dari pratinjau.");
    }

    /**
     * Menyimpan sebagai Draft atau Mengajukan Validasi ke Validator dari halaman Pratinjau Unggahan.
     * Data disimpan ke tfaqs, tfaqsversion, ttag, tdetail_faqs, dan tdaftarpustaka.
     */
    public function submitPratinjau(Request $request)
    {
        $action = $request->input('action', 'submit'); // 'draft' atau 'submit'
        $selectedIds = $request->input('selected_ids', []);
        $itemsJson = $request->input('items_json');

        // Jika frontend mengirimkan state terbaru dari tabel (termasuk hasil edit inline), sinkronkan terlebih dahulu
        if (!empty($itemsJson)) {
            $decoded = json_decode($itemsJson, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (empty($items)) {
            $items = session('uploaded_faqs', []);
        }

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 FAQ menggunakan kotak centang terlebih dahulu.');
        }

        $status = ($action === 'submit') ? 'Submitted' : 'Draft';
        $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;

        try {
            DB::beginTransaction();
            $savedCount = 0;
            $remainingItems = [];

            foreach ($items as $item) {
                $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
                if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                    $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));

                    // Cek apakah FAQ ini sudah ada di tabel tfaqs (berdasarkan ID atau Pertanyaan yang sama)
                    $existingFaq = null;
                    if (!empty($item['id'])) {
                        $existingFaq = Faq::with('versions')->find($item['id']);
                    }
                    if (!$existingFaq && $pertanyaanText !== '') {
                        $existingFaq = Faq::with('versions')
                            ->whereRaw('LOWER(TRIM(pertanyaan)) = ?', [mb_strtolower($pertanyaanText)])
                            ->first();
                    }

                    if ($existingFaq) {
                        // Jika FAQ sudah ada, tetap gunakan ID FAQ tersebut (misal FAQ-0001) dan tambah versi V-nya (FAQ-0001-V2, dst.)
                        $faq = $existingFaq;
                        $faqId = $faq->id;
                        $versionId = $faq->generateNextVersionId();
                    } else {
                        // Jika FAQ baru, generate ID FAQ baru (misal FAQ-0002) dan mulai dari V1 (FAQ-0002-V1)
                        $faqId = Faq::generateNextId();
                        $faq = Faq::create([
                            'id'           => $faqId,
                            'id_dataentry' => $userId,
                            'pertanyaan'   => $pertanyaanText,
                        ]);
                        $versionId = $faqId . '-V1';
                    }

                    $pustakaId = null;
                    $pustakaJudul = trim((string) ($item['pustaka_judul'] ?? ''));
                    if ($pustakaJudul !== '' && strtolower($pustakaJudul) !== 'belum diisi') {
                        $dp = DaftarPustaka::firstOrCreate([
                            'link' => substr($pustakaJudul, 0, 45),
                        ], [
                            'type' => 'link',
                        ]);
                        $pustakaId = $dp->id;
                    }

                    FaqVersion::create([
                        'id'                 => $versionId,
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faqId,
                        'id_validator'       => null,
                        'jawaban'            => $item['jawaban'] ?? '',
                        'status'             => $status,
                        'catatan'            => null,
                    ]);

                    if (!empty($item['tags']) && is_array($item['tags'])) {
                        $tagIds = [];
                        foreach ($item['tags'] as $tagName) {
                            $clean = substr(trim((string) $tagName), 0, 100);
                            if ($clean !== '') {
                                $t = Tag::firstOrCreate(['nama' => $clean]);
                                $tagIds[] = $t->id;
                            }
                        }
                        if (!empty($tagIds)) {
                            $faq->tags()->syncWithoutDetaching(array_unique($tagIds));
                        }
                    }

                    $savedCount++;
                } else {
                    $remainingItems[] = $item;
                }
            }

            DB::commit();

            // Jika masih ada baris yang belum dipilih, biarkan di session atau bersihkan jika semua sudah diproses
            if (empty($remainingItems)) {
                session()->forget(['uploaded_faqs', 'uploaded_filename']);
            } else {
                session(['uploaded_faqs' => $remainingItems]);
            }

            $msg = strtolower($status) === 'submitted'
                ? "{$savedCount} FAQ berhasil diajukan ke Validator untuk diperiksa!"
                : "{$savedCount} FAQ berhasil disimpan sebagai Draft!";

            return redirect()->route('dataentry.dashboard')->with('success', $msg);

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan FAQ dari pratinjau: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Template Excel (.xlsx) asli untuk Import FAQ (setiap kolom terpisah di sel masing-masing).
     */
    public function downloadTemplateFaq()
    {
        $headers = ['Pertanyaan', 'Jawaban', 'Kata Kunci', 'Sumber Pustaka', 'Bagian Sumber'];
        $rows = [
            [
                'Bagaimana prosedur pengajuan cuti akademik?',
                'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender akademik dan disetujui Dosen Wali.',
                'cuti akademik, pengajuan, mahasiswa S1, Akademik',
                'Pedoman Akademik 2026/2027',
                'Bagian Cuti Akademik',
            ],
            [
                'Bagaimana cara mengajukan beasiswa UBAYA?',
                'Pendaftaran beasiswa dibuka sesuai jadwal kemahasiswaan dengan melampirkan transkrip nilai dan surat rekomendasi.',
                'beasiswa, syarat, UBAYA',
                'Pedoman Kemahasiswaan 2026',
                'Bagian Beasiswa',
            ],
            [
                'Apa syarat mengambil mata kuliah lintas prodi?',
                'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota kelas tersedia.',
                'lintas prodi, syarat, mata kuliah',
                'Pedoman Akademik 2026/2027',
                'Bagian Lintas Prodi',
            ],
        ];

        $filePath = FileImportParser::generateXlsx($headers, $rows, 'Template FAQ');

        return response()->download(
            $filePath,
            'Template_Import_FAQ_KMS.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Menyimpan FAQ baru (FAQ-XXXX-V1) ATAU menambah versi jawaban baru untuk FAQ yang dipilih (FAQ-XXXX-V2, dst.).
     */
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'      => 'required|string',
            'jawaban'         => 'required|string',
            'status'          => 'nullable|in:draft,submitted',
            'tags'            => 'nullable|string',
            'pustaka_id'      => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'     => 'nullable|in:link,upload',
            'sumber_link'     => 'nullable|string|max:45',
            'sumber_file'     => 'nullable|file|max:10240',
            'mode_faq'        => 'nullable|in:new,existing',
            'existing_faq_id' => 'nullable|string|exists:tfaqs,id',
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan link maksimal 45 karakter.',
            'sumber_file.max'     => 'Ukuran file dokumen sumber maksimal 10MB.',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;
            $isExistingMode = ($request->input('mode_faq') === 'existing' && $request->filled('existing_faq_id'));

            if ($isExistingMode) {
                // Mode Tambah Versi Jawaban Baru pada FAQ yang sudah ada: ID FAQ tetap (misal FAQ-0001), ID Versi naik (FAQ-0001-V2)
                $faq = Faq::with('versions')->findOrFail($request->existing_faq_id);
                $faqId = $faq->id;
                $versionId = $faq->generateNextVersionId();
            } else {
                // Mode Buat FAQ Baru: ID FAQ naik (FAQ-0001 -> FAQ-0002), ID Versi mulai dari V1 (FAQ-0002-V1)
                $faqId = Faq::generateNextId();
                $faq = Faq::create([
                    'id'           => $faqId,
                    'id_dataentry' => $userId,
                    'pertanyaan'   => $request->pertanyaan,
                ]);
                $versionId = $faqId . '-V1';
            }

            $pustakaId = null;

            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->hasFile('sumber_file')) {
                $file = $request->file('sumber_file');
                $filename = substr($file->getClientOriginalName(), 0, 45);
                $fileContent = file_get_contents($file->getRealPath());

                $pustaka = DaftarPustaka::create([
                    'type'   => 'upload',
                    'link'   => $filename,
                    'upload' => $fileContent,
                ]);
                $pustakaId = $pustaka->id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 45);

                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'type'   => 'link',
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $rawStatus = strtolower($request->input('status', 'draft'));
            $status = ($rawStatus === 'submitted') ? 'Submitted' : 'Draft';

            FaqVersion::create([
                'id'                 => $versionId,
                'tDaftarPustakas_id' => $pustakaId,
                'tFaqs_id'           => $faqId,
                'id_validator'       => null,
                'jawaban'            => $request->jawaban,
                'status'             => $status,
                'catatan'            => null,
            ]);

            $tagIds = [];
            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                foreach ($rawTags as $tagText) {
                    $cleanTag = substr(trim($tagText), 0, 100);
                    if ($cleanTag !== '') {
                        $t = Tag::firstOrCreate(['nama' => $cleanTag]);
                        $tagIds[] = $t->id;
                    }
                }
            }

            if (!empty($tagIds)) {
                $faq->tags()->syncWithoutDetaching(array_unique($tagIds));
            }

            DB::commit();

            $statusText = strtolower($status) === 'submitted' ? 'diajukan ke Validator untuk validasi' : 'disimpan sebagai draft';
            return redirect()->route('dataentry.dashboard')
                ->with('success', "Versi {$versionId} berhasil {$statusText}!");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail FAQ (JSON untuk modal lihat detail).
     */
    public function show($id, Request $request)
    {
        $faq = Faq::with([
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry'
        ])->findOrFail($id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'             => $faq->id,
                    'pertanyaan'     => $faq->pertanyaan,
                    'created_at'     => $faq->created_at?->translatedFormat('d M Y H:i'),
                    'data_entry'     => $faq->dataEntry?->nama,
                    'tags'           => $faq->tags->pluck('nama'),
                    'latest_version' => $faq->latest_version,
                    'versions'       => $faq->versions->map(function ($v) {
                        return [
                            'id'             => $v->id,
                            'status'         => $v->status,
                            'jawaban'        => $v->jawaban,
                            'catatan'        => $v->catatan,
                            'validator'      => $v->validator?->nama,
                            'daftar_pustaka' => $v->daftarPustaka ? [
                                'id'   => $v->daftarPustaka->id,
                                'type' => $v->daftarPustaka->type,
                                'link' => $v->daftarPustaka->link,
                            ] : null,
                            'created_at'     => $v->created_at?->translatedFormat('d M Y H:i'),
                            'updated_at'     => $v->updated_at?->translatedFormat('d M Y H:i'),
                        ];
                    }),
                ],
            ]);
        }

        return redirect()->route('dataentry.dashboard');
    }

    /**
     * Menampilkan form edit FAQ atau tambah versi baru (FAQ-XXXX-V2, dst.).
     * Dikunci hanya ketika sedang ditinjau validator ('submitted').
     */
    public function edit(Request $request, $id)
    {
        $faq = Faq::with(['versions.daftarPustaka', 'tags'])->findOrFail($id);
        $latest = $faq->latest_version;
        $st = $latest ? strtolower($latest->status) : 'draft';

        if ($st === 'submitted') {
            return redirect()->route('dataentry.dashboard')
                ->with('error', "FAQ {$faq->id} sedang ditinjau validator dan tidak dapat diubah.");
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();
        $allFaqs = Faq::with(['versions', 'tags'])->orderBy('id', 'asc')->get();
        $forceNewVersion = $request->boolean('new_version') || in_array($st, ['rejected', 'approved', 'active']);

        return view('dataentry.buatfaq', [
            'faq'             => $faq,
            'isEdit'          => true,
            'forceNewVersion' => $forceNewVersion,
            'newFaqId'        => $faq->id,
            'nextVersionId'   => $faq->generateNextVersionId(),
            'latestVersion'   => $latest,
            'tags'            => $tags,
            'daftarPustaka'   => $daftarPustaka,
            'allFaqs'         => $allFaqs,
        ]);
    }

    /**
     * Memperbarui FAQ di database atau membuat versi jawaban baru (misal FAQ-0001-V2).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pertanyaan'     => 'required|string',
            'jawaban'        => 'required|string',
            'status'         => 'nullable|in:draft,submitted',
            'version_action' => 'nullable|in:update_draft,new_version',
            'tags'           => 'nullable|string',
            'pustaka_id'     => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'    => 'nullable|in:link,upload',
            'sumber_link'    => 'nullable|string|max:45',
            'sumber_file'    => 'nullable|file|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $currentStatus = $latest ? strtolower($latest->status) : 'draft';

            if ($currentStatus === 'submitted') {
                DB::rollBack();
                return redirect()->route('dataentry.dashboard')
                    ->with('error', "FAQ {$faq->id} sedang ditinjau validator dan tidak dapat diubah.");
            }

            $faq->update([
                'pertanyaan' => $request->pertanyaan,
            ]);

            $pustakaId = $latest ? $latest->tDaftarPustakas_id : null;

            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->hasFile('sumber_file')) {
                $file = $request->file('sumber_file');
                $filename = substr($file->getClientOriginalName(), 0, 45);
                $fileContent = file_get_contents($file->getRealPath());

                $pustaka = DaftarPustaka::create([
                    'type'   => 'upload',
                    'link'   => $filename,
                    'upload' => $fileContent,
                ]);
                $pustakaId = $pustaka->id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 45);

                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'type'   => 'link',
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $rawTargetStatus = strtolower($request->input('status', 'draft'));
            $targetStatus = ($rawTargetStatus === 'submitted') ? 'Submitted' : 'Draft';
            $versionAction = $request->input('version_action', 'new_version');

            // Buat versi baru (FAQ-0001-V2, V3, dst.) jika dipilih new_version atau jika status sebelumnya bukan draft
            $shouldCreateNewVersion = ($versionAction === 'new_version') || in_array($currentStatus, ['rejected', 'approved', 'active']);

            if ($shouldCreateNewVersion) {
                $savedVersionId = $faq->generateNextVersionId();

                FaqVersion::create([
                    'id'                 => $savedVersionId,
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faq->id,
                    'id_validator'       => null,
                    'jawaban'            => $request->jawaban,
                    'status'             => $targetStatus,
                    'catatan'            => null,
                ]);
            } else {
                if ($latest) {
                    $savedVersionId = $latest->id;
                    $latest->update([
                        'tDaftarPustakas_id' => $pustakaId,
                        'jawaban'            => $request->jawaban,
                        'status'             => $targetStatus,
                    ]);
                } else {
                    $savedVersionId = $faq->id . '-V1';
                    FaqVersion::create([
                        'id'                 => $savedVersionId,
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faq->id,
                        'id_validator'       => null,
                        'jawaban'            => $request->jawaban,
                        'status'             => $targetStatus,
                        'catatan'            => null,
                    ]);
                }
            }

            $tagIds = [];
            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                foreach ($rawTags as $tagText) {
                    $cleanTag = substr(trim($tagText), 0, 100);
                    if ($cleanTag !== '') {
                        $t = Tag::firstOrCreate(['nama' => $cleanTag]);
                        $tagIds[] = $t->id;
                    }
                }
            }

            $faq->tags()->sync(array_unique($tagIds));

            DB::commit();

            $statusText = strtolower($targetStatus) === 'submitted' ? 'diajukan untuk validasi' : 'disimpan sebagai draft';
            return redirect()->route('dataentry.dashboard')
                ->with('success', "Versi {$savedVersionId} berhasil diperbarui dan {$statusText}!");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus FAQ dan versinya dari database.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::with('latestVersion')->findOrFail($id);
            $latest = $faq->latest_version;
            $st = $latest ? strtolower($latest->status) : 'draft';

            if ($st === 'submitted') {
                DB::rollBack();
                return redirect()->route('dataentry.dashboard')
                    ->with('error', "FAQ {$id} sedang dalam proses validasi dan tidak dapat dihapus.");
            }

            if ($st === 'approved') {
                DB::rollBack();
                return redirect()->route('dataentry.dashboard')
                    ->with('error', "FAQ {$id} telah terbit dan tidak dapat dihapus oleh Data Entry.");
            }

            $faq->tags()->detach();
            $faq->versions()->delete();
            $faq->delete();

            DB::commit();

            return redirect()->route('dataentry.dashboard')
                ->with('success', "FAQ {$id} berhasil dihapus.");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('dataentry.dashboard')
                ->with('error', 'Gagal menghapus FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Live Check duplikasi pertanyaan FAQ.
     */
    public function checkDuplicate(Request $request)
    {
        $keyword = trim($request->query('q', $request->input('pertanyaan', '')));

        if (mb_strlen($keyword) < 3) {
            return response()->json(['duplicates' => []]);
        }

        $duplicates = Faq::where('pertanyaan', 'LIKE', '%' . $keyword . '%')
            ->with(['latestApprovedVersion'])
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'pertanyaan'  => $item->pertanyaan,
                    'is_approved' => $item->latestApprovedVersion ? true : false,
                ];
            });

        return response()->json(['duplicates' => $duplicates]);
    }

    /* =========================================================================
     * MENU 3: DAFTAR KATA KUNCI (TABEL ttag) & IMPORT FILE KATA KUNCI
     * ========================================================================= */

    public function kataKunciIndex()
    {
        $tags = Tag::withCount('faqs')->orderBy('nama', 'asc')->get();
        $previewKeywords = session('uploaded_keywords', []);
        $previewKeywordsFile = session('uploaded_keywords_file', null);

        return view('dataentry.katakunci', compact('tags', 'previewKeywords', 'previewKeywordsFile'));
    }

    public function kataKunciStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Nama kata kunci wajib diisi.',
        ]);

        $parts = preg_split('/[,;]+/', $request->nama);
        $added = 0;

        foreach ($parts as $p) {
            $clean = substr(trim($p), 0, 100);
            if ($clean !== '') {
                Tag::firstOrCreate(['nama' => $clean]);
                $added++;
            }
        }

        return redirect()->route('dataentry.katakunci')
            ->with('success', "{$added} kata kunci berhasil disimpan ke database!");
    }

    public function kataKunciUpdate(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $tag = Tag::findOrFail($id);
        $tag->update(['nama' => trim($request->nama)]);

        return redirect()->route('dataentry.katakunci')
            ->with('success', "Kata kunci berhasil diperbarui menjadi '{$tag->nama}'.");
    }

    public function kataKunciDestroy($id)
    {
        $tag = Tag::findOrFail($id);
        $nama = $tag->nama;
        $tag->faqs()->detach();
        $tag->delete();

        return redirect()->route('dataentry.katakunci')
            ->with('success', "Kata kunci '{$nama}' berhasil dihapus.");
    }

    public function kataKunciUpload(Request $request)
    {
        $request->validate([
            'file_katakunci' => 'required|file|max:10240',
        ], [
            'file_katakunci.required' => 'Pilih file Excel / CSV / PDF kata kunci terlebih dahulu.',
        ]);

        $file = $request->file('file_katakunci');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $keywords = FileImportParser::mapRowsToKeywords($rows);

        if (empty($keywords)) {
            return redirect()->route('dataentry.katakunci')
                ->with('error', "Tidak ditemukan kata kunci yang dapat dibaca dari file {$filename}.");
        }

        session([
            'uploaded_keywords'      => $keywords,
            'uploaded_keywords_file' => $filename,
        ]);

        return redirect()->route('dataentry.katakunci')
            ->with('success', 'Berhasil membaca ' . count($keywords) . " kata kunci dari {$filename}. Silakan tinjau pratinjau di bawah sebelum disimpan!");
    }

    public function kataKunciSaveImport(Request $request)
    {
        $selectedKeywords = $request->input('keywords', []);
        if (empty($selectedKeywords)) {
            $selectedKeywords = session('uploaded_keywords', []);
        }

        $saved = 0;
        foreach ($selectedKeywords as $kw) {
            $clean = substr(trim((string) $kw), 0, 100);
            if ($clean !== '') {
                Tag::firstOrCreate(['nama' => $clean]);
                $saved++;
            }
        }

        session()->forget(['uploaded_keywords', 'uploaded_keywords_file']);

        return redirect()->route('dataentry.katakunci')
            ->with('success', "{$saved} kata kunci dari file import berhasil disimpan ke tabel ttag!");
    }

    public function kataKunciCancelImport()
    {
        session()->forget(['uploaded_keywords', 'uploaded_keywords_file']);
        return redirect()->route('dataentry.katakunci')->with('success', 'Pratinjau import kata kunci dibatalkan.');
    }

    public function downloadTemplateKataKunci()
    {
        $headers = ['No', 'Kata Kunci'];
        $rows = [
            ['1', 'cuti akademik'],
            ['2', 'beasiswa'],
            ['3', 'KRS'],
            ['4', 'transkrip nilai'],
            ['5', 'wisuda'],
        ];

        $filePath = FileImportParser::generateXlsx($headers, $rows, 'Template Kata Kunci');

        return response()->download(
            $filePath,
            'Template_Import_Kata_Kunci.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /* =========================================================================
     * MENU 4: SUMBER PUSTAKA (TABEL tdaftarpustaka) & IMPORT FILE PUSTAKA
     * ========================================================================= */

    public function sumberPustakaIndex()
    {
        $pustakas = DaftarPustaka::withCount('faqVersions')->orderBy('id', 'desc')->get();
        $previewSources = session('uploaded_sources', []);
        $previewSourcesFile = session('uploaded_sources_file', null);

        return view('dataentry.sumberpustaka', compact('pustakas', 'previewSources', 'previewSourcesFile'));
    }

    public function sumberPustakaStore(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:link,upload',
            'link'        => 'nullable|string|max:45',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'link.max' => 'Judul / Link sumber pustaka maksimal 45 karakter sesuai kolom database.',
        ]);

        if ($request->type === 'upload' && $request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $title = $request->filled('link')
                ? substr(trim($request->link), 0, 45)
                : substr($file->getClientOriginalName(), 0, 45);

            DaftarPustaka::create([
                'type'   => 'upload',
                'link'   => $title,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route('dataentry.sumberpustaka')
                ->with('success', "Dokumen '{$title}' berhasil diunggah ke Sumber Pustaka!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Judul / tautan sumber pustaka wajib diisi.');
        }

        $linkText = substr(trim($request->link), 0, 45);
        DaftarPustaka::create([
            'type'   => 'link',
            'link'   => $linkText,
            'upload' => null,
        ]);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "Sumber pustaka '{$linkText}' berhasil ditambahkan!");
    }

    public function sumberPustakaUpload(Request $request)
    {
        $request->validate([
            'file_pustaka' => 'required|file|max:10240',
        ]);

        $file = $request->file('file_pustaka');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $sources = FileImportParser::mapRowsToSumberPustaka($rows);

        if (empty($sources)) {
            return redirect()->route('dataentry.sumberpustaka')
                ->with('error', "Tidak ditemukan daftar sumber pustaka yang dapat dibaca dari file {$filename}.");
        }

        session([
            'uploaded_sources'      => $sources,
            'uploaded_sources_file' => $filename,
        ]);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', 'Berhasil membaca ' . count($sources) . " sumber pustaka dari {$filename}. Silakan tinjau pratinjau di bawah sebelum disimpan!");
    }

    public function sumberPustakaSaveImport(Request $request)
    {
        $selectedIndexes = $request->input('selected_indexes', []);
        $sources = session('uploaded_sources', []);

        $saved = 0;
        foreach ($sources as $idx => $src) {
            if (empty($selectedIndexes) || in_array((string) $idx, $selectedIndexes, true)) {
                $linkName = substr(trim($src['link'] ?? ''), 0, 45);
                if ($linkName !== '') {
                    DaftarPustaka::firstOrCreate([
                        'link' => $linkName,
                    ], [
                        'type'   => $src['type'] ?? 'link',
                        'upload' => null,
                    ]);
                    $saved++;
                }
            }
        }

        session()->forget(['uploaded_sources', 'uploaded_sources_file']);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "{$saved} sumber pustaka berhasil disimpan ke tabel tdaftarpustaka!");
    }

    public function sumberPustakaCancelImport()
    {
        session()->forget(['uploaded_sources', 'uploaded_sources_file']);
        return redirect()->route('dataentry.sumberpustaka')->with('success', 'Pratinjau import sumber pustaka dibatalkan.');
    }

    public function sumberPustakaDestroy($id)
    {
        $pustaka = DaftarPustaka::withCount('faqVersions')->findOrFail($id);
        if ($pustaka->faq_versions_count > 0) {
            return redirect()->route('dataentry.sumberpustaka')
                ->with('error', "Sumber pustaka '{$pustaka->link}' sedang digunakan oleh {$pustaka->faq_versions_count} versi FAQ dan tidak dapat dihapus.");
        }

        $nama = $pustaka->link;
        $pustaka->delete();

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "Sumber pustaka '{$nama}' berhasil dihapus.");
    }

    public function downloadTemplatePustaka()
    {
        $headers = ['Tipe', 'Judul atau Link Sumber Pustaka'];
        $rows = [
            ['link', 'Pedoman Akademik 2026/2027'],
            ['link', 'SK Rektor No. 12/2026'],
            ['link', 'Panduan KRS & Perwalian UBAYA'],
        ];

        $filePath = FileImportParser::generateXlsx($headers, $rows, 'Template Sumber Pustaka');

        return response()->download(
            $filePath,
            'Template_Import_Sumber_Pustaka.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Mengunduh file dokumen sumber pustaka dari tabel tdaftarpustaka (upload blob).
     */
    public function downloadPustaka($id)
    {
        $pustaka = DaftarPustaka::findOrFail($id);

        if ($pustaka->type === 'upload' && !empty($pustaka->upload)) {
            $filename = $pustaka->link ?: 'dokumen-sumber-' . $pustaka->id . '.pdf';

            return response($pustaka->upload)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        if ($pustaka->type === 'link' && !empty($pustaka->link)) {
            if ( str_starts_with($pustaka->link, 'http://') || str_starts_with($pustaka->link, 'https://') ) {
                return redirect()->away($pustaka->link);
            }
            return redirect()->back()->with('success', "Sumber Rujukan: {$pustaka->link}");
        }

        return redirect()->back()->with('error', 'Dokumen pustaka tidak ditemukan.');
    }
}

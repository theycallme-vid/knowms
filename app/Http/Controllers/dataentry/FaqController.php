<?php

namespace App\Http\Controllers\dataentry;

use App\Http\Controllers\Controller;
use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use App\Services\FaqSimilarityService;
use App\Services\FileImportParser;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class FaqController extends Controller
{
    /**
     * Menu 1: Menampilkan Daftar FAQ Data Entry (disamakan dengan tampilan Daftar FAQ Validator,
     * kecuali Data Entry hanya view-only pada status Active/InActive).
     */
    public function daftarFAQ(Request $request)
    {
        $kataKunciOptions = collect();

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $kataKunciOptions = Tag::orderBy('nama', 'asc')->pluck('nama');
            $selectedKataKunci = trim((string) $request->input('kata_kunci', $request->input('fakultas', 'Semua')));
            if ($selectedKataKunci === '') {
                $selectedKataKunci = 'Semua';
            }

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->where('status', '!=', 'Draf')->latest('updated_at'),
                'versions.daftarPustaka',
                'versions.validator',
            ])->whereHas('versions', fn ($q) => $q->where('status', '!=', 'Draf'));

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhere('pertanyaan', 'like', '%' . $search . '%')
                      ->orWhereHas('tags', fn ($tq) => $tq->where('nama', 'like', '%' . $search . '%'));
                });
            }

            if ($selectedKataKunci !== 'Semua') {
                $query->whereHas('tags', fn ($q) => $q->where('nama', $selectedKataKunci));
            }

            if ($request->filled('status') && $request->status !== 'Semua') {
                $statusFilter = match (strtolower(trim((string) $request->status))) {
                    'submitted' => 'Menunggu Validasi',
                    'approved', 'disetujui' => 'Diterima',
                    'rejected'  => 'Ditolak',
                    'active'    => 'Terbit',
                    default     => $request->status,
                };
                $query->whereHas('versions', fn ($q) => $q->where('status', $statusFilter)->where('status', '!=', 'Draf'));
            }

            $sort = $request->get('sort', 'terbaru');
            match ($sort) {
                'terlama' => $query->oldest(),
                'az'      => $query->orderBy('pertanyaan'),
                default   => $query->latest(),
            };

            $faqs = $query->paginate(6)->withQueryString();

            $statBase = Faq::query()->whereHas('versions', fn ($q) => $q->where('status', '!=', 'Draf'));
            if ($selectedKataKunci !== 'Semua') {
                $statBase->whereHas('tags', fn ($q) => $q->where('nama', $selectedKataKunci));
            }
            $faqIds = $statBase->pluck('id');

            $totalFaq       = $faqIds->count();
            $totalAktif     = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Terbit')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalApproved  = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Diterima')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalSubmitted = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Menunggu Validasi')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalRejected  = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Ditolak')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalInaktif   = 0;
            $totalDraft     = 0;

            $total          = $totalFaq;
            $publishedCount = $totalAktif;
            $approvedCount  = $totalApproved;
            $submittedCount = $totalSubmitted;
            $rejectedCount  = $totalRejected;
            $draftCount     = $totalDraft;

            $tags = Tag::orderBy('nama', 'asc')->get();
        } catch (Throwable $e) {
            $faqs           = new LengthAwarePaginator([], 0, 6);
            $totalFaq       = 0;
            $totalAktif     = 0;
            $totalApproved  = 0;
            $totalSubmitted = 0;
            $totalRejected  = 0;
            $totalInaktif   = 0;
            $totalDraft     = 0;
            $total          = 0;
            $publishedCount = 0;
            $approvedCount  = 0;
            $submittedCount = 0;
            $rejectedCount  = 0;
            $draftCount     = 0;
            $tags           = collect();
        }

        return view('dataentry.daftarFAQ', compact(
            'faqs',
            'kataKunciOptions',
            'totalFaq',
            'totalAktif',
            'totalApproved',
            'totalSubmitted',
            'totalRejected',
            'totalInaktif',
            'totalDraft',
            'total',
            'draftCount',
            'submittedCount',
            'rejectedCount',
            'approvedCount',
            'publishedCount',
            'tags'
        ));
    }

    /**
     * Menu Pengajuan FAQ: Menampilkan daftar FAQ yang dapat diajukan oleh Data Entry (referensi Antrean Validasi + checklist).
     */
    public function pengajuanFaq(Request $request)
    {
        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $totalDraft = Faq::whereHas('versions', function ($q) {
                $q->where('status', 'Draf');
            })->count();

            $totalRejected = Faq::whereHas('versions', function ($q) {
                $q->where('status', 'Ditolak');
            })->count();

            $totalSubmitted = Faq::whereHas('versions', function ($q) {
                $q->where('status', 'Menunggu Validasi');
            })->count();

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('updated_at'),
                'versions.daftarPustaka',
                'versions.validator',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Draf', 'Ditolak', 'Menunggu Validasi']);
            });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhere('pertanyaan', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $stFilter = match (strtolower(trim((string) $request->status))) {
                    'draft'     => 'Draf',
                    'rejected'  => 'Ditolak',
                    'submitted' => 'Menunggu Validasi',
                    default     => $request->status,
                };
                $query->whereHas('versions', fn ($q) => $q->where('status', $stFilter));
            }

            $sort = $request->get('sort', 'terbaru');
            if ($sort === 'terlama') {
                $query->oldest('created_at');
            } else {
                $query->latest('created_at');
            }

            $faqs = $query->paginate(6)->withQueryString();
        } catch (Throwable $e) {
            $faqs           = new LengthAwarePaginator([], 0, 6);
            $totalDraft     = 0;
            $totalRejected  = 0;
            $totalSubmitted = 0;
            $sort           = 'terbaru';
        }

        return view('dataentry.pengajuan', compact(
            'faqs',
            'totalDraft',
            'totalRejected',
            'totalSubmitted',
            'sort'
        ));
    }

    /**
     * Mengajukan satu FAQ (Draft / Ditolak) menjadi Menunggu Validasi ('Submitted').
     */
    public function submitSingleFaq(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $faq = Faq::with(['versions', 'tags'])->findOrFail($id);
            $latest = $faq->latest_version;

            $hasTags = $faq->tags && $faq->tags->isNotEmpty();
            $hasSource = $latest && !empty($latest->tDaftarPustakas_id);

            if (!$hasTags || !$hasSource) {
                DB::rollBack();
                $missing = [];
                if (!$hasTags) {
                    $missing[] = 'Kata Kunci';
                }
                if (!$hasSource) {
                    $missing[] = 'Sumber Pustaka';
                }
                $msg = "Peringatan: FAQ {$id} belum memiliki " . implode(' dan ', $missing) . ". Silakan lengkapi terlebih dahulu melalui tombol Edit.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            if ($latest) {
                $latest->update([
                    'status'  => 'Menunggu Validasi',
                    'catatan' => null,
                ]);
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "FAQ {$id} berhasil diajukan ke Validator.",
                ]);
            }

            return redirect()->back()->with('success', "FAQ {$id} berhasil diajukan ke Validator!");
        } catch (Throwable $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengajukan FAQ: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengajukan FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Mengajukan banyak FAQ sekaligus (checklist) menjadi Menunggu Validasi ('Submitted').
     */
    public function bulkSubmitFaq(Request $request)
    {
        $ids = array_filter((array) $request->input('faq_ids', []));
        if (empty($ids)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Pilih minimal 1 FAQ terlebih dahulu.'], 422);
            }
            return redirect()->back()->with('error', 'Pilih minimal 1 FAQ menggunakan checklist terlebih dahulu.');
        }

        try {
            DB::beginTransaction();
            $faqs = Faq::with(['versions', 'tags'])->whereIn('id', $ids)->get();
            $count = 0;
            $incompleteIds = [];

            foreach ($faqs as $faq) {
                $latest = $faq->latest_version;
                $hasTags = $faq->tags && $faq->tags->isNotEmpty();
                $hasSource = $latest && !empty($latest->tDaftarPustakas_id);

                if (!$hasTags || !$hasSource) {
                    $incompleteIds[] = $faq->id;
                }
            }

            if (!empty($incompleteIds)) {
                DB::rollBack();
                $listStr = implode(', ', $incompleteIds);
                $msg = "Peringatan: FAQ ({$listStr}) belum memiliki Kata Kunci atau Sumber Pustaka. Silakan lengkapi terlebih dahulu sebelum diajukan.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            foreach ($faqs as $faq) {
                $latest = $faq->latest_version;
                if ($latest && in_array(strtolower((string) $latest->status), ['draf', 'draft', 'ditolak', 'rejected'], true)) {
                    $latest->update([
                        'status'  => 'Menunggu Validasi',
                        'catatan' => null,
                    ]);
                    $count++;
                }
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'count'   => $count,
                    'message' => "{$count} FAQ berhasil diajukan ke Validator.",
                ]);
            }

            return redirect()->back()->with('success', "{$count} FAQ berhasil diajukan ke Validator!");
        } catch (Throwable $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengajukan FAQ massal: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengajukan FAQ massal: ' . $e->getMessage());
        }
    }

    /**
     * Menu 2 (Tampilan 1 - Input Satuan): Menampilkan form Buat FAQ dengan generate ID otomatis (FAQ-0001, dst),
     * atau membuat versi jawaban baru (FAQ-0001-V2, dst) jika memilih FAQ yang sudah ada.
     */
    public function create(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

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
                "File {$filename} tidak berisi baris pertanyaan & jawaban yang dapat dibaca. Pastikan mengikuti format kolom: Kolom A: Pertanyaan | Kolom B: Jawaban."
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

        $similarityService = new FaqSimilarityService();
        $analysis = $similarityService->analyzeQuestion($pertanyaan, null, 50.0, $jawaban);
        $hasExactDraftMatch = (bool) ($analysis['has_exact_draft_match'] ?? false);

        $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && $pustakaJudul !== '' && !$hasExactDraftMatch);
        $status = $isReady ? 'ready' : 'incomplete';

        $items = session('uploaded_faqs', []);
        $updatedItem = null;

        foreach ($items as &$item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if ($itemKey === $rowId || $item['id'] === $rowId) {
                $item['pertanyaan']            = $pertanyaan;
                $item['jawaban']               = $jawaban;
                $item['tags']                  = $tags;
                $item['pustaka_judul']         = $pustakaJudul;
                $item['pustaka_bagian']        = $pustakaBagian !== '' ? $pustakaBagian : 'Dokumen Rujukan Resmi';
                $item['status']                = $status;
                $item['checked']               = $isReady && !($analysis['has_exact_match'] ?? false);
                $item['has_exact_match']       = (bool) ($analysis['has_exact_match'] ?? false);
                $item['exact_match']           = $analysis['exact_match'] ?? null;
                $item['has_exact_draft_match'] = $hasExactDraftMatch;
                $item['exact_draft_match']     = $analysis['exact_draft_match'] ?? null;
                $item['similar_candidates']    = $analysis['candidates'] ?? [];
                $item['max_similarity']        = $analysis['max_similarity'] ?? 0.0;
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

        $status = ($action === 'submit') ? 'Menunggu Validasi' : 'Draf';
        $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;
        $similarityService = new FaqSimilarityService();

        // Validasi pra-simpan:
        // 1. Cek apakah ada baris terpilih yang sama persis Pertanyaan & Jawabannya dengan Draf yang sudah ada
        // 2. Jika action = 'submit' (Ajukan Validasi), pastikan Kata Kunci & Sumber Pustaka sudah diisi
        $missingTagsOrSource = [];
        $exactDraftConflicts = [];

        foreach ($items as $item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));
                $jawabanText = trim((string) ($item['jawaban'] ?? ''));
                $itemTags = is_array($item['tags'] ?? null) ? array_filter($item['tags'], fn($t) => trim((string) $t) !== '') : [];
                $pustakaJudul = trim((string) ($item['pustaka_judul'] ?? ''));
                $hasValidSource = ($pustakaJudul !== '' && strtolower($pustakaJudul) !== 'belum diisi');

                $draftMatch = $similarityService->findExactDraftMatch($pertanyaanText, $jawabanText);
                if ($draftMatch !== null) {
                    $exactDraftConflicts[] = ($item['id'] ?? $itemKey) . " (sama persis dengan Draf {$draftMatch['id']} / {$draftMatch['version_id']})";
                }

                if ($status === 'Menunggu Validasi' && (empty($itemTags) || !$hasValidSource)) {
                    $missingTagsOrSource[] = $item['id'] ?? $itemKey;
                }
            }
        }

        if (!empty($exactDraftConflicts)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: Tidak dapat melanjutkan karena Pertanyaan dan Jawaban sama persis dengan FAQ berstatus Draf yang sudah ada: ' . implode(', ', $exactDraftConflicts) . '.'
            );
        }

        if ($status === 'Menunggu Validasi' && !empty($missingTagsOrSource)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: FAQ (' . implode(', ', $missingTagsOrSource) . ') belum memiliki Kata Kunci atau Sumber Pustaka. Silakan lengkapi melalui tombol Ubah sebelum mengajukan validasi.'
            );
        }

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();
            $savedCount = 0;
            $remainingItems = [];

            foreach ($items as $item) {
                $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
                if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                    $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));

                    // Pastikan ID FAQ hasil import selalu urutan terbaru & tidak pernah duplikat dengan yang sudah ada
                    $candidateId = trim((string) ($item['id'] ?? ''));
                    if ($candidateId === '' || Faq::where('id', $candidateId)->exists()) {
                        $faqId = Faq::generateNextId();
                    } else {
                        $faqId = $candidateId;
                    }

                    $faq = Faq::create([
                        'id'           => $faqId,
                        'id_dataentry' => $userId,
                        'pertanyaan'   => $pertanyaanText,
                    ]);
                    $versionId = $faqId . '-V1';

                    $pustakaId = null;
                    $pustakaJudul = trim((string) ($item['pustaka_judul'] ?? ''));
                    if ($pustakaJudul !== '' && strtolower($pustakaJudul) !== 'belum diisi') {
                        // Cari sumber pustaka yang sudah terdaftar berdasarkan judul atau link
                        $dp = DaftarPustaka::where('judul', $pustakaJudul)
                            ->orWhere('link', $pustakaJudul)
                            ->first();
                        if (!$dp) {
                            $dp = DaftarPustaka::firstOrCreate([
                                'link' => substr($pustakaJudul, 0, 45),
                            ], [
                                'judul' => substr($pustakaJudul, 0, 100),
                                'type'  => 'public',
                            ]);
                        }
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

            $isSubmitted = ($status === 'Menunggu Validasi');
            $msg = $isSubmitted
                ? "{$savedCount} FAQ berhasil diajukan ke Validator untuk diperiksa!"
                : "{$savedCount} FAQ berhasil disimpan sebagai Draf di Pengajuan FAQ!";

            return redirect()->route($isSubmitted ? 'dataentry.daftarFAQ' : 'dataentry.pengajuan')->with('success', $msg);

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan FAQ dari pratinjau: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Template Excel (.xlsx) asli untuk Unggah File FAQ (Kolom A: Pertanyaan, Kolom B: Jawaban).
     */
    public function downloadTemplateFaq()
    {
        $headers = ['Pertanyaan', 'Jawaban'];
        $rows = [
            [
                'Bagaimana prosedur pengajuan cuti akademik?',
                'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender akademik dan disetujui Dosen Wali.',
            ],
            [
                'Bagaimana cara mengajukan beasiswa UBAYA?',
                'Pendaftaran beasiswa dibuka sesuai jadwal kemahasiswaan dengan melampirkan transkrip nilai dan surat rekomendasi.',
            ],
            [
                'Apa syarat mengambil mata kuliah lintas prodi?',
                'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota kelas tersedia.',
            ],
        ];

        $filePath = FileImportParser::generateXlsx($headers, $rows, 'Template FAQ');

        return response()->download(
            $filePath,
            'Template_Unggah_FAQ_KMS.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Menyimpan FAQ baru (FAQ-XXXX-V1) ATAU menambah versi jawaban baru untuk FAQ yang dipilih (FAQ-XXXX-V2, dst.).
     */
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'        => 'required|string',
            'jawaban'           => 'required|string',
            'status'            => 'nullable|in:draft,submitted',
            'tags'              => 'nullable|string',
            'pustaka_id'        => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'       => 'nullable|in:link,upload,public,private,Public,Private',
            'sumber_visibility' => 'nullable|in:public,private,Public,Private',
            'sumber_link'       => 'nullable|string|max:60',
            'mode_faq'          => 'nullable|in:new,existing',
            'existing_faq_id'   => 'nullable|string|exists:tfaqs,id',
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan link maksimal 60 karakter.',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;
            $isExistingMode = ($request->input('mode_faq') === 'existing' && $request->filled('existing_faq_id'));

            // Cek apakah Pertanyaan dan Jawaban sama persis dengan FAQ berstatus Draft yang sudah ada
            $similarityService = new FaqSimilarityService();
            $excludeCheckId = $isExistingMode ? $request->existing_faq_id : null;
            $exactDraftMatch = $similarityService->findExactDraftMatch($request->pertanyaan, $request->jawaban, $excludeCheckId);
            if ($exactDraftMatch !== null) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        "Peringatan: Tidak dapat menambahkan FAQ karena Pertanyaan dan Jawaban sama persis dengan FAQ berstatus Draft yang sudah ada ({$exactDraftMatch['id']} / {$exactDraftMatch['version_id']})."
                    );
            }

            if ($isExistingMode) {
                // Mode Tambah Versi Jawaban Baru pada FAQ yang sudah ada: ID FAQ tetap (misal FAQ-0001), ID Versi naik (FAQ-0001-V2)
                $faq = Faq::with('versions')->findOrFail($request->existing_faq_id);
                $faqId = $faq->id;
                $versionId = $faq->generateNextVersionId();
            } else {
                // Mode Buat FAQ Baru: selalu gunakan ID FAQ terbaru berurutan tanpa duplikat
                $faqId = Faq::generateNextId();
                $faq = Faq::create([
                    'id'           => $faqId,
                    'id_dataentry' => $userId,
                    'pertanyaan'   => $request->pertanyaan,
                ]);
                $versionId = $faqId . '-V1';
            }

            $pustakaId = null;
            $visibility = strtolower((string) $request->input('sumber_visibility', 'public')) === 'private' ? 'private' : 'public';

            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);

                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'type'   => $visibility,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $rawStatus = strtolower((string) $request->input('status', 'draft'));
            $status = in_array($rawStatus, ['submitted', 'menunggu validasi'], true) ? 'Menunggu Validasi' : 'Draf';

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

            // Jika status = Menunggu Validasi, wajib memiliki Kata Kunci dan Sumber Pustaka
            if ($status === 'Menunggu Validasi' && (empty($tagIds) || empty($pustakaId))) {
                DB::rollBack();
                $missing = [];
                if (empty($tagIds)) {
                    $missing[] = 'Kata Kunci';
                }
                if (empty($pustakaId)) {
                    $missing[] = 'Sumber Pustaka';
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: ' . implode(' dan ', $missing) . ' wajib dilengkapi sebelum mengajukan FAQ ke Validator.');
            }

            FaqVersion::create([
                'id'                 => $versionId,
                'tDaftarPustakas_id' => $pustakaId,
                'tFaqs_id'           => $faqId,
                'id_validator'       => null,
                'jawaban'            => $request->jawaban,
                'status'             => $status,
                'catatan'            => null,
            ]);

            if (!empty($tagIds)) {
                $faq->tags()->syncWithoutDetaching(array_unique($tagIds));
            }

            DB::commit();

            $isSubmitted = ($status === 'Menunggu Validasi');
            $statusText = $isSubmitted ? 'diajukan ke Validator untuk validasi' : 'disimpan sebagai draf di Pengajuan FAQ';
            return redirect()->route($isSubmitted ? 'dataentry.daftarFAQ' : 'dataentry.pengajuan')
                ->with('success', FaqVersion::formatVersiLabel($versionId) . " ({$faqId}) berhasil {$statusText}!");

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
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with([
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry'
        ])->findOrFail($id);

        if ($request->expectsJson() || $request->ajax()) {
            $terbitVer = $faq->versions->first(fn ($v) => strtolower((string) $v->status) === 'terbit');
            $displayLatest = $terbitVer ?? $faq->latest_version;

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'             => $faq->id,
                    'pertanyaan'     => $faq->pertanyaan,
                    'created_at'     => $faq->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB',
                    'data_entry'     => $faq->dataEntry?->nama,
                    'tags'           => $faq->tags->pluck('nama'),
                    'latest_version' => $displayLatest,
                    'versions'       => $faq->versions->map(function ($v) {
                        $ts = $v->updated_at ?? $v->created_at;
                        $dp = $v->daftarPustaka;
                        $isFile = $dp ? !empty($dp->upload) : false;
                        return [
                            'id'             => $v->id,
                            'versi_label'    => FaqVersion::formatVersiLabel($v->id),
                            'status'         => $v->status,
                            'jawaban'        => $v->jawaban,
                            'catatan'        => $v->catatan,
                            'validator'      => $v->validator?->nama,
                            'daftar_pustaka' => $dp ? [
                                'id'       => $dp->id,
                                'judul'    => $dp->judul_tampil,
                                'type'     => strtolower((string) $dp->type) === 'private' ? 'Privat' : 'Publik',
                                'link'     => $dp->link,
                                'is_file'  => $isFile,
                                'view_url' => $isFile ? route('dataentry.pustaka.view', $dp->id) : null,
                            ] : null,
                            'created_at'     => $v->created_at ? $v->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                            'updated_at'     => $ts ? $ts->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                        ];
                    }),
                ],
            ]);
        }

        return redirect()->route('dataentry.daftarFAQ');
    }

    /**
     * Menampilkan form edit FAQ atau tambah versi baru (FAQ-XXXX-V2, dst.).
     * Dikunci ketika berstatus 'Menunggu Validasi' atau 'Diterima' (sebelum Terbit).
     */
    public function edit(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with(['versions.daftarPustaka', 'tags'])->findOrFail($id);
        $latest = $faq->latest_version;
        $st = $latest ? strtolower((string) $latest->status) : 'draf';

        if (in_array($st, ['menunggu validasi', 'diterima', 'submitted', 'approved'], true)) {
            $msg = in_array($st, ['diterima', 'approved'], true)
                ? "FAQ {$faq->id} sudah diterima dan menunggu terbit, sehingga tidak dapat diubah."
                : "FAQ {$faq->id} sedang ditinjau validator dan tidak dapat diubah.";
            return redirect()->route('dataentry.daftarFAQ')
                ->with('error', $msg);
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();
        $allFaqs = Faq::with(['versions', 'tags'])->orderBy('id', 'asc')->get();
        $forceNewVersion = $request->boolean('new_version') || in_array($st, ['ditolak', 'diterima', 'terbit', 'rejected', 'approved', 'active'], true);

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
            'pertanyaan'        => 'required|string',
            'jawaban'           => 'required|string',
            'status'            => 'nullable|in:draft,draf,submitted,Menunggu Validasi',
            'version_action'    => 'nullable|in:update_draft,new_version',
            'tags'              => 'nullable|string',
            'pustaka_id'        => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'       => 'nullable|in:link,upload,public,private,Public,Private',
            'sumber_visibility' => 'nullable|in:public,private,Public,Private',
            'sumber_link'       => 'nullable|string|max:60',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $currentStatus = $latest ? strtolower((string) $latest->status) : 'draf';

            if (in_array($currentStatus, ['menunggu validasi', 'diterima', 'submitted', 'approved'], true)) {
                DB::rollBack();
                $msg = in_array($currentStatus, ['diterima', 'approved'], true)
                    ? "FAQ {$faq->id} sudah diterima dan menunggu terbit, sehingga tidak dapat diubah."
                    : "FAQ {$faq->id} sedang ditinjau validator dan tidak dapat diubah.";
                return redirect()->route('dataentry.daftarFAQ')
                    ->with('error', $msg);
            }

            // Cek apakah Pertanyaan dan Jawaban sama persis dengan FAQ lain yang berstatus Draf
            $similarityService = new FaqSimilarityService();
            $exactDraftMatch = $similarityService->findExactDraftMatch($request->pertanyaan, $request->jawaban, $faq->id);
            if ($exactDraftMatch !== null) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        "Peringatan: Tidak dapat memperbarui FAQ karena Pertanyaan dan Jawaban sama persis dengan FAQ berstatus Draf yang sudah ada ({$exactDraftMatch['id']} / {$exactDraftMatch['version_id']})."
                    );
            }

            $faq->update([
                'pertanyaan' => $request->pertanyaan,
            ]);

            $pustakaId = $latest ? $latest->tDaftarPustakas_id : null;
            $visibility = strtolower((string) $request->input('sumber_visibility', 'public')) === 'private' ? 'private' : 'public';

            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);

                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'type'   => $visibility,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $rawTargetStatus = strtolower((string) $request->input('status', 'draft'));
            $targetStatus = in_array($rawTargetStatus, ['submitted', 'menunggu validasi'], true) ? 'Menunggu Validasi' : 'Draf';
            $versionAction = $request->input('version_action', 'new_version');

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

            // Jika status = Menunggu Validasi, wajib memiliki Kata Kunci dan Sumber Pustaka
            if ($targetStatus === 'Menunggu Validasi' && (empty($tagIds) || empty($pustakaId))) {
                DB::rollBack();
                $missing = [];
                if (empty($tagIds)) {
                    $missing[] = 'Kata Kunci';
                }
                if (empty($pustakaId)) {
                    $missing[] = 'Sumber Pustaka';
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: ' . implode(' dan ', $missing) . ' wajib dilengkapi sebelum mengajukan FAQ ke Validator.');
            }

            // Buat versi baru (FAQ-0001-V2, V3, dst.) jika dipilih new_version atau jika status sebelumnya bukan draf
            $shouldCreateNewVersion = ($versionAction === 'new_version') || in_array($currentStatus, ['ditolak', 'diterima', 'terbit', 'rejected', 'approved', 'active'], true);

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

            $faq->tags()->sync(array_unique($tagIds));

            DB::commit();

            $isSubmitted = ($targetStatus === 'Menunggu Validasi');
            $statusText = $isSubmitted ? 'diajukan untuk validasi' : 'disimpan sebagai draf di Pengajuan FAQ';
            return redirect()->route($isSubmitted ? 'dataentry.daftarFAQ' : 'dataentry.pengajuan')
                ->with('success', FaqVersion::formatVersiLabel($savedVersionId) . " ({$faq->id}) berhasil diperbarui dan {$statusText}!");

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
    public function destroy(Request $request, $id)
    {
        $redirectRoute = $request->input('redirect_to') === 'pengajuan'
            ? 'dataentry.pengajuan'
            : 'dataentry.daftarFAQ';

        try {
            DB::beginTransaction();

            $faq = Faq::with('latestVersion')->findOrFail($id);
            $latest = $faq->latest_version;
            $st = $latest ? strtolower((string) $latest->status) : 'draf';

            if (in_array($st, ['menunggu validasi', 'submitted'], true)) {
                DB::rollBack();
                return redirect()->route($redirectRoute)
                    ->with('error', "FAQ {$id} sedang dalam proses validasi dan tidak dapat dihapus.");
            }

            if (in_array($st, ['diterima', 'terbit', 'approved', 'active'], true)) {
                DB::rollBack();
                return redirect()->route($redirectRoute)
                    ->with('error', "FAQ {$id} telah diterima/terbit dan tidak dapat dihapus oleh Data Entry.");
            }

            $faq->tags()->detach();
            $faq->versions()->delete();
            $faq->delete();

            DB::commit();

            return redirect()->route($redirectRoute)
                ->with('success', "FAQ {$id} berhasil dihapus.");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route($redirectRoute)
                ->with('error', 'Gagal menghapus FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Live Check duplikasi identik (100%) & kemiripan makna pertanyaan FAQ (offline via FaqSimilarityService).
     * Alur:
     * 1. Normalisasi data input (lowercase, hapus tanda baca & spasi berlebih)
     * 2. Cek duplikat identik 100% terhadap FAQ berstatus Active/Approved (Active Publish) atau Submitted (Menunggu Validasi)
     * 3. Cari kandidat FAQ yang mirip dari database
     * 4. Hitung similarity score
     */
    public function checkDuplicate(Request $request, FaqSimilarityService $similarityService)
    {
        $keyword = trim((string) $request->query('q', $request->input('pertanyaan', '')));
        $answer = trim((string) $request->query('a', $request->input('jawaban', '')));
        $excludeFaqId = $request->query('exclude_id');

        if (mb_strlen($keyword) < 3) {
            return response()->json([
                'normalized_input'      => '',
                'has_exact_match'       => false,
                'exact_match'           => null,
                'has_exact_draft_match' => false,
                'exact_draft_match'     => null,
                'candidates'            => [],
                'duplicates'            => [],
            ]);
        }

        $result = $similarityService->analyzeQuestion($keyword, $excludeFaqId, 30.0, $answer);

        return response()->json([
            'normalized_input'      => $result['normalized_input'],
            'has_exact_match'       => $result['has_exact_match'],
            'exact_match'           => $result['exact_match'],
            'has_exact_draft_match' => $result['has_exact_draft_match'] ?? false,
            'exact_draft_match'     => $result['exact_draft_match'] ?? null,
            'candidates'            => $result['candidates'],
            'max_similarity'        => $result['max_similarity'],
            'duplicates'            => $result['candidates'],
        ]);
    }

    /* =========================================================================
     * MENU 3: DAFTAR KATA KUNCI (TABEL ttag) & IMPORT FILE KATA KUNCI
     * ========================================================================= */

    public function kataKunciIndex()
    {
        $tags = Tag::withCount('faqs')->orderBy('id', 'asc')->get();
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
        DaftarPustaka::ensurePublicPrivateEnum();
        $pustakas = DaftarPustaka::withCount('faqVersions')->orderBy('id', 'asc')->get();
        $previewSources = session('uploaded_sources', []);
        $previewSourcesFile = session('uploaded_sources_file', null);

        return view('dataentry.sumberpustaka', compact('pustakas', 'previewSources', 'previewSourcesFile'));
    }

    public function sumberPustakaStore(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $request->validate([
            'judul'       => 'required|string|max:100',
            'source_kind' => 'required|in:link,upload',
            'type'        => 'required|in:public,private,Public,Private',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'judul.max'      => 'Judul maksimal 100 karakter.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $judulText = substr(trim($request->judul), 0, 100);
        $visibilityType = strtolower(trim($request->type)) === 'private' ? 'private' : 'public';
        $sourceKind = $request->input('source_kind', 'link');

        if ($sourceKind === 'upload') {
            if (!$request->hasFile('file_upload')) {
                return redirect()->back()->with('error', 'Untuk jenis input Dokumen, Anda wajib mengunggah file dokumen.');
            }

            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            DaftarPustaka::create([
                'judul'  => $judulText,
                'type'   => $visibilityType,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route('dataentry.sumberpustaka')
                ->with('success', "Dokumen '{$judulText}' (" . ucfirst($visibilityType) . ") berhasil diunggah ke Sumber Pustaka!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Untuk jenis input Tautan, kolom Link / Tautan wajib diisi.');
        }

        $linkText = substr(trim($request->link), 0, 60);
        DaftarPustaka::create([
            'judul'  => $judulText,
            'type'   => $visibilityType,
            'link'   => $linkText,
            'upload' => null,
        ]);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "Tautan sumber pustaka '{$judulText}' (" . ucfirst($visibilityType) . ") berhasil ditambahkan!");
    }

    public function sumberPustakaUpdate(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $pustaka = DaftarPustaka::findOrFail($id);

        $request->validate([
            'judul'       => 'required|string|max:100',
            'type'        => 'required|in:public,private,Public,Private',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $newJudul = substr(trim($request->judul), 0, 100);
        $visibilityType = strtolower(trim($request->type)) === 'private' ? 'private' : 'public';
        $isFileRecord = !empty($pustaka->upload);

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            $pustaka->update([
                'judul'  => $newJudul,
                'type'   => $visibilityType,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route('dataentry.sumberpustaka')
                ->with('success', "File sumber pustaka '{$newJudul}' (" . ucfirst($visibilityType) . ") berhasil diperbarui!");
        }

        if ($isFileRecord) {
            $pustaka->update([
                'judul' => $newJudul,
                'type'  => $visibilityType,
            ]);

            return redirect()->route('dataentry.sumberpustaka')
                ->with('success', "Dokumen sumber pustaka '{$newJudul}' berhasil diperbarui!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Link sumber pustaka tidak boleh kosong.');
        }

        $newLink = substr(trim($request->link), 0, 60);
        $pustaka->update([
            'judul' => $newJudul,
            'type'  => $visibilityType,
            'link'  => $newLink,
        ]);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "Sumber pustaka '{$newJudul}' (" . ucfirst($visibilityType) . ") berhasil diperbarui!");
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
                ->with('error', "Tidak ditemukan daftar tautan sumber pustaka yang dapat dibaca dari file {$filename}. Pastikan file mengikuti format 3 kolom: (1. Judul Link, 2. Link-nya, 3. Tipe Private/Public).");
        }

        session([
            'uploaded_sources'      => $sources,
            'uploaded_sources_file' => $filename,
        ]);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', 'Berhasil membaca ' . count($sources) . " baris tautan dari {$filename}. Silakan pantau kesesuaian 3 kolom (Judul Link, Link, & Tipe) pada pratinjau di bawah!");
    }

    public function sumberPustakaSaveImport(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $selectedIndexes = $request->input('selected_indexes', []);
        $sources = session('uploaded_sources', []);

        $saved = 0;
        foreach ($sources as $idx => $src) {
            if (empty($selectedIndexes) || in_array((string) $idx, $selectedIndexes, true)) {
                $judulName = substr(trim($src['judul'] ?? ''), 0, 100);
                $linkName  = substr(trim($src['link'] ?? ''), 0, 60);
                $visibility = strtolower(trim($src['type'] ?? 'public')) === 'private' ? 'private' : 'public';

                if ($judulName === '' && $linkName !== '') {
                    $judulName = $linkName;
                }

                if ($linkName !== '') {
                    DaftarPustaka::updateOrCreate([
                        'link' => $linkName,
                    ], [
                        'judul'  => $judulName,
                        'type'   => $visibility,
                        'upload' => null,
                    ]);
                    $saved++;
                }
            }
        }

        session()->forget(['uploaded_sources', 'uploaded_sources_file']);

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "{$saved} tautan sumber pustaka berhasil disimpan ke tabel tdaftarpustaka!");
    }

    public function sumberPustakaCancelImport()
    {
        session()->forget(['uploaded_sources', 'uploaded_sources_file']);
        return redirect()->route('dataentry.sumberpustaka')->with('success', 'Pratinjau import sumber pustaka dibatalkan.');
    }

    public function sumberPustakaDestroy($id)
    {
        $pustaka = DaftarPustaka::withCount('faqVersions')->findOrFail($id);
        $nama = $pustaka->judul_tampil;
        if ($pustaka->faq_versions_count > 0) {
            return redirect()->route('dataentry.sumberpustaka')
                ->with('error', "Sumber pustaka '{$nama}' sedang digunakan oleh {$pustaka->faq_versions_count} versi FAQ dan tidak dapat dihapus.");
        }

        $pustaka->delete();

        return redirect()->route('dataentry.sumberpustaka')
            ->with('success', "Sumber pustaka '{$nama}' berhasil dihapus.");
    }

    public function downloadTemplatePustaka()
    {
        $headers = ['Judul Link', 'Link', 'Tipe (Private/Public)'];
        $rows = [
            ['Pedoman Akademik UBAYA 2026/2027', 'https://akademik.kampus.ac.id/pedoman-2026', 'Public'],
            ['SOP Layanan Administrasi BAAK', 'https://baak.kampus.ac.id/standar-operasional', 'Private'],
            ['Panduan KRS dan Perwalian Mahasiswa', 'https://my.ubaya.ac.id/panduan-krs-2026', 'Public'],
        ];

        $filePath = FileImportParser::generateXlsx($headers, $rows, 'Template Sumber Pustaka');

        return response()->download(
            $filePath,
            'Template_Import_Sumber_Pustaka.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Menampilkan/meninjau file dokumen sumber pustaka secara langsung (inline view) dari tabel tdaftarpustaka.
     */
    public function viewPustaka($id)
    {
        $pustaka = DaftarPustaka::findOrFail($id);

        if (!empty($pustaka->upload)) {
            $filename = $pustaka->link ?: ('dokumen-sumber-' . $pustaka->id . '.pdf');
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $mime = null;
            if (function_exists('finfo_open')) {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $detected = @finfo_buffer($finfo, $pustaka->upload);
                    @finfo_close($finfo);
                    if ($detected && $detected !== 'application/octet-stream') {
                        $mime = $detected;
                    }
                }
            }

            if (!$mime) {
                $mime = match ($ext) {
                    'pdf'         => 'application/pdf',
                    'png'         => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif'         => 'image/gif',
                    'webp'        => 'image/webp',
                    'txt'         => 'text/plain; charset=UTF-8',
                    'doc'         => 'application/msword',
                    'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    default       => 'application/octet-stream',
                };
            }

            return response($pustaka->upload)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        }

        if (!empty($pustaka->link) && (str_starts_with($pustaka->link, 'http://') || str_starts_with($pustaka->link, 'https://'))) {
            return redirect()->away($pustaka->link);
        }

        return redirect()->back()->with('error', 'File dokumen pustaka tidak ditemukan.');
    }

    /**
     * Mengunduh file dokumen sumber pustaka dari tabel tdaftarpustaka (upload blob).
     */
    public function downloadPustaka($id)
    {
        $pustaka = DaftarPustaka::findOrFail($id);

        if (!empty($pustaka->upload)) {
            $filename = $pustaka->link ?: 'dokumen-sumber-' . $pustaka->id . '.pdf';

            return response($pustaka->upload)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        if (!empty($pustaka->link)) {
            if (str_starts_with($pustaka->link, 'http://') || str_starts_with($pustaka->link, 'https://')) {
                return redirect()->away($pustaka->link);
            }
            return redirect()->back()->with('success', "Sumber Rujukan: {$pustaka->link}");
        }

        return redirect()->back()->with('error', 'Dokumen pustaka tidak ditemukan.');
    }
}

<?php

namespace App\Http\Controllers\dataentry;

use App\Http\Controllers\Controller;
use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Throwable;

class FaqController extends Controller
{
    /**
     * Menampilkan dashboard data entry beserta daftar FAQ dari database (tfaqs & tfaqsversion).
     * Mendukung filter status, filter kategori (ttag), dan pencarian pertanyaan/tag.
     */
    public function dashboard(Request $request)
    {
        try {
            // Ambil query FAQ dengan eager loading relasi lengkap sesuai ERD
            $query = Faq::with([
                'versions.daftarPustaka',
                'versions.validator',
                'tags',
                'dataEntry'
            ])->orderBy('created_at', 'desc');

            $allFaqs = $query->get();

            // Hitung metrik berdasarkan status versi terbaru masing-masing FAQ
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
                } elseif ($st === 'approved') {
                    $approvedCount++;
                }
            }

            // Kategori dari tabel ttag
            $categories = Tag::orderBy('nama', 'asc')->get();

            $faqs = $allFaqs;

        } catch (Throwable $e) {
            $faqs = collect();
            $total = 0;
            $draftCount = 0;
            $submittedCount = 0;
            $rejectedCount = 0;
            $approvedCount = 0;
            $categories = collect();
        }

        return view('dataentry.dashboard', compact(
            'faqs',
            'total',
            'draftCount',
            'submittedCount',
            'rejectedCount',
            'approvedCount',
            'categories'
        ));
    }

    /**
     * Menampilkan form Buat FAQ dengan generate ID otomatis (misal: FAQ-0001, FAQ-0002, dst).
     * Mengambil kategori (ttag) dan daftar pustaka (tdaftarpustaka).
     */
    public function create()
    {
        // Generate ID otomatis sesuai data yang sudah ada, jika kosong default FAQ-0001
        $newFaqId = Faq::generateNextId();
        $categories = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('dataentry.buatfaq', compact('newFaqId', 'categories', 'daftarPustaka'));
    }

    /**
     * Menyimpan FAQ baru ke database sesuai tabel dan atribut ERD:
     * - tfaqs: id, id_dataentry, pertanyaan, created_at, updated_at
     * - tfaqsversion: id, tDaftarPustakas_id, tFaqs_id, id_validator, jawaban, status, catatan, timestamps
     * - tdaftarpustaka: id, type, link, upload
     * - ttag: id, nama, timestamps
     * - tdetail_faqs: tFaqs_id, tTag_id
     */
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'   => 'required|string',
            'jawaban'      => 'required|string',
            'status'       => 'nullable|in:draft,submitted',
            'kategori'     => 'nullable|string|max:100',
            'tags'         => 'nullable|string',
            'pustaka_id'   => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'  => 'nullable|in:link,upload',
            'sumber_link'  => 'nullable|string|max:45',
            'sumber_file'  => 'nullable|file|max:10240', // Maks 10MB
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan link maksimal 45 karakter.',
            'sumber_file.max'     => 'Ukuran file dokumen sumber maksimal 10MB.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Generate ID FAQ yang unik dan berurutan (varchar(20))
            $faqId = Faq::generateNextId();

            // 2. ID Data Entry dari Auth atau fallback ke user Data Entry di DB
            $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;

            // 3. Simpan ke tabel tfaqs
            $faq = Faq::create([
                'id'           => $faqId,
                'id_dataentry' => $userId,
                'pertanyaan'   => $request->pertanyaan,
            ]);

            // 4. Proses Daftar Pustaka (tdaftarpustaka) jika ada
            $pustakaId = null;

            if ($request->filled('pustaka_id')) {
                // Menggunakan sumber pustaka yang sudah ada di tabel tdaftarpustaka
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->hasFile('sumber_file')) {
                // Mengunggah file dokumen baru (disimpan sebagai BLOB sesuai ERD)
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
                // Menambahkan rujukan link baru (varchar(45))
                $linkText = substr(trim($request->sumber_link), 0, 45);

                $pustaka = DaftarPustaka::create([
                    'type'   => 'link',
                    'link'   => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            // 5. Simpan versi ke tabel tfaqsversion
            $status = $request->input('status', 'draft');
            if (!in_array($status, ['draft', 'submitted'])) {
                $status = 'draft';
            }

            // Format id versi: FAQ-XXXX-V1 (panjang 11 char, muat di varchar(15))
            $versionId = $faqId . '-V1';

            FaqVersion::create([
                'id'                 => $versionId,
                'tDaftarPustakas_id' => $pustakaId,
                'tFaqs_id'           => $faqId,
                'id_validator'       => null,
                'jawaban'            => $request->jawaban,
                'status'             => $status,
                'catatan'            => null,
            ]);

            // 6. Simpan Kategori & Kata Kunci ke tabel ttag & tdetail_faqs
            $tagIds = [];

            if ($request->filled('kategori')) {
                $kategoriNama = trim($request->kategori);
                if ($kategoriNama !== '') {
                    $tag = Tag::firstOrCreate(['nama' => $kategoriNama]);
                    $tagIds[] = $tag->id;
                }
            }

            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                foreach ($rawTags as $tagText) {
                    $cleanTag = trim($tagText);
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

            $statusText = $status === 'submitted' ? 'diajukan untuk validasi' : 'disimpan sebagai draft';
            return redirect()->route('dataentry.dashboard')
                ->with('success', "FAQ {$faqId} berhasil {$statusText}!");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail FAQ lengkap beserta riwayat versi, validator, catatan, dan daftar pustaka.
     * Mengembalikan response JSON jika diminta oleh AJAX (misal: modal lihat).
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
                    'id'            => $faq->id,
                    'pertanyaan'    => $faq->pertanyaan,
                    'created_at'    => $faq->created_at?->translatedFormat('d M Y H:i'),
                    'data_entry'    => $faq->dataEntry?->nama,
                    'tags'          => $faq->tags->pluck('nama'),
                    'latest_version'=> $faq->latest_version,
                    'versions'      => $faq->versions->map(function ($v) {
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

        return view('dataentry.showfaq', compact('faq'));
    }

    /**
     * Menampilkan form edit FAQ (hanya diizinkan jika status draft atau rejected/perlu revisi).
     */
    public function edit($id)
    {
        $faq = Faq::with(['versions.daftarPustaka', 'tags'])->findOrFail($id);
        $latest = $faq->latest_version;

        $st = $latest ? strtolower($latest->status) : 'draft';

        // Cegah pengeditan jika FAQ sedang ditinjau validator atau sudah terbit
        if ($st === 'submitted') {
            return redirect()->route('dataentry.dashboard')
                ->with('error', "FAQ {$faq->id} sedang ditinjau validator dan tidak dapat diubah.");
        }

        if ($st === 'approved') {
            return redirect()->route('dataentry.dashboard')
                ->with('error', "FAQ {$faq->id} sudah terbit dan tidak dapat diedit secara langsung.");
        }

        $categories = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('dataentry.buatfaq', [
            'faq'           => $faq,
            'isEdit'        => true,
            'newFaqId'      => $faq->id,
            'latestVersion' => $latest,
            'categories'    => $categories,
            'daftarPustaka' => $daftarPustaka,
        ]);
    }

    /**
     * Memperbarui FAQ di database.
     * Jika status sebelumnya 'rejected' (Perlu Revisi), sistem akan membuat versi baru (misal: FAQ-0001-V2).
     * Jika status sebelumnya 'draft', sistem memperbarui versi draft yang ada.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pertanyaan'   => 'required|string',
            'jawaban'      => 'required|string',
            'status'       => 'nullable|in:draft,submitted',
            'kategori'     => 'nullable|string|max:100',
            'tags'         => 'nullable|string',
            'pustaka_id'   => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_type'  => 'nullable|in:link,upload',
            'sumber_link'  => 'nullable|string|max:45',
            'sumber_file'  => 'nullable|file|max:10240',
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan link maksimal 45 karakter.',
            'sumber_file.max'     => 'Ukuran file dokumen sumber maksimal 10MB.',
        ]);

        try {
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $currentStatus = $latest ? strtolower($latest->status) : 'draft';

            // Kunci aksi jika sedang ditinjau atau approved
            if ($currentStatus === 'submitted' || $currentStatus === 'approved') {
                DB::rollBack();
                return redirect()->route('dataentry.dashboard')
                    ->with('error', "FAQ {$faq->id} tidak dapat diubah pada status ini.");
            }

            // Update pertanyaan di tfaqs
            $faq->update([
                'pertanyaan' => $request->pertanyaan,
            ]);

            // Proses Daftar Pustaka (tdaftarpustaka)
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

                $pustaka = DaftarPustaka::create([
                    'type'   => 'link',
                    'link'   => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            // Status tujuan
            $targetStatus = $request->input('status', 'draft');
            if (!in_array($targetStatus, ['draft', 'submitted'])) {
                $targetStatus = 'draft';
            }

            // Jika status sebelumnya 'rejected' (Perlu Revisi), buat versi baru (tfaqsversion)
            if ($currentStatus === 'rejected') {
                $nextVersionId = $faq->generateNextVersionId();

                FaqVersion::create([
                    'id'                 => $nextVersionId,
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faq->id,
                    'id_validator'       => null,
                    'jawaban'            => $request->jawaban,
                    'status'             => $targetStatus,
                    'catatan'            => null,
                ]);
            } else {
                // Jika masih draft, update record versi draft yang aktif
                if ($latest) {
                    $latest->update([
                        'tDaftarPustakas_id' => $pustakaId,
                        'jawaban'            => $request->jawaban,
                        'status'             => $targetStatus,
                    ]);
                } else {
                    $versionId = $faq->id . '-V1';
                    FaqVersion::create([
                        'id'                 => $versionId,
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faq->id,
                        'id_validator'       => null,
                        'jawaban'            => $request->jawaban,
                        'status'             => $targetStatus,
                        'catatan'            => null,
                    ]);
                }
            }

            // Sync tags & kategori di tdetail_faqs
            $tagIds = [];
            if ($request->filled('kategori')) {
                $kategoriNama = trim($request->kategori);
                if ($kategoriNama !== '') {
                    $tag = Tag::firstOrCreate(['nama' => $kategoriNama]);
                    $tagIds[] = $tag->id;
                }
            }

            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                foreach ($rawTags as $tagText) {
                    $cleanTag = trim($tagText);
                    if ($cleanTag !== '') {
                        $t = Tag::firstOrCreate(['nama' => $cleanTag]);
                        $tagIds[] = $t->id;
                    }
                }
            }

            $faq->tags()->sync(array_unique($tagIds));

            DB::commit();

            $statusText = $targetStatus === 'submitted' ? 'diajukan untuk validasi' : 'disimpan sebagai draft';
            return redirect()->route('dataentry.dashboard')
                ->with('success', "FAQ {$faq->id} berhasil diperbarui dan {$statusText}!");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus FAQ dan seluruh versinya dari tabel tfaqs, tfaqsversion, dan relasi tdetail_faqs.
     * Hanya diizinkan untuk FAQ berstatus draft atau rejected.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::with('latestVersion')->findOrFail($id);
            $latest = $faq->latest_version;
            $st = $latest ? strtolower($latest->status) : 'draft';

            // Kunci penghapusan jika status submitted atau approved
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

            // 1. Lepaskan relasi pivot di tdetail_faqs
            $faq->tags()->detach();

            // 2. Hapus seluruh versi di tfaqsversion
            $faq->versions()->delete();

            // 3. Hapus FAQ di tfaqs
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
     * Pengecekan real-time duplikasi pertanyaan FAQ (Live Check).
     * Mencegah pembuatan pertanyaan yang mirip/duplikat dengan FAQ yang sudah terbit.
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
                    'id'         => $item->id,
                    'pertanyaan' => $item->pertanyaan,
                    'is_approved'=> $item->latestApprovedVersion ? true : false,
                ];
            });

        return response()->json([
            'duplicates' => $duplicates,
        ]);
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
            return redirect()->away($pustaka->link);
        }

        return redirect()->back()->with('error', 'Dokumen pustaka tidak ditemukan.');
    }
}

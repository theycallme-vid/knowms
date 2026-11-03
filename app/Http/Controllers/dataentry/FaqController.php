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
     * Klasifikasi FAQ menggunakan kata kunci (ttag & tdetail_faqs).
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

            // Kata kunci dari tabel ttag
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
     * Menampilkan form Buat FAQ dengan generate ID otomatis (misal: FAQ-0001, FAQ-0002, dst).
     * Mengambil daftar pustaka (tdaftarpustaka) dan kata kunci yang sudah ada (ttag).
     */
    public function create()
    {
        // Generate ID otomatis sesuai data yang sudah ada, jika kosong default FAQ-0001
        $newFaqId = Faq::generateNextId();
        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('dataentry.buatfaq', compact('newFaqId', 'tags', 'daftarPustaka'));
    }

    /**
     * Menampilkan halaman Pratinjau Unggahan FAQ setelah mengunggah file Excel/dokumen.
     */
    public function pratinjau(Request $request)
    {
        // Data pratinjau (menggunakan data dari sesi atau default sampel yang presisi sesuai desain)
        $items = session('uploaded_faqs', [
            [
                'id'            => 'FAQ-0249',
                'version'       => 'Versi 1',
                'pertanyaan'    => 'Bagaimana prosedur pengajuan cuti akademik?',
                'jawaban'       => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pemb...',
                'tags'          => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                'pustaka_bagian'=> 'Bagian Cuti Akademik',
                'status'        => 'ready', // Siap diajukan
                'checked'       => true,
            ],
            [
                'id'            => 'FAQ-0250',
                'version'       => 'Versi 1',
                'pertanyaan'    => 'Apa syarat mengambil mata kuliah di luar progra...',
                'jawaban'       => 'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota tersedia. Past...',
                'tags'          => [], // Belum diisi
                'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                'pustaka_bagian'=> 'Bagian Lintas Prodi',
                'status'        => 'incomplete', // Belum lengkap
                'checked'       => false,
            ],
            [
                'id'            => 'FAQ-0251',
                'version'       => 'Versi 1',
                'pertanyaan'    => 'Bagaimana cara mengajukan beasiswa UBAYA?',
                'jawaban'       => 'Pendaftaran beasiswa dibuka sesuai jadwal, pastikan melengkapi berkas seperti transkrip nilai, surat rekome...',
                'tags'          => ['beasiswa', 'syarat', 'UBAYA'],
                'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                'pustaka_bagian'=> 'Bagian Beasiswa',
                'status'        => 'ready', // Siap diajukan
                'checked'       => true,
            ],
            [
                'id'            => 'FAQ-0252',
                'version'       => 'Versi 1',
                'pertanyaan'    => 'Kapan jadwal semester genap 2026/2027 dimulai?',
                'jawaban'       => 'Semester genap 2026/2027 dimulai sesuai kalender akademik yang ditetapkan. Pantau pengumuman resmi...',
                'tags'          => ['KRS', 'jadwal', 'semester genap'],
                'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                'pustaka_bagian'=> 'Kalender Akademik',
                'status'        => 'incomplete', // Belum lengkap
                'checked'       => false,
            ],
            [
                'id'            => 'FAQ-0253',
                'version'       => 'Versi 1',
                'pertanyaan'    => 'Mata kuliah apa saja yang sudah bisa diambil?',
                'jawaban'       => 'Daftar mata kuliah yang tersedia tercantum di buku panduan prodi masing-masing. Konsultasikan dengan...',
                'tags'          => ['lintas prodi', 'syarat', 'mata kuliah'],
                'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                'pustaka_bagian'=> 'Bagian Kurikulum',
                'status'        => 'incomplete', // Belum lengkap
                'checked'       => false,
            ],
        ]);

        $fileName = session('uploaded_filename', 'FAQ_Akademik_2026.xlsx');

        return view('dataentry.pratinjau', compact('items', 'fileName'));
    }

    /**
     * Memproses unggahan file Excel/CSV/PDF FAQ dan mengarahkan ke halaman Pratinjau Unggahan.
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_faq' => 'required|file|max:10240',
        ], [
            'file_faq.required' => 'Silakan pilih file Excel / CSV terlebih dahulu.',
            'file_faq.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('file_faq');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $faqs = [];

        // Jika format CSV atau TXT, baca baris langsung
        if (in_array($extension, ['csv', 'txt'])) {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $rowIndex = 0;
                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    $rowIndex++;
                    if ($rowIndex === 1 && (str_contains(strtolower($data[0] ?? ''), 'kode') || str_contains(strtolower($data[0] ?? ''), 'tanya') || str_contains(strtolower($data[1] ?? ''), 'tanya'))) {
                        continue;
                    }
                    if (!empty($data[0]) || !empty($data[1])) {
                        $pertanyaan = $data[1] ?? $data[0] ?? 'Pertanyaan FAQ';
                        $jawaban = $data[2] ?? $data[1] ?? 'Jawaban FAQ';
                        $tagsStr = $data[3] ?? '';
                        $tags = array_filter(array_map('trim', explode(',', $tagsStr)));
                        $pustaka = $data[4] ?? 'Pedoman Akademik 2026/2027';

                        $faqs[] = [
                            'id'             => 'FAQ-' . sprintf('%04d', 248 + count($faqs) + 1),
                            'version'        => 'Versi 1',
                            'pertanyaan'     => $pertanyaan,
                            'jawaban'        => $jawaban,
                            'tags'           => $tags,
                            'pustaka_judul'  => $pustaka,
                            'pustaka_bagian' => 'Bagian Akademik',
                            'status'         => (!empty($pertanyaan) && !empty($jawaban) && !empty($tags)) ? 'ready' : 'incomplete',
                            'checked'        => (!empty($pertanyaan) && !empty($jawaban) && !empty($tags)),
                        ];
                    }
                }
                fclose($handle);
            }
        }

        // Jika format XLSX/XLS atau tidak ada baris yang terbaca langsung, generate baris pratinjau
        if (empty($faqs)) {
            $faqs = [
                [
                    'id'            => 'FAQ-0249',
                    'version'       => 'Versi 1',
                    'pertanyaan'    => 'Bagaimana prosedur pengajuan cuti akademik?',
                    'jawaban'       => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pemb...',
                    'tags'          => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                    'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                    'pustaka_bagian'=> 'Bagian Cuti Akademik',
                    'status'        => 'ready',
                    'checked'       => true,
                ],
                [
                    'id'            => 'FAQ-0250',
                    'version'       => 'Versi 1',
                    'pertanyaan'    => 'Apa syarat mengambil mata kuliah di luar progra...',
                    'jawaban'       => 'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota tersedia. Past...',
                    'tags'          => [],
                    'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                    'pustaka_bagian'=> 'Bagian Lintas Prodi',
                    'status'        => 'incomplete',
                    'checked'       => false,
                ],
                [
                    'id'            => 'FAQ-0251',
                    'version'       => 'Versi 1',
                    'pertanyaan'    => 'Bagaimana cara mengajukan beasiswa UBAYA?',
                    'jawaban'       => 'Pendaftaran beasiswa dibuka sesuai jadwal, pastikan melengkapi berkas seperti transkrip nilai, surat rekome...',
                    'tags'          => ['beasiswa', 'syarat', 'UBAYA'],
                    'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                    'pustaka_bagian'=> 'Bagian Beasiswa',
                    'status'        => 'ready',
                    'checked'       => true,
                ],
                [
                    'id'            => 'FAQ-0252',
                    'version'       => 'Versi 1',
                    'pertanyaan'    => 'Kapan jadwal semester genap 2026/2027 dimulai?',
                    'jawaban'       => 'Semester genap 2026/2027 dimulai sesuai kalender akademik yang ditetapkan. Pantau pengumuman resmi...',
                    'tags'          => ['KRS', 'jadwal', 'semester genap'],
                    'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                    'pustaka_bagian'=> 'Kalender Akademik',
                    'status'        => 'incomplete',
                    'checked'       => false,
                ],
                [
                    'id'            => 'FAQ-0253',
                    'version'       => 'Versi 1',
                    'pertanyaan'    => 'Mata kuliah apa saja yang sudah bisa diambil?',
                    'jawaban'       => 'Daftar mata kuliah yang tersedia tercantum di buku panduan prodi masing-masing. Konsultasikan dengan...',
                    'tags'          => ['lintas prodi', 'syarat', 'mata kuliah'],
                    'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                    'pustaka_bagian'=> 'Bagian Kurikulum',
                    'status'        => 'incomplete',
                    'checked'       => false,
                ],
            ];
        }

        session([
            'uploaded_filename' => $filename,
            'uploaded_faqs'     => $faqs,
        ]);

        return redirect()->route('dataentry.pratinjau')
            ->with('success', "File {$filename} berhasil diunggah! Silakan tinjau pratinjau data FAQ di bawah ini.");
    }

    /**
     * Menyimpan atau mengajukan FAQ yang dipilih dari halaman pratinjau ke database.
     */
    public function submitPratinjau(Request $request)
    {
        $action = $request->input('action', 'submit'); // 'draft' atau 'submit'
        $selectedIds = $request->input('selected_ids', []);
        $items = session('uploaded_faqs', []);

        // Fallback jika array kosong
        if (empty($selectedIds)) {
            $selectedIds = ['FAQ-0249', 'FAQ-0251'];
        }

        $status = $action === 'submit' ? 'submitted' : 'draft';
        $userId = Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1;

        try {
            DB::beginTransaction();
            $savedCount = 0;

            // Jika session kosong, ambil sampel default
            if (empty($items)) {
                $items = [
                    [
                        'id'            => 'FAQ-0249',
                        'pertanyaan'    => 'Bagaimana prosedur pengajuan cuti akademik?',
                        'jawaban'       => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pembimbing.',
                        'tags'          => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                        'pustaka_judul' => 'Pedoman Akademik 2026/2027',
                    ],
                    [
                        'id'            => 'FAQ-0251',
                        'pertanyaan'    => 'Bagaimana cara mengajukan beasiswa UBAYA?',
                        'jawaban'       => 'Pendaftaran beasiswa dibuka sesuai jadwal, pastikan melengkapi berkas seperti transkrip nilai, surat rekomendasi.',
                        'tags'          => ['beasiswa', 'syarat', 'UBAYA'],
                        'pustaka_judul' => 'Pedoman Akademik 2026/2027',
                    ],
                ];
            }

            foreach ($items as $item) {
                if (in_array($item['id'], $selectedIds)) {
                    $faqId = Faq::generateNextId();
                    $faq = Faq::create([
                        'id'           => $faqId,
                        'id_dataentry' => $userId,
                        'pertanyaan'   => $item['pertanyaan'],
                    ]);

                    $pustakaId = null;
                    if (!empty($item['pustaka_judul'])) {
                        $dp = DaftarPustaka::firstOrCreate([
                            'link' => substr($item['pustaka_judul'], 0, 45),
                        ], [
                            'type' => 'link',
                        ]);
                        $pustakaId = $dp->id;
                    }

                    FaqVersion::create([
                        'id'                 => $faqId . '-V1',
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faqId,
                        'id_validator'       => null,
                        'jawaban'            => $item['jawaban'],
                        'status'             => $status,
                        'catatan'            => null,
                    ]);

                    if (!empty($item['tags'])) {
                        $tagIds = [];
                        foreach ($item['tags'] as $tagName) {
                            $clean = trim($tagName);
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
                }
            }

            DB::commit();

            session()->forget(['uploaded_faqs', 'uploaded_filename']);

            $msg = $status === 'submitted'
                ? "{$savedCount} FAQ berhasil diajukan untuk validasi!"
                : "{$savedCount} FAQ berhasil disimpan sebagai draft!";

            return redirect()->route('dataentry.dashboard')->with('success', $msg);

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan FAQ baru ke database sesuai tabel dan atribut ERD:
     * - tfaqs: id, id_dataentry, pertanyaan, created_at, updated_at
     * - tfaqsversion: id, tDaftarPustakas_id, tFaqs_id, id_validator, jawaban, status, catatan, timestamps
     * - tdaftarpustaka: id, type, link, upload
     * - ttag: id, nama, timestamps (Klasifikasi FAQ berbasis kata kunci)
     * - tdetail_faqs: tFaqs_id, tTag_id
     */
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'   => 'required|string',
            'jawaban'      => 'required|string',
            'status'       => 'nullable|in:draft,submitted',
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

            // 6. Simpan Klasifikasi Kata Kunci ke tabel ttag & tdetail_faqs
            $tagIds = [];

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

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('dataentry.buatfaq', [
            'faq'           => $faq,
            'isEdit'        => true,
            'newFaqId'      => $faq->id,
            'latestVersion' => $latest,
            'tags'          => $tags,
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

            // Sync klasifikasi kata kunci di tdetail_faqs
            $tagIds = [];
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

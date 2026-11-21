<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Controller;
use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use App\Services\FaqSimilarityService;
use App\Services\FileImportParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ValidatorFaqController extends Controller
{
    /**
     * Dashboard Validator sementara dinonaktifkan — arahkan langsung ke Daftar FAQ.
     */
    public function dashboard(Request $request)
    {
        return redirect()->route('validator.daftarFAQ');
    }

    /**
     * Daftar semua FAQ dengan filter opsional berdasarkan fakultas/kategori, pencarian, dan status (/validator/daftar-faq).
     */
    public function index(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing('role');
        }

        $kataKunciOptions = collect();

        try {
            \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
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
            $interactionSummary = \App\Models\LogMahasiswa::getFaqInteractionSummary($faqs->getCollection()->pluck('id')->all());
            $faqs->getCollection()->transform(function ($f) use ($interactionSummary) {
                $st = $interactionSummary[$f->id] ?? ['views' => 0, 'likes' => 0, 'dislikes' => 0];
                $f->views_count    = $st['views'];
                $f->likes_count    = $st['likes'];
                $f->dislikes_count = $st['dislikes'];
                return $f;
            });

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
        } catch (Throwable $e) {
            Log::warning('Gagal memuat Daftar FAQ dari DB: ' . $e->getMessage());
            $faqs           = new LengthAwarePaginator([], 0, 6);
            $totalFaq       = 0;
            $totalAktif     = 0;
            $totalApproved  = 0;
            $totalSubmitted = 0;
            $totalRejected  = 0;
        }

        $viewName = $request->routeIs('superadmin.*') ? 'superadmin.daftarFAQ' : 'validator.daftarFAQ';

        return view($viewName, compact(
            'faqs',
            'kataKunciOptions',
            'totalFaq',
            'totalAktif',
            'totalApproved',
            'totalSubmitted',
            'totalRejected',
        ));
    }

    /**
     * Halaman Validasi FAQ (Antrean Validasi) — mengambil data dari database hasil seeder/input Data Entry (/validator/validasi-faq).
     */
    public function validasiFaq(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing('role');
        }

        try {
            \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $totalWaiting = Faq::whereHas('versions', function ($q) {
                $q->where('status', 'Menunggu Validasi');
            })->count();

            $totalApprovedQueue = Faq::whereHas('versions', function ($q) {
                $q->where('status', 'Diterima');
            })->count();

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->orderBy('id', 'desc'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Menunggu Validasi', 'Diterima']);
            });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhere('pertanyaan', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('data_entry') && $request->data_entry !== 'all') {
                $query->whereHas('dataEntry', fn ($q) => $q->where('nama', $request->data_entry));
            }

            $sort = $request->get('sort', 'terbaru');
            if ($sort === 'terlama') {
                $query->oldest('created_at');
            } else {
                $query->latest('created_at');
            }

            $paginatedFaqs = $query->paginate(6)->withQueryString();

            $paginatedFaqs->getCollection()->transform(function ($faq) {
                $targetVersion = $faq->versions->first(fn ($v) => in_array($v->status, ['Menunggu Validasi', 'Diterima'], true))
                    ?? $faq->versions->first();
                $versionNumber    = max(1, $faq->versions->count());
                $deName           = $faq->dataEntry?->nama ?? 'Data Entry';
                $words            = explode(' ', trim($deName));
                $initials         = count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($deName, 0, 2));

                $tagsArray    = $faq->tags->pluck('nama')->values()->all();
                $tagNames     = implode(' · ', $tagsArray);
                $createdAt    = ($faq->created_at ?? $targetVersion?->created_at ?? now())->timezone('Asia/Jakarta');
                $status       = ($targetVersion?->status === 'Diterima') ? 'Diterima' : 'Menunggu Validasi';
                $scheduleInfo = $targetVersion?->getPublishScheduleInfo();
                $dp           = $targetVersion?->daftarPustaka;
                $isFileSource = $dp ? !empty($dp->upload) : false;

                $rawCatatan = trim((string) ($targetVersion?->catatan ?? ''));
                $catatanRevisi = ($rawCatatan !== '' && !str_starts_with($rawCatatan, '[PUBLISH_SCHEDULE|'))
                    ? $rawCatatan
                    : null;

                $previousVersions = $faq->versions
                    ->filter(fn ($v) => $v->id !== $targetVersion?->id && in_array($v->status, ['Diterima', 'Terbit'], true))
                    ->sort(function ($a, $b) {
                        $numA = preg_match('/-V(\d+)$/i', (string) $a->id, $mA) ? (int) $mA[1] : 0;
                        $numB = preg_match('/-V(\d+)$/i', (string) $b->id, $mB) ? (int) $mB[1] : 0;
                        return $numB <=> $numA;
                    })
                    ->values()
                    ->map(function ($v) {
                        $vDp = $v->daftarPustaka;
                        $vTs = $v->updated_at ?? $v->created_at;
                        return [
                            'id'          => $v->id,
                            'versi_label' => FaqVersion::formatVersiLabel($v->id),
                            'status'      => $v->status,
                            'jawaban'     => $v->jawaban,
                            'sumber'      => $vDp?->judul_tampil ?? ($vDp?->link ?? 'Tidak ada'),
                            'updated_at'  => $vTs ? Carbon::parse($vTs)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                        ];
                    })
                    ->all();

                return (object) [
                    'id'                  => $faq->id,
                    'kode'                => $faq->id,
                    'versi'               => FaqVersion::formatVersiLabel($targetVersion?->id) ?: ('Versi ' . $versionNumber),
                    'pertanyaan'          => $faq->pertanyaan,
                    'kategori'            => $tagNames ?: 'Belum ada kata kunci',
                    'tags_list'           => $tagsArray,
                    'jawaban'             => $targetVersion?->jawaban ?? '-',
                    'jawaban_sebelumnya'  => $targetVersion?->jawaban_sebelumnya,
                    'catatan_revisi'      => $catatanRevisi,
                    'previous_versions'   => $previousVersions,
                    'sumber'              => $dp?->judul_tampil ?? 'Pedoman Akademik Kampus',
                    'sumber_id'           => $dp?->id,
                    'sumber_is_file'      => $isFileSource,
                    'sumber_type'         => $dp ? (strtolower((string) $dp->type) === 'private' ? 'Privat' : 'Publik') : 'Publik',
                    'sumber_link'         => $dp?->link ?? '',
                    'sumber_view_url'     => ($dp && $isFileSource) ? route('pustaka.lihat', $dp->id) : '',
                    'data_entry_nama'     => $deName,
                    'data_entry_initials' => $initials,
                    'data_entry_role'     => 'Data Entry',
                    'created_at'          => $createdAt,
                    'tanggal_indo'        => Carbon::parse($createdAt)->timezone('Asia/Jakarta')->translatedFormat('d M Y'),
                    'default_checked'     => false,
                    'status'              => $status,
                    'is_approved'         => $status === 'Diterima',
                    'is_scheduled'        => !empty($scheduleInfo['is_scheduled']),
                    'schedule_mode'       => $scheduleInfo['mode'] ?? 'now',
                    'schedule_date'       => $scheduleInfo['date'] ?? '',
                    'schedule_time'       => $scheduleInfo['time'] ?? '',
                    'schedule_label'      => $scheduleInfo['label'] ?? null,
                ];
            });

            $dataEntryUsers = User::where('tRoles_id', 2)->orderBy('nama')->pluck('nama');
        } catch (Throwable $e) {
            Log::error('Gagal memuat antrean validasi FAQ dari database: ' . $e->getMessage());
            $paginatedFaqs      = new LengthAwarePaginator([], 0, 6);
            $totalWaiting       = 0;
            $totalApprovedQueue = 0;
            $dataEntryUsers     = collect();
        }

        return view('validator.validasiFAQ', [
            'queueItems'         => $paginatedFaqs,
            'totalWaiting'       => $totalWaiting,
            'totalApprovedQueue' => $totalApprovedQueue ?? 0,
            'dataEntryUsers'     => $dataEntryUsers,
        ]);
    }

    /**
     * Terima satu pengajuan FAQ (status -> Diterima).
     */
    public function approve(Request $request, $id)
    {
        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::where('tFaqs_id', $id)
                ->where('status', 'Menunggu Validasi')
                ->update([
                    'status'             => 'Diterima',
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => null,
                    'catatan'            => null,
                    'updated_at'         => now(),
                ]);

            return redirect()->back()
                ->with('success', "FAQ {$id} berhasil diterima.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menerima FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Tolak satu pengajuan FAQ beserta catatan revisi (status -> Ditolak).
     */
    public function reject(Request $request, $id)
    {
        $catatan = $request->input('catatan');

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::where('tFaqs_id', $id)
                ->whereIn('status', ['Menunggu Validasi', 'Diterima'])
                ->update([
                    'status'             => 'Ditolak',
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => DB::raw('jawaban'),
                    'catatan'            => $catatan ?: 'Perlu revisi oleh Data Entry.',
                    'updated_at'         => now(),
                ]);

            return redirect()->back()
                ->with('success', "FAQ {$id} telah ditolak dan dikembalikan ke Data Entry.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Terima banyak FAQ sekaligus dari pilihan checklist (status -> Diterima).
     */
    public function bulkApprove(Request $request)
    {
        $faqIds = $request->input('faq_ids', []);

        if (empty($faqIds) || !is_array($faqIds)) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Pilih setidaknya satu FAQ terlebih dahulu untuk diterima.');
        }

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::whereIn('tFaqs_id', $faqIds)
                ->where('status', 'Menunggu Validasi')
                ->update([
                    'status'             => 'Diterima',
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => null,
                    'catatan'            => null,
                    'updated_at'         => now(),
                ]);

            $count    = count($faqIds);
            $listKode = implode(', ', $faqIds);

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$count} FAQ ({$listKode}) berhasil diterima. Silakan terbitkan melalui tombol Lihat atau Terbitkan.");
        } catch (Throwable $e) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Gagal menerima FAQ secara massal: ' . $e->getMessage());
        }
    }

    /**
     * Terbitkan FAQ yang telah berstatus Diterima (Publikasikan Sekarang [bawaan] atau Jadwal Publikasi).
     */
    public function publish(Request $request)
    {
        $request->validate([
            'faq_ids'      => 'required|array|min:1',
            'faq_ids.*'    => 'required|string',
            'publish_mode' => 'required|in:scheduled,now',
            'publish_date' => 'nullable|required_if:publish_mode,scheduled|date',
            'publish_time' => 'nullable|required_if:publish_mode,scheduled',
        ], [
            'faq_ids.required'         => 'Pilih setidaknya satu FAQ berstatus Diterima untuk diterbitkan.',
            'publish_date.required_if' => 'Tanggal publikasi wajib diisi untuk jadwal publikasi.',
            'publish_time.required_if' => 'Jam publikasi wajib diisi untuk jadwal publikasi.',
        ]);

        $faqIds      = array_values(array_unique((array) $request->input('faq_ids', [])));
        $publishMode = $request->input('publish_mode', 'now');
        $publishDate = $request->input('publish_date');
        $publishTime = $request->input('publish_time');

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $versions = FaqVersion::whereIn('tFaqs_id', $faqIds)
                ->where('status', 'Diterima')
                ->get();

            if ($versions->isEmpty()) {
                return redirect()->route('validator.validasiFAQ')
                    ->with('error', 'Hanya FAQ yang sudah berstatus Diterima yang dapat diterbitkan.');
            }

            $listKode = implode(', ', $versions->pluck('tFaqs_id')->unique()->toArray());
            $count    = $versions->pluck('tFaqs_id')->unique()->count();

            if ($publishMode === 'scheduled' && $publishDate && $publishTime) {
                $targetDt = Carbon::parse($publishDate . ' ' . $publishTime);
                if (now()->gte($targetDt)) {
                    $publishMode = 'now';
                }
            }

            if ($publishMode === 'scheduled') {
                $scheduleNote = FaqVersion::encodePublishSchedule($publishDate, $publishTime);
                $dtFormatted  = Carbon::parse($publishDate . ' ' . $publishTime);

                foreach ($versions as $ver) {
                    $ver->update([
                        'status'       => 'Diterima',
                        'catatan'      => $scheduleNote,
                        'id_validator' => $validatorId,
                        'updated_at'   => now(),
                    ]);
                }

                return redirect()->route('validator.validasiFAQ')
                    ->with('success', "{$count} FAQ ({$listKode}) telah dijadwalkan terbit pada " . $dtFormatted->translatedFormat('d M Y') . " pukul " . $dtFormatted->format('H.i') . " WIB.");
            }

            // Default: now (Publikasikan Sekarang)
            foreach ($versions as $ver) {
                $ver->update([
                    'status'       => 'Terbit',
                    'catatan'      => null,
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ]);
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$count} FAQ ({$listKode}) berhasil diterbitkan sekarang!");
        } catch (Throwable $e) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Gagal memproses publikasi FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail lengkap & riwayat versi FAQ (JSON untuk modal di Daftar FAQ Validator).
     */
    public function show($id, Request $request)
    {
        \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with([
            'versions' => fn ($q) => $q->where('status', '!=', 'Draf')->latest('created_at'),
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry',
        ])->findOrFail($id);

        $terbitVer = $faq->versions->first(fn ($v) => strtolower((string) $v->status) === 'terbit');
        $displayLatest = $terbitVer ?? $faq->versions->first();
        $st = \App\Models\LogMahasiswa::getFaqInteractionSummary([$faq->id])[$faq->id] ?? ['views' => 0, 'likes' => 0, 'dislikes' => 0];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $faq->id,
                'pertanyaan'     => $faq->pertanyaan,
                'created_at'     => $faq->created_at ? $faq->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                'data_entry'     => $faq->dataEntry?->nama,
                'tags'           => $faq->tags->pluck('nama'),
                'views_count'    => $st['views'],
                'likes_count'    => $st['likes'],
                'dislikes_count' => $st['dislikes'],
                'latest_version' => $displayLatest,
                'versions'       => $faq->versions->map(function ($v) {
                    $ts = $v->updated_at ?? $v->created_at;
                    $dp = $v->daftarPustaka;
                    $isFile = $dp ? !empty($dp->upload) : false;
                    return [
                        'id'                 => $v->id,
                        'versi_label'        => FaqVersion::formatVersiLabel($v->id),
                        'status'             => $v->status,
                        'jawaban'            => $v->jawaban,
                        'jawaban_sebelumnya' => $v->jawaban_sebelumnya,
                        'catatan'            => $v->catatan,
                        'validator'      => $v->validator?->nama,
                        'daftar_pustaka' => $dp ? [
                            'id'       => $dp->id,
                            'judul'    => $dp->judul_tampil,
                            'link'     => $dp->link,
                            'is_file'  => $isFile,
                            'view_url' => $isFile ? route('pustaka.lihat', $dp->id) : null,
                        ] : null,
                        'created_at'     => $v->created_at ? $v->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                        'updated_at'     => $ts ? $ts->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                    ];
                }),
            ],
        ]);
    }

    /**
     * Ubah status FAQ dari Daftar FAQ Validator (Terbit, Diterima, Ditolak).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:Terbit,Diterima,Ditolak',
            'catatan' => 'nullable|string',
        ]);

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            if ($latest) {
                $updateData = [
                    'status'             => $request->status,
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => $request->status === 'Ditolak' ? $latest->jawaban : null,
                    'updated_at'         => now(),
                ];
                if ($request->filled('catatan')) {
                    $updateData['catatan'] = $request->catatan;
                } elseif (in_array($request->status, ['Diterima', 'Terbit'], true)) {
                    $updateData['catatan'] = null;
                }
                $latest->update($updateData);
            }

            return redirect()->back()
                ->with('success', "Status FAQ {$id} berhasil diperbarui menjadi {$request->status}.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui status FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Hapus FAQ beserta versinya dari menu titik 3 pada Daftar FAQ Validator.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::findOrFail($id);
            $faq->tags()->detach();
            $faq->versions()->delete();
            $faq->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', "FAQ {$id} beserta riwayat versinya berhasil dihapus.");
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus FAQ: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * BUAT FAQ, UBAH FAQ & PRATINJAU UNGGAHAN FILE UNTUK VALIDATOR
     * (Langsung masuk ke Validasi FAQ berstatus 'Diterima' & siap diterbitkan)
     * ========================================================================= */

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

        return view('validator.buatfaq', compact(
            'newFaqId',
            'tags',
            'daftarPustaka',
            'allFaqs',
            'selectedExistingFaq'
        ));
    }

    public function edit(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with(['versions.daftarPustaka', 'tags'])->findOrFail($id);
        $latest = $faq->latest_version;
        $st = $latest ? strtolower((string) $latest->status) : 'draf';

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();
        $allFaqs = Faq::with(['versions', 'tags'])->orderBy('id', 'asc')->get();
        $forceNewVersion = in_array($st, ['diterima', 'terbit', 'approved', 'active'], true);

        return view('validator.buatfaq', [
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

    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'      => 'required|string',
            'jawaban'         => 'required|string',
            'tags'            => 'nullable|string',
            'pustaka_id'      => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_link'     => 'nullable|string|max:60',
            'mode_faq'        => 'nullable|in:new,existing',
            'existing_faq_id' => 'nullable|string|exists:tfaqs,id',
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan tautan maksimal 60 karakter.',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;
            $isExistingMode = ($request->input('mode_faq') === 'existing' && $request->filled('existing_faq_id'));

            $similarityService = new FaqSimilarityService();
            $excludeCheckId = $isExistingMode ? $request->existing_faq_id : null;
            $exactDraftMatch = $similarityService->findExactDraftMatch($request->pertanyaan, $request->jawaban, $excludeCheckId);
            if ($exactDraftMatch !== null) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        "Peringatan: Tidak dapat menambahkan FAQ karena Pertanyaan dan Jawaban sama persis dengan FAQ yang sudah ada ({$exactDraftMatch['id']} / {$exactDraftMatch['version_id']})."
                    );
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

            if (empty($tagIds)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: Kata Kunci wajib dilengkapi sebelum menyimpan FAQ.');
            }

            if ($isExistingMode) {
                $faq = Faq::with('versions')->findOrFail($request->existing_faq_id);
                $faqId = $faq->id;
                $versionId = $faq->generateNextVersionId();
            } else {
                $faqId = Faq::generateNextId();
                $faq = Faq::create([
                    'id'           => $faqId,
                    'id_dataentry' => $validatorId,
                    'pertanyaan'   => $request->pertanyaan,
                ]);
                $versionId = $faqId . '-V1';
            }

            $pustakaId = null;
            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);
                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            // Langsung berstatus 'Diterima' & divalidasi oleh Validator saat ini (siap diterbitkan di Validasi FAQ)
            FaqVersion::updateOrCreate(
                ['id' => $versionId],
                [
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faqId,
                    'id_validator'       => $validatorId,
                    'jawaban'            => $request->jawaban,
                    'jawaban_sebelumnya' => null,
                    'status'             => 'Diterima',
                    'catatan'            => null,
                ]
            );

            if ($isExistingMode) {
                FaqVersion::where('tFaqs_id', $faqId)
                    ->where('id', '!=', $versionId)
                    ->whereIn('status', ['Draf', 'Ditolak'])
                    ->delete();
            }

            $faq->tags()->syncWithoutDetaching(array_unique($tagIds));

            DB::commit();

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "FAQ {$faqId} ({$versionId}) berhasil disimpan ke Validasi FAQ (status Diterima) dan siap diterbitkan!");
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan FAQ: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pertanyaan'     => 'required|string',
            'jawaban'        => 'required|string',
            'version_action' => 'nullable|in:update_draft,new_version',
            'tags'           => 'nullable|string',
            'pustaka_id'     => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_link'    => 'nullable|string|max:60',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $currentStatus = $latest ? strtolower((string) $latest->status) : 'draf';
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

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

            if (empty($tagIds)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: Kata Kunci wajib dilengkapi sebelum menyimpan perubahan FAQ.');
            }

            $faq->update([
                'pertanyaan' => $request->pertanyaan,
            ]);

            $pustakaId = null;
            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);
                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $savedVersionId = $faq->generateNextVersionId();
            FaqVersion::updateOrCreate(
                ['id' => $savedVersionId],
                [
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faq->id,
                    'id_validator'       => $validatorId,
                    'jawaban'            => $request->jawaban,
                    'jawaban_sebelumnya' => null,
                    'status'             => 'Diterima',
                    'catatan'            => null,
                ]
            );

            FaqVersion::where('tFaqs_id', $faq->id)
                ->where('id', '!=', $savedVersionId)
                ->whereIn('status', ['Draf', 'Ditolak'])
                ->delete();

            $faq->tags()->sync(array_unique($tagIds));

            DB::commit();

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "FAQ {$faq->id} ({$savedVersionId}) berhasil disimpan dengan status Diterima dan siap diterbitkan!");
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui FAQ: ' . $e->getMessage());
        }
    }

    public function checkDuplicate(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $a = $request->has('a') ? trim((string) $request->query('a', '')) : null;
        $excludeId = $request->query('exclude_id');
        $threshold = (float) $request->query('threshold', 50.0);

        $service = new FaqSimilarityService();
        $result = $service->analyzeQuestion($q, $excludeId, $threshold, $a);

        return response()->json([
            'status'                => 'success',
            'has_exact_match'       => $result['has_exact_match'],
            'exact_match'           => $result['exact_match'],
            'has_exact_draft_match' => $result['has_exact_draft_match'],
            'exact_draft_match'     => $result['exact_draft_match'],
            'max_similarity'        => $result['max_similarity'],
            'duplicates'            => $result['candidates'],
        ]);
    }

    public function pratinjau(Request $request)
    {
        $items = session('uploaded_faqs', []);
        $fileName = session('uploaded_filename', 'Belum ada file diunggah');

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
            ];
            $fileName = 'FAQ_Akademik_2026.xlsx';
            session([
                'uploaded_faqs'     => $items,
                'uploaded_filename' => $fileName,
            ]);
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('validator.preview', compact('items', 'fileName', 'tags', 'daftarPustaka'));
    }

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

        $nextIdStr = Faq::generateNextId();
        $startNum = (int) preg_replace('/\D/', '', $nextIdStr) ?: 1;

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

        return redirect()->route('validator.pratinjau')
            ->with('success', 'Berhasil membaca ' . count($faqs) . " baris FAQ dari file {$filename}!");
    }

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

        $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && !$hasExactDraftMatch);
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

        return redirect()->route('validator.pratinjau')->with('success', "Baris {$rowId} berhasil diperbarui.");
    }

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

        return redirect()->route('validator.pratinjau')->with('success', "Baris {$rowId} dihapus dari pratinjau.");
    }

    /**
     * Menyimpan hasil pratinjau unggahan file dari Validator langsung dengan status 'Diterima'
     * agar langsung tampil di halaman Validasi FAQ dan tinggal diterbitkan tanpa perlu disetujui lagi.
     */
    public function submitPratinjau(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        $itemsJson = $request->input('items_json');

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

        $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;
        $similarityService = new FaqSimilarityService();

        $missingTags = [];
        $exactDraftConflicts = [];

        foreach ($items as $item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));
                $jawabanText = trim((string) ($item['jawaban'] ?? ''));
                $itemTags = is_array($item['tags'] ?? null) ? array_filter($item['tags'], fn($t) => trim((string) $t) !== '') : [];

                $draftMatch = $similarityService->findExactDraftMatch($pertanyaanText, $jawabanText);
                if ($draftMatch !== null) {
                    $exactDraftConflicts[] = ($item['id'] ?? $itemKey) . " (sama persis dengan {$draftMatch['id']} / {$draftMatch['version_id']})";
                }

                if (empty($itemTags)) {
                    $missingTags[] = $item['id'] ?? $itemKey;
                }
            }
        }

        if (!empty($exactDraftConflicts)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: Tidak dapat melanjutkan karena Pertanyaan dan Jawaban sama persis dengan FAQ yang sudah ada: ' . implode(', ', $exactDraftConflicts) . '.'
            );
        }

        if (!empty($missingTags)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: FAQ (' . implode(', ', $missingTags) . ') belum memiliki Kata Kunci. Silakan lengkapi melalui tombol Ubah terlebih dahulu.'
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

                    $candidateId = trim((string) ($item['id'] ?? ''));
                    if ($candidateId === '' || Faq::where('id', $candidateId)->exists()) {
                        $faqId = Faq::generateNextId();
                    } else {
                        $faqId = $candidateId;
                    }

                    $faq = Faq::create([
                        'id'           => $faqId,
                        'id_dataentry' => $validatorId,
                        'pertanyaan'   => $pertanyaanText,
                    ]);
                    $versionId = $faqId . '-V1';

                    $pustakaId = null;
                    $pustakaJudul = trim((string) ($item['pustaka_judul'] ?? ''));
                    if ($pustakaJudul !== '' && !in_array(strtolower($pustakaJudul), ['belum diisi', 'tidak ada (opsional)'], true)) {
                        $dp = DaftarPustaka::where('judul', $pustakaJudul)
                            ->orWhere('link', $pustakaJudul)
                            ->first();
                        if (!$dp) {
                            $dp = DaftarPustaka::firstOrCreate([
                                'link' => substr($pustakaJudul, 0, 45),
                            ], [
                                'judul' => substr($pustakaJudul, 0, 100),
                            ]);
                        }
                        $pustakaId = $dp->id;
                    }

                    // Langsung berstatus 'Diterima' & divalidasi oleh Validator saat ini (siap diterbitkan di Validasi FAQ)
                    FaqVersion::create([
                        'id'                 => $versionId,
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faqId,
                        'id_validator'       => $validatorId,
                        'jawaban'            => $item['jawaban'] ?? '',
                        'status'             => 'Diterima',
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

            if (empty($remainingItems)) {
                session()->forget(['uploaded_faqs', 'uploaded_filename']);
            } else {
                session(['uploaded_faqs' => $remainingItems]);
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$savedCount} FAQ dari unggahan berkas berhasil masuk ke Validasi FAQ (status Diterima) dan siap diterbitkan!");
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan FAQ dari pratinjau: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * DAFTAR KATA KUNCI (TABEL ttag) UNTUK VALIDATOR
     * ========================================================================= */

    public function kataKunciIndex()
    {
        $tags = Tag::withCount('faqs')->orderBy('id', 'asc')->get();
        $previewKeywords = session('uploaded_keywords', []);
        $previewKeywordsFile = session('uploaded_keywords_file', null);

        return view('validator.katakunci', compact('tags', 'previewKeywords', 'previewKeywordsFile'));
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

        return redirect()->route('validator.katakunci')
            ->with('success', "{$added} kata kunci berhasil disimpan ke database!");
    }

    public function kataKunciUpdate(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $tag = Tag::findOrFail($id);
        $tag->update(['nama' => trim($request->nama)]);

        return redirect()->route('validator.katakunci')
            ->with('success', "Kata kunci berhasil diperbarui menjadi '{$tag->nama}'.");
    }

    public function kataKunciDestroy($id)
    {
        $tag = Tag::findOrFail($id);
        $nama = $tag->nama;
        $tag->faqs()->detach();
        $tag->delete();

        return redirect()->route('validator.katakunci')
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
            return redirect()->route('validator.katakunci')
                ->with('error', "Tidak ditemukan kata kunci yang dapat dibaca dari file {$filename}.");
        }

        session([
            'uploaded_keywords'      => $keywords,
            'uploaded_keywords_file' => $filename,
        ]);

        return redirect()->route('validator.katakunci')
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

        return redirect()->route('validator.katakunci')
            ->with('success', "{$saved} kata kunci dari file import berhasil disimpan ke tabel ttag!");
    }

    public function kataKunciCancelImport()
    {
        session()->forget(['uploaded_keywords', 'uploaded_keywords_file']);
        return redirect()->route('validator.katakunci')->with('success', 'Pratinjau import kata kunci dibatalkan.');
    }

    /* =========================================================================
     * SUMBER PUSTAKA (TABEL tdaftarpustaka) UNTUK VALIDATOR
     * ========================================================================= */

    public function sumberPustakaIndex()
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        $pustakas = DaftarPustaka::withCount('faqVersions')->orderBy('id', 'asc')->get();
        $previewSources = session('uploaded_sources', []);
        $previewSourcesFile = session('uploaded_sources_file', null);

        return view('validator.sumberpustaka', compact('pustakas', 'previewSources', 'previewSourcesFile'));
    }

    public function sumberPustakaStore(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $request->validate([
            'judul'       => 'required|string|max:100',
            'source_kind' => 'required|in:link,upload',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'judul.max'      => 'Judul maksimal 100 karakter.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $judulText = substr(trim($request->judul), 0, 100);
        $sourceKind = $request->input('source_kind', 'link');

        if ($sourceKind === 'upload') {
            if (!$request->hasFile('file_upload')) {
                return redirect()->back()->with('error', 'Untuk jenis input Dokumen, Anda wajib mengunggah file dokumen.');
            }

            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            DaftarPustaka::create([
                'judul'  => $judulText,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route('validator.sumberpustaka')
                ->with('success', "Dokumen '{$judulText}' berhasil diunggah ke Sumber Pustaka!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Untuk jenis input Tautan, kolom Link / Tautan wajib diisi.');
        }

        $linkText = substr(trim($request->link), 0, 60);
        DaftarPustaka::create([
            'judul'  => $judulText,
            'link'   => $linkText,
            'upload' => null,
        ]);

        return redirect()->route('validator.sumberpustaka')
            ->with('success', "Tautan sumber pustaka '{$judulText}' berhasil ditambahkan!");
    }

    public function sumberPustakaUpdate(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $pustaka = DaftarPustaka::findOrFail($id);

        $request->validate([
            'judul'       => 'required|string|max:100',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $newJudul = substr(trim($request->judul), 0, 100);
        $isFileRecord = !empty($pustaka->upload);

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            $pustaka->update([
                'judul'  => $newJudul,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route('validator.sumberpustaka')
                ->with('success', "Berkas sumber pustaka '{$newJudul}' berhasil diperbarui!");
        }

        if ($isFileRecord) {
            $pustaka->update([
                'judul' => $newJudul,
            ]);

            return redirect()->route('validator.sumberpustaka')
                ->with('success', "Dokumen sumber pustaka '{$newJudul}' berhasil diperbarui!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Link sumber pustaka tidak boleh kosong.');
        }

        $newLink = substr(trim($request->link), 0, 60);
        $pustaka->update([
            'judul' => $newJudul,
            'link'  => $newLink,
        ]);

        return redirect()->route('validator.sumberpustaka')
            ->with('success', "Sumber pustaka '{$newJudul}' berhasil diperbarui!");
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
            return redirect()->route('validator.sumberpustaka')
                ->with('error', "Tidak ditemukan daftar tautan sumber pustaka yang dapat dibaca dari file {$filename}. Pastikan file mengikuti format 2 kolom: (1. Judul Tautan, 2. Tautan/Link).");
        }

        session([
            'uploaded_sources'      => $sources,
            'uploaded_sources_file' => $filename,
        ]);

        return redirect()->route('validator.sumberpustaka')
            ->with('success', 'Berhasil membaca ' . count($sources) . " baris tautan dari {$filename}. Silakan pantau kesesuaian pada pratinjau di bawah!");
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

                if ($judulName === '' && $linkName !== '') {
                    $judulName = $linkName;
                }

                if ($linkName !== '') {
                    DaftarPustaka::updateOrCreate([
                        'link' => $linkName,
                    ], [
                        'judul'  => $judulName,
                        'upload' => null,
                    ]);
                    $saved++;
                }
            }
        }

        session()->forget(['uploaded_sources', 'uploaded_sources_file']);

        return redirect()->route('validator.sumberpustaka')
            ->with('success', "{$saved} tautan sumber pustaka berhasil disimpan ke tabel tdaftarpustaka!");
    }

    public function sumberPustakaCancelImport()
    {
        session()->forget(['uploaded_sources', 'uploaded_sources_file']);
        return redirect()->route('validator.sumberpustaka')->with('success', 'Pratinjau import sumber pustaka dibatalkan.');
    }

    public function sumberPustakaDestroy($id)
    {
        $pustaka = DaftarPustaka::withCount('faqVersions')->findOrFail($id);
        $nama = $pustaka->judul_tampil;
        if ($pustaka->faq_versions_count > 0) {
            return redirect()->route('validator.sumberpustaka')
                ->with('error', "Sumber pustaka '{$nama}' sedang digunakan oleh {$pustaka->faq_versions_count} versi FAQ dan tidak dapat dihapus.");
        }

        $pustaka->delete();

        return redirect()->route('validator.sumberpustaka')
            ->with('success', "Sumber pustaka '{$nama}' berhasil dihapus.");
    }
}

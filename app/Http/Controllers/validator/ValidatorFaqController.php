<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
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

        return view('validator.daftarFAQ', compact(
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
                'versions' => fn ($q) => $q->latest('created_at'),
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

                return (object) [
                    'id'                  => $faq->id,
                    'kode'                => $faq->id,
                    'versi'               => FaqVersion::formatVersiLabel($targetVersion?->id) ?: ('Versi ' . $versionNumber),
                    'pertanyaan'          => $faq->pertanyaan,
                    'kategori'            => $tagNames ?: 'Belum ada kata kunci',
                    'tags_list'           => $tagsArray,
                    'jawaban'             => $targetVersion?->jawaban ?? '-',
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
                    'status'       => 'Diterima',
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
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
                    'status'       => 'Ditolak',
                    'id_validator' => $validatorId,
                    'catatan'      => $catatan ?: 'Perlu revisi oleh Data Entry.',
                    'updated_at'   => now(),
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
                    'status'       => 'Diterima',
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
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
                        'id'             => $v->id,
                        'versi_label'    => FaqVersion::formatVersiLabel($v->id),
                        'status'         => $v->status,
                        'jawaban'        => $v->jawaban,
                        'catatan'        => $v->catatan,
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
                    'status'       => $request->status,
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ];
                if ($request->filled('catatan')) {
                    $updateData['catatan'] = $request->catatan;
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
}

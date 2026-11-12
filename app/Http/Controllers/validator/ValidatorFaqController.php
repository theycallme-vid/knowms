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
     * Menampilkan Dashboard Utama Validator (/validator/dashboard).
     */
    public function dashboard(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing('role');
        }

        try {
            FaqVersion::processDueScheduledPublications();

            $queueFaqs = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('created_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Submitted', 'submitted']);
            })->latest('updated_at')->get();

            $submittedCount = $queueFaqs->count();
            $approvedCount  = FaqVersion::whereIn('status', ['Approved', 'approved', 'Active'])->distinct('tFaqs_id')->count('tFaqs_id');
            $rejectedCount  = FaqVersion::whereIn('status', ['Rejected', 'rejected'])->distinct('tFaqs_id')->count('tFaqs_id');

            // Pilih FAQ yang sedang aktif ditampilkan di panel kanan dashboard
            $selectedId  = $request->query('faq_id');
            $selectedFaq = $selectedId
                ? $queueFaqs->firstWhere('id', $selectedId)
                : $queueFaqs->first();

        } catch (Throwable $e) {
            $queueFaqs      = collect();
            $submittedCount = 0;
            $approvedCount  = 0;
            $rejectedCount  = 0;
            $selectedFaq    = null;
        }

        return view('validator.dashboard', compact(
            'queueFaqs',
            'selectedFaq',
            'submittedCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Daftar semua FAQ dengan filter opsional berdasarkan fakultas/kategori, pencarian, dan status (/validator/daftar-faq).
     */
    public function index(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing('role');
        }

        $fakultasOptions = [
            'Umum',
            'Teknik',
            'Ekonomi & Bisnis',
            'Hukum',
            'Ilmu Sosial',
            'Kedokteran',
            'Sains & Teknologi',
            'Ilmu Komputer',
        ];

        try {
            FaqVersion::processDueScheduledPublications();

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('updated_at'),
                'versions.daftarPustaka',
                'versions.validator',
            ]);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhere('pertanyaan', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('fakultas') && $request->fakultas !== 'Semua') {
                $query->whereHas('tags', fn ($q) => $q->where('nama', 'like', '%' . $request->fakultas . '%'));
            }

            if ($request->filled('status') && $request->status !== 'Semua') {
                $query->whereHas('versions', fn ($q) => $q->where('status', $request->status));
            }

            $sort = $request->get('sort', 'terbaru');
            match ($sort) {
                'terlama' => $query->oldest(),
                'az'      => $query->orderBy('pertanyaan'),
                default   => $query->latest(),
            };

            $faqs = $query->paginate(6)->withQueryString();

            $aktivFakultas = $request->get('fakultas', 'Semua');

            $statBase = Faq::query();
            if ($aktivFakultas && $aktivFakultas !== 'Semua') {
                $statBase->whereHas('tags', fn ($q) => $q->where('nama', 'like', '%' . $aktivFakultas . '%'));
            }
            $faqIds = $statBase->pluck('id');

            $totalFaq       = $faqIds->count();
            $totalAktif     = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Active')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalApproved  = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Approved')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalSubmitted = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'Submitted')->distinct('tFaqs_id')->count('tFaqs_id');
            $totalInaktif   = FaqVersion::whereIn('tFaqs_id', $faqIds)->where('status', 'InActive')->distinct('tFaqs_id')->count('tFaqs_id');
        } catch (Throwable $e) {
            Log::warning('Gagal memuat Daftar FAQ dari DB: ' . $e->getMessage());
            $faqs           = new LengthAwarePaginator([], 0, 6);
            $totalFaq       = 0;
            $totalAktif     = 0;
            $totalApproved  = 0;
            $totalSubmitted = 0;
            $totalInaktif   = 0;
        }

        return view('validator.daftarFAQ', compact(
            'faqs',
            'fakultasOptions',
            'totalFaq',
            'totalAktif',
            'totalApproved',
            'totalSubmitted',
            'totalInaktif',
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
            FaqVersion::processDueScheduledPublications();

            $totalWaiting = Faq::whereHas('versions', function ($q) {
                $q->whereIn('status', ['Submitted', 'submitted']);
            })->count();

            $totalApprovedQueue = Faq::whereHas('versions', function ($q) {
                $q->whereIn('status', ['Approved', 'approved']);
            })->count();

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('created_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Submitted', 'submitted', 'Approved', 'approved']);
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
                $targetVersion = $faq->versions->first(fn ($v) => in_array(strtolower($v->status), ['submitted', 'approved'], true))
                    ?? $faq->versions->first();
                $versionNumber    = max(1, $faq->versions->count());
                $deName           = $faq->dataEntry?->nama ?? 'Data Entry';
                $words            = explode(' ', trim($deName));
                $initials         = count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($deName, 0, 2));

                $tagNames     = $faq->tags->pluck('nama')->implode(' · ');
                $createdAt    = $faq->created_at ?? $targetVersion?->created_at ?? now();
                $rawStatus    = strtolower($targetVersion?->status ?? 'submitted');
                $status       = $rawStatus === 'approved' ? 'Approved' : 'Submitted';
                $scheduleInfo = $targetVersion?->getPublishScheduleInfo();

                return (object) [
                    'id'                  => $faq->id,
                    'kode'                => $faq->id,
                    'versi'               => 'Versi ' . $versionNumber,
                    'pertanyaan'          => $faq->pertanyaan,
                    'kategori'            => $tagNames ?: 'Akademik · Layanan Mahasiswa',
                    'jawaban'             => $targetVersion?->jawaban ?? '-',
                    'sumber'              => $targetVersion?->daftarPustaka?->link ?? 'Pedoman Akademik Kampus',
                    'data_entry_nama'     => $deName,
                    'data_entry_initials' => $initials,
                    'data_entry_role'     => 'Data Entry',
                    'created_at'          => $createdAt,
                    'tanggal_indo'        => Carbon::parse($createdAt)->translatedFormat('d M Y'),
                    'default_checked'     => false,
                    'status'              => $status,
                    'is_approved'         => $status === 'Approved',
                    'is_scheduled'        => !empty($scheduleInfo['is_scheduled']),
                    'schedule_mode'       => $scheduleInfo['mode'] ?? null,
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
     * Setujui (Approved) satu pengajuan FAQ.
     */
    public function approve(Request $request, $id)
    {
        try {
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::where('tFaqs_id', $id)
                ->whereIn('status', ['Submitted', 'submitted'])
                ->update([
                    'status'       => 'Approved',
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ]);

            return redirect()->back()
                ->with('success', "FAQ {$id} berhasil disetujui (Approved).");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyetujui FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Tolak (Rejected) satu pengajuan FAQ beserta catatan revisi.
     */
    public function reject(Request $request, $id)
    {
        $catatan = $request->input('catatan');

        try {
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::where('tFaqs_id', $id)
                ->whereIn('status', ['Submitted', 'submitted', 'Approved', 'approved'])
                ->update([
                    'status'       => 'Rejected',
                    'id_validator' => $validatorId,
                    'catatan'      => $catatan ?: 'Perlu revisi oleh Data Entry.',
                    'updated_at'   => now(),
                ]);

            return redirect()->back()
                ->with('success', "FAQ {$id} telah ditolak (Rejected) dan dikembalikan ke Data Entry.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Setujui (Approved) banyak FAQ sekaligus dari checklist.
     */
    public function bulkApprove(Request $request)
    {
        $faqIds = $request->input('faq_ids', []);

        if (empty($faqIds) || !is_array($faqIds)) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Pilih setidaknya satu FAQ terlebih dahulu untuk disetujui.');
        }

        try {
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            FaqVersion::whereIn('tFaqs_id', $faqIds)
                ->whereIn('status', ['Submitted', 'submitted'])
                ->update([
                    'status'       => 'Approved',
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ]);

            $count    = count($faqIds);
            $listKode = implode(', ', $faqIds);

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$count} FAQ ({$listKode}) berhasil disetujui (Approved). Silakan pilih Publish untuk menerbitkan.");
        } catch (Throwable $e) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Gagal menyetujui FAQ secara massal: ' . $e->getMessage());
        }
    }

    /**
     * Publikasikan FAQ yang telah berstatus Approved (Publikasi Rutin, Jadwalkan Publikasi, atau Publikasikan Sekarang).
     */
    public function publish(Request $request)
    {
        $request->validate([
            'faq_ids'      => 'required|array|min:1',
            'faq_ids.*'    => 'required|string',
            'publish_mode' => 'required|in:routine,scheduled,now',
            'publish_date' => 'nullable|required_if:publish_mode,scheduled|date',
            'publish_time' => 'nullable|required_if:publish_mode,scheduled',
        ], [
            'faq_ids.required'         => 'Pilih setidaknya satu FAQ berstatus Approved untuk dipublikasikan.',
            'publish_date.required_if' => 'Tanggal publikasi wajib diisi untuk publikasi terjadwal.',
            'publish_time.required_if' => 'Jam publikasi wajib diisi untuk publikasi terjadwal.',
        ]);

        $faqIds      = array_values(array_unique((array) $request->input('faq_ids', [])));
        $publishMode = $request->input('publish_mode', 'routine');
        $publishDate = $request->input('publish_date');
        $publishTime = $request->input('publish_time');

        try {
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $versions = FaqVersion::whereIn('tFaqs_id', $faqIds)
                ->whereIn('status', ['Approved', 'approved'])
                ->get();

            if ($versions->isEmpty()) {
                return redirect()->route('validator.validasiFAQ')
                    ->with('error', 'Hanya FAQ yang sudah berstatus Approved yang dapat dipublikasikan.');
            }

            $listKode = implode(', ', $versions->pluck('tFaqs_id')->unique()->toArray());
            $count    = $versions->pluck('tFaqs_id')->unique()->count();

            // Jika memilih Jadwalkan Publikasi namun waktunya sudah lewat/sama dengan sekarang, langsung aktifkan
            if ($publishMode === 'scheduled' && $publishDate && $publishTime) {
                $targetDt = Carbon::parse($publishDate . ' ' . $publishTime);
                if (now()->gte($targetDt)) {
                    $publishMode = 'now';
                }
            }

            if ($publishMode === 'now') {
                foreach ($versions as $ver) {
                    // Nonaktifkan versi Active sebelumnya pada FAQ yang sama
                    FaqVersion::where('tFaqs_id', $ver->tFaqs_id)
                        ->where('id', '!=', $ver->id)
                        ->whereIn('status', ['Active', 'active'])
                        ->update([
                            'status'     => 'InActive',
                            'updated_at' => now(),
                        ]);

                    $ver->update([
                        'status'       => 'Active',
                        'catatan'      => null,
                        'id_validator' => $validatorId,
                        'updated_at'   => now(),
                    ]);
                }

                return redirect()->route('validator.validasiFAQ')
                    ->with('success', "{$count} FAQ ({$listKode}) berhasil dipublikasikan sekarang dan statusnya telah berubah menjadi Active!");
            }

            if ($publishMode === 'scheduled') {
                $scheduleNote = FaqVersion::encodePublishSchedule('scheduled', $publishDate, $publishTime);
                $dtFormatted  = Carbon::parse($publishDate . ' ' . $publishTime);

                foreach ($versions as $ver) {
                    $ver->update([
                        'status'       => 'Approved',
                        'catatan'      => $scheduleNote,
                        'id_validator' => $validatorId,
                        'updated_at'   => now(),
                    ]);
                }

                return redirect()->route('validator.validasiFAQ')
                    ->with('success', "{$count} FAQ ({$listKode}) telah dijadwalkan untuk publikasi pada " . $dtFormatted->translatedFormat('d M Y') . " pukul " . $dtFormatted->format('H.i') . " WIB.");
            }

            // Default: routine (Publikasi Rutin)
            $scheduleNote = FaqVersion::encodePublishSchedule('routine');
            foreach ($versions as $ver) {
                $ver->update([
                    'status'       => 'Approved',
                    'catatan'      => $scheduleNote,
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ]);
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$count} FAQ ({$listKode}) telah masuk antrean Publikasi Rutin (menunggu jam 11 / 23.00–01.00 WIB).");
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
        $faq = Faq::with([
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry',
        ])->findOrFail($id);

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

    /**
     * Ubah status FAQ dari menu titik 3 di Daftar FAQ Validator (Active, Approved, InActive, Rejected).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:Active,Approved,InActive,Rejected',
            'catatan' => 'nullable|string',
        ]);

        try {
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

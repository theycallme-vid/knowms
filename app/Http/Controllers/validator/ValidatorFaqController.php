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
            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('updated_at'),
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
            $totalWaiting = Faq::whereHas('versions', function ($q) {
                $q->whereIn('status', ['Submitted', 'submitted']);
            })->count();

            $query = Faq::with([
                'dataEntry',
                'tags',
                'versions' => fn ($q) => $q->latest('created_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Submitted', 'submitted']);
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
                $submittedVersion = $faq->versions->first(fn ($v) => strtolower($v->status) === 'submitted')
                    ?? $faq->versions->first();
                $versionNumber    = max(1, $faq->versions->count());
                $deName           = $faq->dataEntry?->nama ?? 'Data Entry';
                $words            = explode(' ', trim($deName));
                $initials         = count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($deName, 0, 2));

                $tagNames  = $faq->tags->pluck('nama')->implode(' · ');
                $createdAt = $faq->created_at ?? $submittedVersion?->created_at ?? now();

                return (object) [
                    'id'                  => $faq->id,
                    'kode'                => $faq->id,
                    'versi'               => 'Versi ' . $versionNumber,
                    'pertanyaan'          => $faq->pertanyaan,
                    'kategori'            => $tagNames ?: 'Akademik · Layanan Mahasiswa',
                    'jawaban'             => $submittedVersion?->jawaban ?? '-',
                    'sumber'              => $submittedVersion?->daftarPustaka?->link ?? 'Pedoman Akademik Kampus',
                    'data_entry_nama'     => $deName,
                    'data_entry_initials' => $initials,
                    'data_entry_role'     => 'Data Entry',
                    'created_at'          => $createdAt,
                    'tanggal_indo'        => Carbon::parse($createdAt)->translatedFormat('d M Y'),
                    'default_checked'     => false,
                ];
            });

            $dataEntryUsers = User::where('tRoles_id', 2)->orderBy('nama')->pluck('nama');
        } catch (Throwable $e) {
            Log::error('Gagal memuat antrean validasi FAQ dari database: ' . $e->getMessage());
            $paginatedFaqs  = new LengthAwarePaginator([], 0, 6);
            $totalWaiting   = 0;
            $dataEntryUsers = collect();
        }

        return view('validator.validasiFAQ', [
            'queueItems'     => $paginatedFaqs,
            'totalWaiting'   => $totalWaiting,
            'dataEntryUsers' => $dataEntryUsers,
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
                ->whereIn('status', ['Submitted', 'submitted'])
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
                ->with('success', "{$count} FAQ ({$listKode}) berhasil disetujui (Approved) secara massal.");
        } catch (Throwable $e) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Gagal menyetujui FAQ secara massal: ' . $e->getMessage());
        }
    }
}

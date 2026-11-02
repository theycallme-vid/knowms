<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqVersion;
use Illuminate\Http\Request;

class ValidatorFaqController extends Controller
{
    /**
     * Daftar semua FAQ dengan filter opsional berdasarkan fakultas/kategori, pencarian, dan status.
     */
    public function index(Request $request)
    {
        // Pastikan relasi role ter-load supaya getRoleName() bekerja di blade
        $user = auth()->user()->loadMissing('role');
        // Daftar fakultas / direktorat yang tersedia sebagai opsi filter
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

        $query = Faq::with([
            'dataEntry',
            'tags',
            'versions' => fn ($q) => $q->latest('updated_at'),
        ]);

        // Filter pencarian pertanyaan
        if ($request->filled('search')) {
            $query->where('pertanyaan', 'like', '%' . $request->search . '%');
        }

        // Filter fakultas via tag (nama tag mengandung nama fakultas)
        if ($request->filled('fakultas') && $request->fakultas !== 'Semua') {
            $query->whereHas('tags', fn ($q) => $q->where('nama', 'like', '%' . $request->fakultas . '%'));
        }

        // Filter status versi terbaru
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->whereHas('versions', fn ($q) => $q->where('status', $request->status));
        }

        // Urutkan
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'terlama'  => $query->oldest(),
            'az'       => $query->orderBy('pertanyaan'),
            default    => $query->latest(),
        };

        $faqs = $query->paginate(6)->withQueryString();

        // ── Statistik: ikut filter fakultas yang aktif ──────────────────
        $aktivFakultas = $request->get('fakultas', 'Semua');

        // Base query ID FAQ yang masuk filter fakultas
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
}


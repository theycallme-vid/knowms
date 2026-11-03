<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ValidatorFaqController extends Controller
{
    /**
     * Menampilkan dashboard Validator beserta daftar FAQ yang diajukan oleh Data Entry.
     */
    public function dashboard(Request $request)
    {
        $allFaqs = Faq::with([
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry'
        ])->orderBy('updated_at', 'desc')->get();

        // Filter FAQ yang memiliki versi submitted, approved, atau rejected
        $validatorFaqs = $allFaqs->filter(function ($faq) {
            $latest = $faq->latest_version;
            if (!$latest) {
                return false;
            }
            return in_array(strtolower($latest->status), ['submitted', 'approved', 'rejected']);
        })->values();

        $submittedCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;

        foreach ($validatorFaqs as $faq) {
            $st = strtolower($faq->latest_version->status ?? '');
            if ($st === 'submitted') {
                $submittedCount++;
            } elseif ($st === 'approved') {
                $approvedCount++;
            } elseif ($st === 'rejected') {
                $rejectedCount++;
            }
        }

        $total = $validatorFaqs->count();
        $tags = Tag::orderBy('nama', 'asc')->get();

        return view('validator.dashboard', compact(
            'validatorFaqs',
            'total',
            'submittedCount',
            'approvedCount',
            'rejectedCount',
            'tags'
        ));
    }

    /**
     * Menyetujui (Approve / Terbitkan) versi FAQ yang diajukan oleh Data Entry.
     */
    public function approve(Request $request, $faqId)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($faqId);
            $latest = $faq->latest_version;

            if (!$latest) {
                return redirect()->back()->with('error', 'Versi FAQ tidak ditemukan.');
            }

            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $latest->update([
                'status'       => 'approved',
                'id_validator' => $validatorId,
                'catatan'      => $request->filled('catatan') ? trim($request->catatan) : null,
            ]);

            DB::commit();

            return redirect()->route('validator.dashboard')
                ->with('success', "FAQ {$faq->id} ({$latest->id}) telah disetujui dan resmi diterbitkan!");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyetujui FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menolak / Meminta Revisi (Reject) versi FAQ dengan catatan revisi untuk Data Entry.
     */
    public function reject(Request $request, $faqId)
    {
        $request->validate([
            'catatan' => 'required|string|min:3',
        ], [
            'catatan.required' => 'Catatan revisi wajib diisi agar Data Entry mengetahui bagian yang perlu diperbaiki.',
        ]);

        try {
            DB::beginTransaction();

            $faq = Faq::with('versions')->findOrFail($faqId);
            $latest = $faq->latest_version;

            if (!$latest) {
                return redirect()->back()->with('error', 'Versi FAQ tidak ditemukan.');
            }

            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $latest->update([
                'status'       => 'rejected',
                'id_validator' => $validatorId,
                'catatan'      => trim($request->catatan),
            ]);

            DB::commit();

            return redirect()->route('validator.dashboard')
                ->with('success', "FAQ {$faq->id} dikembalikan ke Data Entry dengan status Perlu Revisi.");

        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim catatan revisi: ' . $e->getMessage());
        }
    }
}


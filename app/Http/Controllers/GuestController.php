<?php

namespace App\Http\Controllers;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Session as TSession;
use Illuminate\Http\Request;
use Throwable;

class GuestController extends Controller
{
    /**
     * Menampilkan halaman FAQ untuk Tamu / Guest (tanpa input NRP & tanpa scan KTM)
     */
    public function index(Request $request)
    {
        // Pastikan ada record sesi di tabel tsession untuk melacak aktivitas guest
        $tsessionId = session('tsession_id');
        if (!$tsessionId) {
            try {
                $sessionRecord = new TSession();
                $sessionRecord->kode_fakultas = null;
                $sessionRecord->created_at = now();
                $sessionRecord->updated_at = now();
                $sessionRecord->save();

                session([
                    'tsession_id'   => $sessionRecord->id,
                    'kode_fakultas' => null,
                    'nama_fakultas' => 'Guest',
                    'is_guest'      => true,
                ]);
            } catch (Throwable $e) {
                // Abaikan jika database terkendala agar halaman tetap bisa diakses
            }
        }

        $publishedFaqs = collect();
        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::processDueScheduledPublications();

            $publishedFaqs = Faq::with([
                'tags',
                'versions' => fn ($q) => $q->whereIn('status', ['Active', 'active'])->latest('updated_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Active', 'active']);
            })->orderBy('created_at', 'desc')->get();
        } catch (Throwable $e) {
            $publishedFaqs = collect();
        }

        return view('guest.faq', [
            'publishedFaqs' => $publishedFaqs,
        ]);
    }

    /**
     * Mengakhiri sesi Guest (saat idle timeout atau klik "Tidak, Akhiri Sesi")
     */
    public function endSession(Request $request)
    {
        $tsessionId = session('tsession_id');

        if ($tsessionId) {
            try {
                $sessionRecord = TSession::find($tsessionId);
                if ($sessionRecord) {
                    $sessionRecord->delete(); // Soft delete sesuai model TSession
                }
            } catch (Throwable $e) {
                // Silently handle
            }
        }

        // Hapus session guest dari browser
        $request->session()->forget([
            'tsession_id',
            'kode_fakultas',
            'nama_fakultas',
            'is_guest',
        ]);

        return redirect()->route('portal');
    }
}

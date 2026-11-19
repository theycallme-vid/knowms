<?php

namespace App\Http\Controllers;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Session as TSession;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $settings = Setting::allIntValues();

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::processDueScheduledPublications();

            $faqs = Faq::with([
                'tags',
                'versions' => fn ($q) => $q->whereIn('status', ['Active', 'active'])->latest('updated_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->whereIn('status', ['Active', 'active']);
            })->orderBy('created_at', 'desc')->get();

            $logStats = DB::table('tlogmahasiswa')
                ->leftJoin('tsession', 'tlogmahasiswa.tSession_id', '=', 'tsession.id')
                ->whereNotNull('tlogmahasiswa.tFaqs_id')
                ->select(
                    'tlogmahasiswa.tFaqs_id as faq_id',
                    'tsession.kode_fakultas as session_key',
                    'tlogmahasiswa.jenis_interaksi',
                    'tlogmahasiswa.keyword',
                    'tlogmahasiswa.hasil',
                    DB::raw('COUNT(*) as total_akses')
                )
                ->groupBy(
                    'tlogmahasiswa.tFaqs_id',
                    'tsession.kode_fakultas',
                    'tlogmahasiswa.jenis_interaksi',
                    'tlogmahasiswa.keyword',
                    'tlogmahasiswa.hasil'
                )
                ->get();

            $publishedFaqs = PortalController::rankFaqsForStudent($faqs, null, null, null, $logStats, $settings);
        } catch (Throwable $e) {
            $publishedFaqs = collect();
        }

        return view('guest.faq', [
            'publishedFaqs'        => $publishedFaqs,
            'idleTimeoutSeconds'   => $settings['idle_timeout_seconds'] ?? 30,
            'idleCountdownSeconds' => $settings['idle_countdown_seconds'] ?? 10,
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
            'kode_prodi',
            'kode_angkatan',
            'tahun_angkatan',
            'semester',
            'is_guest',
        ]);

        return redirect()->route('portal');
    }
}

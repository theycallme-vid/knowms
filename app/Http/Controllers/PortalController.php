<?php

namespace App\Http\Controllers;

use App\Models\Session as TSession;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * =========================================================================
     * TEMPLATE DAFTAR KODE FAKULTAS (2 DIGIT DEPAN NRP)
     * =========================================================================
     * Silakan tambahkan atau sesuaikan kode 2 karakter depan NRP beserta
     * nama fakultasnya pada daftar di bawah ini.
     */
    public const FAKULTAS_MAP = [
        '16' => 'Fakultas Teknik',
        // --- Template Fakultas Lainnya (Silakan disesuaikan kodenya) ---
        '11' => 'Fakultas Farmasi',
        '12' => 'Fakultas Hukum',
        '13' => 'Fakultas Bisnis dan Ekonomika',
        '15' => 'Fakultas Psikologi',
        '17' => 'Fakultas Teknobiologi',
        '18' => 'Fakultas Kedokteran',
        '19' => 'Fakultas Industri Kreatif',
        '10' => 'Politeknik Ubaya',
    ];

    /**
     * Menampilkan halaman Portal Mahasiswa
     */
    public function index()
    {
        return view('portal.portal');
    }

    /**
     * Menyimpan hasil scan QR / input NRP (2 karakter depan) ke tabel tsession
     * lalu mengalihkan otomatis ke halaman FAQ Mahasiswa
     */
    public function storeSession(Request $request)
    {
        $rawNrp = trim((string) $request->input('nrp', ''));

        // Ambil hanya angka/karakter alfanumerik dari hasil scan/input
        $cleanNrp = preg_replace('/[^0-9A-Za-z]/', '', $rawNrp);

        // Ambil 2 karakter depan sebagai kode_fakultas (contoh: "161124002" -> "16")
        $kodeFakultas = strlen($cleanNrp) >= 2 ? substr($cleanNrp, 0, 2) : null;

        // Simpan ke tabel tsession beserta timestamp created_at
        $sessionRecord = new TSession();
        $sessionRecord->kode_fakultas = $kodeFakultas;
        $sessionRecord->created_at = now();
        $sessionRecord->updated_at = now();
        $sessionRecord->save();

        // Tentukan nama fakultas berdasarkan template daftar kode fakultas
        $namaFakultas = self::resolveNamaFakultas($kodeFakultas);

        // Simpan ke session aktif browser agar terbaca di halaman FAQ Mahasiswa
        session([
            'tsession_id'   => $sessionRecord->id,
            'kode_fakultas' => $kodeFakultas,
            'nama_fakultas' => $namaFakultas,
        ]);

        return redirect()->route('mahasiswa.faq');
    }

    /**
     * Menampilkan halaman FAQ Mahasiswa sesuai session kode_fakultas yang aktif
     */
    public function faq(Request $request)
    {
        // Jika ada parameter ?nrp= langsung dari URL (fallback), proses juga ke tsession
        if ($request->filled('nrp')) {
            return $this->storeSession($request);
        }

        $kodeFakultas = session('kode_fakultas');
        $namaFakultas = self::resolveNamaFakultas($kodeFakultas);

        return view('mahasiswa.faq', [
            'kodeFakultas' => $kodeFakultas,
            'namaFakultas' => $namaFakultas,
            'fakultasMap'  => self::FAKULTAS_MAP,
        ]);
    }

    /**
     * Mengambil nama fakultas dari kode 2 karakter depan NRP
     */
    public static function resolveNamaFakultas(?string $kode): string
    {
        if (!$kode) {
            return 'Umum (Semua Fakultas)';
        }

        return self::FAKULTAS_MAP[$kode] ?? ('Fakultas (Kode ' . $kode . ')');
    }
}


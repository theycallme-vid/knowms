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
        '14' => 'Politeknik Ubaya',
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

        // Jika pengguna mengisi NRP (baik lewat scan maupun ketik manual), lakukan validasi ketat:
        if ($rawNrp !== '') {
            // 1. Wajib tepat 9 digit karakter dan hanya berupa string angka (0-9)
            if (!preg_match('/^\d{9}$/', $rawNrp)) {
                return redirect()
                    ->route('portal')
                    ->withInput()
                    ->withErrors([
                        'nrp' => 'NRP harus terdiri dari tepat 9 digit angka (contoh: 160423018).',
                    ]);
            }

            // 2. Ambil 2 karakter terdepan sebagai kode_fakultas dan wajib terdaftar di FAKULTAS_MAP
            $kodeFakultas = substr($rawNrp, 0, 2);

            if (!array_key_exists($kodeFakultas, self::FAKULTAS_MAP)) {
                return redirect()
                    ->route('portal')
                    ->withInput()
                    ->withErrors([
                        'nrp' => "Kode fakultas '{$kodeFakultas}' pada NRP tidak terdaftar di sistem.",
                    ]);
            }
        } else {
            // Jika kosong (Lanjutkan tanpa NRP / Tamu)
            $kodeFakultas = null;
        }

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
     * Mengakhiri session mahasiswa (saat klik Tidak pada pop-up atau habis waktu idle 30+10 detik)
     * lalu mengembalikan ke halaman Portal Mahasiswa
     */
    public function endSession(Request $request)
    {
        $tsessionId = session('tsession_id');

        if ($tsessionId) {
            $sessionRecord = TSession::find($tsessionId);
            if ($sessionRecord) {
                // SoftDeletes: mengisi kolom deleted_at dengan timestamp berakhirnya sesi
                $sessionRecord->delete();
            }
        }

        // Hapus data session fakultas aktif dari browser
        $request->session()->forget([
            'tsession_id',
            'kode_fakultas',
            'nama_fakultas',
        ]);

        return redirect()->route('portal');
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


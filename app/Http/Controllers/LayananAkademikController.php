<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LayananAkademikController extends Controller
{
    /**
     * Nomor Hotline WhatsApp default jika belum diset di .env
     */
    public const DEFAULT_WHATSAPP = '6281234567890';

    /**
     * Halaman Kontak Layanan Akademik (Direct Page, Tanpa Modal):
     * Khusus untuk Mahasiswa Aktif (Guest tidak diperkenankan mengakses).
     * - Bagian Atas: Panggilan Suara (Audio Only) terintegrasi WebRTC / Jitsi Meet
     * - Bagian Bawah: Informasi Hotline WhatsApp resmi + QR Code scan mandiri untuk ponsel mahasiswa
     */
    public function kontak(Request $request)
    {
        // Pastikan hanya mahasiswa aktif yang dapat mengakses layanan akademik (Guest tidak bisa)
        $isGuest = session('is_guest', false) || session('nama_fakultas') === 'Guest';
        $hasStudentSession = session('tsession_id') && (session('kode_fakultas') || session('nrp'));

        if ($isGuest || (!$hasStudentSession && !Auth::check())) {
            return redirect()->route('portal')->with('error', 'Layanan kontak akademik hanya dapat diakses oleh mahasiswa aktif. Silakan masukkan NRP atau pindai KTM terlebih dahulu.');
        }

        $user = Auth::user();
        $userName = 'Mahasiswa';
        $userRole = 'Mahasiswa';
        $userEmail = '';
        $isStaff = false;

        if ($user) {
            $userName = $user->nama ?? $user->name ?? 'Petugas Akademik';
            $userRole = $user->role?->nama ?? $user->role?->name ?? 'Petugas Layanan Akademik';
            $userEmail = $user->email ?? '';
            $isStaff = true;
        } else {
            $namaFak = session('nama_fakultas');
            $kodeFak = session('kode_fakultas');
            $tahunAng = session('tahun_angkatan');

            if ($namaFak && $namaFak !== 'Umum (Semua Fakultas)') {
                $userName = 'Mahasiswa ' . $namaFak . ($tahunAng ? ' (' . $tahunAng . ')' : '');
                $userRole = 'Mahasiswa ' . $namaFak;
            } elseif ($kodeFak) {
                $userName = 'Mahasiswa (Kode ' . $kodeFak . ')';
                $userRole = 'Mahasiswa';
            } else {
                $userName = 'Mahasiswa KMS';
                $userRole = 'Mahasiswa';
            }
        }

        // Room ID unik acak (UUID) untuk sesi panggilan audio
        $audioRoomId = 'kms-audio-' . Str::uuid()->toString();

        // Data Hotline WhatsApp Resmi
        $whatsappNumber = (string) (config('app.whatsapp_hotline') ?: self::DEFAULT_WHATSAPP);
        $formattedWaNumber = '+62 ' . substr($whatsappNumber, 2, 3) . '-' . substr($whatsappNumber, 5, 4) . '-' . substr($whatsappNumber, 9);
        $whatsappDefaultText = "Halo Admin Layanan Akademik KMS, saya mahasiswa ingin berkonsultasi mengenai pertanyaan/informasi akademik yang belum saya temukan di FAQ.";
        $waUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappDefaultText);

        return view('layanan.kontak', [
            'audioRoomId'       => $audioRoomId,
            'userName'          => $userName,
            'userRole'          => $userRole,
            'userEmail'         => $userEmail,
            'isStaff'           => $isStaff,
            'whatsappNumber'    => $whatsappNumber,
            'formattedWaNumber' => $formattedWaNumber,
            'waUrl'             => $waUrl,
            'backUrl'           => route('mahasiswa.faq'),
        ]);
    }

    /**
     * API untuk memulai panggilan suara (Audio Call) dari mahasiswa:
     * Menyimpan sesi ke tvideocall sehingga staf unit yang bersangkutan langsung menerima notifikasi panggilan suara
     */
    public function startAudioCallApi(Request $request)
    {
        $isGuest = session('is_guest', false) || session('nama_fakultas') === 'Guest';
        $hasStudentSession = session('tsession_id') && (session('kode_fakultas') || session('nrp'));

        if ($isGuest || (!$hasStudentSession && !Auth::check())) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan kontak akademik hanya dapat diakses oleh mahasiswa aktif terdaftar.',
            ], 403);
        }

        $roomId = trim((string) $request->input('room_id', ''));
        if (empty($roomId)) {
            $roomId = 'kms-audio-' . Str::uuid()->toString();
        }

        $namaFak = session('nama_fakultas');
        $kodeFak = session('kode_fakultas');
        $tahunAng = session('tahun_angkatan');

        $studentName = 'Mahasiswa';
        if ($namaFak && $namaFak !== 'Umum (Semua Fakultas)') {
            $studentName = 'Mahasiswa ' . $namaFak . ($tahunAng ? ' (' . $tahunAng . ')' : '');
        } elseif ($kodeFak) {
            $studentName = 'Mahasiswa (Kode ' . $kodeFak . ')';
        }

        $facultyName = $namaFak ?: ($kodeFak ? ('Fakultas ' . $kodeFak) : 'Layanan Akademik');

        VideoCall::updateOrCreate(
            ['room_id' => $roomId],
            [
                'nama_mahasiswa' => $studentName,
                'fakultas'       => $facultyName,
                'status'         => 'waiting',
            ]
        );

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'message' => 'Panggilan suara dimulai, notifikasi terkirim ke Admin.',
        ]);
    }

    /**
     * Memulai sesi video call baru:
     * Men-generate 'Room ID' unik acak berbasis UUID (format 'kms-layanan-<uuid>')
     * Menyimpan sesi ke database tvideocall (status: 'waiting') agar admin mendapat notifikasi langsung di website,
     * lalu mengarahkan mahasiswa ke halaman room Jitsi Meet.
     */
    public function startVideoCall(Request $request)
    {
        $uuid = Str::uuid()->toString();
        $roomId = 'kms-layanan-' . $uuid;

        // Ambil data session mahasiswa jika ada
        $namaFak = session('nama_fakultas');
        $kodeFak = session('kode_fakultas');
        $tahunAng = session('tahun_angkatan');

        $studentName = 'Mahasiswa';
        if ($namaFak && $namaFak !== 'Umum (Semua Fakultas)') {
            $studentName = 'Mahasiswa ' . $namaFak . ($tahunAng ? ' (' . $tahunAng . ')' : '');
        } elseif ($kodeFak) {
            $studentName = 'Mahasiswa (Kode ' . $kodeFak . ')';
        }

        $facultyName = $namaFak ?: ($kodeFak ? ('Fakultas ' . $kodeFak) : 'Layanan Akademik');

        // Catat sesi video call ke database agar admin langsung mendapatkan notifikasi panggilan masuk
        VideoCall::create([
            'room_id'        => $roomId,
            'nama_mahasiswa' => $studentName,
            'fakultas'       => $facultyName,
            'status'         => 'waiting',
        ]);

        return redirect()->route('layanan.videocall.room', ['roomId' => $roomId]);
    }

    /**
     * Halaman Ruang Video Call bersama Jitsi Meet API external iframe.
     * Menggunakan Room ID yang sama untuk merender view bagi mahasiswa maupun admin.
     */
    public function videoCallRoom(Request $request, string $roomId)
    {
        $cleanRoomId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $roomId);
        if (empty($cleanRoomId)) {
            $cleanRoomId = 'kms-layanan-' . Str::uuid()->toString();
        }

        // Cari atau daftarkan sesi video call di database
        $callRecord = VideoCall::where('room_id', $cleanRoomId)->first();

        // Tentukan identitas dan peran pengguna aktif
        $user = Auth::user();
        $userName = 'Mahasiswa';
        $userRole = 'Mahasiswa';
        $userEmail = '';
        $isStaff = false;

        if ($user) {
            // Pengguna adalah staf/admin KMS yang sedang login
            $userName = $user->nama ?? $user->name ?? 'Petugas Akademik';
            $userRole = $user->role?->nama ?? $user->role?->name ?? 'Petugas Layanan Akademik';
            $userEmail = $user->email ?? '';
            $isStaff = true;

            // Jika admin bergabung ke ruang panggilan yang sedang 'waiting', ubah status menjadi 'connected'
            if ($callRecord && $callRecord->status === 'waiting') {
                $callRecord->update([
                    'status'     => 'connected',
                    'admin_id'   => $user->id,
                    'admin_nama' => $userName,
                    'started_at' => now(),
                ]);
            }
        } else {
            // Mahasiswa / Pengunjung
            $namaFak = session('nama_fakultas');
            $kodeFak = session('kode_fakultas');
            $tahunAng = session('tahun_angkatan');

            if ($callRecord && !empty($callRecord->nama_mahasiswa)) {
                $userName = $callRecord->nama_mahasiswa;
                $userRole = 'Mahasiswa ' . ($callRecord->fakultas ?? '');
            } elseif ($namaFak && $namaFak !== 'Umum (Semua Fakultas)') {
                $userName = 'Mahasiswa (' . $namaFak . ($tahunAng ? ' ' . $tahunAng : '') . ')';
                $userRole = 'Mahasiswa ' . $namaFak;
            } elseif ($kodeFak) {
                $userName = 'Mahasiswa (Kode ' . $kodeFak . ')';
                $userRole = 'Mahasiswa';
            } else {
                $userName = 'Mahasiswa KMS';
                $userRole = 'Mahasiswa / Pengunjung';
            }
        }

        // URL Room lengkap
        $roomUrl = route('layanan.videocall.room', ['roomId' => $cleanRoomId]);

        // Pengaturan Hotline WhatsApp
        $whatsappNumber = (string) (config('app.whatsapp_hotline') ?: self::DEFAULT_WHATSAPP);
        $whatsappMessage = "Halo Petugas Layanan Akademik KMS, saya sedang berada di ruang video call konsultasi akademik dengan Room ID: {$cleanRoomId}. Mohon berkenan untuk bergabung melalui tautan: {$roomUrl}";

        $backUrl = $isStaff
            ? match ($user->getRoleName()) {
                'Super Admin' => route('superadmin.dashboard'),
                'Validator'   => $user->isValidatorUnit() ? route('validator.unit.daftarFAQ') : route('validator.daftarFAQ'),
                'Data Entry'  => route('dataentry.daftarFAQ'),
                default       => route('portal'),
            }
            : route('mahasiswa.faq');

        return view('layanan.video-call', [
            'roomId'          => $cleanRoomId,
            'callRecord'      => $callRecord,
            'userName'        => $userName,
            'userRole'        => $userRole,
            'userEmail'       => $userEmail,
            'isStaff'         => $isStaff,
            'roomUrl'         => $roomUrl,
            'whatsappNumber'  => $whatsappNumber,
            'whatsappMessage' => $whatsappMessage,
            'backUrl'         => $backUrl,
        ]);
    }

    /**
     * Endpoint API JSON untuk Petugas Unit Penerima Alert Call:
     * Mengambil daftar panggilan masuk berstatus 'waiting' secara real-time.
     * Hanya pengguna yang bertugas di UNIT yang dipilih (dari tabel tsettings) yang akan menerima alert call.
     */
    public function getActiveCalls(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'count'   => 0,
                'calls'   => [],
            ], 401);
        }

        // BUKAN Super Admin (Role 1) atau Validator (Role 3) yang menerima call.
        // Panggilan hanya diterima oleh staf Unit yang ditugaskan.
        $userRoleId = (int) ($user->tRoles_id ?? 0);
        if ($userRoleId === 1 || $userRoleId === 3) {
            return response()->json([
                'success' => true,
                'count'   => 0,
                'calls'   => [],
            ]);
        }

        // Ambil daftar ID unit yang diizinkan menerima call dari tabel tsettings (diatur oleh Validator)
        $allowedUnitIds = \App\Models\Setting::getAllowedCallUnitIds();

        // Periksa apakah unit user yang sedang login ($user->tDirektorat_id) ada di daftar unit terpilih
        $userUnitId = (int) ($user->tDirektorat_id ?? 0);
        $isAllowedUnit = in_array($userUnitId, $allowedUnitIds, true);

        // Jika user BUKAN berasal dari unit yang ditugaskan, jangan berikan notifikasi alert call
        if (!$isAllowedUnit) {
            return response()->json([
                'success' => true,
                'count'   => 0,
                'calls'   => [],
            ]);
        }

        // Ambil panggilan berstatus 'waiting' dalam 15 menit terakhir
        $calls = VideoCall::where('status', 'waiting')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->latest()
            ->get()
            ->map(function ($call) {
                return [
                    'id'              => $call->id,
                    'room_id'         => $call->room_id,
                    'nama_mahasiswa'  => $call->nama_mahasiswa,
                    'fakultas'        => $call->fakultas,
                    'status'          => $call->status,
                    'created_at_diff' => $call->created_at->diffForHumans(),
                    'join_url'        => route('layanan.videocall.room', ['roomId' => $call->room_id]),
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $calls->count(),
            'calls'   => $calls,
        ]);
    }

    /**
     * Mengakhiri panggilan video call
     */
    public function endCall(Request $request, string $roomId)
    {
        if (!Auth::check() && !session('tsession_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $cleanRoomId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $roomId);
        
        VideoCall::where('room_id', $cleanRoomId)->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi video call telah diakhiri.',
        ]);
    }

    /**
     * Memungkinkan admin atau mahasiswa bergabung ke room yang sudah ada
     */
    public function joinRoom(Request $request)
    {
        $rawInput = trim((string) $request->input('room_id', ''));

        if (str_contains($rawInput, '/layanan-akademik/video-call/')) {
            $parts = explode('/layanan-akademik/video-call/', $rawInput);
            $rawInput = end($parts);
        }

        $cleanRoomId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $rawInput);
        if (empty($cleanRoomId)) {
            return redirect()->back()->with('error', 'Silakan masukkan Room ID atau Tautan Video Call yang valid.');
        }

        return redirect()->route('layanan.videocall.room', ['roomId' => $cleanRoomId]);
    }
}

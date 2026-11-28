<?php

namespace App\Http\Controllers;

use App\Models\DaftarPustaka;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\LogMahasiswa;
use App\Models\Session as TSession;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * Menyimpan hasil scan QR / input NRP (format 1ABBCCDDD) ke tabel tsession
     * lalu mengalihkan otomatis ke halaman FAQ Mahasiswa
     */
    public function storeSession(Request $request)
    {
        $rawNrp = trim((string) $request->input('nrp', ''));

        // Jika pengguna mengisi NRP (baik lewat scan maupun ketik manual), lakukan validasi ketat format 1ABBCCDDD:
        if ($rawNrp !== '') {
            // 1. Wajib tepat 9 digit karakter dan hanya berupa string angka (0-9)
            $parsed = self::parseNrp($rawNrp);
            if (!$parsed) {
                return redirect()
                    ->route('portal')
                    ->withInput()
                    ->withErrors([
                        'nrp' => 'NRP harus terdiri dari tepat 9 digit angka dengan format 1ABBCCDDD (contoh: 160423018).',
                    ]);
            }

            // 2. Ambil 2 karakter terdepan (1A) sebagai kode_fakultas dan wajib terdaftar di FAKULTAS_MAP
            $kodeFakultas  = $parsed['kode_fakultas'];
            $kodeProdi     = $parsed['kode_prodi'];
            $kodeAngkatan  = $parsed['kode_angkatan'];
            $tahunAngkatan = $parsed['tahun_angkatan'];
            $semester      = $parsed['semester'];

            if (!array_key_exists($kodeFakultas, self::FAKULTAS_MAP)) {
                return redirect()
                    ->route('portal')
                    ->withInput()
                    ->withErrors([
                        'nrp' => "Kode fakultas '{$kodeFakultas}' pada NRP tidak terdaftar di sistem.",
                    ]);
            }

            // 3. Validasi 2 digit angkatan (CC pada 1ABBCCDDD, contoh: 23 -> 2023)
            $maxYear = (int) now()->format('Y') + 1;
            $tahunInt = (int) $tahunAngkatan;
            if ($tahunInt < 2010 || $tahunInt > $maxYear) {
                return redirect()
                    ->route('portal')
                    ->withInput()
                    ->withErrors([
                        'nrp' => "Kode angkatan '{$kodeAngkatan}' (tahun {$tahunAngkatan}) pada NRP tidak valid.",
                    ]);
            }
        } else {
            // Jika kosong (Lanjutkan tanpa NRP / Tamu)
            $sessionRecord = new TSession();
            $sessionRecord->kode_fakultas = null;
            $sessionRecord->created_at = now();
            $sessionRecord->updated_at = now();
            $sessionRecord->save();

            session([
                'tsession_id'    => $sessionRecord->id,
                'kode_fakultas'  => null,
                'nama_fakultas'  => 'Guest',
                'kode_prodi'     => null,
                'kode_angkatan'  => null,
                'tahun_angkatan' => null,
                'semester'       => null,
                'is_guest'       => true,
            ]);

            return redirect()->route('guest.faq');
        }

        // shortcut: simpan "kodeFakultas-kodeAngkatan" (mis. "16-23") di kolom kode_fakultas varchar(100) agar tidak perlu alter tabel tsession
        $sessionRecord = new TSession();
        $sessionRecord->kode_fakultas = $parsed['session_key'];
        $sessionRecord->created_at = now();
        $sessionRecord->updated_at = now();
        $sessionRecord->save();

        // Tentukan nama fakultas berdasarkan template daftar kode fakultas
        $namaFakultas = $parsed['nama_fakultas'];

        // Simpan ke session aktif browser agar terbaca di halaman FAQ Mahasiswa
        session([
            'tsession_id'    => $sessionRecord->id,
            'kode_fakultas'  => $kodeFakultas,
            'nama_fakultas'  => $namaFakultas,
            'kode_prodi'     => $kodeProdi,
            'kode_angkatan'  => $kodeAngkatan,
            'tahun_angkatan' => $tahunAngkatan,
            'semester'       => $semester,
            'is_guest'       => false,
        ]);

        return redirect()->route('mahasiswa.faq');
    }

    /**
     * Menampilkan halaman FAQ Mahasiswa sesuai session kode_fakultas & angkatan yang aktif,
     * diurutkan menggunakan algoritma rekomendasi (log akses tlogmahasiswa + relevansi fakultas & angkatan).
     */
    public function faq(Request $request)
    {
        // Jika ada parameter ?nrp= langsung dari URL (fallback), proses juga ke tsession
        if ($request->filled('nrp')) {
            return $this->storeSession($request);
        }

        $kodeFakultas  = session('kode_fakultas');
        $namaFakultas  = self::resolveNamaFakultas($kodeFakultas);
        $kodeProdi     = session('kode_prodi');
        $kodeAngkatan  = session('kode_angkatan');
        $tahunAngkatan = session('tahun_angkatan') ?: ($kodeAngkatan ? ('20' . $kodeAngkatan) : null);
        $semester      = session('semester') ?: self::estimateSemester($tahunAngkatan);

        $publishedFaqs = collect();
        $popularKeywords = self::topSearchedKeywords($publishedFaqs);
        $settings = Setting::allIntValues();

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $faqs = Faq::with([
                'tags',
                'versions' => fn ($q) => $q->where('status', 'Terbit')
                    ->orderByDesc('version_number')
                    ->latest('updated_at'),
                'versions.daftarPustaka',
            ])->whereHas('versions', function ($q) {
                $q->where('status', 'Terbit');
            })->orderBy('created_at', 'desc')->get();

            $recLogDays = max(1, (int) ($settings['rec_log_days'] ?? 30));

            $logStats = DB::table('tlogmahasiswa')
                ->leftJoin('tsession', 'tlogmahasiswa.tSession_id', '=', 'tsession.id')
                ->whereNotNull('tlogmahasiswa.tFaqs_id')
                ->where('tlogmahasiswa.waktu_pencarian', '>=', now()->subDays($recLogDays))
                ->select(
                    'tlogmahasiswa.tFaqs_id as faq_id',
                    'tsession.kode_fakultas as session_key',
                    'tlogmahasiswa.jenis_interaksi',
                    'tlogmahasiswa.hasil',
                    DB::raw('COUNT(*) as total_akses')
                )
                ->groupBy(
                    'tlogmahasiswa.tFaqs_id',
                    'tsession.kode_fakultas',
                    'tlogmahasiswa.jenis_interaksi',
                    'tlogmahasiswa.hasil'
                )
                ->get();

            $publishedFaqs = self::rankFaqsForStudent($faqs, $kodeFakultas, $kodeAngkatan, $semester, $logStats, $settings);
            $popularKeywords = self::topSearchedKeywords($publishedFaqs, $recLogDays);
        } catch (Throwable $e) {
            $publishedFaqs = collect();
        }

        return view('mahasiswa.faq', [
            'kodeFakultas'         => $kodeFakultas,
            'namaFakultas'         => $namaFakultas,
            'kodeProdi'            => $kodeProdi,
            'kodeAngkatan'         => $kodeAngkatan,
            'tahunAngkatan'        => $tahunAngkatan,
            'semester'             => $semester,
            'fakultasMap'          => self::FAKULTAS_MAP,
            'publishedFaqs'        => $publishedFaqs,
            'popularKeywords'      => $popularKeywords,
            'idleTimeoutSeconds'   => $settings['idle_timeout_seconds'] ?? 30,
            'idleCountdownSeconds' => $settings['idle_countdown_seconds'] ?? 10,
        ]);
    }

    /**
     * Endpoint pencarian FAQ publik (Mahasiswa & Guest):
     * Hanya mencari pada versi FAQ berstatus 'Terbit' melalui FaqSimilarityService::searchPublishedFaqs().
     */
    public function searchPublishedFaq(Request $request, \App\Services\FaqSimilarityService $similarityService)
    {
        $q = trim((string) $request->query('q', ''));
        $limit = min(25, max(1, (int) $request->query('limit', 10)));

        $response = $similarityService->searchPublishedFaqs($q, [
            'limit'     => $limit,
            'threshold' => 32.0,
        ]);

        return response()->json($response);
    }

    /**
     * Halaman Full Page Riwayat Versi Jawaban untuk 1 pertanyaan FAQ Mahasiswa.
     * Diurutkan dari versi paling terbaru di posisi paling atas.
     */
    public function faqHistory(Request $request, string $id)
    {
        $kodeFakultas = session('kode_fakultas');
        $namaFakultas = session('nama_fakultas');
        if (!$namaFakultas && $kodeFakultas) {
            $namaFakultas = self::FAKULTAS_MAP[$kodeFakultas] ?? ('Fakultas (Kode ' . $kodeFakultas . ')');
        }

        $kodeAngkatan  = session('kode_angkatan');
        $tahunAngkatan = session('tahun_angkatan') ?: ($kodeAngkatan ? ('20' . $kodeAngkatan) : null);
        $semester      = session('semester') ?: self::estimateSemester($tahunAngkatan);
        $settings      = Setting::allIntValues();

        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with([
            'tags',
            'versions' => fn ($q) => $q->where('status', 'Terbit'),
            'versions.daftarPustaka',
        ])->findOrFail($id);

        $tagNamesList = $faq->tags->pluck('nama')->values()->all();

        // Urutkan versi dari yang paling terbaru (nomor versi tertinggi / waktu terbaru) di urutan teratas
        $sortedVersions = $faq->versions
            ->sort(function ($a, $b) {
                $numA = preg_match('/-V(\d+)$/i', (string) $a->id, $mA) ? (int) $mA[1] : 0;
                $numB = preg_match('/-V(\d+)$/i', (string) $b->id, $mB) ? (int) $mB[1] : 0;
                if ($numA !== $numB) {
                    return $numB <=> $numA;
                }
                $tA = ($a->updated_at ?? $a->created_at)?->timestamp ?? 0;
                $tB = ($b->updated_at ?? $b->created_at)?->timestamp ?? 0;
                return $tB <=> $tA;
            })
            ->values();

        $versions = [];
        foreach ($sortedVersions as $vIdx => $verItem) {
            $vPustaka = $verItem->daftarPustaka;
            $vIsFile = $vPustaka ? !empty($vPustaka->upload) : false;
            $vSourceLabel = $vPustaka ? ($vPustaka->judul_tampil ?: $vPustaka->link) : null;
            $vSourceUrl = $vPustaka
                ? ($vIsFile ? route('pustaka.lihat', $vPustaka->id) : (filter_var($vPustaka->link, FILTER_VALIDATE_URL) ? $vPustaka->link : null))
                : null;
            $vTs = $verItem->updated_at ?? $verItem->created_at;

            $versions[] = [
                'version_id'     => $verItem->id,
                'version_label'  => FaqVersion::formatVersiLabel($verItem->id),
                'is_latest'      => ($vIdx === 0),
                'updated_at'     => $vTs ? $vTs->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                'date_only'      => $vTs ? $vTs->timezone('Asia/Jakarta')->translatedFormat('d M Y') : '-',
                'jawaban'        => $verItem->jawaban,
                'tags'           => $tagNamesList,
                'source_label'   => $vSourceLabel,
                'source_url'     => $vSourceUrl,
                'source_is_file' => $vIsFile,
            ];
        }

        return view('mahasiswa.riwayat-jawaban', [
            'faq'                  => $faq,
            'versions'             => $versions,
            'tags'                 => $tagNamesList,
            'kodeFakultas'         => $kodeFakultas,
            'namaFakultas'         => $namaFakultas ?: 'Umum (Semua Fakultas)',
            'tahunAngkatan'        => $tahunAngkatan,
            'semester'             => $semester,
            'idleTimeoutSeconds'   => $settings['idle_timeout_seconds'] ?? 30,
            'idleCountdownSeconds' => $settings['idle_countdown_seconds'] ?? 10,
        ]);
    }

    /**
     * Mencatat interaksi / akses FAQ oleh mahasiswa ke tabel tlogmahasiswa
     * dengan membedakan antara View ('view') dan Feedback ('like' / 'dislike').
     */
    public function logFaqAccess(Request $request)
    {
        $validated = $request->validate([
            'faq_id'                  => ['nullable', 'string', 'max:20'],
            'jenis_interaksi'         => ['nullable', 'string', 'in:view,like,dislike'],
            'keyword'                 => ['nullable', 'string', 'max:255'],
            'hasil'                   => ['nullable', 'integer', 'in:0,1'],
            'items'                   => ['nullable', 'array', 'max:100'],
            'items.*.faq_id'          => ['required', 'string', 'max:20'],
            'items.*.jenis_interaksi' => ['nullable', 'string', 'in:view,like,dislike'],
            'items.*.keyword'         => ['nullable', 'string', 'max:255'],
            'items.*.hasil'           => ['nullable', 'integer', 'in:0,1'],
        ]);

        $items = $validated['items'] ?? (!empty($validated['faq_id']) ? [[
            'faq_id'          => $validated['faq_id'],
            'jenis_interaksi' => $validated['jenis_interaksi'] ?? null,
            'keyword'         => $validated['keyword'] ?? null,
            'hasil'           => $validated['hasil'] ?? 1,
        ]] : []);

        if (empty($items)) {
            return response()->json([
                'status'    => 'noop',
                'synced_at' => now()->format('H:i:s'),
            ]);
        }

        try {
            $tsessionId = session('tsession_id');
            if (!$tsessionId) {
                $kodeFak = session('kode_fakultas');
                $kodeAng = session('kode_angkatan');
                $sessionKey = ($kodeFak && $kodeAng) ? "{$kodeFak}-{$kodeAng}" : $kodeFak;

                $sessionRecord = new TSession();
                $sessionRecord->kode_fakultas = $sessionKey;
                $sessionRecord->created_at = now();
                $sessionRecord->updated_at = now();
                $sessionRecord->save();

                $tsessionId = $sessionRecord->id;
                session(['tsession_id' => $tsessionId]);
            }

            $lastFaqId = null;
            $now = now();

            foreach ($items as $item) {
                $faqId = $item['faq_id'];
                $lastFaqId = $faqId;
                $rawKw = trim((string) ($item['keyword'] ?? ''));
                $hasil = (int) ($item['hasil'] ?? 1);

                $jenisInteraksi = $item['jenis_interaksi'] ?? null;
                if (!$jenisInteraksi) {
                    if ($rawKw === 'feedback-membantu') {
                        $jenisInteraksi = 'like';
                    } elseif ($rawKw === 'feedback-belum' || $hasil === 0) {
                        $jenisInteraksi = 'dislike';
                    } else {
                        $jenisInteraksi = 'view';
                    }
                }

                if ($jenisInteraksi === 'view') {
                    // Cegah duplikat view dalam 1 sesi yang sama untuk FAQ yang sama
                    $alreadyViewed = LogMahasiswa::where('tSession_id', $tsessionId)
                        ->where('tFaqs_id', $faqId)
                        ->where('jenis_interaksi', 'view')
                        ->exists();

                    if (!$alreadyViewed) {
                        LogMahasiswa::create([
                            'jenis_interaksi' => 'view',
                            'keyword'         => $rawKw !== '' ? $rawKw : 'buka-faq',
                            'hasil'           => 1,
                            'waktu_pencarian' => $now,
                            'tSession_id'     => $tsessionId,
                            'tFaqs_id'        => $faqId,
                        ]);
                    }
                } else {
                    // Untuk feedback (like / dislike), 1 sesi hanya memiliki maksimal 1 feedback per FAQ
                    $existingFeedback = LogMahasiswa::where('tSession_id', $tsessionId)
                        ->where('tFaqs_id', $faqId)
                        ->whereIn('jenis_interaksi', ['like', 'dislike'])
                        ->first();

                    $feedbackPayload = [
                        'jenis_interaksi' => $jenisInteraksi,
                        'keyword'         => $jenisInteraksi === 'like' ? 'feedback-membantu' : 'feedback-belum',
                        'hasil'           => $jenisInteraksi === 'like' ? 1 : 0,
                        'waktu_pencarian' => $now,
                        'tSession_id'     => $tsessionId,
                        'tFaqs_id'        => $faqId,
                    ];

                    if ($existingFeedback) {
                        $existingFeedback->update($feedbackPayload);
                    } else {
                        LogMahasiswa::create($feedbackPayload);
                    }
                }
            }

            // Invalidate summary cache agar angka view/like terbaru langsung terbaca
            \Illuminate\Support\Facades\Cache::store('file')->forget('kms_faq_interaction_summary_all');
            $summary = LogMahasiswa::getFaqInteractionSummary();
            $counts = ($lastFaqId && isset($summary[$lastFaqId]))
                ? $summary[$lastFaqId]
                : ['views' => 0, 'likes' => 0, 'dislikes' => 0];

            return response()->json([
                'status'     => 'ok',
                'synced_at'  => $now->format('H:i:s'),
                'counts'     => $counts,
                'counts_map' => $summary,
            ]);
        } catch (Throwable $e) {
            return response()->json(['status' => 'ignored'], 200);
        }
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

        // Hapus data session fakultas & angkatan aktif dari browser
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

    /**
     * Memecah NRP format 1ABBCCDDD (9 digit):
     * - 1A  (digit 1-2): kode fakultas (contoh: '16' -> Fakultas Teknik)
     * - BB  (digit 3-4): kode prodi (contoh: '04')
     * - CC  (digit 5-6): kode angkatan (contoh: '23' -> 2023)
     * - DDD (digit 7-9): nomor urut mahasiswa (contoh: '018')
     */
    public static function parseNrp(string $rawNrp): ?array
    {
        $clean = trim($rawNrp);
        if (!preg_match('/^\d{9}$/', $clean)) {
            return null;
        }

        $kodeFakultas  = substr($clean, 0, 2);
        $kodeProdi     = substr($clean, 2, 2);
        $kodeAngkatan  = substr($clean, 4, 2);
        $nomorUrut     = substr($clean, 6, 3);
        $tahunAngkatan = (string) (2000 + (int) $kodeAngkatan);

        return [
            'nrp'            => $clean,
            'kode_fakultas'  => $kodeFakultas,
            'nama_fakultas'  => self::resolveNamaFakultas($kodeFakultas),
            'kode_prodi'     => $kodeProdi,
            'kode_angkatan'  => $kodeAngkatan,
            'tahun_angkatan' => $tahunAngkatan,
            'semester'       => self::estimateSemester($tahunAngkatan),
            'nomor_urut'     => $nomorUrut,
            'session_key'    => "{$kodeFakultas}-{$kodeAngkatan}",
        ];
    }

    /**
     * Menghitung estimasi semester mahasiswa dari tahun angkatan (misal angkatan 2023 di akhir 2026 = semester 7)
     */
    public static function estimateSemester(?string $tahunAngkatan): ?int
    {
        if (!$tahunAngkatan || !is_numeric($tahunAngkatan)) {
            return null;
        }

        $currentYear  = (int) now()->format('Y');
        $currentMonth = (int) now()->format('n');
        $yearDiff     = max(0, $currentYear - (int) $tahunAngkatan);

        return max(1, ($yearDiff * 2) + ($currentMonth >= 7 ? 1 : 0));
    }

    /**
     * Mengurutkan daftar FAQ berdasarkan:
     * 1. Frekuensi View di tlogmahasiswa oleh segmen NRP yang sama (fakultas + angkatan, angkatan sama, fakultas sama, global)
     * 2. Jumlah Like (membantu) & Dislike (belum membantu)
     * 3. Relevansi topik terhadap fakultas mahasiswa
     * 4. Relevansi topik terhadap tingkat angkatan / semester mahasiswa (misal semester >= 7 -> Tugas Akhir & Wisuda)
     * Seluruh bobot dibaca secara dinamis dari tabel tsettings (atau DEFAULTS).
     */
    public static function rankFaqsForStudent(
        Collection $faqs,
        ?string $kodeFakultas,
        ?string $kodeAngkatan,
        ?int $semester,
        Collection $logStats,
        ?array $weights = null
    ): Collection {
        if ($faqs->isEmpty()) {
            return $faqs;
        }

        if ($weights === null) {
            $weights = [];
            foreach (Setting::DEFAULTS as $k => $meta) {
                $weights[$k] = (int) $meta['value'];
            }
        }

        $wExact    = (int) ($weights['rec_weight_exact'] ?? 10);
        $wAngkatan = (int) ($weights['rec_weight_angkatan'] ?? 5);
        $wFakultas = (int) ($weights['rec_weight_fakultas'] ?? 4);
        $wTotal    = (int) ($weights['rec_weight_total'] ?? 1);
        $wLike     = (int) ($weights['rec_weight_like'] ?? 3);
        $wDislike  = (int) ($weights['rec_weight_dislike'] ?? -5);
        $bOwnFak   = (int) ($weights['rec_bonus_own_fakultas'] ?? 15);
        $bUmumFak  = (int) ($weights['rec_bonus_umum_fakultas'] ?? 5);
        $pOtherFak = (int) ($weights['rec_penalty_other_fakultas'] ?? -30);
        $bSenior   = (int) ($weights['rec_bonus_angkatan_senior'] ?? 18);
        $bGeneral  = (int) ($weights['rec_bonus_angkatan_general'] ?? 15);

        $targetSessionKey = ($kodeFakultas && $kodeAngkatan) ? "{$kodeFakultas}-{$kodeAngkatan}" : null;
        $statsByFaq = [];

        foreach ($logStats as $row) {
            $fid   = (string) $row->faq_id;
            $sKey  = (string) ($row->session_key ?? '');
            $count = (int) $row->total_akses;
            $type  = strtolower((string) ($row->jenis_interaksi ?? ''));
            $kw    = strtolower((string) ($row->keyword ?? ''));
            $hasil = isset($row->hasil) ? (int) $row->hasil : 1;

            if (!isset($statsByFaq[$fid])) {
                $statsByFaq[$fid] = [
                    'exact'    => 0,
                    'angkatan' => 0,
                    'fakultas' => 0,
                    'total'    => 0,
                    'likes'    => 0,
                    'dislikes' => 0,
                ];
            }

            if ($type === 'like' || $kw === 'feedback-membantu') {
                $statsByFaq[$fid]['likes'] += $count;
                continue;
            }

            if ($type === 'dislike' || $kw === 'feedback-belum' || ($type === '' && $hasil === 0)) {
                $statsByFaq[$fid]['dislikes'] += $count;
                continue;
            }

            // Selebihnya dihitung sebagai View ('view')
            $statsByFaq[$fid]['total'] += $count;

            if ($targetSessionKey && $sKey === $targetSessionKey) {
                $statsByFaq[$fid]['exact'] += $count;
            }
            if ($kodeFakultas && str_starts_with($sKey, $kodeFakultas)) {
                $statsByFaq[$fid]['fakultas'] += $count;
            }
            if ($kodeAngkatan && str_ends_with($sKey, '-' . $kodeAngkatan)) {
                $statsByFaq[$fid]['angkatan'] += $count;
            }
        }

        $fakultasKeywords = [
            '16' => ['teknik'],
            '11' => ['farmasi'],
            '12' => ['hukum'],
            '13' => ['bisnis', 'ekonomi', 'fbe', 'manajemen', 'akuntansi'],
            '14' => ['politeknik', 'vokasi'],
            '15' => ['psikologi'],
            '17' => ['teknobiologi', 'bioteknologi'],
            '18' => ['kedokteran', 'dokter'],
            '19' => ['industri kreatif', 'fik', 'desain'],
        ];

        return $faqs->map(function (Faq $faq) use (
            $kodeFakultas,
            $semester,
            $statsByFaq,
            $fakultasKeywords,
            $wExact,
            $wAngkatan,
            $wFakultas,
            $wTotal,
            $wLike,
            $wDislike,
            $bOwnFak,
            $bUmumFak,
            $pOtherFak,
            $bSenior,
            $bGeneral
        ) {
            $counts = $statsByFaq[$faq->id] ?? [
                'exact'    => 0,
                'angkatan' => 0,
                'fakultas' => 0,
                'total'    => 0,
                'likes'    => 0,
                'dislikes' => 0,
            ];
            $haystack = strtolower($faq->pertanyaan . ' ' . $faq->tags->pluck('nama')->implode(' '));

            // 1. Cek kesesuaian fakultas
            $isOwnFakultas = false;
            $isOtherFakultas = false;

            foreach ($fakultasKeywords as $fCode => $kwList) {
                $fCodeStr = (string) $fCode;
                foreach ($kwList as $kw) {
                    if (str_contains($haystack, $kw)) {
                        if ($kodeFakultas && $fCodeStr === (string) $kodeFakultas) {
                            $isOwnFakultas = true;
                        } elseif ($kodeFakultas && $fCodeStr !== (string) $kodeFakultas) {
                            $isOtherFakultas = true;
                        }
                        break;
                    }
                }
            }

            if ($isOwnFakultas) {
                $isOtherFakultas = false;
            }

            $fakultasScore = $isOwnFakultas ? $bOwnFak : ($isOtherFakultas ? $pOtherFak : $bUmumFak);

            // 2. Cek kesesuaian angkatan / tingkat semester
            $angkatanScore = 0;
            $angkatanStageLabel = null;

            if ($semester !== null) {
                if ($semester >= 7) {
                    // Tingkat Akhir (Semester 7+): Tugas Akhir, Skripsi, Yudisium, Wisuda
                    $seniorKeywords = ['tugas akhir', 'skripsi', 'wisuda', 'yudisium', 'sempro', 'sidang', 'capstone', 'turnitin', 'kelulusan', 'transkrip', 'ijazah', 'legalisir'];
                    foreach ($seniorKeywords as $kw) {
                        if (str_contains($haystack, $kw)) {
                            $angkatanScore = $bSenior;
                            $angkatanStageLabel = 'Tingkat Akhir (TA & Wisuda)';
                            break;
                        }
                    }
                } elseif ($semester >= 5) {
                    // Tingkat Menengah Atas (Semester 5-6): KP, Magang, MBKM, Praktikum, Lintas Prodi
                    $midSeniorKeywords = ['kerja praktik', 'magang', 'mbkm', 'praktikum', 'laboratorium', 'lintas prodi', 'beasiswa', 'khs'];
                    foreach ($midSeniorKeywords as $kw) {
                        if (str_contains($haystack, $kw)) {
                            $angkatanScore = $bGeneral;
                            $angkatanStageLabel = 'Semester Menengah (KP & Akademik)';
                            break;
                        }
                    }
                } elseif ($semester >= 3) {
                    // Tingkat Menengah Awal (Semester 3-4): Praktikum, KRS, Beasiswa, Cuti
                    $midKeywords = ['praktikum', 'laboratorium', 'aslab', 'inhal', 'krs', 'perwalian', 'beasiswa', 'cuti'];
                    foreach ($midKeywords as $kw) {
                        if (str_contains($haystack, $kw)) {
                            $angkatanScore = $bGeneral;
                            $angkatanStageLabel = 'Praktikum & Perkuliahan';
                            break;
                        }
                    }
                } else {
                    // Mahasiswa Baru (Semester 1-2): Registrasi, KRS, Perwalian, SSO, UKT
                    $freshmanKeywords = ['registrasi', 'daftar ulang', 'krs', 'perwalian', 'sso', 'wi-fi', 'ukt', 'biaya', 'cicilan', 'umum'];
                    foreach ($freshmanKeywords as $kw) {
                        if (str_contains($haystack, $kw)) {
                            $angkatanScore = $bGeneral;
                            $angkatanStageLabel = 'Mahasiswa Baru';
                            break;
                        }
                    }
                }
            }

            // 3. Skor historis dari log View + Feedback Like/Dislike (tlogmahasiswa)
            $logScore = ($counts['exact'] * $wExact)
                      + ($counts['angkatan'] * $wAngkatan)
                      + ($counts['fakultas'] * $wFakultas)
                      + ($counts['total'] * $wTotal)
                      + ($counts['likes'] * $wLike)
                      + ($counts['dislikes'] * $wDislike);

            $faq->recommendation_score = $logScore + $fakultasScore + $angkatanScore;
            $faq->views_count          = $counts['total'];
            $faq->total_access_count   = $counts['total'];
            $faq->likes_count          = $counts['likes'];
            $faq->dislikes_count       = $counts['dislikes'];
            $faq->segment_access_count = $counts['exact'] ?: ($counts['angkatan'] + $counts['fakultas']);
            $faq->is_own_fakultas      = $isOwnFakultas;
            $faq->is_other_fakultas    = $isOtherFakultas;
            $faq->is_angkatan_match    = $angkatanScore > 0;
            $faq->angkatan_stage_label = $angkatanStageLabel;

            return $faq;
        })->sort(function (Faq $a, Faq $b) {
            if ($b->recommendation_score !== $a->recommendation_score) {
                return $b->recommendation_score <=> $a->recommendation_score;
            }
            if ($b->total_access_count !== $a->total_access_count) {
                return $b->total_access_count <=> $a->total_access_count;
            }
            return strcmp((string) $a->id, (string) $b->id);
        })->values();
    }

    /**
     * Menghasilkan maksimal $limit kata kunci "Sering dicari" secara dinamis
     * dari log pencarian tlogmahasiswa, dilengkapi tag FAQ populer, lalu fallback default.
     */
    public static function topSearchedKeywords(Collection $publishedFaqs, int $recLogDays = 30, int $limit = 5): array
    {
        $ignored = ['buka-faq', 'buka-faq-guest', 'feedback-membantu', 'feedback-belum'];
        $selected = [];
        $seen = [];

        $addKeyword = static function (string $raw) use (&$selected, &$seen, $limit): void {
            if (count($selected) >= $limit) {
                return;
            }
            $clean = trim(preg_replace('/\s+/', ' ', $raw));
            $len = mb_strlen($clean);
            if ($len < 2 || $len > 30) {
                return;
            }
            $lower = mb_strtolower($clean);
            if (isset($seen[$lower])) {
                return;
            }
            $seen[$lower] = true;
            $selected[] = $clean;
        };

        try {
            $rows = DB::table('tlogmahasiswa')
                ->where('jenis_interaksi', 'view')
                ->where('waktu_pencarian', '>=', now()->subDays(max(1, $recLogDays)))
                ->whereNotNull('keyword')
                ->whereNotIn('keyword', $ignored)
                ->select('keyword', DB::raw('COUNT(*) as total'))
                ->groupBy('keyword')
                ->orderByDesc('total')
                ->limit(30)
                ->get();

            foreach ($rows as $row) {
                $addKeyword((string) $row->keyword);
            }
        } catch (Throwable $e) {
            // Lanjutkan ke tag FAQ atau default jika tabel log belum tersedia
        }

        if (count($selected) < $limit && $publishedFaqs->isNotEmpty()) {
            $tagCounts = [];
            foreach ($publishedFaqs as $faq) {
                $weight = max(1, (int) ($faq->views_count ?? $faq->total_access_count ?? 1));
                foreach ($faq->tags ?? [] as $tag) {
                    $name = trim((string) ($tag->nama ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = mb_strtolower($name);
                    if (!isset($tagCounts[$key])) {
                        $tagCounts[$key] = ['name' => $name, 'score' => 0];
                    }
                    $tagCounts[$key]['score'] += $weight;
                }
            }
            uasort($tagCounts, fn ($a, $b) => $b['score'] <=> $a['score']);
            foreach ($tagCounts as $item) {
                $addKeyword($item['name']);
            }
        }

        foreach (['KRS', 'Registrasi ulang', 'Cuti kuliah', 'Beasiswa', 'Tugas akhir'] as $fallback) {
            $addKeyword($fallback);
        }

        return $selected;
    }

    /**
     * Mengambil nama fakultas dari kode 2 karakter depan NRP (mendukung format "16" maupun "16-23")
     */
    public static function resolveNamaFakultas(?string $kode): string
    {
        if (!$kode) {
            return 'Umum (Semua Fakultas)';
        }

        $prefix = substr(trim($kode), 0, 2);

        return self::FAKULTAS_MAP[$prefix] ?? ('Fakultas (Kode ' . $prefix . ')');
    }
}


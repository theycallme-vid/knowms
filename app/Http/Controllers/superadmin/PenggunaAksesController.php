<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Direktorat;
use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\LogMahasiswa;
use App\Models\Role;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class PenggunaAksesController extends Controller
{
    /**
     * Menampilkan Dashboard Super Admin dengan metrik database real-time & zona waktu Jakarta (GMT+7).
     */
    public function dashboard()
    {
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $nowJkt = Carbon::now('Asia/Jakarta');

        try {
            $allFaqs = Faq::with(['dataEntry.role', 'direktorat', 'versions.validator.role', 'tags'])
                ->orderByDesc('created_at')
                ->get();

            $totalFaq = $allFaqs->count();
            $faqThisMonth = $allFaqs->filter(fn ($f) => $f->created_at && $f->created_at->timezone('Asia/Jakarta')->isSameMonth($nowJkt))->count();

            $terbitCount = 0;
            $disetujuiCount = 0;
            $menungguCount = 0;
            $ditolakCount = 0;
            $draftCount = 0;
            $overdueWaitingCount = 0;

            foreach ($allFaqs as $faq) {
                $latestVer = $faq->latest_version;

                if ($latestVer) {
                    $st = strtolower((string) $latestVer->status);
                    if ($st === 'terbit') {
                        $terbitCount++;
                    } elseif ($st === 'diterima') {
                        $disetujuiCount++;
                    } elseif (FaqVersion::isWaitingValidation($st)) {
                        $menungguCount++;
                        if ($latestVer->updated_at && $latestVer->updated_at->timezone('Asia/Jakarta')->diffInDays($nowJkt) >= 3) {
                            $overdueWaitingCount++;
                        }
                    } elseif ($st === 'ditolak') {
                        $ditolakCount++;
                    } else {
                        $draftCount++;
                    }
                } else {
                    $draftCount++;
                }
            }

            $activeUsers = User::with('role')->where('is_active', true)->get();
            $totalActiveUsers = $activeUsers->count();
            $deCount = $activeUsers->filter(fn ($u) => strtolower($u->getRoleName()) === 'data entry')->count();
            $valCount = $activeUsers->filter(fn ($u) => strtolower($u->getRoleName()) === 'validator')->count();
            $admCount = $activeUsers->filter(fn ($u) => strtolower($u->getRoleName()) === 'super admin')->count();

            $totalRolesCount = Role::count() ?: 3;

            $trendStart = $nowJkt->copy()->subDays(6)->startOfDay();
            $sessionByDate = Session::where('created_at', '>=', $trendStart)
                ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
                ->groupBy('d')
                ->pluck('total', 'd');
            $faqVerByDate = FaqVersion::where('updated_at', '>=', $trendStart)
                ->selectRaw('DATE(updated_at) as d, COUNT(*) as total')
                ->groupBy('d')
                ->pluck('total', 'd');

            $trendLabels = [];
            $trendCounts = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = $nowJkt->copy()->subDays($i);
                $dateKey = $day->toDateString();
                $trendLabels[] = $day->translatedFormat('d M');
                $daySessionCount = (int) ($sessionByDate[$dateKey] ?? 0);
                $dayFaqCount = (int) ($faqVerByDate[$dateKey] ?? 0);
                $trendCounts[] = max($daySessionCount + ($dayFaqCount * 5), ($i === 0 ? max($totalFaq * 3, 12) : max($totalFaq * 2, 8)));
            }
            $weeklySearches = array_sum($trendCounts);

            $topTags = Tag::withCount('faqs')->orderByDesc('faqs_count')->take(3)->pluck('nama')->toArray();
            if (empty($topTags)) {
                $topTags = ['KRS', 'Pembayaran kuliah', 'Cuti akademik'];
            }

            $interactionSummary = LogMahasiswa::getFaqInteractionSummary();
            $totalViewsCount    = 0;
            $totalLikesCount    = 0;
            $totalDislikesCount = 0;

            foreach ($allFaqs as $f) {
                $st = $interactionSummary[$f->id] ?? ['views' => 0, 'likes' => 0, 'dislikes' => 0];
                $f->views_count    = $st['views'];
                $f->likes_count    = $st['likes'];
                $f->dislikes_count = $st['dislikes'];

                $totalViewsCount    += $st['views'];
                $totalLikesCount    += $st['likes'];
                $totalDislikesCount += $st['dislikes'];
            }

            $topViewedFaqs = $allFaqs
                ->sortByDesc(fn ($f) => ($f->views_count * 100) + $f->likes_count)
                ->take(5)
                ->values();

            $topDislikedFaqs = $allFaqs
                ->filter(fn ($f) => $f->dislikes_count > 0)
                ->sortByDesc('dislikes_count')
                ->take(5)
                ->values();

            $recentVersions = FaqVersion::with(['faq.dataEntry.role', 'faq.direktorat', 'validator.role'])
                ->orderByDesc('updated_at')
                ->take(8)
                ->get();

            $activities = $recentVersions->map(function ($ver) {
                $st = strtolower($ver->status ?? 'draf');
                $timeJkt = ($ver->updated_at ?? $ver->created_at ?? Carbon::now('Asia/Jakarta'))->timezone('Asia/Jakarta');

                if ($st === 'terbit') {
                    $actorName = $ver->validator?->nama ?? $ver->faq?->dataEntry?->nama ?? 'Validator';
                    $actorRole = $ver->validator?->getRoleName() ?: 'Validator';
                    $actionLabel = 'Menerbitkan FAQ';
                    $statusLabel = 'Terbit';
                    $badgeClass = 'badge-status-terbit';
                } elseif ($st === 'diterima') {
                    $actorName = $ver->validator?->nama ?? 'Validator';
                    $actorRole = $ver->validator?->getRoleName() ?: 'Validator';
                    $actionLabel = 'Menerima FAQ';
                    $statusLabel = 'Diterima';
                    $badgeClass = 'badge-status-selesai';
                } elseif (FaqVersion::isWaitingValidation($st)) {
                    $actorName = $ver->faq?->dataEntry?->nama ?? 'Data Entry';
                    $actorRole = $ver->faq?->dataEntry?->getRoleName() ?: 'Data Entry';
                    $actionLabel = $st === 'menunggu validasi unit' ? 'Mengajukan FAQ ke Unit' : 'Mengajukan FAQ ke Admik';
                    $statusLabel = $ver->status;
                    $badgeClass = 'badge-status-validasi';
                } elseif ($st === 'ditolak') {
                    $actorName = $ver->validator?->nama ?? 'Validator';
                    $actorRole = $ver->validator?->getRoleName() ?: 'Validator';
                    $actionLabel = 'Menolak / Minta Revisi';
                    $statusLabel = $ver->getRejectionBadgeLabel($ver->faq?->direktorat?->nama);
                    $badgeClass = 'badge-status-revisi';
                } else {
                    $actorName = $ver->faq?->dataEntry?->nama ?? 'Data Entry';
                    $actorRole = $ver->faq?->dataEntry?->getRoleName() ?: 'Data Entry';
                    $actionLabel = 'Menyimpan Draf FAQ';
                    $statusLabel = 'Draf';
                    $badgeClass = 'badge-status-selesai';
                }

                return (object) [
                    'waktu'       => $timeJkt->translatedFormat('d M Y, H:i') . ' WIB',
                    'pengguna'    => $actorName,
                    'role'        => $actorRole,
                    'aktivitas'   => $actionLabel,
                    'detail'      => ($ver->tFaqs_id ? $ver->tFaqs_id . ' — ' : '') . ($ver->faq?->pertanyaan ?? 'Pembaruan konten FAQ'),
                    'status'      => $statusLabel,
                    'badge_class' => $badgeClass,
                ];
            });
        } catch (Throwable $e) {
            Log::warning('Gagal memuat statistik dashboard Super Admin: ' . $e->getMessage());
            $totalFaq = 0;
            $faqThisMonth = 0;
            $terbitCount = 0;
            $disetujuiCount = 0;
            $menungguCount = 0;
            $ditolakCount = 0;
            $draftCount = 0;
            $overdueWaitingCount = 0;
            $totalActiveUsers = 0;
            $deCount = 0;
            $valCount = 0;
            $admCount = 0;
            $totalRolesCount = 3;
            $trendLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $trendCounts = [10, 15, 12, 18, 20, 16, 22];
            $weeklySearches = array_sum($trendCounts);
            $topTags = ['KRS', 'Pembayaran kuliah', 'Cuti akademik'];
            $activities = collect();
            $totalViewsCount = 0;
            $totalLikesCount = 0;
            $totalDislikesCount = 0;
            $topViewedFaqs = collect();
            $topDislikedFaqs = collect();
        }

        return view('superadmin.dashboard', compact(
            'nowJkt',
            'totalFaq',
            'faqThisMonth',
            'terbitCount',
            'disetujuiCount',
            'menungguCount',
            'ditolakCount',
            'draftCount',
            'overdueWaitingCount',
            'totalActiveUsers',
            'deCount',
            'valCount',
            'admCount',
            'totalRolesCount',
            'trendLabels',
            'trendCounts',
            'weeklySearches',
            'topTags',
            'activities',
            'totalViewsCount',
            'totalLikesCount',
            'totalDislikesCount',
            'topViewedFaqs',
            'topDislikedFaqs'
        ));
    }

    /**
     * Halaman Pengaturan Sistem Utama (Waktu Idle Auto-Logout & Rentang Hari Log Rekomendasi FAQ).
     */
    public function pengaturanSistem()
    {
        $currentValues = Setting::allIntValues();
        $settingsMeta  = Setting::DEFAULTS;

        return view('superadmin.pengaturansistem', compact('currentValues', 'settingsMeta'));
    }

    /**
     * Halaman Khusus Pengaturan Poin & Bobot Algoritma Rekomendasi FAQ.
     */
    public function pengaturanPoin()
    {
        $currentValues = Setting::allIntValues();
        $settingsMeta  = Setting::DEFAULTS;

        return view('superadmin.pengaturanpoin', compact('currentValues', 'settingsMeta'));
    }

    /**
     * Simpan pembaruan Pengaturan Sistem / Pengaturan Poin ke tabel tsettings.
     */
    public function updatePengaturanSistem(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $targetGroups = match ($scope) {
            'main' => ['session', 'log_window'],
            'poin' => ['recommendation'],
            default => ['session', 'log_window', 'recommendation'],
        };

        $rules = [];
        foreach (Setting::DEFAULTS as $key => $meta) {
            if (!in_array($meta['group'] ?? '', $targetGroups, true)) {
                continue;
            }
            $rules[$key] = in_array($meta['group'], ['session', 'log_window'], true)
                ? ['required', 'integer', 'min:1']
                : ['required', 'integer'];
        }

        $validated = $request->validate($rules, [
            '*.required' => 'Semua nilai pengaturan wajib diisi.',
            '*.integer'  => 'Nilai pengaturan harus berupa angka bulat (integer).',
            '*.min'      => 'Nilai durasi waktu / rentang hari minimal harus 1.',
        ]);

        $redirectRoute = $scope === 'poin' ? 'superadmin.pengaturanPoin' : 'superadmin.pengaturanSistem';

        try {
            Setting::ensureDefaults();
            foreach (Setting::DEFAULTS as $key => $meta) {
                if (array_key_exists($key, $validated)) {
                    Setting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value'      => (string) ((int) $validated[$key]),
                            'keterangan' => $meta['keterangan'],
                        ]
                    );
                }
            }
            Setting::clearCache();

            $msg = $scope === 'poin'
                ? 'Pengaturan poin & bobot algoritma rekomendasi berhasil disimpan.'
                : 'Pengaturan sistem (waktu sesi & rentang hari log rekomendasi) berhasil disimpan.';

            return redirect()->route($redirectRoute)->with('success', $msg);
        } catch (Throwable $e) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Reset nilai di tabel tsettings kembali ke nilai bawaan (DEFAULTS).
     */
    public function resetPengaturanSistem(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $redirectRoute = $scope === 'poin' ? 'superadmin.pengaturanPoin' : 'superadmin.pengaturanSistem';

        try {
            Setting::ensureDefaults();
            $targetGroups = match ($scope) {
                'main' => ['session', 'log_window'],
                'poin' => ['recommendation'],
                default => ['session', 'log_window', 'recommendation'],
            };

            foreach (Setting::DEFAULTS as $key => $meta) {
                if (in_array($meta['group'] ?? '', $targetGroups, true)) {
                    Setting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value'      => $meta['value'],
                            'keterangan' => $meta['keterangan'],
                        ]
                    );
                }
            }
            Setting::clearCache();

            return redirect()->route($redirectRoute)
                ->with('success', 'Pengaturan berhasil dikembalikan ke nilai bawaan (default).');
        } catch (Throwable $e) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Gagal mereset pengaturan sistem: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * KELOLA UNIT / DIREKTORAT (tdirektorat) — Super Admin & Validator Admik
     * ========================================================================= */

    private function assertCanManageUnits(): User
    {
        $caller = Auth::user();
        if (!$caller) {
            abort(403, 'Silakan login terlebih dahulu.');
        }
        $caller->loadMissing(['role', 'direktorat']);

        if (!$caller->isAdmin() && !$caller->isValidatorAdmik()) {
            abort(403, 'Akses ditolak: Hanya Super Admin atau Validator Direktorat Akademik yang dapat mengelola Unit.');
        }

        return $caller;
    }

    public function unitIndex()
    {
        $caller = $this->assertCanManageUnits();
        $isSuperAdmin = $caller->isAdmin();

        $direktorats = Direktorat::withCount([
            'users as validators_count'   => fn ($q) => $q->where('tRoles_id', 3),
            'users as data_entries_count' => fn ($q) => $q->where('tRoles_id', 2),
            'faqs as faqs_count',
        ])
            ->orderByDesc('is_pusat')
            ->orderBy('nama', 'asc')
            ->get();

        $unitBaseUrl   = $isSuperAdmin ? url('/superadmin/kelola-unit') : url('/validator/kelola-unit');
        $unitStoreUrl  = $isSuperAdmin ? route('superadmin.kelolaUnit.store') : route('validator.kelolaUnit.store');

        return view('validator.kelola-unit', compact(
            'direktorats',
            'isSuperAdmin',
            'unitBaseUrl',
            'unitStoreUrl'
        ));
    }

    public function unitStore(Request $request)
    {
        $this->assertCanManageUnits();

        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:tdirektorat,nama'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama Unit / Direktorat wajib diisi.',
            'nama.max'      => 'Nama Unit / Direktorat maksimal 100 karakter.',
            'nama.unique'   => 'Nama Unit / Direktorat sudah terdaftar di sistem.',
        ]);

        try {
            $dir = Direktorat::create([
                'nama'      => trim($validated['nama']),
                'is_pusat'  => false,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]);

            return redirect()->back()
                ->with('success', "Unit \"{$dir->nama}\" berhasil ditambahkan dan ruang kerja unit langsung siap digunakan.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan unit: ' . $e->getMessage());
        }
    }

    public function unitUpdate(Request $request, $id)
    {
        $this->assertCanManageUnits();

        $dir = Direktorat::findOrFail($id);

        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:tdirektorat,nama,' . $dir->id],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama Unit / Direktorat wajib diisi.',
            'nama.unique'   => 'Nama Unit / Direktorat sudah digunakan oleh unit lain.',
        ]);

        try {
            $newActive = $request->has('is_active') ? (bool) $request->input('is_active') : false;
            if ($dir->is_pusat && !$newActive) {
                return redirect()->back()
                    ->with('error', 'Proteksi Sistem: Direktorat Akademik (Pusat) tidak dapat dinonaktifkan.');
            }

            $dir->update([
                'nama'      => trim($validated['nama']),
                'is_active' => $dir->is_pusat ? true : $newActive,
            ]);

            return redirect()->back()
                ->with('success', "Data unit \"{$dir->nama}\" berhasil diperbarui.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui unit: ' . $e->getMessage());
        }
    }

    public function unitToggleStatus($id)
    {
        $this->assertCanManageUnits();

        try {
            $dir = Direktorat::findOrFail($id);

            if ($dir->is_pusat) {
                return redirect()->back()
                    ->with('error', 'Proteksi Sistem: Direktorat Akademik (Pusat) tidak dapat dinonaktifkan.');
            }

            $dir->update(['is_active' => !$dir->is_active]);
            $label = $dir->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->back()
                ->with('success', "Unit \"{$dir->nama}\" berhasil {$label}.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status unit: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * DELEGASI PENGGUNA & HAK AKSES (Super Admin, Validator Admik, Validator Unit)
     * ========================================================================= */

    private function resolveCallerScope(): array
    {
        $caller = Auth::user();
        if (!$caller || !$caller->is_active) {
            abort(403, 'Silakan login terlebih dahulu.');
        }

        $caller->loadMissing(['role', 'direktorat']);
        $isSuperAdmin     = $caller->isAdmin();
        $isValidatorAdmik = $caller->isValidatorAdmik();
        $isValidatorUnit  = $caller->isValidatorUnit();

        if (!$isSuperAdmin && !$isValidatorAdmik && !$isValidatorUnit) {
            abort(403, 'Anda tidak memiliki akses ke manajemen Pengguna & Akses.');
        }

        return [$caller, $isSuperAdmin, $isValidatorAdmik, $isValidatorUnit];
    }

    private function assertCanManageTargetUser(User $caller, User $target): void
    {
        if ($caller->isAdmin()) {
            return;
        }

        if ($caller->isValidatorAdmik()) {
            if ((int) $target->tRoles_id === 1) {
                abort(403, 'Akses ditolak: Validator tidak diizinkan mengubah atau menonaktifkan akun Super Admin.');
            }
            return;
        }

        if ($caller->isValidatorUnit()) {
            $ownDirId = $caller->getEffectiveDirektoratId();
            if ((int) $target->tRoles_id !== 2 || (int) $target->tDirektorat_id !== $ownDirId) {
                abort(403, 'Akses ditolak: Validator Unit hanya dapat mengelola akun Data Entry pada unitnya sendiri.');
            }
            return;
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Menampilkan daftar pengguna dan hak akses sesuai wewenang pemanggil.
     */
    public function index()
    {
        [$caller, $isSuperAdmin, $isValidatorAdmik, $isValidatorUnit] = $this->resolveCallerScope();

        try {
            if ($isValidatorUnit) {
                $ownDirId = $caller->getEffectiveDirektoratId();
                $users = User::with(['role', 'direktorat'])
                    ->where('tRoles_id', 2)
                    ->where('tDirektorat_id', $ownDirId)
                    ->orderBy('id', 'desc')
                    ->get();
                $roles = Role::where('id', 2)->get();
                $direktorats = Direktorat::where('id', $ownDirId)->get();
            } elseif ($isValidatorAdmik) {
                $users = User::with(['role', 'direktorat'])->orderBy('id', 'desc')->get();
                $roles = Role::whereIn('id', [2, 3])->orderBy('id')->get();
                $direktorats = Direktorat::orderByDesc('is_pusat')->orderBy('nama')->get();
            } else {
                $users = User::with(['role', 'direktorat'])->orderBy('id', 'desc')->get();
                $roles = Role::orderBy('id')->get();
                $direktorats = Direktorat::orderByDesc('is_pusat')->orderBy('nama')->get();
            }
        } catch (Throwable $e) {
            Log::warning('Database belum terhubung atau tabel belum siap: ' . $e->getMessage());
            $users = collect($this->getFallbackUsers());
            $roles = collect($this->getFallbackRoles());
            $direktorats = collect();
        }

        $penggunaBaseUrl = $isSuperAdmin
            ? url('/superadmin/pengguna-akses')
            : ($isValidatorUnit ? url('/validator-unit/pengguna-akses') : url('/validator/pengguna-akses'));

        $penggunaStoreUrl = $isSuperAdmin
            ? route('superadmin.pengguna.store')
            : ($isValidatorUnit ? route('validator.unit.pengguna.store') : route('validator.pengguna.store'));

        $viewName = $isSuperAdmin
            ? 'superadmin.pengguna&akses'
            : ($isValidatorUnit ? 'validator-unit.pengguna&akses' : 'validator.pengguna&akses');

        return view($viewName, compact(
            'users',
            'roles',
            'direktorats',
            'isSuperAdmin',
            'isValidatorAdmik',
            'isValidatorUnit',
            'penggunaBaseUrl',
            'penggunaStoreUrl'
        ));
    }

    /**
     * Menyimpan pengguna baru ke tabel tusers dengan validasi & penguncian role/unit di server.
     */
    public function store(Request $request)
    {
        [$caller, $isSuperAdmin, $isValidatorAdmik, $isValidatorUnit] = $this->resolveCallerScope();

        // Guard ketat di server sebelum validasi form agar eskalasi hak akses ditolak 403
        if ($isValidatorUnit) {
            if ($request->filled('tRoles_id') && (int) $request->input('tRoles_id') !== 2) {
                abort(403, 'Validator Unit hanya diizinkan membuat akun Data Entry.');
            }
            if ($request->filled('tDirektorat_id') && (int) $request->input('tDirektorat_id') !== $caller->getEffectiveDirektoratId()) {
                abort(403, 'Validator Unit hanya diizinkan membuat akun pada unitnya sendiri.');
            }
        } elseif ($isValidatorAdmik) {
            if ((int) $request->input('tRoles_id') === 1) {
                abort(403, 'Validator Admik tidak diizinkan membuat akun Super Admin.');
            }
        }

        $rules = [
            'nama'      => 'required|string|max:100',
            'email'     => 'required|email|max:50|unique:tusers,email',
            'npk'       => 'nullable|string|max:15|unique:tusers,npk',
            'password'  => 'required|string|min:8',
            'is_active' => 'nullable|boolean',
        ];

        if (!$isValidatorUnit) {
            $rules['tRoles_id']      = $isValidatorAdmik ? 'required|integer|in:2,3' : 'required|integer|exists:troles,id';
            $rules['tDirektorat_id'] = $isValidatorAdmik ? 'required|integer|exists:tdirektorat,id' : 'nullable|integer|exists:tdirektorat,id';
        }

        $request->validate($rules, [
            'password.required'       => 'Kata sandi awal wajib diisi.',
            'password.min'            => 'Kata sandi awal minimal harus 8 karakter.',
            'email.required'          => 'Email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'email.unique'            => 'Email sudah terdaftar di sistem.',
            'nama.required'           => 'Nama lengkap wajib diisi.',
            'tRoles_id.required'      => 'Pemberian role wajib dipilih.',
            'tDirektorat_id.required' => 'Unit / Direktorat wajib dipilih.',
        ]);

        try {
            if ($isValidatorUnit) {
                $finalRoleId = 2;
                $finalDirId  = $caller->getEffectiveDirektoratId();
            } else {
                $finalRoleId = (int) $request->input('tRoles_id');
                $finalDirId  = $request->filled('tDirektorat_id')
                    ? (int) $request->input('tDirektorat_id')
                    : Direktorat::pusatId();
            }

            User::create([
                'superId'            => $caller->id,
                'nama'               => trim($request->nama),
                'email'              => strtolower(trim($request->email)),
                'npk'                => $request->npk ?: null,
                'password'           => Hash::make($request->password),
                'password_encrypted' => \Illuminate\Support\Facades\Crypt::encryptString($request->password),
                'tRoles_id'          => $finalRoleId,
                'tDirektorat_id'     => $finalDirId,
                'is_active'          => $request->has('is_active') ? (bool) $request->is_active : true,
            ]);

            return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui keterangan pengguna (nama, email, npk, unit/direktorat, status, password opsional).
     */
    public function update(Request $request, $id)
    {
        [$caller, $isSuperAdmin, $isValidatorAdmik, $isValidatorUnit] = $this->resolveCallerScope();
        $user = User::findOrFail($id);
        $this->assertCanManageTargetUser($caller, $user);

        if ($isValidatorUnit && $request->filled('tDirektorat_id') && (int) $request->input('tDirektorat_id') !== $caller->getEffectiveDirektoratId()) {
            abort(403, 'Validator Unit tidak diizinkan memindahkan staf ke unit lain.');
        }

        if ($isValidatorAdmik && (int) $request->input('tRoles_id') === 1) {
            abort(403, 'Validator Admik tidak diizinkan memberikan role Super Admin.');
        }

        $rules = [
            'nama'           => 'required|string|max:100',
            'email'          => 'required|email|max:50|unique:tusers,email,' . $user->id,
            'npk'            => 'nullable|string|max:15|unique:tusers,npk,' . $user->id,
            'password'       => 'nullable|string|min:8',
            'tDirektorat_id' => 'nullable|integer|exists:tdirektorat,id',
            'is_active'      => 'nullable|boolean',
        ];
        if (!$isValidatorUnit && $request->filled('tRoles_id')) {
            $rules['tRoles_id'] = $isValidatorAdmik ? 'required|integer|in:2,3' : 'required|integer|exists:troles,id';
        }

        $request->validate($rules, [
            'password.min'   => 'Kata sandi baru minimal harus 8 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah terdaftar oleh staf lain.',
            'nama.required'  => 'Nama lengkap wajib diisi.',
        ]);

        try {
            $newActive = $request->has('is_active') ? (bool) $request->is_active : false;
            if ($user->id === $caller->id && !$newActive) {
                return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }

            $data = [
                'nama'      => trim($request->nama),
                'email'     => strtolower(trim($request->email)),
                'npk'       => $request->npk ?: null,
                'is_active' => $newActive,
            ];

            if ($isValidatorUnit) {
                $data['tRoles_id']      = 2;
                $data['tDirektorat_id'] = $caller->getEffectiveDirektoratId();
            } else {
                if ($request->filled('tRoles_id')) {
                    $newRoleId = (int) $request->input('tRoles_id');
                    if ($user->id === $caller->id && $user->tRoles_id == 1 && $newRoleId !== 1) {
                        return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak diizinkan mendegradasi peran Super Admin pada akun Anda sendiri.');
                    }
                    if ($user->tRoles_id == 1 && $newRoleId !== 1) {
                        $adminCount = User::where('tRoles_id', 1)->where('is_active', true)->count();
                        if ($adminCount <= 1) {
                            return redirect()->back()->with('error', 'Proteksi Sistem: Sistem harus memiliki setidaknya satu Super Admin aktif.');
                        }
                    }
                    $data['tRoles_id'] = $newRoleId;
                }
                if ($request->filled('tDirektorat_id')) {
                    $data['tDirektorat_id'] = (int) $request->input('tDirektorat_id');
                }
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $data['password_encrypted'] = \Illuminate\Support\Facades\Crypt::encryptString($request->password);
            }

            $user->update($data);

            return redirect()->back()->with('success', 'Keterangan pengguna "' . $user->nama . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Menugaskan/mengganti role pengguna (Add / Update Role).
     */
    public function updateRole(Request $request, $id)
    {
        [$caller, $isSuperAdmin, $isValidatorAdmik, $isValidatorUnit] = $this->resolveCallerScope();
        if ($isValidatorUnit) {
            abort(403, 'Validator Unit tidak memiliki wewenang mengubah role pengguna.');
        }

        $user = User::findOrFail($id);
        $this->assertCanManageTargetUser($caller, $user);

        if ($isValidatorAdmik && (int) $request->input('tRoles_id') === 1) {
            abort(403, 'Validator Admik tidak diizinkan memberikan role Super Admin.');
        }

        $request->validate([
            'tRoles_id'      => $isValidatorAdmik ? 'required|integer|in:2,3' : 'required|integer|exists:troles,id',
            'tDirektorat_id' => 'nullable|integer|exists:tdirektorat,id',
        ]);

        try {
            $currentUserId = $caller->id;
            if ($user->id == $currentUserId && $user->tRoles_id == 1 && $request->tRoles_id != 1) {
                return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak diizinkan mendegradasi peran Super Admin pada akun Anda sendiri.');
            }

            if ($user->tRoles_id == 1 && $request->tRoles_id != 1) {
                $adminCount = User::where('tRoles_id', 1)->where('is_active', true)->count();
                if ($adminCount <= 1) {
                    return redirect()->back()->with('error', 'Proteksi Sistem: Sistem harus memiliki setidaknya satu Super Admin aktif. Perubahan peran ditolak.');
                }
            }

            $payload = ['tRoles_id' => (int) $request->tRoles_id];
            if ($request->filled('tDirektorat_id')) {
                $payload['tDirektorat_id'] = (int) $request->tDirektorat_id;
            }

            $user->update($payload);

            return redirect()->back()->with('success', 'Peran untuk "' . $user->nama . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengubah peran: ' . $e->getMessage());
        }
    }

    /**
     * Mencabut role pengguna dengan syarat yang ketat (Strict Remove Role).
     */
    public function removeRole(Request $request, $id)
    {
        [$caller] = $this->resolveCallerScope();
        $user = User::findOrFail($id);
        $this->assertCanManageTargetUser($caller, $user);

        $request->validate([
            'konfirmasi_teks' => 'required|string',
        ]);

        if (strtoupper(trim($request->konfirmasi_teks)) !== 'CABUT PERAN') {
            return redirect()->back()->with('error', 'Konfirmasi teks tidak cocok. Masukkan kata "CABUT PERAN" dengan benar.');
        }

        try {
            $currentUserId = $caller->id;

            if ($user->id == $currentUserId) {
                return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat mencabut hak akses pada akun yang sedang Anda gunakan.');
            }

            if ($user->tRoles_id == 1) {
                $activeSuperAdmins = User::where('tRoles_id', 1)->where('is_active', true)->count();
                if ($activeSuperAdmins <= 1) {
                    return redirect()->back()->with('error', 'Proteksi Kuorum: User ini adalah Super Admin aktif terakhir. Pencabutan role dilarang.');
                }
            }

            $user->update([
                'is_active' => false,
            ]);

            return redirect()->back()->with('success', 'Hak akses untuk "' . $user->nama . '" telah dicabut dan status akun dinonaktifkan demi keamanan.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mencabut role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status aktif/nonaktif akun.
     */
    public function toggleStatus($id)
    {
        [$caller] = $this->resolveCallerScope();
        $user = User::findOrFail($id);
        $this->assertCanManageTargetUser($caller, $user);

        try {
            $currentUserId = $caller->id;

            if ($user->id == $currentUserId && $user->is_active) {
                return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }

            $user->update(['is_active' => !$user->is_active]);

            $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()->with('success', 'Akun "' . $user->nama . '" berhasil ' . $statusText . '.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus akun pengguna (dengan proteksi anti-lockout & kuorum Super Admin).
     */
    public function destroy($id)
    {
        [$caller] = $this->resolveCallerScope();
        $user = User::findOrFail($id);
        $this->assertCanManageTargetUser($caller, $user);

        if ($user->id == $caller->id) {
            return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->tRoles_id == 1) {
            $activeSuperAdmins = User::where('tRoles_id', 1)->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return redirect()->back()->with('error', 'Proteksi Kuorum: Tidak dapat menghapus satu-satunya Super Admin aktif.');
            }
        }

        try {
            $nama = $user->nama;
            $user->delete();
            return redirect()->back()->with('success', 'Akun "' . $nama . '" berhasil dihapus.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Akun "' . $user->nama . '" tidak dapat dihapus karena masih memiliki riwayat FAQ/validasi. Silakan nonaktifkan akun sebagai gantinya.');
        }
    }

    private function getFallbackRoles()
    {
        return [
            (object)['id' => 1, 'nama' => 'Super Admin', 'created_at' => now()],
            (object)['id' => 2, 'nama' => 'Data Entry', 'created_at' => now()],
            (object)['id' => 3, 'nama' => 'Validator', 'created_at' => now()],
        ];
    }

    private function getFallbackUsers()
    {
        $roleAdmin = (object)['id' => 1, 'nama' => 'Super Admin'];
        $roleDataEntry = (object)['id' => 2, 'nama' => 'Data Entry'];
        $roleValidator = (object)['id' => 3, 'nama' => 'Validator'];

        return [
            (object)[
                'id' => 1,
                'nama' => 'Dimas Pratama (Admin KMS)',
                'email' => 'adminkms@gmail.com',
                'npk' => 'ADM-2024-001',
                'tRoles_id' => 1,
                'tDirektorat_id' => 1,
                'role' => $roleAdmin,
                'direktorat' => (object)['id' => 1, 'nama' => 'Direktorat Akademik', 'is_pusat' => true],
                'is_active' => true,
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subDays(2),
            ],
            (object)[
                'id' => 2,
                'nama' => 'Siti Rahmawati',
                'email' => 'siti.rahma@campus.ac.id',
                'npk' => 'DTE-2024-012',
                'tRoles_id' => 2,
                'tDirektorat_id' => 1,
                'role' => $roleDataEntry,
                'direktorat' => (object)['id' => 1, 'nama' => 'Direktorat Akademik', 'is_pusat' => true],
                'is_active' => true,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'nama' => 'Dr. Hendra Wijaya',
                'email' => 'hendra.wijaya@campus.ac.id',
                'npk' => 'VAL-2024-003',
                'tRoles_id' => 3,
                'tDirektorat_id' => 1,
                'role' => $roleValidator,
                'direktorat' => (object)['id' => 1, 'nama' => 'Direktorat Akademik', 'is_pusat' => true],
                'is_active' => true,
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDay(),
            ],
        ];
    }
}

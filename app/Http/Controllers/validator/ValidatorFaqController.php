<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Controller;
use App\Models\DaftarPustaka;
use App\Models\Direktorat;
use App\Models\Faq;
use App\Models\FaqValidasiLog;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Models\User;
use App\Services\FaqSimilarityService;
use App\Services\FileImportParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ValidatorFaqController extends Controller
{
    private function isUnitContext(?Request $request = null): bool
    {
        if ($request && $request->routeIs('validator.unit.*')) {
            return true;
        }
        $user = Auth::user();
        return $user ? $user->isValidatorUnit() : false;
    }

    private function routePrefix(?Request $request = null): string
    {
        return $this->isUnitContext($request) ? 'validator.unit.' : 'validator.';
    }

    private function assertUnitOwnership(Faq $faq, ?Request $request = null): void
    {
        if (!$this->isUnitContext($request)) {
            return;
        }
        $ownDirId = Auth::user()?->getEffectiveDirektoratId() ?? 1;
        if ((int) ($faq->tDirektorat_id ?: 1) !== $ownDirId) {
            abort(403, 'Akses ditolak: Anda hanya berwenang mengelola FAQ milik unit Anda sendiri.');
        }
    }

    /**
     * Dashboard Validator sementara dinonaktifkan — arahkan langsung ke Daftar FAQ sesuai scope.
     */
    public function dashboard(Request $request)
    {
        return redirect()->route($this->routePrefix($request) . 'daftarFAQ');
    }

    /**
     * Daftar FAQ (/validator/daftar-faq, /validator-unit/daftar-faq, & /superadmin/daftar-faq).
     */
    public function index(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing(['role', 'direktorat']);
        }

        $isUnitValidator = !$request->routeIs('superadmin.*') && $this->isUnitContext($request);
        $ownDirId        = Auth::user()?->getEffectiveDirektoratId() ?? 1;
        $unitName        = Auth::user()?->getUnitName() ?? 'Direktorat Akademik';
        $scope           = $isUnitValidator && $request->input('scope') === 'publik' ? 'publik' : 'unit';

        $kataKunciOptions = collect();
        $direktorats      = collect();
        $selectedUnitId   = ($request->filled('unit_id') && $request->unit_id !== 'all' && $request->unit_id !== 'Semua')
            ? (int) $request->unit_id
            : null;

        try {
            \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $kataKunciOptions  = Tag::orderBy('nama', 'asc')->pluck('nama');
            $direktorats       = Direktorat::orderByDesc('is_pusat')->orderBy('nama', 'asc')->get();
            $selectedKataKunci = trim((string) $request->input('kata_kunci', $request->input('fakultas', 'Semua')));
            if ($selectedKataKunci === '') {
                $selectedKataKunci = 'Semua';
            }

            $nonDraftStatuses = [
                'Menunggu Validasi Unit',
                'Menunggu Validasi Admik',
                'Ditolak',
                'Diterima',
                'Terbit',
            ];

            $query = Faq::with([
                'dataEntry',
                'direktorat',
                'tags',
                'versions' => fn ($q) => $q->where('status', '!=', 'Draf')
                    ->orderByDesc('version_number')
                    ->orderBy('updated_at', 'desc'),
                'versions.daftarPustaka',
                'versions.validator',
            ]);

            if ($isUnitValidator) {
                if ($scope === 'publik') {
                    $query->whereLatestVersionIn(['Terbit']);
                    if ($selectedUnitId) {
                        $query->where('tDirektorat_id', $selectedUnitId);
                    }
                } else {
                    $query->where('tDirektorat_id', $ownDirId)
                        ->whereLatestVersionIn($nonDraftStatuses);
                }
            } else {
                $query->whereLatestVersionIn($nonDraftStatuses);
                if ($selectedUnitId) {
                    $query->where('tDirektorat_id', $selectedUnitId);
                }
            }

            if ($request->filled('search')) {
                $query->searchKeyword((string) $request->search, true);
            }

            if ($selectedKataKunci !== 'Semua') {
                $query->whereHas('tags', fn ($q) => $q->where('nama', $selectedKataKunci));
            }

            if ((!$isUnitValidator || $scope === 'unit') && $request->filled('status') && $request->status !== 'Semua') {
                $rawStFilter = strtolower(trim((string) $request->status));
                if (in_array($rawStFilter, ['submitted', 'menunggu validasi'], true)) {
                    $query->whereLatestVersionIn(['Menunggu Validasi Unit', 'Menunggu Validasi Admik']);
                } elseif ($rawStFilter === 'menunggu validasi unit') {
                    $query->whereLatestVersionIn(['Menunggu Validasi Unit']);
                } elseif ($rawStFilter === 'menunggu validasi admik') {
                    $query->whereLatestVersionIn(['Menunggu Validasi Admik']);
                } else {
                    $statusFilter = match ($rawStFilter) {
                        'approved', 'disetujui' => 'Diterima',
                        'rejected'              => 'Ditolak',
                        'active'                => 'Terbit',
                        default                 => $request->status,
                    };
                    $query->whereLatestVersionIn([$statusFilter]);
                }
            }

            $sort = $request->get('sort', 'terbaru');
            match ($sort) {
                'terlama' => $query->oldest('updated_at'),
                'az'      => $query->orderBy('pertanyaan'),
                default   => $query->latest('updated_at'),
            };

            $faqs = $query->paginate(6)->withQueryString();
            $interactionSummary = \App\Models\LogMahasiswa::getFaqInteractionSummary($faqs->getCollection()->pluck('id')->all());
            $faqs->getCollection()->transform(function ($f) use ($interactionSummary) {
                $st = $interactionSummary[$f->id] ?? ['views' => 0, 'likes' => 0, 'dislikes' => 0];
                $f->views_count    = $st['views'];
                $f->likes_count    = $st['likes'];
                $f->dislikes_count = $st['dislikes'];
                return $f;
            });

            $statDirFilter  = $isUnitValidator ? $ownDirId : $selectedUnitId;
            $statusCounts   = Faq::countLatestByStatus($nonDraftStatuses, $selectedKataKunci, $statDirFilter);
            $totalAktif     = $statusCounts['Terbit'];
            $totalApproved  = $statusCounts['Diterima'];
            $totalSubmitted = ($statusCounts['Menunggu Validasi Unit'] ?? 0) + ($statusCounts['Menunggu Validasi Admik'] ?? 0);
            $totalRejected  = $statusCounts['Ditolak'];
            $totalFaq       = $totalAktif + $totalApproved + $totalSubmitted + $totalRejected;
        } catch (Throwable $e) {
            Log::warning('Gagal memuat Daftar FAQ dari DB: ' . $e->getMessage());
            $faqs           = new LengthAwarePaginator([], 0, 6);
            $totalFaq       = 0;
            $totalAktif     = 0;
            $totalApproved  = 0;
            $totalSubmitted = 0;
            $totalRejected  = 0;
        }

        $viewName = $request->routeIs('superadmin.*')
            ? 'superadmin.daftarFAQ'
            : ($isUnitValidator ? 'validator-unit.daftarFAQ' : 'validator.daftarFAQ');

        return view($viewName, compact(
            'faqs',
            'kataKunciOptions',
            'direktorats',
            'selectedUnitId',
            'scope',
            'ownDirId',
            'unitName',
            'isUnitValidator',
            'totalFaq',
            'totalAktif',
            'totalApproved',
            'totalSubmitted',
            'totalRejected',
        ));
    }

    /**
     * Halaman Validasi FAQ:
     * - Validator Unit (/validator-unit/validasi-faq): Antrean Tahap 1 ('Menunggu Validasi Unit' milik unitnya)
     * - Validator Admik (/validator/validasi-faq): Antrean Tahap 2 ('Menunggu Validasi Admik' & 'Diterima' semua unit)
     */
    public function validasiFaq(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing(['role', 'direktorat']);
        }

        $isUnitValidator = $this->isUnitContext($request);
        $ownDirId        = Auth::user()?->getEffectiveDirektoratId() ?? 1;
        $unitName        = Auth::user()?->getUnitName() ?? 'Direktorat Akademik';
        $direktorats     = collect();
        $selectedUnitId  = (!$isUnitValidator && $request->filled('unit_id') && $request->unit_id !== 'all')
            ? (int) $request->unit_id
            : null;

        try {
            \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            FaqVersion::processDueScheduledPublications();

            $direktorats = Direktorat::orderByDesc('is_pusat')->orderBy('nama', 'asc')->get();

            if ($isUnitValidator) {
                $queueCounts        = Faq::countLatestByStatus(['Menunggu Validasi Unit'], null, $ownDirId);
                $totalWaiting       = $queueCounts['Menunggu Validasi Unit'] ?? 0;
                $totalApprovedQueue = 0;
            } else {
                $queueCounts        = Faq::countLatestByStatus(['Menunggu Validasi Admik', 'Diterima'], null, $selectedUnitId);
                $totalWaiting       = $queueCounts['Menunggu Validasi Admik'] ?? 0;
                $totalApprovedQueue = $queueCounts['Diterima'] ?? 0;
            }

            $targetStatuses = $isUnitValidator
                ? ['Menunggu Validasi Unit']
                : ['Menunggu Validasi Admik', 'Diterima'];

            $query = Faq::with([
                'dataEntry',
                'direktorat',
                'tags',
                'validasiLogs.actor.direktorat',
                'versions' => fn ($q) => $q->orderByDesc('version_number')
                    ->orderBy('updated_at', 'desc'),
                'versions.daftarPustaka',
            ])->whereLatestVersionIn($targetStatuses);

            if ($isUnitValidator) {
                $query->where('tDirektorat_id', $ownDirId);
            } elseif ($selectedUnitId) {
                $query->where('tDirektorat_id', $selectedUnitId);
            }

            if ($request->filled('search')) {
                $query->searchKeyword((string) $request->search, false);
            }

            if ($request->filled('data_entry') && $request->data_entry !== 'all') {
                $query->whereHas('dataEntry', fn ($q) => $q->where('nama', $request->data_entry));
            }

            $sort = $request->get('sort', 'terbaru');
            if ($sort === 'terlama') {
                $query->oldest('updated_at');
            } else {
                $query->latest('updated_at');
            }

            $paginatedFaqs = $query->paginate(6)->withQueryString();

            // Preload kandidat kemiripan lintas seluruh unit 1x saja per halaman (mencegah N+1 query)
            $similarityService   = new FaqSimilarityService();
            $preloadedCandidates = $paginatedFaqs->isEmpty() ? collect() : $similarityService->getCandidateFaqs();

            $paginatedFaqs->getCollection()->transform(function ($faq) use ($similarityService, $preloadedCandidates) {
                $targetVersion = $faq->latest_version ?? $faq->versions->first();
                $versionNumber = max(1, $faq->versions->count());
                $deName        = $faq->dataEntry?->nama ?? 'Data Entry';
                $words         = explode(' ', trim($deName));
                $initials      = count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($deName, 0, 2));

                $tagsArray    = $faq->tags->pluck('nama')->values()->all();
                $tagNames     = implode(' · ', $tagsArray);
                $createdAt    = ($targetVersion?->updated_at ?? $targetVersion?->created_at ?? $faq->updated_at ?? $faq->created_at ?? now())->timezone('Asia/Jakarta');
                $status       = ($targetVersion?->status === 'Diterima') ? 'Diterima' : 'Menunggu Validasi';
                $scheduleInfo = $targetVersion?->getPublishScheduleInfo();
                $dp           = $targetVersion?->daftarPustaka;
                $isFileSource = $dp ? !empty($dp->upload) : false;

                $rawCatatan    = trim((string) ($targetVersion?->catatan ?? ''));
                $catatanRevisi = ($rawCatatan !== '' && !str_starts_with($rawCatatan, '[PUBLISH_SCHEDULE|'))
                    ? $rawCatatan
                    : null;

                $faqUnitNama  = $faq->unit_name;
                $faqIsPusat   = $faq->isPusatUnit();

                // Ringkasan persetujuan Tahap 1 (Validator Unit) jika ada di log
                $unitApproveLog = $faq->validasiLogs
                    ->where('tFaqsVersion_id', $targetVersion?->id)
                    ->where('tahap', 'unit')
                    ->whereIn('aksi', ['setujui', 'ajukan'])
                    ->last();

                $unitApprovalInfo = null;
                if ($unitApproveLog) {
                    $uActor = $unitApproveLog->actor?->nama ?? 'Validator Unit';
                    $uTime  = $unitApproveLog->created_at
                        ? $unitApproveLog->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB'
                        : '';
                    $unitApprovalInfo = $unitApproveLog->aksi === 'ajukan'
                        ? "Disusun langsung oleh Validator Unit ({$uActor})" . ($uTime ? " · {$uTime}" : '')
                        : "Disetujui Validator Unit: {$uActor}" . ($uTime ? " · {$uTime}" : '');
                }

                // Analisis daftar FAQ lain yang mirip lintas semua unit untuk membantu Validator Unit & Validator Admik
                $simAnalysis = $similarityService->analyzeQuestion(
                    (string) $faq->pertanyaan,
                    (string) $faq->id,
                    35.0,
                    null,
                    $preloadedCandidates
                );
                $similarFaqs = array_map(fn ($cand) => [
                    'id'           => $cand['id'],
                    'version_id'   => $cand['version_id'],
                    'pertanyaan'   => $cand['pertanyaan'],
                    'jawaban'      => \Illuminate\Support\Str::limit(strip_tags((string) ($cand['jawaban'] ?? '')), 220),
                    'unit_nama'    => $cand['unit_nama'] ?? 'Direktorat Akademik',
                    'status_label' => $cand['status_label'] ?? 'Draf',
                    'similarity'   => $cand['similarity'],
                    'level'        => $cand['level'],
                ], $simAnalysis['candidates'] ?? []);

                $milestoneLogs = $faq->validasiLogs
                    ->map(fn ($log) => $log->toTimelineArray($faqUnitNama))
                    ->values()
                    ->all();

                $previousVersions = $faq->versions
                    ->filter(fn ($v) => $v->id !== $targetVersion?->id && in_array($v->status, ['Diterima', 'Terbit'], true))
                    ->sort(function ($a, $b) {
                        $numA = preg_match('/-V(\d+)$/i', (string) $a->id, $mA) ? (int) $mA[1] : 0;
                        $numB = preg_match('/-V(\d+)$/i', (string) $b->id, $mB) ? (int) $mB[1] : 0;
                        return $numB <=> $numA;
                    })
                    ->values()
                    ->map(function ($v) {
                        $vDp = $v->daftarPustaka;
                        $vTs = $v->updated_at ?? $v->created_at;
                        return [
                            'id'          => $v->id,
                            'versi_label' => FaqVersion::formatVersiLabel($v->id),
                            'status'      => $v->status,
                            'jawaban'     => $v->jawaban,
                            'sumber'      => $vDp?->judul_tampil ?? ($vDp?->link ?? 'Tidak ada'),
                            'updated_at'  => $vTs ? Carbon::parse($vTs)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                        ];
                    })
                    ->all();

                return (object) [
                    'id'                  => $faq->id,
                    'kode'                => $faq->id,
                    'versi'               => FaqVersion::formatVersiLabel($targetVersion?->id) ?: ('Versi ' . $versionNumber),
                    'pertanyaan'          => $faq->pertanyaan,
                    'kategori'            => $tagNames ?: 'Belum ada kata kunci',
                    'tags_list'           => $tagsArray,
                    'jawaban'             => $targetVersion?->jawaban ?? '-',
                    'jawaban_sebelumnya'  => $targetVersion?->jawaban_sebelumnya,
                    'catatan_revisi'      => $catatanRevisi,
                    'previous_versions'   => $previousVersions,
                    'sumber'              => $dp?->judul_tampil ?? 'Pedoman Akademik Kampus',
                    'sumber_id'           => $dp?->id,
                    'sumber_is_file'      => $isFileSource,
                    'sumber_type'         => 'Publik',
                    'sumber_link'         => $dp?->link ?? '',
                    'sumber_view_url'     => ($dp && $isFileSource) ? route('pustaka.lihat', $dp->id) : '',
                    'data_entry_nama'     => $deName,
                    'data_entry_initials' => $initials,
                    'data_entry_role'     => 'Data Entry',
                    'unit_id'             => (int) ($faq->tDirektorat_id ?: 1),
                    'unit_nama'           => $faqUnitNama,
                    'unit_is_pusat'       => $faqIsPusat,
                    'unit_approval_info'  => $unitApprovalInfo,
                    'similar_faqs'        => $similarFaqs,
                    'milestone_logs'      => $milestoneLogs,
                    'created_at'          => $createdAt,
                    'tanggal_indo'        => Carbon::parse($createdAt)->timezone('Asia/Jakarta')->translatedFormat('d M Y'),
                    'default_checked'     => false,
                    'status'              => $status,
                    'raw_status'          => $targetVersion?->status ?? 'Menunggu Validasi Admik',
                    'is_approved'         => $status === 'Diterima',
                    'is_scheduled'        => !empty($scheduleInfo['is_scheduled']),
                    'schedule_mode'       => $scheduleInfo['mode'] ?? 'now',
                    'schedule_date'       => $scheduleInfo['date'] ?? '',
                    'schedule_time'       => $scheduleInfo['time'] ?? '',
                    'schedule_label'      => $scheduleInfo['label'] ?? null,
                ];
            });

            $deQuery = User::where('tRoles_id', 2)->orderBy('nama');
            if ($isUnitValidator) {
                $deQuery->where('tDirektorat_id', $ownDirId);
            } elseif ($selectedUnitId) {
                $deQuery->where('tDirektorat_id', $selectedUnitId);
            }
            $dataEntryUsers = $deQuery->pluck('nama');
        } catch (Throwable $e) {
            Log::error('Gagal memuat antrean validasi FAQ dari database: ' . $e->getMessage());
            $paginatedFaqs      = new LengthAwarePaginator([], 0, 6);
            $totalWaiting       = 0;
            $totalApprovedQueue = 0;
            $dataEntryUsers     = collect();
        }

        $viewName = $isUnitValidator ? 'validator-unit.validasiFAQ' : 'validator.validasiFAQ';

        return view($viewName, [
            'queueItems'         => $paginatedFaqs,
            'totalWaiting'       => $totalWaiting,
            'totalApprovedQueue' => $totalApprovedQueue ?? 0,
            'dataEntryUsers'     => $dataEntryUsers,
            'direktorats'        => $direktorats,
            'selectedUnitId'     => $selectedUnitId,
            'isUnitValidator'    => $isUnitValidator,
            'unitName'           => $unitName,
        ]);
    }

    /**
     * Terima satu pengajuan FAQ:
     * - Validator Unit: Menunggu Validasi Unit -> Menunggu Validasi Admik (+ log tahap=unit, aksi=setujui)
     * - Validator Admik: Menunggu Validasi Admik -> Diterima (+ log tahap=admik, aksi=setujui)
     */
    public function approve(Request $request, $id)
    {
        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $isUnitVal   = $this->isUnitContext($request);
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $faq = Faq::with(['versions', 'direktorat'])->findOrFail($id);
            $this->assertUnitOwnership($faq, $request);

            $latest = $faq->latest_version;
            if ($isUnitVal) {
                if ($latest && $latest->status === 'Menunggu Validasi Unit') {
                    $latest->update([
                        'status'            => 'Menunggu Validasi Admik',
                        'rejected_by_stage' => null,
                        'id_validator'      => $validatorId,
                        'updated_at'        => now(),
                    ]);
                    $faq->touch();
                    FaqValidasiLog::record(
                        $faq->id,
                        $latest->id,
                        'unit',
                        'setujui',
                        $validatorId,
                        'Disetujui di tahap Unit (' . $faq->unit_name . ') dan diteruskan ke Validator Admik.'
                    );
                }

                return redirect()->back()
                    ->with('success', "FAQ {$id} berhasil disetujui pada tahap Unit dan diteruskan ke Validator Admik.");
            }

            if ($latest && in_array($latest->status, ['Menunggu Validasi Admik', 'Menunggu Validasi'], true)) {
                $latest->update([
                    'status'             => 'Diterima',
                    'rejected_by_stage'  => null,
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => null,
                    'catatan'            => null,
                    'updated_at'         => now(),
                ]);
                $faq->touch();
                FaqValidasiLog::record(
                    $faq->id,
                    $latest->id,
                    'admik',
                    'setujui',
                    $validatorId,
                    'Disetujui oleh Validator Admik (Siap Terbit).'
                );
            }

            return redirect()->back()
                ->with('success', "FAQ {$id} berhasil diterima.");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menerima FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Tolak satu pengajuan FAQ beserta catatan revisi & penanda sumber penolakan (rejected_by_stage = 'unit' | 'admik').
     */
    public function reject(Request $request, $id)
    {
        $catatan   = trim((string) $request->input('catatan', ''));
        $isUnitVal = $this->isUnitContext($request);

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $faq = Faq::with(['versions', 'direktorat'])->findOrFail($id);
            $this->assertUnitOwnership($faq, $request);

            $latest = $faq->latest_version;
            if ($isUnitVal) {
                $finalNote = $catatan !== '' ? $catatan : 'Perlu revisi sesuai catatan Validator Unit.';
                if ($latest && $latest->status === 'Menunggu Validasi Unit') {
                    $latest->update([
                        'status'             => 'Ditolak',
                        'rejected_by_stage'  => 'unit',
                        'id_validator'       => $validatorId,
                        'jawaban_sebelumnya' => $latest->jawaban,
                        'catatan'            => $finalNote,
                        'updated_at'         => now(),
                    ]);
                    $faq->touch();
                    FaqValidasiLog::record($faq->id, $latest->id, 'unit', 'tolak', $validatorId, $finalNote);
                }

                return redirect()->back()
                    ->with('success', "FAQ {$id} ditolak oleh Validator Unit dan dikembalikan ke Data Entry Unit beserta catatan.");
            }

            $finalNote = $catatan !== '' ? $catatan : 'Perlu revisi oleh Data Entry sesuai catatan Validator Admik.';
            if ($latest && in_array($latest->status, ['Menunggu Validasi Admik', 'Menunggu Validasi', 'Diterima'], true)) {
                $latest->update([
                    'status'             => 'Ditolak',
                    'rejected_by_stage'  => 'admik',
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => $latest->jawaban,
                    'catatan'            => $finalNote,
                    'updated_at'         => now(),
                ]);
                $faq->touch();
                FaqValidasiLog::record($faq->id, $latest->id, 'admik', 'tolak', $validatorId, $finalNote);
            }

            return redirect()->back()
                ->with('success', "FAQ {$id} telah ditolak oleh Validator Admik dan dikembalikan ke Data Entry.");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Terima banyak FAQ sekaligus dari pilihan checklist.
     */
    public function bulkApprove(Request $request)
    {
        $faqIds    = $request->input('faq_ids', []);
        $isUnitVal = $this->isUnitContext($request);
        $backRoute = $this->routePrefix($request) . 'validasiFAQ';

        if (empty($faqIds) || !is_array($faqIds)) {
            return redirect()->route($backRoute)
                ->with('error', 'Pilih setidaknya satu FAQ terlebih dahulu untuk disetujui.');
        }

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $faqs = Faq::with(['versions', 'direktorat'])->whereIn('id', $faqIds)->get();
            $approvedCodes = [];

            foreach ($faqs as $faq) {
                if ($isUnitVal) {
                    $this->assertUnitOwnership($faq, $request);
                    $latest = $faq->latest_version;
                    if ($latest && $latest->status === 'Menunggu Validasi Unit') {
                        $latest->update([
                            'status'            => 'Menunggu Validasi Admik',
                            'rejected_by_stage' => null,
                            'id_validator'      => $validatorId,
                            'updated_at'        => now(),
                        ]);
                        $faq->touch();
                        FaqValidasiLog::record(
                            $faq->id,
                            $latest->id,
                            'unit',
                            'setujui',
                            $validatorId,
                            'Disetujui massal oleh Validator Unit (' . $faq->unit_name . ') dan diteruskan ke Validator Admik.'
                        );
                        $approvedCodes[] = $faq->id;
                    }
                } else {
                    $latest = $faq->latest_version;
                    if ($latest && in_array($latest->status, ['Menunggu Validasi Admik', 'Menunggu Validasi'], true)) {
                        $latest->update([
                            'status'             => 'Diterima',
                            'rejected_by_stage'  => null,
                            'id_validator'       => $validatorId,
                            'jawaban_sebelumnya' => null,
                            'catatan'            => null,
                            'updated_at'         => now(),
                        ]);
                        $faq->touch();
                        FaqValidasiLog::record(
                            $faq->id,
                            $latest->id,
                            'admik',
                            'setujui',
                            $validatorId,
                            'Disetujui massal oleh Validator Admik (Siap Terbit).'
                        );
                        $approvedCodes[] = $faq->id;
                    }
                }
            }

            $count    = count($approvedCodes);
            $listKode = implode(', ', $approvedCodes ?: $faqIds);

            $msg = $isUnitVal
                ? "{$count} FAQ ({$listKode}) berhasil disetujui pada tahap Unit dan diteruskan ke Validator Admik."
                : "{$count} FAQ ({$listKode}) berhasil diterima. Silakan terbitkan melalui tombol Lihat atau Terbitkan.";

            return redirect()->route($backRoute)->with('success', $msg);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return redirect()->route($backRoute)
                ->with('error', 'Gagal menerima FAQ secara massal: ' . $e->getMessage());
        }
    }

    /**
     * Terbitkan FAQ yang telah berstatus Diterima (Khusus Validator Admik).
     */
    public function publish(Request $request)
    {
        if ($this->isUnitContext($request)) {
            abort(403, 'Akses ditolak: Penerbitan FAQ hanya dapat dilakukan oleh Validator Direktorat Akademik (Pusat).');
        }

        $request->validate([
            'faq_ids'      => 'required|array|min:1',
            'faq_ids.*'    => 'required|string',
            'publish_mode' => 'required|in:scheduled,now',
            'publish_date' => 'nullable|required_if:publish_mode,scheduled|date',
            'publish_time' => 'nullable|required_if:publish_mode,scheduled',
        ], [
            'faq_ids.required'         => 'Pilih setidaknya satu FAQ berstatus Diterima untuk diterbitkan.',
            'publish_date.required_if' => 'Tanggal publikasi wajib diisi untuk jadwal publikasi.',
            'publish_time.required_if' => 'Jam publikasi wajib diisi untuk jadwal publikasi.',
        ]);

        $faqIds      = array_values(array_unique((array) $request->input('faq_ids', [])));
        $publishMode = $request->input('publish_mode', 'now');
        $publishDate = $request->input('publish_date');
        $publishTime = $request->input('publish_time');

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $faqs = Faq::with('versions')->whereIn('id', $faqIds)->get();
            $versions = collect();
            foreach ($faqs as $faq) {
                $latest = $faq->latest_version;
                if ($latest && $latest->status === 'Diterima') {
                    $versions->push($latest);
                }
            }

            if ($versions->isEmpty()) {
                return redirect()->route('validator.validasiFAQ')
                    ->with('error', 'Hanya FAQ yang sudah berstatus Diterima yang dapat diterbitkan.');
            }

            $listKode = implode(', ', $versions->pluck('tFaqs_id')->unique()->toArray());
            $count    = $versions->pluck('tFaqs_id')->unique()->count();

            if ($publishMode === 'scheduled' && $publishDate && $publishTime) {
                $targetDt = Carbon::parse($publishDate . ' ' . $publishTime);
                if (now()->gte($targetDt)) {
                    $publishMode = 'now';
                }
            }

            if ($publishMode === 'scheduled') {
                $scheduleNote = FaqVersion::encodePublishSchedule($publishDate, $publishTime);
                $dtFormatted  = Carbon::parse($publishDate . ' ' . $publishTime);

                foreach ($versions as $ver) {
                    $ver->update([
                        'status'       => 'Diterima',
                        'catatan'      => $scheduleNote,
                        'id_validator' => $validatorId,
                        'updated_at'   => now(),
                    ]);
                    Faq::where('id', $ver->tFaqs_id)->update(['updated_at' => now()]);
                    FaqValidasiLog::record(
                        (string) $ver->tFaqs_id,
                        (string) $ver->id,
                        'publikasi',
                        'jadwalkan',
                        $validatorId,
                        'Dijadwalkan terbit pada ' . $dtFormatted->translatedFormat('d M Y') . ' pukul ' . $dtFormatted->format('H.i') . ' WIB.'
                    );
                }

                return redirect()->route('validator.validasiFAQ')
                    ->with('success', "{$count} FAQ ({$listKode}) telah dijadwalkan terbit pada " . $dtFormatted->translatedFormat('d M Y') . " pukul " . $dtFormatted->format('H.i') . " WIB.");
            }

            foreach ($versions as $ver) {
                $ver->update([
                    'status'       => 'Terbit',
                    'catatan'      => null,
                    'id_validator' => $validatorId,
                    'updated_at'   => now(),
                ]);
                Faq::where('id', $ver->tFaqs_id)->update(['updated_at' => now()]);
                FaqValidasiLog::record(
                    (string) $ver->tFaqs_id,
                    (string) $ver->id,
                    'publikasi',
                    'terbit',
                    $validatorId,
                    'Diterbitkan ke Portal Mahasiswa.'
                );
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$count} FAQ ({$listKode}) berhasil diterbitkan sekarang!");
        } catch (Throwable $e) {
            return redirect()->route('validator.validasiFAQ')
                ->with('error', 'Gagal memproses publikasi FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail lengkap, riwayat versi & milestone log FAQ (JSON untuk modal).
     */
    public function show($id, Request $request)
    {
        \App\Models\DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $faq = Faq::with([
            'direktorat',
            'validasiLogs.actor.direktorat',
            'versions' => fn ($q) => $q->where('status', '!=', 'Draf')
                ->orderByDesc('version_number')
                ->orderBy('updated_at', 'desc'),
            'versions.daftarPustaka',
            'versions.validator',
            'tags',
            'dataEntry',
        ])->findOrFail($id);

        if (!$request->routeIs('superadmin.*') && $this->isUnitContext($request)) {
            $ownDirId  = Auth::user()?->getEffectiveDirektoratId() ?? 1;
            $hasTerbit = $faq->versions->contains(fn ($v) => strtolower((string) $v->status) === 'terbit');
            if ((int) ($faq->tDirektorat_id ?: 1) !== $ownDirId && !$hasTerbit) {
                abort(403, 'Akses ditolak: Anda hanya dapat melihat FAQ non-terbit milik unit Anda sendiri.');
            }
        }

        $displayLatest = $faq->versions->first();
        $latestTs      = $displayLatest?->updated_at ?? $displayLatest?->created_at ?? $faq->created_at;
        $st            = \App\Models\LogMahasiswa::getFaqInteractionSummary([$faq->id])[$faq->id] ?? ['views' => 0, 'likes' => 0, 'dislikes' => 0];
        $unitNama      = $faq->unit_name;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                 => $faq->id,
                'pertanyaan'         => $faq->pertanyaan,
                'created_at'         => $latestTs ? $latestTs->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                'data_entry'         => $faq->dataEntry?->nama,
                'unit_id'            => (int) ($faq->tDirektorat_id ?: 1),
                'unit_nama'          => $unitNama,
                'is_pusat_unit'      => $faq->isPusatUnit(),
                'tags'               => $faq->tags->pluck('nama'),
                'views_count'        => $st['views'],
                'likes_count'        => $st['likes'],
                'dislikes_count'     => $st['dislikes'],
                'latest_version'     => $displayLatest,
                'rejected_by_stage'  => $displayLatest?->rejected_by_stage,
                'rejection_label'    => ($displayLatest && $displayLatest->status === 'Ditolak')
                    ? $displayLatest->getRejectionBadgeLabel($unitNama)
                    : null,
                'milestone_logs'     => $faq->validasiLogs->map(fn ($l) => $l->toTimelineArray($unitNama))->values(),
                'versions'           => $faq->versions->map(function ($v) use ($unitNama) {
                    $ts = $v->updated_at ?? $v->created_at;
                    $dp = $v->daftarPustaka;
                    $isFile = $dp ? !empty($dp->upload) : false;
                    return [
                        'id'                 => $v->id,
                        'versi_label'        => FaqVersion::formatVersiLabel($v->id),
                        'status'             => $v->status,
                        'rejected_by_stage'  => $v->rejected_by_stage,
                        'rejection_label'    => $v->status === 'Ditolak' ? $v->getRejectionBadgeLabel($unitNama) : null,
                        'jawaban'            => $v->jawaban,
                        'jawaban_sebelumnya' => $v->jawaban_sebelumnya,
                        'catatan'            => $v->catatan,
                        'validator'          => $v->validator?->nama,
                        'daftar_pustaka'     => $dp ? [
                            'id'       => $dp->id,
                            'judul'    => $dp->judul_tampil,
                            'link'     => $dp->link,
                            'is_file'  => $isFile,
                            'view_url' => $isFile ? route('pustaka.lihat', $dp->id) : null,
                        ] : null,
                        'created_at'         => $v->created_at ? $v->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                        'updated_at'         => $ts ? $ts->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                    ];
                }),
            ],
        ]);
    }

    /**
     * Ubah status FAQ dari Daftar FAQ Validator Admik (Terbit, Diterima, Ditolak).
     */
    public function updateStatus(Request $request, $id)
    {
        if ($this->isUnitContext($request)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'status'  => 'required|in:Terbit,Diterima,Ditolak',
            'catatan' => 'nullable|string',
        ]);

        try {
            FaqVersion::ensureIndonesianStatusEnum();
            $faq = Faq::with('versions')->findOrFail($id);
            $latest = $faq->latest_version;
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            if ($latest) {
                $updateData = [
                    'status'             => $request->status,
                    'rejected_by_stage'  => $request->status === 'Ditolak' ? 'admik' : null,
                    'id_validator'       => $validatorId,
                    'jawaban_sebelumnya' => $request->status === 'Ditolak' ? $latest->jawaban : null,
                    'updated_at'         => now(),
                ];
                if ($request->filled('catatan')) {
                    $updateData['catatan'] = $request->catatan;
                } elseif (in_array($request->status, ['Diterima', 'Terbit'], true)) {
                    $updateData['catatan'] = null;
                }
                $latest->update($updateData);
                $faq->touch();

                $logTahap = $request->status === 'Terbit' ? 'publikasi' : 'admik';
                $logAksi  = match ($request->status) {
                    'Terbit'   => 'terbit',
                    'Diterima' => 'setujui',
                    default    => 'tolak',
                };
                FaqValidasiLog::record($faq->id, $latest->id, $logTahap, $logAksi, $validatorId, $request->input('catatan'));
            }

            return redirect()->back()
                ->with('success', "Status FAQ {$id} berhasil diperbarui menjadi {$request->status}.");
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui status FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Hapus FAQ beserta versinya.
     */
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $faq = Faq::findOrFail($id);
            $this->assertUnitOwnership($faq, $request);

            $faq->tags()->detach();
            $faq->versions()->delete();
            $faq->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', "FAQ {$id} beserta riwayat versinya berhasil dihapus.");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus FAQ: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * BUAT FAQ, UBAH FAQ & PRATINJAU UNGGAHAN FILE UNTUK VALIDATOR
     * (Langsung masuk ke Validasi FAQ berstatus 'Diterima' & siap diterbitkan)
     * ========================================================================= */

    public function create(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $isUnitVal     = $this->isUnitContext($request);
        $ownDirId      = Auth::user()?->getEffectiveDirektoratId() ?? 1;
        $newFaqId      = Faq::generateNextId();
        $tags          = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        $allFaqsQuery = Faq::with(['versions', 'tags'])->orderBy('id', 'asc');
        if ($isUnitVal) {
            $allFaqsQuery->where('tDirektorat_id', $ownDirId);
        }
        $allFaqs = $allFaqsQuery->get();

        $selectedExistingFaq = null;
        if ($request->filled('faq_id')) {
            $selectedExistingFaq = $allFaqs->firstWhere('id', $request->query('faq_id'));
        }

        return view('validator.buatfaq', compact(
            'newFaqId',
            'tags',
            'daftarPustaka',
            'allFaqs',
            'selectedExistingFaq'
        ));
    }

    public function edit(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        FaqVersion::ensureIndonesianStatusEnum();
        FaqVersion::processDueScheduledPublications();

        $isUnitVal = $this->isUnitContext($request);
        $faq = Faq::with(['versions.daftarPustaka', 'tags', 'direktorat'])->findOrFail($id);
        $this->assertUnitOwnership($faq, $request);

        $latest = $faq->latest_version;
        $st = $latest ? strtolower((string) $latest->status) : 'draf';

        if ($isUnitVal && $st === 'menunggu validasi admik') {
            return redirect()->route('validator.unit.daftarFAQ')
                ->with('error', "FAQ {$faq->id} sedang ditinjau oleh Validator Admik dan terkunci sementara.");
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();
        $allFaqsQuery = Faq::with(['versions', 'tags'])->orderBy('id', 'asc');
        if ($isUnitVal) {
            $allFaqsQuery->where('tDirektorat_id', Auth::user()?->getEffectiveDirektoratId() ?? 1);
        }
        $allFaqs = $allFaqsQuery->get();
        $forceNewVersion = in_array($st, ['diterima', 'terbit', 'approved', 'active'], true);

        return view('validator.buatfaq', [
            'faq'             => $faq,
            'isEdit'          => true,
            'forceNewVersion' => $forceNewVersion,
            'newFaqId'        => $faq->id,
            'nextVersionId'   => $faq->generateNextVersionId(),
            'latestVersion'   => $latest,
            'tags'            => $tags,
            'daftarPustaka'   => $daftarPustaka,
            'allFaqs'         => $allFaqs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'      => 'required|string',
            'jawaban'         => 'required|string',
            'tags'            => 'nullable|string',
            'pustaka_id'      => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_link'     => 'nullable|string|max:60',
            'mode_faq'        => 'nullable|in:new,existing',
            'existing_faq_id' => 'nullable|string|exists:tfaqs,id',
        ], [
            'pertanyaan.required' => 'Pertanyaan FAQ wajib diisi.',
            'jawaban.required'    => 'Jawaban FAQ wajib diisi.',
            'sumber_link.max'     => 'Panjang URL / rujukan tautan maksimal 60 karakter.',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $isUnitVal      = $this->isUnitContext($request);
            $validatorId    = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;
            $ownDirId       = Auth::user()?->getEffectiveDirektoratId() ?? 1;
            $isExistingMode = ($request->input('mode_faq') === 'existing' && $request->filled('existing_faq_id'));

            $similarityService = new FaqSimilarityService();
            $excludeCheckId = $isExistingMode ? $request->existing_faq_id : null;
            $exactDraftMatch = $similarityService->findExactDraftMatch($request->pertanyaan, $request->jawaban, $excludeCheckId);
            if ($exactDraftMatch !== null) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        "Peringatan: Tidak dapat menambahkan FAQ karena Pertanyaan dan Jawaban sama persis dengan FAQ yang sudah ada ({$exactDraftMatch['id']} / {$exactDraftMatch['version_id']})."
                    );
            }

            $tagIds = [];
            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                $resolvedTags = FileImportParser::resolveMatchingSystemTags($rawTags);
                foreach ($resolvedTags as $tagText) {
                    $cleanTag = substr(trim($tagText), 0, 100);
                    if ($cleanTag !== '') {
                        $existingTag = Tag::where('nama', $cleanTag)->first();
                        $t = $existingTag ?: Tag::firstOrCreate(['nama' => $cleanTag]);
                        $tagIds[] = $t->id;
                    }
                }
            }

            if (empty($tagIds)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: Kata Kunci wajib dilengkapi sebelum menyimpan FAQ.');
            }

            if ($isExistingMode) {
                $faq = Faq::with('versions')->findOrFail($request->existing_faq_id);
                $this->assertUnitOwnership($faq, $request);
                $faqId = $faq->id;
                $latestExisting = $faq->latest_version;
                $latestExistingSt = $latestExisting ? strtolower((string) $latestExisting->status) : 'draf';
                if (!in_array($latestExistingSt, ['diterima', 'terbit', 'approved', 'active'], true)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Peringatan: Versi jawaban baru hanya dapat ditambahkan pada FAQ yang sudah berstatus Diterima atau Terbit ({$faqId} saat ini berstatus " . ($latestExisting?->status ?? 'Draf') . ").");
                }
                $versionId = $faq->generateNextVersionId();
            } else {
                $faqId = Faq::generateNextId();
                $faq = Faq::create([
                    'id'             => $faqId,
                    'id_dataentry'   => $validatorId,
                    'tDirektorat_id' => $ownDirId,
                    'pertanyaan'     => $request->pertanyaan,
                ]);
                $versionId = $faqId . '-V1';
            }

            $pustakaId = null;
            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);
                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $targetStatus = $isUnitVal ? 'Menunggu Validasi Admik' : 'Diterima';

            FaqVersion::updateOrCreate(
                ['id' => $versionId],
                [
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faqId,
                    'id_validator'       => $validatorId,
                    'jawaban'            => $request->jawaban,
                    'jawaban_sebelumnya' => null,
                    'status'             => $targetStatus,
                    'rejected_by_stage'  => null,
                    'catatan'            => null,
                ]
            );

            FaqValidasiLog::record(
                $faqId,
                $versionId,
                $isUnitVal ? 'unit' : 'admik',
                'ajukan',
                $validatorId,
                $isUnitVal
                    ? 'Disusun langsung oleh Validator Unit dan diteruskan ke Validator Admik.'
                    : 'Disusun langsung oleh Validator Admik (Siap Terbit).'
            );

            if ($isExistingMode) {
                FaqVersion::where('tFaqs_id', $faqId)
                    ->where('id', '!=', $versionId)
                    ->whereIn('status', ['Draf', 'Ditolak'])
                    ->delete();
                FaqVersion::syncLatestFlag($faqId);
                $faq->touch();
            }

            $faq->tags()->syncWithoutDetaching(array_unique($tagIds));

            DB::commit();

            if ($isUnitVal) {
                return redirect()->route('validator.unit.daftarFAQ')
                    ->with('success', "FAQ {$faqId} ({$versionId}) berhasil disimpan dan langsung diteruskan ke Validator Admik!");
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "FAQ {$faqId} ({$versionId}) berhasil disimpan ke Validasi FAQ (status Diterima) dan siap diterbitkan!");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan FAQ: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pertanyaan'     => 'required|string',
            'jawaban'        => 'required|string',
            'version_action' => 'nullable|in:update_draft,new_version',
            'tags'           => 'nullable|string',
            'pustaka_id'     => 'nullable|integer|exists:tdaftarpustaka,id',
            'sumber_link'    => 'nullable|string|max:60',
        ]);

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();

            $isUnitVal   = $this->isUnitContext($request);
            $faq         = Faq::with('versions')->findOrFail($id);
            $this->assertUnitOwnership($faq, $request);
            $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;

            $similarityService = new FaqSimilarityService();
            $exactDraftMatch = $similarityService->findExactDraftMatch($request->pertanyaan, $request->jawaban, $faq->id);
            if ($exactDraftMatch !== null) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        "Peringatan: Tidak dapat memperbarui FAQ karena Pertanyaan dan Jawaban sama persis dengan FAQ berstatus Draf yang sudah ada ({$exactDraftMatch['id']} / {$exactDraftMatch['version_id']})."
                    );
            }

            $tagIds = [];
            if ($request->filled('tags')) {
                $rawTags = explode(',', $request->tags);
                $resolvedTags = FileImportParser::resolveMatchingSystemTags($rawTags);
                foreach ($resolvedTags as $tagText) {
                    $cleanTag = substr(trim($tagText), 0, 100);
                    if ($cleanTag !== '') {
                        $existingTag = Tag::where('nama', $cleanTag)->first();
                        $t = $existingTag ?: Tag::firstOrCreate(['nama' => $cleanTag]);
                        $tagIds[] = $t->id;
                    }
                }
            }

            if (empty($tagIds)) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan: Kata Kunci wajib dilengkapi sebelum menyimpan perubahan FAQ.');
            }

            $faq->update([
                'pertanyaan' => $request->pertanyaan,
                'updated_at' => now(),
            ]);

            $pustakaId = null;
            if ($request->filled('pustaka_id')) {
                $pustakaId = (int) $request->pustaka_id;
            } elseif ($request->filled('sumber_link')) {
                $linkText = substr(trim($request->sumber_link), 0, 60);
                $pustaka = DaftarPustaka::firstOrCreate([
                    'link' => $linkText,
                ], [
                    'judul'  => $linkText,
                    'upload' => null,
                ]);
                $pustakaId = $pustaka->id;
            }

            $targetStatus   = $isUnitVal ? 'Menunggu Validasi Admik' : 'Diterima';
            $savedVersionId = $faq->generateNextVersionId();

            FaqVersion::updateOrCreate(
                ['id' => $savedVersionId],
                [
                    'tDaftarPustakas_id' => $pustakaId,
                    'tFaqs_id'           => $faq->id,
                    'id_validator'       => $validatorId,
                    'jawaban'            => $request->jawaban,
                    'jawaban_sebelumnya' => null,
                    'status'             => $targetStatus,
                    'rejected_by_stage'  => null,
                    'catatan'            => null,
                ]
            );

            FaqValidasiLog::record(
                $faq->id,
                $savedVersionId,
                $isUnitVal ? 'unit' : 'admik',
                'ajukan',
                $validatorId,
                $isUnitVal
                    ? 'Diperbarui oleh Validator Unit dan diteruskan ke Validator Admik.'
                    : 'Diperbarui langsung oleh Validator Admik (Siap Terbit).'
            );

            FaqVersion::where('tFaqs_id', $faq->id)
                ->where('id', '!=', $savedVersionId)
                ->whereIn('status', ['Draf', 'Ditolak'])
                ->delete();
            FaqVersion::syncLatestFlag($faq->id);

            $faq->tags()->sync(array_unique($tagIds));

            DB::commit();

            if ($isUnitVal) {
                return redirect()->route('validator.unit.daftarFAQ')
                    ->with('success', "FAQ {$faq->id} ({$savedVersionId}) berhasil diperbarui dan diteruskan ke Validator Admik!");
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "FAQ {$faq->id} ({$savedVersionId}) berhasil disimpan dengan status Diterima dan siap diterbitkan!");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui FAQ: ' . $e->getMessage());
        }
    }

    public function checkDuplicate(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $a = $request->has('a') ? trim((string) $request->query('a', '')) : null;
        $excludeId = $request->query('exclude_id');
        $threshold = (float) $request->query('threshold', 50.0);

        $service = new FaqSimilarityService();
        $result = $service->analyzeQuestion($q, $excludeId, $threshold, $a);

        return response()->json([
            'status'                => 'success',
            'has_exact_match'       => $result['has_exact_match'],
            'exact_match'           => $result['exact_match'],
            'has_exact_draft_match' => $result['has_exact_draft_match'],
            'exact_draft_match'     => $result['exact_draft_match'],
            'max_similarity'        => $result['max_similarity'],
            'candidates'            => $result['candidates'],
            'duplicates'            => $result['candidates'],
        ]);
    }

    public function pratinjau(Request $request)
    {
        $items = session('uploaded_faqs', []);
        $fileName = session('uploaded_filename', 'Belum ada file diunggah');

        if (empty($items)) {
            $nextIdStr = Faq::generateNextId();
            $startNum = (int) preg_replace('/\D/', '', $nextIdStr) ?: 1;

            $items = [
                [
                    'row_key'        => sprintf('FAQ-%04d-V1', $startNum),
                    'id'             => sprintf('FAQ-%04d', $startNum),
                    'version_id'     => sprintf('FAQ-%04d-V1', $startNum),
                    'version'        => 'Versi 1',
                    'is_new_version' => false,
                    'pertanyaan'     => 'Bagaimana prosedur pengajuan cuti akademik?',
                    'jawaban'        => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pembimbing akademik (PA) terlebih dahulu.',
                    'tags'           => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                    'pustaka_judul'  => 'Pedoman Akademik 2026/2027',
                    'pustaka_bagian' => 'Bagian Cuti Akademik',
                    'status'         => 'ready',
                    'checked'        => true,
                ],
            ];
            $fileName = 'FAQ_Akademik_2026.xlsx';
            session([
                'uploaded_faqs'     => $items,
                'uploaded_filename' => $fileName,
            ]);
        }

        $tags = Tag::orderBy('nama', 'asc')->get();
        $daftarPustaka = DaftarPustaka::orderBy('id', 'desc')->get();

        return view('validator.preview', compact('items', 'fileName', 'tags', 'daftarPustaka'));
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_faq' => 'required|file|max:10240',
        ], [
            'file_faq.required' => 'Silakan pilih file Excel / CSV / PDF terlebih dahulu.',
            'file_faq.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('file_faq');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'pdf'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan file .XLSX, .CSV, atau .PDF.');
        }

        $nextIdStr = Faq::generateNextId();
        $startNum = (int) preg_replace('/\D/', '', $nextIdStr) ?: 1;

        $rawRows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $faqs = FileImportParser::mapRowsToFaqPreview($rawRows, $startNum);

        if (empty($faqs)) {
            return redirect()->back()->with(
                'error',
                "File {$filename} tidak berisi baris pertanyaan & jawaban yang dapat dibaca. Pastikan mengikuti format kolom: Kolom A: Pertanyaan | Kolom B: Jawaban | Kolom C: Kata Kunci."
            );
        }

        session([
            'uploaded_filename' => $filename,
            'uploaded_faqs'     => $faqs,
        ]);

        return redirect()->route($this->routePrefix($request) . 'pratinjau')
            ->with('success', 'Berhasil membaca ' . count($faqs) . " baris FAQ dari file {$filename}!");
    }

    public function updatePreviewItem(Request $request)
    {
        $rowId = $request->input('id');
        $pertanyaan = trim((string) $request->input('pertanyaan', ''));
        $jawaban = trim((string) $request->input('jawaban', ''));
        $tagsRaw = trim((string) $request->input('tags', ''));
        $pustakaJudul = trim((string) $request->input('pustaka_judul', ''));
        $pustakaBagian = trim((string) $request->input('pustaka_bagian', ''));

        $rawTagList = array_values(array_filter(
            array_map('trim', preg_split('/[,;\r\n|]+/', $tagsRaw)),
            fn($t) => $t !== ''
        ));
        $tags = FileImportParser::resolveMatchingSystemTags($rawTagList);

        $similarityService = new FaqSimilarityService();
        $analysis = $similarityService->analyzeQuestion($pertanyaan, null, 50.0, $jawaban);
        $hasExactDraftMatch = (bool) ($analysis['has_exact_draft_match'] ?? false);

        $isReady = ($pertanyaan !== '' && $jawaban !== '' && !empty($tags) && !$hasExactDraftMatch);
        $status = $isReady ? 'ready' : 'incomplete';

        $items = session('uploaded_faqs', []);
        $updatedItem = null;

        foreach ($items as &$item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if ($itemKey === $rowId || $item['id'] === $rowId) {
                $item['pertanyaan']            = $pertanyaan;
                $item['jawaban']               = $jawaban;
                $item['tags']                  = $tags;
                $item['pustaka_judul']         = $pustakaJudul;
                $item['pustaka_bagian']        = $pustakaBagian !== '' ? $pustakaBagian : 'Dokumen Rujukan Resmi';
                $item['status']                = $status;
                $item['checked']               = $isReady && !($analysis['has_exact_match'] ?? false);
                $item['has_exact_match']       = (bool) ($analysis['has_exact_match'] ?? false);
                $item['exact_match']           = $analysis['exact_match'] ?? null;
                $item['has_exact_draft_match'] = $hasExactDraftMatch;
                $item['exact_draft_match']     = $analysis['exact_draft_match'] ?? null;
                $item['similar_candidates']    = $analysis['candidates'] ?? [];
                $item['max_similarity']        = $analysis['max_similarity'] ?? 0.0;
                $updatedItem = $item;
                break;
            }
        }
        unset($item);

        session(['uploaded_faqs' => $items]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'item'   => $updatedItem,
            ]);
        }

        return redirect()->route($this->routePrefix($request) . 'pratinjau')->with('success', "Baris {$rowId} berhasil diperbarui.");
    }

    public function deletePreviewItem(Request $request)
    {
        $rowId = $request->input('id');
        $items = session('uploaded_faqs', []);

        $items = array_values(array_filter($items, function ($item) use ($rowId) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            return $itemKey !== $rowId && $item['id'] !== $rowId;
        }));
        session(['uploaded_faqs' => $items]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route($this->routePrefix($request) . 'pratinjau')->with('success', "Baris {$rowId} dihapus dari pratinjau.");
    }

    /**
     * Menyimpan hasil pratinjau unggahan file dari Validator:
     * - Validator Admik -> langsung 'Diterima'
     * - Validator Unit -> langsung 'Menunggu Validasi Admik' (tDirektorat_id unitnya)
     */
    public function submitPratinjau(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        $itemsJson = $request->input('items_json');

        if (!empty($itemsJson)) {
            $decoded = json_decode($itemsJson, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (empty($items)) {
            $items = session('uploaded_faqs', []);
        }

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 FAQ menggunakan kotak centang terlebih dahulu.');
        }

        $isUnitVal   = $this->isUnitContext($request);
        $ownDirId    = Auth::user()?->getEffectiveDirektoratId() ?? 1;
        $validatorId = Auth::id() ?? User::where('tRoles_id', 3)->value('id') ?? 1;
        $similarityService = new FaqSimilarityService();

        $missingTags = [];
        $exactDraftConflicts = [];

        foreach ($items as $item) {
            $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
            if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));
                $jawabanText = trim((string) ($item['jawaban'] ?? ''));
                $itemTags = is_array($item['tags'] ?? null) ? array_filter($item['tags'], fn($t) => trim((string) $t) !== '') : [];

                $draftMatch = $similarityService->findExactDraftMatch($pertanyaanText, $jawabanText);
                if ($draftMatch !== null) {
                    $exactDraftConflicts[] = ($item['id'] ?? $itemKey) . " (sama persis dengan {$draftMatch['id']} / {$draftMatch['version_id']})";
                }

                if (empty($itemTags)) {
                    $missingTags[] = $item['id'] ?? $itemKey;
                }
            }
        }

        if (!empty($exactDraftConflicts)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: Tidak dapat melanjutkan karena Pertanyaan dan Jawaban sama persis dengan FAQ yang sudah ada: ' . implode(', ', $exactDraftConflicts) . '.'
            );
        }

        if (!empty($missingTags)) {
            return redirect()->back()->with(
                'error',
                'Peringatan: FAQ (' . implode(', ', $missingTags) . ') belum memiliki Kata Kunci. Silakan lengkapi melalui tombol Ubah terlebih dahulu.'
            );
        }

        try {
            DaftarPustaka::ensurePublicPrivateEnum();
            FaqVersion::ensureIndonesianStatusEnum();
            DB::beginTransaction();
            $savedCount = 0;
            $remainingItems = [];
            $targetStatus = $isUnitVal ? 'Menunggu Validasi Admik' : 'Diterima';

            foreach ($items as $item) {
                $itemKey = $item['row_key'] ?? $item['version_id'] ?? $item['id'];
                if (in_array($itemKey, $selectedIds) || in_array($item['id'], $selectedIds)) {
                    $pertanyaanText = trim((string) ($item['pertanyaan'] ?? ''));

                    $candidateId = trim((string) ($item['id'] ?? ''));
                    if ($candidateId === '' || Faq::where('id', $candidateId)->exists()) {
                        $faqId = Faq::generateNextId();
                    } else {
                        $faqId = $candidateId;
                    }

                    $faq = Faq::create([
                        'id'             => $faqId,
                        'id_dataentry'   => $validatorId,
                        'tDirektorat_id' => $ownDirId,
                        'pertanyaan'     => $pertanyaanText,
                    ]);
                    $versionId = $faqId . '-V1';

                    $pustakaId = null;
                    $pustakaJudul = trim((string) ($item['pustaka_judul'] ?? ''));
                    if ($pustakaJudul !== '' && !in_array(strtolower($pustakaJudul), ['belum diisi', 'tidak ada (opsional)'], true)) {
                        $dp = DaftarPustaka::where('judul', $pustakaJudul)
                            ->orWhere('link', $pustakaJudul)
                            ->first();
                        if (!$dp) {
                            $dp = DaftarPustaka::firstOrCreate([
                                'link' => substr($pustakaJudul, 0, 45),
                            ], [
                                'judul' => substr($pustakaJudul, 0, 100),
                            ]);
                        }
                        $pustakaId = $dp->id;
                    }

                    FaqVersion::create([
                        'id'                 => $versionId,
                        'tDaftarPustakas_id' => $pustakaId,
                        'tFaqs_id'           => $faqId,
                        'id_validator'       => $validatorId,
                        'jawaban'            => $item['jawaban'] ?? '',
                        'status'             => $targetStatus,
                        'rejected_by_stage'  => null,
                        'catatan'            => null,
                    ]);

                    FaqValidasiLog::record(
                        $faqId,
                        $versionId,
                        $isUnitVal ? 'unit' : 'admik',
                        'ajukan',
                        $validatorId,
                        $isUnitVal
                            ? 'Diunggah oleh Validator Unit dan diteruskan ke Validator Admik.'
                            : 'Diunggah langsung oleh Validator Admik (Siap Terbit).'
                    );

                    if (!empty($item['tags']) && is_array($item['tags'])) {
                        $tagIds = [];
                        $resolvedTags = FileImportParser::resolveMatchingSystemTags($item['tags']);
                        foreach ($resolvedTags as $tagName) {
                            $clean = substr(trim((string) $tagName), 0, 100);
                            if ($clean !== '') {
                                $existingTag = Tag::whereRaw('LOWER(nama) = ?', [mb_strtolower($clean, 'UTF-8')])->first();
                                $t = $existingTag ?: Tag::firstOrCreate(['nama' => $clean]);
                                $tagIds[] = $t->id;
                            }
                        }
                        if (!empty($tagIds)) {
                            $faq->tags()->syncWithoutDetaching(array_unique($tagIds));
                        }
                    }

                    $savedCount++;
                } else {
                    $remainingItems[] = $item;
                }
            }

            DB::commit();

            if (empty($remainingItems)) {
                session()->forget(['uploaded_faqs', 'uploaded_filename']);
            } else {
                session(['uploaded_faqs' => $remainingItems]);
            }

            if ($isUnitVal) {
                return redirect()->route('validator.unit.daftarFAQ')
                    ->with('success', "{$savedCount} FAQ dari unggahan berkas berhasil disimpan dan diteruskan ke Validator Admik!");
            }

            return redirect()->route('validator.validasiFAQ')
                ->with('success', "{$savedCount} FAQ dari unggahan berkas berhasil masuk ke Validasi FAQ (status Diterima) dan siap diterbitkan!");
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan FAQ dari pratinjau: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * DAFTAR KATA KUNCI (TABEL ttag) UNTUK VALIDATOR
     * ========================================================================= */

    public function kataKunciIndex()
    {
        $tags = Tag::withCount('faqs')->orderBy('id', 'asc')->get();
        $previewKeywords = session('uploaded_keywords', []);
        $previewKeywordsFile = session('uploaded_keywords_file', null);

        return view('validator.katakunci', compact('tags', 'previewKeywords', 'previewKeywordsFile'));
    }

    public function kataKunciStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Nama kata kunci wajib diisi.',
        ]);

        $parts = preg_split('/[,;]+/', $request->nama);
        $added = 0;

        foreach ($parts as $p) {
            $clean = substr(trim($p), 0, 100);
            if ($clean !== '') {
                Tag::firstOrCreate(['nama' => $clean]);
                $added++;
            }
        }

        return redirect()->route($this->routePrefix($request) . 'katakunci')
            ->with('success', "{$added} kata kunci berhasil disimpan ke database!");
    }

    public function kataKunciUpdate(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $tag = Tag::findOrFail($id);
        $tag->update(['nama' => trim($request->nama)]);

        return redirect()->route($this->routePrefix($request) . 'katakunci')
            ->with('success', "Kata kunci berhasil diperbarui menjadi '{$tag->nama}'.");
    }

    public function kataKunciDestroy(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $nama = $tag->nama;
        $tag->faqs()->detach();
        $tag->delete();

        return redirect()->route($this->routePrefix($request) . 'katakunci')
            ->with('success', "Kata kunci '{$nama}' berhasil dihapus.");
    }

    public function kataKunciUpload(Request $request)
    {
        $request->validate([
            'file_katakunci' => 'required|file|max:10240',
        ], [
            'file_katakunci.required' => 'Pilih file Excel / CSV / PDF kata kunci terlebih dahulu.',
        ]);

        $file = $request->file('file_katakunci');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $keywords = FileImportParser::mapRowsToKeywords($rows);

        if (empty($keywords)) {
            return redirect()->route($this->routePrefix($request) . 'katakunci')
                ->with('error', "Tidak ditemukan kata kunci yang dapat dibaca dari file {$filename}.");
        }

        session([
            'uploaded_keywords'      => $keywords,
            'uploaded_keywords_file' => $filename,
        ]);

        return redirect()->route($this->routePrefix($request) . 'katakunci')
            ->with('success', 'Berhasil membaca ' . count($keywords) . " kata kunci dari {$filename}. Silakan tinjau pratinjau di bawah sebelum disimpan!");
    }

    public function kataKunciSaveImport(Request $request)
    {
        $selectedKeywords = $request->input('keywords', []);
        if (empty($selectedKeywords)) {
            $selectedKeywords = session('uploaded_keywords', []);
        }

        $saved = 0;
        foreach ($selectedKeywords as $kw) {
            $clean = substr(trim((string) $kw), 0, 100);
            if ($clean !== '') {
                Tag::firstOrCreate(['nama' => $clean]);
                $saved++;
            }
        }

        session()->forget(['uploaded_keywords', 'uploaded_keywords_file']);

        return redirect()->route($this->routePrefix($request) . 'katakunci')
            ->with('success', "{$saved} kata kunci dari file import berhasil disimpan ke tabel ttag!");
    }

    public function kataKunciCancelImport(Request $request)
    {
        session()->forget(['uploaded_keywords', 'uploaded_keywords_file']);
        return redirect()->route($this->routePrefix($request) . 'katakunci')->with('success', 'Pratinjau import kata kunci dibatalkan.');
    }

    /* =========================================================================
     * SUMBER PUSTAKA (TABEL tdaftarpustaka) UNTUK VALIDATOR
     * ========================================================================= */

    public function sumberPustakaIndex()
    {
        DaftarPustaka::ensurePublicPrivateEnum();
        $pustakas = DaftarPustaka::withCount('faqVersions')->orderBy('id', 'asc')->get();
        $previewSources = session('uploaded_sources', []);
        $previewSourcesFile = session('uploaded_sources_file', null);

        return view('validator.sumberpustaka', compact('pustakas', 'previewSources', 'previewSourcesFile'));
    }

    public function sumberPustakaStore(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $request->validate([
            'judul'       => 'required|string|max:100',
            'source_kind' => 'required|in:link,upload',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'judul.max'      => 'Judul maksimal 100 karakter.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $judulText = substr(trim($request->judul), 0, 100);
        $sourceKind = $request->input('source_kind', 'link');

        if ($sourceKind === 'upload') {
            if (!$request->hasFile('file_upload')) {
                return redirect()->back()->with('error', 'Untuk jenis input Dokumen, Anda wajib mengunggah file dokumen.');
            }

            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            DaftarPustaka::create([
                'judul'  => $judulText,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
                ->with('success', "Dokumen '{$judulText}' berhasil diunggah ke Sumber Pustaka!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Untuk jenis input Tautan, kolom Link / Tautan wajib diisi.');
        }

        $linkText = substr(trim($request->link), 0, 60);
        DaftarPustaka::create([
            'judul'  => $judulText,
            'link'   => $linkText,
            'upload' => null,
        ]);

        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
            ->with('success', "Tautan sumber pustaka '{$judulText}' berhasil ditambahkan!");
    }

    public function sumberPustakaUpdate(Request $request, $id)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $pustaka = DaftarPustaka::findOrFail($id);

        $request->validate([
            'judul'       => 'required|string|max:100',
            'link'        => 'nullable|string|max:60',
            'file_upload' => 'nullable|file|max:10240',
        ], [
            'judul.required' => 'Judul Dokumen / Tautan wajib diisi.',
            'link.max'       => 'Link / Tautan sumber pustaka maksimal 60 karakter.',
        ]);

        $newJudul = substr(trim($request->judul), 0, 100);
        $isFileRecord = !empty($pustaka->upload);

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filename = substr($file->getClientOriginalName(), 0, 60);

            $pustaka->update([
                'judul'  => $newJudul,
                'link'   => $filename,
                'upload' => file_get_contents($file->getRealPath()),
            ]);

            return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
                ->with('success', "Berkas sumber pustaka '{$newJudul}' berhasil diperbarui!");
        }

        if ($isFileRecord) {
            $pustaka->update([
                'judul' => $newJudul,
            ]);

            return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
                ->with('success', "Dokumen sumber pustaka '{$newJudul}' berhasil diperbarui!");
        }

        if (!$request->filled('link')) {
            return redirect()->back()->with('error', 'Link sumber pustaka tidak boleh kosong.');
        }

        $newLink = substr(trim($request->link), 0, 60);
        $pustaka->update([
            'judul' => $newJudul,
            'link'  => $newLink,
        ]);

        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
            ->with('success', "Sumber pustaka '{$newJudul}' berhasil diperbarui!");
    }

    public function sumberPustakaUpload(Request $request)
    {
        $request->validate([
            'file_pustaka' => 'required|file|max:10240',
        ]);

        $file = $request->file('file_pustaka');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = FileImportParser::parseRows($file->getRealPath(), $extension);
        $sources = FileImportParser::mapRowsToSumberPustaka($rows);

        if (empty($sources)) {
            return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
                ->with('error', "Tidak ditemukan daftar tautan sumber pustaka yang dapat dibaca dari file {$filename}. Pastikan file mengikuti format 2 kolom: (1. Judul Tautan, 2. Tautan/Link).");
        }

        session([
            'uploaded_sources'      => $sources,
            'uploaded_sources_file' => $filename,
        ]);

        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
            ->with('success', 'Berhasil membaca ' . count($sources) . " baris tautan dari {$filename}. Silakan pantau kesesuaian pada pratinjau di bawah!");
    }

    public function sumberPustakaSaveImport(Request $request)
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $selectedIndexes = $request->input('selected_indexes', []);
        $sources = session('uploaded_sources', []);

        $saved = 0;
        foreach ($sources as $idx => $src) {
            if (empty($selectedIndexes) || in_array((string) $idx, $selectedIndexes, true)) {
                $judulName = substr(trim($src['judul'] ?? ''), 0, 100);
                $linkName  = substr(trim($src['link'] ?? ''), 0, 60);

                if ($judulName === '' && $linkName !== '') {
                    $judulName = $linkName;
                }

                if ($linkName !== '') {
                    DaftarPustaka::updateOrCreate([
                        'link' => $linkName,
                    ], [
                        'judul'  => $judulName,
                        'upload' => null,
                    ]);
                    $saved++;
                }
            }
        }

        session()->forget(['uploaded_sources', 'uploaded_sources_file']);

        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
            ->with('success', "{$saved} tautan sumber pustaka berhasil disimpan ke tabel tdaftarpustaka!");
    }

    public function sumberPustakaCancelImport(Request $request)
    {
        session()->forget(['uploaded_sources', 'uploaded_sources_file']);
        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')->with('success', 'Pratinjau import sumber pustaka dibatalkan.');
    }

    public function sumberPustakaDestroy(Request $request, $id)
    {
        $pustaka = DaftarPustaka::withCount('faqVersions')->findOrFail($id);
        $nama = $pustaka->judul_tampil;
        if ($pustaka->faq_versions_count > 0) {
            return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
                ->with('error', "Sumber pustaka '{$nama}' sedang digunakan oleh {$pustaka->faq_versions_count} versi FAQ dan tidak dapat dihapus.");
        }

        $pustaka->delete();

        return redirect()->route($this->routePrefix($request) . 'sumberpustaka')
            ->with('success', "Sumber pustaka '{$nama}' berhasil dihapus.");
    }

    /**
     * Halaman Pengaturan Unit Penerima Alert Call Layanan Akademik:
     * Validator dapat menentukan unit/direktorat mana yang berhak menerima notifikasi panggilan suara/layanan.
     */
    public function pengaturanAlertCall()
    {
        $direktorats = \App\Models\Direktorat::with(['users' => function ($q) {
            $q->with('role')->where('is_active', 1);
        }])->orderBy('nama', 'asc')->get();

        $selectedUnitIds = \App\Models\Setting::getAllowedCallUnitIds();

        return view('validator.pengaturan-alert-call', [
            'direktorats'     => $direktorats,
            'selectedUnitIds' => $selectedUnitIds,
        ]);
    }

    /**
     * Simpan Pengaturan Unit Penerima Alert Call oleh Validator ke tabel tsettings
     */
    public function updatePengaturanAlertCall(Request $request)
    {
        $request->validate([
            'unit_ids'   => 'required|array|min:1',
            'unit_ids.*' => 'integer|exists:tdirektorat,id',
        ], [
            'unit_ids.required' => 'Pilih minimal satu unit / direktorat untuk menerima alert call.',
            'unit_ids.min'      => 'Pilih minimal satu unit / direktorat untuk menerima alert call.',
        ]);

        \App\Models\Setting::setAllowedCallUnitIds($request->input('unit_ids', []));

        return redirect()->route('validator.pengaturanAlertCall')
            ->with('success', 'Pengaturan unit penerima alert call berhasil disimpan ke tabel pengaturan sistem (tsettings).');
    }
}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Validasi FAQ - Ruang Validator | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator CSS -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">

    @include('partials.milestone-modal-helper')
</head>
<body>

<div class="d-flex">
    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    @include('partials.validator-sidebar', ['activeMenu' => 'validasiFAQ'])

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN WRAPPER
    ══════════════════════════════════════ -->
    <main class="flex-grow-1 min-vw-0">

        <!-- Top Navbar (Tanpa Pencarian) -->
        @php
            $tbUser = auth()->user();
            if ($tbUser && !$tbUser->relationLoaded('role')) {
                $tbUser->load('role');
            }
            $tbRole = $tbUser?->getRoleName() ?: 'Validator';
            $tbName = $tbUser?->nama ?? 'Pengguna KMS';
            $tbEmail = $tbUser?->email ?? '';
            $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'KM';
        @endphp
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Left: Mobile Toggle & Breadcrumb -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="top-breadcrumb d-flex align-items-center">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="crumb-sep">/</span>
                    <span>{{ $tbRole }}</span>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">Validasi FAQ</span>
                </div>
            </div>

            <!-- Right: Notification & Profile -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="top-icon-btn" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ $tbInitials }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $tbName }}</div>
                            <div class="fs-11 text-muted">{{ $tbRole }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end profile-dropdown mt-2">
                        <li>
                            <div class="px-3 py-2 mb-1 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ $tbName }}</div>
                                <div class="fs-11 text-muted">{{ $tbEmail }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('validator.pengaturanAkun') }}">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan Akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openLogoutModal()">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div class="fs-13 fw-medium text-dark">{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div class="fs-13 fw-medium text-dark">{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Header (Ukuran & warna seragam dengan halaman lain) -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="page-title mb-0 me-1">Validasi FAQ</h1>
                </div>
                <p class="fs-13 text-muted mb-0">
                    Tinjau pertanyaan, jawaban, kata kunci, dan rujukan sumber melalui tombol <strong>Lihat</strong> untuk menerima, menolak, atau menerbitkan FAQ.
                </p>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Filter Bar -->
                <form method="GET" action="{{ route('validator.validasiFAQ') }}" id="validasiFilterForm">
                    <div class="filter-bar">
                        <div class="filter-left-group">
                            <!-- Search -->
                            <div class="filter-search-wrap">
                                <i class="bi bi-search"></i>
                                <input type="text"
                                       name="search"
                                       id="searchInputFaq"
                                       value="{{ request('search') }}"
                                       placeholder="Cari kode atau pertanyaan FAQ...">
                            </div>

                            <!-- Filter Unit Terdaftar -->
                            <select name="unit_id" id="unitFilterSelect" class="filter-select" onchange="document.getElementById('validasiFilterForm').submit()">
                                <option value="all" {{ in_array((string) request('unit_id', 'all'), ['', 'all'], true) ? 'selected' : '' }}>Semua Unit Terdaftar</option>
                                @foreach(($direktorats ?? collect()) as $dirOpt)
                                    <option value="{{ $dirOpt->id }}" {{ (string) request('unit_id') === (string) $dirOpt->id ? 'selected' : '' }}>
                                        {{ $dirOpt->nama }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Filter Data Entry -->
                            <select name="data_entry" id="dataEntryFilter" class="filter-select" onchange="document.getElementById('validasiFilterForm').submit()">
                                <option value="all" {{ request('data_entry', 'all') === 'all' ? 'selected' : '' }}>Semua Data Entry</option>
                                @foreach($dataEntryUsers as $deUser)
                                    <option value="{{ $deUser }}" {{ request('data_entry') === $deUser ? 'selected' : '' }}>{{ $deUser }}</option>
                                @endforeach
                            </select>

                            <!-- Sort -->
                            <select name="sort" id="sortFilter" class="filter-select" onchange="document.getElementById('validasiFilterForm').submit()">
                                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                            </select>

                            @if(request()->hasAny(['search', 'unit_id', 'data_entry', 'sort']) && collect(request()->only(['search', 'unit_id', 'data_entry', 'sort']))->filter(fn($v) => $v && $v !== 'all' && $v !== 'terbaru')->isNotEmpty())
                                <a href="{{ route('validator.validasiFAQ') }}" class="btn-filter-reset">
                                    <i class="bi bi-x-lg"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Bulk Approve Form Wrapping Table -->
                <form id="bulkApproveForm" action="{{ route('validator.validasiFAQ.bulkApprove') }}" method="POST">
                    @csrf

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="faq-table" id="validasiFaqTable">
                            <thead>
                                <tr>
                                    <th style="width: 48px;" class="ps-4">
                                        <input type="checkbox" id="selectAllCheckbox" class="faq-checkbox" title="Pilih semua FAQ">
                                    </th>
                                    <th style="width: 130px;">KODE / VERSI</th>
                                    <th>PERTANYAAN</th>
                                    <th style="width: 190px;">UNIT</th>
                                    <th style="width: 185px;">DATA ENTRY</th>
                                    <th style="width: 165px;">WAKTU PENGAJUAN</th>
                                    <th style="width: 115px;" class="text-end pe-4">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="faqTableBody">
                                @forelse($queueItems as $index => $item)
                                    @php
                                        $avatarColors = ['de-avatar-teal', 'de-avatar-blue', 'de-avatar-amber', 'de-avatar-purple'];
                                        $colorClass   = $avatarColors[$index % count($avatarColors)];
                                        $isChecked    = !empty($item->default_checked);
                                        $createdAtObj = ($item->created_at instanceof \Carbon\Carbon
                                            ? $item->created_at
                                            : \Carbon\Carbon::parse($item->created_at))->timezone('Asia/Jakarta');
                                        $itemStatus   = $item->status ?? 'Menunggu Validasi';
                                        $isApproved   = !empty($item->is_approved) || strtolower($itemStatus) === 'diterima';
                                        $isScheduled  = !empty($item->is_scheduled);
                                        $schedMode    = $item->schedule_mode ?? 'now';
                                        $schedDate    = $item->schedule_date ?? '';
                                        $schedTime    = $item->schedule_time ?? '';
                                        $schedLabel   = $item->schedule_label ?? '';
                                        $tagsJson     = json_encode($item->tags_list ?? []);
                                        $prevVersJson = json_encode($item->previous_versions ?? []);
                                        $simFaqsJson  = json_encode($item->similar_faqs ?? []);
                                        $logsJson     = json_encode($item->milestone_logs ?? []);
                                        $hasRevision  = !empty($item->jawaban_sebelumnya) || !empty($item->catatan_revisi);
                                        $simCount     = is_array($item->similar_faqs ?? null) ? count($item->similar_faqs) : 0;
                                    @endphp
                                    <tr class="faq-row {{ $isChecked ? 'row-checked' : '' }}"
                                        data-id="{{ $item->id }}"
                                        data-status="{{ $isApproved ? 'Diterima' : 'Menunggu Validasi' }}"
                                        data-raw-status="{{ $item->raw_status ?? ($isApproved ? 'Diterima' : 'Menunggu Validasi Admik') }}"
                                        data-is-approved="{{ $isApproved ? '1' : '0' }}"
                                        data-is-scheduled="{{ $isScheduled ? '1' : '0' }}"
                                        data-schedule-mode="{{ $schedMode }}"
                                        data-schedule-date="{{ $schedDate }}"
                                        data-schedule-time="{{ $schedTime }}"
                                        data-schedule-label="{{ $schedLabel }}"
                                        data-code="{{ strtolower($item->kode) }}"
                                        data-question="{{ strtolower($item->pertanyaan) }}"
                                        data-category="{{ strtolower($item->kategori) }}"
                                        data-de="{{ $item->data_entry_nama }}"
                                        data-full-code="{{ $item->kode }}"
                                        data-full-version="{{ $item->versi }}"
                                        data-full-question="{{ $item->pertanyaan }}"
                                        data-full-category="{{ $item->kategori }}"
                                        data-full-tags="{{ $tagsJson }}"
                                        data-full-answer="{{ $item->jawaban }}"
                                        data-prev-answer="{{ $item->jawaban_sebelumnya ?? '' }}"
                                        data-revision-note="{{ $item->catatan_revisi ?? '' }}"
                                        data-prev-versions="{{ $prevVersJson }}"
                                        data-similar-faqs="{{ $simFaqsJson }}"
                                        data-milestone-logs="{{ $logsJson }}"
                                        data-unit-nama="{{ $item->unit_nama ?? 'Direktorat Akademik' }}"
                                        data-unit-is-pusat="{{ !empty($item->unit_is_pusat) ? '1' : '0' }}"
                                        data-unit-approval="{{ $item->unit_approval_info ?? '' }}"
                                        data-full-source="{{ $item->sumber }}"
                                        data-full-source-type="{{ $item->sumber_type ?? 'Publik' }}"
                                        data-full-source-link="{{ $item->sumber_link ?? '' }}"
                                        data-full-source-is-file="{{ !empty($item->sumber_is_file) ? '1' : '0' }}"
                                        data-full-source-view-url="{{ $item->sumber_view_url ?? '' }}"
                                        data-full-de="{{ $item->data_entry_nama }}"
                                        data-full-date="{{ $item->tanggal_indo ?? $createdAtObj->translatedFormat('d M Y') }}"
                                        data-full-time="{{ $createdAtObj->format('H.i') }} WIB">

                                        <!-- Checkbox -->
                                        <td class="ps-4">
                                            <input type="checkbox"
                                                   name="faq_ids[]"
                                                   value="{{ $item->id }}"
                                                   data-status="{{ $isApproved ? 'Diterima' : 'Menunggu Validasi' }}"
                                                   class="faq-checkbox row-checkbox"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                        </td>

                                        <!-- Kode / Versi -->
                                        <td>
                                            <div class="faq-code">{{ $item->kode }}</div>
                                            <div class="faq-version">{{ $item->versi }}</div>
                                        </td>

                                        <!-- Pertanyaan -->
                                        <td>
                                            <div class="faq-question">{{ $item->pertanyaan }}</div>
                                        </td>

                                        <!-- Unit -->
                                        <td>
                                            <span class="kms-unit-pill {{ !empty($item->unit_is_pusat) ? 'is-pusat' : '' }}" title="Unit asal pengajuan FAQ">
                                                <i class="bi {{ !empty($item->unit_is_pusat) ? 'bi-bank2' : 'bi-building' }}"></i>{{ $item->unit_nama ?? 'Direktorat Akademik' }}
                                            </span>
                                        </td>

                                        <!-- Data Entry -->
                                        <td>
                                            <div class="fs-13 fw-bold text-dark lh-sm">{{ $item->data_entry_nama }}</div>
                                        </td>

                                        <!-- Waktu Pengajuan (created_at) -->
                                        <td>
                                            <div class="fs-13 fw-medium text-dark lh-sm">
                                                {{ $item->tanggal_indo ?? $createdAtObj->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="fs-11 text-muted mt-0.5">
                                                {{ $createdAtObj->format('H.i') }} WIB
                                            </div>
                                        </td>

                                        <!-- Aksi (Hanya Tombol Lihat) -->
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex align-items-center justify-content-end flex-nowrap">
                                                <button type="button" class="btn-action-pill btn-pill-view" onclick="openViewModal(this)">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Lihat</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                                <i class="bi bi-journal-check fs-2 opacity-50"></i>
                                                <div class="fw-semibold fs-14 text-dark">Belum ada antrean FAQ yang menunggu validasi</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty Filter Notice -->
                    <div id="emptyFilterState" class="text-center py-5 d-none">
                        <i class="bi bi-search fs-2 text-muted mb-2 d-block"></i>
                        <div class="fw-bold fs-14 text-dark">Tidak ada FAQ yang cocok dengan pencarian</div>
                        <div class="fs-12 text-muted">Coba gunakan kata kunci atau filter Data Entry lainnya.</div>
                    </div>

                    <!-- Pagination Footer -->
                    @if($queueItems->hasPages())
                        <div class="pagination-bar">
                            <span>
                                Menampilkan {{ $queueItems->firstItem() }}–{{ $queueItems->lastItem() }}
                                dari {{ $queueItems->total() }} FAQ &middot; {{ $queueItems->perPage() }} FAQ per halaman
                            </span>
                            <div class="d-flex gap-1 align-items-center">
                                <a href="{{ $queueItems->previousPageUrl() ?? '#' }}"
                                   class="page-btn {{ $queueItems->onFirstPage() ? 'disabled' : '' }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>

                                @for($p = 1; $p <= $queueItems->lastPage(); $p++)
                                    <a href="{{ $queueItems->url($p) }}"
                                       class="page-btn {{ $queueItems->currentPage() === $p ? 'active' : '' }}">
                                        {{ $p }}
                                    </a>
                                @endfor

                                <a href="{{ $queueItems->nextPageUrl() ?? '#' }}"
                                   class="page-btn {{ !$queueItems->hasMorePages() ? 'disabled' : '' }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="pagination-bar">
                            <span>
                                Menampilkan {{ $queueItems->count() }} dari {{ $queueItems->total() }} FAQ &middot; 6 FAQ per halaman
                            </span>
                            <div class="d-flex gap-1 align-items-center">
                                <span class="page-btn disabled"><i class="bi bi-chevron-left"></i></span>
                                <span class="page-btn active">1</span>
                                <span class="page-btn disabled"><i class="bi bi-chevron-right"></i></span>
                            </div>
                        </div>
                    @endif
                </form>

            </div><!-- /.table-card -->

            <!-- Bottom Bulk Approval & Publish Bar -->
            <div class="bulk-approval-card">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-selected-count" id="selectedCountBadge">0 FAQ dipilih</span>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="btnBulkApprove" class="btn-bulk-approve" onclick="openBulkApproveModal()" disabled>
                        <i class="bi bi-check2-circle"></i>
                        <span>Diterima</span>
                    </button>
                    <button type="button" id="btnBulkPublish" class="btn-bulk-publish" onclick="openBulkPublishModal()" disabled>
                        <i class="bi bi-send-check"></i>
                        <span>Terbitkan</span>
                    </button>
                </div>
            </div>

        </div><!-- /.content-area -->
    </main>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 1: DETAIL VIEW FAQ (TEMA WEBSITE ELEGAN & MUDAH DIBACA)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalViewFaq" tabindex="-1" aria-labelledby="modalViewFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content kms-modal-content" style="border-radius: 18px; overflow: hidden;">
            <div class="kms-modal-header" style="background: linear-gradient(135deg, #FFFDFB 0%, #FDF2F4 100%); border-bottom: 1px solid #F3E2E5; padding: 1.1rem 1.4rem;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-12" style="background-color: #85172C; color: #ffffff;" id="viewModalCode">FAQ-0001</span>
                    <span class="fs-12 fw-semibold" style="color: #85172C;" id="viewModalVersion">Versi 1</span>
                    <span class="kms-unit-pill" id="viewModalUnitPill"><i class="bi bi-building" id="viewModalUnitIcon"></i><span id="viewModalUnitText">Direktorat Akademik</span></span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fs-12 text-muted" id="viewModalDe">Data Entry</span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fs-12 text-muted" id="viewModalTime">-</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body" style="padding: 1.4rem; max-height: 74vh; overflow-y: auto;">
                <!-- Milestone Status Validasi -->
                <div id="viewModalMilestoneWrap"></div>

                <!-- Info Persetujuan Tahap 1 (Unit) bila berasal dari Unit Non-Pusat -->
                <div class="mb-3 d-none" id="viewModalUnitApprovalBanner">
                    <div class="rounded-3 px-3 py-2 d-flex align-items-center gap-2" style="background-color:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;font-size:0.78rem;">
                        <i class="bi bi-patch-check-fill fs-6"></i>
                        <span id="viewModalUnitApprovalText"></span>
                    </div>
                </div>

                <!-- Catatan Kemiripan dengan FAQ Lain Lintas Seluruh Unit -->
                <div class="mb-3">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;">
                        <i class="bi bi-intersect me-1"></i>Catatan Kemiripan dengan FAQ Lain (Semua Unit)
                    </div>
                    <div id="viewModalSimilarWrap"></div>
                </div>

                <!-- Pertanyaan -->
                <div class="mb-3">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;">Pertanyaan</div>
                    <div class="p-3 rounded-3 fs-15 fw-semibold text-dark lh-base" style="background-color: #FFFDFB; border: 1px solid #EDE7E1;" id="viewModalQuestion"></div>
                </div>

                <!-- Jawaban -->
                <div class="mb-3">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;" id="viewModalAnswerLabel">Jawaban</div>
                    <div class="p-3 rounded-3 bg-white border fs-14 text-dark lh-lg" style="border-color: #EDE7E1 !important;" id="viewModalAnswer"></div>
                </div>

                <!-- Pembanding Revisi Sebelumnya (Jika FAQ ini hasil revisi dari penolakan) -->
                <div class="mb-3 d-none" id="viewModalRevisionCompareWrap">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #B91C1C; letter-spacing: 0.05em;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Pembanding Sebelum Direvisi (Ditolak)
                    </div>
                    <div class="p-3 rounded-3" style="background-color: #FEF2F2; border: 1px solid #FECACA;">
                        <div class="mb-2 d-none" id="viewModalRevisionNoteBox">
                            <div class="fw-bold fs-12" style="color: #991B1B;">
                                <i class="bi bi-chat-left-text-fill me-1"></i>Catatan Penolakan Validator Sebelumnya:
                            </div>
                            <div class="fs-13 text-dark mt-1" id="viewModalRevisionNoteText"></div>
                        </div>
                        <div class="d-none" id="viewModalPrevAnswerBox">
                            <div class="fw-bold fs-12 mb-1" style="color: #991B1B;">Jawaban Sebelum Direvisi:</div>
                            <div class="p-2.5 rounded-3 bg-white border fs-13 text-secondary lh-base" style="border-color: #FCA5A5 !important; padding: 0.65rem 0.85rem;" id="viewModalPrevAnswerText"></div>
                        </div>
                    </div>
                </div>

                <!-- Kata Kunci -->
                <div class="mb-3">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;">Kata Kunci</div>
                    <div class="d-flex flex-wrap gap-1.5" id="viewModalTagsContainer"></div>
                </div>

                <!-- Sumber Pustaka -->
                <div>
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;">Sumber Pustaka</div>
                    <div class="rounded-3 px-3 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background-color: #FBF9F7; border: 1px solid #EDE7E1;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-bookmark-fill" style="color: #85172C; font-size: 1rem;"></i>
                            <div>
                                <div class="fw-semibold fs-13 text-dark" id="viewModalSource"></div>
                                <div class="fs-11 text-muted" id="viewModalSourceMeta"></div>
                            </div>
                        </div>
                        <a href="#" target="_blank" id="viewModalSourceBtn" class="btn btn-sm btn-outline-secondary rounded-3 d-none fs-12 fw-semibold">
                            <i class="bi bi-eye me-1"></i>Lihat Berkas
                        </a>
                    </div>
                </div>

                <!-- Pembanding Versi Valid Sebelumnya (Misal Versi 1 yang sudah Diterima/Terbit) -->
                <div class="mt-3 d-none" id="viewModalPrevVersionsWrap">
                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #85172C; letter-spacing: 0.05em;">
                        <i class="bi bi-clock-history me-1"></i>Pembanding Versi Sebelumnya
                    </div>
                    <div class="d-flex flex-column gap-2" id="viewModalPrevVersionsList"></div>
                </div>
            </div>
            <div class="kms-modal-footer justify-content-between" style="padding: 0.9rem 1.4rem;">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <!-- Kiri (sebelah tombol Ditolak): Diterima (jika Menunggu Validasi) atau Terbit (jika Diterima) -->
                    <button type="button" class="btn-action-pill btn-pill-approved" id="modalBtnApprove">
                        <i class="bi bi-check2"></i>
                        <span>Diterima</span>
                    </button>
                    <!-- Kanan: Ditolak -->
                    <button type="button" class="btn-action-pill btn-pill-rejected" id="modalBtnReject">
                        <i class="bi bi-x"></i>
                        <span>Ditolak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Tersembunyi untuk Langsung Menerima Satu FAQ Tanpa Modal Konfirmasi -->
<form id="formSingleApprove" method="POST" class="d-none">
    @csrf
</form>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 3: KONFIRMASI PENOLAKAN FAQ (SINGLE REJECT + CATATAN REVISI)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalSingleReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-x-circle"></i>
                    </span>
                    <h5 class="kms-modal-title" id="singleRejectTitle">Tolak Pengajuan FAQ</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <form id="formSingleReject" method="POST">
                @csrf
                <div class="kms-modal-body">
                    <div class="p-2.5 rounded-3 border bg-light mb-2.5" style="padding: 0.65rem 0.85rem;">
                        <div class="fw-bold fs-12 text-dark" id="singleRejectCode">FAQ-0001</div>
                        <div class="fs-12 text-muted mt-0.5" id="singleRejectQuestion"></div>
                    </div>
                    <div>
                        <label for="rejectCatatanInput" class="form-label fw-semibold fs-12 text-dark mb-1">
                            Catatan Revisi / Alasan Penolakan <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <textarea name="catatan" id="rejectCatatanInput" rows="3" class="kms-textarea" placeholder="Tuliskan bagian yang perlu diperbaiki oleh Data Entry..."></textarea>
                    </div>
                </div>
                <div class="kms-modal-footer">
                    <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-kms-danger">
                        <i class="bi bi-x-lg"></i>
                        <span>Tolak Pengajuan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 4: KONFIRMASI PERSETUJUAN MASSAL (BULK APPROVE)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalBulkApprove" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: var(--brand-maroon-soft); color: var(--brand-maroon);">
                        <i class="bi bi-check2-all"></i>
                    </span>
                    <h5 class="kms-modal-title" id="bulkApproveModalTitle">Setujui FAQ Terpilih</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                <p class="mb-2 text-secondary fs-13">
                    Anda akan menerima pengajuan FAQ berikut agar siap diterbitkan:
                </p>
                <div class="p-2.5 rounded-3 border bg-light fs-12 fw-semibold text-dark" id="bulkApproveCodesList" style="padding: 0.65rem 0.85rem;"></div>
                <div class="fs-11 text-muted mt-2">
                    Setelah berstatus Diterima, Anda dapat menekan tombol <strong>Terbitkan</strong> atau membuka menu <strong>Lihat</strong> untuk menentukan waktu terbit.
                </div>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-primary" id="btnConfirmBulkSubmit">
                    <i class="bi bi-check2-all"></i>
                    <span id="btnConfirmBulkText">Terima FAQ</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 5: TERBITKAN FAQ (PUBLIKASIKAN SEKARANG [BAWAAN] & JADWAL PUBLIKASI)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPublishFaq" tabindex="-1" aria-labelledby="modalPublishFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content kms-modal-content">
            <form id="formPublishFaq" action="{{ route('validator.validasiFAQ.publish') }}" method="POST" onsubmit="return validatePublishForm()">
                @csrf
                <div id="publishFaqIdsContainer"></div>
                <input type="hidden" name="publish_mode" id="publishModeInput" value="now">

                <div class="publish-modal-box" style="max-height: 72vh; overflow-y: auto;">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <div>
                            <h5 class="fw-bold text-dark mb-1" id="modalPublishFaqLabel" style="font-size: 1.05rem;">Terbitkan FAQ</h5>
                            <p class="text-muted fs-13 mb-2">Pilih metode dan waktu penerbitan FAQ yang telah diterima.</p>
                        </div>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
                    </div>

                    <!-- Info FAQ yang akan diterbitkan -->
                    <div class="mb-3 px-3 py-2 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                        <span class="fs-12 text-muted">FAQ yang akan diterbitkan:</span>
                        <span class="fs-12 fw-bold text-dark" id="publishSelectedCodesText">-</span>
                    </div>

                    <!-- Pilihan 1 (Bawaan): Publikasikan Sekarang -->
                    <div class="publish-option-card active" data-mode="now" onclick="selectPublishMode('now')">
                        <div class="publish-option-header">
                            <div class="publish-option-title">
                                <i class="bi bi-lightning-charge" style="color: #059669; font-size: 1.1rem;"></i>
                                <span>Publikasikan Sekarang</span>
                            </div>
                            <span class="publish-default-badge">Bawaan</span>
                        </div>
                        <div class="publish-option-desc">
                            FAQ langsung diterbitkan sekarang agar segera tersedia di halaman FAQ mahasiswa dan tamu.
                        </div>
                        <p class="publish-option-sub">Cocok untuk informasi yang harus langsung tayang saat ini juga.</p>
                    </div>

                    <!-- Pilihan 2: Jadwal Publikasi (Satu kesatuan kotak dengan input tanggal & jam) -->
                    <div class="publish-option-card mb-0" data-mode="scheduled" onclick="selectPublishMode('scheduled')">
                        <div class="publish-option-header">
                            <div class="publish-option-title">
                                <i class="bi bi-calendar-event" style="color: #2563EB; font-size: 1.1rem;"></i>
                                <span>Jadwal Publikasi</span>
                            </div>
                        </div>
                        <div class="publish-option-desc">
                            Tentukan sendiri tanggal dan jam publikasi FAQ.
                        </div>
                        <p class="publish-option-sub">Cocok jika FAQ harus tersedia pada waktu tertentu.</p>

                        <!-- Detail Input Tanggal & Jam (Menyatu di dalam kotak Jadwal Publikasi) -->
                        <div id="publishScheduleDetailBox"
                             class="d-none mt-3 pt-3"
                             style="border-top: 1px solid #DBEAFE; cursor: default;"
                             onclick="event.stopPropagation()">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-clock-history" style="color: #2563EB; font-size: 0.9rem;"></i>
                                <span class="fw-bold text-dark fs-13">Atur Tanggal &amp; Jam Publikasi</span>
                            </div>
                            <div class="text-muted fs-12 mb-2.5">
                                FAQ akan otomatis diterbitkan sesuai tanggal dan jam yang ditentukan.
                            </div>
                            <div class="row g-2.5">
                                <div class="col-12 col-sm-6">
                                    <label for="publishDateInput" class="fw-semibold text-dark fs-12 mb-1 d-block">Tanggal publikasi</label>
                                    <div class="publish-input-wrap">
                                        <i class="bi bi-calendar3"></i>
                                        <input type="date" id="publishDateInput" name="publish_date" min="{{ date('Y-m-d') }}" placeholder="Pilih tanggal">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="publishTimeInput" class="fw-semibold text-dark fs-12 mb-1 d-block">Jam publikasi</label>
                                    <div class="publish-input-wrap">
                                        <i class="bi bi-clock"></i>
                                        <input type="time" id="publishTimeInput" name="publish_time" placeholder="Pilih jam">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kms-modal-footer">
                    <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-kms-primary" id="btnConfirmPublishSubmit">
                        <i class="bi bi-send-check"></i>
                        <span id="btnConfirmPublishText">Publikasikan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL KONFIRMASI KELUAR (LOGOUT)
══════════════════════════════════════════════════════════════════ -->
@php
    $lmUser = auth()->user();
    $lmName = $lmUser?->nama ?? 'Pengguna KMS';
    $lmInitials = collect(preg_split('/\s+/', trim($lmName)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: 'KM';
@endphp
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<div class="modal fade" id="modalLogoutConfirm" tabindex="-1" aria-labelledby="modalLogoutTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-box-arrow-right"></i>
                    </span>
                    <h5 class="kms-modal-title" id="modalLogoutTitle">Konfirmasi Keluar</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem KMS?
                <div class="logout-user-card">
                    <div class="avatar-circle">{{ $lmInitials }}</div>
                    <div class="lh-sm">
                        <div class="fw-bold fs-13 text-dark">{{ $lmName }}</div>
                        <div class="fs-11 text-muted">{{ $lmUser?->email }}</div>
                    </div>
                </div>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-primary" id="btnConfirmLogout" onclick="submitLogout(this)">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Ya, Keluar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openLogoutModal() {
        const el = document.getElementById('modalLogoutConfirm');
        if (window.bootstrap && el) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (confirm('Apakah Anda yakin ingin keluar dari sistem KMS?')) {
            document.getElementById('logout-form').submit();
        }
    }

    function confirmLogout() {
        openLogoutModal();
    }

    function submitLogout(btn) {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>Memproses...</span>';
        }
        document.getElementById('logout-form').submit();
    }
</script>

<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }



    // ═══════════════════════════════════════════════════════════════
    // CHECKLIST, BULK APPROVED & BULK PUBLISH LOGIC
    // ═══════════════════════════════════════════════════════════════
    const selectAllCheckbox  = document.getElementById('selectAllCheckbox');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const selectedHelperText = document.getElementById('selectedHelperText');
    const btnBulkApprove     = document.getElementById('btnBulkApprove');
    const btnBulkPublish     = document.getElementById('btnBulkPublish');

    function getVisibleRowCheckboxes() {
        return Array.from(document.querySelectorAll('.faq-row'))
            .filter(row => row.style.display !== 'none')
            .map(row => row.querySelector('.row-checkbox'))
            .filter(Boolean);
    }

    function updateChecklistState() {
        const allCheckboxes     = Array.from(document.querySelectorAll('.row-checkbox'));
        const checkedBoxes      = allCheckboxes.filter(cb => cb.checked);
        const visibleCheckboxes = getVisibleRowCheckboxes();
        const visibleChecked    = visibleCheckboxes.filter(cb => cb.checked);

        // Highlight baris yang dicentang
        allCheckboxes.forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) {
                row.classList.toggle('row-checked', cb.checked);
            }
        });

        // Hitung berapa yang Menunggu Validasi vs Diterima di antara yang dicentang
        const checkedSubmitted = checkedBoxes.filter(cb => (cb.getAttribute('data-status') || 'Menunggu Validasi') === 'Menunggu Validasi');
        const checkedApproved  = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'Diterima');

        // Update badge jumlah FAQ dipilih
        const count = checkedBoxes.length;
        selectedCountBadge.textContent = `${count} FAQ dipilih`;

        // Syarat tombol Diterima nyala: ada minimal 1 FAQ berstatus Menunggu Validasi yang dicentang
        if (btnBulkApprove) {
            btnBulkApprove.disabled = (checkedSubmitted.length === 0);
        }

        // Syarat tombol Terbitkan nyala: ada minimal 1 FAQ yang sudah berstatus Diterima yang dicentang
        if (btnBulkPublish) {
            btnBulkPublish.disabled = (checkedApproved.length === 0);
        }

        if (selectedHelperText) {
            if (count === 0) {
                selectedHelperText.textContent = 'Centang FAQ Menunggu Validasi untuk Diterima, atau centang FAQ Diterima untuk menyalakan tombol Terbitkan.';
            } else {
                selectedHelperText.textContent = `${checkedSubmitted.length} menunggu validasi · ${checkedApproved.length} siap diterbitkan.`;
            }
        }

        // Update status master checkbox di header
        if (!selectAllCheckbox) return;
        if (visibleCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (visibleChecked.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (visibleChecked.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            getVisibleRowCheckboxes().forEach(cb => {
                cb.checked = isChecked;
            });
            updateChecklistState();
        });
    }

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateChecklistState);
    });

    updateChecklistState();

    // ═══════════════════════════════════════════════════════════════
    // FILTER PENCARIAN CEPAT
    // ═══════════════════════════════════════════════════════════════
    const searchInputFaq  = document.getElementById('searchInputFaq');
    const topNavbarSearch = document.getElementById('topNavbarSearch');

    function applyQuickSearch() {
        const query = (searchInputFaq.value || '').toLowerCase().trim();
        const rows  = document.querySelectorAll('.faq-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const code     = row.getAttribute('data-code') || '';
            const question = row.getAttribute('data-question') || '';
            const category = row.getAttribute('data-category') || '';

            const matchSearch = !query || code.includes(query) || question.includes(query) || category.includes(query);
            if (matchSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('emptyFilterState');
        if (emptyState && rows.length > 0) {
            emptyState.classList.toggle('d-none', visibleCount > 0);
        }

        updateChecklistState();
    }

    if (searchInputFaq) {
        searchInputFaq.addEventListener('input', applyQuickSearch);
    }
    if (topNavbarSearch) {
        topNavbarSearch.addEventListener('input', function () {
            searchInputFaq.value = this.value;
            applyQuickSearch();
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // MODAL LIHAT DETAIL, TERIMA, TOLAK, TERIMA MASSAL & TERBITKAN
    // ═══════════════════════════════════════════════════════════════
    function escapeHtmlSimple(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function openViewModal(button) {
        const row = button.closest('.faq-row');
        if (!row) return;

        const id           = row.getAttribute('data-id');
        const code         = row.getAttribute('data-full-code');
        const version      = row.getAttribute('data-full-version');
        const question     = row.getAttribute('data-full-question');
        const answer       = row.getAttribute('data-full-answer');
        const prevAnswer   = row.getAttribute('data-prev-answer') || '';
        const revisionNote = row.getAttribute('data-revision-note') || '';
        const source       = row.getAttribute('data-full-source');
        const sourceType   = row.getAttribute('data-full-source-type') || 'Publik';
        const sourceLink   = row.getAttribute('data-full-source-link') || '';
        const sourceIsFile = row.getAttribute('data-full-source-is-file') === '1';
        const sourceUrl    = row.getAttribute('data-full-source-view-url') || '';
        const de           = row.getAttribute('data-full-de');
        const date         = row.getAttribute('data-full-date');
        const time         = row.getAttribute('data-full-time');
        const isApproved   = row.getAttribute('data-is-approved') === '1';
        const schedMode    = row.getAttribute('data-schedule-mode') || 'now';
        const schedDate    = row.getAttribute('data-schedule-date') || '';
        const schedTime    = row.getAttribute('data-schedule-time') || '';

        let tagsList = [];
        try {
            tagsList = JSON.parse(row.getAttribute('data-full-tags') || '[]');
        } catch (e) {
            tagsList = [];
        }

        let prevVersions = [];
        try {
            prevVersions = JSON.parse(row.getAttribute('data-prev-versions') || '[]');
        } catch (e) {
            prevVersions = [];
        }

        let similarFaqs = [];
        try {
            similarFaqs = JSON.parse(row.getAttribute('data-similar-faqs') || '[]');
        } catch (e) {
            similarFaqs = [];
        }

        let milestoneLogs = [];
        try {
            milestoneLogs = JSON.parse(row.getAttribute('data-milestone-logs') || '[]');
        } catch (e) {
            milestoneLogs = [];
        }

        const unitNama     = row.getAttribute('data-unit-nama') || 'Direktorat Akademik';
        const unitIsPusat  = row.getAttribute('data-unit-is-pusat') === '1';
        const unitApproval = row.getAttribute('data-unit-approval') || '';
        const rawStatusVal = row.getAttribute('data-raw-status') || (isApproved ? 'Diterima' : 'Menunggu Validasi Admik');

        document.getElementById('viewModalCode').textContent     = code;
        document.getElementById('viewModalVersion').textContent  = version;
        document.getElementById('viewModalQuestion').textContent = question;
        document.getElementById('viewModalDe').textContent       = de;
        document.getElementById('viewModalTime').textContent     = `${date}, ${time}`;
        document.getElementById('viewModalAnswer').innerHTML     = answer;

        const unitPillEl = document.getElementById('viewModalUnitPill');
        const unitTextEl = document.getElementById('viewModalUnitText');
        const unitIconEl = document.getElementById('viewModalUnitIcon');
        if (unitTextEl) unitTextEl.textContent = unitNama;
        if (unitPillEl) unitPillEl.classList.toggle('is-pusat', unitIsPusat);
        if (unitIconEl) unitIconEl.className = unitIsPusat ? 'bi bi-bank2' : 'bi bi-building';

        const unitBannerEl = document.getElementById('viewModalUnitApprovalBanner');
        const unitBannerTextEl = document.getElementById('viewModalUnitApprovalText');
        if (unitBannerEl && unitBannerTextEl) {
            if (unitApproval) {
                unitBannerTextEl.textContent = unitApproval;
                unitBannerEl.classList.remove('d-none');
            } else {
                unitBannerEl.classList.add('d-none');
            }
        }

        const milestoneWrapEl = document.getElementById('viewModalMilestoneWrap');
        if (milestoneWrapEl && window.kmsBuildMilestoneHtml) {
            milestoneWrapEl.innerHTML = window.kmsBuildMilestoneHtml({
                raw_status: rawStatusVal,
                is_pusat_unit: unitIsPusat,
                unit_nama: unitNama,
                milestone_logs: milestoneLogs,
            });
        }

        const simWrapEl = document.getElementById('viewModalSimilarWrap');
        if (simWrapEl && window.kmsBuildSimilarFaqsHtml) {
            simWrapEl.innerHTML = window.kmsBuildSimilarFaqsHtml(similarFaqs);
        }

        const answerLabelEl = document.getElementById('viewModalAnswerLabel');
        if (answerLabelEl) {
            answerLabelEl.textContent = (prevAnswer || revisionNote)
                ? `Jawaban (${version} · Hasil Revisi Terbaru)`
                : `Jawaban (${version})`;
        }

        // Tampilkan Pembanding Revisi Sebelumnya (jika sempat ditolak lalu direvisi)
        const revWrapEl = document.getElementById('viewModalRevisionCompareWrap');
        const revNoteBoxEl = document.getElementById('viewModalRevisionNoteBox');
        const revNoteTextEl = document.getElementById('viewModalRevisionNoteText');
        const prevAnsBoxEl = document.getElementById('viewModalPrevAnswerBox');
        const prevAnsTextEl = document.getElementById('viewModalPrevAnswerText');

        if (revWrapEl) {
            if (prevAnswer || revisionNote) {
                revWrapEl.classList.remove('d-none');
                if (revNoteBoxEl && revNoteTextEl) {
                    if (revisionNote) {
                        revNoteTextEl.textContent = revisionNote;
                        revNoteBoxEl.classList.remove('d-none');
                    } else {
                        revNoteBoxEl.classList.add('d-none');
                    }
                }
                if (prevAnsBoxEl && prevAnsTextEl) {
                    if (prevAnswer) {
                        prevAnsTextEl.innerHTML = prevAnswer;
                        prevAnsBoxEl.classList.remove('d-none');
                    } else {
                        prevAnsBoxEl.classList.add('d-none');
                    }
                }
            } else {
                revWrapEl.classList.add('d-none');
            }
        }

        // Tampilkan Pembanding Versi Valid Sebelumnya (misal Versi 1)
        const prevVersWrapEl = document.getElementById('viewModalPrevVersionsWrap');
        const prevVersListEl = document.getElementById('viewModalPrevVersionsList');
        if (prevVersWrapEl && prevVersListEl) {
            if (Array.isArray(prevVersions) && prevVersions.length > 0) {
                prevVersWrapEl.classList.remove('d-none');
                prevVersListEl.innerHTML = prevVersions.map(pv => `
                    <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.72rem; padding: 0.28rem 0.65rem;">${escapeHtmlSimple(pv.versi_label || 'Versi 1')}</span>
                                <span class="badge rounded-pill" style="background-color: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.7rem; padding: 0.25rem 0.6rem;">${escapeHtmlSimple(pv.status || 'Terbit')}</span>
                            </div>
                            <span class="fs-11 text-muted"><i class="bi bi-clock me-1"></i>${escapeHtmlSimple(pv.updated_at || '-')}</span>
                        </div>
                        <div class="p-2.5 rounded-3 bg-white border fs-13 text-dark lh-base" style="border-color: #E2E8F0 !important; padding: 0.65rem 0.85rem;">${pv.jawaban || '-'}</div>
                        <div class="fs-11 text-muted mt-1.5"><i class="bi bi-journal-bookmark me-1"></i>Sumber: <strong class="text-dark">${escapeHtmlSimple(pv.sumber || 'Tidak ada')}</strong></div>
                    </div>
                `).join('');
            } else {
                prevVersWrapEl.classList.add('d-none');
                prevVersListEl.innerHTML = '';
            }
        }

        // Tampilkan Kata Kunci
        const tagsContainer = document.getElementById('viewModalTagsContainer');
        if (tagsContainer) {
            if (Array.isArray(tagsList) && tagsList.length > 0) {
                tagsContainer.innerHTML = tagsList.map(t =>
                    `<span class="badge rounded-pill" style="background-color:#F8F5F1;color:#85172C;border:1px solid #EDE7E1;font-size:0.76rem;padding:0.35rem 0.8rem;font-weight:600;">${escapeHtmlSimple(t)}</span>`
                ).join('');
            } else {
                tagsContainer.innerHTML = '<span class="fs-12 text-muted">Belum ada kata kunci</span>';
            }
        }

        // Tampilkan Sumber Pustaka + tombol lihat berkas jika ada file
        document.getElementById('viewModalSource').textContent = source || 'Tidak ada (Opsional)';
        const metaEl = document.getElementById('viewModalSourceMeta');
        const btnEl  = document.getElementById('viewModalSourceBtn');
        if (metaEl) {
            metaEl.textContent = source ? (sourceIsFile ? 'Berkas Dokumen' : 'Tautan Rujukan') : 'Sumber pustaka tidak disertakan';
        }
        if (btnEl) {
            if (sourceIsFile && sourceUrl) {
                btnEl.href = sourceUrl;
                btnEl.innerHTML = '<i class="bi bi-eye me-1"></i>Lihat Berkas';
                btnEl.classList.remove('d-none');
            } else if (!sourceIsFile && /^https?:\/\//i.test(sourceLink)) {
                btnEl.href = sourceLink;
                btnEl.innerHTML = '<i class="bi bi-box-arrow-up-right me-1"></i>Buka Tautan';
                btnEl.classList.remove('d-none');
            } else {
                btnEl.classList.add('d-none');
            }
        }

        // Bagian Kiri (sebelah tombol Ditolak): Diterima (langsung terima tanpa pop-up konfirmasi) atau Terbit
        const approveBtn = document.getElementById('modalBtnApprove');
        if (approveBtn) {
            if (isApproved) {
                approveBtn.className = 'btn-action-pill btn-pill-publish';
                approveBtn.innerHTML = '<i class="bi bi-send-check"></i><span>Terbit</span>';
                approveBtn.onclick = function () {
                    bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
                    openSinglePublishModal(id, code, question, schedMode === 'scheduled' ? 'scheduled' : 'now', schedDate, schedTime);
                };
            } else {
                approveBtn.className = 'btn-action-pill btn-pill-approved';
                approveBtn.innerHTML = '<i class="bi bi-check2"></i><span>Diterima</span>';
                approveBtn.onclick = function () {
                    submitSingleApprove(id);
                };
            }
        }

        // Bagian Kanan: Ditolak
        const rejectBtn = document.getElementById('modalBtnReject');
        if (rejectBtn) {
            rejectBtn.innerHTML = '<i class="bi bi-x"></i><span>Ditolak</span>';
            rejectBtn.onclick = function () {
                bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
                openSingleRejectModal(id, code, question);
            };
        }

        const modal = new bootstrap.Modal(document.getElementById('modalViewFaq'));
        modal.show();
    }

    function submitSingleApprove(id) {
        const form = document.getElementById('formSingleApprove');
        if (!form) return;
        form.action = `/validator/validasi-faq/${encodeURIComponent(id)}/approve`;
        form.submit();
    }

    function openSingleRejectModal(id, code, question) {
        document.getElementById('singleRejectTitle').textContent    = `Tolak ${code}`;
        document.getElementById('singleRejectCode').textContent     = code;
        document.getElementById('singleRejectQuestion').textContent = question;
        document.getElementById('rejectCatatanInput').value         = '';
        document.getElementById('formSingleReject').action          = `/validator/validasi-faq/${encodeURIComponent(id)}/reject`;

        const modal = new bootstrap.Modal(document.getElementById('modalSingleReject'));
        modal.show();
    }

    function openBulkApproveModal() {
        const checkedSubmitted = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .filter(cb => (cb.getAttribute('data-status') || 'Menunggu Validasi') === 'Menunggu Validasi');
        if (checkedSubmitted.length === 0) return;

        // Pastikan hanya baris Menunggu Validasi yang ikut disubmit saat klik Diterima massal
        Array.from(document.querySelectorAll('.row-checkbox')).forEach(cb => {
            if (cb.checked && cb.getAttribute('data-status') !== 'Menunggu Validasi') {
                cb.checked = false;
            }
        });
        updateChecklistState();

        document.getElementById('bulkApproveForm')?.submit();
    }

    // ═══════════════════════════════════════════════════════════════
    // LOGIKA MODAL TERBITKAN FAQ (PUBLIKASIKAN SEKARANG & JADWAL PUBLIKASI)
    // ═══════════════════════════════════════════════════════════════
    function selectPublishMode(mode) {
        const targetMode  = (mode === 'scheduled') ? 'scheduled' : 'now';
        const modeInput   = document.getElementById('publishModeInput');
        const scheduleBox = document.getElementById('publishScheduleDetailBox');
        const dateInput   = document.getElementById('publishDateInput');
        const timeInput   = document.getElementById('publishTimeInput');
        const btnText     = document.getElementById('btnConfirmPublishText');

        if (modeInput) modeInput.value = targetMode;

        document.querySelectorAll('.publish-option-card').forEach(card => {
            card.classList.toggle('active', card.getAttribute('data-mode') === targetMode);
        });

        if (targetMode === 'scheduled') {
            scheduleBox?.classList.remove('d-none');
            if (dateInput) dateInput.required = true;
            if (timeInput) timeInput.required = true;
            if (btnText) btnText.textContent = 'Jadwalkan Publikasi';
        } else {
            scheduleBox?.classList.add('d-none');
            if (dateInput) dateInput.required = false;
            if (timeInput) timeInput.required = false;
            if (btnText) btnText.textContent = 'Publikasikan Sekarang';
        }
    }

    function populatePublishModalIds(ids, codesLabel, defaultMode, defaultDate, defaultTime) {
        const container = document.getElementById('publishFaqIdsContainer');
        const codesText = document.getElementById('publishSelectedCodesText');
        const dateInput = document.getElementById('publishDateInput');
        const timeInput = document.getElementById('publishTimeInput');

        if (container) {
            container.innerHTML = ids.map(id => `<input type="hidden" name="faq_ids[]" value="${id}">`).join('');
        }
        if (codesText) {
            codesText.textContent = codesLabel;
        }
        if (dateInput) {
            dateInput.value = defaultDate || '{{ date("Y-m-d") }}';
        }
        if (timeInput) {
            timeInput.value = defaultTime || '08:00';
        }

        selectPublishMode(defaultMode === 'scheduled' ? 'scheduled' : 'now');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPublishFaq'));
        modal.show();
    }

    function openSinglePublishModal(id, code, question, currentMode, currentDate, currentTime) {
        populatePublishModalIds(
            [id],
            `${code} — ${question.length > 48 ? question.substring(0, 48) + '...' : question}`,
            currentMode === 'scheduled' ? 'scheduled' : 'now',
            currentDate || '',
            currentTime || ''
        );
    }

    function openBulkPublishModal() {
        const checkedApproved = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .filter(cb => cb.getAttribute('data-status') === 'Diterima');

        if (checkedApproved.length === 0) return;

        const ids   = checkedApproved.map(cb => cb.value);
        const codes = checkedApproved.map(cb => cb.closest('.faq-row')?.getAttribute('data-full-code')).filter(Boolean);

        populatePublishModalIds(
            ids,
            `${checkedApproved.length} FAQ (${codes.join(', ')})`,
            'now',
            '',
            ''
        );
    }

    function validatePublishForm() {
        const mode = document.getElementById('publishModeInput')?.value;
        if (mode === 'scheduled') {
            const d = document.getElementById('publishDateInput')?.value;
            const t = document.getElementById('publishTimeInput')?.value;
            if (!d || !t) {
                alert('Silakan pilih Tanggal publikasi dan Jam publikasi terlebih dahulu.');
                return false;
            }
        }
        return true;
    }
</script>
</body>
</html>

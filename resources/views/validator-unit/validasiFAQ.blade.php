<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Validasi FAQ Unit - {{ $unitName }} | KMS</title>
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
    <!-- Sidebar Dinamis Validator Unit -->
    @include('partials.validator-sidebar', ['activeMenu' => 'validasiFAQ'])

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Wrapper -->
    <main class="flex-grow-1 min-vw-0">

        @php
            $tbUser = auth()->user();
            if ($tbUser) {
                $tbUser->loadMissing(['role', 'direktorat']);
            }
            $tbRole = ($tbUser?->getRoleName() ?: 'Validator') . ' · ' . ($unitName ?? 'Unit');
            $tbName = $tbUser?->nama ?? 'Pengguna KMS';
            $tbEmail = $tbUser?->email ?? '';
            $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'KM';
        @endphp

        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="top-breadcrumb d-flex align-items-center">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="crumb-sep">/</span>
                    <span>Validator Unit ({{ $unitName }})</span>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">Validasi FAQ Unit</span>
                </div>
            </div>

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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('validator.unit.pengaturanAkun') }}">
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

            <!-- Page Header -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="page-title mb-0 me-1">Validasi FAQ Unit</h1>
                </div>
                <p class="fs-13 text-muted mb-0">
                    Tinjau pengajuan FAQ dari Data Entry <strong>{{ $unitName }}</strong> beserta catatan kemiripan lintas unit melalui tombol <strong>Lihat</strong> untuk <strong>Setujui &amp; Teruskan ke Admik</strong> atau <strong>Tolak</strong> beserta catatan revisi.
                </p>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Filter Bar -->
                <form method="GET" action="{{ route('validator.unit.validasiFAQ') }}" id="validasiFilterForm">
                    <div class="filter-bar">
                        <div class="filter-left-group">
                            <div class="filter-search-wrap">
                                <i class="bi bi-search"></i>
                                <input type="text"
                                       name="search"
                                       id="searchInputFaq"
                                       value="{{ request('search') }}"
                                       placeholder="Cari kode atau pertanyaan FAQ...">
                            </div>

                            <select name="data_entry" id="dataEntryFilter" class="filter-select" onchange="document.getElementById('validasiFilterForm').submit()">
                                <option value="all" {{ request('data_entry', 'all') === 'all' ? 'selected' : '' }}>Semua Data Entry Unit</option>
                                @foreach($dataEntryUsers as $deUser)
                                    <option value="{{ $deUser }}" {{ request('data_entry') === $deUser ? 'selected' : '' }}>{{ $deUser }}</option>
                                @endforeach
                            </select>

                            <select name="sort" id="sortFilter" class="filter-select" onchange="document.getElementById('validasiFilterForm').submit()">
                                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                            </select>

                            @if(request()->hasAny(['search', 'data_entry', 'sort']) && collect(request()->only(['search', 'data_entry', 'sort']))->filter(fn($v) => $v && $v !== 'all' && $v !== 'terbaru')->isNotEmpty())
                                <a href="{{ route('validator.unit.validasiFAQ') }}" class="btn-filter-reset">
                                    <i class="bi bi-x-lg"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Bulk Approve Form (Tahap 1 -> Teruskan ke Admik) -->
                <form id="bulkApproveForm" action="{{ route('validator.unit.validasiFAQ.bulkApprove') }}" method="POST">
                    @csrf

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
                                        $isChecked    = !empty($item->default_checked);
                                        $createdAtObj = ($item->created_at instanceof \Carbon\Carbon
                                            ? $item->created_at
                                            : \Carbon\Carbon::parse($item->created_at))->timezone('Asia/Jakarta');
                                        $tagsJson     = json_encode($item->tags_list ?? []);
                                        $prevVersJson = json_encode($item->previous_versions ?? []);
                                        $simFaqsJson  = json_encode($item->similar_faqs ?? []);
                                        $logsJson     = json_encode($item->milestone_logs ?? []);
                                        $hasRevision  = !empty($item->jawaban_sebelumnya) || !empty($item->catatan_revisi);
                                        $simCount     = is_array($item->similar_faqs ?? null) ? count($item->similar_faqs) : 0;
                                    @endphp
                                    <tr class="faq-row {{ $isChecked ? 'row-checked' : '' }}"
                                        data-id="{{ $item->id }}"
                                        data-status="Menunggu Validasi Unit"
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
                                        data-unit-nama="{{ $item->unit_nama }}"
                                        data-unit-is-pusat="{{ !empty($item->unit_is_pusat) ? '1' : '0' }}"
                                        data-full-source="{{ $item->sumber }}"
                                        data-full-source-type="{{ $item->sumber_type ?? 'Publik' }}"
                                        data-full-source-link="{{ $item->sumber_link ?? '' }}"
                                        data-full-source-is-file="{{ !empty($item->sumber_is_file) ? '1' : '0' }}"
                                        data-full-source-view-url="{{ $item->sumber_view_url ?? '' }}"
                                        data-full-de="{{ $item->data_entry_nama }}"
                                        data-full-date="{{ $item->tanggal_indo ?? $createdAtObj->translatedFormat('d M Y') }}"
                                        data-full-time="{{ $createdAtObj->format('H.i') }} WIB">

                                        <td class="ps-4">
                                            <input type="checkbox"
                                                   name="faq_ids[]"
                                                   value="{{ $item->id }}"
                                                   data-status="Menunggu Validasi Unit"
                                                   class="faq-checkbox row-checkbox"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                        </td>

                                        <td>
                                            <div class="faq-code">{{ $item->kode }}</div>
                                            <div class="faq-version">{{ $item->versi }}</div>
                                        </td>

                                        <td>
                                            <div class="faq-question">{{ $item->pertanyaan }}</div>
                                        </td>

                                        <td>
                                            <span class="kms-unit-pill {{ !empty($item->unit_is_pusat) ? 'is-pusat' : '' }}">
                                                <i class="bi {{ !empty($item->unit_is_pusat) ? 'bi-bank2' : 'bi-building' }}"></i>{{ $item->unit_nama }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="fs-13 fw-bold text-dark lh-sm">{{ $item->data_entry_nama }}</div>
                                        </td>

                                        <td>
                                            <div class="fs-13 fw-medium text-dark lh-sm">
                                                {{ $item->tanggal_indo ?? $createdAtObj->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="fs-11 text-muted mt-0.5">
                                                {{ $createdAtObj->format('H.i') }} WIB
                                            </div>
                                        </td>

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
                                                <div class="fw-semibold fs-14 text-dark">Belum ada antrean FAQ dari Data Entry {{ $unitName }} yang menunggu validasi tahap Unit</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div id="emptyFilterState" class="text-center py-5 d-none">
                        <i class="bi bi-search fs-2 text-muted mb-2 d-block"></i>
                        <div class="fw-bold fs-14 text-dark">Tidak ada FAQ yang cocok dengan pencarian</div>
                        <div class="fs-12 text-muted">Coba gunakan kata kunci atau filter Data Entry lainnya.</div>
                    </div>

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

            <!-- Bottom Bulk Approval Bar (Hanya Setujui & Teruskan ke Admik — Tanpa Tombol Publish) -->
            <div class="bulk-approval-card">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-selected-count" id="selectedCountBadge">0 FAQ dipilih</span>
                    <span class="fs-12 text-muted d-none d-md-inline">FAQ yang disetujui pada tahap Unit akan diteruskan ke antrean Validator Admik.</span>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="btnBulkApprove" class="btn-bulk-approve" onclick="openBulkApproveModal()" disabled>
                        <i class="bi bi-check2-circle"></i>
                        <span>Setujui &amp; Teruskan ke Admik</span>
                    </button>
                </div>
            </div>

        </div><!-- /.content-area -->
    </main>
</div>

<!-- MODAL DETAIL VIEW FAQ + CATATAN KEMIRIPAN LINTAS UNIT + MILESTONE -->
<div class="modal fade" id="modalViewFaq" tabindex="-1" aria-labelledby="modalViewFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content kms-modal-content" style="border-radius: 18px; overflow: hidden;">
            <div class="kms-modal-header" style="background: linear-gradient(135deg, #FFFDFB 0%, #FDF2F4 100%); border-bottom: 1px solid #F3E2E5; padding: 1.1rem 1.4rem;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-12" style="background-color: #85172C; color: #ffffff;" id="viewModalCode">FAQ-0001</span>
                    <span class="fs-12 fw-semibold" style="color: #85172C;" id="viewModalVersion">Versi 1</span>
                    <span class="kms-unit-pill" id="viewModalUnitPill"><i class="bi bi-building"></i><span id="viewModalUnitText">{{ $unitName }}</span></span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fs-12 text-muted" id="viewModalDe">Data Entry</span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fs-12 text-muted" id="viewModalTime">-</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body" style="padding: 1.4rem; max-height: 72vh; overflow-y: auto;">
                <!-- Milestone Status -->
                <div id="viewModalMilestoneWrap"></div>

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

                <!-- Pembanding Revisi Sebelumnya -->
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

                <!-- Pembanding Versi Sebelumnya -->
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
                    <button type="button" class="btn-action-pill btn-pill-approved" id="modalBtnApprove">
                        <i class="bi bi-check2"></i>
                        <span>Setujui &amp; Teruskan ke Admik</span>
                    </button>
                    <button type="button" class="btn-action-pill btn-pill-rejected" id="modalBtnReject">
                        <i class="bi bi-x"></i>
                        <span>Tolak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Tersembunyi untuk Setujui Satu FAQ -->
<form id="formSingleApprove" method="POST" class="d-none">
    @csrf
</form>

<!-- MODAL KONFIRMASI PENOLAKAN OLEH VALIDATOR UNIT -->
<div class="modal fade" id="modalSingleReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 470px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-x-circle"></i>
                    </span>
                    <h5 class="kms-modal-title" id="singleRejectTitle">Tolak Pengajuan FAQ (Tahap Unit)</h5>
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
                    <div class="rounded-3 px-2.5 py-2 mb-3 fs-11" style="background-color:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        FAQ akan dikembalikan ke Data Entry dengan penanda <strong>"Ditolak oleh Validator Unit ({{ $unitName }})"</strong>.
                    </div>
                    <div>
                        <label for="rejectCatatanInput" class="form-label fw-semibold fs-12 text-dark mb-1">
                            Catatan Revisi / Alasan Penolakan <span class="text-muted fw-normal">(Wajib jelas bagi Data Entry)</span>
                        </label>
                        <textarea name="catatan" id="rejectCatatanInput" rows="3" class="kms-textarea" placeholder="Tuliskan bagian jawaban atau rujukan yang perlu diperbaiki oleh Data Entry {{ $unitName }}..."></textarea>
                    </div>
                </div>
                <div class="kms-modal-footer">
                    <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-kms-danger">
                        <i class="bi bi-x-lg"></i>
                        <span>Tolak &amp; Kembalikan ke Data Entry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal Konfirmasi Keluar -->
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

    function submitLogout(btn) {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>Memproses...</span>';
        }
        document.getElementById('logout-form').submit();
    }

    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('show');
        document.getElementById('sidebarBackdrop')?.classList.toggle('d-none');
    }

    const selectAllCheckbox  = document.getElementById('selectAllCheckbox');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const btnBulkApprove     = document.getElementById('btnBulkApprove');

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

        allCheckboxes.forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) row.classList.toggle('row-checked', cb.checked);
        });

        const count = checkedBoxes.length;
        if (selectedCountBadge) selectedCountBadge.textContent = `${count} FAQ dipilih`;
        if (btnBulkApprove) btnBulkApprove.disabled = (count === 0);

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

    const searchInputFaq = document.getElementById('searchInputFaq');
    if (searchInputFaq) {
        searchInputFaq.addEventListener('input', function () {
            const query = (this.value || '').toLowerCase().trim();
            const rows  = document.querySelectorAll('.faq-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const code     = row.getAttribute('data-code') || '';
                const question = row.getAttribute('data-question') || '';
                const category = row.getAttribute('data-category') || '';
                const matchSearch = !query || code.includes(query) || question.includes(query) || category.includes(query);
                row.style.display = matchSearch ? '' : 'none';
                if (matchSearch) visibleCount++;
            });

            const emptyState = document.getElementById('emptyFilterState');
            if (emptyState && rows.length > 0) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }
            updateChecklistState();
        });
    }

    function escapeHtmlSimple(str) {
        return window.kmsEscapeHtml ? window.kmsEscapeHtml(str) : String(str || '');
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
        const sourceLink   = row.getAttribute('data-full-source-link') || '';
        const sourceIsFile = row.getAttribute('data-full-source-is-file') === '1';
        const sourceUrl    = row.getAttribute('data-full-source-view-url') || '';
        const de           = row.getAttribute('data-full-de');
        const date         = row.getAttribute('data-full-date');
        const time         = row.getAttribute('data-full-time');
        const unitNama     = row.getAttribute('data-unit-nama') || '{{ $unitName }}';

        let tagsList = [];
        try { tagsList = JSON.parse(row.getAttribute('data-full-tags') || '[]'); } catch (e) { tagsList = []; }

        let prevVersions = [];
        try { prevVersions = JSON.parse(row.getAttribute('data-prev-versions') || '[]'); } catch (e) { prevVersions = []; }

        let similarFaqs = [];
        try { similarFaqs = JSON.parse(row.getAttribute('data-similar-faqs') || '[]'); } catch (e) { similarFaqs = []; }

        let milestoneLogs = [];
        try { milestoneLogs = JSON.parse(row.getAttribute('data-milestone-logs') || '[]'); } catch (e) { milestoneLogs = []; }

        document.getElementById('viewModalCode').textContent     = code;
        document.getElementById('viewModalVersion').textContent  = version;
        document.getElementById('viewModalUnitText').textContent = unitNama;
        document.getElementById('viewModalQuestion').textContent = question;
        document.getElementById('viewModalDe').textContent       = de;
        document.getElementById('viewModalTime').textContent     = `${date}, ${time}`;
        document.getElementById('viewModalAnswer').innerHTML     = answer;

        // Render Milestone & Kemiripan Lintas Unit
        const milestoneWrap = document.getElementById('viewModalMilestoneWrap');
        if (milestoneWrap && window.kmsBuildMilestoneHtml) {
            milestoneWrap.innerHTML = window.kmsBuildMilestoneHtml({
                raw_status: 'Menunggu Validasi Unit',
                is_pusat_unit: false,
                unit_nama: unitNama,
                milestone_logs: milestoneLogs,
            });
        }

        const simWrap = document.getElementById('viewModalSimilarWrap');
        if (simWrap && window.kmsBuildSimilarFaqsHtml) {
            simWrap.innerHTML = window.kmsBuildSimilarFaqsHtml(similarFaqs);
        }

        const answerLabelEl = document.getElementById('viewModalAnswerLabel');
        if (answerLabelEl) {
            answerLabelEl.textContent = (prevAnswer || revisionNote)
                ? `Jawaban (${version} · Hasil Revisi Terbaru)`
                : `Jawaban (${version})`;
        }

        const revWrapEl     = document.getElementById('viewModalRevisionCompareWrap');
        const revNoteBoxEl  = document.getElementById('viewModalRevisionNoteBox');
        const revNoteTextEl = document.getElementById('viewModalRevisionNoteText');
        const prevAnsBoxEl  = document.getElementById('viewModalPrevAnswerBox');
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

        const approveBtn = document.getElementById('modalBtnApprove');
        if (approveBtn) {
            approveBtn.onclick = function () {
                submitSingleApprove(id);
            };
        }

        const rejectBtn = document.getElementById('modalBtnReject');
        if (rejectBtn) {
            rejectBtn.onclick = function () {
                bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
                openSingleRejectModal(id, code, question);
            };
        }

        new bootstrap.Modal(document.getElementById('modalViewFaq')).show();
    }

    function submitSingleApprove(id) {
        const form = document.getElementById('formSingleApprove');
        if (!form) return;
        form.action = `{{ url('/validator-unit/validasi-faq') }}/${encodeURIComponent(id)}/approve`;
        form.submit();
    }

    function openSingleRejectModal(id, code, question) {
        document.getElementById('singleRejectTitle').textContent    = `Tolak ${code} (Tahap Unit)`;
        document.getElementById('singleRejectCode').textContent     = code;
        document.getElementById('singleRejectQuestion').textContent = question;
        document.getElementById('rejectCatatanInput').value         = '';
        document.getElementById('formSingleReject').action          = `{{ url('/validator-unit/validasi-faq') }}/${encodeURIComponent(id)}/reject`;

        new bootstrap.Modal(document.getElementById('modalSingleReject')).show();
    }

    function openBulkApproveModal() {
        const checkedBoxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;
        document.getElementById('bulkApproveForm')?.submit();
    }
</script>

@include('partials.admin-incoming-call-alert')
</body>
</html>

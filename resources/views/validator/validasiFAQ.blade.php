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
</head>
<body>

@php
    $authUser = auth()->user();
    if ($authUser && !$authUser->relationLoaded('role')) {
        $authUser->load('role');
    }
    $roleName = $authUser ? $authUser->getRoleName() : 'Validator';
@endphp

<div class="d-flex">
    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Validator</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('validator.dashboard') }}" class="nav-link-custom">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('validator.validasiFAQ') }}" class="nav-link-custom active">
                    <i class="bi bi-list-check"></i>
                    <span>Validasi FAQ</span>
                </a>
                <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider">
            <a href="{{ route('validator.pengaturanAkun') }}" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

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
                <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="page-title mb-0 me-1">Validasi FAQ</h1>
                    <span class="badge-waiting">{{ $totalWaiting }} menunggu validasi</span>
                    @if(($totalApprovedQueue ?? 0) > 0)
                        <span class="badge rounded-pill fw-semibold fs-12 px-3 py-1" style="background-color: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                            {{ $totalApprovedQueue }} Approved (Siap Publish)
                        </span>
                    @endif
                </div>
                <p class="fs-13 text-muted mb-0">
                    Tinjau pertanyaan, jawaban, dan rujukan sumber untuk menyetujui (Approved) dan mempublikasikan (Publish) FAQ.
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

                            @if(request()->hasAny(['search', 'data_entry', 'sort']) && collect(request()->only(['search', 'data_entry', 'sort']))->filter(fn($v) => $v && $v !== 'all' && $v !== 'terbaru')->isNotEmpty())
                                <a href="{{ route('validator.validasiFAQ') }}" class="btn-filter-reset">
                                    <i class="bi bi-x-lg"></i> Reset
                                </a>
                            @endif
                        </div>

                        <div>
                            <span class="pill-waiting-status">Menunggu Validasi</span>
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
                                    <th style="width: 130px;">Kode / Versi</th>
                                    <th>Pertanyaan</th>
                                    <th style="width: 210px;">Data Entry</th>
                                    <th style="width: 165px;">Waktu Pengajuan</th>
                                    <th style="width: 310px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="faqTableBody">
                                @forelse($queueItems as $index => $item)
                                    @php
                                        $avatarColors = ['de-avatar-teal', 'de-avatar-blue', 'de-avatar-amber', 'de-avatar-purple'];
                                        $colorClass   = $avatarColors[$index % count($avatarColors)];
                                        $isChecked    = !empty($item->default_checked);
                                        $createdAtObj = $item->created_at instanceof \Carbon\Carbon
                                            ? $item->created_at
                                            : \Carbon\Carbon::parse($item->created_at);
                                        $itemStatus   = $item->status ?? 'Submitted';
                                        $isApproved   = !empty($item->is_approved) || strtolower($itemStatus) === 'approved';
                                        $isScheduled  = !empty($item->is_scheduled);
                                        $schedMode    = $item->schedule_mode ?? 'routine';
                                        $schedDate    = $item->schedule_date ?? '';
                                        $schedTime    = $item->schedule_time ?? '';
                                        $schedLabel   = $item->schedule_label ?? '';
                                    @endphp
                                    <tr class="faq-row {{ $isChecked ? 'row-checked' : '' }}"
                                        data-id="{{ $item->id }}"
                                        data-status="{{ $isApproved ? 'Approved' : 'Submitted' }}"
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
                                        data-full-answer="{{ $item->jawaban }}"
                                        data-full-source="{{ $item->sumber }}"
                                        data-full-de="{{ $item->data_entry_nama }}"
                                        data-full-date="{{ $item->tanggal_indo ?? $createdAtObj->translatedFormat('d M Y') }}"
                                        data-full-time="{{ $createdAtObj->format('H.i') }} WIB">

                                        <!-- Checkbox -->
                                        <td class="ps-4">
                                            <input type="checkbox"
                                                   name="faq_ids[]"
                                                   value="{{ $item->id }}"
                                                   data-status="{{ $isApproved ? 'Approved' : 'Submitted' }}"
                                                   class="faq-checkbox row-checkbox"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                        </td>

                                        <!-- Kode / Versi -->
                                        <td>
                                            <div class="faq-code">{{ $item->kode }}</div>
                                            <div class="faq-version">{{ $item->versi }}</div>
                                            <div class="mt-1">
                                                @if($isApproved)
                                                    <span class="badge-status badge-approved">
                                                        <i class="bi bi-check-circle-fill"></i> Approved
                                                    </span>
                                                @else
                                                    <span class="badge-status badge-submitted">
                                                        <i class="bi bi-hourglass-split"></i> Submitted
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Pertanyaan -->
                                        <td>
                                            <div class="faq-question">{{ $item->pertanyaan }}</div>
                                            <div class="faq-category-sub">{{ $item->kategori }}</div>
                                            @if($isApproved)
                                                <div class="mt-1.5">
                                                    @if($isScheduled && $schedLabel)
                                                        <span class="publish-wait-badge {{ $schedMode === 'routine' ? 'is-routine' : 'is-scheduled-date' }}">
                                                            <i class="bi {{ $schedMode === 'routine' ? 'bi-clock-history' : 'bi-calendar-event' }}"></i>
                                                            <span>{{ $schedLabel }}</span>
                                                        </span>
                                                    @else
                                                        <span class="publish-wait-badge">
                                                            <i class="bi bi-info-circle"></i>
                                                            <span>Disetujui &middot; Belum Dipublish</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Data Entry -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="de-avatar {{ $colorClass }}">{{ $item->data_entry_initials }}</div>
                                                <div>
                                                    <div class="fs-13 fw-bold text-dark lh-sm">{{ $item->data_entry_nama }}</div>
                                                    <div class="fs-11 text-muted">{{ $item->data_entry_role ?? 'Data Entry' }}</div>
                                                </div>
                                            </div>
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

                                        <!-- Aksi (View, Approved / Publish, Rejected) -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                <button type="button" class="btn-action-pill btn-pill-view" onclick="openViewModal(this)">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>
                                                @if($isApproved)
                                                    <button type="button"
                                                            class="btn-action-pill btn-pill-publish {{ $isScheduled ? 'is-scheduled' : '' }}"
                                                            onclick="openSinglePublishModal('{{ $item->id }}', '{{ $item->kode }}', '{{ addslashes($item->pertanyaan) }}', '{{ $schedMode }}', '{{ $schedDate }}', '{{ $schedTime }}')">
                                                        <i class="bi {{ $isScheduled ? 'bi-calendar2-check' : 'bi-send-check' }}"></i>
                                                        <span>Publish</span>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn-action-pill btn-pill-approved" onclick="openSingleApproveModal('{{ $item->id }}', '{{ $item->kode }}', '{{ addslashes($item->pertanyaan) }}')">
                                                        <i class="bi bi-check2"></i>
                                                        <span>Approved</span>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn-action-pill btn-pill-rejected" onclick="openSingleRejectModal('{{ $item->id }}', '{{ $item->kode }}', '{{ addslashes($item->pertanyaan) }}')">
                                                    <i class="bi bi-x"></i>
                                                    <span>Rejected</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                                <i class="bi bi-journal-check fs-2 opacity-50"></i>
                                                <div class="fw-semibold fs-14 text-dark">Belum ada antrean FAQ yang menunggu validasi</div>
                                                <div class="fs-12">Jalankan <code>php artisan db:seed</code> untuk mengisi data uji coba atau tunggu pengajuan dari Data Entry.</div>
                                            </div>
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
                                Menampilkan {{ $queueItems->count() }} dari {{ $totalWaiting }} FAQ &middot; 6 FAQ per halaman
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
                    <span class="fs-12 text-muted" id="selectedHelperText">Centang FAQ Submitted untuk Approved, atau centang FAQ Approved untuk menyalakan tombol Publish.</span>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="btnBulkApprove" class="btn-bulk-approve" onclick="openBulkApproveModal()" disabled>
                        <i class="bi bi-check2-circle"></i>
                        <span>Approved</span>
                    </button>
                    <button type="button" id="btnBulkPublish" class="btn-bulk-publish" onclick="openBulkPublishModal()" disabled>
                        <i class="bi bi-send-check"></i>
                        <span>Publish</span>
                    </button>
                </div>
            </div>

        </div><!-- /.content-area -->
    </main>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 1: DETAIL VIEW FAQ
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalViewFaq" tabindex="-1" aria-labelledby="modalViewFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="pill-waiting-status">Menunggu Validasi</span>
                    <span class="fs-13 fw-bold text-dark" id="viewModalCode">FAQ-0248</span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fs-12 text-muted" id="viewModalVersion">Versi 1</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                <div class="fs-11 fw-bold text-uppercase text-muted mb-1" id="viewModalCategory">Akademik · Fakultas Teknik</div>
                <h3 class="display-serif fs-20 fw-bold text-dark mb-3" id="viewModalQuestion">
                    Persyaratan yudisium Fakultas Teknik
                </h3>

                <div class="rounded-3 p-3 mb-3" style="background-color: #F8FAFC; border: 1px solid #F1F5F9;">
                    <div class="row g-2 fs-12">
                        <div class="col-sm-6">
                            <span class="text-muted">Diajukan oleh:</span>
                            <strong class="text-dark ms-1" id="viewModalDe">Nadia Putri</strong>
                            <span class="text-muted">(Data Entry)</span>
                        </div>
                        <div class="col-sm-6 text-sm-end text-muted">
                            Waktu Pengajuan: <strong class="text-dark" id="viewModalTime">21 Okt 2026, 10.18 WIB</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-uppercase fw-bold fs-11 text-muted mb-2" style="letter-spacing: 0.05em;">Jawaban untuk Mahasiswa</div>
                    <div class="p-3 rounded-3 bg-white border fs-13 text-secondary lh-lg" id="viewModalAnswer"></div>
                </div>

                <div>
                    <div class="text-uppercase fw-bold fs-11 text-muted mb-2" style="letter-spacing: 0.05em;">Rujukan Sumber Pustaka Resmi</div>
                    <div class="rounded-3 p-3 d-flex align-items-center gap-3" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: #DBEAFE; color: #2563EB;">
                            <i class="bi bi-file-earmark-text fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-13 text-dark" id="viewModalSource"></div>
                            <div class="fs-11 text-muted">Dokumen Resmi Kampus &middot; Terverifikasi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kms-modal-footer justify-content-between">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-action-pill btn-pill-rejected" id="modalBtnReject">
                        <i class="bi bi-x"></i>
                        <span>Rejected</span>
                    </button>
                    <button type="button" class="btn-action-pill btn-pill-approved" id="modalBtnApprove">
                        <i class="bi bi-check2"></i>
                        <span>Approved</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 2: KONFIRMASI PERSETUJUAN SATU FAQ (SINGLE APPROVE)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalSingleApprove" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: #ECFDF5; color: #059669;">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                    <h5 class="kms-modal-title" id="singleApproveTitle">Setujui Pengajuan FAQ</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <form id="formSingleApprove" method="POST">
                @csrf
                <div class="kms-modal-body">
                    <p class="mb-2 text-secondary fs-13">
                        Anda akan menyetujui pengajuan FAQ berikut agar siap diterbitkan ke portal mahasiswa:
                    </p>
                    <div class="p-2.5 rounded-3 border bg-light" style="padding: 0.65rem 0.85rem;">
                        <div class="fw-bold fs-12 text-dark" id="singleApproveCode">FAQ-0248</div>
                        <div class="fs-12 text-muted mt-0.5" id="singleApproveQuestion"></div>
                    </div>
                </div>
                <div class="kms-modal-footer">
                    <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-kms-success">
                        <i class="bi bi-check2"></i>
                        <span>Setujui FAQ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <div class="p-2.5 rounded-3 border bg-light mb-3" style="padding: 0.65rem 0.85rem;">
                        <div class="fw-bold fs-12 text-dark" id="singleRejectCode">FAQ-0245</div>
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
                    Anda akan menyetujui (Approved) pengajuan FAQ berikut:
                </p>
                <div class="p-2.5 rounded-3 border bg-light fs-12 fw-semibold text-dark" id="bulkApproveCodesList" style="padding: 0.65rem 0.85rem;"></div>
                <div class="fs-11 text-muted mt-2">
                    Setelah berstatus Approved, Anda dapat menekan tombol <strong>Publish</strong> untuk memilih metode publikasi.
                </div>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-primary" id="btnConfirmBulkSubmit">
                    <i class="bi bi-check2-all"></i>
                    <span id="btnConfirmBulkText">Approved</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 5: PUBLIKASIKAN FAQ (PILIHAN METODE & JADWAL PUBLIKASI)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPublishFaq" tabindex="-1" aria-labelledby="modalPublishFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content kms-modal-content">
            <form id="formPublishFaq" action="{{ route('validator.validasiFAQ.publish') }}" method="POST" onsubmit="return validatePublishForm()">
                @csrf
                <div id="publishFaqIdsContainer"></div>
                <input type="hidden" name="publish_mode" id="publishModeInput" value="routine">

                <div class="publish-modal-box">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <div>
                            <h5 class="fw-bold text-dark mb-1" id="modalPublishFaqLabel" style="font-size: 1.05rem;">Publikasikan FAQ</h5>
                            <p class="text-muted fs-13 mb-2">Pilih metode dan waktu publikasi FAQ yang telah disetujui.</p>
                        </div>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
                    </div>

                    <!-- Info FAQ yang akan dipublish -->
                    <div class="mb-3 px-3 py-2 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                        <span class="fs-12 text-muted">FAQ yang akan dipublikasikan:</span>
                        <span class="fs-12 fw-bold text-dark" id="publishSelectedCodesText">-</span>
                    </div>

                    <!-- Pilihan 1: Publikasi Rutin -->
                    <div class="publish-option-card active" data-mode="routine" onclick="selectPublishMode('routine')">
                        <div class="publish-option-header">
                            <div class="publish-option-title">
                                <i class="bi bi-calendar2-check" style="color: #16A34A; font-size: 1.1rem;"></i>
                                <span>Publikasi Rutin</span>
                            </div>
                            <span class="publish-default-badge">Default</span>
                        </div>
                        <div class="publish-option-desc">
                            FAQ akan dipublikasikan secara otomatis pada jadwal rutin sistem, yaitu pukul <strong>00.00–01.00 WIB (atau Jam 23.00 WIB).</strong>
                        </div>
                        <p class="publish-option-sub">Cocok untuk publikasi FAQ secara berkala dalam jumlah banyak.</p>
                    </div>

                    <!-- Pilihan 2: Jadwalkan Publikasi -->
                    <div class="publish-option-card" data-mode="scheduled" onclick="selectPublishMode('scheduled')">
                        <div class="publish-option-header">
                            <div class="publish-option-title">
                                <i class="bi bi-calendar-event" style="color: #2563EB; font-size: 1.1rem;"></i>
                                <span>Jadwalkan Publikasi</span>
                            </div>
                        </div>
                        <div class="publish-option-desc">
                            Tentukan sendiri tanggal dan jam publikasi FAQ.
                        </div>
                        <p class="publish-option-sub">Cocok jika FAQ harus tersedia pada waktu tertentu.</p>
                    </div>

                    <!-- Pilihan 3: Publikasikan Sekarang -->
                    <div class="publish-option-card mb-0" data-mode="now" onclick="selectPublishMode('now')">
                        <div class="publish-option-header">
                            <div class="publish-option-title">
                                <i class="bi bi-lightning-charge" style="color: #EA580C; font-size: 1.1rem;"></i>
                                <span>Publikasikan Sekarang</span>
                            </div>
                        </div>
                        <div class="publish-option-desc">
                            FAQ langsung dipublikasikan tanpa menunggu jadwal rutin.
                        </div>
                        <p class="publish-option-sub">Cocok untuk informasi yang harus segera diketahui mahasiswa.</p>
                    </div>

                    <!-- Detail Input Tanggal & Jam (Muncul saat memilih Jadwalkan Publikasi) -->
                    <div id="publishScheduleDetailBox" class="publish-schedule-box d-none mt-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-calendar-event" style="color: #2563EB; font-size: 1rem;"></i>
                            <span class="fw-bold text-dark" style="font-size: 0.9375rem;">Jadwalkan Publikasi</span>
                        </div>
                        <div class="text-muted fs-12 mb-2">
                            FAQ akan otomatis diaktifkan dan ditampilkan kepada mahasiswa sesuai tanggal dan waktu yang ditentukan.
                        </div>
                        <hr class="my-2.5" style="border-color: #E2E8F0;">

                        <div class="mb-3">
                            <label for="publishDateInput" class="fw-bold text-dark fs-13 mb-1.5 d-block">Tanggal publikasi</label>
                            <div class="publish-input-wrap">
                                <i class="bi bi-calendar3"></i>
                                <input type="date" id="publishDateInput" name="publish_date" min="{{ date('Y-m-d') }}" placeholder="Pilih tanggal">
                            </div>
                        </div>

                        <div>
                            <label for="publishTimeInput" class="fw-bold text-dark fs-13 mb-1.5 d-block">Jam publikasi</label>
                            <div class="publish-input-wrap">
                                <i class="bi bi-clock"></i>
                                <input type="time" id="publishTimeInput" name="publish_time" placeholder="Pilih jam">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kms-modal-footer">
                    <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-kms-primary" id="btnConfirmPublishSubmit">
                        <i class="bi bi-send-check"></i>
                        <span id="btnConfirmPublishText">Konfirmasi Publish</span>
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

        // Hitung berapa yang Submitted vs Approved di antara yang dicentang
        const checkedSubmitted = checkedBoxes.filter(cb => (cb.getAttribute('data-status') || 'Submitted') === 'Submitted');
        const checkedApproved  = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'Approved');

        // Update badge jumlah FAQ dipilih
        const count = checkedBoxes.length;
        selectedCountBadge.textContent = `${count} FAQ dipilih`;

        // Syarat tombol Approved nyala: ada minimal 1 FAQ berstatus Submitted yang dicentang
        if (btnBulkApprove) {
            btnBulkApprove.disabled = (checkedSubmitted.length === 0);
        }

        // Syarat tombol Publish nyala: ada minimal 1 FAQ yang sudah berstatus Approved yang dicentang
        if (btnBulkPublish) {
            btnBulkPublish.disabled = (checkedApproved.length === 0);
        }

        if (selectedHelperText) {
            if (count === 0) {
                selectedHelperText.textContent = 'Centang FAQ Submitted untuk Approved, atau centang FAQ Approved untuk menyalakan tombol Publish.';
            } else {
                selectedHelperText.textContent = `${checkedSubmitted.length} menunggu Approved · ${checkedApproved.length} siap di-Publish.`;
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
    // MODAL VIEW, APPROVE, REJECT, BULK APPROVE & PUBLISH
    // ═══════════════════════════════════════════════════════════════
    function openViewModal(button) {
        const row = button.closest('.faq-row');
        if (!row) return;

        const id          = row.getAttribute('data-id');
        const code        = row.getAttribute('data-full-code');
        const version     = row.getAttribute('data-full-version');
        const question    = row.getAttribute('data-full-question');
        const category    = row.getAttribute('data-full-category');
        const answer      = row.getAttribute('data-full-answer');
        const source      = row.getAttribute('data-full-source');
        const de          = row.getAttribute('data-full-de');
        const date        = row.getAttribute('data-full-date');
        const time        = row.getAttribute('data-full-time');
        const isApproved  = row.getAttribute('data-is-approved') === '1';
        const schedMode   = row.getAttribute('data-schedule-mode') || 'routine';
        const schedDate   = row.getAttribute('data-schedule-date') || '';
        const schedTime   = row.getAttribute('data-schedule-time') || '';

        document.getElementById('viewModalCode').textContent     = code;
        document.getElementById('viewModalVersion').textContent  = version;
        document.getElementById('viewModalCategory').textContent = category;
        document.getElementById('viewModalQuestion').textContent = question;
        document.getElementById('viewModalDe').textContent       = de;
        document.getElementById('viewModalTime').textContent     = `${date}, ${time}`;
        document.getElementById('viewModalAnswer').innerHTML     = answer;
        document.getElementById('viewModalSource').textContent   = source;

        const approveBtn = document.getElementById('modalBtnApprove');
        if (approveBtn) {
            if (isApproved) {
                approveBtn.className = 'btn-action-pill btn-pill-publish';
                approveBtn.innerHTML = '<i class="bi bi-send-check"></i><span>Publish</span>';
                approveBtn.onclick = function () {
                    bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
                    openSinglePublishModal(id, code, question, schedMode, schedDate, schedTime);
                };
            } else {
                approveBtn.className = 'btn-action-pill btn-pill-approved';
                approveBtn.innerHTML = '<i class="bi bi-check2"></i><span>Approved</span>';
                approveBtn.onclick = function () {
                    bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
                    openSingleApproveModal(id, code, question);
                };
            }
        }

        document.getElementById('modalBtnReject').onclick = function () {
            bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
            openSingleRejectModal(id, code, question);
        };

        const modal = new bootstrap.Modal(document.getElementById('modalViewFaq'));
        modal.show();
    }

    function openSingleApproveModal(id, code, question) {
        document.getElementById('singleApproveTitle').textContent    = `Setujui ${code}`;
        document.getElementById('singleApproveCode').textContent     = code;
        document.getElementById('singleApproveQuestion').textContent = question;
        document.getElementById('formSingleApprove').action          = `/validator/validasi-faq/${encodeURIComponent(id)}/approve`;

        const modal = new bootstrap.Modal(document.getElementById('modalSingleApprove'));
        modal.show();
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
            .filter(cb => (cb.getAttribute('data-status') || 'Submitted') === 'Submitted');
        if (checkedSubmitted.length === 0) return;

        // Pastikan hanya baris Submitted yang ikut disubmit saat klik Approved massal
        Array.from(document.querySelectorAll('.row-checkbox')).forEach(cb => {
            if (cb.checked && cb.getAttribute('data-status') !== 'Submitted') {
                cb.checked = false;
            }
        });
        updateChecklistState();

        const codes = checkedSubmitted.map(cb => cb.closest('.faq-row')?.getAttribute('data-full-code')).filter(Boolean);

        document.getElementById('bulkApproveModalTitle').textContent = `Approved ${checkedSubmitted.length} FAQ Terpilih`;
        document.getElementById('bulkApproveCodesList').textContent  = codes.join(', ');
        document.getElementById('btnConfirmBulkText').textContent    = `Approved ${checkedSubmitted.length} FAQ`;

        document.getElementById('btnConfirmBulkSubmit').onclick = function () {
            document.getElementById('bulkApproveForm').submit();
        };

        const modal = new bootstrap.Modal(document.getElementById('modalBulkApprove'));
        modal.show();
    }

    // ═══════════════════════════════════════════════════════════════
    // LOGIKA MODAL PUBLIKASIKAN FAQ (SINGLE & BULK PUBLISH)
    // ═══════════════════════════════════════════════════════════════
    function selectPublishMode(mode) {
        const modeInput   = document.getElementById('publishModeInput');
        const scheduleBox = document.getElementById('publishScheduleDetailBox');
        const dateInput   = document.getElementById('publishDateInput');
        const timeInput   = document.getElementById('publishTimeInput');
        const btnText     = document.getElementById('btnConfirmPublishText');

        if (modeInput) modeInput.value = mode;

        document.querySelectorAll('.publish-option-card').forEach(card => {
            card.classList.toggle('active', card.getAttribute('data-mode') === mode);
        });

        if (mode === 'scheduled') {
            scheduleBox?.classList.remove('d-none');
            if (dateInput) dateInput.required = true;
            if (timeInput) timeInput.required = true;
            if (btnText) btnText.textContent = 'Jadwalkan Publikasi';
        } else {
            scheduleBox?.classList.add('d-none');
            if (dateInput) dateInput.required = false;
            if (timeInput) timeInput.required = false;
            if (btnText) {
                btnText.textContent = mode === 'now' ? 'Publikasikan Sekarang' : 'Simpan Publikasi Rutin';
            }
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
            timeInput.value = defaultTime || '23:00';
        }

        selectPublishMode(defaultMode || 'routine');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPublishFaq'));
        modal.show();
    }

    function openSinglePublishModal(id, code, question, currentMode, currentDate, currentTime) {
        populatePublishModalIds(
            [id],
            `${code} — ${question.length > 48 ? question.substring(0, 48) + '...' : question}`,
            currentMode || 'routine',
            currentDate || '',
            currentTime || ''
        );
    }

    function openBulkPublishModal() {
        const checkedApproved = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .filter(cb => cb.getAttribute('data-status') === 'Approved');

        if (checkedApproved.length === 0) return;

        const ids   = checkedApproved.map(cb => cb.value);
        const codes = checkedApproved.map(cb => cb.closest('.faq-row')?.getAttribute('data-full-code')).filter(Boolean);

        populatePublishModalIds(
            ids,
            `${checkedApproved.length} FAQ (${codes.join(', ')})`,
            'routine',
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

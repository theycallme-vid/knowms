<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KMS — Validasi FAQ (Antrean Validasi)</title>
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
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Pill -->
            <div>
                <span class="role-pill">Ruang {{ $roleName ?: 'Validator' }}</span>
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
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom text-secondary" style="cursor: pointer;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>

            <!-- Hidden Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN WRAPPER
    ══════════════════════════════════════ -->
    <main class="flex-grow-1 min-vw-0">

        <!-- Top Navbar -->
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Left: Mobile Toggle & Breadcrumb -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center text-muted fs-13">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="mx-2 text-secondary">/</span>
                    <span>{{ $roleName ?: 'Validator' }}</span>
                    <span class="mx-2 text-secondary">/</span>
                    <strong class="text-dark">Validasi FAQ</strong>
                </div>
            </div>

            <!-- Right: Search, Notification & Profile Dropdown -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" id="topNavbarSearch" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr($authUser->nama ?? 'RK', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? 'Validator KMS' }}</div>
                            <div class="fs-11 text-muted">{{ $roleName ?: 'Validator' }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? 'Validator KMS' }}</div>
                                <div class="fs-11 text-muted">{{ $authUser->email ?? '' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 d-flex align-items-center gap-2" href="#">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan Akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openLogoutModal()">
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
                <div class="d-flex align-items-center gap-3 flex-wrap mb-1">
                    <h1 class="page-title mb-0">Antrean Validasi</h1>
                    <span class="badge-waiting">{{ $totalWaiting }} menunggu</span>
                </div>
                <p class="fs-13 text-muted mb-0">
                    Tinjau pertanyaan, jawaban, dan rujukan sumber sebelum menyetujui FAQ.
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
                                    @endphp
                                    <tr class="faq-row {{ $isChecked ? 'row-checked' : '' }}"
                                        data-id="{{ $item->id }}"
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
                                            <div class="faq-category-sub">{{ $item->kategori }}</div>
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

                                        <!-- Aksi (View, Approved, Rejected merah) -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                <button type="button" class="btn-action-pill btn-pill-view" onclick="openViewModal(this)">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>
                                                <button type="button" class="btn-action-pill btn-pill-approved" onclick="openSingleApproveModal('{{ $item->id }}', '{{ $item->kode }}', '{{ addslashes($item->pertanyaan) }}')">
                                                    <i class="bi bi-check2"></i>
                                                    <span>Approved</span>
                                                </button>
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

            <!-- Bottom Bulk Approval Bar (Pojok Kanan Bawah) -->
            <div class="bulk-approval-card">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-selected-count" id="selectedCountBadge">0 FAQ dipilih</span>
                    <span class="fs-12 text-muted">Setujui hanya FAQ yang telah ditinjau beserta sumber pustakanya.</span>
                </div>
                <div class="ms-auto">
                    <button type="button" id="btnBulkApprove" class="btn-bulk-approve" onclick="openBulkApproveModal()">
                        <i class="bi bi-arrow-right"></i>
                        <span>Approved FAQ</span>
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
                    Anda akan menyetujui secara massal pengajuan FAQ berikut:
                </p>
                <div class="p-2.5 rounded-3 border bg-light fs-12 fw-semibold text-dark" id="bulkApproveCodesList" style="padding: 0.65rem 0.85rem;"></div>
                <div class="fs-11 text-muted mt-2">
                    Pastikan substansi jawaban dan sumber pustaka pada FAQ yang dipilih telah ditinjau.
                </div>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-primary" id="btnConfirmBulkSubmit">
                    <i class="bi bi-check2-all"></i>
                    <span id="btnConfirmBulkText">Setujui Semua</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL 5: KONFIRMASI KELUAR (LOGOUT)
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalLogoutConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kms-modal-badge-icon" style="background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-box-arrow-right"></i>
                    </span>
                    <h5 class="kms-modal-title">Konfirmasi Keluar</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem KMS?
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-primary" onclick="document.getElementById('logout-form').submit()">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Ya, Keluar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    // Modal Konfirmasi Logout
    function openLogoutModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalLogoutConfirm'));
        modal.show();
    }

    // ═══════════════════════════════════════════════════════════════
    // CHECKLIST & BULK APPROVAL LOGIC
    // ═══════════════════════════════════════════════════════════════
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

        // Highlight baris yang dicentang
        allCheckboxes.forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) {
                row.classList.toggle('row-checked', cb.checked);
            }
        });

        // Update badge jumlah FAQ dipilih
        const count = checkedBoxes.length;
        selectedCountBadge.textContent = `${count} FAQ dipilih`;
        btnBulkApprove.disabled = (count === 0);

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
    // MODAL VIEW, APPROVE, REJECT & BULK APPROVE
    // ═══════════════════════════════════════════════════════════════
    function openViewModal(button) {
        const row = button.closest('.faq-row');
        if (!row) return;

        const id       = row.getAttribute('data-id');
        const code     = row.getAttribute('data-full-code');
        const version  = row.getAttribute('data-full-version');
        const question = row.getAttribute('data-full-question');
        const category = row.getAttribute('data-full-category');
        const answer   = row.getAttribute('data-full-answer');
        const source   = row.getAttribute('data-full-source');
        const de       = row.getAttribute('data-full-de');
        const date     = row.getAttribute('data-full-date');
        const time     = row.getAttribute('data-full-time');

        document.getElementById('viewModalCode').textContent     = code;
        document.getElementById('viewModalVersion').textContent  = version;
        document.getElementById('viewModalCategory').textContent = category;
        document.getElementById('viewModalQuestion').textContent = question;
        document.getElementById('viewModalDe').textContent       = de;
        document.getElementById('viewModalTime').textContent     = `${date}, ${time}`;
        document.getElementById('viewModalAnswer').textContent   = answer;
        document.getElementById('viewModalSource').textContent   = source;

        document.getElementById('modalBtnApprove').onclick = function () {
            bootstrap.Modal.getInstance(document.getElementById('modalViewFaq'))?.hide();
            openSingleApproveModal(id, code, question);
        };
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
        const checkedBoxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
        if (checkedBoxes.length === 0) return;

        const codes = checkedBoxes.map(cb => cb.closest('.faq-row')?.getAttribute('data-full-code')).filter(Boolean);

        document.getElementById('bulkApproveModalTitle').textContent = `Setujui ${checkedBoxes.length} FAQ Terpilih`;
        document.getElementById('bulkApproveCodesList').textContent  = codes.join(', ');
        document.getElementById('btnConfirmBulkText').textContent    = `Setujui ${checkedBoxes.length} FAQ`;

        document.getElementById('btnConfirmBulkSubmit').onclick = function () {
            document.getElementById('bulkApproveForm').submit();
        };

        const modal = new bootstrap.Modal(document.getElementById('modalBulkApprove'));
        modal.show();
    }
</script>
</body>
</html>

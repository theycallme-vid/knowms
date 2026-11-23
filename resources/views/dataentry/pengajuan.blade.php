<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengajuan FAQ - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator & Data Entry CSS (Referensi Antrean Validasi) -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">

    <style>
        div.swal2-popup.kms-pengajuan-view-modal {
            width: 880px !important;
            max-width: 95vw !important;
            height: auto !important;
            max-height: 88vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 1.35rem 1.5rem 1.25rem 1.5rem !important;
            position: relative !important;
            border-radius: 18px !important;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-title:empty,
        div.swal2-popup.kms-pengajuan-view-modal .swal2-actions {
            display: none !important;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-close {
            position: absolute !important;
            top: 0.9rem !important;
            right: 1rem !important;
            width: 34px !important;
            height: 34px !important;
            border-radius: 50% !important;
            background-color: #F8FAFC !important;
            color: #64748B !important;
            font-size: 1.3rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 15 !important;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-html-container {
            flex: 0 1 auto !important;
            max-height: calc(88vh - 2.6rem) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            margin: 0 !important;
            padding: 0.1rem 0.45rem 0.1rem 0.1rem !important;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-html-container::-webkit-scrollbar {
            width: 7px;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-html-container::-webkit-scrollbar-track {
            background: #F4F1EC;
            border-radius: 999px;
        }
        div.swal2-popup.kms-pengajuan-view-modal .swal2-html-container::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 999px;
        }
        .kms-ver-list {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }
        .kms-ver-item {
            background-color: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            overflow: hidden;
        }
        .kms-ver-item.is-latest {
            border-color: #E5B8C0;
        }
        .kms-ver-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            background-color: #FFFFFF;
            border: 0;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .kms-ver-trigger:hover {
            background-color: #FAF8F5;
        }
        .kms-ver-trigger:not(.collapsed) {
            background-color: #FDF9F9;
            border-bottom: 1px solid #F1EDE7;
        }
        .kms-ver-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }
        .kms-ver-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #334155;
            color: #FFFFFF;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.78rem;
            border-radius: 999px;
            white-space: nowrap;
        }
        .kms-ver-item.is-latest .kms-ver-badge {
            background-color: #85172C;
        }
        .kms-ver-time {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.77rem;
            font-weight: 500;
            color: #475569;
        }
        .kms-ver-current-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background-color: #ECFDF5;
            color: #059669;
            border: 1px solid #A7F3D0;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
        }
        .kms-ver-chevron {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: #F4F1EC;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
        }
        .kms-ver-trigger:not(.collapsed) .kms-ver-chevron {
            transform: rotate(180deg);
            background-color: #FCECEF;
            color: #85172C;
        }
        .kms-ver-body {
            padding: 0.85rem 1rem 0.95rem 1rem;
            background-color: #FFFFFF;
        }
        .kms-ver-sublabel {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #85172C;
            margin-bottom: 0.35rem;
        }
        .kms-ver-answer {
            background-color: #FAF9F6;
            border: 1px solid #EDE7E1;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            font-size: 0.84rem;
            line-height: 1.6;
            color: #1E293B;
        }
    </style>
</head>
<body>

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

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('dataentry.daftarFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.pengajuan') }}" class="nav-link-custom active">
                    <i class="bi bi-send-check"></i>
                    <span>Pengajuan FAQ</span>
                </a>
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="{{ route('dataentry.katakunci') }}" class="nav-link-custom">
                    <i class="bi bi-tags"></i>
                    <span>Daftar Kata Kunci</span>
                </a>
                <a href="{{ route('dataentry.sumberpustaka') }}" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider">
            <a href="{{ route('dataentry.pengaturanAkun') }}" class="nav-link-custom">
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
            $tbRole = $tbUser?->getRoleName() ?: 'Data Entry';
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
                    <span class="crumb-current">Pengajuan FAQ</span>
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('dataentry.pengaturanAkun') }}">
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

            <!-- Page Header (Referensi Antrean Validasi) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h1 class="page-title mb-0 me-1">Pengajuan FAQ</h1>
                    </div>
                    <p class="fs-13 text-muted mb-0">
                        Centang FAQ berstatus Draf atau Ditolak untuk diajukan ke Validator secara satuan maupun sekaligus.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-outline-maroon text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i><span>Unggah File</span>
                    </button>
                    <a href="{{ route('dataentry.buatfaq') }}" class="btn-maroon text-nowrap">
                        <i class="bi bi-plus-lg"></i><span>Buat FAQ</span>
                    </a>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Filter Bar -->
                <form method="GET" action="{{ route('dataentry.pengajuan') }}" id="pengajuanFilterForm">
                    <div class="filter-bar">
                        <div class="filter-left-group">
                            <!-- Search -->
                            <div class="filter-search-wrap">
                                <i class="bi bi-search"></i>
                                <input type="text"
                                       name="search"
                                       id="searchInputPengajuan"
                                       value="{{ request('search') }}"
                                       placeholder="Cari kode atau pertanyaan FAQ...">
                            </div>

                            <!-- Filter Status -->
                            <select name="status" class="filter-select" onchange="document.getElementById('pengajuanFilterForm').submit()">
                                @php $stPengReq = request('status', 'all'); @endphp
                                <option value="all"              {{ $stPengReq === 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="Draf"             {{ in_array($stPengReq, ['Draf', 'Draft'], true) ? 'selected' : '' }}>Draf</option>
                                <option value="Ditolak"          {{ in_array($stPengReq, ['Ditolak', 'Rejected'], true) ? 'selected' : '' }}>Ditolak</option>
                                <option value="Menunggu Validasi" {{ in_array($stPengReq, ['Menunggu Validasi', 'Submitted'], true) ? 'selected' : '' }}>Menunggu Validasi</option>
                            </select>

                            <!-- Sort -->
                            <select name="sort" class="filter-select" onchange="document.getElementById('pengajuanFilterForm').submit()">
                                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                            </select>

                            @if(request()->hasAny(['search', 'status', 'sort']) && collect(request()->only(['search', 'status', 'sort']))->filter(fn($v) => $v && $v !== 'all' && $v !== 'terbaru')->isNotEmpty())
                                <a href="{{ route('dataentry.pengajuan') }}" class="btn-filter-reset">
                                    <i class="bi bi-x-lg"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Bulk Submit Form Wrapping Table -->
                <form id="bulkSubmitPengajuanForm" action="{{ route('dataentry.pengajuan.bulkSubmit') }}" method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="faq-table" id="pengajuanFaqTable">
                            <thead>
                                <tr>
                                    <th style="width: 48px;" class="ps-4">
                                        <input type="checkbox" id="selectAllCheckbox" class="faq-checkbox" title="Pilih semua FAQ yang dapat diajukan">
                                    </th>
                                    <th style="width: 130px;">Kode / Versi</th>
                                    <th>Pertanyaan</th>
                                    <th style="width: 170px;">Data Entry</th>
                                    <th style="width: 155px;">Waktu Pengajuan</th>
                                    <th style="width: 145px;">Status</th>
                                    <th style="width: 195px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="faqTableBody">
                                @forelse($faqs as $faq)
                                    @php
                                        $latestVersion = $faq->versions->first();
                                        $status        = $latestVersion?->status ?? 'Draf';
                                        $stLower       = strtolower($status);
                                        $versionCount  = max(1, $faq->versions->count());
                                        $deName        = $faq->dataEntry?->nama ?? 'Data Entry';
                                        $nextVerId     = $faq->generateNextVersionId();
                                        $canSubmit     = in_array($stLower, ['draf', 'draft', 'ditolak', 'rejected'], true);
                                        $hasTags       = $faq->tags && $faq->tags->isNotEmpty();
                                        $hasSource     = $latestVersion && !empty($latestVersion->tDaftarPustakas_id);
                                        $createdAtJkt  = ($latestVersion?->updated_at ?? $faq->created_at)?->timezone('Asia/Jakarta');

                                        $statusClass = match($status) {
                                            'Menunggu Validasi', 'Submitted' => 'badge-submitted',
                                            'Ditolak', 'Rejected'            => 'badge-rejected',
                                            default                          => 'badge-draft',
                                        };
                                        $statusLabel = match($status) {
                                            'Menunggu Validasi', 'Submitted' => 'Menunggu Validasi',
                                            'Ditolak', 'Rejected'            => 'Ditolak',
                                            default                          => 'Draf',
                                        };
                                        $statusIcon = match($status) {
                                            'Menunggu Validasi', 'Submitted' => 'bi-hourglass-split',
                                            'Ditolak', 'Rejected'            => 'bi-x-circle-fill',
                                            default                          => 'bi-pencil',
                                        };
                                    @endphp
                                    <tr class="faq-row"
                                        data-id="{{ $faq->id }}"
                                        data-status="{{ $status }}"
                                        data-can-submit="{{ $canSubmit ? '1' : '0' }}"
                                        data-has-tags="{{ $hasTags ? '1' : '0' }}"
                                        data-has-source="{{ $hasSource ? '1' : '0' }}"
                                        data-code="{{ strtolower($faq->id) }}"
                                        data-question="{{ strtolower($faq->pertanyaan) }}"
                                        data-full-code="{{ $faq->id }}">

                                        <!-- Checkbox -->
                                        <td class="ps-4">
                                            <input type="checkbox"
                                                   name="faq_ids[]"
                                                   value="{{ $faq->id }}"
                                                   data-status="{{ $status }}"
                                                   data-can-submit="{{ $canSubmit ? '1' : '0' }}"
                                                   class="faq-checkbox row-checkbox"
                                                   {{ !$canSubmit ? 'disabled' : '' }}>
                                        </td>

                                        <!-- Kode / Versi -->
                                        <td>
                                            <div class="faq-code">{{ $faq->id }}</div>
                                            <div class="faq-version">{{ \App\Models\FaqVersion::formatVersiLabel($latestVersion?->id) }}</div>
                                        </td>

                                        <!-- Pertanyaan -->
                                        <td>
                                            <div class="faq-question">{{ $faq->pertanyaan }}</div>
                                            @if(in_array($stLower, ['ditolak', 'rejected'], true) && $latestVersion && $latestVersion->catatan)
                                                <div class="mt-1">
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-11 fw-normal">
                                                        <i class="bi bi-exclamation-circle me-1"></i>Catatan Revisi: {{ \Illuminate\Support\Str::limit($latestVersion->catatan, 65) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Data Entry (Cukup Namanya Saja) -->
                                        <td>
                                            <div class="fs-13 fw-bold text-dark lh-sm">{{ $deName }}</div>
                                        </td>

                                        <!-- Waktu Pengajuan -->
                                        <td>
                                            <div class="fs-13 fw-medium text-dark lh-sm">
                                                {{ $createdAtJkt ? $createdAtJkt->translatedFormat('d M Y') : '—' }}
                                            </div>
                                            <div class="fs-11 text-muted mt-0.5">
                                                {{ $createdAtJkt ? $createdAtJkt->format('H.i') . ' WIB' : '' }}
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td>
                                            <span class="badge-status {{ $statusClass }}">
                                                <i class="bi {{ $statusIcon }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <!-- Aksi (Lihat & Ubah) -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                <button type="button" class="btn-action btn-action-view" onclick="viewFaqDetail('{{ $faq->id }}')">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Lihat</span>
                                                </button>

                                                @if(in_array($stLower, ['menunggu validasi', 'submitted'], true))
                                                    <button type="button"
                                                            class="btn-action btn-action-edit action-icon-disabled"
                                                            style="opacity: 0.45; cursor: not-allowed; background-color: #F1F5F9; color: #94A3B8; border-color: #CBD5E1;"
                                                            title="Terkunci — Sedang Menunggu Validasi"
                                                            disabled>
                                                        <i class="bi bi-pencil"></i>
                                                        <span>Ubah</span>
                                                    </button>
                                                @elseif(in_array($stLower, ['ditolak', 'rejected'], true))
                                                    <a href="{{ route('dataentry.faq.edit', $faq->id) }}" class="btn-action btn-action-edit text-decoration-none" title="Revisi FAQ ({{ $latestVersion?->id ?? ($faq->id . '-V1') }})">
                                                        <i class="bi bi-pencil"></i>
                                                        <span>Revisi</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('dataentry.faq.edit', $faq->id) }}" class="btn-action btn-action-edit text-decoration-none" title="Ubah Draf FAQ ({{ $latestVersion?->id ?? ($faq->id . '-V1') }})">
                                                        <i class="bi bi-pencil"></i>
                                                        <span>Ubah</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                                <i class="bi bi-send-check fs-2 opacity-50"></i>
                                                <div class="fw-semibold fs-14 text-dark">Belum ada antrean pengajuan FAQ</div>
                                                <div class="fs-12">Buat FAQ baru atau unggah file untuk mengajukan FAQ ke Validator.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    @if($faqs->hasPages())
                        <div class="pagination-bar">
                            <span>
                                Menampilkan {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }}
                                dari {{ $faqs->total() }} FAQ &middot; {{ $faqs->perPage() }} FAQ per halaman
                            </span>
                            <div class="d-flex gap-1 align-items-center">
                                <a href="{{ $faqs->previousPageUrl() ?? '#' }}"
                                   class="page-btn {{ $faqs->onFirstPage() ? 'disabled' : '' }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>

                                @for($p = 1; $p <= $faqs->lastPage(); $p++)
                                    <a href="{{ $faqs->url($p) }}"
                                       class="page-btn {{ $faqs->currentPage() === $p ? 'active' : '' }}">
                                        {{ $p }}
                                    </a>
                                @endfor

                                <a href="{{ $faqs->nextPageUrl() ?? '#' }}"
                                   class="page-btn {{ !$faqs->hasMorePages() ? 'disabled' : '' }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="pagination-bar">
                            <span>Menampilkan {{ $faqs->count() }} FAQ</span>
                        </div>
                    @endif
                </form>

            </div><!-- /.table-card -->

            <!-- Bottom Bulk Submit Bar (Referensi Antrean Validasi) -->
            <div class="bulk-approval-card">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-selected-count" id="selectedCountBadge">0 FAQ dipilih</span>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="btnBulkSubmitPengajuan" class="btn-bulk-approve" onclick="confirmBulkSubmit()" disabled>
                        <i class="bi bi-send-check"></i>
                        <span>Ajukan Validasi</span>
                    </button>
                </div>
            </div>

        </div><!-- /.content-area -->
    </main>
</div>

<!-- Single Submit Hidden Form -->
<form id="singleSubmitForm" method="POST" class="d-none">
    @csrf
</form>

<!-- Single Delete Hidden Form -->
<form id="singleDeleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="redirect_to" value="pengajuan">
</form>

<!-- Modal Unggah File FAQ -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="pengajuanExcelForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Unggah File FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="document.getElementById('pengajuanExcelForm').submit()">
                        </label>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Contoh Template Unggah File:</span>
                            <a href="{{ route('dataentry.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Unggah & Tinjau</button>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('d-none');
    }

    // Checklist Logic
    const selectAllCheckbox      = document.getElementById('selectAllCheckbox');
    const selectedCountBadge     = document.getElementById('selectedCountBadge');
    const selectedHelperText     = document.getElementById('selectedHelperText');
    const btnBulkSubmitPengajuan = document.getElementById('btnBulkSubmitPengajuan');

    function getSubmittableCheckboxes() {
        return Array.from(document.querySelectorAll('.row-checkbox'))
            .filter(cb => !cb.disabled && cb.closest('.faq-row')?.style.display !== 'none');
    }

    function updateChecklistState() {
        const allCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
        const checkedBoxes  = allCheckboxes.filter(cb => cb.checked && !cb.disabled);
        const submittable   = getSubmittableCheckboxes();
        const subChecked    = submittable.filter(cb => cb.checked);

        allCheckboxes.forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) row.classList.toggle('row-checked', cb.checked);
        });

        const count = checkedBoxes.length;
        if (selectedCountBadge) selectedCountBadge.textContent = `${count} FAQ dipilih`;
        if (btnBulkSubmitPengajuan) btnBulkSubmitPengajuan.disabled = (count === 0);

        if (selectedHelperText) {
            selectedHelperText.textContent = count === 0
                ? 'Centang FAQ berstatus Draft atau Ditolak untuk diajukan ke Validator.'
                : `${count} FAQ siap diajukan ke Validator.`;
        }

        if (!selectAllCheckbox) return;
        if (submittable.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (subChecked.length === submittable.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (subChecked.length > 0) {
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
            getSubmittableCheckboxes().forEach(cb => {
                cb.checked = isChecked;
            });
            updateChecklistState();
        });
    }

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateChecklistState);
    });

    updateChecklistState();

    // Quick Search
    document.getElementById('searchInputPengajuan')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.faq-row').forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const question = row.getAttribute('data-question') || '';
            row.style.display = (!q || code.includes(q) || question.includes(q)) ? '' : 'none';
        });
        updateChecklistState();
    });

    function confirmSingleSubmit(faqId, questionText) {
        const row = document.querySelector(`.faq-row[data-id="${faqId}"]`);
        if (row) {
            const hasTags = row.getAttribute('data-has-tags') === '1';
            if (!hasTags) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    html: `Peringatan: FAQ <strong>${faqId}</strong> belum memiliki <strong>Kata Kunci</strong>.<br><br>Silakan klik tombol <strong>Ubah</strong> untuk melengkapinya terlebih dahulu.`,
                    confirmButtonColor: '#85172C'
                });
                return;
            }
        }

        Swal.fire({
            title: `Ajukan ${faqId} ke Validator?`,
            text: questionText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Ajukan Validasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(res => {
            if (res.isConfirmed) {
                const form = document.getElementById('singleSubmitForm');
                form.action = `{{ url('/data-entry/pengajuan-faq') }}/${encodeURIComponent(faqId)}/submit`;
                form.submit();
            }
        });
    }

    function confirmDeleteFaq(faqId, questionText) {
        Swal.fire({
            title: `Hapus ${faqId}?`,
            text: questionText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(res => {
            if (res.isConfirmed) {
                const form = document.getElementById('singleDeleteForm');
                form.action = `{{ url('/data-entry/faq') }}/${encodeURIComponent(faqId)}`;
                form.submit();
            }
        });
    }

    function confirmBulkSubmit() {
        const checked = Array.from(document.querySelectorAll('.row-checkbox:checked')).filter(cb => !cb.disabled);
        if (checked.length === 0) return;

        const incompleteCodes = [];
        checked.forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) {
                const hasTags = row.getAttribute('data-has-tags') === '1';
                if (!hasTags) {
                    incompleteCodes.push(cb.value);
                }
            }
        });

        if (incompleteCodes.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Kata Kunci Belum Lengkap',
                html: `Peringatan: FAQ (<strong>${incompleteCodes.join(', ')}</strong>) belum memiliki <strong>Kata Kunci</strong>.<br><br>Silakan lengkapi melalui tombol <strong>Ubah</strong> terlebih dahulu sebelum diajukan.`,
                confirmButtonColor: '#85172C'
            });
            return;
        }

        const codes = checked.map(cb => cb.value).join(', ');
        Swal.fire({
            title: `Ajukan ${checked.length} FAQ Terpilih?`,
            html: `FAQ berikut akan diajukan ke Validator:<br><strong class="text-dark">${codes}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#64748B',
            confirmButtonText: `Ajukan (${checked.length})`,
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(res => {
            if (res.isConfirmed) {
                document.getElementById('bulkSubmitPengajuanForm').submit();
            }
        });
    }

    function formatVersiCode(verId) {
        const m = String(verId || '').match(/-V(\d+)$/i);
        return m ? ('Versi ' + parseInt(m[1], 10)) : 'Versi 1';
    }

    // Lihat Detail FAQ
    function viewFaqDetail(faqId) {
        fetch(`{{ url('/data-entry/faq') }}/${faqId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const latest = d.latest_version || {};
                const tagsHtml = (d.tags && d.tags.length > 0)
                    ? `<div style="margin-bottom: 1.1rem;"><div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Kata Kunci</div><div class="d-flex flex-wrap gap-2">${d.tags.map(t => `<span class="badge rounded-pill" style="background-color:#F8F5F1;color:#85172C;border:1px solid #EDE7E1;font-size:0.76rem;padding:0.35rem 0.8rem;font-weight:600;">${t}</span>`).join('')}</div></div>`
                    : '';

                let catatanHtml = '';
                if (latest.catatan && !String(latest.catatan).startsWith('[PUBLISH_SCHEDULE|')) {
                    catatanHtml = `
                        <div class="rounded-3" style="background-color: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; font-size: 0.85rem; padding: 0.85rem 1rem; margin-bottom: 1.1rem;">
                            <div class="fw-bold" style="margin-bottom: 0.25rem;"><i class="bi bi-exclamation-circle-fill me-1"></i>Catatan Validator:</div>
                            <div>${latest.catatan}</div>
                        </div>
                    `;
                }

                const rawSt = (latest.status || 'Draf').toLowerCase();
                let statusBadge = `<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.75rem;padding:0.4rem 0.85rem;">Draf</span>`;
                if (rawSt === 'terbit' || rawSt === 'active') statusBadge = '<span class="badge rounded-pill" style="background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#FFFFFF;box-shadow:0 2px 6px rgba(5,150,105,0.25);font-size:0.75rem;padding:0.4rem 0.9rem;font-weight:700;"><i class="bi bi-lightning-fill me-1"></i>Terbit</span>';
                else if (rawSt === 'diterima' || rawSt === 'approved') statusBadge = '<span class="badge rounded-pill" style="background-color:#EDE9FE;color:#6D28D9;border:1px solid #DDD6FE;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-check-circle-fill me-1"></i>Diterima</span>';
                else if (rawSt === 'menunggu validasi' || rawSt === 'submitted') statusBadge = '<span class="badge rounded-pill" style="background-color:#FFFBEB;color:#D97706;border:1px solid #FDE68A;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi</span>';
                else if (rawSt === 'ditolak' || rawSt === 'rejected') statusBadge = '<span class="badge rounded-pill" style="background-color:#FEF2F2;color:#DC2626;border:1px solid #FECACA;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>';

                const latestVerObj = (d.versions || []).find(v => v.id === latest.id) || (d.versions || [])[0] || {};
                const dp = latestVerObj.daftar_pustaka || null;
                let sourceHtml = '';
                if (dp) {
                    const actionLink = dp.is_file && dp.view_url
                        ? `<a href="${dp.view_url}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.75rem;"><i class="bi bi-eye me-1"></i>Lihat Berkas</a>`
                        : (dp.link && /^https?:\/\//i.test(dp.link) ? `<a href="${dp.link}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.75rem;"><i class="bi bi-eye me-1"></i>Lihat Tautan</a>` : '');
                    sourceHtml = `
                        <div style="margin-bottom: 1.1rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Sumber Pustaka</div>
                            <div class="rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background-color: #FBF9F7; border: 1px solid #EDE7E1; padding: 0.75rem 1rem;">
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.85rem;">${dp.judul || '-'}</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">${dp.is_file ? 'Berkas Dokumen' : 'Tautan Rujukan'}</div>
                                </div>
                                ${actionLink}
                            </div>
                        </div>
                    `;
                }

                let versionsHistoryHtml = '';
                if (d.versions && d.versions.length > 0) {
                    const getVerNum = (ver) => {
                        const m = String(ver?.id || ver?.versi_label || '').match(/(\d+)\s*$/);
                        return m ? parseInt(m[1], 10) : 1;
                    };
                    const sortedVersions = [...d.versions].sort((a, b) => getVerNum(b) - getVerNum(a));

                    versionsHistoryHtml = `
                        <div style="margin-top: 0.25rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.5rem;">Riwayat Versi Jawaban</div>
                            <div class="kms-ver-list" id="kmsPengajuanVersionsAccordion">
                                ${sortedVersions.map((v, idx) => {
                                    const versiLabel = v.versi_label || formatVersiCode(v.id);
                                    const isCurrent = (latest && latest.id && v.id === latest.id) || (!latest?.id && idx === 0);
                                    const collapseId = `kmsPengajuanVerCollapse_${idx}`;
                                    return `
                                        <div class="kms-ver-item ${isCurrent ? 'is-latest' : ''}">
                                            <button type="button"
                                                    class="kms-ver-trigger collapsed"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#${collapseId}"
                                                    aria-expanded="false"
                                                    aria-controls="${collapseId}">
                                                <div class="kms-ver-left">
                                                    <span class="kms-ver-badge">${versiLabel}</span>
                                                    <span class="kms-ver-time">
                                                        <i class="bi bi-clock"></i>
                                                        <span>${v.updated_at || '-'}</span>
                                                    </span>
                                                    ${isCurrent ? `<span class="kms-ver-current-pill"><i class="bi bi-check-circle-fill"></i><span>Versi Saat Ini</span></span>` : ''}
                                                </div>
                                                <span class="kms-ver-chevron" aria-hidden="true">
                                                    <i class="bi bi-chevron-down"></i>
                                                </span>
                                            </button>
                                            <div id="${collapseId}" class="collapse" data-bs-parent="#kmsPengajuanVersionsAccordion">
                                                <div class="kms-ver-body">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                        <div class="kms-ver-sublabel mb-0">Jawaban</div>
                                                        ${v.status ? `<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.7rem;padding:0.25rem 0.65rem;">Status: ${v.status}</span>` : ''}
                                                    </div>
                                                    <div class="kms-ver-answer">${v.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}</div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                window._pengajuanModalFaq = {
                    id: d.id,
                    pertanyaan: d.pertanyaan || ''
                };

                const canModalAction = ['draf', 'draft', 'ditolak', 'rejected'].includes(rawSt);
                const editUrl = `{{ url('/data-entry/faq') }}/${encodeURIComponent(d.id)}/edit`;
                const modalFooterActionsHtml = canModalAction
                    ? `
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mt-3" style="border-top: 1px solid #EDE7E1;">
                            <button type="button" class="btn-action-pill btn-pill-approved" onclick="handleModalAjukanFaq()">
                                <i class="bi bi-send-check"></i>
                                <span>Ajukan</span>
                            </button>
                            <a href="${editUrl}" class="btn-action btn-action-edit text-decoration-none">
                                <i class="bi bi-pencil"></i>
                                <span>Revisi</span>
                            </a>
                            <button type="button" class="btn-action-pill btn-pill-rejected" onclick="handleModalHapusFaq()">
                                <i class="bi bi-trash3"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    `
                    : `
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mt-3" style="border-top: 1px solid #EDE7E1;">
                            <button type="button" class="btn-action-pill" style="opacity: 0.45; cursor: not-allowed; background-color: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1;" title="Terkunci — Sedang Menunggu Validasi" disabled>
                                <i class="bi bi-send-check"></i>
                                <span>Ajukan</span>
                            </button>
                            <button type="button" class="btn-action btn-action-edit action-icon-disabled" style="opacity: 0.45; cursor: not-allowed; background-color: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1;" title="Terkunci — Sedang Menunggu Validasi" disabled>
                                <i class="bi bi-pencil"></i>
                                <span>Revisi</span>
                            </button>
                            <button type="button" class="btn-action-pill" style="opacity: 0.45; cursor: not-allowed; background-color: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1;" title="Tidak dapat dihapus — Sedang Menunggu Validasi" disabled>
                                <i class="bi bi-trash3"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    `;

                Swal.fire({
                    title: '',
                    html: `
                        <div class="text-start" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #1E293B;">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-bottom: 1px solid #EDE7E1; padding: 0 2.75rem 0.85rem 0; margin-bottom: 1rem;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.78rem; padding: 0.42rem 0.9rem;">${d.id}</span>
                                    ${statusBadge}
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="bi bi-person me-1"></i><strong class="text-dark">${d.data_entry || '-'}</strong></span>
                                    <span>&middot;</span>
                                    <span><i class="bi bi-clock me-1"></i>${d.created_at || '-'}</span>
                                </div>
                            </div>

                            ${catatanHtml}

                            <div style="margin-bottom: 0.95rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.4rem;">Pertanyaan</div>
                                <div class="rounded-3 fw-semibold text-dark" style="background-color: #FFFDFB; border: 1px solid #EDE7E1; font-size: 0.93rem; line-height: 1.55; padding: 0.8rem 1rem;">
                                    ${d.pertanyaan || '-'}
                                </div>
                            </div>

                            <div style="margin-bottom: 0.95rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.4rem;">Jawaban (${formatVersiCode(latest.id || (d.id + '-V1'))})</div>
                                <div class="rounded-3 bg-white text-dark" style="border: 1px solid #EDE7E1; font-size: 0.88rem; max-height: 165px; overflow-y: auto; line-height: 1.62; padding: 0.85rem 1rem;">
                                    ${latest.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}
                                </div>
                            </div>

                            ${tagsHtml}

                            ${sourceHtml}

                            ${versionsHistoryHtml}

                            ${modalFooterActionsHtml}
                        </div>
                    `,
                    width: '880px',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0 kms-pengajuan-view-modal'
                    }
                });
            }
        })
        .catch(err => console.error(err));
    }

    function handleModalAjukanFaq() {
        const info = window._pengajuanModalFaq;
        if (!info) return;
        Swal.close();
        confirmSingleSubmit(info.id, info.pertanyaan);
    }

    function handleModalHapusFaq() {
        const info = window._pengajuanModalFaq;
        if (!info) return;
        Swal.close();
        confirmDeleteFaq(info.id, info.pertanyaan);
    }
</script>
</body>
</html>


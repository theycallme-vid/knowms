<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar FAQ - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator & Data Entry Shared Styles -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
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
                <a href="{{ route('dataentry.daftarFAQ') }}" class="nav-link-custom active">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.pengajuan') }}" class="nav-link-custom">
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

    <!-- Overlay mobile -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
         id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN AREA
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
                    <span class="crumb-current">Daftar FAQ</span>
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

        <!-- Content -->
        <div class="content-area">

            <!-- Page Header (Disamakan dengan Tampilan Validator) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Daftar FAQ</h1>
                    <p class="fs-13 text-muted mb-0">
                        Kelola FAQ Anda, lengkapi sumber resmi, lalu ajukan untuk divalidasi.
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

            <!-- 5 Kotak Statistik Sejajar (Sama dengan Tampilan Daftar FAQ Validator) -->
            <div class="stat-grid-5 mb-4">
                {{-- Total FAQ --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total FAQ</div>
                        <div class="stat-value">{{ $totalFaq ?? $total ?? 0 }}</div>
                    </div>
                </div>

                {{-- Terbit --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Terbit</div>
                        <div class="stat-value">{{ $totalAktif ?? $publishedCount ?? 0 }}</div>
                    </div>
                </div>

                {{-- Diterima --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EDE9FE; color:#7C3AED;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Diterima</div>
                        <div class="stat-value">{{ $totalApproved ?? $approvedCount ?? 0 }}</div>
                    </div>
                </div>

                {{-- Menunggu Validasi --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FFFBEB; color:#D97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Menunggu Validasi</div>
                        <div class="stat-value">{{ $totalSubmitted ?? $submittedCount ?? 0 }}</div>
                    </div>
                </div>

                {{-- Ditolak --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FEF2F2; color:#DC2626;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-value">{{ $totalRejected ?? $rejectedCount ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Card Header Title -->
                <div class="table-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="fw-bold fs-15 text-dark mb-0">Daftar Seluruh FAQ</h2>
                    </div>
                </div>

                <!-- ── Filter Bar (Search + Kata Kunci + Status + Sort, Auto-Submit) ── -->
                @include('partials.daftar-faq-filter-bar', [
                    'actionRoute' => route('dataentry.daftarFAQ'),
                    'kataKunciOptions' => $kataKunciOptions ?? collect(),
                ])

                <!-- ── Table (Sama dengan Tampilan Validator) ── -->
                <div class="table-responsive">
                    <table class="faq-table">
                        <thead>
                            <tr>
                                <th style="width:115px;">Kode / Versi</th>
                                <th>Pertanyaan</th>
                                <th style="width:155px;">Data Entry</th>
                                <th style="width:135px;">Waktu Pengajuan</th>
                                <th style="width:215px;">Status</th>
                                <th style="width:195px;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                @php
                                    $rawLatestVersion = $faq->versions->first();
                                    $terbitFaqVersion = $faq->versions->first(fn ($v) => strtolower((string) $v->status) === 'terbit');
                                    $latestVersion = $terbitFaqVersion ?? $rawLatestVersion;
                                    $status        = $latestVersion?->status ?? 'Draf';
                                    $stLower       = strtolower($status);
                                    $versionCount  = max(1, $faq->versions->count());
                                    $de            = $faq->dataEntry;
                                    $nextVerId     = $faq->generateNextVersionId();
                                    $statusClass   = match($status) {
                                        'Diterima'          => 'badge-approved',
                                        'Terbit'            => 'badge-active',
                                        'Menunggu Validasi' => 'badge-submitted',
                                        'Ditolak'           => 'badge-rejected',
                                        default             => 'badge-draft',
                                    };
                                    $statusLabel   = match($status) {
                                        'Terbit'            => 'Terbit',
                                        'Diterima'          => 'Diterima',
                                        'Menunggu Validasi' => 'Menunggu Validasi',
                                        'Ditolak'           => 'Ditolak',
                                        default             => $status,
                                    };
                                    $statusIcon    = match($status) {
                                        'Diterima'          => 'bi-check-circle-fill',
                                        'Terbit'            => 'bi-lightning-fill',
                                        'Menunggu Validasi' => 'bi-hourglass-split',
                                        'Ditolak'           => 'bi-x-circle-fill',
                                        default             => 'bi-pencil',
                                    };
                                    $createdAtJkt  = $faq->created_at ? $faq->created_at->timezone('Asia/Jakarta') : null;
                                @endphp
                                <tr>
                                    <!-- Kode / Versi -->
                                    <td>
                                        <div class="faq-code">{{ $faq->id }}</div>
                                        <div class="faq-version">Versi {{ $versionCount }}</div>
                                    </td>

                                    <!-- Pertanyaan + Tags -->
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <div class="faq-question">{{ Str::limit($faq->pertanyaan, 85) }}</div>
                                            @if($stLower === 'rejected' && $latestVersion && $latestVersion->catatan)
                                                <span class="revisi-note-icon ms-1"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="right"
                                                      data-bs-html="true"
                                                      title="<strong>Catatan Validator:</strong><br>{{ e($latestVersion->catatan) }}">
                                                    <i class="bi bi-chat-left-text-fill" style="color: #DC2626; font-size: 0.85rem;"></i>
                                                </span>
                                            @endif
                                        </div>
                                        @if($faq->tags->isNotEmpty())
                                            <div class="faq-tags">
                                                @foreach($faq->tags->take(3) as $tag)
                                                    <span class="tag-pill">{{ $tag->nama }}</span>
                                                @endforeach
                                                @if($faq->tags->count() > 3)
                                                    <span class="tag-pill">+{{ $faq->tags->count() - 3 }}</span>
                                                @endif
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="faq-tags">
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Data Entry (Cukup Namanya Saja) -->
                                    <td>
                                        <div class="fs-13 fw-semibold text-dark">{{ $de?->nama ?? '—' }}</div>
                                    </td>

                                    <!-- Waktu Pengajuan (Timezone Jakarta GMT+7) -->
                                    <td>
                                        <div class="fs-13 text-dark">
                                            {{ $createdAtJkt ? $createdAtJkt->translatedFormat('d M Y') : '—' }}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            {{ $createdAtJkt ? $createdAtJkt->format('H.i') . ' WIB' : '' }}
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        @php
                                            $schedInfoDaftar = ($status === 'Diterima' && $latestVersion) ? $latestVersion->getPublishScheduleInfo() : null;
                                        @endphp
                                        @if($status === 'Diterima' && !empty($schedInfoDaftar['is_scheduled']))
                                            <div class="status-cell-stack">
                                                <span class="badge-status badge-scheduled-wait">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Terjadwal Publikasi
                                                </span>
                                                @if(!empty($schedInfoDaftar['scheduled_at']))
                                                    <span class="status-subnote">
                                                        {{ \Carbon\Carbon::parse($schedInfoDaftar['scheduled_at'])->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }} WIB
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge-status {{ $statusClass }}">
                                                <i class="bi {{ $statusIcon }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi (Lihat & Ubah) -->
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                            <button type="button" class="btn-action btn-action-view" onclick="viewFaqDetail('{{ $faq->id }}')">
                                                <i class="bi bi-eye"></i> Lihat
                                            </button>

                                            @if(in_array($stLower, ['menunggu validasi', 'diterima', 'submitted', 'approved'], true))
                                                <button type="button"
                                                        class="btn-action btn-action-edit action-icon-disabled"
                                                        style="opacity: 0.45; cursor: not-allowed; background-color: #F1F5F9; color: #94A3B8; border-color: #CBD5E1;"
                                                        title="{{ in_array($stLower, ['diterima', 'approved'], true) ? 'Terkunci — Tidak dapat diubah saat status Diterima sebelum Terbit' : 'Terkunci — Tidak dapat diubah saat status Menunggu Validasi' }}"
                                                        disabled>
                                                    <i class="bi bi-pencil"></i> Ubah
                                                </button>
                                            @else
                                                <a href="{{ route('dataentry.faq.edit', $faq->id) }}" class="btn-action btn-action-edit text-decoration-none" title="Ubah / Buat Versi Baru ({{ $nextVerId }})">
                                                    <i class="bi bi-pencil"></i> Ubah
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                            <i class="bi bi-journal-x fs-1 opacity-30"></i>
                                            <div class="fw-semibold fs-14">Belum ada FAQ ditemukan</div>
                                            <div class="fs-12">
                                                @if(request('search') || (request('fakultas') && request('fakultas') !== 'Semua'))
                                                    Coba ubah filter atau kata kunci pencarian.
                                                @else
                                                    Belum ada FAQ yang ditambahkan.
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ── Pagination ── -->
                @if(method_exists($faqs, 'hasPages') && $faqs->hasPages())
                    <div class="pagination-bar">
                        <span>
                            Menampilkan {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }}
                            dari {{ $faqs->total() }} FAQ &middot; {{ $faqs->perPage() }} per halaman
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

            </div><!-- /.table-card -->

        </div><!-- /.content-area -->
    </main>
</div>

<!-- Modal Unggah File FAQ -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="dashExcelForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16" id="uploadModalLabel">Unggah File FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="submitDashExcel(this)">
                        </label>
                        <div id="dashFileNameText" class="fs-12 text-success fw-semibold mt-2" style="display:none;"></div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Contoh Template Unggah File:</span>
                            <a href="{{ route('dataentry.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong></div>
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

<!-- Bootstrap JS -->
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    function submitDashExcel(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const textEl = document.getElementById('dashFileNameText');
            if (textEl) {
                textEl.textContent = 'File: ' + fileName;
                textEl.style.display = 'block';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mengunggah File...',
                    text: `Memproses ${fileName} untuk pratinjau data FAQ.`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            document.getElementById('dashExcelForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });

    function formatVersiCode(verId) {
        const m = String(verId || '').match(/-V(\d+)$/i);
        return m ? ('Versi ' + parseInt(m[1], 10)) : 'Versi 1';
    }

    // Helper: Lihat Detail FAQ
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
                    versionsHistoryHtml = `
                        <div style="margin-top: 0.25rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.5rem;">Riwayat Versi Jawaban</div>
                            <div class="rounded-3 overflow-auto" style="border: 1px solid #EDE7E1; max-height: 210px;">
                                <table class="table table-sm mb-0 align-middle" style="font-size: 0.83rem;">
                                    <thead style="background-color: #FBF9F7; border-bottom: 1px solid #EDE7E1; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="text-secondary fw-semibold" style="width: 115px; font-size: 0.74rem; padding: 0.7rem 1rem;">Versi</th>
                                            <th class="text-secondary fw-semibold" style="font-size: 0.74rem; padding: 0.7rem 1rem;">Jawaban Versi Tersebut</th>
                                            <th class="text-secondary fw-semibold" style="width: 155px; font-size: 0.74rem; padding: 0.7rem 1rem;">Diperbarui</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${d.versions.map(v => {
                                            const versiLabel = v.versi_label || formatVersiCode(v.id);
                                            return `
                                                <tr style="border-bottom: 1px solid #F3EFEA;">
                                                    <td class="fw-bold text-nowrap" style="color: #85172C; padding: 0.8rem 1rem;">${versiLabel}</td>
                                                    <td class="text-dark" style="line-height: 1.55; word-break: break-word; padding: 0.8rem 1rem;">${v.jawaban || '-'}</td>
                                                    <td class="text-secondary text-nowrap" style="font-size: 0.77rem; padding: 0.8rem 1rem;">${v.updated_at || '-'}</td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: '',
                    html: `
                        <div class="text-start" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #1E293B;">
                            <!-- Header Banner Tema Website -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-bottom: 1px solid #EDE7E1; padding: 0 3rem 0.95rem 0; margin-bottom: 1.15rem;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.78rem; padding: 0.42rem 0.9rem;">${d.id}</span>
                                    ${statusBadge}
                                    <span class="badge rounded-pill" style="background-color: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; font-size: 0.74rem; padding: 0.38rem 0.75rem;">
                                        <i class="bi bi-eye me-1"></i>${d.views_count ?? 0} dilihat
                                        &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>${d.likes_count ?? 0}
                                        &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>${d.dislikes_count ?? 0}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="bi bi-person me-1"></i><strong class="text-dark">${d.data_entry || '-'}</strong></span>
                                    <span>&middot;</span>
                                    <span><i class="bi bi-clock me-1"></i>${d.created_at || '-'}</span>
                                </div>
                            </div>

                            ${catatanHtml}

                            <!-- Pertanyaan -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Pertanyaan</div>
                                <div class="rounded-3 fw-semibold text-dark" style="background-color: #FFFDFB; border: 1px solid #EDE7E1; font-size: 0.94rem; line-height: 1.55; padding: 0.85rem 1.05rem;">
                                    ${d.pertanyaan || '-'}
                                </div>
                            </div>

                            <!-- Jawaban -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Jawaban (${formatVersiCode(latest.id || (d.id + '-V1'))})</div>
                                <div class="rounded-3 bg-white text-dark" style="border: 1px solid #EDE7E1; font-size: 0.89rem; max-height: 165px; overflow-y: auto; line-height: 1.65; padding: 0.9rem 1.05rem;">
                                    ${latest.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}
                                </div>
                            </div>

                            ${tagsHtml}

                            ${sourceHtml}

                            ${versionsHistoryHtml}
                        </div>
                    `,
                    width: '820px',
                    showCloseButton: true,
                    confirmButtonColor: '#85172C',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0 kms-view-modal'
                    }
                });
            }
        })
        .catch(err => console.error(err));
    }

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3500
        });
    });
    @endif

    @if(session('error'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'Perhatian',
            text: "{{ session('error') }}",
            confirmButtonColor: '#85172C'
        });
    });
    @endif
</script>
</body>
</html>

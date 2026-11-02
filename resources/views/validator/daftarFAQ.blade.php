<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS — Daftar FAQ</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator CSS -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
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

            <!-- Role Pill: dinamis sesuai role user yang login -->
            @php
                $authUser = auth()->user();
                if ($authUser && !$authUser->relationLoaded('role')) {
                    $authUser->load('role');
                }
                $roleName = $authUser ? $authUser->getRoleName() : 'Validator';
            @endphp
            <div><span class="role-pill">Ruang {{ $roleName ?: 'Validator' }}</span></div>

            <!-- Navigation: tampilkan nav sesuai role -->
            <nav class="nav flex-column">
                @if($roleName === 'Super Admin')
                    {{-- Super Admin: pakai nav superadmin --}}
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link-custom">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom active">
                        <i class="bi bi-journal-text"></i>
                        <span>Daftar FAQ</span>
                    </a>
                    <a href="{{ route('superadmin.pengguna-akses') }}" class="nav-link-custom">
                        <i class="bi bi-people"></i>
                        <span>Pengguna &amp; Akses</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-diagram-3"></i>
                        <span>Kategori</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-bar-chart"></i>
                        <span>Laporan Pencarian</span>
                    </a>
                @else
                    {{-- Validator --}}
                    <a href="{{ route('validator.dashboard') }}" class="nav-link-custom">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom active">
                        <i class="bi bi-journal-text"></i>
                        <span>Daftar FAQ</span>
                    </a>
                    <a href="{{ route('validator.validasiFAQ') }}" class="nav-link-custom">
                        <i class="bi bi-list-check"></i>
                        <span>Validasi FAQ</span>
                    </a>
                    <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                        <i class="bi bi-people"></i>
                        <span>Pengguna &amp; Akses</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Bottom -->
        <div>
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom text-secondary" style="cursor:pointer;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
         id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════ -->
    <main class="flex-grow-1 min-vw-0">

        <!-- Top Navbar -->
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3"
                        onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center text-muted fs-13">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="mx-2 text-secondary">/</span>
                    <span>{{ $roleName ?: 'Validator' }}</span>
                    <span class="mx-2 text-secondary">/</span>
                    <strong class="text-dark">Daftar FAQ</strong>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($authUser->nama ?? 'V', 0, 2)) }}
                        </div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? '-' }}</div>
                            <div class="fs-11 text-muted">{{ $roleName ?: 'Validator' }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? '-' }}</div>
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

        <!-- Content -->
        <div class="content-area">

            <!-- Page Header (Ukuran & Warna Seragam) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                    <h1 class="page-title mb-1">Daftar FAQ</h1>
                    <p class="fs-13 text-muted mb-0">
                        Tinjau pertanyaan, jawaban, dan rujukan sumber sebelum menyetujui FAQ.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-secondary bg-white rounded-pill px-3 py-2
                                       d-inline-flex align-items-center gap-2 shadow-sm text-dark fs-13 fw-semibold"
                       style="border-color:#E2E8F0;">
                        <i class="bi bi-download"></i>
                        <span class="d-none d-sm-inline">Ekspor</span>
                    </a>
                </div>
            </div>

            <!-- 5 Kotak Statistik Sejajar (Ringkas: Hanya Judul & Jumlah) -->
            <div class="stat-grid-5 mb-4">
                {{-- Total FAQ --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total FAQ</div>
                        <div class="stat-value">{{ $totalFaq }}</div>
                    </div>
                </div>

                {{-- Aktif --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Aktif</div>
                        <div class="stat-value">{{ $totalAktif }}</div>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EDE9FE; color:#7C3AED;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Approved</div>
                        <div class="stat-value">{{ $totalApproved }}</div>
                    </div>
                </div>

                {{-- Menunggu Validasi --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FFFBEB; color:#D97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Menunggu Validasi</div>
                        <div class="stat-value">{{ $totalSubmitted }}</div>
                    </div>
                </div>

                {{-- Nonaktif --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FEF2F2; color:#DC2626;">
                        <i class="bi bi-slash-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Nonaktif</div>
                        <div class="stat-value">{{ $totalInaktif }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Card Header Title (Tanpa subjudul 'Semua Fakultas') -->
                <div class="table-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="fw-bold fs-15 text-dark mb-0">Daftar Seluruh FAQ</h2>
                        @if($faqs->total() > 0)
                            <span class="badge rounded-pill fw-medium fs-12 px-3 py-1"
                                  style="background-color:#DBEAFE; color:#1D4ED8;">
                                {{ $faqs->total() }} FAQ ditemukan
                            </span>
                        @endif
                    </div>
                </div>

                <!-- ── Filter Bar (Satu Baris: Search + Kategori Fakultas + Status + Sort) ── -->
                @php $aktivFakultas = request('fakultas', 'Semua'); @endphp
                <form method="GET" action="{{ route('validator.daftarFAQ') }}" id="filterForm">
                    <div class="filter-bar">
                        <div class="filter-left-group">
                            <!-- Search -->
                            <div class="filter-search-wrap">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari kode atau pertanyaan FAQ...">
                            </div>

                            <!-- Filter Kategori / Fakultas (Satu Baris dengan Search) -->
                            <select name="fakultas" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="Semua" {{ $aktivFakultas === 'Semua' ? 'selected' : '' }}>Semua Kategori</option>
                                @foreach($fakultasOptions as $fak)
                                    <option value="{{ $fak }}" {{ $aktivFakultas === $fak ? 'selected' : '' }}>{{ $fak }}</option>
                                @endforeach
                            </select>

                            <!-- Filter Status -->
                            <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="Semua" {{ request('status','Semua') === 'Semua' ? 'selected' : '' }}>Semua Status</option>
                                <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Menunggu Validasi</option>
                                <option value="Approved"  {{ request('status') === 'Approved'  ? 'selected' : '' }}>Approved</option>
                                <option value="Active"    {{ request('status') === 'Active'    ? 'selected' : '' }}>Aktif</option>
                                <option value="Rejected"  {{ request('status') === 'Rejected'  ? 'selected' : '' }}>Rejected</option>
                                <option value="InActive"  {{ request('status') === 'InActive'  ? 'selected' : '' }}>Nonaktif</option>
                            </select>

                            <!-- Sort -->
                            <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="terbaru" {{ request('sort','terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                                <option value="az"      {{ request('sort') === 'az'      ? 'selected' : '' }}>A &rarr; Z</option>
                            </select>

                            <button type="submit" class="btn-filter-submit">
                                <i class="bi bi-funnel"></i> Filter
                            </button>

                            @if(request()->hasAny(['search', 'fakultas', 'status', 'sort']) && collect(request()->only(['search','fakultas','status','sort']))->filter(fn($v) => $v && $v !== 'Semua' && $v !== 'terbaru')->isNotEmpty())
                                <a href="{{ route('validator.daftarFAQ') }}" class="btn-filter-reset">
                                    <i class="bi bi-x-lg"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- ── Table ── -->
                <div class="table-responsive">
                    <table class="faq-table">
                        <thead>
                            <tr>
                                <th style="width:120px;">Kode / Versi</th>
                                <th>Pertanyaan</th>
                                <th style="width:170px;">Data Entry</th>
                                <th style="width:140px;">Waktu Pengajuan</th>
                                <th style="width:140px;">Status</th>
                                <th style="width:200px;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                @php
                                    $latestVersion = $faq->versions->first();
                                    $status        = $latestVersion?->status ?? 'Draft';
                                    $versionCount  = max(1, $faq->versions->count());
                                    $de            = $faq->dataEntry;
                                    $initials      = strtoupper(substr($de?->nama ?? '?', 0, 2));
                                    $statusClass   = match($status) {
                                        'Approved'  => 'badge-approved',
                                        'Active'    => 'badge-active',
                                        'Submitted' => 'badge-submitted',
                                        'Rejected'  => 'badge-rejected',
                                        'InActive'  => 'badge-inactive',
                                        default     => 'badge-draft',
                                    };
                                    $statusLabel   = match($status) {
                                        'Submitted' => 'Menunggu Validasi',
                                        'InActive'  => 'Nonaktif',
                                        default     => $status,
                                    };
                                    $statusIcon    = match($status) {
                                        'Approved'  => 'bi-check-circle-fill',
                                        'Active'    => 'bi-lightning-fill',
                                        'Submitted' => 'bi-hourglass-split',
                                        'Rejected'  => 'bi-x-circle-fill',
                                        'InActive'  => 'bi-dash-circle',
                                        default     => 'bi-pencil',
                                    };
                                @endphp
                                <tr>
                                    <!-- Kode / Versi -->
                                    <td>
                                        <div class="faq-code">{{ $faq->id }}</div>
                                        <div class="faq-version">Versi {{ $versionCount }}</div>
                                    </td>

                                    <!-- Pertanyaan + Tags -->
                                    <td>
                                        <div class="faq-question">{{ Str::limit($faq->pertanyaan, 85) }}</div>
                                        @if($faq->tags->isNotEmpty())
                                            <div class="faq-tags">
                                                @foreach($faq->tags->take(3) as $tag)
                                                    <span class="tag-pill">{{ $tag->nama }}</span>
                                                @endforeach
                                                @if($faq->tags->count() > 3)
                                                    <span class="tag-pill">+{{ $faq->tags->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Data Entry -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="de-avatar">{{ $initials }}</div>
                                            <div>
                                                <div class="fs-13 fw-semibold text-dark">{{ $de?->nama ?? '—' }}</div>
                                                <div class="fs-11 text-muted">Data Entry</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Waktu Pengajuan -->
                                    <td>
                                        <div class="fs-13 text-dark">
                                            {{ optional($faq->created_at)->translatedFormat('d M Y') ?? '—' }}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            {{ optional($faq->created_at)->format('H.i') }} WIB
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        <span class="badge-status {{ $statusClass }}">
                                            <i class="bi {{ $statusIcon }}"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                                            <a href="{{ route('validator.validasiFAQ', ['search' => $faq->id]) }}" class="btn-action btn-action-view">
                                                <i class="bi bi-eye"></i> View
                                            </a>
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
                @if($faqs->hasPages())
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

<!-- ══════════════════════════════════════════════════════════════════
     MODAL KONFIRMASI KELUAR (LOGOUT)
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    function openLogoutModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalLogoutConfirm'));
        modal.show();
    }
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ruang Validator | KMS</title>
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
    $queueFaqs = $queueFaqs ?? collect();
    $selectedFaq = $selectedFaq ?? $queueFaqs->first();
    $selectedVer = $selectedFaq ? ($selectedFaq->versions->first(fn($v) => strtolower($v->status) === 'submitted') ?? $selectedFaq->versions->first()) : null;
@endphp

<div class="d-flex">
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
                <a href="{{ route('validator.dashboard') }}" class="nav-link-custom active">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('validator.validasiFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-list-check"></i>
                    <span>Validasi FAQ</span>
                </a>
                <a href="{{ route('validator.buatfaq') }}" class="nav-link-custom">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="{{ route('validator.katakunci') }}" class="nav-link-custom">
                    <i class="bi bi-tags"></i>
                    <span>Daftar Kata Kunci</span>
                </a>
                <a href="{{ route('validator.sumberpustaka') }}" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
                <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
                <a href="{{ route('validator.pengaturanAlertCall') }}" class="nav-link-custom">
                    <i class="bi bi-bell-fill"></i>
                    <span>Setting Alert Call</span>
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

    <!-- Main Wrapper -->
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
                    <span class="crumb-current">Dashboard</span>
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 fs-13 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-13 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                    <h1 class="page-title mb-1">Dashboard</h1>
                    <p class="text-muted fs-13 mb-0">Tinjau substansi, verifikasi sumber, dan bantu penulis menyempurnakan pengetahuan.</p>
                </div>
                <div>
                    <a href="{{ route('validator.validasiFAQ') }}" class="btn btn-outline-secondary bg-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-2xs text-dark fs-13 fw-semibold" style="border-color: #E2E8F0;">
                        <i class="bi bi-list-check"></i>
                        <span>Buka Antrean Validasi</span>
                    </a>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Stat 1: Menunggu validasi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-sliders2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu Validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $submittedCount ?? 0 }}</div>
                            <div class="fs-12 text-muted">Antrean seluruh tim validator</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Disetujui (Siap Publish) -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #EDE9FE; color: #6D28D9;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Diterima</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $approvedOnlyCount ?? 0 }}</div>
                            <div class="fs-12" style="color: #6D28D9;">Siap dipublikasikan</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Terbit -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3" style="border-color: #A7F3D0; background: linear-gradient(135deg, #FFFFFF 0%, #ECFDF5 100%);">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #059669 0%, #10B981 100%); color: #FFFFFF; box-shadow: 0 3px 8px rgba(5, 150, 105, 0.22);">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 fw-semibold mb-1" style="color: #065F46;">Terbit</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $publishedCount ?? 0 }}</div>
                            <div class="fs-12 fw-medium" style="color: #059669;">Tampil di FAQ Mahasiswa</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Ditolak / Revisi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Ditolak (Perlu Revisi)</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $rejectedCount ?? 0 }}</div>
                            <div class="fs-12" style="color: #DC2626;">Perlu tindak lanjut penulis</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Split Workspace Layout -->
            <div class="row g-3 g-xl-4">
                <!-- Left Column: Antrean Validasi List -->
                <div class="col-12 col-lg-4 col-xl-3">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-3">
                                <h2 class="fw-bold fs-16 text-dark mb-0">Antrean validasi</h2>
                                <div class="text-muted fs-12">{{ $queueFaqs->count() }} FAQ menunggu penelaahan</div>
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="mb-3">
                                <form method="GET" action="{{ route('validator.dashboard') }}" id="dashboardQueueSortForm">
                                    @if(request('faq_id'))
                                        <input type="hidden" name="faq_id" value="{{ request('faq_id') }}">
                                    @endif
                                    <div class="position-relative">
                                        <select name="sort" class="form-select rounded-3 fs-13 text-secondary" style="border-color: #E2E8F0; padding-top: 0.45rem; padding-bottom: 0.45rem;" onchange="document.getElementById('dashboardQueueSortForm').submit()">
                                            <option value="terbaru" {{ ($sort ?? 'terbaru') === 'terbaru' ? 'selected' : '' }}>&#8645; Terbaru diajukan</option>
                                            <option value="terlama" {{ ($sort ?? 'terbaru') === 'terlama' ? 'selected' : '' }}>&#8645; Terlama menunggu</option>
                                        </select>
                                    </div>
                                </form>
                            </div>

                            <!-- Queue Items List -->
                            <div class="queue-list">
                                @forelse($queueFaqs->take(5) as $qFaq)
                                    @php
                                        $qSubVer = $qFaq->versions->first(fn($v) => strtolower($v->status) === 'submitted') ?? $qFaq->versions->first();
                                        $qTimeObj = ($qSubVer?->updated_at ?? $qFaq->created_at)?->timezone('Asia/Jakarta');
                                    @endphp
                                    <a href="{{ route('validator.dashboard', ['faq_id' => $qFaq->id, 'sort' => $sort ?? 'terbaru']) }}"
                                       class="queue-item {{ ($selectedFaq && $selectedFaq->id === $qFaq->id) ? 'active' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fs-11 fw-semibold" style="color: #2563EB;">{{ $qFaq->id }}</span>
                                            <i class="bi bi-record-circle" style="color: #2563EB; font-size: 0.75rem;"></i>
                                        </div>
                                        <div class="fw-bold fs-13 text-dark mb-1">{{ $qFaq->pertanyaan }}</div>
                                        <div class="fs-11 text-muted">{{ $qFaq->dataEntry?->nama ?? 'Data Entry' }} &middot; {{ $qTimeObj?->translatedFormat('d M Y, H:i') }} WIB</div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted fs-12">
                                        Tidak ada antrean FAQ yang menunggu validasi saat ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Queue List Footer & Notice -->
                        <div>
                            <div class="d-flex align-items-center justify-content-between pt-2 pb-2 text-muted fs-11">
                                <span>Menampilkan {{ min(5, $queueFaqs->count()) }} dari {{ $queueFaqs->count() }} pengajuan</span>
                                <a href="{{ route('validator.validasiFAQ') }}" class="text-decoration-none fw-semibold" style="color: #85172C;">Lihat Semua</a>
                            </div>

                            <!-- Notification Box -->
                            <div class="rounded-3 p-2.5 d-flex align-items-start gap-2 mt-2" style="background-color: #FFFBEB; border: 1px solid #FEF3C7; padding: 0.65rem;">
                                <i class="bi bi-info-circle text-warning fs-6 mt-0.5" style="color: #D97706 !important;"></i>
                                <div class="fs-11" style="color: #92400E; line-height: 1.35;">
                                    Dahulukan pengajuan terlama agar informasi segera tersedia bagi mahasiswa.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Detail & Verification Workspace -->
                <div class="col-12 col-lg-8 col-xl-9">
                    <div class="card-custom">
                        @if($selectedFaq)
                            <div class="row g-4">
                                <!-- Left Sub-column: Content Details -->
                                <div class="col-12 col-lg-7 col-xl-8 pe-lg-4 border-end-lg">
                                    <!-- Header Badge & Version Link -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill fw-medium fs-12 px-3 py-1" style="background-color: #DBEAFE; color: #1D4ED8;">
                                                Menunggu Validasi
                                            </span>
                                            <span class="fs-12 text-muted">{{ $selectedFaq->id }} &middot; Versi {{ $selectedFaq->versions->count() ?: 1 }}</span>
                                        </div>
                                        <a href="{{ route('validator.validasiFAQ') }}" class="fs-12 fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="color: #2563EB;">
                                            <span>Lihat di Validasi FAQ</span>
                                            <i class="bi bi-box-arrow-up-right fs-11"></i>
                                        </a>
                                    </div>

                                    <!-- Question Title -->
                                    <h2 class="display-serif fs-20 fw-bold text-dark mb-3">
                                        {{ $selectedFaq->pertanyaan }}
                                    </h2>

                                    <!-- Metadata Box -->
                                    <div class="rounded-3 p-3 mb-4" style="background-color: #F8FAFC; border: 1px solid #F1F5F9;">
                                        <div class="row g-2 fs-12">
                                            <div class="col-sm-6">
                                                <div class="text-secondary mb-1">
                                                    <span class="text-muted">Kata Kunci :</span> {{ $selectedFaq->tags->pluck('nama')->implode(', ') ?: '-' }}
                                                </div>
                                                <div class="text-secondary">
                                                    <span class="text-muted">Diajukan :</span> {{ $selectedFaq->dataEntry?->nama ?? 'Data Entry' }}
                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-sm-end">
                                                <div class="text-muted mb-1">Dibuat {{ $selectedFaq->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</div>
                                                <div class="text-muted">Diperbarui {{ $selectedVer?->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Answer Section -->
                                    <div class="mb-4">
                                        <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #64748B; letter-spacing: 0.06em;">
                                            JAWABAN UNTUK MAHASISWA
                                        </div>
                                        <div class="fs-13 text-secondary lh-base">
                                            {!! $selectedVer?->jawaban ?? '-' !!}
                                        </div>
                                    </div>

                                    <!-- Official Source Attachment -->
                                    <div class="rounded-3 p-3 d-flex align-items-center gap-3" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                                        <div class="stat-icon" style="width: 38px; height: 38px; background-color: #DBEAFE; color: #2563EB;">
                                            <i class="bi bi-file-earmark-text fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold fs-13 text-dark">{{ $selectedVer?->daftarPustaka?->judul_tampil ?? $selectedVer?->daftarPustaka?->link ?? 'Pedoman Akademik Kampus' }}</div>
                                            <div class="fs-11 text-muted">Rujukan Resmi</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Sub-column: Review & Actions -->
                                <div class="col-12 col-lg-5 col-xl-4 ps-lg-3">
                                    <div>
                                        <h3 class="fw-bold fs-14 text-dark mb-1">Daftar pemeriksaan</h3>
                                        <p class="fs-11 text-muted mb-3">Periksa seluruh aspek sebelum menyetujui.</p>

                                        <!-- 4 Checklist Items -->
                                        <div class="d-flex flex-column gap-2 mb-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="checklist-icon"><i class="bi bi-check"></i></span>
                                                <span class="fs-12 text-secondary">Jawaban sesuai dokumen resmi</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="checklist-icon"><i class="bi bi-check"></i></span>
                                                <span class="fs-12 text-secondary">Sumber masih berlaku dan dapat diakses</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="checklist-icon"><i class="bi bi-check"></i></span>
                                                <span class="fs-12 text-secondary">Alur dan persyaratan sudah lengkap</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="checklist-icon"><i class="bi bi-check"></i></span>
                                                <span class="fs-12 text-secondary">Bahasa jelas, tanpa data pribadi</span>
                                            </div>
                                        </div>

                                        <!-- Form Reject / Revisi -->
                                        <form id="dashboardRejectForm" action="{{ route('validator.validasiFAQ.reject', $selectedFaq->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label fw-bold fs-12 text-dark mb-1">Catatan revisi</label>
                                                <textarea name="catatan" class="form-control rounded-3 fs-12 text-secondary" rows="3" placeholder="Tulis bagian yang perlu diperbaiki dan arahan untuk penulis..." style="border-color: #E2E8F0;" required></textarea>
                                                <div class="fs-11 text-muted mt-1">Wajib diisi jika menolak / meminta revisi.</div>
                                            </div>
                                        </form>

                                        <!-- Actions Buttons -->
                                        <div class="d-flex flex-column gap-2 mt-4">
                                            <form action="{{ route('validator.validasiFAQ.approve', $selectedFaq->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn-maroon w-100 justify-content-center shadow-2xs">
                                                    <i class="bi bi-check2"></i>
                                                    <span>Terima FAQ (Diterima)</span>
                                                </button>
                                            </form>
                                            <button type="submit" form="dashboardRejectForm" class="btn-pill-light w-100 justify-content-center shadow-2xs">
                                                <i class="bi bi-chat-left-text"></i>
                                                <span>Tolak / Minta Revisi</span>
                                            </button>
                                        </div>

                                        <!-- Footer Disclaimer -->
                                        <div class="fs-11 text-muted text-center mt-3 lh-sm">
                                            Keputusan dan identitas penelaah akan tersimpan dalam riwayat FAQ.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted fs-14">
                                <i class="bi bi-check2-all fs-1 d-block mb-2 text-success"></i>
                                Semua pengajuan FAQ telah selesai ditinjau!
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Riwayat Penelaahan Terbaru Table -->
            <div class="card-custom mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="fw-bold fs-16 text-dark mb-0">Riwayat penelaahan terbaru</h2>
                        <div class="text-muted fs-12">Keputusan validasi dan publikasi FAQ terakhir (Zona Waktu Jakarta WIB)</div>
                    </div>
                    <a href="{{ route('validator.daftarFAQ') }}" class="fs-13 fw-semibold text-decoration-none" style="color: #85172C;">
                        Lihat Daftar FAQ &rarr;
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 fs-13">
                        <thead class="table-light fs-11 text-uppercase text-muted">
                            <tr>
                                <th style="width: 110px;">ID / Versi</th>
                                <th>Pertanyaan FAQ</th>
                                <th style="width: 170px;">Data Entry</th>
                                <th style="width: 170px;">Penelaah</th>
                                <th style="width: 170px;">Waktu Diperbarui</th>
                                <th style="width: 130px; text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($recentValidatedVersions ?? collect()) as $rVer)
                                @php
                                    $rSt = strtolower($rVer->status ?? '');
                                    $rBadge = match($rSt) {
                                        'terbit', 'active'   => ['bg' => '#059669', 'color' => '#FFFFFF', 'label' => 'Terbit'],
                                        'diterima', 'approved' => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Diterima'],
                                        'ditolak', 'rejected'  => ['bg' => '#FEF2F2', 'color' => '#DC2626', 'label' => 'Ditolak'],
                                        'menunggu validasi', 'submitted' => ['bg' => '#FFFBEB', 'color' => '#D97706', 'label' => 'Menunggu Validasi'],
                                        default    => ['bg' => '#EFF6FF', 'color' => '#2563EB', 'label' => $rVer->status],
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $rVer->tfaqs_id }}</div>
                                        <div class="fs-11 text-muted">Versi #{{ $rVer->id }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $rVer->faq?->pertanyaan ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary">{{ $rVer->faq?->dataEntry?->nama ?? 'Data Entry' }}</td>
                                    <td class="text-secondary">{{ $rVer->validator?->nama ?? 'Validator' }}</td>
                                    <td class="text-muted fs-12">{{ $rVer->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                                    <td style="text-align: right;">
                                        <span class="badge rounded-pill fw-semibold px-3 py-1" style="background-color: {{ $rBadge['bg'] }}; color: {{ $rBadge['color'] }};">
                                            {{ $rBadge['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4 fs-12">Belum ada riwayat penelaahan FAQ.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
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
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }
</script>


</body>
</html>

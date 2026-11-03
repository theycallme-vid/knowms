<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Validator Dashboard</title>
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
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang {{ $roleName ?: 'Validator' }}</span>
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
                <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <!-- Account Settings & Logout -->
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

    <!-- Main Wrapper -->
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
                    <strong class="text-dark">Dashboard</strong>
                </div>
            </div>

            <!-- Right: Search, Notification & Profile -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr($authUser->nama ?? 'RK', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? 'Ratna Kusuma' }}</div>
                            <div class="fs-11 text-muted">{{ $roleName ?: 'Validator' }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ $authUser->nama ?? 'Ratna Kusuma' }}</div>
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
            <!-- Page Header Area (Ukuran & Warna Seragam) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                    <h1 class="page-title mb-1">Pastikan setiap jawaban layak dipercaya.</h1>
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
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">8</div>
                            <div class="fs-12 text-muted">Antrean seluruh tim validator</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Perlu diprioritaskan -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-alarm"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Perlu diprioritaskan</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">4</div>
                            <div class="fs-12" style="color: #DC2626;">Menunggu lebih dari 3 hari</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Ditinjau minggu ini -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #ECFDF5; color: #059669;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Ditinjau minggu ini</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">38</div>
                            <div class="fs-12 text-muted">32 terbit &middot; 6 dikembalikan</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Rata-rata waktu ulasan -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FFFBEB; color: #D97706;">
                            <i class="bi bi-stopwatch"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Rata-rata waktu ulasan</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">1,2 hari</div>
                            <div class="fs-12 text-muted">Target layanan maksimal 2 hari</div>
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
                                <div class="text-muted fs-12">8 FAQ menunggu penelaahan</div>
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="mb-3">
                                <div class="position-relative">
                                    <select class="form-select rounded-3 fs-13 text-secondary" style="border-color: #E2E8F0; padding-top: 0.45rem; padding-bottom: 0.45rem;">
                                        <option selected>&#8645; Terlama menunggu</option>
                                        <option value="1">Terbaru diajukan</option>
                                        <option value="2">Kategori A - Z</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Queue Items List -->
                            <div class="queue-list">
                                <!-- Item 1 (Selected) -->
                                <a href="{{ route('validator.validasiFAQ') }}" class="queue-item active">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fs-11 fw-semibold" style="color: #DC2626;">4 hari menunggu</span>
                                        <i class="bi bi-record-circle" style="color: #DC2626; font-size: 0.75rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Persyaratan yudisium Fakultas Teknik</div>
                                    <div class="fs-11 text-muted">Nadia Putri &middot; 21 Okt 2026</div>
                                </a>

                                <!-- Item 2 -->
                                <a href="{{ route('validator.validasiFAQ') }}" class="queue-item">
                                    <div class="fs-11 text-muted mb-1">4 hari menunggu</div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Bagaimana cara melakukan pengisian KRS?</div>
                                    <div class="fs-11 text-muted">Aditya Pratama &middot; 21 Okt 2026</div>
                                </a>

                                <!-- Item 3 -->
                                <a href="{{ route('validator.validasiFAQ') }}" class="queue-item">
                                    <div class="fs-11 text-muted mb-1">3 hari menunggu</div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Bagaimana cara mengajukan legalisasi transkrip nilai?</div>
                                    <div class="fs-11 text-muted">Dewi Lestari &middot; 20 Okt 2026</div>
                                </a>
                            </div>
                        </div>

                        <!-- Queue List Footer & Notice -->
                        <div>
                            <div class="d-flex align-items-center justify-content-between pt-2 pb-2 text-muted fs-11">
                                <span>1-3 dari 8 pengajuan</span>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none"><i class="bi bi-chevron-left"></i></button>
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none"><i class="bi bi-chevron-right"></i></button>
                                </div>
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
                        <div class="row g-4">
                            <!-- Left Sub-column: Content Details -->
                            <div class="col-12 col-lg-7 col-xl-8 pe-lg-4 border-end-lg">
                                <!-- Header Badge & Version Link -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-pill fw-medium fs-12 px-3 py-1" style="background-color: #DBEAFE; color: #1D4ED8;">
                                            Menunggu Validasi
                                        </span>
                                        <span class="fs-12 text-muted">FAQ-0244 &middot; Versi 2.0</span>
                                    </div>
                                    <a href="{{ route('validator.validasiFAQ') }}" class="fs-12 fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="color: #2563EB;">
                                        <span>Lihat di Validasi FAQ</span>
                                        <i class="bi bi-box-arrow-up-right fs-11"></i>
                                    </a>
                                </div>

                                <!-- Question Title -->
                                <h2 class="display-serif fs-20 fw-bold text-dark mb-3">
                                    Apa persyaratan dan alur pengajuan cuti akademik?
                                </h2>

                                <!-- Metadata Box -->
                                <div class="rounded-3 p-3 mb-4" style="background-color: #F8FAFC; border: 1px solid #F1F5F9;">
                                    <div class="row g-2 fs-12">
                                        <div class="col-sm-6">
                                            <div class="text-secondary mb-1">
                                                <span class="text-muted">Kategori :</span> Akademik / Cuti
                                            </div>
                                            <div class="text-secondary">
                                                <span class="text-muted">Diajukan</span> Nadia Putri &middot; Data Entry
                                            </div>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">
                                            <div class="text-muted mb-1">Diajukan 17 Okt 2026, 09.10</div>
                                            <div class="text-muted">Diperbarui 20 Okt 2026, 15.45</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Answer Section -->
                                <div class="mb-4">
                                    <div class="text-uppercase fw-bold fs-11 mb-2" style="color: #64748B; letter-spacing: 0.06em;">
                                        JAWABAN UNTUK MAHASISWA
                                    </div>
                                    <p class="fs-13 text-secondary lh-base mb-3">
                                        Cuti akademik dapat diajukan oleh mahasiswa aktif yang telah memenuhi ketentuan masa studi. Pengajuan dilakukan sebelum batas waktu pada kalender akademik dan harus mendapat persetujuan fakultas.
                                    </p>
                                    <ol class="fs-13 text-secondary ps-3 mb-3 lh-base" style="list-style-type: decimal;">
                                        <li class="mb-2">Konsultasikan alasan dan rencana cuti dengan dosen wali untuk memastikan kelayakan pengajuan.</li>
                                        <li class="mb-2">Lengkapi formulir cuti, surat permohonan, serta dokumen pendukung sesuai ketentuan fakultas.</li>
                                        <li class="mb-2">Ajukan berkas melalui layanan administrasi fakultas. Pantau persetujuan dan selesaikan kewajiban administrasi yang ditetapkan.</li>
                                    </ol>
                                    <p class="fs-13 text-secondary lh-base mb-0">
                                        Setelah disetujui, simpan bukti cuti. Ajukan aktif kembali sebelum periode registrasi berikutnya; cuti tidak otomatis memperpanjang batas masa studi.
                                    </p>
                                </div>

                                <!-- Official Source Attachment -->
                                <div class="rounded-3 p-3 d-flex align-items-center gap-3" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                                    <div class="stat-icon" style="width: 38px; height: 38px; background-color: #DBEAFE; color: #2563EB;">
                                        <i class="bi bi-file-earmark-text fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-13 text-dark">Pedoman Akademik 2026/2027 &middot; Bab Cuti Akademik</div>
                                        <div class="fs-11 text-muted">Biro Administrasi Akademik &middot; Dokumen resmi &middot; Hal. 42-44</div>
                                        <a href="#" class="fs-11 fw-semibold text-decoration-none d-inline-flex align-items-center gap-1 mt-0.5" style="color: #2563EB;">
                                            <span>pedoman-akademik-2026.pdf &middot; Buka sumber</span>
                                            <i class="bi bi-box-arrow-up-right" style="font-size: 0.65rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Sub-column: Review & Actions -->
                            <div class="col-12 col-lg-5 col-xl-4 ps-lg-3">
                                <div>
                                    <h3 class="fw-bold fs-14 text-dark mb-1">Daftar pemeriksaan</h3>
                                    <p class="fs-11 text-muted mb-3">Periksa seluruh aspek sebelum menerbitkan.</p>

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

                                    <!-- Revision Notes Textarea -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold fs-12 text-dark mb-1">Catatan revisi</label>
                                        <textarea class="form-control rounded-3 fs-12 text-secondary" rows="3" placeholder="Tulis bagian yang perlu diperbaiki dan arahan untuk penulis..." style="border-color: #E2E8F0;"></textarea>
                                        <div class="fs-11 text-muted mt-1">Wajib diisi jika meminta revisi.</div>
                                    </div>

                                    <!-- Actions Buttons -->
                                    <div class="d-flex flex-column gap-2 mt-4">
                                        <a href="{{ route('validator.validasiFAQ') }}" class="btn-maroon shadow-2xs">
                                            <i class="bi bi-check2"></i>
                                            <span>Setujui & terbitkan</span>
                                        </a>
                                        <a href="{{ route('validator.validasiFAQ') }}" class="btn-pill-light shadow-2xs">
                                            <i class="bi bi-chat-left-text"></i>
                                            <span>Minta revisi</span>
                                        </a>
                                    </div>

                                    <!-- Footer Disclaimer -->
                                    <div class="fs-11 text-muted text-center mt-3 lh-sm">
                                        Keputusan dan identitas penelaah akan tersimpan dalam riwayat FAQ.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
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

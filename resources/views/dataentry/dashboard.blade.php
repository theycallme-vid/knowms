<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar FAQ - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Data Entry Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand mb-3">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div class="mb-3">
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('dataentry.dashboard') }}" class="nav-link-custom active">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider my-3">
            <!-- Account Settings & Logout -->
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link-custom text-secondary" style="cursor: pointer;">
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
                    <span class="mx-2 text-muted">/</span>
                    <span class="text-dark fw-semibold">Data Entry</span>
                </div>
            </div>

            <!-- Right: Search, Profile & Notification -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" id="topSearchInput" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->nama ?? 'NP', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Nadia Putri' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()->getRoleName() ?: 'Data Entry' }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Nadia Putri' }}</div>
                                <div class="fs-11 text-muted">{{ Auth::user()->email ?? 'nadiaputri@kms.ac.id' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 d-flex align-items-center gap-2" href="#">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan Akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RUANG PENULIS &middot; DATA ENTRY</div>
                    <h1 class="display-serif fs-2 mb-1" style="font-size: 1.85rem; font-weight: 500;">Pengetahuan yang Anda tulis, membantu banyak langkah.</h1>
                    <p class="text-muted fs-14 mb-0">Kelola FAQ Anda, lengkapi sumber resmi, lalu ajukan untuk divalidasi.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ route('dataentry.buatfaq') }}" class="btn-maroon">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat FAQ</span>
                    </a>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 mb-4">
                <!-- Stat 1: Total FAQ saya -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-copy"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Total FAQ saya</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">24</div>
                            <div class="fs-12 text-muted">8 di antaranya telah terbit</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Draft -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Draft</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">8</div>
                            <div class="fs-12 text-muted">Lanjutkan penulisan Anda</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Menunggu Validasi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu Validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">5</div>
                            <div class="fs-12 text-muted">Sedang ditinjau validator</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Perlu Revisi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #FEE2E2; color: #DC2626;">
                            <i class="bi bi-chat-square-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Perlu Revisi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">3</div>
                            <div class="fs-12" style="color: #DC2626;">Ada catatan untuk ditindaklanjuti</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Full-Width FAQ Table Card -->
            <div class="card-custom">
                <!-- Table Header Info -->
                <div class="mb-3">
                    <h2 class="fw-bold fs-16 text-dark mb-1">Daftar FAQ</h2>
                    <div class="text-muted fs-12">24 FAQ &middot; seluruh pengajuan dan publikasi Anda</div>
                </div>

                <!-- Search & Filter Controls -->
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <div class="position-relative flex-grow-1" style="min-width: 240px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                        <input type="text" id="faqSearchInput" class="form-control rounded-3 ps-5 fs-13" placeholder="Cari judul FAQ atau kata kunci..." style="border-color: #E2E8F0; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    </div>
                    <div>
                        <select id="categoryFilter" class="form-select rounded-3 fs-13 text-secondary" style="border-color: #E2E8F0; width: auto; padding-top: 0.5rem; padding-bottom: 0.5rem; min-width: 160px;">
                            <option value="all" selected>Semua kategori</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Kelulusan">Kelulusan</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="Transkrip & nilai">Transkrip & nilai</option>
                            <option value="Rencana studi">Rencana studi</option>
                            <option value="Administrasi">Administrasi</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary bg-white rounded-3 fs-13 d-flex align-items-center gap-2 text-dark" style="border-color: #E2E8F0; padding: 0.5rem 0.85rem;">
                            <i class="bi bi-filter"></i>
                            <span>Terakhir diperbarui</span>
                        </button>
                    </div>
                </div>

                <!-- Status Filter Tabs -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="filter-pill active" data-status="all">Semua 24</button>
                    <button class="filter-pill" data-status="Draft">Draft 8</button>
                    <button class="filter-pill" data-status="Menunggu Validasi">Menunggu Validasi 5</button>
                    <button class="filter-pill" data-status="Perlu Revisi">Perlu Revisi 3</button>
                    <button class="filter-pill" data-status="Terbit">Terbit 8</button>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 44%;">JUDUL FAQ</th>
                                <th style="width: 16%;">KATEGORI</th>
                                <th style="width: 16%;">DIPERBARUI</th>
                                <th style="width: 14%;">STATUS</th>
                                <th style="width: 10%; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="faqTableBody">
                            <!-- Row 1: Draft -->
                            <tr data-status="Draft" data-category="Akademik">
                                <td>
                                    <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana prosedur pengajuan cuti akademik?</div>
                                    <div class="fs-11 text-muted">FAQ-0248 &middot; Versi 1</div>
                                </td>
                                <td class="text-secondary fs-12">Akademik</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">21 Okt 2026</div>
                                    <div class="fs-11 text-muted">10.12 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-draft">Draft</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 2: Menunggu Validasi — edit DISABLED -->
                            <tr data-status="Menunggu Validasi" data-category="Kelulusan">
                                <td>
                                    <div class="fw-semibold text-dark fs-13 mb-0.5">Apa saja persyaratan yudisium Fakultas Teknik?</div>
                                    <div class="fs-11 text-muted">FAQ-0247 &middot; Versi 1</div>
                                </td>
                                <td class="text-secondary fs-12">Kelulusan</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">21 Okt 2026</div>
                                    <div class="fs-11 text-muted">10.18 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-validasi">Menunggu Validasi</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn action-icon-disabled" title="Tidak dapat diedit saat sedang ditinjau validator" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 3: Perlu Revisi (Highlighted) — dengan tooltip catatan validator -->
                            <tr class="row-revision" data-status="Perlu Revisi" data-category="Keuangan">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Kapan batas pembayaran UKT semester genap?</div>
                                            <div class="fs-11 text-muted">FAQ-0239 &middot; Versi 2</div>
                                        </div>
                                        <!-- Revisi tooltip icon -->
                                        <span class="revisi-note-icon ms-1"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            data-bs-html="true"
                                            title="<strong>Catatan Validator:</strong><br>Mohon cantumkan surat edaran terbaru dan perjelas ketentuan denda. Tautan sumber sebelumnya belum dapat diakses.<br><span class='text-muted' style='font-size:0.7rem;'>— Ratna Kusuma, 20 Okt 14.30</span>">
                                            <i class="bi bi-chat-left-text-fill" style="color: #DC2626; font-size: 0.75rem;"></i>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-secondary fs-12">Keuangan</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">20 Okt 2026</div>
                                    <div class="fs-11 text-muted">14.30 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-revisi">Perlu Revisi</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn" title="Edit / Perbaiki"><i class="bi bi-pencil"></i></button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 4: Terbit -->
                            <tr data-status="Terbit" data-category="Transkrip & nilai">
                                <td>
                                    <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana cara mengunduh transkrip sementara?</div>
                                    <div class="fs-11 text-muted">FAQ-0235 &middot; Versi 3</div>
                                </td>
                                <td class="text-secondary fs-12">Transkrip & nilai</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">19 Okt 2026</div>
                                    <div class="fs-11 text-muted">09.45 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-terbit">Terbit</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn action-icon-disabled" title="FAQ yang telah terbit tidak dapat diedit langsung" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 5: Menunggu Validasi — edit DISABLED -->
                            <tr data-status="Menunggu Validasi" data-category="Rencana studi">
                                <td>
                                    <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana mengajukan perubahan kelas KRS?</div>
                                    <div class="fs-11 text-muted">FAQ-0231 &middot; Versi 1</div>
                                </td>
                                <td class="text-secondary fs-12">Rencana studi</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">18 Okt 2026</div>
                                    <div class="fs-11 text-muted">13.20 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-validasi">Menunggu Validasi</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn action-icon-disabled" title="Tidak dapat diedit saat sedang ditinjau validator" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 6: Terbit -->
                            <tr data-status="Terbit" data-category="Administrasi">
                                <td>
                                    <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana mendapatkan surat aktif kuliah?</div>
                                    <div class="fs-11 text-muted">FAQ-0228 &middot; Versi 4</div>
                                </td>
                                <td class="text-secondary fs-12">Administrasi</td>
                                <td>
                                    <div class="text-secondary fs-12 lh-sm">17 Okt 2026</div>
                                    <div class="fs-11 text-muted">11.05 WIB</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-terbit">Terbit</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="action-icon-btn" title="Lihat"><i class="bi bi-eye"></i></button>
                                        <button class="action-icon-btn action-icon-disabled" title="FAQ yang telah terbit tidak dapat diedit langsung" disabled>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination Footer -->
                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2 border-top border-light gap-2">
                    <div class="fs-12 text-muted" id="faqCountLabel">
                        Menampilkan 1-6 dari 24 FAQ
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="#" class="page-pill"><i class="bi bi-chevron-left"></i></a>
                        <a href="#" class="page-pill active">1</a>
                        <a href="#" class="page-pill">2</a>
                        <a href="#" class="page-pill">3</a>
                        <a href="#" class="page-pill">4</a>
                        <a href="#" class="page-pill"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Unggah File -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-16" id="uploadModalLabel">Unggah Dokumen / Sumber Resmi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light">
                    <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-2 d-block"></i>
                    <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                    <div class="text-muted fs-12 mb-3">Mendukung format PDF, DOCX, atau Gambar hingga 10MB</div>
                    <label class="btn btn-sm btn-white-outline">
                        Pilih Dokumen
                        <input type="file" class="d-none">
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-maroon px-3" data-bs-dismiss="modal">Unggah Sekarang</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 for modern confirmation pop-up -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    // Konfirmasi Logout dengan SweetAlert2
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C', // Warna maroon KMS
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin keluar dari sistem KMS?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }

    // Dynamic Filter & Search Handler
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('faqSearchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const filterPills = document.querySelectorAll('.filter-pill');
        const tableRows = document.querySelectorAll('#faqTableBody tr');
        let currentStatus = 'all';

        function filterTable() {
            const query = (searchInput.value || '').toLowerCase().trim();
            const selectedCategory = categoryFilter.value;
            let visibleCount = 0;

            tableRows.forEach(row => {
                const status = row.getAttribute('data-status');
                const category = row.getAttribute('data-category');
                const textContent = row.textContent.toLowerCase();

                const matchesStatus = (currentStatus === 'all') || (status === currentStatus);
                const matchesCategory = (selectedCategory === 'all') || (category === selectedCategory);
                const matchesSearch = !query || textContent.includes(query);

                if (matchesStatus && matchesCategory && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countLabel = document.getElementById('faqCountLabel');
            if (countLabel) {
                countLabel.textContent = `Menampilkan ${visibleCount} dari 24 FAQ`;
            }
        }

        // Filter Pills Click
        filterPills.forEach(pill => {
            pill.addEventListener('click', function () {
                filterPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                currentStatus = this.getAttribute('data-status');
                filterTable();
            });
        });

        // Search Input
        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
        }

        // Category Filter
        if (categoryFilter) {
            categoryFilter.addEventListener('change', filterTable);
        }
    });
</script>
</body>
</html>
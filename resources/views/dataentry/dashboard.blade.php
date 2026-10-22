<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Data Entry Dashboard</title>

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --app-bg: #FAF9F5;
            --sidebar-bg: #FAF9F5;
            --card-bg: #FFFFFF;
            --card-border: #EAEAE4;
            --brand-maroon: #85172C;
            --brand-maroon-hover: #701023;
            --brand-maroon-soft: #FCEEEF;
            --text-heading: #171A1F;
            --text-body: #334155;
            --text-muted: #64748B;
            --text-subtle: #94A3B8;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Lora', Georgia, serif;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--app-bg);
            color: var(--text-body);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Typography */
        .display-serif {
            font-family: var(--font-serif);
            font-weight: 600;
            letter-spacing: -0.01em;
            color: var(--text-heading);
        }

        .fs-11 { font-size: 0.6875rem !important; }
        .fs-12 { font-size: 0.75rem !important; }
        .fs-13 { font-size: 0.8125rem !important; }
        .fs-14 { font-size: 0.875rem !important; }
        .fs-15 { font-size: 0.9375rem !important; }
        .fs-24 { font-size: 1.5rem !important; }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--card-border);
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem 1.25rem 1.25rem 1.25rem;
            z-index: 1020;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: #E2E8F0;
            border-radius: 4px;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background-color: #0F172A;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            flex-shrink: 0;
        }

        .role-pill {
            display: inline-block;
            background-color: #ECEFE8;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-top: 1rem;
            margin-bottom: 1.25rem;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: 12px;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.2rem;
        }

        .nav-link-custom:hover {
            background-color: #F1F4F8;
            color: var(--text-heading);
        }

        .nav-link-custom.active {
            background-color: var(--brand-maroon-soft);
            color: var(--brand-maroon);
            font-weight: 600;
        }

        .nav-link-custom i {
            font-size: 1.05rem;
        }

        /* Help Box */
        .help-box {
            background-color: #EEF4F9;
            border: 1px solid #DCE6F0;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .help-icon {
            width: 28px;
            height: 28px;
            color: #3B82F6;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        /* Top Header Navbar */
        .top-navbar {
            padding: 1.25rem 2rem 0.75rem 2rem;
            background-color: var(--app-bg);
        }

        .search-container {
            position: relative;
            width: 340px;
        }

        .search-container i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-subtle);
            font-size: 0.875rem;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 9999px;
            border: 1px solid #E2E8F0;
            background-color: #FFFFFF;
            font-size: 0.8125rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--brand-maroon);
            box-shadow: 0 0 0 3px rgba(133, 23, 44, 0.1);
        }

        .search-input::placeholder {
            color: var(--text-subtle);
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #EDE4D8;
            color: #795535;
            font-weight: 700;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Main Content Layout */
        .content-area {
            padding: 0.5rem 2rem 2.5rem 2rem;
        }

        /* Custom Cards */
        .card-custom {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            padding: 1.25rem;
            transition: box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        /* Stat Icon */
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Primary Maroon Button */
        .btn-maroon {
            background-color: var(--brand-maroon);
            color: #FFFFFF;
            border: none;
            border-radius: 9999px;
            padding: 0.6rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-maroon:hover {
            background-color: var(--brand-maroon-hover);
            color: #FFFFFF;
        }

        /* Status Filter Buttons */
        .filter-pill {
            border: none;
            padding: 0.35rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: #F1F5F9;
            color: #64748B;
            transition: all 0.15s ease;
        }

        .filter-pill:hover {
            background-color: #E2E8F0;
            color: var(--text-heading);
        }

        .filter-pill.active {
            background-color: #0F172A;
            color: #FFFFFF;
            font-weight: 600;
        }

        /* Badges */
        .badge-status {
            font-size: 0.72rem;
            font-weight: 500;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-status-draft {
            background-color: #F1F5F9;
            color: #475569;
        }

        .badge-status-validasi {
            background-color: #DBEAFE;
            color: #1D4ED8;
        }

        .badge-status-revisi {
            background-color: #FEE2E2;
            color: #DC2626;
        }

        .badge-status-terbit {
            background-color: #DCFCE7;
            color: #15803D;
        }

        /* Modern Table Styling */
        .faq-table th {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-subtle);
            border-bottom: 1px solid #F1F5F9;
            padding: 0.75rem 0.75rem;
            background: transparent;
        }

        .faq-table td {
            font-size: 0.8125rem;
            padding: 0.95rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #F8FAFC;
        }

        .faq-table tbody tr:hover td {
            background-color: #FAFAFA;
        }

        /* Highlighted row for needs revision */
        .faq-table tbody tr.row-revision td {
            background-color: #FFF6F6;
        }

        .faq-table tbody tr.row-revision:hover td {
            background-color: #FFEEED;
        }

        /* Action Icon Buttons */
        .action-icon-btn {
            background: transparent;
            border: none;
            color: #94A3B8;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-icon-btn:hover {
            color: #1E293B;
            background-color: #F1F5F9;
        }

        /* Pagination item */
        .page-pill {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #64748B;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .page-pill:hover {
            background-color: #F1F5F9;
            color: var(--text-heading);
        }

        .page-pill.active {
            background-color: #0F172A;
            color: #FFFFFF;
            font-weight: 600;
        }

        /* Date Tag */
        .date-badge {
            color: #2E7D32;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
        }

        /* Responsive Sidebar Drawer */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -260px;
                transition: left 0.3s ease;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            .sidebar.show {
                left: 0;
            }
            .top-navbar {
                padding: 1rem;
            }
            .content-area {
                padding: 0.5rem 1rem 2rem 1rem;
            }
            .search-container {
                width: 100%;
                max-width: 260px;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="d-flex align-items-center gap-2">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-15 text-dark lh-sm">KMS</div>
                    <div class="fs-11 text-muted">Knowledge Management System</div>
                </div>
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="#" class="nav-link-custom active">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>FAQ saya</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-plus-circle"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber resmi</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat pengajuan</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <!-- Help Box -->
            <div class="help-box">
                <div class="help-icon">
                    <i class="bi bi-life-preserver fs-5"></i>
                </div>
                <div class="fw-bold fs-13 text-dark mb-1">Butuh bantuan?</div>
                <div class="text-muted fs-11 mb-2">Panduan pengelolaan pengetahuan untuk tim kampus.</div>
                <a href="#" class="fw-semibold fs-12 text-decoration-none" style="color: #2563EB;">
                    Buka panduan &rarr;
                </a>
            </div>

            <!-- Account Settings & Logout -->
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan akun</span>
            </a>
            <a href="#" class="nav-link-custom text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
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
                    <strong class="text-dark">Data Entry</strong>
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

                <!-- User Profile -->
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle">NP</div>
                    <div class="d-none d-sm-block text-start lh-sm">
                        <div class="fw-bold fs-13 text-dark">Nadia Putri</div>
                        <div class="fs-11 text-muted">Data Entry</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RUANG PENULIS &middot; DATA ENTRY</div>
                    <h1 class="display-serif fs-1 mb-1">Pengetahuan yang Anda tulis, membantu banyak langkah.</h1>
                    <p class="text-muted fs-14 mb-0">Kelola FAQ Anda, lengkapi sumber resmi, lalu ajukan untuk divalidasi.</p>
                </div>
                <div>
                    <a href="#" class="btn-maroon shadow-sm">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat FAQ baru</span>
                    </a>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Stat 1: Total FAQ saya -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-files"></i>
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
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="bi bi-pencil-square"></i>
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
                    <div class="card-custom d-flex align-items-start gap-3">
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
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEE2E2; color: #DC2626;">
                            <i class="bi bi-exclamation-square"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Perlu Revisi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">3</div>
                            <div class="fs-12" style="color: #DC2626;">Ada catatan untuk ditindaklanjuti</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Split Layout (Table on Left, Assist Cards on Right) -->
            <div class="row g-3 g-xl-4">
                <!-- Left: FAQ Table Section -->
                <div class="col-12 col-lg-8">
                    <div class="card-custom">
                        <!-- Table Header Info -->
                        <div class="mb-3">
                            <h2 class="fw-bold fs-16 text-dark mb-0">FAQ saya</h2>
                            <div class="text-muted fs-12">24 FAQ &middot; seluruh pengajuan dan publikasi Anda</div>
                        </div>

                        <!-- Search & Filter Bar -->
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            <div class="position-relative flex-grow-1" style="min-width: 220px;">
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                                <input type="text" class="form-control rounded-3 ps-5 fs-13" placeholder="Cari judul FAQ atau kata kunci..." style="border-color: #E2E8F0; padding-top: 0.45rem; padding-bottom: 0.45rem;">
                            </div>
                            <div>
                                <select class="form-select rounded-3 fs-13 text-secondary" style="border-color: #E2E8F0; width: auto; padding-top: 0.45rem; padding-bottom: 0.45rem;">
                                    <option selected>Semua kategori</option>
                                    <option value="1">Akademik</option>
                                    <option value="2">Kelulusan</option>
                                    <option value="3">Keuangan</option>
                                    <option value="4">Transkrip & nilai</option>
                                    <option value="5">Rencana studi</option>
                                    <option value="6">Administrasi</option>
                                </select>
                            </div>
                            <div>
                                <button class="btn btn-outline-secondary bg-white rounded-3 fs-13 d-flex align-items-center gap-2 text-dark shadow-2xs" style="border-color: #E2E8F0; padding: 0.45rem 0.85rem;">
                                    <i class="bi bi-filter"></i>
                                    <span>Terakhir diperbarui</span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button class="filter-pill active">Semua 24</button>
                            <button class="filter-pill">Draft 8</button>
                            <button class="filter-pill">Menunggu Validasi 5</button>
                            <button class="filter-pill">Perlu Revisi 3</button>
                            <button class="filter-pill">Terbit 8</button>
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
                                <tbody>
                                    <!-- Row 1: Draft -->
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana prosedur pengajuan cuti akademik?</div>
                                            <div class="fs-11 text-muted">FAQ-0248 &middot; Sumber resmi terlampir</div>
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

                                    <!-- Row 2: Menunggu Validasi -->
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Apa saja persyaratan yudisium Fakultas Teknik?</div>
                                            <div class="fs-11 text-muted">FAQ-0247 &middot; Sumber resmi terlampir</div>
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
                                                <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Row 3: Perlu Revisi (Highlighted) -->
                                    <tr class="row-revision">
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Kapan batas pembayaran UKT semester genap?</div>
                                            <div class="fs-11 text-muted">FAQ-0239 &middot; Sumber resmi terlampir</div>
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
                                                <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Row 4: Terbit -->
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana cara mengunduh transkrip sementara?</div>
                                            <div class="fs-11 text-muted">FAQ-0235 &middot; Sumber resmi terlampir</div>
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
                                                <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Row 5: Menunggu Validasi -->
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana mengajukan perubahan kelas KRS?</div>
                                            <div class="fs-11 text-muted">FAQ-0231 &middot; Sumber resmi terlampir</div>
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
                                                <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Row 6: Terbit -->
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-13 mb-0.5">Bagaimana mendapatkan surat aktif kuliah?</div>
                                            <div class="fs-11 text-muted">FAQ-0228 &middot; Sumber resmi terlampir</div>
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
                                                <button class="action-icon-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="action-icon-btn" title="Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Pagination Footer -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2 border-top border-light gap-2">
                            <div class="fs-12 text-muted">
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

                <!-- Right: Side Helper / Action Cards -->
                <div class="col-12 col-lg-4">
                    <!-- Card 1: Revisi Perlu Perhatian -->
                    <div class="card-custom mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-square text-danger fs-6"></i>
                            <span class="text-danger fw-bold fs-12">Revisi perlu perhatian</span>
                        </div>
                        <div class="fw-bold fs-14 text-dark mb-1">
                            Batas pembayaran UKT semester genap
                        </div>
                        <div class="fs-12 text-muted mb-3">
                            Ratna Kusuma &middot; 20 Okt, 14.30
                        </div>
                        <p class="fs-12 text-secondary mb-3 lh-base">
                            &ldquo;Mohon cantumkan surat edaran terbaru dan perjelas ketentuan denda. Tautan sumber sebelumnya belum dapat diakses.&rdquo;
                        </p>
                        <button class="btn btn-outline-secondary bg-white rounded-pill w-100 py-2 d-flex align-items-center justify-content-center gap-2 text-dark fs-13 fw-semibold shadow-2xs" style="border-color: #E2E8F0;">
                            <i class="bi bi-pencil"></i>
                            <span>Perbaiki FAQ</span>
                        </button>
                    </div>

                    <!-- Card 2: Siap Untuk Ditinjau? -->
                    <div class="card-custom">
                        <div class="fw-bold fs-14 text-dark mb-3">
                            Siap untuk ditinjau?
                        </div>

                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success fs-6 mt-0.5"></i>
                            <span class="fs-12 text-secondary">
                                Gunakan pertanyaan yang spesifik dan mudah dipahami.
                            </span>
                        </div>

                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success fs-6 mt-0.5"></i>
                            <span class="fs-12 text-secondary">
                                Lampirkan dokumen atau tautan resmi yang masih berlaku.
                            </span>
                        </div>

                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success fs-6 mt-0.5"></i>
                            <span class="fs-12 text-secondary">
                                Pastikan jawaban lengkap, ringkas, dan tidak memuat data pribadi.
                            </span>
                        </div>

                        <div class="border-top border-light pt-3 mt-1">
                            <a href="#" class="d-inline-flex align-items-center gap-2 fs-12 fw-semibold text-dark text-decoration-none">
                                <i class="bi bi-book"></i>
                                <span>Buka pustaka sumber resmi</span>
                                <i class="bi bi-box-arrow-up-right fs-11 ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
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
</script>
</body>
</html>

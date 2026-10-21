<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Super Admin Dashboard</title>

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

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
            height: 100%;
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

        /* Action List Item */
        .quick-action-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.75rem;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s ease;
            margin-bottom: 0.5rem;
        }

        .quick-action-item:hover {
            background-color: #F8FAFC;
            transform: translateX(2px);
        }

        /* Custom Badges */
        .badge-status {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.35rem 0.875rem;
            border-radius: 9999px;
            display: inline-block;
        }

        .badge-status-terbit {
            background-color: #DCFCE7;
            color: #15803D;
        }

        .badge-status-validasi {
            background-color: #DBEAFE;
            color: #1D4ED8;
        }

        .badge-status-selesai {
            background-color: #CCFBF1;
            color: #0F766E;
        }

        .badge-status-revisi {
            background-color: #FEF3C7;
            color: #B45309;
        }

        /* Modern Table Styling */
        .activity-table th {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-subtle);
            border-bottom: 1px solid #F1F5F9;
            padding: 0.75rem 0.5rem;
            background: transparent;
        }

        .activity-table td {
            font-size: 0.8125rem;
            padding: 0.875rem 0.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #F8FAFC;
            background: transparent;
        }

        .activity-table tbody tr:hover td {
            background-color: #FAFAFA;
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

        /* Donut Legend Dot */
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
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
                <span class="role-pill">Ruang Super Admin</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="#" class="nav-link-custom active">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna & akses</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-diagram-3"></i>
                    <span>Kategori</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-graph-up"></i>
                    <span>Laporan pencarian</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat aktivitas</span>
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

            <!-- Account Settings -->
            <a href="#" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Pengaturan akun</span>
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
                    <strong class="text-dark">Super Admin</strong>
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
                    <div class="avatar-circle">DP</div>
                    <div class="d-none d-sm-block text-start lh-sm">
                        <div class="fw-bold fs-13 text-dark">Dimas Pratama</div>
                        <div class="fs-11 text-muted">Super Admin</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RABU, 21 OKTOBER 2026</div>
                    <h1 class="display-serif fs-1 mb-1">Gambaran besar, keputusan lebih baik.</h1>
                    <p class="text-muted fs-14 mb-0">Pantau kualitas pengetahuan dan layanan informasi kampus dalam satu ruang.</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary bg-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm text-dark fs-13 fw-medium" style="border-color: #E2E8F0;">
                        <i class="bi bi-download"></i>
                        <span>Unduh laporan</span>
                    </button>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Stat 1: Total FAQ -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-files"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Total FAQ</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">248</div>
                            <div class="fs-12 text-muted">+16 FAQ bulan ini</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Menunggu validasi -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">12</div>
                            <div class="fs-12" style="color: #DC2626;">4 pengajuan lebih dari 3 hari</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Pengguna internal aktif -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #ECFDF5; color: #059669;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Pengguna internal aktif</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">18</div>
                            <div class="fs-12 text-muted">9 penulis &middot; 6 validator &middot; 3 admin</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Pencarian minggu ini -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FFFBEB; color: #D97706;">
                            <i class="bi bi-search"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Pencarian minggu ini</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">1.260</div>
                            <div class="fs-12 text-muted">
                                <span style="color: #059669; font-weight: 600;">&uarr; 14%</span> dari minggu sebelumnya
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Section: 3 Cards (Line Chart, Donut Chart, Quick Actions) -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Column 1: Trend Pencarian Mahasiswa -->
                <div class="col-12 col-xl-6">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h2 class="fw-bold fs-15 text-dark mb-1">Apa yang dicari mahasiswa?</h2>
                                    <div class="text-muted fs-12">1.260 pencarian dalam 7 hari terakhir</div>
                                </div>
                            </div>
                            
                            <!-- Line Chart Container -->
                            <div class="position-relative" style="height: 180px; width: 100%;">
                                <canvas id="searchTrendChart"></canvas>
                            </div>
                        </div>

                        <!-- Footer Tags -->
                        <div class="pt-3 mt-2 border-top border-light fs-12 text-muted">
                            Paling dicari: <strong style="color: var(--brand-maroon);">KRS &middot; Pembayaran kuliah &middot; Cuti akademik</strong>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Kesehatan Konten -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-3">Kesehatan konten</h2>
                            
                            <!-- Donut Chart & Legend -->
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <!-- Donut Canvas with Center Text -->
                                <div class="position-relative" style="width: 125px; height: 125px; flex-shrink: 0;">
                                    <canvas id="contentHealthChart"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                                        <div class="fw-bold fs-15 text-dark lh-1">248</div>
                                        <div class="fs-11 text-muted" style="font-size: 0.65rem !important;">Total FAQ</div>
                                    </div>
                                </div>

                                <!-- Breakdown Legend -->
                                <div class="flex-grow-1 ps-2">
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #236B64;"></span>
                                            <span class="text-secondary">Terbit</span>
                                        </div>
                                        <strong class="text-dark">180</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #1E293B;"></span>
                                            <span class="text-secondary">Draft</span>
                                        </div>
                                        <strong class="text-dark">40</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #DC2626;"></span>
                                            <span class="text-secondary">Validasi</span>
                                        </div>
                                        <strong class="text-dark">12</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #B45309;"></span>
                                            <span class="text-secondary">Revisi</span>
                                        </div>
                                        <strong class="text-dark">16</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Green Notice Box -->
                        <div class="rounded-3 p-2 d-flex align-items-center gap-2" style="background-color: #ECFDF5; border: 1px solid #D1FAE5;">
                            <i class="bi bi-check-circle text-success fs-5"></i>
                            <div class="fs-11" style="color: #065F46; line-height: 1.35;">
                                180 FAQ terbit telah melewati validasi sumber resmi.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Tindakan Cepat -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-3">Tindakan cepat</h2>

                            <!-- Action Item 1 -->
                            <a href="#" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EFF6FF; color: #2563EB;">
                                        <i class="bi bi-person-plus fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Kelola pengguna</div>
                                        <div class="fs-11 text-muted">18 akun internal</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>

                            <!-- Action Item 2 -->
                            <a href="#" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EEF2FF; color: #4F46E5;">
                                        <i class="bi bi-key fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Atur hak akses</div>
                                        <div class="fs-11 text-muted">3 peran pengelola</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>

                            <!-- Action Item 3 -->
                            <a href="#" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #F0FDF4; color: #0D9488;">
                                        <i class="bi bi-journal-check fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Kelola seluruh FAQ</div>
                                        <div class="fs-11 text-muted">248 pengetahuan kampus</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>
                        </div>

                        <!-- Footer Note -->
                        <div class="fs-11 text-muted pt-2">
                            Semua akses tercatat dalam riwayat.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Aktivitas Terbaru Table -->
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="fw-bold fs-15 text-dark mb-0">Aktivitas terbaru</h2>
                    <a href="#" class="fs-13 fw-semibold text-decoration-none" style="color: var(--brand-maroon);">
                        Lihat seluruh aktivitas &rarr;
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table activity-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 16%;">WAKTU</th>
                                <th style="width: 20%;">PENGGUNA</th>
                                <th style="width: 20%;">AKTIVITAS</th>
                                <th style="width: 32%;">DETAIL</th>
                                <th style="width: 12%; text-align: right;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 10.42</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Ratna Kusuma</div>
                                    <div class="fs-11 text-muted">Validator</div>
                                </td>
                                <td class="text-dark fs-13">Menerbitkan FAQ</td>
                                <td class="text-secondary fs-13">Prosedur pengajuan cuti akademik</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-terbit">Terbit</span>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 10.18</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Nadia Putri</div>
                                    <div class="fs-11 text-muted">Data Entry</div>
                                </td>
                                <td class="text-dark fs-13">Mengajukan FAQ</td>
                                <td class="text-secondary fs-13">Persyaratan yudisium Fakultas Teknik</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-validasi">Validasi</span>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 09.56</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Dimas Pratama</div>
                                    <div class="fs-11 text-muted">Super Admin</div>
                                </td>
                                <td class="text-dark fs-13">Memperbarui akses</td>
                                <td class="text-secondary fs-13">Hak Validator untuk Sari Wulandari</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-selesai">Selesai</span>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 09.31</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Ratna Kusuma</div>
                                    <div class="fs-11 text-muted">Validator</div>
                                </td>
                                <td class="text-dark fs-13">Meminta revisi</td>
                                <td class="text-secondary fs-13">Batas pembayaran UKT semester genap</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-revisi">Revisi</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Charts Initialization Scripts -->
<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Line Chart: Apa yang dicari mahasiswa? ---
        const ctxLine = document.getElementById('searchTrendChart').getContext('2d');
        
        // Gradient fill for area under curve
        const gradientFill = ctxLine.createLinearGradient(0, 0, 0, 180);
        gradientFill.addColorStop(0, 'rgba(133, 23, 44, 0.16)');
        gradientFill.addColorStop(1, 'rgba(133, 23, 44, 0.00)');

        // Custom plugin to draw the "260" pill marker at the peak/last data point
        const peakCalloutPlugin = {
            id: 'peakCallout',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const meta = chart.getDatasetMeta(0);
                const lastPoint = meta.data[meta.data.length - 1]; // 21 Okt point

                if (!lastPoint) return;

                const x = lastPoint.x;
                const y = lastPoint.y;
                const text = '260';

                ctx.save();
                ctx.font = '600 11px "Plus Jakarta Sans", sans-serif';
                const textWidth = ctx.measureText(text).width;
                const pillWidth = textWidth + 14;
                const pillHeight = 20;
                const pillX = x - pillWidth / 2 - 10;
                const pillY = y - 28;

                // Draw pill background
                ctx.fillStyle = '#0F172A';
                ctx.beginPath();
                ctx.roundRect(pillX, pillY, pillWidth, pillHeight, 10);
                ctx.fill();

                // Draw pill text
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(text, pillX + pillWidth / 2, pillY + pillHeight / 2);

                // Small dot on the point
                ctx.fillStyle = '#85172C';
                ctx.beginPath();
                ctx.arc(x, y, 4, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.restore();
            }
        };

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['15 Okt', '16 Okt', '17 Okt', '18 Okt', '19 Okt', '20 Okt', '21 Okt'],
                datasets: [{
                    label: 'Pencarian',
                    data: [130, 168, 155, 220, 190, 205, 260],
                    borderColor: '#85172C',
                    borderWidth: 2.5,
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.35,
                    pointRadius: [0, 0, 0, 0, 0, 0, 0],
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#85172C',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 4,
                        right: 16,
                        top: 24,
                        bottom: 0
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 8,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#94A3B8',
                            font: { size: 11, family: 'Plus Jakarta Sans' }
                        }
                    },
                    y: {
                        min: 0,
                        max: 300,
                        ticks: {
                            stepSize: 100,
                            color: '#94A3B8',
                            font: { size: 11, family: 'Plus Jakarta Sans' }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        },
                        border: { display: false }
                    }
                }
            },
            plugins: [peakCalloutPlugin]
        });

        // --- 2. Donut Chart: Kesehatan Konten ---
        const ctxDonut = document.getElementById('contentHealthChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Terbit', 'Draft', 'Validasi', 'Revisi'],
                datasets: [{
                    data: [180, 40, 12, 16],
                    backgroundColor: [
                        '#236B64', // Terbit (dark emerald / teal)
                        '#1E293B', // Draft (navy)
                        '#DC2626', // Validasi (red)
                        '#B45309'  // Revisi (amber)
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 8,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} FAQ`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>

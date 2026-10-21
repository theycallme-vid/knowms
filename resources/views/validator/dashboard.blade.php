<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Validator Dashboard</title>

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
            min-height: 100%;
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

        /* Date Tag */
        .date-badge {
            color: #2E7D32;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
        }

        /* Queue Item Card */
        .queue-item {
            border-radius: 12px;
            padding: 0.875rem;
            margin-bottom: 0.625rem;
            text-decoration: none;
            display: block;
            color: inherit;
            transition: all 0.15s ease;
            border: 1px solid #F1F5F9;
            background-color: #FFFFFF;
        }

        .queue-item:hover {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .queue-item.active {
            background-color: #FFF5F5;
            border-color: #FECACA;
        }

        /* Checklist badge */
        .checklist-icon {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            background-color: #059669;
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        /* Primary Maroon Button */
        .btn-maroon {
            background-color: var(--brand-maroon);
            color: #FFFFFF;
            border: none;
            border-radius: 9999px;
            padding: 0.625rem 1.25rem;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            text-decoration: none;
        }

        .btn-maroon:hover {
            background-color: var(--brand-maroon-hover);
            color: #FFFFFF;
        }

        /* Secondary Pill Button */
        .btn-pill-light {
            background-color: #FFFFFF;
            color: var(--text-heading);
            border: 1px solid #E2E8F0;
            border-radius: 9999px;
            padding: 0.625rem 1.25rem;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            text-decoration: none;
        }

        .btn-pill-light:hover {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
            color: var(--text-heading);
        }

        @media (min-width: 992px) {
            .border-end-lg {
                border-right: 1px solid #F1F5F9;
            }
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
                <span class="role-pill">Ruang Validator</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="#" class="nav-link-custom active">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link-custom ">
                    <i class="bi bi-card-checklist"></i>
                    <span>Antrean validasi</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat ulasan</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber resmi</span>
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
                    <strong class="text-dark">Validator</strong>
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
                    <div class="avatar-circle">RK</div>
                    <div class="d-none d-sm-block text-start lh-sm">
                        <div class="fw-bold fs-13 text-dark">Ratna Kusuma</div>
                        <div class="fs-11 text-muted">Validator</div>
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
                    <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                    <h1 class="display-serif fs-1 mb-1">Pastikan setiap jawaban layak dipercaya.</h1>
                    <p class="text-muted fs-14 mb-0">Tinjau substansi, verifikasi sumber, dan bantu penulis menyempurnakan pengetahuan.</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary bg-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-2xs text-dark fs-13 fw-semibold" style="border-color: #E2E8F0;">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat ulasan</span>
                    </button>
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
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">12</div>
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
                                <div class="text-muted fs-12">12 FAQ menunggu penelaahan</div>
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
                                <a href="#" class="queue-item active">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fs-11 fw-semibold" style="color: #DC2626;">4 hari menunggu</span>
                                        <i class="bi bi-record-circle" style="color: #DC2626; font-size: 0.75rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Persyaratan pengajuan cuti akademik</div>
                                    <div class="fs-11 text-muted">Nadia Putri &middot; 17 Okt 2026</div>
                                </a>

                                <!-- Item 2 -->
                                <a href="#" class="queue-item">
                                    <div class="fs-11 text-muted mb-1">4 hari menunggu</div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Ketentuan pengambilan ulang mata kuliah</div>
                                    <div class="fs-11 text-muted">Arif Setiawan &middot; 17 Okt 2026</div>
                                </a>

                                <!-- Item 3 -->
                                <a href="#" class="queue-item">
                                    <div class="fs-11 text-muted mb-1">3 hari menunggu</div>
                                    <div class="fw-bold fs-13 text-dark mb-1">Permohonan legalisasi transkrip nilai</div>
                                    <div class="fs-11 text-muted">Sinta Dewi &middot; 18 Okt 2026</div>
                                </a>
                            </div>
                        </div>

                        <!-- Queue List Footer & Notice -->
                        <div>
                            <div class="d-flex align-items-center justify-content-between pt-2 pb-2 text-muted fs-11">
                                <span>1-3 dari 12 pengajuan</span>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none"><i class="bi bi-chevron-left"></i></button>
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>

                            <!-- Notification Box -->
                            <div class="rounded-3 p-2.5 d-flex align-items-start gap-2 mt-2" style="background-color: #FFFBEB; border: 1px solid #FEF3C7;">
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
                                    <a href="#" class="fs-12 fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="color: #2563EB;">
                                        <span>Lihat perubahan versi</span>
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
                                        <button class="btn-maroon shadow-2xs">
                                            <i class="bi bi-check2"></i>
                                            <span>Setujui & terbitkan</span>
                                        </button>
                                        <button class="btn-pill-light shadow-2xs">
                                            <i class="bi bi-chat-left-text"></i>
                                            <span>Minta revisi</span>
                                        </button>
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

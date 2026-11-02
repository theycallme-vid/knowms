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

    <style>
        :root {
            --app-bg:              #FAF9F5;
            --sidebar-bg:          #FAF9F5;
            --card-bg:             #FFFFFF;
            --card-border:         #EAEAE4;
            --brand-maroon:        #85172C;
            --brand-maroon-hover:  #701023;
            --brand-maroon-soft:   #FCEEEF;
            --text-heading:        #171A1F;
            --text-body:           #334155;
            --text-muted:          #64748B;
            --text-subtle:         #94A3B8;
            --font-sans:           'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif:          'Lora', Georgia, serif;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--app-bg);
            color: var(--text-body);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Typography ── */
        .display-serif { font-family: var(--font-serif); font-weight: 600; letter-spacing: -0.01em; color: var(--text-heading); }
        .fs-11 { font-size: 0.6875rem !important; }
        .fs-12 { font-size: 0.75rem   !important; }
        .fs-13 { font-size: 0.8125rem !important; }
        .fs-14 { font-size: 0.875rem  !important; }
        .fs-15 { font-size: 0.9375rem !important; }
        .fs-20 { font-size: 1.25rem   !important; }
        .fs-24 { font-size: 1.5rem    !important; }

        /* ── Sidebar ── */
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
            padding: 1.5rem 1.25rem 1.25rem;
            z-index: 1020;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .sidebar::-webkit-scrollbar      { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #E2E8F0; border-radius: 4px; }

        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background-color: #0F172A;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; flex-shrink: 0;
        }
        .role-pill {
            display: inline-block;
            background-color: #ECEFE8;
            color: #475569;
            font-size: 0.72rem; font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-top: 1rem; margin-bottom: 1.25rem;
        }
        .nav-link-custom {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: 12px;
            color: #475569;
            font-size: 0.875rem; font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.2rem;
        }
        .nav-link-custom:hover { background-color: #F1F4F8; color: var(--text-heading); }
        .nav-link-custom.active { background-color: var(--brand-maroon-soft); color: var(--brand-maroon); font-weight: 600; }
        .nav-link-custom i { font-size: 1.05rem; }

        /* ── Top Navbar ── */
        .top-navbar {
            padding: 1.25rem 2rem 0.75rem;
            background-color: var(--app-bg);
            border-bottom: 1px solid var(--card-border);
        }
        .search-container { position: relative; width: 340px; }
        .search-container i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.875rem; }
        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 9999px;
            border: 1px solid #E2E8F0;
            background-color: #fff;
            font-size: 0.8125rem; outline: none;
            transition: all 0.2s ease;
        }
        .search-input:focus { border-color: var(--brand-maroon); box-shadow: 0 0 0 3px rgba(133,23,44,0.1); }
        .search-input::placeholder { color: var(--text-subtle); }

        .avatar-circle {
            width: 36px; height: 36px; border-radius: 50%;
            background-color: #EDE4D8; color: #795535;
            font-weight: 700; font-size: 0.8125rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        /* ── Content ── */
        .content-area { padding: 1.5rem 2rem 3rem; }

        /* ── Stat Cards ── */
        .stat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.1rem 1.25rem;
            display: flex; align-items: center; gap: 1rem;
            transition: box-shadow 0.2s ease;
        }
        .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .stat-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }

        /* ── Main Table Card ── */
        .table-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            overflow: hidden;
        }
        .table-card-header {
            padding: 1.25rem 1.5rem 0;
            border-bottom: 1px solid var(--card-border);
        }

        /* ── Fakultas Tabs ── */
        .fakultas-tabs {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
            padding: 0 1.5rem;
            margin-top: 1rem;
            border-bottom: 1px solid var(--card-border);
        }
        .fakultas-tab {
            font-size: 0.8125rem; font-weight: 500;
            color: var(--text-muted);
            padding: 0.55rem 0.9rem;
            border: none; background: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .fakultas-tab:hover { color: var(--text-heading); }
        .fakultas-tab.active {
            color: var(--brand-maroon);
            border-bottom-color: var(--brand-maroon);
            font-weight: 600;
        }

        /* ── Filter Bar ── */
        .filter-bar {
            padding: 0.875rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
            border-bottom: 1px solid #F1F5F9;
        }
        .filter-search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 340px; }
        .filter-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; }
        .filter-search-wrap input {
            width: 100%;
            padding: 0.45rem 1rem 0.45rem 2rem;
            border: 1px solid #E2E8F0;
            border-radius: 9999px;
            font-size: 0.8125rem;
            outline: none;
            transition: all 0.2s ease;
        }
        .filter-search-wrap input:focus { border-color: var(--brand-maroon); box-shadow: 0 0 0 3px rgba(133,23,44,0.08); }

        .filter-select {
            padding: 0.45rem 2rem 0.45rem 0.85rem;
            border: 1px solid #E2E8F0;
            border-radius: 9999px;
            font-size: 0.8125rem;
            background-color: #fff;
            color: var(--text-body);
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%2364748b' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            transition: all 0.2s ease;
        }
        .filter-select:focus { border-color: var(--brand-maroon); box-shadow: 0 0 0 3px rgba(133,23,44,0.08); }

        .btn-filter-submit {
            background-color: var(--brand-maroon);
            color: #fff;
            border: none;
            border-radius: 9999px;
            padding: 0.45rem 1.1rem;
            font-size: 0.8125rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 0.4rem;
            cursor: pointer; transition: background-color 0.2s ease;
        }
        .btn-filter-submit:hover { background-color: var(--brand-maroon-hover); }
        .btn-filter-reset {
            color: var(--text-muted);
            border: 1px solid #E2E8F0;
            background-color: #fff;
            border-radius: 9999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.8125rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: 0.35rem;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-filter-reset:hover { background-color: #F8FAFC; color: var(--text-heading); }

        /* ── Table ── */
        .faq-table { width: 100%; border-collapse: collapse; }
        .faq-table thead tr { border-bottom: 1px solid #F1F5F9; }
        .faq-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em;
            text-transform: uppercase; color: var(--text-muted);
            text-align: left;
        }
        .faq-table tbody tr {
            border-bottom: 1px solid #F8FAFC;
            transition: background-color 0.12s ease;
        }
        .faq-table tbody tr:hover { background-color: #FAFBFD; }
        .faq-table tbody tr:last-child { border-bottom: none; }
        .faq-table td { padding: 0.9rem 1rem; vertical-align: middle; }

        .faq-code {
            font-size: 0.75rem; font-weight: 700;
            color: var(--text-muted);
            font-family: monospace;
            white-space: nowrap;
        }
        .faq-version {
            font-size: 0.68rem;
            color: var(--text-subtle);
        }
        .faq-question {
            font-size: 0.875rem; font-weight: 600;
            color: var(--text-heading); line-height: 1.4;
        }
        .faq-tags {
            display: flex; flex-wrap: wrap; gap: 0.3rem;
            margin-top: 0.3rem;
        }
        .tag-pill {
            font-size: 0.68rem; font-weight: 500;
            background-color: #F1F5F9; color: #475569;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
        }

        /* Status badges */
        .badge-status {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.72rem; font-weight: 600;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }
        .badge-approved  { background-color: #ECFDF5; color: #059669; }
        .badge-active    { background-color: #EFF6FF; color: #2563EB; }
        .badge-submitted { background-color: #FFFBEB; color: #D97706; }
        .badge-draft     { background-color: #F8FAFC; color: #64748B; }
        .badge-rejected  { background-color: #FEF2F2; color: #DC2626; }
        .badge-inactive  { background-color: #F1F5F9; color: #94A3B8; }

        /* Data Entry avatar */
        .de-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background-color: #EDE4D8; color: #795535;
            font-weight: 700; font-size: 0.65rem;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* Action buttons */
        .btn-action {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem; font-weight: 600;
            border-radius: 9999px; border: 1px solid;
            cursor: pointer; transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-action-view   { background-color: #EFF6FF; color: #2563EB; border-color: #DBEAFE; }
        .btn-action-view:hover { background-color: #DBEAFE; }
        .btn-action-approve { background-color: #ECFDF5; color: #059669; border-color: #A7F3D0; }
        .btn-action-approve:hover { background-color: #D1FAE5; }
        .btn-action-reject  { background-color: #FEF2F2; color: #DC2626; border-color: #FECACA; }
        .btn-action-reject:hover { background-color: #FEE2E2; }

        /* ── Pagination ── */
        .pagination-bar {
            padding: 0.85rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            border-top: 1px solid #F1F5F9;
            font-size: 0.8125rem; color: var(--text-muted);
        }
        .page-btn {
            width: 30px; height: 30px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.8125rem; font-weight: 600;
            border: 1px solid #E2E8F0;
            background-color: #fff;
            color: var(--text-body);
            cursor: pointer; text-decoration: none;
            transition: all 0.15s ease;
        }
        .page-btn:hover { background-color: #F1F5F9; color: var(--text-heading); }
        .page-btn.active { background-color: var(--brand-maroon); color: #fff; border-color: var(--brand-maroon); }
        .page-btn.disabled { opacity: 0.4; pointer-events: none; }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .sidebar { position: fixed; left: -260px; transition: left 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
            .sidebar.show { left: 0; }
            .top-navbar { padding: 1rem; }
            .content-area { padding: 1rem 1rem 2rem; }
            .search-container { width: 100%; max-width: 260px; }
        }
        @media (max-width: 767.98px) {
            .faq-table thead { display: none; }
            .faq-table td { display: block; padding: 0.4rem 1rem; }
            .faq-table td:first-child { padding-top: 0.9rem; }
            .faq-table td:last-child { padding-bottom: 0.9rem; }
            .fakultas-tabs { padding: 0 1rem; gap: 0.2rem; }
            .filter-bar { padding: 0.75rem 1rem; }
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
            <!-- Brand -->
            <div class="d-flex align-items-center gap-2">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-15 text-dark lh-sm">KMS</div>
                    <div class="fs-11 text-muted">Knowledge Management System</div>
                </div>
            </div>

            <!-- Role Pill: dinamis sesuai role user yang login -->
            @php
                $authUser = auth()->user();
                // Pastikan relasi role ter-load
                if (!$authUser->relationLoaded('role')) {
                    $authUser->load('role');
                }
                $roleName = $authUser->getRoleName();
            @endphp
            <div><span class="role-pill">Ruang {{ $roleName ?: 'User' }}</span></div>

            <!-- Navigation: tampilkan nav sesuai role -->
            <nav class="nav flex-column">
                @if($roleName === 'Super Admin')
                    {{-- Super Admin: pakai nav superadmin --}}
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link-custom">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom active">
                        <i class="bi bi-list-check"></i>
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
                        <i class="bi bi-list-check"></i>
                        <span>Daftar FAQ</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-card-checklist"></i>
                        <span>Antrean Validasi</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Ulasan</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-book"></i>
                        <span>Sumber Resmi</span>
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
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link-custom text-secondary" style="cursor:pointer;">
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
                    <span>{{ auth()->user()->getRoleName() }}</span>
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
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'V', 0, 2)) }}
                    </div>
                    <div class="d-none d-sm-block text-start lh-sm">
                        <div class="fw-bold fs-13 text-dark">{{ auth()->user()->nama ?? '-' }}</div>
                        <div class="fs-11 text-muted">{{ auth()->user()->getRoleName() }}</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-area">

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <div class="fs-11 fw-bold text-muted text-uppercase mb-1" style="letter-spacing:0.07em;">
                        RUANG PENELAAH &middot; VALIDATOR
                    </div>
                    <h1 class="display-serif fs-20 fw-bold mb-1">Daftar FAQ</h1>
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

            <!-- Stat Cards -->
            @php
                $aktivFakultas = request('fakultas', 'Semua');
                $labelFakultas = ($aktivFakultas && $aktivFakultas !== 'Semua') ? $aktivFakultas : 'Semua Fakultas';
            @endphp
            <div class="row g-3 mb-4">
                {{-- Total FAQ --}}
                <div class="col-6 col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Total FAQ</div>
                            <div class="fs-24 fw-bold text-dark lh-1">{{ $totalFaq }}</div>
                            <div class="fs-11 text-muted mt-1">{{ $labelFakultas }}</div>
                        </div>
                    </div>
                </div>

                {{-- Aktif --}}
                <div class="col-6 col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Aktif</div>
                            <div class="fs-24 fw-bold text-dark lh-1">{{ $totalAktif }}</div>
                            <div class="fs-11 text-muted mt-1">Sudah tayang ke mahasiswa</div>
                        </div>
                    </div>
                </div>

                {{-- Approved (sudah divalidasi, belum aktif) --}}
                <div class="col-6 col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color:#EDE9FE; color:#7C3AED;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Approved</div>
                            <div class="fs-24 fw-bold text-dark lh-1">{{ $totalApproved }}</div>
                            <div class="fs-11 text-muted mt-1">Disetujui, belum aktif</div>
                        </div>
                    </div>
                </div>

                {{-- Menunggu Validasi --}}
                <div class="col-6 col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color:#FFFBEB; color:#D97706;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu Validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1">{{ $totalSubmitted }}</div>
                            <div class="fs-11 text-muted mt-1">Perlu ditinjau</div>
                        </div>
                    </div>
                </div>

                {{-- Nonaktif --}}
                <div class="col-6 col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color:#FEF2F2; color:#DC2626;">
                            <i class="bi bi-slash-circle"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Nonaktif</div>
                            <div class="fs-24 fw-bold text-dark lh-1">{{ $totalInaktif }}</div>
                            <div class="fs-11 text-muted mt-1">Dinonaktifkan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Card Header Title -->
                <div class="table-card-header pb-0">
                    <div class="d-flex align-items-center justify-content-between pb-3">
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-0">Validasi FAQ</h2>
                            <div class="fs-12 text-muted">
                                @if(request('fakultas') && request('fakultas') !== 'Semua')
                                    Menampilkan FAQ — Fakultas <strong>{{ request('fakultas') }}</strong>
                                @else
                                    Semua Fakultas
                                @endif
                            </div>
                        </div>
                        @if($faqs->total() > 0)
                            <span class="badge rounded-pill fw-medium fs-12 px-3 py-1"
                                  style="background-color:#DBEAFE; color:#1D4ED8;">
                                {{ $faqs->total() }} FAQ ditemukan
                            </span>
                        @endif
                    </div>
                </div>

                <!-- ── Fakultas Tab Filter ── -->
                <div class="fakultas-tabs">
                    @php $aktivFakultas = request('fakultas', 'Semua'); @endphp

                    <a href="{{ request()->fullUrlWithQuery(['fakultas' => 'Semua', 'page' => 1]) }}"
                       class="fakultas-tab {{ $aktivFakultas === 'Semua' ? 'active' : '' }}">
                        Semua
                    </a>

                    @foreach($fakultasOptions as $fak)
                        <a href="{{ request()->fullUrlWithQuery(['fakultas' => $fak, 'page' => 1]) }}"
                           class="fakultas-tab {{ $aktivFakultas === $fak ? 'active' : '' }}">
                            {{ $fak }}
                        </a>
                    @endforeach
                </div>

                <!-- ── Filter Bar ── -->
                <form method="GET" action="{{ route('validator.daftarFAQ') }}" id="filterForm">
                    {{-- Simpan state tab fakultas yang aktif ke dalam form --}}
                    <input type="hidden" name="fakultas" value="{{ $aktivFakultas }}">

                    <div class="filter-bar">
                        <!-- Search -->
                        <div class="filter-search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari kode atau pertanyaan FAQ...">
                        </div>

                        <!-- Filter Status -->
                        <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="Semua" {{ request('status','Semua') === 'Semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Menunggu Validasi</option>
                            <option value="Approved"  {{ request('status') === 'Approved'  ? 'selected' : '' }}>Approved (Belum Aktif)</option>
                            <option value="Active"    {{ request('status') === 'Active'    ? 'selected' : '' }}>Aktif</option>
                            <option value="Rejected"  {{ request('status') === 'Rejected'  ? 'selected' : '' }}>Rejected</option>
                            <option value="InActive"  {{ request('status') === 'InActive'  ? 'selected' : '' }}>Nonaktif</option>
                        </select>

                        <!-- Sort -->
                        <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="terbaru" {{ request('sort','terbaru') === 'terbaru' ? 'selected' : '' }}>&#8645; Terbaru</option>
                            <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>&#8645; Terlama</option>
                            <option value="az"      {{ request('sort') === 'az'      ? 'selected' : '' }}>A &rarr; Z</option>
                        </select>

                        <button type="submit" class="btn-filter-submit">
                            <i class="bi bi-funnel"></i> Filter
                        </button>

                        @if(request()->hasAny(['search', 'status', 'sort']) && collect(request()->only(['search','status','sort']))->filter(fn($v) => $v && $v !== 'Semua' && $v !== 'terbaru')->isNotEmpty())
                            <a href="{{ route('validator.daftarFAQ', ['fakultas' => $aktivFakultas]) }}"
                               class="btn-filter-reset">
                                <i class="bi bi-x-lg"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>

                <!-- ── Table ── -->
                <div class="table-responsive">
                    <table class="faq-table">
                        <thead>
                            <tr>
                                <th style="width:110px;">Kode / Versi</th>
                                <th>Pertanyaan</th>
                                <th style="width:160px;">Data Entry</th>
                                <th style="width:130px;">Waktu Pengajuan</th>
                                <th style="width:130px;">Status</th>
                                <th style="width:180px;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                @php
                                    $latestVersion = $faq->versions->first();
                                    $status        = $latestVersion?->status ?? 'Draft';
                                    $versionCount  = $faq->versions->count();
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
                                        <div class="faq-question">{{ Str::limit($faq->pertanyaan, 80) }}</div>
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
                                            {{ optional($faq->created_at)->format('H:i') }} WIB
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
                                            <a href="#" class="btn-action btn-action-view">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            @if($status === 'Submitted')
                                                <a href="#" class="btn-action btn-action-approve">
                                                    <i class="bi bi-check-lg"></i> Setujui
                                                </a>
                                                <a href="#" class="btn-action btn-action-reject">
                                                    <i class="bi bi-x-lg"></i> Tolak
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    function confirmLogout() {
        if (confirm('Yakin ingin keluar dari sistem?')) {
            document.getElementById('logout-form').submit();
        }
    }
</script>
</body>
</html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KMS - Pengguna & Hak Akses (Super Admin)</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Super Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/superadmin/style.css') }}">
</head>
<body>

@php
    // Inisialisasi data pengguna dan peran dari database (atau fallback jika belum dimigrate)
    $allRoles = isset($roles) && count($roles) > 0 ? $roles : [
        (object)['id' => 1, 'nama' => 'Super Admin'],
        (object)['id' => 2, 'nama' => 'Data Entry'],
        (object)['id' => 3, 'nama' => 'Validator'],
    ];

    $userList = isset($users) && count($users) > 0 ? $users : [
        (object)[
            'id' => 1,
            'nama' => 'Dimas Pratama (Admin KMS)',
            'email' => 'adminkms@gmail.com',
            'npk' => 'ADM-2024-001',
            'tRoles_id' => 1,
            'role' => (object)['id' => 1, 'nama' => 'Super Admin'],
            'is_active' => true,
            'created_at' => now()->subMonths(6),
            'updated_at' => now()->subDays(2),
        ],
        (object)[
            'id' => 2,
            'nama' => 'Siti Rahmawati',
            'email' => 'siti.rahma@campus.ac.id',
            'npk' => 'DTE-2024-012',
            'tRoles_id' => 2,
            'role' => (object)['id' => 2, 'nama' => 'Data Entry'],
            'is_active' => true,
            'created_at' => now()->subMonths(4),
            'updated_at' => now()->subDays(5),
        ],
        (object)[
            'id' => 3,
            'nama' => 'Dr. Hendra Wijaya',
            'email' => 'hendra.wijaya@campus.ac.id',
            'npk' => 'VAL-2024-003',
            'tRoles_id' => 3,
            'role' => (object)['id' => 3, 'nama' => 'Validator'],
            'is_active' => true,
            'created_at' => now()->subMonths(5),
            'updated_at' => now()->subDay(),
        ],
        (object)[
            'id' => 4,
            'nama' => 'Anisa Maharani',
            'email' => 'anisa.m@campus.ac.id',
            'npk' => 'DTE-2024-018',
            'tRoles_id' => 2,
            'role' => (object)['id' => 2, 'nama' => 'Data Entry'],
            'is_active' => true,
            'created_at' => now()->subMonths(3),
            'updated_at' => now()->subDays(7),
        ],
        (object)[
            'id' => 5,
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@campus.ac.id',
            'npk' => 'VAL-2024-008',
            'tRoles_id' => 3,
            'role' => (object)['id' => 3, 'nama' => 'Validator'],
            'is_active' => false,
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subDays(10),
        ],
    ];

    $totalUsers = count($userList);
    $countSuperAdmin = collect($userList)->where('tRoles_id', 1)->count();
    $countDataEntry = collect($userList)->where('tRoles_id', 2)->count();
    $countValidator = collect($userList)->where('tRoles_id', 3)->count();
    $countNonaktif = collect($userList)->where('is_active', false)->count();

    // Akun yang sedang aktif login (default ID 1 untuk demonstrasi guard)
    $currentAdminId = auth()->id() ?? 1;
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
                <span class="role-pill">Ruang Super Admin</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="/superadmin/dashboard" class="nav-link-custom">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="/superadmin/pengguna-akses" class="nav-link-custom active">
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

            <!-- Account Settings & Logout -->
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan akun</span>
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
                    <span class="mx-2 text-secondary">/</span>
                    <strong class="text-dark">Pengguna & Hak Akses</strong>
                </div>
            </div>

            <!-- Right: Search, Notification & Profile -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" id="topSearchBar" class="search-input" placeholder="Cari nama, email, atau NPK...">
                </div>

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-1 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">Notifikasi baru</span>
                    </span>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->nama ?? 'SA', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Super Admin' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()->getRoleName() ?? 'Super Admin' }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Super Admin' }}</div>
                                <div class="fs-11 text-muted">{{ Auth::user()->email ?? '' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 d-flex align-items-center gap-2" href="#">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan akun
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
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">

            <!-- Flash Alerts -->
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-start gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger mt-0.5"></i>
                    <div class="fs-13 text-dark">
                        <strong class="d-block mb-1">Gagal menyimpan pengguna:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">KAMIS, 22 OKTOBER 2026</div>
                    <h1 class="display-serif fs-1 mb-1">Pengguna & Hak Akses</h1>
                    <p class="text-muted fs-14 mb-0">Kelola akun staf kampus, penugasan peran (role), dan kontrol wewenang dengan proteksi keamanan ketat.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-maroon rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm fs-13" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Tambah Pengguna</span>
                    </button>
                </div>
            </div>


            <!-- Table Container Card -->
            <div class="card-custom">
                <!-- Toolbar: Filter Pills & Search -->
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                    <!-- Pilihan Dropdown Pengguna/Role -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="roleSelectFilter" class="fs-12 fw-semibold text-muted text-nowrap d-none d-sm-inline">
                            <i class="bi bi-funnel me-1"></i>Pilih Pengguna:
                        </label>
                        <div class="position-relative" style="min-width: 260px;">
                            <select id="roleSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-13 fw-semibold text-dark shadow-sm border-secondary-subtle" onchange="filterByRole(this.value)">
                                <option value="all" selected>Semua Pengguna ({{ $totalUsers }})</option>
                                @foreach($allRoles as $r)
                                    <option value="{{ $r->nama }}">
                                        {{ $r->nama }} ({{ collect($userList)->where('tRoles_id', $r->id)->count() }})
                                    </option>
                                @endforeach
                                <option value="nonaktif">Pengguna Nonaktif ({{ $countNonaktif }})</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search & Secondary Filters -->
                    <div class="d-flex align-items-center gap-2 w-100 w-lg-auto justify-content-end">
                        <div class="search-container" style="max-width: 260px;">
                            <i class="bi bi-search"></i>
                            <input type="text" id="tableFilterInput" class="search-input" placeholder="Saring tabel pengguna...">
                        </div>
                        <select id="statusSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-12 border-secondary-subtle" style="width: 140px;" onchange="filterTableRows()">
                            <option value="all">Semua Status</option>
                            <option value="aktif">Hanya Aktif</option>
                            <option value="nonaktif">Hanya Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Table Header Info -->
                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                    <div class="fs-13 text-muted">
                        Menampilkan <strong id="visibleCount" class="text-dark">{{ count($userList) }}</strong> dari <strong>{{ count($userList) }}</strong> pengguna terdaftar
                    </div>
                    <div class="fs-12 text-muted">
                        <span class="badge-status-dot bg-success me-1"></span> Database: Tabel <code>tusers</code> & <code>troles</code>
                    </div>
                </div>

                <!-- Main Data Table -->
                <div class="table-responsive-custom">
                    <table class="table custom-user-table align-middle" id="userTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 27%;">INFORMASI PENGGUNA</th>
                                <th style="width: 13%;">NPK</th>
                                <th style="width: 18%;">PERAN / ROLE</th>
                                <th style="width: 12%;">STATUS AKUN</th>
                                <th style="width: 13%;">DIPERBARUI</th>
                                <th style="width: 12%; text-align: right;">AKSI & AKSES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userList as $index => $u)
                                @php
                                    $roleName = $u->role->nama ?? ($u->tRoles_id == 1 ? 'Super Admin' : ($u->tRoles_id == 2 ? 'Data Entry' : ($u->tRoles_id == 3 ? 'Validator' : 'Tanpa Role')));
                                    $initials = strtoupper(substr($u->nama, 0, 2));
                                    $isCurrentAdmin = ($u->id == $currentAdminId);

                                    // Avatar color styling by role
                                    $avatarBg = match($u->tRoles_id) {
                                        1 => 'background-color: #FCEEEF; color: #85172C;',
                                        2 => 'background-color: #CCFBF1; color: #0F766E;',
                                        3 => 'background-color: #DBEAFE; color: #1D4ED8;',
                                        default => 'background-color: #F1F5F9; color: #64748B;'
                                    };
                                @endphp
                                <tr class="user-row" 
                                    data-id="{{ $u->id }}"
                                    data-nama="{{ $u->nama }}"
                                    data-email="{{ $u->email }}"
                                    data-npk="{{ $u->npk ?? '' }}"
                                    data-role-id="{{ $u->tRoles_id }}"
                                    data-role-name="{{ $roleName }}"
                                    data-is-active="{{ $u->is_active ? '1' : '0' }}"
                                    data-is-current="{{ $isCurrentAdmin ? '1' : '0' }}">
                                    
                                    <!-- Index -->
                                    <td class="text-muted fw-semibold fs-12">{{ $index + 1 }}</td>

                                    <!-- User Info -->
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-user-table" style="{{ $avatarBg }}">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <div class="fw-bold fs-13 text-dark d-flex align-items-center gap-2">
                                                    <span>{{ $u->nama }}</span>
                                                    @if($isCurrentAdmin)
                                                        <span class="badge" style="background-color: #85172C; font-size: 0.65rem;">Akun Anda</span>
                                                    @endif
                                                </div>
                                                <div class="fs-12 text-muted">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- NPK -->
                                    <td>
                                        @if(!empty($u->npk))
                                            <span class="badge bg-light text-dark border font-monospace fs-11 px-2 py-1">
                                                {{ $u->npk }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-12">-</span>
                                        @endif
                                    </td>

                                    <!-- Role Pill -->
                                    <td>
                                        @if($u->tRoles_id == 1)
                                            <span class="badge-role badge-role-superadmin">
                                                <i class="bi bi-shield-fill-check"></i> Super Admin
                                            </span>
                                        @elseif($u->tRoles_id == 2)
                                            <span class="badge-role badge-role-dataentry">
                                                <i class="bi bi-pencil-fill"></i> Data Entry
                                            </span>
                                        @elseif($u->tRoles_id == 3)
                                            <span class="badge-role badge-role-validator">
                                                <i class="bi bi-patch-check-fill"></i> Validator
                                            </span>
                                        @else
                                            <span class="badge-role badge-role-none">
                                                <i class="bi bi-dash-circle"></i> Tanpa Role
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @if($u->is_active)
                                            <span class="badge-role badge-active">
                                                <span class="badge-status-dot bg-success"></span> Aktif
                                            </span>
                                        @else
                                            <span class="badge-role badge-inactive">
                                                <span class="badge-status-dot bg-secondary"></span> Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Date -->
                                    <td class="text-muted fs-12">
                                        <div>{{ isset($u->updated_at) ? \Carbon\Carbon::parse($u->updated_at)->isoFormat('D MMM Y') : '-' }}</div>
                                        <div class="fs-11 text-secondary">
                                            {{ isset($u->updated_at) ? \Carbon\Carbon::parse($u->updated_at)->format('H:i') . ' WIB' : '' }}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Edit Info Button -->
                                            <button type="button" 
                                                    class="btn-action-icon" 
                                                    title="Edit Keterangan Pengguna"
                                                    onclick="openEditModal(this)">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- Assign / Add Role Button -->
                                            <button type="button" 
                                                    class="btn-action-icon" 
                                                    title="Ubah / Tambah Peran (Role)"
                                                    onclick="openRoleModal(this)">
                                                <i class="bi bi-shield-plus"></i>
                                            </button>

                                            <!-- Remove Role Button (Strict Guarded) -->
                                            <button type="button" 
                                                    class="btn-action-icon btn-action-danger" 
                                                    title="Cabut Role (Syarat Ketat)"
                                                    onclick="openRemoveRoleModal(this)">
                                                <i class="bi bi-shield-x"></i>
                                            </button>

                                            <!-- Dropdown Options -->
                                            <div class="dropdown d-inline-block">
                                                <button class="btn-action-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi Lainnya">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border shadow-sm rounded-3 fs-13 py-1">
                                                    <li>
                                                        <form action="{{ route('superadmin.pengguna.toggleStatus', $u->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 {{ $isCurrentAdmin ? 'disabled text-muted' : '' }}">
                                                                <i class="bi bi-power {{ $u->is_active ? 'text-warning' : 'text-success' }}"></i>
                                                                <span>{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" onclick="copyEmail('{{ $u->email }}')">
                                                            <i class="bi bi-clipboard text-muted"></i>
                                                            <span>Salin Email Staf</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="stat-icon mx-auto mb-2" style="background-color: #F8FAFC; color: #94A3B8;">
                                            <i class="bi bi-people fs-4"></i>
                                        </div>
                                        <div class="fw-bold fs-14 text-dark">Belum ada pengguna terdaftar</div>
                                        <div class="text-muted fs-12 mb-3">Tambahkan pengguna baru untuk memberikan akses ke sistem KMS kampus.</div>
                                        <button type="button" class="btn btn-maroon btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                                            + Tambah Pengguna Pertama
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Empty State after filtering -->
                <div id="noDataFiltered" class="text-center py-5 d-none">
                    <i class="bi bi-search fs-2 text-muted mb-2 d-block"></i>
                    <div class="fw-bold fs-14 text-dark">Tidak ada data pengguna yang cocok</div>
                    <div class="text-muted fs-12">Coba sesuaikan kata kunci pencarian atau ganti filter peran.</div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: TAMBAH PENGGUNA BARU                                            -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahPenggunaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: var(--brand-maroon-soft); color: var(--brand-maroon);">
                        <i class="bi bi-person-plus-fill fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalTambahPenggunaLabel">Tambah Pengguna Baru</h2>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('superadmin.pengguna.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label for="create_nama" class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="create_nama" name="nama" required maxlength="100" placeholder="Contoh: Sarah Anindya, S.Kom">
                        <div class="form-text fs-11 text-muted">Sesuai nama identitas staf kampus.</div>
                    </div>

                    <!-- Email & NPK -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="create_email" class="form-label-custom">Email<span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" id="create_email" name="email" required maxlength="50" placeholder="nama@staff.ubaya.ac.id">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="create_npk" class="form-label-custom">NPK / ID Staf</label>
                            <input type="text" class="form-control form-control-custom font-monospace" id="create_npk" name="npk" maxlength="15" placeholder="202020">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="create_password" class="form-label-custom">Kata Sandi Awal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control form-control-custom" 
                                   id="create_password" 
                                   name="password" 
                                   required 
                                   minlength="8" 
                                   placeholder="Minimal 8 karakter"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('create_password', this)" title="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="fs-11 text-muted" id="create_password_msg">
                                <i class="bi bi-info-circle me-1"></i>Minimal 8 karakter.
                            </div>
                            <div class="fs-11 fw-semibold text-muted" id="create_password_counter">
                                0/8 karakter
                            </div>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label for="create_role" class="form-label-custom">Pemberian Role <span class="text-danger">*</span></label>
                        <select class="form-select form-select-custom" id="create_role" name="tRoles_id" required>
                            <option value="" disabled selected>-- Pilih Role --</option>
                            @foreach($allRoles as $r)
                                <option value="{{ $r->id }}">
                                    {{ $r->nama }} 
                                    @if($r->id == 1) (Akses Penuh Seluruh Sistem)
                                    @elseif($r->id == 2) (Input & Kelola Draf FAQ)
                                    @elseif($r->id == 3) (Verifikasi, Revisi & Penerbitan)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Switch -->
                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" role="switch" id="create_is_active" name="is_active" value="1" checked>
                        <label class="form-check-label fs-13 text-dark fw-medium" for="create_is_active">
                            Langsung Aktifkan Akun Pengguna
                        </label>
                        <div class="fs-11 text-muted">Pengguna dapat langsung masuk ke sistem setelah disimpan.</div>
                    </div>
                </div>

                <div class="modal-footer modal-footer-custom d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fs-13" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-maroon rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Pengguna</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: UPDATE KETERANGAN PENGGUNA                                      -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-labelledby="modalEditPenggunaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EFF6FF; color: #2563EB;">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalEditPenggunaLabel">Update Keterangan Pengguna</h2>
                        <div class="fs-12 text-muted">Perbarui profil dan status staf</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditPengguna" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id" name="id">

                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="edit_nama" name="nama" required maxlength="100">
                    </div>

                    <!-- Email & NPK -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="edit_email" class="form-label-custom">Alamat Email Kampus <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" id="edit_email" name="email" required maxlength="50">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="edit_npk" class="form-label-custom">NPK / ID Staf</label>
                            <input type="text" class="form-control form-control-custom font-monospace" id="edit_npk" name="npk" maxlength="15">
                        </div>
                    </div>

                    <!-- Password Baru (Opsional) -->
                    <div class="mb-3">
                        <label for="edit_password" class="form-label-custom">Ganti Kata Sandi (Opsional)</label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control form-control-custom" 
                                   id="edit_password" 
                                   name="password" 
                                   minlength="8" 
                                   placeholder="Minimal 8 karakter jika ingin mengganti"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('edit_password', this)" title="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text fs-11 text-muted">Biarkan kosong jika tidak ingin mengubah sandi. Jika diisi, minimal 8 karakter.</div>
                    </div>

                    <!-- Status Aktif / Nonaktif -->
                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" role="switch" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label fs-13 text-dark fw-medium" for="edit_is_active">
                            Status Akun Aktif
                        </label>
                        <div class="fs-11 text-muted" id="edit_active_note">Jika dinonaktifkan, pengguna tidak dapat masuk ke sistem.</div>
                    </div>
                </div>

                <div class="modal-footer modal-footer-custom d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fs-13" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-maroon rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: PENUGASAN / ADD / UBAH ROLE                                      -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalUbahRole" tabindex="-1" aria-labelledby="modalUbahRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EEF2FF; color: #4F46E5;">
                        <i class="bi bi-shield-lock-fill fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalUbahRoleLabel">Penugasan Peran (Add / Ubah Role)</h2>
                        <div class="fs-12 text-muted">Sesuaikan hak akses staf pada basis data <code>tRoles_id</code></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUbahRole" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Ringkasan Staf -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
                        <div class="avatar-user-table bg-white text-dark border fw-bold" id="role_preview_avatar">
                            DP
                        </div>
                        <div>
                            <div class="fw-bold fs-13 text-dark" id="role_preview_nama">Dimas Pratama</div>
                            <div class="fs-11 text-muted" id="role_preview_email">adminkms@gmail.com</div>
                            <div class="fs-11 text-secondary mt-1">Peran saat ini: <strong id="role_preview_current" class="text-dark">Super Admin</strong></div>
                        </div>
                    </div>

                    <!-- Role Option Cards -->
                    <div class="mb-3">
                        <label class="form-label-custom mb-2">Pilih Peran Baru:</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($allRoles as $r)
                                <label class="border rounded-3 p-3 d-flex align-items-start gap-3 cursor-pointer role-option-label" for="role_radio_{{ $r->id }}">
                                    <input class="form-check-input mt-1" type="radio" name="tRoles_id" id="role_radio_{{ $r->id }}" value="{{ $r->id }}" required>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold fs-13 text-dark">{{ $r->nama }}</span>
                                            @if($r->id == 1)
                                                <span class="badge bg-danger bg-opacity-10 text-danger fs-11">Akses Penuh</span>
                                            @elseif($r->id == 2)
                                                <span class="badge bg-teal bg-opacity-10 text-success fs-11">Operasional Draf</span>
                                            @elseif($r->id == 3)
                                                <span class="badge bg-primary bg-opacity-10 text-primary fs-11">Persetujuan FAQ</span>
                                            @endif
                                        </div>
                                        <div class="fs-11 text-muted">
                                            @if($r->id == 1)
                                                Mengelola seluruh akun pengguna, master role, audit log, konfigurasi kategori, dan semua modul KMS.
                                            @elseif($r->id == 2)
                                                Membuat draf konten FAQ, mengunggah materi pertanyaan & jawaban, dan mengajukan draf ke Validator.
                                            @elseif($r->id == 3)
                                                Meninjau draf FAQ, memberikan catatan revisi, dan menyetujui penerbitan FAQ ke publik portal kampus.
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Warning for self protection -->
                    <div class="alert alert-warning border-0 d-none d-flex align-items-center gap-2 fs-12 mb-0" id="roleSelfWarning">
                        <i class="bi bi-shield-slash-fill fs-5 text-warning"></i>
                        <div><strong>Perhatian:</strong> Anda sedang mengubah peran pada akun login Anda sendiri. Pastikan ada akun Super Admin lain yang aktif.</div>
                    </div>
                </div>

                <div class="modal-footer modal-footer-custom d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fs-13" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-maroon rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-shield-check"></i>
                        <span>Terapkan Peran Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: CABUT ROLE DENGAN SYARAT KETAT (STRICT REMOVE ROLE)              -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCabutRole" tabindex="-1" aria-labelledby="modalCabutRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <!-- Modal Header Hazard -->
            <div class="modal-header modal-header-custom bg-danger bg-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-shield-x fs-5"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-danger mb-0" id="modalCabutRoleLabel">Cabut Hak Akses (Remove Role)</h2>
                        <div class="fs-12 text-muted">Tindakan berisiko tinggi dengan syarat ketat</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCabutRole" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Strict Lockout Guard Block (If Target is Current Admin) -->
                    <div id="removeRoleSelfBlocked" class="alert alert-danger border-0 d-none">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-slash-circle-fill fs-5 mt-1 text-danger"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger">Tindakan Ditolak (Anti-Lockout)</div>
                                <div class="fs-12 text-dark mt-1">
                                    Anda tidak diizinkan mencabut hak akses pada akun Super Admin Anda sendiri yang sedang digunakan untuk masuk. Tindakan ini dicegah demi keamanan sistem.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quorum Guard Block (If Last Super Admin) -->
                    <div id="removeRoleQuorumBlocked" class="alert alert-danger border-0 d-none">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock-fill fs-5 mt-1 text-danger"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger">Proteksi Kuorum Super Admin</div>
                                <div class="fs-12 text-dark mt-1">
                                    Pengguna ini adalah satu-satunya Super Admin yang aktif. Sistem mewajibkan setidaknya satu Super Admin aktif untuk operasional kampus.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Normal Confirmation Body -->
                    <div id="removeRoleAllowedSection">
                        <!-- User target card -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Target Pengguna:</div>
                            <div class="fw-bold fs-14 text-dark" id="remove_target_nama">-</div>
                            <div class="fs-12 text-muted" id="remove_target_email">-</div>
                            <div class="mt-2 pt-2 border-top fs-12 text-secondary">
                                Peran saat ini: <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold" id="remove_target_role">-</span>
                            </div>
                        </div>

                        <!-- Strict Verification Box -->
                        <div class="strict-verification-box mb-3">
                            <div class="d-flex align-items-center gap-2 text-danger fw-bold fs-13 mb-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>Syarat Ketat Verifikasi:</span>
                            </div>
                            <p class="fs-12 text-dark mb-2">
                                Mencabut role akan melucuti seluruh kewenangan operasional staf dan secara otomatis <strong>menonaktifkan akun</strong> tersebut demi mencegah akses tanpa izin.
                            </p>
                            <label for="confirmStrictInput" class="form-label-custom fs-12">
                                Ketik kata <strong class="text-danger">CABUT PERAN</strong> di bawah ini untuk membuka tombol konfirmasi:
                            </label>
                            <input type="text" 
                                   class="form-control form-control-custom font-monospace text-uppercase" 
                                   id="confirmStrictInput" 
                                   name="konfirmasi_teks"
                                   placeholder="Ketik 'CABUT PERAN'"
                                   autocomplete="off"
                                   oninput="validateStrictVerification(this.value)">
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-footer-custom d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fs-13" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" 
                            id="btnExecuteRemoveRole" 
                            class="btn btn-danger rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2" 
                            disabled>
                        <i class="bi bi-shield-x"></i>
                        <span>Cabut Hak Akses Staf</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 for modern confirmation pop-up -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Interactive Scripts: Strict Verification, Search & Filters -->
<script>
    // Jumlah Super Admin aktif saat ini untuk validasi kuorum di sisi client
    const currentActiveSuperAdmins = {{ $countSuperAdmin }};
    const currentLoggedInUserId = {{ $currentAdminId }};

    // Toggle Sidebar Mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    // Konfirmasi Logout dengan Pop-Up
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b', // Warna maroon KMS
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

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    // Open Edit Modal with Pre-filled Data
    function openEditModal(button) {
        const row = button.closest('tr');
        const id = row.getAttribute('data-id');
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const npk = row.getAttribute('data-npk');
        const isActive = row.getAttribute('data-is-active') === '1';

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_npk').value = npk;
        document.getElementById('edit_is_active').checked = isActive;
        document.getElementById('edit_password').value = '';

        // Form action
        const form = document.getElementById('formEditPengguna');
        form.action = `/superadmin/pengguna-akses/update/${id}`;

        const modal = new bootstrap.Modal(document.getElementById('modalEditPengguna'));
        modal.show();
    }

    // Open Role Assignment Modal
    function openRoleModal(button) {
        const row = button.closest('tr');
        const id = row.getAttribute('data-id');
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const roleId = row.getAttribute('data-role-id');
        const roleName = row.getAttribute('data-role-name');
        const isCurrent = row.getAttribute('data-is-current') === '1';

        document.getElementById('role_preview_nama').innerText = nama;
        document.getElementById('role_preview_email').innerText = email;
        document.getElementById('role_preview_current').innerText = roleName;
        document.getElementById('role_preview_avatar').innerText = nama.substring(0, 2).toUpperCase();

        // Check the radio button
        const radio = document.getElementById(`role_radio_${roleId}`);
        if (radio) radio.checked = true;

        // Show/hide self warning
        const selfWarning = document.getElementById('roleSelfWarning');
        if (isCurrent) {
            selfWarning.classList.remove('d-none');
        } else {
            selfWarning.classList.add('d-none');
        }

        // Form action
        const form = document.getElementById('formUbahRole');
        form.action = `/superadmin/pengguna-akses/role/${id}`;

        const modal = new bootstrap.Modal(document.getElementById('modalUbahRole'));
        modal.show();
    }

    // Open Strict Remove Role Modal
    function openRemoveRoleModal(button) {
        const row = button.closest('tr');
        const id = parseInt(row.getAttribute('data-id'));
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const roleId = parseInt(row.getAttribute('data-role-id'));
        const roleName = row.getAttribute('data-role-name');
        const isCurrent = row.getAttribute('data-is-current') === '1';

        // Target displays
        document.getElementById('remove_target_nama').innerText = nama;
        document.getElementById('remove_target_email').innerText = email;
        document.getElementById('remove_target_role').innerText = roleName;

        // Reset input & disabled button
        const confirmInput = document.getElementById('confirmStrictInput');
        confirmInput.value = '';
        const btnExecute = document.getElementById('btnExecuteRemoveRole');
        btnExecute.disabled = true;

        const selfBlock = document.getElementById('removeRoleSelfBlocked');
        const quorumBlock = document.getElementById('removeRoleQuorumBlocked');
        const allowedSection = document.getElementById('removeRoleAllowedSection');

        // Check Syarat Ketat:
        // Syarat 1: Akun diri sendiri dilarang dicabut
        if (isCurrent || id === currentLoggedInUserId) {
            selfBlock.classList.remove('d-none');
            quorumBlock.classList.add('d-none');
            allowedSection.classList.add('d-none');
            btnExecute.classList.add('d-none');
        }
        // Syarat 2: Kuorum Super Admin terakhir
        else if (roleId === 1 && currentActiveSuperAdmins <= 1) {
            selfBlock.classList.add('d-none');
            quorumBlock.classList.remove('d-none');
            allowedSection.classList.add('d-none');
            btnExecute.classList.add('d-none');
        }
        // Syarat dipenuhi: izinkan dengan verifikasi ketik manual
        else {
            selfBlock.classList.add('d-none');
            quorumBlock.classList.add('d-none');
            allowedSection.classList.remove('d-none');
            btnExecute.classList.remove('d-none');
        }

        // Form action
        const form = document.getElementById('formCabutRole');
        form.action = `/superadmin/pengguna-akses/remove-role/${id}`;

        const modal = new bootstrap.Modal(document.getElementById('modalCabutRole'));
        modal.show();
    }

    // Live validation for strict confirmation input 'CABUT PERAN'
    function validateStrictVerification(val) {
        const btnExecute = document.getElementById('btnExecuteRemoveRole');
        if (val.trim().toUpperCase() === 'CABUT PERAN') {
            btnExecute.disabled = false;
        } else {
            btnExecute.disabled = true;
        }
    }

    // Copy Email Helper
    function copyEmail(email) {
        navigator.clipboard.writeText(email).then(() => {
            alert('Alamat email staf berhasil disalin: ' + email);
        });
    }

    // Table Filtering System
    let currentRoleFilter = 'all';

    function filterByRole(role) {
        currentRoleFilter = role;

        const roleSelect = document.getElementById('roleSelectFilter');
        if (roleSelect && roleSelect.value !== role) {
            roleSelect.value = role;
        }

        filterTableRows();
    }

    function filterTableRows() {
        const query = (document.getElementById('tableFilterInput').value || '').toLowerCase().trim();
        const statusFilter = document.getElementById('statusSelectFilter').value;
        const rows = document.querySelectorAll('#userTable tbody tr.user-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const nama = (row.getAttribute('data-nama') || '').toLowerCase();
            const email = (row.getAttribute('data-email') || '').toLowerCase();
            const npk = (row.getAttribute('data-npk') || '').toLowerCase();
            const roleName = row.getAttribute('data-role-name');
            const isActive = row.getAttribute('data-is-active');

            // Text search match
            const matchesText = nama.includes(query) || email.includes(query) || npk.includes(query) || roleName.toLowerCase().includes(query);

            // Role filter match
            let matchesRole = true;
            if (currentRoleFilter === 'nonaktif') {
                matchesRole = (isActive === '0');
            } else if (currentRoleFilter !== 'all') {
                matchesRole = (roleName === currentRoleFilter);
            }

            // Status select filter match
            let matchesStatus = true;
            if (statusFilter === 'aktif') {
                matchesStatus = (isActive === '1');
            } else if (statusFilter === 'nonaktif') {
                matchesStatus = (isActive === '0');
            }

            if (matchesText && matchesRole && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Visible count indicator
        document.getElementById('visibleCount').innerText = visibleCount;

        // No data empty alert
        const noData = document.getElementById('noDataFiltered');
        if (visibleCount === 0) {
            noData.classList.remove('d-none');
        } else {
            noData.classList.add('d-none');
        }
    }

    // Bind Search Input Listeners
    document.getElementById('tableFilterInput').addEventListener('input', filterTableRows);
    document.getElementById('topSearchBar').addEventListener('input', function() {
        document.getElementById('tableFilterInput').value = this.value;
        filterTableRows();
    });

    // =========================================================================
    // PENGECEKAN KATA SANDI MINIMAL 8 KARAKTER (TAMBAH PENGGUNA)
    // =========================================================================
    const createPassInput = document.getElementById('create_password');
    const createPassMsg = document.getElementById('create_password_msg');
    const createPassCounter = document.getElementById('create_password_counter');
    const formTambahPengguna = document.querySelector('#modalTambahPengguna form');

    function validateCreatePassword() {
        if (!createPassInput) return true;
        const len = createPassInput.value.length;

        if (createPassCounter) {
            createPassCounter.innerText = `${len}/8 karakter`;
        }

        if (len === 0) {
            createPassInput.classList.remove('is-invalid', 'is-valid');
            if (createPassMsg) {
                createPassMsg.className = 'fs-11 text-muted';
                createPassMsg.innerHTML = '<i class="bi bi-info-circle me-1"></i>Minimal 8 karakter.';
            }
            if (createPassCounter) {
                createPassCounter.className = 'fs-11 fw-semibold text-muted';
            }
            return false;
        } else if (len < 8) {
            createPassInput.classList.add('is-invalid');
            createPassInput.classList.remove('is-valid');
            if (createPassMsg) {
                createPassMsg.className = 'fs-11 text-danger';
                createPassMsg.innerHTML = `<i class="bi bi-x-circle me-1"></i>Kata sandi terlalu pendek (kurang ${8 - len} karakter).`;
            }
            if (createPassCounter) {
                createPassCounter.className = 'fs-11 fw-semibold text-danger';
            }
            return false;
        } else {
            createPassInput.classList.remove('is-invalid');
            createPassInput.classList.add('is-valid');
            if (createPassMsg) {
                createPassMsg.className = 'fs-11 text-success';
                createPassMsg.innerHTML = '<i class="bi bi-check-circle me-1"></i>Kata sandi memenuhi syarat minimal 8 karakter.';
            }
            if (createPassCounter) {
                createPassCounter.className = 'fs-11 fw-semibold text-success';
            }
            return true;
        }
    }

    if (createPassInput) {
        createPassInput.addEventListener('input', validateCreatePassword);
    }

    if (formTambahPengguna) {
        formTambahPengguna.addEventListener('submit', function (e) {
            if (!validateCreatePassword()) {
                e.preventDefault();
                createPassInput.focus();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Kata Sandi Kurang Dari 8 Karakter',
                        text: 'Silakan masukkan kata sandi awal minimal 8 karakter sebelum menyimpan pengguna.',
                        icon: 'warning',
                        confirmButtonColor: '#85172C',
                        confirmButtonText: 'Mengerti'
                    });
                }
            }
        });
    }

    // Reset Form Tambah Pengguna saat modal ditutup (klik Batal, klik X, atau klik backdrop)
    document.getElementById('modalTambahPengguna').addEventListener('hidden.bs.modal', function () {
        // Reset semua input teks & select
        document.getElementById('create_nama').value = '';
        document.getElementById('create_email').value = '';
        document.getElementById('create_npk').value = '';
        document.getElementById('create_password').value = '';
        document.getElementById('create_role').selectedIndex = 0;

        // Reset checkbox is_active ke kondisi awal (checked/aktif)
        document.getElementById('create_is_active').checked = true;

        // Reset status validasi & ikon mata kata sandi
        if (createPassInput) {
            createPassInput.classList.remove('is-invalid', 'is-valid');
            if (createPassInput.type === 'text') {
                createPassInput.type = 'password';
                const eyeIcon = createPassInput.nextElementSibling?.querySelector('i');
                if (eyeIcon) {
                    eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            }
        }

        if (createPassMsg) {
            createPassMsg.className = 'fs-11 text-muted';
            createPassMsg.innerHTML = '<i class="bi bi-info-circle me-1"></i>Minimal 8 karakter.';
        }
        if (createPassCounter) {
            createPassCounter.className = 'fs-11 fw-semibold text-muted';
            createPassCounter.innerText = '0/8 karakter';
        }
    });
</script>

</body>
</html>

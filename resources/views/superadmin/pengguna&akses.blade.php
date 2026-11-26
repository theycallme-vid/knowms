<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengguna & Akses - Ruang {{ auth()->user()?->getRoleName() ?: 'Super Admin' }} | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Super Admin & Validator Shared CSS -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/superadmin/style.css') }}">
</head>
<body>

@php
    $authUser = auth()->user();
    if ($authUser) {
        $authUser->loadMissing(['role', 'direktorat']);
    }
    $currentUserRole  = $authUser ? $authUser->getRoleName() : 'Super Admin';
    $isSuperAdmin     = $isSuperAdmin ?? ($authUser ? $authUser->isAdmin() : true);
    $isValidatorAdmik = $isValidatorAdmik ?? ($authUser ? $authUser->isValidatorAdmik() : false);
    $isValidatorUnit  = $isValidatorUnit ?? ($authUser ? $authUser->isValidatorUnit() : false);
    $callerUnitName   = $authUser ? $authUser->getUnitName() : 'Direktorat Akademik';
    $callerDirId      = $authUser ? $authUser->getEffectiveDirektoratId() : 1;

    $allRoles = isset($roles) && count($roles) > 0 ? $roles : [
        (object)['id' => 2, 'nama' => 'Data Entry'],
        (object)['id' => 3, 'nama' => 'Validator'],
    ];
    $allDirektorats = isset($direktorats) ? $direktorats : collect();

    $userList = isset($users) ? $users : collect();

    $totalUsers      = count($userList);
    $countSuperAdmin = collect($userList)->where('tRoles_id', 1)->count();
    $countDataEntry  = collect($userList)->where('tRoles_id', 2)->count();
    $countValidator  = collect($userList)->where('tRoles_id', 3)->count();
    $countNonaktif   = collect($userList)->where('is_active', false)->count();

    $currentAdminId   = auth()->id() ?? 1;
    $penggunaBaseUrl  = $penggunaBaseUrl ?? ($isSuperAdmin ? url('/superadmin/pengguna-akses') : ($isValidatorUnit ? url('/validator-unit/pengguna-akses') : url('/validator/pengguna-akses')));
    $penggunaStoreUrl = $penggunaStoreUrl ?? ($isSuperAdmin ? route('superadmin.pengguna.store') : ($isValidatorUnit ? route('validator.unit.pengguna.store') : route('validator.pengguna.store')));
@endphp

<div class="d-flex">
    <!-- Sidebar -->
    @if($currentUserRole === 'Validator')
        @include('partials.validator-sidebar', ['activeMenu' => 'pengguna-akses'])
    @else
        <aside class="sidebar" id="sidebar">
            <div>
                <div class="sidebar-brand">
                    <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
                </div>
                <div>
                    <span class="role-pill">Ruang Super Admin</span>
                </div>
                <nav class="nav flex-column">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link-custom">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('superadmin.daftarFAQ') }}" class="nav-link-custom">
                        <i class="bi bi-journal-text"></i>
                        <span>Daftar FAQ</span>
                    </a>
                    <a href="{{ route('superadmin.kelolaUnit') }}" class="nav-link-custom">
                        <i class="bi bi-building-gear"></i>
                        <span>Kelola Unit</span>
                    </a>
                    <a href="{{ route('superadmin.pengguna-akses') }}" class="nav-link-custom active">
                        <i class="bi bi-people"></i>
                        <span>Pengguna &amp; Akses</span>
                    </a>
                    <a href="{{ route('superadmin.pengaturanSistem') }}" class="nav-link-custom">
                        <i class="bi bi-sliders"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-diagram-3"></i>
                        <span>Kategori</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-graph-up"></i>
                        <span>Laporan Pencarian</span>
                    </a>
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Aktivitas</span>
                    </a>
                </nav>
            </div>
            <div>
                <hr class="sidebar-divider">
                <a href="{{ route('superadmin.pengaturanAkun') }}" class="nav-link-custom">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan Akun</span>
                </a>
                <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
    @endif

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Wrapper -->
    <main class="flex-grow-1 min-vw-0">
        <!-- Top Navbar -->
        @php
            $tbRole = $authUser?->getRoleName() ?: 'Super Admin';
            $tbName = $authUser?->nama ?? 'Pengguna KMS';
            $tbEmail = $authUser?->email ?? '';
            $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'KM';
            $accountRoute = $isValidatorUnit
                ? route('validator.unit.pengaturanAkun')
                : ($tbRole === 'Validator' ? route('validator.pengaturanAkun') : route('superadmin.pengaturanAkun'));
        @endphp
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="top-breadcrumb d-flex align-items-center">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="crumb-sep">/</span>
                    <span>{{ $tbRole }}{{ $isValidatorUnit ? ' · ' . $callerUnitName : '' }}</span>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">{{ $isValidatorUnit ? 'Pengguna & Akses Unit' : 'Pengguna & Akses' }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="top-icon-btn" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ $tbInitials }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $tbName }}</div>
                            <div class="fs-11 text-muted">{{ $tbRole }} &middot; {{ $callerUnitName }}</div>
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ $accountRoute }}">
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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="date-badge">
                        @if($isValidatorUnit)
                            RUANG VALIDATOR UNIT &middot; {{ strtoupper($callerUnitName) }}
                        @elseif($isValidatorAdmik)
                            RUANG PENELAAH PUSAT &middot; DIREKTORAT AKADEMIK
                        @else
                            MANAJEMEN PENGGUNA &amp; AKSES SISTEM
                        @endif
                    </div>
                    <h1 class="page-title mb-1">{{ $isValidatorUnit ? 'Pengguna & Akses Unit' : 'Pengguna & Akses' }}</h1>
                    <p class="text-muted fs-13 mb-0">
                        @if($isValidatorUnit)
                            Kelola akun staf Data Entry khusus untuk unit <strong>{{ $callerUnitName }}</strong>.
                        @elseif($isValidatorAdmik)
                            Kelola akun Validator Unit serta Data Entry di seluruh unit &amp; direktorat terdaftar.
                        @else
                            Kelola akun staf kampus, penugasan peran (role), dan penempatan unit/direktorat dengan proteksi keamanan ketat.
                        @endif
                    </p>
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
                <div class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                    <div class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2">
                        @if(!$isValidatorUnit)
                            <select id="roleSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-13 fw-semibold text-dark shadow-sm border-secondary-subtle" style="min-width: 195px;" onchange="filterByRole(this.value)">
                                <option value="all" selected>Semua Role ({{ $totalUsers }})</option>
                                @if($isSuperAdmin)
                                    <option value="Super Admin">Super Admin ({{ $countSuperAdmin }})</option>
                                @endif
                                <option value="Validator">Validator ({{ $countValidator }})</option>
                                <option value="Data Entry">Data Entry ({{ $countDataEntry }})</option>
                            </select>

                            <select id="statusSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-13 fw-medium text-dark shadow-sm border-secondary-subtle" style="min-width: 155px;" onchange="filterTableRows()">
                                <option value="all">Semua Status</option>
                                <option value="aktif">Hanya Aktif</option>
                                <option value="nonaktif">Hanya Nonaktif</option>
                            </select>

                            <select id="unitSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-13 fw-medium text-dark shadow-sm border-secondary-subtle" style="min-width: 215px;" onchange="filterTableRows()">
                                <option value="all" selected>Semua Unit / Direktorat</option>
                                @foreach($allDirektorats as $dOpt)
                                    <option value="{{ $dOpt->id }}">{{ $dOpt->nama }}</option>
                                @endforeach
                            </select>
                        @else
                            <span class="badge rounded-pill px-3 py-2 fs-12 fw-semibold" style="background-color:#FCEEEF;color:#85172C;border:1px solid #F3C5CE;">
                                <i class="bi bi-building me-1"></i>Unit Terkunci: {{ $callerUnitName }} (Hanya Data Entry)
                            </span>
                            <select id="statusSelectFilter" class="form-select form-select-sm rounded-pill px-3 py-2 fs-13 fw-medium text-dark shadow-sm border-secondary-subtle" style="min-width: 155px;" onchange="filterTableRows()">
                                <option value="all">Semua Status</option>
                                <option value="aktif">Hanya Aktif</option>
                                <option value="nonaktif">Hanya Nonaktif</option>
                            </select>
                        @endif
                    </div>

                    <!-- Search -->
                    <div class="search-container ms-xl-auto" style="min-width: 220px; max-width: 350px; width: 100%;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="tableFilterInput" class="search-input" placeholder="Cari Nama Pengguna">
                    </div>
                </div>

                <!-- Main Data Table -->
                <div class="table-responsive-custom">
                    <table class="table custom-user-table align-middle" id="userTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 32%;">PENGGUNA</th>
                                <th style="width: 24%;">UNIT / FAKULTAS</th>
                                <th style="width: 15%;">PERAN</th>
                                <th style="width: 11%;">STATUS</th>
                                <th style="width: 13%; text-align: right;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userList as $index => $u)
                                @php
                                    $roleName = $u->role->nama ?? ($u->tRoles_id == 1 ? 'Super Admin' : ($u->tRoles_id == 2 ? 'Data Entry' : ($u->tRoles_id == 3 ? 'Validator' : 'Tanpa Role')));
                                    $unitName = $u->direktorat?->nama ?? 'Direktorat Akademik';
                                    $unitId   = (int) ($u->tDirektorat_id ?: 1);
                                    $unitIsPusat = $u->direktorat ? (bool) $u->direktorat->is_pusat : ($unitId === 1);
                                    $initials = strtoupper(substr($u->nama, 0, 2));
                                    $isCurrentAdmin = ($u->id == $currentAdminId);
                                    $createdAtJkt = isset($u->created_at) ? \Carbon\Carbon::parse($u->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-';
                                    $updatedAtJkt = isset($u->updated_at) ? \Carbon\Carbon::parse($u->updated_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : $createdAtJkt;
                                    $viewablePass = method_exists($u, 'getViewablePassword') ? $u->getViewablePassword() : '';
                                @endphp
                                <tr class="user-row"
                                    data-id="{{ $u->id }}"
                                    data-nama="{{ $u->nama }}"
                                    data-email="{{ $u->email }}"
                                    data-password="{{ $viewablePass }}"
                                    data-npk="{{ $u->npk ?? '' }}"
                                    data-role-id="{{ $u->tRoles_id }}"
                                    data-role-name="{{ $roleName }}"
                                    data-unit-id="{{ $unitId }}"
                                    data-unit-name="{{ $unitName }}"
                                    data-is-active="{{ $u->is_active ? '1' : '0' }}"
                                    data-is-current="{{ $isCurrentAdmin ? '1' : '0' }}"
                                    data-created-at="{{ $createdAtJkt }}"
                                    data-updated-at="{{ $updatedAtJkt }}">

                                    <td class="text-muted fw-semibold fs-12 row-number-cell">{{ $index + 1 }}</td>

                                    <td>
                                        <div class="fw-bold fs-13 text-dark d-flex align-items-center gap-2">
                                            <span>{{ $u->nama }}</span>
                                            @if($isCurrentAdmin)
                                                <span class="badge" style="background-color: #85172C; font-size: 0.65rem;">Akun Anda</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Unit / Fakultas -->
                                    <td>
                                        <span class="badge rounded-pill fw-semibold d-inline-flex align-items-center gap-1"
                                              style="{{ $unitIsPusat ? 'background-color:#FFF7ED;color:#9A3412;border:1px solid #FED7AA;' : 'background-color:#F8FAFC;color:#334155;border:1px solid #CBD5E1;' }} font-size: 0.72rem; padding: 0.32rem 0.75rem;">
                                            <i class="bi {{ $unitIsPusat ? 'bi-bank2' : 'bi-building' }}"></i>
                                            <span>{{ $unitName }}</span>
                                        </span>
                                    </td>

                                    <!-- Peran -->
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

                                    <!-- Status (Font putih) -->
                                    <td>
                                        @if($u->is_active)
                                            <span class="badge-role" style="background-color: #059669; color: #FFFFFF;">
                                                <span class="badge-status-dot bg-white"></span> Aktif
                                            </span>
                                        @else
                                            <span class="badge-role" style="background-color: #64748B; color: #FFFFFF;">
                                                <span class="badge-status-dot bg-white"></span> Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi: Lihat & Ubah -->
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <button type="button"
                                                    class="btn-action"
                                                    style="background-color: #FFFFFF; color: #334155; border: 1px solid #CBD5E1;"
                                                    title="Lihat Detail Pengguna"
                                                    onclick="openViewUserModal(this)">
                                                <i class="bi bi-eye"></i>
                                                <span>Lihat</span>
                                            </button>
                                            <button type="button"
                                                    class="btn-action btn-action-edit"
                                                    title="Ubah Pengguna & Peran"
                                                    onclick="openEditModal(this)">
                                                <i class="bi bi-pencil"></i>
                                                <span>Ubah</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="stat-icon mx-auto mb-2" style="background-color: #F8FAFC; color: #94A3B8;">
                                            <i class="bi bi-people fs-4"></i>
                                        </div>
                                        <div class="fw-bold fs-14 text-dark">Belum ada pengguna terdaftar</div>
                                        <div class="text-muted fs-12 mb-3">Tambahkan akun baru untuk memberikan akses ke sistem KMS.</div>
                                        <button type="button" class="btn btn-maroon btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                                            + Tambah Pengguna
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
                    <div class="text-muted fs-12">Coba sesuaikan kata kunci pencarian, unit, atau filter peran.</div>
                </div>

                <!-- Bottom Info & Pagination (10 item/halaman) -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top" id="userPaginationBar">
                    <div class="fs-13 text-muted" id="paginationInfoText">
                        Menampilkan 0 dari 0 pengguna
                    </div>
                    <div class="d-flex gap-1 align-items-center" id="paginationControls"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: TAMBAH PENGGUNA BARU                                            -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahPenggunaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: var(--brand-maroon-soft); color: var(--brand-maroon);">
                        <i class="bi bi-person-plus-fill fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalTambahPenggunaLabel">
                            {{ $isValidatorUnit ? 'Tambah Data Entry Unit' : 'Tambah Pengguna Baru' }}
                        </h2>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ $penggunaStoreUrl }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="create_nama" class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="create_nama" name="nama" required maxlength="100" placeholder="Contoh: Sarah Anindya, S.Kom">
                        <div class="form-text fs-11 text-muted">Sesuai nama identitas staf kampus.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="create_email" class="form-label-custom">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" id="create_email" name="email" required maxlength="50" placeholder="nama@staff.ubaya.ac.id">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="create_npk" class="form-label-custom">NPK / ID Staf</label>
                            <input type="text" class="form-control form-control-custom font-monospace" id="create_npk" name="npk" maxlength="15" placeholder="202020">
                        </div>
                    </div>

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

                    @if($isValidatorUnit)
                        <div class="p-3 rounded-3 border bg-light mb-3">
                            <div class="fs-12 fw-semibold text-dark mb-1">Penugasan Otomatis Unit Anda:</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge-role badge-role-dataentry"><i class="bi bi-pencil-fill"></i> Role: Data Entry</span>
                                <span class="badge rounded-pill bg-white text-dark border px-2.5 py-1 fs-12"><i class="bi bi-building me-1"></i>Unit: {{ $callerUnitName }}</span>
                            </div>
                        </div>
                    @else
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="create_role" class="form-label-custom">Pemberian Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-custom" id="create_role" name="tRoles_id" required>
                                    <option value="" disabled selected>-- Pilih Role --</option>
                                    @foreach($allRoles as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="create_direktorat" class="form-label-custom">Unit / Direktorat <span class="text-danger">*</span></label>
                                <select class="form-select form-select-custom" id="create_direktorat" name="tDirektorat_id" required>
                                    @foreach($allDirektorats as $d)
                                        @if($d->is_active || $d->is_pusat)
                                            <option value="{{ $d->id }}">
                                                {{ $d->nama }}{{ $d->is_pusat ? ' (Pusat)' : '' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="fs-11 text-muted mb-2">
                            <i class="bi bi-lightbulb me-1 text-warning"></i>Pilih <strong>Direktorat Akademik (Pusat)</strong> untuk staf Admik, atau pilih unit/fakultas tujuan untuk <strong>Validator Unit</strong> maupun <strong>Data Entry Unit</strong>.
                        </div>
                    @endif

                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" role="switch" id="create_is_active" name="is_active" value="1" checked>
                        <label class="form-check-label fs-13 text-dark fw-medium" for="create_is_active">
                            Langsung Aktifkan Akun Pengguna
                        </label>
                        <div class="fs-11 text-muted">Pengguna dapat langsung masuk ke ruang kerja unitnya setelah disimpan.</div>
                    </div>
                </div>

                <div class="modal-footer modal-footer-custom d-flex justify-content-end">
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EFF6FF; color: #2563EB;">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalEditPenggunaLabel">Update Keterangan Pengguna</h2>
                        <div class="fs-12 text-muted">Perbarui profil, unit penempatan, dan status staf</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditPengguna" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label for="edit_nama" class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="edit_nama" name="nama" required maxlength="100">
                    </div>

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

                    @if(!$isValidatorUnit)
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="edit_role" class="form-label-custom">Peran / Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-custom" id="edit_role" name="tRoles_id" required>
                                    @foreach($allRoles as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="edit_direktorat" class="form-label-custom">Unit / Direktorat Penempatan <span class="text-danger">*</span></label>
                                <select class="form-select form-select-custom" id="edit_direktorat" name="tDirektorat_id" required>
                                    @foreach($allDirektorats as $d)
                                        <option value="{{ $d->id }}">
                                            {{ $d->nama }}{{ $d->is_pusat ? ' (Pusat)' : '' }}{{ !$d->is_active ? ' — Nonaktif' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

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
                        <div class="form-text fs-11 text-muted">Biarkan kosong jika tidak ingin mengubah sandi.</div>
                    </div>

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
<!-- MODAL 2B: LIHAT DETAIL PENGGUNA                                           -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalLihatPengguna" tabindex="-1" aria-labelledby="modalLihatPenggunaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
        <div class="modal-content modal-content-custom" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header modal-header-custom" style="background: linear-gradient(135deg, #FFFDFB 0%, #FDF2F4 100%); border-bottom: 1px solid #F3E2E5; padding: 1.15rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="width: 40px; height: 40px; background-color: #FCEEEF; color: #85172C; border-radius: 12px;">
                        <i class="bi bi-person-vcard fs-5"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalLihatPenggunaLabel">Detail Informasi Pengguna</h2>
                        <div class="fs-12 text-muted">Rincian profil, kredensial, unit penempatan, dan status akun</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden;">
                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-person text-dark"></i><span>Nama Lengkap</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-14 fw-bold text-dark" id="view_user_nama">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-envelope text-dark"></i><span>Email</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-13 fw-semibold text-dark" id="view_user_email">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-key text-dark"></i><span>Password</span>
                        </div>
                        <div class="col-12 col-sm-8">
                            <div class="input-group input-group-sm">
                                <input type="password" class="form-control bg-white text-dark font-monospace border-secondary-subtle" id="view_user_password" value="••••••••" readonly>
                                <button class="btn btn-outline-secondary bg-white text-dark" type="button" onclick="toggleViewUserPassword(this)" title="Tampilkan / Sembunyikan">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-card-text text-dark"></i><span>NPK / ID Staf</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-13 fw-semibold font-monospace text-dark" id="view_user_npk">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-building text-dark"></i><span>Unit / Fakultas</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-13 fw-semibold text-dark" id="view_user_unit">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-shield-check text-dark"></i><span>Peran</span>
                        </div>
                        <div class="col-12 col-sm-8" id="view_user_role">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-activity text-dark"></i><span>Status</span>
                        </div>
                        <div class="col-12 col-sm-8" id="view_user_status">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem; border-bottom: 1px solid #E2E8F0;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-calendar-plus text-dark"></i><span>Dibuat</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-13 fw-medium text-dark" id="view_user_created">-</div>
                    </div>

                    <div class="row g-0 align-items-center" style="padding: 0.8rem 1.25rem;">
                        <div class="col-12 col-sm-4 fs-12 fw-semibold text-dark d-flex align-items-center gap-2 mb-1 mb-sm-0">
                            <i class="bi bi-clock-history text-dark"></i><span>Diperbarui</span>
                        </div>
                        <div class="col-12 col-sm-8 fs-13 fw-medium text-dark" id="view_user_updated">-</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer modal-footer-custom d-flex flex-wrap justify-content-end gap-2">
                <form id="formViewToggleStatus" action="" method="POST" class="m-0">
                    @csrf
                    <button type="submit" id="btnViewToggleStatus" class="btn rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2" style="background-color: #475569; color: #FFFFFF; border: none;">
                        <i class="bi bi-power"></i>
                        <span id="btnViewToggleStatusText">Nonaktifkan Akun</span>
                    </button>
                </form>

                <form id="formViewDeleteUser" action="" method="POST" class="m-0" onsubmit="return confirmDeleteUser(event)">
                    @csrf
                    <button type="submit" id="btnViewDeleteUser" class="btn btn-danger rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-trash"></i>
                        <span>Hapus Akun</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: PENUGASAN / ADD / UBAH ROLE                                      -->
<!-- ========================================================================= -->
@if(!$isValidatorUnit)
<div class="modal fade" id="modalUbahRole" tabindex="-1" aria-labelledby="modalUbahRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EEF2FF; color: #4F46E5;">
                        <i class="bi bi-shield-lock-fill fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0" id="modalUbahRoleLabel">Penugasan Peran &amp; Unit</h2>
                        <div class="fs-12 text-muted">Sesuaikan kombinasi <code>Role + Direktorat</code></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUbahRole" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
                        <div class="avatar-user-table bg-white text-dark border fw-bold" id="role_preview_avatar">DP</div>
                        <div>
                            <div class="fw-bold fs-13 text-dark" id="role_preview_nama">-</div>
                            <div class="fs-11 text-muted" id="role_preview_email">-</div>
                            <div class="fs-11 text-secondary mt-1">Saat ini: <strong id="role_preview_current" class="text-dark">-</strong></div>
                        </div>
                    </div>

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
                                                Mengelola seluruh akun pengguna, unit, konfigurasi sistem, dan monitoring semua modul KMS.
                                            @elseif($r->id == 2)
                                                Menyusun draf FAQ unitnya, mengunggah materi, dan mengajukan validasi.
                                            @elseif($r->id == 3)
                                                Validator Pusat (Direktorat Akademik) memvalidasi tahap akhir &amp; publish; Validator Unit memvalidasi tahap 1 unitnya.
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="role_direktorat_select" class="form-label-custom">Unit / Direktorat:</label>
                        <select class="form-select form-select-custom" id="role_direktorat_select" name="tDirektorat_id">
                            @foreach($allDirektorats as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}{{ $d->is_pusat ? ' (Pusat)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-warning border-0 d-none d-flex align-items-center gap-2 fs-12 mt-3 mb-0" id="roleSelfWarning">
                        <i class="bi bi-shield-slash-fill fs-5 text-warning"></i>
                        <div><strong>Perhatian:</strong> Anda sedang mengubah peran pada akun login Anda sendiri.</div>
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
@endif

<!-- ========================================================================= -->
<!-- MODAL 4: CABUT ROLE DENGAN SYARAT KETAT (STRICT REMOVE ROLE)              -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCabutRole" tabindex="-1" aria-labelledby="modalCabutRoleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
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
                    <div id="removeRoleSelfBlocked" class="alert alert-danger border-0 d-none">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-slash-circle-fill fs-5 mt-1 text-danger"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger">Tindakan Ditolak (Anti-Lockout)</div>
                                <div class="fs-12 text-dark mt-1">
                                    Anda tidak diizinkan mencabut hak akses pada akun Anda sendiri yang sedang digunakan untuk masuk.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="removeRoleQuorumBlocked" class="alert alert-danger border-0 d-none">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock-fill fs-5 mt-1 text-danger"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger">Proteksi Kuorum Super Admin</div>
                                <div class="fs-12 text-dark mt-1">
                                    Pengguna ini adalah satu-satunya Super Admin yang aktif.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="removeRoleAllowedSection">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Target Pengguna:</div>
                            <div class="fw-bold fs-14 text-dark" id="remove_target_nama">-</div>
                            <div class="fs-12 text-muted" id="remove_target_email">-</div>
                            <div class="mt-2 pt-2 border-top fs-12 text-secondary">
                                Peran saat ini: <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold" id="remove_target_role">-</span>
                            </div>
                        </div>

                        <div class="strict-verification-box mb-3">
                            <div class="d-flex align-items-center gap-2 text-danger fw-bold fs-13 mb-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>Syarat Ketat Verifikasi:</span>
                            </div>
                            <p class="fs-12 text-dark mb-2">
                                Mencabut role akan secara otomatis <strong>menonaktifkan akun</strong> tersebut demi mencegah akses tanpa izin.
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

<!-- Modal Logout -->
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
                    <div class="avatar-circle">{{ $tbInitials }}</div>
                    <div class="lh-sm">
                        <div class="fw-bold fs-13 text-dark">{{ $tbName }}</div>
                        <div class="fs-11 text-muted">{{ $tbEmail }}</div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const currentActiveSuperAdmins = {{ $countSuperAdmin }};
    const currentLoggedInUserId    = {{ $currentAdminId }};
    const penggunaBaseUrl          = @json($penggunaBaseUrl);

    function openLogoutModal() {
        const el = document.getElementById('modalLogoutConfirm');
        if (window.bootstrap && el) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (confirm('Apakah Anda yakin ingin keluar dari sistem KMS?')) {
            document.getElementById('logout-form').submit();
        }
    }

    function submitLogout(btn) {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>Memproses...</span>';
        }
        document.getElementById('logout-form').submit();
    }

    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('show');
        document.getElementById('sidebarBackdrop')?.classList.toggle('d-none');
    }

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

    function openEditModal(button) {
        const row = button.closest('tr');
        const id = row.getAttribute('data-id');
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const npk = row.getAttribute('data-npk');
        const roleId = row.getAttribute('data-role-id');
        const unitId = row.getAttribute('data-unit-id');
        const isActive = row.getAttribute('data-is-active') === '1';

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_npk').value = npk;
        document.getElementById('edit_is_active').checked = isActive;
        document.getElementById('edit_password').value = '';

        const editRoleSelect = document.getElementById('edit_role');
        if (editRoleSelect && roleId) {
            editRoleSelect.value = roleId;
        }

        const editDirSelect = document.getElementById('edit_direktorat');
        if (editDirSelect && unitId) {
            editDirSelect.value = unitId;
        }

        const form = document.getElementById('formEditPengguna');
        form.action = `${penggunaBaseUrl}/update/${encodeURIComponent(id)}`;

        new bootstrap.Modal(document.getElementById('modalEditPengguna')).show();
    }

    let currentViewUserPlainPassword = '';

    function openViewUserModal(button) {
        const row = button.closest('tr');
        const id = parseInt(row.getAttribute('data-id'), 10);
        const nama = row.getAttribute('data-nama') || '-';
        const email = row.getAttribute('data-email') || '-';
        currentViewUserPlainPassword = row.getAttribute('data-password') || '';
        const npk = row.getAttribute('data-npk') || '-';
        const roleId = parseInt(row.getAttribute('data-role-id'), 10);
        const roleName = row.getAttribute('data-role-name') || '-';
        const unitName = row.getAttribute('data-unit-name') || '-';
        const isActive = row.getAttribute('data-is-active') === '1';
        const isCurrent = row.getAttribute('data-is-current') === '1';
        const createdAt = row.getAttribute('data-created-at') || '-';
        const updatedAt = row.getAttribute('data-updated-at') || '-';

        document.getElementById('view_user_nama').innerText = nama;
        document.getElementById('view_user_email').innerText = email;
        document.getElementById('view_user_npk').innerText = npk || '-';
        document.getElementById('view_user_unit').innerText = unitName;
        document.getElementById('view_user_created').innerText = createdAt;
        document.getElementById('view_user_updated').innerText = updatedAt;

        const passEl = document.getElementById('view_user_password');
        if (passEl) {
            passEl.type = 'password';
            passEl.value = currentViewUserPlainPassword ? '••••••••' : '••••••••';
            const eyeIcon = passEl.nextElementSibling?.querySelector('i');
            if (eyeIcon) {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }

        document.getElementById('view_user_role').innerHTML =
            `<span class="badge-role ${roleId === 1 ? 'badge-role-superadmin' : (roleId === 2 ? 'badge-role-dataentry' : 'badge-role-validator')}">${roleName}</span>`;

        document.getElementById('view_user_status').innerHTML = isActive
            ? '<span class="badge-role" style="background-color:#059669;color:#FFFFFF;"><span class="badge-status-dot bg-white"></span> Aktif</span>'
            : '<span class="badge-role" style="background-color:#64748B;color:#FFFFFF;"><span class="badge-status-dot bg-white"></span> Nonaktif</span>';

        const toggleForm = document.getElementById('formViewToggleStatus');
        const toggleBtn = document.getElementById('btnViewToggleStatus');
        const toggleText = document.getElementById('btnViewToggleStatusText');
        toggleForm.action = `${penggunaBaseUrl}/toggle-status/${encodeURIComponent(id)}`;
        if (isActive) {
            toggleBtn.style.backgroundColor = '#475569';
            toggleBtn.style.color = '#FFFFFF';
            toggleBtn.style.border = 'none';
            toggleText.innerText = 'Nonaktifkan Akun';
        } else {
            toggleBtn.style.backgroundColor = '#059669';
            toggleBtn.style.color = '#FFFFFF';
            toggleBtn.style.border = 'none';
            toggleText.innerText = 'Aktifkan Akun';
        }

        const deleteForm = document.getElementById('formViewDeleteUser');
        const deleteBtn = document.getElementById('btnViewDeleteUser');
        deleteForm.action = `${penggunaBaseUrl}/delete/${encodeURIComponent(id)}`;
        deleteForm.setAttribute('data-target-name', nama);

        const isLastSuperAdmin = (roleId === 1 && currentActiveSuperAdmins <= 1);
        toggleBtn.disabled = (isCurrent && isActive);
        deleteBtn.disabled = (isCurrent || isLastSuperAdmin);

        new bootstrap.Modal(document.getElementById('modalLihatPengguna')).show();
    }

    function toggleViewUserPassword(btn) {
        const input = document.getElementById('view_user_password');
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            input.value = currentViewUserPlainPassword || 'Belum tersimpan (ubah sandi untuk melihat)';
            icon?.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            input.value = '••••••••';
            icon?.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    function confirmDeleteUser(event) {
        const form = event.target;
        const nama = form.getAttribute('data-target-name') || 'pengguna ini';
        if (typeof Swal !== 'undefined') {
            event.preventDefault();
            Swal.fire({
                title: 'Hapus Akun?',
                text: `Apakah Anda yakin ingin menghapus akun "${nama}" secara permanen?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Hapus Akun',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
        return confirm(`Hapus akun "${nama}" secara permanen?`);
    }

    function openRoleModal(button) {
        const modalEl = document.getElementById('modalUbahRole');
        if (!modalEl) return;
        const row = button.closest('tr');
        const id = row.getAttribute('data-id');
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const roleId = row.getAttribute('data-role-id');
        const roleName = row.getAttribute('data-role-name');
        const unitId = row.getAttribute('data-unit-id');
        const unitName = row.getAttribute('data-unit-name');
        const isCurrent = row.getAttribute('data-is-current') === '1';

        document.getElementById('role_preview_nama').innerText = nama;
        document.getElementById('role_preview_email').innerText = email;
        document.getElementById('role_preview_current').innerText = `${roleName} · ${unitName}`;
        document.getElementById('role_preview_avatar').innerText = nama.substring(0, 2).toUpperCase();

        const radio = document.getElementById(`role_radio_${roleId}`);
        if (radio) radio.checked = true;

        const dirSel = document.getElementById('role_direktorat_select');
        if (dirSel && unitId) {
            dirSel.value = unitId;
        }

        const selfWarning = document.getElementById('roleSelfWarning');
        if (selfWarning) {
            selfWarning.classList.toggle('d-none', !isCurrent);
        }

        const form = document.getElementById('formUbahRole');
        form.action = `${penggunaBaseUrl}/role/${encodeURIComponent(id)}`;

        new bootstrap.Modal(modalEl).show();
    }

    function openRemoveRoleModal(button) {
        const row = button.closest('tr');
        const id = parseInt(row.getAttribute('data-id'), 10);
        const nama = row.getAttribute('data-nama');
        const email = row.getAttribute('data-email');
        const roleId = parseInt(row.getAttribute('data-role-id'), 10);
        const roleName = row.getAttribute('data-role-name');
        const unitName = row.getAttribute('data-unit-name');
        const isCurrent = row.getAttribute('data-is-current') === '1';

        document.getElementById('remove_target_nama').innerText = nama;
        document.getElementById('remove_target_email').innerText = email;
        document.getElementById('remove_target_role').innerText = `${roleName} (${unitName})`;

        const confirmInput = document.getElementById('confirmStrictInput');
        confirmInput.value = '';
        const btnExecute = document.getElementById('btnExecuteRemoveRole');
        btnExecute.disabled = true;

        const selfBlock = document.getElementById('removeRoleSelfBlocked');
        const quorumBlock = document.getElementById('removeRoleQuorumBlocked');
        const allowedSection = document.getElementById('removeRoleAllowedSection');

        if (isCurrent || id === currentLoggedInUserId) {
            selfBlock.classList.remove('d-none');
            quorumBlock.classList.add('d-none');
            allowedSection.classList.add('d-none');
            btnExecute.classList.add('d-none');
        } else if (roleId === 1 && currentActiveSuperAdmins <= 1) {
            selfBlock.classList.add('d-none');
            quorumBlock.classList.remove('d-none');
            allowedSection.classList.add('d-none');
            btnExecute.classList.add('d-none');
        } else {
            selfBlock.classList.add('d-none');
            quorumBlock.classList.add('d-none');
            allowedSection.classList.remove('d-none');
            btnExecute.classList.remove('d-none');
        }

        const form = document.getElementById('formCabutRole');
        form.action = `${penggunaBaseUrl}/remove-role/${encodeURIComponent(id)}`;

        new bootstrap.Modal(document.getElementById('modalCabutRole')).show();
    }

    function validateStrictVerification(val) {
        const btnExecute = document.getElementById('btnExecuteRemoveRole');
        btnExecute.disabled = (val.trim().toUpperCase() !== 'CABUT PERAN');
    }

    function copyEmail(email) {
        navigator.clipboard.writeText(email).then(() => {
            alert('Alamat email staf berhasil disalin: ' + email);
        });
    }

    let currentRoleFilter = 'all';
    let currentPage = 1;
    const itemsPerPage = 10;

    function filterByRole(role) {
        currentRoleFilter = role;
        const roleSelect = document.getElementById('roleSelectFilter');
        if (roleSelect && roleSelect.value !== role) {
            roleSelect.value = role;
        }
        currentPage = 1;
        filterTableRows();
    }

    function goToPage(page) {
        currentPage = page;
        filterTableRows(false);
    }

    function filterTableRows(resetPage = true) {
        if (resetPage) currentPage = 1;
        const query = (document.getElementById('tableFilterInput')?.value || '').toLowerCase().trim();
        const statusFilter = document.getElementById('statusSelectFilter')?.value || 'all';
        const unitFilter = document.getElementById('unitSelectFilter')?.value || 'all';
        const rows = Array.from(document.querySelectorAll('#userTable tbody tr.user-row'));

        const matchedRows = [];
        rows.forEach(row => {
            const nama = (row.getAttribute('data-nama') || '').toLowerCase();
            const email = (row.getAttribute('data-email') || '').toLowerCase();
            const npk = (row.getAttribute('data-npk') || '').toLowerCase();
            const roleName = row.getAttribute('data-role-name') || '';
            const unitId = row.getAttribute('data-unit-id') || '';
            const unitName = (row.getAttribute('data-unit-name') || '').toLowerCase();
            const isActive = row.getAttribute('data-is-active');

            const matchesText = nama.includes(query) || email.includes(query) || npk.includes(query) || roleName.toLowerCase().includes(query) || unitName.includes(query);

            let matchesRole = true;
            if (currentRoleFilter === 'nonaktif') {
                matchesRole = (isActive === '0');
            } else if (currentRoleFilter !== 'all') {
                matchesRole = (roleName === currentRoleFilter);
            }

            let matchesStatus = true;
            if (statusFilter === 'aktif') {
                matchesStatus = (isActive === '1');
            } else if (statusFilter === 'nonaktif') {
                matchesStatus = (isActive === '0');
            }

            let matchesUnit = (unitFilter === 'all' || unitId === unitFilter);

            if (matchesText && matchesRole && matchesStatus && matchesUnit) {
                matchedRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalMatched = matchedRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatched / itemsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = Math.min(startIdx + itemsPerPage, totalMatched);

        matchedRows.forEach((row, idx) => {
            if (idx >= startIdx && idx < endIdx) {
                row.style.display = '';
                const numCell = row.querySelector('.row-number-cell');
                if (numCell) numCell.innerText = idx + 1;
            } else {
                row.style.display = 'none';
            }
        });

        const infoEl = document.getElementById('paginationInfoText');
        if (infoEl) {
            if (totalMatched === 0) {
                infoEl.innerHTML = 'Menampilkan 0 dari 0 pengguna';
            } else {
                infoEl.innerHTML = `Menampilkan <strong class="text-dark">${startIdx + 1}–${endIdx}</strong> dari <strong class="text-dark">${totalMatched}</strong> pengguna &middot; ${itemsPerPage} per halaman`;
            }
        }

        const ctrlEl = document.getElementById('paginationControls');
        if (ctrlEl) {
            if (totalMatched === 0) {
                ctrlEl.innerHTML = '';
            } else {
                let html = `<button type="button" class="page-btn ${currentPage <= 1 ? 'disabled' : ''}" ${currentPage <= 1 ? 'disabled' : `onclick="goToPage(${currentPage - 1})"`}><i class="bi bi-chevron-left"></i></button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button type="button" class="page-btn ${p === currentPage ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
                }
                html += `<button type="button" class="page-btn ${currentPage >= totalPages ? 'disabled' : ''}" ${currentPage >= totalPages ? 'disabled' : `onclick="goToPage(${currentPage + 1})"`}><i class="bi bi-chevron-right"></i></button>`;
                ctrlEl.innerHTML = html;
            }
        }

        document.getElementById('noDataFiltered')?.classList.toggle('d-none', totalMatched !== 0);
    }

    document.getElementById('tableFilterInput')?.addEventListener('input', () => filterTableRows(true));
    document.addEventListener('DOMContentLoaded', () => filterTableRows(true));

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
            if (createPassCounter) createPassCounter.className = 'fs-11 fw-semibold text-muted';
            return false;
        } else if (len < 8) {
            createPassInput.classList.add('is-invalid');
            createPassInput.classList.remove('is-valid');
            if (createPassMsg) {
                createPassMsg.className = 'fs-11 text-danger';
                createPassMsg.innerHTML = `<i class="bi bi-x-circle me-1"></i>Kata sandi terlalu pendek (kurang ${8 - len} karakter).`;
            }
            if (createPassCounter) createPassCounter.className = 'fs-11 fw-semibold text-danger';
            return false;
        } else {
            createPassInput.classList.remove('is-invalid');
            createPassInput.classList.add('is-valid');
            if (createPassMsg) {
                createPassMsg.className = 'fs-11 text-success';
                createPassMsg.innerHTML = '<i class="bi bi-check-circle me-1"></i>Kata sandi memenuhi syarat minimal 8 karakter.';
            }
            if (createPassCounter) createPassCounter.className = 'fs-11 fw-semibold text-success';
            return true;
        }
    }

    createPassInput?.addEventListener('input', validateCreatePassword);

    formTambahPengguna?.addEventListener('submit', function (e) {
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

    document.getElementById('modalTambahPengguna')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('create_nama').value = '';
        document.getElementById('create_email').value = '';
        document.getElementById('create_npk').value = '';
        document.getElementById('create_password').value = '';
        const crRole = document.getElementById('create_role');
        if (crRole) crRole.selectedIndex = 0;
        document.getElementById('create_is_active').checked = true;

        if (createPassInput) {
            createPassInput.classList.remove('is-invalid', 'is-valid');
            if (createPassInput.type === 'text') {
                createPassInput.type = 'password';
                createPassInput.nextElementSibling?.querySelector('i')?.classList.replace('bi-eye-slash', 'bi-eye');
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

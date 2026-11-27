<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Unit / Direktorat - Ruang {{ !empty($isSuperAdmin) ? 'Super Admin' : 'Validator' }} | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Shared Styles -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/superadmin/style.css') }}">
</head>
<body>

@php
    $tbUser = auth()->user();
    if ($tbUser) {
        $tbUser->loadMissing(['role', 'direktorat']);
    }
    $tbRole     = $tbUser?->getRoleName() ?: 'Validator';
    $tbUnitName = $tbUser?->getUnitName() ?: 'Direktorat Akademik';
    $tbName     = $tbUser?->nama ?? 'Pengguna KMS';
    $tbEmail    = $tbUser?->email ?? '';
    $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: 'KM';

    $totalUnits   = $direktorats->count();
    $activeUnits  = $direktorats->where('is_active', true)->count();
    $inactiveUnits = $direktorats->where('is_active', false)->count();
@endphp

<div class="d-flex">
    @if(!empty($isSuperAdmin))
        <aside class="sidebar" id="sidebar">
            <div>
                <div class="sidebar-brand">
                    <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="sidebar-logo-img">
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
                    <a href="{{ route('superadmin.kelolaUnit') }}" class="nav-link-custom active">
                        <i class="bi bi-building-gear"></i>
                        <span>Kelola Unit</span>
                    </a>
                    <a href="{{ route('superadmin.pengguna-akses') }}" class="nav-link-custom">
                        <i class="bi bi-people"></i>
                        <span>Pengguna &amp; Akses</span>
                    </a>
                    <a href="{{ route('superadmin.pengaturanSistem') }}" class="nav-link-custom">
                        <i class="bi bi-sliders"></i>
                        <span>Pengaturan Sistem</span>
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
    @else
        @include('partials.validator-sidebar', ['activeMenu' => 'kelola-unit'])
    @endif

    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <main class="flex-grow-1 min-vw-0">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
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
                    <span class="crumb-current">Kelola Unit</span>
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
                            <div class="fs-11 text-muted">{{ $tbRole }} &middot; {{ $tbUnitName }}</div>
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ !empty($isSuperAdmin) ? route('superadmin.pengaturanAkun') : route('validator.pengaturanAkun') }}">
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

        <div class="content-area">
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
                        <strong class="d-block mb-1">Periksa kembali input Anda:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Kelola Unit / Direktorat</h1>
                    <p class="text-muted fs-13 mb-0">
                        Tambahkan unit atau fakultas baru agar ruang kerja Validator Unit &amp; Data Entry Unit otomatis tersedia tanpa perlu mengubah kode.
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-maroon rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm fs-13" data-bs-toggle="modal" data-bs-target="#modalTambahUnit">
                        <i class="bi bi-building-add"></i>
                        <span>Tambah Unit Baru</span>
                    </button>
                </div>
            </div>

            <!-- Statistik Ringkas -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="stat-card-compact h-100">
                        <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Unit Terdaftar</div>
                            <div class="stat-value">{{ $totalUnits }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-card-compact h-100">
                        <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="stat-label">Unit Aktif</div>
                            <div class="stat-value">{{ $activeUnits }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="stat-card-compact h-100">
                        <div class="stat-icon" style="background-color:#F1F5F9; color:#64748B;">
                            <i class="bi bi-pause-circle"></i>
                        </div>
                        <div>
                            <div class="stat-label">Unit Nonaktif</div>
                            <div class="stat-value">{{ $inactiveUnits }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Unit -->
            <div class="card-custom">
                <div class="d-flex align-items-center justify-content-end gap-3 mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
                        <div class="search-container" style="max-width: 350px;">
                            <i class="bi bi-search"></i>
                            <input type="text" id="unitSearchInput" class="search-input" placeholder="Cari Nama Unit / Fakultas">
                        </div>
                    </div>
                </div>

                <div class="table-responsive-custom">
                    <table class="table custom-user-table align-middle" id="unitTable">
                        <thead>
                            <tr>
                                <th style="width: 6%;">NO</th>
                                <th style="width: 32%;">NAMA UNIT / DIREKTORAT</th>
                                <th style="width: 14%;">KLASIFIKASI</th>
                                <th style="width: 12%;">VALIDATOR</th>
                                <th style="width: 12%;">DATA ENTRY</th>
                                <th style="width: 10%;">TOTAL FAQ</th>
                                <th style="width: 12%;">STATUS</th>
                                <th style="width: 14%; text-align: right;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($direktorats as $idx => $dir)
                                <tr class="unit-row"
                                    data-id="{{ $dir->id }}"
                                    data-nama="{{ $dir->nama }}"
                                    data-is-pusat="{{ $dir->is_pusat ? '1' : '0' }}"
                                    data-is-active="{{ $dir->is_active ? '1' : '0' }}">
                                    <td class="text-muted fw-semibold fs-12">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-user-table flex-shrink-0" style="{{ $dir->is_pusat ? 'background-color:#FCEEEF;color:#85172C;' : 'background-color:#EFF6FF;color:#1D4ED8;' }}">
                                                <i class="bi {{ $dir->is_pusat ? 'bi-bank2' : 'bi-building' }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold fs-13 text-dark">{{ $dir->nama }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($dir->is_pusat)
                                            <span class="badge rounded-pill" style="background-color:#FCEEEF;color:#85172C;border:1px solid #F3C5CE;font-size:0.72rem;padding:0.32rem 0.75rem;font-weight:700;">
                                                <i class="bi bi-star-fill me-1"></i>Pusat (Admik)
                                            </span>
                                        @else
                                            <span class="badge rounded-pill" style="background-color:#F8FAFC;color:#334155;border:1px solid #CBD5E1;font-size:0.72rem;padding:0.32rem 0.75rem;font-weight:600;">
                                                Unit / Fakultas
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold fs-13 text-dark">{{ $dir->validators_count ?? 0 }}</span>
                                        <span class="fs-11 text-muted">akun</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold fs-13 text-dark">{{ $dir->data_entries_count ?? 0 }}</span>
                                        <span class="fs-11 text-muted">akun</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-semibold fs-12 px-2.5 py-1">{{ $dir->faqs_count ?? 0 }} FAQ</span>
                                    </td>
                                    <td>
                                        @if($dir->is_active)
                                            <span class="badge-role" style="background-color: #059669; color: #FFFFFF;">
                                                <span class="badge-status-dot bg-white"></span> Aktif
                                            </span>
                                        @else
                                            <span class="badge-role badge-inactive">
                                                <span class="badge-status-dot bg-secondary"></span> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" class="btn-action btn-action-edit" onclick="openEditUnitModal(this)" title="Ubah Unit & Status">
                                                <i class="bi bi-pencil"></i>
                                                <span>Ubah</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted fs-13">Belum ada unit terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah Unit -->
<div class="modal fade" id="modalTambahUnit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: var(--brand-maroon-soft); color: var(--brand-maroon);">
                        <i class="bi bi-building-add fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0">Tambah Unit / Direktorat Baru</h2>
                        <div class="fs-12 text-muted">Ruang kerja Validator Unit &amp; Data Entry Unit langsung aktif otomatis</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ $unitStoreUrl }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="create_unit_nama" class="form-label-custom">Nama Unit / Fakultas / Direktorat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="create_unit_nama" name="nama" required maxlength="100" placeholder="Contoh: DPK atau Direktorat Keuangan">
                    </div>

                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" role="switch" id="create_unit_active" name="is_active" value="1" checked>
                        <label class="form-check-label fs-13 text-dark fw-medium" for="create_unit_active">
                            Langsung Aktifkan Unit
                        </label>
                    </div>
                </div>
                <div class="modal-footer modal-footer-custom d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fs-13" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-maroon rounded-pill px-4 fs-13 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Unit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Unit -->
<div class="modal fade" id="modalEditUnit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EFF6FF; color: #2563EB;">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fw-bold fs-15 text-dark mb-0">Ubah Unit / Direktorat</h2>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditUnit" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_unit_nama" class="form-label-custom">Nama Unit / Fakultas / Direktorat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="edit_unit_nama" name="nama" required maxlength="100">
                    </div>

                    <div class="mt-3 pt-3 border-top" id="editUnitActiveWrap">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_unit_active" name="is_active" value="1">
                            <label class="form-check-label fs-13 text-dark fw-medium" for="edit_unit_active">
                                Status Unit Aktif
                            </label>
                        </div>
                        <div class="fs-11 text-muted" id="editUnitActiveNote">
                            Jika dinonaktifkan, ruang kerja unit ini akan dinonaktifkan sementara hingga diaktifkan kembali.
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-custom d-flex align-items-center justify-content-between">
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

<!-- Modal Logout -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
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
                <button type="button" class="btn-kms-primary" onclick="this.disabled=true;document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Ya, Keluar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const unitBaseUrl = @json($unitBaseUrl);
    let currentEditingUnit = { id: null, nama: '', isPusat: false, isActive: true };

    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('show');
        document.getElementById('sidebarBackdrop')?.classList.toggle('d-none');
    }

    function openLogoutModal() {
        const el = document.getElementById('modalLogoutConfirm');
        if (window.bootstrap && el) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function openEditUnitModal(btn) {
        const row = btn.closest('tr');
        if (!row) return;
        const id = row.getAttribute('data-id');
        const nama = row.getAttribute('data-nama') || '';
        const isPusat = row.getAttribute('data-is-pusat') === '1';
        const isActive = row.getAttribute('data-is-active') === '1';

        currentEditingUnit = { id, nama, isPusat, isActive };

        const form = document.getElementById('formEditUnit');
        if (form) {
            delete form.dataset.confirmedStatusChange;
            form.action = `${unitBaseUrl}/update/${encodeURIComponent(id)}`;
        }

        document.getElementById('edit_unit_nama').value = nama;
        const activeCheckbox = document.getElementById('edit_unit_active');
        const activeWrap = document.getElementById('editUnitActiveWrap');
        const activeNote = document.getElementById('editUnitActiveNote');

        activeCheckbox.checked = isPusat ? true : isActive;
        activeCheckbox.disabled = isPusat;

        if (activeWrap) {
            activeWrap.classList.toggle('opacity-50', isPusat);
        }

        if (activeNote) {
            activeNote.textContent = isPusat
                ? 'Direktorat Akademik (Pusat) selalu aktif sebagai pusat koordinasi KMS.'
                : 'Jika dinonaktifkan, ruang kerja unit ini akan dinonaktifkan sementara hingga diaktifkan kembali.';
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditUnit')).show();
    }

    document.getElementById('formEditUnit')?.addEventListener('submit', function (e) {
        if (currentEditingUnit.isPusat) return;
        const activeCheckbox = document.getElementById('edit_unit_active');
        if (!activeCheckbox) return;

        const newActive = activeCheckbox.checked;
        if (newActive !== currentEditingUnit.isActive && !this.dataset.confirmedStatusChange) {
            e.preventDefault();
            const actionTitle = newActive ? 'Konfirmasi Aktifkan Unit' : 'Konfirmasi Nonaktifkan Unit';
            const actionDesc = newActive
                ? `Anda mengubah status "${currentEditingUnit.nama}" menjadi Aktif. Lanjutkan menyimpan perubahan?`
                : `Anda mengubah status "${currentEditingUnit.nama}" menjadi Nonaktif. Lanjutkan menyimpan perubahan?`;
            const confirmBtnText = newActive ? 'Ya, Aktifkan & Simpan' : 'Ya, Nonaktifkan & Simpan';
            const confirmColor = newActive ? '#059669' : '#D97706';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: actionTitle,
                    text: actionDesc,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#64748B',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((res) => {
                    if (res.isConfirmed) {
                        this.dataset.confirmedStatusChange = '1';
                        this.submit();
                    }
                });
            } else if (confirm(actionDesc)) {
                this.dataset.confirmedStatusChange = '1';
                this.submit();
            }
        }
    });

    document.getElementById('unitSearchInput')?.addEventListener('input', function () {
        const q = (this.value || '').toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('#unitTable tbody tr.unit-row').forEach(row => {
            const nama = (row.getAttribute('data-nama') || '').toLowerCase();
            const show = !q || nama.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const counter = document.getElementById('unitVisibleCount');
        if (counter) counter.textContent = visible;
    });
</script>
</body>
</html>

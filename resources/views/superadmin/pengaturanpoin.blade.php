<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Poin Algoritma - Ruang Super Admin | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Super Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/superadmin/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
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
                <a href="{{ route('superadmin.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
                <a href="{{ route('superadmin.pengaturanSistem') }}" class="nav-link-custom active">
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

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Wrapper -->
    <main class="flex-grow-1 min-vw-0">
        @php
            $tbUser = auth()->user();
            if ($tbUser && !$tbUser->relationLoaded('role')) {
                $tbUser->load('role');
            }
            $tbRole = $tbUser?->getRoleName() ?: 'Super Admin';
            $tbName = $tbUser?->nama ?? 'Pengguna KMS';
            $tbEmail = $tbUser?->email ?? '';
            $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'KM';
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
                    <span>{{ $tbRole }}</span>
                    <span class="crumb-sep">/</span>
                    <a href="{{ route('superadmin.pengaturanSistem') }}" class="text-decoration-none text-muted">Pengaturan Sistem</a>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">Pengaturan Poin Algoritma</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('superadmin.pengaturanAkun') }}">
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
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="date-badge">KONFIGURASI ALGORITMA REKOMENDASI</div>
                    <h1 class="page-title mb-1">Pengaturan Poin &amp; Bobot Algoritma</h1>
                    <p class="text-muted fs-13 mb-0">Sesuaikan bobot poin View, Like, Dislike, bonus Fakultas, serta bonus Semester untuk perhitungan Rekomendasi FAQ.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.pengaturanSistem') }}" class="btn btn-outline-secondary bg-white rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-sm text-dark fs-13 fw-semibold" style="border-color: #E2E8F0;">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <button type="button" class="btn btn-outline-secondary bg-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm text-dark fs-13 fw-semibold" style="border-color: #E2E8F0;" onclick="openResetConfirmModal()">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Kembalikan ke Default</span>
                    </button>
                    <button type="button" class="btn rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm text-white fs-13 fw-semibold" style="background-color: var(--brand-maroon);" onclick="openSaveConfirmModal()">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 fs-13 mb-4">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 fs-13 mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-3 fs-13 mb-4">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Terdapat kesalahan input:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="formPengaturanSistem" action="{{ route('superadmin.pengaturanSistem.update') }}" method="POST">
                @csrf
                <input type="hidden" name="scope" value="poin">

                <!-- PENGATURAN BOBOT SKOR ALGORITMA REKOMENDASI FAQ -->
                <div class="card-custom mb-4">
                    <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-0">Bobot Skor Algoritma Rekomendasi FAQ Mahasiswa</h2>
                            <div class="fs-12 text-muted">Rumus Total Skor = <code>(View Segmen Persis &times; Bobot) + (View Angkatan &times; Bobot) + (View Fakultas &times; Bobot) + (View Global &times; Bobot) + (Like &times; Bobot) + (Dislike &times; Penalti) + Bonus Fakultas + Bonus Semester</code> (dihitung dari log <strong>{{ $currentValues['rec_log_days'] ?? 30 }} hari terakhir</strong>).</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach($settingsMeta as $key => $meta)
                            @if(($meta['group'] ?? '') === 'recommendation')
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background-color: #FAF9F6; border: 1px solid #EDE7E1;">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                <label for="input_{{ $key }}" class="fw-bold fs-13 text-dark mb-0">{{ $meta['label'] }}</label>
                                                <span class="badge rounded-pill flex-shrink-0" style="background-color: #F1F5F9; color: #475569; font-size: 0.7rem;">Default: {{ $meta['value'] }}</span>
                                            </div>
                                            <div class="fs-12 text-muted mb-3">{{ $meta['keterangan'] }}</div>
                                        </div>
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                id="input_{{ $key }}"
                                                name="{{ $key }}"
                                                class="form-control fw-bold text-dark"
                                                value="{{ old($key, $currentValues[$key] ?? $meta['value']) }}"
                                                required
                                            >
                                            <span class="input-group-text bg-white text-muted fs-12">poin</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </form>

            <!-- Form Tersembunyi Reset Default -->
            <form id="formResetPengaturanSistem" action="{{ route('superadmin.pengaturanSistem.reset') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="scope" value="poin">
            </form>
        </div>
    </main>
</div>

<!-- MODAL KONFIRMASI SIMPAN PERUBAHAN PENGATURAN POIN -->
<div class="modal fade" id="modalConfirmSaveSettings" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="kms-modal-badge-icon" style="background-color: #FCEEEF; color: #85172C;">
                        <i class="bi bi-sliders"></i>
                    </span>
                    <h5 class="kms-modal-title">Konfirmasi Simpan Poin Algoritma</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                <p class="kms-modal-desc mb-2">
                    Apakah Anda yakin ingin menyimpan perubahan pada <strong>Bobot Poin Algoritma Rekomendasi</strong>?
                </p>
                <div class="p-2.5 rounded-3 fs-12 text-secondary" style="background-color: #FAF8F5; border: 1px solid #EDE7E1;">
                    <div class="text-muted">Perubahan bobot skor akan langsung diterapkan pada urutan rekomendasi FAQ mahasiswa.</div>
                </div>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-danger" style="background-color: var(--brand-maroon); border-color: var(--brand-maroon);" onclick="document.getElementById('formPengaturanSistem').submit();">
                    <i class="bi bi-check2-circle me-1"></i>Ya, Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI RESET DEFAULT -->
<div class="modal fade" id="modalConfirmResetSettings" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="kms-modal-badge-icon" style="background-color: #FFFBEB; color: #D97706;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </span>
                    <h5 class="kms-modal-title">Kembalikan Poin ke Default?</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                <p class="kms-modal-desc mb-0">
                    Seluruh bobot poin algoritma rekomendasi akan dikembalikan ke angka bawaan sistem. Lanjutkan?
                </p>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-kms-danger" onclick="document.getElementById('formResetPengaturanSistem').submit();">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Ya, Reset Default
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI KELUAR (LOGOUT) -->
<div class="modal fade" id="modalLogoutConfirm" tabindex="-1" aria-labelledby="modalLogoutTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content kms-modal-content">
            <div class="kms-modal-header">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="kms-modal-badge-icon" style="background-color: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-box-arrow-right"></i>
                    </span>
                    <h5 class="kms-modal-title" id="modalLogoutTitle">Konfirmasi Keluar</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
            </div>
            <div class="kms-modal-body">
                <p class="kms-modal-desc mb-0">
                    Apakah Anda yakin ingin keluar dari sesi <strong>Ruang Super Admin</strong>?
                </p>
            </div>
            <div class="kms-modal-footer">
                <button type="button" class="btn-kms-cancel" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-kms-danger">
                        <i class="bi bi-box-arrow-right me-1"></i>Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sb = document.getElementById('sidebar');
        const bd = document.getElementById('sidebarBackdrop');
        if (sb) sb.classList.toggle('show');
        if (bd) bd.classList.toggle('d-none');
    }

    function openLogoutModal() {
        const el = document.getElementById('modalLogoutConfirm');
        if (el && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function openSaveConfirmModal() {
        const el = document.getElementById('modalConfirmSaveSettings');
        if (el && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function openResetConfirmModal() {
        const el = document.getElementById('modalConfirmResetSettings');
        if (el && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }
</script>
</body>
</html>

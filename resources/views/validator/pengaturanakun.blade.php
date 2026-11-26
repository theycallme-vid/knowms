<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Ruang Validator | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator External CSS -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
</head>
<body>

@php
    $user = $user ?? auth()->user();
    if ($user && !$user->relationLoaded('role')) {
        $user->load(['role', 'direktorat']);
    }
    $roleName = $user?->getRoleName() ?: 'Validator';
    $userName = $user?->nama ?? 'Pengguna KMS';
    $userEmail = $user?->email ?? '';
    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: 'KM';
    $activeTab = request('tab') === 'keamanan' || $errors->password->any() ? 'keamanan' : 'profil';
    $isUnitVal = $user ? $user->isValidatorUnit() : false;
    $valPrefix = $isUnitVal ? 'validator.unit.' : 'validator.';
@endphp

<div class="d-flex">
    <!-- Sidebar -->
    @include('partials.validator-sidebar', ['activeMenu' => 'pengaturanAkun'])

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Wrapper -->
    <main class="flex-grow-1 min-vw-0">
        <!-- Top Navbar (Tanpa Pencarian) -->
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Left: Mobile Toggle & Breadcrumb -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="top-breadcrumb d-flex align-items-center">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="crumb-sep">/</span>
                    <span>{{ $roleName }}</span>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">Pengaturan Akun</span>
                </div>
            </div>

            <!-- Right: Notification & Profile -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="top-icon-btn" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ $initials }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ $userName }}</div>
                            <div class="fs-11 text-muted">{{ $roleName }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end profile-dropdown mt-2">
                        <li>
                            <div class="px-3 py-2 mb-1 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ $userName }}</div>
                                <div class="fs-11 text-muted">{{ $userEmail }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route($valPrefix . 'pengaturanAkun') }}">
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 fs-13 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="date-badge">RUANG PENELAAH &middot; VALIDATOR</div>
                    <h1 class="page-title mb-1">Pengaturan Akun</h1>
                    <p class="page-subtitle">Kelola informasi profil dan keamanan kata sandi akun Anda.</p>
                </div>
            </div>

            <div class="row g-3 g-xl-4">
                <!-- Kolom Kiri: Ringkasan Akun & Navigasi -->
                <div class="col-12 col-lg-4 col-xl-3">
                    <div class="settings-card">
                        <div class="settings-card-body text-center">
                            <div class="settings-avatar-wrap mb-2">
                                <div class="settings-avatar-lg mx-auto" id="profileAvatarPreview" data-initials="{{ $initials }}">{{ $initials }}</div>
                                <input type="file" id="profileAvatarInput" class="d-none" accept="image/png,image/jpeg,image/webp,image/gif" onchange="handleLocalAvatarChange(event)">
                                <div class="avatar-upload-actions">
                                    <button type="button" class="btn-avatar-upload" onclick="document.getElementById('profileAvatarInput').click()">
                                        <i class="bi bi-camera"></i> Ubah Foto
                                    </button>
                                    <button type="button" class="btn-avatar-remove d-none" id="btnRemoveAvatar" onclick="removeLocalAvatar()">
                                        <i class="bi bi-trash3"></i> Hapus
                                    </button>
                                </div>
                                <div class="fs-11 text-muted mt-1">Tersimpan di perangkat Anda</div>
                            </div>
                            <div class="fw-bold fs-15 text-dark">{{ $user->nama }}</div>
                            <div class="fs-12 text-muted mb-2">{{ $user->email }}</div>
                            <span class="role-pill m-0">{{ $roleName }}</span>
                        </div>
                        <div class="px-3 pb-3 settings-nav">
                            <a href="?tab=profil" class="settings-nav-link {{ $activeTab === 'profil' ? 'active' : '' }}" data-tab-target="profil">
                                <i class="bi bi-person"></i> Informasi Profil
                            </a>
                            <a href="?tab=keamanan" class="settings-nav-link {{ $activeTab === 'keamanan' ? 'active' : '' }}" data-tab-target="keamanan">
                                <i class="bi bi-shield-lock"></i> Keamanan &amp; Kata Sandi
                            </a>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h2 class="settings-card-title">Detail Akun</h2>
                        </div>
                        <div class="settings-card-body py-2">
                            <div class="settings-meta-row">
                                <span class="meta-key">NPK</span>
                                <span class="meta-val">{{ $user->npk ?: '-' }}</span>
                            </div>
                            <div class="settings-meta-row">
                                <span class="meta-key">Peran</span>
                                <span class="meta-val">{{ $roleName }}</span>
                            </div>
                            <div class="settings-meta-row">
                                <span class="meta-key">Unit</span>
                                <span class="meta-val">{{ $user->direktorat?->nama ?? '-' }}</span>
                            </div>
                            <div class="settings-meta-row">
                                <span class="meta-key">Status</span>
                                <span class="meta-val">
                                    @if($user->is_active)
                                        <span class="badge rounded-pill fw-semibold" style="background-color:#ECFDF5;color:#059669;">Aktif</span>
                                    @else
                                        <span class="badge rounded-pill fw-semibold" style="background-color:#F1F5F9;color:#94A3B8;">Nonaktif</span>
                                    @endif
                                </span>
                            </div>
                            <div class="settings-meta-row">
                                <span class="meta-key">Terdaftar</span>
                                <span class="meta-val">{{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Form -->
                <div class="col-12 col-lg-8 col-xl-9">
                    <!-- TAB: Informasi Profil -->
                    <div data-tab-pane="profil" class="{{ $activeTab === 'profil' ? '' : 'd-none' }}">
                        <form method="POST" action="{{ route($valPrefix . 'pengaturanAkun.profile') }}" class="settings-card">
                            @csrf
                            <div class="settings-card-header">
                                <h2 class="settings-card-title">Informasi Profil</h2>
                                <div class="fs-12 text-muted">Perbarui nama dan alamat email yang digunakan untuk masuk ke sistem.</div>
                            </div>
                            <div class="settings-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="settings-label" for="inputNama">Nama Lengkap</label>
                                        <input type="text" id="inputNama" name="nama" maxlength="100"
                                               class="settings-input {{ $errors->profile->has('nama') ? 'is-invalid' : '' }}"
                                               value="{{ old('nama', $user->nama) }}" required>
                                        @if($errors->profile->has('nama'))
                                            <div class="settings-error">{{ $errors->profile->first('nama') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="settings-label" for="inputEmail">Email</label>
                                        <input type="email" id="inputEmail" name="email" maxlength="100"
                                               class="settings-input {{ $errors->profile->has('email') ? 'is-invalid' : '' }}"
                                               value="{{ old('email', $user->email) }}" required>
                                        @if($errors->profile->has('email'))
                                            <div class="settings-error">{{ $errors->profile->first('email') }}</div>
                                        @else
                                            <div class="settings-help">Email ini digunakan sebagai username saat login.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="settings-label">NPK</label>
                                        <input type="text" class="settings-input" value="{{ $user->npk ?: '-' }}" readonly>
                                        <div class="settings-help">NPK hanya dapat diubah oleh Super Admin.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="settings-label">Peran &amp; Unit</label>
                                        <input type="text" class="settings-input" value="{{ $roleName }} · {{ $user->direktorat?->nama ?? '-' }}" readonly>
                                        <div class="settings-help">Peran diatur melalui menu Pengguna &amp; Akses.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="settings-card-footer">
                                <button type="reset" class="btn-kms-cancel">Batalkan</button>
                                <button type="submit" class="btn-kms-primary">
                                    <i class="bi bi-check2"></i>
                                    <span>Simpan Perubahan</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: Keamanan & Kata Sandi -->
                    <div data-tab-pane="keamanan" class="{{ $activeTab === 'keamanan' ? '' : 'd-none' }}">
                        <form method="POST" action="{{ route($valPrefix . 'pengaturanAkun.password') }}" class="settings-card">
                            @csrf
                            <div class="settings-card-header">
                                <h2 class="settings-card-title">Ubah Kata Sandi</h2>
                                <div class="fs-12 text-muted">Gunakan minimal 8 karakter yang mengandung kombinasi huruf dan angka.</div>
                            </div>
                            <div class="settings-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="settings-label" for="currentPassword">Kata Sandi Saat Ini</label>
                                        <div class="password-toggle-wrap">
                                            <input type="password" id="currentPassword" name="current_password" autocomplete="current-password"
                                                   class="settings-input {{ $errors->password->has('current_password') ? 'is-invalid' : '' }}" required>
                                            <button type="button" class="password-toggle-btn" onclick="togglePasswordField('currentPassword', this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
                                        </div>
                                        @if($errors->password->has('current_password'))
                                            <div class="settings-error">{{ $errors->password->first('current_password') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 d-none d-md-block"></div>
                                    <div class="col-md-6">
                                        <label class="settings-label" for="newPassword">Kata Sandi Baru</label>
                                        <div class="password-toggle-wrap">
                                            <input type="password" id="newPassword" name="password" autocomplete="new-password"
                                                   class="settings-input {{ $errors->password->has('password') ? 'is-invalid' : '' }}" required>
                                            <button type="button" class="password-toggle-btn" onclick="togglePasswordField('newPassword', this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
                                        </div>
                                        @if($errors->password->has('password'))
                                            <div class="settings-error">{{ $errors->password->first('password') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="settings-label" for="confirmPassword">Konfirmasi Kata Sandi Baru</label>
                                        <div class="password-toggle-wrap">
                                            <input type="password" id="confirmPassword" name="password_confirmation" autocomplete="new-password" class="settings-input" required>
                                            <button type="button" class="password-toggle-btn" onclick="togglePasswordField('confirmPassword', this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-3 d-flex align-items-start gap-2 mt-3" style="background-color:#FFFBEB;border:1px solid #FEF3C7;padding:0.65rem 0.8rem;">
                                    <i class="bi bi-info-circle" style="color:#D97706;"></i>
                                    <div class="fs-12" style="color:#92400E;">Setelah kata sandi diubah, gunakan kata sandi baru pada saat login berikutnya.</div>
                                </div>
                            </div>
                            <div class="settings-card-footer">
                                <button type="reset" class="btn-kms-cancel">Batalkan</button>
                                <button type="submit" class="btn-kms-primary">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Perbarui Kata Sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL KONFIRMASI KELUAR (LOGOUT)
══════════════════════════════════════════════════════════════════ -->
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
                    <div class="avatar-circle">{{ $initials }}</div>
                    <div class="lh-sm">
                        <div class="fw-bold fs-13 text-dark">{{ $userName }}</div>
                        <div class="fs-11 text-muted">{{ $userEmail }}</div>
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

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('d-none');
    }

    function openLogoutModal() {
        const el = document.getElementById('modalLogoutConfirm');
        if (window.bootstrap && el) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (confirm('Apakah Anda yakin ingin keluar dari sistem KMS?')) {
            document.getElementById('logout-form').submit();
        }
    }

    function confirmLogout() {
        openLogoutModal();
    }

    function submitLogout(btn) {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>Memproses...</span>';
        }
        document.getElementById('logout-form').submit();
    }

    document.querySelectorAll('[data-tab-target]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = this.getAttribute('data-tab-target');
            document.querySelectorAll('[data-tab-target]').forEach(l => l.classList.toggle('active', l === this));
            document.querySelectorAll('[data-tab-pane]').forEach(p => p.classList.toggle('d-none', p.getAttribute('data-tab-pane') !== target));
            history.replaceState(null, '', '?tab=' + target);
        });
    });

    function togglePasswordField(id, btn) {
        const input = document.getElementById(id);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    }

    const LOCAL_AVATAR_KEY = 'kms_local_avatar_user_{{ $user->id }}';

    function renderLocalAvatar() {
        const savedDataUrl = localStorage.getItem(LOCAL_AVATAR_KEY);
        const mainAvatar = document.getElementById('profileAvatarPreview');
        const removeBtn = document.getElementById('btnRemoveAvatar');
        const initials = mainAvatar ? mainAvatar.getAttribute('data-initials') : '{{ $initials }}';

        if (mainAvatar) {
            if (savedDataUrl) {
                mainAvatar.innerHTML = '<img src="' + savedDataUrl + '" alt="Foto Profil">';
                if (removeBtn) removeBtn.classList.remove('d-none');
            } else {
                mainAvatar.textContent = initials;
                if (removeBtn) removeBtn.classList.add('d-none');
            }
        }

        document.querySelectorAll('.avatar-circle').forEach(function (el) {
            if (savedDataUrl) {
                el.innerHTML = '<img src="' + savedDataUrl + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
            } else {
                el.textContent = initials;
            }
        });
    }

    function handleLocalAvatarChange(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Pilih file gambar (JPG, PNG, WEBP, atau GIF).');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 2 MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                localStorage.setItem(LOCAL_AVATAR_KEY, e.target.result);
                renderLocalAvatar();
            } catch (err) {
                alert('Gagal menyimpan gambar di browser. Pilih gambar dengan ukuran lebih kecil.');
            }
        };
        reader.readAsDataURL(file);
    }

    function removeLocalAvatar() {
        localStorage.removeItem(LOCAL_AVATAR_KEY);
        const input = document.getElementById('profileAvatarInput');
        if (input) input.value = '';
        renderLocalAvatar();
    }

    document.addEventListener('DOMContentLoaded', renderLocalAvatar);
</script>
</body>
</html>

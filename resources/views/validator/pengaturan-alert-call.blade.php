<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Alert Call - Ruang Validator | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Data Entry & Validator Styles -->
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">

    <style>
        .unit-card-select {
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            background: #FFFFFF;
            padding: 1.25rem 1.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .unit-card-select:hover {
            border-color: #85172C;
            box-shadow: 0 8px 20px rgba(133, 23, 44, 0.06);
            transform: translateY(-2px);
        }

        .unit-card-select.is-selected {
            border-color: #85172C;
            background: #FFFDFD;
            box-shadow: 0 4px 14px rgba(133, 23, 44, 0.08);
        }

        .unit-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #85172C;
            cursor: pointer;
        }

        .user-avatar-mini {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #F1F5F9;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #CBD5E1;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar Validator -->
    @include('partials.validator-sidebar', ['activeMenu' => 'alert-call'])

    <!-- Overlay Mobile -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1">
        <!-- Topbar Header -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="page-title">
                    <h1 class="h4 fw-bold mb-0 text-dark">Setting Alert Call Layanan Akademik</h1>
                    <span class="text-muted fs-12">Atur unit / direktorat yang berhak menerima notifikasi panggilan suara langsung dari mahasiswa</span>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="d-flex align-items-center gap-3">
                <div class="user-profile d-flex align-items-center gap-2">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'V', 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="user-name">{{ auth()->user()->nama ?? 'Validator KMS' }}</div>
                        <div class="user-role">Validator</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-body p-4">
            <!-- Alert Notifikasi -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Info Callout -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(180deg, #FAF8F5 0%, #FFFFFF 100%); border: 1px solid #EDE7E1 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 p-2.5 bg-danger bg-opacity-10 text-danger flex-shrink-0" style="color: #85172C !important;">
                            <i class="bi bi-telephone-inbound-fill fs-3"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold text-dark mb-1">Mekanisme Notifikasi Panggilan (Call Routing)</h2>
                            <p class="text-muted fs-13 mb-0" style="line-height: 1.6;">
                                Ketika mahasiswa menekan tombol <strong>Mulai Panggilan</strong> pada halaman Layanan Akademik, alarm notifikasi panggilan masuk <strong>hanya akan dimunculkan kepada akun petugas yang terdaftar di Unit/Direktorat yang Anda pilih di bawah ini</strong>. Akun di luar unit yang dipilih (termasuk Super Admin dan Validator yang tidak bertugas di unit tersebut) <strong>tidak akan menerima gangguan alert call</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Pengaturan Unit -->
            <form action="{{ route('validator.pengaturanAlertCall.update') }}" method="POST">
                @csrf

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="h6 fw-bold mb-0 text-dark">Daftar Unit / Direktorat Kampus</h3>
                                <div class="text-muted fs-12 mt-0.5">Centang satu atau beberapa unit yang ditugaskan untuk menerima panggilan mahasiswa</div>
                            </div>
                            <span class="badge rounded-pill" style="background-color: #FCEEEF; color: #85172C; font-size: 0.72rem; padding: 0.4rem 0.85rem; font-weight: 700;">
                                Disimpan ke Database (tsettings)
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            @forelse($direktorats as $dir)
                                @php
                                    $isSelected = in_array((int)$dir->id, $selectedUnitIds, true);
                                    $userCount = $dir->users->count();
                                @endphp
                                <div class="col-md-6 col-xl-4">
                                    <label class="unit-card-select w-100 d-block {{ $isSelected ? 'is-selected' : '' }}" for="unit_check_{{ $dir->id }}">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="checkbox" 
                                                       name="unit_ids[]" 
                                                       value="{{ $dir->id }}" 
                                                       id="unit_check_{{ $dir->id }}" 
                                                       class="unit-checkbox" 
                                                       {{ $isSelected ? 'checked' : '' }}
                                                       onchange="toggleCardSelected(this)">
                                                <span class="fw-bold text-dark fs-14">{{ $dir->nama }}</span>
                                            </div>
                                            <span class="badge rounded-pill {{ $isSelected ? 'bg-danger text-white' : 'bg-light text-muted border' }}" style="font-size: 0.68rem;">
                                                {{ $isSelected ? 'Aktif Menerima' : 'Tidak Aktif' }}
                                            </span>
                                        </div>

                                        <!-- Informasi Staf di Unit Tersebut -->
                                        <div class="mt-2 pt-2 border-top border-light fs-12 text-muted">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span>Total Petugas:</span>
                                                <span class="fw-semibold text-dark">{{ $userCount }} Akun</span>
                                            </div>
                                            @if($userCount > 0)
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    @foreach($dir->users->take(3) as $usr)
                                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;" title="{{ $usr->email }} ({{ $usr->role->nama ?? 'Staff' }})">
                                                            <i class="bi bi-person me-0.5"></i>{{ Str::limit($usr->nama, 14) }}
                                                        </span>
                                                    @endforeach
                                                    @if($userCount > 3)
                                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">+{{ $userCount - 3 }} lainnya</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-warning fs-11 mt-1">
                                                    <i class="bi bi-info-circle me-1"></i>Belum ada akun petugas di unit ini
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted fs-13">
                                    Tidak ada data unit/direktorat di database.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card-footer bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                        <span class="fs-12 text-muted">
                            <i class="bi bi-shield-check text-success me-1"></i>Perubahan langsung berlaku secara real-time pada panggilan berikutnya.
                        </span>
                        <div class="d-flex gap-2">
                            <a href="{{ route('validator.dashboard') }}" class="btn btn-outline-secondary px-4 py-2 fs-13 fw-semibold rounded-pill">
                                Batal
                            </a>
                            <button type="submit" class="btn px-4 py-2 fs-13 fw-semibold text-white rounded-pill shadow-sm" style="background-color: #85172C;">
                                <i class="bi bi-check-lg me-1"></i>Simpan Pengaturan Unit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-box-arrow-right text-danger fs-1 d-block mb-3"></i>
                <h3 class="h5 fw-bold mb-2">Konfirmasi Keluar</h3>
                <p class="text-muted fs-13 mb-4">Apakah Anda yakin ingin keluar dari Ruang Validator?</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill rounded-pill py-2 fs-13" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" class="flex-fill m-0">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fs-13">Ya, Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleCardSelected(cb) {
        const card = cb.closest('.unit-card-select');
        if (card) {
            if (cb.checked) {
                card.classList.add('is-selected');
            } else {
                card.classList.remove('is-selected');
            }
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar) sidebar.classList.toggle('show');
        if (backdrop) backdrop.classList.toggle('d-none');
    }

    function openLogoutModal() {
        const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
        modal.show();
    }
</script>



</body>
</html>

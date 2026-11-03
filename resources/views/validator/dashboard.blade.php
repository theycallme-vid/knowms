<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Validator - Validasi FAQ | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- External CSS in public/ -->
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <div class="sidebar-brand mb-3">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="sidebar-logo-img">
            </div>

            <div class="mb-3">
                <span class="role-pill">Ruang Validator</span>
            </div>

            <nav class="nav flex-column">
                <a href="{{ route('validator.dashboard') }}" class="nav-link-custom active">
                    <i class="bi bi-check2-square"></i>
                    <span>Antrean Validasi FAQ</span>
                </a>
            </nav>
        </div>

        <div>
            <hr class="sidebar-divider my-3">
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link-custom text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Mobile Sidebar Backdrop -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="flex-grow-1 min-vw-0">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center text-muted fs-13">
                    <i class="bi bi-house me-2"></i>
                    <span>Ruang kerja</span>
                    <span class="mx-2 text-muted">/</span>
                    <span class="text-dark fw-semibold">Validator</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()?->nama ?? 'VL', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()?->nama ?? 'Validator KMS' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()?->getRoleName() ?: 'Validator' }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                        <li>
                            <a class="dropdown-item py-2 fs-13 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <div class="mt-2 mb-4">
                <div class="date-badge">RUANG PEMERIKSA &middot; VALIDATOR</div>
                <h1 class="display-serif fs-2 mb-1">Validasi & Penerbitan FAQ</h1>
                <p class="text-muted fs-14 mb-0">Periksa pengajuan FAQ dari Data Entry (satuan maupun hasil import Excel/PDF), setujui untuk diterbitkan atau berikan catatan revisi.</p>
            </div>

            <!-- 3 Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu Validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $submittedCount ?? 0 }}</div>
                            <div class="fs-12 text-muted">Perlu diperiksa oleh Anda</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #DCFCE7; color: #16A34A;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Telah Disetujui (Terbit)</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $approvedCount ?? 0 }}</div>
                            <div class="fs-12 text-muted">Tersedia untuk mahasiswa</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #FEE2E2; color: #DC2626;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <div>
                            <div class="fs-12 text-muted fw-medium mb-1">Dikembalikan (Perlu Revisi)</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $rejectedCount ?? 0 }}</div>
                            <div class="fs-12 text-muted">Menunggu perbaikan Data Entry</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Validasi FAQ -->
            <div class="card-custom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="filter-pill active" onclick="filterValTable('all', this)">Semua ({{ $total ?? 0 }})</button>
                        <button type="button" class="filter-pill" onclick="filterValTable('submitted', this)">Menunggu Validasi ({{ $submittedCount ?? 0 }})</button>
                        <button type="button" class="filter-pill" onclick="filterValTable('approved', this)">Terbit ({{ $approvedCount ?? 0 }})</button>
                        <button type="button" class="filter-pill" onclick="filterValTable('rejected', this)">Perlu Revisi ({{ $rejectedCount ?? 0 }})</button>
                    </div>
                    <div class="position-relative" style="min-width: 260px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-12"></i>
                        <input type="text" id="valSearchInput" oninput="applyValSearch()" class="form-control form-control-sm rounded-3 ps-5 fs-13" placeholder="Cari pertanyaan atau kata kunci...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 115px;">KODE / VERSI</th>
                                <th style="min-width: 260px;">PERTANYAAN & JAWABAN</th>
                                <th style="width: 180px;">KATA KUNCI</th>
                                <th style="width: 170px;">SUMBER PUSTAKA</th>
                                <th style="width: 130px;">PENULIS</th>
                                <th style="width: 130px;">STATUS</th>
                                <th style="width: 190px; text-align: right;">TINDAKAN VALIDATOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!isset($validatorFaqs) || $validatorFaqs->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted fs-13">
                                        Belum ada pengajuan FAQ dari Data Entry saat ini.
                                    </td>
                                </tr>
                            @else
                                @foreach($validatorFaqs as $faq)
                                    @php
                                        $latest = $faq->latest_version;
                                        $st = strtolower($latest->status ?? 'submitted');
                                        $dp = $latest?->daftarPustaka;
                                    @endphp
                                    <tr class="val-row" data-status="{{ $st }}">
                                        <td>
                                            <div class="fw-bold text-dark fs-12">{{ $faq->id }}</div>
                                            <div class="fs-11 text-muted">{{ $latest?->id }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-13 mb-1">{{ $faq->pertanyaan }}</div>
                                            <div class="fs-12 text-muted line-clamp-2">{!! strip_tags($latest?->jawaban ?? '') !!}</div>
                                            @if($st === 'rejected' && $latest?->catatan)
                                                <div class="mt-1 p-1.5 rounded bg-danger-subtle text-danger fs-11">
                                                    <strong>Catatan Revisi:</strong> {{ $latest->catatan }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($faq->tags as $t)
                                                    <span class="keyword-pill-blue">{{ $t->nama }}</span>
                                                @empty
                                                    <span class="keyword-pill-empty">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            @if($dp)
                                                <div class="fw-bold text-dark fs-12">{{ $dp->link }}</div>
                                                <div class="fs-11 text-muted">Tipe: {{ strtoupper($dp->type) }}</div>
                                            @else
                                                <span class="text-muted fs-12">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark fs-12">{{ $faq->dataEntry?->nama ?? 'Data Entry' }}</div>
                                            <div class="fs-11 text-muted">{{ $latest?->updated_at?->translatedFormat('d M Y H:i') }}</div>
                                        </td>
                                        <td>
                                            @if($st === 'submitted')
                                                <span class="badge-status badge-status-validasi">Menunggu Validasi</span>
                                            @elseif($st === 'approved')
                                                <span class="badge-status badge-status-terbit">Terbit</span>
                                            @else
                                                <span class="badge-status badge-status-revisi">Perlu Revisi</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;" class="text-nowrap">
                                            @if($st === 'submitted')
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <form action="{{ route('validator.faq.approve', $faq->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-3 fs-11 fw-semibold px-2.5 py-1">
                                                            <i class="bi bi-check2"></i> Setujui
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3 fs-11 fw-semibold px-2.5 py-1"
                                                            onclick="openRejectModal('{{ $faq->id }}', '{{ addslashes($faq->pertanyaan) }}')">
                                                        <i class="bi bi-x-lg"></i> Revisi
                                                    </button>
                                                </div>
                                            @else
                                                <span class="fs-11 text-muted">Telah diproses</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Beri Catatan Revisi (Reject) -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold fs-16" id="rejectModalTitle">Kembalikan untuk Revisi</h5>
                        <div class="fs-12 text-muted" id="rejectModalSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label-custom form-label-required">Catatan Revisi untuk Data Entry</label>
                    <textarea name="catatan" rows="3" class="form-control fs-13 rounded-3" placeholder="Tuliskan bagian jawaban atau sumber pustaka yang perlu diperbaiki..." required></textarea>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-3 px-3">Kirim Catatan Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS & SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentValStatus = 'all';

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('d-none');
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Konfirmasi Keluar',
            text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) document.getElementById('logout-form').submit();
        });
    }

    function openRejectModal(faqId, pertanyaan) {
        const form = document.getElementById('rejectForm');
        form.action = `{{ url('/validator/faq') }}/${faqId}/reject`;
        document.getElementById('rejectModalTitle').textContent = 'Kembalikan FAQ ' + faqId + ' untuk Revisi';
        document.getElementById('rejectModalSub').textContent = pertanyaan;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    function filterValTable(st, btn) {
        currentValStatus = st;
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        applyValSearch();
    }

    function applyValSearch() {
        const q = (document.getElementById('valSearchInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.val-row').forEach(row => {
            const st = row.getAttribute('data-status');
            const txt = row.textContent.toLowerCase();
            const matchSt = (currentValStatus === 'all') || (st === currentValStatus);
            const matchQ = !q || txt.includes(q);
            row.style.display = (matchSt && matchQ) ? '' : 'none';
        });
    }

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3500
        });
    });
    @endif

    @if(session('error'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'Perhatian',
            text: "{{ session('error') }}",
            confirmButtonColor: '#85172C'
        });
    });
    @endif
</script>
</body>
</html>


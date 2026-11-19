<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar FAQ - Ruang Validator | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator CSS -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    @php
                $authUser = auth()->user();
                if ($authUser && !$authUser->relationLoaded('role')) {
                    $authUser->load('role');
                }
                $roleName = $authUser ? $authUser->getRoleName() : 'Validator';
            @endphp
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Validator</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom active">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('validator.validasiFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-list-check"></i>
                    <span>Validasi FAQ</span>
                </a>
                <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider">
            <a href="{{ route('validator.pengaturanAkun') }}" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
         id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════ -->
    <main class="flex-grow-1 min-vw-0">

        <!-- Top Navbar (Tanpa Pencarian) -->
        @php
            $tbUser = auth()->user();
            if ($tbUser && !$tbUser->relationLoaded('role')) {
                $tbUser->load('role');
            }
            $tbRole = $tbUser?->getRoleName() ?: 'Validator';
            $tbName = $tbUser?->nama ?? 'Pengguna KMS';
            $tbEmail = $tbUser?->email ?? '';
            $tbInitials = collect(preg_split('/\s+/', trim($tbName)))
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'KM';
        @endphp
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
                    <span>{{ $tbRole }}</span>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current">Daftar FAQ</span>
                </div>
            </div>

            <!-- Right: Notification & Profile -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="top-icon-btn" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('validator.pengaturanAkun') }}">
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

        <!-- Content -->
        <div class="content-area">

            <!-- Page Header (Ukuran & Warna Seragam) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Daftar FAQ</h1>
                    <p class="fs-13 text-muted mb-0">
                        Tinjau pertanyaan, jawaban, dan rujukan sumber sebelum menyetujui FAQ.
                    </p>
                </div>
            </div>

            <!-- 5 Kotak Statistik Sejajar (Ringkas: Hanya Judul & Jumlah) -->
            <div class="stat-grid-5 mb-4">
                {{-- Total FAQ --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total FAQ</div>
                        <div class="stat-value">{{ $totalFaq }}</div>
                    </div>
                </div>

                {{-- Terbit --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Terbit</div>
                        <div class="stat-value">{{ $totalAktif }}</div>
                    </div>
                </div>

                {{-- Disetujui --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EDE9FE; color:#7C3AED;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Disetujui</div>
                        <div class="stat-value">{{ $totalApproved }}</div>
                    </div>
                </div>

                {{-- Menunggu Validasi --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FFFBEB; color:#D97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Menunggu Validasi</div>
                        <div class="stat-value">{{ $totalSubmitted }}</div>
                    </div>
                </div>

                {{-- Ditolak --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FEF2F2; color:#DC2626;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-value">{{ $totalRejected ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Card Header Title (Tanpa subjudul 'Semua Fakultas') -->
                <div class="table-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="fw-bold fs-15 text-dark mb-0">Daftar Seluruh FAQ</h2>
                    </div>
                </div>

                <!-- ── Filter Bar (Satu Baris: Search + Kategori Fakultas + Status + Sort) ── -->
                <!-- ── Filter Bar (Search + Kata Kunci + Status + Sort, Auto-Submit) ── -->
                @include('partials.daftar-faq-filter-bar', [
                    'actionRoute' => route('validator.daftarFAQ'),
                    'kataKunciOptions' => $kataKunciOptions ?? collect(),
                ])

                <!-- ── Table ── -->
                <div class="table-responsive">
                    <table class="faq-table">
                        <thead>
                            <tr>
                                <th style="width:115px;">Kode / Versi</th>
                                <th>Pertanyaan</th>
                                <th style="width:155px;">Data Entry</th>
                                <th style="width:135px;">Waktu Pengajuan</th>
                                <th style="width:215px;">Status</th>
                                <th style="width:120px;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                @php
                                    $rawLatestVersion = $faq->versions->first();
                                    $activeFaqVersion = $faq->versions->first(fn ($v) => strtolower((string) $v->status) === 'active');
                                    $latestVersion = ($rawLatestVersion && strtolower((string) $rawLatestVersion->status) === 'inactive' && $activeFaqVersion)
                                        ? $activeFaqVersion
                                        : $rawLatestVersion;
                                    $status        = $latestVersion?->status ?? 'Draft';
                                    $versionCount  = max(1, $faq->versions->count());
                                    $de            = $faq->dataEntry;
                                    $statusClass   = match($status) {
                                        'Approved'  => 'badge-approved',
                                        'Active'    => 'badge-active',
                                        'Submitted' => 'badge-submitted',
                                        'Rejected'  => 'badge-rejected',
                                        'InActive'  => 'badge-inactive',
                                        default     => 'badge-draft',
                                    };
                                    $statusLabel   = match($status) {
                                        'Active'    => 'Terbit',
                                        'Approved'  => 'Disetujui',
                                        'Submitted' => 'Menunggu Validasi',
                                        'Rejected'  => 'Ditolak',
                                        'InActive'  => 'InActive',
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
                                    $createdAtJkt  = $faq->created_at ? $faq->created_at->timezone('Asia/Jakarta') : null;
                                @endphp
                                <tr>
                                    <!-- Kode / Versi -->
                                    <td>
                                        <div class="faq-code">{{ $faq->id }}</div>
                                        <div class="faq-version">Versi {{ $versionCount }}</div>
                                    </td>

                                    <!-- Pertanyaan + Tags -->
                                    <td>
                                        <div class="faq-question">{{ Str::limit($faq->pertanyaan, 85) }}</div>
                                        @if($faq->tags->isNotEmpty())
                                            <div class="faq-tags">
                                                @foreach($faq->tags->take(3) as $tag)
                                                    <span class="tag-pill">{{ $tag->nama }}</span>
                                                @endforeach
                                                @if($faq->tags->count() > 3)
                                                    <span class="tag-pill">+{{ $faq->tags->count() - 3 }}</span>
                                                @endif
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="faq-tags">
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Data Entry (Cukup Namanya Saja) -->
                                    <td>
                                        <div class="fs-13 fw-semibold text-dark">{{ $de?->nama ?? '—' }}</div>
                                    </td>

                                    <!-- Waktu Pengajuan (Timezone Jakarta GMT+7) -->
                                    <td>
                                        <div class="fs-13 text-dark">
                                            {{ $createdAtJkt ? $createdAtJkt->translatedFormat('d M Y') : '—' }}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            {{ $createdAtJkt ? $createdAtJkt->format('H.i') . ' WIB' : '' }}
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        @php
                                            $schedInfoDaftar = ($status === 'Approved' && $latestVersion) ? $latestVersion->getPublishScheduleInfo() : null;
                                            $schedModeDaftar = $schedInfoDaftar['mode'] ?? null;
                                        @endphp
                                        @if($status === 'Approved' && $schedModeDaftar === 'routine')
                                            <span class="badge-status badge-routine-wait">
                                                <i class="bi bi-clock"></i>
                                                Menunggu Publikasi Rutin
                                            </span>
                                        @elseif($status === 'Approved' && $schedModeDaftar === 'scheduled')
                                            <div class="status-cell-stack">
                                                <span class="badge-status badge-scheduled-wait">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Terjadwal Publikasi
                                                </span>
                                                @if(!empty($schedInfoDaftar['scheduled_at']))
                                                    <span class="status-subnote">
                                                        {{ \Carbon\Carbon::parse($schedInfoDaftar['scheduled_at'])->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }} WIB
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge-status {{ $statusClass }}">
                                                <i class="bi {{ $statusIcon }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi (Hanya Tombol View, Tanpa Titik 3) -->
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center justify-content-end">
                                            <button type="button" class="btn-action btn-action-view" onclick="viewValidatorFaqDetail('{{ $faq->id }}')">
                                                <i class="bi bi-eye"></i> View
                                            </button>
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

<!-- ══════════════════════════════════════════════════════════════════
     MODAL KONFIRMASI KELUAR (LOGOUT)
══════════════════════════════════════════════════════════════════ -->
@php
    $lmUser = auth()->user();
    $lmName = $lmUser?->nama ?? 'Pengguna KMS';
    $lmInitials = collect(preg_split('/\s+/', trim($lmName)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: 'KM';
@endphp
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
                    <div class="avatar-circle">{{ $lmInitials }}</div>
                    <div class="lh-sm">
                        <div class="fw-bold fs-13 text-dark">{{ $lmName }}</div>
                        <div class="fs-11 text-muted">{{ $lmUser?->email }}</div>
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

<script>
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
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    function confirmDeleteValidatorFaq(faqId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus FAQ ' + faqId + '?',
                text: 'FAQ dan seluruh riwayat versinya akan dihapus permanen dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    document.getElementById('validator-delete-faq-' + faqId)?.submit();
                }
            });
        } else if (confirm('Yakin ingin menghapus FAQ ' + faqId + '?')) {
            document.getElementById('validator-delete-faq-' + faqId)?.submit();
        }
    }

    function toggleValidatorVersionStatus(faqId, versionId, targetStatus) {
        fetch(`{{ url('/validator/faq-version') }}/${versionId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: targetStatus })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                // Muat ulang isi modal View agar status Active/InActive langsung terbarui
                viewValidatorFaqDetail(faqId, true);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal mengubah status versi.' });
            }
        })
        .catch(err => console.error(err));
    }

    function viewValidatorFaqDetail(faqId, isRefresh = false) {
        fetch(`{{ url('/validator/faq') }}/${faqId}/detail`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const latest = d.latest_version || {};
                const tagsHtml = (d.tags && d.tags.length > 0)
                    ? `<div class="d-flex flex-wrap gap-2" style="margin-bottom: 1.15rem;">${d.tags.map(t => `<span class="badge rounded-pill" style="background-color:#F8F5F1;color:#85172C;border:1px solid #EDE7E1;font-size:0.76rem;padding:0.35rem 0.8rem;font-weight:600;">${t}</span>`).join('')}</div>`
                    : '';

                const rawSt = (latest.status || 'Draft').toLowerCase();
                let statusBadge = `<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.75rem;padding:0.4rem 0.85rem;">Draft</span>`;
                if (rawSt === 'active') statusBadge = '<span class="badge rounded-pill" style="background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#FFFFFF;box-shadow:0 2px 6px rgba(5,150,105,0.25);font-size:0.75rem;padding:0.4rem 0.9rem;font-weight:700;"><i class="bi bi-lightning-fill me-1"></i>Terbit</span>';
                else if (rawSt === 'approved') statusBadge = '<span class="badge rounded-pill" style="background-color:#EDE9FE;color:#6D28D9;border:1px solid #DDD6FE;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>';
                else if (rawSt === 'submitted') statusBadge = '<span class="badge rounded-pill" style="background-color:#FFFBEB;color:#D97706;border:1px solid #FDE68A;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi</span>';
                else if (rawSt === 'rejected') statusBadge = '<span class="badge rounded-pill" style="background-color:#FEF2F2;color:#DC2626;border:1px solid #FECACA;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>';
                else if (rawSt === 'inactive') statusBadge = '<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;border:1px solid #CBD5E1;font-size:0.75rem;padding:0.4rem 0.85rem;">InActive</span>';

                let versionsHistoryHtml = '';
                if (d.versions && d.versions.length > 0) {
                    versionsHistoryHtml = `
                        <div style="margin-top: 0.25rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.5rem;">Riwayat Versi Jawaban</div>
                            <div class="rounded-3 overflow-auto" style="border: 1px solid #EDE7E1; max-height: 210px;">
                                <table class="table table-sm mb-0 align-middle" style="font-size: 0.83rem;">
                                    <thead style="background-color: #FBF9F7; border-bottom: 1px solid #EDE7E1; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="text-secondary fw-semibold" style="width: 125px; font-size: 0.74rem; padding: 0.7rem 1rem;">ID Versi</th>
                                            <th class="text-secondary fw-semibold" style="font-size: 0.74rem; padding: 0.7rem 1rem;">Jawaban Versi Tersebut</th>
                                            <th class="text-secondary fw-semibold text-center" style="width: 175px; font-size: 0.74rem; padding: 0.7rem 1rem;">Status</th>
                                            <th class="text-secondary fw-semibold" style="width: 155px; font-size: 0.74rem; padding: 0.7rem 1rem;">Diperbarui</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${d.versions.map(v => {
                                            const vSt = (v.status || 'Draft').toLowerCase();
                                            const isVerActive = vSt === 'active';
                                            const canToggleVer = ['active', 'inactive'].includes(vSt);

                                            let statusActionHtml = '';
                                            if (canToggleVer) {
                                                statusActionHtml = `
                                                    <div class="d-inline-flex align-items-center gap-1 p-1 rounded-pill" style="background-color: #F8F5F1; border: 1px solid #EDE7E1;">
                                                        <button type="button"
                                                                ${!isVerActive ? `onclick="toggleValidatorVersionStatus('${d.id}', '${v.id}', 'Active')"` : ''}
                                                                class="btn btn-sm rounded-pill fw-semibold"
                                                                style="font-size: 0.72rem; padding: 0.25rem 0.7rem; ${isVerActive ? 'background-color: #16A34A; color: #FFFFFF; border: none; cursor: default;' : 'background-color: transparent; color: #64748B; border: none;'}">
                                                            Active
                                                        </button>
                                                        <button type="button"
                                                                ${isVerActive ? `onclick="toggleValidatorVersionStatus('${d.id}', '${v.id}', 'InActive')"` : ''}
                                                                class="btn btn-sm rounded-pill fw-semibold"
                                                                style="font-size: 0.72rem; padding: 0.25rem 0.7rem; ${!isVerActive ? 'background-color: #475569; color: #FFFFFF; border: none; cursor: default;' : 'background-color: transparent; color: #64748B; border: none;'}">
                                                            InActive
                                                        </button>
                                                    </div>
                                                `;
                                            } else {
                                                statusActionHtml = `
                                                    <div class="d-inline-flex align-items-center gap-1 p-1 rounded-pill" style="background-color: #F1F5F9; border: 1px solid #E2E8F0; opacity: 0.55; cursor: not-allowed;" title="Dimatikan — hanya dapat dinyalakan setelah FAQ diterbitkan">
                                                        <button type="button"
                                                                disabled
                                                                class="btn btn-sm rounded-pill fw-semibold"
                                                                style="font-size: 0.72rem; padding: 0.25rem 0.7rem; background-color: transparent; color: #94A3B8; border: none; cursor: not-allowed;">
                                                            Active
                                                        </button>
                                                        <button type="button"
                                                                disabled
                                                                class="btn btn-sm rounded-pill fw-semibold"
                                                                style="font-size: 0.72rem; padding: 0.25rem 0.7rem; background-color: #CBD5E1; color: #64748B; border: none; cursor: not-allowed;">
                                                            InActive
                                                        </button>
                                                    </div>
                                                `;
                                            }

                                            return `
                                                <tr style="border-bottom: 1px solid #F3EFEA;">
                                                    <td class="fw-bold text-nowrap" style="color: #85172C; padding: 0.8rem 1rem;">${v.id}</td>
                                                    <td class="text-dark" style="line-height: 1.55; word-break: break-word; padding: 0.8rem 1rem;">${v.jawaban || '-'}</td>
                                                    <td class="text-center" style="padding: 0.8rem 1rem;">${statusActionHtml}</td>
                                                    <td class="text-secondary text-nowrap" style="font-size: 0.77rem; padding: 0.8rem 1rem;">${v.updated_at || '-'}</td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: '',
                    html: `
                        <div class="text-start" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #1E293B;">
                            <!-- Header Banner Tema Website -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-bottom: 1px solid #EDE7E1; padding: 0 3rem 0.95rem 0; margin-bottom: 1.15rem;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.78rem; padding: 0.42rem 0.9rem;">${d.id}</span>
                                    ${statusBadge}
                                    <span class="badge rounded-pill" style="background-color: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; font-size: 0.74rem; padding: 0.38rem 0.75rem;">
                                        <i class="bi bi-eye me-1"></i>${d.views_count ?? 0} dilihat
                                        &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>${d.likes_count ?? 0}
                                        &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>${d.dislikes_count ?? 0}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="bi bi-person me-1"></i><strong class="text-dark">${d.data_entry || '-'}</strong></span>
                                    <span>&middot;</span>
                                    <span><i class="bi bi-clock me-1"></i>${d.created_at || '-'}</span>
                                </div>
                            </div>

                            <!-- Pertanyaan -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Pertanyaan</div>
                                <div class="rounded-3 fw-semibold text-dark" style="background-color: #FFFDFB; border: 1px solid #EDE7E1; font-size: 0.94rem; line-height: 1.55; padding: 0.85rem 1.05rem;">
                                    ${d.pertanyaan || '-'}
                                </div>
                            </div>

                            <!-- Jawaban -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Jawaban (${latest.id || (d.id + '-V1')})</div>
                                <div class="rounded-3 bg-white text-dark" style="border: 1px solid #EDE7E1; font-size: 0.89rem; max-height: 165px; overflow-y: auto; line-height: 1.65; padding: 0.9rem 1.05rem;">
                                    ${latest.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}
                                </div>
                            </div>

                            ${tagsHtml}

                            ${versionsHistoryHtml}
                        </div>
                    `,
                    width: '820px',
                    showCloseButton: true,
                    confirmButtonColor: '#85172C',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0 kms-view-modal'
                    },
                    didClose: () => {
                        if (window._validatorVersionChanged) {
                            window._validatorVersionChanged = false;
                            window.location.reload();
                        }
                    }
                });

                if (isRefresh) {
                    window._validatorVersionChanged = true;
                }
            }
        })
        .catch(err => console.error(err));
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

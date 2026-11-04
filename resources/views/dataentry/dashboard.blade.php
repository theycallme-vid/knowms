<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar FAQ - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Data Entry Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand mb-3">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div class="mb-3">
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('dataentry.dashboard') }}" class="nav-link-custom active">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="{{ route('dataentry.katakunci') }}" class="nav-link-custom">
                    <i class="bi bi-tags"></i>
                    <span>Daftar Kata Kunci</span>
                </a>
                <a href="{{ route('dataentry.sumberpustaka') }}" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider my-3">
            <!-- Account Settings & Logout -->
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
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
                    <span class="mx-2 text-muted">/</span>
                    <span class="text-dark fw-semibold">Data Entry</span>
                </div>
            </div>

            <!-- Right: Search, Profile & Notification -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" id="topSearchInput" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()?->nama ?? 'NP', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()?->nama ?? 'Nadia Putri' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()?->getRoleName() ?: 'Data Entry' }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ Auth::user()?->nama ?? 'Nadia Putri' }}</div>
                                <div class="fs-11 text-muted">{{ Auth::user()?->email ?? 'nadiaputri@kms.ac.id' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 d-flex align-items-center gap-2" href="#">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan Akun
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

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RUANG PENULIS &middot; DATA ENTRY</div>
                    <h1 class="display-serif fs-2 mb-1" style="font-size: 1.85rem; font-weight: 500;">Pengetahuan yang Anda tulis, membantu banyak langkah.</h1>
                    <p class="text-muted fs-14 mb-0">Kelola FAQ Anda, lengkapi sumber resmi, lalu ajukan untuk divalidasi.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-outline-secondary bg-white rounded-3 px-3 py-2 text-dark fs-13 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i>
                        <span>Import File</span>
                    </button>
                    <a href="{{ route('dataentry.buatfaq') }}" class="btn-maroon">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat FAQ</span>
                    </a>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 mb-4">
                <!-- Stat 1: Total FAQ saya -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-copy"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Total FAQ saya</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $total }}</div>
                            <div class="fs-12 text-muted">{{ $approvedCount }} di antaranya telah terbit</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Draft -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Draft</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $draftCount }}</div>
                            <div class="fs-12 text-muted">Lanjutkan penulisan Anda</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Menunggu Validasi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu Validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $submittedCount }}</div>
                            <div class="fs-12 text-muted">Sedang ditinjau validator</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Perlu Revisi -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card-custom d-flex align-items-start gap-3 h-100">
                        <div class="stat-icon" style="background-color: #FEE2E2; color: #DC2626;">
                            <i class="bi bi-chat-square-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Perlu Revisi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">{{ $rejectedCount }}</div>
                            <div class="fs-12" style="color: #DC2626;">{{ $rejectedCount > 0 ? 'Ada catatan untuk ditindaklanjuti' : 'Tidak ada catatan revisi' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Full-Width FAQ Table Card -->
            <div class="card-custom">
                <!-- Table Header Info -->
                <div class="mb-3">
                    <h2 class="fw-bold fs-16 text-dark mb-1">Daftar FAQ</h2>
                    <div class="text-muted fs-12">{{ $total }} FAQ &middot; seluruh pengajuan dan publikasi Anda</div>
                </div>

                <!-- Search & Filter Controls -->
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <div class="position-relative flex-grow-1" style="min-width: 240px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                        <input type="text" id="faqSearchInput" class="form-control rounded-3 ps-5 fs-13" placeholder="Cari judul FAQ atau kata kunci..." style="border-color: #E2E8F0; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    </div>
                    <div>
                        <select id="tagFilter" class="form-select rounded-3 fs-13 text-secondary" style="border-color: #E2E8F0; width: auto; padding-top: 0.5rem; padding-bottom: 0.5rem; min-width: 160px;">
                            <option value="all" selected>Semua kata kunci</option>
                            @if(isset($tags))
                                @foreach($tags as $t)
                                    <option value="{{ $t->nama }}">{{ $t->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary bg-white rounded-3 fs-13 d-flex align-items-center gap-2 text-dark" style="border-color: #E2E8F0; padding: 0.5rem 0.85rem;">
                            <i class="bi bi-filter"></i>
                            <span>Terakhir diperbarui</span>
                        </button>
                    </div>
                </div>

                <!-- Status Filter Tabs -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="filter-pill active" data-status="all">Semua {{ $total }}</button>
                    <button class="filter-pill" data-status="Draft">Draft {{ $draftCount }}</button>
                    <button class="filter-pill" data-status="Menunggu Validasi">Menunggu Validasi {{ $submittedCount }}</button>
                    <button class="filter-pill" data-status="Perlu Revisi">Perlu Revisi {{ $rejectedCount }}</button>
                    <button class="filter-pill" data-status="Terbit">Terbit {{ $approvedCount }}</button>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40%;">JUDUL FAQ</th>
                                <th style="width: 20%;">KATA KUNCI</th>
                                <th style="width: 16%;">DIPERBARUI</th>
                                <th style="width: 14%;">STATUS</th>
                                <th style="width: 10%; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="faqTableBody">
                            @if($faqs->isEmpty())
                                <tr id="emptyFaqRow">
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <div class="mb-3">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background-color: #F8FAFC; color: #94A3B8; border: 1px dashed #CBD5E1;">
                                                    <i class="bi bi-journal-x fs-2"></i>
                                                </span>
                                            </div>
                                            <h5 class="fw-bold text-dark fs-16 mb-1">Belum ada FAQ</h5>
                                            <p class="text-muted fs-13 mb-3 mx-auto" style="max-width: 440px;">
                                                Belum ada data FAQ yang tercatat di sistem saat ini. Klik tombol di bawah untuk membuat FAQ pertama Anda.
                                            </p>
                                            <a href="{{ route('dataentry.buatfaq') }}" class="btn-maroon">
                                                <i class="bi bi-plus-lg"></i>
                                                <span>Buat FAQ Baru</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($faqs as $faq)
                                    @php
                                        $latest = $faq->latest_version;
                                        $st = $latest ? strtolower($latest->status) : 'draft';

                                        $statusMap = [
                                            'draft'     => ['label' => 'Draft', 'badge' => 'badge-status-draft', 'rowClass' => ''],
                                            'submitted' => ['label' => 'Menunggu Validasi', 'badge' => 'badge-status-validasi', 'rowClass' => ''],
                                            'rejected'  => ['label' => 'Perlu Revisi', 'badge' => 'badge-status-revisi', 'rowClass' => 'row-revision'],
                                            'approved'  => ['label' => 'Terbit', 'badge' => 'badge-status-terbit', 'rowClass' => ''],
                                        ];
                                        $meta = $statusMap[$st] ?? ['label' => ucfirst($st), 'badge' => 'badge-status-draft', 'rowClass' => ''];
                                        $versionCount = $faq->versions->count() ?: 1;
                                        $latestVerId = $latest ? $latest->id : ($faq->id . '-V1');
                                        $nextVerId = $faq->generateNextVersionId();
                                        $updatedTime = $latest && $latest->updated_at ? $latest->updated_at : $faq->updated_at;
                                        $tagString = $faq->tags->pluck('nama')->implode(',');
                                    @endphp
                                    <tr class="{{ $meta['rowClass'] }}" data-status="{{ $meta['label'] }}" data-tags="{{ strtolower($tagString) }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <div class="fw-semibold text-dark fs-13 mb-0.5">{{ $faq->pertanyaan }}</div>
                                                    <div class="fs-11 text-muted">{{ $faq->id }} &middot; {{ $latestVerId }} (Versi {{ $versionCount }})</div>
                                                </div>
                                                @if($st === 'rejected' && $latest && $latest->catatan)
                                                    <span class="revisi-note-icon ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="right"
                                                        data-bs-html="true"
                                                        title="<strong>Catatan Validator:</strong><br>{{ e($latest->catatan) }}">
                                                        <i class="bi bi-chat-left-text-fill" style="color: #DC2626; font-size: 0.75rem;"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($faq->tags->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($faq->tags as $t)
                                                        <span class="badge bg-light text-secondary border fs-11 fw-normal px-2 py-0.5 rounded-pill">{{ $t->nama }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted fs-12">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-secondary fs-12 lh-sm">{{ $updatedTime ? $updatedTime->translatedFormat('d M Y') : '-' }}</div>
                                            <div class="fs-11 text-muted">{{ $updatedTime ? $updatedTime->format('H.i') . ' WIB' : '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge-status {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex gap-1 align-items-center">
                                                <!-- Lihat Detail -->
                                                <button type="button" class="action-icon-btn" title="Lihat Detail FAQ" onclick="viewFaqDetail('{{ $faq->id }}')">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <!-- Edit FAQ / Tambah Versi Jawaban Baru -->
                                                @if($st === 'submitted')
                                                    <button class="action-icon-btn action-icon-disabled" title="Tidak dapat diedit saat status {{ $meta['label'] }}" disabled>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('dataentry.faq.edit', $faq->id) }}" class="action-icon-btn text-decoration-none" title="Edit / Buat Versi Baru ({{ $nextVerId }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif

                                                <!-- Dropdown Lainnya -->
                                                <div class="dropdown d-inline-block">
                                                    <button class="action-icon-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1 fs-12">
                                                        <li>
                                                            <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="viewFaqDetail('{{ $faq->id }}')">
                                                                <i class="bi bi-clock-history me-1.5 text-secondary"></i> Riwayat Versi ({{ $versionCount }})
                                                            </a>
                                                        </li>
                                                        @if($st !== 'submitted')
                                                            <li>
                                                                <a class="dropdown-item py-1.5" href="{{ route('dataentry.buatfaq', ['faq_id' => $faq->id]) }}">
                                                                    <i class="bi bi-plus-circle me-1.5 text-primary"></i> Tambah Versi Jawaban Baru ({{ $nextVerId }})
                                                                </a>
                                                            </li>
                                                        @endif
                                                        @if(!in_array($st, ['submitted', 'approved']))
                                                            <li><hr class="dropdown-divider my-1"></li>
                                                            <li>
                                                                <form action="{{ route('dataentry.faq.destroy', $faq->id) }}" method="POST" id="delete-faq-{{ $faq->id }}" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="dropdown-item py-1.5 text-danger" onclick="confirmDeleteFaq('{{ $faq->id }}')">
                                                                        <i class="bi bi-trash3 me-1.5"></i> Hapus FAQ
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination Footer -->
                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2 border-top border-light gap-2">
                    <div class="fs-12 text-muted" id="faqCountLabel">
                        @if($faqs->isEmpty())
                            Menampilkan 0 FAQ
                        @else
                            Menampilkan 1-{{ $faqs->count() }} dari {{ $total }} FAQ
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="#" class="page-pill"><i class="bi bi-chevron-left"></i></a>
                        <a href="#" class="page-pill active">1</a>
                        <a href="#" class="page-pill"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Unggah File FAQ -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="dashExcelForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16" id="uploadModalLabel">Unggah File FAQ (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="submitDashExcel(this)">
                        </label>
                        <div id="dashFileNameText" class="fs-12 text-success fw-semibold mt-2" style="display:none;"></div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Format Kolom Excel / CSV:</span>
                            <a href="{{ route('dataentry.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong> (pisah koma) &middot; Kolom D: <strong>Sumber Pustaka</strong> &middot; Kolom E: <strong>Bagian Sumber</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Unggah & Tinjau</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 for modern confirmation pop-up -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    // Konfirmasi Logout dengan SweetAlert2
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C', // Warna maroon KMS
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

    // Upload Excel Auto-submit Handler
    function submitDashExcel(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const textEl = document.getElementById('dashFileNameText');
            if (textEl) {
                textEl.textContent = 'File: ' + fileName;
                textEl.style.display = 'block';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mengunggah File...',
                    text: `Memproses ${fileName} untuk pratinjau data FAQ.`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            document.getElementById('dashExcelForm').submit();
        }
    }

    // Inisialisasi Tooltip Bootstrap
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });

    // Dynamic Filter & Search Handler (Klasifikasi Kata Kunci)
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('faqSearchInput');
        const tagFilter = document.getElementById('tagFilter');
        const filterPills = document.querySelectorAll('.filter-pill');
        const tableRows = document.querySelectorAll('#faqTableBody tr:not(#emptyFaqRow)');
        const totalFaqCount = {{ $total }};
        let currentStatus = 'all';

        function filterTable() {
            if (totalFaqCount === 0) return;

            const query = (searchInput.value || '').toLowerCase().trim();
            const selectedTag = tagFilter ? tagFilter.value.toLowerCase() : 'all';
            let visibleCount = 0;

            tableRows.forEach(row => {
                const status = row.getAttribute('data-status');
                const rowTags = (row.getAttribute('data-tags') || '').split(',').map(t => t.trim().toLowerCase());
                const textContent = row.textContent.toLowerCase();

                const matchesStatus = (currentStatus === 'all') || (status === currentStatus);
                const matchesTag = (selectedTag === 'all') || rowTags.includes(selectedTag);
                const matchesSearch = !query || textContent.includes(query);

                if (matchesStatus && matchesTag && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countLabel = document.getElementById('faqCountLabel');
            if (countLabel) {
                countLabel.textContent = `Menampilkan ${visibleCount} dari ${totalFaqCount} FAQ`;
            }
        }

        // Filter Pills Click
        filterPills.forEach(pill => {
            pill.addEventListener('click', function () {
                filterPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                currentStatus = this.getAttribute('data-status');
                filterTable();
            });
        });

        // Search Input
        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
        }

        // Kata Kunci Filter
        if (tagFilter) {
            tagFilter.addEventListener('change', filterTable);
        }
    });

    // Helper: Konfirmasi Hapus FAQ
    function confirmDeleteFaq(faqId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus FAQ ' + faqId + '?',
                text: 'FAQ dan riwayat versinya akan dihapus permanen dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-faq-' + faqId).submit();
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus FAQ ' + faqId + '?')) {
                document.getElementById('delete-faq-' + faqId).submit();
            }
        }
    }

    // Helper: Lihat Detail FAQ via AJAX
    function viewFaqDetail(faqId) {
        fetch(`{{ url('/data-entry/faq') }}/${faqId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const latest = d.latest_version || {};
                let tagsHtml = '';
                if (d.tags && d.tags.length > 0) {
                    tagsHtml = d.tags.map(t => `<span class="badge bg-light text-secondary border fs-12 px-2 py-1 rounded-pill me-1 mb-1">${t}</span>`).join('');
                } else {
                    tagsHtml = '<span class="text-muted fs-12">Tidak ada kata kunci</span>';
                }

                let catatanHtml = '';
                if (latest.catatan) {
                    catatanHtml = `
                        <div class="alert alert-danger p-2.5 rounded-3 fs-13 mb-3">
                            <strong>Catatan Validator:</strong><br>${latest.catatan}
                        </div>
                    `;
                }

                let statusBadge = `<span class="badge bg-secondary">${latest.status || 'Draft'}</span>`;
                if (latest.status && latest.status.toLowerCase() === 'approved') statusBadge = '<span class="badge bg-success">Terbit</span>';
                else if (latest.status && latest.status.toLowerCase() === 'submitted') statusBadge = '<span class="badge bg-primary">Menunggu Validasi</span>';
                else if (latest.status && latest.status.toLowerCase() === 'rejected') statusBadge = '<span class="badge bg-danger">Perlu Revisi</span>';

                let versionsHistoryHtml = '';
                if (d.versions && d.versions.length > 0) {
                    versionsHistoryHtml = `
                        <div class="mt-3">
                            <label class="fw-bold text-dark d-block mb-1">Riwayat Versi Jawaban (\`tfaqsversion\`):</label>
                            <div class="border rounded-3 overflow-hidden">
                                <table class="table table-sm mb-0 fs-12 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID Versi</th>
                                            <th>Status</th>
                                            <th>Sumber Pustaka</th>
                                            <th>Diperbarui</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${d.versions.map(v => `
                                            <tr>
                                                <td class="fw-bold text-dark">${v.id}</td>
                                                <td><span class="badge bg-light text-dark border">${v.status}</span></td>
                                                <td>${v.daftar_pustaka ? v.daftar_pustaka.link : '-'}</td>
                                                <td class="text-muted">${v.updated_at || '-'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: `<div class="fs-18 fw-bold text-start text-dark">${d.id}: ${d.pertanyaan}</div>`,
                    html: `
                        <div class="text-start fs-13 text-secondary">
                            <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                <span>Versi Aktif: <strong class="text-dark">${latest.id || (d.id + '-V1')}</strong></span>
                                <span>&middot;</span>
                                <span>Status: ${statusBadge}</span>
                                <span>&middot;</span>
                                <span>Dibuat: ${d.created_at || '-'}</span>
                            </div>
                            ${catatanHtml}
                            <div class="mb-3">
                                <label class="fw-bold text-dark d-block mb-1">Kata Kunci:</label>
                                <div>${tagsHtml}</div>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold text-dark d-block mb-1">Jawaban (${latest.id || (d.id + '-V1')}):</label>
                                <div class="p-3 bg-light rounded-3 text-dark border fs-13" style="max-height: 180px; overflow-y: auto;">
                                    ${latest.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}
                                </div>
                            </div>
                            ${versionsHistoryHtml}
                        </div>
                    `,
                    width: '680px',
                    confirmButtonColor: '#85172C',
                    confirmButtonText: 'Tutup'
                });
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        }
    });
    @endif

    @if(session('error'))
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: "{{ session('error') }}",
                confirmButtonColor: '#85172C'
            });
        }
    });
    @endif
</script>
</body>
</html>
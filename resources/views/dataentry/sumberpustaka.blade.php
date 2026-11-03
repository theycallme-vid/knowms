<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sumber Pustaka - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Data Entry External CSS -->
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
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <nav class="nav flex-column">
                <a href="{{ route('dataentry.dashboard') }}" class="nav-link-custom">
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
                <a href="{{ route('dataentry.sumberpustaka') }}" class="nav-link-custom active">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <div>
            <hr class="sidebar-divider my-3">
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
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
                    <span class="text-dark fw-semibold">Data Entry</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()?->nama ?? 'NP', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()?->nama ?? 'Nadia Putri' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()?->getRoleName() ?: 'Data Entry' }}</div>
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

                <button class="btn btn-link text-secondary p-1 text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RUJUKAN RESMI &middot; TABEL TDAFTARPUSTAKA</div>
                    <h1 class="display-serif fs-2 mb-1">Sumber Pustaka</h1>
                    <p class="text-muted fs-14 mb-0">Kelola dokumen peraturan, pedoman akademik, dan rujukan resmi yang menjadi dasar jawaban FAQ.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#uploadPustakaModal">
                        <i class="bi bi-upload"></i>
                        <span>Unggah File (Excel/PDF)</span>
                    </button>
                    <button type="button" class="btn-maroon" data-bs-toggle="modal" data-bs-target="#addPustakaModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Sumber Pustaka</span>
                    </button>
                </div>
            </div>

            <!-- Pratinjau Import Sumber Pustaka (Muncul jika pengguna baru saja mengunggah file daftar pustaka) -->
            @if(!empty($previewSources))
                <div class="card-custom mb-4 border-primary-subtle" style="background-color: #F8FAFF;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="fw-bold fs-15 text-dark mb-0">Pratinjau Unggahan Sumber Pustaka</h2>
                                <span class="badge-pratinjau">{{ count($previewSources) }} sumber terbaca</span>
                            </div>
                            <div class="fs-12 text-muted mt-1">
                                Sumber file: <strong>{{ $previewSourcesFile }}</strong> &middot; Tinjau daftar rujukan sebelum disimpan ke tabel <code>tdaftarpustaka</code>.
                            </div>
                        </div>
                        <form action="{{ route('dataentry.sumberpustaka.cancelImport') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 fs-12">Batalkan Pratinjau</button>
                        </form>
                    </div>

                    <form action="{{ route('dataentry.sumberpustaka.saveImport') }}" method="POST">
                        @csrf
                        <div class="table-responsive bg-white border rounded-3 mb-3">
                            <table class="table faq-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">PILIH</th>
                                        <th style="width: 120px;">TIPE</th>
                                        <th>JUDUL / TAUTAN SUMBER PUSTAKA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewSources as $idx => $src)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="selected_indexes[]" value="{{ $idx }}" class="form-check-input" checked>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border fs-11 text-uppercase">{{ $src['type'] ?? 'link' }}</span>
                                            </td>
                                            <td class="fw-semibold text-dark fs-13">{{ $src['link'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-maroon">
                                <i class="bi bi-check2-circle"></i>
                                <span>Simpan Sumber Pustaka ke Database</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tabel Sumber Pustaka -->
            <div class="card-custom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h2 class="fw-bold fs-16 text-dark mb-0">Daftar Sumber Pustaka Resmi</h2>
                        <div class="fs-12 text-muted">{{ $pustakas->count() }} sumber rujukan tersimpan di database</div>
                    </div>
                    <div class="position-relative" style="min-width: 260px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                        <input type="text" id="searchPustakaInput" class="form-control form-control-sm rounded-3 ps-5 fs-13" placeholder="Cari sumber pustaka...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>JUDUL / TAUTAN SUMBER PUSTAKA</th>
                                <th style="width: 140px;">TIPE</th>
                                <th style="width: 180px;">DIGUNAKAN PADA</th>
                                <th style="width: 180px; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="pustakaTableBody">
                            @if($pustakas->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted fs-13">
                                        Belum ada sumber pustaka di database. Klik <strong>Tambah Sumber Pustaka</strong> atau <strong>Unggah File</strong>.
                                    </td>
                                </tr>
                            @else
                                @foreach($pustakas as $p)
                                    <tr class="pustaka-row">
                                        <td class="text-muted fw-semibold">#{{ $p->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="stat-icon" style="width: 34px; height: 34px; background-color: {{ $p->type === 'upload' ? '#EFF6FF' : '#F0FDF4' }}; color: {{ $p->type === 'upload' ? '#2563EB' : '#16A34A' }}; font-size: 0.95rem;">
                                                    <i class="bi {{ $p->type === 'upload' ? 'bi-file-earmark-pdf' : 'bi-link-45deg' }}"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark fs-13">{{ $p->link ?: 'Dokumen Sumber #' . $p->id }}</div>
                                                    <div class="fs-11 text-muted">
                                                        {{ $p->type === 'upload' ? 'Dokumen tersimpan di database (BLOB)' : 'Rujukan Link / Pedoman Resmi' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($p->type === 'upload')
                                                <span class="badge-status badge-status-validasi">UPLOAD FILE</span>
                                            @else
                                                <span class="badge-status badge-status-terbit">LINK / JUDUL</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fs-11 fw-semibold px-2.5 py-1 rounded-pill">
                                                {{ $p->faq_versions_count }} Versi FAQ
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                @if($p->type === 'upload' && !empty($p->upload))
                                                    <a href="{{ route('dataentry.pustaka.download', $p->id) }}" class="btn-table-edit text-decoration-none">
                                                        <i class="bi bi-download"></i> Unduh
                                                    </a>
                                                @endif

                                                <form action="{{ route('dataentry.sumberpustaka.destroy', $p->id) }}" method="POST" id="del-pustaka-{{ $p->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-table-delete" onclick="confirmDeletePustaka('{{ $p->id }}', '{{ addslashes($p->link) }}')">
                                                        <i class="bi bi-trash3"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
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

<!-- Modal Tambah Sumber Pustaka Satuan -->
<div class="modal fade" id="addPustakaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.sumberpustaka.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-16">Tambah Sumber Pustaka</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-custom form-label-required">Tipe Sumber Pustaka</label>
                        <select name="type" id="pustakaTypeSelect" class="form-select fs-13 rounded-3" onchange="togglePustakaTypeInput(this.value)">
                            <option value="link">Rujukan Judul / Tautan Resmi (Link)</option>
                            <option value="upload">Unggah Dokumen Resmi (PDF / DOCX ke Database)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom form-label-required">Judul / Nama Rujukan (Maks. 45 Karakter)</label>
                        <input type="text" name="link" maxlength="45" class="form-control fs-13 rounded-3" placeholder="contoh: Pedoman Akademik 2026/2027" required>
                    </div>

                    <div class="mb-2 d-none" id="fileUploadContainer">
                        <label class="form-label-custom form-label-required">Pilih File Dokumen (PDF / DOCX)</label>
                        <input type="file" name="file_upload" accept=".pdf,.doc,.docx" class="form-control fs-13 rounded-3">
                        <div class="fs-11 text-muted mt-1">Dokumen akan disimpan ke kolom <code>upload</code> (MEDIUMBLOB) pada tabel <code>tdaftarpustaka</code>.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Simpan Sumber Pustaka</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Unggah File Daftar Sumber Pustaka (Excel / CSV / PDF) -->
<div class="modal fade" id="uploadPustakaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.sumberpustaka.upload') }}" method="POST" enctype="multipart/form-data" id="uploadPustakaForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Import Daftar Sumber Pustaka (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-journal-bookmark fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Pilih file daftar sumber pustaka</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_pustaka" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="document.getElementById('uploadPustakaForm').submit()">
                        </label>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Format Kolom:</span>
                            <a href="{{ route('dataentry.template.pustaka') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Tipe</strong> (<code>link</code>) &middot; Kolom B: <strong>Judul atau Link Sumber Pustaka</strong></div>
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

<!-- Bootstrap 5.3 JS & SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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

    function togglePustakaTypeInput(val) {
        const box = document.getElementById('fileUploadContainer');
        if (val === 'upload') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    function confirmDeletePustaka(id, nama) {
        Swal.fire({
            title: `Hapus Sumber Pustaka "${nama}"?`,
            text: 'Sumber pustaka ini akan dihapus dari database.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) document.getElementById('del-pustaka-' + id).submit();
        });
    }

    document.getElementById('searchPustakaInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.pustaka-row').forEach(row => {
            row.style.display = (!q || row.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });

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


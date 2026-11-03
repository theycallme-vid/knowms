<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kata Kunci - Ruang Data Entry | KMS</title>
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
                <a href="{{ route('dataentry.katakunci') }}" class="nav-link-custom active">
                    <i class="bi bi-tags"></i>
                    <span>Daftar Kata Kunci</span>
                </a>
                <a href="{{ route('dataentry.sumberpustaka') }}" class="nav-link-custom">
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
                    <div class="date-badge">KLASIFIKASI PENGETAHUAN &middot; TABEL TTAG</div>
                    <h1 class="display-serif fs-2 mb-1">Daftar Kata Kunci</h1>
                    <p class="text-muted fs-14 mb-0">Kelola kata kunci klasifikasi FAQ secara satuan atau import langsung dari file Excel/CSV/PDF.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#uploadKeywordModal">
                        <i class="bi bi-upload"></i>
                        <span>Unggah File</span>
                    </button>
                    <button type="button" class="btn-maroon" data-bs-toggle="modal" data-bs-target="#addKeywordModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Kata Kunci</span>
                    </button>
                </div>
            </div>

            <!-- Pratinjau Import Kata Kunci (Muncul jika pengguna baru saja mengunggah file) -->
            @if(!empty($previewKeywords))
                <div class="card-custom mb-4 border-primary-subtle" style="background-color: #F8FAFF;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="fw-bold fs-15 text-dark mb-0">Pratinjau Unggahan Kata Kunci</h2>
                                <span class="badge-pratinjau">{{ count($previewKeywords) }} kata kunci terbaca</span>
                            </div>
                            <div class="fs-12 text-muted mt-1">
                                Sumber file: <strong>{{ $previewKeywordsFile }}</strong> &middot; Centang kata kunci yang ingin disimpan ke tabel <code>ttag</code>.
                            </div>
                        </div>
                        <form action="{{ route('dataentry.katakunci.cancelImport') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 fs-12">Batalkan Pratinjau</button>
                        </form>
                    </div>

                    <form action="{{ route('dataentry.katakunci.saveImport') }}" method="POST">
                        @csrf
                        <div class="d-flex flex-wrap gap-2 p-3 bg-white border rounded-3 mb-3">
                            @foreach($previewKeywords as $idx => $kw)
                                <label class="d-inline-flex align-items-center gap-2 px-3 py-1.5 border rounded-pill bg-light fs-12 fw-semibold text-dark" style="cursor: pointer;">
                                    <input type="checkbox" name="keywords[]" value="{{ $kw }}" class="form-check-input mt-0" checked>
                                    <span>{{ $kw }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn-maroon">
                                <i class="bi bi-check2-circle"></i>
                                <span>Simpan Kata Kunci ke Database</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tabel Daftar Kata Kunci -->
            <div class="card-custom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h2 class="fw-bold fs-16 text-dark mb-0">Seluruh Kata Kunci</h2>
                        <div class="fs-12 text-muted">{{ $tags->count() }} kata kunci terdaftar di sistem</div>
                    </div>
                    <div class="position-relative" style="min-width: 260px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                        <input type="text" id="searchTagInput" class="form-control form-control-sm rounded-3 ps-5 fs-13" placeholder="Cari kata kunci...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>NAMA KATA KUNCI</th>
                                <th style="width: 200px;">DIGUNAKAN PADA FAQ</th>
                                <th style="width: 180px;">DIBUAT PADA</th>
                                <th style="width: 150px; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="tagTableBody">
                            @if($tags->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted fs-13">
                                        Belum ada kata kunci di database. Klik <strong>Tambah Kata Kunci</strong> atau <strong>Unggah File</strong>.
                                    </td>
                                </tr>
                            @else
                                @foreach($tags as $tag)
                                    <tr class="tag-row">
                                        <td class="text-muted fw-semibold">#{{ $tag->id }}</td>
                                        <td>
                                            <span class="keyword-pill-blue fs-12 px-3 py-1">{{ $tag->nama }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fs-11 fw-semibold px-2.5 py-1 rounded-pill">
                                                {{ $tag->faqs_count }} FAQ
                                            </span>
                                        </td>
                                        <td class="text-muted fs-12">
                                            {{ $tag->created_at ? $tag->created_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <button type="button" class="btn-table-edit" onclick="openEditTagModal('{{ $tag->id }}', '{{ addslashes($tag->nama) }}')">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form action="{{ route('dataentry.katakunci.destroy', $tag->id) }}" method="POST" id="del-tag-{{ $tag->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-table-delete" onclick="confirmDeleteTag('{{ $tag->id }}', '{{ addslashes($tag->nama) }}')">
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

<!-- Modal Tambah Kata Kunci Satuan / Banyak -->
<div class="modal fade" id="addKeywordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.katakunci.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-16">Tambah Kata Kunci</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label-custom form-label-required">Nama Kata Kunci</label>
                    <input type="text" name="nama" class="form-control fs-13 rounded-3" placeholder="contoh: cuti akademik, beasiswa, KRS" required>
                    <div class="fs-11 text-muted mt-1.5">
                        Anda dapat memasukkan beberapa kata kunci sekaligus dengan memisahkannya menggunakan tanda koma (<code>,</code>).
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Simpan Kata Kunci</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kata Kunci -->
<div class="modal fade" id="editKeywordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="editKeywordForm" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-16">Edit Kata Kunci</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label-custom form-label-required">Nama Kata Kunci</label>
                    <input type="text" name="nama" id="editKeywordNama" class="form-control fs-13 rounded-3" maxlength="100" required>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Unggah File Kata Kunci (Excel / CSV / PDF) -->
<div class="modal fade" id="uploadKeywordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.katakunci.upload') }}" method="POST" enctype="multipart/form-data" id="uploadKwForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Unggah File Kata Kunci (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-tags fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Pilih file daftar kata kunci</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_katakunci" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="document.getElementById('uploadKwForm').submit()">
                        </label>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Format Kolom:</span>
                            <a href="{{ route('dataentry.template.katakunci') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>No</strong> (opsional) &middot; Kolom B: <strong>Kata Kunci</strong></div>
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

    function openEditTagModal(id, nama) {
        const form = document.getElementById('editKeywordForm');
        form.action = `{{ url('/data-entry/kata-kunci') }}/${id}/update`;
        document.getElementById('editKeywordNama').value = nama;
        new bootstrap.Modal(document.getElementById('editKeywordModal')).show();
    }

    function confirmDeleteTag(id, nama) {
        Swal.fire({
            title: `Hapus kata kunci "${nama}"?`,
            text: 'Kata kunci ini akan dilepaskan dari FAQ yang menggunakannya.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) document.getElementById('del-tag-' + id).submit();
        });
    }

    document.getElementById('searchTagInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.tag-row').forEach(row => {
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


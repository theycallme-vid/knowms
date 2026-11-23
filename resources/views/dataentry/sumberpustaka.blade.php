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
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('dataentry.daftarFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.pengajuan') }}" class="nav-link-custom">
                    <i class="bi bi-send-check"></i>
                    <span>Pengajuan FAQ</span>
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

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider">
            <a href="{{ route('dataentry.pengaturanAkun') }}" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Sidebar Backdrop -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="flex-grow-1 min-vw-0">
        <!-- Top Navbar (Tanpa Pencarian) -->
        @php
            $tbUser = auth()->user();
            if ($tbUser && !$tbUser->relationLoaded('role')) {
                $tbUser->load('role');
            }
            $tbRole = $tbUser?->getRoleName() ?: 'Data Entry';
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
                    <span class="crumb-current">Sumber Pustaka</span>
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('dataentry.pengaturanAkun') }}">
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

        <!-- Content Area -->
        <div class="content-area">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <h1 class="page-title mb-1">Sumber Pustaka</h1>
                    <p class="text-muted fs-14 mb-0">Kelola dokumen peraturan, pedoman akademik, dan rujukan resmi yang menjadi dasar jawaban FAQ.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-maroon" data-bs-toggle="modal" data-bs-target="#addPustakaModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Sumber Pustaka</span>
                    </button>
                </div>
            </div>

            <!-- Pratinjau Import Sumber Pustaka (Muncul jika pengguna baru saja mengunggah file daftar pustaka) -->
            @if(!empty($previewSources))
                @php
                    $validImportCount = collect($previewSources)->where('is_valid', true)->count();
                    $invalidImportCount = count($previewSources) - $validImportCount;
                @endphp
                <div class="card-custom mb-4 border-primary-subtle" style="background-color: #F8FAFF;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h2 class="fw-bold fs-15 text-dark mb-0">Pratinjau &amp; Pemantauan Import Tautan Sumber Pustaka</h2>
                                <span class="badge-pratinjau">{{ count($previewSources) }} baris terbaca</span>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle fs-11">{{ $validImportCount }} Sesuai</span>
                                @if($invalidImportCount > 0)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle fs-11">{{ $invalidImportCount }} Belum Sesuai</span>
                                @endif
                            </div>
                            <div class="fs-12 text-muted mt-1">
                                Sumber file: <strong>{{ $previewSourcesFile }}</strong> &middot; Membaca 2 kolom: <strong>1. Judul Tautan</strong> dan <strong>2. Tautan / Link</strong>.
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
                                        <th>1. JUDUL TAUTAN</th>
                                        <th>2. LINK / TAUTAN</th>
                                        <th style="width: 260px;">STATUS KESESUAIAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewSources as $idx => $src)
                                        @php
                                            $isRowValid = $src['is_valid'] ?? true;
                                        @endphp
                                        <tr class="{{ !$isRowValid ? 'table-warning' : '' }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="selected_indexes[]" value="{{ $idx }}" class="form-check-input" {{ $isRowValid ? 'checked' : '' }}>
                                            </td>
                                            <td class="fw-bold text-dark fs-13">
                                                {{ $src['judul'] ?? $src['link'] ?: '(Kosong)' }}
                                            </td>
                                            <td class="text-secondary fs-13">
                                                {{ $src['link'] ?: '(Kosong)' }}
                                            </td>
                                            <td>
                                                @if($isRowValid)
                                                    <span class="badge bg-success text-white fs-11 me-1"><i class="bi bi-check-circle me-1"></i>Sesuai</span>
                                                    <span class="fs-12 text-muted">{{ $src['notes'] ?? 'Siap disimpan' }}</span>
                                                @else
                                                    <span class="badge bg-danger text-white fs-11 me-1"><i class="bi bi-exclamation-triangle me-1"></i>Belum Sesuai</span>
                                                    <div class="fs-11 text-danger mt-0.5">{{ $src['notes'] ?? 'Periksa format kolom' }}</div>
                                                @endif
                                            </td>
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
                        <h1 class="fw-bold fs-16 text-dark mb-0">Daftar Sumber Pustaka Resmi</h1>
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
                                <th>Dokumen/Tautan Sumber Pustaka</th>
                                <th style="width: 145px;">JENIS</th>
                                <th style="width: 165px;">DIGUNAKAN PADA</th>
                                <th style="width: 300px; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="pustakaTableBody">
                            @if($pustakas->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted fs-13">
                                        Belum ada sumber pustaka di database. Klik <strong>Tambah Sumber Pustaka</strong>.
                                    </td>
                                </tr>
                            @else
                                @foreach($pustakas as $p)
                                    @php
                                        $isFile = !empty($p->upload);
                                        $judulDisplay = $p->judul_tampil;
                                    @endphp
                                    <tr class="pustaka-row">
                                        <td class="text-dark fw-semibold">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-bold text-dark fs-14">{{ $judulDisplay }}</div>
                                        </td>
                                        <td>
                                            @if($isFile)
                                                <span class="badge-status" style="background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #DBEAFE;">
                                                    <i class="bi bi-file-earmark-text me-1"></i>Dokumen
                                                </span>
                                            @else
                                                <span class="badge-status" style="background-color: #F0FDF4; color: #15803D; border: 1px solid #DCFCE7;">
                                                    <i class="bi bi-link-45deg me-1"></i>Tautan
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fs-12 fw-semibold px-2.5 py-1 rounded-pill">
                                                {{ $p->faq_versions_count }} Versi FAQ
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex align-items-center gap-2">
                                                @if($isFile)
                                                    <button type="button"
                                                            class="btn-action btn-action-view"
                                                            onclick="openViewPustakaModal('{{ route('dataentry.pustaka.view', $p->id) }}', '{{ route('dataentry.pustaka.download', $p->id) }}', '{{ addslashes($judulDisplay) }}', '{{ addslashes($p->link) }}')">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </button>
                                                    <a href="{{ route('dataentry.pustaka.download', $p->id) }}" class="btn-action btn-action-view text-decoration-none">
                                                        <i class="bi bi-download"></i> Unduh
                                                    </a>
                                                @elseif(str_starts_with((string) $p->link, 'http://') || str_starts_with((string) $p->link, 'https://'))
                                                    <a href="{{ $p->link }}" target="_blank" class="btn-action btn-action-view text-decoration-none">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </a>
                                                @endif

                                                <button type="button"
                                                        class="btn-action btn-action-edit"
                                                        onclick="openEditPustakaModal('{{ $p->id }}', '{{ addslashes($judulDisplay) }}', '{{ addslashes($p->link) }}', {{ $isFile ? 'true' : 'false' }})">
                                                    <i class="bi bi-pencil"></i> Ubah
                                                </button>

                                                <form action="{{ route('dataentry.sumberpustaka.destroy', $p->id) }}" method="POST" id="del-pustaka-{{ $p->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-table-delete" onclick="confirmDeletePustaka('{{ $p->id }}', '{{ addslashes($judulDisplay) }}')">
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
                    <!-- 1. Judul Tautan / Judul Dokumen -->
                    <div class="mb-3">
                        <label class="form-label-custom form-label-required" id="addJudulLabel">1. Judul Tautan</label>
                        <input type="text" name="judul" id="addPustakaJudulInput" maxlength="100" class="form-control fs-13 rounded-3" placeholder="contoh: Pedoman Akademik UBAYA 2026/2027" required>
                    </div>

                    <!-- 2. Jenis Input -->
                    <div class="mb-3">
                        <label class="form-label-custom form-label-required">2. Jenis Input</label>
                        <select name="source_kind" id="pustakaKindSelect" class="form-select fs-13 rounded-3" onchange="togglePustakaTypeInput(this.value)">
                            <option value="link">Tautan (Link)</option>
                            <option value="upload">Dokumen (Unggah File)</option>
                        </select>
                    </div>

                    <!-- 3. Tautan/Link (Jika Jenis Input = Tautan) -->
                    <div class="mb-3" id="linkInputContainer">
                        <label class="form-label-custom form-label-required" id="linkInputLabel">3. Tautan/Link</label>
                        <input type="text" name="link" id="addPustakaLinkInput" maxlength="60" class="form-control fs-13 rounded-3" placeholder="contoh: https://akademik.kampus.ac.id/pedoman-2026" required>
                        <div class="fs-11 text-muted mt-1">Maksimal 60 karakter untuk tautan sumber pustaka.</div>
                    </div>

                    <!-- 3. Unggah File Dokumen (Jika Jenis Input = Dokumen) -->
                    <div class="mb-2 d-none" id="fileUploadContainer">
                        <label class="form-label-custom form-label-required">3. Unggah Berkas Dokumen</label>
                        <input type="file" name="file_upload" id="addPustakaFileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-control fs-13 rounded-3">
                        <div class="fs-11 text-muted mt-1">Untuk jenis input Dokumen, wajib mengunggah berkas rujukan (PDF, DOC/DOCX, atau gambar; tersimpan aman di database).</div>
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

<!-- Modal Edit Sumber Pustaka -->
<div class="modal fade" id="editPustakaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="editPustakaForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-16" id="editPustakaModalTitle">Ubah Sumber Pustaka</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Judul Tautan / Dokumen -->
                    <div class="mb-3">
                        <label class="form-label-custom form-label-required" id="editJudulLabel">Judul Sumber Pustaka</label>
                        <input type="text" name="judul" id="editPustakaJudul" maxlength="100" class="form-control fs-13 rounded-3" placeholder="Masukkan judul sumber pustaka..." required>
                    </div>

                    <!-- Muncul jika Sumber Pustaka berupa Link -->
                    <div class="mb-3" id="editLinkBox">
                        <label class="form-label-custom form-label-required" id="editLinkLabel">Tautan Sumber Pustaka (Maks. 60 Karakter)</label>
                        <input type="text" name="link" id="editPustakaLink" maxlength="60" class="form-control fs-13 rounded-3" placeholder="Masukkan tautan sumber pustaka baru (maks. 60 karakter)...">
                    </div>

                    <!-- Muncul jika Sumber Pustaka berupa Berkas Dokumen -->
                    <div class="mb-2 d-none" id="editFileBox">
                        <div class="p-2.5 bg-light border rounded-3 mb-3 fs-12">
                            <span class="text-muted">Berkas saat ini:</span>
                            <strong class="text-dark ms-1" id="currentFileNameText">-</strong>
                        </div>
                        <label class="form-label-custom">Unggah Berkas Dokumen Baru (PDF / DOCX / Gambar)</label>
                        <input type="file" name="file_upload" id="editPustakaFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-control fs-13 rounded-3">
                        <div class="fs-11 text-muted mt-1">Pilih berkas baru untuk menggantikan berkas sebelumnya (kosongkan jika hanya mengubah judul).</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Unggah File Daftar Sumber Pustaka (Khusus Link: Excel / CSV / PDF) -->
<div class="modal fade" id="uploadPustakaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.sumberpustaka.upload') }}" method="POST" enctype="multipart/form-data" id="uploadPustakaForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Import Daftar Tautan Sumber Pustaka (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="alert alert-warning rounded-3 p-2.5 fs-12 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <strong>Catatan:</strong> Unggah file Excel/CSV/PDF ini <strong>hanya dikhususkan untuk Tautan (Link)</strong> saja. Untuk sumber pustaka berupa <strong>Dokumen (File)</strong>, wajib mengunggah melalui tombol <strong>+ Tambah Sumber Pustaka</strong>.
                    </div>

                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-journal-bookmark fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Pilih file daftar tautan sumber pustaka</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_pustaka" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="document.getElementById('uploadPustakaForm').submit()">
                        </label>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-table me-1"></i>Format 2 Kolom (Excel / CSV / PDF):</span>
                            <a href="{{ route('dataentry.template.pustaka') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>1. <strong>Judul Tautan</strong> &middot; 2. <strong>Tautan/Link</strong> (maks. 60 karakter)</div>
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

<!-- Modal Pratinjau Berkas Sumber Pustaka -->
<div class="modal fade" id="viewPustakaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold fs-16" id="viewPustakaModalTitle">Pratinjau Dokumen</h5>
                    <div class="fs-12 text-muted" id="viewPustakaFileName">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background-color: #F8FAFC;">
                <iframe id="viewPustakaFrame" src="about:blank" style="width: 100%; height: 70vh; border: none; display: block;"></iframe>
            </div>
            <div class="modal-footer border-top justify-content-between">
                <span class="fs-12 text-muted">Jika dokumen berupa format Word (.doc/.docx), silakan klik <strong>Unduh Berkas</strong> untuk membukanya.</span>
                <div class="d-flex gap-2">
                    <a href="#" target="_blank" id="viewPustakaOpenTabBtn" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru
                    </a>
                    <a href="#" id="viewPustakaDownloadBtn" class="btn btn-sm btn-maroon rounded-3 px-3">
                        <i class="bi bi-download me-1"></i>Unduh Berkas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS & SweetAlert2 -->
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
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('d-none');
    }



    function togglePustakaTypeInput(val) {
        const judulLabel = document.getElementById('addJudulLabel');
        const linkContainer = document.getElementById('linkInputContainer');
        const linkInput = document.getElementById('addPustakaLinkInput');
        const fileBox = document.getElementById('fileUploadContainer');
        const fileInput = document.getElementById('addPustakaFileInput');

        if (val === 'upload') {
            if (judulLabel) judulLabel.textContent = '1. Judul Dokumen';
            linkContainer?.classList.add('d-none');
            if (linkInput) linkInput.required = false;
            fileBox?.classList.remove('d-none');
            if (fileInput) fileInput.required = true;
        } else {
            if (judulLabel) judulLabel.textContent = '1. Judul Tautan';
            fileBox?.classList.add('d-none');
            if (fileInput) fileInput.required = false;
            linkContainer?.classList.remove('d-none');
            if (linkInput) linkInput.required = true;
        }
    }

    function openViewPustakaModal(viewUrl, downloadUrl, judul, fileName) {
        const titleEl = document.getElementById('viewPustakaModalTitle');
        const nameEl = document.getElementById('viewPustakaFileName');
        const frameEl = document.getElementById('viewPustakaFrame');
        const openTabEl = document.getElementById('viewPustakaOpenTabBtn');
        const dlEl = document.getElementById('viewPustakaDownloadBtn');

        if (titleEl) titleEl.textContent = judul || 'Pratinjau Dokumen Sumber Pustaka';
        if (nameEl) nameEl.textContent = fileName || 'Dokumen';
        if (frameEl) frameEl.src = viewUrl;
        if (openTabEl) openTabEl.href = viewUrl;
        if (dlEl) dlEl.href = downloadUrl;

        const modalEl = document.getElementById('viewPustakaModal');
        modalEl?.addEventListener('hidden.bs.modal', function () {
            if (frameEl) frameEl.src = 'about:blank';
        }, { once: true });
        new bootstrap.Modal(modalEl).show();
    }

    function openEditPustakaModal(id, currentJudul, currentLink, isFile) {
        const form = document.getElementById('editPustakaForm');
        form.action = `{{ url('/data-entry/sumber-pustaka') }}/${id}/update`;

        const titleEl = document.getElementById('editPustakaModalTitle');
        const editJudulLabel = document.getElementById('editJudulLabel');
        const editJudulInput = document.getElementById('editPustakaJudul');
        const editLinkBox = document.getElementById('editLinkBox');
        const editLinkInput = document.getElementById('editPustakaLink');
        const editFileBox = document.getElementById('editFileBox');
        const editFileInput = document.getElementById('editPustakaFile');
        const currentFileNameText = document.getElementById('currentFileNameText');

        if (editJudulInput) editJudulInput.value = currentJudul || '';
        if (editFileInput) editFileInput.value = '';

        if (isFile) {
            if (titleEl) titleEl.textContent = 'Edit Dokumen Sumber Pustaka';
            if (editJudulLabel) editJudulLabel.textContent = 'Judul Dokumen';
            editLinkBox?.classList.add('d-none');
            editFileBox?.classList.remove('d-none');
            if (editLinkInput) {
                editLinkInput.value = currentLink || '';
                editLinkInput.required = false;
            }
            if (currentFileNameText) {
                currentFileNameText.textContent = currentLink || currentJudul || ('Dokumen ' + id);
            }
        } else {
            if (titleEl) titleEl.textContent = 'Edit Tautan Sumber Pustaka';
            if (editJudulLabel) editJudulLabel.textContent = 'Judul Tautan';
            editFileBox?.classList.add('d-none');
            editLinkBox?.classList.remove('d-none');
            if (editLinkInput) {
                editLinkInput.value = currentLink || '';
                editLinkInput.required = true;
            }
        }

        new bootstrap.Modal(document.getElementById('editPustakaModal')).show();
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


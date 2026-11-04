<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Unggahan FAQ - Ruang Data Entry | KMS</title>
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
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom active">
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
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route('dataentry.dashboard') }}" class="back-link mb-0">
                    <i class="bi bi-chevron-left"></i>
                    <span>Daftar FAQ</span>
                </a>
                <span class="text-muted fs-12">&middot;</span>
                <a href="{{ route('dataentry.buatfaq') }}" class="back-link mb-0">
                    <i class="bi bi-pencil-square"></i>
                    <span>Mode Input Satuan</span>
                </a>
            </div>

            <!-- Header: Judul Buat FAQ + Badge Pratinjau unggahan & Nama File + Unggah File -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="display-serif fs-2 mb-0">Buat FAQ</h1>
                        <span class="badge-pratinjau">Pratinjau unggahan</span>
                    </div>
                    <p class="text-muted fs-14 mb-0 mt-1">
                        Tinjau setiap FAQ dari file yang diunggah sebelum diajukan ke validator.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="file-pill-box">
                        <i class="bi bi-file-earmark-spreadsheet text-success"></i>
                        <span id="activeFileName">{{ $fileName ?? 'FAQ_Akademik_2026.xlsx' }}</span>
                    </div>
                    <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i>
                        <span>Unggah File</span>
                    </button>
                </div>
            </div>

            <!-- Filter Pills & Search Bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" onclick="setFilter('all', this)" class="preview-filter-pill active" data-filter="all">
                        <span>Semua</span>
                        <span class="preview-filter-count" id="badgeCountAll">{{ count($items) }}</span>
                    </button>
                    <button type="button" onclick="setFilter('incomplete', this)" class="preview-filter-pill" data-filter="incomplete">
                        <span>Belum lengkap</span>
                        <span class="preview-filter-count" id="badgeCountIncomplete">0</span>
                    </button>
                    <button type="button" onclick="setFilter('ready', this)" class="preview-filter-pill" data-filter="ready">
                        <span>Siap diajukan</span>
                        <span class="preview-filter-count" id="badgeCountReady">0</span>
                    </button>
                </div>

                <div class="position-relative" style="min-width: 260px;">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-12"></i>
                    <input type="text" id="searchInput" oninput="filterFaqs()" class="form-control form-control-sm rounded-3 ps-5 fs-13" placeholder="Cari pertanyaan...">
                </div>
            </div>

            <!-- Tabel Pratinjau -->
            <div class="preview-table-card">
                <div class="table-responsive">
                    <table class="table faq-table align-middle mb-0" id="previewTable">
                        <thead>
                            <tr>
                                <th style="width: 44px;" class="text-center">
                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input" onchange="toggleSelectAll(this)">
                                </th>
                                <th style="width: 120px;">KODE / VERSI</th>
                                <th style="min-width: 280px;">PERTANYAAN & JAWABAN</th>
                                <th style="min-width: 190px;">KATA KUNCI</th>
                                <th style="min-width: 190px;">SUMBER PUSTAKA</th>
                                <th style="width: 130px;">STATUS</th>
                                <th style="width: 145px; text-align: right;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($items as $item)
                                @php
                                    $rowKey = $item['row_key'] ?? $item['version_id'] ?? ($item['id'] . '-V1');
                                    $verId = $item['version_id'] ?? ($item['id'] . '-V1');
                                    $verLabel = $item['version'] ?? 'Versi 1';
                                @endphp
                                <tr class="faq-row {{ !empty($item['checked']) ? 'preview-row-selected' : '' }}"
                                    data-status="{{ $item['status'] }}"
                                    data-id="{{ $item['id'] }}"
                                    data-row-key="{{ $rowKey }}"
                                    data-version-id="{{ $verId }}"
                                    data-version="{{ $verLabel }}"
                                    data-pertanyaan="{{ e($item['pertanyaan']) }}"
                                    data-jawaban="{{ e($item['jawaban']) }}"
                                    data-tags="{{ e(implode(', ', $item['tags'] ?? [])) }}"
                                    data-pustaka-judul="{{ e($item['pustaka_judul'] ?? '') }}"
                                    data-pustaka-bagian="{{ e($item['pustaka_bagian'] ?? '') }}"
                                    id="row-{{ $rowKey }}">
                                    <td class="text-center">
                                        <input type="checkbox"
                                               class="form-check-input row-checkbox"
                                               value="{{ $rowKey }}"
                                               {{ !empty($item['checked']) ? 'checked' : '' }}
                                               onchange="handleRowCheckboxChange(this, '{{ $rowKey }}')">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-12">{{ $item['id'] }}</div>
                                        <div class="fs-11 text-muted">{{ $verId }} ({{ $verLabel }})</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-13 line-clamp-1 mb-0.5 faq-pertanyaan">{{ $item['pertanyaan'] }}</div>
                                        <div class="fs-12 text-muted line-clamp-1 faq-jawaban">{{ strip_tags($item['jawaban']) }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 faq-tags-container">
                                            @if(!empty($item['tags']))
                                                @foreach($item['tags'] as $tag)
                                                    <span class="keyword-pill-blue">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                <span class="keyword-pill-empty">Belum diisi</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($item['pustaka_judul']))
                                            <div class="fw-bold text-dark fs-12 line-clamp-1 faq-pustaka-judul">{{ $item['pustaka_judul'] }}</div>
                                            <div class="fs-11 text-muted line-clamp-1 faq-pustaka-bagian">{{ $item['pustaka_bagian'] ?: 'Dokumen Rujukan Resmi' }}</div>
                                        @else
                                            <div class="fw-bold text-danger fs-12 faq-pustaka-judul">Belum diisi</div>
                                            <div class="fs-11 text-muted faq-pustaka-bagian">Pilih / ketik sumber</div>
                                        @endif
                                    </td>
                                    <td class="faq-status-cell">
                                        @if($item['status'] === 'ready')
                                            <span class="badge-ready">Siap diajukan</span>
                                        @else
                                            <span class="badge-incomplete">Belum lengkap</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;" class="text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" class="btn-table-edit" onclick="openEditModal('{{ $rowKey }}')">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn-table-delete" onclick="deleteRow('{{ $rowKey }}')">
                                                <i class="bi bi-trash3"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel -->
                <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 border-top bg-white fs-12 text-muted">
                    <div>
                        Menampilkan <span id="visibleCounter">{{ count($items) }}</span> dari <span id="totalCounter">{{ count($items) }}</span> FAQ &middot; <span id="selectedCounterText">0 dipilih</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="page-pill"><i class="bi bi-chevron-left"></i></span>
                        <span class="page-pill active">1</span>
                        <span class="page-pill"><i class="bi bi-chevron-right"></i></span>
                    </div>
                </div>
            </div>

            <!-- Form Submit Pratinjau -->
            <form id="submitPratinjauForm" action="{{ route('dataentry.pratinjau.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="submitActionInput" value="submit">
                <input type="hidden" name="items_json" id="itemsJsonInput">
                <div id="selectedIdsContainer"></div>

                <!-- Sticky Bottom Action Bar -->
                <div class="sticky-bottom-bar d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="selected-pill-badge" id="bottomPillCount">0 dipilih</span>
                        <span class="fs-12 text-muted">FAQ siap diajukan akan diperiksa oleh validator sebelum tersedia.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="saveAsDraft()" class="btn-draft">
                            Simpan Draft
                        </button>
                        <button type="button" onclick="submitSelectedForValidation()" class="btn-submit-validasi">
                            <i class="bi bi-arrow-right"></i>
                            <span id="btnSubmitValidationText">Ajukan Validasi (0)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Modal Edit Baris Pratinjau FAQ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold fs-16" id="modalEditTitle">Edit FAQ</h5>
                    <div class="fs-12 text-muted">Lengkapi pertanyaan, jawaban, pilih kata kunci, dan sumber pustaka agar siap diajukan.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editRowId">

                <div class="mb-3">
                    <label class="form-label-custom form-label-required">Pertanyaan FAQ</label>
                    <textarea id="modalEditPertanyaan" rows="2" class="form-control fs-13 rounded-3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom form-label-required">Jawaban FAQ</label>
                    <textarea id="modalEditJawaban" rows="3" class="form-control fs-13 rounded-3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom form-label-required">Kata Kunci</label>
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-5">
                            <select id="modalSelectExistingTag" class="form-select fs-13 rounded-3" onchange="pickExistingTagFromDropdown(this)">
                                <option value="">+ Pilih dari kata kunci yang sudah ada...</option>
                                @if(isset($tags) && $tags->isNotEmpty())
                                    @foreach($tags as $t)
                                        <option value="{{ $t->nama }}">{{ $t->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <input type="text" id="modalEditTags" class="form-control fs-13 rounded-3" placeholder="contoh: akademik, cuti, mahasiswa S1 (pisah dengan koma)">
                        </div>
                    </div>

                    @if(isset($tags) && $tags->isNotEmpty())
                        <div class="p-2.5 bg-light border rounded-3">
                            <div class="fs-11 text-muted mb-1.5">Klik untuk menambahkan/melepas pilihan kata kunci dari database:</div>
                            <div class="d-flex flex-wrap gap-1" style="max-height: 95px; overflow-y: auto;">
                                @foreach($tags as $t)
                                    <button type="button"
                                            class="btn btn-sm btn-white border rounded-pill fs-11 py-0.5 px-2.5 modal-tag-choice"
                                            data-tag-name="{{ $t->nama }}"
                                            onclick="appendExistingTagToModal('{{ addslashes($t->nama) }}')">
                                        + {{ $t->nama }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label-custom form-label-required">Judul / Link Sumber Pustaka</label>
                    <div class="row g-2">
                        <div class="col-12 col-md-5">
                            <select id="modalSelectExistingPustaka" class="form-select fs-13 rounded-3" onchange="pickExistingPustakaFromDropdown(this)">
                                <option value="">-- Pilih dari Sumber Pustaka yang Ada --</option>
                                @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                                    @foreach($daftarPustaka as $dp)
                                        <option value="{{ $dp->link }}">{{ $dp->link ?: ('Dokumen #' . $dp->id) }} ({{ strtoupper($dp->type) }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <input type="text" id="modalEditJudulPustaka" class="form-control fs-13 rounded-3" maxlength="45" list="existingPustakaList" placeholder="Atau ketik judul/link sumber pustaka baru...">
                            <datalist id="existingPustakaList">
                                @if(isset($daftarPustaka))
                                    @foreach($daftarPustaka as $dp)
                                        <option value="{{ $dp->link }}"></option>
                                    @endforeach
                                @endif
                            </datalist>
                        </div>
                    </div>
                    <div class="fs-11 text-muted mt-1">Pilih dari daftar sumber pustaka yang sudah ada di kiri, atau ketik sumber pustaka baru di kanan.</div>
                </div>

                <div class="mb-1">
                    <label class="form-label-custom">Bagian / Keterangan Sumber Pustaka</label>
                    <input type="text" id="modalEditBagian" class="form-control fs-13 rounded-3" placeholder="contoh: Bagian Cuti Akademik">
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-maroon px-3" onclick="saveEditedFaq()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unggah File Baru -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="reuploadExcelForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Unggah File FAQ Baru (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="handleNewExcelUpload(this)">
                        </label>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Format Kolom:</span>
                            <a href="{{ route('dataentry.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong> &middot; Kolom D: <strong>Sumber Pustaka</strong> &middot; Kolom E: <strong>Bagian Sumber</strong></div>
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
    let currentFilter = 'all';

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

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function updateCounters() {
        const rows = document.querySelectorAll('.faq-row');
        const total = rows.length;
        let readyCount = 0;
        let incompleteCount = 0;
        let selectedCount = 0;
        let visibleCount = 0;

        rows.forEach(row => {
            const st = row.getAttribute('data-status');
            const cb = row.querySelector('.row-checkbox');
            if (st === 'ready') readyCount++;
            if (st === 'incomplete') incompleteCount++;
            if (cb && cb.checked) selectedCount++;
            if (row.style.display !== 'none') visibleCount++;
        });

        document.getElementById('badgeCountAll').textContent = total;
        document.getElementById('badgeCountReady').textContent = readyCount;
        document.getElementById('badgeCountIncomplete').textContent = incompleteCount;

        const selectedText = `${selectedCount} dipilih`;
        document.getElementById('bottomPillCount').textContent = selectedText;
        document.getElementById('selectedCounterText').textContent = selectedText;
        document.getElementById('btnSubmitValidationText').textContent = `Ajukan Validasi (${selectedCount})`;
        document.getElementById('totalCounter').textContent = total;
        document.getElementById('visibleCounter').textContent = visibleCount;
    }

    function setFilter(filterType, element) {
        currentFilter = filterType;
        document.querySelectorAll('.preview-filter-pill').forEach(btn => btn.classList.remove('active'));
        if (element) element.classList.add('active');
        filterFaqs();
    }

    function filterFaqs() {
        const query = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.faq-row').forEach(row => {
            const st = row.getAttribute('data-status');
            const text = row.textContent.toLowerCase();
            const matchFilter = (currentFilter === 'all') || (st === currentFilter);
            const matchSearch = !query || text.includes(query);
            row.style.display = (matchFilter && matchSearch) ? '' : 'none';
        });
        updateCounters();
    }

    function toggleSelectAll(masterCb) {
        const isChecked = masterCb.checked;
        document.querySelectorAll('.faq-row:not([style*="display: none"])').forEach(row => {
            const cb = row.querySelector('.row-checkbox');
            if (cb) {
                cb.checked = isChecked;
                row.classList.toggle('preview-row-selected', isChecked);
            }
        });
        updateCounters();
    }

    function handleRowCheckboxChange(cb, rowId) {
        const row = document.getElementById('row-' + rowId);
        if (row) row.classList.toggle('preview-row-selected', cb.checked);
        updateCounters();
    }

    // Edit Modal
    function openEditModal(rowId) {
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        const faqCode = row.getAttribute('data-id') || rowId;
        const verId = row.getAttribute('data-version-id') || rowId;
        const currentPustaka = row.getAttribute('data-pustaka-judul') || '';

        document.getElementById('editRowId').value = rowId;
        document.getElementById('modalEditTitle').textContent = `Edit FAQ - ${faqCode} (${verId})`;
        document.getElementById('modalEditPertanyaan').value = row.getAttribute('data-pertanyaan') || '';
        document.getElementById('modalEditJawaban').value = row.getAttribute('data-jawaban') || '';
        document.getElementById('modalEditTags').value = row.getAttribute('data-tags') || '';
        document.getElementById('modalEditJudulPustaka').value = currentPustaka;
        document.getElementById('modalEditBagian').value = row.getAttribute('data-pustaka-bagian') || '';

        const tagSelect = document.getElementById('modalSelectExistingTag');
        if (tagSelect) tagSelect.value = '';

        const pustakaSelect = document.getElementById('modalSelectExistingPustaka');
        if (pustakaSelect) {
            let matched = false;
            Array.from(pustakaSelect.options).forEach(opt => {
                if (opt.value && opt.value.toLowerCase() === currentPustaka.toLowerCase()) {
                    pustakaSelect.value = opt.value;
                    matched = true;
                }
            });
            if (!matched) pustakaSelect.value = '';
        }

        highlightModalTagChoices();

        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }

    function pickExistingTagFromDropdown(selectEl) {
        const val = (selectEl.value || '').trim();
        if (val) {
            appendExistingTagToModal(val, true);
            selectEl.value = '';
        }
    }

    function appendExistingTagToModal(tagName, forceAddOnly = false) {
        const input = document.getElementById('modalEditTags');
        if (!input) return;

        const currentTags = input.value
            ? input.value.split(',').map(t => t.trim()).filter(Boolean)
            : [];

        const existingIndex = currentTags.findIndex(t => t.toLowerCase() === tagName.trim().toLowerCase());
        if (existingIndex === -1) {
            currentTags.push(tagName.trim());
        } else if (!forceAddOnly) {
            currentTags.splice(existingIndex, 1);
        }

        input.value = currentTags.join(', ');
        highlightModalTagChoices();
    }

    function highlightModalTagChoices() {
        const input = document.getElementById('modalEditTags');
        if (!input) return;

        const activeTags = input.value
            ? input.value.split(',').map(t => t.trim().toLowerCase()).filter(Boolean)
            : [];

        document.querySelectorAll('.modal-tag-choice').forEach(btn => {
            const name = (btn.getAttribute('data-tag-name') || '').trim();
            const isSelected = activeTags.includes(name.toLowerCase());
            if (isSelected) {
                btn.classList.remove('btn-white', 'border');
                btn.classList.add('btn-primary', 'text-white');
                btn.textContent = '✓ ' + name;
            } else {
                btn.classList.add('btn-white', 'border');
                btn.classList.remove('btn-primary', 'text-white');
                btn.textContent = '+ ' + name;
            }
        });
    }

    document.getElementById('modalEditTags')?.addEventListener('input', highlightModalTagChoices);

    function pickExistingPustakaFromDropdown(selectEl) {
        const val = (selectEl.value || '').trim();
        if (val) {
            const input = document.getElementById('modalEditJudulPustaka');
            if (input) input.value = val;
        }
    }

    function saveEditedFaq() {
        const rowId = document.getElementById('editRowId').value;
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        const pertanyaan = document.getElementById('modalEditPertanyaan').value.trim();
        const jawaban = document.getElementById('modalEditJawaban').value.trim();
        const tagsRaw = document.getElementById('modalEditTags').value.trim();
        const pustakaJudul = document.getElementById('modalEditJudulPustaka').value.trim();
        const pustakaBagian = document.getElementById('modalEditBagian').value.trim() || 'Dokumen Rujukan Resmi';

        const tagsArr = tagsRaw ? tagsRaw.split(',').map(t => t.trim()).filter(Boolean) : [];
        const isReady = (pertanyaan !== '' && jawaban !== '' && tagsArr.length > 0 && pustakaJudul !== '');
        const newStatus = isReady ? 'ready' : 'incomplete';

        // Update atribut data pada baris DOM
        row.setAttribute('data-pertanyaan', pertanyaan);
        row.setAttribute('data-jawaban', jawaban);
        row.setAttribute('data-tags', tagsArr.join(', '));
        row.setAttribute('data-pustaka-judul', pustakaJudul);
        row.setAttribute('data-pustaka-bagian', pustakaBagian);
        row.setAttribute('data-status', newStatus);

        row.querySelector('.faq-pertanyaan').textContent = pertanyaan;
        row.querySelector('.faq-jawaban').textContent = jawaban;

        const tagsContainer = row.querySelector('.faq-tags-container');
        if (tagsArr.length > 0) {
            tagsContainer.innerHTML = tagsArr.map(t => `<span class="keyword-pill-blue">${escHtml(t)}</span>`).join('');
        } else {
            tagsContainer.innerHTML = `<span class="keyword-pill-empty">Belum diisi</span>`;
        }

        const judulEl = row.querySelector('.faq-pustaka-judul');
        const bagianEl = row.querySelector('.faq-pustaka-bagian');
        if (pustakaJudul) {
            judulEl.textContent = pustakaJudul;
            judulEl.classList.remove('text-danger');
            judulEl.classList.add('text-dark');
            bagianEl.textContent = pustakaBagian;
        } else {
            judulEl.textContent = 'Belum diisi';
            judulEl.classList.add('text-danger');
            bagianEl.textContent = 'Pilih / ketik sumber';
        }

        const statusCell = row.querySelector('.faq-status-cell');
        if (isReady) {
            statusCell.innerHTML = `<span class="badge-ready">Siap diajukan</span>`;
            const cb = row.querySelector('.row-checkbox');
            if (cb) {
                cb.checked = true;
                row.classList.add('preview-row-selected');
            }
        } else {
            statusCell.innerHTML = `<span class="badge-incomplete">Belum lengkap</span>`;
        }

        // Kirim juga ke session backend via AJAX
        fetch(`{{ route('dataentry.pratinjau.updateItem') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: rowId,
                pertanyaan: pertanyaan,
                jawaban: jawaban,
                tags: tagsArr.join(','),
                pustaka_judul: pustakaJudul,
                pustaka_bagian: pustakaBagian
            })
        }).catch(() => {});

        bootstrap.Modal.getInstance(document.getElementById('editModal'))?.hide();
        filterFaqs();
    }

    function deleteRow(rowId) {
        Swal.fire({
            title: 'Hapus ' + rowId + ' dari Pratinjau?',
            text: 'Baris ini akan dihapus dari daftar unggahan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) {
                document.getElementById('row-' + rowId)?.remove();
                fetch(`{{ route('dataentry.pratinjau.deleteItem') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: rowId })
                }).catch(() => {});
                updateCounters();
            }
        });
    }

    function collectCurrentItemsState() {
        const items = [];
        document.querySelectorAll('.faq-row').forEach(row => {
            const tagsStr = row.getAttribute('data-tags') || '';
            const tags = tagsStr ? tagsStr.split(',').map(t => t.trim()).filter(Boolean) : [];
            items.push({
                row_key: row.getAttribute('data-row-key') || row.getAttribute('data-version-id') || row.getAttribute('data-id'),
                id: row.getAttribute('data-id'),
                version_id: row.getAttribute('data-version-id') || (row.getAttribute('data-id') + '-V1'),
                version: row.getAttribute('data-version') || 'Versi 1',
                pertanyaan: row.getAttribute('data-pertanyaan') || '',
                jawaban: row.getAttribute('data-jawaban') || '',
                tags: tags,
                pustaka_judul: row.getAttribute('data-pustaka-judul') || '',
                pustaka_bagian: row.getAttribute('data-pustaka-bagian') || '',
                status: row.getAttribute('data-status') || 'incomplete'
            });
        });
        return items;
    }

    function submitFormWithAction(actionType) {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkedBoxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada FAQ Dipilih',
                text: 'Centang minimal 1 baris FAQ pada tabel terlebih dahulu.',
                confirmButtonColor: '#85172C'
            });
            return;
        }

        // Jika ingin mengajukan validasi, cek apakah ada yang masih 'Belum lengkap'
        if (actionType === 'submit') {
            let hasIncomplete = false;
            checkedBoxes.forEach(cb => {
                const r = cb.closest('.faq-row');
                if (r && r.getAttribute('data-status') !== 'ready') {
                    hasIncomplete = true;
                }
            });

            if (hasIncomplete) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ada FAQ Belum Lengkap',
                    text: 'Beberapa FAQ yang Anda pilih masih berstatus "Belum lengkap". Klik tombol Edit pada baris tersebut untuk melengkapi Kata Kunci / Sumber Pustaka, atau simpan sebagai Draft.',
                    confirmButtonColor: '#85172C'
                });
                return;
            }
        }

        const container = document.getElementById('selectedIdsContainer');
        container.innerHTML = '';
        checkedBoxes.forEach(cb => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'selected_ids[]';
            inp.value = cb.value;
            container.appendChild(inp);
        });

        document.getElementById('submitActionInput').value = actionType;
        document.getElementById('itemsJsonInput').value = JSON.stringify(collectCurrentItemsState());
        document.getElementById('submitPratinjauForm').submit();
    }

    function saveAsDraft() {
        submitFormWithAction('draft');
    }

    function submitSelectedForValidation() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (checkedCount === 0) {
            submitFormWithAction('submit');
            return;
        }

        Swal.fire({
            title: `Ajukan ${checkedCount} FAQ ke Validator?`,
            text: 'FAQ yang dipilih akan dikirim ke ruang kerja Validator untuk diperiksa.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Ajukan Validasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) submitFormWithAction('submit');
        });
    }

    function handleNewExcelUpload(input) {
        if (input.files && input.files[0]) {
            Swal.fire({
                title: 'Membaca & Mengonversi File...',
                text: 'Sedang memproses ' + input.files[0].name,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            document.getElementById('reuploadExcelForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateCounters();
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

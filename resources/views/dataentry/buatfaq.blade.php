<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($isEdit) && $isEdit ? 'Edit FAQ - ' . $faq->id : 'Buat FAQ' }} - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Data Entry CSS -->
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">
</head>
<body>

<div class="d-flex">
    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand mb-3">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div class="mb-3">
                <span class="role-pill">Ruang Data Entry</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('dataentry.dashboard') }}" class="nav-link-custom">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom {{ !isset($isEdit) ? 'active' : '' }}">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section -->
        <div>
            <hr class="sidebar-divider my-3">
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link-custom text-secondary" style="cursor: pointer;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ===================== MAIN ===================== -->
    <main class="flex-grow-1 min-vw-0 d-flex flex-column" style="min-height: 100vh;">

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
                    <span class="mx-2">/</span>
                    <span class="text-dark fw-semibold">Data Entry</span>
                </div>
            </div>

            <!-- Right: Search, Profile, Bell -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                            <a class="dropdown-item py-2 fs-13 text-danger d-flex align-items-center gap-2"
                                href="javascript:void(0)" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>
            </div>
        </header>

        <!-- ===================== CONTENT AREA ===================== -->
        <div class="content-area flex-grow-1" style="padding-bottom: 0;">

            <!-- Back link -->
            <a href="{{ route('dataentry.dashboard') }}" class="back-link mt-2 d-inline-flex">
                <i class="bi bi-chevron-left"></i>
                <span>Daftar FAQ</span>
            </a>

            <!-- Page Title Row -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="display-serif mb-1" style="font-size: 2rem; font-weight: 600; letter-spacing: -0.01em;">
                        {{ isset($isEdit) && $isEdit ? 'Edit FAQ' : 'Buat FAQ' }}
                    </h1>
                    <p class="text-muted fs-14 mb-0">
                        {{ isset($isEdit) && $isEdit ? 'Perbarui draft FAQ Anda atau tindak lanjuti catatan validator.' : 'Susun jawaban yang jelas dan lengkapi dengan sumber pustaka resmi.' }}
                    </p>
                </div>
            </div>

            @php
                $isEditing = isset($isEdit) && $isEdit;
                $activeFaq = $isEditing ? $faq : null;
                $latestVer = $isEditing ? $faq->latest_version : null;
                $faqStatus = $latestVer ? strtolower($latestVer->status) : 'draft';
                $formAction = $isEditing ? route('dataentry.faq.update', $faq->id) : route('dataentry.buatfaq.store');
                $categoryName = $isEditing ? ($faq->tags->pluck('nama')->first() ?? '') : '';
            @endphp

            @if($isEditing && $faqStatus === 'rejected' && $latestVer && $latestVer->catatan)
                <!-- Alert Catatan Revisi Validator -->
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-start gap-3" style="background-color: #FEF2F2;">
                    <div class="flex-shrink-0 text-danger fs-4">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-danger mb-1 fs-14">Perlu Revisi (Catatan Validator)</h6>
                        <div class="fs-13 text-dark lh-base">{{ $latestVer->catatan }}</div>
                        <div class="fs-11 text-muted mt-1">Perubahan yang disimpan akan dibuat sebagai versi baru (Versi {{ ($faq->versions->count() + 1) }}).</div>
                    </div>
                </div>
            @endif

            <!-- ===== FORM SECTION CARD ===== -->
            <form id="faqForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" id="formStatus" value="draft">
                <input type="hidden" name="faq_id" value="{{ $isEditing ? $faq->id : ($newFaqId ?? 'FAQ-0001') }}">
                <input type="hidden" name="tags" id="hiddenTags">
                <input type="hidden" name="pustaka_id" id="hiddenPustakaId" value="{{ $latestVer?->tDaftarPustakas_id ?? '' }}">

                <div class="form-section">
                    <!-- Meta Bar: FAQ ID, Versi & Status Badge -->
                    <div class="form-meta-bar">
                        <div>
                            <div class="meta-id">
                                {{ $isEditing ? $faq->id : ($newFaqId ?? 'FAQ-0001') }} &middot; Versi {{ $isEditing ? ($faqStatus === 'rejected' ? ($faq->versions->count() + 1) . ' (Revisi)' : ($faq->versions->count() ?: 1)) : '1' }}
                            </div>
                            <div class="meta-desc">
                                {{ $isEditing ? 'Form perbaikan/pembaruan FAQ. Tersimpan di tabel tfaqs & tfaqsversion.' : 'Form manual untuk menyusun FAQ dari awal. ID di-generate otomatis sesuai urutan database.' }}
                            </div>
                        </div>
                        <span class="badge-status {{ $isEditing && $faqStatus === 'rejected' ? 'badge-status-revisi' : 'badge-status-draft' }} flex-shrink-0">
                            {{ $isEditing && $faqStatus === 'rejected' ? 'Revisi Versi' : 'Draft' }}
                        </span>
                    </div>

                    <!-- Kategori Dropdown -->
                    <div class="mb-4">
                        <label class="form-label-custom form-label-required">Kategori FAQ</label>
                        <select name="kategori" id="inputKategori" class="form-select rounded-3 fs-13 py-2" required style="border-color: #E2E8F0;">
                            <option value="" disabled {{ empty($categoryName) && !old('kategori') ? 'selected' : '' }}>Pilih kategori klasifikasi...</option>
                            @php
                                $standardCats = ['Akademik', 'Keuangan', 'Kelulusan', 'Transkrip & Nilai', 'Rencana Studi', 'Administrasi'];
                                $selectedCategory = old('kategori', $categoryName);
                            @endphp
                            @foreach($standardCats as $cat)
                                <option value="{{ $cat }}" {{ $selectedCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                            @if(isset($categories))
                                @foreach($categories as $c)
                                    @if(!in_array($c->nama, $standardCats))
                                        <option value="{{ $c->nama }}" {{ $selectedCategory == $c->nama ? 'selected' : '' }}>{{ $c->nama }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Pertanyaan FAQ -->
                    <div class="mb-4">
                        <label class="form-label-custom form-label-required">Pertanyaan FAQ</label>
                        <textarea
                            id="inputPertanyaan"
                            name="pertanyaan"
                            class="faq-question-input"
                            rows="2"
                            placeholder="Tulis pertanyaan yang sering diajukan mahasiswa..."
                            required>{{ old('pertanyaan', $isEditing ? $faq->pertanyaan : '') }}</textarea>

                        <!-- Live Check Warning Container -->
                        <div id="duplicateWarning" class="alert alert-warning d-none mt-2 p-2.5 fs-13 d-flex align-items-start gap-2 border-0 rounded-3" style="background-color: #FEF3C7; color: #92400E;">
                            <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 text-warning"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">Perhatian: Pertanyaan Serupa Terdeteksi</strong>
                                <div id="duplicateList" class="fs-12"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Jawaban (Rich Text Editor) -->
                    <div class="mb-4">
                        <label class="form-label-custom form-label-required">Jawaban</label>
                        <div class="rich-editor-wrapper">
                            <!-- Toolbar -->
                            <div class="editor-toolbar">
                                <button type="button" class="editor-toolbar-btn" data-cmd="bold" title="Tebal"><strong>B</strong></button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="italic" title="Miring"><em>I</em></button>
                                <div class="editor-toolbar-divider"></div>
                                <button type="button" class="editor-toolbar-btn" data-cmd="createLink" title="Tautan">
                                    <i class="bi bi-link-45deg"></i> Link
                                </button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" title="Daftar Bernomor">
                                    <i class="bi bi-list-ol"></i> List
                                </button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="insertImage" title="Sisipkan Gambar">
                                    <i class="bi bi-image"></i> Gambar
                                </button>
                            </div>
                            <!-- Editable content area -->
                            <div
                                id="editorJawaban"
                                class="editor-content"
                                contenteditable="true"
                                data-placeholder="Tulis jawaban lengkap di sini...">{!! old('jawaban', $isEditing && $latestVer ? $latestVer->jawaban : '') !!}</div>
                        </div>
                        <!-- Hidden input to capture editor content -->
                        <input type="hidden" name="jawaban" id="hiddenJawaban">
                    </div>

                    <!-- Kata Kunci / Tags -->
                    <div class="mb-4">
                        <label class="form-label-custom">Kata kunci</label>
                        <div class="tag-container" id="tagContainer" onclick="document.getElementById('tagInput').focus()">
                            @if($isEditing && $faq->tags)
                                @foreach($faq->tags as $t)
                                    @if($t->nama !== $categoryName)
                                        <span class="tag-chip">
                                            {{ $t->nama }}
                                            <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>
                                        </span>
                                    @endif
                                @endforeach
                            @endif
                            <!-- Input for new tags -->
                            <input
                                type="text"
                                id="tagInput"
                                class="tag-add-placeholder"
                                placeholder="Tambah kata kunci..."
                                onkeydown="handleTagInput(event)"
                                autocomplete="off">
                        </div>
                        <div class="fs-12 text-muted mt-1">Tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk menambah kata kunci.</div>
                    </div>

                    <!-- Rujukan Sumber Pustaka (Terintegrasi Pilihan & Unggah) -->
                    <div class="mb-2">
                        <label class="form-label-custom form-label-required">Rujukan sumber pustaka</label>

                        <!-- Buttons Group -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="source-add-btn" onclick="openSourceModal()">
                                <i class="bi bi-book"></i>
                                <span>Pilih dari Sumber Pustaka</span>
                            </button>
                            <label class="btn-white-outline mb-0" style="cursor: pointer;">
                                <i class="bi bi-upload"></i>
                                <span>Unggah Dokumen (PDF/DOCX)</span>
                                <input type="file" name="sumber_file" id="sumberFileInput" class="d-none" onchange="handleFilePicked(this)">
                            </label>
                        </div>

                        <!-- Direct URL / Link Input -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted fs-13" style="border-color: #E2E8F0;">
                                    <i class="bi bi-link-45deg"></i>
                                </span>
                                <input type="text" name="sumber_link" id="inputSumberLink" class="form-control fs-13 border-start-0"
                                    placeholder="Atau tautan rujukan resmi (maksimal 45 karakter)..."
                                    maxlength="45"
                                    value="{{ old('sumber_link', ($isEditing && $latestVer && $latestVer->daftarPustaka && $latestVer->daftarPustaka->type === 'link') ? $latestVer->daftarPustaka->link : '') }}"
                                    style="border-color: #E2E8F0;">
                            </div>
                            <div class="fs-11 text-muted mt-1">Format URL atau SK resmi (disimpan di kolom link tabel tdaftarpustaka).</div>
                        </div>

                        <!-- Source items list -->
                        <div id="sourceList">
                            @if($isEditing && $latestVer && $latestVer->daftarPustaka)
                                @php $dp = $latestVer->daftarPustaka; @endphp
                                <div class="source-item" id="source-existing">
                                    <button type="button" class="source-remove-btn" onclick="clearSelectedSource()" title="Hapus sumber">&times;</button>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="source-item-number">1</span>
                                        <span class="source-item-title">{{ $dp->link ?: 'Dokumen Sumber Pustaka #' . $dp->id }}</span>
                                    </div>
                                    <div class="source-item-meta">
                                        Tipe: {{ strtoupper($dp->type) }} &middot; Tersimpan di database
                                    </div>
                                </div>
                            @else
                                <div class="source-item" id="source-default">
                                    <button type="button" class="source-remove-btn" onclick="clearSelectedSource()" title="Hapus sumber">&times;</button>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="source-item-number">1</span>
                                        <span class="source-item-title" id="selectedSourceTitle">Pedoman Akademik 2026/2027</span>
                                    </div>
                                    <div class="source-item-meta" id="selectedSourceMeta">Biro Akademik &middot; Bagian Cuti Akademik &middot; Dokumen contoh resmi</div>
                                </div>
                            @endif
                        </div>

                        <div id="sourceEmptyMsg" class="fs-12 text-muted mt-2" style="display:none;">
                            Belum ada sumber pustaka yang dipilih atau diunggah.
                        </div>
                    </div>
                </div><!-- /form-section -->

            </form><!-- /faqForm -->
        </div><!-- /content-area -->

        <!-- ===================== STICKY FOOTER ACTION BAR ===================== -->
        <div class="form-action-bar">
            <a href="{{ route('dataentry.dashboard') }}" class="btn-cancel">
                Batal
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn-draft" onclick="saveDraft()">
                    Simpan Draft
                </button>
                <button type="button" class="btn-submit-validasi" onclick="submitValidasi()">
                    <i class="bi bi-send-check"></i>
                    Ajukan Validasi
                </button>
            </div>
        </div>

    </main>
</div>


<!-- =================== MODAL: Pilih Sumber Pustaka (tdaftarpustaka) =================== -->
<div class="modal fade" id="sourceModal" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-16" id="sourceModalLabel">Pilih Sumber Pustaka dari Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                    <input type="text" id="sourceSearchInput" class="form-control rounded-3 ps-5 fs-13"
                        placeholder="Cari dokumen atau tautan sumber pustaka..."
                        style="border-color: #E2E8F0; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                </div>

                <div id="sourceOptionsList">
                    @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                        @foreach($daftarPustaka as $p)
                            <div class="source-option d-flex align-items-start gap-3 p-3 rounded-3 mb-2"
                                style="border: 1px solid #E2E8F0; cursor: pointer; transition: background 0.15s;"
                                onmouseenter="this.style.backgroundColor='#F8FAFC'" onmouseleave="this.style.backgroundColor=''"
                                onclick="selectSource('{{ $p->id }}', '{{ addslashes($p->link ?: 'Dokumen #' . $p->id) }}', 'Tipe: {{ strtoupper($p->type) }}')">
                                <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center"
                                    style="width:36px; height:36px; background:{{ $p->type === 'upload' ? '#EFF6FF; color:#2563EB;' : '#F0FDF4; color:#15803D;' }} font-size: 1rem;">
                                    <i class="bi {{ $p->type === 'upload' ? 'bi-file-earmark-pdf' : 'bi-link-45deg' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold fs-13 text-dark">{{ $p->link ?: 'Dokumen ID #' . $p->id }}</div>
                                    <div class="fs-12 text-muted">Tipe Sumber: {{ strtoupper($p->type) }} &middot; ID Database: {{ $p->id }}</div>
                                </div>
                                <span class="badge-status {{ $p->type === 'upload' ? 'badge-status-draft' : 'badge-status-terbit' }} flex-shrink-0">
                                    {{ strtoupper($p->type) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        <!-- Data default/dummy jika tabel tdaftarpustaka belum terisi -->
                        <div class="source-option d-flex align-items-start gap-3 p-3 rounded-3 mb-2"
                            style="border: 1px solid #E2E8F0; cursor: pointer; transition: background 0.15s;"
                            onmouseenter="this.style.backgroundColor='#F8FAFC'" onmouseleave="this.style.backgroundColor=''"
                            onclick="selectSource('', 'Pedoman Akademik 2026/2027', 'Biro Akademik · Bagian Cuti Akademik')">
                            <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center"
                                style="width:36px; height:36px; background:#EEF2FF; color:#4338CA; font-size: 1rem;">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold fs-13 text-dark">Pedoman Akademik 2026/2027</div>
                                <div class="fs-12 text-muted">Biro Akademik &middot; Bagian Cuti Akademik</div>
                            </div>
                            <span class="badge-status badge-status-draft flex-shrink-0">PDF</span>
                        </div>

                        <div class="source-option d-flex align-items-start gap-3 p-3 rounded-3 mb-2"
                            style="border: 1px solid #E2E8F0; cursor: pointer; transition: background 0.15s;"
                            onmouseenter="this.style.backgroundColor='#F8FAFC'" onmouseleave="this.style.backgroundColor=''"
                            onclick="selectSource('', 'SK Rektor No. 12/2026', 'Rektorat · Kebijakan Akademik')">
                            <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center"
                                style="width:36px; height:36px; background:#F0FDF4; color:#15803D; font-size: 1rem;">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold fs-13 text-dark">SK Rektor No. 12/2026</div>
                                <div class="fs-12 text-muted">Rektorat &middot; Kebijakan Akademik</div>
                            </div>
                            <span class="badge-status badge-status-terbit flex-shrink-0">Resmi</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- =================== SCRIPTS =================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /* ---- Sidebar toggle ---- */
    function toggleSidebar() {
        const sidebar   = document.getElementById('sidebar');
        const backdrop  = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    /* ---- Logout confirm ---- */
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
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
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }

    /* ---- Rich text editor toolbar ---- */
    document.querySelectorAll('.editor-toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const cmd = this.getAttribute('data-cmd');
            if (!cmd) return;
            document.getElementById('editorJawaban').focus();

            if (cmd === 'createLink') {
                const url = prompt('Masukkan URL tautan:');
                if (url) document.execCommand(cmd, false, url);
            } else if (cmd === 'insertImage') {
                const imgUrl = prompt('Masukkan URL gambar (atau tautan file gambar):');
                if (imgUrl) document.execCommand('insertImage', false, imgUrl);
            } else {
                document.execCommand(cmd, false, null);
            }
        });
    });

    /* ---- Tag / Keyword chips ---- */
    function handleTagInput(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const input = document.getElementById('tagInput');
            const val   = input.value.replace(',', '').trim();
            if (val) {
                addTag(val);
                input.value = '';
            }
        }
    }

    function addTag(text) {
        const container = document.getElementById('tagContainer');
        const input     = document.getElementById('tagInput');
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `${escHtml(text)} <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>`;
        container.insertBefore(chip, input);
    }

    function removeTag(btn) {
        btn.closest('.tag-chip').remove();
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ---- Source library Modal ---- */
    function openSourceModal() {
        const modal = new bootstrap.Modal(document.getElementById('sourceModal'));
        modal.show();
    }

    function selectSource(pustakaId, title, meta) {
        document.getElementById('hiddenPustakaId').value = pustakaId;
        const list = document.getElementById('sourceList');
        list.innerHTML = `
            <div class="source-item" id="source-selected">
                <button type="button" class="source-remove-btn" onclick="clearSelectedSource()" title="Hapus sumber">&times;</button>
                <div class="d-flex align-items-center mb-1">
                    <span class="source-item-number">1</span>
                    <span class="source-item-title">${escHtml(title)}</span>
                </div>
                <div class="source-item-meta">${escHtml(meta)}</div>
            </div>
        `;
        document.getElementById('sourceEmptyMsg').style.display = 'none';

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('sourceModal')).hide();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Sumber pustaka terpilih',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }
    }

    function clearSelectedSource() {
        document.getElementById('hiddenPustakaId').value = '';
        document.getElementById('sourceList').innerHTML = '';
        document.getElementById('sourceEmptyMsg').style.display = 'block';
    }

    function handleFilePicked(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('hiddenPustakaId').value = '';
            const list = document.getElementById('sourceList');
            list.innerHTML = `
                <div class="source-item" id="source-file">
                    <button type="button" class="source-remove-btn" onclick="clearSelectedSource()" title="Hapus sumber">&times;</button>
                    <div class="d-flex align-items-center mb-1">
                        <span class="source-item-number">1</span>
                        <span class="source-item-title">${escHtml(file.name)}</span>
                    </div>
                    <div class="source-item-meta">File diunggah &middot; ${(file.size / 1024).toFixed(1)} KB &middot; Disimpan sebagai BLOB di tdaftarpustaka</div>
                </div>
            `;
            document.getElementById('sourceEmptyMsg').style.display = 'none';
        }
    }

    /* ---- Real-time Duplicate Check (Live Check) ---- */
    let duplicateTimer = null;
    const inputPertanyaanEl = document.getElementById('inputPertanyaan');
    const warningBox = document.getElementById('duplicateWarning');
    const duplicateListEl = document.getElementById('duplicateList');

    if (inputPertanyaanEl) {
        inputPertanyaanEl.addEventListener('input', function () {
            clearTimeout(duplicateTimer);
            const query = this.value.trim();

            if (query.length < 4) {
                warningBox.classList.add('d-none');
                return;
            }

            duplicateTimer = setTimeout(() => {
                fetch(`{{ route('dataentry.faq.checkDuplicate') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.duplicates && data.duplicates.length > 0) {
                            let html = '<ul class="mb-0 ps-3">';
                            data.duplicates.forEach(d => {
                                const badge = d.is_approved ? '<span class="badge bg-success ms-1 fs-11">Terbit</span>' : '';
                                html += `<li><strong>${escHtml(d.id)}:</strong> ${escHtml(d.pertanyaan)} ${badge}</li>`;
                            });
                            html += '</ul>';
                            duplicateListEl.innerHTML = html;
                            warningBox.classList.remove('d-none');
                        } else {
                            warningBox.classList.add('d-none');
                        }
                    })
                    .catch(() => {
                        warningBox.classList.add('d-none');
                    });
            }, 400);
        });
    }

    /* ---- Sync tags chips into hidden input ---- */
    function syncTags() {
        const chips = document.querySelectorAll('#tagContainer .tag-chip');
        const tags = [];
        chips.forEach(chip => {
            const clone = chip.cloneNode(true);
            const removeBtn = clone.querySelector('.tag-remove');
            if (removeBtn) removeBtn.remove();
            const text = clone.textContent.trim();
            if (text) tags.push(text);
        });
        document.getElementById('hiddenTags').value = tags.join(',');
    }

    /* ---- Save Draft ---- */
    function saveDraft() {
        syncEditorContent();
        syncTags();
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        if (!pertanyaan) {
            Swal.fire({
                icon: 'warning',
                title: 'Pertanyaan Diperlukan',
                text: 'Mohon isi pertanyaan FAQ sebelum menyimpan draft.',
                confirmButtonColor: '#85172C'
            });
            return;
        }
        document.getElementById('formStatus').value = 'draft';
        document.getElementById('faqForm').submit();
    }

    /* ---- Submit Validasi ---- */
    function submitValidasi() {
        syncEditorContent();
        syncTags();
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        const kategori = document.getElementById('inputKategori') ? document.getElementById('inputKategori').value : '';
        const rawJawaban = document.getElementById('hiddenJawaban').value.trim();
        const cleanJawaban = rawJawaban.replace(/<[^>]*>/g, '').trim();

        if (!pertanyaan) {
            Swal.fire({ icon: 'warning', title: 'Pertanyaan Diperlukan', text: 'Mohon isi pertanyaan FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }
        if (!kategori) {
            Swal.fire({ icon: 'warning', title: 'Kategori Diperlukan', text: 'Mohon pilih kategori FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }
        if (!cleanJawaban) {
            Swal.fire({ icon: 'warning', title: 'Jawaban Diperlukan', text: 'Mohon isi jawaban FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }

        Swal.fire({
            title: 'Ajukan untuk Validasi?',
            text: 'FAQ ini akan disimpan dan diajukan ke validator untuk ditinjau.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formStatus').value = 'submitted';
                document.getElementById('faqForm').submit();
            }
        });
    }

    /* ---- Sync hidden input from contenteditable ---- */
    function syncEditorContent() {
        const editor = document.getElementById('editorJawaban');
        if (editor) {
            document.getElementById('hiddenJawaban').value = editor.innerHTML;
        }
    }

    /* Auto-resize textarea */
    document.addEventListener('DOMContentLoaded', function () {
        const ta = document.getElementById('inputPertanyaan');
        if (ta) {
            ta.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        }
    });

    @if(session('error'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}",
            confirmButtonColor: '#85172C'
        });
    });
    @endif
</script>
</body>
</html>

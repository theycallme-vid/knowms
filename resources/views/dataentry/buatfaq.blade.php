<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat FAQ - Ruang Data Entry | KMS</title>
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
                <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom active">
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
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->nama ?? 'NP', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Nadia Putri' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()->getRoleName() ?: 'Data Entry' }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Nadia Putri' }}</div>
                                <div class="fs-11 text-muted">{{ Auth::user()->email ?? '' }}</div>
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
                    <h1 class="display-serif mb-1" style="font-size: 2rem; font-weight: 600; letter-spacing: -0.01em;">Buat FAQ</h1>
                    <p class="text-muted fs-14 mb-0">Susun jawaban yang jelas dan lengkapi dengan sumber pustaka.</p>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="btn-white-outline" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i>
                        <span>Upload File</span>
                    </button>
                </div>
            </div>

            <!-- ===== FORM SECTION CARD ===== -->
            <form id="faqForm" method="POST" action="#">
                @csrf

                <div class="form-section">
                    <!-- Meta Bar: FAQ ID, Versi & Status Badge -->
                    <div class="form-meta-bar">
                        <div>
                            <div class="meta-id">FAQ-0249 &middot; Versi 1</div>
                            <div class="meta-desc">Form manual untuk menyusun FAQ dari awal.</div>
                        </div>
                        <span class="badge-status badge-status-draft flex-shrink-0">Draft</span>
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
                            required>Bagaimana prosedur pengajuan cuti akademik?</textarea>
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
                            </div>
                            <!-- Editable content area -->
                            <div
                                id="editorJawaban"
                                class="editor-content"
                                contenteditable="true"
                                data-placeholder="Tulis jawaban lengkap di sini...">
                                <p>Pengajuan cuti akademik dilakukan melalui layanan akademik sebelum batas waktu pada kalender akademik. Alur pengajuannya sebagai berikut:</p>
                                <ol>
                                    <li>Konsultasikan rencana cuti dengan dosen pembimbing akademik.</li>
                                    <li>Isi formulir cuti dan lampirkan dokumen pendukung yang dipersyaratkan.</li>
                                    <li>Ajukan ke bagian akademik fakultas untuk pemeriksaan dan persetujuan.</li>
                                </ol>
                                <p>Simpan surat persetujuan sebagai bukti. Lihat Pedoman Akademik 2026/2027, bagian Cuti Akademik [1], untuk ketentuan dan dokumen yang diperlukan.</p>
                            </div>
                        </div>
                        <!-- Hidden input to capture editor content -->
                        <input type="hidden" name="jawaban" id="hiddenJawaban">
                    </div>

                    <!-- Kata Kunci / Tags -->
                    <div class="mb-4">
                        <label class="form-label-custom">Kata kunci</label>
                        <div class="tag-container" id="tagContainer" onclick="document.getElementById('tagInput').focus()">
                            <!-- Existing tags -->
                            <span class="tag-chip">cuti akademik <button type="button" class="tag-remove" onclick="removeTag(this)" aria-label="Hapus tag">&times;</button></span>
                            <span class="tag-chip">pengajuan cuti <button type="button" class="tag-remove" onclick="removeTag(this)" aria-label="Hapus tag">&times;</button></span>
                            <span class="tag-chip">mahasiswa S1 <button type="button" class="tag-remove" onclick="removeTag(this)" aria-label="Hapus tag">&times;</button></span>
                            <span class="tag-chip">Akademik <button type="button" class="tag-remove" onclick="removeTag(this)" aria-label="Hapus tag">&times;</button></span>
                            <span class="tag-chip">Fakultas Teknik <button type="button" class="tag-remove" onclick="removeTag(this)" aria-label="Hapus tag">&times;</button></span>
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

                    <!-- Rujukan Sumber Pustaka -->
                    <div class="mb-2">
                        <label class="form-label-custom form-label-required">Rujukan sumber pustaka</label>

                        <!-- Add source button -->
                        <div class="mb-2">
                            <button type="button" class="source-add-btn" onclick="openSourceModal()">
                                <i class="bi bi-plus-lg"></i>
                                <span>+ Pilih dari Sumber Pustaka</span>
                            </button>
                        </div>

                        <!-- Source items list -->
                        <div id="sourceList">
                            <div class="source-item" id="source-1">
                                <button type="button" class="source-remove-btn" onclick="removeSource('source-1')" title="Hapus sumber">&times;</button>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="source-item-number">1</span>
                                    <span class="source-item-title">Pedoman Akademik 2026/2027</span>
                                </div>
                                <div class="source-item-meta">Biro Akademik &middot; Bagian Cuti Akademik &middot; Dokumen contoh, belum diverifikasi</div>
                            </div>
                        </div>

                        <div id="sourceEmptyMsg" class="fs-12 text-muted mt-2" style="display:none;">
                            Belum ada sumber pustaka yang dipilih.
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


<!-- =================== MODAL: Pilih Sumber Pustaka =================== -->
<div class="modal fade" id="sourceModal" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-16" id="sourceModalLabel">Pilih Sumber Pustaka</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-13"></i>
                    <input type="text" id="sourceSearchInput" class="form-control rounded-3 ps-5 fs-13"
                        placeholder="Cari dokumen atau judul sumber..."
                        style="border-color: #E2E8F0; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                </div>

                <div id="sourceOptionsList">
                    <!-- Sample source options -->
                    <div class="source-option d-flex align-items-start gap-3 p-3 rounded-3 mb-2"
                        style="border: 1px solid #E2E8F0; cursor: pointer; transition: background 0.15s;"
                        onmouseenter="this.style.backgroundColor='#F8FAFC'" onmouseleave="this.style.backgroundColor=''"
                        onclick="selectSource('Pedoman Akademik 2026/2027', 'Biro Akademik · Bagian Cuti Akademik')">
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
                        onclick="selectSource('SK Rektor No. 12/2026', 'Rektorat · Kebijakan Akademik')">
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

                    <div class="source-option d-flex align-items-start gap-3 p-3 rounded-3 mb-2"
                        style="border: 1px solid #E2E8F0; cursor: pointer; transition: background 0.15s;"
                        onmouseenter="this.style.backgroundColor='#F8FAFC'" onmouseleave="this.style.backgroundColor=''"
                        onclick="selectSource('Formulir Cuti Akademik FT-2026', 'Fakultas Teknik · Formulir Administrasi')">
                        <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center"
                            style="width:36px; height:36px; background:#FFF7ED; color:#C2410C; font-size: 1rem;">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold fs-13 text-dark">Formulir Cuti Akademik FT-2026</div>
                            <div class="fs-12 text-muted">Fakultas Teknik &middot; Formulir Administrasi</div>
                        </div>
                        <span class="badge-status badge-status-draft flex-shrink-0">DOCX</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-16" id="uploadModalLabel">Unggah Dokumen / Sumber Resmi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light">
                    <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-2 d-block"></i>
                    <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                    <div class="text-muted fs-12 mb-3">Mendukung format PDF, DOCX, atau Gambar hingga 10MB</div>
                    <label class="btn btn-sm btn-white-outline">
                        Pilih Dokumen
                        <input type="file" class="d-none">
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-maroon px-3" data-bs-dismiss="modal">Unggah Sekarang</button>
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
                const url = prompt('Masukkan URL:');
                if (url) document.execCommand(cmd, false, url);
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
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ---- Source library ---- */
    let sourceCount = 1; // already 1 loaded

    function openSourceModal() {
        const modal = new bootstrap.Modal(document.getElementById('sourceModal'));
        modal.show();
    }

    function selectSource(title, meta) {
        sourceCount++;
        const list = document.getElementById('sourceList');
        const id   = 'source-' + Date.now();
        const item = document.createElement('div');
        item.className = 'source-item';
        item.id = id;
        item.innerHTML = `
            <button type="button" class="source-remove-btn" onclick="removeSource('${id}')" title="Hapus sumber">&times;</button>
            <div class="d-flex align-items-center mb-1">
                <span class="source-item-number">${sourceCount}</span>
                <span class="source-item-title">${escHtml(title)}</span>
            </div>
            <div class="source-item-meta">${escHtml(meta)}</div>`;
        list.appendChild(item);

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('sourceModal')).hide();

        // Toast notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Sumber pustaka ditambahkan',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }
    }

    function removeSource(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
        // Re-number
        document.querySelectorAll('#sourceList .source-item-number').forEach((el, i) => {
            el.textContent = i + 1;
        });
        sourceCount = document.querySelectorAll('#sourceList .source-item').length;
        document.getElementById('sourceEmptyMsg').style.display =
            sourceCount === 0 ? 'block' : 'none';
    }

    /* ---- Source search in modal ---- */
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('sourceSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.source-option').forEach(opt => {
                    opt.style.display = opt.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });

    /* ---- Save Draft ---- */
    function saveDraft() {
        syncEditorContent();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Draft berhasil disimpan',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
    }

    /* ---- Submit Validasi ---- */
    function submitValidasi() {
        syncEditorContent();
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        if (!pertanyaan) {
            Swal.fire({ icon: 'warning', title: 'Pertanyaan diperlukan', text: 'Mohon isi pertanyaan FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Ajukan untuk Validasi?',
                text: 'FAQ ini akan dikirim ke validator untuk ditinjau. Pastikan data sudah lengkap.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan',
                cancelButtonText: 'Kembali',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('faqForm').submit();
                }
            });
        } else {
            document.getElementById('faqForm').submit();
        }
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
</script>
</body>
</html>


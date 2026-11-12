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
                <a href="{{ route('dataentry.dashboard') }}" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
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
                    <span class="crumb-current">Buat FAQ</span>
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
            <a href="{{ route('dataentry.dashboard') }}" class="back-link">
                <i class="bi bi-chevron-left"></i>
                <span>Daftar FAQ</span>
            </a>

            <!-- Page Title & Upload Excel/PDF Button -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Buat FAQ</h1>
                    <p class="text-muted fs-14 mb-0">Susun jawaban yang jelas dan lengkapi dengan sumber pustaka resmi</p>
                </div>
                <div>
                    <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i>
                        <span>Import File (Excel/PDF)</span>
                    </button>
                </div>
            </div>

            @php
                $isEditing = isset($isEdit) && $isEdit;
                $latestVer = $isEditing ? $faq->latest_version : null;
                $faqStatus = $latestVer ? strtolower($latestVer->status) : 'draft';
                $formAction = $isEditing ? route('dataentry.faq.update', $faq->id) : route('dataentry.buatfaq.store');
                $defaultMode = (!$isEditing && isset($selectedExistingFaq) && $selectedExistingFaq) ? 'existing' : 'new';

                if ($isEditing) {
                    $shouldCreateNewVerByDefault = ($forceNewVersion ?? false) || in_array($faqStatus, ['rejected', 'approved', 'active']);
                    $displayFaqId = $faq->id;
                    $displayVersionId = $shouldCreateNewVerByDefault
                        ? ($nextVersionId ?? $faq->generateNextVersionId())
                        : ($latestVer ? $latestVer->id : ($faq->id . '-V1'));
                    $displayVerNum = (int) preg_replace('/.*-V/i', '', $displayVersionId) ?: 1;
                } elseif ($defaultMode === 'existing' && $selectedExistingFaq) {
                    $displayFaqId = $selectedExistingFaq->id;
                    $displayVersionId = $selectedExistingFaq->generateNextVersionId();
                    $displayVerNum = (int) preg_replace('/.*-V/i', '', $displayVersionId) ?: 2;
                } else {
                    $displayFaqId = $newFaqId ?? 'FAQ-0001';
                    $displayVersionId = $displayFaqId . '-V1';
                    $displayVerNum = 1;
                }
            @endphp

            @if($isEditing && $faqStatus === 'rejected' && $latestVer && $latestVer->catatan)
                <div class="alert alert-danger rounded-4 border-danger-subtle d-flex align-items-start gap-3 mb-4">
                    <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-1"></i>
                    <div>
                        <div class="fw-bold fs-12 text-uppercase mb-1">Catatan Revisi dari Validator</div>
                        <div class="fs-13 text-dark">{{ $latestVer->catatan }}</div>
                        <div class="fs-11 text-danger mt-1">FAQ ini akan diajukan ulang sebagai versi jawaban baru: <strong>{{ $nextVersionId ?? $faq->generateNextVersionId() }}</strong>.</div>
                    </div>
                </div>
            @endif

            <!-- Main Form Card -->
            <form id="faqForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" id="formStatus" value="draft">
                <input type="hidden" name="faq_id" id="hiddenFaqId" value="{{ $displayFaqId }}">
                <input type="hidden" name="mode_faq" id="hiddenModeFaq" value="{{ $defaultMode }}">
                <input type="hidden" name="version_action" id="hiddenVersionAction" value="{{ ($isEditing && ($shouldCreateNewVerByDefault ?? false)) ? 'new_version' : 'update_draft' }}">
                <input type="hidden" name="tags" id="hiddenTags">
                <input type="hidden" name="pustaka_id" id="hiddenPustakaId" value="{{ $latestVer?->tDaftarPustakas_id ?? '' }}">

                <div class="form-section">
                    <!-- Meta Bar: FAQ ID & Version ID + Status Badge -->
                    <div class="form-meta-bar">
                        <div>
                            <div class="meta-id" id="metaHeaderIdText">
                                {{ $displayFaqId }} &middot; Versi {{ $displayVerNum }} <span class="badge bg-light text-dark border ms-1 fw-semibold" id="metaVersionIdBadge">{{ $displayVersionId }}</span>
                            </div>
                            <div class="meta-desc" id="metaHeaderDesc">
                                @if($isEditing)
                                    Menyusun versi jawaban <strong>{{ $displayVersionId }}</strong> untuk pertanyaan <strong>{{ $faq->id }}</strong>.
                                @else
                                    Jawaban baru dari FAQ baru menggunakan kode <strong>{{ $newFaqId ?? 'FAQ-0001' }}-V1</strong>. Jika menambah versi jawaban dari FAQ yang dipilih, kode menjadi <strong>FAQ-XXXX-V2</strong>, dst.
                                @endif
                            </div>
                        </div>
                        <span class="badge-status {{ $isEditing && $faqStatus === 'rejected' ? 'badge-status-revisi' : 'badge-status-draft' }}">
                            {{ $isEditing && $faqStatus === 'rejected' ? 'Perlu Revisi' : 'Draft' }}
                        </span>
                    </div>

                    @if(!$isEditing)
                        <!-- Pilihan Mode: Buat FAQ Baru (FAQ-XXXX-V1) vs Tambah Versi Jawaban dari FAQ yang Dipilih (FAQ-XXXX-V2) -->
                        <div class="p-3 rounded-3 bg-light border mb-4">
                            <label class="form-label-custom mb-2">Jenis Penyusunan Jawaban FAQ</label>
                            <div class="d-flex flex-wrap gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_faq_radio" id="modeFaqNew" value="new"
                                           {{ $defaultMode === 'new' ? 'checked' : '' }} onchange="switchFaqCreationMode('new')">
                                    <label class="form-check-label fs-13 fw-semibold text-dark" for="modeFaqNew">
                                        Buat Pertanyaan FAQ Baru <span class="text-muted fw-normal"></span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_faq_radio" id="modeFaqExisting" value="existing"
                                           {{ $defaultMode === 'existing' ? 'checked' : '' }} onchange="switchFaqCreationMode('existing')">
                                    <label class="form-check-label fs-13 fw-semibold text-dark" for="modeFaqExisting">
                                        Tambah Versi Jawaban Baru dari FAQ yang Sudah Ada <span class="text-muted fw-normal"></span>
                                    </label>
                                </div>
                            </div>

                            <div id="existingFaqSelectorBox" class="{{ $defaultMode === 'existing' ? '' : 'd-none' }} mt-2 pt-2 border-top">
                                <label class="fs-12 fw-semibold text-secondary mb-1">Pilih FAQ yang ingin dibuatkan versi jawaban barunya:</label>
                                <select name="existing_faq_id" id="existingFaqSelect" class="form-select fs-13 rounded-3" onchange="handleExistingFaqSelected(this)">
                                    <option value="">-- Pilih Kode / Pertanyaan FAQ --</option>
                                    @if(isset($allFaqs))
                                        @foreach($allFaqs as $fItem)
                                            @php
                                                $nextVId = $fItem->generateNextVersionId();
                                                $nextVNum = (int) preg_replace('/.*-V/i', '', $nextVId) ?: 2;
                                                $fTags = $fItem->tags->pluck('nama')->implode(',');
                                            @endphp
                                            <option value="{{ $fItem->id }}"
                                                    data-pertanyaan="{{ e($fItem->pertanyaan) }}"
                                                    data-next-version-id="{{ $nextVId }}"
                                                    data-next-version-num="{{ $nextVNum }}"
                                                    data-tags="{{ e($fTags) }}"
                                                    {{ (isset($selectedExistingFaq) && $selectedExistingFaq && $selectedExistingFaq->id === $fItem->id) ? 'selected' : '' }}>
                                                {{ $fItem->id }} — {{ \Illuminate\Support\Str::limit($fItem->pertanyaan, 75) }} (Versi baru: {{ $nextVId }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    @else
                        <!-- Pilihan Mode saat Edit FAQ yang sudah ada -->
                        <div class="p-3 rounded-3 bg-light border mb-4">
                            <label class="form-label-custom mb-2">Mode Penyimpanan Versi (`tfaqsversion`)</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_version_radio" id="editModeNewVer" value="new_version"
                                           {{ ($shouldCreateNewVerByDefault ?? true) ? 'checked' : '' }}
                                           onchange="switchEditVersionMode('new_version', '{{ $faq->id }}', '{{ $nextVersionId ?? $faq->generateNextVersionId() }}')">
                                    <label class="form-check-label fs-13 fw-semibold text-dark" for="editModeNewVer">
                                        Simpan sebagai Versi Jawaban Baru: <span class="badge bg-dark">{{ $nextVersionId ?? $faq->generateNextVersionId() }}</span>
                                    </label>
                                </div>
                                @if($faqStatus === 'draft' && $latestVer)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="edit_version_radio" id="editModeUpdateDraft" value="update_draft"
                                               {{ !($shouldCreateNewVerByDefault ?? true) ? 'checked' : '' }}
                                               onchange="switchEditVersionMode('update_draft', '{{ $faq->id }}', '{{ $latestVer->id }}')">
                                        <label class="form-check-label fs-13 fw-semibold text-dark" for="editModeUpdateDraft">
                                            Perbarui Draft Saat Ini: <span class="badge bg-secondary">{{ $latestVer->id }}</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Pertanyaan FAQ * -->
                    <div class="mb-4">
                        <label for="inputPertanyaan" class="form-label-custom form-label-required">Pertanyaan FAQ</label>
                        <textarea
                            id="inputPertanyaan"
                            name="pertanyaan"
                            rows="2"
                            class="form-control rounded-3 fs-14"
                            placeholder="Tulis pertanyaan yang sering diajukan mahasiswa..."
                            required>{{ old('pertanyaan', $isEditing ? $faq->pertanyaan : ($selectedExistingFaq?->pertanyaan ?? '')) }}</textarea>

                        <!-- Peringatan Duplikat Identik 100% (Active Publish / Menunggu Validasi) -->
                        <div id="exactDuplicateAlert" class="alert alert-danger rounded-3 p-3 mt-2 fs-13 d-none border-danger">
                            <div class="d-flex align-items-start gap-2.5">
                                <i class="bi bi-Octagon-fill bi-exclamation-octagon-fill text-danger fs-5 mt-0.5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-danger mb-1" id="exactDuplicateTitle">Peringatan: Pertanyaan Duplikat 100% Terdeteksi!</div>
                                    <div id="exactDuplicateBody" class="text-dark"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Kemiripan Pertanyaan / Makna Serupa (Normalisasi + Similarity Score) -->
                        <div id="duplicateWarning" class="alert alert-warning rounded-3 p-3 mt-2 fs-13 d-none">
                            <div class="d-flex align-items-start gap-2.5">
                                <i class="bi bi-exclamation-triangle-fill text-warning fs-5 mt-0.5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark mb-1">Informasi Kemiripan Pertanyaan / Makna Serupa di Database</div>
                                    <div class="fs-12 text-muted mb-2">Ditemukan kandidat FAQ dengan status <strong>Active Publish</strong> atau <strong>Menunggu Validasi</strong> yang memiliki kemiripan makna:</div>
                                    <div id="duplicateList" class="d-flex flex-column gap-1.5"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jawaban * (WYSIWYG) -->
                    <div class="mb-4">
                        <label class="form-label-custom form-label-required">Jawaban</label>
                        <div class="rich-editor-wrapper">
                            <div class="editor-toolbar">
                                <button type="button" class="editor-toolbar-btn" data-cmd="bold" title="Tebal"><strong>B</strong></button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="italic" title="Miring"><em>I</em></button>
                                <div class="editor-toolbar-divider"></div>
                                <button type="button" class="editor-toolbar-btn" data-cmd="createLink" title="Tautan"><i class="bi bi-link-45deg"></i> Link</button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" title="Daftar Bernomor"><i class="bi bi-list-ol"></i> List</button>
                                <button type="button" class="editor-toolbar-btn" data-cmd="insertImage" title="Sisipkan Gambar"><i class="bi bi-image"></i> Gambar</button>
                            </div>
                            <div id="editorJawaban"
                                 class="editor-content"
                                 contenteditable="true"
                                 data-placeholder="Tulis jawaban lengkap di sini...">{!! old('jawaban', $isEditing && $latestVer ? $latestVer->jawaban : '') !!}</div>
                        </div>
                        <input type="hidden" name="jawaban" id="hiddenJawaban">
                    </div>

                    <!-- Kata Kunci (Tag Input) -->
                    <div class="mb-4">
                        <label class="form-label-custom">Kata Kunci</label>
                        <div id="tagContainer" class="tag-container" onclick="document.getElementById('tagInput').focus()">
                            @if($isEditing && $faq->tags)
                                @foreach($faq->tags as $t)
                                    <span class="tag-chip">
                                        {{ $t->nama }}
                                        <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>
                                    </span>
                                @endforeach
                            @endif
                            <input type="text"
                                   id="tagInput"
                                   class="tag-add-placeholder"
                                   placeholder="Tambah kata kunci (tekan Enter)..."
                                   onkeydown="handleTagInput(event)"
                                   autocomplete="off">
                        </div>
                        <div class="fs-11 text-muted mt-1">
                            Tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk menambahkan kata kunci, atau klik dari daftar kata kunci di bawah:
                        </div>
                        @if(isset($tags) && $tags->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @foreach($tags->take(10) as $existingTag)
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill fs-11 py-0 px-2" onclick="addTag('{{ addslashes($existingTag->nama) }}')">
                                        + {{ $existingTag->nama }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Rujukan Sumber Pustaka * -->
                    <div>
                        <label class="form-label-custom form-label-required">Rujukan Sumber Pustaka</label>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#sourceModal">
                                <i class="bi bi-book"></i>
                                <span>Pilih dari Sumber Pustaka</span>
                            </button>

                            <label class="btn-white-outline mb-0" style="cursor: pointer;">
                                <i class="bi bi-upload"></i>
                                <span>Unggah Dokumen (PDF/DOCX)</span>
                                <input type="file" name="sumber_file" id="sumberFileInput" class="d-none" onchange="handleFilePicked(this)">
                            </label>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-8">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-link-45deg"></i></span>
                                    <input type="text"
                                           name="sumber_link"
                                           id="inputSumberLink"
                                           maxlength="45"
                                           value="{{ old('sumber_link', ($isEditing && $latestVer && $latestVer->daftarPustaka && empty($latestVer->daftarPustaka->upload)) ? $latestVer->daftarPustaka->link : '') }}"
                                           placeholder="Atau ketik dokumen/tautan sumber pustaka baru (maks. 45 karakter)..."
                                           class="form-control fs-13">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                @php
                                    $currentVis = ($isEditing && $latestVer && $latestVer->daftarPustaka)
                                        ? strtolower((string) $latestVer->daftarPustaka->type)
                                        : 'public';
                                @endphp
                                <select name="sumber_visibility" id="inputSumberVisibility" class="form-select form-select-sm fs-13 rounded-2">
                                    <option value="public" {{ $currentVis !== 'private' ? 'selected' : '' }}>Tipe: Public (Dilihat Mahasiswa)</option>
                                    <option value="private" {{ $currentVis === 'private' ? 'selected' : '' }}>Tipe: Private (Internal FAQ)</option>
                                </select>
                            </div>
                        </div>

                        <div id="sourceList">
                            @if($isEditing && $latestVer && $latestVer->daftarPustaka)
                                @php
                                    $dp = $latestVer->daftarPustaka;
                                    $dpVis = strtolower((string) $dp->type) === 'private' ? 'Private' : 'Public';
                                @endphp
                                <div class="source-item" id="source-selected">
                                    <div class="d-flex align-items-start">
                                        <span class="source-item-number">1</span>
                                        <div>
                                            <div class="source-item-title">{{ $dp->link ?: 'Dokumen Sumber Pustaka #' . $dp->id }}</div>
                                            <div class="source-item-meta">Tipe: {{ $dpVis }} &middot; {{ !empty($dp->upload) ? 'File Dokumen' : 'Tautan Resmi' }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="source-remove-btn" onclick="clearSelectedSource()">&times;</button>
                                </div>
                            @endif
                        </div>

                        <div id="sourceEmptyMsg" class="fs-12 text-muted mt-2 {{ ($isEditing && $latestVer && $latestVer->daftarPustaka) ? 'd-none' : '' }}">
                            Belum ada sumber pustaka yang dipilih dari database.
                        </div>
                    </div>
                </div>

                <!-- Sticky Bottom Action Bar -->
                <div class="sticky-bottom-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="{{ route('dataentry.dashboard') }}" class="btn-cancel">Batal</a>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="saveDraft()" class="btn-draft">Simpan Draft</button>
                        <button type="button" onclick="submitValidasi()" class="btn-submit-validasi">
                            <i class="bi bi-send-check"></i>
                            <span>Ajukan Validasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Modal Pilih Sumber Pustaka -->
<div class="modal fade" id="sourceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold fs-16">Pilih Sumber Pustaka dari Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="sourceSearchInput" class="form-control form-control-sm rounded-3 mb-3 fs-13" placeholder="Cari dokumen atau tautan sumber...">
                <div id="sourceOptionsList" style="max-height: 300px; overflow-y: auto;">
                    @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                        @foreach($daftarPustaka as $p)
                            @php
                                $pVis = strtolower((string) $p->type) === 'private' ? 'Private' : 'Public';
                                $pIsFile = !empty($p->upload);
                            @endphp
                            <div class="source-option p-3 border rounded-3 mb-2 d-flex align-items-center justify-content-between"
                                 style="cursor: pointer;"
                                 onclick="selectSource('{{ $p->id }}', '{{ addslashes($p->link ?: 'Dokumen #' . $p->id) }}', 'Tipe: {{ $pVis }} · {{ $pIsFile ? 'Dokumen File' : 'Tautan' }}')">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: {{ $pIsFile ? '#EFF6FF' : '#F0FDF4' }}; color: {{ $pIsFile ? '#2563EB' : '#16A34A' }};">
                                        <i class="bi {{ $pIsFile ? 'bi-file-earmark-pdf' : 'bi-link-45deg' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark">{{ $p->link ?: 'Dokumen ID #' . $p->id }}</div>
                                        <div class="fs-11 text-muted">ID: #{{ $p->id }} &middot; Tipe: {{ $pVis }} ({{ $pVis === 'Public' ? 'Tampil di Mahasiswa' : 'Internal FAQ' }})</div>
                                    </div>
                                </div>
                                <span class="badge {{ $pVis === 'Private' ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : 'bg-success-subtle text-success-emphasis border-success-subtle' }} border fs-11">{{ $pVis }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted fs-13">
                            Belum ada data di tabel Sumber Pustaka. Anda dapat menambahkannya di menu <a href="{{ route('dataentry.sumberpustaka') }}">Sumber Pustaka</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unggah File FAQ (Excel / CSV / PDF) -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="excelUploadForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Import File FAQ (Excel / CSV / PDF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik & lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" id="modalFileInput" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="handleModalFileChosen(this)">
                        </label>
                        <div id="selectedUploadFileName" class="fs-12 text-success fw-semibold mt-2 d-none"></div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Format Kolom Excel / CSV:</span>
                            <a href="{{ route('dataentry.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong> &middot; Kolom D: <strong>Sumber Pustaka</strong> &middot; Kolom E: <strong>Bagian Sumber</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">
                        <i class="bi bi-upload"></i> Unggah & Tinjau Pratinjau
                    </button>
                </div>
            </form>
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



    // WYSIWYG Toolbar
    document.querySelectorAll('.editor-toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const cmd = this.getAttribute('data-cmd');
            document.getElementById('editorJawaban').focus();
            if (cmd === 'createLink') {
                const url = prompt('Masukkan tautan URL:');
                if (url) document.execCommand(cmd, false, url);
            } else if (cmd === 'insertImage') {
                const imgUrl = prompt('Masukkan URL gambar:');
                if (imgUrl) document.execCommand('insertImage', false, imgUrl);
            } else {
                document.execCommand(cmd, false, null);
            }
        });
    });

    // Tag Input
    function handleTagInput(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const input = document.getElementById('tagInput');
            const val = input.value.replace(',', '').trim();
            if (val) {
                addTag(val);
                input.value = '';
            }
        }
    }

    function addTag(text) {
        const container = document.getElementById('tagContainer');
        const input = document.getElementById('tagInput');
        const existing = Array.from(container.querySelectorAll('.tag-chip')).map(c => c.firstChild.textContent.trim().toLowerCase());
        if (existing.includes(text.trim().toLowerCase())) return;

        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `${escHtml(text)} <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>`;
        container.insertBefore(chip, input);
    }

    function removeTag(btn) {
        btn.closest('.tag-chip').remove();
    }

    function syncTags() {
        const chips = document.querySelectorAll('#tagContainer .tag-chip');
        const tags = [];
        chips.forEach(chip => {
            const clone = chip.cloneNode(true);
            const rm = clone.querySelector('.tag-remove');
            if (rm) rm.remove();
            const t = clone.textContent.trim();
            if (t) tags.push(t);
        });
        document.getElementById('hiddenTags').value = tags.join(',');
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function selectSource(pustakaId, title, meta) {
        document.getElementById('hiddenPustakaId').value = pustakaId;
        document.getElementById('sourceList').innerHTML = `
            <div class="source-item" id="source-selected">
                <div class="d-flex align-items-start">
                    <span class="source-item-number">1</span>
                    <div>
                        <div class="source-item-title">${escHtml(title)}</div>
                        <div class="source-item-meta">${escHtml(meta)}</div>
                    </div>
                </div>
                <button type="button" class="source-remove-btn" onclick="clearSelectedSource()">&times;</button>
            </div>
        `;
        document.getElementById('sourceEmptyMsg').classList.add('d-none');
        bootstrap.Modal.getInstance(document.getElementById('sourceModal'))?.hide();
    }

    function clearSelectedSource() {
        document.getElementById('hiddenPustakaId').value = '';
        document.getElementById('sourceList').innerHTML = '';
        document.getElementById('sourceEmptyMsg').classList.remove('d-none');
    }

    function handleFilePicked(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('hiddenPustakaId').value = '';
            document.getElementById('sourceList').innerHTML = `
                <div class="source-item" id="source-selected">
                    <div class="d-flex align-items-start">
                        <span class="source-item-number">1</span>
                        <div>
                            <div class="source-item-title">${escHtml(file.name)}</div>
                            <div class="source-item-meta">Dokumen Unggahan Baru (${(file.size / 1024).toFixed(1)} KB)</div>
                        </div>
                    </div>
                    <button type="button" class="source-remove-btn" onclick="clearSelectedSource()">&times;</button>
                </div>
            `;
            document.getElementById('sourceEmptyMsg').classList.add('d-none');
        }
    }

    function handleModalFileChosen(input) {
        if (input.files && input.files[0]) {
            const label = document.getElementById('selectedUploadFileName');
            label.textContent = 'File terpilih: ' + input.files[0].name;
            label.classList.remove('d-none');
            Swal.fire({
                title: 'Membaca & Mengonversi File...',
                text: 'Mohon tunggu, sedang mengekstrak data FAQ untuk pratinjau.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            document.getElementById('excelUploadForm').submit();
        }
    }

    // Live Check Duplikat Identik (100%) & Kemiripan Makna (Similarity Score)
    let dupTimeout = null;
    const currentEditingFaqId = '{{ $isEditing ? $faq->id : "" }}';

    document.getElementById('inputPertanyaan')?.addEventListener('input', function () {
        clearTimeout(dupTimeout);
        const q = this.value.trim();
        const exactBox = document.getElementById('exactDuplicateAlert');
        const exactBody = document.getElementById('exactDuplicateBody');
        const warnBox = document.getElementById('duplicateWarning');
        const listBox = document.getElementById('duplicateList');

        if (q.length < 3) {
            exactBox?.classList.add('d-none');
            warnBox?.classList.add('d-none');
            return;
        }

        dupTimeout = setTimeout(() => {
            const url = `{{ route('dataentry.faq.checkDuplicate') }}?q=${encodeURIComponent(q)}${currentEditingFaqId ? '&exclude_id=' + encodeURIComponent(currentEditingFaqId) : ''}`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    // 1. Jika terdeteksi Duplikat 100% (Active Publish atau Menunggu Validasi)
                    if (data.has_exact_match && data.exact_match) {
                        const em = data.exact_match;
                        const isPublish = em.status_label === 'Active Publish';
                        const badgeClass = isPublish
                            ? 'bg-success text-white'
                            : 'bg-primary text-white';

                        exactBody.innerHTML = `
                            <div class="mb-1">
                                Pertanyaan yang Anda ketik memiliki kemiripan <strong>100%</strong> dengan FAQ <strong>${escHtml(em.id)}</strong> yang saat ini berada dalam kondisi:
                                <span class="badge ${badgeClass} ms-1 px-2 py-1">${escHtml(em.status_label)}</span>
                            </div>
                            <div class="p-2 bg-white rounded-2 border fs-12 text-secondary mt-1.5">
                                <strong>${escHtml(em.id)} (${escHtml(em.version_id || em.id + '-V1')}):</strong> "${escHtml(em.pertanyaan)}"
                            </div>
                        `;
                        exactBox?.classList.remove('d-none');
                    } else {
                        exactBox?.classList.add('d-none');
                    }

                    // 2. Kandidat FAQ yang memiliki kemiripan teks / makna serupa
                    const candidates = (data.candidates || []).filter(c => !c.is_exact);
                    if (candidates.length > 0) {
                        listBox.innerHTML = candidates.map(d => {
                            const stBadge = d.status_label === 'Active Publish'
                                ? '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Active Publish</span>'
                                : '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Menunggu Validasi</span>';
                            const simBadge = d.similarity >= 70
                                ? `<span class="badge bg-danger text-white">${d.similarity}% Mirip</span>`
                                : `<span class="badge bg-warning text-dark">${d.similarity}% Mirip</span>`;

                            return `
                                <div class="p-2 bg-white rounded-2 border d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <span class="fw-bold text-dark">${escHtml(d.id)}</span>:
                                        <span class="text-dark">${escHtml(d.pertanyaan)}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        ${simBadge}
                                        ${stBadge}
                                    </div>
                                </div>
                            `;
                        }).join('');
                        warnBox?.classList.remove('d-none');
                    } else {
                        warnBox?.classList.add('d-none');
                    }
                }).catch(() => {});
        }, 350);
    });

    const defaultNewFaqId = '{{ $newFaqId ?? "FAQ-0001" }}';

    function switchFaqCreationMode(mode) {
        const hiddenMode = document.getElementById('hiddenModeFaq');
        const selectorBox = document.getElementById('existingFaqSelectorBox');
        const pertanyaanInput = document.getElementById('inputPertanyaan');
        const metaIdEl = document.getElementById('metaHeaderIdText');

        if (hiddenMode) hiddenMode.value = mode;

        if (mode === 'existing') {
            selectorBox?.classList.remove('d-none');
            const sel = document.getElementById('existingFaqSelect');
            if (sel && sel.value) {
                handleExistingFaqSelected(sel);
            }
        } else {
            selectorBox?.classList.add('d-none');
            document.getElementById('hiddenFaqId').value = defaultNewFaqId;
            if (metaIdEl) {
                metaIdEl.innerHTML = `${defaultNewFaqId} &middot; Versi 1 <span class="badge bg-light text-dark border ms-1 fw-semibold" id="metaVersionIdBadge">${defaultNewFaqId}-V1</span>`;
            }
            if (pertanyaanInput) {
                pertanyaanInput.readOnly = false;
            }
        }
    }

    function handleExistingFaqSelected(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        const faqId = opt.value;
        const pertanyaan = opt.getAttribute('data-pertanyaan') || '';
        const nextVerId = opt.getAttribute('data-next-version-id') || (faqId + '-V2');
        const nextVerNum = opt.getAttribute('data-next-version-num') || '2';
        const tagsStr = opt.getAttribute('data-tags') || '';

        document.getElementById('hiddenFaqId').value = faqId;
        const metaIdEl = document.getElementById('metaHeaderIdText');
        if (metaIdEl) {
            metaIdEl.innerHTML = `${faqId} &middot; Versi ${nextVerNum} <span class="badge bg-light text-dark border ms-1 fw-semibold" id="metaVersionIdBadge">${nextVerId}</span>`;
        }

        const pertanyaanInput = document.getElementById('inputPertanyaan');
        if (pertanyaanInput) {
            pertanyaanInput.value = pertanyaan;
        }

        if (tagsStr) {
            tagsStr.split(',').map(t => t.trim()).filter(Boolean).forEach(t => addTag(t));
        }
    }

    function switchEditVersionMode(actionType, faqId, versionId) {
        const hiddenVerAction = document.getElementById('hiddenVersionAction');
        if (hiddenVerAction) hiddenVerAction.value = actionType;

        const verNum = versionId.replace(/^.*-V/i, '') || '1';
        const metaIdEl = document.getElementById('metaHeaderIdText');
        if (metaIdEl) {
            metaIdEl.innerHTML = `${faqId} &middot; Versi ${verNum} <span class="badge bg-light text-dark border ms-1 fw-semibold" id="metaVersionIdBadge">${versionId}</span>`;
        }
    }

    function prepareAndSubmit(statusValue) {
        const modeFaq = document.getElementById('hiddenModeFaq')?.value;
        if (modeFaq === 'existing') {
            const sel = document.getElementById('existingFaqSelect');
            if (!sel || !sel.value) {
                Swal.fire({ icon: 'warning', title: 'Pilih FAQ Terlebih Dahulu', text: 'Silakan pilih FAQ yang ingin ditambahkan versi jawaban barunya.', confirmButtonColor: '#85172C' });
                return;
            }
        }

        const q = document.getElementById('inputPertanyaan').value.trim();
        const ansHtml = document.getElementById('editorJawaban').innerHTML.trim();
        const ansText = document.getElementById('editorJawaban').textContent.trim();

        if (!q) {
            Swal.fire({ icon: 'warning', title: 'Pertanyaan Kosong', text: 'Silakan isi pertanyaan FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }
        if (!ansText) {
            Swal.fire({ icon: 'warning', title: 'Jawaban Kosong', text: 'Silakan tulis jawaban FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }

        document.getElementById('hiddenJawaban').value = ansHtml;
        document.getElementById('formStatus').value = statusValue;
        syncTags();
        document.getElementById('faqForm').submit();
    }

    function saveDraft() {
        prepareAndSubmit('draft');
    }

    function submitValidasi() {
        Swal.fire({
            title: 'Ajukan ke Validator?',
            text: 'FAQ ini akan dikirim ke Validator untuk diperiksa sebelum diterbitkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Ajukan Validasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) prepareAndSubmit('submitted');
        });
    }
</script>
</body>
</html>

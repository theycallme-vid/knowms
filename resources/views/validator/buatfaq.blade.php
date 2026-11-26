<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($isEdit) && $isEdit ? 'Ubah FAQ - ' . $faq->id : 'Buat FAQ' }} - Ruang Validator | KMS</title>
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

    @include('partials.milestone-modal-helper')
</head>
<body>
@php
    $valAuthUser = auth()->user();
    if ($valAuthUser) {
        $valAuthUser->loadMissing(['role', 'direktorat']);
    }
    $isUnitVal    = $valAuthUser ? $valAuthUser->isValidatorUnit() : false;
    $valPrefix    = $isUnitVal ? 'validator.unit.' : 'validator.';
    $valUnitLabel = $valAuthUser ? $valAuthUser->getUnitName() : 'Direktorat Akademik';
@endphp

<div class="d-flex">
    <!-- Sidebar -->
    @include('partials.validator-sidebar', ['activeMenu' => 'buatfaq'])

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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route($valPrefix . 'pengaturanAkun') }}">
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

            <!-- Page Title & Upload Excel/PDF Button -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">{{ isset($isEdit) && $isEdit ? 'Edit FAQ' : 'Buat FAQ' }}</h1>
                    <p class="text-muted fs-14 mb-0">Susun jawaban yang jelas dan lengkapi dengan sumber pustaka resmi</p>
                </div>
                <div>
                    <button type="button" class="btn-outline-maroon text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i><span>Unggah File</span>
                    </button>
                </div>
            </div>

            @php
                $isEditing = isset($isEdit) && $isEdit;
                $latestVer = $isEditing ? $faq->latest_version : null;
                $faqStatus = $latestVer ? strtolower($latestVer->status) : 'draf';
                $formAction = $isEditing ? route($valPrefix . 'faq.update', $faq->id) : route($valPrefix . 'buatfaq.store');
                $defaultMode = (!$isEditing && isset($selectedExistingFaq) && $selectedExistingFaq) ? 'existing' : 'new';

                if ($isEditing) {
                    $shouldCreateNewVerByDefault = ($forceNewVersion ?? false) || in_array($faqStatus, ['diterima', 'terbit', 'approved', 'active'], true);
                    $displayFaqId = $faq->id;
                    $displayVersionId = $nextVersionId ?? $faq->generateNextVersionId();
                    $displayVerNum = (int) preg_replace('/.*-V/i', '', $displayVersionId) ?: 1;
                } elseif ($defaultMode === 'existing' && $selectedExistingFaq) {
                    $displayFaqId = $selectedExistingFaq->id;
                    $displayVersionId = $selectedExistingFaq->generateNextVersionId();
                    $displayVerNum = (int) preg_replace('/.*-V/i', '', $displayVersionId) ?: 1;
                } else {
                    $displayFaqId = $newFaqId ?? 'FAQ-0001';
                    $displayVersionId = $displayFaqId . '-V1';
                    $displayVerNum = 1;
                }
            @endphp

            @if(session('error'))
                <div class="alert alert-danger rounded-4 border-danger-subtle d-flex align-items-start gap-3 mb-4">
                    <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-1"></i>
                    <div>
                        <div class="fw-bold fs-12 text-uppercase mb-1">Peringatan Validasi</div>
                        <div class="fs-13 text-dark">{{ session('error') }}</div>
                    </div>
                </div>
            @endif

            @if($isEditing && in_array($faqStatus, ['ditolak', 'rejected', 'draf', 'draft'], true) && $latestVer && $latestVer->catatan && !str_starts_with(trim((string) $latestVer->catatan), '[PUBLISH_SCHEDULE|'))
                <div class="alert alert-danger rounded-4 border-danger-subtle d-flex align-items-start gap-3 mb-4">
                    <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-1"></i>
                    <div>
                        <div class="fw-bold fs-12 text-uppercase mb-1">
                            {{ ($latestVer && in_array($faqStatus, ['ditolak', 'rejected'], true)) ? $latestVer->getRejectionBadgeLabel($faq->unit_name) : 'Catatan Revisi dari Validator' }}
                        </div>
                        <div class="fs-13 text-dark">{{ $latestVer->catatan }}</div>
                        <div class="fs-11 text-danger mt-1">Perbaikan akan disimpan tetap pada <strong>Versi {{ $displayVerNum }} ({{ $displayVersionId }})</strong> tanpa menambah nomor versi baru.</div>
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
                                {{ $displayFaqId }} &middot; Versi {{ $displayVerNum }} <span class="badge bg-light text-dark border ms-1 fw-semibold" id="metaVersionIdBadge"></span>
                            </div>
                        </div>
                        <span class="badge-status {{ in_array($faqStatus, ['ditolak', 'rejected'], true) ? 'badge-status-revisi' : 'badge-status-draft' }}">
                            {{ in_array($faqStatus, ['ditolak', 'rejected'], true) ? 'Perlu Revisi' : 'Draf' }}
                        </span>
                    </div>

                    @if(!$isEditing)
                        <!-- Pilihan Mode: Desain Kartu Ramah Pengguna -->
                        <div class="mb-4">
                            <label class="form-label-custom mb-1">Jenis FAQ yang Ingin Dibuat</label>
                            <div class="fs-12 text-muted mb-2">Pilih apakah Anda ingin menulis pertanyaan baru atau menambah alternatif jawaban dari FAQ yang sudah ada.</div>
                            <div class="faq-mode-grid">
                                <label class="faq-mode-card {{ $defaultMode === 'new' ? 'is-selected' : '' }}" id="modeCardNew" for="modeFaqNew">
                                    <input class="form-check-input" type="radio" name="mode_faq_radio" id="modeFaqNew" value="new"
                                           {{ $defaultMode === 'new' ? 'checked' : '' }} onchange="switchFaqCreationMode('new')">
                                    <div>
                                        <div class="fw-bold fs-13 text-dark">Pertanyaan FAQ Baru</div>
                                        <div class="fs-12 text-muted mt-0.5">Untuk topik pertanyaan yang belum pernah dibuat sebelumnya.</div>
                                    </div>
                                </label>
                                <label class="faq-mode-card {{ $defaultMode === 'existing' ? 'is-selected' : '' }}" id="modeCardExisting" for="modeFaqExisting">
                                    <input class="form-check-input" type="radio" name="mode_faq_radio" id="modeFaqExisting" value="existing"
                                           {{ $defaultMode === 'existing' ? 'checked' : '' }} onchange="switchFaqCreationMode('existing')">
                                    <div>
                                        <div class="fw-bold fs-13 text-dark">Tambah Versi Jawaban Baru</div>
                                        <div class="fs-12 text-muted mt-0.5">Menambah pembaruan jawaban pada FAQ yang sudah ada.</div>
                                    </div>
                                </label>
                            </div>

                            <div id="existingFaqSelectorBox" class="{{ $defaultMode === 'existing' ? '' : 'd-none' }} mt-3 pt-3 border-top">
                                <label class="fs-12 fw-semibold text-secondary mb-1">Pilih FAQ yang ingin dibuatkan versi jawaban barunya:</label>
                                <select name="existing_faq_id" id="existingFaqSelect" class="form-select fs-13 rounded-3" onchange="handleExistingFaqSelected(this)">
                                    <option value="">-- Pilih Kode / Pertanyaan FAQ --</option>
                                    @if(isset($allFaqs))
                                        @foreach($allFaqs as $fItem)
                                            @php
                                                $nextVId = $fItem->generateNextVersionId();
                                                $nextVNum = (int) preg_replace('/.*-V/i', '', $nextVId) ?: 2;
                                                $fTags = $fItem->tags->pluck('nama')->implode(',');
                                                $fLatestSt = strtolower((string) ($fItem->latest_version?->status ?? 'draf'));
                                                $canAddNewVer = in_array($fLatestSt, ['diterima', 'terbit', 'approved', 'active'], true);
                                                $optionSuffix = match(true) {
                                                    \App\Models\FaqVersion::isWaitingValidation($fLatestSt)        => '(Sedang Menunggu Validasi)',
                                                    in_array($fLatestSt, ['ditolak', 'rejected'], true)            => '(Status Ditolak — Silakan Revisi)',
                                                    in_array($fLatestSt, ['draf', 'draft'], true)                  => '(Status Draf — Belum Diterima/Terbit)',
                                                    default                                                        => '(Versi ' . $nextVNum . ')',
                                                };
                                            @endphp
                                            <option value="{{ $fItem->id }}"
                                                    data-pertanyaan="{{ e($fItem->pertanyaan) }}"
                                                    data-next-version-id="{{ $nextVId }}"
                                                    data-next-version-num="{{ $nextVNum }}"
                                                    data-tags="{{ e($fTags) }}"
                                                    {{ !$canAddNewVer ? 'disabled' : '' }}
                                                    {{ (isset($selectedExistingFaq) && $selectedExistingFaq && $selectedExistingFaq->id === $fItem->id && $canAddNewVer) ? 'selected' : '' }}>
                                                {{ $fItem->id }} — {{ \Illuminate\Support\Str::limit($fItem->pertanyaan, 75) }} {{ $optionSuffix }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
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

                        <!-- Peringatan Duplikat Persis dengan Draft (Pertanyaan + Jawaban Sama Persis -> Diblokir) -->
                        <div id="exactDraftDuplicateAlert" class="alert alert-danger rounded-3 p-3 mt-2 fs-13 d-none border-danger shadow-sm">
                            <div class="d-flex align-items-start gap-2.5">
                                <i class="bi bi-x-octagon-fill text-danger fs-5 mt-0.5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-danger mb-1">Tidak Dapat Ditambahkan: Pertanyaan & Jawaban Sama Persis dengan Draft!</div>
                                    <div id="exactDraftDuplicateBody" class="text-dark"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Peringatan Duplikat Identik 100% (Active Publish / Menunggu Validasi / Draft) -->
                        <div id="exactDuplicateAlert" class="alert alert-danger rounded-3 p-3 mt-2 fs-13 d-none border-danger">
                            <div class="d-flex align-items-start gap-2.5">
                                <i class="bi bi-exclamation-octagon-fill text-danger fs-5 mt-0.5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-danger mb-1" id="exactDuplicateTitle">Peringatan: Pertanyaan Duplikat 100% Terdeteksi!</div>
                                    <div id="exactDuplicateBody" class="text-dark"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Kemiripan Pertanyaan / Makna Serupa (Normalisasi + Skor Kemiripan) -->
                        <div id="duplicateWarning" class="alert alert-warning rounded-3 p-3 mt-2 fs-13 d-none">
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark mb-1">Analisis Kemiripan Pertanyaan</div>
                                    <div class="fs-12 text-muted mb-2">Ditemukan kandidat FAQ di database yang memiliki kemiripan makna dengan pertanyaan yang Anda masukkan:</div>
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
                                <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" title="Daftar"><i class="bi bi-list-ol"></i> List</button>
                            </div>
                            <div id="editorJawaban"
                                 class="editor-content"
                                 contenteditable="true"
                                 data-placeholder="Tulis jawaban lengkap di sini...">{!! old('jawaban', $isEditing && $latestVer ? $latestVer->jawaban : '') !!}</div>
                        </div>
                        <input type="hidden" name="jawaban" id="hiddenJawaban">
                    </div>

                    <!-- Kata Kunci (Satu Kolom Pencarian + Tambah Kata Kunci & Daftar Scrollable) -->
                    <div class="mb-4">
                        <label class="form-label-custom mb-1">Kata Kunci</label>
                        <div class="fs-12 text-muted mb-2">Cari kata kunci yang tersedia pada daftar di bawah, atau ketik kata kunci baru lalu klik <strong>Tambah</strong>.</div>

                        <!-- Kotak Kata Kunci Terpilih + Kolom Pencarian -->
                        <div class="tag-input-box-wrap">
                            <div id="tagContainer" class="tag-container-clean" onclick="document.getElementById('tagInput').focus()">
                                @if($isEditing && $faq->tags)
                                    @foreach($faq->tags as $t)
                                        <span class="tag-chip">
                                            {{ $t->nama }}
                                            <button type="button" class="tag-remove" onclick="removeTag(this)" title="Hapus kata kunci">&times;</button>
                                        </span>
                                    @endforeach
                                @endif
                                <input type="text"
                                       id="tagInput"
                                       class="tag-add-placeholder"
                                       placeholder="Cari atau ketik kata kunci baru..."
                                       oninput="filterTagSuggestions(this.value)"
                                       onkeydown="handleTagInput(event)"
                                       autocomplete="off">
                            </div>
                            <button type="button" class="btn-tag-quick-add" onclick="addTagFromInput()">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </div>

                        <!-- Daftar Pilihan Kata Kunci yang Tersaring Otomatis & Dapat Di-scroll -->
                        @if(isset($tags) && $tags->isNotEmpty())
                            <div class="tag-library-box">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <span class="fs-12 fw-semibold text-dark">
                                        <i class="bi bi-tags me-1" style="color: var(--brand-maroon);"></i>Daftar kata kunci tersedia (klik untuk memilih):
                                    </span>
                                </div>
                                <div class="tag-suggestions-scroll" id="tagSuggestionsGrid">
                                    @foreach($tags as $existingTag)
                                        <button type="button"
                                                class="tag-suggestion-pill"
                                                data-tag-name="{{ $existingTag->nama }}"
                                                onclick="toggleSuggestionTag({{ \Illuminate\Support\Js::from($existingTag->nama) }})">
                                            <i class="bi bi-plus"></i>
                                            <span>{{ $existingTag->nama }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                <div id="tagSuggestionEmpty" class="fs-12 text-muted text-center py-2 d-none">
                                    Kata kunci belum terdaftar. Tekan <strong>Enter</strong> atau klik <strong>Tambah</strong> untuk menambahkan kata kunci baru.
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Rujukan Sumber Pustaka (Opsional) -->
                    <div>
                        <label class="form-label-custom">Rujukan Sumber Pustaka <span class="text-muted fw-normal fs-12">(Opsional)</span></label>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <button type="button" class="btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#sourceModal">
                                <i class="bi bi-book"></i>
                                <span>Pilih dari Sumber Pustaka</span>
                            </button>
                        </div>

                        <div id="sourceList">
                            @if($isEditing && $latestVer && $latestVer->daftarPustaka)
                                @php
                                    $dp = $latestVer->daftarPustaka;
                                    $dpJudul = $dp->judul_tampil;
                                @endphp
                                <div class="source-item" id="source-selected">
                                    <div class="d-flex align-items-start">
                                        <span class="source-item-number">1</span>
                                        <div>
                                            <div class="source-item-title">{{ $dpJudul }}</div>
                                            <div class="source-item-meta">{{ !empty($dp->upload) ? 'Berkas Dokumen' : 'Tautan Resmi' }}</div>
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
                    <a href="{{ route($valPrefix . 'daftarFAQ') }}" class="btn-cancel">Batal</a>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="submitValidasi()" class="btn-submit-validasi">
                            <i class="bi bi-send-check"></i>
                            <span>{{ $isUnitVal ? 'Simpan & Teruskan ke Admik' : 'Simpan & Siap Terbit' }}</span>
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
                <input type="text" id="sourceSearchInput" class="form-control form-control-sm rounded-3 mb-3 fs-13" placeholder="Cari judul sumber pustaka...">
                <div id="sourceOptionsList" style="max-height: 300px; overflow-y: auto;">
                    @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                        @foreach($daftarPustaka as $p)
                            @php
                                $pIsFile = !empty($p->upload);
                                $pJudul = $p->judul_tampil;
                            @endphp
                            <div class="source-option p-3 border rounded-3 mb-2 d-flex align-items-center justify-content-between"
                                 style="cursor: pointer;"
                                 onclick="selectSource('{{ $p->id }}', '{{ addslashes($pJudul) }}', '{{ $pIsFile ? 'Berkas Dokumen' : 'Tautan Rujukan' }}')">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: {{ $pIsFile ? '#EFF6FF' : '#F0FDF4' }}; color: {{ $pIsFile ? '#2563EB' : '#16A34A' }};">
                                        <i class="bi {{ $pIsFile ? 'bi-file-earmark-pdf' : 'bi-link-45deg' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark">{{ $pJudul }}</div>
                                        <div class="fs-11 text-muted">ID: {{ $p->id }} &middot; {{ $pIsFile ? 'Berkas Dokumen' : 'Tautan Rujukan' }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border fs-11">{{ $pIsFile ? 'Dokumen' : 'Tautan' }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted fs-13">
                            Belum ada data di tabel Sumber Pustaka. Anda dapat menambahkannya di menu <a href="{{ route($valPrefix . 'sumberpustaka') }}">Sumber Pustaka</a>.
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
            <form action="{{ route($valPrefix . 'uploadExcel') }}" method="POST" enctype="multipart/form-data" id="excelUploadForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16">Unggah File FAQ</h5>
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
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Contoh Template Unggah File:</span>
                            <a href="{{ route($valPrefix . 'template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong></div>
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

    // Tag Input & Quick Library Picker
    function getSelectedTagsNormalized() {
        return Array.from(document.querySelectorAll('#tagContainer .tag-chip')).map(chip => {
            const clone = chip.cloneNode(true);
            const rm = clone.querySelector('.tag-remove');
            if (rm) rm.remove();
            return clone.textContent.trim().toLowerCase();
        }).filter(Boolean);
    }

    function syncTagUiState() {
        const selected = new Set(getSelectedTagsNormalized());
        document.querySelectorAll('#tagSuggestionsGrid .tag-suggestion-pill').forEach(btn => {
            const name = (btn.getAttribute('data-tag-name') || '').trim().toLowerCase();
            const isSelected = selected.has(name);
            btn.classList.toggle('is-selected', isSelected);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isSelected ? 'bi bi-check-lg' : 'bi bi-plus';
            }
        });
    }

    function handleTagInput(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTagFromInput();
        }
    }

    function addTagFromInput() {
        const input = document.getElementById('tagInput');
        if (!input) return;
        const val = input.value.replace(/,/g, '').trim();
        if (val) {
            addTag(val);
            input.value = '';
            filterTagSuggestions('');
        }
        input.focus();
    }

    function addTag(text) {
        const cleanText = String(text || '').trim();
        if (!cleanText) return;
        const container = document.getElementById('tagContainer');
        const input = document.getElementById('tagInput');
        const existing = getSelectedTagsNormalized();
        if (existing.includes(cleanText.toLowerCase())) {
            syncTagUiState();
            return;
        }

        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `${escHtml(cleanText)} <button type="button" class="tag-remove" onclick="removeTag(this)" title="Hapus kata kunci">&times;</button>`;
        container.insertBefore(chip, input);
        syncTagUiState();
    }

    function toggleSuggestionTag(tagName) {
        const cleanLower = String(tagName || '').trim().toLowerCase();
        const chips = Array.from(document.querySelectorAll('#tagContainer .tag-chip'));
        const existingChip = chips.find(chip => {
            const clone = chip.cloneNode(true);
            const rm = clone.querySelector('.tag-remove');
            if (rm) rm.remove();
            return clone.textContent.trim().toLowerCase() === cleanLower;
        });

        if (existingChip) {
            existingChip.remove();
            syncTagUiState();
        } else {
            addTag(tagName);
            const input = document.getElementById('tagInput');
            if (input && input.value.trim() !== '') {
                input.value = '';
                filterTagSuggestions('');
            }
        }
    }

    function filterTagSuggestions(query) {
        const q = String(query || '').trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('#tagSuggestionsGrid .tag-suggestion-pill').forEach(btn => {
            const name = (btn.getAttribute('data-tag-name') || '').toLowerCase();
            const show = !q || name.includes(q);
            btn.classList.toggle('d-none', !show);
            if (show) visibleCount++;
        });
        const emptyEl = document.getElementById('tagSuggestionEmpty');
        if (emptyEl) emptyEl.classList.toggle('d-none', visibleCount > 0);
    }

    function removeTag(btn) {
        btn.closest('.tag-chip').remove();
        syncTagUiState();
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

    document.getElementById('sourceSearchInput')?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('#sourceOptionsList .source-option').forEach(opt => {
            const text = opt.textContent.toLowerCase();
            opt.classList.toggle('d-none', q !== '' && !text.includes(q));
        });
    });

    document.addEventListener('DOMContentLoaded', syncTagUiState);

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

    // Live Check Duplikat Identik (100%), Duplikat Draft (Pertanyaan + Jawaban Sama Persis), & Kemiripan Makna (Similarity Score)
    let dupTimeout = null;
    const currentEditingFaqId = '{{ $isEditing ? $faq->id : "" }}';
    window._hasExactDraftMatch = false;
    window._exactDraftMatchInfo = null;

    function runFaqSimilarityCheck() {
        clearTimeout(dupTimeout);
        const modeFaq = document.getElementById('hiddenModeFaq')?.value;
        const q = (document.getElementById('inputPertanyaan')?.value || '').trim();
        const a = (document.getElementById('editorJawaban')?.textContent || '').trim();

        const exactDraftBox = document.getElementById('exactDraftDuplicateAlert');
        const exactDraftBody = document.getElementById('exactDraftDuplicateBody');
        const exactBox = document.getElementById('exactDuplicateAlert');
        const exactBody = document.getElementById('exactDuplicateBody');
        const warnBox = document.getElementById('duplicateWarning');
        const listBox = document.getElementById('duplicateList');

        // Saat memilih "Tambah Versi Jawaban Baru", pertanyaan FAQ tetap sama sehingga similarity check pertanyaan tidak ditampilkan
        if (modeFaq === 'existing') {
            exactBox?.classList.add('d-none');
            warnBox?.classList.add('d-none');
        }

        if (q.length < 3 || (modeFaq === 'existing' && a.length === 0)) {
            window._hasExactDraftMatch = false;
            window._exactDraftMatchInfo = null;
            exactDraftBox?.classList.add('d-none');
            exactBox?.classList.add('d-none');
            warnBox?.classList.add('d-none');
            return;
        }

        const selectedExistingId = document.getElementById('existingFaqSelect')?.value || '';
        const excludeId = currentEditingFaqId || (modeFaq === 'existing' ? selectedExistingId : '');

        dupTimeout = setTimeout(() => {
            const url = `{{ route($valPrefix . 'faq.checkDuplicate') }}?q=${encodeURIComponent(q)}&a=${encodeURIComponent(a)}${excludeId ? '&exclude_id=' + encodeURIComponent(excludeId) : ''}`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    // 1. Jika Pertanyaan DAN Jawaban sama persis dengan FAQ berstatus Draf -> Tampilkan peringatan & blokir penambahan
                    if (data.has_exact_draft_match && data.exact_draft_match) {
                        const dm = data.exact_draft_match;
                        window._hasExactDraftMatch = true;
                        window._exactDraftMatchInfo = dm;
                        if (exactDraftBody) {
                            exactDraftBody.innerHTML = `
                                <div class="mb-1">
                                    Pertanyaan dan Jawaban yang Anda masukkan <strong>sama persis (100%)</strong> dengan FAQ <strong>${escHtml(dm.id)} (Versi ${escHtml(String(dm.version_id || '').replace(/^.*-V/i, '') || '1')})</strong> yang saat ini masih berstatus <span class="badge bg-secondary text-white">Draf</span>. Data ini tidak dapat dilanjutkan / ditambahkan.
                                </div>
                                <div class="p-2 bg-white rounded-2 border fs-12 text-secondary mt-1.5">
                                    <div><strong>Pertanyaan:</strong> "${escHtml(dm.pertanyaan)}"</div>
                                    <div class="mt-1"><strong>Jawaban:</strong> "${escHtml(dm.jawaban)}"</div>
                                </div>
                            `;
                        }
                        exactDraftBox?.classList.remove('d-none');
                    } else {
                        window._hasExactDraftMatch = false;
                        window._exactDraftMatchInfo = null;
                        exactDraftBox?.classList.add('d-none');
                    }

                    // Pada mode Tambah Versi Jawaban Baru, jangan tampilkan pemeriksaan kemiripan pertanyaan
                    if (modeFaq === 'existing') {
                        exactBox?.classList.add('d-none');
                        warnBox?.classList.add('d-none');
                        return;
                    }

                    // 2. Jika terdeteksi Duplikat Pertanyaan 100%
                    if (data.has_exact_match && data.exact_match && !data.has_exact_draft_match) {
                        const em = data.exact_match;
                        let badgeClass = 'bg-secondary text-white';
                        if (em.status_label === 'Terbit' || em.status_label === 'Diterima' || em.status_label === 'Active Publish' || em.status_label === 'Disetujui') {
                            badgeClass = 'bg-success text-white';
                        } else if (String(em.status_label || '').toLowerCase().startsWith('menunggu validasi')) {
                            badgeClass = 'bg-primary text-white';
                        }

                        exactBody.innerHTML = `
                            <div class="mb-1">
                                Pertanyaan yang Anda ketik memiliki kemiripan <strong>100%</strong> dengan FAQ <strong>${escHtml(em.id)}</strong> (${escHtml(em.unit_nama || 'Direktorat Akademik')}) yang saat ini berada dalam kondisi:
                                <span class="badge ${badgeClass} ms-1 px-2 py-1">${escHtml(em.status_label)}</span>
                            </div>
                            <div class="p-2 bg-white rounded-2 border fs-12 text-secondary mt-1.5">
                                <strong>${escHtml(em.id)} (Versi ${escHtml(String(em.version_id || '').replace(/^.*-V/i, '') || '1')}):</strong> "${escHtml(em.pertanyaan)}"
                            </div>
                        `;
                        exactBox?.classList.remove('d-none');
                    } else {
                        exactBox?.classList.add('d-none');
                    }

                    // 3. Kandidat FAQ yang memiliki kemiripan teks / makna serupa lintas unit
                    const candidates = (data.candidates || data.duplicates || []);
                    if (candidates.length > 0) {
                        listBox.innerHTML = candidates.map(d => {
                            const stLbl = String(d.status_label || 'Draf');
                            let stBadge = '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">' + escHtml(stLbl) + '</span>';
                            if (stLbl === 'Terbit' || stLbl === 'Diterima' || stLbl === 'Active Publish' || stLbl === 'Disetujui') {
                                stBadge = '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">' + escHtml(stLbl) + '</span>';
                            } else if (stLbl.toLowerCase().startsWith('menunggu validasi')) {
                                stBadge = '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">' + escHtml(stLbl) + '</span>';
                            } else if (stLbl === 'Ditolak') {
                                stBadge = '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Ditolak</span>';
                            }

                            const simBadge = d.similarity >= 70
                                ? `<span class="badge bg-danger text-white">${d.similarity}% Mirip</span>`
                                : `<span class="badge bg-warning text-dark">${d.similarity}% Mirip</span>`;

                            return `
                                <div class="p-2 bg-white rounded-2 border d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <span class="fw-bold text-dark">${escHtml(d.id)}</span>
                                        <span class="kms-unit-pill ms-1"><i class="bi bi-building"></i>${escHtml(d.unit_nama || 'Direktorat Akademik')}</span>:
                                        <span class="text-dark ms-1">${escHtml(d.pertanyaan)}</span>
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
        }, 300);
    }

    document.getElementById('inputPertanyaan')?.addEventListener('input', runFaqSimilarityCheck);
    document.getElementById('editorJawaban')?.addEventListener('input', runFaqSimilarityCheck);

    const defaultNewFaqId = '{{ $newFaqId ?? "FAQ-0001" }}';

    function switchFaqCreationMode(mode) {
        const hiddenMode = document.getElementById('hiddenModeFaq');
        const selectorBox = document.getElementById('existingFaqSelectorBox');
        const pertanyaanInput = document.getElementById('inputPertanyaan');
        const metaIdEl = document.getElementById('metaHeaderIdText');

        document.getElementById('modeCardNew')?.classList.toggle('is-selected', mode === 'new');
        document.getElementById('modeCardExisting')?.classList.toggle('is-selected', mode === 'existing');

        if (hiddenMode) hiddenMode.value = mode;

        if (mode === 'existing') {
            selectorBox?.classList.remove('d-none');
            document.getElementById('exactDuplicateAlert')?.classList.add('d-none');
            document.getElementById('duplicateWarning')?.classList.add('d-none');
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
            runFaqSimilarityCheck();
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

        document.getElementById('editCardNewVer')?.classList.toggle('is-selected', actionType === 'new_version');
        document.getElementById('editCardUpdateDraft')?.classList.toggle('is-selected', actionType === 'update_draft');

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

        syncTags();
        const tagsVal = (document.getElementById('hiddenTags')?.value || '').trim();

        if (statusValue === 'submitted' && !tagsVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Peringatan: Kata Kunci wajib dilengkapi terlebih dahulu sebelum mengajukan FAQ ke Validator.',
                confirmButtonColor: '#85172C'
            });
            return;
        }

        const selectedExistingId = document.getElementById('existingFaqSelect')?.value || '';
        const excludeId = currentEditingFaqId || (modeFaq === 'existing' ? selectedExistingId : '');
        const checkUrl = `{{ route($valPrefix . 'faq.checkDuplicate') }}?q=${encodeURIComponent(q)}&a=${encodeURIComponent(ansText)}${excludeId ? '&exclude_id=' + encodeURIComponent(excludeId) : ''}`;

        fetch(checkUrl)
            .then(r => r.json())
            .then(data => {
                if (data.has_exact_draft_match && data.exact_draft_match) {
                    const dm = data.exact_draft_match;
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplikat Draf Terdeteksi',
                        html: `Pertanyaan dan Jawaban sama persis dengan FAQ berstatus <strong>Draf</strong> yang sudah ada (<strong>${escHtml(dm.id)}</strong>).<br><br>Data tidak dapat dilanjutkan atau ditambahkan.`,
                        confirmButtonColor: '#85172C'
                    });
                    return;
                }

                document.getElementById('hiddenJawaban').value = ansHtml;
                document.getElementById('formStatus').value = statusValue;
                document.getElementById('faqForm').submit();
            })
            .catch(() => {
                if (window._hasExactDraftMatch && window._exactDraftMatchInfo) {
                    const dm = window._exactDraftMatchInfo;
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplikat Draf Terdeteksi',
                        html: `Pertanyaan dan Jawaban sama persis dengan FAQ berstatus <strong>Draf</strong> yang sudah ada (<strong>${escHtml(dm.id)}</strong>).<br><br>Data tidak dapat dilanjutkan atau ditambahkan.`,
                        confirmButtonColor: '#85172C'
                    });
                    return;
                }
                document.getElementById('hiddenJawaban').value = ansHtml;
                document.getElementById('formStatus').value = statusValue;
                document.getElementById('faqForm').submit();
            });
    }

    function saveDraft() {
        prepareAndSubmit('draft');
    }

    function submitValidasi() {
        syncTags();
        const tagsVal = (document.getElementById('hiddenTags')?.value || '').trim();

        if (!tagsVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada Kata Kunci',
                text: 'Peringatan: FAQ yang ingin diajukan wajib memiliki Kata Kunci. Silakan lengkapi terlebih dahulu.',
                confirmButtonColor: '#85172C'
            });
            return;
        }

        Swal.fire({
            title: {{ \Illuminate\Support\Js::from($isUnitVal ? 'Simpan & Teruskan ke Validator Admik?' : 'Simpan & Siap Diterbitkan?') }},
            text: {{ \Illuminate\Support\Js::from($isUnitVal ? 'FAQ ini akan disimpan untuk unit Anda dan langsung diteruskan ke antrean Validator Admik.' : 'FAQ ini akan langsung masuk ke halaman Validasi FAQ dengan status Diterima dan tinggal diterbitkan tanpa perlu disetujui lagi.') }},
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: {{ \Illuminate\Support\Js::from($isUnitVal ? 'Ya, Teruskan ke Admik' : 'Ya, Simpan & Siap Terbit') }},
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) prepareAndSubmit('submitted');
        });
    }
</script>
</body>
</html>

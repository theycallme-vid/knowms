<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat FAQ - Ruang Validator | KMS</title>
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
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route($valPrefix . 'daftarFAQ') }}" class="back-link mb-0">
                    <i class="bi bi-chevron-left"></i>
                    <span>Daftar FAQ</span>
                </a>
                <span class="text-muted fs-12">&middot;</span>
                <a href="{{ route($valPrefix . 'buatfaq') }}" class="back-link mb-0">
                    <i class="bi bi-pencil-square"></i>
                    <span>Mode Input Satuan</span>
                </a>
            </div>

            <!-- Header: Judul Buat FAQ + Badge Pratinjau unggahan & Nama File + Unggah File -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="page-title mb-0">Buat FAQ</h1>
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
                    <button type="button" class="btn-outline-maroon text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i><span>Unggah File</span>
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

            @php
                $simService = app(\App\Services\FaqSimilarityService::class);
                $exactDuplicateCount = 0;
                $similarCount = 0;

                foreach ($items as $idx => $it) {
                    if (!array_key_exists('has_exact_match', $it)) {
                        $an = $simService->analyzeQuestion((string) ($it['pertanyaan'] ?? ''), null, 50.0);
                        $items[$idx]['has_exact_match'] = $an['has_exact_match'];
                        $items[$idx]['exact_match'] = $an['exact_match'];
                        $items[$idx]['similar_candidates'] = $an['candidates'];
                        $items[$idx]['max_similarity'] = $an['max_similarity'];
                    }
                    if (!empty($items[$idx]['has_exact_match'])) {
                        $exactDuplicateCount++;
                    } elseif (!empty($items[$idx]['similar_candidates'])) {
                        $similarCount++;
                    }
                }
            @endphp

            @if($exactDuplicateCount > 0 || $similarCount > 0)
                <div class="alert {{ $exactDuplicateCount > 0 ? 'alert-danger border-danger' : 'alert-warning border-warning' }} rounded-4 p-3 mb-3 fs-13">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi {{ $exactDuplicateCount > 0 ? 'bi-exclamation-octagon-fill text-danger' : 'bi-info-circle-fill text-warning' }} fs-5 mt-0.5"></i>
                        <div>
                            <div class="fw-bold text-dark mb-0.5">Hasil Pengecekan Duplikat &amp; Kemiripan File Import</div>
                            <div class="text-secondary fs-12">
                                @if($exactDuplicateCount > 0)
                                    Terdapat <strong class="text-danger">{{ $exactDuplicateCount }} baris pertanyaan</strong> yang terdeteksi <strong>100% sama (duplikat)</strong> dengan FAQ yang sedang berstatus <strong>Terbit</strong> atau <strong>Menunggu Validasi</strong> di database.
                                @endif
                                @if($similarCount > 0)
                                    Ditemukan pula <strong>{{ $similarCount }} baris pertanyaan</strong> yang memiliki kemiripan makna dengan FAQ yang sudah ada.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                                <th style="min-width: 280px;">PERTANYAAN</th>
                                <th style="min-width: 190px;">KATA KUNCI</th>
                                <th style="min-width: 190px;">SUMBER PUSTAKA</th>
                                <th style="width: 130px;">STATUS</th>
                                <th style="width: 180px; text-align: right;">AKSI</th>
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
                                    data-similar-faqs='@json($item['similar_candidates'] ?? [])'
                                    data-exact-match='@json($item['exact_match'] ?? null)'
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
                                        <div class="fs-11 text-muted">{{ $verLabel }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-13 line-clamp-2 faq-pertanyaan">{{ $item['pertanyaan'] }}</div>

                                        <div class="faq-similarity-box mt-1">
                                            @if(!empty($item['has_exact_match']) && !empty($item['exact_match']))
                                                @php
                                                    $em = $item['exact_match'];
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-2 bg-danger-subtle text-danger border border-danger-subtle fs-11 fw-semibold">
                                                    <i class="bi bi-exclamation-octagon-fill"></i>
                                                    <span>Mirip 100% dengan <strong>{{ $em['id'] }}</strong></span>
                                                </div>
                                            @elseif(!empty($item['similar_candidates']))
                                                @php
                                                    $topCand = $item['similar_candidates'][0];
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-2 bg-warning-subtle text-dark border border-warning-subtle fs-11">
                                                    <i class="bi bi-info-circle-fill text-warning"></i>
                                                    <span>Mirip <strong>{{ $topCand['similarity'] }}%</strong> dengan <strong>{{ $topCand['id'] }}</strong></span>
                                                </div>
                                            @endif

                                            @if(!empty($item['in_file_duplicate']))
                                                <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-2 bg-secondary-subtle text-secondary border fs-11 mt-0.5">
                                                    <i class="bi bi-files"></i>
                                                    <span>Duplikat di dalam file yang sama dengan <strong>{{ $item['in_file_duplicate'] }}</strong></span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 faq-tags-container">
                                            @if(!empty($item['tags']))
                                                @foreach($item['tags'] as $tag)
                                                    <span class="keyword-pill-blue">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                <div class="fw-bold text-danger fs-12">Belum diisi</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($item['pustaka_judul']))
                                            <div class="fw-bold text-dark fs-13 line-clamp-1 faq-pustaka-judul">{{ $item['pustaka_judul'] }}</div>
                                        @else
                                            <div class="text-muted fs-12 faq-pustaka-judul">Tidak ada (Opsional)</div>
                                        @endif
                                    </td>
                                    <td class="faq-status-cell">
                                        @php
                                            $exactStatusLabel = $item['exact_match']['status_label'] ?? '';
                                            $isWaitingValPreview = (strtolower($item['status'] ?? '') === 'submitted') || str_starts_with(strtolower((string) $exactStatusLabel), 'menunggu validasi');
                                        @endphp
                                        @if(!empty($item['has_exact_match']) && !empty($item['exact_match']))
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fs-11 d-inline-block mb-1">
                                                Duplikat 100% ({{ $exactStatusLabel ?: 'Terbit' }})
                                            </span>
                                        @endif
                                        @if($isWaitingValPreview)
                                            <span class="badge-status badge-status-validasi">Menunggu Validasi</span>
                                        @elseif($item['status'] === 'ready')
                                            <span class="badge-ready">Siap diajukan</span>
                                        @else
                                            <span class="badge-incomplete">Belum lengkap</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;" class="text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            @if($isWaitingValPreview)
                                                <button type="button" class="btn-action btn-action-edit action-icon-disabled" style="opacity: 0.5; cursor: not-allowed;" title="Terkunci — Sedang Menunggu Validasi" disabled>
                                                    <i class="bi bi-pencil"></i><span>Ubah</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn-action btn-action-edit" onclick="openEditModal('{{ $rowKey }}')">
                                                    <i class="bi bi-pencil"></i><span>Ubah</span>
                                                </button>
                                            @endif
                                            <button type="button" class="btn-table-delete" onclick="deleteRow('{{ $rowKey }}')">
                                                <i class="bi bi-trash3"></i><span>Hapus</span>
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
            <form id="submitPratinjauForm" action="{{ route($valPrefix . 'pratinjau.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="submitActionInput" value="submit">
                <input type="hidden" name="items_json" id="itemsJsonInput">
                <div id="selectedIdsContainer"></div>

                <!-- Sticky Bottom Action Bar -->
                <div class="sticky-bottom-bar d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="selected-pill-badge" id="bottomPillCount">0 dipilih</span>
                        <span class="fs-12 text-muted">
                            {{ $isUnitVal ? 'FAQ yang disimpan akan langsung diteruskan ke antrean Validator Admik.' : 'FAQ yang disimpan akan langsung masuk ke halaman Validasi FAQ (status Diterima) dan siap diterbitkan.' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="submitSelectedForValidation()" class="btn-submit-validasi">
                            <i class="bi bi-send-check"></i>
                            <span id="btnSubmitValidationText">{{ $isUnitVal ? 'Simpan & Teruskan ke Admik (0)' : 'Simpan & Siap Terbit (0)' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Modal Edit Baris Pratinjau FAQ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 960px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFFDFB 0%, #FDF2F4 100%); border-bottom: 1px solid #F3E2E5; padding: 1.05rem 1.4rem;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-12" style="background-color: #85172C; color: #ffffff;" id="modalEditCode">FAQ-0001</span>
                    <span class="fs-12 fw-semibold" style="color: #85172C;" id="modalEditVersion">Versi 1</span>
                    <span class="fs-12 text-muted">&middot;</span>
                    <span class="fw-bold text-dark fs-14" id="modalEditTitle">Ubah Pratinjau FAQ</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size: 0.75rem;"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 76vh; overflow-y: auto;">
                <input type="hidden" id="editRowId">

                <!-- Catatan Kemiripan dengan FAQ Lain -->
                <div class="mb-3" id="editModalSimilarSection">
                    <div class="text-uppercase fw-bold mb-2" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em;">
                        <i class="bi bi-intersect me-1"></i>Catatan Kemiripan dengan FAQ Lain
                    </div>
                    <div id="editModalSimilarWrap"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-6">
                        <label class="text-uppercase fw-bold d-block mb-2" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em;">Pertanyaan FAQ <span class="text-danger">*</span></label>
                        <textarea id="modalEditPertanyaan" rows="4" class="form-control rounded-3 text-dark fw-semibold" style="font-size: 0.9rem; background-color: #FFFDFB; border-color: #EDE7E1;"></textarea>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="text-uppercase fw-bold d-block mb-2" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em;">Jawaban FAQ <span class="text-danger">*</span></label>
                        <textarea id="modalEditJawaban" rows="4" class="form-control rounded-3 text-dark" style="font-size: 0.875rem; border-color: #EDE7E1;"></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-uppercase fw-bold d-block mb-2" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em;">Kata Kunci (Pilih dari Daftar Kata Kunci) <span class="text-danger">*</span></label>
                    <input type="hidden" id="modalEditTags">

                    <!-- Kata kunci yang sudah dipilih -->
                    <div id="modalSelectedTagsBox" class="modal-tag-selected-box">
                        <span class="text-muted fs-12 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-tags"></i>
                            <span>Belum ada kata kunci dipilih — klik kata kunci di bawah untuk menambahkan</span>
                        </span>
                    </div>

                    <!-- Pencarian & Daftar Kata Kunci yang Bisa Di-scroll -->
                    <div class="modal-tag-library-box">
                        <div class="modal-tag-search-wrap">
                            <i class="bi bi-search modal-tag-search-icon"></i>
                            <input type="text"
                                   id="searchModalTagInput"
                                   class="modal-tag-search-input"
                                   placeholder="Cari kata kunci di Daftar Kata Kunci..."
                                   oninput="filterModalTagChoices(this.value)">
                        </div>

                        <div class="modal-tag-library-header">
                            <span class="d-inline-flex align-items-center gap-2">
                                <i class="bi bi-collection"></i>
                                <span>Daftar Kata Kunci Tersedia</span>
                            </span>
                            <span id="modalTagVisibleCount">{{ isset($tags) ? $tags->count() : 0 }} kata kunci</span>
                        </div>

                        <div id="modalTagChoiceContainer" class="modal-tag-choices-scroll">
                            @if(isset($tags) && $tags->isNotEmpty())
                                @foreach($tags as $t)
                                    <button type="button"
                                            class="modal-tag-choice-pill modal-tag-choice"
                                            data-tag-name="{{ $t->nama }}"
                                            onclick="appendExistingTagToModal('{{ addslashes($t->nama) }}')">
                                        <i class="bi bi-plus-lg"></i>
                                        <span>{{ $t->nama }}</span>
                                    </button>
                                @endforeach
                            @else
                                <span class="text-muted fs-12">Belum ada kata kunci terdaftar di Daftar Kata Kunci.</span>
                            @endif
                            <div id="modalTagEmptyNotice" class="w-100 text-center text-muted fs-12 py-3" style="display: none;">
                                <i class="bi bi-search me-1"></i> Kata kunci yang dicari tidak ditemukan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-1">
                    <label class="text-uppercase fw-bold d-block mb-2" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em;">Dokumen / Link Sumber Pustaka <span class="text-muted fw-normal text-lowercase">(opsional)</span></label>
                    <select id="modalEditJudulPustaka" class="form-select rounded-3 text-dark" style="font-size: 0.85rem; border-color: #EDE7E1;">
                        <option value="">-- Tidak ada / Opsional (Pilih jika ada Sumber Pustaka) --</option>
                        @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                            @foreach($daftarPustaka as $dp)
                                <option value="{{ $dp->judul_tampil }}">{{ $dp->judul_tampil }} ({{ ucfirst(strtolower($dp->type)) }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i><span>Batal</span>
                </button>
                <button type="button" class="btn btn-sm btn-maroon px-3 py-2 d-inline-flex align-items-center gap-2" onclick="saveEditedFaq()">
                    <i class="bi bi-check2-circle"></i><span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unggah File Baru -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route($valPrefix . 'uploadExcel') }}" method="POST" enctype="multipart/form-data" id="reuploadExcelForm">
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
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="handleNewExcelUpload(this)">
                        </label>
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
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Unggah & Tinjau</button>
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
    let currentFilter = 'all';

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('d-none');
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
        const baseBtnLabel = {{ \Illuminate\Support\Js::from($isUnitVal ? 'Simpan & Teruskan ke Admik' : 'Simpan & Siap Terbit') }};
        document.getElementById('btnSubmitValidationText').textContent = `${baseBtnLabel} (${selectedCount})`;
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

    function renderPreviewSimilarBox(row) {
        const wrap = document.getElementById('editModalSimilarWrap');
        if (!wrap) return;

        let candidates = [];
        try {
            candidates = JSON.parse(row.getAttribute('data-similar-faqs') || '[]');
        } catch (_) { candidates = []; }

        let exactMatch = null;
        try {
            exactMatch = JSON.parse(row.getAttribute('data-exact-match') || 'null');
        } catch (_) { exactMatch = null; }

        if (exactMatch && exactMatch.id && !candidates.some(c => c.id === exactMatch.id)) {
            candidates = [exactMatch, ...candidates];
        }

        if (!Array.isArray(candidates) || candidates.length === 0) {
            wrap.innerHTML = `
                <div class="rounded-3 px-3 py-2 d-flex align-items-center gap-2" style="background-color:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;font-size:0.78rem;">
                    <i class="bi bi-shield-check fs-6"></i>
                    <span>Tidak ditemukan FAQ lain yang mirip.</span>
                </div>
            `;
            return;
        }

        wrap.innerHTML = `
            <div class="rounded-4 p-3" style="background: linear-gradient(135deg, #FFFDFB 0%, #FFFBEB 100%); border: 1px solid #FDE68A;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2" style="border-bottom: 1px dashed #FCD34D;">
                    <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        <span>Ditemukan ${candidates.length} FAQ yang Memiliki Kemiripan</span>
                    </div>
                    <span class="text-muted" style="font-size: 0.72rem;">Periksa kesesuaian agar tidak terjadi duplikasi</span>
                </div>
                <div class="d-flex flex-column gap-2 pe-1" style="max-height: 205px; overflow-y: auto;">
                    ${candidates.map((sim, idx) => {
                        const pct = Number(sim.similarity || 0);
                        const isHigh = pct >= 70;
                        const accentColor = isHigh ? '#DC2626' : '#D97706';
                        const pillStyle = isHigh
                            ? 'background-color:#FEF2F2;color:#DC2626;border:1px solid #FECACA;'
                            : 'background-color:#FEF3C7;color:#92400E;border:1px solid #FDE68A;';
                        const collapseId = `previewSimAns_${idx}_${String(sim.id || '').replace(/[^a-zA-Z0-9]/g, '')}`;
                        return `
                            <div class="rounded-3 bg-white px-3 py-2.5 shadow-sm" style="border:1px solid #EDE7E1; border-left: 3px solid ${accentColor};">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge rounded-2 fw-bold" style="background-color:#F8F5F1;color:#85172C;border:1px solid #E5DCD3;font-size:0.72rem;padding:0.22rem 0.5rem;">${escHtml(sim.id)}</span>
                                            <span class="badge rounded-pill fw-bold" style="${pillStyle}font-size:0.7rem;padding:0.22rem 0.6rem;">${pct}% Mirip</span>
                                            ${sim.unit_nama ? `<span class="badge rounded-pill bg-light text-secondary border fw-semibold" style="font-size:0.7rem;padding:0.22rem 0.55rem;"><i class="bi bi-building me-1"></i>${escHtml(sim.unit_nama)}</span>` : ''}
                                        </div>
                                        <div class="text-dark fw-semibold" style="font-size:0.85rem;line-height:1.45;">${escHtml(sim.pertanyaan || '')}</div>
                                    </div>
                                    ${sim.jawaban ? `
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-3 flex-shrink-0 d-inline-flex align-items-center gap-2 fw-semibold" style="font-size:0.73rem; border-color:#CBD5E1;" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                                            <i class="bi bi-eye"></i>
                                            <span>Lihat Jawaban</span>
                                        </button>
                                    ` : ''}
                                </div>
                                ${sim.jawaban ? `
                                    <div id="${collapseId}" class="collapse mt-2 pt-2" style="border-top:1px dashed #E2E8F0;">
                                        <div class="rounded-2 p-2.5 text-dark" style="background-color:#F8FAFC; font-size:0.8rem; line-height:1.55;">
                                            <strong style="color:#85172C;">Jawaban ${escHtml(sim.id)}:</strong> ${escHtml(sim.jawaban)}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    // Edit Modal
    function openEditModal(rowId) {
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        const faqCode = row.getAttribute('data-id') || rowId;
        const verLabel = row.getAttribute('data-version') || 'Versi 1';
        const currentPustaka = row.getAttribute('data-pustaka-judul') || '';

        document.getElementById('editRowId').value = rowId;
        const codeEl = document.getElementById('modalEditCode');
        const verEl = document.getElementById('modalEditVersion');
        if (codeEl) codeEl.textContent = faqCode;
        if (verEl) verEl.textContent = verLabel;
        document.getElementById('modalEditTitle').textContent = 'Ubah Pratinjau FAQ';
        renderPreviewSimilarBox(row);
        document.getElementById('modalEditPertanyaan').value = row.getAttribute('data-pertanyaan') || '';
        document.getElementById('modalEditJawaban').value = row.getAttribute('data-jawaban') || '';
        document.getElementById('modalEditTags').value = row.getAttribute('data-tags') || '';

        const searchTagInput = document.getElementById('searchModalTagInput');
        if (searchTagInput) {
            searchTagInput.value = '';
            filterModalTagChoices('');
        }

        const pustakaSelect = document.getElementById('modalEditJudulPustaka');
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

    function filterModalTagChoices(query) {
        const q = (query || '').toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('.modal-tag-choice').forEach(btn => {
            const name = (btn.getAttribute('data-tag-name') || '').toLowerCase();
            const isMatch = (!q || name.includes(q));
            btn.style.display = isMatch ? 'inline-flex' : 'none';
            if (isMatch) visibleCount++;
        });
        const countEl = document.getElementById('modalTagVisibleCount');
        if (countEl) {
            countEl.textContent = `${visibleCount} kata kunci`;
        }
        const emptyEl = document.getElementById('modalTagEmptyNotice');
        if (emptyEl) {
            emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
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

    function removeTagFromModal(tagName) {
        appendExistingTagToModal(tagName, false);
    }

    function highlightModalTagChoices() {
        const input = document.getElementById('modalEditTags');
        if (!input) return;

        const rawTags = input.value
            ? input.value.split(',').map(t => t.trim()).filter(Boolean)
            : [];
        const activeTags = rawTags.map(t => t.toLowerCase());

        const selectedBox = document.getElementById('modalSelectedTagsBox');
        if (selectedBox) {
            if (rawTags.length > 0) {
                selectedBox.innerHTML = rawTags.map(t => `
                    <span class="modal-tag-selected-chip">
                        <span>${escHtml(t)}</span>
                        <button type="button" class="modal-tag-remove-btn" title="Hapus kata kunci" onclick="removeTagFromModal('${escHtml(t).replace(/'/g, "\\'")}')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </span>
                `).join('');
            } else {
                selectedBox.innerHTML = `<span class="text-muted fs-12 d-inline-flex align-items-center gap-2"><i class="bi bi-tags"></i><span>Belum ada kata kunci dipilih — klik kata kunci di bawah untuk menambahkan</span></span>`;
            }
        }

        document.querySelectorAll('.modal-tag-choice').forEach(btn => {
            const name = (btn.getAttribute('data-tag-name') || '').trim();
            const isSelected = activeTags.includes(name.toLowerCase());
            btn.classList.toggle('is-selected', isSelected);
            btn.innerHTML = isSelected
                ? `<i class="bi bi-check-lg"></i><span>${escHtml(name)}</span>`
                : `<i class="bi bi-plus-lg"></i><span>${escHtml(name)}</span>`;
        });
    }

    function saveEditedFaq() {
        const rowId = document.getElementById('editRowId').value;
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        const pertanyaan = document.getElementById('modalEditPertanyaan').value.trim();
        const jawaban = document.getElementById('modalEditJawaban').value.trim();
        const tagsRaw = document.getElementById('modalEditTags').value.trim();
        const pustakaJudul = document.getElementById('modalEditJudulPustaka').value.trim();

        const tagsArr = tagsRaw ? tagsRaw.split(',').map(t => t.trim()).filter(Boolean) : [];
        const isReady = (pertanyaan !== '' && jawaban !== '' && tagsArr.length > 0);
        const newStatus = isReady ? 'ready' : 'incomplete';

        // Update atribut data pada baris DOM
        row.setAttribute('data-pertanyaan', pertanyaan);
        row.setAttribute('data-jawaban', jawaban);
        row.setAttribute('data-tags', tagsArr.join(', '));
        row.setAttribute('data-pustaka-judul', pustakaJudul);
        row.setAttribute('data-status', newStatus);

        row.querySelector('.faq-pertanyaan').textContent = pertanyaan;
        const jawEl = row.querySelector('.faq-jawaban');
        if (jawEl) jawEl.textContent = jawaban;

        const tagsContainer = row.querySelector('.faq-tags-container');
        if (tagsArr.length > 0) {
            tagsContainer.innerHTML = tagsArr.map(t => `<span class="keyword-pill-blue">${escHtml(t)}</span>`).join('');
        } else {
            tagsContainer.innerHTML = `<div class="fw-bold text-danger fs-12">Belum diisi</div>`;
        }

        const judulEl = row.querySelector('.faq-pustaka-judul');
        if (judulEl) {
            if (pustakaJudul) {
                judulEl.textContent = pustakaJudul;
                judulEl.classList.remove('text-danger', 'text-muted', 'fs-12');
                judulEl.classList.add('fw-bold', 'text-dark', 'fs-13');
            } else {
                judulEl.textContent = 'Tidak ada (Opsional)';
                judulEl.classList.remove('fw-bold', 'text-dark', 'text-danger', 'fs-13');
                judulEl.classList.add('text-muted', 'fs-12');
            }
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
        fetch(`{{ route($valPrefix . 'pratinjau.updateItem') }}`, {
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
                pustaka_judul: pustakaJudul
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
                fetch(`{{ route($valPrefix . 'pratinjau.deleteItem') }}`, {
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

        // Jika ingin mengajukan validasi, cek apakah ada yang belum memiliki Kata Kunci
        if (actionType === 'submit') {
            const incompleteIds = [];
            checkedBoxes.forEach(cb => {
                const r = cb.closest('.faq-row');
                if (r) {
                    const rowCode = r.getAttribute('data-id') || cb.value;
                    const tStr = (r.getAttribute('data-tags') || '').trim();
                    if (!tStr || r.getAttribute('data-status') !== 'ready') {
                        incompleteIds.push(rowCode);
                    }
                }
            });

            if (incompleteIds.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kata Kunci Belum Lengkap',
                    html: `Peringatan: FAQ (<strong>${escHtml(incompleteIds.join(', '))}</strong>) belum memiliki <strong>Kata Kunci</strong>.<br><br>Klik tombol <strong>Ubah</strong> pada baris tersebut untuk melengkapi, atau klik <strong>Simpan Draf</strong> terlebih dahulu.`,
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
            title: {{ \Illuminate\Support\Js::from($isUnitVal ? 'Teruskan ' : 'Simpan ') }} + checkedCount + {{ \Illuminate\Support\Js::from($isUnitVal ? ' FAQ ke Validator Admik?' : ' FAQ ke Antrean Siap Terbit?') }},
            text: {{ \Illuminate\Support\Js::from($isUnitVal ? 'FAQ yang dipilih akan langsung diteruskan ke antrean Validator Admik.' : 'FAQ yang dipilih akan langsung masuk ke halaman Validasi FAQ dengan status Diterima dan tinggal diterbitkan tanpa perlu disetujui lagi.') }},
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: {{ \Illuminate\Support\Js::from($isUnitVal ? 'Ya, Teruskan ke Admik' : 'Ya, Simpan & Siap Terbit') }},
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar FAQ - Ruang Validator | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Validator & Data Entry Shared Styles -->
    <link rel="stylesheet" href="{{ asset('css/validator/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dataentry/style.css') }}">

    <style>
        div.swal2-popup.kms-view-modal {
            width: 1080px !important;
            max-width: 95vw !important;
            height: 84vh !important;
            max-height: 84vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 1.5rem 1.6rem 1.2rem 1.6rem !important;
            position: relative !important;
        }
        div.swal2-popup.kms-view-modal .swal2-close {
            position: absolute !important;
            top: 0.9rem !important;
            right: 1rem !important;
            z-index: 15 !important;
        }
        div.swal2-popup.kms-view-modal .swal2-html-container {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            margin: 0 !important;
            padding: 0.15rem 0.65rem 0.5rem 0.15rem !important;
        }
        div.swal2-popup.kms-view-modal .swal2-html-container::-webkit-scrollbar {
            width: 7px;
        }
        div.swal2-popup.kms-view-modal .swal2-html-container::-webkit-scrollbar-track {
            background: #F4F1EC;
            border-radius: 999px;
        }
        div.swal2-popup.kms-view-modal .swal2-html-container::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 999px;
        }
        div.swal2-popup.kms-view-modal .swal2-html-container::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
        div.swal2-popup.kms-view-modal .swal2-actions {
            flex-shrink: 0 !important;
            margin: 0.85rem 0 0 0 !important;
            padding-top: 0.75rem !important;
            border-top: 1px solid #EDE7E1 !important;
            width: 100% !important;
            justify-content: flex-end !important;
        }
        .kms-ver-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .kms-ver-item {
            background-color: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .kms-ver-item.is-latest {
            border-color: #E5B8C0;
        }
        .kms-ver-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.78rem 1.05rem;
            background-color: #FFFFFF;
            border: 0;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .kms-ver-trigger:hover {
            background-color: #FAF8F5;
        }
        .kms-ver-trigger:not(.collapsed) {
            background-color: #FDF9F9;
            border-bottom: 1px solid #F1EDE7;
        }
        .kms-ver-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.6rem;
            min-width: 0;
        }
        .kms-ver-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #334155;
            color: #FFFFFF;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.28rem 0.82rem;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .kms-ver-item.is-latest .kms-ver-badge {
            background-color: #85172C;
        }
        .kms-ver-time {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }
        .kms-ver-time i {
            font-size: 0.78rem;
            line-height: 1;
            color: #94A3B8;
        }
        .kms-ver-current-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background-color: #ECFDF5;
            color: #059669;
            border: 1px solid #A7F3D0;
            font-size: 0.71rem;
            font-weight: 700;
            padding: 0.22rem 0.68rem;
            border-radius: 999px;
            white-space: nowrap;
        }
        .kms-ver-current-pill i {
            font-size: 0.72rem;
            line-height: 1;
        }
        .kms-ver-chevron {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border-radius: 50%;
            background-color: #F4F1EC;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
        }
        .kms-ver-chevron i {
            font-size: 0.78rem;
            line-height: 1;
        }
        .kms-ver-trigger:not(.collapsed) .kms-ver-chevron {
            transform: rotate(180deg);
            background-color: #FCECEF;
            color: #85172C;
        }
        .kms-ver-body {
            padding: 0.95rem 1.1rem 1.05rem 1.1rem;
            background-color: #FFFFFF;
        }
        .kms-ver-sublabel {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #85172C;
            margin-bottom: 0.38rem;
        }
        .kms-ver-answer {
            background-color: #FAF9F6;
            border: 1px solid #EDE7E1;
            border-radius: 10px;
            padding: 0.8rem 0.95rem;
            font-size: 0.85rem;
            line-height: 1.62;
            color: #1E293B;
            word-break: break-word;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    @php
                $authUser = auth()->user();
                if ($authUser && !$authUser->relationLoaded('role')) {
                    $authUser->load('role');
                }
                $roleName = $authUser ? $authUser->getRoleName() : 'Validator';
            @endphp
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <!-- KMS Brand Header -->
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
            </div>

            <!-- Role Badge -->
            <div>
                <span class="role-pill">Ruang Validator</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="{{ route('validator.daftarFAQ') }}" class="nav-link-custom active">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="{{ route('validator.validasiFAQ') }}" class="nav-link-custom">
                    <i class="bi bi-list-check"></i>
                    <span>Validasi FAQ</span>
                </a>
                <a href="{{ route('validator.buatfaq') }}" class="nav-link-custom">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat FAQ</span>
                </a>
                <a href="{{ route('validator.katakunci') }}" class="nav-link-custom">
                    <i class="bi bi-tags"></i>
                    <span>Daftar Kata Kunci</span>
                </a>
                <a href="{{ route('validator.sumberpustaka') }}" class="nav-link-custom">
                    <i class="bi bi-book"></i>
                    <span>Sumber Pustaka</span>
                </a>
                <a href="{{ route('validator.pengguna-akses') }}" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna &amp; Akses</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <hr class="sidebar-divider">
            <a href="{{ route('validator.pengaturanAkun') }}" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Akun</span>
            </a>
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
         id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- ══════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════ -->
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
                    <span class="crumb-current">Daftar FAQ</span>
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
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('validator.pengaturanAkun') }}">
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

        <!-- Content -->
        <div class="content-area">

            <!-- Page Header (Ukuran & Warna Seragam) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">Daftar FAQ</h1>
                    <p class="fs-13 text-muted mb-0">
                        Tinjau, tambahkan, atau perbarui pertanyaan dan jawaban FAQ untuk portal mahasiswa.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-outline-maroon text-nowrap" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i><span>Unggah File</span>
                    </button>
                    <a href="{{ route('validator.buatfaq') }}" class="btn-maroon text-nowrap">
                        <i class="bi bi-plus-lg"></i><span>Buat FAQ</span>
                    </a>
                </div>
            </div>

            <!-- 5 Kotak Statistik Sejajar (Ringkas: Hanya Judul & Jumlah) -->
            <div class="stat-grid-5 mb-4">
                {{-- Total FAQ --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EFF6FF; color:#2563EB;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total FAQ</div>
                        <div class="stat-value">{{ $totalFaq }}</div>
                    </div>
                </div>

                {{-- Terbit --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#ECFDF5; color:#059669;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Terbit</div>
                        <div class="stat-value">{{ $totalAktif }}</div>
                    </div>
                </div>

                {{-- Diterima --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#EDE9FE; color:#7C3AED;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Diterima</div>
                        <div class="stat-value">{{ $totalApproved }}</div>
                    </div>
                </div>

                {{-- Menunggu Validasi --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FFFBEB; color:#D97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Menunggu Validasi</div>
                        <div class="stat-value">{{ $totalSubmitted }}</div>
                    </div>
                </div>

                {{-- Ditolak --}}
                <div class="stat-card-compact">
                    <div class="stat-icon" style="background-color:#FEF2F2; color:#DC2626;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-value">{{ $totalRejected ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="table-card">

                <!-- Card Header Title (Tanpa subjudul 'Semua Fakultas') -->
                <div class="table-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="fw-bold fs-15 text-dark mb-0">Daftar Seluruh FAQ</h2>
                    </div>
                </div>

                <!-- ── Filter Bar (Satu Baris: Search + Kategori Fakultas + Status + Sort) ── -->
                <!-- ── Filter Bar (Search + Kata Kunci + Status + Sort, Auto-Submit) ── -->
                @include('partials.daftar-faq-filter-bar', [
                    'actionRoute' => route('validator.daftarFAQ'),
                    'kataKunciOptions' => $kataKunciOptions ?? collect(),
                ])

                <!-- ── Table ── -->
                <div class="table-responsive">
                    <table class="faq-table">
                        <thead>
                            <tr>
                                <th style="width:115px;">Kode / Versi</th>
                                <th>Pertanyaan</th>
                                <th style="width:155px;">Data Entry</th>
                                <th style="width:135px;">Waktu Pengajuan</th>
                                <th style="width:215px;">Status</th>
                                <th style="width:195px;" class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                @php
                                    $latestVersion = $faq->versions->first();
                                    $status        = $latestVersion?->status ?? 'Draf';
                                    $versiLabel    = \App\Models\FaqVersion::formatVersiLabel($latestVersion?->id);
                                    $de            = $faq->dataEntry;
                                    $statusClass   = match($status) {
                                        'Diterima'          => 'badge-approved',
                                        'Terbit'            => 'badge-active',
                                        'Menunggu Validasi' => 'badge-submitted',
                                        'Ditolak'           => 'badge-rejected',
                                        default             => 'badge-draft',
                                    };
                                    $statusLabel   = match($status) {
                                        'Terbit'            => 'Terbit',
                                        'Diterima'          => 'Diterima',
                                        'Menunggu Validasi' => 'Menunggu Validasi',
                                        'Ditolak'           => 'Ditolak',
                                        default             => $status,
                                    };
                                    $statusIcon    = match($status) {
                                        'Diterima'          => 'bi-check-circle-fill',
                                        'Terbit'            => 'bi-lightning-fill',
                                        'Menunggu Validasi' => 'bi-hourglass-split',
                                        'Ditolak'           => 'bi-x-circle-fill',
                                        default             => 'bi-pencil',
                                    };
                                    $createdAtJkt  = ($latestVersion?->updated_at ?? $latestVersion?->created_at ?? $faq->updated_at ?? $faq->created_at)?->timezone('Asia/Jakarta');
                                @endphp
                                <tr>
                                    <!-- Kode / Versi -->
                                    <td>
                                        <div class="faq-code">{{ $faq->id }}</div>
                                        <div class="faq-version">{{ $versiLabel }}</div>
                                    </td>

                                    <!-- Pertanyaan + Tags -->
                                    <td>
                                        <div class="faq-question">{{ Str::limit($faq->pertanyaan, 85) }}</div>
                                        @if($faq->tags->isNotEmpty())
                                            <div class="faq-tags">
                                                @foreach($faq->tags->take(3) as $tag)
                                                    <span class="tag-pill">{{ $tag->nama }}</span>
                                                @endforeach
                                                @if($faq->tags->count() > 3)
                                                    <span class="tag-pill">+{{ $faq->tags->count() - 3 }}</span>
                                                @endif
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="faq-tags">
                                                <span class="tag-pill" style="background-color:#F8FAFC; color:#475569; border-color:#E2E8F0;" title="Statistik Views, Like & Dislike Mahasiswa">
                                                    <i class="bi bi-eye me-1"></i>{{ $faq->views_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>{{ $faq->likes_count ?? 0 }}
                                                    &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>{{ $faq->dislikes_count ?? 0 }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Data Entry (Cukup Namanya Saja) -->
                                    <td>
                                        <div class="fs-13 fw-semibold text-dark">{{ $de?->nama ?? '—' }}</div>
                                    </td>

                                    <!-- Waktu Pengajuan (Timezone Jakarta GMT+7) -->
                                    <td>
                                        <div class="fs-13 text-dark">
                                            {{ $createdAtJkt ? $createdAtJkt->translatedFormat('d M Y') : '—' }}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            {{ $createdAtJkt ? $createdAtJkt->format('H.i') . ' WIB' : '' }}
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        @php
                                            $schedInfoDaftar = ($status === 'Diterima' && $latestVersion) ? $latestVersion->getPublishScheduleInfo() : null;
                                        @endphp
                                        @if($status === 'Diterima' && !empty($schedInfoDaftar['is_scheduled']))
                                            <div class="status-cell-stack">
                                                <span class="badge-status badge-scheduled-wait">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Terjadwal Publikasi
                                                </span>
                                                @if(!empty($schedInfoDaftar['scheduled_at']))
                                                    <span class="status-subnote">
                                                        {{ \Carbon\Carbon::parse($schedInfoDaftar['scheduled_at'])->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }} WIB
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge-status {{ $statusClass }}">
                                                <i class="bi {{ $statusIcon }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        @endif
                                    </td>

                                    @php
                                        $nextVerId = $faq->generateNextVersionId();
                                        $stValLower = strtolower((string) $status);
                                    @endphp
                                    <!-- Aksi (Lihat & Ubah) -->
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                            <button type="button" class="btn-action btn-action-view" onclick="viewValidatorFaqDetail('{{ $faq->id }}')">
                                                <i class="bi bi-eye"></i> Lihat
                                            </button>
                                            <a href="{{ route('validator.faq.edit', $faq->id) }}" class="btn-action btn-action-edit text-decoration-none" title="{{ in_array($stValLower, ['ditolak', 'rejected'], true) ? 'Revisi FAQ (' . ($latestVersion?->id ?? ($faq->id . '-V1')) . ')' : 'Ubah / Buat Versi Baru (' . $nextVerId . ')' }}">
                                                <i class="bi bi-pencil"></i> {{ in_array($stValLower, ['ditolak', 'rejected'], true) ? 'Revisi' : 'Ubah' }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                            <i class="bi bi-journal-x fs-1 opacity-30"></i>
                                            <div class="fw-semibold fs-14">Belum ada FAQ ditemukan</div>
                                            <div class="fs-12">
                                                @if(request('search') || (request('fakultas') && request('fakultas') !== 'Semua'))
                                                    Coba ubah filter atau kata kunci pencarian.
                                                @else
                                                    Belum ada FAQ yang ditambahkan.
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ── Pagination ── -->
                @if($faqs->hasPages())
                    <div class="pagination-bar">
                        <span>
                            Menampilkan {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }}
                            dari {{ $faqs->total() }} FAQ &middot; {{ $faqs->perPage() }} per halaman
                        </span>
                        <div class="d-flex gap-1 align-items-center">
                            <a href="{{ $faqs->previousPageUrl() ?? '#' }}"
                               class="page-btn {{ $faqs->onFirstPage() ? 'disabled' : '' }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>

                            @for($p = 1; $p <= $faqs->lastPage(); $p++)
                                <a href="{{ $faqs->url($p) }}"
                                   class="page-btn {{ $faqs->currentPage() === $p ? 'active' : '' }}">
                                    {{ $p }}
                                </a>
                            @endfor

                            <a href="{{ $faqs->nextPageUrl() ?? '#' }}"
                               class="page-btn {{ !$faqs->hasMorePages() ? 'disabled' : '' }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="pagination-bar">
                        <span>Menampilkan {{ $faqs->count() }} FAQ</span>
                    </div>
                @endif

            </div><!-- /.table-card -->

        </div><!-- /.content-area -->
    </main>
</div>

<!-- Modal Unggah File FAQ -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('validator.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="dashExcelForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-16" id="uploadModalLabel">Unggah File FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="upload-dropzone mb-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-success mb-2 d-block"></i>
                        <div class="fw-semibold text-dark fs-14 mb-1">Tarik &amp; lepas file di sini</div>
                        <div class="text-muted fs-12 mb-3">Mendukung format .XLSX, .CSV, atau .PDF hingga 10MB</div>
                        <label class="btn btn-sm btn-white-outline">
                            <i class="bi bi-folder2-open"></i> Pilih Dokumen
                            <input type="file" name="file_faq" accept=".xlsx,.xls,.csv,.pdf" class="d-none" onchange="submitDashExcel(this)">
                        </label>
                        <div id="dashFileNameText" class="fs-12 text-success fw-semibold mt-2" style="display:none;"></div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border fs-12 text-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i>Contoh Template Unggah File:</span>
                            <a href="{{ route('validator.template.faq') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-maroon);">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                        <div>Kolom A: <strong>Pertanyaan</strong> &middot; Kolom B: <strong>Jawaban</strong> &middot; Kolom C: <strong>Kata Kunci</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-maroon px-3">Unggah &amp; Tinjau</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
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
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

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

    function confirmDeleteValidatorFaq(faqId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus FAQ ' + faqId + '?',
                text: 'FAQ dan seluruh riwayat versinya akan dihapus permanen dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    document.getElementById('validator-delete-faq-' + faqId)?.submit();
                }
            });
        } else if (confirm('Yakin ingin menghapus FAQ ' + faqId + '?')) {
            document.getElementById('validator-delete-faq-' + faqId)?.submit();
        }
    }

    function formatVersiCode(verId) {
        const m = String(verId || '').match(/-V(\d+)$/i);
        return m ? ('Versi ' + parseInt(m[1], 10)) : 'Versi 1';
    }

    function viewValidatorFaqDetail(faqId) {
        fetch(`{{ url('/validator/faq') }}/${faqId}/detail`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const latest = d.latest_version || {};
                const tagsHtml = (d.tags && d.tags.length > 0)
                    ? `<div style="margin-bottom: 1.1rem;"><div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Kata Kunci</div><div class="d-flex flex-wrap gap-2">${d.tags.map(t => `<span class="badge rounded-pill" style="background-color:#F8F5F1;color:#85172C;border:1px solid #EDE7E1;font-size:0.76rem;padding:0.35rem 0.8rem;font-weight:600;">${t}</span>`).join('')}</div></div>`
                    : '';

                const rawSt = (latest.status || 'Draf').toLowerCase();
                let statusBadge = `<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.75rem;padding:0.4rem 0.85rem;">Draf</span>`;
                if (rawSt === 'terbit' || rawSt === 'active') statusBadge = '<span class="badge rounded-pill" style="background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#FFFFFF;box-shadow:0 2px 6px rgba(5,150,105,0.25);font-size:0.75rem;padding:0.4rem 0.9rem;font-weight:700;"><i class="bi bi-lightning-fill me-1"></i>Terbit</span>';
                else if (rawSt === 'diterima' || rawSt === 'approved') statusBadge = '<span class="badge rounded-pill" style="background-color:#EDE9FE;color:#6D28D9;border:1px solid #DDD6FE;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-check-circle-fill me-1"></i>Diterima</span>';
                else if (rawSt === 'menunggu validasi' || rawSt === 'submitted') statusBadge = '<span class="badge rounded-pill" style="background-color:#FFFBEB;color:#D97706;border:1px solid #FDE68A;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi</span>';
                else if (rawSt === 'ditolak' || rawSt === 'rejected') statusBadge = '<span class="badge rounded-pill" style="background-color:#FEF2F2;color:#DC2626;border:1px solid #FECACA;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>';

                const latestVerObj = (d.versions || []).find(v => v.id === latest.id) || (d.versions || [])[0] || {};
                const dp = latestVerObj.daftar_pustaka || null;
                let sourceHtml = '';
                if (dp) {
                    const actionLink = dp.is_file && dp.view_url
                        ? `<a href="${dp.view_url}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.75rem;"><i class="bi bi-eye me-1"></i>Lihat Berkas</a>`
                        : (dp.link && /^https?:\/\//i.test(dp.link) ? `<a href="${dp.link}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.75rem;"><i class="bi bi-eye me-1"></i>Lihat Tautan</a>` : '');
                    sourceHtml = `
                        <div style="margin-bottom: 1.1rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Sumber Pustaka</div>
                            <div class="rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background-color: #FBF9F7; border: 1px solid #EDE7E1; padding: 0.75rem 1rem;">
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.85rem;">${dp.judul || '-'}</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">${dp.is_file ? 'Berkas Dokumen' : 'Tautan Rujukan'}</div>
                                </div>
                                ${actionLink}
                            </div>
                        </div>
                    `;
                }

                let versionsHistoryHtml = '';
                if (d.versions && d.versions.length > 0) {
                    const getVerNum = (ver) => {
                        const m = String(ver?.id || ver?.versi_label || '').match(/(\d+)\s*$/);
                        return m ? parseInt(m[1], 10) : 1;
                    };
                    const sortedVersions = [...d.versions].sort((a, b) => getVerNum(b) - getVerNum(a));

                    versionsHistoryHtml = `
                        <div style="margin-top: 0.35rem;">
                            <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.6rem;">Riwayat Versi Jawaban</div>
                            <div class="kms-ver-list" id="kmsModalVersionsAccordion">
                                ${sortedVersions.map((v, idx) => {
                                    const versiLabel = v.versi_label || formatVersiCode(v.id);
                                    const isCurrent = (latest && latest.id && v.id === latest.id) || (!latest?.id && idx === 0);
                                    const collapseId = `kmsVerCollapse_${idx}`;
                                    const vDp = v.daftar_pustaka || null;
                                    const vActionBtn = vDp
                                        ? (vDp.is_file && vDp.view_url
                                            ? `<a href="${vDp.view_url}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.74rem;"><i class="bi bi-eye me-1"></i>Lihat Berkas</a>`
                                            : (vDp.link && /^https?:\/\//i.test(vDp.link) ? `<a href="${vDp.link}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" style="font-size:0.74rem;"><i class="bi bi-eye me-1"></i>Lihat Tautan</a>` : ''))
                                        : '';
                                    const vSourceHtml = vDp
                                        ? `
                                            <div class="mt-3">
                                                <div class="kms-ver-sublabel">Sumber Pustaka</div>
                                                <div class="rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background-color: #EFF6FF; border: 1px solid #DBEAFE; padding: 0.68rem 0.9rem;">
                                                    <div>
                                                        <div class="fw-semibold text-dark" style="font-size: 0.82rem;">${vDp.judul || '-'}</div>
                                                        <div class="text-muted" style="font-size: 0.72rem;">${vDp.is_file ? 'Berkas Dokumen' : 'Tautan Rujukan'}</div>
                                                    </div>
                                                    ${vActionBtn}
                                                </div>
                                            </div>
                                        `
                                        : '';
                                    const vNoteHtml = (v.catatan && !String(v.catatan).startsWith('[PUBLISH_SCHEDULE|'))
                                        ? `<div class="rounded-3 mt-3" style="background-color:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.78rem;padding:0.6rem 0.85rem;"><strong>Catatan:</strong> ${v.catatan}</div>`
                                        : '';

                                    return `
                                        <div class="kms-ver-item ${isCurrent ? 'is-latest' : ''}">
                                            <button type="button"
                                                    class="kms-ver-trigger collapsed"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#${collapseId}"
                                                    aria-expanded="false"
                                                    aria-controls="${collapseId}">
                                                <div class="kms-ver-left">
                                                    <span class="kms-ver-badge">${versiLabel}</span>
                                                    <span class="kms-ver-time">
                                                        <i class="bi bi-clock"></i>
                                                        <span>${v.updated_at || '-'}</span>
                                                    </span>
                                                    ${isCurrent ? `<span class="kms-ver-current-pill"><i class="bi bi-check-circle-fill"></i><span>Versi Saat Ini</span></span>` : ''}
                                                </div>
                                                <span class="kms-ver-chevron" aria-hidden="true">
                                                    <i class="bi bi-chevron-down"></i>
                                                </span>
                                            </button>
                                            <div id="${collapseId}" class="collapse" data-bs-parent="#kmsModalVersionsAccordion">
                                                <div class="kms-ver-body">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                        <div class="kms-ver-sublabel mb-0">Jawaban</div>
                                                        ${v.status ? `<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.7rem;padding:0.25rem 0.65rem;">Status: ${v.status}</span>` : ''}
                                                    </div>
                                                    <div class="kms-ver-answer">${v.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}</div>
                                                    ${vSourceHtml}
                                                    ${vNoteHtml}
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: '',
                    html: `
                        <div class="text-start" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #1E293B;">
                            <!-- Header Banner Tema Website -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-bottom: 1px solid #EDE7E1; padding: 0 3rem 0.95rem 0; margin-bottom: 1.15rem;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.78rem; padding: 0.42rem 0.9rem;">${d.id}</span>
                                    ${statusBadge}
                                    <span class="badge rounded-pill" style="background-color: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; font-size: 0.74rem; padding: 0.38rem 0.75rem;">
                                        <i class="bi bi-eye me-1"></i>${d.views_count ?? 0} dilihat
                                        &middot; <i class="bi bi-hand-thumbs-up text-success ms-1 me-1"></i>${d.likes_count ?? 0}
                                        &middot; <i class="bi bi-hand-thumbs-down text-danger ms-1 me-1"></i>${d.dislikes_count ?? 0}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="bi bi-person me-1"></i><strong class="text-dark">${d.data_entry || '-'}</strong></span>
                                    <span>&middot;</span>
                                    <span><i class="bi bi-clock me-1"></i>${d.created_at || '-'}</span>
                                </div>
                            </div>

                            <!-- Pertanyaan -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Pertanyaan</div>
                                <div class="rounded-3 fw-semibold text-dark" style="background-color: #FFFDFB; border: 1px solid #EDE7E1; font-size: 0.94rem; line-height: 1.55; padding: 0.85rem 1.05rem;">
                                    ${d.pertanyaan || '-'}
                                </div>
                            </div>

                            <!-- Jawaban -->
                            <div style="margin-bottom: 1.1rem;">
                                <div class="text-uppercase fw-bold" style="font-size: 0.72rem; color: #85172C; letter-spacing: 0.06em; margin-bottom: 0.45rem;">Jawaban (${formatVersiCode(latest.id || (d.id + '-V1'))})</div>
                                <div class="rounded-3 bg-white text-dark" style="border: 1px solid #EDE7E1; font-size: 0.89rem; max-height: 165px; overflow-y: auto; line-height: 1.65; padding: 0.9rem 1.05rem;">
                                    ${latest.jawaban || '<em class="text-muted">Belum ada jawaban</em>'}
                                </div>
                            </div>

                            ${tagsHtml}

                            ${sourceHtml}

                            ${versionsHistoryHtml}
                        </div>
                    `,
                    width: '1080px',
                    showCloseButton: true,
                    confirmButtonColor: '#85172C',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0 kms-view-modal'
                    }
                });
            }
        })
        .catch(err => console.error(err));
    }

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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Mahasiswa - KMS (Knowledge Management System)</title>
    <meta name="description" content="Pusat pengetahuan mahasiswa: temukan jawaban seputar KRS, registrasi, cuti kuliah, KHS, dan beasiswa.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Mahasiswa FAQ CSS (Full Width) -->
    <link rel="stylesheet" href="{{ asset('css/mahasiswa/style.css') }}">
</head>
<body>

@php
    /*
    |--------------------------------------------------------------------------
    | TEMPLATE DETAIL KODE FAKULTAS (MENGACU KE PortalController::FAKULTAS_MAP)
    |--------------------------------------------------------------------------
    | Daftar ini mencocokkan kode_fakultas dari session aktif (tabel tsession).
    | Contoh: NRP "161124002" -> kode "16" -> "Fakultas Teknik".
    */
    $daftarFakultas = $fakultasMap ?? \App\Http\Controllers\PortalController::FAKULTAS_MAP;

    $activeKodeFakultas = $kodeFakultas ?? session('kode_fakultas');
    $activeNamaFakultas = $namaFakultas ?? (
        $activeKodeFakultas
            ? ($daftarFakultas[$activeKodeFakultas] ?? ('Fakultas (Kode ' . $activeKodeFakultas . ')'))
            : 'Umum (Semua Fakultas)'
    );
    $activeKodeAngkatan  = $kodeAngkatan ?? session('kode_angkatan');
    $activeTahunAngkatan = $tahunAngkatan ?? session('tahun_angkatan') ?? ($activeKodeAngkatan ? ('20' . $activeKodeAngkatan) : null);
    $activeSemester      = $semester ?? session('semester') ?? \App\Http\Controllers\PortalController::estimateSemester($activeTahunAngkatan);

    $categories = [
        ['key' => 'top',         'label' => 'Top FAQ',              'icon' => 'bi-stars'],
        ['key' => 'rekomendasi', 'label' => 'Rekomendasi FAQ',      'icon' => 'bi-person-check'],
        ['key' => 'umum',        'label' => 'Kategori umum',        'icon' => 'bi-grid'],
        ['key' => 'fakultas',    'label' => $activeNamaFakultas,    'icon' => 'bi-building'],
        ['key' => 'akademik',    'label' => 'Akademik',             'icon' => 'bi-book'],
        ['key' => 'biaya',       'label' => 'Biaya & beasiswa',     'icon' => 'bi-wallet2'],
        ['key' => 'praktikum',   'label' => 'Praktikum',            'icon' => 'bi-eyedropper'],
        ['key' => 'tugas',       'label' => 'Tugas akhir',          'icon' => 'bi-mortarboard'],
    ];

    $resolveCategoryKeys = function ($dbFaq, $activeNamaFakultas) {
        $tagNames = $dbFaq->tags->pluck('nama')->toArray();
        $tagString = strtolower(implode(' ', $tagNames));
        $qLower = strtolower($dbFaq->pertanyaan);
        $keys = ['top'];
        if (empty($dbFaq->is_other_fakultas)) {
            $keys[] = 'rekomendasi';
        }

        // 1. Kategori Fakultas
        $activeFak = strtolower(trim((string) $activeNamaFakultas));
        $isFakultasMatch = false;

        if ($activeFak && $activeFak !== 'umum (semua fakultas)' && $activeFak !== 'guest') {
            if (str_contains($activeFak, 'teknik') && (str_contains($tagString, 'teknik') || str_contains($qLower, 'teknik'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'farmasi') && (str_contains($tagString, 'farmasi') || str_contains($qLower, 'farmasi'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'hukum') && (str_contains($tagString, 'hukum') || str_contains($qLower, 'hukum'))) {
                $isFakultasMatch = true;
            } elseif ((str_contains($activeFak, 'bisnis') || str_contains($activeFak, 'ekonomi')) && (str_contains($tagString, 'bisnis') || str_contains($tagString, 'ekonomi') || str_contains($qLower, 'fbe') || str_contains($qLower, 'manajemen') || str_contains($qLower, 'akuntansi'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'kedokteran') && (str_contains($tagString, 'kedokteran') || str_contains($qLower, 'dokter') || str_contains($qLower, 'kedokteran'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'psikologi') && (str_contains($tagString, 'psikologi') || str_contains($qLower, 'psikologi'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'teknobiologi') && (str_contains($tagString, 'teknobiologi') || str_contains($qLower, 'bioteknologi') || str_contains($qLower, 'teknobiologi'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'industri kreatif') && (str_contains($tagString, 'kreatif') || str_contains($tagString, 'fik') || str_contains($qLower, 'desain'))) {
                $isFakultasMatch = true;
            } elseif (str_contains($activeFak, 'politeknik') && (str_contains($tagString, 'politeknik') || str_contains($qLower, 'vokasi') || str_contains($qLower, 'politeknik'))) {
                $isFakultasMatch = true;
            } else {
                foreach ($tagNames as $tn) {
                    if (str_contains(strtolower($tn), $activeFak) || str_contains($activeFak, strtolower($tn))) {
                        $isFakultasMatch = true;
                        break;
                    }
                }
            }
        } else {
            if (str_contains($tagString, 'fakultas') || str_contains($tagString, 'teknik') || str_contains($tagString, 'politeknik')) {
                $isFakultasMatch = true;
            }
        }

        if ($isFakultasMatch) {
            $keys[] = 'fakultas';
        }

        // 2. Kategori Umum
        if (
            str_contains($tagString, 'umum') ||
            str_contains($qLower, 'umum') ||
            str_contains($qLower, 'seluruh mahasiswa') ||
            str_contains($qLower, 'registrasi ulang') ||
            str_contains($qLower, 'wi-fi') ||
            str_contains($qLower, 'sso')
        ) {
            $keys[] = 'umum';
        }

        // 3. Kategori Akademik
        if (
            str_contains($tagString, 'akademik') ||
            str_contains($tagString, 'krs') ||
            str_contains($tagString, 'rencana studi') ||
            str_contains($tagString, 'cuti') ||
            str_contains($tagString, 'transkrip') ||
            str_contains($tagString, 'surat akademik') ||
            str_contains($tagString, 'pindah prodi') ||
            str_contains($qLower, 'krs') ||
            str_contains($qLower, 'perwalian') ||
            str_contains($qLower, 'sks') ||
            str_contains($qLower, 'mata kuliah') ||
            str_contains($qLower, 'semester') ||
            str_contains($qLower, 'prasyarat') ||
            str_contains($qLower, 'akademik')
        ) {
            $keys[] = 'akademik';
        }

        // 4. Kategori Biaya & Beasiswa
        if (
            str_contains($tagString, 'biaya') ||
            str_contains($tagString, 'beasiswa') ||
            str_contains($qLower, 'biaya') ||
            str_contains($qLower, 'ukt') ||
            str_contains($qLower, 'cicilan') ||
            str_contains($qLower, 'beasiswa') ||
            str_contains($qLower, 'keringanan') ||
            str_contains($qLower, 'keuangan')
        ) {
            $keys[] = 'biaya';
        }

        // 5. Kategori Praktikum
        if (
            str_contains($tagString, 'praktikum') ||
            str_contains($tagString, 'laboratorium') ||
            str_contains($tagString, 'fasilitas') ||
            str_contains($qLower, 'praktikum') ||
            str_contains($qLower, 'laboratorium') ||
            str_contains($qLower, 'lab') ||
            str_contains($qLower, 'inhal') ||
            str_contains($qLower, 'aslab') ||
            str_contains($qLower, 'k3') ||
            str_contains($qLower, 'alat')
        ) {
            $keys[] = 'praktikum';
        }

        // 6. Kategori Tugas Akhir
        if (
            str_contains($tagString, 'tugas akhir') ||
            str_contains($tagString, 'yudisium') ||
            str_contains($tagString, 'wisuda') ||
            str_contains($tagString, 'capstone') ||
            str_contains($qLower, 'tugas akhir') ||
            str_contains($qLower, 'skripsi') ||
            str_contains($qLower, 'sempro') ||
            str_contains($qLower, 'sidang') ||
            str_contains($qLower, 'yudisium') ||
            str_contains($qLower, 'wisuda') ||
            str_contains($qLower, 'turnitin') ||
            str_contains($qLower, 'pembimbing')
        ) {
            $keys[] = 'tugas';
        }

        return array_values(array_unique($keys));
    };

    $faqs = [
        ['id' => 1, 'q' => 'Bagaimana cara mengisi dan mengajukan KRS?', 'cat' => 'KRS & perkuliahan', 'keys' => ['top', 'rekomendasi', 'akademik', 'umum'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.', 'source_url' => '#'],
        ['id' => 2, 'q' => 'Apa yang perlu disiapkan untuk registrasi ulang?', 'cat' => 'Administrasi akademik', 'keys' => ['top', 'rekomendasi', 'umum', 'akademik'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa', 'source_url' => '#'],
        ['id' => 3, 'q' => 'Bagaimana prosedur pengajuan cuti kuliah?', 'cat' => 'Administrasi akademik', 'keys' => ['top', 'rekomendasi', 'umum', 'akademik'], 'a' => null, 'source_type' => 'private', 'source_label' => 'SOP Internal Pengajuan Cuti Kuliah', 'source_url' => '#'],
        ['id' => 4, 'q' => 'Di mana saya bisa melihat hasil studi (KHS)?', 'cat' => 'KRS & perkuliahan', 'keys' => ['top', 'rekomendasi', 'akademik'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa', 'source_url' => '#'],
        ['id' => 5, 'q' => 'Bagaimana cara mengajukan beasiswa?', 'cat' => 'Biaya & beasiswa', 'keys' => ['top', 'rekomendasi', 'biaya'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka panduan pengajuan beasiswa mahasiswa', 'source_url' => '#'],
    ];

    if (isset($publishedFaqs) && $publishedFaqs->isNotEmpty()) {
        $mappedDbFaqs = [];
        foreach ($publishedFaqs as $idx => $dbFaq) {
            $activeVer = $dbFaq->versions->first();
            $pustaka = $activeVer?->daftarPustaka;
            $sourceType = $pustaka ? strtolower((string) $pustaka->type) : null;
            $sourceLabel = $pustaka ? ($pustaka->judul_tampil ?: $pustaka->link) : null;
            $sourceUrl = ($pustaka && filter_var($pustaka->link, FILTER_VALIDATE_URL)) ? $pustaka->link : '#';
            $tagNames = $dbFaq->tags->pluck('nama')->implode(' · ');
            $assignedKeys = $resolveCategoryKeys($dbFaq, $activeNamaFakultas);
            $mappedDbFaqs[] = [
                'id'                   => $idx + 1,
                'faq_id'               => $dbFaq->id,
                'q'                    => $dbFaq->pertanyaan,
                'a'                    => $activeVer?->jawaban,
                'cat'                  => $tagNames ?: 'Panduan Akademik',
                'keys'                 => $assignedKeys,
                'source_type'          => $sourceType,
                'source_label'         => $sourceLabel,
                'source_url'           => $sourceUrl,
                'access_count'         => (int) ($dbFaq->views_count ?? $dbFaq->total_access_count ?? 0),
                'views_count'          => (int) ($dbFaq->views_count ?? $dbFaq->total_access_count ?? 0),
                'likes_count'          => (int) ($dbFaq->likes_count ?? 0),
                'dislikes_count'       => (int) ($dbFaq->dislikes_count ?? 0),
                'segment_access_count' => (int) ($dbFaq->segment_access_count ?? 0),
                'angkatan_stage_label' => $dbFaq->angkatan_stage_label ?? null,
            ];
        }
        $faqs = $mappedDbFaqs;
    }
@endphp

<!-- ========================================================================= -->
<!-- NAVBAR (FULL WIDTH)                                                       -->
<!-- ========================================================================= -->
<nav class="mhs-navbar">
    <div class="mhs-container d-flex align-items-center justify-content-between py-2">
        <!-- Left: Logo KMS -->
        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="navbar-logo-img">
        </a>

        <!-- Center: Nav Links -->
        <div class="d-none d-md-flex align-items-center gap-1">
            <a href="#" class="mhs-nav-link">Beranda</a>
            <a href="{{ url('/') }}" class="mhs-nav-link active">FAQ</a>
            <a href="#" class="mhs-nav-link">Panduan akademik</a>
        </div>

        <!-- Right: Notifications & Profile -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                <i class="bi bi-bell fs-5"></i>
            </button>
        </div>
    </div>
</nav>

<!-- ========================================================================= -->
<!-- HERO SECTION & BACKGROUND BANNER (FULL WIDTH)                            -->
<!-- ========================================================================= -->
<header class="hero-section">
    <div class="mhs-container hero-inner">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="fs-11 mb-4">
            <a href="#" class="text-decoration-none text-muted">Beranda</a>
            <span class="mx-1.5 text-muted">&gt;</span>
            <strong style="color: var(--brand-maroon);">FAQ Mahasiswa</strong>
        </nav>

        <div class="row align-items-center g-4">
            <!-- Left Column: Title, Subtitle & Search Bar -->
            <div class="col-lg-8 col-xl-8">
                <div class="hero-eyebrow mb-2">
                    <span style="font-weight: 900; margin-right: 2px;">&mdash;</span> PUSAT PENGETAHUAN MAHASISWA
                </div>
                <h1 class="display-serif hero-title mb-3">
                    Ada pertanyaan?<br>Temukan jawabannya di sini.
                </h1>
                <p class="text-muted fs-13 mb-4" style="line-height: 1.6; max-width: 600px;">
                    Dari rencana studi hingga tugas akhir, akses informasi akademik yang kamu butuhkan untuk melangkah lebih pasti.
                </p>

                <!-- Search Input Form -->
                <form class="hero-search mb-2" onsubmit="event.preventDefault(); filterFaq();" role="search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="faqSearch" placeholder='Cari pertanyaan, misalnya "cara mengisi KRS"' aria-label="Cari pertanyaan">
                    <button type="submit" class="btn-maroon border-0 shadow-sm">
                        <span>Cari jawaban</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Quick Keywords -->
                <div class="fs-11 text-muted mt-2">
                    <span>Sering dicari:</span>
                    <a href="#" class="suggest-chip ms-2" onclick="quickSearch('KRS'); return false;">KRS</a>
                    <a href="#" class="suggest-chip" onclick="quickSearch('cuti'); return false;">Cuti kuliah</a>
                    <a href="#" class="suggest-chip" onclick="quickSearch('tugas akhir'); return false;">Tugas akhir</a>
                </div>
            </div>

            <!-- Right Column: Floating Faculty Card -->
            <div class="col-lg-4 col-xl-4 d-flex justify-content-lg-end">
                <div class="faculty-card">
                    <div class="hero-eyebrow mb-2" style="font-size: 0.65rem;">
                        <i class="bi bi-building me-1"></i> Fakultas
                    </div>
                    <div class="display-serif fs-4 fw-bold text-dark mb-0">{{ $activeNamaFakultas }}</div>
                    @if(!empty($activeTahunAngkatan))
                        <div class="text-muted fs-12 mt-1">
                            <i class="bi bi-mortarboard me-1"></i>Angkatan {{ $activeTahunAngkatan }}{{ !empty($activeSemester) ? ' · Semester ' . $activeSemester : '' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========================================================================= -->
<!-- MAIN CONTENT AREA (FULL WIDTH CONTAINER)                                  -->
<!-- ========================================================================= -->
<main class="mhs-container py-5">

    <!-- ============================ KATEGORI ============================ -->
    <section class="mb-5" aria-label="Jelajahi kategori">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="hero-eyebrow text-secondary" style="letter-spacing: 0.08em; font-size: 0.7rem;">
                JELAJAHI KATEGORI
            </span>
            <span class="fs-12 text-muted d-none d-md-inline">
                Pilih kategori sesuai kebutuhan studimu.
            </span>
        </div>
        
        <div class="cat-grid" id="categoryList">
            @foreach($categories as $c)
                <button type="button" 
                        class="cat-pill {{ $loop->first ? 'active' : '' }}" 
                        data-cat="{{ $c['key'] }}" 
                        onclick="setCategory('{{ $c['key'] }}', this)">
                    <i class="bi {{ $c['icon'] }}"></i>
                    <span>{{ $c['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <!-- ============================ DAFTAR FAQ ============================ -->
    <section aria-labelledby="faqHeading" class="mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3.5">
            <div>
                <h2 id="faqHeading" class="display-serif fs-2 mb-1">Mulai dari yang sering ditanyakan</h2>
                <p class="text-muted fs-13 mb-0">
                    @if(!empty($activeTahunAngkatan))
                        Rekomendasi FAQ terpopuler untuk {{ $activeNamaFakultas }} &middot; Angkatan {{ $activeTahunAngkatan }}{{ !empty($activeSemester) ? ' (Semester ' . $activeSemester . ')' : '' }}.
                    @else
                        Pertanyaan populer mahasiswa. Jawaban praktis, tanpa perlu mencari jauh.
                    @endif
                </p>
            </div>
            <span class="tag-soft shadow-sm">
                <i class="bi bi-stars"></i>
                <span id="faqCount">Top FAQ &middot; {{ count($faqs) }} pertanyaan</span>
            </span>
        </div>

        <!-- Accordion FAQ List -->
        <div id="faqList" class="accordion mt-3" style="--bs-accordion-border-width:0;">
            @foreach($faqs as $f)
                @php
                    $catClasses = is_array($f['keys'] ?? null) ? implode(' ', $f['keys']) : ($f['key'] ?? 'akademik');
                    $realFaqId = $f['faq_id'] ?? ('FAQ-' . sprintf('%04d', $f['id']));
                @endphp
                <article class="faq-item" data-faq-id="{{ $realFaqId }}" data-rank="{{ $f['id'] }}" data-views="{{ $f['views_count'] ?? 0 }}" data-cat="{{ $catClasses }}" data-text="{{ strtolower($f['q'].' '.$f['cat']) }}">
                    <button class="faq-toggle collapsed" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#faq{{ $f['id'] }}" 
                            aria-expanded="false" 
                            aria-controls="faq{{ $f['id'] }}">
                        <span class="faq-num">{{ sprintf('%02d', $f['id']) }}</span>
                        <div class="flex-grow-1 text-start">
                            <div class="faq-q">{{ $f['q'] }}</div>
                        </div>
                        <span class="faq-cat-label d-inline-flex align-items-center gap-2">
                            <span>{{ $f['cat'] }}</span>
                            <span class="text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-eye"></i> <span data-stat-views="{{ $realFaqId }}">{{ $f['views_count'] ?? 0 }}</span>
                                &middot; <i class="bi bi-hand-thumbs-up text-success"></i> <span data-stat-likes="{{ $realFaqId }}">{{ $f['likes_count'] ?? 0 }}</span>
                                &middot; <i class="bi bi-hand-thumbs-down text-secondary"></i> <span data-stat-dislikes="{{ $realFaqId }}">{{ $f['dislikes_count'] ?? 0 }}</span>
                            </span>
                        </span>
                        <span class="faq-icon">
                            <i class="bi bi-plus-lg faq-icon-plus"></i>
                            <i class="bi bi-dash-lg faq-icon-minus"></i>
                        </span>
                    </button>

                    <div id="faq{{ $f['id'] }}" class="collapse" data-faq-id="{{ $realFaqId }}" data-bs-parent="#faqList">
                        <div class="faq-body pt-1">
                            @if(!empty($f['a']))
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="tag-soft">{{ $f['cat'] }}</span>
                                    <span class="fs-12 text-muted">
                                        @if(!empty($f['angkatan_stage_label']))
                                            Rekomendasi {{ $f['angkatan_stage_label'] }} &middot; Panduan resmi mahasiswa
                                        @else
                                            Panduan resmi mahasiswa
                                        @endif
                                    </span>
                                </div>
                                <div class="fs-13 text-secondary mb-3" style="line-height: 1.65;">
                                    {!! $f['a'] !!}
                                </div>
                                {{-- Hanya tampilkan Sumber Pustaka jika bertipe Public (Private disembunyikan dari Mahasiswa) --}}
                                @if(($f['source_type'] ?? null) === 'public' && !empty($f['source_label']))
                                    <a href="{{ $f['source_url'] ?? '#' }}" target="{{ ($f['source_url'] ?? '#') !== '#' ? '_blank' : '_self' }}" rel="noopener noreferrer" class="source-box mb-3">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-book fs-6 text-primary"></i>
                                            <span>Sumber Pustaka: {{ $f['source_label'] }}</span>
                                        </span>
                                        <i class="bi bi-arrow-up-right fs-13"></i>
                                    </a>
                                @endif
                                <!-- Feedback Section -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light fs-11 text-muted">
                                    <span>Apakah jawaban ini membantu?</span>
                                    <span class="d-flex gap-3">
                                        <button type="button" class="btn btn-link p-0 fs-12 text-decoration-none fw-semibold" style="color: var(--brand-maroon);" onclick="submitFaqFeedback('{{ $realFaqId }}', 1, this); return false;">
                                            <i class="bi bi-hand-thumbs-up me-1"></i>Ya, membantu
                                        </button>
                                        <button type="button" class="btn btn-link p-0 fs-12 text-decoration-none text-muted" onclick="submitFaqFeedback('{{ $realFaqId }}', 0, this); return false;">
                                            <i class="bi bi-hand-thumbs-down me-1"></i>Belum
                                        </button>
                                    </span>
                                </div>
                            @elseif($f['id'] === 1)
                                <p class="fs-13 text-secondary mb-3 mt-1" style="line-height: 1.6;">
                                    Kartu Rencana Studi (KRS) berisi mata kuliah yang akan kamu tempuh dalam satu semester. Secara umum, kamu dapat mengikuti langkah berikut:
                                </p>

                                <ol class="list-unstyled mb-3">
                                    <li class="d-flex gap-3 mb-3">
                                        <span class="step-num">1</span>
                                        <div>
                                            <div class="fw-bold fs-13 text-dark">Cek informasi dan kesiapan akademik</div>
                                            <div class="fs-12 text-muted" style="line-height: 1.5;">Lihat kalender akademik serta status registrasi di portal mahasiswa. Siapkan daftar mata kuliah yang ingin kamu ambil.</div>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3 mb-3">
                                        <span class="step-num">2</span>
                                        <div>
                                            <div class="fw-bold fs-13 text-dark">Susun rencana studi di portal</div>
                                            <div class="fs-12 text-muted" style="line-height: 1.5;">Masuk ke menu KRS, pilih mata kuliah sesuai kurikulum, lalu periksa prasyarat, jadwal, dan beban studi bersama dosen pembimbing akademik.</div>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3 mb-2">
                                        <span class="step-num">3</span>
                                        <div>
                                            <div class="fw-bold fs-13 text-dark">Ajukan dan pantau persetujuan</div>
                                            <div class="fs-12 text-muted" style="line-height: 1.5;">Simpan rencana studi dan ajukan sesuai alur di portal. Periksa status persetujuan serta catatan dari dosen pembimbing akademik.</div>
                                        </div>
                                    </li>
                                </ol>

                                <!-- Official Source Box (hanya tampil jika tipe Public) -->
                                @if(($f['source_type'] ?? 'public') === 'public')
                                    <a href="{{ $f['source_url'] ?? '#' }}" class="source-box mb-3">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-book fs-6 text-primary"></i>
                                            <span>{{ $f['source_label'] ?? 'Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.' }}</span>
                                        </span>
                                        <i class="bi bi-arrow-up-right fs-13"></i>
                                    </a>
                                @endif

                                <!-- Feedback Section -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light fs-11 text-muted">
                                    <span>Apakah jawaban ini membantu?</span>
                                    <span class="d-flex gap-3">
                                        <button class="btn btn-link p-0 fs-12 text-decoration-none fw-semibold" style="color: var(--brand-maroon);" onclick="alert('Terima kasih atas masukan Anda!'); return false;">
                                            <i class="bi bi-hand-thumbs-up me-1"></i>Ya, membantu
                                        </button>
                                        <button class="btn btn-link p-0 fs-12 text-decoration-none text-muted" onclick="alert('Terima kasih, kami akan menyempurnakan jawaban ini.'); return false;">
                                            <i class="bi bi-hand-thumbs-down me-1"></i>Belum
                                        </button>
                                    </span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="tag-soft">{{ $f['cat'] }}</span>
                                    <span class="fs-12 text-muted">Panduan resmi mahasiswa</span>
                                </div>
                                <p class="fs-13 text-secondary mb-3" style="line-height: 1.6;">
                                    Informasi lengkap terkait <strong>{{ strtolower($f['q']) }}</strong> dapat diakses melalui pengumuman resmi fakultas atau panduan terintegrasi KMS.
                                </p>
                                @if(($f['source_type'] ?? 'public') === 'public')
                                    <a href="{{ $f['source_url'] ?? '#' }}" class="source-box mb-2">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text fs-6 text-primary"></i>
                                            <span>{{ $f['source_label'] ?? 'Buka petunjuk teknis layanan akademik mahasiswa' }}</span>
                                        </span>
                                        <i class="bi bi-arrow-up-right fs-13"></i>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            <!-- Empty State if Search has 0 results -->
            <div id="faqEmpty" class="text-center text-muted fs-13 py-5 d-none">
                <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
                <div class="fw-semibold text-dark fs-14">Tidak ada pertanyaan yang cocok</div>
                <div class="fs-12 text-muted mt-1">Coba gunakan kata kunci pencarian yang lain atau pilih kategori di atas.</div>
            </div>
        </div>

        <!-- Official Disclaimer Callout -->
        <div class="disclaimer mt-3 d-flex align-items-center gap-2.5 shadow-sm">
            <span>FAQ ini merupakan panduan umum. Persyaratan dan jadwal resmi mengikuti pengumuman program studi atau fakultasmu.</span>
        </div>
    </section>

    <!-- ============================ CTA BANNER ============================ -->
    <section class="cta-card mt-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <div class="cta-icon">
                <i class="bi bi-chat-square-text fs-4"></i>
            </div>
            <div>
                <div class="display-serif fs-4 fw-bold text-dark">Belum menemukan jawabanmu?</div>
                <div class="fs-13 text-muted">Sampaikan pertanyaanmu ke tim akademik. Kami bantu arahkan ke layanan yang tepat.</div>
            </div>
        </div>
        <a href="#" class="btn-maroon px-4 py-2.5 fs-13">
            <span>Hubungi layanan akademik</span>
            <i class="bi bi-arrow-up-right"></i>
        </a>
    </section>
</main>

<!-- ========================================================================= -->
<!-- FOOTER (FULL WIDTH)                                                       -->
<!-- ========================================================================= -->
<footer class="mhs-footer">
    <div class="mhs-container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 border-bottom border-light">

            <div class="d-flex gap-4">
                <a href="#" class="footer-link">Tentang KMS</a>
                <a href="#" class="footer-link">Panduan penggunaan</a>
                <a href="#" class="footer-link">Kebijakan privasi</a>
            </div>

            <span class="footer-link d-inline-flex align-items-center gap-1.5 cursor-pointer">
                <i class="bi bi-globe2"></i>
                <span>Bahasa Indonesia</span>
                <i class="bi bi-chevron-down fs-11 ms-0.5"></i>
            </span>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3 fs-12 text-muted">
            <span>&copy; {{ date('Y') }} KMS &middot; Knowledge Management System</span>
        </div>
    </div>
</footer>

<!-- POP UP TAMPILAN HITUNG MUNDUR END SESSION -->
<div
    class="modal fade"
    id="sessionTimeoutModal"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby="sessionTimeoutModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered" style="max-width: 430px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                <!-- Icon -->
                <div
                    class="d-inline-flex align-items-center justify-content-center mb-3"
                    style="width: 56px; height: 56px; border-radius: 14px; background-color: #FCEEEF; color: #85172C;"
                >
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>

                <!-- Title -->
                <h5 id="sessionTimeoutModalLabel" class="display-serif fw-bold text-dark mb-2" style="font-size: 1.35rem;">
                    Apakah Anda masih ingin melanjutkan?
                </h5>

                <!-- Subtitle -->
                <p class="text-muted fs-13 mb-3" style="line-height: 1.55;">
                    Tidak ada aktivitas terdeteksi selama {{ $idleTimeoutSeconds ?? 30 }} detik. Demi keamanan dan kenyamanan bersama, sesi akan berakhir otomatis dalam:
                </p>

                <!-- Countdown Box -->
                <div
                    class="py-2.5 px-3 mb-3 mx-auto d-inline-flex align-items-center gap-2"
                    style="background-color: #FAF8F5; border: 1px solid #EBE6DF; border-radius: 999px;"
                >
                    <span class="fw-bold" style="color: #85172C; font-size: 1.05rem;" id="idleCountdownNumber">{{ $idleCountdownSeconds ?? 10 }}</span>
                    <span class="text-muted fs-12 fw-medium">detik tersisa</span>
                </div>

                <!-- Visual Progress Bar -->
                <div class="progress mb-4" style="height: 6px; border-radius: 999px; background-color: #F1F5F9;">
                    <div
                        id="idleCountdownBar"
                        class="progress-bar"
                        role="progressbar"
                        style="width: 100%; background-color: #85172C; transition: width 1s linear;"
                        aria-valuenow="{{ $idleCountdownSeconds ?? 10 }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $idleCountdownSeconds ?? 10 }}"
                    ></div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-light border flex-fill py-2 fw-semibold fs-13"
                        style="border-radius: 10px; color: #334155;"
                        onclick="endPortalSessionNow()"
                    >
                        Tidak, Akhiri Sesi
                    </button>
                    <button
                        type="button"
                        class="btn flex-fill py-2 fw-semibold fs-13 text-white"
                        style="border-radius: 10px; background-color: #85172C;"
                        onclick="continueActiveSession()"
                    >
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Tersembunyi untuk Mengakhiri Sesi dan Kembali ke Portal -->
<form id="endPortalSessionForm" action="{{ route('portal.end-session') }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Filter & Search Script -->
<script>
    let activeCat = 'top';
    const faqLogUrl = "{{ route('faq.log-access') }}";
    const csrfToken = "{{ csrf_token() }}";
    const viewedFaqIds = new Set();

    function updateFaqStatDom(faqId, counts) {
        if (!faqId || !counts) return;
        document.querySelectorAll(`[data-stat-views="${faqId}"]`).forEach(el => { el.textContent = counts.views ?? 0; });
        document.querySelectorAll(`[data-stat-likes="${faqId}"]`).forEach(el => { el.textContent = counts.likes ?? 0; });
        document.querySelectorAll(`[data-stat-dislikes="${faqId}"]`).forEach(el => { el.textContent = counts.dislikes ?? 0; });
        document.querySelectorAll(`.faq-item[data-faq-id="${faqId}"]`).forEach(el => { el.dataset.views = counts.views ?? 0; });
    }

    function logFaqInteraction(faqId, jenisInteraksi = 'view', keyword = '', hasil = 1) {
        if (!faqId) return;
        if (jenisInteraksi === 'view') {
            if (viewedFaqIds.has(faqId)) return;
            viewedFaqIds.add(faqId);
        }

        fetch(faqLogUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                faq_id: faqId,
                jenis_interaksi: jenisInteraksi,
                keyword: keyword || (document.getElementById('faqSearch')?.value || '').trim() || 'buka-faq',
                hasil: hasil
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res && res.counts) {
                updateFaqStatDom(faqId, res.counts);
            }
        })
        .catch(() => {});
    }

    function submitFaqFeedback(faqId, hasil, btnEl) {
        const jenis = hasil === 1 ? 'like' : 'dislike';
        logFaqInteraction(faqId, jenis, hasil === 1 ? 'feedback-membantu' : 'feedback-belum', hasil);
        if (btnEl && btnEl.parentElement) {
            btnEl.parentElement.innerHTML = '<span class="text-success fw-semibold fs-12"><i class="bi bi-check-circle me-1"></i>Terima kasih atas masukanmu!</span>';
        }
    }

    // Catat 1x view setiap kali mahasiswa membuka jawaban FAQ (accordion)
    const faqListEl = document.getElementById('faqList');
    if (faqListEl) {
        faqListEl.addEventListener('shown.bs.collapse', function (e) {
            const faqId = e.target?.dataset?.faqId;
            if (faqId) {
                logFaqInteraction(faqId, 'view', '', 1);
            }
        });
    }

    function setCategory(key, btn) {
        activeCat = key;
        document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterFaq();
    }

    function quickSearch(term) {
        document.getElementById('faqSearch').value = term;
        filterFaq();
    }

    function filterFaq() {
        const q = (document.getElementById('faqSearch').value || '').toLowerCase().trim();
        let visible = 0;

        const items = Array.from(document.querySelectorAll('#faqList .faq-item'));
        items.sort((a, b) => {
            if (activeCat === 'top') {
                const viewDiff = Number(b.dataset.views || 0) - Number(a.dataset.views || 0);
                if (viewDiff !== 0) return viewDiff;
            }
            return Number(a.dataset.rank || 0) - Number(b.dataset.rank || 0);
        });

        items.forEach(item => {
            if (faqListEl) faqListEl.appendChild(item);
            const itemCats = (item.dataset.cat || '').split(' ');
            const matchCat = activeCat === 'top' || itemCats.includes(activeCat);
            const matchText = !q || (item.dataset.text || '').includes(q);
            const show = matchCat && matchText;
            item.classList.toggle('d-none', !show);
            if (show) {
                visible++;
                const numEl = item.querySelector('.faq-num');
                if (numEl) numEl.textContent = String(visible).padStart(2, '0');
            }
        });

        const emptyEl = document.getElementById('faqEmpty');
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', visible !== 0);
        }

        const countEl = document.getElementById('faqCount');
        if (countEl) {
            const label = activeCat === 'top'
                ? 'Top FAQ'
                : (activeCat === 'rekomendasi' ? 'Rekomendasi FAQ' : 'Hasil kategori');
            countEl.innerText = label + ' · ' + visible + ' pertanyaan';
        }
    }

    filterFaq();

    document.getElementById('faqSearch').addEventListener('input', filterFaq);

    // =========================================================================
    // SISTEM IDLE TIMEOUT & POP-UP HITUNG MUNDUR (DINAMIS DARI TABEL TSETTINGS)
    // =========================================================================
    const IDLE_LIMIT_SECONDS     = {{ (int) ($idleTimeoutSeconds ?? 30) }};
    const POPUP_COUNTDOWN_SEC    = {{ (int) ($idleCountdownSeconds ?? 10) }};

    let idleTimeoutTimer         = null;
    let popupCountdownInterval   = null;
    let remainingPopupSeconds    = POPUP_COUNTDOWN_SEC;
    let isTimeoutModalShown      = false;
    let timeoutModalInstance     = null;

    const modalEl                = document.getElementById('sessionTimeoutModal');
    const countdownNumberEl      = document.getElementById('idleCountdownNumber');
    const countdownBarEl         = document.getElementById('idleCountdownBar');
    const endSessionForm         = document.getElementById('endPortalSessionForm');

    if (modalEl && typeof bootstrap !== 'undefined') {
        timeoutModalInstance = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });
    }

    /**
     * Mereset timer inaktivitas 30 detik setiap kali pengguna menggerakkan kursor,
     * klik, mengetik, atau melakukan scroll (selama pop-up belum muncul).
     */
    function resetIdleTimer() {
        if (isTimeoutModalShown) {
            return; // Jika pop-up sudah muncul, user wajib memilih tombol pada pop-up
        }

        if (idleTimeoutTimer) {
            clearTimeout(idleTimeoutTimer);
        }

        idleTimeoutTimer = setTimeout(showSessionTimeoutPopup, IDLE_LIMIT_SECONDS * 1000);
    }

    /**
     * Menampilkan pop-up konfirmasi setelah 30 detik tidak ada aktivitas
     * dan memulai hitung mundur 10 detik.
     */
    function showSessionTimeoutPopup() {
        isTimeoutModalShown = true;
        remainingPopupSeconds = POPUP_COUNTDOWN_SEC;
        updateCountdownDisplay(remainingPopupSeconds);

        if (timeoutModalInstance) {
            timeoutModalInstance.show();
        }

        if (popupCountdownInterval) {
            clearInterval(popupCountdownInterval);
        }

        popupCountdownInterval = setInterval(() => {
            remainingPopupSeconds -= 1;
            updateCountdownDisplay(remainingPopupSeconds);

            if (remainingPopupSeconds <= 0) {
                clearInterval(popupCountdownInterval);
                endPortalSessionNow();
            }
        }, 1000);
    }

    /**
     * Memperbarui angka dan progress bar hitung mundur 10 detik pada pop-up
     */
    function updateCountdownDisplay(sec) {
        const safeSec = Math.max(0, sec);
        if (countdownNumberEl) {
            countdownNumberEl.textContent = safeSec;
        }
        if (countdownBarEl) {
            const pct = (safeSec / POPUP_COUNTDOWN_SEC) * 100;
            countdownBarEl.style.width = pct + '%';
        }
    }

    /**
     * Dipanggil saat pengguna menekan tombol "Ya, Lanjutkan"
     */
    function continueActiveSession() {
        if (popupCountdownInterval) {
            clearInterval(popupCountdownInterval);
            popupCountdownInterval = null;
        }

        if (timeoutModalInstance) {
            timeoutModalInstance.hide();
        }

        isTimeoutModalShown = false;
        resetIdleTimer();
    }

    /**
     * Dipanggil saat pengguna menekan tombol "Tidak, Akhiri Sesi"
     * atau ketika waktu 10 detik pada pop-up habis tanpa aktivitas/respon
     */
    function endPortalSessionNow() {
        if (popupCountdownInterval) {
            clearInterval(popupCountdownInterval);
            popupCountdownInterval = null;
        }
        if (idleTimeoutTimer) {
            clearTimeout(idleTimeoutTimer);
            idleTimeoutTimer = null;
        }

        if (endSessionForm) {
            endSessionForm.submit();
        } else {
            window.location.href = "{{ route('portal') }}";
        }
    }

    // Daftarkan event listener aktivitas pengguna (kursor, klik, ketik, scroll, sentuh layar)
    ['mousemove', 'mousedown', 'click', 'keydown', 'scroll', 'touchstart'].forEach(evtName => {
        window.addEventListener(evtName, resetIdleTimer, { passive: true });
    });

    // Mulai penghitung inaktivitas 30 detik saat halaman FAQ dibuka
    resetIdleTimer();
</script>

</body>
</html>

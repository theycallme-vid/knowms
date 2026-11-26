<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Tamu - KMS (Knowledge Management System)</title>
    <meta name="description" content="Pusat pengetahuan KMS untuk Pengunjung / Guest: temukan informasi akademik, pendaftaran, beasiswa, dan panduan umum kampus tanpa perlu scan KTM atau NRP.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Guest FAQ CSS (Full Width) -->
    <link rel="stylesheet" href="{{ asset('css/guest/style.css') }}?v={{ file_exists(public_path('css/guest/style.css')) ? filemtime(public_path('css/guest/style.css')) : time() }}">
</head>
<body>

@php
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI KATEGORI FAQ UNTUK GUEST
    |--------------------------------------------------------------------------
    | Pada halaman Guest, kategori "Fakultas Teknik" digantikan dengan "Guest",
    | karena guest tidak terikat pada satu fakultas spesifik.
    */
    $categories = [
        ['key' => 'top',       'label' => 'FAQ Populer',          'icon' => 'bi-stars'],
        ['key' => 'umum',      'label' => 'Kategori umum',        'icon' => 'bi-grid'],
        ['key' => 'akademik',  'label' => 'Akademik',             'icon' => 'bi-book'],
        ['key' => 'biaya',     'label' => 'Biaya & beasiswa',     'icon' => 'bi-wallet2'],
        ['key' => 'praktikum', 'label' => 'Praktikum',            'icon' => 'bi-eyedropper'],
        ['key' => 'tugas',     'label' => 'Tugas akhir',          'icon' => 'bi-mortarboard'],
    ];

    $resolveCategoryKeys = function ($dbFaq) {
        $tagNames = $dbFaq->tags->pluck('nama')->toArray();
        $tagString = strtolower(implode(' ', $tagNames));
        $qLower = strtolower($dbFaq->pertanyaan);
        $keys = ['top'];

        // Kategori Umum
        if (
            str_contains($tagString, 'umum') ||
            str_contains($qLower, 'umum') ||
            str_contains($qLower, 'seluruh mahasiswa') ||
            str_contains($qLower, 'registrasi ulang')
        ) {
            $keys[] = 'umum';
        }

        // Kategori Akademik
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
            str_contains($qLower, 'akademik')
        ) {
            $keys[] = 'akademik';
        }

        // Kategori Biaya & Beasiswa
        if (
            str_contains($tagString, 'biaya') ||
            str_contains($tagString, 'beasiswa') ||
            str_contains($qLower, 'biaya') ||
            str_contains($qLower, 'ukt') ||
            str_contains($qLower, 'cicilan') ||
            str_contains($qLower, 'beasiswa') ||
            str_contains($qLower, 'keuangan')
        ) {
            $keys[] = 'biaya';
        }

        // Kategori Praktikum
        if (
            str_contains($tagString, 'praktikum') ||
            str_contains($tagString, 'laboratorium') ||
            str_contains($tagString, 'fasilitas') ||
            str_contains($qLower, 'praktikum') ||
            str_contains($qLower, 'laboratorium') ||
            str_contains($qLower, 'lab') ||
            str_contains($qLower, 'alat') ||
            str_contains($qLower, 'k3')
        ) {
            $keys[] = 'praktikum';
        }

        // Kategori Tugas Akhir
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
            str_contains($qLower, 'turnitin')
        ) {
            $keys[] = 'tugas';
        }

        return array_values(array_unique($keys));
    };

    $faqs = [
        ['id' => 1, 'q' => 'Bagaimana cara mengisi dan mengajukan KRS?', 'cat' => 'KRS & perkuliahan', 'keys' => ['top', 'akademik', 'umum'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.', 'source_url' => '#'],
        ['id' => 2, 'q' => 'Apa yang perlu disiapkan untuk registrasi ulang?', 'cat' => 'Administrasi akademik', 'keys' => ['top', 'umum', 'akademik'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa', 'source_url' => '#'],
        ['id' => 3, 'q' => 'Bagaimana prosedur pengajuan cuti kuliah?', 'cat' => 'Administrasi akademik', 'keys' => ['top', 'umum', 'akademik'], 'a' => null, 'source_type' => 'private', 'source_label' => 'SOP Internal Pengajuan Cuti Kuliah', 'source_url' => '#'],
        ['id' => 4, 'q' => 'Di mana saya bisa melihat hasil studi (KHS)?', 'cat' => 'KRS & perkuliahan', 'keys' => ['top', 'akademik'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa', 'source_url' => '#'],
        ['id' => 5, 'q' => 'Bagaimana cara mengajukan beasiswa?', 'cat' => 'Biaya & beasiswa', 'keys' => ['top', 'biaya'], 'a' => null, 'source_type' => 'public', 'source_label' => 'Buka panduan pengajuan beasiswa mahasiswa', 'source_url' => '#'],
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
            $assignedKeys = $resolveCategoryKeys($dbFaq);
            $mappedDbFaqs[] = [
                'id'             => $idx + 1,
                'faq_id'         => $dbFaq->id,
                'q'              => $dbFaq->pertanyaan,
                'a'              => $activeVer?->jawaban,
                'cat'            => $tagNames ?: 'Panduan Akademik',
                'keys'           => $assignedKeys,
                'source_type'    => $sourceType,
                'source_label'   => $sourceLabel,
                'source_url'     => $sourceUrl,
                'access_count'   => (int) ($dbFaq->views_count ?? $dbFaq->total_access_count ?? 0),
                'views_count'    => (int) ($dbFaq->views_count ?? $dbFaq->total_access_count ?? 0),
                'likes_count'    => (int) ($dbFaq->likes_count ?? 0),
                'dislikes_count' => (int) ($dbFaq->dislikes_count ?? 0),
            ];
        }
        $faqs = $mappedDbFaqs;
    }
@endphp

<!-- ========================================================================= -->
<!-- NAVBAR (FULL WIDTH + DOCKED SEARCH SLOT)                                  -->
<!-- ========================================================================= -->
<nav class="guest-navbar" id="faqTopNavbar">
    <div class="guest-container d-flex align-items-center justify-content-between gap-3 py-2">
        <!-- Left: Logo KMS + Sticky Search Bar Slot -->
        <div class="navbar-brand-search-group">
            <a href="{{ route('guest.faq') }}" class="d-flex align-items-center text-decoration-none flex-shrink-0">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="navbar-logo-img">
            </a>
            @include('partials.faq-google-search', ['part' => 'navbar'])
        </div>

        <!-- Right: Nav Links & Notifications -->
        <div class="d-flex align-items-center gap-3 flex-shrink-0">
            <div class="guest-nav-links-wrap d-none d-md-flex align-items-center gap-1">
                <a href="{{ url('/') }}" class="guest-nav-link">Beranda</a>
                <a href="{{ route('guest.faq') }}" class="guest-nav-link active">FAQ</a>
                <a href="#" class="guest-nav-link">Panduan akademik</a>
            </div>
            <button class="navbar-btn-icon" aria-label="Notifikasi" title="Notifikasi">
                <i class="bi bi-bell"></i>
            </button>
        </div>
    </div>
</nav>

<!-- ========================================================================= -->
<!-- HERO SECTION: GOOGLE-LIKE FULL-SCREEN SEARCH                              -->
<!-- ========================================================================= -->
<header class="hero-section hero-fullscreen">
    <div class="guest-container hero-inner">
        <div class="hero-center-stack">
            <div class="hero-context-badge mb-3">
                <i class="bi bi-globe2"></i>
                <span>Guest</span>
                <span class="hero-badge-muted">&middot; Akses Publik &amp; Informasi Umum Kampus</span>
            </div>

            <div class="hero-eyebrow mb-2">
                PUSAT PENGETAHUAN GUEST
            </div>

            <h1 class="display-serif hero-title mb-3">
                Ada pertanyaan?<br>Temukan jawabannya di sini.
            </h1>

            <p class="hero-subtitle mb-4">
                Dari rencana studi hingga tugas akhir, cari informasi akademik yang kamu butuhkan secara instan.
            </p>

            <div id="heroSearchAnchor" class="hero-search-anchor">
                @include('partials.faq-google-search', ['part' => 'bar'])
            </div>

            <!-- Quick Keywords -->
            <div class="hero-quick-chips">
                <span class="fs-12 text-muted me-1">Sering dicari:</span>
                <a href="#" class="suggest-pill" onclick="quickSearch('KRS'); return false;">KRS</a>
                <a href="#" class="suggest-pill" onclick="quickSearch('registrasi ulang'); return false;">Registrasi ulang</a>
                <a href="#" class="suggest-pill" onclick="quickSearch('cuti'); return false;">Cuti kuliah</a>
                <a href="#" class="suggest-pill" onclick="quickSearch('beasiswa'); return false;">Beasiswa</a>
                <a href="#" class="suggest-pill" onclick="quickSearch('tugas akhir'); return false;">Tugas akhir</a>
            </div>
        </div>
    </div>

    <button type="button"
            class="hero-scroll-cue"
            onclick="document.getElementById('faqExploreCategoriesSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' })">
        <span>Jelajahi kategori &amp; FAQ populer</span>
        <i class="bi bi-chevron-down"></i>
    </button>
</header>

<!-- ========================================================================= -->
<!-- MAIN CONTENT AREA (FULL WIDTH CONTAINER)                                  -->
<!-- ========================================================================= -->
<main class="guest-container py-5">

    <!-- ============================ KATEGORI (PATEN DI ATAS) ============================ -->
    <section id="faqExploreCategoriesSection" class="mb-5" style="scroll-margin-top: 88px;" aria-label="Jelajahi kategori">
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

    @include('partials.faq-google-search', ['part' => 'results'])

    <!-- ============================ DAFTAR FAQ (ISI KATEGORI) ============================ -->
    <section id="faqCategoryListSection" aria-labelledby="faqHeading" class="mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3.5">
            <div>
                <h2 id="faqHeading" class="display-serif fs-2 mb-1">Mulai dari yang sering ditanyakan</h2>
                <p class="text-muted fs-13 mb-0">Lima pertanyaan populer mahasiswa. Jawaban praktis, tanpa perlu mencari jauh.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="tag-soft shadow-sm" title="Sinkronisasi otomatis setiap 30 detik saat ada aktivitas">
                    <i class="bi bi-arrow-repeat"></i>
                    <span id="lastSyncLabel">Last synchronize {{ now()->format('H:i:s') }}</span>
                </span>
                <span class="tag-soft shadow-sm">
                    <i class="bi bi-stars"></i>
                    <span id="faqCount">FAQ Populer &middot; 5 pertanyaan</span>
                </span>
            </div>
        </div>

        <!-- Accordion FAQ List -->
        <div id="faqList" class="accordion mt-3" style="--bs-accordion-border-width:0;">
            @foreach($faqs as $f)
                @php
                    $catClasses = is_array($f['keys'] ?? null) ? implode(' ', $f['keys']) : ($f['key'] ?? 'akademik');
                    $realFaqId = $f['faq_id'] ?? ('FAQ-' . sprintf('%04d', $f['id']));
                @endphp
                <article class="faq-item" data-faq-id="{{ $realFaqId }}" data-cat="{{ $catClasses }}" data-text="{{ strtolower($f['q'].' '.$f['cat']) }}">
                    <button class="faq-toggle {{ $f['id'] === 1 ? '' : 'collapsed' }}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#faq{{ $f['id'] }}" 
                            aria-expanded="{{ $f['id'] === 1 ? 'true' : 'false' }}" 
                            aria-controls="faq{{ $f['id'] }}">
                        <span class="faq-num">{{ sprintf('%02d', $f['id']) }}</span>
                        <div class="flex-grow-1 text-start">
                            @if($f['id'] === 1)
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1.5">
                                    <span class="tag-soft">{{ $f['cat'] }}</span>
                                    <span class="fs-12 text-muted fw-normal">Panduan umum &middot; Untuk seluruh mahasiswa</span>
                                    <span class="fs-11 text-muted d-inline-flex align-items-center gap-2 ms-1">
                                        <span><i class="bi bi-eye me-1"></i><span data-stat-views="{{ $realFaqId }}">{{ $f['views_count'] ?? 0 }}</span> dilihat</span>
                                        <span>&middot;</span>
                                        <span class="text-success"><i class="bi bi-hand-thumbs-up me-1"></i><span data-stat-likes="{{ $realFaqId }}">{{ $f['likes_count'] ?? 0 }}</span></span>
                                        <span class="text-secondary"><i class="bi bi-hand-thumbs-down me-1"></i><span data-stat-dislikes="{{ $realFaqId }}">{{ $f['dislikes_count'] ?? 0 }}</span></span>
                                    </span>
                                </div>
                            @endif
                            <div class="faq-q">{{ $f['q'] }}</div>
                        </div>
                        @if($f['id'] !== 1)
                            <span class="faq-cat-label d-inline-flex align-items-center gap-2">
                                <span>{{ $f['cat'] }}</span>
                                <span class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-eye"></i> <span data-stat-views="{{ $realFaqId }}">{{ $f['views_count'] ?? 0 }}</span>
                                    &middot; <i class="bi bi-hand-thumbs-up text-success"></i> <span data-stat-likes="{{ $realFaqId }}">{{ $f['likes_count'] ?? 0 }}</span>
                                    &middot; <i class="bi bi-hand-thumbs-down text-secondary"></i> <span data-stat-dislikes="{{ $realFaqId }}">{{ $f['dislikes_count'] ?? 0 }}</span>
                                </span>
                            </span>
                        @endif
                        <span class="faq-icon">
                            <i class="bi bi-plus-lg faq-icon-plus"></i>
                            <i class="bi bi-dash-lg faq-icon-minus"></i>
                        </span>
                    </button>

                    <div id="faq{{ $f['id'] }}" class="collapse {{ $f['id'] === 1 ? 'show' : '' }}" data-faq-id="{{ $realFaqId }}" data-bs-parent="#faqList">
                        <div class="faq-body pt-1">
                            @if(!empty($f['a']))
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="tag-soft">{{ $f['cat'] }}</span>
                                    <span class="fs-12 text-muted">Panduan resmi mahasiswa</span>
                                </div>
                                <div class="fs-13 text-secondary mb-3" style="line-height: 1.65;">
                                    {!! $f['a'] !!}
                                </div>
                                @if(!empty($f['source_label']))
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

    <!-- ============================ CTA BANNER UNTUK GUEST ============================ -->
    <section class="cta-card mt-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <div class="cta-icon">
                <i class="bi bi-info-circle fs-4"></i>
            </div>
            <div>
                <div class="display-serif fs-4 fw-bold text-dark">Butuh Bantuan Layanan Akademik?</div>
                <div class="fs-13 text-muted">Layanan kontak dan konsultasi akademik langsung hanya tersedia untuk mahasiswa aktif. Silakan masuk melalui portal mahasiswa.</div>
            </div>
        </div>
        <a href="{{ route('portal') }}" class="btn-maroon px-4 py-2.5 fs-13 text-decoration-none">
            <span>Masuk Portal Mahasiswa</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </section>
</main>

<!-- ========================================================================= -->
<!-- FOOTER (FULL WIDTH)                                                       -->
<!-- ========================================================================= -->
<footer class="guest-footer">
    <div class="guest-container py-4">
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
            <span class="fst-italic">Pengetahuan terhubung. Perjalanan studi lebih terarah.</span>
        </div>
    </div>
</footer>

<!-- POP UP TAMPILAN HITUNG MUNDUR END SESSION GUEST -->
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
                        onclick="endGuestSessionNow()"
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

<!-- Form Tersembunyi untuk Mengakhiri Sesi Guest dan Kembali ke Portal -->
<form id="endGuestSessionForm" action="{{ route('guest.end-session') }}" method="POST" class="d-none">
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
    const sessionFeedbackMap = new Map();
    const pendingSyncQueue = new Map();
    const AUTO_SYNC_INTERVAL_MS = 30000;

    function formatSyncTime() {
        return new Date().toTimeString().slice(0, 8);
    }

    function setLastSyncText(timeStr) {
        const el = document.getElementById('lastSyncLabel');
        if (el) el.textContent = 'Last synchronize ' + (timeStr || formatSyncTime());
    }

    function updateFaqStatDom(faqId, counts) {
        if (!faqId || !counts) return;
        document.querySelectorAll(`[data-stat-views="${faqId}"]`).forEach(el => { el.textContent = counts.views ?? 0; });
        document.querySelectorAll(`[data-stat-likes="${faqId}"]`).forEach(el => { el.textContent = counts.likes ?? 0; });
        document.querySelectorAll(`[data-stat-dislikes="${faqId}"]`).forEach(el => { el.textContent = counts.dislikes ?? 0; });
    }

    function adjustFaqStatDomDelta(faqId, dViews, dLikes, dDislikes) {
        const readVal = attr => Number(document.querySelector(`[${attr}="${faqId}"]`)?.textContent || 0);
        updateFaqStatDom(faqId, {
            views: Math.max(0, readVal('data-stat-views') + dViews),
            likes: Math.max(0, readVal('data-stat-likes') + dLikes),
            dislikes: Math.max(0, readVal('data-stat-dislikes') + dDislikes)
        });
    }

    function logFaqInteraction(faqId, jenisInteraksi = 'view', keyword = '', hasil = 1) {
        if (!faqId) return;
        if (jenisInteraksi === 'view') {
            if (viewedFaqIds.has(faqId)) return;
            viewedFaqIds.add(faqId);
            adjustFaqStatDomDelta(faqId, 1, 0, 0);
            pendingSyncQueue.set(faqId + ':view', {
                faq_id: faqId,
                jenis_interaksi: 'view',
                keyword: keyword || (document.getElementById('faqSearch')?.value || '').trim() || 'buka-faq-guest',
                hasil: 1
            });
        } else {
            const prev = sessionFeedbackMap.get(faqId);
            if (prev === jenisInteraksi) return;
            sessionFeedbackMap.set(faqId, jenisInteraksi);
            const dLikes = (jenisInteraksi === 'like' ? 1 : 0) - (prev === 'like' ? 1 : 0);
            const dDislikes = (jenisInteraksi === 'dislike' ? 1 : 0) - (prev === 'dislike' ? 1 : 0);
            adjustFaqStatDomDelta(faqId, 0, dLikes, dDislikes);
            pendingSyncQueue.set(faqId + ':feedback', {
                faq_id: faqId,
                jenis_interaksi: jenisInteraksi,
                keyword: keyword || (jenisInteraksi === 'like' ? 'feedback-membantu' : 'feedback-belum'),
                hasil: hasil
            });
        }
    }

    function flushFaqSyncQueue(isUnload = false) {
        // Hanya sinkronisasi jika komputer ini punya aktivitas yang dikirim (jika idle / antrean kosong, tidak hit server)
        if (pendingSyncQueue.size === 0) return;

        const items = Array.from(pendingSyncQueue.values());
        pendingSyncQueue.clear();
        const payload = JSON.stringify({ _token: csrfToken, items: items });

        if (isUnload && navigator.sendBeacon) {
            navigator.sendBeacon(faqLogUrl, new Blob([payload], { type: 'application/json' }));
            setLastSyncText();
            return;
        }

        fetch(faqLogUrl, {
            method: 'POST',
            keepalive: isUnload,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: payload
        })
        .then(r => r.json())
        .then(res => {
            if (res && res.counts_map) {
                Object.entries(res.counts_map).forEach(([id, counts]) => updateFaqStatDom(id, counts));
            }
            setLastSyncText(res?.synced_at);
        })
        .catch(() => {});
    }

    setInterval(() => flushFaqSyncQueue(false), AUTO_SYNC_INTERVAL_MS);
    window.addEventListener('pagehide', () => flushFaqSyncQueue(true));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') flushFaqSyncQueue(true);
    });

    function submitFaqFeedback(faqId, hasil, btnEl) {
        const jenis = hasil === 1 ? 'like' : 'dislike';
        logFaqInteraction(faqId, jenis, hasil === 1 ? 'feedback-membantu' : 'feedback-belum', hasil);
        if (btnEl && btnEl.parentElement) {
            btnEl.parentElement.innerHTML = '<span class="text-success fw-semibold fs-12"><i class="bi bi-check-circle me-1"></i>Terima kasih atas masukanmu!</span>';
        }
    }

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
        if (typeof window.clearFaqSearch === 'function') {
            window.clearFaqSearch();
        }
        activeCat = key;
        document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterFaq();
    }

    function filterFaq() {
        let visible = 0;
        
        document.querySelectorAll('#faqList .faq-item').forEach(item => {
            const itemCats = (item.dataset.cat || '').split(' ');
            const show = activeCat === 'top' || itemCats.includes(activeCat);
            item.classList.toggle('d-none', !show);
            if (show) visible++;
        });

        const emptyEl = document.getElementById('faqEmpty');
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', visible !== 0);
        }

        const countEl = document.getElementById('faqCount');
        if (countEl) {
            countEl.innerText = (activeCat === 'top' ? 'Top FAQ' : 'Hasil kategori') + ' · ' + visible + ' pertanyaan';
        }
    }

    // =========================================================================
    // SISTEM IDLE TIMEOUT & POP-UP HITUNG MUNDUR (GUEST - DARI TSETTINGS)
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
    const endSessionForm         = document.getElementById('endGuestSessionForm');

    if (modalEl && typeof bootstrap !== 'undefined') {
        timeoutModalInstance = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });
    }

    /**
     * Mereset timer inaktivitas 30 detik setiap kali pengguna berinteraksi
     */
    function resetIdleTimer() {
        if (isTimeoutModalShown) {
            return;
        }

        if (idleTimeoutTimer) {
            clearTimeout(idleTimeoutTimer);
        }

        idleTimeoutTimer = setTimeout(showSessionTimeoutPopup, IDLE_LIMIT_SECONDS * 1000);
    }

    /**
     * Menampilkan pop-up konfirmasi setelah 30 detik idle
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
                endGuestSessionNow();
            }
        }, 1000);
    }

    /**
     * Memperbarui angka dan progress bar hitung mundur
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
     * atau saat hitung mundur 10 detik berakhir
     */
    function endGuestSessionNow() {
        if (popupCountdownInterval) {
            clearInterval(popupCountdownInterval);
            popupCountdownInterval = null;
        }
        if (idleTimeoutTimer) {
            clearTimeout(idleTimeoutTimer);
            idleTimeoutTimer = null;
        }

        flushFaqSyncQueue(true);

        if (endSessionForm) {
            endSessionForm.submit();
        } else {
            window.location.href = "{{ route('portal') }}";
        }
    }

    // Daftarkan event listener aktivitas pengguna
    ['mousemove', 'mousedown', 'click', 'keydown', 'scroll', 'touchstart'].forEach(evtName => {
        window.addEventListener(evtName, resetIdleTimer, { passive: true });
    });

    // Mulai timer inaktivitas saat halaman dibuka
    resetIdleTimer();
</script>

@include('partials.faq-google-search', ['part' => 'script'])

</body>
</html>

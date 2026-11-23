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
        ['key' => 'top',         'label' => 'FAQ Populer',          'icon' => 'bi-stars'],
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
        [
            'id' => 1,
            'faq_id' => 'FAQ-0001',
            'q' => 'Bagaimana cara mengisi dan mengajukan KRS?',
            'cat' => 'KRS & perkuliahan',
            'keys' => ['top', 'rekomendasi', 'akademik', 'umum'],
            'a' => 'Pengisian KRS dilakukan secara daring melalui portal akademik mahasiswa sesuai jadwal perwalian. Pilih mata kuliah yang akan diambil berdasarkan jatah SKS, lalu ajukan persetujuan ke Dosen Wali.',
            'source_label' => 'Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.',
            'source_url' => '#',
            'versions_history' => [
                [
                    'version_id' => 'FAQ-0001-V1',
                    'version_label' => 'Versi 1',
                    'is_latest' => true,
                    'updated_at' => '-',
                    'jawaban' => 'Pengisian KRS dilakukan secara daring melalui portal akademik mahasiswa sesuai jadwal perwalian. Pilih mata kuliah yang akan diambil berdasarkan jatah SKS, lalu ajukan persetujuan ke Dosen Wali.',
                    'tags' => ['KRS', 'Perkuliahan', 'Akademik'],
                    'source_label' => 'Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.',
                    'source_url' => '#',
                    'source_is_file' => false,
                ],
            ],
        ],
        [
            'id' => 2,
            'faq_id' => 'FAQ-0002',
            'q' => 'Apa yang perlu disiapkan untuk registrasi ulang?',
            'cat' => 'Administrasi akademik',
            'keys' => ['top', 'rekomendasi', 'umum', 'akademik'],
            'a' => 'Siapkan bukti pelunasan UKT semester berjalan, kartu hasil studi semester sebelumnya, dan pastikan status akun portal mahasiswa dalam keadaan aktif.',
            'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa',
            'source_url' => '#',
            'versions_history' => [
                [
                    'version_id' => 'FAQ-0002-V1',
                    'version_label' => 'Versi 1',
                    'is_latest' => true,
                    'updated_at' => '-',
                    'jawaban' => 'Siapkan bukti pelunasan UKT semester berjalan, kartu hasil studi semester sebelumnya, dan pastikan status akun portal mahasiswa dalam keadaan aktif.',
                    'tags' => ['Administrasi Akademik', 'Registrasi Ulang'],
                    'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa',
                    'source_url' => '#',
                    'source_is_file' => false,
                ],
            ],
        ],
        [
            'id' => 3,
            'faq_id' => 'FAQ-0003',
            'q' => 'Bagaimana prosedur pengajuan cuti kuliah?',
            'cat' => 'Administrasi akademik',
            'keys' => ['top', 'rekomendasi', 'umum', 'akademik'],
            'a' => 'Pengajuan cuti kuliah dilakukan melalui portal layanan akademik dengan persetujuan Dosen Pembimbing Akademik dan Dekan Fakultas sebelum batas waktu kalender akademik.',
            'source_label' => 'SOP Pengajuan Cuti Kuliah',
            'source_url' => '#',
            'versions_history' => [
                [
                    'version_id' => 'FAQ-0003-V1',
                    'version_label' => 'Versi 1',
                    'is_latest' => true,
                    'updated_at' => '-',
                    'jawaban' => 'Pengajuan cuti kuliah dilakukan melalui portal layanan akademik dengan persetujuan Dosen Pembimbing Akademik dan Dekan Fakultas sebelum batas waktu kalender akademik.',
                    'tags' => ['Administrasi Akademik', 'Cuti Kuliah'],
                    'source_label' => 'SOP Pengajuan Cuti Kuliah',
                    'source_url' => '#',
                    'source_is_file' => false,
                ],
            ],
        ],
        [
            'id' => 4,
            'faq_id' => 'FAQ-0004',
            'q' => 'Di mana saya bisa melihat hasil studi (KHS)?',
            'cat' => 'KRS & perkuliahan',
            'keys' => ['top', 'rekomendasi', 'akademik'],
            'a' => 'Kartu Hasil Studi (KHS) dapat dilihat dan diunduh langsung pada menu Hasil Studi di portal akademik mahasiswa setelah periode input nilai berakhir.',
            'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa',
            'source_url' => '#',
            'versions_history' => [
                [
                    'version_id' => 'FAQ-0004-V1',
                    'version_label' => 'Versi 1',
                    'is_latest' => true,
                    'updated_at' => '-',
                    'jawaban' => 'Kartu Hasil Studi (KHS) dapat dilihat dan diunduh langsung pada menu Hasil Studi di portal akademik mahasiswa setelah periode input nilai berakhir.',
                    'tags' => ['KRS', 'KHS', 'Akademik'],
                    'source_label' => 'Buka petunjuk teknis layanan akademik mahasiswa',
                    'source_url' => '#',
                    'source_is_file' => false,
                ],
            ],
        ],
        [
            'id' => 5,
            'faq_id' => 'FAQ-0005',
            'q' => 'Bagaimana cara mengajukan beasiswa?',
            'cat' => 'Biaya & beasiswa',
            'keys' => ['top', 'rekomendasi', 'biaya'],
            'a' => 'Pendaftaran beasiswa dibuka melalui Direktorat Kemahasiswaan sesuai jadwal tiap semester dengan melampirkan transkrip nilai dan berkas persyaratan.',
            'source_label' => 'Buka panduan pengajuan beasiswa mahasiswa',
            'source_url' => '#',
            'versions_history' => [
                [
                    'version_id' => 'FAQ-0005-V1',
                    'version_label' => 'Versi 1',
                    'is_latest' => true,
                    'updated_at' => '-',
                    'jawaban' => 'Pendaftaran beasiswa dibuka melalui Direktorat Kemahasiswaan sesuai jadwal tiap semester dengan melampirkan transkrip nilai dan berkas persyaratan.',
                    'tags' => ['Biaya', 'Beasiswa'],
                    'source_label' => 'Buka panduan pengajuan beasiswa mahasiswa',
                    'source_url' => '#',
                    'source_is_file' => false,
                ],
            ],
        ],
    ];

    if (isset($publishedFaqs) && $publishedFaqs->isNotEmpty()) {
        $mappedDbFaqs = [];
        foreach ($publishedFaqs as $idx => $dbFaq) {
            $activeVer = $dbFaq->versions->first();
            $pustaka = $activeVer?->daftarPustaka;
            $isFileSource = $pustaka ? !empty($pustaka->upload) : false;
            $sourceLabel = $pustaka ? ($pustaka->judul_tampil ?: $pustaka->link) : null;
            $sourceUrl = $pustaka
                ? ($isFileSource ? route('pustaka.lihat', $pustaka->id) : (filter_var($pustaka->link, FILTER_VALIDATE_URL) ? $pustaka->link : '#'))
                : '#';
            $tagNamesList = $dbFaq->tags->pluck('nama')->values()->all();
            $tagNames = implode(' · ', $tagNamesList);
            $assignedKeys = $resolveCategoryKeys($dbFaq, $activeNamaFakultas);

            $versionsHistory = [];
            foreach ($dbFaq->versions as $vIdx => $verItem) {
                $vPustaka = $verItem->daftarPustaka;
                $vIsFile = $vPustaka ? !empty($vPustaka->upload) : false;
                $vSourceLabel = $vPustaka ? ($vPustaka->judul_tampil ?: $vPustaka->link) : null;
                $vSourceUrl = $vPustaka
                    ? ($vIsFile ? route('pustaka.lihat', $vPustaka->id) : (filter_var($vPustaka->link, FILTER_VALIDATE_URL) ? $vPustaka->link : null))
                    : null;
                $vTs = $verItem->updated_at ?? $verItem->created_at;
                $versionsHistory[] = [
                    'version_id'     => $verItem->id,
                    'version_label'  => \App\Models\FaqVersion::formatVersiLabel($verItem->id),
                    'is_latest'      => ($vIdx === 0),
                    'updated_at'     => $vTs ? $vTs->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') . ' WIB' : '-',
                    'jawaban'        => $verItem->jawaban,
                    'tags'           => $tagNamesList,
                    'source_label'   => $vSourceLabel,
                    'source_url'     => $vSourceUrl,
                    'source_is_file' => $vIsFile,
                ];
            }

            $mappedDbFaqs[] = [
                'id'                   => $idx + 1,
                'faq_id'               => $dbFaq->id,
                'q'                    => $dbFaq->pertanyaan,
                'a'                    => $activeVer?->jawaban,
                'cat'                  => $tagNames ?: 'Panduan Akademik',
                'keys'                 => $assignedKeys,
                'source_label'         => $sourceLabel,
                'source_url'           => $sourceUrl,
                'versions_history'     => $versionsHistory,
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

    $faqHistoryPayload = [];
    foreach ($faqs as $itemFaq) {
        $fidKey = $itemFaq['faq_id'] ?? ('FAQ-' . sprintf('%04d', $itemFaq['id']));
        $faqHistoryPayload[$fidKey] = [
            'faq_id'   => $fidKey,
            'question' => $itemFaq['q'],
            'versions' => $itemFaq['versions_history'] ?? [],
        ];
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
        <!-- Right: Notifications & Profile -->
    </div>
</nav>

<!-- ========================================================================= -->
<!-- HERO SECTION & BACKGROUND BANNER (FULL WIDTH)                            -->
<!-- ========================================================================= -->
<header class="hero-section">
    <div class="mhs-container hero-inner">
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

                @include('partials.faq-google-search', ['part' => 'bar'])

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
                <p class="text-muted fs-13 mb-0">
                    @if(!empty($activeTahunAngkatan))
                        Rekomendasi FAQ terpopuler untuk {{ $activeNamaFakultas }} &middot; Angkatan {{ $activeTahunAngkatan }}{{ !empty($activeSemester) ? ' (Semester ' . $activeSemester . ')' : '' }}.
                    @else
                        Pertanyaan populer mahasiswa. Jawaban praktis, tanpa perlu mencari jauh.
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="tag-soft shadow-sm" title="Sinkronisasi otomatis setiap 30 detik saat ada aktivitas">
                    <i class="bi bi-arrow-repeat"></i>
                    <span id="lastSyncLabel">Last synchronize {{ now()->format('H:i:s') }}</span>
                </span>
                <span class="tag-soft shadow-sm">
                    <i class="bi bi-stars"></i>
                    <span id="faqCount">FAQ Populer &middot; {{ count($faqs) }} pertanyaan</span>
                </span>
            </div>
        </div>

        <!-- Area Daftar FAQ dengan Tombol Kiri (<) & Kanan (>) untuk Halaman Sebelumnya / Selanjutnya -->
        <div id="faqStageWrap" class="faq-stage-wrap mt-3">
            <div class="faq-side-nav-col d-none d-md-flex">
                <button type="button"
                        id="faqSidePrevBtn"
                        class="faq-side-nav-btn"
                        onclick="stepFaqPage(-1)"
                        aria-label="Halaman sebelumnya"
                        title="Halaman sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <!-- Accordion FAQ List -->
            <div id="faqList" class="accordion flex-grow-1" style="--bs-accordion-border-width:0; min-width: 0;">
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
                                {!! $f['a'] ?? ('Informasi lengkap terkait <strong>' . e(strtolower($f['q'])) . '</strong> dapat diakses melalui pengumuman resmi fakultas atau panduan terintegrasi KMS.') !!}
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

                            <!-- Baris Bawah: Pojok Kiri = Riwayat Versi Jawaban (Full Page), Kanan = Apakah jawaban ini membantu? + Tombol -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top border-light fs-11 text-muted">
                                <a href="{{ route('mahasiswa.faq.riwayat', $realFaqId) }}"
                                   class="btn-history-version text-decoration-none">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Riwayat Versi Jawaban</span>
                                </a>
                                {{-- [DINONAKTIFKAN SEMENTARA] Versi Pop-up Modal:
                                <button type="button"
                                        class="btn-history-version"
                                        onclick="openFaqHistoryModal('{{ $realFaqId }}'); return false;">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Riwayat Versi Jawaban</span>
                                </button>
                                --}}

                                <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
                                    <span class="text-muted fs-12">Apakah jawaban ini membantu?</span>
                                    <span class="d-inline-flex align-items-center gap-3">
                                        <button type="button" class="btn btn-link p-0 fs-12 text-decoration-none fw-semibold" style="color: var(--brand-maroon);" onclick="submitFaqFeedback('{{ $realFaqId }}', 1, this); return false;">
                                            <i class="bi bi-hand-thumbs-up me-1"></i>Ya, membantu
                                        </button>
                                        <button type="button" class="btn btn-link p-0 fs-12 text-decoration-none text-muted" onclick="submitFaqFeedback('{{ $realFaqId }}', 0, this); return false;">
                                            <i class="bi bi-hand-thumbs-down me-1"></i>Belum
                                        </button>
                                    </span>
                                </div>
                            </div>
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

            <div class="faq-side-nav-col d-none d-md-flex">
                <button type="button"
                        id="faqSideNextBtn"
                        class="faq-side-nav-btn"
                        onclick="stepFaqPage(1)"
                        aria-label="Halaman selanjutnya"
                        title="Halaman selanjutnya">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Navigasi Halaman (Top 10 per Halaman di Setiap Kategori) -->
        <div id="faqPagination" class="faq-pagination-bar mt-3 d-none"></div>

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
        <a href="{{ route('layanan.kontak') }}" class="btn-maroon px-4 py-2.5 fs-13 text-decoration-none">
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

<!-- ========================================================================= -->
<!-- MODAL POP-UP RIWAYAT VERSI JAWABAN (BESAR & MUDAH DIBACA)                 -->
<!-- ========================================================================= -->
<style>
    .faq-stage-wrap {
        display: flex;
        align-items: stretch;
        gap: 0.9rem;
    }
    .faq-stage-wrap.no-side-nav .faq-side-nav-col {
        display: none !important;
    }
    .faq-side-nav-col {
        width: 44px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
    }
    .faq-side-nav-btn {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 50%;
        background-color: #FFFFFF;
        border: 1px solid #E2E8F0;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        transition: all 0.18s ease;
    }
    .faq-side-nav-btn i {
        font-size: 1.05rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .faq-side-nav-btn:hover:not(:disabled) {
        background-color: #FDF7F8;
        border-color: #85172C;
        color: #85172C;
        box-shadow: 0 6px 16px rgba(133, 23, 44, 0.18);
    }
    .faq-side-nav-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
        box-shadow: none;
        background-color: #F8FAFC;
    }
    .btn-history-version {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.85rem;
        font-size: 0.76rem;
        font-weight: 600;
        color: #85172C;
        background-color: #FDF7F8;
        border: 1px solid #F3DCE0;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-history-version:hover {
        background-color: #FCECEF;
        border-color: #E5B8C0;
        color: #6B1122;
    }
    .faq-pagination-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 0.9rem 1.25rem;
        background-color: #FFFFFF;
        border: 1px solid #EAEAE4;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .faq-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        min-width: 36px;
        height: 36px;
        padding: 0 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        background-color: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .faq-page-btn:hover:not(:disabled):not(.active) {
        background-color: #F8FAFC;
        border-color: #94A3B8;
        color: #0F172A;
    }
    .faq-page-btn.active {
        background-color: #85172C;
        border-color: #85172C;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(133, 23, 44, 0.25);
    }
    .faq-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        background-color: #F8FAFC;
    }
    .history-acc-item {
        background-color: #FFFFFF;
        border: 1px solid #E5E0D8;
        border-radius: 13px;
        margin-bottom: 0.65rem;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .history-acc-item:last-child {
        margin-bottom: 0;
    }
    .history-acc-item.is-latest-version {
        border-color: #DFC0C7;
    }
    .history-acc-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.8rem 1.1rem;
        background-color: #FFFFFF;
        border: 0;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .history-acc-btn:hover {
        background-color: #FAF8F5;
    }
    .history-acc-btn:not(.collapsed) {
        background-color: #FDF9F9;
        border-bottom: 1px solid #EFECE6;
    }
    .history-acc-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
    }
    .history-acc-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #85172C;
        color: #FFFFFF;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.78rem;
        border-radius: 999px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .history-acc-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.69rem;
        font-weight: 700;
        padding: 0.24rem 0.62rem;
        border-radius: 999px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .history-acc-status.latest {
        background-color: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }
    .history-acc-status.archive {
        background-color: #F1F5F9;
        color: #64748B;
        border: 1px solid #E2E8F0;
    }
    .history-acc-time {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.76rem;
        color: #475569;
        font-weight: 500;
        white-space: nowrap;
    }
    .history-acc-time i {
        font-size: 0.76rem;
        line-height: 1;
        color: #94A3B8;
        flex-shrink: 0;
    }
    .history-acc-chevron {
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
    .history-acc-chevron i {
        font-size: 0.78rem;
        line-height: 1;
    }
    .history-acc-btn:not(.collapsed) .history-acc-chevron {
        transform: rotate(180deg);
        background-color: #FCECEF;
        color: #85172C;
    }
    .history-acc-body {
        padding: 1rem 1.15rem 1.1rem 1.15rem;
        background-color: #FFFFFF;
    }
    .history-section-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #85172C;
        margin-bottom: 0.4rem;
    }
    .history-answer-box {
        background-color: #FAF9F6;
        border: 1px solid #EAE5DD;
        border-radius: 10px;
        padding: 0.8rem 1rem;
        font-size: 0.84rem;
        line-height: 1.65;
        color: #1E293B;
        word-break: break-word;
    }
    .history-tag-pill {
        display: inline-flex;
        align-items: center;
        background-color: #F8F5F1;
        color: #85172C;
        border: 1px solid #EDE7E1;
        border-radius: 999px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 0.28rem 0.72rem;
    }
    .history-source-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        background-color: #EFF6FF;
        border: 1px solid #DBEAFE;
        border-radius: 10px;
        padding: 0.72rem 0.95rem;
    }
    .history-source-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        flex: 1 1 auto;
    }
    .history-source-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 9px;
        background-color: #DBEAFE;
        color: #1D4ED8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 1;
        font-size: 0.95rem;
    }
    .history-source-icon i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .history-source-text {
        min-width: 0;
        flex: 1 1 auto;
    }
    .history-source-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0F172A;
        line-height: 1.35;
        word-break: break-word;
    }
    .history-source-sub {
        font-size: 0.71rem;
        color: #64748B;
        margin-top: 0.1rem;
    }
    .history-source-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.75rem;
        font-size: 0.74rem;
        font-weight: 600;
        color: #334155;
        background-color: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .history-source-btn:hover {
        background-color: #F8FAFC;
        border-color: #94A3B8;
        color: #0F172A;
    }
    .history-source-btn i {
        font-size: 0.78rem;
        line-height: 1;
    }
    .history-source-empty {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        width: 100%;
        background-color: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 10px;
        padding: 0.65rem 0.9rem;
        font-size: 0.78rem;
        color: #64748B;
    }
    .history-source-empty i {
        font-size: 0.85rem;
        line-height: 1;
        flex-shrink: 0;
    }
</style>

{{-- =========================================================================
     [DINONAKTIFKAN SEMENTARA] MODAL POP-UP RIWAYAT VERSI JAWABAN
     Diganti ke halaman penuh (route: mahasiswa.faq.riwayat)
     =========================================================================
<div class="modal fade" id="faqHistoryModal" tabindex="-1" aria-labelledby="faqHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1020px; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header border-bottom" style="background-color: #FAF8F5; padding: 1.05rem 1.4rem;">
                <div class="pe-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge rounded-pill fw-bold" style="background-color: #85172C; color: #FFFFFF; font-size: 0.72rem; padding: 0.32rem 0.78rem;" id="historyModalFaqId">FAQ-0001</span>
                        <span class="badge rounded-pill" style="background-color: #F3EFEA; color: #475569; border: 1px solid #E2DDD5; font-size: 0.71rem; padding: 0.3rem 0.72rem;" id="historyModalVersionCount">1 Versi Jawaban</span>
                        <span class="text-muted" style="font-size: 0.74rem;">Riwayat Versi Jawaban</span>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="faqHistoryModalLabel" style="font-size: 0.96rem; line-height: 1.45;">
                        Riwayat Versi Jawaban
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" style="background-color: #F8F6F2; padding: 1.15rem 1.4rem 1.35rem 1.4rem;">
                <div class="text-muted mb-3" style="font-size: 0.76rem;">
                    <i class="bi bi-info-circle me-1"></i>Klik salah satu versi di bawah untuk melihat detail jawaban, kata kunci, dan sumber pustaka.
                </div>
                <div class="accordion" id="historyModalVersionsContainer"></div>
            </div>
        </div>
    </div>
</div>
--}}

<!-- Form Tersembunyi untuk Mengakhiri Sesi dan Kembali ke Portal -->
<form id="endPortalSessionForm" action="{{ route('portal.end-session') }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Filter & Search Script -->
<script>
    let activeCat = 'top';
    let currentPage = 1;
    const ITEMS_PER_PAGE = 10;
    const faqLogUrl = "{{ route('faq.log-access') }}";
    const csrfToken = "{{ csrf_token() }}";
    const viewedFaqIds = new Set();
    const sessionFeedbackMap = new Map();
    const pendingSyncQueue = new Map();
    const AUTO_SYNC_INTERVAL_MS = 30000;
    const faqHistoryData = @json($faqHistoryPayload ?? []);

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
        document.querySelectorAll(`.faq-item[data-faq-id="${faqId}"]`).forEach(el => { el.dataset.views = counts.views ?? 0; });
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
                keyword: keyword || (document.getElementById('faqSearch')?.value || '').trim() || 'buka-faq',
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

    /* [DINONAKTIFKAN SEMENTARA] Handler Pop-up Riwayat Versi Jawaban (diganti ke halaman penuh)
    function escHtmlMhs(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function openFaqHistoryModal(faqId) {
        const entry = faqHistoryData[faqId];
        const idEl = document.getElementById('historyModalFaqId');
        const countEl = document.getElementById('historyModalVersionCount');
        const titleEl = document.getElementById('faqHistoryModalLabel');
        const containerEl = document.getElementById('historyModalVersionsContainer');

        if (!containerEl) return;

        const questionText = entry?.question || 'Detail Pertanyaan FAQ';
        const rawVersions = Array.isArray(entry?.versions) && entry.versions.length > 0
            ? [...entry.versions]
            : [];

        const getVerNum = (ver) => {
            const m = String(ver?.version_id || ver?.version_label || '').match(/(\d+)\s*$/);
            return m ? parseInt(m[1], 10) : 1;
        };
        const versions = rawVersions.sort((a, b) => getVerNum(b) - getVerNum(a));

        if (idEl) idEl.textContent = faqId;
        if (countEl) countEl.textContent = `${versions.length} Versi Jawaban`;
        if (titleEl) titleEl.textContent = questionText;

        const modalEl = document.getElementById('faqHistoryModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
    */

    let lastTotalPages = 1;

    function setCategory(key, btn) {
        if (typeof window.clearFaqSearch === 'function') {
            window.clearFaqSearch();
        }
        activeCat = key;
        currentPage = 1;
        document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterFaq(true);
    }

    function stepFaqPage(delta) {
        const target = currentPage + delta;
        if (target < 1 || target > lastTotalPages) return;
        goToFaqPage(target);
    }

    function syncSideNavButtons(totalMatched, totalPages) {
        lastTotalPages = totalPages;
        const stageWrap = document.getElementById('faqStageWrap');
        const prevBtn = document.getElementById('faqSidePrevBtn');
        const nextBtn = document.getElementById('faqSideNextBtn');
        const hasMultiplePages = totalMatched > ITEMS_PER_PAGE;

        if (stageWrap) {
            stageWrap.classList.toggle('no-side-nav', !hasMultiplePages);
        }
        if (prevBtn) {
            prevBtn.disabled = (!hasMultiplePages || currentPage <= 1);
        }
        if (nextBtn) {
            nextBtn.disabled = (!hasMultiplePages || currentPage >= totalPages);
        }
    }

    function goToFaqPage(page) {
        currentPage = page;
        filterFaq(false);
        const headingEl = document.getElementById('faqHeading');
        if (headingEl) {
            headingEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function renderFaqPagination(totalMatched, totalPages, startIdx, endIdx) {
        const pagEl = document.getElementById('faqPagination');
        if (!pagEl) return;

        if (totalMatched <= ITEMS_PER_PAGE) {
            pagEl.classList.add('d-none');
            pagEl.innerHTML = '';
            return;
        }

        pagEl.classList.remove('d-none');
        const showingFrom = startIdx + 1;
        const showingTo = Math.min(endIdx, totalMatched);

        let pageButtonsHtml = `
            <button type="button" class="faq-page-btn" ${currentPage <= 1 ? 'disabled' : ''} onclick="goToFaqPage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
                <span>Sebelumnya</span>
            </button>
        `;

        for (let p = 1; p <= totalPages; p++) {
            pageButtonsHtml += `
                <button type="button" class="faq-page-btn ${p === currentPage ? 'active' : ''}" onclick="goToFaqPage(${p})">
                    ${p}
                </button>
            `;
        }

        pageButtonsHtml += `
            <button type="button" class="faq-page-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="goToFaqPage(${currentPage + 1})">
                <span>Selanjutnya</span>
                <i class="bi bi-chevron-right"></i>
            </button>
        `;

        pagEl.innerHTML = `
            <div class="fs-12 text-muted">
                Menampilkan <strong class="text-dark">${showingFrom}&ndash;${showingTo}</strong> dari <strong class="text-dark">${totalMatched}</strong> pertanyaan (Halaman ${currentPage} dari ${totalPages})
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                ${pageButtonsHtml}
            </div>
        `;
    }

    function filterFaq(resetPage = true) {
        if (resetPage) {
            currentPage = 1;
        }

        const items = Array.from(document.querySelectorAll('#faqList .faq-item'));

        items.sort((a, b) => {
            if (activeCat === 'top') {
                const viewDiff = Number(b.dataset.views || 0) - Number(a.dataset.views || 0);
                if (viewDiff !== 0) return viewDiff;
            }
            return Number(a.dataset.rank || 0) - Number(b.dataset.rank || 0);
        });

        const matchedItems = [];
        items.forEach(item => {
            if (faqListEl) faqListEl.appendChild(item);
            const itemCats = (item.dataset.cat || '').split(' ');
            const matchCat = activeCat === 'top' || itemCats.includes(activeCat);
            if (matchCat) {
                matchedItems.push(item);
            } else {
                item.classList.add('d-none');
            }
        });

        const totalMatched = matchedItems.length;
        const totalPages = Math.max(1, Math.ceil(totalMatched / ITEMS_PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIdx = startIdx + ITEMS_PER_PAGE;

        matchedItems.forEach((item, idx) => {
            const onCurrentPage = idx >= startIdx && idx < endIdx;
            item.classList.toggle('d-none', !onCurrentPage);
            if (onCurrentPage) {
                const numEl = item.querySelector('.faq-num');
                if (numEl) numEl.textContent = String(idx + 1).padStart(2, '0');
            } else {
                const collapseEl = item.querySelector('.collapse.show');
                if (collapseEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Collapse.getInstance(collapseEl)?.hide();
                }
            }
        });

        renderFaqPagination(totalMatched, totalPages, startIdx, endIdx);
        syncSideNavButtons(totalMatched, totalPages);

        const emptyEl = document.getElementById('faqEmpty');
        if (emptyEl) {
            emptyEl.classList.toggle('d-none', totalMatched !== 0);
        }

        const countEl = document.getElementById('faqCount');
        if (countEl) {
            const label = activeCat === 'top'
                ? 'FAQ Populer'
                : (activeCat === 'rekomendasi' ? 'Rekomendasi FAQ' : 'Hasil kategori');
            countEl.innerText = label + ' · ' + totalMatched + ' pertanyaan';
        }
    }

    filterFaq(true);

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

        flushFaqSyncQueue(true);

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

@include('partials.faq-google-search', ['part' => 'script'])

</body>
</html>

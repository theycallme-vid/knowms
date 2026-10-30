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
    $categories = [
        ['key' => 'top',       'label' => 'Top FAQ',        'icon' => 'bi-stars'],
        ['key' => 'umum',      'label' => 'Kategori umum',  'icon' => 'bi-grid'],
        ['key' => 'fakultas',  'label' => 'Fakultas Teknik','icon' => 'bi-building'],
        ['key' => 'akademik',  'label' => 'Akademik',       'icon' => 'bi-book'],
        ['key' => 'biaya',     'label' => 'Biaya & beasiswa','icon' => 'bi-wallet2'],
        ['key' => 'praktikum', 'label' => 'Praktikum',      'icon' => 'bi-eyedropper'],
        ['key' => 'tugas',     'label' => 'Tugas akhir',    'icon' => 'bi-mortarboard'],
    ];

    $faqs = [
        ['id' => 1, 'q' => 'Bagaimana cara mengisi dan mengajukan KRS?', 'cat' => 'KRS & perkuliahan', 'key' => 'akademik'],
        ['id' => 2, 'q' => 'Apa yang perlu disiapkan untuk registrasi ulang?', 'cat' => 'Administrasi akademik', 'key' => 'umum'],
        ['id' => 3, 'q' => 'Bagaimana prosedur pengajuan cuti kuliah?', 'cat' => 'Administrasi akademik', 'key' => 'umum'],
        ['id' => 4, 'q' => 'Di mana saya bisa melihat hasil studi (KHS)?', 'cat' => 'KRS & perkuliahan', 'key' => 'akademik'],
        ['id' => 5, 'q' => 'Bagaimana cara mengajukan beasiswa?', 'cat' => 'Biaya & beasiswa', 'key' => 'biaya'],
    ];
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
                        <i class="bi bi-building me-1"></i> DISESUAIKAN UNTUKMU
                    </div>
                    <div class="display-serif fs-4 fw-bold text-dark mb-0">Fakultas Teknik</div>
                    <div class="fs-12 text-muted mb-2.5">Program Studi Informatika</div>
                    <a href="#" class="fs-12 fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="color: var(--brand-maroon);">
                        <span>Ubah fakultas</span>
                        <i class="bi bi-chevron-down fs-11"></i>
                    </a>
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
                <p class="text-muted fs-13 mb-0">Lima pertanyaan populer mahasiswa. Jawaban praktis, tanpa perlu mencari jauh.</p>
            </div>
            <span class="tag-soft shadow-sm">
                <i class="bi bi-stars"></i>
                <span id="faqCount">Top FAQ &middot; 5 pertanyaan</span>
            </span>
        </div>

        <!-- Accordion FAQ List -->
        <div id="faqList" class="accordion mt-3" style="--bs-accordion-border-width:0;">
            @foreach($faqs as $f)
                <article class="faq-item" data-cat="{{ $f['key'] }}" data-text="{{ strtolower($f['q'].' '.$f['cat']) }}">
                    <button class="faq-toggle {{ $f['id'] === 1 ? '' : 'collapsed' }}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#faq{{ $f['id'] }}" 
                            aria-expanded="{{ $f['id'] === 1 ? 'true' : 'false' }}" 
                            aria-controls="faq{{ $f['id'] }}">
                        <span class="faq-num">{{ sprintf('%02d', $f['id']) }}</span>
                        <div class="flex-grow-1 text-start">
                            @if($f['id'] === 1)
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <span class="tag-soft">KRS &amp; perkuliahan</span>
                                    <span class="fs-12 text-muted fw-normal">Panduan umum &middot; Untuk seluruh mahasiswa</span>
                                </div>
                            @endif
                            <div class="faq-q">{{ $f['q'] }}</div>
                        </div>
                        @if($f['id'] !== 1)
                            <span class="faq-cat-label">{{ $f['cat'] }}</span>
                        @endif
                        <span class="faq-icon">
                            <i class="bi bi-plus-lg faq-icon-plus"></i>
                            <i class="bi bi-dash-lg faq-icon-minus"></i>
                        </span>
                    </button>

                    <div id="faq{{ $f['id'] }}" class="collapse {{ $f['id'] === 1 ? 'show' : '' }}" data-bs-parent="#faqList">
                        <div class="faq-body pt-1">
                            @if($f['id'] === 1)
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

                                <!-- Official Source Box -->
                                <a href="#" class="source-box mb-3">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-book fs-6 text-primary"></i>
                                        <span>Cek jadwal dan ketentuan terbaru di portal akademik fakultasmu.</span>
                                    </span>
                                    <i class="bi bi-arrow-up-right fs-13"></i>
                                </a>

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
                                <a href="#" class="source-box mb-2">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-text fs-6 text-primary"></i>
                                        <span>Buka petunjuk teknis layanan akademik mahasiswa</span>
                                    </span>
                                    <i class="bi bi-arrow-up-right fs-13"></i>
                                </a>
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
            <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
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
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="navbar-logo-img">
            
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
            <a href="{{ url('/login') }}" class="footer-link fs-12 text-decoration-none" title="Masuk ke ruang kerja staf atau administrator">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk staf
            </a>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Filter & Search Script -->
<script>
    let activeCat = 'top';

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
        
        document.querySelectorAll('#faqList .faq-item').forEach(item => {
            const matchCat = activeCat === 'top' || item.dataset.cat === activeCat;
            const matchText = !q || (item.dataset.text || '').includes(q);
            const show = matchCat && matchText;
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

    document.getElementById('faqSearch').addEventListener('input', filterFaq);
</script>

</body>
</html>

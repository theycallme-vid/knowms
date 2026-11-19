<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Versi Jawaban - {{ $faq->pertanyaan }} | KMS</title>
    <meta name="description" content="Riwayat versi jawaban untuk pertanyaan {{ $faq->pertanyaan }}.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Mahasiswa FAQ CSS (Tema Utama Project) -->
    <link rel="stylesheet" href="{{ asset('css/mahasiswa/style.css') }}">

    <style>
        .rv-hero {
            background: linear-gradient(180deg, #FDF9F6 0%, #F8F9FB 100%);
            border-bottom: 1px solid #EAE6E0;
            padding: 1.85rem 0 2.1rem 0;
        }
        .rv-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.46rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: all 0.15s ease;
        }
        .rv-back-btn:hover {
            color: #85172C;
            border-color: #E5B8C0;
            background-color: #FDF7F8;
            transform: translateX(-2px);
        }
        .rv-back-btn i {
            font-size: 0.85rem;
            line-height: 1;
        }
        .rv-question-title {
            font-family: var(--font-serif);
            font-weight: 600;
            font-size: 1.55rem;
            line-height: 1.4;
            color: #171A1F;
            letter-spacing: -0.015em;
            margin: 0;
            max-width: 900px;
        }
        @media (max-width: 767.98px) {
            .rv-question-title {
                font-size: 1.25rem;
            }
        }

        /* Timeline Container */
        .rv-timeline {
            position: relative;
            padding-left: 2rem;
        }
        .rv-timeline::before {
            content: '';
            position: absolute;
            top: 24px;
            bottom: 24px;
            left: 9px;
            width: 2px;
            background: linear-gradient(180deg, #85172C 0%, #DFD8CE 35%, #E5E7EB 100%);
            border-radius: 2px;
        }
        @media (max-width: 575.98px) {
            .rv-timeline {
                padding-left: 0;
            }
            .rv-timeline::before,
            .rv-timeline-dot {
                display: none !important;
            }
        }
        .rv-timeline-item {
            position: relative;
            margin-bottom: 1.15rem;
        }
        .rv-timeline-item:last-child {
            margin-bottom: 0;
        }
        .rv-timeline-dot {
            position: absolute;
            left: -2rem;
            top: 1.15rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #FFFFFF;
            border: 2px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .rv-timeline-dot::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #94A3B8;
        }
        .rv-timeline-item.is-latest .rv-timeline-dot {
            border-color: #85172C;
            box-shadow: 0 0 0 4px rgba(133, 23, 44, 0.12);
        }
        .rv-timeline-item.is-latest .rv-timeline-dot::after {
            background-color: #85172C;
        }

        /* Version Card (Terbuka di Awal, Tetap Bisa Dilipat Bila Perlu) */
        .rv-card {
            background-color: #FFFFFF;
            border: 1px solid #E5E0D8;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .rv-card:hover {
            border-color: #D7CFC4;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }
        .rv-timeline-item.is-latest .rv-card {
            border-color: #E3C3CA;
        }
        .rv-card-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 1.35rem;
            background-color: #FFFFFF;
            border: 0;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .rv-card-trigger:hover {
            background-color: #FCFBF9;
        }
        .rv-card-trigger:not(.collapsed) {
            background-color: #FDF9F9;
            border-bottom: 1px solid #EFECE6;
        }
        .rv-trigger-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.7rem;
            min-width: 0;
        }
        .rv-ver-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #85172C;
            color: #FFFFFF;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.34rem 0.9rem;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .rv-ver-badge.is-archive {
            background-color: #334155;
        }
        .rv-ver-time {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }
        .rv-ver-time i {
            font-size: 0.8rem;
            line-height: 1;
            color: #94A3B8;
            flex-shrink: 0;
        }
        .rv-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.32rem;
            font-size: 0.71rem;
            font-weight: 700;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
            background-color: #ECFDF5;
            color: #059669;
            border: 1px solid #A7F3D0;
        }
        .rv-status-pill i {
            font-size: 0.72rem;
            line-height: 1;
        }
        .rv-chevron {
            width: 30px;
            height: 30px;
            min-width: 30px;
            border-radius: 50%;
            background-color: #F4F1EC;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
        }
        .rv-chevron i {
            font-size: 0.8rem;
            line-height: 1;
        }
        .rv-card-trigger:not(.collapsed) .rv-chevron {
            transform: rotate(180deg);
            background-color: #FCECEF;
            color: #85172C;
        }

        /* Version Detail Body */
        .rv-card-body {
            padding: 1.2rem 1.35rem 1.3rem 1.35rem;
            background-color: #FFFFFF;
        }
        .rv-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #85172C;
            margin-bottom: 0.45rem;
        }
        .rv-answer-box {
            background-color: #FAF9F6;
            border: 1px solid #EAE5DD;
            border-radius: 12px;
            padding: 0.95rem 1.15rem;
            font-size: 0.875rem;
            line-height: 1.7;
            color: #1E293B;
            word-break: break-word;
        }
        .rv-tag-pill {
            display: inline-flex;
            align-items: center;
            background-color: #F8F5F1;
            color: #85172C;
            border: 1px solid #EDE7E1;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 0.28rem 0.75rem;
        }
        .rv-source-card {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.9rem;
            background-color: #EFF6FF;
            border: 1px solid #DBEAFE;
            border-radius: 12px;
            padding: 0.78rem 1rem;
        }
        .rv-source-left {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
            flex: 1 1 260px;
        }
        .rv-source-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 10px;
            background-color: #DBEAFE;
            color: #1D4ED8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            line-height: 1;
        }
        .rv-source-icon i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .rv-source-text {
            min-width: 0;
            flex: 1 1 auto;
        }
        .rv-source-title {
            font-size: 0.84rem;
            font-weight: 600;
            color: #0F172A;
            line-height: 1.38;
            word-break: break-word;
        }
        .rv-source-sub {
            font-size: 0.72rem;
            color: #64748B;
            margin-top: 0.1rem;
        }
        .rv-source-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            padding: 0.38rem 0.85rem;
            font-size: 0.76rem;
            font-weight: 600;
            color: #334155;
            background-color: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .rv-source-btn:hover {
            background-color: #F8FAFC;
            border-color: #85172C;
            color: #85172C;
        }
        .rv-source-btn i {
            font-size: 0.8rem;
            line-height: 1;
        }
        .rv-source-empty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            background-color: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 11px;
            padding: 0.7rem 1rem;
            font-size: 0.8rem;
            color: #64748B;
        }
        .rv-source-empty i {
            font-size: 0.88rem;
            line-height: 1;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<!-- ========================================================================= -->
<!-- NAVBAR (SERAGAM DENGAN FAQ MAHASISWA)                                     -->
<!-- ========================================================================= -->
<nav class="mhs-navbar">
    <div class="mhs-container d-flex align-items-center justify-content-between py-2">
        <a href="{{ route('mahasiswa.faq') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="navbar-logo-img">
        </a>

        <div class="d-none d-md-flex align-items-center gap-1">
            <a href="{{ url('/') }}" class="mhs-nav-link">Beranda</a>
            <a href="{{ route('mahasiswa.faq') }}" class="mhs-nav-link active">FAQ</a>
            <a href="#" class="mhs-nav-link">Panduan akademik</a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('mahasiswa.faq') }}" class="rv-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke FAQ</span>
            </a>
        </div>
    </div>
</nav>

<!-- ========================================================================= -->
<!-- HEADER RINGKAS & BERSIH                                                   -->
<!-- ========================================================================= -->
<header class="rv-hero">
    <div class="mhs-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="fs-11 mb-3">
            <a href="{{ url('/') }}" class="text-decoration-none text-muted">Beranda</a>
            <span class="mx-2 text-muted">&gt;</span>
            <a href="{{ route('mahasiswa.faq') }}" class="text-decoration-none text-muted">FAQ Mahasiswa</a>
            <span class="mx-2 text-muted">&gt;</span>
            <strong style="color: var(--brand-maroon);">Riwayat Versi Jawaban</strong>
        </nav>

        <h1 class="rv-question-title mb-2">
            {{ $faq->pertanyaan }}
        </h1>

        <p class="text-muted fs-13 mb-0">
            Menampilkan {{ count($versions) }} versi jawaban &middot; Diurutkan dari yang terbaru di atas.
        </p>
    </div>
</header>

<!-- ========================================================================= -->
<!-- KONTEN UTAMA: SEMUA VERSI LANGSUNG TERBUKA (TERBARU DI ATAS)              -->
<!-- ========================================================================= -->
<main class="mhs-container py-5">
    <section aria-label="Daftar versi jawaban">
        @if(empty($versions))
            <div class="bg-white border rounded-4 p-5 text-center text-muted">
                <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-50"></i>
                <div class="fw-semibold text-dark fs-14">Belum ada riwayat versi jawaban</div>
                <div class="fs-12 mt-1">Silakan kembali ke halaman daftar FAQ untuk melihat pertanyaan lainnya.</div>
            </div>
        @else
            <div class="rv-timeline" id="historyFullPageList">
                @foreach($versions as $idx => $v)
                    @php
                        $collapseId = 'rvCollapse_' . $idx;
                        $isLatest = !empty($v['is_latest']);
                    @endphp
                    <div class="rv-timeline-item {{ $isLatest ? 'is-latest' : '' }}">
                        <span class="rv-timeline-dot" aria-hidden="true"></span>

                        <article class="rv-card">
                            <button type="button"
                                    class="rv-card-trigger"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="true"
                                    aria-controls="{{ $collapseId }}">
                                <div class="rv-trigger-left">
                                    <span class="rv-ver-badge {{ $isLatest ? '' : 'is-archive' }}">
                                        {{ $v['version_label'] }}
                                    </span>

                                    <span class="rv-ver-time">
                                        <i class="bi bi-clock"></i>
                                        <span>{{ $v['updated_at'] }}</span>
                                    </span>

                                    @if($isLatest)
                                        <span class="rv-status-pill">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Versi Saat Ini</span>
                                        </span>
                                    @endif
                                </div>

                                <span class="rv-chevron" aria-hidden="true">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </button>

                            <div id="{{ $collapseId }}" class="collapse show">
                                <div class="rv-card-body">
                                    <!-- 1. Jawaban -->
                                    <div class="mb-3">
                                        <div class="rv-section-label">Jawaban</div>
                                        <div class="rv-answer-box">
                                            {!! $v['jawaban'] ?: '<em class="text-muted">Belum ada jawaban</em>' !!}
                                        </div>
                                    </div>

                                    <!-- 2. Kata Kunci -->
                                    <div class="mb-3">
                                        <div class="rv-section-label">Kata Kunci</div>
                                        @if(!empty($v['tags']))
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($v['tags'] as $tName)
                                                    <span class="rv-tag-pill">{{ $tName }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fs-12">Tidak ada kata kunci</span>
                                        @endif
                                    </div>

                                    <!-- 3. Sumber Pustaka -->
                                    <div>
                                        <div class="rv-section-label">Sumber Pustaka</div>
                                        @if(!empty($v['source_label']))
                                            <div class="rv-source-card">
                                                <div class="rv-source-left">
                                                    <span class="rv-source-icon">
                                                        <i class="bi {{ !empty($v['source_is_file']) ? 'bi-file-earmark-text' : 'bi-book' }}"></i>
                                                    </span>
                                                    <div class="rv-source-text">
                                                        <div class="rv-source-title">{{ $v['source_label'] }}</div>
                                                        <div class="rv-source-sub">
                                                            {{ !empty($v['source_is_file']) ? 'Berkas Dokumen Rujukan' : 'Tautan Sumber Pustaka Resmi' }}
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(!empty($v['source_url']) && $v['source_url'] !== '#')
                                                    <a href="{{ $v['source_url'] }}" target="_blank" rel="noopener noreferrer" class="rv-source-btn">
                                                        <i class="bi bi-eye"></i>
                                                        <span>{{ !empty($v['source_is_file']) ? 'Lihat Berkas' : 'Lihat Tautan' }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <div class="rv-source-empty">
                                                <i class="bi bi-dash-circle"></i>
                                                <span>Tidak menggunakan sumber pustaka</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4 d-flex justify-content-start">
            <a href="{{ route('mahasiswa.faq') }}" class="rv-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar FAQ Mahasiswa</span>
            </a>
        </div>
    </section>
</main>

<!-- ========================================================================= -->
<!-- FOOTER (SERAGAM DENGAN FAQ MAHASISWA)                                     -->
<!-- ========================================================================= -->
<footer class="mhs-footer">
    <div class="mhs-container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 border-bottom border-light">
            <div class="d-flex gap-4">
                <a href="#" class="footer-link">Tentang KMS</a>
                <a href="#" class="footer-link">Panduan penggunaan</a>
                <a href="#" class="footer-link">Kontak layanan akademik</a>
            </div>
            <div class="fs-11 text-muted">
                &copy; 2026 Knowledge Management System &middot; Pusat Pengetahuan Mahasiswa
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

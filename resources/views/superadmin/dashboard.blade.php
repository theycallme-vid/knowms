<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Super Admin Dashboard</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Super Admin Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/superadmin/style.css') }}">
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
                <span class="role-pill">Ruang Super Admin</span>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column">
                <a href="#" class="nav-link-custom active">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-journal-text"></i>
                    <span>Daftar FAQ</span>
                </a>
                <a href="/superadmin/pengguna-akses" class="nav-link-custom">
                    <i class="bi bi-people"></i>
                    <span>Pengguna & akses</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-diagram-3"></i>
                    <span>Kategori</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-graph-up"></i>
                    <span>Laporan pencarian</span>
                </a>
                <a href="#" class="nav-link-custom">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat aktivitas</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section of Sidebar -->
        <div>
            <!-- Account Settings & Logout -->
            <a href="#" class="nav-link-custom mb-1">
                <i class="bi bi-gear"></i>
                <span>Pengaturan akun</span>
            </a>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link-custom text-secondary" style="cursor: pointer;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>

            <!-- Hidden Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Wrapper -->
    <main class="flex-grow-1 min-vw-0">
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
                    <span class="mx-2 text-secondary">/</span>
                    <strong class="text-dark">Dashboard</strong>
                </div>
            </div>

            <!-- Right: Search, Notification & Profile -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Cari FAQ, pengguna, atau aktivitas...">
                </div>

                <!-- Notification Bell -->
                <button class="btn btn-link text-secondary p-1 position-relative text-decoration-none" aria-label="Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->nama ?? 'SA', 0, 2)) }}</div>
                        <div class="d-none d-sm-block text-start lh-sm">
                            <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Super Admin' }}</div>
                            <div class="fs-11 text-muted">{{ Auth::user()->getRoleName() ?? 'Super Admin' }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted fs-11 ms-1 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 200px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold fs-13 text-dark">{{ Auth::user()->nama ?? 'Super Admin' }}</div>
                                <div class="fs-11 text-muted">{{ Auth::user()->email ?? '' }}</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 d-flex align-items-center gap-2" href="#">
                                <i class="bi bi-gear text-secondary"></i> Pengaturan akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 fs-13 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Page Header Area -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-2 mb-4">
                <div>
                    <div class="date-badge">RABU, 21 OKTOBER 2026</div>
                    <h1 class="display-serif fs-1 mb-1">Gambaran besar, keputusan lebih baik.</h1>
                    <p class="text-muted fs-14 mb-0">Pantau kualitas pengetahuan dan layanan informasi kampus dalam satu ruang.</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary bg-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm text-dark fs-13 fw-medium" style="border-color: #E2E8F0;">
                        <i class="bi bi-download"></i>
                        <span>Unduh laporan</span>
                    </button>
                </div>
            </div>

            <!-- 4 Metric Stat Cards -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Stat 1: Total FAQ -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #EFF6FF; color: #2563EB;">
                            <i class="bi bi-files"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Total FAQ</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">248</div>
                            <div class="fs-12 text-muted">+16 FAQ bulan ini</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Menunggu validasi -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Menunggu validasi</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">12</div>
                            <div class="fs-12" style="color: #DC2626;">4 pengajuan lebih dari 3 hari</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Pengguna internal aktif -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #ECFDF5; color: #059669;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Pengguna internal aktif</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">18</div>
                            <div class="fs-12 text-muted">9 penulis &middot; 6 validator &middot; 3 admin</div>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Pencarian minggu ini -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-custom d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background-color: #FFFBEB; color: #D97706;">
                            <i class="bi bi-search"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fs-12 text-muted fw-medium mb-1">Pencarian minggu ini</div>
                            <div class="fs-24 fw-bold text-dark lh-1 mb-1">1.260</div>
                            <div class="fs-12 text-muted">
                                <span style="color: #059669; font-weight: 600;">&uarr; 14%</span> dari minggu sebelumnya
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Section: 3 Cards (Line Chart, Donut Chart, Quick Actions) -->
            <div class="row g-3 g-xl-4 mb-4">
                <!-- Column 1: Trend Pencarian Mahasiswa -->
                <div class="col-12 col-xl-6">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h2 class="fw-bold fs-15 text-dark mb-1">Apa yang dicari mahasiswa?</h2>
                                    <div class="text-muted fs-12">1.260 pencarian dalam 7 hari terakhir</div>
                                </div>
                            </div>
                            
                            <!-- Line Chart Container -->
                            <div class="position-relative" style="height: 180px; width: 100%;">
                                <canvas id="searchTrendChart"></canvas>
                            </div>
                        </div>

                        <!-- Footer Tags -->
                        <div class="pt-3 mt-2 border-top border-light fs-12 text-muted">
                            Paling dicari: <strong style="color: var(--brand-maroon);">KRS &middot; Pembayaran kuliah &middot; Cuti akademik</strong>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Kesehatan Konten -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-3">Kesehatan konten</h2>
                            
                            <!-- Donut Chart & Legend -->
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <!-- Donut Canvas with Center Text -->
                                <div class="position-relative" style="width: 125px; height: 125px; flex-shrink: 0;">
                                    <canvas id="contentHealthChart"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                                        <div class="fw-bold fs-15 text-dark lh-1">248</div>
                                        <div class="fs-11 text-muted" style="font-size: 0.65rem !important;">Total FAQ</div>
                                    </div>
                                </div>

                                <!-- Breakdown Legend -->
                                <div class="flex-grow-1 ps-2">
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #236B64;"></span>
                                            <span class="text-secondary">Terbit</span>
                                        </div>
                                        <strong class="text-dark">180</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #1E293B;"></span>
                                            <span class="text-secondary">Draft</span>
                                        </div>
                                        <strong class="text-dark">40</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #DC2626;"></span>
                                            <span class="text-secondary">Validasi</span>
                                        </div>
                                        <strong class="text-dark">12</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fs-12 py-1">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-dot" style="background-color: #B45309;"></span>
                                            <span class="text-secondary">Revisi</span>
                                        </div>
                                        <strong class="text-dark">16</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Green Notice Box -->
                        <div class="rounded-3 p-2 d-flex align-items-center gap-2" style="background-color: #ECFDF5; border: 1px solid #D1FAE5;">
                            <i class="bi bi-check-circle text-success fs-5"></i>
                            <div class="fs-11" style="color: #065F46; line-height: 1.35;">
                                180 FAQ terbit telah melewati validasi sumber resmi.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Tindakan Cepat -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card-custom d-flex flex-column justify-content-between">
                        <div>
                            <h2 class="fw-bold fs-15 text-dark mb-3">Tindakan cepat</h2>

                            <!-- Action Item 1 -->
                            <a href="/superadmin/pengguna-akses" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EFF6FF; color: #2563EB;">
                                        <i class="bi bi-person-plus fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Kelola pengguna</div>
                                        <div class="fs-11 text-muted">18 akun internal</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>

                            <!-- Action Item 2 -->
                            <a href="#" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #EEF2FF; color: #4F46E5;">
                                        <i class="bi bi-key fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Atur hak akses</div>
                                        <div class="fs-11 text-muted">3 peran pengelola</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>

                            <!-- Action Item 3 -->
                            <a href="#" class="quick-action-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="width: 36px; height: 36px; background-color: #F0FDF4; color: #0D9488;">
                                        <i class="bi bi-journal-check fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-13 text-dark lh-sm">Kelola seluruh FAQ</div>
                                        <div class="fs-11 text-muted">248 pengetahuan kampus</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted fs-12"></i>
                            </a>
                        </div>

                        <!-- Footer Note -->
                        <div class="fs-11 text-muted pt-2">
                            Semua akses tercatat dalam riwayat.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Aktivitas Terbaru Table -->
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="fw-bold fs-15 text-dark mb-0">Aktivitas terbaru</h2>
                    <a href="#" class="fs-13 fw-semibold text-decoration-none" style="color: var(--brand-maroon);">
                        Lihat seluruh aktivitas &rarr;
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table activity-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 16%;">WAKTU</th>
                                <th style="width: 20%;">PENGGUNA</th>
                                <th style="width: 20%;">AKTIVITAS</th>
                                <th style="width: 32%;">DETAIL</th>
                                <th style="width: 12%; text-align: right;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 10.42</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Ratna Kusuma</div>
                                    <div class="fs-11 text-muted">Validator</div>
                                </td>
                                <td class="text-dark fs-13">Menerbitkan FAQ</td>
                                <td class="text-secondary fs-13">Prosedur pengajuan cuti akademik</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-terbit">Terbit</span>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 10.18</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Nadia Putri</div>
                                    <div class="fs-11 text-muted">Data Entry</div>
                                </td>
                                <td class="text-dark fs-13">Mengajukan FAQ</td>
                                <td class="text-secondary fs-13">Persyaratan yudisium Fakultas Teknik</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-validasi">Validasi</span>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 09.56</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Dimas Pratama</div>
                                    <div class="fs-11 text-muted">Super Admin</div>
                                </td>
                                <td class="text-dark fs-13">Memperbarui akses</td>
                                <td class="text-secondary fs-13">Hak Validator untuk Sari Wulandari</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-selesai">Selesai</span>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td class="text-secondary fs-12">21 Okt &middot; 09.31</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">Ratna Kusuma</div>
                                    <div class="fs-11 text-muted">Validator</div>
                                </td>
                                <td class="text-dark fs-13">Meminta revisi</td>
                                <td class="text-secondary fs-13">Batas pembayaran UKT semester genap</td>
                                <td style="text-align: right;">
                                    <span class="badge-status badge-status-revisi">Revisi</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 for modern confirmation pop-up -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Charts Initialization Scripts -->
<script>
    // Toggle Mobile Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('d-none');
    }

    // Konfirmasi Logout dengan Pop-Up
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b', // Warna maroon KMS
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
            if (confirm('Apakah Anda yakin ingin keluar dari sistem KMS?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Line Chart: Apa yang dicari mahasiswa? ---
        const ctxLine = document.getElementById('searchTrendChart').getContext('2d');
        
        // Gradient fill for area under curve
        const gradientFill = ctxLine.createLinearGradient(0, 0, 0, 180);
        gradientFill.addColorStop(0, 'rgba(133, 23, 44, 0.16)');
        gradientFill.addColorStop(1, 'rgba(133, 23, 44, 0.00)');

        // Custom plugin to draw the "260" pill marker at the peak/last data point
        const peakCalloutPlugin = {
            id: 'peakCallout',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const meta = chart.getDatasetMeta(0);
                const lastPoint = meta.data[meta.data.length - 1]; // 21 Okt point

                if (!lastPoint) return;

                const x = lastPoint.x;
                const y = lastPoint.y;
                const text = '260';

                ctx.save();
                ctx.font = '600 11px "Plus Jakarta Sans", sans-serif';
                const textWidth = ctx.measureText(text).width;
                const pillWidth = textWidth + 14;
                const pillHeight = 20;
                const pillX = x - pillWidth / 2 - 10;
                const pillY = y - 28;

                // Draw pill background
                ctx.fillStyle = '#0F172A';
                ctx.beginPath();
                ctx.roundRect(pillX, pillY, pillWidth, pillHeight, 10);
                ctx.fill();

                // Draw pill text
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(text, pillX + pillWidth / 2, pillY + pillHeight / 2);

                // Small dot on the point
                ctx.fillStyle = '#85172C';
                ctx.beginPath();
                ctx.arc(x, y, 4, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.restore();
            }
        };

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['15 Okt', '16 Okt', '17 Okt', '18 Okt', '19 Okt', '20 Okt', '21 Okt'],
                datasets: [{
                    label: 'Pencarian',
                    data: [130, 168, 155, 220, 190, 205, 260],
                    borderColor: '#85172C',
                    borderWidth: 2.5,
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.35,
                    pointRadius: [0, 0, 0, 0, 0, 0, 0],
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#85172C',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 4,
                        right: 16,
                        top: 24,
                        bottom: 0
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 8,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#94A3B8',
                            font: { size: 11, family: 'Plus Jakarta Sans' }
                        }
                    },
                    y: {
                        min: 0,
                        max: 300,
                        ticks: {
                            stepSize: 100,
                            color: '#94A3B8',
                            font: { size: 11, family: 'Plus Jakarta Sans' }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        },
                        border: { display: false }
                    }
                }
            },
            plugins: [peakCalloutPlugin]
        });

        // --- 2. Donut Chart: Kesehatan Konten ---
        const ctxDonut = document.getElementById('contentHealthChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Terbit', 'Draft', 'Validasi', 'Revisi'],
                datasets: [{
                    data: [180, 40, 12, 16],
                    backgroundColor: [
                        '#236B64', // Terbit (dark emerald / teal)
                        '#1E293B', // Draft (navy)
                        '#DC2626', // Validasi (red)
                        '#B45309'  // Revisi (amber)
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 8,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} FAQ`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>

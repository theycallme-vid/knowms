<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Unggahan FAQ - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (CDN dengan Konfigurasi Brand KMS) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50: '#fdf2f4',
                            100: '#fbe6e9',
                            200: '#f7d0d6',
                            300: '#ef95a3',
                            400: '#e55a71',
                            500: '#85172C', // Warna Brand Marun KMS
                            600: '#751426',
                            700: '#641121',
                            800: '#530e1b',
                            900: '#430b16',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['Lora', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FAFAF9] font-sans text-gray-800 antialiased min-h-screen">

<div class="flex min-h-screen">
    <!-- ========================================================================= -->
    <!-- SIDEBAR KIRI                                                              -->
    <!-- ========================================================================= -->
    <aside id="sidebar" class="w-64 bg-white border-r border-gray-200/80 fixed inset-y-0 left-0 z-40 flex flex-col justify-between p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
        <div>
            <!-- KMS Brand Logo -->
            <div class="mb-5 px-1">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="h-9 w-auto object-contain">
            </div>

            <!-- Role Badge -->
            <div class="mb-6 px-1">
                <span class="inline-block px-3 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                    Ruang Data Entry
                </span>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1">
                <a href="{{ route('dataentry.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                    <i class="bi bi-file-earmark-text text-base text-gray-400"></i>
                    <span>Daftar FAQ</span>
                </a>

                <a href="{{ route('dataentry.buatfaq') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-maroon-500 bg-maroon-50 transition">
                    <i class="bi bi-plus-lg text-base text-maroon-500"></i>
                    <span>Buat FAQ</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                    <i class="bi bi-book text-base text-gray-400"></i>
                    <span>Sumber Pustaka</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Sidebar Section -->
        <div class="border-t border-gray-100 pt-4 space-y-1">
            <a href="#" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                <i class="bi bi-gear text-base text-gray-400"></i>
                <span>Pengaturan Akun</span>
            </a>
            <button type="button" onclick="confirmLogout()" class="w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm text-gray-500 hover:text-red-600 hover:bg-red-50 transition">
                <i class="bi bi-box-arrow-right text-base"></i>
                <span>Keluar</span>
            </button>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 z-30 bg-black/30 backdrop-blur-xs hidden lg:hidden"></div>

    <!-- ========================================================================= -->
    <!-- KONTEN UTAMA                                                              -->
    <!-- ========================================================================= -->
    <main class="flex-1 lg:ml-64 flex flex-col min-w-0 min-h-screen">

        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200/80 px-4 sm:px-8 py-3.5 flex items-center justify-between gap-4 sticky top-0 z-20">
            <!-- Left: Mobile Toggle & Breadcrumb -->
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="p-2 text-gray-600 hover:text-gray-900 lg:hidden rounded-lg hover:bg-gray-100" aria-label="Toggle Menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                    <i class="bi bi-house"></i>
                    <span>Ruang kerja</span>
                    <span class="text-gray-300">/</span>
                    <span class="font-semibold text-gray-800">Data Entry</span>
                </div>
            </div>

            <!-- Right: Search, Profile & Notification -->
            <div class="flex items-center gap-3 sm:gap-4 ml-auto">
                <!-- Search bar -->
                <div class="relative hidden sm:block w-64 lg:w-72">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" placeholder="Cari FAQ, pengguna, atau aktivitas..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:bg-white focus:border-maroon-500 transition placeholder:text-gray-400">
                </div>

                <!-- Profile Info -->
                <div class="flex items-center gap-2.5 pl-2 border-l border-gray-100">
                    <div class="w-8 h-8 rounded-full bg-maroon-50 text-maroon-500 font-bold text-xs flex items-center justify-center border border-maroon-200">
                        {{ strtoupper(substr(Auth::user()?->nama ?? 'NP', 0, 2)) }}
                    </div>
                    <div class="hidden md:block text-left leading-tight">
                        <div class="text-xs font-bold text-gray-900">{{ Auth::user()?->nama ?? 'Nadia Putri' }}</div>
                        <div class="text-[11px] text-gray-500">{{ Auth::user()?->getRoleName() ?: 'Data Entry' }}</div>
                    </div>
                </div>

                <!-- Bell Notification -->
                <button type="button" class="text-gray-500 hover:text-gray-800 p-1 rounded-lg hover:bg-gray-100" aria-label="Notifikasi">
                    <i class="bi bi-bell text-lg"></i>
                </button>
            </div>
        </header>

        <!-- Area Isi Halaman -->
        <div class="flex-1 px-4 sm:px-8 py-6 w-full max-w-[1400px] mx-auto pb-28">

            <!-- Back Link -->
            <div class="mb-3">
                <a href="{{ route('dataentry.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                    <i class="bi bi-chevron-left text-xs"></i>
                    <span>Daftar FAQ</span>
                </a>
            </div>

            <!-- ========================================================================= -->
            <!-- HEADER KONTEN: JUDUL + BADGE PRATINJAU & NAMA FILE + TOMBOL UNGGAH FILE   -->
            <!-- ========================================================================= -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight">
                            Buat FAQ
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">
                            Pratinjau unggahan
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Tinjau setiap FAQ dari file yang diunggah sebelum diajukan ke validator.
                    </p>
                </div>

                <!-- Kanan Atas: Nama File Excel & Tombol Outline Merah Unggah File -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Pill Nama File Excel -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-medium">
                        <i class="bi bi-file-earmark-excel text-emerald-600 text-sm"></i>
                        <span id="activeFileName">{{ $fileName ?? 'FAQ_Akademik_2026.xlsx' }}</span>
                    </div>

                    <!-- Tombol Outline Merah Unggah File -->
                    <button type="button" onclick="openUploadModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-maroon-500 text-maroon-500 hover:bg-maroon-50 text-xs sm:text-sm font-semibold rounded-xl transition duration-150 shadow-xs">
                        <i class="bi bi-upload"></i>
                        <span>Unggah File</span>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- FILTER PILL & SEARCH BAR                                                  -->
            <!-- ========================================================================= -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <!-- Grup Tombol Filter Berbentuk Pill -->
                <div class="flex items-center gap-2 flex-wrap" id="filterPillsContainer">
                    <!-- Pill Semua -->
                    <button type="button" onclick="setFilter('all', this)"
                            class="filter-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition"
                            data-filter="all">
                        <span>Semua</span>
                        <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center text-[11px] font-bold" id="badgeCountAll">5</span>
                    </button>

                    <!-- Pill Belum Lengkap -->
                    <button type="button" onclick="setFilter('incomplete', this)"
                            class="filter-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition"
                            data-filter="incomplete">
                        <span>Belum lengkap</span>
                        <span class="w-5 h-5 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-[11px] font-bold" id="badgeCountIncomplete">3</span>
                    </button>

                    <!-- Pill Siap Diajukan (Default Active sesuai screenshot) -->
                    <button type="button" onclick="setFilter('ready', this)"
                            class="filter-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold border-2 border-slate-700 text-slate-800 bg-slate-100 shadow-2xs transition"
                            data-filter="ready">
                        <span>Siap diajukan</span>
                        <span class="w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center text-[11px] font-bold" id="badgeCountReady">2</span>
                    </button>
                </div>

                <!-- Search Bar Cari Pertanyaan -->
                <div class="relative w-full sm:w-72">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="searchInput" oninput="filterFaqs()" placeholder="Cari pertanyaan..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs text-gray-800 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500 focus:ring-1 focus:ring-maroon-500/20 transition placeholder:text-gray-400">
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TABEL PRATINJAU                                                           -->
            <!-- ========================================================================= -->
            <div class="bg-white border border-gray-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700" id="previewTable">
                        <thead class="bg-gray-50/70 border-b border-gray-200/80 text-[11px] uppercase tracking-wider font-semibold text-gray-500">
                            <tr>
                                <th scope="col" class="py-3.5 px-4 w-10 text-center">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                           class="w-4 h-4 rounded text-maroon-500 focus:ring-maroon-500 border-gray-300 cursor-pointer">
                                </th>
                                <th scope="col" class="py-3.5 px-4 w-28">Kode / Versi</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[280px]">Pertanyaan & Jawaban</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[190px]">Kata Kunci</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[190px]">Sumber Pustaka</th>
                                <th scope="col" class="py-3.5 px-4 w-32">Status</th>
                                <th scope="col" class="py-3.5 px-4 w-32 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="tableBody">
                            @php
                                $sampleItems = $items ?? [
                                    [
                                        'id' => 'FAQ-0249',
                                        'version' => 'Versi 1',
                                        'pertanyaan' => 'Bagaimana prosedur pengajuan cuti akademik?',
                                        'jawaban' => 'Pengajuan cuti dilakukan melalui layanan akademik sebelum batas kalender. Konsultasikan ke dosen pemb...',
                                        'tags' => ['cuti akademik', 'pengajuan', 'mahasiswa S1', 'Akademik'],
                                        'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                                        'pustaka_bagian' => 'Bagian Cuti Akademik',
                                        'status' => 'ready',
                                        'checked' => true,
                                    ],
                                    [
                                        'id' => 'FAQ-0250',
                                        'version' => 'Versi 1',
                                        'pertanyaan' => 'Apa syarat mengambil mata kuliah di luar progra...',
                                        'jawaban' => 'Mahasiswa dapat mengambil mata kuliah lintas prodi dengan persetujuan dosen PA dan kuota tersedia. Past...',
                                        'tags' => [],
                                        'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                                        'pustaka_bagian' => 'Bagian Lintas Prodi',
                                        'status' => 'incomplete',
                                        'checked' => false,
                                    ],
                                    [
                                        'id' => 'FAQ-0251',
                                        'version' => 'Versi 1',
                                        'pertanyaan' => 'Bagaimana cara mengajukan beasiswa UBAYA?',
                                        'jawaban' => 'Pendaftaran beasiswa dibuka sesuai jadwal, pastikan melengkapi berkas seperti transkrip nilai, surat rekome...',
                                        'tags' => ['beasiswa', 'syarat', 'UBAYA'],
                                        'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                                        'pustaka_bagian' => 'Bagian Beasiswa',
                                        'status' => 'ready',
                                        'checked' => true,
                                    ],
                                    [
                                        'id' => 'FAQ-0252',
                                        'version' => 'Versi 1',
                                        'pertanyaan' => 'Kapan jadwal semester genap 2026/2027 dimulai?',
                                        'jawaban' => 'Semester genap 2026/2027 dimulai sesuai kalender akademik yang ditetapkan. Pantau pengumuman resmi...',
                                        'tags' => ['KRS', 'jadwal', 'semester genap'],
                                        'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                                        'pustaka_bagian' => 'Kalender Akademik',
                                        'status' => 'incomplete',
                                        'checked' => false,
                                    ],
                                    [
                                        'id' => 'FAQ-0253',
                                        'version' => 'Versi 1',
                                        'pertanyaan' => 'Mata kuliah apa saja yang sudah bisa diambil?',
                                        'jawaban' => 'Daftar mata kuliah yang tersedia tercantum di buku panduan prodi masing-masing. Konsultasikan dengan...',
                                        'tags' => ['lintas prodi', 'syarat', 'mata kuliah'],
                                        'pustaka_judul' => 'Pedoman Akademik 2026/20...',
                                        'pustaka_bagian' => 'Bagian Kurikulum',
                                        'status' => 'incomplete',
                                        'checked' => false,
                                    ],
                                ];
                            @endphp

                            @foreach($sampleItems as $index => $item)
                                <tr class="faq-row hover:bg-slate-50/80 transition duration-150 {{ $item['checked'] ? 'bg-blue-50/20' : '' }}"
                                    data-status="{{ $item['status'] }}"
                                    data-id="{{ $item['id'] }}"
                                    id="row-{{ $item['id'] }}">
                                    <!-- Checkbox -->
                                    <td class="py-4 px-4 text-center">
                                        <input type="checkbox"
                                               class="row-checkbox w-4 h-4 rounded text-maroon-500 focus:ring-maroon-500 border-gray-300 cursor-pointer"
                                               {{ $item['checked'] ? 'checked' : '' }}
                                               onchange="handleRowCheckboxChange(this, '{{ $item['id'] }}')">
                                    </td>

                                    <!-- Kode / Versi -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900 text-xs">{{ $item['id'] }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $item['version'] }}</div>
                                    </td>

                                    <!-- Pertanyaan & Jawaban (2 baris teks yang di-truncate) -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900 text-xs mb-1 line-clamp-1 leading-snug faq-pertanyaan">
                                            {{ $item['pertanyaan'] }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 line-clamp-1 leading-relaxed faq-jawaban">
                                            {{ $item['jawaban'] }}
                                        </div>
                                    </td>

                                    <!-- Kata Kunci (Badge pill biru muda) -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex flex-wrap gap-1.5 faq-tags-container">
                                            @if(!empty($item['tags']))
                                                @foreach($item['tags'] as $tag)
                                                    <span class="inline-block px-2.5 py-0.5 bg-blue-50/90 text-blue-700 border border-blue-100 rounded-md text-[11px] font-medium">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-400 rounded-md text-[11px] italic">
                                                    Belum diisi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Sumber Pustaka (Judul & Bagian) -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900 text-xs line-clamp-1 faq-pustaka-judul">
                                            {{ $item['pustaka_judul'] }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 line-clamp-1 faq-pustaka-bagian">
                                            {{ $item['pustaka_bagian'] }}
                                        </div>
                                    </td>

                                    <!-- Status (Badge Hijau 'Siap diajukan' / Merah 'Belum lengkap') -->
                                    <td class="py-4 px-4 align-top">
                                        @if($item['status'] === 'ready')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Siap diajukan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200">
                                                Belum lengkap
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tindakan (Tombol Outline 'Edit' & 'Hapus') -->
                                    <td class="py-4 px-4 align-top text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Tombol Edit -->
                                            <button type="button" onclick="openEditModal('{{ $item['id'] }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-2xs">
                                                <i class="bi bi-pencil text-[10px]"></i>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button type="button" onclick="deleteRow('{{ $item['id'] }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition shadow-2xs">
                                                <i class="bi bi-trash3 text-[10px]"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel: Pagination & Counter -->
                <div class="px-5 py-3.5 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                    <div>
                        Menampilkan <span id="visibleCounter">5</span> dari <span id="totalCounter">5</span> FAQ &middot; <span id="selectedCounterText">2 dipilih</span>
                    </div>

                    <!-- Pagination Pills -->
                    <div class="flex items-center gap-1">
                        <button type="button" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-400 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50" disabled>
                            <i class="bi bi-chevron-left text-[10px]"></i>
                        </button>
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-800 text-white font-bold flex items-center justify-center text-xs">
                            1
                        </button>
                        <button type="button" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-400 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50" disabled>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div><!-- /Tabel Pratinjau -->

        </div>

        <!-- ========================================================================= -->
        <!-- FOOTER FORM (STICKY BOTTOM ACTION BAR)                                    -->
        <!-- ========================================================================= -->
        <footer class="fixed bottom-0 left-0 lg:left-64 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-200/90 px-4 sm:px-8 py-3.5 shadow-lg">
            <div class="max-w-[1400px] mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Sisi Kiri: Pill Jumlah Item Dipilih & Keterangan -->
                <div class="flex items-center gap-3">
                    <span id="bottomPillCount" class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 font-bold rounded-lg text-xs border border-blue-200">
                        2 dipilih
                    </span>
                    <span class="text-xs text-gray-500">
                        FAQ siap diajukan akan diperiksa oleh validator sebelum tersedia.
                    </span>
                </div>

                <!-- Sisi Kanan: Tombol Simpan Draft & Tombol Solid Merah Marun Ajukan Validasi -->
                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" onclick="saveAsDraft()"
                            class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition shadow-xs">
                        Simpan Draft
                    </button>
                    <button type="button" onclick="submitSelectedForValidation()" id="btnSubmitValidation"
                            class="px-6 py-2.5 text-xs sm:text-sm font-semibold text-white bg-maroon-500 hover:bg-maroon-600 rounded-xl shadow-xs inline-flex items-center gap-2 transition">
                        <i class="bi bi-send-check text-sm"></i>
                        <span id="btnSubmitValidationText">Ajukan Validasi (2)</span>
                    </button>
                </div>
            </div>
        </footer>

    </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT FAQ DRAFT DARI TABEL                                          -->
<!-- ========================================================================= -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900" id="modalEditTitle">Edit FAQ - FAQ-0249</h3>
                <p class="text-xs text-gray-500">Perbarui pertanyaan, jawaban, atau kata kunci sebelum diajukan.</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="editRowId">

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                    Pertanyaan FAQ
                </label>
                <textarea id="modalEditPertanyaan" rows="2"
                          class="w-full px-3.5 py-2.5 text-xs text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                    Jawaban FAQ
                </label>
                <textarea id="modalEditJawaban" rows="3"
                          class="w-full px-3.5 py-2.5 text-xs text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                    Kata Kunci (Pisahkan dengan koma)
                </label>
                <input type="text" id="modalEditTags"
                       class="w-full px-3.5 py-2 text-xs text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500"
                       placeholder="contoh: akademik, cuti, mahasiswa">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                    Bagian Sumber Pustaka
                </label>
                <input type="text" id="modalEditBagian"
                       class="w-full px-3.5 py-2 text-xs text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500"
                       placeholder="contoh: Bagian Cuti Akademik">
            </div>
        </div>
        <div class="px-6 py-3.5 bg-gray-50 flex justify-end gap-2 border-t border-gray-100">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl">
                Batal
            </button>
            <button type="button" onclick="saveEditedFaq()" class="px-5 py-2 text-xs font-semibold text-white bg-maroon-500 hover:bg-maroon-600 rounded-xl">
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UNGGAH FILE EXCEL BARU                                             -->
<!-- ========================================================================= -->
<div id="uploadModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="reuploadExcelForm">
            @csrf
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Unggah File FAQ Baru (Excel / CSV)</h3>
                <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <div class="p-6">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center bg-gray-50/50 hover:bg-gray-50 transition">
                    <i class="bi bi-file-earmark-spreadsheet text-4xl text-emerald-600 block mb-2"></i>
                    <div class="text-sm font-semibold text-gray-800 mb-1">Tarik & lepas file Excel di sini</div>
                    <div class="text-xs text-gray-500 mb-4">Mendukung format .XLSX, .XLS, atau .CSV hingga 10MB</div>
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl cursor-pointer shadow-xs">
                        <span>Pilih Dokumen Excel</span>
                        <input type="file" name="file_faq" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleNewExcelUpload(this)">
                    </label>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-maroon-500 hover:bg-maroon-600 rounded-xl">
                    Unggah & Tinjau
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /* ---- Toggle Mobile Sidebar ---- */
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    }

    /* ---- Logout Confirm ---- */
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem KMS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#85172C',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }

    /* ---- State Management ---- */
    let currentFilter = 'ready'; // Sesuai screenshot tombol aktif default: 'Siap diajukan'

    function updateCounters() {
        const rows = document.querySelectorAll('.faq-row');
        const total = rows.length;
        let readyCount = 0;
        let incompleteCount = 0;
        let selectedCount = 0;
        let visibleCount = 0;

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const checkbox = row.querySelector('.row-checkbox');

            if (status === 'ready') readyCount++;
            if (status === 'incomplete') incompleteCount++;
            if (checkbox && checkbox.checked) selectedCount++;
            if (row.style.display !== 'none') visibleCount++;
        });

        // Update Pill Counts
        const badgeAll = document.getElementById('badgeCountAll');
        const badgeReady = document.getElementById('badgeCountReady');
        const badgeInc = document.getElementById('badgeCountIncomplete');
        if (badgeAll) badgeAll.textContent = total;
        if (badgeReady) badgeReady.textContent = readyCount;
        if (badgeInc) badgeInc.textContent = incompleteCount;

        // Update Bottom Sticky Counters
        const selectedText = `${selectedCount} dipilih`;
        const bottomPill = document.getElementById('bottomPillCount');
        const selectedCounter = document.getElementById('selectedCounterText');
        const btnSubmitText = document.getElementById('btnSubmitValidationText');
        const totalCounter = document.getElementById('totalCounter');
        const visibleCounter = document.getElementById('visibleCounter');

        if (bottomPill) bottomPill.textContent = selectedText;
        if (selectedCounter) selectedCounter.textContent = selectedText;
        if (btnSubmitText) btnSubmitText.textContent = `Ajukan Validasi (${selectedCount})`;
        if (totalCounter) totalCounter.textContent = total;
        if (visibleCounter) visibleCounter.textContent = visibleCount;
    }

    /* ---- Filter Handler ---- */
    function setFilter(filterType, element) {
        currentFilter = filterType;

        // Update Pill Button Classes
        document.querySelectorAll('.filter-pill').forEach(btn => {
            btn.className = 'filter-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition';
            const badge = btn.querySelector('span:last-child');
            if (badge) {
                badge.className = 'w-5 h-5 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center text-[11px] font-bold';
            }
        });

        // Set Active Pill Style (Border-2 slate-700 & dark badge)
        if (element) {
            element.className = 'filter-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold border-2 border-slate-700 text-slate-800 bg-slate-100 shadow-2xs transition';
            const activeBadge = element.querySelector('span:last-child');
            if (activeBadge) {
                activeBadge.className = 'w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center text-[11px] font-bold';
            }
        }

        filterFaqs();
    }

    function filterFaqs() {
        const query = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.faq-row');

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const textContent = row.textContent.toLowerCase();

            const matchFilter = (currentFilter === 'all') || (status === currentFilter);
            const matchSearch = !query || textContent.includes(query);

            if (matchFilter && matchSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateCounters();
    }

    /* ---- Checkbox Handlers ---- */
    function toggleSelectAll(masterCheckbox) {
        const isChecked = masterCheckbox.checked;
        const visibleRows = document.querySelectorAll('.faq-row:not([style*="display: none"])');

        visibleRows.forEach(row => {
            const cb = row.querySelector('.row-checkbox');
            if (cb) {
                cb.checked = isChecked;
                if (isChecked) {
                    row.classList.add('bg-blue-50/20');
                } else {
                    row.classList.remove('bg-blue-50/20');
                }
            }
        });

        updateCounters();
    }

    function handleRowCheckboxChange(cb, rowId) {
        const row = document.getElementById('row-' + rowId);
        if (cb.checked) {
            row?.classList.add('bg-blue-50/20');
        } else {
            row?.classList.remove('bg-blue-50/20');
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) selectAll.checked = false;
        }

        updateCounters();
    }

    /* ---- Delete Action ---- */
    function deleteRow(rowId) {
        Swal.fire({
            title: `Hapus ${rowId}?`,
            text: 'Data baris pratinjau ini akan dihapus dari daftar unggahan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const row = document.getElementById('row-' + rowId);
                if (row) {
                    row.remove();
                    updateCounters();
                    filterFaqs();
                }

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: `${rowId} berhasil dihapus`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    }

    /* ---- Edit Modal Action ---- */
    function openEditModal(rowId) {
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        document.getElementById('editRowId').value = rowId;
        document.getElementById('modalEditTitle').textContent = `Edit FAQ - ${rowId}`;

        const pertanyaan = row.querySelector('.faq-pertanyaan')?.textContent.trim() || '';
        const jawaban = row.querySelector('.faq-jawaban')?.textContent.trim() || '';
        const bagian = row.querySelector('.faq-pustaka-bagian')?.textContent.trim() || '';

        const tags = [];
        row.querySelectorAll('.faq-tags-container span').forEach(el => {
            const text = el.textContent.trim();
            if (text && text !== 'Belum diisi') tags.push(text);
        });

        document.getElementById('modalEditPertanyaan').value = pertanyaan;
        document.getElementById('modalEditJawaban').value = jawaban;
        document.getElementById('modalEditTags').value = tags.join(', ');
        document.getElementById('modalEditBagian').value = bagian;

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function saveEditedFaq() {
        const rowId = document.getElementById('editRowId').value;
        const row = document.getElementById('row-' + rowId);
        if (!row) return;

        const newPertanyaan = document.getElementById('modalEditPertanyaan').value.trim();
        const newJawaban = document.getElementById('modalEditJawaban').value.trim();
        const newTagsStr = document.getElementById('modalEditTags').value.trim();
        const newBagian = document.getElementById('modalEditBagian').value.trim();

        if (newPertanyaan) {
            const pEl = row.querySelector('.faq-pertanyaan');
            if (pEl) pEl.textContent = newPertanyaan;
        }

        if (newJawaban) {
            const jEl = row.querySelector('.faq-jawaban');
            if (jEl) jEl.textContent = newJawaban;
        }

        if (newBagian) {
            const bEl = row.querySelector('.faq-pustaka-bagian');
            if (bEl) bEl.textContent = newBagian;
        }

        // Update Tags Container
        const tagsContainer = row.querySelector('.faq-tags-container');
        if (tagsContainer) {
            const tagsArr = newTagsStr.split(',').map(s => s.trim()).filter(s => s.length > 0);
            if (tagsArr.length > 0) {
                tagsContainer.innerHTML = tagsArr.map(t =>
                    `<span class="inline-block px-2.5 py-0.5 bg-blue-50/90 text-blue-700 border border-blue-100 rounded-md text-[11px] font-medium">${escHtml(t)}</span>`
                ).join(' ');
            } else {
                tagsContainer.innerHTML = `<span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-400 rounded-md text-[11px] italic">Belum diisi</span>`;
            }
        }

        closeEditModal();

        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'success',
            title: `Perubahan ${rowId} disimpan`,
            showConfirmButton: false,
            timer: 2000
        });
    }

    /* ---- Upload Modal Action ---- */
    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function handleNewExcelUpload(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            Swal.fire({
                title: 'Memproses File Baru...',
                text: `Memuat ${file.name} untuk pratinjau data FAQ.`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('reuploadExcelForm').submit();
        }
    }

    /* ---- Action Bar (Simpan Draft & Ajukan Validasi) ---- */
    function saveAsDraft() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        Swal.fire({
            title: 'Simpan sebagai Draft?',
            text: `${checkedCount > 0 ? checkedCount : 'Seluruh'} FAQ dari file unggahan akan disimpan ke database sebagai draft.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Simpan Draft',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                submitFormAction('draft');
            }
        });
    }

    function submitSelectedForValidation() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;

        if (count === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Item Dipilih',
                text: 'Silakan centang minimal 1 FAQ yang berstatus siap diajukan.',
                confirmButtonColor: '#85172C'
            });
            return;
        }

        Swal.fire({
            title: `Ajukan ${count} FAQ untuk Validasi?`,
            text: 'FAQ yang dipilih akan dikirim ke validator untuk diverifikasi sebelum diterbitkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan Validasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                submitFormAction('submit');
            }
        });
    }

    function submitFormAction(actionType) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('dataentry.pratinjau.submit') }}";

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = actionType;
        form.appendChild(actionInput);

        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            const row = cb.closest('.faq-row');
            if (row) {
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'selected_ids[]';
                idInput.value = row.getAttribute('data-id');
                form.appendChild(idInput);
            }
        });

        Swal.fire({
            title: 'Menyimpan ke Database...',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Run initial filter on page load (matches screenshot: 'Siap diajukan' default)
    document.addEventListener('DOMContentLoaded', function () {
        const readyButton = document.querySelector('[data-filter="ready"]');
        if (readyButton) {
            setFilter('ready', readyButton);
        } else {
            updateCounters();
        }
    });
</script>
</body>
</html>


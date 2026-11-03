<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($isEdit) && $isEdit ? 'Edit FAQ - ' . $faq->id : 'Buat FAQ' }} - Ruang Data Entry | KMS</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (CDN with Custom Config) -->
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
                            500: '#85172C', // Brand Maroon
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

    <style>
        /* Placeholder for contenteditable */
        [contenteditable]:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
            cursor: text;
        }
    </style>
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
        <div class="flex-1 px-4 sm:px-8 py-6 max-w-6xl w-full mx-auto">

            <!-- Back Link -->
            <div class="mb-3">
                <a href="{{ route('dataentry.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                    <i class="bi bi-chevron-left text-xs"></i>
                    <span>Daftar FAQ</span>
                </a>
            </div>

            <!-- Header Konten: Judul & Tombol Unggah File (Excel/PDF) Outline Merah -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight">
                        {{ isset($isEdit) && $isEdit ? 'Edit FAQ' : 'Buat FAQ' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Susun jawaban yang jelas dan lengkapi dengan sumber pustaka resmi
                    </p>
                </div>
                <div>
                    <button type="button" onclick="openUploadModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-maroon-500 text-maroon-500 hover:bg-maroon-50 text-xs sm:text-sm font-semibold rounded-xl transition duration-150 shadow-xs">
                        <i class="bi bi-upload"></i>
                        <span>Unggah File (Excel/PDF)</span>
                    </button>
                </div>
            </div>

            @php
                $isEditing = isset($isEdit) && $isEdit;
                $activeFaq = $isEditing ? $faq : null;
                $latestVer = $isEditing ? $faq->latest_version : null;
                $faqStatus = $latestVer ? strtolower($latestVer->status) : 'draft';
                $formAction = $isEditing ? route('dataentry.faq.update', $faq->id) : route('dataentry.buatfaq.store');
            @endphp

            @if($isEditing && $faqStatus === 'rejected' && $latestVer && $latestVer->catatan)
                <!-- Alert Catatan Revisi Validator -->
                <div class="mb-6 p-4 bg-red-50/80 border border-red-200 rounded-2xl flex items-start gap-3 text-red-900 shadow-xs">
                    <i class="bi bi-exclamation-octagon-fill text-red-600 text-xl shrink-0 mt-0.5"></i>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-red-700 mb-1">Catatan Revisi dari Validator</div>
                        <div class="text-sm text-gray-800">{{ $latestVer->catatan }}</div>
                        <div class="text-xs text-red-600/80 mt-1">FAQ ini akan diajukan ulang sebagai versi baru (Versi {{ ($faq->versions->count() + 1) }}).</div>
                    </div>
                </div>
            @endif

            <!-- ========================================================================= -->
            <!-- CARD FORM UTAMA                                                           -->
            <!-- ========================================================================= -->
            <form id="faqForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" id="formStatus" value="draft">
                <input type="hidden" name="faq_id" value="{{ $isEditing ? $faq->id : ($newFaqId ?? 'FAQ-0003') }}">
                <input type="hidden" name="tags" id="hiddenTags">
                <input type="hidden" name="pustaka_id" id="hiddenPustakaId" value="{{ $latestVer?->tDaftarPustakas_id ?? '' }}">

                <div class="bg-white border border-gray-200/90 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">

                    <!-- Label ID FAQ & Versi beserta Badge 'Draft' di Kanan -->
                    <div class="flex items-center justify-between pb-5 border-b border-gray-100">
                        <div>
                            <div class="text-sm font-bold text-gray-900">
                                {{ $isEditing ? $faq->id : ($newFaqId ?? 'FAQ-0003') }} &middot; Versi {{ $isEditing ? ($faqStatus === 'rejected' ? ($faq->versions->count() + 1) . ' (Revisi)' : ($faq->versions->count() ?: 1)) : '1' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $isEditing ? 'Form perbaikan/pembaruan data FAQ yang tersimpan di sistem.' : 'Form manual untuk menyusun FAQ dari awal. ID di-generate otomatis secara berurutan.' }}
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $isEditing && $faqStatus === 'rejected' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $isEditing && $faqStatus === 'rejected' ? 'Perlu Revisi' : 'Draft' }}
                        </span>
                    </div>

                    <!-- Input Text untuk 'Pertanyaan FAQ *' -->
                    <div>
                        <label for="inputPertanyaan" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            Pertanyaan FAQ <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="inputPertanyaan"
                            name="pertanyaan"
                            rows="2"
                            class="w-full px-4 py-3 text-sm text-gray-900 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 transition placeholder:text-gray-400"
                            placeholder="Tulis pertanyaan yang sering diajukan mahasiswa..."
                            required>{{ old('pertanyaan', $isEditing ? $faq->pertanyaan : '') }}</textarea>

                        <!-- Live Check Duplikasi Warning Alert -->
                        <div id="duplicateWarning" class="hidden mt-2 p-3 text-xs bg-amber-50 border border-amber-200 text-amber-900 rounded-xl flex items-start gap-2.5">
                            <i class="bi bi-exclamation-triangle-fill text-amber-500 text-base shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <div class="font-bold mb-1">Perhatian: Pertanyaan Serupa Terdeteksi</div>
                                <div id="duplicateList" class="space-y-0.5 text-gray-700"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Area 'Jawaban *' dengan Toolbar Simpel WYSIWYG & Textarea -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            Jawaban <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-maroon-500/20 focus-within:border-maroon-500 transition">
                            <!-- Toolbar WYSIWYG -->
                            <div class="flex flex-wrap items-center gap-1 bg-gray-50/90 px-3 py-2 border-b border-gray-200 text-gray-700 text-xs">
                                <button type="button" class="toolbar-btn px-2.5 py-1.5 rounded-lg hover:bg-gray-200 font-bold" data-cmd="bold" title="Tebal">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="toolbar-btn px-2.5 py-1.5 rounded-lg hover:bg-gray-200 italic font-serif" data-cmd="italic" title="Miring">
                                    <em>I</em>
                                </button>
                                <div class="h-4 w-px bg-gray-300 mx-1"></div>
                                <button type="button" class="toolbar-btn px-2.5 py-1.5 rounded-lg hover:bg-gray-200 inline-flex items-center gap-1 font-medium" data-cmd="createLink" title="Tautan">
                                    <i class="bi bi-link-45deg"></i> Link
                                </button>
                                <button type="button" class="toolbar-btn px-2.5 py-1.5 rounded-lg hover:bg-gray-200 inline-flex items-center gap-1 font-medium" data-cmd="insertOrderedList" title="Daftar Bernomor">
                                    <i class="bi bi-list-ol"></i> List
                                </button>
                                <button type="button" class="toolbar-btn px-2.5 py-1.5 rounded-lg hover:bg-gray-200 inline-flex items-center gap-1 font-medium" data-cmd="insertImage" title="Sisipkan Gambar">
                                    <i class="bi bi-image"></i> Gambar
                                </button>
                            </div>
                            <!-- Editable WYSIWYG Content Area -->
                            <div
                                id="editorJawaban"
                                class="p-4 text-sm text-gray-900 min-h-[160px] max-h-[380px] overflow-y-auto focus:outline-none leading-relaxed"
                                contenteditable="true"
                                data-placeholder="Tulis jawaban lengkap di sini...">{!! old('jawaban', $isEditing && $latestVer ? $latestVer->jawaban : '') !!}</div>
                        </div>
                        <input type="hidden" name="jawaban" id="hiddenJawaban">
                    </div>

                    <!-- Input 'Kata kunci' Bergaya Tag Input (Enter untuk menambah tag) -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            Kata kunci
                        </label>
                        <div id="tagContainer"
                             class="flex flex-wrap items-center gap-2 p-2.5 border border-gray-200 rounded-xl bg-white min-h-[46px] cursor-text focus-within:ring-2 focus-within:ring-maroon-500/20 focus-within:border-maroon-500 transition"
                             onclick="document.getElementById('tagInput').focus()">
                            @if($isEditing && $faq->tags)
                                @foreach($faq->tags as $t)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-800 rounded-lg text-xs font-medium border border-gray-200 tag-chip">
                                        {{ $t->nama }}
                                        <button type="button" class="text-gray-400 hover:text-gray-700 text-sm leading-none tag-remove" onclick="removeTag(this)">&times;</button>
                                    </span>
                                @endforeach
                            @endif
                            <input
                                type="text"
                                id="tagInput"
                                class="flex-1 min-w-[140px] text-xs text-gray-800 focus:outline-none bg-transparent placeholder:text-gray-400 py-1"
                                placeholder="Tambah kata kunci (tekan Enter)..."
                                onkeydown="handleTagInput(event)"
                                autocomplete="off">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            Tekan <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-[10px] text-gray-600 font-sans">Enter</kbd> atau <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-[10px] text-gray-600 font-sans">,</kbd> untuk menambahkan kata kunci baru.
                        </p>
                    </div>

                    <!-- Bagian 'Rujukan sumber pustaka *' dengan Dua Tombol Action -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            Rujukan sumber pustaka <span class="text-red-500">*</span>
                        </label>

                        <!-- Dua Tombol Action: Pilih dari Sumber Pustaka (Teks Merah) & Unggah Dokumen (Teks Hitam) -->
                        <div class="flex flex-wrap items-center gap-2.5 mb-3">
                            <!-- 1. Tombol 'Pilih dari Sumber Pustaka' (Teks Merah, Ikon Buku) -->
                            <button type="button" onclick="openSourceModal()"
                                    class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 bg-red-50/70 hover:bg-red-50 text-maroon-500 text-xs font-semibold rounded-xl transition duration-150">
                                <i class="bi bi-book"></i>
                                <span>Pilih dari Sumber Pustaka</span>
                            </button>

                            <!-- 2. Tombol 'Unggah Dokumen (PDF/DOCX)' (Teks Hitam, Ikon Upload) -->
                            <label class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 text-xs font-semibold rounded-xl cursor-pointer transition duration-150 mb-0">
                                <i class="bi bi-upload"></i>
                                <span>Unggah Dokumen (PDF/DOCX)</span>
                                <input type="file" name="sumber_file" id="sumberFileInput" class="hidden" onchange="handleFilePicked(this)">
                            </label>
                        </div>

                        <!-- Input URL / Tautan Resmi Cadangan -->
                        <div class="mb-3">
                            <div class="relative">
                                <i class="bi bi-link-45deg absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
                                <input
                                    type="text"
                                    name="sumber_link"
                                    id="inputSumberLink"
                                    maxlength="45"
                                    value="{{ old('sumber_link', ($isEditing && $latestVer && $latestVer->daftarPustaka && $latestVer->daftarPustaka->type === 'link') ? $latestVer->daftarPustaka->link : '') }}"
                                    placeholder="Atau tautan rujukan resmi (maksimal 45 karakter)..."
                                    class="w-full pl-9 pr-4 py-2 text-xs text-gray-800 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 placeholder:text-gray-400 transition">
                            </div>
                        </div>

                        <!-- List Sumber Pustaka Terpilih -->
                        <div id="sourceList" class="space-y-2">
                            @if($isEditing && $latestVer && $latestVer->daftarPustaka)
                                @php $dp = $latestVer->daftarPustaka; @endphp
                                <div class="flex items-start justify-between p-3.5 bg-gray-50 border border-gray-200 rounded-xl" id="source-selected">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 text-maroon-500 font-bold text-xs flex items-center justify-center shrink-0">
                                            1
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-900">{{ $dp->link ?: 'Dokumen Sumber Pustaka #' . $dp->id }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">Tipe: {{ strtoupper($dp->type) }} &middot; Tersimpan di database</div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearSelectedSource()" class="text-gray-400 hover:text-red-500 text-lg leading-none p-1" title="Hapus sumber">&times;</button>
                                </div>
                            @else
                                <div class="flex items-start justify-between p-3.5 bg-gray-50 border border-gray-200 rounded-xl" id="source-selected">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 text-maroon-500 font-bold text-xs flex items-center justify-center shrink-0">
                                            1
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-900" id="selectedSourceTitle">Pedoman Akademik 2026/2027</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5" id="selectedSourceMeta">Biro Akademik &middot; Bagian Cuti Akademik &middot; Dokumen contoh resmi</div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearSelectedSource()" class="text-gray-400 hover:text-red-500 text-lg leading-none p-1" title="Hapus sumber">&times;</button>
                                </div>
                            @endif
                        </div>

                        <div id="sourceEmptyMsg" class="text-xs text-gray-400 mt-2 hidden">
                            Belum ada sumber pustaka yang dipilih atau diunggah.
                        </div>
                    </div>

                </div><!-- /Card Form Utama -->
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- FOOTER FORM (STICKY BOTTOM)                                              -->
        <!-- ========================================================================= -->
        <footer class="sticky bottom-0 z-20 bg-white/95 backdrop-blur-md border-t border-gray-200/90 px-4 sm:px-8 py-3.5 mt-auto">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <!-- Tombol Teks Batal di Kiri -->
                <a href="{{ route('dataentry.dashboard') }}"
                   class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition">
                    Batal
                </a>

                <!-- Kanan: Tombol Outline Simpan Draft & Tombol Solid Merah Marun Ajukan Validasi -->
                <div class="flex items-center gap-3">
                    <button type="button" onclick="saveDraft()"
                            class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition shadow-xs">
                        Simpan Draft
                    </button>
                    <button type="button" onclick="submitValidasi()"
                            class="px-6 py-2.5 text-xs sm:text-sm font-semibold text-white bg-maroon-500 hover:bg-maroon-600 rounded-xl shadow-xs inline-flex items-center gap-2 transition">
                        <i class="bi bi-send-check text-sm"></i>
                        <span>Ajukan Validasi</span>
                    </button>
                </div>
            </div>
        </footer>

    </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PILIH SUMBER PUSTAKA                                               -->
<!-- ========================================================================= -->
<div id="sourceModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Pilih Sumber Pustaka dari Database</h3>
            <button type="button" onclick="closeSourceModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            <div class="relative mb-4">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="sourceSearchInput" placeholder="Cari dokumen atau tautan sumber..."
                       class="w-full pl-9 pr-3 py-2 text-xs text-gray-800 border border-gray-200 rounded-xl focus:outline-none focus:border-maroon-500">
            </div>

            <div id="sourceOptionsList" class="max-h-72 overflow-y-auto space-y-2 pr-1">
                @if(isset($daftarPustaka) && $daftarPustaka->isNotEmpty())
                    @foreach($daftarPustaka as $p)
                        <div class="source-option flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition"
                             onclick="selectSource('{{ $p->id }}', '{{ addslashes($p->link ?: 'Dokumen #' . $p->id) }}', 'Tipe: {{ strtoupper($p->type) }}')">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg {{ $p->type === 'upload' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }} flex items-center justify-center text-base shrink-0">
                                    <i class="bi {{ $p->type === 'upload' ? 'bi-file-earmark-pdf' : 'bi-link-45deg' }}"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900">{{ $p->link ?: 'Dokumen ID #' . $p->id }}</div>
                                    <div class="text-[11px] text-gray-500">ID Database: {{ $p->id }} &middot; Tipe: {{ strtoupper($p->type) }}</div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold uppercase px-2.5 py-1 rounded-full {{ $p->type === 'upload' ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700' }}">
                                {{ strtoupper($p->type) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="source-option flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition"
                         onclick="selectSource('', 'Pedoman Akademik 2026/2027', 'Biro Akademik · Bagian Cuti Akademik')">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900">Pedoman Akademik 2026/2027</div>
                                <div class="text-[11px] text-gray-500">Biro Akademik &middot; Bagian Cuti Akademik</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">
                            PDF
                        </span>
                    </div>

                    <div class="source-option flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition"
                         onclick="selectSource('', 'SK Rektor No. 12/2026', 'Rektorat · Kebijakan Akademik')">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-base shrink-0">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900">SK Rektor No. 12/2026</div>
                                <div class="text-[11px] text-gray-500">Rektorat &middot; Kebijakan Akademik</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold uppercase px-2.5 py-1 rounded-full bg-green-50 text-green-700">
                            Resmi
                        </span>
                    </div>
                @endif
            </div>
        </div>
        <div class="px-6 py-3 bg-gray-50 flex justify-end">
            <button type="button" onclick="closeSourceModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UNGGAH FILE (EXCEL/PDF)                                            -->
<!-- ========================================================================= -->
<div id="uploadModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <form action="{{ route('dataentry.uploadExcel') }}" method="POST" enctype="multipart/form-data" id="excelUploadForm">
            @csrf
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Unggah File FAQ (Excel / PDF)</h3>
                <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <div class="p-6">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center bg-gray-50/50 hover:bg-gray-50 transition">
                    <i class="bi bi-file-earmark-spreadsheet text-4xl text-emerald-600 block mb-2"></i>
                    <div class="text-sm font-semibold text-gray-800 mb-1">Tarik & lepas file di sini</div>
                    <div class="text-xs text-gray-500 mb-4">Mendukung format .XLSX, .XLS, .CSV, atau .PDF hingga 10MB</div>
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl cursor-pointer shadow-xs">
                        <span>Pilih Dokumen</span>
                        <input type="file" name="file_faq" id="modalFileInput" accept=".xlsx,.xls,.csv,.pdf" class="hidden" onchange="handleModalFileChosen(this)">
                    </label>
                    <div id="selectedUploadFileName" class="text-xs text-emerald-700 font-semibold mt-2.5 hidden"></div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-maroon-500 hover:bg-maroon-600 rounded-xl inline-flex items-center gap-1.5 shadow-xs">
                    <i class="bi bi-upload"></i>
                    <span>Unggah & Tinjau Pratinjau</span>
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

    /* ---- WYSIWYG Toolbar ---- */
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const cmd = this.getAttribute('data-cmd');
            if (!cmd) return;
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

    /* ---- Tag Input (Kata Kunci) ---- */
    function handleTagInput(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const input = document.getElementById('tagInput');
            const val = input.value.replace(',', '').trim();
            if (val) {
                addTag(val);
                input.value = '';
            }
        }
    }

    function addTag(text) {
        const container = document.getElementById('tagContainer');
        const input = document.getElementById('tagInput');
        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-800 rounded-lg text-xs font-medium border border-gray-200 tag-chip';
        chip.innerHTML = `${escHtml(text)} <button type="button" class="text-gray-400 hover:text-gray-700 text-sm leading-none tag-remove" onclick="removeTag(this)">&times;</button>`;
        container.insertBefore(chip, input);
    }

    function removeTag(btn) {
        btn.closest('.tag-chip').remove();
    }

    function syncTags() {
        const chips = document.querySelectorAll('#tagContainer .tag-chip');
        const tags = [];
        chips.forEach(chip => {
            const clone = chip.cloneNode(true);
            const removeBtn = clone.querySelector('.tag-remove');
            if (removeBtn) removeBtn.remove();
            const text = clone.textContent.trim();
            if (text) tags.push(text);
        });
        document.getElementById('hiddenTags').value = tags.join(',');
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ---- Modals Handler ---- */
    function openSourceModal() {
        document.getElementById('sourceModal').classList.remove('hidden');
    }
    function closeSourceModal() {
        document.getElementById('sourceModal').classList.add('hidden');
    }

    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }
    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function selectSource(pustakaId, title, meta) {
        document.getElementById('hiddenPustakaId').value = pustakaId;
        const list = document.getElementById('sourceList');
        list.innerHTML = `
            <div class="flex items-start justify-between p-3.5 bg-gray-50 border border-gray-200 rounded-xl" id="source-selected">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-maroon-500 font-bold text-xs flex items-center justify-center shrink-0">
                        1
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900">${escHtml(title)}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">${escHtml(meta)}</div>
                    </div>
                </div>
                <button type="button" onclick="clearSelectedSource()" class="text-gray-400 hover:text-red-500 text-lg leading-none p-1" title="Hapus sumber">&times;</button>
            </div>
        `;
        document.getElementById('sourceEmptyMsg').classList.add('hidden');
        closeSourceModal();

        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'success',
            title: 'Sumber pustaka dipilih',
            showConfirmButton: false,
            timer: 1800,
            timerProgressBar: true
        });
    }

    function clearSelectedSource() {
        document.getElementById('hiddenPustakaId').value = '';
        document.getElementById('sourceList').innerHTML = '';
        document.getElementById('sourceEmptyMsg').classList.remove('hidden');
    }

    function handleFilePicked(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('hiddenPustakaId').value = '';
            const list = document.getElementById('sourceList');
            list.innerHTML = `
                <div class="flex items-start justify-between p-3.5 bg-gray-50 border border-gray-200 rounded-xl" id="source-file">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-maroon-500 font-bold text-xs flex items-center justify-center shrink-0">
                            1
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-900">${escHtml(file.name)}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">File diunggah &middot; ${(file.size / 1024).toFixed(1)} KB &middot; Disimpan di database</div>
                        </div>
                    </div>
                    <button type="button" onclick="clearSelectedSource()" class="text-gray-400 hover:text-red-500 text-lg leading-none p-1" title="Hapus sumber">&times;</button>
                </div>
            `;
            document.getElementById('sourceEmptyMsg').classList.add('hidden');
        }
    }

    function handleModalFileChosen(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const nameEl = document.getElementById('selectedUploadFileName');
            if (nameEl) {
                nameEl.textContent = `File terpilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                nameEl.classList.remove('hidden');
            }

            Swal.fire({
                title: 'Mengunggah File...',
                text: `Memproses ${file.name} untuk pratinjau data FAQ.`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('excelUploadForm').submit();
        }
    }

    /* ---- Real-time Duplicate Check ---- */
    let duplicateTimer = null;
    const inputPertanyaanEl = document.getElementById('inputPertanyaan');
    const warningBox = document.getElementById('duplicateWarning');
    const duplicateListEl = document.getElementById('duplicateList');

    if (inputPertanyaanEl) {
        inputPertanyaanEl.addEventListener('input', function () {
            clearTimeout(duplicateTimer);
            const query = this.value.trim();

            if (query.length < 4) {
                warningBox.classList.add('hidden');
                return;
            }

            duplicateTimer = setTimeout(() => {
                fetch(`{{ route('dataentry.faq.checkDuplicate') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.duplicates && data.duplicates.length > 0) {
                            let html = '<ul class="list-disc pl-4 space-y-1 mt-1">';
                            data.duplicates.forEach(d => {
                                const badge = d.is_approved ? '<span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded text-[10px] ml-1">Terbit</span>' : '';
                                html += `<li><span class="font-semibold">${escHtml(d.id)}:</span> ${escHtml(d.pertanyaan)} ${badge}</li>`;
                            });
                            html += '</ul>';
                            duplicateListEl.innerHTML = html;
                            warningBox.classList.remove('hidden');
                        } else {
                            warningBox.classList.add('hidden');
                        }
                    })
                    .catch(() => {
                        warningBox.classList.add('hidden');
                    });
            }, 400);
        });
    }

    /* ---- Sync WYSIWYG Content ---- */
    function syncEditorContent() {
        const editor = document.getElementById('editorJawaban');
        if (editor) {
            document.getElementById('hiddenJawaban').value = editor.innerHTML;
        }
    }

    /* ---- Form Submission (Save Draft & Submit Validasi) ---- */
    function saveDraft() {
        syncEditorContent();
        syncTags();
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        if (!pertanyaan) {
            Swal.fire({
                icon: 'warning',
                title: 'Pertanyaan Diperlukan',
                text: 'Mohon isi pertanyaan FAQ sebelum menyimpan draft.',
                confirmButtonColor: '#85172C'
            });
            return;
        }
        document.getElementById('formStatus').value = 'draft';
        document.getElementById('faqForm').submit();
    }

    function submitValidasi() {
        syncEditorContent();
        syncTags();
        const pertanyaan = document.getElementById('inputPertanyaan').value.trim();
        const rawJawaban = document.getElementById('hiddenJawaban').value.trim();
        const cleanJawaban = rawJawaban.replace(/<[^>]*>/g, '').trim();

        if (!pertanyaan) {
            Swal.fire({ icon: 'warning', title: 'Pertanyaan Diperlukan', text: 'Mohon isi pertanyaan FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }
        if (!cleanJawaban) {
            Swal.fire({ icon: 'warning', title: 'Jawaban Diperlukan', text: 'Mohon isi jawaban FAQ terlebih dahulu.', confirmButtonColor: '#85172C' });
            return;
        }

        Swal.fire({
            title: 'Ajukan untuk Validasi?',
            text: 'FAQ ini akan disimpan dan diajukan ke validator untuk ditinjau.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#85172C',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formStatus').value = 'submitted';
                document.getElementById('faqForm').submit();
            }
        });
    }

    // Modal Search Filter for Source Library
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('sourceSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.source-option').forEach(opt => {
                    opt.style.display = opt.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });

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

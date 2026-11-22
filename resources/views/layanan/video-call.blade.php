<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call Layanan Akademik - KMS</title>
    <meta name="description" content="Layanan konsultasi tatap muka virtual mahasiswa dan admin akademik via Jitsi Meet.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Jitsi Meet External API -->
    <script src="https://meet.jit.si/external_api.js"></script>

    <style>
        :root {
            --brand-maroon: #85172C;
            --brand-maroon-hover: #6D1122;
            --brand-maroon-soft: #FCEEEF;
            --card-border: #E8ECEF;
            --text-heading: #171A1F;
            --text-body: #334155;
            --text-muted: #64748B;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Lora', Georgia, serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background-color: #0F172A;
            color: #F8FAFC;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        .display-serif {
            font-family: var(--font-serif);
        }

        /* Top Header Navigation */
        .call-header {
            background: #1E293B;
            border-bottom: 1px solid #334155;
            padding: 0.75rem 1.5rem;
            z-index: 100;
        }

        .call-logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #10B981;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .room-pill {
            background: #334155;
            border: 1px solid #475569;
            color: #E2E8F0;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-family: monospace;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-copy-code {
            background: transparent;
            border: 0;
            color: #94A3B8;
            cursor: pointer;
            padding: 0;
            font-size: 0.85rem;
            transition: color 0.15s ease;
        }

        .btn-copy-code:hover {
            color: #FFFFFF;
        }

        /* Main Video Call Area */
        .call-workspace {
            flex: 1;
            display: flex;
            height: calc(100vh - 66px);
            position: relative;
            background: #0B0F19;
            overflow: hidden;
        }

        .call-stage {
            flex: 1;
            height: 100%;
            position: relative;
            background: #020617;
            display: flex;
            flex-direction: column;
        }

        #jitsi-meet-container {
            width: 100%;
            height: 100%;
            border: 0;
            flex: 1;
        }

        /* Loading Skeleton */
        .jitsi-loading-overlay {
            position: absolute;
            inset: 0;
            background: #0F172A;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .jitsi-loading-overlay.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* Side Panel */
        .call-sidebar {
            width: 360px;
            background: #1E293B;
            border-left: 1px solid #334155;
            height: 100%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), margin-right 0.3s ease;
        }

        .call-sidebar.collapsed {
            margin-right: -360px;
        }

        .sidebar-section {
            padding: 1.25rem;
            border-bottom: 1px solid #334155;
        }

        .sidebar-section:last-child {
            border-bottom: 0;
        }

        .sidebar-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94A3B8;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-card {
            background: #0F172A;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 1rem;
        }

        .guide-step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .guide-step:last-child {
            margin-bottom: 0;
        }

        .guide-step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #85172C;
            color: #FFFFFF;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .guide-step-text {
            font-size: 0.8rem;
            color: #CBD5E1;
            line-height: 1.45;
        }

        /* Action Buttons */
        .btn-maroon-custom {
            background-color: var(--brand-maroon);
            color: #FFFFFF;
            border: 0;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-maroon-custom:hover {
            background-color: var(--brand-maroon-hover);
            color: #FFFFFF;
        }

        .btn-wa-custom {
            background-color: #25D366;
            color: #FFFFFF;
            border: 0;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-wa-custom:hover {
            background-color: #20BD5A;
            color: #FFFFFF;
        }

        /* Toast Container */
        .toast-container-custom {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            pointer-events: none;
        }

        .custom-toast {
            background: #1E293B;
            border: 1px solid #475569;
            color: #F8FAFC;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border-radius: 9999px;
            padding: 0.65rem 1.25rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .custom-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 991.98px) {
            .call-sidebar {
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                z-index: 90;
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
            }
            .call-sidebar.collapsed {
                transform: translateX(100%);
                margin-right: 0;
            }
        }
    </style>
</head>
<body>

<!-- ========================================================================= -->
<!-- HEADER RUANG VIDEO CALL                                                   -->
<!-- ========================================================================= -->
<header class="call-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <!-- Left: Brand & Status -->
    <div class="d-flex align-items-center gap-3">
        <a href="{{ $backUrl }}" class="d-flex align-items-center text-decoration-none" title="Kembali">
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="call-logo-img">
        </a>
        <div class="d-none d-sm-block border-start border-secondary ps-3">
            <div class="d-flex align-items-center gap-2">
                <span class="pulse-dot"></span>
                <span class="fw-bold fs-14 text-white">Video Call Layanan Akademik</span>
            </div>
            <div class="fs-11 text-muted">Portal Knowledge Management System (KMS)</div>
        </div>
    </div>

    <!-- Center: Room ID & Copy -->
    <div class="d-flex align-items-center gap-2">
        <div class="room-pill">
            <i class="bi bi-door-open text-warning"></i>
            <span class="d-none d-md-inline text-secondary">Room:</span>
            <span id="callRoomIdText" class="fw-bold text-white">{{ $roomId }}</span>
            <button type="button" class="btn-copy-code ms-1" onclick="copyRoomId()" title="Salin Room ID">
                <i class="bi bi-copy"></i>
            </button>
        </div>

        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fs-12 d-flex align-items-center gap-1.5" onclick="copyRoomLink()" title="Salin Tautan Room untuk dibagikan">
            <i class="bi bi-link-45deg fs-14"></i>
            <span class="d-none d-sm-inline">Salin Link</span>
        </button>
    </div>

    <!-- Right: Sidebar Toggle & Akhiri Panggilan -->
    <div class="d-flex align-items-center gap-2">
        <button type="button" 
                class="btn btn-sm btn-dark border-secondary rounded-pill px-3 fs-12 d-flex align-items-center gap-1.5" 
                onclick="toggleSidebar()" 
                id="btnToggleSidebar">
            <i class="bi bi-info-circle"></i>
            <span class="d-none d-md-inline" id="btnToggleSidebarText">Panduan &amp; Info</span>
        </button>

        <button type="button" 
                class="btn btn-sm btn-danger rounded-pill px-3.5 fs-12 fw-semibold d-flex align-items-center gap-1.5" 
                onclick="confirmExitCall()">
            <i class="bi bi-telephone-x-fill"></i>
            <span>Akhiri</span>
        </button>
    </div>
</header>

<!-- ========================================================================= -->
<!-- MAIN WORKSPACE: JITSI MEET IFRAME & SIDE PANEL                            -->
<!-- ========================================================================= -->
<main class="call-workspace">
    <!-- Area Video Call (Jitsi Meet Container) -->
    <section class="call-stage">
        <!-- Loading Overlay -->
        <div id="jitsiLoadingOverlay" class="jitsi-loading-overlay">
            <div class="spinner-border text-danger mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Memuat...</span>
            </div>
            <h4 class="display-serif fw-bold text-white mb-2">Memuat Ruang Video Call...</h4>
            <p class="text-secondary fs-13 mb-3 text-center px-3" style="max-width: 460px;">
                Menghubungkan ke ruang pertemuan virtual aman via Jitsi Meet. Pastikan izin kamera dan mikrofon diaktifkan pada browser.
            </p>
            <div class="badge bg-dark border border-secondary px-3 py-2 text-muted fs-12">
                Room ID: <span class="text-white fw-bold">{{ $roomId }}</span>
            </div>
        </div>

        <!-- Jitsi Iframe Mount Point -->
        <div id="jitsi-meet-container"></div>
    </section>

    <!-- Side Panel: Info, Undangan & Panduan -->
    <aside class="call-sidebar" id="callSidebar">
        <!-- Section 1: Info Sesi & Pengguna -->
        <div class="sidebar-section">
            <div class="sidebar-title">
                <span>Informasi Sesi Aktif</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size: 0.65rem;">
                    ONLINE
                </span>
            </div>
            <div class="sidebar-card">
                <div class="d-flex align-items-center gap-2.5 mb-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-14" style="width: 38px; height: 38px; background: #85172C;">
                        {{ strtoupper(substr($userName, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-white fs-13 text-truncate" style="max-width: 220px;">
                            {{ $userName }}
                        </div>
                        <div class="fs-11 text-secondary">
                            {{ $userRole }}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary fs-12">
                    <span class="text-muted">Durasi Pertemuan:</span>
                    <span id="callDurationTimer" class="fw-bold font-monospace text-warning">00:00:00</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Undang Admin / Bagikan Tautan -->
        <div class="sidebar-section">
            <div class="sidebar-title">
                <span>Ajak Admin / Petugas</span>
                <i class="bi bi-share"></i>
            </div>
            <p class="fs-12 text-secondary mb-2.5" style="line-height: 1.5;">
                Bagikan tautan room ini ke Admin atau Petugas Akademik agar dapat bergabung ke ruang video call yang sama.
            </p>

            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill py-2 fs-12 d-flex align-items-center justify-content-center gap-2" onclick="copyRoomLink()">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Salin Tautan Video Call</span>
                </button>

                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($whatsappMessage) }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn-wa-custom btn-sm py-2 fs-12 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                    <i class="bi bi-whatsapp"></i>
                    <span>Kirim Undangan via WhatsApp</span>
                </a>
            </div>
        </div>

        <!-- Section 3: Panduan Penggunaan Singkat -->
        <div class="sidebar-section flex-fill">
            <div class="sidebar-title">
                <span>Panduan Penggunaan</span>
                <i class="bi bi-question-circle"></i>
            </div>

            <div class="sidebar-card">
                <div class="guide-step">
                    <span class="guide-step-num">1</span>
                    <div class="guide-step-text">
                        <strong>Izinkan Akses:</strong> Klik <em>Allow / Izinkan</em> saat browser meminta izin Mikrofon &amp; Kamera.
                    </div>
                </div>

                <div class="guide-step">
                    <span class="guide-step-num">2</span>
                    <div class="guide-step-text">
                        <strong>Audio &amp; Video:</strong> Gunakan tombol mikrofon dan kamera di toolbar bawah untuk mute/unmute.
                    </div>
                </div>

                <div class="guide-step">
                    <span class="guide-step-num">3</span>
                    <div class="guide-step-text">
                        <strong>Berbagi Layar:</strong> Klik ikon monitor <em>(Share Screen)</em> jika ingin memperlihatkan berkas KRS atau bukti kendala.
                    </div>
                </div>

                <div class="guide-step">
                    <span class="guide-step-num">4</span>
                    <div class="guide-step-text">
                        <strong>Chat Teks:</strong> Gunakan menu pesan obrolan di pojok kiri bawah jika suara kurang terdengar jelas.
                    </div>
                </div>

                <div class="guide-step">
                    <span class="guide-step-num">5</span>
                    <div class="guide-step-text">
                        <strong>Akhiri:</strong> Klik tombol merah bergambar gagang telepon untuk keluar dari ruangan.
                    </div>
                </div>
            </div>

            <!-- Bantuan Hotline Cadangan -->
            <div class="mt-3 p-2.5 rounded-3 border border-secondary text-center" style="background: rgba(30, 41, 59, 0.6);">
                <div class="fs-11 text-secondary mb-1">Mengalami kendala sinyal atau koneksi?</div>
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="fs-12 text-success fw-bold text-decoration-none">
                    <i class="bi bi-whatsapp me-1"></i>Beralih ke Hotline WhatsApp
                </a>
            </div>
        </div>
    </aside>
</main>

<!-- ========================================================================= -->
<!-- MODAL KONFIRMASI KELUAR PANGGILAN                                         -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalExitCall" tabindex="-1" aria-labelledby="modalExitCallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 text-dark" style="border-radius: 20px; overflow: hidden; background: #FFFFFF;">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center mb-3 text-danger" style="width: 54px; height: 54px; border-radius: 14px; background: #FCEEEF;">
                    <i class="bi bi-telephone-x fs-2"></i>
                </div>
                <h4 class="display-serif fw-bold text-dark mb-1.5" style="font-size: 1.25rem;">
                    Akhiri Sesi Video Call?
                </h4>
                <p class="text-muted fs-13 mb-4" style="line-height: 1.5;">
                    Apakah Anda yakin ingin meninggalkan ruang tatap muka virtual ini dan kembali ke portal KMS?
                </p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border flex-fill py-2 fs-13 fw-semibold text-secondary rounded-pill" data-bs-dismiss="modal">
                        Tetap di Ruang
                    </button>
                    <a href="{{ $backUrl }}" onclick="terminateCallSession()" class="btn btn-danger flex-fill py-2 fs-13 fw-semibold rounded-pill">
                        Ya, Akhiri Panggilan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TOAST NOTIFIKASI SALIN LINK                                               -->
<!-- ========================================================================= -->
<div class="toast-container-custom">
    <div id="callToast" class="custom-toast">
        <i class="bi bi-check-circle-fill text-success fs-5"></i>
        <span id="callToastMessage">Tautan video call berhasil disalin!</span>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Jitsi Meet API Integration Script -->
<script>
    const ROOM_NAME = @json($roomId);
    const USER_DISPLAY_NAME = @json($userName);
    const USER_EMAIL = @json($userEmail ?? '');
    const ROOM_URL = @json($roomUrl);
    const BACK_URL = @json($backUrl);

    let jitsiApiInstance = null;
    let callTimerInterval = null;
    let callSecondsElapsed = 0;

    /**
     * Format detik ke format jam:menit:detik (HH:MM:SS)
     */
    function formatTime(totalSeconds) {
        const hrs = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const mins = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const secs = String(totalSeconds % 60).padStart(2, '0');
        return `${hrs}:${mins}:${secs}`;
    }

    /**
     * Memulai Timer Durasi Video Call
     */
    function startCallTimer() {
        if (callTimerInterval) clearInterval(callTimerInterval);
        const timerEl = document.getElementById('callDurationTimer');
        callTimerInterval = setInterval(() => {
            callSecondsElapsed++;
            if (timerEl) {
                timerEl.textContent = formatTime(callSecondsElapsed);
            }
        }, 1000);
    }

    /**
     * Menampilkan Toast Notifikasi
     */
    function showCallToast(message) {
        const toastEl = document.getElementById('callToast');
        const msgEl = document.getElementById('callToastMessage');
        if (!toastEl) return;
        if (msgEl) msgEl.textContent = message;
        toastEl.classList.add('show');
        setTimeout(() => {
            toastEl.classList.remove('show');
        }, 3000);
    }

    /**
     * Salin Room ID ke Clipboard
     */
    function copyRoomId() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(ROOM_NAME).then(() => {
                showCallToast(`Room ID "${ROOM_NAME}" berhasil disalin!`);
            }).catch(() => fallbackCopy(ROOM_NAME));
        } else {
            fallbackCopy(ROOM_NAME);
        }
    }

    /**
     * Salin URL Room ke Clipboard
     */
    function copyRoomLink() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(ROOM_URL).then(() => {
                showCallToast('Tautan Video Call berhasil disalin ke clipboard!');
            }).catch(() => fallbackCopy(ROOM_URL));
        } else {
            fallbackCopy(ROOM_URL);
        }
    }

    function fallbackCopy(text) {
        const tempInput = document.createElement('textarea');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showCallToast('Teks berhasil disalin!');
    }

    /**
     * Toggle Sidebar Panel
     */
    function toggleSidebar() {
        const sidebar = document.getElementById('callSidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
        }
    }

    /**
     * Beritahu server bahwa sesi video call diakhiri
     */
    function terminateCallSession() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            fetch(`/layanan-akademik/api/end/${encodeURIComponent(ROOM_NAME)}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        } catch (e) {}
    }

    /**
     * Buka Modal Konfirmasi Keluar
     */
    function confirmExitCall() {
        const modalEl = document.getElementById('modalExitCall');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            if (confirm('Akhiri sesi video call dan kembali ke portal?')) {
                terminateCallSession();
                window.location.href = BACK_URL;
            }
        }
    }

    /**
     * Inisialisasi Jitsi Meet API
     */
    function initJitsiMeet() {
        const container = document.querySelector('#jitsi-meet-container');
        const loadingOverlay = document.getElementById('jitsiLoadingOverlay');

        if (typeof JitsiMeetExternalAPI === 'undefined') {
            console.error('JitsiMeetExternalAPI script failed to load.');
            if (loadingOverlay) {
                loadingOverlay.innerHTML = `
                    <div class="text-center p-4">
                        <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-3"></i>
                        <h4 class="text-white mb-2">Gagal Memuat Jitsi Meet API</h4>
                        <p class="text-secondary fs-13 mb-3">
                            Koneksi ke server video call eksternal terhambat atau diblokir oleh browser/ekstensi ad-blocker.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="https://meet.jit.si/${encodeURIComponent(ROOM_NAME)}" target="_blank" class="btn btn-primary rounded-pill px-3 fs-13">
                                Buka di Tab Baru Jitsi &rarr;
                            </a>
                            <a href="${BACK_URL}" class="btn btn-outline-light rounded-pill px-3 fs-13">
                                Kembali ke Portal
                            </a>
                        </div>
                    </div>
                `;
            }
            return;
        }

        const domain = 'meet.jit.si';
        const options = {
            roomName: ROOM_NAME,
            width: '100%',
            height: '100%',
            parentNode: container,
            userInfo: {
                displayName: USER_DISPLAY_NAME,
                email: USER_EMAIL
            },
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                prejoinPageEnabled: false,
                enableWelcomePage: false,
                disableDeepLinking: true,
                enableClosePage: false
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_BRAND_WATERMARK: false,
                BRAND_WATERMARK_LINK: '',
                SHOW_POWERED_BY: false,
                APP_NAME: 'KMS Layanan Akademik',
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'chat', 'raisehand',
                    'videoquality', 'tileview', 'settings'
                ]
            }
        };

        try {
            jitsiApiInstance = new JitsiMeetExternalAPI(domain, options);

            // Sembunyikan overlay loading saat conference sudah siap
            jitsiApiInstance.addEventListeners({
                videoConferenceJoined: function (event) {
                    console.log('Video conference joined:', event);
                    if (loadingOverlay) {
                        loadingOverlay.classList.add('loaded');
                    }
                    startCallTimer();
                    showCallToast('Berhasil terhubung ke Ruang Video Call!');
                },
                participantJoined: function (event) {
                    console.log('Participant joined:', event);
                    showCallToast(`Peserta baru bergabung: ${event.displayName || 'Tamu'}`);
                },
                participantLeft: function (event) {
                    console.log('Participant left:', event);
                    showCallToast('Seorang peserta telah keluar dari ruang.');
                },
                readyToClose: function () {
                    // Ketika pengguna menekan tombol tutup / hang up di iframe Jitsi
                    console.log('Conference closed by user');
                    if (callTimerInterval) clearInterval(callTimerInterval);
                    terminateCallSession();
                    window.location.href = BACK_URL;
                }
            });

            // Timeout safety fallback untuk overlay
            setTimeout(() => {
                if (loadingOverlay && !loadingOverlay.classList.contains('loaded')) {
                    loadingOverlay.classList.add('loaded');
                }
            }, 5000);

        } catch (err) {
            console.error('Error initializing Jitsi Meet:', err);
            if (loadingOverlay) {
                loadingOverlay.classList.add('loaded');
            }
        }
    }

    // Jalankan inisialisasi saat dokumen siap
    document.addEventListener('DOMContentLoaded', initJitsiMeet);
</script>

</body>
</html>

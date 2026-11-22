<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hubungi Layanan Akademik - KMS (Knowledge Management System)</title>
    <meta name="description" content="Layanan kontak akademik mahasiswa: panggilan suara langsung (audio only) dan hotline WhatsApp resmi via QR scan.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Jitsi Meet External API (Audio-only Calling Engine) -->
    <script src="https://meet.jit.si/external_api.js"></script>

    <style>
        :root {
            --brand-maroon: #85172C;
            --brand-maroon-hover: #6D1122;
            --brand-maroon-soft: #FCEEEF;
            --brand-maroon-border: #F8D7DA;
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
            background-color: #F8F9FB;
            color: var(--text-body);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .display-serif {
            font-family: var(--font-serif);
        }

        /* Navbar */
        .kontak-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--card-border);
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }

        .navbar-logo-img {
            height: 52px;
            width: auto;
            object-fit: contain;
        }

        /* Container */
        .kontak-container {
            width: 100%;
            max-width: 920px;
            margin: 0 auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        /* ========================================================================= */
        /* BAGIAN ATAS: PANGGILAN SUARA (AUDIO ONLY)                                 */
        /* ========================================================================= */
        .audio-call-section {
            padding-top: 3rem;
            padding-bottom: 3.5rem;
        }

        .audio-station-card {
            background: #FFFFFF;
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 3rem 2rem;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Avatar Panggilan Sentral dengan Gelombang Suara */
        .call-avatar-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .call-avatar-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #85172C 0%, #A8243E 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 12px 28px rgba(133, 23, 44, 0.28);
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        /* Animasi Ring Gelombang Suara */
        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid rgba(133, 23, 44, 0.4);
            opacity: 0;
            z-index: 1;
            pointer-events: none;
        }

        .is-calling .pulse-ring,
        .is-connected .pulse-ring {
            animation: ringWave 2s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        .is-calling .pulse-ring:nth-child(2),
        .is-connected .pulse-ring:nth-child(2) {
            animation-delay: 0.6s;
        }

        .is-calling .pulse-ring:nth-child(3),
        .is-connected .pulse-ring:nth-child(3) {
            animation-delay: 1.2s;
        }

        @keyframes ringWave {
            0% {
                transform: scale(0.85);
                opacity: 0.8;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        /* Equalizer Gelombang Suara Statis & Dinamis */
        .sound-wave-bars {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            height: 32px;
            margin: 1rem auto 1.25rem;
        }

        .sound-bar {
            width: 4px;
            border-radius: 999px;
            background: #CBD5E1;
            transition: height 0.2s ease, background-color 0.2s ease;
        }

        .sound-bar:nth-child(1) { height: 12px; }
        .sound-bar:nth-child(2) { height: 20px; }
        .sound-bar:nth-child(3) { height: 28px; }
        .sound-bar:nth-child(4) { height: 16px; }
        .sound-bar:nth-child(5) { height: 26px; }
        .sound-bar:nth-child(6) { height: 14px; }
        .sound-bar:nth-child(7) { height: 8px; }

        .is-connected .sound-bar {
            background: #10B981;
            animation: barActive 1.2s infinite ease-in-out alternate;
        }

        .is-calling .sound-bar {
            background: #85172C;
            animation: barCalling 1s infinite ease-in-out alternate;
        }

        .is-connected .sound-bar:nth-child(1) { animation-delay: 0.1s; }
        .is-connected .sound-bar:nth-child(2) { animation-delay: 0.3s; }
        .is-connected .sound-bar:nth-child(3) { animation-delay: 0.5s; }
        .is-connected .sound-bar:nth-child(4) { animation-delay: 0.2s; }
        .is-connected .sound-bar:nth-child(5) { animation-delay: 0.4s; }
        .is-connected .sound-bar:nth-child(6) { animation-delay: 0.15s; }
        .is-connected .sound-bar:nth-child(7) { animation-delay: 0.35s; }

        @keyframes barActive {
            0% { height: 8px; }
            100% { height: 32px; }
        }

        @keyframes barCalling {
            0% { height: 6px; }
            100% { height: 22px; }
        }

        /* Tombol Panggilan Utama */
        .btn-call-action {
            min-width: 240px;
            padding: 0.95rem 2.5rem;
            border-radius: 9999px;
            font-size: 1.05rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.25s ease;
            box-shadow: 0 10px 24px rgba(133, 23, 44, 0.22);
        }

        .btn-call-start {
            background-color: var(--brand-maroon);
            color: #FFFFFF;
            border: 0;
        }

        .btn-call-start:hover {
            background-color: var(--brand-maroon-hover);
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(133, 23, 44, 0.3);
        }

        .btn-call-end {
            background-color: #DC2626;
            color: #FFFFFF;
            border: 0;
            box-shadow: 0 10px 24px rgba(220, 38, 38, 0.25);
        }

        .btn-call-end:hover {
            background-color: #B91C1C;
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        .btn-mute-mic {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-mute-mic:hover {
            background: #E2E8F0;
        }

        .btn-mute-mic.is-muted {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #DC2626;
        }

        /* Scroll Down Link */
        .scroll-down-hint {
            color: #64748B;
            font-size: 0.8125rem;
            transition: color 0.15s ease;
        }

        .scroll-down-hint:hover {
            color: var(--brand-maroon);
        }

        /* ========================================================================= */
        /* BAGIAN BAWAH: HOTLINE WHATSAPP                                            */
        /* ========================================================================= */
        .hotline-section {
            padding-top: 1rem;
            padding-bottom: 5rem;
        }

        .hotline-card {
            background: #FFFFFF;
            border: 1.5px solid #D1FAE5;
            border-radius: 28px;
            padding: 3rem 2rem;
            box-shadow: 0 16px 40px rgba(16, 185, 129, 0.06);
            text-align: center;
        }

        .hotline-phone-badge {
            background: #ECFDF5;
            border: 1.5px solid #A7F3D0;
            border-radius: 18px;
            padding: 1.25rem 2rem;
            display: inline-block;
            margin-bottom: 1.75rem;
        }

        .hotline-phone-number {
            font-size: 1.85rem;
            font-weight: 800;
            color: #065F46;
            letter-spacing: 0.04em;
        }

        .qr-code-wrapper {
            background: #FFFFFF;
            border: 2px solid #E2E8F0;
            border-radius: 20px;
            padding: 1.25rem;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
        }

        .qr-code-img {
            width: 190px;
            height: 190px;
            object-fit: contain;
            display: block;
        }

        @media (max-width: 576px) {
            .audio-station-card,
            .hotline-card {
                padding: 2rem 1.25rem;
                border-radius: 20px;
            }
            .hotline-phone-number {
                font-size: 1.35rem;
            }
            .qr-code-img {
                width: 160px;
                height: 160px;
            }
        }
    </style>
</head>
<body>

<!-- ========================================================================= -->
<!-- NAVBAR                                                                    -->
<!-- ========================================================================= -->
<nav class="kontak-navbar">
    <div class="kontak-container py-3 d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS" class="navbar-logo-img">
        </a>
        <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold fs-12 d-flex align-items-center gap-1.5">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke FAQ</span>
        </a>
    </div>
</nav>

<main class="kontak-container">
    <!-- ===================================================================== -->
    <!-- BAGIAN ATAS: PANGGILAN SUARA (AUDIO ONLY - MURNI CALL BIASA/SUARA)    -->
    <!-- ===================================================================== -->
    <section class="audio-call-section" id="section-audio-call">
        <div class="text-center mb-3">
            <span class="badge rounded-pill px-3 py-1.5" style="background-color: #FCEEEF; color: var(--brand-maroon); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em;">
                <i class="bi bi-telephone-outbound me-1"></i> PANGGILAN SUARA LANGSUNG
            </span>
            <h1 class="display-serif fw-bold text-dark mt-2 mb-1" style="font-size: 2rem;">
                Hubungi Admin Akademik
            </h1>
            <p class="text-muted fs-13 mb-0" style="max-width: 560px; margin: 0 auto; line-height: 1.55;">
                Panggilan suara (Audio Only) langsung via browser web tanpa video. Sistem akan menghubungkan Anda dengan petugas layanan akademik secara instan.
            </p>
        </div>

        <div class="audio-station-card mt-4" id="audioCallCard">
            <!-- Central Avatar dengan Gelombang Suara -->
            <div class="call-avatar-wrapper" id="callAvatarWrapper">
                <div class="pulse-ring"></div>
                <div class="pulse-ring"></div>
                <div class="pulse-ring"></div>
                <div class="call-avatar-circle" id="callAvatarCircle">
                    <i class="bi bi-headset" id="avatarIcon"></i>
                </div>
            </div>

            <!-- Identitas Penerima & Status Panggilan -->
            <h3 class="display-serif fw-bold text-dark mb-1" style="font-size: 1.35rem;" id="callTargetName">
                Petugas Layanan Akademik KMS
            </h3>
            <div class="text-muted fs-13 mb-2" id="callStatusText">
                Siap melakukan panggilan suara
            </div>

            <!-- Durasi Panggilan (saat terhubung) -->
            <div id="callDuration" class="font-monospace fw-bold text-success fs-14 mb-2 d-none">
                00:00
            </div>

            <!-- Equalizer Gelombang Suara -->
            <div class="sound-wave-bars" id="soundWaveBars" aria-hidden="true">
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
                <span class="sound-bar"></span>
            </div>

            <!-- Tombol Kontrol Panggilan -->
            <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
                <!-- Tombol Mute Mic (Tampil saat memanggil/terhubung) -->
                <button type="button" 
                        id="btnToggleMic" 
                        class="btn-mute-mic d-none" 
                        onclick="toggleMicrophone()" 
                        title="Bisukan Mikrofon">
                    <i class="bi bi-mic-fill" id="micIcon"></i>
                </button>

                <!-- Tombol Utama: Mulai Panggilan / Akhiri Panggilan -->
                <button type="button" 
                        id="btnMainCall" 
                        class="btn-call-action btn-call-start" 
                        onclick="handleCallButtonClick()">
                    <i class="bi bi-telephone-fill" id="mainCallIcon"></i>
                    <span id="mainCallLabel">Mulai Panggilan</span>
                </button>
            </div>

            <!-- Hint Keterangan Audio -->
            <div class="fs-12 text-muted mt-3" id="callInstructionHint">
                <i class="bi bi-info-circle me-1"></i>Pastikan izin mikrofon browser Anda telah diaktifkan saat memulai panggilan.
            </div>

            <!-- Navigasi Halus ke Bagian Bawah -->
            <div class="mt-4 pt-3 border-top border-light">
                <a href="#section-hotline-wa" class="scroll-down-hint d-inline-flex align-items-center gap-1.5 text-decoration-none">
                    <span>Tidak bisa melakukan panggilan? Hubungi Hotline WhatsApp di bawah</span>
                    <i class="bi bi-arrow-down-short fs-5"></i>
                </a>
            </div>
        </div>

        <!-- Hidden Audio Container untuk Jitsi Meet / WebRTC (Audio Only) -->
        <div id="jitsiAudioContainer" style="width: 1px; height: 1px; opacity: 0; position: absolute; pointer-events: none; overflow: hidden;"></div>
        <audio id="localAudioPlayer" autoplay class="d-none"></audio>
    </section>

    <!-- ===================================================================== -->
    <!-- BAGIAN BAWAH: HOTLINE WHATSAPP (NOMOR TERTERA DI ATAS + SCAN QR)      -->
    <!-- ===================================================================== -->
    <section class="hotline-section" id="section-hotline-wa">
        <div class="hotline-card">
            <!-- Badge -->
            <span class="badge rounded-pill px-3 py-1.5 mb-2" style="background-color: #ECFDF5; color: #059669; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; border: 1px solid #A7F3D0;">
                <i class="bi bi-whatsapp me-1"></i> HOTLINE CHAT RESMI
            </span>

            <!-- Judul Banner Alternatif -->
            <h2 class="display-serif fw-bold text-dark mt-1 mb-2" style="font-size: 1.65rem;">
                Tidak bisa melakukan panggilan? Hubungi Hotline WhatsApp kami
            </h2>
            <p class="text-muted fs-13 mb-4" style="max-width: 600px; margin: 0 auto; line-height: 1.55;">
                Silakan hubungi admin akademik melalui chat WhatsApp dengan memindai kode QR di bawah ini menggunakan ponsel pintar (smartphone) pribadi Anda.
            </p>

            <!-- 1. Text Nomor Kontak yang Ditulis di Bagian Atas QR -->
            <div class="hotline-phone-badge">
                <div class="fs-12 text-secondary fw-semibold text-uppercase tracking-wider mb-1">
                    <i class="bi bi-telephone-fill text-success me-1"></i> Nomor Kontak Hotline Resmi Akademik
                </div>
                <div class="hotline-phone-number">
                    {{ $formattedWaNumber }}
                </div>
                <div class="fs-12 text-muted mt-1">
                    Jam Layanan: Senin &ndash; Jumat (08:00 &ndash; 16:00 WIB)
                </div>
            </div>

            <!-- 2. QR Code WhatsApp Siap Scan -->
            <div>
                <div class="qr-code-wrapper">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($waUrl) }}" 
                         alt="QR Code WhatsApp Hotline Layanan Akademik KMS" 
                         class="qr-code-img">
                </div>
                <div class="fs-13 fw-semibold text-dark mb-1">
                    <i class="bi bi-qr-code-scan text-success me-1"></i> Scan QR Code Menggunakan Kamera / WhatsApp HP
                </div>
                <p class="fs-12 text-muted mb-0" style="max-width: 480px; margin: 0 auto;">
                    Arahkan kamera smartphone atau scanner WhatsApp ponsel Anda ke QR code di atas untuk langsung terhubung chat dengan Admin Layanan Akademik.
                </p>
            </div>

            <!-- 3. Catatan Keamanan Komputer Publik / Kiosk (Alasan ditiadakannya tombol direct WhatsApp) -->
            <div class="mt-4 p-3 rounded-3 mx-auto text-start d-flex align-items-start gap-2.5" style="max-width: 640px; background-color: #FAF8F5; border: 1px solid #EBE6DF;">
                <i class="bi bi-shield-lock-fill text-secondary fs-5 mt-0.5"></i>
                <div class="fs-12 text-muted" style="line-height: 1.55;">
                    <span class="fw-bold text-dark">Pemberitahuan Komputer Kampus / Fasilitas Publik:</span><br>
                    Tautan langsung membuka chat WhatsApp pada peramban komputer ini sengaja ditiadakan demi melindungi keamanan data dan privasi akun Anda. Harap gunakan ponsel pribadi Anda untuk memindai QR code di atas.
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Logika Audio Call (Audio-only via Jitsi Meet Engine) -->
<script>
    const AUDIO_ROOM_ID = @json($audioRoomId);
    const USER_NAME = @json($userName);
    const USER_ROLE = @json($userRole);

    let jitsiAudioInstance = null;
    let callTimerInterval = null;
    let callDurationSeconds = 0;
    let isCallActive = false;
    let isMicMuted = false;

    const audioCardEl = document.getElementById('audioCallCard');
    const statusTextEl = document.getElementById('callStatusText');
    const timerEl = document.getElementById('callDuration');
    const btnMainCall = document.getElementById('btnMainCall');
    const btnToggleMic = document.getElementById('btnToggleMic');
    const micIcon = document.getElementById('micIcon');
    const mainCallIcon = document.getElementById('mainCallIcon');
    const mainCallLabel = document.getElementById('mainCallLabel');
    const callInstructionHint = document.getElementById('callInstructionHint');

    /**
     * Format detik menjadi MM:SS
     */
    function formatCallTimer(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    /**
     * Handle Klik Tombol Utama Panggilan (Mulai / Akhiri)
     */
    function handleCallButtonClick() {
        if (!isCallActive) {
            startAudioCall();
        } else {
            endAudioCall();
        }
    }

    /**
     * Memulai Panggilan Suara (Audio Only)
     */
    async function startAudioCall() {
        isCallActive = true;
        btnMainCall.disabled = true;

        // Update UI status calling
        audioCardEl.classList.add('is-calling');
        statusTextEl.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-broadcast me-1"></i>Menghubungi Admin Akademik...</span>';
        mainCallLabel.textContent = 'Menghubungkan...';
        mainCallIcon.className = 'spinner-border spinner-border-sm me-2';

        // 1. Beritahu Server & Catat ke Database tvideocall agar Admin mendapatkan notifikasi real-time
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            await fetch("{{ route('layanan.audiocall.api.start') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    room_id: AUDIO_ROOM_ID
                })
            });
        } catch (e) {
            console.warn('Gagal mencatat panggilan audio ke server:', e);
        }

        // 2. Inisialisasi Jitsi Meet API dengan Audio Only (Murni Suara, Video Muted)
        const container = document.getElementById('jitsiAudioContainer');
        const domain = 'meet.jit.si';
        const options = {
            roomName: AUDIO_ROOM_ID,
            width: '1px',
            height: '1px',
            parentNode: container,
            userInfo: {
                displayName: USER_NAME
            },
            configOverwrite: {
                startAudioOnly: true,         // Murni Suara / Audio Only
                startWithVideoMuted: true,    // Kamera Video Mati
                startWithAudioMuted: false,   // Mikrofon Suara Hidup
                prejoinPageEnabled: false,    // Langsung masuk tanpa halaman prejoin
                enableWelcomePage: false,
                disableDeepLinking: true,
                enableClosePage: false
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                TOOLBAR_BUTTONS: ['microphone', 'hangup']
            }
        };

        try {
            jitsiAudioInstance = new JitsiMeetExternalAPI(domain, options);

            // Listener Event Panggilan
            jitsiAudioInstance.addEventListeners({
                videoConferenceJoined: function () {
                    // Panggilan terhubung
                    btnMainCall.disabled = false;
                    audioCardEl.classList.remove('is-calling');
                    audioCardEl.classList.add('is-connected');

                    statusTextEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-telephone-inbound-fill me-1"></i>Panggilan Suara Terhubung</span>';
                    btnMainCall.className = 'btn-call-action btn-call-end';
                    mainCallIcon.className = 'bi bi-telephone-x-fill';
                    mainCallLabel.textContent = 'Akhiri Panggilan';

                    btnToggleMic.classList.remove('d-none');
                    timerEl.classList.remove('d-none');
                    callInstructionHint.textContent = 'Panggilan suara sedang berlangsung. Anda terhubung dengan petugas layanan akademik.';

                    // Mulai Penghitung Waktu Panggilan
                    callDurationSeconds = 0;
                    if (callTimerInterval) clearInterval(callTimerInterval);
                    callTimerInterval = setInterval(() => {
                        callDurationSeconds++;
                        timerEl.textContent = formatCallTimer(callDurationSeconds);
                    }, 1000);
                },

                participantJoined: function (event) {
                    statusTextEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-person-check-fill me-1"></i>${event.displayName || 'Petugas Akademik'} telah tersambung</span>`;
                },

                participantLeft: function () {
                    statusTextEl.innerHTML = '<span class="text-warning fw-semibold">Petugas telah mengakhiri panggilan</span>';
                    setTimeout(endAudioCall, 1500);
                },

                readyToClose: function () {
                    endAudioCall();
                }
            });

            // Fallback timeout jika konferensi belum memicu event
            setTimeout(() => {
                if (btnMainCall.disabled) {
                    btnMainCall.disabled = false;
                    btnMainCall.className = 'btn-call-action btn-call-end';
                    mainCallIcon.className = 'bi bi-telephone-x-fill';
                    mainCallLabel.textContent = 'Akhiri Panggilan';
                    btnToggleMic.classList.remove('d-none');
                }
            }, 6000);

        } catch (err) {
            console.error('Error saat inisialisasi audio call:', err);
            statusTextEl.innerHTML = '<span class="text-danger">Gagal menghubungkan panggilan. Silakan coba kembali atau gunakan Hotline WhatsApp di bawah.</span>';
            resetCallUI();
        }
    }

    /**
     * Mengakhiri Panggilan Suara
     */
    function endAudioCall() {
        if (callTimerInterval) {
            clearInterval(callTimerInterval);
            callTimerInterval = null;
        }

        // Beritahu server bahwa sesi berakhir
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`/layanan-akademik/api/end/${encodeURIComponent(AUDIO_ROOM_ID)}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        } catch (e) {}

        // Tutup koneksi Jitsi
        if (jitsiAudioInstance) {
            try {
                jitsiAudioInstance.dispose();
            } catch (e) {}
            jitsiAudioInstance = null;
        }

        statusTextEl.innerHTML = '<span class="text-muted">Panggilan suara telah selesai. Terima kasih.</span>';
        resetCallUI();
    }

    /**
     * Reset Tampilan Tombol & Status
     */
    function resetCallUI() {
        isCallActive = false;
        btnMainCall.disabled = false;
        audioCardEl.classList.remove('is-calling', 'is-connected');
        btnMainCall.className = 'btn-call-action btn-call-start';
        mainCallIcon.className = 'bi bi-telephone-fill';
        mainCallLabel.textContent = 'Mulai Panggilan';
        btnToggleMic.classList.add('d-none');
        timerEl.classList.add('d-none');
        callInstructionHint.innerHTML = '<i class="bi bi-info-circle me-1"></i>Pastikan izin mikrofon browser Anda telah diaktifkan saat memulai panggilan.';
    }

    /**
     * Mute / Unmute Mikrofon
     */
    function toggleMicrophone() {
        if (!jitsiAudioInstance) return;
        jitsiAudioInstance.executeCommand('toggleAudio');
        isMicMuted = !isMicMuted;

        if (isMicMuted) {
            btnToggleMic.classList.add('is-muted');
            micIcon.className = 'bi bi-mic-mute-fill';
            btnToggleMic.title = 'Aktifkan Mikrofon';
        } else {
            btnToggleMic.classList.remove('is-muted');
            micIcon.className = 'bi bi-mic-fill';
            btnToggleMic.title = 'Bisukan Mikrofon';
        }
    }
</script>

</body>
</html>

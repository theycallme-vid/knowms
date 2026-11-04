<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Mahasiswa - KMS (Knowledge Management System)</title>
    <meta name="description" content="Portal Akses Mahasiswa KMS. Pindai QR kartu mahasiswa digital atau gunakan NRP untuk menemukan informasi akademik.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Lora (Konsisten dengan Data Entry & Mahasiswa) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Portal Custom CSS (Dilengkapi Auto Cache-Busting agar setiap perubahan CSS langsung aktif) -->
    <link rel="stylesheet" href="{{ asset('css/portal/style.css') }}?v={{ file_exists(public_path('css/portal/style.css')) ? filemtime(public_path('css/portal/style.css')) : time() }}">
</head>
<body>

<!-- ========================================================================= -->
<!-- MAIN PORTAL PAGE (MENYATU DENGAN BODY CREAM TANPA SCROLL PADA ZOOM 100%)  -->
<!-- ========================================================================= -->
<main class="portal-main">
    <div class="portal-container">
        <div class="row align-items-center g-4 g-xl-5">

            <!-- ============================================================= -->
            <!-- KOLOM KIRI: LOGO, HERO TEXT, GAMBAR KAMPUS, DAN AKSES TAMU    -->
            <!-- [Ukuran lebar kolom kiri diatur oleh class col-lg-4 / col-xl-4] -->
            <!-- ============================================================= -->
            <div class="col-lg-4 col-xl-4">
                <!-- Logo KMS (Menyatu langsung dengan background cream, height 80px) -->
                <div class="portal-brand-bar mb-3">
                    <a href="{{ url('/portal') }}" class="d-inline-flex align-items-center text-decoration-none">
                        <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="portal-logo-img">
                    </a>
                </div>

                <!-- Eyebrow -->
                <div class="portal-eyebrow mb-2">
                    TEMAN PERJALANAN AKADEMIKMU
                </div>

                <!-- Main Serif Heading -->
                <h1 class="display-serif portal-hero-title mb-2">
                    Temukan <span class="text-maroon">jawaban.</span><br>
                    Lanjutkan langkahmu.
                </h1>

                <!-- Subtitle -->
                <p class="portal-hero-desc mb-2">
                    Dari KRS hingga kelulusan, informasi resmi kampus kini lebih dekat. Mulai dengan kartu mahasiswa digitalmu.
                </p>

                <!-- 3 Feature Highlights -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 pt-1">
                    <span class="feature-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Sumber resmi</span>
                    </span>
                    <span class="feature-item">
                        <i class="bi bi-search text-maroon"></i>
                        <span>Mudah ditemukan</span>
                    </span>
                    <span class="feature-item">
                        <i class="bi bi-stars text-warning"></i>
                        <span>Sesuai kebutuhanmu</span>
                    </span>
                </div>

                <!-- Campus Banner Image -->
                <div class="campus-banner-wrapper mb-2">
                    <img
                        src="{{ asset('images/portal/campus-banner.jpg') }}"
                        alt="Suasana Kampus Ubaya"
                        class="campus-banner-img"
                        id="portalCampusBanner"
                    >
                </div>

                <!-- Card: Jelajahi FAQ sebagai tamu -->
                <a href="{{ route('mahasiswa.faq') }}" class="guest-card d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="guest-icon-box">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-14">Jelajahi FAQ sebagai tamu</div>
                            <div class="text-muted fs-12">Akses FAQ umum tanpa perlu memindai kartu mahasiswa.</div>
                        </div>
                    </div>
                    <span class="guest-action-link">
                        <span>Jelajahi</span>
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            </div>

            <!-- ============================================================= -->
            <!-- KOLOM KANAN: KARTU PINDAI QR & INPUT NRP                      -->
            <!-- [Ukuran lebar kolom kanan diatur oleh class col-lg-8 / col-xl-8]-->
            <!-- ============================================================= -->
            <div class="col-lg-8 col-xl-8">
                <div class="portal-access-card">

                    <!-- Card Header: Badge & QR Frame Icon -->
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                        <div>
                            <span class="badge-akses-mhs mb-2">AKSES MAHASISWA</span>
                            <h2 class="display-serif fs-3 fw-bold text-dark mb-1">
                                Pindai kartu, buka pengetahuan.
                            </h2>
                            <p class="text-muted fs-13 mb-0">
                                Gunakan QR pada kartu mahasiswa digital untuk melanjutkan.
                            </p>
                        </div>

                        <!-- Decorative Scan Icon Top Right -->
                        <div class="qr-corner-badge flex-shrink-0" title="Pemindai QR KTM">
                            <i class="bi bi-qr-code-scan fs-6"></i>
                        </div>
                    </div>

                    <!-- Scanner & Steps Row -->
                    <div class="row g-4 align-items-start mt-1">

                        <!-- Inner Left: Camera Viewport & Activate Button -->
                        <div class="col-md-7">
                            <!-- Camera Box -->
                            <div class="camera-viewport" id="cameraViewport">
                                <!-- Live Video Feed (Hidden until camera is activated) -->
                                <video id="cameraVideo" class="camera-video d-none" autoplay playsinline muted></video>
                                <canvas id="qrCanvas" class="d-none"></canvas>

                                <!-- Switch Camera Button (muncul saat kamera aktif) -->
                                <button
                                    type="button"
                                    id="btnSwitchCamera"
                                    class="btn-switch-camera d-none"
                                    onclick="switchCameraFacing()"
                                    title="Ganti kamera depan/belakang"
                                >
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>

                                <!-- Scanner Frame Overlay (Corner & Midpoint Guides) -->
                                <div class="scanner-frame">
                                    <span class="scanner-corner corner-tl"></span>
                                    <span class="scanner-mid mid-top"></span>
                                    <span class="scanner-corner corner-tr"></span>
                                    <span class="scanner-mid mid-left"></span>
                                    <span class="scanner-mid mid-right"></span>
                                    <span class="scanner-corner corner-bl"></span>
                                    <span class="scanner-mid mid-bottom"></span>
                                    <span class="scanner-corner corner-br"></span>
                                </div>

                                <!-- Default Placeholder (Before Camera is Active) -->
                                <div class="camera-placeholder" id="cameraPlaceholder">
                                    <div class="camera-icon-card">
                                        <i class="bi bi-camera" id="placeholderIcon"></i>
                                    </div>
                                    <div class="fw-bold text-dark fs-14 mb-1" id="cameraStatusTitle">Kamera belum aktif</div>
                                    <div class="text-muted fs-11" id="cameraStatusDesc">
                                        QR akan terbaca otomatis di dalam bingkai
                                    </div>
                                </div>

                                <!-- Live Status Badge (Shown when Camera is Active) -->
                                <div class="camera-live-badge d-none" id="cameraLiveBadge">
                                    <span class="live-dot"></span>
                                    <span id="cameraLiveText">Kamera aktif &middot; Arahkan QR ke bingkai</span>
                                </div>
                            </div>

                            <!-- Camera Notification / Alert Box (if QR detected or permission error) -->
                            <div id="cameraAlertBox" class="alert py-2 px-3 mt-2 mb-0 fs-12 d-none d-flex align-items-center gap-2" role="alert">
                                <i id="cameraAlertIcon" class="bi bi-info-circle"></i>
                                <span id="cameraAlertMessage"></span>
                            </div>

                            <!-- Button: Aktifkan Kamera & Pindai QR -->
                            <button
                                type="button"
                                id="btnToggleCamera"
                                class="btn-portal-maroon mt-3"
                                onclick="toggleCamera()"
                            >
                                <i class="bi bi-camera" id="btnCameraIcon"></i>
                                <span id="btnCameraLabel">Aktifkan kamera &amp; pindai QR</span>
                            </button>

                            <!-- Privacy Note -->
                            <div class="text-center text-muted fs-11 mt-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-lock"></i>
                                <span>Kamera hanya digunakan saat pemindaian.</span>
                            </div>
                        </div>

                        <!-- Inner Right: 3 Preparation Steps (Ditulis langsung dalam HTML agar mudah dilihat & diedit) -->
                        <div class="col-md-5 ps-lg-3">
                            <div class="portal-step-header pt-1">
                                Siapkan dalam tiga langkah
                            </div>

                            <div class="d-flex flex-column">
                                <!-- Langkah 1 -->
                                <div class="portal-step-item">
                                    <div class="step-number-circle">1</div>
                                    <div>
                                        <div class="step-title">Buka KTM Digital di MyUbaya</div>
                                        <div class="step-desc">
                                            Tampilkan QR KTM
                                        </div>
                                    </div>
                                </div>

                                <!-- Langkah 2 -->
                                <div class="portal-step-item">
                                    <div class="step-number-circle">2</div>
                                    <div>
                                        <div class="step-title">Izinkan akses kamera</div>
                                        <div class="step-desc">
                                            Pastikan pencahayaan cukup dan QR terlihat jelas.
                                        </div>
                                    </div>
                                </div>

                                <!-- Langkah 3 -->
                                <div class="portal-step-item">
                                    <div class="step-number-circle">3</div>
                                    <div>
                                        <div class="step-title">Arahkan QR ke bingkai</div>
                                        <div class="step-desc">
                                            Portal akan mengenali NRP dan menampilkan FAQ untukmu.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="portal-divider">
                        <span>atau gunakan NRP</span>
                    </div>

                    <!-- NRP Manual Input Form -->
                    <form id="portalNrpForm" action="{{ route('portal.session') }}" method="POST">
                        @csrf
                        <label for="nrpInput" class="form-label fw-bold text-dark fs-12 mb-2">
                            Nomor Registrasi Pokok (NRP)
                        </label>

                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                            <div class="nrp-input-wrapper">
                                <i class="bi bi-person-vcard nrp-input-icon"></i>
                                <input
                                    type="text"
                                    id="nrpInput"
                                    name="nrp"
                                    value="{{ old('nrp') }}"
                                    maxlength="9"
                                    class="nrp-form-control @error('nrp') border-danger @enderror"
                                    placeholder="Contoh: 160423018 (9 digit angka)"
                                    autocomplete="off"
                                    inputmode="numeric"
                                >
                            </div>

                            <button type="submit" id="btnSubmitNrp" class="btn-nrp-submit">
                                <i class="bi bi-arrow-right"></i>
                                <span id="btnSubmitNrpLabel">Lanjutkan tanpa NRP</span>
                            </button>
                        </div>

                        <!-- Pesan Error Validasi NRP -->
                        <div
                            id="nrpErrorBox"
                            class="text-danger fs-12 fw-medium mt-1.5 d-flex align-items-center gap-1 {{ $errors->has('nrp') ? '' : 'd-none' }}"
                            style="margin-top: 0.4rem;"
                        >
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span id="nrpErrorText">{{ $errors->first('nrp') }}</span>
                        </div>

                        <div class="text-muted fs-11 mt-2">
                            NRP digunakan untuk menyesuaikan informasi akademik, bukan untuk mengubah data mahasiswa.
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</main>

<!-- ========================================================================= -->
<!-- FOOTER                                                                    -->
<!-- ========================================================================= -->
<footer class="portal-footer">
    <div class="portal-container d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
        <span>&copy; {{ date('Y') }} KMS &middot; Layanan informasi akademik</span>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jsQR Library untuk membaca QR Code secara otomatis dari stream kamera -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: SISTEM AKTIVASI KAMERA DEVICE & PEMINDAI QR                   -->
<!-- ========================================================================= -->
<script>
    let mediaStream = null;
    let isCameraActive = false;
    let scanAnimationFrame = null;
    let currentFacingMode = 'environment'; // Default kamera belakang pada HP, fallback ke webcam di laptop

    const cameraViewport    = document.getElementById('cameraViewport');
    const cameraVideo       = document.getElementById('cameraVideo');
    const qrCanvas          = document.getElementById('qrCanvas');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const cameraLiveBadge   = document.getElementById('cameraLiveBadge');
    const btnSwitchCamera   = document.getElementById('btnSwitchCamera');
    const btnToggleCamera   = document.getElementById('btnToggleCamera');
    const btnCameraIcon     = document.getElementById('btnCameraIcon');
    const btnCameraLabel    = document.getElementById('btnCameraLabel');
    const cameraAlertBox    = document.getElementById('cameraAlertBox');
    const cameraAlertIcon   = document.getElementById('cameraAlertIcon');
    const cameraAlertMsg    = document.getElementById('cameraAlertMessage');
    const nrpInput          = document.getElementById('nrpInput');
    const btnSubmitNrp      = document.getElementById('btnSubmitNrp');
    const btnSubmitNrpLabel = document.getElementById('btnSubmitNrpLabel');

    /**
     * Menampilkan pesan status atau error di bawah kotak kamera
     */
    function showCameraAlert(message, type = 'danger') {
        cameraAlertBox.className = `alert alert-${type} py-2 px-3 mt-2 mb-0 fs-12 d-flex align-items-center gap-2`;
        cameraAlertIcon.className = type === 'success'
            ? 'bi bi-check-circle-fill flex-shrink-0'
            : (type === 'warning' ? 'bi bi-exclamation-triangle-fill flex-shrink-0' : 'bi bi-exclamation-circle-fill flex-shrink-0');
        cameraAlertMsg.textContent = message;
        cameraAlertBox.classList.remove('d-none');
    }

    function hideCameraAlert() {
        cameraAlertBox.classList.add('d-none');
    }

    /**
     * Toggle Aktifkan / Matikan Kamera Device
     */
    async function toggleCamera() {
        if (isCameraActive) {
            stopCamera();
        } else {
            await startCamera();
        }
    }

    /**
     * Memulai stream kamera dari device pengguna (Webcam / Kamera HP)
     */
    async function startCamera() {
        hideCameraAlert();

        // Pastikan browser mendukung MediaDevices API
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraAlert('Browser Anda tidak mendukung akses kamera langsung. Pastikan membuka halaman melalui localhost atau HTTPS, atau gunakan input NRP di bawah.', 'warning');
            return;
        }

        // Ubah status tombol saat meminta izin kamera
        btnToggleCamera.disabled = true;
        btnCameraLabel.textContent = 'Meminta izin akses kamera...';

        try {
            // Coba gunakan kamera sesuai facingMode (environment/user), jika gagal gunakan kamera default apa saja
            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: currentFacingMode },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });
            } catch (firstErr) {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: false
                });
            }

            mediaStream = stream;
            cameraVideo.srcObject = stream;
            await cameraVideo.play();

            // Update tampilan UI ke mode kamera aktif
            isCameraActive = true;
            cameraViewport.classList.add('is-active');
            cameraVideo.classList.remove('d-none');
            cameraPlaceholder.classList.add('d-none');
            cameraLiveBadge.classList.remove('d-none');
            btnSwitchCamera.classList.remove('d-none');

            btnToggleCamera.disabled = false;
            btnToggleCamera.classList.add('is-stop');
            btnCameraIcon.className = 'bi bi-camera-video-off';
            btnCameraLabel.textContent = 'Matikan kamera';

            // Mulai pemindaian frame untuk mendeteksi QR Code
            scanAnimationFrame = requestAnimationFrame(scanQrFrame);

        } catch (err) {
            btnToggleCamera.disabled = false;
            btnToggleCamera.classList.remove('is-stop');
            btnCameraIcon.className = 'bi bi-camera';
            btnCameraLabel.textContent = 'Aktifkan kamera & pindai QR';

            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                showCameraAlert('Izin kamera ditolak. Silakan izinkan akses kamera pada pengaturan browser Anda, lalu klik tombol kembali.', 'danger');
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                showCameraAlert('Perangkat kamera tidak ditemukan pada device ini. Silakan gunakan input NRP di bawah.', 'warning');
            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                showCameraAlert('Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.', 'warning');
            } else {
                showCameraAlert('Gagal mengaktifkan kamera (' + (err.message || 'Error tidak diketahui') + ').', 'danger');
            }
        }
    }

    /**
     * Menghentikan stream kamera dan mengembalikan tampilan awal
     */
    function stopCamera() {
        if (scanAnimationFrame) {
            cancelAnimationFrame(scanAnimationFrame);
            scanAnimationFrame = null;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }

        cameraVideo.pause();
        cameraVideo.srcObject = null;
        isCameraActive = false;

        cameraViewport.classList.remove('is-active');
        cameraVideo.classList.add('d-none');
        cameraPlaceholder.classList.remove('d-none');
        cameraLiveBadge.classList.add('d-none');
        btnSwitchCamera.classList.add('d-none');

        btnToggleCamera.disabled = false;
        btnToggleCamera.classList.remove('is-stop');
        btnCameraIcon.className = 'bi bi-camera';
        btnCameraLabel.textContent = 'Aktifkan kamera & pindai QR';
    }

    /**
     * Mengganti kamera depan / belakang
     */
    async function switchCameraFacing() {
        if (!isCameraActive) return;
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        stopCamera();
        await startCamera();
    }

    /**
     * Memindai frame video secara real-time untuk membaca QR Code KTM
     */
    function scanQrFrame() {
        if (!isCameraActive) return;

        if (cameraVideo.readyState === cameraVideo.HAVE_ENOUGH_DATA && typeof jsQR === 'function') {
            const ctx = qrCanvas.getContext('2d', { willReadFrequently: true });
            qrCanvas.width = cameraVideo.videoWidth;
            qrCanvas.height = cameraVideo.videoHeight;
            ctx.drawImage(cameraVideo, 0, 0, qrCanvas.width, qrCanvas.height);

            const imageData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (code && code.data) {
                handleQrDetected(code.data.trim());
                return;
            }
        }

        scanAnimationFrame = requestAnimationFrame(scanQrFrame);
    }

    // Daftar kode fakultas resmi yang terdaftar pada PortalController::FAKULTAS_MAP
    const fakultasMap = @json(\App\Http\Controllers\PortalController::FAKULTAS_MAP);

    const nrpErrorBox  = document.getElementById('nrpErrorBox');
    const nrpErrorText = document.getElementById('nrpErrorText');
    const portalNrpForm = document.getElementById('portalNrpForm');

    function showNrpError(msg) {
        if (!nrpErrorBox || !nrpErrorText) return;
        nrpErrorText.textContent = msg;
        nrpErrorBox.classList.remove('d-none');
        nrpInput.classList.add('border-danger');
    }

    function hideNrpError() {
        if (!nrpErrorBox) return;
        nrpErrorBox.classList.add('d-none');
        nrpInput.classList.remove('border-danger');
    }

    /**
     * Memvalidasi ketentuan NRP:
     * 1. Wajib tepat 9 digit karakter angka (0-9)
     * 2. 2 karakter terdepan wajib terdaftar di PortalController::FAKULTAS_MAP
     */
    function validateNrpValue(nrp) {
        if (!/^\d{9}$/.test(nrp)) {
            return {
                valid: false,
                message: 'NRP harus terdiri dari tepat 9 digit angka (contoh: 160423018).'
            };
        }

        const kodeFakultas = nrp.substring(0, 2);
        if (!Object.prototype.hasOwnProperty.call(fakultasMap, kodeFakultas)) {
            return {
                valid: false,
                message: `Kode fakultas '${kodeFakultas}' pada NRP tidak terdaftar di sistem.`
            };
        }

        return {
            valid: true,
            kodeFakultas: kodeFakultas,
            namaFakultas: fakultasMap[kodeFakultas]
        };
    }

    /**
     * Ketika QR Code berhasil terbaca:
     * Mengambil string NRP, memvalidasi 9 digit & kode fakultas, lalu otomatis submit ke tsession & redirect ke FAQ
     */
    function handleQrDetected(qrValue) {
        stopCamera();

        // Ekstrak 9 digit angka NRP jika QR berisi format URL atau teks gabungan, atau gunakan langsung
        const nrpMatch = qrValue.match(/\b\d{9}\b/);
        const extractedNrp = nrpMatch ? nrpMatch[0] : qrValue.replace(/\D/g, '');

        nrpInput.value = extractedNrp.slice(0, 9);
        updateNrpButtonState();

        const validation = validateNrpValue(extractedNrp);
        if (!validation.valid) {
            showCameraAlert('QR terbaca (' + (extractedNrp || qrValue) + '), namun tidak valid: ' + validation.message, 'danger');
            showNrpError(validation.message);
            nrpInput.focus();
            return;
        }

        hideNrpError();
        showCameraAlert(
            'QR berhasil terbaca! NRP: ' + extractedNrp + ' (' + validation.namaFakultas + '). Mengalihkan ke FAQ...',
            'success'
        );

        // Otomatis submit form untuk menyimpan 2 karakter depan NRP ke tabel tsession & pindah ke faq.blade.php
        if (portalNrpForm) {
            portalNrpForm.submit();
        }
    }

    /**
     * Mengubah tombol submit NRP secara dinamis saat NRP diisi / kosong
     * sekaligus memfilter agar input hanya menerima angka maksimal 9 digit
     */
    function updateNrpButtonState() {
        const cleaned = (nrpInput.value || '').replace(/\D/g, '').slice(0, 9);
        if (nrpInput.value !== cleaned) {
            nrpInput.value = cleaned;
        }

        hideNrpError();

        if (cleaned.length > 0) {
            btnSubmitNrp.classList.add('has-value');
            btnSubmitNrpLabel.textContent = 'Lanjutkan dengan NRP';
        } else {
            btnSubmitNrp.classList.remove('has-value');
            btnSubmitNrpLabel.textContent = 'Lanjutkan tanpa NRP';
        }
    }

    nrpInput.addEventListener('input', updateNrpButtonState);

    // Validasi saat form NRP di-submit secara manual
    if (portalNrpForm) {
        portalNrpForm.addEventListener('submit', function (e) {
            const val = (nrpInput.value || '').trim();
            // Jika kosong, izinkan masuk sebagai tamu ("Lanjutkan tanpa NRP")
            if (val === '') {
                hideNrpError();
                return;
            }

            const check = validateNrpValue(val);
            if (!check.valid) {
                e.preventDefault();
                showNrpError(check.message);
                nrpInput.focus();
            }
        });
    }

    // Inisialisasi state tombol saat halaman dimuat (misal ketika ada old('nrp'))
    if ((nrpInput.value || '').trim().length > 0) {
        btnSubmitNrp.classList.add('has-value');
        btnSubmitNrpLabel.textContent = 'Lanjutkan dengan NRP';
    }

    // Matikan kamera secara otomatis apabila pengguna berpindah halaman
    window.addEventListener('beforeunload', () => {
        if (isCameraActive) stopCamera();
    });
</script>

</body>
</html>


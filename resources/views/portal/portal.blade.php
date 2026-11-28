<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Informasi Akademik - KMS</title>
    <meta name="description" content="Portal Informasi Akademik KMS. Masuk menggunakan NRP, QR KTM Digital, atau sebagai tamu.">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Portal CSS -->
    <link rel="stylesheet" href="{{ asset('css/portal/style.css') }}?v={{ file_exists(public_path('css/portal/style.css')) ? filemtime(public_path('css/portal/style.css')) : time() }}">
</head>
<body>

<main class="portal-main">
    <div class="portal-shell">
        <section class="portal-card" aria-labelledby="portalHeading">
            <!-- Institutional Brand & Title -->
            <div class="portal-header">
                <a href="{{ url('/portal') }}" class="d-inline-flex align-items-center text-decoration-none mb-2">
                    <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="portal-logo-img">
                </a>
                <h1 id="portalHeading" class="portal-title">Portal Informasi Akademik</h1>
            </div>

            <!-- Pilihan Peran Utama: Mahasiswa | Umum -->
            <div class="portal-segmented" role="tablist" aria-label="Pilih jenis pengguna">
                <button
                    type="button"
                    id="roleBtnMahasiswa"
                    class="portal-segment-btn active"
                    role="tab"
                    aria-selected="true"
                >
                    <i class="bi bi-mortarboard"></i>
                    <span>Mahasiswa</span>
                </button>
                <button
                    type="button"
                    id="roleBtnUmum"
                    class="portal-segment-btn"
                    role="tab"
                    aria-selected="false"
                    onclick="enterAsGuest()"
                >
                    <i class="bi bi-globe2"></i>
                    <span>Umum</span>
                </button>
            </div>

            <!-- AREA MAHASISWA: SCAN QR KTM + INPUT NRP -->
            <div id="paneQr">
                <div class="camera-viewport" id="cameraViewport">
                    <video id="cameraVideo" class="camera-video d-none" autoplay playsinline muted></video>
                    <canvas id="qrCanvas" class="d-none"></canvas>

                    <button
                        type="button"
                        id="btnSwitchCamera"
                        class="btn-switch-camera d-none"
                        onclick="switchCameraFacing()"
                        title="Ganti kamera depan/belakang"
                    >
                        <i class="bi bi-arrow-repeat"></i>
                    </button>

                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <div class="camera-icon-card">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <div class="camera-placeholder-title">Kamera belum aktif</div>
                        <div class="camera-placeholder-desc">
                            Arahkan QR KTM Digital dari aplikasi MyUbaya ke kamera.
                        </div>
                    </div>
                </div>

                <div id="cameraAlertBox" class="alert py-2 px-3 mt-2 mb-0 fs-12 d-none d-flex align-items-center gap-2" role="alert">
                    <i id="cameraAlertIcon" class="bi bi-info-circle"></i>
                    <span id="cameraAlertMessage"></span>
                </div>

                <button
                    type="button"
                    id="btnToggleCamera"
                    class="btn-portal-maroon mt-3"
                    onclick="toggleCamera()"
                >
                    <i class="bi bi-camera" id="btnCameraIcon"></i>
                    <span id="btnCameraLabel">Aktifkan Kamera</span>
                </button>
            </div>

            <div class="portal-divider">
                <span>atau input NRP</span>
            </div>

            <!-- INPUT NRP -->
            <div id="paneNrp">
                <form id="portalNrpForm" action="{{ route('portal.session') }}" method="POST" novalidate>
                    @csrf
                    <label for="nrpInput" class="portal-label">
                        Nomor Registrasi Pokok (NRP)
                    </label>

                    <div class="nrp-input-wrapper">
                        <i class="bi bi-person-vcard nrp-input-icon"></i>
                        <input
                            type="text"
                            id="nrpInput"
                            name="nrp"
                            value="{{ old('nrp') }}"
                            maxlength="9"
                            class="nrp-form-control @error('nrp') is-invalid-nrp @enderror"
                            placeholder="Contoh: 160423018"
                            autocomplete="off"
                            inputmode="numeric"
                        >
                    </div>

                    <!-- Detected Faculty Preview -->
                    <div id="nrpDetectedBox" class="nrp-detected-box d-none">
                        <i class="bi bi-check-circle-fill"></i>
                        <span id="nrpDetectedText"></span>
                    </div>

                    <!-- Validation Error -->
                    <div id="nrpErrorBox" class="nrp-error-box {{ $errors->has('nrp') ? '' : 'd-none' }}">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        <span id="nrpErrorText">{{ $errors->first('nrp') }}</span>
                    </div>

                    <div id="nrpHintText" class="nrp-hint {{ $errors->has('nrp') ? 'd-none' : '' }}">
                        9 digit angka sesuai kartu mahasiswa aktif.
                    </div>

                    <button type="submit" id="btnSubmitNrp" class="btn-portal-maroon mt-3">
                        <span>Masuk sebagai Mahasiswa</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- Form Tersembunyi untuk Otomatis Masuk ke Tampilan Guest -->
            <form id="portalGuestForm" action="{{ route('portal.session') }}" method="POST" class="d-none">
                @csrf
            </form>

            <footer class="portal-footer">
                &copy; {{ date('Y') }} KMS &middot; Layanan Informasi Akademik
            </footer>
        </section>
    </div>
</main>

<!-- Bootstrap 5.3 JS Bundle & jsQR -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
    let mediaStream = null;
    let isCameraActive = false;
    let scanAnimationFrame = null;
    let currentFacingMode = 'environment';

    const fakultasMap       = @json(\App\Http\Controllers\PortalController::FAKULTAS_MAP);
    const portalNrpForm     = document.getElementById('portalNrpForm');
    const nrpInput          = document.getElementById('nrpInput');
    const nrpErrorBox       = document.getElementById('nrpErrorBox');
    const nrpErrorText      = document.getElementById('nrpErrorText');
    const nrpDetectedBox    = document.getElementById('nrpDetectedBox');
    const nrpDetectedText   = document.getElementById('nrpDetectedText');
    const nrpHintText       = document.getElementById('nrpHintText');
    const cameraViewport    = document.getElementById('cameraViewport');
    const cameraVideo       = document.getElementById('cameraVideo');
    const qrCanvas          = document.getElementById('qrCanvas');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const btnSwitchCamera   = document.getElementById('btnSwitchCamera');
    const btnToggleCamera   = document.getElementById('btnToggleCamera');
    const btnCameraIcon     = document.getElementById('btnCameraIcon');
    const btnCameraLabel    = document.getElementById('btnCameraLabel');
    const cameraAlertBox    = document.getElementById('cameraAlertBox');
    const cameraAlertIcon   = document.getElementById('cameraAlertIcon');
    const cameraAlertMsg    = document.getElementById('cameraAlertMessage');

    function enterAsGuest() {
        if (isCameraActive) {
            stopCamera();
        }
        document.getElementById('portalGuestForm')?.submit();
    }

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

    const CAMERA_AUTO_KEY   = 'kms_portal_camera_auto';

    async function toggleCamera() {
        if (isCameraActive) {
            stopCamera(true);
        } else {
            await startCamera();
        }
    }

    async function startCamera() {
        hideCameraAlert();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraAlert('Browser tidak mendukung akses kamera. Gunakan HTTPS/localhost atau masuk melalui tab Input NRP.', 'warning');
            return;
        }

        btnToggleCamera.disabled = true;
        btnCameraLabel.textContent = 'Meminta izin kamera...';

        try {
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
            } catch (_) {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            }

            mediaStream = stream;
            cameraVideo.srcObject = stream;
            await cameraVideo.play();

            isCameraActive = true;
            try { localStorage.setItem(CAMERA_AUTO_KEY, '1'); } catch (_) {}
            cameraViewport.classList.add('is-active');
            cameraVideo.classList.remove('d-none');
            cameraPlaceholder.classList.add('d-none');
            btnSwitchCamera.classList.remove('d-none');

            btnToggleCamera.disabled = false;
            btnToggleCamera.classList.add('is-stop');
            btnCameraIcon.className = 'bi bi-camera-video-off';
            btnCameraLabel.textContent = 'Matikan Kamera';

            scanAnimationFrame = requestAnimationFrame(scanQrFrame);
        } catch (err) {
            btnToggleCamera.disabled = false;
            btnToggleCamera.classList.remove('is-stop');
            btnCameraIcon.className = 'bi bi-camera';
            btnCameraLabel.textContent = 'Aktifkan Kamera';

            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                showCameraAlert('Izin kamera ditolak. Izinkan akses kamera di pengaturan browser lalu coba kembali.', 'danger');
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                showCameraAlert('Kamera tidak ditemukan pada perangkat ini. Silakan gunakan tab Input NRP.', 'warning');
            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                showCameraAlert('Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.', 'warning');
            } else {
                showCameraAlert('Gagal mengaktifkan kamera (' + (err.message || 'Error tidak diketahui') + ').', 'danger');
            }
        }
    }

    function stopCamera(isManual = false) {
        if (isManual) {
            try { localStorage.setItem(CAMERA_AUTO_KEY, '0'); } catch (_) {}
        }
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
        btnSwitchCamera.classList.add('d-none');

        btnToggleCamera.disabled = false;
        btnToggleCamera.classList.remove('is-stop');
        btnCameraIcon.className = 'bi bi-camera';
        btnCameraLabel.textContent = 'Aktifkan Kamera';
    }

    async function switchCameraFacing() {
        if (!isCameraActive) return;
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        stopCamera();
        await startCamera();
    }

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

    function validateNrpValue(nrp) {
        if (!/^\d{9}$/.test(nrp)) {
            return {
                valid: false,
                message: 'NRP harus terdiri dari tepat 9 digit angka (contoh: 160423018).'
            };
        }

        const kodeFakultas  = nrp.substring(0, 2);
        const kodeProdi     = nrp.substring(2, 4);
        const kodeAngkatan  = nrp.substring(4, 6);
        const tahunAngkatan = 2000 + parseInt(kodeAngkatan, 10);
        const maxYear       = new Date().getFullYear() + 1;

        if (!Object.prototype.hasOwnProperty.call(fakultasMap, kodeFakultas)) {
            return {
                valid: false,
                message: `Kode fakultas '${kodeFakultas}' pada NRP tidak terdaftar di sistem.`
            };
        }

        if (isNaN(tahunAngkatan) || tahunAngkatan < 2010 || tahunAngkatan > maxYear) {
            return {
                valid: false,
                message: `Kode angkatan '${kodeAngkatan}' (tahun ${tahunAngkatan}) pada NRP tidak valid.`
            };
        }

        return {
            valid: true,
            kodeFakultas,
            namaFakultas: fakultasMap[kodeFakultas],
            kodeProdi,
            kodeAngkatan,
            tahunAngkatan
        };
    }

    function showNrpError(msg) {
        nrpDetectedBox?.classList.add('d-none');
        nrpHintText?.classList.add('d-none');
        if (nrpErrorBox && nrpErrorText) {
            nrpErrorText.textContent = msg;
            nrpErrorBox.classList.remove('d-none');
        }
        nrpInput?.classList.add('is-invalid-nrp');
    }

    function hideNrpFeedback() {
        nrpErrorBox?.classList.add('d-none');
        nrpDetectedBox?.classList.add('d-none');
        nrpHintText?.classList.remove('d-none');
        nrpInput?.classList.remove('is-invalid-nrp');
    }

    function syncNrpState() {
        const cleaned = (nrpInput.value || '').replace(/\D/g, '').slice(0, 9);
        if (nrpInput.value !== cleaned) {
            nrpInput.value = cleaned;
        }

        if (cleaned.length < 9) {
            hideNrpFeedback();
            return;
        }

        const check = validateNrpValue(cleaned);
        if (check.valid) {
            nrpErrorBox?.classList.add('d-none');
            nrpHintText?.classList.add('d-none');
            nrpInput.classList.remove('is-invalid-nrp');
            if (nrpDetectedBox && nrpDetectedText) {
                nrpDetectedText.textContent = `${check.namaFakultas} · Angkatan ${check.tahunAngkatan}`;
                nrpDetectedBox.classList.remove('d-none');
            }
        } else {
            showNrpError(check.message);
        }
    }

    function handleQrDetected(qrValue) {
        stopCamera();

        const nrpMatch = qrValue.match(/\b\d{9}\b/);
        const extractedNrp = nrpMatch ? nrpMatch[0] : qrValue.replace(/\D/g, '');

        nrpInput.value = extractedNrp.slice(0, 9);
        const validation = validateNrpValue(extractedNrp);

        if (!validation.valid) {
            showCameraAlert('QR terbaca (' + (extractedNrp || qrValue) + '), namun tidak valid: ' + validation.message, 'danger');
            return;
        }

        showCameraAlert(
            `Terverifikasi: ${validation.namaFakultas} (${validation.tahunAngkatan}). Mengalihkan...`,
            'success'
        );

        if (portalNrpForm) {
            portalNrpForm.submit();
        }
    }

    nrpInput.addEventListener('input', syncNrpState);

    if (portalNrpForm) {
        portalNrpForm.addEventListener('submit', function (e) {
            const val = (nrpInput.value || '').trim();
            if (val === '') {
                e.preventDefault();
                showNrpError('Masukkan 9 digit NRP Anda, atau pilih "Umum" di atas.');
                nrpInput.focus();
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

    if ((nrpInput.value || '').trim().length === 9) {
        syncNrpState();
    }

    window.addEventListener('beforeunload', () => {
        if (isCameraActive) stopCamera(false);
    });

    // Nyalakan kamera otomatis bila user sebelumnya sudah mengizinkan & tidak mematikan secara manual
    (async function initAutoCamera() {
        let pref = null;
        try { pref = localStorage.getItem(CAMERA_AUTO_KEY); } catch (_) {}
        if (pref === '0') return;

        if (pref === '1') {
            startCamera();
            return;
        }

        if (navigator.permissions && navigator.permissions.query) {
            try {
                const perm = await navigator.permissions.query({ name: 'camera' });
                if (perm.state === 'granted') {
                    startCamera();
                }
            } catch (_) {}
        }
    })();
</script>

</body>
</html>

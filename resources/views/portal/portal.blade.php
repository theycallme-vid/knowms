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
                <a href="{{ url('/portal') }}" class="d-inline-flex align-items-center text-decoration-none mb-3">
                    <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="portal-logo-img">
                </a>
                <h1 id="portalHeading" class="portal-title">Portal Informasi Akademik</h1>
                <p class="portal-subtitle">
                    Masukkan NRP atau pindai QR KTM Digital untuk menampilkan informasi sesuai fakultas Anda.
                </p>
            </div>

            @php
                $showNrpFirst = $errors->has('nrp') || filled(old('nrp'));
            @endphp

            <!-- Segmented Switch: Scan QR KTM (Utama) | Input NRP -->
            <div class="portal-segmented" role="tablist" aria-label="Metode masuk mahasiswa">
                <button
                    type="button"
                    id="tabBtnQr"
                    class="portal-segment-btn {{ $showNrpFirst ? '' : 'active' }}"
                    role="tab"
                    aria-selected="{{ $showNrpFirst ? 'false' : 'true' }}"
                    aria-controls="paneQr"
                    onclick="switchPortalTab('qr')"
                >
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Scan QR KTM</span>
                </button>
                <button
                    type="button"
                    id="tabBtnNrp"
                    class="portal-segment-btn {{ $showNrpFirst ? 'active' : '' }}"
                    role="tab"
                    aria-selected="{{ $showNrpFirst ? 'true' : 'false' }}"
                    aria-controls="paneNrp"
                    onclick="switchPortalTab('nrp')"
                >
                    <i class="bi bi-person-vcard"></i>
                    <span>Input NRP</span>
                </button>
            </div>

            <!-- TAB PANE 1: SCAN QR KTM (DEFAULT UTAMA) -->
            <div id="paneQr" class="{{ $showNrpFirst ? 'd-none' : '' }}" role="tabpanel" aria-labelledby="tabBtnQr">
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

                    <div class="scanner-frame">
                        <span class="scanner-corner corner-tl"></span>
                        <span class="scanner-corner corner-tr"></span>
                        <span class="scanner-corner corner-bl"></span>
                        <span class="scanner-corner corner-br"></span>
                    </div>

                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <div class="camera-icon-card">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <div class="camera-placeholder-title">Kamera belum aktif</div>
                        <div class="camera-placeholder-desc">
                            Arahkan QR KTM Digital dari aplikasi MyUbaya ke dalam bingkai.
                        </div>
                    </div>

                    <div class="camera-live-badge d-none" id="cameraLiveBadge">
                        <span class="live-dot"></span>
                        <span>Kamera aktif &middot; Pindai QR KTM</span>
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

            <!-- TAB PANE 2: INPUT NRP -->
            <div id="paneNrp" class="{{ $showNrpFirst ? '' : 'd-none' }}" role="tabpanel" aria-labelledby="tabBtnNrp">
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

            <!-- Divider & Single Guest Action -->
            <div class="portal-divider">
                <span>atau</span>
            </div>

            <form action="{{ route('portal.session') }}" method="POST">
                @csrf
                <button type="submit" class="btn-portal-guest">
                    <span>Masuk sebagai Tamu (Umum)</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </section>

        <footer class="portal-footer">
            &copy; {{ date('Y') }} KMS &middot; Layanan Informasi Akademik
        </footer>
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

    const tabBtnNrp         = document.getElementById('tabBtnNrp');
    const tabBtnQr          = document.getElementById('tabBtnQr');
    const paneNrp           = document.getElementById('paneNrp');
    const paneQr            = document.getElementById('paneQr');

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

    const fakultasMap       = @json(\App\Http\Controllers\PortalController::FAKULTAS_MAP);
    const portalNrpForm     = document.getElementById('portalNrpForm');
    const nrpInput          = document.getElementById('nrpInput');
    const nrpErrorBox       = document.getElementById('nrpErrorBox');
    const nrpErrorText      = document.getElementById('nrpErrorText');
    const nrpDetectedBox    = document.getElementById('nrpDetectedBox');
    const nrpDetectedText   = document.getElementById('nrpDetectedText');
    const nrpHintText       = document.getElementById('nrpHintText');

    function switchPortalTab(tab) {
        const isNrp = tab === 'nrp';
        tabBtnNrp.classList.toggle('active', isNrp);
        tabBtnNrp.setAttribute('aria-selected', isNrp ? 'true' : 'false');
        tabBtnQr.classList.toggle('active', !isNrp);
        tabBtnQr.setAttribute('aria-selected', !isNrp ? 'true' : 'false');

        paneNrp.classList.toggle('d-none', !isNrp);
        paneQr.classList.toggle('d-none', isNrp);

        if (isNrp && isCameraActive) {
            stopCamera();
        }
        if (isNrp) {
            nrpInput?.focus();
        }
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

    async function toggleCamera() {
        if (isCameraActive) {
            stopCamera();
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
            cameraViewport.classList.add('is-active');
            cameraVideo.classList.remove('d-none');
            cameraPlaceholder.classList.add('d-none');
            cameraLiveBadge.classList.remove('d-none');
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
                showNrpError('Masukkan 9 digit NRP Anda, atau pilih "Masuk sebagai Tamu" di bawah.');
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
        if (isCameraActive) stopCamera();
    });
</script>

</body>
</html>

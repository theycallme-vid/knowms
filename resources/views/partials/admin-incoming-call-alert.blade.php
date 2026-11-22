<!-- ========================================================================= -->
<!-- NOTIFIKASI PANGGILAN VIDEO MASUK REAL-TIME UNTUK ADMIN                    -->
<!-- ========================================================================= -->
<style>
    .admin-call-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 32px);
        background: #1E293B;
        color: #FFFFFF;
        border: 2px solid #85172C;
        border-radius: 18px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.35);
        z-index: 1060;
        overflow: hidden;
        animation: callToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: none;
    }

    .admin-call-toast.active {
        display: block;
    }

    @keyframes callToastSlideIn {
        from {
            transform: translateY(60px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .call-bell-ring {
        display: inline-block;
        animation: bellRing 1.5s infinite ease-in-out;
        transform-origin: 50% 0%;
    }

    @keyframes bellRing {
        0% { transform: rotate(0); }
        10% { transform: rotate(18deg); }
        20% { transform: rotate(-18deg); }
        30% { transform: rotate(14deg); }
        40% { transform: rotate(-14deg); }
        50% { transform: rotate(10deg); }
        60% { transform: rotate(-10deg); }
        70% { transform: rotate(0); }
        100% { transform: rotate(0); }
    }

    .call-pulse-glow {
        box-shadow: 0 0 0 0 rgba(133, 23, 44, 0.7);
        animation: callGlow 1.8s infinite;
    }

    @keyframes callGlow {
        0% { box-shadow: 0 0 0 0 rgba(133, 23, 44, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(133, 23, 44, 0); }
        100% { box-shadow: 0 0 0 0 rgba(133, 23, 44, 0); }
    }
</style>

<div id="adminIncomingCallToast" class="admin-call-toast call-pulse-glow" role="alert" aria-live="assertive">
    <div class="p-3" style="background: linear-gradient(180deg, #2D141A 0%, #1E293B 100%);">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge rounded-pill bg-danger d-inline-flex align-items-center gap-1.5 px-2.5 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-bell-fill call-bell-ring"></i> Panggilan Masuk Layanan Akademik!
            </span>
            <button type="button" class="btn btn-sm text-secondary p-0" onclick="dismissIncomingCall()" title="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2.5 mt-2 mb-2">
            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 1.15rem; flex-shrink: 0;">
                <i class="bi bi-telephone-inbound-fill"></i>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-white fs-14 text-truncate" id="incomingCallStudentName">
                    Mahasiswa (Fakultas Teknik)
                </div>
                <div class="fs-12 text-secondary text-truncate" id="incomingCallMeta">
                    Menunggu di ruang video call portal KMS
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-dark bg-opacity-50 border border-secondary mb-3 fs-11 text-light">
            <span>Room ID:</span>
            <span class="font-monospace fw-bold text-warning text-truncate" style="max-width: 220px;" id="incomingCallRoomId">kms-layanan-xxx</span>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary text-light flex-fill fs-12 rounded-pill" onclick="dismissIncomingCall()">
                Abaikan
            </button>
            <a href="#" id="btnAcceptIncomingCall" class="btn btn-sm btn-success flex-fill fs-12 fw-semibold rounded-pill d-flex align-items-center justify-content-center gap-1.5" style="background-color: #059669; border-color: #059669;">
                <i class="bi bi-telephone-inbound-fill"></i>
                <span>Terima Panggilan</span>
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    let dismissedCallIds = new Set();
    let currentCall = null;
    let audioCtx = null;

    /**
     * Suara panggilan lembut menggunakan Web Audio API tanpa butuh file audio eksternal
     */
    function playChime() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const now = audioCtx.currentTime;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now); // D5
            osc.frequency.setValueAtTime(880, now + 0.15); // A5

            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(now);
            osc.stop(now + 0.6);
        } catch (e) {
            // Audio autoplay policy
        }
    }

    /**
     * Periksa panggilan video aktif dari mahasiswa setiap 4 detik
     */
    async function checkIncomingCalls() {
        try {
            const res = await fetch("{{ route('layanan.videocall.api.active') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (data.success && data.calls && data.calls.length > 0) {
                // Ambil panggilan pertama yang belum diabaikan
                const call = data.calls.find(c => !dismissedCallIds.has(c.id));
                if (call) {
                    currentCall = call;
                    showIncomingCallAlert(call);
                } else {
                    hideIncomingCallAlert();
                }
            } else {
                hideIncomingCallAlert();
            }
        } catch (err) {
            // Silently ignore polling errors
        }
    }

    function showIncomingCallAlert(call) {
        const toastEl = document.getElementById('adminIncomingCallToast');
        const nameEl = document.getElementById('incomingCallStudentName');
        const metaEl = document.getElementById('incomingCallMeta');
        const roomEl = document.getElementById('incomingCallRoomId');
        const acceptBtn = document.getElementById('btnAcceptIncomingCall');

        if (!toastEl) return;

        if (nameEl) nameEl.textContent = call.nama_mahasiswa || 'Mahasiswa';
        if (metaEl) metaEl.textContent = `Menunggu bantuan (${call.fakultas || 'Layanan Akademik'}) &middot; ${call.created_at_diff}`;
        if (roomEl) roomEl.textContent = call.room_id;
        if (acceptBtn) acceptBtn.href = call.join_url;

        if (!toastEl.classList.contains('active')) {
            toastEl.classList.add('active');
            playChime();
        }
    }

    function hideIncomingCallAlert() {
        const toastEl = document.getElementById('adminIncomingCallToast');
        if (toastEl) toastEl.classList.remove('active');
    }

    window.dismissIncomingCall = function() {
        if (currentCall) {
            dismissedCallIds.add(currentCall.id);
        }
        hideIncomingCallAlert();
    };

    // Jalankan poller berkala
    setInterval(checkIncomingCalls, 3500);
    setTimeout(checkIncomingCalls, 1500);
})();
</script>

<style>
    .kms-unit-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background-color: #F8FAFC;
        color: #334155;
        border: 1px solid #CBD5E1;
        font-size: 0.69rem;
        font-weight: 600;
        padding: 0.18rem 0.58rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .kms-unit-pill.is-pusat {
        background-color: #FFF7ED;
        color: #9A3412;
        border-color: #FED7AA;
    }
    .kms-scope-tabs {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background-color: #F4F1EC;
        border: 1px solid #E5E0D8;
        padding: 0.28rem;
        border-radius: 999px;
    }
    .kms-scope-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.95rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .kms-scope-tab:hover {
        color: #1E293B;
    }
    .kms-scope-tab.active {
        background-color: #85172C;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(133, 23, 44, 0.2);
    }
    .kms-stepper-box {
        background-color: #FAF9F6;
        border: 1px solid #EDE7E1;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
    }
    .kms-stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.35rem;
        position: relative;
    }
    .kms-step-item {
        flex: 1 1 0%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        min-width: 0;
    }
    .kms-step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 13px;
        left: calc(50% + 16px);
        width: calc(100% - 32px);
        height: 2px;
        background-color: #E2E8F0;
        z-index: 1;
    }
    .kms-step-item.is-done:not(:last-child)::after {
        background-color: #059669;
    }
    .kms-step-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #FFFFFF;
        border: 2px solid #CBD5E1;
        color: #94A3B8;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .kms-step-item.is-done .kms-step-dot {
        background-color: #ECFDF5;
        border-color: #059669;
        color: #059669;
    }
    .kms-step-item.is-active .kms-step-dot {
        background-color: #FFFBEB;
        border-color: #D97706;
        color: #D97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.14);
    }
    .kms-step-item.is-rejected .kms-step-dot {
        background-color: #FEF2F2;
        border-color: #DC2626;
        color: #DC2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.14);
    }
    .kms-step-label {
        font-size: 0.71rem;
        font-weight: 600;
        color: #64748B;
        margin-top: 0.35rem;
        line-height: 1.25;
    }
    .kms-step-item.is-done .kms-step-label {
        color: #065F46;
    }
    .kms-step-item.is-active .kms-step-label {
        color: #B45309;
        font-weight: 700;
    }
    .kms-step-item.is-rejected .kms-step-label {
        color: #B91C1C;
        font-weight: 700;
    }
    .kms-mini-stepper {
        display: inline-flex;
        align-items: center;
        gap: 0.22rem;
        flex-wrap: wrap;
        margin-top: 0.35rem;
    }
    .kms-mini-step {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.12rem 0.45rem;
        border-radius: 999px;
        border: 1px solid #E2E8F0;
        background-color: #F8FAFC;
        color: #94A3B8;
    }
    .kms-mini-step.done {
        background-color: #ECFDF5;
        border-color: #A7F3D0;
        color: #059669;
    }
    .kms-mini-step.current {
        background-color: #FFFBEB;
        border-color: #FDE68A;
        color: #B45309;
    }
    .kms-mini-step.rejected {
        background-color: #FEF2F2;
        border-color: #FECACA;
        color: #DC2626;
    }
    .kms-timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.55rem 0.75rem;
        background-color: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
    }
    .kms-sim-box {
        background-color: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        padding: 0.85rem 1rem;
    }
    .kms-sim-item {
        background-color: #FFFFFF;
        border: 1px solid #F3E8C8;
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
    }
</style>

<script>
    window.kmsEscapeHtml = function (str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    window.kmsFormatStatusBadgeHtml = function (statusRaw, rejectionLabel) {
        const rawSt = String(statusRaw || 'Draf').toLowerCase();
        if (rawSt === 'terbit' || rawSt === 'active') {
            return '<span class="badge rounded-pill" style="background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#FFFFFF;box-shadow:0 2px 6px rgba(5,150,105,0.25);font-size:0.75rem;padding:0.4rem 0.9rem;font-weight:700;"><i class="bi bi-lightning-fill me-1"></i>Terbit</span>';
        }
        if (rawSt === 'diterima' || rawSt === 'approved') {
            return '<span class="badge rounded-pill" style="background-color:#EDE9FE;color:#6D28D9;border:1px solid #DDD6FE;font-size:0.75rem;padding:0.4rem 0.85rem;"><i class="bi bi-check-circle-fill me-1"></i>Diterima</span>';
        }
        if (rawSt === 'menunggu validasi unit') {
            return '<span class="badge rounded-pill" style="background-color:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;font-size:0.75rem;padding:0.4rem 0.85rem;font-weight:600;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi Unit</span>';
        }
        if (rawSt === 'menunggu validasi admik' || rawSt === 'menunggu validasi' || rawSt === 'submitted') {
            return '<span class="badge rounded-pill" style="background-color:#FFFBEB;color:#D97706;border:1px solid #FDE68A;font-size:0.75rem;padding:0.4rem 0.85rem;font-weight:600;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Validasi Admik</span>';
        }
        if (rawSt === 'ditolak' || rawSt === 'rejected') {
            const label = window.kmsEscapeHtml(rejectionLabel || 'Ditolak');
            const isUnitRej = String(rejectionLabel || '').toLowerCase().includes('validator unit');
            const styleStr = isUnitRej
                ? 'background-color:#FFF1F2;color:#BE123C;border:1px solid #FECDD3;'
                : 'background-color:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;';
            return `<span class="badge rounded-pill" style="${styleStr}font-size:0.75rem;padding:0.4rem 0.85rem;font-weight:700;"><i class="bi bi-x-circle-fill me-1"></i>${label}</span>`;
        }
        return '<span class="badge rounded-pill" style="background-color:#F1F5F9;color:#475569;font-size:0.75rem;padding:0.4rem 0.85rem;">Draf</span>';
    };

    window.kmsBuildMilestoneHtml = function (data) {
        if (!data) return '';
        const latest = data.latest_version || {};
        const rawSt = String(latest.status || data.raw_status || 'Draf').toLowerCase();
        const isPusat = Boolean(data.is_pusat_unit);
        const rejStage = String(data.rejected_by_stage || latest.rejected_by_stage || '').toLowerCase();
        const logs = Array.isArray(data.milestone_logs) ? data.milestone_logs : [];

        const steps = isPusat
            ? [
                { key: 'draf', label: 'Draf / Pengajuan' },
                { key: 'admik', label: 'Validasi Admik' },
                { key: 'diterima', label: 'Diterima' },
                { key: 'terbit', label: 'Terbit' },
            ]
            : [
                { key: 'draf', label: 'Draf / Pengajuan' },
                { key: 'unit', label: 'Validasi Unit' },
                { key: 'admik', label: 'Validasi Admik' },
                { key: 'diterima', label: 'Diterima' },
                { key: 'terbit', label: 'Terbit' },
            ];

        const getStepState = (stepKey) => {
            if (rawSt === 'terbit' || rawSt === 'active') return 'done';
            if (rawSt === 'diterima' || rawSt === 'approved') {
                if (stepKey === 'terbit') return 'pending';
                if (stepKey === 'diterima') return 'active';
                return 'done';
            }
            if (rawSt === 'menunggu validasi admik' || rawSt === 'menunggu validasi') {
                if (stepKey === 'draf' || stepKey === 'unit') return 'done';
                if (stepKey === 'admik') return 'active';
                return 'pending';
            }
            if (rawSt === 'menunggu validasi unit') {
                if (stepKey === 'draf') return 'done';
                if (stepKey === 'unit') return 'active';
                return 'pending';
            }
            if (rawSt === 'ditolak' || rawSt === 'rejected') {
                if (stepKey === 'draf') return 'done';
                if (!isPusat && rejStage === 'unit') {
                    if (stepKey === 'unit') return 'rejected';
                    return 'pending';
                }
                if (stepKey === 'unit') return 'done';
                if (stepKey === 'admik') return 'rejected';
                return 'pending';
            }
            // Draf
            return stepKey === 'draf' ? 'active' : 'pending';
        };

        const stepperHtml = steps.map((s, idx) => {
            const state = getStepState(s.key);
            const cls = state === 'done' ? 'is-done' : (state === 'active' ? 'is-active' : (state === 'rejected' ? 'is-rejected' : ''));
            const icon = state === 'done'
                ? '<i class="bi bi-check-lg"></i>'
                : (state === 'rejected' ? '<i class="bi bi-x-lg"></i>' : (state === 'active' ? '<i class="bi bi-hourglass-split"></i>' : (idx + 1)));
            return `
                <div class="kms-step-item ${cls}">
                    <span class="kms-step-dot">${icon}</span>
                    <span class="kms-step-label">${window.kmsEscapeHtml(s.label)}</span>
                </div>
            `;
        }).join('');

        return `
            <div class="kms-stepper-box">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <span class="text-uppercase fw-bold" style="font-size: 0.71rem; color: #85172C; letter-spacing: 0.06em;">
                        <i class="bi bi-signpost-split me-1"></i>Milestone Status Validasi
                    </span>
                    ${data.unit_nama ? `<span class="kms-unit-pill ${isPusat ? 'is-pusat' : ''}"><i class="bi ${isPusat ? 'bi-bank2' : 'bi-building'}"></i>${window.kmsEscapeHtml(data.unit_nama)}</span>` : ''}
                </div>
                <div class="kms-stepper">${stepperHtml}</div>
            </div>
        `;
    };

    window.kmsBuildSimilarFaqsHtml = function (similarList) {
        const items = Array.isArray(similarList) ? similarList : [];
        if (items.length === 0) {
            return `
                <div class="rounded-3 px-3 py-2 d-flex align-items-center gap-2" style="background-color:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;font-size:0.78rem;">
                    <i class="bi bi-shield-check fs-6"></i>
                    <span>Tidak ditemukan FAQ lain yang mirip di seluruh unit (aman dari indikasi duplikasi).</span>
                </div>
            `;
        }

        return `
            <div class="kms-sim-box">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div class="fw-bold text-dark" style="font-size:0.79rem;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                        Ditemukan ${items.length} FAQ Lain yang Memiliki Kemiripan (Lintas Seluruh Unit)
                    </div>
                    <span class="text-muted" style="font-size:0.71rem;">Periksa sebelum menyetujui agar tidak duplikat</span>
                </div>
                <div class="d-flex flex-column gap-2" style="max-height: 210px; overflow-y: auto;">
                    ${items.map((sim, idx) => {
                        const pct = Number(sim.similarity || 0);
                        const simPillStyle = pct >= 70
                            ? 'background-color:#DC2626;color:#FFFFFF;'
                            : 'background-color:#FEF3C7;color:#92400E;border:1px solid #FDE68A;';
                        const collapseId = `kmsSimAns_${idx}_${String(sim.id || '').replace(/[^a-zA-Z0-9]/g, '')}`;
                        return `
                            <div class="kms-sim-item">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">
                                            <span class="fw-bold text-dark" style="font-size:0.78rem;">${window.kmsEscapeHtml(sim.id)}</span>
                                            <span class="kms-unit-pill"><i class="bi bi-building"></i>${window.kmsEscapeHtml(sim.unit_nama || 'Direktorat Akademik')}</span>
                                            <span class="badge rounded-pill bg-light text-secondary border" style="font-size:0.68rem;">${window.kmsEscapeHtml(sim.status_label || 'Terbit')}</span>
                                            <span class="badge rounded-pill fw-bold" style="${simPillStyle}font-size:0.69rem;padding:0.22rem 0.55rem;">${pct}% Mirip</span>
                                        </div>
                                        <div class="text-dark fw-medium" style="font-size:0.82rem;line-height:1.45;">${window.kmsEscapeHtml(sim.pertanyaan)}</div>
                                    </div>
                                    ${sim.jawaban ? `
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 flex-shrink-0" style="font-size:0.7rem;" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                                            Bandingkan Jawaban
                                        </button>
                                    ` : ''}
                                </div>
                                ${sim.jawaban ? `
                                    <div id="${collapseId}" class="collapse mt-2 pt-2" style="border-top:1px dashed #E2E8F0;">
                                        <div class="text-muted" style="font-size:0.75rem;line-height:1.5;">
                                            <strong class="text-dark">Ringkasan Jawaban ${window.kmsEscapeHtml(sim.id)}:</strong> ${window.kmsEscapeHtml(sim.jawaban)}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    };
</script>

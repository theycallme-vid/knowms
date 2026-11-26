@php
    $part = $part ?? 'bar';
    $isGuestUserFaq = session('is_guest') === true || session('nama_fakultas') === 'Guest' || !session('kode_fakultas');
@endphp

@if($part === 'navbar')
<div id="navbarSearchSlot" class="navbar-search-slot" aria-label="Pencarian cepat di navigasi"></div>
@elseif($part === 'bar')
<style>
    .hero-section {
        z-index: 20;
    }

    /* Full-screen Google-like Hero Layout */
    .hero-section.hero-fullscreen {
        min-height: calc(100vh - 74px);
        min-height: calc(100svh - 74px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2.5rem 0 5.25rem;
        background:
            radial-gradient(circle at 50% 42%,
                rgba(248, 249, 251, 0.62) 0%,
                rgba(248, 249, 251, 0.42) 55%,
                rgba(248, 249, 251, 0.24) 100%),
            url('/images/mahasiswa/banner-faq.jpg') no-repeat center center / cover;
    }

    .hero-center-stack {
        width: 100%;
        max-width: 880px;
        margin: 0 auto;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* ===================================================================== */
    /* iOS 26 LIQUID GLASS — MINIMAL, RAZOR-THIN 1PX SPECULAR RIM            */
    /* ===================================================================== */
    .mhs-navbar,
    .guest-navbar {
        background: rgba(255, 255, 255, 0.66);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    /* Clear Translucent Liquid Glass (Secondary Pills & Controls) */
    .hero-context-badge,
    .hero-quick-chips,
    .hero-scroll-cue,
    .navbar-context-pill,
    .navbar-btn-icon,
    .cat-pill,
    .faq-side-nav-btn,
    .faq-page-btn {
        position: relative;
        background: rgba(255, 255, 255, 0.36);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 0;
        box-shadow:
            inset 0 0.75px 0 rgba(255, 255, 255, 0.65),
            0 8px 24px -8px rgba(15, 23, 42, 0.10),
            0 1px 2px rgba(15, 23, 42, 0.04);
    }

    /* Masked 1px Diagonal Specular Rim (Authentic iOS 26 Glass Edge) */
    .hero-context-badge::after,
    .hero-quick-chips::after,
    .hero-scroll-cue::after,
    .navbar-context-pill::after,
    .navbar-btn-icon::after,
    .cat-pill::after,
    .faq-side-nav-btn::after,
    .faq-page-btn::after,
    .btn-maroon::after,
    .hero-search::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.90) 0%,
            rgba(255, 255, 255, 0.22) 36%,
            rgba(255, 255, 255, 0.06) 64%,
            rgba(255, 255, 255, 0.62) 100%
        );
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: exclude;
        pointer-events: none;
    }

    /* Maroon / Red Tinted Liquid Glass (Clean Flat Translucent + 1px Specular Rim) */
    .btn-maroon,
    .cat-pill.active,
    .faq-page-btn.active {
        position: relative;
        background: rgba(133, 23, 44, 0.82) !important;
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 0 !important;
        color: #FFFFFF !important;
        box-shadow:
            inset 0 0.75px 0 rgba(255, 255, 255, 0.34),
            0 10px 24px -8px rgba(133, 23, 44, 0.38),
            0 2px 6px -1px rgba(133, 23, 44, 0.18) !important;
    }

    .btn-maroon::after,
    .cat-pill.active::after,
    .faq-page-btn.active::after {
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.52) 0%,
            rgba(255, 255, 255, 0.12) 38%,
            rgba(255, 255, 255, 0.04) 64%,
            rgba(255, 214, 222, 0.36) 100%
        );
    }

    .btn-maroon:hover,
    .cat-pill.active:hover {
        background: rgba(148, 26, 50, 0.88) !important;
        transform: translateY(-1px);
        box-shadow:
            inset 0 0.75px 0 rgba(255, 255, 255, 0.42),
            0 12px 28px -8px rgba(133, 23, 44, 0.44),
            0 2px 8px -1px rgba(133, 23, 44, 0.22) !important;
    }

    .btn-maroon:active,
    .cat-pill:active,
    .suggest-pill:active {
        transform: scale(0.985);
    }

    /* Soft Rose-Tinted Liquid Glass (Badges, Active Nav, Expanded Icons) */
    .tag-soft,
    .btn-history-version,
    .guest-nav-link.active,
    .mhs-nav-link.active,
    .faq-toggle:not(.collapsed) .faq-icon {
        background: rgba(133, 23, 44, 0.09);
        backdrop-filter: blur(14px) saturate(160%);
        -webkit-backdrop-filter: blur(14px) saturate(160%);
        border: 1px solid rgba(133, 23, 44, 0.14);
        box-shadow: inset 0 0.75px 0 rgba(255, 255, 255, 0.55);
    }

    /* Subtle Clean Glass for Category Pills on Light Page Surface */
    .cat-pill:not(.active),
    .faq-side-nav-btn,
    .faq-page-btn:not(.active),
    .navbar-context-pill,
    .navbar-btn-icon {
        background: rgba(255, 255, 255, 0.68);
        border: 1px solid rgba(15, 23, 42, 0.07);
    }

    .cat-pill:not(.active):hover,
    .faq-side-nav-btn:hover:not(:disabled),
    .faq-page-btn:hover:not(:disabled):not(.active) {
        background: rgba(255, 255, 255, 0.92);
        border-color: rgba(133, 23, 44, 0.22);
        color: var(--brand-maroon);
        box-shadow: 0 8px 20px -8px rgba(15, 23, 42, 0.12);
    }

    .source-box {
        background: rgba(234, 242, 249, 0.72);
        backdrop-filter: blur(14px) saturate(160%);
        -webkit-backdrop-filter: blur(14px) saturate(160%);
        border: 1px solid rgba(59, 130, 246, 0.14);
    }

    .disclaimer {
        background: rgba(254, 249, 238, 0.76);
        backdrop-filter: blur(14px) saturate(160%);
        -webkit-backdrop-filter: blur(14px) saturate(160%);
        border: 1px solid rgba(217, 119, 6, 0.16);
    }

    .cta-card {
        background: linear-gradient(135deg, rgba(234, 242, 249, 0.68) 0%, rgba(250, 240, 242, 0.72) 100%);
        backdrop-filter: blur(18px) saturate(170%);
        -webkit-backdrop-filter: blur(18px) saturate(170%);
        border: 1px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 12px 32px -12px rgba(15, 23, 42, 0.08);
    }

    .hero-context-badge {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.46rem 1.1rem;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #0F172A;
    }

    .hero-context-badge i {
        color: var(--brand-maroon);
    }

    .hero-context-badge .hero-badge-muted {
        color: #334155;
        font-weight: 500;
    }

    .hero-fullscreen .hero-title {
        font-size: clamp(2.2rem, 4.2vw, 3.5rem);
        line-height: 1.15;
        max-width: 780px;
        text-shadow: 0 1px 12px rgba(255, 255, 255, 0.7);
    }

    .hero-fullscreen .hero-subtitle {
        font-size: 0.95rem;
        line-height: 1.65;
        max-width: 580px;
        color: #1E293B;
        font-weight: 500;
        text-shadow: 0 1px 10px rgba(255, 255, 255, 0.75);
    }

    .hero-search-anchor {
        width: 100%;
        max-width: 860px;
        min-height: 74px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .hero-search-wrapper {
        position: relative;
        width: 100%;
        max-width: 860px;
        z-index: 30;
        text-align: left;
    }

    /* HIGHLIGHTED SPOTLIGHT GLASS SEARCH BAR (Clean Luminous White vs Clear Secondary Pills) */
    .hero-search {
        position: relative;
    }

    .hero-fullscreen .hero-search {
        max-width: 860px;
        min-height: 74px;
        padding: 0.6rem 0.7rem 0.6rem 1.8rem;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(28px) saturate(180%);
        -webkit-backdrop-filter: blur(28px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.96);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 1),
            0 18px 42px -12px rgba(15, 23, 42, 0.16),
            0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .hero-fullscreen .hero-search:focus-within,
    .hero-fullscreen .hero-search-wrapper.is-dropdown-open .hero-search {
        background: rgba(255, 255, 255, 0.96);
        border-color: rgba(133, 23, 44, 0.42);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 1),
            0 0 0 4px rgba(133, 23, 44, 0.08),
            0 22px 48px -12px rgba(15, 23, 42, 0.18);
    }

    .hero-fullscreen .hero-search i.bi-search {
        font-size: 1.32rem;
        color: var(--brand-maroon);
    }

    .hero-fullscreen .hero-search input {
        font-size: 1.05rem;
        font-weight: 500;
        color: #0F172A;
    }

    .hero-fullscreen .hero-search input::placeholder {
        font-size: 1rem;
        color: #64748B;
        font-weight: 400;
    }

    .hero-fullscreen .hero-search .btn-maroon {
        padding: 0.88rem 2.1rem;
        font-size: 0.9rem;
    }

    /* Quick Search Chips under Hero Search (Clear iOS 26 Glass Bar) */
    .hero-quick-chips {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        margin-top: 1.2rem;
        padding: 0.46rem 0.9rem;
        border-radius: 9999px;
    }

    .hero-quick-chips > span {
        color: #0F172A !important;
        font-weight: 600;
    }

    .suggest-pill {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.88rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.52);
        border: 1px solid rgba(255, 255, 255, 0.72);
        color: #0F172A;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.16s ease;
    }

    .suggest-pill:hover {
        background: rgba(133, 23, 44, 0.84);
        border-color: rgba(255, 255, 255, 0.35);
        color: #FFFFFF;
        box-shadow: 0 6px 16px -6px rgba(133, 23, 44, 0.38);
        transform: translateY(-1px);
    }

    /* Scroll Down Indicator at bottom of Full-Screen Hero */
    .hero-scroll-cue {
        position: absolute;
        bottom: 1.35rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        color: #0F172A;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.5rem 1.18rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .hero-scroll-cue:hover {
        color: var(--brand-maroon);
        background: rgba(255, 255, 255, 0.68);
        transform: translateX(-50%) translateY(-2px);
    }

    .hero-scroll-cue i {
        font-size: 0.85rem;
        animation: heroCueBounce 2.2s infinite ease-in-out;
    }

    @keyframes heroCueBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(3px); }
    }

    /* Docked Search Bar beside KMS Logo in Sticky Navbar (Clean Highlighted White Glass) */
    .navbar-brand-search-group {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex: 1 1 auto;
        min-width: 0;
    }

    .navbar-search-slot {
        flex: 1 1 auto;
        width: 100%;
        max-width: none;
        min-width: 0;
        opacity: 0;
        transform: translateY(8px) scale(0.98);
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .navbar-search-slot.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .navbar-search-slot .hero-search-wrapper {
        max-width: 100%;
        margin-bottom: 0 !important;
    }

    .navbar-search-slot .hero-search {
        min-height: 46px;
        max-width: 100%;
        padding: 0.28rem 0.35rem 0.28rem 1.05rem;
        gap: 0.6rem;
        background: rgba(255, 255, 255, 0.90);
        backdrop-filter: blur(22px) saturate(180%);
        -webkit-backdrop-filter: blur(22px) saturate(180%);
        border: 1px solid rgba(15, 23, 42, 0.09);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 1),
            0 4px 14px -4px rgba(15, 23, 42, 0.08);
    }

    .navbar-search-slot .hero-search:focus-within,
    .navbar-search-slot .hero-search-wrapper.is-dropdown-open .hero-search {
        background: #FFFFFF;
        border-color: rgba(133, 23, 44, 0.45);
        box-shadow:
            0 0 0 3px rgba(133, 23, 44, 0.08),
            0 8px 20px -6px rgba(15, 23, 42, 0.12);
    }

    .navbar-search-slot .hero-search i.bi-search {
        font-size: 1rem;
        color: var(--brand-maroon);
    }

    .navbar-search-slot .hero-search input {
        font-size: 0.875rem;
        font-weight: 500;
        color: #0F172A;
    }

    .navbar-search-slot .hero-search input::placeholder {
        font-size: 0.85rem;
    }

    .navbar-search-slot .hero-search .btn-maroon {
        padding: 0.45rem 1.15rem;
        font-size: 0.78rem;
        gap: 0.4rem;
    }

    .navbar-search-slot .hero-search-clear {
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }

    .navbar-search-slot .faq-search-dropdown {
        top: calc(100% + 8px);
        z-index: 1050;
    }

    .navbar-context-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.92rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #0F172A;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .navbar-context-pill i {
        color: var(--brand-maroon);
    }

    .hero-search-wrapper.is-dropdown-open .hero-search {
        border-color: rgba(133, 23, 44, 0.45);
    }

    .hero-search-clear {
        border: 0;
        background: rgba(15, 23, 42, 0.06);
        color: #64748B;
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.78rem;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .hero-search-clear:hover {
        background: rgba(15, 23, 42, 0.12);
        color: #0F172A;
    }

    .faq-search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(28px) saturate(180%);
        -webkit-backdrop-filter: blur(28px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.92);
        border-radius: 22px;
        box-shadow:
            0 20px 50px -12px rgba(15, 23, 42, 0.18),
            0 0 0 1px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        z-index: 1000;
    }

    .faq-search-dropdown-header {
        padding: 0.65rem 1.25rem 0.4rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #F1F5F9;
    }

    .faq-search-dropdown-list {
        max-height: 360px;
        overflow-y: auto;
    }

    .faq-suggest-item {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 0.8rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        cursor: pointer;
        border-bottom: 1px solid #F8FAFC;
        transition: background-color 0.12s ease;
    }

    .faq-suggest-item:last-child {
        border-bottom: 0;
    }

    .faq-suggest-item:hover,
    .faq-suggest-item.is-active {
        background-color: #FAF6F7;
    }

    .faq-suggest-icon {
        width: 30px;
        height: 30px;
        border-radius: 9999px;
        background: #F8FAFC;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
        font-size: 0.85rem;
    }

    .faq-suggest-item:hover .faq-suggest-icon,
    .faq-suggest-item.is-active .faq-suggest-icon {
        background: var(--brand-maroon-soft);
        color: var(--brand-maroon);
    }

    .faq-suggest-title {
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--text-heading);
        line-height: 1.4;
    }

    .faq-suggest-meta {
        font-size: 0.74rem;
        color: var(--text-muted);
        margin-top: 0.15rem;
    }

    .faq-search-mark {
        background: transparent;
        color: var(--brand-maroon);
        font-weight: 700;
        padding: 0;
    }

    .faq-search-dropdown-footer {
        width: 100%;
        border: 0;
        border-top: 1px solid #F1F5F9;
        background: #FAF9F7;
        padding: 0.7rem 1.25rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--brand-maroon);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        text-align: left;
        transition: background-color 0.15s ease;
    }

    .faq-search-dropdown-footer:hover {
        background: var(--brand-maroon-soft);
    }

    .faq-search-results-section {
        scroll-margin-top: 90px;
        background: #FFFFFF;
        border: 1px solid var(--card-border);
        border-radius: 22px;
        padding: 1.5rem 1.65rem;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
    }

    .faq-search-results-list {
        max-height: 550px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.45rem;
        overscroll-behavior: contain;
    }

    .faq-search-results-list .faq-item:last-child {
        margin-bottom: 0;
    }

    .faq-search-results-list::-webkit-scrollbar {
        width: 7px;
    }

    .faq-search-results-list::-webkit-scrollbar-track {
        background: #F4F1EC;
        border-radius: 999px;
    }

    .faq-search-results-list::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 999px;
    }

    .faq-search-results-list::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    @media (max-width: 991.98px) {
        .is-search-docked .guest-nav-links-wrap,
        .is-search-docked .navbar-context-pill {
            display: none !important;
        }
    }

    @media (max-width: 767.98px) {
        .hero-section.hero-fullscreen {
            min-height: calc(100vh - 64px);
            min-height: calc(100svh - 64px);
            padding: 2rem 0 4.75rem;
        }
        .navbar-brand-search-group {
            gap: 0.65rem;
        }
        .navbar-search-slot .faq-search-dropdown {
            position: fixed;
            top: 66px;
            left: 0.75rem;
            right: 0.75rem;
            width: auto;
        }
    }

    @media (max-width: 576px) {
        .faq-search-results-section {
            padding: 1.15rem 1rem;
            border-radius: 16px;
        }
        .hero-fullscreen .hero-search {
            min-height: 60px;
            gap: 0.5rem;
            padding: 0.45rem 0.5rem 0.45rem 1.15rem;
        }
        .hero-quick-chips {
            border-radius: 26px;
        }
        .hero-fullscreen .hero-search input {
            font-size: 0.95rem;
        }
        .hero-search .btn-maroon span,
        .navbar-search-slot .hero-search .btn-maroon span {
            display: none;
        }
        .hero-fullscreen .hero-search .btn-maroon {
            padding: 0.7rem 0.95rem;
        }
        .navbar-search-slot .hero-search {
            min-height: 40px;
            padding: 0.2rem 0.28rem 0.2rem 0.8rem;
            gap: 0.4rem;
        }
        .navbar-search-slot .hero-search .btn-maroon {
            padding: 0.42rem 0.68rem;
        }
        .is-search-docked .navbar-logo-img {
            height: 40px;
        }
    }
</style>

<div class="hero-search-wrapper" id="faqSearchWrapper">
    <!-- Search Input Form -->
    <form class="hero-search mb-0" id="faqSearchForm" onsubmit="event.preventDefault(); submitFaqSearch();" role="search">
        <i class="bi bi-search"></i>
        <input type="text"
               id="faqSearch"
               autocomplete="off"
               placeholder='Cari pertanyaan, misalnya "cara mengisi KRS"'
               aria-label="Cari pertanyaan"
               aria-autocomplete="list"
               aria-controls="faqSearchDropdown"
               aria-expanded="false">
        <button type="button"
                id="faqSearchClear"
                class="hero-search-clear d-none"
                aria-label="Hapus pencarian"
                title="Hapus pencarian">
            <i class="bi bi-x-lg"></i>
        </button>
        <button type="submit" class="btn-maroon border-0 shadow-sm">
            <span>Cari jawaban</span>
            <i class="bi bi-arrow-right"></i>
        </button>
    </form>

    <!-- Google-like Floating Autocomplete Dropdown -->
    <div id="faqSearchDropdown" class="faq-search-dropdown d-none" role="listbox" aria-label="Saran pencarian FAQ">
        <div class="faq-search-dropdown-header">
            <span>Saran pertanyaan</span>
            <span id="faqDropdownCount"></span>
        </div>
        <div id="faqSearchDropdownList" class="faq-search-dropdown-list"></div>
        <div id="faqSearchDropdownEmpty" class="p-3 d-none">
            <div class="px-2 pb-2.5 text-muted fs-13">
                Tidak ada pertanyaan yang cocok untuk "<strong class="text-dark" id="faqDropdownEmptyQuery"></strong>".
            </div>
            @if($isGuestUserFaq)
                <div class="cta-card p-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="border-radius: 16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cta-icon" style="width: 42px; height: 42px; border-radius: 12px;">
                            <i class="bi bi-person-lock fs-5"></i>
                        </div>
                        <div>
                            <div class="display-serif fw-bold text-dark" style="font-size: 1rem;">Layanan Kontak Khusus Mahasiswa</div>
                            <div class="fs-12 text-muted">Akses panggilan suara dan hotline akademik tersedia untuk mahasiswa aktif terdaftar.</div>
                        </div>
                    </div>
                    <a href="{{ route('portal') }}" class="btn-maroon px-3 py-2 fs-12 text-decoration-none">
                        <span>Masuk Portal Mahasiswa</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            @else
                <div class="cta-card p-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="border-radius: 16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cta-icon" style="width: 42px; height: 42px; border-radius: 12px;">
                            <i class="bi bi-chat-square-text fs-5"></i>
                        </div>
                        <div>
                            <div class="display-serif fw-bold text-dark" style="font-size: 1rem;">Belum menemukan jawabanmu?</div>
                            <div class="fs-12 text-muted">Sampaikan pertanyaanmu ke tim akademik. Kami bantu arahkan ke layanan yang tepat.</div>
                        </div>
                    </div>
                    <a href="{{ route('layanan.kontak') }}" class="btn-maroon px-3 py-2 fs-12 text-decoration-none">
                        <span>Hubungi layanan akademik</span>
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            @endif
        </div>
        <button type="button" id="faqSearchDropdownFooter" class="faq-search-dropdown-footer d-none">
            <span id="faqSearchDropdownFooterText">Lihat semua hasil pencarian</span>
            <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</div>
@elseif($part === 'results')
<!-- ============================ HASIL PENCARIAN TERPISAH ============================ -->
<section id="faqSearchResultsSection" class="faq-search-results-section mb-5 d-none" aria-labelledby="faqSearchResultsHeading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-3 border-bottom border-light">
        <div>
            <div class="hero-eyebrow mb-1">
                <i class="bi bi-search"></i> HASIL PENCARIAN
            </div>
            <h2 id="faqSearchResultsHeading" class="display-serif fs-4 mb-0">
                Hasil untuk "<span id="faqSearchQueryText"></span>"
            </h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="tag-soft">
                <i class="bi bi-list-ul"></i>
                <span id="faqSearchResultCount">0 pertanyaan</span>
            </span>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold fs-12"
                    onclick="clearFaqSearch()">
                <i class="bi bi-x-lg me-1"></i>Tutup hasil
            </button>
        </div>
    </div>

    <div id="faqSearchResultsList" class="accordion faq-search-results-list" style="--bs-accordion-border-width:0;"></div>

    <div id="faqSearchResultsEmpty" class="d-none">
        <div class="text-center text-muted fs-13 py-4">
            <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
            <div class="fw-semibold text-dark fs-14">Tidak ada pertanyaan yang cocok</div>
            <div class="fs-12 text-muted mt-1">
                Coba kata kunci lain, atau jelajahi daftar FAQ Populer &amp; kategori di bawah ini.
            </div>
        </div>
        @if($isGuestUserFaq)
            <div class="cta-card mt-2 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="cta-icon">
                        <i class="bi bi-person-lock fs-4"></i>
                    </div>
                    <div>
                        <div class="display-serif fs-4 fw-bold text-dark">Layanan Kontak Khusus Mahasiswa</div>
                        <div class="fs-13 text-muted">Akses panggilan suara dan hotline akademik tersedia untuk mahasiswa aktif terdaftar.</div>
                    </div>
                </div>
                <a href="{{ route('portal') }}" class="btn-maroon px-4 py-2.5 fs-13 text-decoration-none">
                    <span>Masuk Portal Mahasiswa</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @else
            <div class="cta-card mt-2 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="cta-icon">
                        <i class="bi bi-chat-square-text fs-4"></i>
                    </div>
                    <div>
                        <div class="display-serif fs-4 fw-bold text-dark">Belum menemukan jawabanmu?</div>
                        <div class="fs-13 text-muted">Sampaikan pertanyaanmu ke tim akademik. Kami bantu arahkan ke layanan yang tepat.</div>
                    </div>
                </div>
                <a href="{{ route('layanan.kontak') }}" class="btn-maroon px-4 py-2.5 fs-13 text-decoration-none">
                    <span>Hubungi layanan akademik</span>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        @endif
    </div>
</section>
@elseif($part === 'script')
<script>
(function () {
    const searchWrapper = document.getElementById('faqSearchWrapper');
    const searchInput = document.getElementById('faqSearch');
    const clearBtn = document.getElementById('faqSearchClear');
    const dropdownEl = document.getElementById('faqSearchDropdown');
    const dropdownListEl = document.getElementById('faqSearchDropdownList');
    const dropdownEmptyEl = document.getElementById('faqSearchDropdownEmpty');
    const dropdownEmptyQueryEl = document.getElementById('faqDropdownEmptyQuery');
    const dropdownCountEl = document.getElementById('faqDropdownCount');
    const dropdownFooterBtn = document.getElementById('faqSearchDropdownFooter');
    const dropdownFooterText = document.getElementById('faqSearchDropdownFooterText');

    const resultsSectionEl = document.getElementById('faqSearchResultsSection');
    const resultsListEl = document.getElementById('faqSearchResultsList');
    const resultsEmptyEl = document.getElementById('faqSearchResultsEmpty');
    const resultsQueryEl = document.getElementById('faqSearchQueryText');
    const resultsCountEl = document.getElementById('faqSearchResultCount');
    const exploreCategoriesEl = document.getElementById('faqExploreCategoriesSection');
    const categoryFaqSectionEl = document.getElementById('faqCategoryListSection');

    if (!searchInput || !resultsSectionEl || !resultsListEl) return;

    let activeSuggestIdx = -1;
    let currentSuggestions = [];
    let activeSearchQuery = '';

    function buildSearchIndex() {
        const items = Array.from(document.querySelectorAll('#faqList .faq-item'));
        return items.map((el, idx) => {
            const question = (el.querySelector('.faq-q')?.textContent || '').replace(/\s+/g, ' ').trim();
            const category = (el.querySelector('.faq-cat-label > span:first-child, .tag-soft')?.textContent || '').replace(/\s+/g, ' ').trim();
            const bodyText = (el.querySelector('.faq-body')?.textContent || '').replace(/\s+/g, ' ').trim();
            return {
                el,
                idx,
                faqId: el.dataset.faqId || '',
                views: Number(el.dataset.views || el.querySelector('[data-stat-views]')?.textContent || 0),
                question,
                category,
                qLower: question.toLowerCase(),
                catLower: category.toLowerCase(),
                bodyLower: bodyText.toLowerCase(),
            };
        });
    }

    function findMatchingFaqs(rawQuery, preferredFaqId = null) {
        const q = (rawQuery || '').toLowerCase().trim();
        if (!q) return [];

        const tokens = q.split(/\s+/).filter(Boolean);
        const index = buildSearchIndex();
        const scored = [];

        index.forEach(entry => {
            const combined = entry.qLower + ' ' + entry.catLower + ' ' + entry.bodyLower;
            const hasAllTokens = tokens.every(tok => combined.includes(tok));
            if (!hasAllTokens) return;

            let score = 0;
            if (preferredFaqId && entry.faqId === String(preferredFaqId)) score += 1000;
            if (entry.qLower === q) score += 200;
            if (entry.qLower.includes(q)) score += 100;
            if (entry.qLower.startsWith(q)) score += 40;
            if (tokens.every(tok => entry.qLower.includes(tok))) score += 50;
            if (tokens.some(tok => entry.catLower.includes(tok))) score += 20;
            if (tokens.some(tok => entry.bodyLower.includes(tok))) score += 5;

            scored.push({ entry, score });
        });

        scored.sort((a, b) => {
            if (b.score !== a.score) return b.score - a.score;
            if (b.entry.views !== a.entry.views) return b.entry.views - a.entry.views;
            return a.entry.idx - b.entry.idx;
        });

        return scored.map(item => item.entry);
    }

    // Safely highlight matched tokens without injecting raw HTML
    function appendHighlightedText(container, text, query) {
        const tokens = (query || '').trim().split(/\s+/).filter(Boolean);
        if (tokens.length === 0) {
            container.textContent = text;
            return;
        }
        const escaped = tokens.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        const regex = new RegExp('(' + escaped.join('|') + ')', 'gi');
        const parts = text.split(regex);
        parts.forEach(part => {
            if (!part) return;
            if (regex.test(part)) {
                const mark = document.createElement('mark');
                mark.className = 'faq-search-mark';
                mark.textContent = part;
                container.appendChild(mark);
            } else {
                container.appendChild(document.createTextNode(part));
            }
            regex.lastIndex = 0;
        });
    }

    function openDropdown() {
        dropdownEl.classList.remove('d-none');
        searchWrapper?.classList.add('is-dropdown-open');
        searchInput.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        dropdownEl.classList.add('d-none');
        searchWrapper?.classList.remove('is-dropdown-open');
        searchInput.setAttribute('aria-expanded', 'false');
        activeSuggestIdx = -1;
    }

    function renderDropdown(rawQuery) {
        const q = (rawQuery || '').trim();
        if (!q) {
            closeDropdown();
            return;
        }

        const matches = findMatchingFaqs(q);
        currentSuggestions = matches.slice(0, 6);
        activeSuggestIdx = -1;
        dropdownListEl.innerHTML = '';

        if (dropdownCountEl) {
            dropdownCountEl.textContent = matches.length > 0 ? (matches.length + ' cocok') : '0 cocok';
        }

        if (currentSuggestions.length === 0) {
            if (dropdownEmptyQueryEl) dropdownEmptyQueryEl.textContent = q;
            dropdownEmptyEl?.classList.remove('d-none');
            dropdownFooterBtn.classList.add('d-none');
            openDropdown();
            return;
        }

        dropdownEmptyEl?.classList.add('d-none');

        currentSuggestions.forEach((entry, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'faq-suggest-item';
            btn.setAttribute('role', 'option');
            btn.dataset.suggestIdx = String(idx);

            const iconWrap = document.createElement('span');
            iconWrap.className = 'faq-suggest-icon';
            iconWrap.innerHTML = '<i class="bi bi-search"></i>';

            const contentWrap = document.createElement('div');
            contentWrap.className = 'flex-grow-1 min-w-0';

            const titleEl = document.createElement('div');
            titleEl.className = 'faq-suggest-title';
            appendHighlightedText(titleEl, entry.question, q);

            const metaEl = document.createElement('div');
            metaEl.className = 'faq-suggest-meta';
            metaEl.textContent = (entry.category || 'FAQ') + ' · ' + entry.views + ' dilihat';

            contentWrap.appendChild(titleEl);
            contentWrap.appendChild(metaEl);
            btn.appendChild(iconWrap);
            btn.appendChild(contentWrap);

            btn.addEventListener('mousedown', function (e) {
                e.preventDefault(); // Keep focus logic clean before click
            });
            btn.addEventListener('click', function () {
                selectSuggestion(entry);
            });

            dropdownListEl.appendChild(btn);
        });

        dropdownFooterBtn.classList.remove('d-none');
        dropdownFooterText.textContent = 'Lihat semua ' + matches.length + ' hasil untuk "' + q + '"';
        openDropdown();
    }

    function updateDropdownActiveHighlight() {
        const items = dropdownListEl.querySelectorAll('.faq-suggest-item');
        items.forEach((el, idx) => {
            el.classList.toggle('is-active', idx === activeSuggestIdx);
            if (idx === activeSuggestIdx) {
                el.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function selectSuggestion(entry) {
        if (!entry) return;
        searchInput.value = entry.question;
        syncClearButton();
        closeDropdown();
        renderSearchResults(entry.question, entry.faqId);
    }

    function syncSearchResultsScrollHeight() {
        const renderedItems = Array.from(resultsListEl.querySelectorAll(':scope > .faq-item'));
        if (renderedItems.length <= 7) {
            resultsListEl.style.maxHeight = 'none';
            return;
        }

        // Hitung tinggi pas untuk maksimal 7 hasil di kotak Hasil Pencarian, sisanya bisa di-scroll di dalamnya
        const collapsedSamples = renderedItems
            .slice(0, 7)
            .filter(item => !item.querySelector('.collapse.show'));

        const fallbackCollapsedHeight = collapsedSamples.length > 0
            ? collapsedSamples.reduce((sum, item) => sum + item.offsetHeight, 0) / collapsedSamples.length
            : 76;

        let visibleTargetHeight = 0;
        for (let i = 0; i < 7; i++) {
            const item = renderedItems[i];
            const isExpanded = Boolean(item.querySelector('.collapse.show'));
            const style = window.getComputedStyle(item);
            const mb = i < 6 ? (parseFloat(style.marginBottom) || 0) : 0;
            visibleTargetHeight += (isExpanded ? fallbackCollapsedHeight : item.offsetHeight) + mb;
        }

        if (visibleTargetHeight > 0) {
            resultsListEl.style.maxHeight = Math.ceil(visibleTargetHeight + 2) + 'px';
        }
    }

    function renderSearchResults(rawQuery, expandFaqId = null, shouldScroll = true) {
        const q = (rawQuery || '').trim();
        if (!q) {
            hideSearchResults();
            return;
        }

        activeSearchQuery = q;
        const matches = findMatchingFaqs(q, expandFaqId);

        resultsQueryEl.textContent = q;
        resultsCountEl.textContent = matches.length + ' pertanyaan';
        resultsListEl.innerHTML = '';

        // Sembunyikan isi daftar kategori di bawah saat Hasil Pencarian tampil (Jelajahi Kategori tetap paten di atas)
        categoryFaqSectionEl?.classList.add('d-none');

        if (matches.length === 0) {
            resultsListEl.style.maxHeight = 'none';
            resultsEmptyEl.classList.remove('d-none');
        } else {
            resultsEmptyEl.classList.add('d-none');
            const autoExpandSingle = matches.length === 1;

            matches.forEach((entry, index) => {
                const clone = entry.el.cloneNode(true);
                clone.classList.remove('d-none');

                const numEl = clone.querySelector('.faq-num');
                if (numEl) numEl.textContent = String(index + 1).padStart(2, '0');

                const toggleBtn = clone.querySelector('.faq-toggle');
                const collapseEl = clone.querySelector('.collapse');
                const targetId = 'searchFaqResult' + (index + 1);

                const shouldExpand = Boolean(
                    (expandFaqId && entry.faqId === String(expandFaqId)) ||
                    (autoExpandSingle && index === 0)
                );

                if (collapseEl) {
                    collapseEl.id = targetId;
                    collapseEl.setAttribute('data-bs-parent', '#faqSearchResultsList');
                    collapseEl.classList.toggle('show', shouldExpand);
                }

                if (toggleBtn) {
                    toggleBtn.setAttribute('data-bs-target', '#' + targetId);
                    toggleBtn.setAttribute('aria-controls', targetId);
                    toggleBtn.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');
                    toggleBtn.classList.toggle('collapsed', !shouldExpand);
                }

                resultsListEl.appendChild(clone);

                if (shouldExpand && entry.faqId && typeof logFaqInteraction === 'function') {
                    logFaqInteraction(entry.faqId, 'view', q, 1);
                }
            });
        }

        resultsSectionEl.classList.remove('d-none');
        resultsListEl.scrollTop = 0;
        syncSearchResultsScrollHeight();

        if (shouldScroll) {
            const rect = resultsSectionEl.getBoundingClientRect();
            if (rect.top + 220 > window.innerHeight || rect.top < 75) {
                resultsSectionEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function hideSearchResults() {
        activeSearchQuery = '';
        resultsListEl.innerHTML = '';
        resultsListEl.style.maxHeight = '';
        resultsSectionEl.classList.add('d-none');
        categoryFaqSectionEl?.classList.remove('d-none');
    }

    function syncClearButton() {
        if (!clearBtn) return;
        const hasVal = (searchInput.value || '').trim().length > 0;
        clearBtn.classList.toggle('d-none', !hasVal);
    }

    window.submitFaqSearch = function () {
        const q = (searchInput.value || '').trim();
        if (!q) {
            hideSearchResults();
            closeDropdown();
            searchInput.focus();
            return;
        }
        closeDropdown();
        renderSearchResults(q, null, true);
    };

    window.quickSearch = function (term) {
        searchInput.value = term;
        syncClearButton();
        closeDropdown();
        renderSearchResults(term, null, true);
    };

    window.clearFaqSearch = function () {
        searchInput.value = '';
        syncClearButton();
        closeDropdown();
        hideSearchResults();
    };

    searchInput.addEventListener('input', function () {
        syncClearButton();
        const val = searchInput.value;
        if (!val.trim()) {
            closeDropdown();
            hideSearchResults();
            return;
        }
        renderDropdown(val);
    });

    searchInput.addEventListener('focus', function () {
        if ((searchInput.value || '').trim()) {
            renderDropdown(searchInput.value);
        }
    });

    searchInput.addEventListener('keydown', function (e) {
        if (dropdownEl.classList.contains('d-none')) {
            if (e.key === 'Escape') {
                closeDropdown();
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            if (currentSuggestions.length > 0) {
                e.preventDefault();
                activeSuggestIdx = (activeSuggestIdx + 1) % currentSuggestions.length;
                updateDropdownActiveHighlight();
            }
        } else if (e.key === 'ArrowUp') {
            if (currentSuggestions.length > 0) {
                e.preventDefault();
                activeSuggestIdx = activeSuggestIdx <= 0 ? currentSuggestions.length - 1 : activeSuggestIdx - 1;
                updateDropdownActiveHighlight();
            }
        } else if (e.key === 'Enter') {
            if (activeSuggestIdx >= 0 && currentSuggestions[activeSuggestIdx]) {
                e.preventDefault();
                selectSuggestion(currentSuggestions[activeSuggestIdx]);
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeDropdown();
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            window.clearFaqSearch();
            searchInput.focus();
        });
    }

    if (dropdownFooterBtn) {
        dropdownFooterBtn.addEventListener('mousedown', function (e) {
            e.preventDefault();
        });
        dropdownFooterBtn.addEventListener('click', function () {
            window.submitFaqSearch();
        });
    }

    document.addEventListener('click', function (e) {
        if (searchWrapper && !searchWrapper.contains(e.target)) {
            closeDropdown();
        }
    });

    resultsListEl.addEventListener('shown.bs.collapse', function (e) {
        const faqId = e.target?.dataset?.faqId;
        if (faqId && typeof logFaqInteraction === 'function') {
            logFaqInteraction(faqId, 'view', activeSearchQuery, 1);
        }
    });

    // Dock search bar beside KMS logo in sticky navbar when hero search scrolls out of view
    const heroSearchAnchor = document.getElementById('heroSearchAnchor');
    const navbarSearchSlot = document.getElementById('navbarSearchSlot');
    const topNavbar = document.getElementById('faqTopNavbar') || document.querySelector('.mhs-navbar, .guest-navbar');
    let isSearchDocked = false;

    function syncSearchDockState() {
        if (!heroSearchAnchor || !navbarSearchSlot || !searchWrapper) return;

        if (!isSearchDocked && searchWrapper.offsetHeight > 0) {
            heroSearchAnchor.style.minHeight = searchWrapper.offsetHeight + 'px';
        }

        const navBottom = topNavbar ? topNavbar.getBoundingClientRect().bottom : 72;
        const anchorRect = heroSearchAnchor.getBoundingClientRect();

        if (!isSearchDocked && anchorRect.bottom <= navBottom + 12) {
            const hadFocus = document.activeElement === searchInput;
            const selStart = hadFocus ? searchInput.selectionStart : null;
            const selEnd = hadFocus ? searchInput.selectionEnd : null;

            closeDropdown();
            navbarSearchSlot.appendChild(searchWrapper);
            navbarSearchSlot.classList.add('is-visible');
            topNavbar?.classList.add('is-search-docked');
            isSearchDocked = true;

            if (hadFocus) {
                searchInput.focus({ preventScroll: true });
                if (selStart !== null && selEnd !== null) {
                    try { searchInput.setSelectionRange(selStart, selEnd); } catch (_) {}
                }
            }
        } else if (isSearchDocked && anchorRect.bottom > navBottom + 28) {
            const hadFocus = document.activeElement === searchInput;
            const selStart = hadFocus ? searchInput.selectionStart : null;
            const selEnd = hadFocus ? searchInput.selectionEnd : null;

            closeDropdown();
            navbarSearchSlot.classList.remove('is-visible');
            topNavbar?.classList.remove('is-search-docked');
            heroSearchAnchor.appendChild(searchWrapper);
            isSearchDocked = false;

            if (hadFocus) {
                searchInput.focus({ preventScroll: true });
                if (selStart !== null && selEnd !== null) {
                    try { searchInput.setSelectionRange(selStart, selEnd); } catch (_) {}
                }
            }
        }
    }

    window.addEventListener('scroll', syncSearchDockState, { passive: true });
    window.addEventListener('resize', syncSearchDockState, { passive: true });

    syncClearButton();
    syncSearchDockState();
})();
</script>
@endif

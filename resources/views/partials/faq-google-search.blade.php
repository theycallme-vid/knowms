@php
    $part = $part ?? 'bar';
@endphp

@if($part === 'bar')
<style>
    .hero-section {
        z-index: 20;
    }

    .hero-search-wrapper {
        position: relative;
        width: 100%;
        max-width: 820px;
        z-index: 30;
    }

    .hero-search-wrapper.is-dropdown-open .hero-search {
        border-color: var(--brand-maroon);
        box-shadow: 0 14px 36px rgba(133, 23, 44, 0.14);
    }

    .hero-search-clear {
        border: 0;
        background: #F1F5F9;
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
        background: #E2E8F0;
        color: #171A1F;
    }

    .faq-search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid var(--card-border);
        border-radius: 20px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.15);
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

    @media (max-width: 576px) {
        .faq-search-results-section {
            padding: 1.15rem 1rem;
            border-radius: 16px;
        }
        .hero-search {
            gap: 0.5rem;
            padding-left: 1rem;
        }
        .hero-search .btn-maroon span {
            display: none;
        }
        .hero-search .btn-maroon {
            padding: 0.65rem 0.9rem;
        }
    }
</style>

<div class="hero-search-wrapper mb-2" id="faqSearchWrapper">
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
                <a href="#" class="btn-maroon px-3 py-2 fs-12">
                    <span>Hubungi layanan akademik</span>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
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

    <div id="faqSearchResultsList" class="accordion" style="--bs-accordion-border-width:0;"></div>

    <div id="faqSearchResultsEmpty" class="d-none">
        <div class="text-center text-muted fs-13 py-4">
            <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
            <div class="fw-semibold text-dark fs-14">Tidak ada pertanyaan yang cocok</div>
            <div class="fs-12 text-muted mt-1">
                Coba kata kunci lain, atau jelajahi daftar FAQ Populer &amp; kategori di bawah ini.
            </div>
        </div>
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
            <a href="#" class="btn-maroon px-4 py-2.5 fs-13">
                <span>Hubungi layanan akademik</span>
                <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>
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

        if (matches.length === 0) {
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
        if (shouldScroll) {
            resultsSectionEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function hideSearchResults() {
        activeSearchQuery = '';
        resultsListEl.innerHTML = '';
        resultsSectionEl.classList.add('d-none');
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

    syncClearButton();
})();
</script>
@endif

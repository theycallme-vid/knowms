@php
    $aktivKataKunci = trim((string) request('kata_kunci', request('fakultas', 'Semua')));
    if ($aktivKataKunci === '') {
        $aktivKataKunci = 'Semua';
    }
    $showUnitDropdown = !empty($showUnitFilter) && isset($direktorats) && count($direktorats) > 0;
    $hideStDropdown   = !empty($hideStatusFilter);
    $activeUnitReq    = (string) request('unit_id', 'all');
    $hasActiveFilter  = (
        request()->filled('search') ||
        $aktivKataKunci !== 'Semua' ||
        ($showUnitDropdown && $activeUnitReq !== '' && $activeUnitReq !== 'all' && $activeUnitReq !== 'Semua') ||
        (!$hideStDropdown && request()->filled('status') && request('status') !== 'Semua') ||
        (request()->filled('sort') && request('sort') !== 'terbaru')
    );
    $resetHref = !empty($scopeInput) ? ($actionRoute . '?scope=' . urlencode($scopeInput)) : $actionRoute;
@endphp

<form method="GET" action="{{ $actionRoute }}" id="filterForm">
    @if(!empty($scopeInput))
        <input type="hidden" name="scope" value="{{ $scopeInput }}">
    @endif
    <div class="filter-bar">
        <div class="filter-left-group">
            <!-- Search (Auto-submit saat mengetik) -->
            <div class="filter-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text"
                       id="faqFilterSearchInput"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari kode atau pertanyaan FAQ..."
                       autocomplete="off">
            </div>

            <!-- Filter Unit / Direktorat Terdaftar -->
            @if($showUnitDropdown)
                <select name="unit_id" class="filter-select" onchange="submitFaqFilterForm()">
                    <option value="all" {{ in_array($activeUnitReq, ['', 'all', 'Semua'], true) ? 'selected' : '' }}>Semua Unit Terdaftar</option>
                    @foreach($direktorats as $dirOpt)
                        <option value="{{ $dirOpt->id }}" {{ (string) $dirOpt->id === $activeUnitReq ? 'selected' : '' }}>
                            {{ $dirOpt->nama }}
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Filter Kata Kunci (Bisa dicari & di-scroll) -->
            <div class="keyword-dropdown-wrap" id="kataKunciDropdownWrap">
                <input type="hidden" name="kata_kunci" id="kataKunciHiddenInput" value="{{ $aktivKataKunci }}">
                <button type="button"
                        id="kataKunciTrigger"
                        class="filter-select keyword-dropdown-trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        onclick="toggleKataKunciDropdown(event)">
                    <span class="keyword-dropdown-trigger-text" id="kataKunciTriggerLabel">
                        {{ $aktivKataKunci === 'Semua' ? 'Semua Kata Kunci' : $aktivKataKunci }}
                    </span>
                </button>

                <div class="keyword-dropdown-menu d-none" id="kataKunciMenu" onclick="event.stopPropagation()">
                    <div class="keyword-dropdown-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text"
                               id="kataKunciSearchInput"
                               class="keyword-dropdown-search-input"
                               placeholder="Cari kata kunci..."
                               autocomplete="off"
                               oninput="filterKataKunciOptions(this.value)"
                               onkeydown="handleKataKunciSearchKeydown(event)">
                    </div>

                    <div class="keyword-dropdown-list" id="kataKunciOptionsList" role="listbox">
                        <button type="button"
                                class="keyword-dropdown-item {{ $aktivKataKunci === 'Semua' ? 'is-selected' : '' }}"
                                data-keyword="Semua"
                                data-search-text="semua kata kunci"
                                onclick="selectKataKunciOption('Semua', 'Semua Kata Kunci')">
                            <span>Semua Kata Kunci</span>
                            @if($aktivKataKunci === 'Semua')
                                <i class="bi bi-check2"></i>
                            @endif
                        </button>

                        @foreach(($kataKunciOptions ?? []) as $tagNama)
                            <button type="button"
                                    class="keyword-dropdown-item {{ $aktivKataKunci === $tagNama ? 'is-selected' : '' }}"
                                    data-keyword="{{ $tagNama }}"
                                    data-search-text="{{ mb_strtolower($tagNama) }}"
                                    onclick="selectKataKunciOption({{ \Illuminate\Support\Js::from($tagNama) }}, {{ \Illuminate\Support\Js::from($tagNama) }})">
                                <span class="text-truncate">{{ $tagNama }}</span>
                                @if($aktivKataKunci === $tagNama)
                                    <i class="bi bi-check2 flex-shrink-0"></i>
                                @endif
                            </button>
                        @endforeach

                        <div id="kataKunciEmptyMsg" class="keyword-dropdown-empty d-none">
                            Kata kunci tidak ditemukan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Status -->
            @if(!$hideStDropdown)
                <select name="status" class="filter-select" onchange="submitFaqFilterForm()">
                    @php $stReq = request('status', 'Semua'); @endphp
                    <option value="Semua"                    {{ $stReq === 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Menunggu Validasi"        {{ in_array($stReq, ['Menunggu Validasi', 'Submitted'], true) ? 'selected' : '' }}>Menunggu Validasi (Semua)</option>
                    <option value="Menunggu Validasi Unit"   {{ $stReq === 'Menunggu Validasi Unit' ? 'selected' : '' }}>Menunggu Validasi Unit</option>
                    <option value="Menunggu Validasi Admik"  {{ $stReq === 'Menunggu Validasi Admik' ? 'selected' : '' }}>Menunggu Validasi Admik</option>
                    <option value="Diterima"                 {{ in_array($stReq, ['Diterima', 'Approved'], true) ? 'selected' : '' }}>Diterima</option>
                    <option value="Ditolak"                  {{ in_array($stReq, ['Ditolak', 'Rejected'], true) ? 'selected' : '' }}>Ditolak</option>
                    <option value="Terbit"                   {{ in_array($stReq, ['Terbit', 'Active'], true) ? 'selected' : '' }}>Terbit</option>
                </select>
            @endif

            <!-- Sort -->
            <select name="sort" class="filter-select" onchange="submitFaqFilterForm()">
                <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                <option value="terlama" {{ request('sort') === 'terlama'            ? 'selected' : '' }}>Terlama</option>
                <option value="az"      {{ request('sort') === 'az'                 ? 'selected' : '' }}>A &rarr; Z</option>
            </select>

            @if($hasActiveFilter)
                <a href="{{ $resetHref }}" class="btn-filter-reset">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
            @endif
        </div>
    </div>
</form>

<script>
    (function () {
        let faqSearchTimer = null;
        const form = document.getElementById('filterForm');
        const mainSearchInput = document.getElementById('faqFilterSearchInput');

        window.submitFaqFilterForm = function () {
            if (!form) return;
            if (mainSearchInput && document.activeElement === mainSearchInput) {
                try {
                    sessionStorage.setItem('daftarFaqSearchFocused', '1');
                } catch (e) {}
            } else {
                try {
                    sessionStorage.removeItem('daftarFaqSearchFocused');
                } catch (e) {}
            }
            form.submit();
        };

        window.toggleKataKunciDropdown = function (e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('kataKunciMenu');
            const trigger = document.getElementById('kataKunciTrigger');
            const searchBox = document.getElementById('kataKunciSearchInput');
            const tableCard = trigger ? trigger.closest('.table-card') : null;
            if (!menu || !trigger) return;

            const isHidden = menu.classList.contains('d-none');
            menu.classList.toggle('d-none', !isHidden);
            trigger.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            if (tableCard) {
                tableCard.style.overflow = isHidden ? 'visible' : '';
            }

            if (isHidden && searchBox) {
                searchBox.value = '';
                window.filterKataKunciOptions('');
                setTimeout(() => searchBox.focus(), 20);
            }
        };

        window.closeKataKunciDropdown = function () {
            const menu = document.getElementById('kataKunciMenu');
            const trigger = document.getElementById('kataKunciTrigger');
            const tableCard = trigger ? trigger.closest('.table-card') : null;
            if (menu) menu.classList.add('d-none');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            if (tableCard) tableCard.style.overflow = '';
        };

        window.filterKataKunciOptions = function (rawQuery) {
            const q = (rawQuery || '').trim().toLowerCase();
            const items = document.querySelectorAll('#kataKunciOptionsList .keyword-dropdown-item');
            const emptyMsg = document.getElementById('kataKunciEmptyMsg');
            let visibleCount = 0;

            items.forEach(item => {
                const text = (item.getAttribute('data-search-text') || '').toLowerCase();
                const match = q === '' || text.includes(q);
                item.classList.toggle('d-none', !match);
                if (match) visibleCount++;
            });

            if (emptyMsg) {
                emptyMsg.classList.toggle('d-none', visibleCount > 0);
            }
        };

        window.handleKataKunciSearchKeydown = function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            } else if (e.key === 'Escape') {
                window.closeKataKunciDropdown();
            }
        };

        window.selectKataKunciOption = function (value, label) {
            const hiddenInput = document.getElementById('kataKunciHiddenInput');
            const triggerLabel = document.getElementById('kataKunciTriggerLabel');
            if (hiddenInput) hiddenInput.value = value;
            if (triggerLabel) triggerLabel.textContent = label;
            window.closeKataKunciDropdown();
            window.submitFaqFilterForm();
        };

        document.addEventListener('click', function (e) {
            const wrap = document.getElementById('kataKunciDropdownWrap');
            if (wrap && !wrap.contains(e.target)) {
                window.closeKataKunciDropdown();
            }
        });

        if (mainSearchInput) {
            mainSearchInput.addEventListener('input', function () {
                clearTimeout(faqSearchTimer);
                faqSearchTimer = setTimeout(() => {
                    window.submitFaqFilterForm();
                }, 450);
            });

            mainSearchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(faqSearchTimer);
                    window.submitFaqFilterForm();
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                try {
                    if (sessionStorage.getItem('daftarFaqSearchFocused') === '1') {
                        sessionStorage.removeItem('daftarFaqSearchFocused');
                        mainSearchInput.focus();
                        const len = mainSearchInput.value.length;
                        mainSearchInput.setSelectionRange(len, len);
                    }
                } catch (e) {}
            });
        }
    })();
</script>

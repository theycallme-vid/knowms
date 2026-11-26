@php
    $sbUser = auth()->user();
    if ($sbUser) {
        $sbUser->loadMissing(['role', 'direktorat']);
    }
    $isUnitVal = $sbUser ? $sbUser->isValidatorUnit() : false;
    $sbPrefix  = $isUnitVal ? 'validator.unit.' : 'validator.';
    $sbUnitLabel = $sbUser ? $sbUser->getUnitName() : 'Direktorat Akademik';
    $activeKey = $activeMenu ?? '';
@endphp
<aside class="sidebar" id="sidebar">
    <div>
        <!-- KMS Brand Header -->
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="sidebar-logo-img">
        </div>

        <!-- Role & Unit Dynamic Pill -->
        <div>
            <span class="role-pill" style="line-height: 1.35; border-radius: 14px; max-width: 100%;">
                {{ $sbUnitLabel }}
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="nav flex-column">
            <a href="{{ route($sbPrefix . 'daftarFAQ') }}" class="nav-link-custom {{ $activeKey === 'daftarFAQ' ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Daftar FAQ</span>
            </a>
            <a href="{{ route($sbPrefix . 'validasiFAQ') }}" class="nav-link-custom {{ $activeKey === 'validasiFAQ' ? 'active' : '' }}">
                <i class="bi bi-list-check"></i>
                <span>{{ $isUnitVal ? 'Validasi FAQ Unit' : 'Validasi FAQ' }}</span>
            </a>
            <a href="{{ route($sbPrefix . 'buatfaq') }}" class="nav-link-custom {{ $activeKey === 'buatfaq' ? 'active' : '' }}">
                <i class="bi bi-plus-lg"></i>
                <span>Buat FAQ</span>
            </a>
            <a href="{{ route($sbPrefix . 'katakunci') }}" class="nav-link-custom {{ $activeKey === 'katakunci' ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Daftar Kata Kunci</span>
            </a>
            <a href="{{ route($sbPrefix . 'sumberpustaka') }}" class="nav-link-custom {{ $activeKey === 'sumberpustaka' ? 'active' : '' }}">
                <i class="bi bi-book"></i>
                <span>Sumber Pustaka</span>
            </a>

            @if(!$isUnitVal)
                <a href="{{ route('validator.kelolaUnit') }}" class="nav-link-custom {{ $activeKey === 'kelola-unit' ? 'active' : '' }}">
                    <i class="bi bi-building-gear"></i>
                    <span>Kelola Unit</span>
                </a>
            @endif

            <a href="{{ route($sbPrefix . 'pengguna-akses') }}" class="nav-link-custom {{ $activeKey === 'pengguna-akses' ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>{{ $isUnitVal ? 'Pengguna & Akses Unit' : 'Pengguna & Akses' }}</span>
            </a>

            @if(!$isUnitVal)
                <a href="{{ route('validator.pengaturanAlertCall') }}" class="nav-link-custom {{ $activeKey === 'alert-call' ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Setting Alert Call</span>
                </a>
            @endif
        </nav>
    </div>

    <!-- Bottom Section of Sidebar -->
    <div>
        <hr class="sidebar-divider">
        <a href="{{ route($sbPrefix . 'pengaturanAkun') }}" class="nav-link-custom {{ $activeKey === 'pengaturanAkun' ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Pengaturan Akun</span>
        </a>
        <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>

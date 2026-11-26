@php
    $deSbUser = auth()->user();
    if ($deSbUser) {
        $deSbUser->loadMissing(['role', 'direktorat']);
    }
    $deUnitLabel = $deSbUser ? $deSbUser->getUnitName() : 'Direktorat Akademik';
    $activeKey   = $activeMenu ?? '';
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
                {{ $deUnitLabel }}
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="nav flex-column">
            <a href="{{ route('dataentry.daftarFAQ') }}" class="nav-link-custom {{ $activeKey === 'daftarFAQ' ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Daftar FAQ</span>
            </a>
            <a href="{{ route('dataentry.pengajuan') }}" class="nav-link-custom {{ $activeKey === 'pengajuan' ? 'active' : '' }}">
                <i class="bi bi-send-check"></i>
                <span>Pengajuan FAQ</span>
            </a>
            <a href="{{ route('dataentry.buatfaq') }}" class="nav-link-custom {{ $activeKey === 'buatfaq' ? 'active' : '' }}">
                <i class="bi bi-plus-lg"></i>
                <span>Buat FAQ</span>
            </a>
            <a href="{{ route('dataentry.katakunci') }}" class="nav-link-custom {{ $activeKey === 'katakunci' ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Daftar Kata Kunci</span>
            </a>
            <a href="{{ route('dataentry.sumberpustaka') }}" class="nav-link-custom {{ $activeKey === 'sumberpustaka' ? 'active' : '' }}">
                <i class="bi bi-book"></i>
                <span>Sumber Pustaka</span>
            </a>
        </nav>
    </div>

    <!-- Bottom Section of Sidebar -->
    <div>
        <hr class="sidebar-divider">
        <a href="{{ route('dataentry.pengaturanAkun') }}" class="nav-link-custom {{ $activeKey === 'pengaturanAkun' ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Pengaturan Akun</span>
        </a>
        <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-custom nav-link-logout text-secondary">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>

<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Concerns\HandlesAccountSettings;
use App\Http\Controllers\Controller;

/**
 * Pengaturan Akun untuk role Super Admin.
 */
class PengaturanAkunController extends Controller
{
    use HandlesAccountSettings;

    protected function settingsView(): string
    {
        return 'superadmin.pengaturanakun';
    }

    protected function settingsRoute(): string
    {
        return 'superadmin.pengaturanAkun';
    }
}


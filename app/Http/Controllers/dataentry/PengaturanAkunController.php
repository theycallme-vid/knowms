<?php

namespace App\Http\Controllers\dataentry;

use App\Http\Controllers\Concerns\HandlesAccountSettings;
use App\Http\Controllers\Controller;

/**
 * Pengaturan Akun untuk role Data Entry.
 */
class PengaturanAkunController extends Controller
{
    use HandlesAccountSettings;

    protected function settingsView(): string
    {
        return 'dataentry.pengaturanakun';
    }

    protected function settingsRoute(): string
    {
        return 'dataentry.pengaturanAkun';
    }
}


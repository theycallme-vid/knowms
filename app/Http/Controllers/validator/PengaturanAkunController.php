<?php

namespace App\Http\Controllers\validator;

use App\Http\Controllers\Concerns\HandlesAccountSettings;
use App\Http\Controllers\Controller;

/**
 * Pengaturan Akun untuk role Validator.
 */
class PengaturanAkunController extends Controller
{
    use HandlesAccountSettings;

    protected function settingsView(): string
    {
        return 'validator.pengaturanakun';
    }

    protected function settingsRoute(): string
    {
        $user = auth()->user();
        return ($user && $user->isValidatorUnit())
            ? 'validator.unit.pengaturanAkun'
            : 'validator.pengaturanAkun';
    }
}


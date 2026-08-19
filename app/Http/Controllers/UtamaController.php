<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtamaController extends Controller
{
    function boleh() {
        $arrdata = ['Vella', 'Vadrian', 'Verina'];
        return view('coba', ['dataku' => $arrdata]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtamaController extends Controller
{
    public function boleh() {
        $arrData = ['Vella', 'Gading', 'Vern'];
        return view('oh', ['dataku' => $arrData]);
    }
}

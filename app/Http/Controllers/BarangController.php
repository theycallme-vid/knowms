<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Kategori;

class BarangController extends Controller
{
    public function tampil(){
        $barangs = DB::table('barangs')->get();
        return view('barang.daftar', ['barangs' => $barangs]);
    }

    public function create(){
        $kategoris = Kategori::all();
        return view('barang.create', compact('kategoris'));
    }
}

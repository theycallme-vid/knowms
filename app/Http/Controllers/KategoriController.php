<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function tampil(){
        // $kategoris = DB::table('kategoris')->get();
        $kategoris = Kategori::all();
        return view('kategori.daftar', ['kategoris' => $kategoris]);
    }

    public function create(){
        return view('kategori.create');
    }

    public function simpan(Request $request){
        // DB::table('kategoris')->insert([
        //     'nama_kategori' => $request->get('nama_kategori'),
        //     'deskripsi' => $request->get('deskripsi'),
        // ]);

        $kategori = new Kategori;
        $kategori->nama_kategori = $request->get('nama_kategori');
        $kategori->deskripsi = $request->get('deskripsi');
        $kategori->save();
        return redirect('daftar-kategori');
    }

    public function hapus(Kategori $kategori){
        $kategori->delete();
        return redirect('daftar-kategori');
    }
    

    public function ubah(Kategori $kategori){
        return view('kategori.ubah', ['kategori' => $kategori]);
    }

    public function update(Request $request) {
        $kategori = Kategori::find($request->get('id'));
        $kategori->nama_kategori = $request->get('nama_kategori');
        $kategori->deskripsi = $request->get('deskripsi');
        $kategori->save();
        return redirect('daftar-kategori');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Kategori;
use App\Models\Barang;

class BarangController extends Controller
{
    public function tampil()
    {
        $barangs = Barang::with('kategori')->get();
        return view('barang.daftar', ['barangs' => $barangs]);
    }

    // BUAT BARANG + mengambil data kategori
    public function create(){
        $kategoris = Kategori::all();
        return view('barang.create', compact('kategoris'));
    }
    
    // SIMPAN BARANG
    public function simpan(Request $request){
        try {
            $barang = new Barang;
            $barang->nama = $request->get('nama');
            $barang->harga = $request->get('harga');
            $barang->stok = $request->get('stok');
            $barang->kategori_id = $request->get('kategori_id');
            $barang->save();
            return redirect('daftar-barang')->with('sukses', 'Data barang berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect('daftar-barang')->with('error', 'Gagal menambah data barang: ' . $e->getMessage());
        }
    }

    // HAPUS BARANG
    public function hapus(Barang $barang){
        try {
            $barang->delete();
            return redirect('daftar-barang')->with('sukses', 'Data barang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect('daftar-barang')->with('error', 'Gagal menghapus data barang: ' . $e->getMessage());
        }
    }


    public function ubah(Barang $barang)
    {
        $kategoris = Kategori::all();
        return view('barang.ubah', compact('barang', 'kategoris'));
    }

    
    public function update(Request $request) {
        try {
            $barang = Barang::find($request->get('id'));
            $barang->nama = $request->get('nama');
            $barang->harga = $request->get('harga');
            $barang->stok = $request->get('stok');
            $barang->kategori_id = $request->get('kategori_id');
            $barang->save();
            return redirect('daftar-barang')->with('sukses', 'Data barang berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect('daftar-barang')->with('error', 'Gagal memperbarui data barang: ' . $e->getMessage());
        }
    }

    
}



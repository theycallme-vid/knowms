<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang</title>
</head>
<body>
    <form method="post" action="{{ url('/update-barang') }}">
        @method('PUT')
        @csrf
        <input type="hidden" name="id" value="{{$barang->id}}">
        <table>
            <tr>
                <td>Nama Barang</td>
                <td><input type="text" name="nama" value="{{$barang->nama}}"></td>
            </tr>
            <tr>
                <td>Harga</td>
                <td><input type="text" name="harga" value="{{$barang->harga}}"></td>
            </tr>
            <tr>
                <td>Stok</td>
                <td><input type="text" name="stok" value="{{$barang->stok}}"></td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td><select name="kategori_id">
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ $barang->kategori_id == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Simpan"></td>
            </tr>
        </table>
    </form> 
</body> 
</body>
</html>
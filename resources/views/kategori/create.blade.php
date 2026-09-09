<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori</title>
</head>
<body>
    <form method="post" action="{{ url('simpan-kategori') }}">
        @csrf
        <table>
            <tr>
                <td>Nama Kategori</td>
                <td>
                    <input type="text" name="nama_kategori"
                           value="{{ old('nama_kategori') }}"
                           pattern="[^0-9]*"
                           title="Nama kategori tidak boleh mengandung angka">
                    @error('nama_kategori')
                        <br><span style="color: red;">{{ $message }}</span>
                    @enderror
                </td>
            </tr>
            <tr>
                <td>Deskripsi</td>
                <td>
                    <textarea name="deskripsi">{{ old('deskripsi') }}</textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Simpan"></td>
            </tr>
        </table>
    </form> 
    
</html>
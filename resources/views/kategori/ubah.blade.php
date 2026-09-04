<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post" action="{{ url('update-kategori') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{$kategori->id}}">
        <table>
            <tr>
                <td>Nama Kategori</td>
                <td><input type="text" name="nama_kategori" value="{{$kategori->nama_kategori}}"></td>
            </tr>
            <tr>
                <td>Deskripsi</td>
                <td>
                    <textarea name="deskripsi">
                        {{$kategori->deskripsi}}
                    </textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Simpan"></td>
            </tr>
        </table>
    </form> 
</body>
</html>
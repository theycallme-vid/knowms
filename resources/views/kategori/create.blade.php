<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post" action="{{ url('simpan-kategori') }}">
        @csrf
        <table>
            <tr>
                <td>Nama Kategori</td>
                <td><input type="text" name="nama_kategori"></td>
            </tr>
            <tr>
                <td>Deskripsi</td>
                <td>
                    <textarea name="deskripsi"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Simpan"></td>
            </tr>
        </table>
    </form> 
</body>
</html>
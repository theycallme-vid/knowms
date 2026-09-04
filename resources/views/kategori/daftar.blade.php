<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table border="1">
        <tr>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th colspan="2">Aksi</th>
        </tr>
        @foreach ($kategoris as $kategori)
            <tr>
                <td>{{ $kategori->nama_kategori }}</td>
                <td>{{ $kategori->deskripsi }}</td>
                <td>
                    <form action="{{route('kategori.hapus', $kategori)}}" method="POST">
                        @method('DELETE')
                        @csrf 
                        <input type="hidden" name="id" value="{{$kategori->id}}">
                        <input type="submit" value="hapus">
                    </form>
                </td>

            </tr>
        @endforeach
    </table>
</body>
</html>
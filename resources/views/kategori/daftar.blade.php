<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    @if (session('sukses'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            {{ session('sukses') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            {{ session('error') }}
        </div>
    @endif

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
                    <form action="{{route('kategori.hapus', $kategori)}}" method="POST" onsubmit="return confirm('Apakah anda yakin ingin menhapus kategori {{ $kategori->nama_kategori }}?')">
                        @method('DELETE')
                        @csrf 
                        <input type="hidden" name="id" value="{{$kategori->id}}">
                        <input type="submit" value="[HAPUS]">
                    </form>
                </td>
                <td>
                    <a href="{{route('kategori.ubah', $kategori)}}">[UBAH]</a>
                </td>
            </tr>
        @endforeach
    </table>
</body>
</html>
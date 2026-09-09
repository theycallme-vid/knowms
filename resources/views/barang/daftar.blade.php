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
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
            <th colspan=2>Aksi</th>
        </tr>
        @foreach ($barangs as $barang)
            <tr>
                <td>{{ $barang->nama }}</td>
                <td>{{ $barang->harga }}</td>
                <td>{{ $barang->stok }}</td>
                <td>{{ $barang->kategori->nama_kategori }}</td>
                <td>
                    <form action="{{route('barang.hapus', $barang)}}" method="POST" onsubmit="return confirm('Apakah anda yakin ingin menhapus kategori {{ $barang->nama }}?')">
                        @method('DELETE')
                        @csrf
                        <input type="hidden" name="id" value="{{$barang->id}}">
                        <input type="submit" value="[HAPUS]">
                    </form>
                </td>
                <td>
                    <a href="{{route('barang.ubah', $barang)}}">[UBAH]</a>
                </td>
            </tr>
        @endforeach
    </table>

    <!-- PESAN KHUSUS -->
     @if (session('sukes'))
        <div>
            {{ session('sukses') }}
        </div>
     @endif
</body>
</html>
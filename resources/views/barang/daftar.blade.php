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
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
        </tr>
        @foreach ($barangs as $barang)
            <tr>
                <td>{{ $barang->nama }}</td>
                <td>{{ $barang->harga }}</td>
                <td>{{ $barang->stok }}</td>
                <td>{{ $barang->kategori_id }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
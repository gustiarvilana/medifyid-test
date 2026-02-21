<!DOCTYPE html>
<html>
<head>
    <title>Printout Kategori - {{ $data->nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Detail Kategori</h2>
    </div>

    <p><strong>Nama Kategori:</strong> {{ $data->nama }}</p>
    <p><strong>Kode Kategori:</strong> {{ $data->kode }}</p>

    <h4>Daftar Item</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Item</th>
                <th>Nama Item</th>
                <th>Supplier</th>
                <th>Harga Beli</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->supplier }}</td>
                <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $time }}
    </div>
</body>
</html>

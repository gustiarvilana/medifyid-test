<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Printout Pasien - {{ $data->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
            font-size: 11px;
        }

        .patient-photo {
            float: right;
            width: 120px;
            height: 150px;
            border: 1px solid #ccc;
            object-fit: cover;
        }

        .no-photo {
            float: right;
            width: 120px;
            height: 150px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            color: #999;
            font-size: 11px;
        }

        .info-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .info-table th {
            width: 35%;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 10px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            margin-top: 20px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        @if ($data->photo)
            <img src="{{ public_path('storage/photos/pasien/' . $data->photo) }}" class="patient-photo" alt="Photo">
        @else
            <div class="no-photo">No Photo</div>
        @endif
        <h2>REKAM MEDIS PASIEN</h2>
        <p>MedifyID Healthcare System</p>
    </div>

    <div style="clear: both;"></div>

    <table class="info-table">
        <tr>
            <th>Kode Pasien</th>
            <td>{{ $data->kode }}</td>
        </tr>
        <tr>
            <th>Nama Lengkap</th>
            <td>{{ $data->nama }}</td>
        </tr>
        <tr>
            <th>NIK</th>
            <td>{{ $data->nik ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tempat, Tanggal Lahir</th>
            <td>
                {{ $data->tempat_lahir ?? '-' }}, 
                {{ $data->tanggal_lahir ? $data->tanggal_lahir->format('d/m/Y') : '-' }}
                @if ($data->tanggal_lahir)
                    ({{ $data->umur }} tahun)
                @endif
            </td>
        </tr>
        <tr>
            <th>Jenis Kelamin</th>
            <td>
                @if ($data->jenis_kelamin == 'L')
                    Laki-laki
                @elseif ($data->jenis_kelamin == 'P')
                    Perempuan
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Golongan Darah</th>
            <td>{{ $data->golongan_darah ?? '-' }}</td>
        </tr>
        <tr>
            <th>Agama</th>
            <td>{{ $data->agama ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pekerjaan</th>
            <td>{{ $data->pekerjaan ?? '-' }}</td>
        </tr>
        <tr>
            <th>No. Telepon</th>
            <td>{{ $data->no_telepon ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $data->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $data->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <th>Terdaftar</th>
            <td>{{ $data->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ $time }}
    </div>
</body>

</html>

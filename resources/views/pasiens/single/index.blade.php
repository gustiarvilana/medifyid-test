@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Detail Pasien - {{ $data->nama }}</h4>
                    <div class="float-end">
                        <a href="{{ url('pasiens/download-pdf/' . $data->kode) }}" class="btn btn-success btn-sm">Download PDF</a>
                        <a href="{{ url('pasiens/form/edit/' . $data->kode) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ url('pasiens') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            @if ($data->photo)
                                <img src="{{ asset('storage/photos/pasien/' . $data->photo) }}" 
                                     alt="Photo Pasien" 
                                     class="img-thumbnail" 
                                     width="200" 
                                     height="250"
                                     style="object-fit: cover;">
                            @else
                                <div class="img-thumbnail d-flex align-items-center justify-content-center" 
                                     style="width: 200px; height: 250px; background-color: #e9ecef;">
                                    <span class="text-muted">No Photo</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Kode Pasien</th>
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
                                            <span class="badge bg-primary">Laki-laki</span>
                                        @elseif ($data->jenis_kelamin == 'P')
                                            <span class="badge bg-danger">Perempuan</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Golongan Darah</th>
                                    <td>
                                        @if ($data->golongan_darah)
                                            <span class="badge bg-danger">{{ $data->golongan_darah }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
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
                                    <td>
                                        @if ($data->no_telepon)
                                            <a href="tel:{{ $data->no_telepon }}">{{ $data->no_telepon }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        @if ($data->email)
                                            <a href="mailto:{{ $data->email }}">{{ $data->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $data->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Terdaftar Sejak</th>
                                    <td>{{ $data->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>

                            @if (!$data->deleted_at)
                                <form action="{{ url('pasiens/delete/' . $data->id) }}" method="POST" class="mt-3" 
                                      onsubmit="return confirm('Yakin ingin menghapus data pasien ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Hapus Pasien</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

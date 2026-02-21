@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Detail Master Item - {{ $data->nama ?? '' }}
                        <a href="{{ url('master-items/form/edit/' . ($data->id ?? 0)) }}"
                            class="btn btn-warning btn-sm float-end ms-2">Edit</a>
                        <a href="{{ url('master-items') }}" class="btn btn-secondary btn-sm float-end">Kembali</a>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Kode</th>
                                <td>{{ $data->kode ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $data->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis</th>
                                <td>{{ $data->jenis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Harga Beli</th>
                                <td>Rp {{ number_format($data->harga_beli ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Laba</th>
                                <td>{{ $data->laba ?? 0 }}%</td>
                            </tr>
                            <tr>
                                <th>Harga Jual</th>
                                @php
                                    $harga_jual =
                                        ($data->harga_beli ?? 0) +
                                        (($data->harga_beli ?? 0) * ($data->laba ?? 0)) / 100;
                                    $harga_jual = round($harga_jual);
                                @endphp
                                <td>Rp {{ number_format($harga_jual, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>{{ $data->supplier ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Photo</th>
                                <td>
                                    @if (isset($data->photo) && $data->photo)
                                        <img src="{{ asset('storage/photos/' . $data->photo) }}" width="300"
                                            height="300" style="object-fit: cover; border-radius: 5px;">
                                    @else
                                        <span class="text-muted">Tidak ada photo</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>
                                    @forelse($data->kategoris as $kat)
                                        <span class="badge bg-info text-dark">{{ $kat->nama }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th>Dibuat Pada</th>
                                <td>{{ $data->created_at ? $data->created_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Diperbarui Pada</th>
                                <td>{{ $data->updated_at ? $data->updated_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>

                        <form action="{{ url('master-items/delete/' . ($data->id ?? 0)) }}" method="POST" class="mt-3"
                            onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">Hapus Data</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
@endsection

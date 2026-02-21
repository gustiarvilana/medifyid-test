@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    Detail Kategori - {{ $data->nama }}
                    <div class="float-end">
                        <a href="{{ url('kategoris/download-pdf/' . $data->id) }}" class="btn btn-success btn-sm">Download PDF</a>
                        <a href="{{ url('kategoris/form/edit/' . $data->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ url('kategoris') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Kode Kategori</th>
                            <td>{{ $data->kode }}</td>
                        </tr>
                        <tr>
                            <th>Nama Kategori</th>
                            <td>{{ $data->nama }}</td>
                        </tr>
                    </table>

                    <h4 class="mt-4">List Item dengan Kategori Ini</h4>
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th>Supplier</th>
                                <th>Harga Beli</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->items as $item)
                                <tr>
                                    <td>{{ $item->kode }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->supplier }}</td>
                                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ url('master-items/view/' . $item->kode) }}" class="btn btn-primary btn-sm">View Item</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada item dalam kategori ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <form action="{{ url('kategoris/delete/' . $data->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                        @csrf
                        <button type="submit" class="btn btn-danger">Hapus Kategori</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable();
    });
</script>
@endsection

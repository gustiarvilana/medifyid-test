@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{url('kategoris')}}" class="btn btn-secondary">Kembali ke Daftar Kategori</a>
            </div>
            <div class="card">
                <div class="card-header">
                    {{ $method == 'new' ? 'Buat Kategori Baru' : 'Edit Kategori' }}
                </div>

                <div class="card-body">
                    @include('kategoris.form.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

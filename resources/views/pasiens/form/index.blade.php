@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $method == 'new' ? 'Tambah' : 'Edit' }} Data Pasien</h4>
                </div>
                <div class="card-body">
                    @include('pasiens.form.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

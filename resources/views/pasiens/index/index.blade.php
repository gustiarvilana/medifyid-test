@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="form-group mb-2">
                <a href="{{url('pasiens/form/new')}}" class="btn btn-secondary">+ Pasien Baru</a>
                <a href="{{url('pasiens/download-excel')}}" class="btn btn-success">Download Excel</a>
            </div>
            <div class="card">
                <div class="card-header">Daftar Pasien</div>

                <div class="card-body">
                    @include('pasiens.index.filter')
                    @include('pasiens.index.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@include('pasiens.index.js')
@endsection

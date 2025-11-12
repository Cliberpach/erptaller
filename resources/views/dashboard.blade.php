@extends('layouts.template')

@section('title')
    Home
@endsection

@section('content')
    <div class="">
        <div class="card">
            <div class="card-body">
                <div class="col-12" style="display:flex;justify-content:center;">
                    <img src="{{asset('assets/img/logo.png')}}" alt="" style="height: 400px;object-fit:cover;">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

@endsection

@section('js')
    <script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection

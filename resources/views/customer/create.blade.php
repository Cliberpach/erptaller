@extends('layouts.template')

@section('title')
    Registrar nuevo cliente
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">Registrar Cliente</h4>

            <div class="d-flex flex-wrap gap-2">
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('customer.forms.form_create_customer')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>

</script>
@endsection

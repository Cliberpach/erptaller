@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="card" style="background-color: #fafafa;">
        <form action="{{ route('tenant.mantenimientos.horario.store') }}" method="POST">
            @csrf
            <div class="card-header d-flex justify-content-between flex-row">
                <h4 class="card-title">HORARIO DE ATENCIÓN</h4>

                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 p-3">
                <div class="col">
                    <div class="card mb-3">
                        <ul class="list-group list-group-flush">
                            @foreach ($horas_1 as $hora)
                                <li class="list-group-item">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-1" type="checkbox"
                                            id="defaultCheck{{ $loop->iteration }}" value="{{ $hora }}"
                                            name="schedules[]"
                                            {{ $schedules->contains(function ($item) use ($hora) {
                                                return $item->description == $hora;
                                            }) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="defaultCheck{{ $loop->iteration }}"
                                            style="margin-top: 3px !important">
                                            {{ $hora }}
                                        </label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-3">
                        <ul class="list-group list-group-flush">
                            @foreach ($horas_2 as $hora)
                                <li class="list-group-item">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-1" type="checkbox"
                                            id="defaultCheck{{ $loop->iteration }}" value="{{ $hora }}"
                                            name="schedules[]"
                                            {{ $schedules->contains(function ($item) use ($hora) {
                                                return $item->description == $hora;
                                            }) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="defaultCheck{{ $loop->iteration }}"
                                            style="margin-top: 3px !important">
                                            {{ $hora }}
                                        </label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-3">
                        <ul class="list-group list-group-flush">
                            @foreach ($horas_3 as $hora)
                                <li class="list-group-item">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-1" type="checkbox"
                                            id="defaultCheck{{ $loop->iteration }}" value="{{ $hora }}"
                                            name="schedules[]"
                                            {{ $schedules->contains(function ($item) use ($hora) {
                                                return $item->description == $hora;
                                            }) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="defaultCheck{{ $loop->iteration }}"
                                            style="margin-top: 3px !important">
                                            {{ $hora }}
                                        </label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('css')
    <style>
        .badge {
            font-size: 1.8125em !important;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                timer: 1000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif
    </script>
@endsection

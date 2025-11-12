@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between flex-row">
            <h4 class="card-title">LISTA DE EGRESOS</h4>
            <div class="input-group-append">
                <a href="{{ route('tenant.egreso.create') }}" class="btn btn-primary">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2">Añadir nuevo</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <form action="{{ route('tenant.cajas.egreso') }}" method="GET">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="form-group me-3">
                        <label for="from_date">Desde</label>
                        <input type="date" name="from_date" id="from_date" class="form-control"
                            value="{{ $from_today }}">
                    </div>
                    <div class="form-group">
                        <label for="to_date">Hasta</label>
                        <div class="d-flex align-items-center">
                            <input type="date" name="to_date" id="to_date" class="form-control me-2"
                                value="{{ $to_today }}">

                            <button type="submit" class="btn btn-rounded btn-primary">
                                <i class='bx bx-search-alt-2'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="col">
                <table id="miTabla" style="width:100%" class="table-hover table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha de emisión</th>
                            <th>Razón</th>
                            <th>Proveedor</th>
                            <th>Número</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($exit_money->count() > 0)
                            @foreach ($exit_money as $exit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $exit->date }}</td>
                                    <td>{{ $exit->reason }}</td>
                                    <td>{{ $exit->supplier->name }}</td>
                                    <td>{{ $exit->number }}</td>
                                    <td>{{ $exit->total }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <!-- Botón Ver -->
                                            <a class="btn btn-info btn-sm me-1" href="{{ route('tenant.egreso.pdf', $exit->id) }}">
                                                Ver
                                            </a>
                                    
                                            <!-- Botón Editar -->
                                            <a class="btn btn-primary btn-sm me-1" href="{{ route('tenant.egreso.edit', $exit->id) }}">
                                                Editar
                                            </a>
                                    
                                            <!-- Botón Anular -->
                                            <form action="{{ route('tenant.egreso.cancel', $exit->id) }}" method="POST"
                                                onsubmit="return confirm('¿Estás seguro de que quieres anular este egreso?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Anular</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">NO DATA</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection


@section('js')
    <script></script>
@endsection

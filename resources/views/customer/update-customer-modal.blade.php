@extends('layouts.template')

@section('title')
    Editar Cliente
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Editar cliente</h5>
        </div>
        <div class="card-body">
            <form id="updateCustomerForm" action="{{ route('tenant.ventas.cliente.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="document_number" class="form-label">DNI</label>
                    <input type="text" id="document_number" name="document_number" class="form-control" placeholder="Ingrese DNI" value="{{ $customer->document_number }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre (*)</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Ingrese nombre" value="{{ $customer->name }}" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Celular (*)</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Ingrese número de celular" value="{{ $customer->phone }}" required>
                </div>

                <!-- Nuevo Checkbox: Dar Crédito -->
                <div class="mb-3 form-check">
                    <input type="checkbox" id="credit" name="credit" class="form-check-input" value="1" {{ $customer->credit ? 'checked' : '' }}>
                    <label for="credit" class="form-check-label">Dar Crédito</label>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('tenant.ventas.cliente') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

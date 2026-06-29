@extends('layouts.template')

@section('title')
    API de Placas
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">CONFIGURACIÓN — API DE PLACAS</h5>
            <small class="text-muted">Parámetro GLOBAL de la plataforma (aplica a todos los tenants).</small>
        </div>

        <div class="card-body">

            @if (session('message_success'))
                <div class="alert alert-success">{{ session('message_success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('landlord.configuracion.api_placas.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="api_placa_url" class="form-label fw-bold">URL del API de Placas</label>
                    <input type="text" class="form-control" id="api_placa_url" name="api_placa_url"
                        value="{{ old('api_placa_url', $api_placa_url) }}"
                        placeholder="https://multijc.com/api/queryplaca">
                    <small class="text-muted">Base del servicio de consulta de placas (sin la placa ni el token).</small>
                </div>

                <div class="mb-3">
                    <label for="api_placa_token" class="form-label fw-bold">Token</label>
                    <input type="text" class="form-control" id="api_placa_token" name="api_placa_token"
                        value="{{ old('api_placa_token', $api_placa_token) }}"
                        placeholder="Token del proveedor" autocomplete="off">
                    <small class="text-muted">Token de acceso al API (sensible).</small>
                </div>

                <div class="mb-3">
                    <label for="api_placa_bearer" class="form-label fw-bold">Bearer (Authorization) — opcional</label>
                    <input type="text" class="form-control" id="api_placa_bearer" name="api_placa_bearer"
                        value="{{ old('api_placa_bearer', $api_placa_bearer) }}"
                        placeholder="Token Bearer del header Authorization (dejar vacío si no aplica)" autocomplete="off">
                    <small class="text-muted">Token del header Authorization (sensible). Opcional: solo si el API lo exige.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>

        </div>
    </div>
@endsection

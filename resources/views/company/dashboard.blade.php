@extends('layouts.template')

@section('title')
    Dashboard
@endsection

@section('css')
@endsection

@section('content')
    <div class="mb-3">
        <h5 class="mb-0">DASHBOARD</h5>
        <small class="text-muted">Resumen del sistema ErpTaller</small>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Empresas</p>
                        <h3 class="mb-0">{{ $totalEmpresas }}</h3>
                    </div>
                    <span class="badge bg-label-primary rounded-circle p-3">
                        <i class="fas fa-building fa-lg"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Empresas Activas</p>
                        <h3 class="mb-0 text-success">{{ $empresasActivas }}</h3>
                    </div>
                    <span class="badge bg-label-success rounded-circle p-3">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Empresas Bloqueadas</p>
                        <h3 class="mb-0 text-danger">{{ $empresasBloqueadas }}</h3>
                    </div>
                    <span class="badge bg-label-danger rounded-circle p-3">
                        <i class="fas fa-ban fa-lg"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-3">Accesos Rápidos</h6>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('landlord.mantenimientos.empresas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Empresa
                </a>
                <a href="{{ route('landlord.mantenimientos.empresa') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-building"></i> Ver Empresas
                </a>
                <a href="{{ route('landlord.configuracion.backups') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-database"></i> Copias de Seguridad
                </a>
                <a href="{{ route('landlord.configuracion.api_placas') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-cog"></i> Configurar API
                </a>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection

@extends('layouts.template')

@section('title')
    Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">MANTENIMIENTO DE EMPRESAS</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table" id="tabla-users">
                <thead>
                    <tr>
                        <th>RUC</th>
                        <th>RAZON SOCIAL</th>
                        <th>RAZON SOCIAL ABREVIADA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($companies as $company)
                        <tr>
                            <td>{{ $company->ruc }}</td>
                            <td>{{ $company->business_name }}</td>
                            <td>{{ $company->business_name }}</td>
                            <td>
                                <div class="btn-group">
                                    <a class="btn btn-warning btn-sm" href="#">
                                        <i class="fa fa-eye"></i> 
                                    </a>
                                    <a class="btn btn-primary btn-sm" href="{{ route('tenant.mantenimientos.empresa.edit', $company->id) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection


@section('js')
@endsection

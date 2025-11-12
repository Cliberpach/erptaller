@extends('layouts.template')

@section('title')
    Clientes
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <x-card>
        <x-slot name="headerCard">
            <h4 class="card-title">LISTA DE CLIENTES</h4>
            <div class="input-group-append">
                <a href="{{ route('tenant.ventas.cliente.create') }}"><button type="button" class="btn btn-primary btn-add-new" >
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2">NUEVO</p>
                    </div>
                </button></a>
            </div>
        </x-slot>
        <x-slot name="contentCard">
            <div class="row">
                <div class="col">
                    <table id="miTabla" style="width:100%;" class="table table-hover">
                        <thead>
                            <tr>
                                <th data-priority="2" scope="col">DNI</th>
                                <th data-priority="2" scope="col">Nombre</th>
                                <th data-priority="2" scope="col">Celular</th>
                                <th data-priority="2" scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="body-table">
                        </tbody>
                    </table>
                </div>
            </div>
        </x-slot>
    </x-card>

    

@endsection

@section('js')
    <script>

        var customersList = @json($customersList);
        var columns = [
            { data: 'document_number' },
            { data: 'name' },
            { data: 'phone' },
            {
                data: 'id', 
                render: function (data, type, row) {
                    var editUrl = '{{ route("tenant.ventas.cliente.edit", ":id") }}';
                    var deleteUrl = '{{ route("tenant.ventas.cliente.delete", ":id") }}';
                    editUrl = editUrl.replace(':id', data);
                    deleteUrl = deleteUrl.replace(':id', data);
                    return `
                        <a href="${editUrl}">
                            <button data-id="${data}" type="button" data-bs-whatever="Editar Cliente" class="btn btn-warning btn-edit">
                                <i data-id="${data}" class="fas fa-edit btn-edit"></i>
                            </button>
                        </a>
                        <form action="${deleteUrl}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de que quieres eliminar este campo?')">
                                 @csrf
                                @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-delete">
                            <i  class="fas fa-trash-alt btn-delete"></i>
                            </button>
                                            </form>
                    `;
                }
            }
        ];

        </script>
    
    <script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/customers.js') }}" type="module"></script>
@endsection
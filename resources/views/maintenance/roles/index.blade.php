@extends('layouts.template')

@section('title')
    ROLES Y PERMISOS
@endsection

@section('content')
    @include('maintenance.roles.modals.modal_create_role')
    @include('maintenance.roles.modals.modal_permisos')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">ROLES Y PERMISOS</h4>
            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlNuevoRol()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo rol
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @include('maintenance.roles.tables.table_list_roles')
            </div>
        </div>
    </div>
@endsection

<style>
    .swal2-container { z-index: 9999999; }
</style>

<script>
    let dtRoles = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableRoles();
        eventsMdlCreateRol();
        eventsMdlPermisos();
    })

    function iniciarDataTableRoles() {
        const url = '{{ route('tenant.mantenimientos.roles.getRoles') }}';
        dtRoles = new DataTable('#table_roles', {
            serverSide: true,
            processing: true,
            ajax: { url: url, type: 'GET' },
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name' },
                { data: 'permissions_count', name: 'permissions_count', orderable: false, searchable: false,
                  render: d => `<span class="badge bg-info">${d}</span>` },
                { data: 'created_at', name: 'created_at' },
                {
                    data: null, name: 'actions', orderable: false, searchable: false,
                    render: function (data, type, row) {
                        if (Number(row.es_admin) === 1) {
                            return `<span class="badge bg-dark"><i class="fa-solid fa-lock"></i> ACCESO TOTAL</span>`;
                        }
                        return `
                            <div class="btn-group dropstart">
                              <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a class="dropdown-item" href="javascript:void(0);" onclick="abrirPermisos(${data.id}, '${data.name}')">
                                    <i class="fa-solid fa-key"></i> Permisos
                                  </a>
                                </li>
                              </ul>
                            </div>`;
                    }
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "search": "Buscar:",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
                "loadingRecords": "Cargando...", "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles en la tabla"
            }
        });
    }
</script>

@extends('layouts.template')

@section('title')
    SEDES
@endsection

@section('content')
    @include('maintenance.sedes.modals.modal_create_sede')
    @include('maintenance.sedes.modals.modal_edit_sede')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">SEDES</h4>

            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlNuevaSede()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nueva
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('maintenance.sedes.tables.table_list_sedes')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

<script>
    let dtSedes = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableSedes();
        events();
    })

    function events() {
        eventsMdlCreateSede();
        eventsMdlEditSede();
    }

    function iniciarDataTableSedes() {
        const urlGetSedes = '{{ route('tenant.mantenimientos.sedes.getSedes') }}';

        dtSedes = new DataTable('#table_sedes', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetSedes,
                type: 'GET',
            },
            "order": [
                [0, 'asc']
            ],
            columns: [{
                    data: 'numero',
                    name: 'numero'
                },
                {
                    data: 'nombre',
                    name: 'nombre'
                },
                {
                    data: 'codigo',
                    name: 'codigo'
                },
                {
                    data: 'es_principal',
                    name: 'es_principal',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return Number(data) === 1
                            ? '<span class="badge bg-primary">PRINCIPAL</span>'
                            : '<span class="badge bg-secondary">ADICIONAL</span>';
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data === 'ACTIVO'
                            ? '<span class="badge bg-success">ACTIVO</span>'
                            : '<span class="badge bg-warning text-dark">INACTIVO</span>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        const accionEstado = row.status === 'ACTIVO' ?
                            `<i class="fa-solid fa-ban"></i> Desactivar` :
                            `<i class="fa-solid fa-check"></i> Activar`;

                        let toggleItem = '';
                        if (Number(row.es_principal) !== 1) {
                            toggleItem = `
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="toggleEstadoSede(${data.id})">
                                        ${accionEstado}
                                    </a>
                                </li>`;
                        }

                        return `
                            <div class="btn-group dropstart">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEditSede(${data.id})">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                ${toggleItem}
                            </ul>
                            </div>
                        `;
                    },
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles en la tabla"
            }
        });
    }

    function toggleEstadoSede(id) {
        toastr.clear();
        let row = getRowById(dtSedes, id);

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        const accion = row.status === 'ACTIVO' ? 'DESACTIVAR' : 'ACTIVAR';

        swalWithBootstrapButtons.fire({
            title: `¿DESEA ${accion} LA SEDE?`,
            text: `Sede: ${row.nombre}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion.toLowerCase()}!`,
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando estado de la sede...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let url = `{{ route('tenant.mantenimientos.sedes.toggleStatus', ['id' => ':id']) }}`;
                    url = url.replace(':id', id);
                    const token = document.querySelector('input[name="_token"]').value;

                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });

                    const res = await response.json();

                    if (res.success) {
                        dtSedes.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.warning(res.message, 'OPERACIÓN NO PERMITIDA');
                    }
                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN');
                } finally {
                    Swal.close();
                }
            }
        });
    }
</script>

@extends('layouts.template')
@section('title')
    COLABORADORES
@endsection

@section('content')
    @include('maintenance.positions.modals.modal_create_cargo')
    @include('maintenance.positions.modals.modal_edit_cargo')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">COLABORADORES</h4>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" onclick="goToCrearColaborador()">
                    <i class="fa-solid fa-plus"></i> NUEVO
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('maintenance.collaborators.tables.table_list_colaboradores')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end card -->
@endsection

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

<script>
    let dtColaboradores = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableColaboradores();
    })

    function iniciarDataTableColaboradores() {
        const urlGetColaboradores = '{{ route('tenant.mantenimientos.colaboradores.getColaboradores') }}';

        dtColaboradores = new DataTable('#table_colaboradores', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetColaboradores,
                type: 'GET',
            },
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'full_name',
                    name: 'co.full_name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'position_name',
                    name: 'p.name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'address',
                    name: 'co.address',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'phone',
                    name: 'co.phone',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'document_number',
                    name: 'co.document_number',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'work_days',
                    name: 'co.work_days',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'rest_days',
                    name: 'co.rest_days',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'monthly_salary',
                    name: 'co.monthly_salary',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        return formatSoles(data);
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        const baseUrlEdit =
                            `{{ route('tenant.mantenimientos.colaboradores.edit', ['id' => ':id']) }}`;
                        urlEdit = baseUrlEdit.replace(':id', data.id);

                        const urlDelete =
                            `{{ route('tenant.mantenimientos.colaboradores.destroy', ':id') }}`.replace(
                                ':id', data.id);

                        return `
                            <div class="btn-group dropstart">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="${urlEdit}">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarColaborador(${data.id})">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </a>
                                </li>
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
                "emptyTable": "No hay datos disponibles en la tabla",
                "aria": {
                    "sortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    function goToCrearColaborador() {
        window.location.href = @json(route('tenant.mantenimientos.colaboradores.create'));
    }


    function eliminarColaborador(id) {
        toastr.clear();
        let row = getRowById(dtColaboradores, id);
        let message = '';
        let tipo_documento = '';

        if (row.document_type_id == 39) {
            tipo_documento = 'DNI';
        }

        if (row.document_type_id == 41) {
            tipo_documento = 'CARNET EXTRANJERÍA';
        }

        message = `
            <div class="text-center">
                <p>
                    <i class="fas fa-user-times text-danger fa-2x"></i><br>
                </p>

                <p class="mt-3">
                    <i class="fas fa-id-card"></i>
                    <strong>${row.full_name}</strong><br>
                    <i class="fas fa-file-alt"></i> ${tipo_documento}:
                    <strong>${row.document_number}</strong>
                </p>

                <hr>

                <p class="text-danger mt-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Advertencia:</strong><br>
                    También se <u>anulará el usuario asociado</u> a este colaborador.
                </p>
            </div>
        `;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: 'Desea eliminar el colaborador',
            html: `${message}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Eliminando colaborador...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let urlDeleteColaborador =
                        `{{ route('tenant.mantenimientos.colaboradores.destroy', ['id' => ':id']) }}`;
                    urlDeleteColaborador = urlDeleteColaborador.replace(':id', id);
                    const token = document.querySelector('input[name="_token"]').value;

                    const response = await fetch(urlDeleteColaborador, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });

                    const res = await response.json();

                    if (res.success) {
                        dtColaboradores.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR COLABORADOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR COLABORADOR');
                } finally {
                    Swal.close();
                }

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>

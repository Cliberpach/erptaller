@extends('layouts.template')

@section('title')
    CARGOS
@endsection

@section('content')
    @include('maintenance.positions.modals.modal_create_cargo')
    @include('maintenance.positions.modals.modal_edit_cargo')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">CARGOS</h4>

            <div class="d-flex flex-wrap gap-2">
                {{-- <button class="btn btn-warning" onclick="openMdlImportMarca()">
                    <i class="fa-solid fa-upload"></i> IMPORTAR
                </button> --}}

                <a onclick="openMdlNuevoCargo()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('maintenance.positions.tables.table_list_cargos')
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
    let dtCargos = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableCargos();
        iniciarSelect2();
        events();
    })

    function events() {
        eventsMdlCreateCargo();
        eventsMdlEditCargo()
    }

    function iniciarSelect2() {
        $('.select2_form').select2({
            theme: "bootstrap-5",
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: true,
        });
    }

    function iniciarDataTableCargos() {
        const urlGetCargos = '{{ route('tenant.mantenimientos.cargos.getPositions') }}';

        dtCargos = new DataTable('#table_cargos', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetCargos,
                type: 'GET',
            },
            "order": [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    render: function(data, type, row) {

                        return `
                            <div class="btn-group dropstart">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">

                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEditCargo(${data.id})">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarCargo(${data.id})">
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


    function eliminarCargo(id) {
        toastr.clear();
        let row = getRowById(dtCargos, id);
        let message = '';
        let tipo_documento = '';

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: `DESEA ELIMINAR EL CARGO?`,
            text: `Cargo: ${row.name}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Eliminando cargo...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let urlDeleteCargo =
                        `{{ route('tenant.mantenimientos.cargos.destroy', ['id' => ':id']) }}`;
                    urlDeleteCargo = urlDeleteCargo.replace(':id', id);
                    const token = document.querySelector('input[name="_token"]').value;

                    const response = await fetch(urlDeleteCargo, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });

                    const res = await response.json();

                    if (res.success) {
                        dtCargos.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CARGO');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CARGO');
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

@extends('layouts.template')

@section('title')
    Marcas
@endsection

@section('content')
    @include('workshop.brands.modals.mdl_create_marca')
    @include('workshop.brands.modals.mdl_edit_marca')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">LISTA DE MARCAS</h6>
            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlCreateMarca()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('workshop.brands.tables.tbl_list_marcas')
        </div>
    </div>
@endsection

<style>
    .my-swal {
        z-index: 3000 !important;
    }

    input[type="color"].form-control {
        height: 38px;
        padding: 0;
        width: 100%;
    }
</style>

@section('js')
    <script>
        let dtMarcas = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtMarcas();
            events();
        })

        function events() {
            eventsMdlCreateMarca();
            eventsMdlEditMarca();
        }

        function iniciarDtMarcas() {
            dtMarcas = new DataTable('#dt-marcas', {
                "processing": true,
                "ajax": '{{ route('tenant.taller.marcas.getMarcas') }}',
                "columns": [{
                        data: 'id',
                        className: "text-center",
                        "visible": false
                    },
                    {
                        data: 'description',
                        className: "text-center"
                    },
                    {
                        data: null,
                        className: "text-center",
                        render: function(data) {
                            return `
                            <div class="btn-group">
                                <button
                                    class="btn btn-warning btn-sm modificarDetalle"
                                    onclick="openMdlEditMarca(${data.id})"
                                    type="button"
                                    title="Modificar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <a
                                    class="btn btn-danger btn-sm"
                                    href="#"
                                    onclick="eliminar(${data.id})"
                                    title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        `;
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar columna ascendente",
                        sortDescending: ": activar para ordenar columna descendente"
                    },
                    select: {
                        rows: {
                            _: "%d filas seleccionadas",
                            0: "Haz clic en una fila para seleccionarla",
                            1: "1 fila seleccionada"
                        }
                    }
                },
                "order": [
                    [0, "desc"]
                ],
            });

        }

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        function eliminar(id) {
            const fila = getRowById(dtMarcas, id);
            const descripcion = fila?.description || 'Sin descripción';

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar la marca?',
                html: `
                    <div style="text-align: center; font-size: 15px;">
                        <p><i class="fa fa-palette text-primary"></i>
                            <strong>Descripción:</strong> ${descripcion}
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando marca...',
                        html: `
                    <div style="display:flex; align-items:center; justify-content:center; flex-direction:column;">
                        <i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                        <p style="margin:0; font-weight:600;">Por favor, espere un momento</p>
                    </div>
                `,
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });

                    try {
                        const res = await axios.delete(route('tenant.taller.marcas.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtMarcas.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR MARCA');
                    } finally {
                        Swal.close();
                    }

                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalWithBootstrapButtons.fire({
                        title: 'Cancelado',
                        text: 'La solicitud ha sido cancelada.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }

        $(".btn-modal-file").on('click', function() {
            $("#modal_file").modal("show");
        });
    </script>
@endsection

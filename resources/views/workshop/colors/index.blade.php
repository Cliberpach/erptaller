@extends('layouts.template')

@section('title')
    Colores
@endsection

@section('content')
    @include('workshop.colors.modals.mdl_create_color')
    @include('workshop.colors.modals.mdl_edit_color')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">LISTA DE COLORES</h6>
            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlCreateColor()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('workshop.colors.tables.tbl_list_colores')
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
        let dtColores = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtColores();
            events();
        })

        function events() {
            eventsMdlCreateColor();
            eventsMdlEditColor();
        }

        function iniciarDtColores() {
            dtColores = new DataTable('#dt-colores', {
                "processing": true,
                "ajax": '{{ route('tenant.taller.colores.getColores') }}',
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
                        data: 'codigo',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!data) {
                                return '<span class="text-muted">—</span>';
                            }
                            return `
                        <div style="
                            display: inline-block;
                            width: 25px;
                            height: 25px;
                            border-radius: 5px;
                            border: 1px solid #ccc;
                            background-color: ${data};
                        " title="${data}"></div>
                        <div style="font-size: 0.85rem; margin-top: 3px;">${data}</div>
                    `;
                        }
                    },
                    {
                        data: null,
                        className: "text-center",
                        render: function(data) {
                            return `
                            <div class="btn-group">
                                <button
                                    class="btn btn-warning btn-sm modificarDetalle"
                                    onclick="openMdlEditColor(${data.id})"
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
            const fila = getRowById(dtColores, id);
            const descripcion = fila?.description || 'Sin descripción';
            const codigo = fila?.codigo || '#ffffff';

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar el color?',
                html: `
            <div style="text-align: center; font-size: 15px;">
                <p><i class="fa fa-palette text-primary"></i>
                    <strong>Descripción:</strong> ${descripcion}
                </p>
                <p><i class="fa fa-square text-info"></i>
                    <strong>Código:</strong> ${codigo}
                    <span style="display:inline-block; width:20px; height:20px; background:${codigo}; border:1px solid #ccc; margin-left:6px; vertical-align:middle; border-radius:4px;"></span>
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
                        title: 'Eliminando color...',
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
                        const res = await axios.delete(route('tenant.taller.colores.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtColores.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR COLOR');
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

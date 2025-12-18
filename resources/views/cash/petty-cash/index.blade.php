@extends('layouts.template')

@section('title')
    Caja
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>
        .my-swal {
            z-index: 3000 !important;
        }
    </style>
@endsection


@section('content')
    @include('cash.petty-cash.modals.mdl_create_cash')
    @include('cash.petty-cash.modals.mdl_edit_cash')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">LISTA DE CAJAS</h6>
            <div class="input-group-append">
                <a onclick="openMdlCreateCash()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> NUEVO
                </a>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('cash.petty-cash.tables.tbl_cash_list')
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtCash = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtCash();
            events();
        })

        function events() {
            eventsMdlCreateCash();
            eventsMdlEditCash();
        }

        function iniciarDtCash() {
            dtCash = new DataTable('#dt-cash', {
                "processing": true,
                "ajax": '{{ route('tenant.cajas.getListCash') }}',
                "order": [
                    [0, "desc"]
                ],
                "columns": [{
                        data: 'id',
                        "visible": false,
                        "searchable": false
                    },
                    {
                        data: 'name',
                        name: 'c.name',
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'created_at',
                        name: 'c.created_at',
                        searchable: true,
                        orderable: true,
                        type: 'string'
                    },
                    {
                        data: 'status',
                        name: 'c.status',
                        searchable: true,
                        orderable: true,
                        render: function(status) {

                            let badgeClass = '';

                            switch (status) {
                                case 'ANULADO':
                                    badgeClass = 'bg-dark';
                                    break;

                                case 'CERRADO':
                                    badgeClass = 'bg-danger';
                                    break;

                                case 'ABIERTO':
                                    badgeClass = 'bg-success';
                                    break;
                            }

                            return `<span class="badge ${badgeClass}">${status}</span>`;
                        }
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: null,
                        className: "text-end",
                        render: function(data) {
                            return `
                            <div class="btn-group">
                                <button
                                    class="btn btn-warning btn-sm modificarDetalle"
                                    onclick="openMdlEditCash(${data.id})"
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
                }
            });

        }

        function eliminar(id) {
            const fila = getRowById(dtCash, id);
            const name = fila.name;
            const messageHtml = `
                <div class="text-center" style="font-size: 15px;">
                    <p class="mb-2">
                        <i class="fas fa-tag text-primary me-2"></i>
                        <strong>Nombre:</strong> ${name}
                    </p>
                </div>
            `;

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar la caja?',
                html: messageHtml,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando caja...',
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
                        const res = await axios.delete(route('tenant.cajas.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtCash.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CAJA');
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
    </script>
@endsection

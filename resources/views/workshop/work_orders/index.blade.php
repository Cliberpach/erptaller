@extends('layouts.template')

@section('title')
    Órdenes Trabajo
@endsection

@section('content')
    @include('workshop.work_orders.modals.mdl_show_order')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">LISTA DE ÓRDENES DE TRABAJO</h4>

            <div class="d-flex flex-wrap gap-2">
                {{-- <button class="btn btn-warning" onclick="openMdlImportMarca()">
                    <i class="fa-solid fa-upload"></i> IMPORTAR
                </button> --}}

                <a href="{{ route('tenant.taller.ordenes_trabajo.create') }}" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('workshop.work_orders.tables.tbl_list_orders')
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

@section('js')
    <script>
        let dtOrders = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadDtOrders();
            events();
        })

        function events() {}

        function loadDtOrders() {
            dtOrders = new DataTable('#dt-orders', {
                "serverSide": true,
                "processing": true,
                "ajax": '{{ route('tenant.taller.ordenes_trabajo.getWorkOrders') }}',
                "columns": [{
                        data: 'id',
                        name: 'o.id',
                        className: "text-center",
                        "visible": false,
                        "searchable": false
                    },
                    {
                        data: 'code',
                        className: "text-center",
                        "visible": true,
                        "searchable": true,
                        "orderable": true
                    },
                    {
                        data: 'quote_code',
                        className: "text-center",
                        "searchable": true,
                        "orderable": true
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'plate',
                        name: 'o.plate',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'warehouse_name',
                        name: 'o.warehouse_name',
                        searchable: true,
                        orderable: true,
                        visible: false,
                        className: "text-center"
                    },
                    {
                        data: 'total',
                        name: 'o.total',
                        searchable: false,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'create_user_name',
                        name: 'o.create_user_name',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'status',
                        name: 'o.status',
                        searchable: false,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row) {

                            let badgeClass = '';
                            let label = data ?? '';

                            switch (data) {
                                case 'ACTIVO':
                                    badgeClass = 'badge bg-primary';
                                    break;
                                case 'ANULADO':
                                    badgeClass = 'badge bg-danger';
                                    break;
                                case 'CONVERTIDO':
                                    badgeClass = 'badge bg-warning';
                                    break;
                                case 'EXPIRADO':
                                    badgeClass = 'badge bg-dark';
                                    break;
                                default:
                                    badgeClass = 'badge bg-secondary';
                                    break;
                            }

                            return `<span class="${badgeClass}">${label}</span>`;

                        }
                    },
                    {
                        data: 'created_at',
                        name: 'o.created_at',
                        searchable: false,
                        orderable: false,
                        className: "text-center"
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: null,
                        className: "text-center",
                        render: function(data) {

                            let actions = `
                                            <div class="dropdown text-center">
                                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fa fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item generarPDF"
                                                           href="${route('tenant.taller.ordenes_trabajo.pdfOne', data.id)}" target="_blank"
                                                            title="PDF" role="button" aria-label="Generar PDF">
                                                            <i class="fas fa-file-pdf me-2 text-danger"></i> PDF
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="openMdlShowOrder(${data.id})">
                                                            <i class="fa fa-eye me-2"></i> Ver
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item modificarDetalle" href="#" onclick="redirectParams('tenant.taller.ordenes_trabajo.edit', ${data.id})">
                                                            <i class="fa fa-edit me-2"></i> Modificar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="eliminar(${data.id})">
                                                            <i class="fa fa-trash me-2"></i> Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            `;
                            return actions;
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
            const fila = getRowById(dtOrders, id);
            const htmlInfo = `
                <div class="card shadow-sm border-0">
                    <div class="card-body p-2" style="font-size: 1.2rem;">

                        <div class="mb-1">
                            <i class="fas fa-hashtag text-dark me-1 small"></i>
                            <span class="fw-bold small">N° Orden:</span><br>
                            <span class="text-muted small">${fila.id}</span>
                        </div>

                        <div class="mb-1">
                            <i class="fas fa-user text-primary me-1 small"></i>
                            <span class="fw-bold small">Cliente:</span><br>
                            <span class="text-muted small">${fila.customer_name}</span>
                        </div>

                        <div class="mb-1">
                            <i class="fas fa-car text-info me-1 small"></i>
                            <span class="fw-bold small">Placa:</span><br>
                            <span class="text-muted small">${fila.plate}</span>
                        </div>

                         <div class="mb-1">
                            <i class="fas fa-coins text-warning me-1 small"></i>
                            <span class="fw-bold small">Total:</span><br>
                            <span class="text-muted small">S/ ${formatSoles(fila.total)}</span>
                        </div>

                    </div>
                </div>
            `;

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar la orden?',
                html: `${htmlInfo}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando orden...',
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
                        const res = await axios.delete(route('tenant.taller.ordenes_trabajo.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtOrders.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR ORDEN');
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

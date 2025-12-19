@extends('layouts.template')

@section('title')
    Órdenes Trabajo
@endsection

@section('content')
    @include('workshop.work_orders.modals.mdl_show_order')
    <div class="card overflow-hidden">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE ÓRDENES DE TRABAJO</h6>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 text-md-right mt-md-0 mt-2" style="text-align:end;">
                    <a href="{{ route('tenant.taller.ordenes_trabajo.create') }}" class="btn btn-primary text-white">
                        <i class="fas fa-plus-circle"></i> Nuevo
                    </a>
                </div>
            </div>

            <!-- Fila 2: Filtro Cliente -->
            <div class="row">

                <!-- Cliente -->
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-primary mr-1"></i> Cliente:
                    </label>
                    <select class="form-control" id="client_id" name="client_id">
                        <option value="">Seleccione un cliente</option>
                    </select>
                    <p class="client_id_error msgError mb-0"></p>
                </div>

                <!-- Fecha Inicio -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-success mr-1"></i> Fecha inicio:
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>

                <!-- Fecha Fin -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-check text-danger mr-1"></i> Fecha fin:
                    </label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>

                <!-- Estado -->
                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks text-info mr-1"></i> Estado:
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todo</option>
                        <option selected value="ACTIVO">Activo</option>
                        <option value="FINALIZADO">Finalizado</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2 text-end">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-block"
                        onclick="filterData();">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>

            </div>

        </div>

        <div class="card-body p-0 pb-2">
            @include('workshop.work_orders.tables.tbl_list_orders')
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
            loadTomSelect();
            events();
            initTooltips();

        })

        function events() {
        }

        function loadTomSelect() {
            window.clientSelect = new TomSelect('#client_id', {
                valueField: 'id',
                labelField: 'full_name',
                searchField: ['full_name'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un cliente',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastCustomerQuery = str;
                },
                load: async (query, callback) => {
                    if (!query.length) return callback();
                    try {
                        const url = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar clientes');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
                        if (results.length === 0) {
                            customerParams.documentSearchCustomer = lastCustomerQuery;
                            console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                        }
                    } catch (error) {
                        console.error('Error cargando clientes:', error);
                        callback();
                    }
                },
                render: {
                    option: (item, escape) => `
                        <div>
                            <strong>${escape(item.full_name)}</strong><br>
                            <small>${escape(item.email ?? '')}</small>
                        </div>
                    `,
                    item: (item, escape) => `<div>${escape(item.full_name)}</div>`
                }
            });

        }

        function loadDtOrders() {
            dtOrders = new DataTable('#dt-orders', {
                "serverSide": true,
                "processing": true,
                ajax: {
                    url: '{{ route('tenant.taller.ordenes_trabajo.getWorkOrders') }}',
                    data: function(d) {
                        d.customer_id = $('#client_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                    }
                },
                "columns": [{
                        data: 'id',
                        name: 'o.id',
                        "visible": false,
                        "searchable": false
                    },
                    {
                        data: 'code',
                        "visible": true,
                        "searchable": true,
                        "orderable": true
                    },
                    {
                        data: 'quote_code',
                        "searchable": true,
                        "orderable": true
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'plate',
                        name: 'o.plate',
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'warehouse_name',
                        name: 'o.warehouse_name',
                        searchable: true,
                        orderable: true,
                        visible: false,
                    },
                    {
                        data: 'total',
                        name: 'o.total',
                        searchable: false,
                        orderable: false,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'on_account',
                        name: 'on_account',
                        searchable: false,
                        orderable: false,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                        searchable: false,
                        orderable: false,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'create_user_name',
                        name: 'o.create_user_name',
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'status',
                        name: 'o.status',
                        searchable: false,
                        orderable: false,
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
                        data: 'status_invoice',
                        name: 'o.status_invoice',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row) {

                            let badgeClass = '';
                            let label = data ?? '';

                            switch (data) {
                                case 'FACTURADO':
                                    badgeClass = 'badge bg-primary';
                                    break;
                                case 'NO FACTURADO':
                                    badgeClass = 'badge bg-danger';
                                    break;
                                case 'FACTURADO PARCIAL':
                                    badgeClass = 'badge bg-warning';
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
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: null,
                        className: "text-end",
                        render: function(data) {

                            let actions = `<div class="dropdown text-center">
                                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fa fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">`;


                            actions += `
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
                                        `;

                            if (data.status_invoice != 'FACTURADO') {
                                actions += `<li>
                                                <a class="dropdown-item text-primary" href="#" onclick="redirectParams('tenant.taller.ordenes_trabajo.invoiceCreate',${data.id})">
                                                    <i class="fa fa-file-invoice-dollar me-2"></i> Facturar
                                                </a>
                                            </li>`;
                            }

                            if (data.status == 'ACTIVO') {
                                actions += `
                                            <li>
                                                <a class="dropdown-item modificarDetalle" href="#" onclick="redirectParams('tenant.taller.ordenes_trabajo.edit', ${data.id})">
                                                    <i class="fa fa-edit me-2"></i> Modificar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-primary" href="#" onclick="finalizar(${data.id})">
                                                            <i class="fa fa-check-circle me-2"></i> Finalizar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="eliminar(${data.id})">
                                                    <i class="fa fa-trash me-2"></i> Eliminar
                                                </a>
                                            </li>`;
                            }

                            actions += `</ul></div>`;

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

            Swal.fire({
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

        function finalizar(id) {
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

                        <hr class="my-2">

                        <div class="alert alert-warning p-2 mb-0 small d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>
                                Al finalizar la orden, <strong>se restará el stock</strong> de los productos utilizados.
                            </span>
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
                title: '¿Desea finalizar la orden?',
                html: `${htmlInfo}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Finalizando orden...',
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
                        const res = await axios.post(route('tenant.taller.ordenes_trabajo.finish', id));
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

        function filterData() {
            const startDate = document.getElementById('start_date')?.value;
            const endDate = document.getElementById('end_date')?.value;

            if (startDate && endDate) {
                if (startDate > endDate) {
                    toastr.error(
                        'La fecha inicio no puede ser mayor que la fecha fin',
                        'Fechas inválidas'
                    );
                    return;
                }
            }

            dtOrders.ajax.reload();
        }
    </script>
@endsection

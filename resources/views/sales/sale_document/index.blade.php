@extends('layouts.template')

@section('title')
    Ventas
@endsection

@section('css')
@endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE VENTAS</h6>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 text-md-right mt-md-0 mt-2" style="text-align:end;">
                    <button onclick="goToSaleCreate()" type="button" data-bs-whatever="Nueva caja"
                        class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <div class="lign-items-center d-flex align-items-center">
                            <i class="fas fa-plus pe-1"></i>
                            <p class="mb-0 ml-2"> NUEVO</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Fila 2: Filtro Cliente -->
            <div class="row">

                <!-- Cliente -->
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-primary mr-1"></i> Cliente:
                    </label>
                    <select class="form-control" id="customer_id" name="customer_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="customer_id_error msgError mb-0"></p>
                </div>

                <!-- Fecha Inicio -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-success mr-1"></i> Fecha inicio:
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $fecha_inicio }}">
                </div>

                <!-- Fecha Fin -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-check text-danger mr-1"></i> Fecha fin:
                    </label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $fecha_fin }}">
                </div>

                <!-- Placa -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-car text-dark mr-1"></i> Placa:
                    </label>
                    <input type="text" class="form-control" id="plate" name="plate" placeholder="Ej. TXT-987"
                        onkeydown="if(event.key==='Enter'){ filterData(); }">
                </div>

                <!-- Estado -->
                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks text-info mr-1"></i> Sunat:
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todo</option>
                        <option value="ACEPTADO">Aceptado</option>
                        <option selected value="PENDIENTE">Pendiente</option>
                        <option value="ENVIADO">Enviado</option>
                        <option value="RECHAZADO">Rechazado</option>
                        <option value="ANULADO">Anulado</option>
                        <option value="ANULADO PARCIAL">Anulado Parcial</option>
                    </select>
                </div>

                @if ($esAdmin)
                    <!-- Vendedor (solo admin) -->
                    <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-tie text-secondary mr-1"></i> Vendedor:
                        </label>
                        <select class="form-control" id="seller_id" name="seller_id">
                            <option value="">Todos</option>
                            @foreach ($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2 text-end">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-block" onclick="filterData();">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>

            </div>

        </div>
        <div class="card-body p-0 pb-2">

            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        @include('sales.sale_document.tables.tbl_list_sales')
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@section('js')
    <script>
        let dtSales = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadTomSelect();
            events();
        })

        function events() {
            startDataTableSales();
        }

        function loadTomSelect() {
            const customerEl = document.getElementById('customer_id');
            // Guarda anti-doble-init: si ya es TomSelect, no re-inicializar.
            if (!customerEl || customerEl.tomselect) return;

            window.clientSelect = new TomSelect(customerEl, {
                valueField: 'id',
                labelField: 'full_name',
                searchField: ['full_name'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un cliente',
                maxOptions: 20,
                create: false,
                preload: false,
                load: async (query, callback) => {
                    if (query.length < 3) return callback();
                    try {
                        const url = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar clientes');
                        const data = await response.json();
                        callback(data.data ?? []);
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
                    item: (item, escape) => `<div>${escape(item.full_name)}</div>`,
                    no_results: (data, escape) => `
                        <div class="no-results">
                            <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                            Sin resultados
                        </div>
                    `
                }
            });
        }

        function startDataTableSales() {
            const urlGetSales = '{{ route('tenant.ventas.comprobante_venta.getSales') }}';

            dtSales = new DataTable('#tbl_list_sales', {
                responsive: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetSales,
                    type: 'GET',
                    data: function(d) {
                        d.customer_id = $('#customer_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                        d.plate = $('#plate').val();
                        // Filtro Vendedor solo existe para admin; no-admin no lo envía
                        // (y el backend lo ignora de todos modos: su query es fijo a su user).
                        d.seller_id = $('#seller_id').val() || '';
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [

                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'fecha_registro',
                        name: 'fecha_registro'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'plate',
                        name: 'plate'
                    },
                    {
                        data: 'vendedor',
                        name: 'vendedor',
                        searchable: false
                    },
                    {
                        data: 'doc',
                        name: 'doc'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            let badge_class = '';

                            if (data.sunat_status === 'PENDIENTE') {
                                badge_class = 'danger';
                            }
                            if (data.sunat_status === 'ENVIADO') {
                                badge_class = 'warning';
                            }
                            if (data.sunat_status === 'ACEPTADO') {
                                badge_class = 'primary';
                            }
                            if (data.sunat_status === 'RECHAZADO') {
                                badge_class = 'dark';
                            }

                            return `<span class="badge bg-${badge_class}">${data.sunat_status}</span>`;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            let badge_class = '';

                            if (data.payment_status === 'PENDIENTE') {
                                badge_class = 'danger';
                            }
                            if (data.payment_status === 'PAGADO') {
                                badge_class = 'primary';
                            }

                            return `<span class="badge bg-${badge_class}">${data.payment_status}</span>`;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'metodo_pago',
                        name: 'metodo_pago',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            const urlPdf =
                                "{{ route('tenant.ventas.comprobante_venta.pdf_voucher', ':id') }}".replace(
                                    ':id', data.id);
                            const urlDownloadXml =
                                "{{ route('tenant.ventas.comprobante_venta.downloadXml', ':id') }}".replace(
                                    ':id', data.id);
                            const urlDownloadCdr =
                                "{{ route('tenant.ventas.comprobante_venta.downloadCdr', ':id') }}".replace(
                                    ':id', data.id);

                            let acciones = `
                            <div class="dropdown float-end">
                                <button
                                    class="btn btn-white btn-sm btn-shadow btn-icon dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    aria-expanded="false">
                                    <i class="fi fi-rr-menu-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                            `;

                            acciones += `<li>
                                            <a class="dropdown-item" target="_blank" href="${urlPdf}">
                                                <i class="fa-solid fa-file-pdf text-danger"></i> PDF
                                            </a>
                                        </li>`;

                            // XML/CDR movidos desde la columna Descargas (solo comprobantes SUNAT).
                            if (data.ruta_xml) {
                                acciones += `<li>
                                                <a class="dropdown-item" href="${urlDownloadXml}">
                                                    <i class="fa-solid fa-file-excel text-success"></i> XML
                                                </a>
                                            </li>`;
                            }

                            if (data.ruta_cdr) {
                                acciones += `<li>
                                                <a class="dropdown-item" href="${urlDownloadCdr}">
                                                    <i class="fa-solid fa-book text-primary"></i> CDR
                                                </a>
                                            </li>`;
                            }

                            if (data.type_sale_code === '09' || data.type_sale_code === '01') {

                                if (data.sunat_status === 'PENDIENTE' || data.sunat_status ===
                                    'RECHAZADO') {
                                    acciones += `<li>
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="sendSunat(${data.id})">
                                                        <i class="fa-solid fa-paper-plane"></i> Sunat
                                                    </a>
                                                </li>`;
                                }

                            }

                            acciones += `</ul></div>`;


                            return acciones;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 25,
                lengthChange: false,
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

        function goToSaleCreate() {
            const route = @json(route('tenant.ventas.comprobante_venta.create'));
            window.location.href = route;
        }

        function sendSunat(sale_document_id) {

            const sale_document = getRowById(dtSales, sale_document_id);

            let message = `Enviar el documento: ${sale_document.serie}-${sale_document.correlative} a Sunat?`;

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: message,
                text: "OPERACIÓN NO REVERSIBLE!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, enviar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: `Documento de venta: ${sale_document.serie}-${sale_document.correlative}`,
                        html: "ENVIANDO A SUNAT...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        toastr.clear();
                        const token = document.querySelector('input[name="_token"]').value;

                        const formData = new FormData();
                        const urlInvoice = @json(route('tenant.ventas.comprobante_venta.send_sunat'));

                        formData.append('sale_document_id', sale_document_id);

                        const response = await fetch(urlInvoice, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        });

                        const res = await response.json();

                        Swal.close();

                        if (response.status === 422) {
                            if ('errors' in res) {
                                //pintarErroresValidacion(res.errors);
                            }
                            Swal.close();
                            return;
                        }

                        if (res.success) {
                            dtSales.ajax.reload(null, false);
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                            //     window.location.href    =   sale_index;
                            Swal.close();
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR VENTA');
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

            dtSales.ajax.reload();
        }
    </script>
@endsection

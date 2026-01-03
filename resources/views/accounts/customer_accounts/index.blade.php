@extends('layouts.template')

@section('title')
    Cuentas Cliente
@endsection

@section('content')
    @include('accounts.customer_accounts.modalDetalle')

    <div class="card">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE CUENTAS CLIENTE</h6>
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
                        <option selected value="PENDIENTE">Pendiente</option>
                        <option value="PAGADO">Pagado</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2 text-end">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-block" onclick="filterData();">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>

            </div>

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('accounts.customer_accounts.tables.tbl_list_cuentas')
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
        let dtCuentasCliente = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtCuentasCliente();
            loadTomSelect();
            events();
            setDatosDefault();
        })

        function events() {
            eventsMdlPagar();
            iniciarSelectsMdlPagar();
        }

        function iniciarDtCuentasCliente() {
            dtCuentasCliente = $('.dataTables-cajas').DataTable({
                processing: true,
                serverSide: true,
                bPaginate: true,
                bLengthChange: true,
                bFilter: true,
                bInfo: true,
                bAutoWidth: false,
                ajax: {
                    url: "{{ route('tenant.cuentas.cliente.getCustomerAccounts') }}",
                    data: function(d) {
                        d.customer_id = $('#customer_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'ca.id',
                        visible: false
                    },
                    {
                        data: 'customer_name',
                        name: 'wo.customer_name'
                    },
                    {
                        data: 'document_number',
                        name: 'ca.document_number'
                    },
                    {
                        data: 'document_date',
                        name: 'ca.document_date'
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: 'amount',
                        name: 'ca.amount',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'paid_amount',
                        orderable: false,
                        searchable: false,
                        name: 'paid_amount',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: 'balance',
                        name: 'ca.balance',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'status',
                        name: 'ca.status',
                        searchable: false,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row) {

                            let badgeClass = '';
                            let label = data ?? '';

                            switch (data) {

                                case 'PENDIENTE':
                                    badgeClass = 'badge bg-danger';
                                    break;
                                case 'PAGADO':
                                    badgeClass = 'badge bg-primary';
                                    break;
                                case 'ANULADO':
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
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function(data, type, row) {
                            return `<button data-id='${row.id}' onclick="openMdlPagar(${row.id})" class='btn btn-primary btn-sm btn-detalle'>
                            <i class='fa fa-list'></i>
                        </button>`;
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
                order: [
                    [0, "desc"]
                ]
            });
        }


        function loadTomSelect() {
            window.clientSelect = new TomSelect('#customer_id', {
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

        //------------------------------
        $('.dataTables-detalle').DataTable({
            "bPaginate": false,
            "bLengthChange": true,
            "bFilter": true,
            "bInfo": false,
            "bAutoWidth": false,
            "processing": true,
            "order": [
                [0, "desc"]
            ],
            'aoColumns': [{
                    sClass: 'text-center'
                },
                {
                    sClass: 'text-center'
                },
                {
                    sClass: 'text-center'
                },
                {
                    sClass: 'text-center'
                }
            ],
        });

        $("#btn_buscar").on('click', function() {
            $('.dataTables-cajas').DataTable().ajax.reload();
        });

        $("#btn_pdf").on('click', function() {
            var cliente = $("#cliente_b").val();
            var estado = $("#estado_b").val();

            let enviar = true;

            if (cliente == null || cliente == '') {
                toastr.error("Seleccionar cliente", "Error")
                enviar = false;
            }

            if (estado == null || estado == '') {
                toastr.error("Seleccionar estado", "Error")
                enviar = false;
            }

            if (enviar) {
                var url_open_pdf = '/cuentaCliente/detalle?id=' + cliente + '&estado=' + estado;
                window.open(url_open_pdf, 'Informe SISCOM',
                    'location=1, status=1, scrollbars=1,width=900, height=600');
            }
        });

        function setDatosDefault() {
            window.modoPagoSelect.setValue(3);
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

            dtCuentasCliente.ajax.reload();
        }
    </script>
@endsection

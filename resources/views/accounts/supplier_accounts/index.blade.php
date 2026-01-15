@extends('layouts.template')

@section('title')
    LISTA CUENTAS PROVEEDOR
@endsection

@push('js-head')
    @vite(['resources/js/libs/filepond.js'])
@endpush

@section('content')
    @include('accounts.supplier_accounts.modals.mdl_pagar')
    @include('accounts.supplier_accounts.modals.mdl_ver')


    <div class="card">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE CUENTAS PROVEEDOR</h6>
                </div>
            </div>

            <!-- Fila 2: Filtro Cliente -->
            <div class="row">

                <!-- Cliente -->
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-primary mr-1"></i> Proveedor:
                    </label>
                    <select class="form-control" id="supplier" name="supplier">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="filterSupplier_error msgError mb-0"></p>
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

            <div class="row">
                <div class="col-lg-12 d-flex align-items-end justify-content-end">
                    <button class="btn btn-success" style="margin-right: 10px;" onclick="downloadExcel();">
                        <i class="fas fa-file-excel"></i> EXCEL
                    </button>
                    <button class="btn btn-danger" onclick="downloadPdf()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('accounts.supplier_accounts.tables.tbl_cobranza_list')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtCobranzas = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadDtAccounts();
            loadTomSelect();
            events();
        })

        function events() {
            eventsMdlPagar();
        }

        function loadDtAccounts() {
            const url = '{{ route('tenant.cuentas.proveedor.getAll') }}';

            dtCobranzas = new DataTable('#tbl_cobranza_list', {
                serverSide: true,
                processing: true,
                ajax: {
                    url: url,
                    type: 'GET',
                    data: function(d) {
                        d.supplier = document.querySelector('#supplier').value;
                        d.status = document.querySelector('#status').value;
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'sa.id',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'supplier_name',
                        name: 'pd.supplier_name'
                    },
                    {
                        data: 'document_number',
                        name: 'sa.document_number'
                    },
                    {
                        data: 'document_date',
                        name: 'sa.document_date'
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: 'amount',
                        name: 'sa.amount',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'paid',
                        orderable: false,
                        searchable: false,
                        name: 'sa.paid',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: 'balance',
                        name: 'sa.balance',
                        render: function(data) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'status',
                        name: 'sa.status',
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
                        render: function(data, type, row) {

                            let acciones = `
                            <div class="btn-group">
                                <button type="button" class="dropdown-toggle btn btn-primary btn-sm"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-grip text-white"></i>
                                </button>
                                <ul class="dropdown-menu" style="max-height:150px; overflow-y:auto;">
                        `;

                            if (data.status === 'PENDIENTE') {
                                acciones += `
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                    href="javascript:void(0);"
                                    onclick="openMdlPagar(${data.id})">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <span>Pagar</span>
                                    </a>
                                </li>
                            `;
                            }

                            acciones += `
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                href="javascript:void(0);"
                                onclick="openMdlVerCobranza(${data.id})">
                                    <i class="fas fa-eye text-info"></i>
                                    <span>Ver</span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                href="javascript:void(0);"
                                onclick="descargarPdf(${data.id})">
                                    <i class="far fa-file-pdf text-danger"></i>
                                    <span>PDF</span>
                                </a>
                            </li>
                        </ul>
                        </div>
                        `;

                            return acciones;
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

        function loadTomSelect() {
            window.supplierSelect = new TomSelect('#supplier', {
                valueField: 'id',
                labelField: 'full_name',
                searchField: ['full_name'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un proveedor',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastCustomerQuery = str;
                },
                load: async (query, callback) => {
                    if (!query.length) return callback();
                    try {
                        const url = `{{ route('tenant.utils.searchSupplier') }}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar proveedores');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
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

        function descargarPdf(id) {
            let url = "{{ route('tenant.cuentas.proveedor.pdfOne') }}";

            url += "?id=" + id;

            window.open(url, '_blank');
        }

        function filterData() {
            toastr.clear();
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

            dtCobranzas.ajax.reload();
        }

        function downloadPdf() {

            const url = route('tenant.cuentas.proveedor.pdfAll', {
                supplier: document.querySelector('#supplier').value,
                status: document.querySelector('#status').value,
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            });

            window.open(url, '_blank');

        }

        function downloadExcel() {

            const url = route('tenant.cuentas.proveedor.excelAll', {
                supplier: document.querySelector('#supplier').value,
                status: document.querySelector('#status').value,
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            });

            window.open(url, '_blank');

        }
    </script>
@endsection

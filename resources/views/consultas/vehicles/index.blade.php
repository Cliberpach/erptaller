@extends('layouts.template')
@section('title')
    CONSULTA VEHÍCULOS
@endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">Consulta Vehículos</h6>
                </div>
            </div>

            <div class="row mb-3">

                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <label class="form-label fw-bold">Cliente:</label>
                    <select class="form-control" id="customer_id" name="customer_id" required>
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="customer_id_error msgError mb-0"></p>
                </div>

                <!-- Vehículo -->
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <label class="form-label fw-bold">Vehículo:</label>
                    <select class="form-control" id="vehicle_id" name="vehicle_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="vehicle_id_error msgError mb-0"></p>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                    <label for="fecha_inicio" style="font-weight:bold;">FECHA INICIO</label>
                    <input type="date" class="form-control" id="fecha_inicio">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                    <label for="fecha_fin" style="font-weight:bold;">FECHA FIN</label>
                    <input type="date" class="form-control" id="fecha_fin">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12" style="text-align: end;margin-top:auto;">
                    <button class="btn btn-primary btnFiltrar"><i class="fas fa-search"></i> FILTRAR</button>
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
        <div class="card-body p-0 pb-2">
            @include('consultas.vehicles.tables.tbl_list')
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtQuery = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadtDtQuery();
            loadTomSelect();
            events();
        })

        function events() {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.btnFiltrar')) {
                    filtrar();
                }
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

            window.vehicleSelect = new TomSelect('#vehicle_id', {
                valueField: 'id',
                labelField: 'text',
                searchField: ['text'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un vehículo',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastVehicleQuery = str;
                },
                load: async (query, callback) => {
                    if (!query.length) return callback();
                    try {
                        const url = route('tenant.utils.searchVehicle', {
                            q: query,
                            customer_id: window.clientSelect.getValue()
                        });

                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar vehiculos');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
                        if (results.length === 0) {
                            vehicleParams.plateSearchVehicle = lastVehicleQuery;
                            console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                        }
                    } catch (error) {
                        console.error('Error cargando vehiculos:', error);
                        callback();
                    }
                },
                render: {
                    option: (item, escape) => `
                        <div>
                            <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                            <strong>${escape(item.text)}</strong><br>
                            <small>${escape(item.subtext ?? '')}</small>
                        </div>
                    `,
                    item: (item, escape) => `
                            <div>
                                <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                                ${escape(item.text)}
                            </div>
                        `,
                    no_results: function(data, escape) {
                        return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
                    }
                }
            });
        }

        function loadtDtQuery() {
            const url = '{{ route('tenant.consultas.vehiculos.getList') }}';

            dtQuery = new DataTable('#tbl_list', {
                serverSide: true,
                processing: true,
                pageLength: 50,
                ajax: {
                    url: url,
                    type: 'GET',
                    data: function(d) {
                        d.start_date = document.querySelector('#fecha_inicio').value;
                        d.end_date = document.querySelector('#fecha_fin').value;
                        d.customer_id = document.querySelector('#customer_id').value;
                        d.vehicle_id = document.querySelector('#vehicle_id').value;
                    },
                    beforeSend: function() {
                        mostrarAnimacion1();
                    },
                    complete: function() {
                        ocultarAnimacion1();
                    },
                    dataSrc: function(json) {
                        // document.querySelector('#utilidad_total').value = parseFloat(json.utilidad_total)
                        //     .toFixed(3);
                        return json.data;
                    }
                },
                "order": [
                    [1, 'desc']
                ],
                initComplete: function() {
                    const input = $('#dt-search-0');

                    if (!$('#search-help').length) {
                        input.after(`
                            <small id="search-help" class="text-black d-block mt-1">
                                Buscar:
                                <strong>Doc</strong>,
                                <strong>Cliente</strong>,
                                <strong>Placa</strong>,
                                <strong>Condición</strong>
                            </small>
                        `);
                    }
                },
                columns: [{
                        data: 'document_id',
                        name: 'document_id',
                        visible: false,
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'date',
                        name: 'date',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'document_number',
                        name: 'document_number',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'plate',
                        name: 'plate',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'payment_condition',
                        name: 'payment_condition',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        searchable: false,
                        orderable: true,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'paid',
                        name: 'paid',
                        searchable: false,
                        orderable: true,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                        searchable: false,
                        orderable: true,
                        className: "text-end",
                        render: function(data, type, row) {
                            return formatSoles(data);
                        }
                    },
                ],
                language: {
                    "lengthMenu": "Mostrar _MENU_ items por página",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ items",
                    "infoEmpty": "Mostrando 0 a 0 de 0 items",
                    "infoFiltered": "(filtrado de _MAX_ items totales)",
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

        function filterDataTable() {
            dtQuery.ajax.reload();
        }

        function downloadExcel() {

            const url = @json(route('tenant.consultas.vehiculos.getExcel'));

            const params = {
                start_date: document.querySelector('#fecha_inicio').value,
                end_date: document.querySelector('#fecha_fin').value,
                customer_id: document.querySelector('#customer_id').value,
                vehicle_id: document.querySelector('#vehicle_id').value,
            };

            const queryString = new URLSearchParams(params).toString();

            const finalUrl = `${url}?${queryString}`;
            window.location.href = finalUrl;

        }

        function downloadPdf() {

            const url = @json(route('tenant.consultas.vehiculos.getPdf'));

            const params = {
                start_date: document.querySelector('#fecha_inicio').value,
                end_date: document.querySelector('#fecha_fin').value,
                customer_id: document.querySelector('#customer_id').value,
                vehicle_id: document.querySelector('#vehicle_id').value,
            };

            const queryString = new URLSearchParams(params).toString();

            const finalUrl = `${url}?${queryString}`;
            window.open(finalUrl, '_blank');

        }

        function filtrar() {

            toastr.clear();
            const fecha_inicio = document.querySelector('#fecha_inicio').value;
            const fecha_fin = document.querySelector('#fecha_fin').value;

            if (fecha_inicio > fecha_fin && fecha_fin && fecha_inicio) {
                toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
                document.querySelector('#fecha_inicio').focus();
                return;
            }
            dtQuery.draw();

        }
    </script>
@endsection

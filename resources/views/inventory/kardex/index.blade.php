@extends('layouts.template')

@section('title')
    KARDEX
@endsection

@section('content')
    <div class="card">
        @csrf
        <div class="card-header d-flex justify-content-between flex-row">
            <h4 class="card-title">KARDEX</h4>

            <div class="input-group-append">

            </div>

        </div>
        <div class="card-body">
            <div class="row align-items-end mb-3">

                <!-- PRODUCTO -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="product_id" class="fw-bold text-primary">
                        <i class="fas fa-box-open me-1"></i> PRODUCTO
                    </label>
                    <select data-placeholder="Seleccionar" id="product_id" class="form-select">
                        <option value=""></option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- FECHA INICIO -->
                <div class="col-lg-2 col-md-4 col-sm-12">
                    <label for="date_start" class="fw-bold text-success">
                        <i class="fas fa-calendar-day me-1"></i> FECHA INICIO
                    </label>
                    <input type="date" class="form-control" id="date_start" onchange="changeDateStart(this.value)">
                </div>

                <!-- FECHA FIN -->
                <div class="col-lg-2 col-md-4 col-sm-12">
                    <label for="date_end" class="fw-bold text-danger">
                        <i class="fas fa-calendar-check me-1"></i> FECHA FIN
                    </label>
                    <input type="date" class="form-control" id="date_end" onchange="changeDateEnd(this.value)">
                </div>

                <!-- BOTÓN FILTRAR -->
                <div class="col-lg-4 col-md-4 col-sm-12 text-end">
                    <button type="button" class="btn btn-primary" onclick="filterDataTable()">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>

            </div>

            <div class="row">
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-success me-2" onclick="downloadExcel();" type="button">
                        <i class="fas fa-file-excel me-1"></i> EXCEL
                    </button>

                    <button class="btn btn-danger" onclick="downloadPdf();" type="button">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        @include('inventory.kardex.tables.tbl_list_kardex')
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


@section('js')
    <script>
        let dtKardex = null;

        document.addEventListener('DOMContentLoaded', () => {
            events();
        })

        function events() {
            startDataTableKardex();
            loadTomSelect();
        }

        function loadTomSelect() {
            window.productSelect = new TomSelect('#product_id', {
                create: false,
                plugins: ['clear_button'],
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }

        function startDataTableKardex() {
            const urlGetKardex = '{{ route('tenant.inventory.kardex.getKardex') }}';

            dtKardex = new DataTable('#tbl_list_kardex', {
                responsive: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetKardex,
                    type: 'GET',
                    data: function(d) {
                        d.warehouse_id = 1;
                        d.product_id = document.querySelector('#product_id').value;
                        d.start_date = document.querySelector('#date_start').value;
                        d.end_date = document.querySelector('#date_end').value;
                    }
                },
                order: [
                    [1, 'desc'],
                    [2, 'desc'],
                ],
                columns: [{
                        data: 'product_id',
                        name: 'product_id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'category_name',
                        name: 'category_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'brand_name',
                        name: 'brand_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'type',
                        name: 'type',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'document_serie',
                        name: 'document_serie',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'previous_stock',
                        name: 'previous_stock',
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'entrada',
                        name: 'entrada',
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'salida',
                        name: 'salida',
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'later_stock',
                        name: 'later_stock',
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'creator_user_name',
                        name: 'creator_user_name',
                        searchable: false,
                        orderable: false
                    },
                ],
                pageLength: 50,
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

        function filterDataTable() {
            dtKardex.ajax.reload();
        }

        function changeDateStart(date_start) {

            toastr.clear();
            const date_end = document.querySelector('#date_end').value;

            if (date_start > date_end && date_end) {
                document.querySelector('#date_start').value = '';
                toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
                return;
            }

        }

        function changeDateEnd(date_end) {

            toastr.clear();
            const date_start = document.querySelector('#date_start').value;

            if (date_end < date_start && date_start) {
                document.querySelector('#date_end').value = '';
                toastr.error('LA FECHA FINAL DEBE SER MAYOR IGUAL A LA FECHA INICIAL!!');
                return;
            }

        }

        function downloadExcel() {

            const url = @json(route('tenant.inventory.kardex.excel'));

            const params = {
                product_id: document.querySelector('#product_id').value,
                date_start: document.querySelector('#date_start').value,
                date_end: document.querySelector('#date_end').value
            };

            const queryString = new URLSearchParams(params).toString();

            const finalUrl = `${url}?${queryString}`;
            window.location.href = finalUrl;

        }

        function downloadPdf() {

            const url = @json(route('tenant.inventory.kardex.pdf'));

            const params = {
                product_id: document.querySelector('#product_id').value,
                date_start: document.querySelector('#date_start').value,
                date_end: document.querySelector('#date_end').value
            };

            const queryString = new URLSearchParams(params).toString();

            const finalUrl = `${url}?${queryString}`;
            window.open(finalUrl, '_blank');

        }
    </script>
@endsection

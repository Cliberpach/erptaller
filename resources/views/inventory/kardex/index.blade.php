@extends('layouts.template')

@section('title')
    KARDEX
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <!-- Elemento de superposición -->
    <div id="overlay" class="overlay"></div>


    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">KARDEX</h4>

            <div class="input-group-append">
                {{-- <button onclick="goToSaleCreate()"  type="button" data-bs-whatever="Nueva caja" class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2"> NUEVO</p>
                    </div>
                </button>  --}}
            </div>

        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="product_id" style="font-weight:bold;">PRODUCTO</label>
                    <select data-placeholder="Seleccionar" id="product_id" class="form-select select2_form"
                        aria-label="Default select example" onchange="filterDataTable()">
                        <option value=""></option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="date_start" style="font-weight:bold;">FECHA INICIO</label>
                    <input type="date" class="form-control" id="date_start" onchange="changeDateStart(this.value)">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="date_end" style="font-weight:bold;">FECHA FIN</label>
                    <input type="date" class="form-control" id="date_end" onchange="changeDateEnd(this.value)">
                </div>
            </div>
            <div class="row">
                <div class="col-12" style="display:flex;justify-content:end;">
                    <button class="btn btn-primary" style="margin-right: 10px;" onclick="downloadExcel();">EXCEL</button>
                    <button class="btn btn-primary" onclick="downloadPdf()">PDF</button>
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
            loadSelect2();
        }

        function loadSelect2() {
            $('.select2_form').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
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
                        d.product_id = document.querySelector('#product_id').value;
                        d.date_start = document.querySelector('#date_start').value;
                        d.date_end = document.querySelector('#date_end').value;
                    }
                },
                order: [
                    [1, 'desc'],
                    [2, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'product_id',
                        name: 'product_id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'brand_name',
                        name: 'brand_name'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'document',
                        name: 'document'
                    },
                    {
                        data: 'stock_previous',
                        name: 'stock_previous'
                    },
                    {
                        data: 'entrada',
                        name: 'entrada'
                    },
                    {
                        data: 'salida',
                        name: 'salida'
                    },
                    {
                        data: 'stock_later',
                        name: 'stock_later'
                    },
                    {
                        data: 'user_recorder_name',
                        name: 'user_recorder_name'
                    },
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

            filterDataTable();

        }

        function changeDateEnd(date_end) {

            toastr.clear();
            const date_start = document.querySelector('#date_start').value;

            if (date_end < date_start && date_start) {
                document.querySelector('#date_end').value = '';
                toastr.error('LA FECHA FINAL DEBE SER MAYOR IGUAL A LA FECHA INICIAL!!');
                return;
            }

            filterDataTable();

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
    <script src="{{ asset('assets/js/utils.js') }}"></script>
@endsection

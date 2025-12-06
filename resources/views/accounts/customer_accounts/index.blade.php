@extends('layouts.template')

@section('title')
    Cuentas Cliente
@endsection

@section('content')
    @include('accounts.customer_accounts.modalDetalle')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">LISTA DE CUENTAS CLIENTE</h4>

            <div class="d-flex flex-wrap gap-2">
                <div class="row">
                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="required">Cliente</label>
                            <select name="cliente_b" id="cliente_b" class="select2_form form-control">
                                <option value=""></option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="required">Estado</label>
                            <select name="estado_b" id="estado_b" class="select2_form form-control">
                                <option value=""></option>
                                <option selected value="PENDIENTE">PENDIENTES</option>
                                <option value="PAGADO">PAGADOS</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button class="btn btn-primary btn-block" id="btn_buscar" type="button"><i
                                    class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button class="btn btn-danger btn-block" id="btn_pdf" type="button"><i
                                    class="fa fa-file-pdf-o"></i> PDF</button>
                        </div>
                    </div> --}}
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
                        d.customer = $("#cliente_b").val();
                        d.status = $("#estado_b").val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'ca.id',
                        visible: false
                    },
                    {
                        data: 'customer_name',
                        name: 'sd.customer_name'
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
                        data: 'agreement',
                        orderable: false,
                        searchable: false,
                        name: 'ca.agreement'
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
    </script>
@endsection

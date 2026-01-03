@extends('layouts.template')
@section('title')
    LISTADO DE CLIENTES
@endsection


@section('content')
    <div class="card overflow-hidden">
        <div class="card-header">

            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE CLIENTES</h6>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 text-md-right mt-md-0 mt-2" style="text-align:end;">
                    <button onclick="goToCrearCliente()" type="button" data-bs-whatever="Nuevo Cliente"
                        class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <div class="lign-items-center d-flex align-items-center">
                            <i class="fas fa-plus pe-1"></i>
                            <p class="mb-0 ml-2"> NUEVO</p>
                        </div>
                    </button>
                </div>
            </div>

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

                <div class="col-lg-8 col-md-6 col-sm-12 col-xs-12 mb-2 text-end">
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
                        @include('sales.customers.tables.tbl_clientes_list')
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

<script>
    let dtClientes = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadDtCustomers();
        loadTomSelect();
    })

    function loadDtCustomers() {
        const urlGetClientes = '{{ route('tenant.ventas.cliente.getAll') }}';

        dtClientes = new DataTable('#tbl_clientes_list', {
            serverSide: true,
            processing: true,
            responsive: true,
            ajax: {
                url: urlGetClientes,
                type: 'GET',
                data: function(d) {
                    d.customer_id = $('#customer_id').val();
                }
            },
            order: [
                [0, 'desc']
            ],
            initComplete: function() {
                const searchContainer = $('.dt-search');
                const searchInput = $('#dt-search-0');

                searchInput.attr('placeholder', 'Buscar clientes...');

                if (!$('#dt-search-help').length) {
                    searchContainer.append(`
                        <div id="dt-search-help" class="text-muted mt-1" style="font-size: 0.85rem;">
                            Nombre, documento, email, teléfono o ubicación
                        </div>
                    `);
                }
            },
            columns: [{
                    data: 'id',
                    name: 'c.id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'name',
                    name: 'c.name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'type_document_abbreviation',
                    name: 'c.type_document_abbreviation',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'document_number',
                    name: 'c.document_number',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'phone',
                    name: 'c.phone',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'address',
                    name: 'c.address',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'email',
                    name: 'c.email',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'department_name',
                    name: 'c.department_name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'province_name',
                    name: 'c.province_name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'district_name',
                    name: 'c.district_name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'ubigeo',
                    name: 'c.ubigeo',
                    searchable: true,
                    orderable: true
                },

                // {
                //     data: 'control_credito',
                //     name: 'control_credito',
                //     render: function(data, type, row) {
                //         return `${data == 1? "SI":"NO"}`
                //     }
                // },
                // {
                //     data: 'limite_credito',
                //     name: 'limite_credito',
                //     className: 'text-end',
                //     render: function(data, type, row) {
                //         return parseFloat(data).toLocaleString('es-US', {
                //             minimumFractionDigits: 2,
                //             maximumFractionDigits: 2
                //         });
                //     }
                // },
                // {
                //     data: 'credito_usado',
                //     name: 'credito_usado',
                //     className: 'text-end',
                //     render: function(data, type, row) {
                //         return parseFloat(data).toLocaleString('es-US', {
                //             minimumFractionDigits: 2,
                //             maximumFractionDigits: 2
                //         });
                //     }
                // },
                // {
                //     data: 'saldo_credito',
                //     name: 'saldo_credito',
                //     className: 'text-end',
                //     render: function(data, type, row) {
                //         return parseFloat(data).toLocaleString('es-US', {
                //             minimumFractionDigits: 2,
                //             maximumFractionDigits: 2
                //         });
                //     }
                // },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function(data, type, row) {

                        const baseUrlEdit =
                        `{{ route('tenant.ventas.cliente.edit', ['id' => ':id']) }}`;
                        const urlEdit = baseUrlEdit.replace(':id', data.id);

                        return `
                            <div class="dropdown">
                                <button
                                    class="btn btn-primary btn-sm btn-shadow btn-icon dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    aria-expanded="false">
                                    <i class="fi fi-rr-menu-dots"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">



                                    <li>
                                        <a class="dropdown-item" href="${urlEdit}">
                                            <i class="fa-solid fa-pen-to-square me-2"></i> Editar
                                        </a>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>

                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)"
                                        onclick="eliminarCliente(${data.id})">
                                            <i class="fa-solid fa-trash me-2"></i> Eliminar
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ clientes por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ clientes",
                "infoEmpty": "Mostrando 0 a 0 de 0 clientes",
                "infoFiltered": "(filtrado de _MAX_ clientes totales)",
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

    function goToCrearCliente() {
        window.location.href = @json(route('tenant.ventas.cliente.create'));
    }


    function eliminarCliente(id) {
        toastr.clear();
        let row = getRowById(dtClientes, id);
        const message = `
                <div class="text-start">
                    <div class="mb-2">
                        <i class="fa-solid fa-user text-primary me-2"></i>
                        <strong>Cliente</strong>
                    </div>

                    <div class="ps-4">
                        <div class="mb-1">
                            <i class="fa-solid fa-id-card me-2 text-muted"></i>
                            ${row.type_document_abbreviation}: ${row.document_number}
                        </div>

                        <div class="mb-1">
                            <i class="fa-solid fa-signature me-2 text-muted"></i>
                            ${row.name}
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="text-danger small">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Esta acción no se puede deshacer
                    </div>
                </div>
            `;

        Swal.fire({
            title: 'Desea eliminar el cliente?',
            html: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Eliminando cliente...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let url = `{{ route('tenant.ventas.cliente.destroy', ['id' => ':id']) }}`;
                    url = url.replace(':id', id);

                    const res = await axios.delete(route('tenant.ventas.cliente.destroy', id));

                    if (res.data.success) {
                        dtClientes.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CLIENTE');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CLIENTE');
                } finally {
                    Swal.close();
                }

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                Swal.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }


    function filterData() {
        dtClientes.ajax.reload();
    }
</script>

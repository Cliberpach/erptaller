@extends('layouts.template')
@section('title')
    LISTA ALERTAS
@endsection

@section('content')
    @csrf
    <div class="card overflow-hidden">
        <div class="card-header">

            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA NOTIFICACIONES</h6>
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
                {{-- <div class="col-lg-12 d-flex align-items-end justify-content-end">
                    <button class="btn btn-success" style="margin-right: 10px;" onclick="downloadExcel();">
                        <i class="fas fa-file-excel"></i> EXCEL
                    </button>
                    <button class="btn btn-danger" onclick="downloadPdf()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div> --}}
            </div>

        </div>
        <div class="card-body p-0 pb-2">
            @include('consultas.alerts.tables.tbl_list')
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

        }

        function loadtDtQuery() {
            const url = '{{ route('tenant.consultas.notificaciones.getAll') }}';

            dtQuery = new DataTable('#tbl_list', {
                serverSide: true,
                processing: true,
                pageLength: 50,

                ajax: {
                    url: url,
                    type: 'GET',
                    data: function(d) {
                        d.start_date = document.querySelector('#fecha_inicio')?.value;
                        d.end_date = document.querySelector('#fecha_fin')?.value;
                        d.customer_id = document.querySelector('#customer_id')?.value;
                    },
                    beforeSend: function() {
                        mostrarAnimacion1();
                    },
                    complete: function() {
                        ocultarAnimacion1();
                    },
                    dataSrc: function(json) {
                        return json.data;
                    }
                },

                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'a.id',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'a.created_at',
                        searchable: false,
                        orderable: true,
                        render: function(data) {
                            return new Date(data).toLocaleDateString('es-PE');
                        }
                    },
                    {
                        data: 'name',
                        name: 'a.name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'description',
                        name: 'a.description',
                        searchable: true,
                        orderable: false,
                        render: data => data ?? '-'
                    },
                    {
                        data: 'type_object',
                        name: 'a.type_object',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'object_id',
                        name: 'a.object_id',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'creator_user_name',
                        name: 'a.creator_user_name',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'a.status',
                        searchable: true,
                        orderable: false,
                        render: function(data) {
                            return `
                            <span class="badge ${data === 'ACTIVO' ? 'bg-primary' : 'bg-danger'}">
                                ${data}
                            </span>`;
                        }
                    },
                    {
                        data: null,
                        name: 'options',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton${row.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${row.id}">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="verDetalle(${row.id})">
                                                <i class="fa-solid fa-eye me-2"></i> VER
                                            </a>
                                        </li>
                                        ${row.status === 'ACTIVO' ? `
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="finishAlert(${row.id})">
                                                    <i class="fa-solid fa-check me-2"></i> FINALIZAR
                                                </a>
                                            </li>` : ''}
                                    </ul>
                                </div>
                            `;
                        }
                    }
                ],

                initComplete: function() {
                    const input = $('#dt-search-0');

                    if (!$('#search-help').length) {
                        input.after(`
                        <small id="search-help" class="text-black d-block mt-1">
                            Buscar por:
                            <strong>Nombre</strong>,
                            <strong>Descripción</strong>,
                            <strong>Tipo</strong>,
                            <strong>Usuario</strong>
                        </small>
                    `);
                    }
                },

                language: {
                    lengthMenu: "Mostrar _MENU_ items por página",
                    zeroRecords: "No se encontraron resultados",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ items",
                    infoEmpty: "Mostrando 0 a 0 de 0 items",
                    infoFiltered: "(filtrado de _MAX_ items totales)",
                    search: "Buscar:",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    emptyTable: "No hay notificaciones disponibles"
                }
            });
        }

        function filterDataTable() {
            dtQuery.ajax.reload();
        }

        function finishAlert(id) {
            toastr.clear();
            let row = getRowById(dtQuery, id);
            let messageHtml = `
                <i class="fa-solid fa-bell me-2 text-warning"></i>
                <strong>${row.name}</strong>
            `;

            Swal.fire({
                title: 'DESEA FINALIZAR LA ALERTA?',
                html: `
                    ${messageHtml}
                    <p class="mt-2">Operación <strong>no reversible</strong>!</p>
                `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, finalizar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Finalizando alerta...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {

                        let url = `{{ route('notifications.finish', ['id' => ':id']) }}`;
                        url = url.replace(':id', id);
                        const token = document.querySelector('input[name="_token"]').value;

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'X-HTTP-Method-Override': 'PUT'
                            }
                        });

                        const res = await response.json();

                        if (res.success) {
                            dtQuery.ajax.reload();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOr');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN FINALIZAR ALERTA');
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


        // function downloadExcel() {

        //     const url = @json(route('tenant.consultas.vehiculos.getExcel'));

        //     const params = {
        //         start_date: document.querySelector('#fecha_inicio').value,
        //         end_date: document.querySelector('#fecha_fin').value,
        //         customer_id: document.querySelector('#customer_id').value,
        //         vehicle_id: document.querySelector('#vehicle_id').value,
        //     };

        //     const queryString = new URLSearchParams(params).toString();

        //     const finalUrl = `${url}?${queryString}`;
        //     window.location.href = finalUrl;

        // }

        // function downloadPdf() {

        //     const url = @json(route('tenant.consultas.vehiculos.getPdf'));

        //     const params = {
        //         start_date: document.querySelector('#fecha_inicio').value,
        //         end_date: document.querySelector('#fecha_fin').value,
        //         customer_id: document.querySelector('#customer_id').value,
        //         vehicle_id: document.querySelector('#vehicle_id').value,
        //     };

        //     const queryString = new URLSearchParams(params).toString();

        //     const finalUrl = `${url}?${queryString}`;
        //     window.open(finalUrl, '_blank');

        // }

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

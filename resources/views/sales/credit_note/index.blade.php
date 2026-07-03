@extends('layouts.template')

@section('title')
    Notas de Crédito
@endsection

@section('css')
@endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="card-header">

            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">NOTAS DE CRÉDITO</h6>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-primary mr-1"></i> Cliente:
                    </label>
                    <select class="form-control" id="customer_id" name="customer_id">
                        <option value="">Seleccionar</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-success mr-1"></i> Fecha inicio:
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-check text-danger mr-1"></i> Fecha fin:
                    </label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks text-info mr-1"></i> Sunat:
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todo</option>
                        <option selected value="PENDIENTE">Pendiente</option>
                        <option value="ACEPTADO">Aceptado</option>
                        <option value="RECHAZADO">Rechazado</option>
                        <option value="OBSERVADO">Observado</option>
                    </select>
                </div>

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
                        @include('sales.credit_note.tables.tbl_list')
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script>
        let dtNotes = null;
        const filterSaleId = @json($sale_id);

        document.addEventListener('DOMContentLoaded', () => {
            loadTomSelect();
            events();
        })

        function events() {
            startDataTableCreditNotes();
        }

        function loadTomSelect() {
            const customerEl = document.getElementById('customer_id');
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
                    option: (item, escape) => `<div><strong>${escape(item.full_name)}</strong></div>`,
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

        function startDataTableCreditNotes() {
            const urlGetAll = '{{ route('tenant.ventas.nota_credito.getAll') }}';

            dtNotes = new DataTable('#tbl_list_credit_notes', {
                responsive: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetAll,
                    type: 'GET',
                    data: function(d) {
                        d.customer_id = $('#customer_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                        d.sale_id = filterSaleId || '';
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'fecha_registro', name: 'fecha_registro' },
                    { data: 'creator_user_name', name: 'creator_user_name' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'doc', name: 'doc' },
                    {
                        data: null,
                        render: function(data) {
                            const afectado = data.tipDocAfectado === '01' ? 'FACTURA' :
                                (data.tipDocAfectado === '03' ? 'BOLETA' : data.tipDocAfectado);
                            return `${afectado} ${data.numDocfectado ?? ''}`;
                        },
                        name: 'doc_afectado',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `${data.codMotivo ?? ''} - ${data.desMotivo ?? ''}`;
                        },
                        name: 'motivo',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'total', name: 'total' },
                    {
                        data: null,
                        render: function(data) {
                            let badge_class = '';
                            if (data.sunat_status === 'PENDIENTE') badge_class = 'danger';
                            if (data.sunat_status === 'ACEPTADO') badge_class = 'primary';
                            if (data.sunat_status === 'RECHAZADO') badge_class = 'dark';
                            // OBSERVADO: sin regla de color, igual que en ventas.
                            return `<span class="badge bg-${badge_class}">${data.sunat_status}</span>`;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function(data) {
                            const urlDownloadXml =
                                "{{ route('tenant.ventas.nota_credito.downloadXml', ':id') }}".replace(':id', data.id);
                            const urlDownloadCdr =
                                "{{ route('tenant.ventas.nota_credito.downloadCdr', ':id') }}".replace(':id', data.id);

                            let descargas =
                                `<div style="display: flex; justify-content: flex-start; gap: 10px; flex-wrap: nowrap;">`;

                            if (data.ruta_xml) {
                                descargas += `<a class="btn btn-success btn-sm" style="color:white;" href="${urlDownloadXml}">
                                                <i class="fa-solid fa-file-excel"></i> XML
                                            </a>`;
                            }
                            if (data.ruta_cdr) {
                                descargas += `<a class="btn btn-primary btn-sm" style="color:white;" href="${urlDownloadCdr}">
                                                <i class="fa-solid fa-book"></i> CDR
                                            </a>`;
                            }

                            descargas += `</div>`;
                            return descargas;
                        },
                        name: 'descargas',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function(data) {
                            const urlPdf =
                                "{{ route('tenant.ventas.nota_credito.pdf', ':id') }}".replace(':id', data.id);

                            let acciones = `
                            <div class="dropdown float-end">
                                <button
                                    class="btn btn-white btn-sm btn-shadow btn-icon dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
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

                            if (['PENDIENTE', 'RECHAZADO', 'OBSERVADO'].includes(data.sunat_status)) {
                                acciones += `<li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="sendSunat(${data.id})">
                                                    <i class="fa-solid fa-paper-plane"></i> Sunat
                                                </a>
                                            </li>`;
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

            // Mismo fix que en ventas: dropdown no se corte con scrollbar lateral con pocas filas.
            dtNotes.on('draw.dt', function() {
                document.querySelectorAll('#tbl_list_credit_notes [data-bs-toggle="dropdown"]').forEach(function(el) {
                    const existing = bootstrap.Dropdown.getInstance(el);
                    if (existing) existing.dispose();
                    new bootstrap.Dropdown(el, {
                        popperConfig: {
                            strategy: 'fixed'
                        }
                    });
                });
            });
        }

        function sendSunat(credit_note_id) {
            const nc = getRowById(dtNotes, credit_note_id);

            Swal.fire({
                title: `Enviar la NC: ${nc.doc} a Sunat?`,
                text: "OPERACIÓN NO REVERSIBLE!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, enviar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: `Nota de Crédito: ${nc.doc}`,
                    html: "ENVIANDO A SUNAT...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    const url = @json(route('tenant.ventas.nota_credito.send_sunat'));

                    const formData = new FormData();
                    formData.append('credit_note_id', credit_note_id);

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token },
                        body: formData
                    });
                    const res = await response.json();
                    Swal.close();

                    if (res.success) {
                        dtNotes.ajax.reload(null, false);
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }
                } catch (error) {
                    Swal.close();
                    toastr.error(error, 'ERROR EN LA PETICIÓN ENVIAR A SUNAT');
                }
            });
        }

        function filterData() {
            const startDate = document.getElementById('start_date')?.value;
            const endDate = document.getElementById('end_date')?.value;

            if (startDate && endDate && startDate > endDate) {
                toastr.error('La fecha inicio no puede ser mayor que la fecha fin', 'Fechas inválidas');
                return;
            }

            dtNotes.ajax.reload();
        }
    </script>
@endsection

@extends('layouts.template')

@section('title')
    Ventas
@endsection

@section('css')
@endsection

@section('content')
    {{-- Modal Convertir documento interno -> Boleta/Factura (solo formaliza). --}}
    <div class="modal fade" id="mdlConvertir" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Convertir <span id="conv_doc"></span> a comprobante fiscal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="conv_ticket_id">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label class="form-label fw-bold required_field">Comprobante</label>
                            <select class="form-control" id="conv_type_sale"></select>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                            <label class="form-label fw-bold required_field">Cliente</label>
                            <select class="form-control" id="conv_customer"></select>
                            <small id="conv_ruc_hint" class="text-danger" style="display:none;">La factura exige cliente con RUC.</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Producto</th><th>Marca</th><th class="text-end">Cant.</th><th class="text-end">P.Venta</th><th class="text-end">Importe</th></tr>
                            </thead>
                            <tbody id="conv_productos"></tbody>
                            <tfoot>
                                <tr><th colspan="4" class="text-end">TOTAL S/</th><th class="text-end" id="conv_total"></th></tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="text-muted small mb-0">No descuenta stock ni vuelve a cobrar: solo formaliza fiscalmente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmConvertir">
                        <i class="fa-solid fa-right-left"></i> Convertir
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('sales.sale_document.modals.mdl_credit_note')

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
                        <option selected value="PENDIENTE">Pendiente</option>
                        <option value="ACEPTADO">Aceptado</option>
                        <option value="RECHAZADO">Rechazado</option>
                        <option value="OBSERVADO">Observado</option>
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
        // Gate del ítem Anular (además del candado en la ruta can:ventas.anular).
        const puedeAnular = @json($puedeAnular);

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

                            // Anulada (estado real en sd.status) -> badge rojo ANULADO,
                            // sin importar el sunat_status.
                            if (data.status === 'ANULADO') {
                                return `<span class="badge bg-danger">ANULADO</span>`;
                            }
                            // Documento interno convertido a fiscal -> trazabilidad.
                            if (data.converted_to_id) {
                                return `<span class="badge bg-info text-dark">Convertido &rarr; ${data.converted_to_doc ?? ''}</span>`;
                            }

                            let badge_class = '';

                            if (data.sunat_status === 'PENDIENTE') {
                                badge_class = 'danger';
                            }
                            if (data.sunat_status === 'ACEPTADO') {
                                badge_class = 'primary';
                            }
                            if (data.sunat_status === 'RECHAZADO') {
                                badge_class = 'dark';
                            }
                            // OBSERVADO: sin regla de color (igual que la referencia) -> badge sin clase.

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

                            const urlDownloadXml =
                                "{{ route('tenant.ventas.comprobante_venta.downloadXml', ':id') }}".replace(
                                    ':id', data.id);
                            const urlDownloadCdr =
                                "{{ route('tenant.ventas.comprobante_venta.downloadCdr', ':id') }}".replace(
                                    ':id', data.id);

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
                        render: function(data, type, row) {

                            const urlPdf =
                                "{{ route('tenant.ventas.comprobante_venta.pdf_voucher', ':id') }}".replace(
                                    ':id', data.id);

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

                            // ===== Acciones por tipo de documento (SOLO UI, sin lógica aún) =====
                            // Documento INTERNO (NV legacy + TICKET '50') -> Convertir + Anular.
                            // Boleta(03)/Factura(01) -> Generar Guía + Anular (fiscal, no Convertir).
                            // Los onclick llaman a accionProximamente() (toast). NO hay rutas/backend todavía.
                            // Documento interno YA convertido -> sin Convertir ni Anular (solo trazabilidad).
                            if (['NV', '50'].includes(data.type_sale_code) && !data.converted_to_id) {
                                // Convertir: abre el modal (crea boleta/factura, no mueve stock/caja).
                                acciones += `<li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="openConvertir(${data.id})">
                                                    <i class="fa-solid fa-right-left text-info"></i> Convertir
                                                </a>
                                            </li>`;
                                // Anular: REAL. Solo si tiene permiso + está ACTIVO + es CONTADO
                                // (crédito interno -> próximamente; el server también lo valida).
                                if (puedeAnular && data.status === 'ACTIVO' && data.payment_condition_name === 'CONTADO') {
                                    acciones += `<li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="anularVenta(${data.id})">
                                                        <i class="fa-solid fa-ban"></i> Anular
                                                    </a>
                                                </li>`;
                                }
                            }

                            if (data.type_sale_code === '03' || data.type_sale_code === '01') {
                                acciones += `<li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="accionProximamente('Generar Guía')">
                                                    <i class="fa-solid fa-truck text-secondary"></i> Generar Guía
                                                </a>
                                            </li>`;
                                acciones += `<li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="openCreditNote(${data.id})">
                                                    <i class="fa-solid fa-file-invoice"></i> Nota de Crédito
                                                </a>
                                            </li>`;
                                const urlVerNc =
                                    "{{ route('tenant.ventas.nota_credito.index') }}?sale_id=" + data.id;
                                acciones += `<li>
                                                <a class="dropdown-item" target="_blank" href="${urlVerNc}">
                                                    <i class="fa-solid fa-file-invoice text-info"></i> Ver Notas de Crédito
                                                </a>
                                            </li>`;
                            }

                            if (data.type_sale_code === '03' || data.type_sale_code === '01') {

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

            // Fix: dropdown de acciones no se corte con scrollbar lateral cuando hay pocas filas
            // (card overflow-hidden + responsive:true de DataTables atrapan el Popper por defecto).
            dtSales.on('draw.dt', function() {
                document.querySelectorAll('#tbl_list_sales [data-bs-toggle="dropdown"]').forEach(function(el) {
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

        function goToSaleCreate() {
            const route = @json(route('tenant.ventas.comprobante_venta.create'));
            window.location.href = route;
        }

        // Placeholder: las acciones Convertir/Generar Guía aún no tienen lógica.
        // Solo avisan "Próximamente" (no navegan, no llaman backend). Cada una se
        // implementará por separado en su propio paso.
        function accionProximamente(nombre) {
            toastr.info(nombre + ': próximamente', 'EN CONSTRUCCIÓN');
        }

        // Anular documento interno (Ticket / NV) CONTADO: confirma + motivo opcional ->
        // POST -> revierte stock + marca ANULADO -> recarga la tabla.
        function anularVenta(sale_document_id) {
            const sale = getRowById(dtSales, sale_document_id);

            Swal.fire({
                title: '¿Anular la venta?',
                html: `Documento: <b>${sale.serie}-${sale.correlative}</b><br>` +
                      `<span class="text-muted">Esto devuelve el stock al almacén. No se puede deshacer.</span>`,
                icon: 'warning',
                input: 'text',
                inputLabel: 'Motivo (opcional)',
                inputPlaceholder: 'Ej. error de digitación',
                showCancelButton: true,
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'No',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Anulando...',
                    html: `Anulando ${sale.serie}-${sale.correlative}...`,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    let url = @json(route('tenant.ventas.comprobante_venta.anular', ['id' => ':id']));
                    url = url.replace(':id', sale_document_id);

                    const formData = new FormData();
                    formData.append('reason', result.value || '');

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token },
                        body: formData
                    });
                    const res = await response.json();
                    Swal.close();

                    if (res.success) {
                        dtSales.ajax.reload(null, false);
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'NO SE PUDO ANULAR');
                    }
                } catch (error) {
                    Swal.close();
                    toastr.error(error, 'ERROR EN LA PETICIÓN ANULAR');
                }
            });
        }

        // ===== CONVERTIR documento interno -> Boleta/Factura =====
        let convCustomerSelect = null;

        async function openConvertir(id) {
            const url = @json(route('tenant.ventas.comprobante_venta.convertData', ['id' => ':id'])).replace(':id', id);
            try {
                const res = await (await fetch(url)).json();
                if (!res.success) { toastr.error(res.message || 'No se pudo cargar'); return; }

                document.getElementById('conv_ticket_id').value = id;
                document.getElementById('conv_doc').textContent = res.doc;
                document.getElementById('conv_total').textContent = res.total;

                // Tipos fiscales (Boleta/Factura)
                const sel = document.getElementById('conv_type_sale');
                sel.innerHTML = res.tipos.map(t => `<option value="${t.id}" data-param="${t.parameter}">${t.name}</option>`).join('');
                sel.onchange = toggleRucHint;

                // Productos del ticket (solo lectura)
                document.getElementById('conv_productos').innerHTML = res.productos.map(p => `
                    <tr><td>${p.product_name}</td><td>${p.brand_name ?? ''}</td>
                        <td class="text-end">${(+p.quantity)}</td>
                        <td class="text-end">${(+p.price_sale).toFixed(2)}</td>
                        <td class="text-end">${(+p.amount).toFixed(2)}</td></tr>`).join('');

                // Cliente: TomSelect con búsqueda (reusa searchCustomer), sembrado con el del ticket.
                if (convCustomerSelect) { convCustomerSelect.destroy(); }
                const el = document.getElementById('conv_customer');
                el.innerHTML = `<option value="${res.customer.id}">${res.customer.full_name}</option>`;
                convCustomerSelect = new TomSelect(el, {
                    valueField: 'id', labelField: 'full_name', searchField: ['full_name'],
                    options: [res.customer], items: [String(res.customer.id)],
                    create: false, preload: false, maxOptions: 20,
                    load: async (q, cb) => {
                        if (q.length < 3) return cb();
                        try {
                            const u = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(q)}`;
                            const r = await (await fetch(u)).json();
                            cb(r.data ?? []);
                        } catch (e) { cb(); }
                    },
                    render: { option: (i, e) => `<div>${e(i.full_name)}</div>`, item: (i, e) => `<div>${e(i.full_name)}</div>` }
                });

                toggleRucHint();
                $('#mdlConvertir').modal('show');
            } catch (e) {
                toastr.error('Error cargando datos de conversión');
            }
        }

        function toggleRucHint() {
            const opt = document.getElementById('conv_type_sale').selectedOptions[0];
            const esFactura = opt && opt.dataset.param === 'F';
            document.getElementById('conv_ruc_hint').style.display = esFactura ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('btnConfirmConvertir').addEventListener('click', confirmarConvertir);
        });

        async function confirmarConvertir() {
            const id = document.getElementById('conv_ticket_id').value;
            const type_sale = document.getElementById('conv_type_sale').value;
            const customer_id = convCustomerSelect ? convCustomerSelect.getValue() : '';
            if (!customer_id) { toastr.error('Seleccione un cliente'); return; }

            Swal.fire({ title: 'Convirtiendo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const url = @json(route('tenant.ventas.comprobante_venta.convertir', ['id' => ':id'])).replace(':id', id);
                const fd = new FormData();
                fd.append('type_sale', type_sale);
                fd.append('customer_id', customer_id);
                const res = await (await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: fd })).json();
                Swal.close();
                if (res.success) {
                    $('#mdlConvertir').modal('hide');
                    dtSales.ajax.reload(null, false);
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                } else {
                    toastr.error(res.message, 'NO SE PUDO CONVERTIR');
                }
            } catch (e) {
                Swal.close();
                toastr.error('Error en la petición convertir');
            }
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
                        const token = document.querySelector('meta[name="csrf-token"]').content;

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

        // ===== NOTA DE CRÉDITO (Capa 1: documento + PDF) =====
        let ncLinesData = [];

        async function openCreditNote(id) {
            try {
                const url = @json(route('tenant.ventas.nota_credito.data', ['id' => ':id'])).replace(':id', id);
                const res = await (await fetch(url)).json();
                if (!res.success) { toastr.error(res.message || 'No se pudo cargar la venta'); return; }

                document.getElementById('nc_sale_id').value = res.sale.id;
                document.getElementById('nc_doc').textContent = res.sale.doc;
                ncLinesData = res.lines;

                // Capa 3: gate de caja-abierta. Solo bloquea si la NC atribuye caja
                // (directa+CONTADO) y no hay caja abierta. Convertida/OT/crédito emiten igual.
                const faltaCaja = res.requiere_caja && !res.caja_abierta;
                document.getElementById('nc_aviso_caja').classList.toggle('d-none', !faltaCaja);
                document.getElementById('nc_btn_emit').disabled = faltaCaja;

                document.getElementById('nc_lines').innerHTML = res.lines.map((l, i) => {
                    const sinDisponible = (+l.disponible) <= 0;
                    return `
                    <tr class="${sinDisponible ? 'text-muted' : ''}">
                        <td><input type="checkbox" class="nc-chk" data-i="${i}" ${sinDisponible ? 'disabled' : ''}></td>
                        <td>${l.product_name}</td>
                        <td>${l.brand_name ?? ''}</td>
                        <td class="text-end">
                            ${(+l.quantity).toFixed(2)} / ${(+l.disponible).toFixed(2)}
                            ${sinDisponible ? '<span class="badge bg-label-secondary ms-1">Ya acreditado</span>' : ''}
                        </td>
                        <td class="text-end">
                            <input type="number" class="form-control form-control-sm nc-qty text-end" data-i="${i}"
                                   min="0" max="${+l.disponible}" step="0.01" value="0" disabled>
                        </td>
                        <td class="text-end nc-imp" data-i="${i}">0.00</td>
                    </tr>`;
                }).join('');

                recalcNc();
                $('#mdlCreditNote').modal('show');
            } catch (e) {
                toastr.error('Error cargando datos de la Nota de Crédito');
            }
        }

        function recalcNc() {
            let total = 0;
            document.querySelectorAll('#nc_lines tr').forEach(tr => {
                const chk = tr.querySelector('.nc-chk');
                const qtyEl = tr.querySelector('.nc-qty');
                const i = chk.dataset.i;
                const price = +ncLinesData[i].price_sale;
                qtyEl.disabled = !chk.checked;
                if (!chk.checked) qtyEl.value = 0;
                const imp = (+qtyEl.value || 0) * price;
                tr.querySelector('.nc-imp').textContent = imp.toFixed(2);
                total += imp;
            });
            document.getElementById('nc_total').textContent = total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const body = document.getElementById('nc_lines');
            if (body) {
                body.addEventListener('input', recalcNc);
                body.addEventListener('change', recalcNc);
            }
            document.getElementById('nc_btn_all')?.addEventListener('click', () => {
                document.querySelectorAll('#nc_lines tr').forEach(tr => {
                    const chk = tr.querySelector('.nc-chk');
                    if (chk.disabled) return;
                    const qty = tr.querySelector('.nc-qty');
                    chk.checked = true;
                    qty.disabled = false;
                    qty.value = ncLinesData[chk.dataset.i].disponible;
                });
                recalcNc();
            });
            document.getElementById('nc_btn_emit')?.addEventListener('click', emitCreditNote);
        });

        async function emitCreditNote() {
            const sale_id = document.getElementById('nc_sale_id').value;
            const motivoEl = document.getElementById('nc_cod_motivo');
            const codMotivo = motivoEl.value;
            const desMotivo = motivoEl.selectedOptions[0].dataset.text;
            const observation = document.getElementById('nc_observation').value;

            const lines = [];
            document.querySelectorAll('#nc_lines tr').forEach(tr => {
                const chk = tr.querySelector('.nc-chk');
                if (!chk.checked) return;
                const i = chk.dataset.i;
                const qty = +tr.querySelector('.nc-qty').value;
                if (qty > 0) lines.push({
                    product_id: ncLinesData[i].product_id,
                    warehouse_id: ncLinesData[i].warehouse_id,
                    quantity: qty
                });
            });
            if (!lines.length) { toastr.error('Seleccione al menos una línea con cantidad a acreditar'); return; }

            Swal.fire({ title: 'Emitiendo Nota de Crédito...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const url = @json(route('tenant.ventas.nota_credito.store'));
                const fd = new FormData();
                fd.append('sale_id', sale_id);
                fd.append('codMotivo', codMotivo);
                fd.append('desMotivo', desMotivo);
                fd.append('observation', observation);
                fd.append('lines', JSON.stringify(lines));

                const res = await (await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: fd })).json();
                Swal.close();

                if (res.success) {
                    $('#mdlCreditNote').modal('hide');
                    dtSales.ajax.reload(null, false);
                    toastr.success(res.message + ' ' + res.doc, 'OPERACIÓN COMPLETADA');
                    const pdfUrl = @json(route('tenant.ventas.nota_credito.pdf', ['id' => ':id'])).replace(':id', res.id);
                    window.open(pdfUrl, '_blank');
                } else {
                    toastr.error(res.message, 'NO SE PUDO EMITIR LA NC');
                }
            } catch (e) {
                Swal.close();
                toastr.error('Error en la petición de Nota de Crédito');
            }
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

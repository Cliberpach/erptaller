<!-- Modal para visualizar orden de trabajo -->
<div class="modal fade" id="mdl_show_quote" tabindex="-1" aria-labelledby="mdl_show_quote_label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_show_quote_label">Cotización #<span
                            id="show_order_id"></span></h5>
                    <small class="text-muted">Detalles completos de la cotización</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Sección Maestro -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-info-circle me-1"></i> Información General</h6>
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Almacén:</strong> <span
                                id="show_warehouse_name"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Cliente:</strong> <span
                                id="show_customer_name"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Tipo Doc.:</strong> <span
                                id="show_customer_type_document"></span>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Número Doc.:</strong> <span
                                id="show_customer_document_number"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Placa:</strong> <span
                                id="show_vehicle_plate"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Total:</strong> S/ <span
                                id="show_total"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Subtotal:</strong> S/ <span
                                id="show_subtotal"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>IGV:</strong> S/ <span
                                id="show_igv"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Estado:</strong> <span
                                id="show_status" class="badge bg-success"></span></div>
                    </div>
                </div>

                <hr>

                <!-- Sección Productos -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-boxes me-1"></i> Productos</h6>
                    <div class="table-responsive">
                        <table class="table-sm table-bordered table">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Unidad</th>
                                    <th>Categoría</th>
                                    <th>Marca</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody id="show_products_body">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Sección Servicios -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-concierge-bell me-1"></i> Servicios</h6>
                    <div class="table-responsive">
                        <table class="table-sm table-bordered table">
                            <thead class="table-light">
                                <tr>
                                    <th>Servicio</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody id="show_services_body">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-end flex-wrap">
                <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

<script>
    const paramsMdlShowQuote = {
        id: null
    };

    async function openMdlShowQuote(id) {
        paramsMdlShowQuote.id = id;
        await getQuote(id);
        const modal = new bootstrap.Modal(document.getElementById('mdl_show_quote'));
        modal.show();
    }

    async function getQuote(id) {
        try {
            mostrarAnimacion1();
            const res = await axios.get(route('tenant.taller.cotizaciones.getQuote', id));
            if (res.data.success) {
                paintQuoteMaster(res.data.data.quote);
                paintOrderProducts(res.data.data.products);
                paintOrderServices(res.data.data.services);
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER ORDEN');
        } finally {
            ocultarAnimacion1();
        }
    }

    function paintQuoteMaster(order) {
        document.getElementById('show_order_id').innerText = order.id;

        document.getElementById('show_warehouse_name').innerText = order.warehouse_name;
        document.getElementById('show_customer_name').innerText = order.customer_name;
        document.getElementById('show_customer_type_document').innerText = order.customer_type_document_abbreviation;
        document.getElementById('show_customer_document_number').innerText = order.customer_document_number;
        document.getElementById('show_vehicle_plate').innerText = order.plate ?? '-';
        document.getElementById('show_total').innerText = formatSoles(order.total);
        document.getElementById('show_subtotal').innerText = formatSoles(order.subtotal);
        document.getElementById('show_igv').innerText = formatSoles(order.igv);

        const statusEl = document.getElementById('show_status');
        statusEl.innerText = order.status;
        statusEl.classList.remove('bg-success', 'bg-danger');
        statusEl.classList.add(order.status === 'ACTIVO' ? 'bg-success' : 'bg-danger');
    }

    function paintOrderProducts(products) {
        const tbody = document.getElementById('show_products_body');
        tbody.innerHTML = '';

        products.forEach(product => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${product.product_name}</td>
                <td>${product.product_unit}</td>
                <td>${product.category_name}</td>
                <td>${product.brand_name}</td>
                <td>${parseFloat(product.quantity).toFixed(2)}</td>
                <td>S/ ${parseFloat(product.price_sale).toFixed(2)}</td>
                <td>S/ ${parseFloat(product.amount).toFixed(2)}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function paintOrderServices(services) {
        const tbody = document.getElementById('show_services_body');
        tbody.innerHTML = '';

        services.forEach(service => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td>${service.service_name}</td>
            <td>${parseFloat(service.quantity).toFixed(2)}</td>
            <td>${formatSoles(service.price_sale)}</td>
            <td>${formatSoles(service.amount)}</td>
        `;

            tbody.appendChild(tr);
        });
    }

</script>

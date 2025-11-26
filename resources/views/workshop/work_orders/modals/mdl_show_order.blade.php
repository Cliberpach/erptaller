<!-- Modal para visualizar orden de trabajo -->
<div class="modal fade" id="mdl_show_order" tabindex="-1" aria-labelledby="mdl_show_order_label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_show_order_label">Orden de Trabajo #<span
                            id="show_order_id"></span></h5>
                    <small class="text-muted">Detalles completos de la orden</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Sección Maestro -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-info-circle me-1"></i> Información General</h6>
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Almacén:</strong> <span id="show_warehouse_name"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Cliente:</strong> <span id="show_customer_name"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Tipo Doc.:</strong> <span id="show_customer_type_document"></span>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Número Doc.:</strong> <span
                                id="show_customer_document_number"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Placa:</strong> <span id="show_vehicle_plate"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Total:</strong> S/ <span id="show_total"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Subtotal:</strong> S/ <span id="show_subtotal"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>IGV:</strong> S/ <span id="show_igv"></span></div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Estado:</strong> <span id="show_status"
                                class="badge bg-success"></span></div>
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
                                    <th>Estado</th>
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
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="show_services_body">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Sección Técnicos -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-user-cog me-1"></i> Técnicos</h6>
                    <div class="table-responsive">
                        <table class="table-sm table-bordered table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Técnico</th>
                                    <th>Nombre</th>
                                </tr>
                            </thead>
                            <tbody id="show_technicians_body">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Sección Inventario -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-clipboard-list me-1"></i> Inventario</h6>
                    <div class="table-responsive">
                        <table class="table-sm table-bordered table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                </tr>
                            </thead>
                            <tbody id="show_inventory_body">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sección Imágenes -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fa fa-image me-1"></i> Imágenes</h6>
                    <div class="row" id="show_images_body">
                        <!-- Se llenará dinámicamente -->
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
    const paramsMdlShowOrder = {
        id: null
    };

    async function openMdlShowOrder(id) {
        const modal = new bootstrap.Modal(document.getElementById('mdl_show_order'));
        modal.show();
        paramsMdlShowOrder.id = id;

        await getWorkOrder(id);
    }

    async function getWorkOrder(id) {
        try {
            mostrarAnimacion1();
            const res = await axios.get(route('tenant.taller.ordenes_trabajo.getWorkOrder', id));
            if (res.data.success) {
                paintOrderMaster(res.data.data.order);
                paintOrderProducts(res.data.data.products);
                paintOrderServices(res.data.data.services);
                paintOrderTechnicians(res.data.data.technicians);
                paintOrderInventory(res.data.data.inventory);
                paintOrderImages(res.data.data.images);
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER ORDEN');
        } finally {
            ocultarAnimacion1();
        }
    }

    function paintOrderMaster(order) {
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
            <td>
                <span class="badge ${product.status === 'ACTIVO' ? 'bg-success' : 'bg-danger'}">
                    ${product.status}
                </span>
            </td>
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
            <td>
                <span class="badge ${service.status === 'ACTIVO' ? 'bg-success' : 'bg-danger'}">
                    ${service.status}
                </span>
            </td>
        `;

            tbody.appendChild(tr);
        });
    }


    function paintOrderTechnicians(technicians) {
        const tbody = document.getElementById('show_technicians_body');
        tbody.innerHTML = ''; // Limpiar tabla

        technicians.forEach(tech => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${tech.technical_id}</td>
            <td>${tech.technical_name}</td>
        `;
            tbody.appendChild(tr);
        });
    }

    function paintOrderInventory(inventory) {
        const tbody = document.getElementById('show_inventory_body');
        tbody.innerHTML = ''; // Limpiar tabla

        inventory.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.inventory_name}</td>
        `;
            tbody.appendChild(tr);
        });
    }

    function paintOrderImages(images) {
        const container = document.getElementById('show_images_body');
        container.innerHTML = '';

        images.forEach(img => {
            const col = document.createElement('div');
            col.classList.add('col-6', 'col-md-3', 'mb-3');

            const imgUrl = "{{ asset('') }}" + img.img_route;

            col.innerHTML = `
                <div class="card h-100 shadow-sm">
                    <img src="${imgUrl}" class="card-img-top" alt="${img.img_name}" style="object-fit: cover; height:150px;">
                    <div class="card-body p-2 text-center">
                        <p class="mb-1 small text-truncate" title="${img.img_name}">${img.img_name}</p>
                        <span class="badge ${img.status === 'ACTIVO' ? 'bg-success' : 'bg-danger'}">${img.status}</span>
                    </div>
                </div>
            `;

            container.appendChild(col);
        });
    }

    function registrarModelo(formCreateColor) {

        const modeloNombre = document.querySelector('#description').value;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea registrar el año?",
            html: `
            <div style="text-align: center; margin-top: 10px;">
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>Nombre:</strong> ${modeloNombre}
                </p>
            </div>
        `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Registrando año...",
                    text: "Por favor, espere",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    toastr.clear();

                    const formData = new FormData(formCreateColor);
                    const res = await axios.post(route('tenant.taller.years.store'), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                        $('#mdl_show_order').modal('hide');
                        dtYears.ajax.reload();
                    } else {
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            Swal.close();
                            toastr.error('Errores de validación encontrados.', 'ERROR DE VALIDACIÓN');
                        } else {
                            Swal.close();
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        Swal.close();
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        Swal.close();
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                } finally {
                    Swal.close();
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>

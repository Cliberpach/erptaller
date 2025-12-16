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
                @include('workshop.work_orders.lists.lst_show_order')
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

    .card.hvr-float-shadow {
        display: flex;
    }
</style>

<script>
    const paramsMdlShowOrder = {
        id: null,
        galleryInstance: null
    };

    async function openMdlShowOrder(id) {
        paramsMdlShowOrder.id = id;
        await getWorkOrder(id);
        const modal = new bootstrap.Modal(document.getElementById('mdl_show_order'));
        modal.show();
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
            console.error("Mensaje:", error.message);
            console.error("Archivo:", error.fileName);
            console.error("Línea:", error.lineNumber);
            console.error("Stack:", error.stack);
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
        statusEl.classList.remove('bg-primary', 'bg-danger');
        statusEl.classList.add(order.status === 'ACTIVO' ? 'bg-primary' : 'bg-danger');

        const spanValidationStock = document.querySelector('#show_validation_stock');
        spanValidationStock.textContent = order.validation_stock == '1' ? 'VALIDADO' : 'NO VALIDADO';
        spanValidationStock.classList.remove('bg-primary', 'bg-danger');
        spanValidationStock.classList.add(order.validation_stock === '1' ? 'bg-primary' : 'bg-danger');

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
        tbody.innerHTML = '';

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

        if (paramsMdlShowOrder.galleryInstance) {
            paramsMdlShowOrder.galleryInstance.destroy();
            paramsMdlShowOrder.galleryInstance = null;
        }

        container.innerHTML = '';

        images.forEach(img => {
            const col = document.createElement('div');
            col.classList.add('col-lg-4', 'col-md-6', 'col-sm-12', 'col-xs-12', 'mb-3');

            const imgUrl = "{{ asset('') }}" + img.img_route;

            col.innerHTML = `
                <a href="${imgUrl}" class="lg-item">
                    <div class="card h-100 shadow-sm hvr-float-shadow">
                        <img src="${imgUrl}" class="card-img-top" alt="${img.img_name}" style="object-fit: cover; height:150px;">
                        <div class="card-body p-2 text-center">
                            <p class="mb-1 small text-truncate" title="${img.img_name}">
                                ${img.img_name}
                            </p>
                        </div>
                    </div>
                </a>
            `;

            container.appendChild(col);
        });

        if (images.length > 0) {
            paramsMdlShowOrder.galleryInstance = lightGallery(container, {
                selector: '.lg-item',
                plugins: [lgThumbnail, lgZoom],
                appendSubHtmlTo: '.lg-item',
                mobileSettings: {
                    controls: true,
                    showCloseIcon: true,
                }
            });
        }

    }
</script>

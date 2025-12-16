@extends('layouts.template')

@section('title')
    Comprobante de Venta
@endsection

@section('content')
    @include('utils.modals.customer.mdl_create_customer')
    @include('utils.modals.vehicles.mdl_create_vehicle')
    @include('utils.modals.products.mdl_create_product')
    @include('utils.modals.services.mdl_create_service')
    @include('sales.sale_document.modals.mdl_edit_product')
    @include('sales.sale_document.modals.mdl_edit_service')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">DOCUMENTO DE VENTA</h4>

            <div class="d-flex flex-wrap gap-2">

            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('sales.sale_document.forms.form_create_sale_ot')
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 d-flex justify-content-end">

                    <!-- BOTÓN VOLVER -->
                    <button type="button" class="btn btn-danger me-1"
                        onclick="redirect('tenant.taller.ordenes_trabajo.index')">
                        <i class="fas fa-arrow-left"></i> VOLVER
                    </button>

                    <!-- BOTÓN REGISTRAR -->
                    <button class="btn btn-primary" form="form-create-quote" type="submit">
                        <i class="fas fa-save"></i> REGISTRAR
                    </button>

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
    <script src="https://unpkg.com/justgage@latest/dist/justgage.umd.js"></script>

    <script>
        const lstProducts = @json($lst_products);
        const lstServices = @json($lst_services);
        let dtProducts = null;
        let dtServices = null;
        const amounts = {
            subTotal: 0,
            tax: 0,
            totalPay: 0
        }
        let lastCustomerQuery = null;
        let lastProductQuery = null;
        let lastServiceQuery = null;
        let lastVehicleQuery = null;

        document.addEventListener('DOMContentLoaded', () => {
            dtProducts = loadDataTableSimple('dt-orders-products');
            dtServices = loadDataTableSimple('dt-orders-services');

            loadTomSelect();
            loadFilePound();
            events();
            setQuote();
            loadPreviewData();

        })

        function events() {
            eventsMdlCreateCustomer();
            eventsMdlEditProduct();
            eventsMdlEditService();
            eventsMdlVehicle();
            eventsMdlProduct();
            eventsMdlService();

            document.querySelector('#form-create-quote').addEventListener('submit', (e) => {
                e.preventDefault();
                storeQuote(e.target);
            })

            window.clientSelect.on('change', function(value) {
                actionChangeClient(value);
            });

            window.invoiceTypeSelect.on('change', function(value) {
                actionChangeInvoiceType(value);
            });

            document.addEventListener('click', (e) => {
                const btnAddProduct = e.target.closest('.btn-add-product');
                if (btnAddProduct) {
                    actionAddProduct();
                }

                const btnDeleteProduct = e.target.closest('.btn-delete-product');
                if (btnDeleteProduct) {
                    actionDeleteProduct(btnDeleteProduct, lstProducts);
                }

                const btnAddService = e.target.closest('.btn-add-service');
                if (btnAddService) {
                    actionAddService();
                }

                const btnDeleteService = e.target.closest('.btn-delete-service');
                if (btnDeleteService) {
                    actionDeleteService(btnDeleteService, lstServices);
                }
            });

        }

        function loadTomSelect() {

            window.warehouseSelect = new TomSelect('#warehouse_id', {
                create: false,
                plugins: ['clear_button'],
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            const initialCustomer = @json($customer_formatted);
            window.clientSelect = new TomSelect('#client_id', {
                valueField: 'id',
                labelField: 'full_name',
                options: [initialCustomer],
                items: [initialCustomer.id],
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

            const invoiceTypeSelect = document.getElementById('invoice_type');
            if (invoiceTypeSelect && !invoiceTypeSelect.tomselect) {
                window.invoiceTypeSelect = new TomSelect(invoiceTypeSelect, {
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name', 'id'],
                    create: false,
                    sortField: {
                        field: 'id',
                        direction: 'desc'
                    },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `
                            <div>
                                ${escape(item.name)}
                            </div>
                        `,
                        item: (item, escape) => `
                            <div>${escape(item.name)}</div>
                        `
                    }
                });
            }

        }

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        function validationStore() {
            if (lstProducts.length === 0 && lstServices.length === 0) {
                toastr.error('DEBE AGREGAR AL MENOS UN PRODUCTO O SERVICIO A LA COTIZACIÓN');
                return false;
            }
            return true;
        }

        async function storeQuote(formCreateQuote) {

            toastr.clear();
            const isValid = validationStore();
            if (!isValid) {
                return;
            }

            const result = await Swal.fire({
                title: '¿Desea generar el comprobante de venta?',
                text: "Confirmar",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'SI, registrar',
                cancelButtonText: 'NO',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            });

            if (result.isConfirmed) {

                try {

                    clearValidationErrors('msgError');

                    Swal.fire({
                        title: 'Generando Comprobante...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData(formCreateQuote);
                    formData.append('lst_products', JSON.stringify(lstProducts));
                    formData.append('lst_services', JSON.stringify(lstServices));
                    formData.append('work_order_id', @json($work_order->id));

                    const res = await axios.post(route('tenant.taller.ordenes_trabajo.invoiceStore'), formData);

                    if (res.data.success) {
                        window.open(res.data.pdf_url, '_blank');
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        redirect('tenant.taller.ordenes_trabajo.index');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    Swal.close();
                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        paintValidationErrors(errors, 'error');
                        return;
                    }
                }

            } else {

                Swal.fire({
                    icon: 'info',
                    title: 'Operación cancelada',
                    text: 'No se realizaron acciones.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                });

            }
        }

        function actionChangeProduct(value) {
            if (!value) return;

            const item = productSelect.options[value];

            if (item && item.sale_price) {
                document.querySelector('#product_price').value = item.sale_price;
                document.querySelector('#product_quantity').value = 1;
            }
            if (item && item.stock) {
                document.querySelector('#product_stock').value = item.stock;
            }
        }

        async function actionAddProduct() {

            toastr.clear();
            const productSelected = getProductSelected();
            if (!productSelected) {
                return null;
            }

            const validation = await validationAddProduct(productSelected, lstProducts);
            if (!validation) {
                return;
            }

            addItem(productSelected, lstProducts);
            dtProducts = destroyDataTable(dtProducts);
            clearTable('dt-orders-products');
            paintOrderProducts(lstProducts);
            dtProducts = loadDataTableSimple('dt-orders-products');

            calculateAmounts();
            paintAmounts();
            toastr.success('PRODUCTO AGREGADO AL DETALLE DE PRODUCTOS');

        }

        function getProductSelected() {

            const id = parseInt(window.productSelect.getValue());
            const quantity = parseFloat(document.querySelector('#product_quantity').value);
            const price = parseFloat(document.querySelector('#product_price').value);

            const validation = validationFormProduct(id, quantity, price);
            if (!validation) {
                return null;
            };

            const productSelected = window.productSelect.options[id];

            const product = {
                warehouse_id: productSelected.warehouse_id,
                id,
                name: productSelected.name,
                category_name: productSelected.category_name,
                brand_name: productSelected.brand_name,
                sale_price: price,
                quantity,
                total: price * quantity
            }

            return product;
        }

        function validationFormProduct(id, quantity, price) {
            if (isNaN(id)) {
                toastr.error('DEBE SELECCIONAR UN PRODUCTO');
                window.productSelect.open();
                return false;
            }
            if (isNaN(quantity)) {
                toastr.error('DEBE INGRESAR UNA CANTIDAD');
                document.querySelector('#product_quantity').focus();
                return false;
            }
            if (isNaN(price)) {
                toastr.error('DEBE INGRESAR UN PRECIO');
                document.querySelector('#product_price').focus();
                return false;
            }
            return true;
        }

        async function validationAddProduct(productSelected, lstItems) {

            const indexExists = lstItems.findIndex((i) => i.id == productSelected.id);
            if (indexExists != -1) {
                toastr.error(`${lstItems[indexExists].name} YA EXISTE EN EL DETALLE DE PRODUCTOS`);
                return false;
            }
            return true;
        }

        function addItem(itemSelected, lstItems) {
            lstItems.push(itemSelected);
        }

        function paintOrderProducts(lstItems) {
            const tbody = document.querySelector('#dt-orders-products tbody');
            let rows = ``;

            lstItems.forEach((item) => {
                rows += `
                    <tr>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger btn-delete-product" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                        <td>${item.name}</td>
                        <td>${item.category_name}</td>
                        <td>${item.brand_name}</td>
                        <td>${formatQuantity(item.quantity)}</td>
                        <td>${formatSoles(item.sale_price)}</td>
                        <td>${formatSoles(item.total)}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = rows;
        }

        function actionDeleteProduct(btn, lstItems) {

            toastr.clear();
            const id = btn.getAttribute('data-id');

            const indexItem = lstItems.findIndex(i => i.id == id);
            if (indexItem === -1) {
                toastr.error('EL PRODUCTO NO EXISTE EN EL DETALLE PRODUCTOS');
                return;
            }

            lstItems.splice(indexItem, 1);

            dtProducts = destroyDataTable(dtProducts);
            clearTable('dt-orders-products');
            paintOrderProducts(lstItems);
            dtProducts = loadDataTableSimple('dt-orders-products');

            calculateAmounts();
            paintAmounts();
            toastr.info('PRODUCTO ELIMINADO DEL DETALLE PRODUCTOS');
        }

        function actionChangeService(value) {
            if (!value) return;

            const item = serviceSelect.options[value];

            if (item && item.sale_price) {
                document.querySelector('#service_price').value = item.sale_price;
            }
        }

        async function actionChangeInvoiceType(value){
            if(!value)return;
            mostrarAnimacion1();
            try {
                toastr.clear();
                const res = await axios.get(route('tenant.utils.isActiveInvoiceType',{id:value}));
                if(res.data.success){
                    toastr.info(res.data.message,'OPERACIÓN COMPLETADA');
                }else{
                    toastr.error(res.data.message,'ERROR EN EL SERVIDOR');
                    window.invoiceTypeSelect.open();
                }
            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN VALIDAR COMPROBANTE ACTIVO');
            }finally{
                ocultarAnimacion1();
            }
        }

        function actionAddService() {

            toastr.clear();
            const serviceSelected = getServiceSelected();
            if (!serviceSelected) {
                return null;
            }

            const validation = validationAddService(serviceSelected, lstServices);
            if (!validation) {
                return;
            }

            addItem(serviceSelected, lstServices);
            dtServices = destroyDataTable(dtServices);
            clearTable('dt-orders-services');
            paintOrderServices(lstServices);
            dtServices = loadDataTableSimple('dt-orders-services');

            calculateAmounts();
            paintAmounts();
            toastr.success('SERVICIO AGREGADO AL DETALLE DE SERVICIOS');

        }

        function getServiceSelected() {

            const id = parseInt(window.serviceSelect.getValue());
            const quantity = parseFloat(document.querySelector('#service_quantity').value);
            const price = parseFloat(document.querySelector('#service_price').value);

            const validation = validationFormService(id, quantity, price);
            if (!validation) {
                return null;
            };

            const serviceSelected = window.serviceSelect.options[id];

            const service = {
                id,
                name: serviceSelected.name,
                sale_price: price,
                quantity,
                total: price * quantity
            }

            return service;
        }

        function validationFormService(id, quantity, price) {
            if (isNaN(id)) {
                toastr.error('DEBE SELECCIONAR UN SERVICIO');
                window.serviceSelect.open();
                return false;
            }
            if (isNaN(quantity)) {
                toastr.error('DEBE INGRESAR UNA CANTIDAD EN EL SERVICIO');
                document.querySelector('#service_quantity').focus();
                return false;
            }
            if (isNaN(price)) {
                toastr.error('DEBE INGRESAR UN PRECIO EN EL SERVICIO');
                document.querySelector('#service_price').focus();
                return false;
            }
            return true;
        }

        function validationAddService(serviceSelected, lstItems) {
            const indexExists = lstItems.findIndex((i) => i.id == serviceSelected.id);
            if (indexExists != -1) {
                toastr.error(`${lstItems[indexExists].name} YA EXISTE EN EL DETALLE DE SERVICIOS`);
                return false;
            }
            return true;
        }

        function paintOrderServices(lstItems) {
            const tbody = document.querySelector('#dt-orders-services tbody');
            let rows = ``;

            lstItems.forEach((item) => {
                rows += `
                    <tr>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger btn-delete-service" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                        <td>${item.name}</td>
                        <td>${formatQuantity(item.quantity)}</td>
                        <td>${formatSoles(item.sale_price)}</td>
                        <td>${formatSoles(item.total)}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = rows;
        }

        function actionDeleteService(btn, lstItems) {

            toastr.clear();
            const id = btn.getAttribute('data-id');
            console.log(id);

            const indexItem = lstItems.findIndex(i => i.id == id);
            if (indexItem === -1) {
                toastr.error('EL SERVICIO NO EXISTE EN EL DETALLE DE SERVICIOS');
                return;
            }

            lstItems.splice(indexItem, 1);

            dtServices = destroyDataTable(dtServices);
            clearTable('dt-orders-services');
            paintOrderServices(lstItems);
            dtServices = loadDataTableSimple('dt-orders-services');

            calculateAmounts();
            paintAmounts();
            toastr.info('SERVICIO ELIMINADO DEL DETALLE SERVICIOS');
        }

        function calculateAmounts() {
            let igv = @json($igv);
            let totalPay = 0;
            let tax = 0;
            let subTotal = 0;

            lstProducts.forEach((item) => {
                totalPay += parseFloat(item.total);
            });

            lstServices.forEach((item) => {
                totalPay += parseFloat(item.total);
            });

            subTotal = totalPay / ((100 + igv) / 100);
            tax = totalPay - subTotal;

            amounts.subTotal = subTotal;
            amounts.tax = tax;
            amounts.totalPay = totalPay;
        }

        function paintAmounts() {
            document.querySelector('#subtotal_amount').innerText = formatSoles(amounts.subTotal);
            document.querySelector('#igv_amount').innerText = formatSoles(amounts.tax);
            document.querySelector('#total_amount').innerText = formatSoles(amounts.totalPay);
        }

        async function actionChangeClient(value) {
            if (!value) return;

            mostrarAnimacion1();
            try {

                const res = await axios.get(route('tenant.utils.searchVehicle', {
                    q: '',
                    customer_id: value
                }));

                if (res.data.success) {
                    toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                    setVehiclesClient(res.data.data);
                }

            } catch (error) {
                toastr.error(error, 'ERROR AL CARGAR VEHÍCULOS DEL CLIENTE');
                return;
            } finally {
                ocultarAnimacion1();
            }

        }

        function setVehiclesClient(vehicles) {
            window.vehicleSelect.clear();
            window.vehicleSelect.clearOptions();

            vehicles.forEach(v => {
                window.vehicleSelect.addOption({
                    id: v.id,
                    text: v.text,
                    subtext: v.subtext
                });
            });
        }

        async function actionChangeVehicle(value) {

            if (!value) return;

            //========= TRAER CLIENTES ==========
            mostrarAnimacion1();
            try {

                const res = await axios.get(route('tenant.utils.searchCustomer', {
                    q: '',
                    vehicle_id: value
                }));

                if (res.data.success) {
                    toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                    setCustomerOfVehicle(res.data.data);
                }

            } catch (error) {
                toastr.error(error, 'ERROR AL CARGAR CLIENTE DEL VEHÍCULO');
                return;
            } finally {
                ocultarAnimacion1();
            }
        }

        function setCustomerOfVehicle(customer) {
            window.clientSelect.clear();
            window.clientSelect.clearOptions();

            customer.forEach(v => {
                window.clientSelect.addOption({
                    id: v.id,
                    full_name: v.full_name,
                    email: v.email
                });
            });

            if (customer.length > 0) {
                window.clientSelect.off('change');
                window.clientSelect.setValue(customer[0].id);
                window.clientSelect.on('change', actionChangeClient);

            }
        }

        function loadFilePound() {
            document.querySelectorAll('.filepond-input').forEach(input => {
                FilePond.create(input, {
                    allowImagePreview: true,
                    imagePreviewHeight: 120,
                    imageCropAspectRatio: '1:1',
                    styleLayout: 'compact',
                    stylePanelAspectRatio: 0.5,
                    storeAsFile: true,
                });
            });
        }

        function setQuote() {
            const quote = @json($quote ?? null);
            if (quote) {

                //====== PRODUCTS =========
                const lstProductsBD = @json($lst_products ?? null);
                lstProducts = [...lstProductsBD];

                dtProducts = destroyDataTable(dtProducts);
                clearTable('dt-orders-products');
                paintOrderProducts(lstProducts);
                dtProducts = loadDataTableSimple('dt-orders-products');

                //======= SERVICES =======
                const lstServicesBD = @json($lst_services ?? null);
                lstServices = [...lstServicesBD];

                dtServices = destroyDataTable(dtServices);
                clearTable('dt-orders-services');
                paintOrderServices(lstServices);
                dtServices = loadDataTableSimple('dt-orders-services');

                calculateAmounts();
                paintAmounts();

            }
        }

        function loadPreviewData() {

            //====== PRODUCTS
            dtProducts = destroyDataTable(dtProducts);
            clearTable('dt-orders-products');
            paintOrderProducts(lstProducts);
            dtProducts = loadDataTableSimple('dt-orders-products');

            //======= SERVICES =======
            dtServices = destroyDataTable(dtServices);
            clearTable('dt-orders-services');
            paintOrderServices(lstServices);
            dtServices = loadDataTableSimple('dt-orders-services');

            calculateAmounts();
            paintAmounts();

        }
    </script>
@endsection

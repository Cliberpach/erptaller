@extends('layouts.template')

@section('title')
    Ventas
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @include('utils.modals.vehicles.mdl_create_vehicle')

    <x-card style="margin-top: 0;width:100%;">
        @csrf
        <x-slot name="headerCard">
            <h4 class="card-title">
                PUNTO DE VENTAS
            </h4>
        </x-slot>

        <x-slot name="contentCard">
            @include('sales.sale_document.forms.form_create')
        </x-slot>
    </x-card>
    @include('utils.modals.customer.mdl_create_customer')
@endsection

@section('js')
    <script>
        const lstSale = [];
        let dtProducts = [];
        const amounts = {
            subtotal: 0,
            monto_igv: 0,
            total: 0
        };
        // PASO 4 (Capa B): cada línea = método + cuenta (si electrónico) + monto + n° operación.
        const lstPays = [{
            method_pay: 1,
            amount: 0,
            bank_account_id: null,
            operation_number: null
        }];
        const MAX_PAYS = 10; // tope sano de líneas de pago

        let customerParameters = {
            documentSearchCustomer: null
        };

        let debounceTimer;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDataTableProductos();
            iniciarSelect2();
            loadTomSelect();
            events();

        });

        function events() {

            eventsMdlVehicle();
            eventsMdlCreateCustomer();

            //========== SELECT2 BÚSQUEDA CLIENTE ======
            $('.select2_customer').on('select2:open', function() {
                const searchInput = $('.select2-search__field');

                searchInput.on('input', function() {
                    const searchTerm = $(this).val();
                    console.log('buscado', searchTerm);
                    customerParameters.documentSearchCustomer = $(this).val();
                });
            });

            $('#tbl_products').DataTable().on('search.dt', function(e, settings) {

                const searchValue = settings.oPreviousSearch.search;
                console.log('El usuario buscó:', settings.oPreviousSearch.search);

                $('#tbl_products').DataTable().one('draw', function() {
                    const filteredRows = $('#tbl_products').DataTable().rows({
                        search: 'applied'
                    }).data();

                    if (filteredRows.length === 1) {

                        console.log('Filas filtradas después de la búsqueda:', filteredRows.toArray());
                        const product_id = filteredRows[0].id;
                        const element = document.querySelector(`.btnAdd[data-id="${product_id}"]`);
                        element.click();

                        $('#dt-search-0').val('');

                    }

                });
            });

            document.addEventListener('change', (e) => {

                if (e.target.classList.contains('amount_pay')) {
                    const indexPay = e.target.getAttribute('data-index');
                    const amount_pay = e.target.value;

                    lstPays[indexPay].amount = amount_pay;
                }

                // PASO 4 (Capa B): cuenta elegida por línea
                if (e.target.classList.contains('account_pay')) {
                    const indexPay = e.target.getAttribute('data-index');
                    lstPays[indexPay].bank_account_id = e.target.value || null;
                }
            })

            // PASO 4 (Capa B): n° operación por línea
            document.addEventListener('input', (e) => {
                if (e.target.classList.contains('operation_pay')) {
                    const indexPay = e.target.getAttribute('data-index');
                    lstPays[indexPay].operation_number = e.target.value || null;
                }
            })

            document.querySelector('.btnAddPay').addEventListener('click', (e) => {
                addPay();
            })

            document.addEventListener('input', async (e) => {

                if (e.target.classList.contains('inputCantProduct')) {

                    clearTimeout(debounceTimer);

                    mostrarAnimacion1();
                    toastr.clear();

                    e.target.blur();
                    const product_id = e.target.getAttribute('data-id');
                    let cant = e.target.value;

                    //========= VALIDANDO CANTIDAD =======
                    //========= NO PERMITIR 0 ; COLOCAR 1 POR DEFECTO ======
                    if (isNaN(parseFloat(cant))) {
                        e.target.focus();
                        ocultarAnimacion1();
                        return;
                    }

                    if (!isNaN(parseFloat(cant)) && cant <= 0) {
                        cant = 1;
                        e.target.value = cant;
                    }

                    e.target.focus();
                    ocultarAnimacion1();

                    debounceTimer = setTimeout(async () => {
                        mostrarAnimacion1();
                        const product = {
                            product_id,
                            cant
                        };
                        const resValidateStock = await validateStock(product);

                        if (resValidateStock.success) {

                            //========= ESTABLECIENDO NUEVA CANTIDAD EN EL DETALLE DE LA VENTA ======
                            const indexProduct = lstSale.findIndex((p) => {
                                return p.id == product_id
                            });

                            lstSale[indexProduct].cant = cant;
                            calculatePrices(lstSale);
                            paintTableAmounts();

                            //========= ACTUALIZANDO IMPORTE EN SALE TABLE DETAIL =======
                            const inputAmountProduct = document.querySelector(
                                `.amount_product_${product_id}`);
                            const amount_product = parseFloat(lstSale[indexProduct].sale_price) *
                                parseFloat(lstSale[indexProduct].cant);
                            const amount_formatted = amount_product.toLocaleString('es-PE', {
                                style: 'currency',
                                currency: 'PEN',
                                minimumFractionDigits: 2
                            });
                            inputAmountProduct.value = amount_formatted;

                            lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                            paintLstPays(lstPays);

                            toastr.success(resValidateStock.message, 'OPERACIÓN COMPLETADA');
                        } else {

                            toastr.error(resValidateStock.message, 'ERROR EN EL SERVIDOR');

                            //======== COLOCANDO STOCK EN EL INPUT DE CANTIDAD =======
                            e.target.value = resValidateStock.stock;

                            const indexProduct = lstSale.findIndex((p) => {
                                return p.id == product_id
                            });
                            lstSale[indexProduct].cant = resValidateStock.stock;
                            calculatePrices(lstSale);
                            paintTableAmounts();

                            //========= ACTUALIZANDO IMPORTE EN SALE TABLE DETAIL =======
                            const inputAmountProduct = document.querySelector(
                                `.amount_product_${product_id}`);
                            const amount_product = parseFloat(lstSale[indexProduct].sale_price) *
                                parseFloat(lstSale[indexProduct].cant);
                            const amount_formatted = amount_product.toLocaleString('es-PE', {
                                style: 'currency',
                                currency: 'PEN',
                                minimumFractionDigits: 2
                            });
                            inputAmountProduct.value = amount_formatted;

                            lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                            paintLstPays(lstPays);
                        }

                        e.target.focus();
                        ocultarAnimacion1();
                    }, 1200);

                }

            })

            document.querySelector('#form-store').addEventListener('submit', (e) => {
                e.preventDefault();
                toastr.clear();
                const validation = validateSale();
                if (validation) {
                    storeSale(e.target);
                }
            })

            document.addEventListener('click', async (e) => {

                if (e.target.closest('.btn_delete_pay')) {
                    const btn = e.target.closest('.btn_delete_pay');
                    const indexPay = btn.dataset.index;

                    lstPays.splice(indexPay, 1);
                    paintLstPays(lstPays);
                }


                const btnDeleteproduct = e.target.closest('.delete-product');
                if (btnDeleteproduct) {

                    toastr.clear();
                    mostrarAnimacion1();
                    const producto_id = btnDeleteproduct.getAttribute('data-id');

                    const indexProductExists = lstSale.findIndex((p) => {
                        return p.id == producto_id;
                    })

                    if (indexProductExists === -1) {
                        toastr.error('EL PRODUCTO NO EXISTE EN EL DETALLE DE LA VENTA');
                        return;
                    }

                    lstSale.splice(indexProductExists, 1);

                    calculatePrices(lstSale);
                    clearTable('tbl_sale_detail');
                    paintTableSaleDetail(lstSale);
                    paintTableAmounts();

                    lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                    paintLstPays(lstPays);

                    toastr.info('PRODUCTO ELIMINADO!!!');
                    ocultarAnimacion1();
                }

                if (e.target.classList.contains('btnAdd')) {

                    const product_id = e.target.getAttribute('data-id');
                    const product = getRowById(dtProducts, e.target.dataset.id);

                    //====== COMPROBANDO SI EXISTE EL PRODUCTO  EN EL SALE DETAIL ========
                    const indexProductExists = lstSale.findIndex(p => p.id == product_id);
                    let cant = 1;

                    if (indexProductExists !== -1) {
                        cant = parseFloat(lstSale[indexProductExists].cant) + 1;
                    }

                    //==== VALIDANDO STOCK =======
                    mostrarAnimacion1();
                    toastr.clear();
                    const resValidateStock = await validateStock({
                        product_id,
                        cant
                    });

                    if (resValidateStock.success) {
                        toastr.success(resValidateStock.message, 'OPERACIÓN COMPLETADA');

                        //======= AGREGANDO AL DETALLE DE LA VENTA =======
                        addProductToCar({
                            ...product
                        }, indexProductExists, cant);
                        calculatePrices(lstSale);
                        clearTable('tbl_sale_detail');
                        paintTableSaleDetail(lstSale);
                        paintTableAmounts();

                        lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                        paintLstPays(lstPays);

                    } else {
                        toastr.error(resValidateStock.message, 'ERROR EN EL SERVIDOR');
                        e.target.value = resValidateStock.stock;
                    }

                    ocultarAnimacion1();

                }

                if (e.target.classList.contains('remove-product')) {

                    const productId = e.target.dataset.id;
                    removeProductFromCar(productId);
                    paintCar(lstSale);
                }
            })

            document.querySelector('#payment_condition_id').addEventListener('change', setExpirationDate);

            window.clientSelect.on('change', function(value) {
                actionChangeClient(value);
            });

            window.vehicleSelect.on('change', function(value) {
                actionChangeVehicle(value);
            });

        }

        function changeMethodPay(selecMethodPay) {
            const indexPay = selecMethodPay.getAttribute('data-index');
            const method_pay = selecMethodPay.value;

            lstPays[indexPay].method_pay = method_pay;
            // Al cambiar el método se resetea cuenta/operación; el combo se recarga.
            lstPays[indexPay].bank_account_id = null;
            lstPays[indexPay].operation_number = null;
            refreshAccountCombo(indexPay, method_pay, null, null);
        }

        // PASO 4 (Capa B): carga las cuentas del método en la línea; muestra/oculta cuenta + n° op.
        async function refreshAccountCombo(index, methodId, selectedId, opValue) {
            const accSel = document.querySelector(`.account_pay[data-index="${index}"]`);
            const opInput = document.querySelector(`.operation_pay[data-index="${index}"]`);
            if (!accSel || !opInput || !methodId) return;

            const url = @json(route('tenant.utils.paymentAccounts', ['method' => ':m'])).replace(':m', methodId);
            try {
                const res = await (await fetch(url)).json();
                if (res.needs_account) {
                    accSel.innerHTML = '<option value="">Seleccionar cuenta</option>' +
                        res.data.map(c => `<option value="${c.id}" ${selectedId == c.id ? 'selected' : ''}>${c.label}</option>`).join('');
                    accSel.style.display = '';
                    opInput.style.display = '';
                    opInput.value = opValue || '';
                    lstPays[index].bank_account_id = selectedId || null;
                    lstPays[index].operation_number = opValue || null;
                } else {
                    accSel.innerHTML = '<option value=""></option>';
                    accSel.style.display = 'none';
                    opInput.value = '';
                    opInput.style.display = 'none';
                    lstPays[index].bank_account_id = null;
                    lstPays[index].operation_number = null;
                }
            } catch (e) { /* combo no crítico: si falla, queda sin cuenta */ }
        }

        function addPay() {
            toastr.clear();
            if (lstPays.length < MAX_PAYS) {

                lstPays.push({
                    method_pay: null,
                    amount: 0,
                    bank_account_id: null,
                    operation_number: null
                });
                paintLstPays(lstPays);

            } else {
                toastr.error('MÁXIMO DE LÍNEAS DE PAGO: ' + MAX_PAYS);
            }

        }

        function paintLstPays(lstPays) {
            const tbody = document.querySelector('#tbl_pay tbody');
            const paymentMethods = @json($payment_methods);
            tbody.innerHTML = '';

            lstPays.forEach((pay, index) => {

                let new_pay = `<tr>
                                    <td>
                                        <select onchange="changeMethodPay(this)"  name="" id="" class="form-control method_pay select2_pay" data-index="${index}" data-placeholder="Seleccionar">
                                            <option></option>
                                `;

                paymentMethods.forEach((p) => {
                    new_pay +=
                        `<option ${pay.method_pay == p.id? 'selected':''} value="${p.id}">${p.description}</option>`;
                })

                new_pay += `</select>
                                    </td>
                                    <td>
                                        <select class="form-control account_pay" data-index="${index}" style="display:none;">
                                            <option value=""></option>
                                        </select>
                                    </td>
                                    <td>
                                        <input data-index="${index}" type="text" class="form-control operation_pay" placeholder="N° op." style="display:none;">
                                    </td>
                                    <td>
                                        <input data-index="${index}" value="${pay.amount}" type="text" class="form-control amount_pay inputDecimalPositivo">
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm btn_delete_pay" data-index="${index}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>`;

                tbody.insertAdjacentHTML('beforeend', new_pay);
            })

            $('.select2_pay').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            // Recargar el combo de cuenta de cada línea (según su método actual).
            lstPays.forEach((pay, index) => refreshAccountCombo(index, pay.method_pay, pay.bank_account_id, pay.operation_number));
        }

        function loadTomSelect() {

            const initialCustomer = @json($customer_formatted);
            window.clientSelect = new TomSelect('#customer_id', {
                valueField: 'id',
                options: [initialCustomer],
                items: [initialCustomer.id],
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
                    if (query.length < 3) return callback();
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
                    item: (item, escape) => `<div>${escape(item.full_name)}</div>`,
                    no_results: function(data, escape) {
                        return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
                    }
                }
            });

            window.vehicleSelect = new TomSelect('#vehicle_id', {
                valueField: 'id',
                labelField: 'text',
                searchField: ['text'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un vehículo',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastVehicleQuery = str;
                },
                load: async (query, callback) => {
                    if (!query.length) return callback();
                    try {
                        const url = route('tenant.utils.searchVehicle', {
                            q: query,
                            customer_id: window.clientSelect.getValue()
                        });

                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar vehiculos');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
                        if (results.length === 0) {
                            vehicleParams.plateSearchVehicle = lastVehicleQuery;
                            console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                        }
                    } catch (error) {
                        console.error('Error cargando vehiculos:', error);
                        callback();
                    }
                },
                render: {
                    option: (item, escape) => `
                        <div>
                            <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                            <strong>${escape(item.text)}</strong><br>
                            <small>${escape(item.subtext ?? '')}</small>
                        </div>
                    `,
                    item: (item, escape) => `
                            <div>
                                <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                                ${escape(item.text)}
                            </div>
                        `,
                    no_results: function(data, escape) {
                        return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
                    }
                }
            });
        }

        function iniciarSelect2() {
            $('.select2_form').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            $('.select2_customer').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            $('.select2_pay').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
            });

            $('.select2_form_customer').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                dropdownParent: $('#mdlCreateCustomer'),
            });
        }

        function iniciarDataTableProductos() {
            const urlGetProductos = '{{ route('tenant.ventas.comprobante_venta.getProductos') }}';

            dtProducts = new DataTable('#tbl_products', {
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetProductos,
                    type: 'GET',
                    data: function(d) {
                        d.categoria_id = $('#categoria').val();
                        d.marca_id = $('#marca').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row) {

                            return `
                            <i data-id="${data.id}" class="fas fa-plus btnAdd btn btn-primary" ></i>
                        `;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
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
                        data: 'sale_price',
                        name: 'sale_price'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'code_bar',
                        name: 'code_bar'
                    },
                ],
                pageLength: 25,
                lengthChange: false,
                dom: '<"row mb-3"<"col-12"f>>t<"row"<"col-6"i><"col-6"p>>',
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

            const inputSearchDataTable = document.querySelector('#dt-search-0');
            const previousSibling = inputSearchDataTable.previousElementSibling;
            inputSearchDataTable.style.width = '100%';
            inputSearchDataTable.placeholder = 'Buscar producto';
            previousSibling.style.display = 'none';

        }

        function validateSale() {

            let validation = true;


            //======== VALIDANDO DETALLE DE VENTA =========
            if (lstSale.length === 0) {
                toastr.error('EL DETALLE DE LA VENTA ESTÁ VACÍO!!!');
                validation = false;
                return validation;
            }

            //======= VALIDANDO TIPO DE VENTA ===========
            const type_sale = document.querySelector('#type_sale').value;
            if (!type_sale) {
                toastr.error('DEBE SELECCIONAR UN TIPO DE COMPROBANTE!!!');
                validation = false;
            }

            // if (type_sale !== '80' && type_sale !== '3' && type_sale !== '1') {
            //     toastr.error('EL TIPO DE COMPROBANTE NO EXISTE!!!');
            //     validation = false;
            // }

            //===== VALIDANDO CLIENTE =========
            const customer_id = document.querySelector('#customer_id');
            if (!customer_id.value) {
                toastr.error('DEBE SELECCIONAR UN CLIENTE!!!');
                validation = false;
            }

            //======== VALIDANDO PAGOS ========
            if (lstPays.length === 0) {
                toastr.error('DEBE AGREGAR UN PAGO!!!');
                validation = false;
            }

            const lstMethodPays = [];
            let paysRepeat = false;
            let payNoNumber = false;
            let payCero = false;
            let methodPayNull = false;
            let totalPay = 0;
            for (const pay of lstPays) {

                if (!pay.method_pay) {
                    methodPayNull = true;
                }

                if (!lstMethodPays.includes(pay.method_pay)) {
                    lstMethodPays.push(pay.method_pay);
                } else {
                    paysRepeat = true;
                }

                if (isNaN(parseFloat(pay.amount))) {
                    payNoNumber = true;
                    console.log(pay.amount);
                }

                if (parseFloat(pay.amount) === 0) {
                    payCero = true;
                }

                totalPay += parseFloat(pay.amount);
            }

            if (paysRepeat) {
                toastr.error('LOS MÉTODOS DE PAGO DEBEN SER DIFERENTES!!!');
                validation = false;
                return validation;
            }
            if (methodPayNull) {
                toastr.error('NO HA SELECCIONADO MÉTODO DE PAGO!!!');
                validation = false;
                return validation;
            }

            if (payNoNumber) {
                toastr.error('LOS PAGOS DEBEN SER NUMÉRICOS!!!');
                validation = false;
                return validation;
            }
            if (payCero) {
                toastr.error('LOS PAGOS DEBEN SER MAYOR A 0!!!');
                validation = false;
                return validation;
            }

            if (parseFloat(totalPay.toFixed(2)) !== parseFloat(amounts.total.toFixed(2))) {
                toastr.error('EL TOTAL DE VENTA NO COINCIDE CON EL TOTAL PAGO!!!');
                validation = false;
                return validation;
            }


            return validation;
        }

        const paintTableAmounts = () => {

            const pOpAmount = document.querySelector('.op-amount');
            const pIgvAmount = document.querySelector('.igv-amount');
            const pTotalAmount = document.querySelector('.total-amount');

            pOpAmount.textContent = amounts.subtotal.toLocaleString('es-PE', {
                style: 'currency',
                currency: 'PEN'
            });
            pIgvAmount.textContent = amounts.monto_igv.toLocaleString('es-PE', {
                style: 'currency',
                currency: 'PEN'
            });
            pTotalAmount.textContent = amounts.total.toLocaleString('es-PE', {
                style: 'currency',
                currency: 'PEN'
            });

        }

        const paintTableSaleDetail = (lstItems) => {

            const tbody = document.querySelector('#tbl_sale_detail tbody');
            let rows = '';

            lstItems.forEach((p) => {

                let formattedAmount = (p.sale_price * p.cant).toLocaleString('es-PE', {
                    style: 'currency',
                    currency: 'PEN',
                    minimumFractionDigits: 2
                });

                rows += `<tr>
                            <td>
                                <input style="width:66px;" data-id="${p.id}" value="${p.cant}" type="number" class="form-control inputCantProduct">
                            </td>
                            <td>
                                ${p.name}
                            </td>
                            <td>
                                <div style="display:flex;justify-content:end;">
                                    <input readonly="readonly" value="${formattedAmount}" type="text" class="form-control amount_product_${p.id}">
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-product" data-id="${p.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
            })

            tbody.innerHTML = rows;
        }

        const calculatePrices = (lstItems) => {
            const percentageIgv = @json($company->igv);
            let subtotal = 0;
            let total = 0;
            let monto_igv = 0;


            lstItems.forEach((item) => {
                total += (parseFloat(item.sale_price) * parseFloat(item.cant));
            })

            subtotal = total / (1 + (percentageIgv / 100));
            monto_igv = total - subtotal;

            amounts.subtotal = subtotal;
            amounts.monto_igv = monto_igv;
            amounts.total = total;

        }

        function storeSale(formStore) {
            clearValidationErrors();

            Swal.fire({
                title: "DESEA REGISTRAR LA VENTA?",
                text: "Se generará un comprobante de venta!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "SÍ, REGISTRAR!",
                cancelButtonText: "NO, CANCELAR!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Registrando venta...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const token = document.querySelector('input[name="_token"]').value;
                        const formData = new FormData(formStore);
                        const urlStoreSale = @json(route('tenant.ventas.comprobante_venta.store'));

                        formData.append('lstSale', JSON.stringify(lstSale));
                        formData.append('lstPays', JSON.stringify(lstPays));
                        formData.append('user_recorder_id', @json(Auth::user()->id));
                        formData.append('igv_percentage', @json($company->igv));

                        const response = await fetch(urlStoreSale, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        });

                        const res = await response.json();

                        if (response.status === 422) {
                            if ('errors' in res) {
                                paintValidationErrors(res.errors);
                            }
                            Swal.close();
                            return;
                        }

                        if (res.success) {

                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');

                            const url_open_pdf =
                                "{{ route('tenant.ventas.comprobante_venta.pdf_voucher', ['id' => '__id__']) }}"
                                .replace('__id__', res.data.sale_id);
                            window.open(url_open_pdf, 'Comprobante SISCOM',
                                'location=1, status=1, scrollbars=1,width=900, height=600');

                            const sale_index = @json(route('tenant.ventas.comprobante_venta'));

                            window.location.href = sale_index;

                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR VENTA');
                        Swal.close();
                    }


                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalWithBootstrapButtons.fire({
                        title: "OPERACIÓN CANCELADA",
                        text: "NO SE REALIZARON ACCIONES",
                        icon: "error"
                    });
                }
            });
        }

        async function validateStock(product) {
            try {

                const token = document.querySelector('input[name="_token"]').value;
                const urlValidateStock = new URL(@json(route('tenant.ventas.comprobante_venta.validateStock')));

                Object.keys(product).forEach(key => {
                    urlValidateStock.searchParams.append(key, product[key]);
                });

                const response = await fetch(urlValidateStock, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                });

                const res = await response.json();

                return res;

            } catch (error) {
                return {
                    success: false,
                    message: error
                };
                toastr.error(error, 'ERROR EN LA PETICIÓN VALIDAR STOCK');
            }
        }

        const addProductToCar = (product, indexProductExists, cant) => {

            if (indexProductExists === -1) {
                product.cant = cant;
                lstSale.push(product);
            } else {
                lstSale[indexProductExists].cant++;
            }

        }

        function setExpirationDate() {

            const selectCondicion = document.getElementById('payment_condition_id');
            const inputFechaRegistro = document.querySelector('#registration_date');
            const inputFechaVencimiento = document.querySelector('#expiration_date');

            const selectedOption = selectCondicion.options[selectCondicion.selectedIndex];
            const days = parseInt(selectedOption.dataset.days || 0, 10);

            const [year, month, day] = inputFechaRegistro.value.split('-');
            const fechaBase = new Date(year, month - 1, day);

            fechaBase.setDate(fechaBase.getDate() + days);

            const yyyy = fechaBase.getFullYear();
            const mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
            const dd = String(fechaBase.getDate()).padStart(2, '0');

            inputFechaVencimiento.value = `${yyyy}-${mm}-${dd}`;
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
            document.querySelector('#plate').value = '';
            const vehicleInfo = document.querySelector('#vehicle_info');
            vehicleInfo.classList.add('d-none');
            vehicleInfo.querySelector('.fw-semibold').textContent = '';


            if (!value) return;
            const vehicle = window.vehicleSelect.options[value];
            document.querySelector('#plate').value = vehicle.text;

            vehicleInfo.classList.remove('d-none');
            vehicleInfo.querySelector('.fw-semibold').textContent = vehicle.subtext;

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
    </script>
@endsection

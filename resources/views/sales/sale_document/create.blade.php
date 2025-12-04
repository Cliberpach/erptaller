@extends('layouts.template')

@section('title')
    Ventas
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')

    @include('utils.spinners.spinner_1')

    <x-card style="margin-top: 0;width:100%;">
        @csrf
        <x-slot name="headerCard">
            <h4 class="card-title">
                PUNTO DE VENTAS
            </h4>
        </x-slot>

        <x-slot name="contentCard">
            <div class="row">
                <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        @include('sales.sale_document.tables.tbl_products')
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">

                        <div class="col-lg-12">
                            <div class="form-floating">

                                <select class="form-select" id="type_sale" aria-label="Floating label select example">
                                    <option value="80">NOTA DE VENTA</option>
                                    <option value="3">BOLETA</option>
                                    <option value="1">FACTURA</option>
                                </select>
                                <label for="type_sale">Comprobante</label>

                            </div>
                        </div>

                        <div class="col-12">
                            <div id="boleta-container" style="margin-top:10px;">
                                <label for="customer_id" style="font-weight:bold;">Cliente</label><i class="fas fa-user-plus btn btn-warning" onclick="openMdlNewCustomer();" style="margin-left:4px;margin-bottom:4px;"></i>
                                <select data-placeholder="Seleccionar" class="form-select select2_customer" id="customer_id" aria-label="Floating label select example">
                                    @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->document_number.' - '.$customer->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row sale-detail">
                        <div class="col sale-detail__col">
                            @include('sales.sale_document.tables.tbl_sale_detail')
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-lg-6" style="display:flex;justify-content:end;">
                                    <span style="font-weight:bold;">OP. GRAVADA</span>
                                </div>
                                <div class="col-lg-6" style="display:flex;justify-content:end;">
                                    <p class="op-amount"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6" style="display:flex;justify-content:end;">
                                    <span style="font-weight:bold;">IGV</span>
                                </div>
                                <div class="col-lg-6" style="display:flex;justify-content:end;">
                                    <p class="igv-amount"></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="table-responsive">
                                    @include('sales.sale_document.tables.tbl_pay')
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <button class="btn btn-primary btnAddPay">
                                        <i class="fas fa-plus" ></i> Agregar pago
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div style="background-color:#343a40; color:#fff;" class="payment__total btn btn-dark">
                                    <div class="payment__header d-flex align-items-center">
                                        <i class="fas fa-chevron-circle-right"></i>
                                        <p>TOTAL</p>
                                    </div>
                                    <div class="payment__amount d-flex align-items-center">
                                        <p class="total-amount">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </x-slot>
    </x-card>
    @include('utils.modals.customer.mdl_create_customer')

@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    const lstSale       =   [];
    let dtProducts      =   [];
    const amounts       =   {subtotal:0,monto_igv:0,total:0};
    const lstPays       =   [{method_pay:1,amount:0}];

    let customerParameters =   {documentSearchCustomer:null};

    let debounceTimer;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableProductos();
        iniciarSelect2();
        events();

    });

    function events(){

        eventsMdlCreateCustomer();

        //========== SELECT2 BÚSQUEDA CLIENTE ======
        $('.select2_customer').on('select2:open', function() {
            const searchInput = $('.select2-search__field');

            searchInput.on('input', function() {
                const searchTerm = $(this).val();
                console.log('buscado',searchTerm);
                customerParameters.documentSearchCustomer  =   $(this).val();
            });
        });

        $('#tbl_products').DataTable().on('search.dt', function (e, settings) {

            const searchValue = settings.oPreviousSearch.search;
            console.log('El usuario buscó:', settings.oPreviousSearch.search);

            $('#tbl_products').DataTable().one('draw', function () {
                const filteredRows = $('#tbl_products').DataTable().rows({ search: 'applied' }).data();

                if(filteredRows.length === 1){

                    console.log('Filas filtradas después de la búsqueda:', filteredRows.toArray());
                    const product_id    =   filteredRows[0].id;
                    const element = document.querySelector(`.btnAdd[data-id="${product_id}"]`);
                    element.click();

                    $('#dt-search-0').val('');

                }

            });
        });

        document.addEventListener('change',(e)=>{

            if(e.target.classList.contains('amount_pay')){
                const indexPay      =   e.target.getAttribute('data-index');
                const amount_pay    =   e.target.value;

                lstPays[indexPay].amount    =   amount_pay;
            }

        })

        document.querySelector('.btnAddPay').addEventListener('click',(e)=>{
            addPay();
        })

        document.addEventListener('input',async (e)=>{

            if(e.target.classList.contains('inputCantProduct')){

                clearTimeout(debounceTimer);

                mostrarAnimacion1();
                toastr.clear();

                e.target.blur();
                const product_id        =   e.target.getAttribute('data-id');
                let cant                =   e.target.value;

                //========= VALIDANDO CANTIDAD =======
                //========= NO PERMITIR 0 ; COLOCAR 1 POR DEFECTO ======
                if(isNaN(parseFloat(cant))){
                    e.target.focus();
                    ocultarAnimacion1();
                    return;
                }

                if ( !isNaN(parseFloat(cant)) && cant <= 0 ) {
                    cant            =   1;
                    e.target.value  =   cant;
                }

                e.target.focus();
                ocultarAnimacion1();

                debounceTimer = setTimeout(async () => {
                    mostrarAnimacion1();
                    const product           =   {product_id,cant};
                    const resValidateStock  =   await validateStock(product);

                    if(resValidateStock.success){

                        //========= ESTABLECIENDO NUEVA CANTIDAD EN EL DETALLE DE LA VENTA ======
                        const indexProduct  =   lstSale.findIndex((p)=>{return p.id == product_id});

                        lstSale[indexProduct].cant  =   cant;
                        calculatePrices(lstSale);
                        paintTableAmounts();

                        //========= ACTUALIZANDO IMPORTE EN SALE TABLE DETAIL =======
                        const inputAmountProduct    =   document.querySelector(`.amount_product_${product_id}`);
                        const amount_product        =   parseFloat(lstSale[indexProduct].sale_price) * parseFloat(lstSale[indexProduct].cant);
                        const amount_formatted      =   amount_product.toLocaleString('es-PE', {
                                                            style: 'currency',
                                                            currency: 'PEN',
                                                            minimumFractionDigits: 2
                                                        });
                        inputAmountProduct.value    =   amount_formatted;

                        lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                        paintLstPays(lstPays);

                        toastr.success(resValidateStock.message,'OPERACIÓN COMPLETADA');
                    }else{

                        toastr.error(resValidateStock.message,'ERROR EN EL SERVIDOR');

                        //======== COLOCANDO STOCK EN EL INPUT DE CANTIDAD =======
                        e.target.value =   resValidateStock.stock;

                        const indexProduct          =   lstSale.findIndex((p)=>{return p.id == product_id});
                        lstSale[indexProduct].cant  =   resValidateStock.stock;
                        calculatePrices(lstSale);
                        paintTableAmounts();

                        //========= ACTUALIZANDO IMPORTE EN SALE TABLE DETAIL =======
                        const inputAmountProduct    =   document.querySelector(`.amount_product_${product_id}`);
                        const amount_product        =   parseFloat(lstSale[indexProduct].sale_price) * parseFloat(lstSale[indexProduct].cant);
                        const amount_formatted      =   amount_product.toLocaleString('es-PE', {
                                                            style: 'currency',
                                                            currency: 'PEN',
                                                            minimumFractionDigits: 2
                                                        });
                        inputAmountProduct.value    =   amount_formatted;

                        lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                        paintLstPays(lstPays);
                    }

                    e.target.focus();
                    ocultarAnimacion1();
                },1200);

            }

        })

        document.addEventListener('click',async (e)=>{

            if (e.target.closest('.payment__total')) {
                toastr.clear();
                const validation    =   validateSale();
                if(validation){
                    storeSale();
                }
            }

            if(e.target.classList.contains('btn_delete_pay')){
                const indexPay      =   e.target.getAttribute('data-index');
                lstPays.splice(indexPay,1);
                paintLstPays(lstPays);
            }

            if(e.target.classList.contains('delete-product')){

                toastr.clear();
                mostrarAnimacion1();
                const   producto_id         =   e.target.getAttribute('data-id');

                const indexProductExists    =   lstSale.findIndex((p)=>{
                    return p.id == producto_id;
                })

                if(indexProductExists === -1){
                    toastr.error('EL PRODUCTO NO EXISTE EN EL DETALLE DE LA VENTA');
                    return;
                }

                lstSale.splice(indexProductExists,1);

                calculatePrices(lstSale);
                clearTable('tbl_sale_detail');
                paintTableSaleDetail(lstSale);
                paintTableAmounts();

                lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                paintLstPays(lstPays);

                toastr.info('PRODUCTO ELIMINADO!!!');
                ocultarAnimacion1();
            }

            if(e.target.classList.contains('btnAdd')){

                const product_id    =   e.target.getAttribute('data-id');
                const product       =   getRowById(dtProducts,e.target.dataset.id);

                //====== COMPROBANDO SI EXISTE EL PRODUCTO  EN EL SALE DETAIL ========
                const indexProductExists    =   lstSale.findIndex(p=>  p.id == product_id);
                let cant                    =   1;

                if(indexProductExists !== -1){
                    cant    =   parseFloat(lstSale[indexProductExists].cant) + 1;
                }

                //==== VALIDANDO STOCK =======
                mostrarAnimacion1();
                toastr.clear();
                const resValidateStock  =   await validateStock({product_id,cant});

                if(resValidateStock.success){
                    toastr.success(resValidateStock.message,'OPERACIÓN COMPLETADA');

                    //======= AGREGANDO AL DETALLE DE LA VENTA =======
                    addProductToCar({...product},indexProductExists,cant);
                    calculatePrices(lstSale);
                    clearTable('tbl_sale_detail');
                    paintTableSaleDetail(lstSale);
                    paintTableAmounts();

                    lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
                    paintLstPays(lstPays);

                }else{
                    toastr.error(resValidateStock.message,'ERROR EN EL SERVIDOR');
                    e.target.value =   resValidateStock.stock;
                }

                ocultarAnimacion1();

            }

            if(e.target.classList.contains('remove-product')){

                    const productId= e.target.dataset.id;
                    removeProductFromCar(productId);
                    paintCar(lstSale);
            }
        })

    }

    function changeMethodPay(selecMethodPay){
        const indexPay      =   selecMethodPay.getAttribute('data-index');
        const method_pay    =   selecMethodPay.value;

        lstPays[indexPay].method_pay    =   method_pay;
    }

    function addPay(){
        toastr.clear();
        if(lstPays.length < 2){

            lstPays.push({method_pay:null,amount:0});
            paintLstPays(lstPays);

        }else{
            toastr.error('MÁXIMO DE PAGOS PERMITIDOS 2!!!');
        }

    }

    function paintLstPays(lstPays){
        const tbody     =   document.querySelector('#tbl_pay tbody');
        const paymentMethods    =   @json($payment_methods);
        tbody.innerHTML =   '';

        lstPays.forEach((pay,index)=>{

            let new_pay =   `<tr>
                                    <td>
                                        <select onchange="changeMethodPay(this)"  name="" id="" class="form-control method_pay select2_pay" data-index="${index}" data-placeholder="Seleccionar">
                                            <option></option>
                                `;

            paymentMethods.forEach((p)=>{
                new_pay +=  `<option ${pay.method_pay == p.id? 'selected':''} value="${p.id}">${p.description}</option>`;
            })

            new_pay +=  `</select>
                                    </td>
                                    <td>
                                        <input data-index="${index}" value="${pay.amount}" type="text" class="form-control amount_pay inputDecimalPositivo">
                                    </td>
                                    <td>
                                        <i class="fas fa-trash-alt btn btn-danger btn_delete_pay" data-index="${index}"></i>
                                    </td>
                                </tr>`;

            tbody.insertAdjacentHTML('beforeend', new_pay);
        })

        $( '.select2_pay' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true
        } );
    }

    function iniciarSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true
        } );

        $( '.select2_customer' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true
        } );

        $( '.select2_pay' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
        } );

        $( '.select2_form_customer' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            dropdownParent: $('#mdlCreateCustomer'),
        } );
    }

    function iniciarDataTableProductos(){
        const urlGetProductos = '{{ route('tenant.ventas.comprobante_venta.getProductos') }}';

        dtProducts  =   new DataTable('#tbl_products',{
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetProductos,
                type: 'GET',
                data: function (d) {
                    d.categoria_id  =   $('#categoria').val();
                    d.marca_id      =   $('#marca').val();
                }
            },
            columns: [
                {
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
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'brand_name', name: 'brand_name' },
                { data: 'sale_price', name: 'sale_price' },
                { data: 'stock', name: 'stock' },
                { data: 'code_bar', name: 'code_bar' },
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

        const inputSearchDataTable          =   document.querySelector('#dt-search-0');
        const previousSibling               =   inputSearchDataTable.previousElementSibling;
        inputSearchDataTable.style.width    =   '100%';
        inputSearchDataTable.placeholder    =   'Buscar producto';
        previousSibling.style.display       =   'none';

    }

    function validateSale(){

        let validation  =   true;


        //======== VALIDANDO DETALLE DE VENTA =========
        if(lstSale.length === 0){
            toastr.error('EL DETALLE DE LA VENTA ESTÁ VACÍO!!!');
            validation  =   false;
            return validation;
        }

        //======= VALIDANDO TIPO DE VENTA ===========
        const type_sale =   document.querySelector('#type_sale').value;
        if(!type_sale){
            toastr.error('DEBE SELECCIONAR UN TIPO DE COMPROBANTE!!!');
            validation  =   false;
        }

        if (type_sale !== '80' && type_sale !== '3' && type_sale !== '1') {
            toastr.error('EL TIPO DE COMPROBANTE NO EXISTE!!!');
            validation  =   false;
        }

        //===== VALIDANDO CLIENTE =========
        const customer_id =   document.querySelector('#customer_id');
        if(!customer_id.value){
            toastr.error('DEBE SELECCIONAR UN CLIENTE!!!');
            validation = false;
        }

        //======== VALIDANDO PAGOS ========
        if(lstPays.length === 0){
            toastr.error('DEBE AGREGAR UN PAGO!!!');
            validation  =   false;
        }

        const lstMethodPays     =   [];
        let paysRepeat          =   false;
        let payNoNumber         =   false;
        let payCero             =   false;
        let methodPayNull       =   false;
        let   totalPay          =   0;
        for (const pay of lstPays) {

            if(!pay.method_pay){
                methodPayNull   =   true;
            }

            if (!lstMethodPays.includes(pay.method_pay)) {
                lstMethodPays.push(pay.method_pay);
            } else {
                paysRepeat  =   true;
            }

            if(isNaN(parseFloat(pay.amount))){
                payNoNumber =   true;
                console.log(pay.amount);
            }

            if(parseFloat(pay.amount) === 0){
                payCero =   true;
            }

            totalPay    +=  parseFloat(pay.amount);
        }

        if(paysRepeat){
            toastr.error('LOS MÉTODOS DE PAGO DEBEN SER DIFERENTES!!!');
            validation = false;
            return validation;
        }
        if(methodPayNull){
            toastr.error('NO HA SELECCIONADO MÉTODO DE PAGO!!!');
            validation = false;
            return validation;
        }

        if(payNoNumber){
            toastr.error('LOS PAGOS DEBEN SER NUMÉRICOS!!!');
            validation = false;
            return validation;
        }
        if(payCero){
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

        const pOpAmount     =   document.querySelector('.op-amount');
        const pIgvAmount    =   document.querySelector('.igv-amount');
        const pTotalAmount  =   document.querySelector('.total-amount');

        pOpAmount.textContent       =   amounts.subtotal.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
        pIgvAmount.textContent      =   amounts.monto_igv.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
        pTotalAmount.textContent    =   amounts.total.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });

    }

    const paintTableSaleDetail   =   (lstItems)=>{

        const tbody =   document.querySelector('#tbl_sale_detail tbody');
        let rows    =   '';

        lstItems.forEach((p)=>{

            let formattedAmount = (p.sale_price * p.cant).toLocaleString('es-PE', {
                style: 'currency',
                currency: 'PEN',
                minimumFractionDigits: 2
            });

            rows    +=  `<tr>
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
                                <i class="fas fa-trash-alt delete-product btn btn-danger" data-id=${p.id}></i>
                            </td>
                        </tr>`;
        })

        tbody.innerHTML =   rows;
    }

    const calculatePrices=(lstItems)=>{
        const   percentageIgv       =   @json($company->igv);
        let     subtotal            =   0;
        let     total               =   0;
        let     monto_igv           =   0;


        lstItems.forEach((item)=>{
            total   +=  (parseFloat(item.sale_price) * parseFloat(item.cant));
        })

        subtotal    =   total / (1 + (percentageIgv / 100));
        monto_igv   =   total - subtotal;

        amounts.subtotal    =   subtotal;
        amounts.monto_igv   =   monto_igv;
        amounts.total       =   total;

    }

    function storeSale(){
        clearValidationErrors();
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
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
                const token                     =   document.querySelector('input[name="_token"]').value;
                const formData                  =   new FormData();
                const urlStoreSale              =   @json(route('tenant.ventas.comprobante_venta.store'));

                formData.append('lstSale',JSON.stringify(lstSale));
                formData.append('lstPays',JSON.stringify(lstPays));
                formData.append('type_sale',document.querySelector('#type_sale').value);
                formData.append('customer_id',document.querySelector('#customer_id').value);
                formData.append('user_recorder_id',@json(Auth::user()->id));
                formData.append('igv_percentage',@json($company->igv));

                const response  =   await fetch(urlStoreSale, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors);
                    }
                    Swal.close();
                    return;
                }

                if(res.success){

                    toastr.success(res.message,'OPERACIÓN COMPLETADA');

                    const url_open_pdf  = "{{ route('tenant.ventas.comprobante_venta.pdf_voucher', ['id' => '__id__']) }}".replace('__id__', res.data.sale_id);
                    window.open(url_open_pdf, 'Comprobante SISCOM', 'location=1, status=1, scrollbars=1,width=900, height=600');

                    const sale_index        =   @json(route('tenant.ventas.comprobante_venta'));

                    window.location.href    =   sale_index;

                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR VENTA');
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

    async function validateStock(product){
        try {

            const token             =   document.querySelector('input[name="_token"]').value;
            const urlValidateStock = new URL(@json(route('tenant.ventas.comprobante_venta.validateStock')));

            Object.keys(product).forEach(key => {
                urlValidateStock.searchParams.append(key, product[key]);
            });

            const response  =   await fetch(urlValidateStock, {
                                        method: 'GET',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                    });

            const   res =   await response.json();

            return res;

        } catch (error) {
            return {success:false,message:error};
            toastr.error(error,'ERROR EN LA PETICIÓN VALIDAR STOCK');
        }
    }

    // const calculateTotalAmount=(product,type)=>{
    //     type=="add"?totalAmount=  totalAmount+parseFloat(product.sale_price):null;
    //     type=="remove"?totalAmount=  totalAmount-parseFloat(product.sale_price):null;
    // }

    // const calculateIgv=()=>{
    //     igv=Math.round((baseIGV*totalAmount) * 100) / 100;
    // }

    // const calculateOp=()=>{
    //     opgrav= Math.round((totalAmount-igv)*100)/100;
    //     totalAmount= Math.round((igv+opgrav)*100)/100;
    // }

    const addProductToCar = (product,indexProductExists,cant)=>{

        if(indexProductExists === -1){
            product.cant    =   cant;
            lstSale.push(product);
        }else{
            lstSale[indexProductExists].cant++;
        }

    }

    function setSaleTypeCustomer(selectedValue,boleta,customers){
        if (selectedValue === '3') {

                // Inserta HTML en el contenedor 'boleta'
                boleta.innerHTML = `
                <div class="form-floating mt-2">
                    <select class="form-select" id="customerSelect" aria-label="Floating label select example">
                        ${customers
                        .filter(customer => customer.name === 'VARIOS')
                        .map(customer => `<option value="${customer.id}">${customer.name}</option>`)
                        .join('')}
                        <option value="DNI">DNI</option>
                    </select>
                    <label for="customerSelect">Cliente</label>
                </div>
                <div id="dni_container"></div>
                `;

                var customerSelect  =   document.querySelector('#customerSelect');
                var dni_container   =   document.querySelector('#dni_container');

                customerSelect.addEventListener("change",()=>{
                    if (customerSelect.value === "DNI"){

                        dni_container.innerHTML = `
                        <div class="input-group mt-2">
                            <input class="form-control" type="text" id="dni" aria-label="Floating label select example" placeholder="Ingrese número de DNI" maxlength="8">
                            <button class="btn btn-primary" type="button" id="buscarDNI">BUSCAR</button>
                            <button class="btn btn-primary" type="button" id="btn_change" hidden>CAMBIAR</button>
                        </div>
                        <input class="form-control mt-2" type="text" id="dni_name" aria-label="Floating label select example" disabled >
                        `;

                        document.querySelector('#buscarDNI').addEventListener('click', () => {
                        const dni = document.querySelector('#dni').value;

                        if (dni.length !== 8) {
                            Swal.fire({
                                icon: 'error',
                                title: 'DNI Inválido',
                                text: 'El DNI debe tener exactamente 8 dígitos.',
                            });
                            return;
                        }

                        Swal.fire({
                            title: 'Consultar',
                            text: "¿Desea consultar DNI?",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: "#696cff",
                            confirmButtonText: 'Si, Confirmar',
                            cancelButtonText: "No, Cancelar",
                            showLoaderOnConfirm: true,
                            preConfirm: function() {
                                var url = '/landlord/dni/' + dni;
                                return fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                }).then(response => response.json())
                                .catch(error => {
                                    console.error('Error al consultar la API:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Hubo un problema al consultar la API.'
                                    });
                                });
                            },
                            allowOutsideClick: function() {
                                return !Swal.isLoading();
                            }
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    var data = result.value;
                                    if (data.success === false) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: 'DNI inválido o no existe!'
                                        });
                                    } else {
                                        document.getElementById('dni_name').value = data.data.nombre_completo;
                                        document.getElementById('dni').disabled = true;
                                        document.getElementById('buscarDNI').hidden = true;
                                        document.getElementById('btn_change').hidden = false;

                                        document.querySelector('#btn_change').addEventListener('click',()=>{
                                            document.getElementById('btn_change').hidden = true;
                                            document.getElementById('buscarDNI').hidden = false;
                                            document.getElementById('dni').disabled = false;
                                            document.getElementById('dni_name').value = "";
                                            document.getElementById('dni').value = "";
                                        })
                                    }
                                }
                            }).catch(function(error) {
                                console.error('Error al consultar la API:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Hubo un problema al consultar la API.'
                                });
                            });
                        });
                    }else{
                        dni_container.innerHTML = "";
                    }

                })


            } else if(selectedValue === "1") {  //======= FACTURA =======
                boleta.innerHTML = `
                <div class="form-floating mt-2">
                <div class="input-group">
                    <input class="form-control" type="text" id="ruc" aria-label="Floating label select example" placeholder="Ingrese número de RUC" maxlength="11">
                    <button class="btn btn-primary" type="button" id="sunatButton">SUNAT</button>
                    <button class="btn btn-primary" type="button" id="btn_change" hidden>CAMBIAR</button>
                </div>
                <input class="form-control mt-2" type="text" id="ruc_name" aria-label="Floating label select example" disabled >

            </div>
                `;


                document.querySelector('#sunatButton').addEventListener('click', () => {
                    const ruc = document.querySelector('#ruc').value;

                    // Validar que el RUC tenga exactamente 11 dígitos
                    if (ruc.length !== 11) {
                        Swal.fire({
                            icon: 'error',
                            title: 'RUC Inválido',
                            text: 'El RUC debe tener exactamente 11 dígitos.',
                        });
                        return; // Detiene la ejecución si el RUC es inválido
                    }

                    Swal.fire({
                        title: 'Consultar',
                        text: "¿Desea consultar RUC a Sunat?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: "#696cff",
                        confirmButtonText: 'Si, Confirmar',
                        cancelButtonText: "No, Cancelar",
                        showLoaderOnConfirm: true,
                        preConfirm: function() {
                            var url = '/landlord/ruc/' + ruc;
                            return fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }).then(response => response.json())
                            .catch(error => {
                                console.error('Error al consultar la API:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Hubo un problema al consultar la API.'
                                });
                            });
                        },
                        allowOutsideClick: function() {
                            return !Swal.isLoading();
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            var data = result.value;
                            if (data.success === false) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'RUC inválido o no existe!'
                                });
                            } else {
                                document.getElementById('ruc_name').value = data.data.nombre_o_razon_social;
                                document.getElementById('ruc').disabled = true;
                                document.getElementById('sunatButton').hidden = true;
                                document.getElementById('btn_change').hidden = false;

                                document.querySelector('#btn_change').addEventListener('click',()=>{
                                    document.getElementById('btn_change').hidden = true;
                                    document.getElementById('sunatButton').hidden = false;
                                    document.getElementById('ruc').disabled = false;
                                    document.getElementById('ruc_name').value = "";
                                    document.getElementById('ruc').value = "";
                                })
                            }
                        }
                    }).catch(function(error) {
                        console.error('Error al consultar la API:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al consultar la API.'
                        });
                    });
                });
            } else {  //======== NOTA VENTA ==========
                boleta.innerHTML = `
                <div class="form-floating mt-2">
                    <select class="form-select" id="customerSelect" aria-label="Floating label select example">
                        ${customers
                        .filter(customer => customer.name === 'VARIOS')
                        .map(customer => `<option value="${customer.id}">${customer.name}</option>`)
                        .join('')}
                    </select>
                    <label for="customerSelect">Cliente</label>
                </div>
                `;
            }
    }

</script>

<script src="{{asset('assets/js/utils.js')}}"></script>
<script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection

@extends('layouts.template')

@section('title')
    DOCUMENTO DE COMPRA
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @include('utils.spinners.spinner_1')
    @include('purchases.purchase_document.modals.mdl_products')
    @include('purchases.purchase_document.modals.mdl_edit_item')
    @include('utils.modals.suppliers.mdl_create_supplier')



    <div class="card-style settings-card-1 mb-30">
        <div class="title mb-30 d-flex justify-content-between align-items-center">
            <h6>Datos del Documento de Compra <i class="fa-solid fa-toolbox"></i></h6>
        </div>
        <div class="card-body">
            @include('purchases.purchase_document.forms.form_create_purchase_document')
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>

            <div style="display:flex;">
                <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                    <i class="fa-solid fa-door-open"></i> VOLVER
                </button>
                <button class="btn btn-primary" type="submit" form="formStorePurchaseDocument">
                    <i class="fa-solid fa-floppy-disk"></i> REGISTRAR
                </button>
            </div>
        </div>
    </div>
    <!-- end card -->
@endsection

@section('js')
    <script>
        let dtProductos = null;
        let dtCompraDetalle = null;
        const lstPurchaseDocument = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadSelect2();
            loadDataTableProducts();
            iniciarDataTableCompraDetalle();
            events();
        })

        function events() {

            eventsMdlEditItem();
            eventsMdlCreateProveedor();

            document.querySelector('#igv_chk').addEventListener('change', (e) => {
                toastr.clear();
                const estado = e.target.checked;
                const valorIgv = e.target.value;

                if (lstPurchaseDocument.length > 0) {
                    const montos = calcularMontos(lstPurchaseDocument, estado, valorIgv);
                    pintarTableMontos(montos);
                    toastr.info('MONTOS ACTUALIZADOS');
                }
            })

            document.querySelector('#formStorePurchaseDocument').addEventListener('submit', (e) => {
                e.preventDefault();
                const validacion = validacionRegistrarCotizacionCompra();
                if (validacion) {
                    storePurchaseDocument();
                }
            })

            document.addEventListener('click', (e) => {
                if (e.target.closest('.btnVolver')) {
                    const rutaIndex = '{{ route('tenant.compras.documento_compra.index') }}';
                    window.location.href = rutaIndex;
                }

                if (e.target.closest('.btnAgregarProducto')) {

                    toastr.clear();
                    const inputCantidad = document.querySelector('#cantidad');
                    const inputPrecio = document.querySelector('#precio');
                    const validacion = validationAddProduct();

                    if (validacion) {
                        mostrarAnimacion1();

                        const productSelected = structuredClone(product_selected);
                        productSelected.purchase_price = inputPrecio.value;

                        addProduct(productSelected, inputCantidad.value);
                        clearFormSelectProduct();
                        ocultarAnimacion1();
                    }

                }
            })

        }

        function loadSelect2() {
            $('.select2_form').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            $('.select2_form_mdl').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                dropdownParent: $('#mdlProducts')
            });

            $('.select2_form_mdl_supplier').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                dropdownParent: $('#mdlCreateProveedor')
            });
        }

        function loadDataTableProducts() {
            const urlGetProductos = @json(route('tenant.compras.documento_compra.getProducts'));

            dtProductos = new DataTable('#tbl_purchase_documents_products', {
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetProductos,
                    type: 'GET',
                    data: function(d) {
                        d.categoria_id = $('#categoria').val();
                        d.marca_id = $('#marca').val();
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'brand_name',
                        name: 'brand_name'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'stock',
                        name: 'Stock'
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    $(row).css('cursor', 'pointer');

                    $(row).attr('onclick', 'selectProduct(' + data.id + ')');
                },
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
        }

        function iniciarDataTableCompraDetalle() {
            dtCompraDetalle = new DataTable('#tbl_purchase_document_detail', {
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
        }

        function validationAddProduct() {

            if (!product_selected.product_id) {
                toastr.error('DEBE SELECCIONAR UN PRODUCTO!!');
                return false;
            }

            const inputCantidad = document.querySelector('#cantidad');
            if (!inputCantidad.value) {
                toastr.error('DEBE INGRESAR UNA CANTIDAD!!');
                return false;
            }
            if (inputCantidad.value == 0) {
                toastr.error('LA CANTIDAD DEBE SER MAYOR A 0!!');
                return false;
            }

            const inputPrecio = document.querySelector('#precio');
            if (!inputPrecio.value) {
                inputPrecio.focus();
                toastr.error('DEBE INGRESAR UN PRECIO!!');
                return false;
            }

            return true;
        }

        function validacionRegistrarCotizacionCompra() {
            if (lstPurchaseDocument.length === 0) {
                toastr.error('EL DETALLE DE LA NOTA DE INGRESO ESTÁ VACÍO!!!');
                return false;
            }
            return true;
        }

        function addProduct(producto, cantidad) {
            producto.quantity = cantidad;
            producto.total = parseFloat(producto.quantity) * parseFloat(producto.purchase_price);

            const indiceProducto = lstPurchaseDocument.findIndex((p) => {
                return p.product_id == producto.product_id;
            })

            if (indiceProducto !== -1) {
                toastr.error('EL PRODUCTO YA EXISTE EN EL DETALLE');
                return;
            }

            lstPurchaseDocument.push(producto);
            clearTable('tbl_purchase_document_detail');
            destroyDataTable(dtCompraDetalle);
            pintarTableCompraDetalle(lstPurchaseDocument);
            iniciarDataTableCompraDetalle();

            const inputIgv = document.querySelector('#igv_chk');
            const montos = calcularMontos(lstPurchaseDocument, inputIgv.checked, inputIgv.value);
            pintarTableMontos(montos);
            toastr.info('PRODUCTO AGREGADO AL DETALLE');
        }

        function pintarTableMontos(montos) {
            const tdSubtotal = document.querySelector('#tbl_subtotal');
            const tdMontoIgv = document.querySelector('#tbl_monto_igv');
            const tdTotal = document.querySelector('#tbl_total');

            const moneda = document.querySelector('#moneda').value;

            tdSubtotal.textContent = formatCurrency(montos.subtotal, moneda);
            tdMontoIgv.textContent = formatCurrency(montos.monto_igv, moneda);
            tdTotal.textContent = formatCurrency(montos.total, moneda);
        }

        function pintarTableCompraDetalle(lstItems) {
            let filas = ``;
            lstItems.forEach((producto) => {
                filas += `<tr>
                            <th>
                                <div style="display:flex;justify-content:center;gap:5px;">
                                    <i class="fas fa-edit btn btn-warning btnEditItem" data-producto-id="${producto.product_id}"></i>
                                    <i class="fas fa-trash-alt btn btn-danger btnDeleteItem" data-producto-id="${producto.product_id}"></i>
                                </div>
                            </th>
                            <td>${producto.product_name}</td>
                            <td>${producto.category_name}</td>
                            <td>${producto.brand_name}</td>
                            <td>NIU</td>
                            <td>${parseFloat(producto.purchase_price || 0).toFixed(2)}</td>
                            <td>${parseFloat(producto.quantity || 0).toFixed(2)}</td>
                            <td>${parseFloat(producto.total || 0).toFixed(2)}</td>
                        </tr>`;
            })

            const tbody = document.querySelector('#tbl_purchase_document_detail tbody');
            tbody.innerHTML = filas;
        }


        function pintarProductos(lstProductos) {
            let filas = ``;
            lstProductos.forEach((producto) => {
                filas += `<tr style="cursor:pointer;" onclick="selectProduct(${producto.producto_id});">
                            <th>${producto.producto_id}</th>
                            <td>${producto.producto_nombre}</td>
                            <td>${producto.categoria_nombre}</td>
                            <td>${producto.marca_nombre}</td>
                            <td>${producto.producto_stock}</td>
                        </tr>`;
            })

            const tbody = document.querySelector('#table_productos tbody');
            tbody.innerHTML = filas;
        }


        function storePurchaseDocument() {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: "DESEA REGISTRAR EL DOCUMENTO DE COMPRA?",
                text: "Se registrará la compra e ingresará stock!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "SÍ, REGISTRAR!",
                cancelButtonText: "NO, CANCELAR!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    clearValidationErrors('msgError');
                    const token = document.querySelector('input[name="_token"]').value;
                    const formStorePurchaseDocument = document.querySelector('#formStorePurchaseDocument');
                    const formData = new FormData(formStorePurchaseDocument);
                    const urlStorePurchaseDocument = @json(route('tenant.compras.documento_compra.store'));

                    formData.append('lstPurchaseDocument', JSON.stringify(lstPurchaseDocument));
                    formData.append('user_recorder_id', @json($colaborador_registrador->id));
                    formData.append('user_recorder_name', @json($colaborador_registrador->name));
                    formData.append('observation', document.querySelector('#observation').value);
                    formData.append('igv_value', @json($igv));

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Registrando documento de compra...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch(urlStorePurchaseDocument, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        });

                        const res = await response.json();

                        console.log(res);

                        if (response.status === 422) {
                            if ('errors' in res) {
                                pintarErroresValidacion(res.errors);
                            }
                            Swal.close();
                            return;
                        }

                        if (res.success) {
                            const purchase_document_index = @json(route('tenant.compras.documento_compra.index'));
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                            window.location.href = purchase_document_index;
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }


                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR DOCUMENTO DE COMPRA');
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

        function pintarErroresValidacion(objErroresValidacion) {
            for (let clave in objErroresValidacion) {
                const pError = document.querySelector(`.${clave}_error`);
                pError.textContent = objErroresValidacion[clave][0];
            }
        }

        function calcularMontos(lstItems, chkIgv, valorIgv) {
            let subtotal = 0;
            let monto_igv = 0;
            let total = 0;
            valorIgv = parseFloat(valorIgv);

            if (chkIgv) { //======= PRECIOS CON IGV ======

                lstItems.forEach((item) => {
                    total += parseFloat(item.total);
                })

                subtotal = total / ((100 + valorIgv) / 100);
                monto_igv = total - subtotal;
            } else {

                //======= PRECIOS SIN IGV =======
                lstItems.forEach((item) => {
                    subtotal += item.total;
                })

                monto_igv = (valorIgv / 100) * subtotal;
                total = subtotal + monto_igv;
            }

            return {
                subtotal,
                monto_igv,
                total
            };
        }

        function formatCurrency(amount, moneda) {
            let formato = '';

            if (moneda === 'PEN') {
                formato = 'es-PE';
            }
            if (moneda === 'USD') {
                formato = 'en-US';
            }

            return new Intl.NumberFormat(formato, {
                style: 'currency',
                currency: moneda,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }
    </script>
    <script src="{{ asset('assets/js/utils.js') }}"></script>
    <script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection

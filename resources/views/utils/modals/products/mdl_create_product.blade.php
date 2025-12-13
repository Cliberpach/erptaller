<div class="modal fade" id="mdlCreateProduct" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('utils.modals.products.forms.form_create_product')
            </div>
            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="margin-right: 6px;">Cerrar</button>
                            <button class="btn btn-primary btnstoreCustomer" type="submit" form="form_create_product">
                                <i class="fa-solid fa-floppy-disk"></i> Registrar
                            </button>
                        </div>

                        <div class="col-12">
                            <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los
                                campos con (*) son obligatorios</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const productParams = {
        name: null
    }

    function openMdlCreateProduct() {
        document.querySelector('#name_mdlproduct').value = productParams.name;
        $('#mdlCreateProduct').modal('show');
    }

    function eventsMdlProduct() {
        loadSelectMdlProduct();

        document.querySelector('#form_create_product').addEventListener('submit', (e) => {
            e.preventDefault();
            storeProduct(e.target);
        })

        $('#mdlCreateProduct').on('hidden.bs.modal', function() {
            clearMdlCreateProduct();
        });

        document.querySelector('#image_mdlproduct').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            if (file) {

                reader.onload = function(e) {
                    document.getElementById('img_vista_previa').src = e.target.result;
                };

                reader.readAsDataURL(file);
            } else {
                document.getElementById('img_vista_previa').src = @json(asset('assets/img/products/img_default.png'));
            }
        });

        document.addEventListener('click', (e) => {
            //======== LIMPIAR IMAGEN =======
            if (e.target.closest('.btnSetImgDefault')) {
                const inputImgPreview = document.querySelector('#img_vista_previa');
                inputImgPreview.src = @json(asset('assets/img/products/img_default.png'));

                const inputCargarImg = document.querySelector('#image_mdlproduct');
                inputCargarImg.value = '';
            }
        })
    }

    function loadSelectMdlProduct() {

        const categorySelect = document.getElementById('category_id_mdlproduct');
        if (categorySelect && !categorySelect.tomselect) {
            window.categorySelect = new TomSelect(categorySelect, {
                valueField: 'id',
                labelField: 'description',
                searchField: ['description', 'id'],
                placeholder: 'Seleccionar',
                create: false,
                sortField: {
                    field: 'id',
                    direction: 'desc'
                },
                plugins: ['clear_button'],
                render: {
                    option: (item, escape) => `
                        <div>
                            <i class="fas fa-tags" style="margin-right:6px; color:#28a745;"></i>
                            ${escape(item.description)}
                        </div>
                    `,
                    item: (item, escape) => `
                        <div>
                            <i class="fas fa-tags" style="margin-right:6px; color:#28a745;"></i>
                            ${escape(item.description)}
                        </div>
                    `
                }
            });
        }

        const brandSelect = document.getElementById('brand_id_mdlproduct');
        if (brandSelect && !brandSelect.tomselect) {
            window.brandSelect = new TomSelect(brandSelect, {
                valueField: 'id',
                labelField: 'description',
                searchField: ['description', 'id'],
                placeholder: 'Seleccionar',
                create: false,
                sortField: {
                    field: 'id',
                    direction: 'desc'
                },
                plugins: ['clear_button'],
                render: {
                    option: (item, escape) => `
                        <div>
                            <i class="fas fa-bullseye" style="margin-right:6px; color:#0d6efd;"></i>
                            ${escape(item.description)}
                        </div>
                    `,
                    item: (item, escape) => `
                        <div>
                            <i class="fas fa-bullseye" style="margin-right:6px; color:#0d6efd;"></i>
                            ${escape(item.description)}
                        </div>
                    `,
                }
            });
        }
    }

    function storeProduct(formRegistrarProducto) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR EL PRODUCTO?",
            text: "Se creará un nuevo producto!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formRegistrarProducto);
                const urlRegistrarProducto = @json(route('tenant.inventarios.productos.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nuevo producto...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlRegistrarProducto, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'mdl_product_error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        setNewProduct(res.product);
                        $('#mdlCreateProduct').modal('hide');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR PRODUCTO');
                } finally {
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

    function setNewProduct(product) {
        window.productSelect.clear();

        const item = {
            id: product.id,
            text: `${product.name} - (${product.stock})`,
            subtext: `${product.category.name}-${product.brand.name}`,
            sale_price: product.sale_price,
            name: product.name,
            category_name: product.category.name,
            brand_name: product.brand.name
        }

        window.productSelect.addOption(item);
        window.productSelect.setValue(item.id);
    }

    function clearMdlCreateProduct() {
        document.querySelector('#name_mdlproduct').value = '';
        document.querySelector('#description_mdlproduct').value = '';
        document.querySelector('#sale_price_mdlproduct').value = '1';
        document.querySelector('#purchase_price_mdlproduct').value = '1';
        document.querySelector('#stock_mdlproduct').value = '0';
        document.querySelector('#stock_min_mdlproduct').value = '1';
        document.querySelector('#code_factory_mdlproduct').value = '';
        document.querySelector('#code_bar_mdlproduct').value = '';
        window.categorySelect.clear();
        window.brandSelect.clear();
        setText(window.categorySelect, 'REPUESTO');
        setText(window.brandSelect, 'NACIONAL');
        document.querySelector('.btnSetImgDefault').click();
    }
</script>

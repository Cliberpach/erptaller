<!-- Modal product -->
<div class="modal fade" id="mdl-edit-product" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                @include('product.forms.form_edit')

            </div>
            <div class="modal-footer">
                <div class="col-info">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                </div>
                <div class="col-buttons">
                    <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">
                        <i style="margin-right: 3px;" class="fas fa-window-close"></i>Cancelar
                    </button>
                    <button type="submit" form="form-edit-product" class="btn btn-primary btn-save">
                        <i style="margin-right: 3px;" class="fas fa-save"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let pondImgEdit = null;

    const parameters = {
        id: null,
        row: null,
        deleteImg: null
    };

    function eventsMdlEditProduct() {
        loadTomSelectEdit();
        loadFilePoundEdit();

        document.querySelector('#form-edit-product').addEventListener('submit', (e) => {
            e.preventDefault();
            updateProduct(e.target);
        })

        document.querySelector('#image_edit').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            if (file) {

                reader.onload = function(e) {
                    document.getElementById('img_vista_previa_edit').src = e.target.result;
                };

                reader.readAsDataURL(file);
            } else {
                document.getElementById('img_vista_previa_edit').src = @json(asset('assets/img/products/img_default.png'));
            }
        });

        document.addEventListener('click', (e) => {
            //======== LIMPIAR IMAGEN =======
            if (e.target.closest('.btnSetImgEditDefault')) {
                const inputImgPreview = document.querySelector('#img_vista_previa_edit');
                inputImgPreview.src = @json(asset('assets/img/products/img_default.png'));

                const inputCargarImg = document.querySelector('#image_edit');
                inputCargarImg.value = '';

                parameters.deleteImg = 1;
            }
        })
    }

    function loadTomSelectEdit() {
        const categoryEditSelect = document.getElementById('category_id_edit');
        if (categoryEditSelect && !categoryEditSelect.tomselect) {
            window.categoryEditSelect = new TomSelect(categoryEditSelect, {
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

        const brandEditSelect = document.getElementById('brand_id_edit');
        if (brandEditSelect && !brandEditSelect.tomselect) {
            window.brandEditSelect = new TomSelect(brandEditSelect, {
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

        const unitEditSelect = document.getElementById('unit_id_edit');
        if (unitEditSelect && !unitEditSelect.tomselect) {
            window.unitEditSelect = new TomSelect(unitEditSelect, {
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
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined me-2 text-primary"></i>
                                ${escape(item.description)}
                            </div>
                        `,
                    item: (item, escape) => `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined me-2 text-primary"></i>
                                ${escape(item.description)}
                            </div>
                        `
                }
            });
        }

    }

    function openMdlEdit(id) {
        if (!id) {
            toastr.error('FALTA EL PARÁMETRO ID DEL PRODUCTO');
            return;
        }
        const row = getRowById(dtProducts, id);
        if (!row) {
            toastr.error('PRODUCTO NO ENCONTRADO');
            return;
        }

        parameters.id = id;
        parameters.row = row;

        setFormEdit(row);

        $('#mdl-edit-product').modal('show');
    }

    function loadFilePoundEdit() {
        const inputImg = document.querySelector('#image_edit');

        pondImgEdit = FilePond.create(inputImg, {
            allowImagePreview: true,
            imagePreviewHeight: 120,
            imageCropAspectRatio: '1:1',
            styleLayout: 'compact',
            stylePanelAspectRatio: 0.5,
            storeAsFile: true,

            maxFileSize: '2MB',
            acceptedFileTypes: [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/avif'
            ],
            labelFileTypeNotAllowed: 'Formato no permitido',
            labelMaxFileSizeExceeded: 'El archivo supera los 2 MB',
        });
    }

    function setFormEdit(row) {
        document.querySelector('#name_edit').value = row.name;
        document.querySelector('#description_edit').value = row.description;
        document.querySelector('#sale_price_edit').value = row.sale_price;
        document.querySelector('#purchase_price_edit').value = row.purchase_price;
        document.querySelector('#stock_min_edit').value = row.stock_min;
        document.querySelector('#code_factory_edit').value = row.code_factory;
        document.querySelector('#code_bar_edit').value = row.code_bar;

        window.categoryEditSelect.setValue(row.category_id);
        window.brandEditSelect.setValue(row.brand_id);
        window.unitEditSelect.setValue(row.unit_id);

        if (!pondImgEdit) return;
        pondImgEdit.removeFiles();

        if (row.img_route) {
            pondImgEdit.addFile(
                @json(asset('')) + row.img_route
            );
        }
    }

    function updateProduct(formUpdateProduct) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR EL PRODUCTO?",
            text: "Se actualizaran los datos del producto!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando producto...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    clearValidationErrors('msgError');
                    const token = document.querySelector('input[name="_token"]').value;
                    const formData = new FormData(formUpdateProduct);
                    formData.append('deleteImg', parameters.deleteImg);

                    let url = `{{ route('tenant.inventarios.productos.update', ['id' => ':id']) }}`;
                    url = url.replace(':id', parameters.id);

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtProducts.ajax.reload();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        $('#mdl-edit-product').modal('hide');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR PRODUCTO');
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
</script>

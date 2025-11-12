<!-- Modal product -->
<div class="modal fade" id="mdl-edit-product" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr>
            <div class="modal-body">

                @include('product.forms.form_edit')

            </div>
            <hr>
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
    const parameters = {
        id: null,
        row: null,
        deleteImg: null
    };

    function eventsMdlEditProduct() {
        loadSelect2ProductEdit();

        document.querySelector('#form-edit-product').addEventListener('submit', (e) => {
            e.preventDefault();
            updateProduct(e.target);
        })

        document.querySelector('#image_edit').addEventListener('change', function(event) {
            const file      = event.target.files[0];
            const reader    = new FileReader();
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
                const inputImgPreview   = document.querySelector('#img_vista_previa_edit');
                inputImgPreview.src     = @json(asset('assets/img/products/img_default.png'));

                const inputCargarImg = document.querySelector('#image_edit');
                inputCargarImg.value = '';

                parameters.deleteImg = 1;
            }
        })
    }

    function loadSelect2ProductEdit() {
        $('.select2_form_product_edit').select2({
            theme: "bootstrap-5",
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            dropdownParent: $('#mdl-edit-product')
        });
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

    function setFormEdit(row) {
        document.querySelector('#name_edit').value = row.name;
        document.querySelector('#description_edit').value = row.description;
        document.querySelector('#sale_price_edit').value = row.sale_price;
        document.querySelector('#purchase_price_edit').value = row.purchase_price;
        document.querySelector('#stock_min_edit').value = row.stock_min;
        document.querySelector('#code_factory_edit').value = row.code_factory;
        document.querySelector('#code_bar_edit').value = row.code_bar;
        $('#category_id_edit').val(row.category_id).trigger('change');
        $('#brand_id_edit').val(row.brand_id).trigger('change');

        const imgPreview = document.querySelector('#img_vista_previa_edit');
        if (row.img_route) {
            console.log(@json(asset('')) + row.img_route);
            imgPreview.src = @json(asset('')) + row.img_route;
        } else {
            imgPreview.src = "{{ asset('assets/img/products/img_default.png') }}";
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

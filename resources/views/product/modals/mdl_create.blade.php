<!-- Modal product -->
<div class="modal fade" id="mdl-create-product" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr>
            <div class="modal-body">

                @include('product.forms.form_create')

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
                    <button type="submit" form="form-create-product" class="btn btn-primary btn-save">
                        <i style="margin-right: 3px;" class="fas fa-save"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openMdlCreate() {
        $('#mdl-create-product').modal('show');
    }

    function eventsMdlCreateProduct() {
        document.querySelector('#form-create-product').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarProducto(e.target);
        })

        document.querySelector('#image').addEventListener('change', function(event) {
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

                const inputCargarImg = document.querySelector('#image');
                inputCargarImg.value = '';
            }
        })
    }

    function registrarProducto(formRegistrarProducto) {
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

                    console.log(res);

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
                        $('#mdl-create-product').modal('hide');
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
</script>

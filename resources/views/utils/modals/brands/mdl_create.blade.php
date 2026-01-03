<div class="modal fade" id="mdlCreateBrand" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Marca</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('utils.modals.brands.forms.form_create_brand')
            </div>
            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="margin-right: 6px;">Cerrar</button>
                            <button class="btn btn-primary btnstoreCustomer" type="submit" form="form_create_brand">
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
    const brandParams = {
        name: null
    }

    function openMdlCreateBrand() {
        document.querySelector('#name_mdlbrand').value = brandParams.name;
        $('#mdlCreateBrand').modal('show');
    }

    function eventsMdlBrand() {

        document.querySelector('#form_create_brand').addEventListener('submit', (e) => {
            e.preventDefault();
            storeBrand(e.target);
        })

        $('#mdlCreateBrand').on('hidden.bs.modal', function() {
            clearMdlCreateBrand();
        });
    }

    function storeBrand(formStoreBrand) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR LA MARCA?",
            text: "Se creará una nueva marca!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formStoreBrand);
                const urlStore = @json(route('tenant.inventarios.productos.marca.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando marca...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlStore, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'mdlbrand_error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        setNewBrand(res.brand);
                        $('#mdlCreateBrand').modal('hide');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR MARCA');
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

    function setNewBrand(_item) {
        window.brandSelect.clear();

        const item = {
            id: _item.id,
            description: _item.name
        }

        window.brandSelect.addOption(item);
        window.brandSelect.setValue(item.id);
    }

    function clearMdlCreateBrand() {
        document.querySelector('#name_mdlbrand').value = '';
    }
</script>

<!-- Modal -->
<div class="modal fade" id="mdl-create-brand" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Registrar Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                @include('brand.forms.form_create')

            </div>
            <div class="modal-footer">
                <div class="col-info">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button form="formCreateBrand" type="submit" class="btn btn-primary">Guardar</button>
            </div>

        </div>
    </div>
</div>

<script>

    function openMdlCreate() {
        $('#mdl-create-brand').modal('show');
    }

    function eventsMdlCreate() {

        document.querySelector('#mdl-create-brand').addEventListener('submit', (e) => {
            e.preventDefault();
            storeBrand(e.target);
        })

        $('#mdl-create-brand').on('hidden.bs.modal', function(e) {
            const formCreateBrand = document.querySelector('#formCreateBrand');
            formCreateBrand.reset();
            clearValidationErrors('msgError');
        });

    }

    function storeBrand(form) {
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
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nueva marca...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    clearValidationErrors('msgError');
                    const token = document.querySelector('input[name="_token"]').value;
                    const formData = new FormData(form);
                    const urlstoreBrand = @json(route('tenant.inventarios.productos.marca.store'));

                    const response = await fetch(urlstoreBrand, {
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
                        dtBrands.ajax.reload();
                        $('#mdl-create-brand').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR MARCA');
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

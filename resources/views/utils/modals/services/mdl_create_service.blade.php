<div class="modal fade" id="mdlCreateService" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Servicio</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('utils.modals.services.forms.form_create_service')
            </div>
            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="margin-right: 6px;">Cerrar</button>
                            <button class="btn btn-primary btnstoreCustomer" type="submit" form="form_create_service">
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
    const serviceParams = {
        name: null
    }

    function openMdlCreateService() {
        document.querySelector('#name_mdlservice').value = serviceParams.name;
        $('#mdlCreateService').modal('show');
    }

    function eventsMdlService() {

        document.querySelector('#form_create_service').addEventListener('submit', (e) => {
            e.preventDefault();
            storeService(e.target);
        })

        $('#mdlCreateService').on('hidden.bs.modal', function() {
            clearMdlCreateService();
        });
    }

    function storeService(formStoreService) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR EL SERVICIO?",
            text: "Se creará un nuevo servicio!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formStoreService);
                const urlStore = @json(route('tenant.taller.servicios.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nuevo servicio...',
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
                            paintValidationErrors(res.errors, 'mdlservice_error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        setNewService(res.service);
                        $('#mdlCreateService').modal('hide');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR SERVICIO');
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

    function setNewService(service) {
        window.serviceSelect.clear();

        const item = {
            id: service.id,
            text: `${service.name}`,
            subtext: `S/ ${formatSoles(service.price)}`,
            sale_price: service.price,
            name: service.name,
        }

        window.serviceSelect.addOption(item);
        window.serviceSelect.setValue(item.id);
    }

    function clearMdlCreateService() {
        document.querySelector('#name_mdlservice').value = '';
        document.querySelector('#price_mdlservice').value = '';
        document.querySelector('#description_mdlservice').value = '';
    }
</script>

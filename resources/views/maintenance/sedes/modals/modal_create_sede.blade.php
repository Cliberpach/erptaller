<div class="modal fade" id="mdlCreateSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Registrar Sede</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('maintenance.sedes.forms.form_create_sede')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary btnRegistrarSede" type="submit" form="formRegistrarSede">
                    <i class="fa-solid fa-floppy-disk"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlCreateSede() {
        document.querySelector('#formRegistrarSede').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarSede();
        });

        $('#mdlCreateSede').on('hidden.bs.modal', function () {
            document.querySelector('#formRegistrarSede').reset();
            clearValidationErrors('msgError');
        });
    }

    function openMdlNuevaSede() {
        $('#mdlCreateSede').modal('show');
    }

    function registrarSede() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "¿DESEA REGISTRAR LA SEDE?",
            text: "Se creará una nueva sede!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(document.querySelector('#formRegistrarSede'));
                const url = @json(route('tenant.mantenimientos.sedes.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nueva sede...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
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
                        dtSedes.draw();
                        $('#mdlCreateSede').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }
                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR SEDE');
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

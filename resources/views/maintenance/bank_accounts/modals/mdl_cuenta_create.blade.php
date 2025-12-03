<div class="modal fade" id="mdlCreateCuenta" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('maintenance.bank_accounts.forms.form_create')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" form="formRegisterAccount">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>

        </div>
    </div>
</div>


<script>
    function eventsMdlCreateCuenta() {
        document.querySelector('#formRegisterAccount').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarCuenta();
        })

        $('#mdlCreateCuenta').on('hidden.bs.modal', function(e) {
            const formRegisterAccount = document.querySelector('#formRegisterAccount');
            formRegisterAccount.reset();
            clearValidationErrors('msgError');
        });

        $('#mdlCreateCuenta').on('shown.bs.modal', function(e) {
            document.querySelector('#titular').focus();
        });

    }

    function openMdlNuevoMetodoPago() {
        $('#mdlCreateCuenta').modal('show');
    }

    function registrarCuenta() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR LA CUENTA BANCARIA?",
            text: "Se creará una nueva cuenta bancaria!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formRegisterAccount = document.querySelector('#formRegisterAccount');
                const formData = new FormData(formRegisterAccount);
                const urlRegistrarCuenta = @json(route('tenant.mantenimiento.cuentas.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nueva cuenta bancaria...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlRegistrarCuenta, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            pintarErroresValidacion(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtCuentas.draw();
                        $('#mdlCreateCuenta').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR CUENTA BANCARIA');
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

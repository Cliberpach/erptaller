<div class="modal fade" id="mdlEditCuenta" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Cuenta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                @include('maintenance.bank_accounts.forms.form_edit')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit" form="formUpdateAccount">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    let rowEditar = null;

    function eventsMdlEditCuenta() {
        document.querySelector('#formUpdateAccount').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarCuenta();
        })

        $('#mdlEditCuenta').on('hidden.bs.modal', function(e) {
            const formUpdateAccount = document.querySelector('#formUpdateAccount');
            formUpdateAccount.reset();
            clearValidationErrors('msgError_edit');
        });

        $('#mdlEditCuenta').on('shown.bs.modal', function(e) {
            document.querySelector('#holder_edit').focus();
        });
    }

    function openMdlEditCuenta(id) {
        rowEditar = getRowById(dtCuentas, id);

        if (!rowEditar) {
            toastr.error('NO SE ENCONTRÓ EL MÉTODO PAGO EN EL DATATABLE');
            return;
        }

        //======== SETTEANDO DATA ========
        document.querySelector('#holder_edit').value = rowEditar.holder;
        $('#currency_edit').val(rowEditar.currency).trigger('change');
        document.querySelector('#account_number_edit').value = rowEditar.account_number;
        document.querySelector('#cci_edit').value = rowEditar.cci;
        document.querySelector('#phone_edit').value = rowEditar.phone;
        $('#bank_id_edit').val(rowEditar.bank_id).trigger('change');

        $('#mdlEditCuenta').modal('show');
    }

    function actualizarCuenta() {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA LA CUENTA BANCARIA?",
            text: `Cuenta: ${rowEditar.account_number}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError_edit');
                const token = document.querySelector('input[name="_token"]').value;
                const formUpdateAccount = document.querySelector('#formUpdateAccount');
                const formData = new FormData(formUpdateAccount);
                let urlUpdate = `{{ route('tenant.mantenimiento.cuentas.update', ['id' => ':id']) }}`;
                urlUpdate = urlUpdate.replace(':id', rowEditar.id);

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando cuenta bancaria...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlUpdate, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    });

                    const res = await response.json();

                    console.log(res);

                    if (response.status === 422) {
                        if ('errors' in res) {
                            pintarErroresValidacion(res.errors, 'edit_error')
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtCuentas.draw();
                        $('#mdlEditCuenta').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR CUENTA BANCARIA');
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

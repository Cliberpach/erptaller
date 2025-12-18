<div class="modal fade" id="mdlOpenCash" tabindex="-1" aria-labelledby="mdlOpenCashLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Aperturar Caja </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 colCaja">
                            @include('cash.petty-cash-book.forms.form_open_cash')
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="modal-footer">
                <div class="row footer-row">
                    <div class="col-5 col-info">
                        <i class="fas fa-info-circle"></i>
                        <p>Los campos marcados con asterisco (*) son obligatorios.</p>
                    </div>
                    <div class="col-7 col-botones">
                        <button type="button" class="btn btn-secondary btnCancelar" data-bs-dismiss="modal">
                            <i class="fas fa-window-close"></i>Cancelar
                        </button>
                        <button form="form-open-cash" type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>Guardar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function openMdlOpenCash() {
        $('#mdlOpenCash').modal('show');
    }

    function eventsMdlOpenCash() {
        document.querySelector('#form-open-cash').addEventListener('submit', (e) => {
            e.preventDefault();
            openPettyCash(e.target);
        })
    }

    function openPettyCash(formOpenCash) {
        const id = cashesAvailableSelect.getValue();
        const item = cashesAvailableSelect.options[id];

        console.log(item);

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea aperturar la caja?",
            html: `
            <div style="text-align: center; margin-top: 10px;">
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>Nombre:</strong> ${item.name}
                </p>
            </div>
        `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Aperturando caja...",
                    text: "Por favor, espere",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    toastr.clear();

                    const formData = new FormData(formOpenCash);
                    const res = await axios.post(route('tenant.movimientos_caja.abrirCaja'), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        $('#mdlOpenCash').modal('hide');
                        dtCash.ajax.reload();
                    } else {
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            Swal.close();
                            toastr.error('Errores de validación encontrados.', 'ERROR DE VALIDACIÓN');
                        } else {
                            Swal.close();
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        Swal.close();
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        Swal.close();
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                } finally {
                    Swal.close();
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>

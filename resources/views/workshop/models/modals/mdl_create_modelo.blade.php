<div class="modal fade" id="modal_crear_color" tabindex="-1" aria-labelledby="modal_crear_color_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="modal_crear_color_label">Modelo</h5>
                    <small class="text-muted">Crear nuevo modelo.</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('workshop.models.forms.form_create_modelo')
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-warning small">
                    <i class="fa fa-exclamation-circle"></i>
                    Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.
                </div>
                <div class="mt-sm-0 mt-2 text-end">
                    <button type="submit" form="form_create_color" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

<script>
    function openMdlCreateMarca() {
        const modal = new bootstrap.Modal(document.getElementById('modal_crear_color'));
        modal.show();
    }

    function eventsMdlCreateMarca() {
        document.querySelector('#form_create_color').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarModelo(e.target);
        })
    }

    function registrarModelo(formCreateColor) {

        const modeloNombre = document.querySelector('#description').value;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea registrar el modelo?",
            html: `
            <div style="text-align: center; margin-top: 10px;">
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>Nombre:</strong> ${modeloNombre}
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
                    title: "Registrando modelo...",
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

                    const formData = new FormData(formCreateColor);
                    const res = await axios.post(route('tenant.taller.modelos.store'), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                        $('#modal_crear_color').modal('hide');
                        dtMarcas.ajax.reload();
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

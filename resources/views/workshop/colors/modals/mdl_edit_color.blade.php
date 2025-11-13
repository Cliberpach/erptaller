<div class="modal fade" id="modal_edit_color" tabindex="-1" aria-labelledby="modal_edit_color_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="modal_edit_color_label">Color</h5>
                    <small class="text-muted">Editar color.</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('workshop.colors.forms.form_edit_color')
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-warning small">
                    <i class="fa fa-exclamation-circle"></i>
                    Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.
                </div>
                <div class="mt-sm-0 mt-2 text-end">
                    <button type="submit" form="form_edit_color" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
    const parametrosMdlEditColor = {
        id: null
    };

    async function openMdlEditColor(colorId) {
        parametrosMdlEditColor.id = colorId;

        const data = await getColor(colorId);
        if (!data) return;
        pintarColorEdit(data);
        $('#modal_edit_color').modal('show');
    }

    function eventsMdlEditColor() {
        document.querySelector('#form_edit_color').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarColor(e.target);
        })
    }

    function pintarColorEdit(color) {
        document.querySelector('#description_edit').value = color.description;
        document.querySelector('#codigo_edit').value = color.codigo ? color.codigo : '#ffffff';
    }

    async function getColor(colorId) {
        try {
            mostrarAnimacion1();
            toastr.clear();
            const res = await axios.get(route('tenant.taller.colores.getColor', colorId));
            console.log(res);
            if (res.data.success) {
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                return res.data.data;
            } else {
                return null;
                toastr.error(res.data.message);
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER COLOR');
            return null;
        } finally {
            ocultarAnimacion1();
        }
    }

    function actualizarColor(formActualizarColor) {

        const colorNombre = document.querySelector('#description_edit').value;
        const codigoColor = document.querySelector('#codigo_edit').value;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea actualizar el color?",
            html: `
            <div style="text-align: center; margin-top: 10px;">
                <p style="font-size: 16px; margin-bottom: 10px;">
                    <strong>Nombre:</strong> ${colorNombre}
                </p>
                <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                    <div
                        style="
                            width: 60px;
                            height: 60px;
                            background-color: ${codigoColor};
                            border: 2px solid #333;
                            border-radius: 8px;
                            margin-bottom: 8px;
                        ">
                    </div>
                    <span style="font-weight: bold;">${codigoColor}</span>
                </div>
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
                    title: "Actualizando color...",
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

                    const formData = new FormData(formActualizarColor);
                    formData.append('_method', 'PUT');

                    const res = await axios.post(route('tenant.taller.colores.update', parametrosMdlEditColor
                        .id), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                        $('#modal_edit_color').modal('hide');
                        dtColores.ajax.reload();
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

<!-- Modal para visualizar orden de trabajo -->
<div class="modal fade" id="mdl_alert_order" tabindex="-1" aria-labelledby="mdl_alert_order_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_alert_order_label">Orden de Trabajo #<span
                            id="alert_order_id"></span></h5>
                    <small class="text-muted">Registrar Alerta</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                @include('workshop.work_orders.forms.form_alert')
            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-end flex-wrap">
                <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button type="submit" form="form_alert" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>

        </div>
    </div>
</div>


<style>
    .swal2-container {
        z-index: 9999999;
    }

    .card.hvr-float-shadow {
        display: flex;
    }
</style>

<script>
    const paramsMdlAlertOrder = {
        id: null,
    };

    async function openMdlAlertOrder(id) {
        paramsMdlAlertOrder.id = id;
        paintOrderMaster();
        const modal = new bootstrap.Modal(document.getElementById('mdl_alert_order'));
        modal.show();
    }

    function eventsMdlAlert() {
        document.querySelector('#form_alert').addEventListener('submit', (e) => {
            e.preventDefault();
            storeAlert(e.target);
        })
    }


    function paintOrderMaster() {
        const row = getRowById(dtOrders, paramsMdlAlertOrder.id);
        document.getElementById('alert_order_id').innerText = row.id;
    }

    function storeAlert(formAlert) {

        Swal.fire({
            title: "Desea registrar la alerta?",
            html: `Se registrarán cambios`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Registrando alerta...",
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

                    const formData = new FormData(formAlert);
                    formData.append('object_id', paramsMdlAlertOrder.id);
                    formData.append('type_object', 'ORDEN_TRABAJO');

                    const res = await axios.post(route('tenant.taller.ordenes_trabajo.alertStore'),
                        formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                        $('#mdl_alert_order').modal('hide');
                        dtOrders.ajax.reload();
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
                Swal.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>

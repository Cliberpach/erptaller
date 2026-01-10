<!-- Modal para Crear/Editar Evento -->
<div class="modal fade" id="mdlEditEvent" tabindex="-1" aria-labelledby="mdlEditEventLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlEditEventLabel">
                    <i class="fa-solid fa-calendar-plus me-2"></i>
                    <span id="modalTitle">Editar Evento</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('workshop.appointments.forms.form_edit_event')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                </button>
                <button type="submit" form="formCreateEvent" class="btn btn-primary" id="saveEventBtn">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

@push('js-script')
    <script>
        let currentEvent = null;
        const modal = new bootstrap.Modal(document.getElementById('mdlEditEvent'));
        const eventTitle = document.getElementById('name_event');
        const eventCalendar = document.getElementById('type_calendar_event');
        const eventAllDay = document.getElementById('all_day_event');
        const eventStartDate = document.getElementById('start_date_event');
        const eventStartTime = document.getElementById('start_time_event');
        const eventEndDate = document.getElementById('end_date_event');
        const eventEndTime = document.getElementById('end_time_event');
        const eventLocation = document.getElementById('location_event');
        const eventDescription = document.getElementById('description_event');
        const saveEventBtn = document.getElementById('saveEventBtn');
        const deleteEventBtn = document.getElementById('deleteEventBtn');
        const modalTitle = document.getElementById('modalTitle');
        const startTimeContainer = document.getElementById('startTimeContainer');
        const endTimeContainer = document.getElementById('endTimeContainer');

        function eventsMdlCreateEvent() {
            document.querySelector('#formCreateEvent').addEventListener('submit', (e) => {
                e.preventDefault();
                storeEvent(e.target);
            })
        }

        function resetForm() {
            document.getElementById('formCreateEvent').reset();
            currentEvent = null;
            modalTitle.textContent = 'Crear Evento';
            deleteEventBtn.style.display = 'none';
            startTimeContainer.style.display = 'block';
            endTimeContainer.style.display = 'block';
        }


        function openMdlCreateEvent(info) {
            resetForm();
            setFormData(info);

            const now = new Date();
            const endTime = new Date(now.getTime() + 60 * 60 * 1000); // +1 hora




            // Trigger del evento allDay para mostrar/ocultar campos
            eventAllDay.dispatchEvent(new Event('change'));

            modal.show();
        }

        function setFormData(info) {
            console.log('✅ Clic en celda vacía:', info);
            console.log('FECHA INICIO', info.start);
            console.log('FECHA FIN', info.end);

            // inputs
            eventStartDate.value = formatDateInput(info.start);
            eventEndDate.value = formatDateInput(info.end);

            eventStartTime.value = formatTimeInput(info.start);
            eventEndTime.value = formatTimeInput(info.end);

            eventCalendar.value = 'cal1';
        }


        function storeEvent(formCreate) {

            const name = document.querySelector('#name_event').value;

            Swal.fire({
                title: "Desea registrar la cita?",
                html: `
                <div style="text-align: center; margin-top: 10px;">
                    <p style="font-size: 16px; margin-bottom: 10px;">
                        <strong>Nombre:</strong> ${name}
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
                        title: "Registrando cita...",
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

                        clearValidationErrors('msgError');

                        const formData = new FormData(formCreate);
                        const res = await axios.post(route('tenant.taller.citas.store'), formData);

                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                            $('#mdlEditEvent').modal('hide');
                            loadEventsFromServer();
                        } else {
                            Swal.close();
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }

                    } catch (error) {
                        if (error.response) {
                            if (error.response.status === 422) {
                                const errors = error.response.data.errors;
                                paintValidationErrors(errors, 'event_error');
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
@endpush

<!-- Modal para Ver Detalles del Evento -->
<div class="modal fade" id="mdlShowEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="fa-solid fa-calendar-check me-2"></i>
                    <span id="detailTitle"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('workshop.appointments.lists.lst_show_event')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnEditEvent">
                    <i class="fa-solid fa-pen me-2"></i>Editar
                </button>
                <button type="button" class="btn btn-danger" id="btnDeleteEvent">
                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>


@push('js-script')
    <script>
        const paramsMdlShowEvent = {
            event: null
        }

        function eventsMdlShowEvent() {
            document.querySelector('#btnEditEvent').addEventListener('click', (e) => {
                openMdlEditEvent(paramsMdlShowEvent.event);
            })


            document.querySelector('#btnDeleteEvent').addEventListener('click', (e) => {
                destroyAppointment(paramsMdlShowEvent.event);
            })
        }

        function openMdlShowEvent(event) {

            paramsMdlShowEvent.event = event;
            setFormShowEvent(event);

            const detailModal = new bootstrap.Modal(
                document.getElementById('mdlShowEvent')
            );

            detailModal.show();
        }

        function setFormShowEvent(event) {
            // Helper: fecha y hora
            const formatDateTime = (date) =>
                new Date(date).toLocaleString('es-PE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

            // Helper: fecha simple
            const formatDate = (date) =>
                new Date(date + 'T00:00:00').toLocaleDateString('es-PE');

            // ======================
            // Setear datos
            // ======================
            document.getElementById('detailId').textContent = event.id;
            document.getElementById('detailName').textContent = event.name;

            document.getElementById('detailCustomer').textContent =
                event.customer_name ?? '-';

            document.getElementById('detailDocument').textContent =
                `${event.customer_type_document_abbreviation ?? ''} ${event.customer_document_number ?? ''}`.trim() || '-';

            document.getElementById('detailVehicle').textContent =
                event.plate ? `Placa: ${event.plate}` : '-';

            document.getElementById('detailStart').textContent =
                `${formatDate(event.start_date)} ${event.start_time}`;

            document.getElementById('detailEnd').textContent =
                `${formatDate(event.end_date)} ${event.end_time}`;

            document.getElementById('detailLocation').textContent =
                event.location ?? '-';

            document.getElementById('detailDescription').textContent =
                event.description ?? '-';

            document.getElementById('detailCreator').textContent =
                event.creator_user_name ?? '-';

            document.getElementById('detailCreatedAt').textContent =
                formatDateTime(event.created_at);

            // ======================
            // Tipo calendario
            // ======================
            const calendarBadge = document.getElementById('detailCalendar');
            calendarBadge.textContent = event.type_calendar;
            calendarBadge.className = 'badge';

            switch (event.type_calendar) {
                case 'TRABAJO':
                    calendarBadge.classList.add('bg-primary');
                    break;
                case 'PERSONAL':
                    calendarBadge.classList.add('bg-warning', 'text-dark');
                    break;
                default:
                    calendarBadge.classList.add('bg-secondary');
            }

            // ======================
            // Estado
            // ======================
            const statusBadge = document.getElementById('detailStatus');
            statusBadge.textContent = event.status;
            statusBadge.className = 'badge';

            statusBadge.classList.add(
                event.status === 'ACTIVO' ?
                'bg-success' :
                'bg-secondary'
            );

        }

        function destroyAppointment(event) {
            toastr.clear();

            let message = `
                    <div class="text-center">

                        <h5 class="fw-bold mb-3">
                            <i class="fa-solid fa-calendar-check text-primary me-2"></i>
                            ${event.name}
                        </h5>

                        <div class="mb-2">
                            <i class="fa-solid fa-user text-info me-2"></i>
                            <strong>Cliente:</strong> ${event.customer_name ?? '-'}
                        </div>

                        <div class="mb-2">
                            <i class="fa-solid fa-calendar-day text-success me-2"></i>
                            <strong>Inicio:</strong>
                            ${event.start_date} &nbsp;
                            <i class="fa-solid fa-clock text-secondary me-1"></i>
                            ${event.start_time}
                        </div>

                        <div class="mb-2">
                            <i class="fa-solid fa-calendar-xmark text-danger me-2"></i>
                            <strong>Fin:</strong>
                            ${event.end_date} &nbsp;
                            <i class="fa-solid fa-clock text-secondary me-1"></i>
                            ${event.end_time}
                        </div>

                        <hr class="my-3">

                        <p class="text-danger fw-semibold mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Esta acción no se puede deshacer
                        </p>

                    </div>
                `;

            Swal.fire({
                title: 'DESEA ELIMINAR LA CITA',
                html: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Eliminando Cita...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {

                        let url = `{{ route('tenant.taller.citas.destroy', ['id' => ':id']) }}`;
                        url = url.replace(':id', event.id);

                        const res = await axios.delete(url);

                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            $('#mdlShowEvent').modal('hide');
                            loadEventsFromServer();

                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CITA');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CITA');
                    } finally {
                        Swal.close();
                    }

                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
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

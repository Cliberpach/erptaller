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
                <button type="button" class="btn btn-danger" id="deleteEventFromDetailBtn">
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
    </script>
@endpush

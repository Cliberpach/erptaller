@extends('layouts.template')

@section('title')
    Citas
@endsection

@push('js-head')
    @vite(['resources/js/libs/calendar.js'])
@endpush

@section('content')
    @include('utils.modals.vehicles.mdl_create_vehicle')
    @include('utils.modals.customer.mdl_create_customer')
    @include('workshop.quotes.modals.mdl_show_quote')
    @include('workshop.appointments.modals.mdl_create_event')
    @include('workshop.appointments.modals.mdl_show_event')
    @include('workshop.appointments.modals.mdl_edit_event')
    <div class="card overflow-hidden">
        <div class="card-header">
            <!-- Fila 1: Título + Botón -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h6 class="card-title mb-0">LISTA DE CITAS</h6>
                </div>

                {{-- <div class="col-lg-6 col-md-6 col-sm-12 text-md-right mt-md-0 mt-2" style="text-align:end;">
                    <a href="{{ route('tenant.taller.cotizaciones.create') }}" class="btn btn-primary text-white">
                        <i class="fas fa-plus-circle"></i> Nuevo
                    </a>
                </div> --}}
            </div>

            <!-- Fila 2: Filtro Cliente -->
            <div class="row">

                <!-- Cliente -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-user text-primary mr-1"></i> Cliente:
                    </label>
                    <select class="form-control" id="filter_client_id" name="filter_client_id">
                        <option value="">Seleccione un cliente</option>
                    </select>
                    <p class="filter_client_id_error msgError mb-0"></p>
                </div>

                <!-- Fecha Inicio -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-success mr-1"></i> Fecha inicio:
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>

                <!-- Fecha Fin -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-check text-danger mr-1"></i> Fecha fin:
                    </label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>

                <!-- Estado -->
                {{-- <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks text-info mr-1"></i> Estado:
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todo</option>
                        <option selected value="ACTIVO">Activo</option>
                        <option value="FINALIZADO">Finalizado</option>
                    </select>
                </div> --}}

                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 mb-2 text-end">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-block" onclick="filterData();">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>

            </div>
        </div>
        <div class="card-body p-0 pb-2">
            <div class="row">
                <div class="col-12">
                    <div id="calendar" style="height: 800px"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

@section('js')
    <script>
        let dtQuotes = null;
        let calendar = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadCalendar();
            loadEventsFromServer();
            events();
        })

        function loadCalendar() {
            calendar = new Calendar('#calendar', {
                defaultView: 'week',
                useFormPopup: false,
                useDetailPopup: false,
                isReadOnly: false,
                week: {
                    showTimezoneCollapseButton: false,
                    timezonesCollapsed: false,
                    eventView: true,
                    taskView: false,
                    collapseDuplicateEvents: {
                        getDuplicateStates: () => false,
                        collapse: false,
                    },
                    dayNames: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                },
                month: {
                    dayNames: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    visibleWeeksCount: 0,
                    workweek: false,
                    narrowWeekend: false,
                    startDayOfWeek: 1, // 0: Domingo, 1: Lunes
                },
                template: {
                    popupSave: function() {
                        return 'Add Event';
                    },
                    time(event) {
                        const {
                            raw,
                            title
                        } = event;

                        return `
                                <div style="
                                    color: white;
                                    font-size: 12px;
                                    line-height: 1.3;
                                ">
                                    <div><strong>${raw.item.plate}</strong></div>
                                    <div>${raw.item.customer_name}</div>
                                </div>
                            `;
                    },
                    /*time(event) {
                        const {
                            start,
                            end,
                            title
                        } = event;
                        return `<span style="color: white;">${formatTime(start)}~${formatTime(end)} ${title}</span>`;
                    },*/
                    allday(event) {
                        return `<span style="color: gray;">${event.title}</span>`;
                    },
                    milestone(event) {
                        return `<span>${event.title}</span>`; // Personalizar milestone si lo necesitas
                    },
                    task(event) {
                        return `<span>${event.title}</span>`; // Personalizar task si lo necesitas
                    },
                },
                calendars: [{
                        id: 'cal1',
                        name: 'PERSONAL',
                        backgroundColor: '#03bd9e',
                    },
                    {
                        id: 'cal2',
                        name: 'TRABAJO',
                        backgroundColor: '#00a9ff',
                    },
                ],
            });
        }

        function events() {

            eventsMdlCreateEvent();
            eventsMdlShowEvent();
            eventsMdlEditEvent();
            eventsMdlCreateCustomer();
            eventsMdlVehicle();

            calendar.on('beforeCreateEvent', (event) => {
                console.log('Creando evento:', event);

                calendar.createEvents([{
                    id: String(Date.now()),
                    calendarId: event.calendarId || 'cal1',
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    isAllDay: event.isAllDay,
                }]);

            });

            calendar.createEvents([{
                id: '1',
                calendarId: 'cal1',
                title: 'timed event',
                body: 'TOAST UI Calendar',
                start: '2026-01-06T10:00:00',
                end: '2026-01-06T11:00:00',
                location: 'Meeting Room A',
                attendees: ['A', 'B', 'C'],
                category: 'time',
                state: 'Free',
                isReadOnly: true,
                color: '#fff',
                backgroundColor: '#ccc',
                customStyle: {
                    fontStyle: 'italic',
                    fontSize: '15px',
                },
            }, ]);

            const timedEvent = calendar.getEvent('1', 'cal1');
            calendar.on('clickEvent', (info) => {
                console.log('HAS CLICKEADO EN UN EVENT', info.event);
                console.log(info.event.raw);

                openMdlShowEvent(info.event.raw.item);
            });

            calendar.on('selectDateTime', (info) => {
                openMdlCreateEvent(info);
            });

        }

        function formatTime(date) {
            return new Date(date.toDate()).toLocaleTimeString('es-PE', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function formatTimeInput(date) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${hours}:${minutes}`;
        }


        async function loadEventsFromServer() {
            try {
                mostrarAnimacion1();
                const response = await axios.get(route('tenant.taller.citas.getEvents'));

                calendar.clear();

                if (response.data && response.data.length > 0) {
                    calendar.createEvents(response.data);
                    toastr.info('EVENTOS CARGADOS');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN CARGAR CITAS');
            } finally {
                ocultarAnimacion1();
            }
        }


        function eliminar(id) {
            const fila = getRowById(dtQuotes, id);
            const htmlVehicleInfo = `
            <div class="card shadow-sm border-0">
                <div class="card-body p-2" style="font-size: 1.2rem;">

                    <div class="mb-1">
                        <i class="fas fa-user text-primary me-1 small"></i>
                        <span class="fw-bold small">Cliente:</span><br>
                        <span class="text-muted small">${fila.customer_name}</span>
                    </div>

                    <div class="mb-1">
                        <i class="fas fa-car text-info me-1 small"></i>
                        <span class="fw-bold small">Placa:</span><br>
                        <span class="text-muted small">${fila.plate}</span>
                    </div>

                    <div class="mb-1">
                        <i class="fas fa-flag text-success me-1 small"></i>
                        <span class="fw-bold small">Marca:</span><br>
                        <span class="text-muted small">${fila.brand_name}</span>
                    </div>

                    <div class="mb-1">
                        <i class="fas fa-tag text-warning me-1 small"></i>
                        <span class="fw-bold small">Modelo:</span><br>
                        <span class="text-muted small">${fila.model_name}</span>
                    </div>

                    <div class="mb-1">
                        <i class="fas fa-calendar-alt text-primary me-1 small"></i>
                        <span class="fw-bold small">Año:</span><br>
                        <span class="text-muted small">${fila.year_name}</span>
                    </div>

                    <div class="mb-0">
                        <i class="fas fa-palette text-danger me-1 small"></i>
                        <span class="fw-bold small">Color:</span><br>
                        <span class="text-muted small">${fila.color_name}</span>
                    </div>

                </div>
            </div>
        `;


            Swal.fire({
                title: '¿Desea eliminar la cotización?',
                html: `${htmlVehicleInfo}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando vehículo...',
                        html: `
                            <div style="display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                <i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                                <p style="margin:0; font-weight:600;">Por favor, espere un momento</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });

                    try {
                        const res = await axios.delete(route('tenant.taller.vehiculos.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtQuotes.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR COTIZACIÓN');
                    } finally {
                        Swal.close();
                    }

                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalWithBootstrapButtons.fire({
                        title: 'Cancelado',
                        text: 'La solicitud ha sido cancelada.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }

        function filterData() {
            const startDate = document.getElementById('start_date')?.value;
            const endDate = document.getElementById('end_date')?.value;

            if (startDate && endDate) {
                if (startDate > endDate) {
                    toastr.error(
                        'La fecha inicio no puede ser mayor que la fecha fin',
                        'Fechas inválidas'
                    );
                    return;
                }
            }

            dtQuotes.ajax.reload();
        }
    </script>
@endsection

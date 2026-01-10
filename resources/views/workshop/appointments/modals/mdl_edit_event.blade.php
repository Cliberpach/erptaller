<!-- Modal para Crear/Editar Evento -->
<div class="modal fade" id="mdlEditEvent" tabindex="-1" aria-labelledby="mdlEditEventLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlEditEventLabel">
                    <i class="fa-solid fa-calendar-plus me-2"></i>
                    <span id="modalTitleEdit">Editar Evento</span>
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
                <button type="submit" form="formEditEvent" class="btn btn-primary" id="saveEventBtnEdit">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

@push('js-script')
    <script>
        const mdlEditEvent = new bootstrap.Modal(document.getElementById('mdlEditEvent'));
        const eventTitleEdit = document.getElementById('name_event');
        const eventCalendarEdit = document.getElementById('type_calendar_event');
        const eventAllDayEdit = document.getElementById('all_day_event');
        const eventStartDateEdit = document.getElementById('start_date_event');
        const eventStartTimeEdit = document.getElementById('start_time_event');
        const eventEndDateEdit = document.getElementById('end_date_event');
        const eventEndTimeEdit = document.getElementById('end_time_event');
        const eventLocationEdit = document.getElementById('location_event');
        const eventDescriptionEdit = document.getElementById('description_event');
        const deleteEventBtnEdit = document.getElementById('deleteEventBtnEdit');
        const modalTitleEdit = document.getElementById('modalTitleEdit');
        const startTimeContainerEdit = document.getElementById('startTimeContainerEdit');
        const endTimeContainerEdit = document.getElementById('endTimeContainerEdit');

        const paramsMdlEditEvent = {
            event: null
        };

        function eventsMdlEditEvent() {
            loadTomSelectMdlEditEvent();
            document.querySelector('#formEditEvent').addEventListener('submit', (e) => {
                e.preventDefault();
                updateEvent(e.target);
            })

            window.customerEditSelect.on('change', function(value) {
                actionChangeClient(value, window.vehicleEditSelect);
            });

            window.vehicleEditSelect.on('change', function(value) {
                actionChangeVehicleEdit(value);
            });
        }

        function resetFormEdit() {
            document.getElementById('formEditEvent').reset();
            modalTitleEdit.textContent = 'Crear Evento';
            deleteEventBtnEdit.style.display = 'none';
            startTimeContainerEdit.style.display = 'block';
            endTimeContainerEdit.style.display = 'block';
        }


        function openMdlEditEvent(event) {
            // resetFormEdit();

            $('#mdlShowEvent').modal('hide');
            paramsMdlEditEvent.event = event;
            setFormDataEdit(event);

            // const now = new Date();
            // const endTime = new Date(now.getTime() + 60 * 60 * 1000); // +1 hora


            // // Trigger del evento allDay para mostrar/ocultar campos
            // eventAllDayEdit.dispatchEvent(new Event('change'));

            mdlEditEvent.show();
        }

        function setFormDataEdit(event) {
            console.log('✅ SET FORM DATA EDIT:', event);

            document.querySelector('#name_event_edit').value = event.name;
            document.querySelector('#location_event_edit').value = event.location;
            document.querySelector('#description_event_edit').value = event.description;
            document.querySelector('#type_calendar_event_edit').value =
                event.type_calendar ?? 'TRABAJO';

            const allDayCheckbox = document.querySelector('#all_day_event_edit');
            allDayCheckbox.checked = event.full_day === 1;


            // ======================
            // Fechas y horas
            // ======================
            document.querySelector('#start_date_event_edit').value = event.start_date;

            document.querySelector('#start_time_event_edit').value = event.start_time;

            document.querySelector('#end_date_event_edit').value = event.end_date;

            document.querySelector('#end_time_event_edit').value = event.end_time;


            // ======================
            // Cliente (Tom Select)
            // ======================
            window.customerEditSelect.clear();
            window.customerEditSelect.addOption(event.customer);
            window.customerEditSelect.setValue(event.customer_id, true);

            //========= VEHICLE ========
            window.vehicleEditSelect.clear();
            window.vehicleEditSelect.addOption(event.vehicle);
            window.vehicleEditSelect.setValue(event.vehicle_id, true);


            eventCalendarEdit.value = 'cal1';
        }

        function loadTomSelectMdlEditEvent() {
            const initialCustomer = @json($customer_formatted);
            window.customerEditSelect = new TomSelect('#customer_id_event_edit', {
                valueField: 'id',
                options: [initialCustomer],
                items: [initialCustomer.id],
                labelField: 'full_name',
                searchField: ['full_name'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un cliente',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastCustomerQuery = str;
                },
                load: async (query, callback) => {
                    if (query.length < 3) return callback();
                    try {
                        const url = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar clientes');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
                        if (results.length === 0) {
                            customerParams.documentSearchCustomer = lastCustomerQuery;
                            console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                        }
                    } catch (error) {
                        console.error('Error cargando clientes:', error);
                        callback();
                    }
                },
                render: {
                    option: (item, escape) => `
                        <div>
                            <strong>${escape(item.full_name)}</strong><br>
                            <small>${escape(item.email ?? '')}</small>
                        </div>
                    `,
                    item: (item, escape) => `<div>${escape(item.full_name)}</div>`,
                    no_results: function(data, escape) {
                        return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
                    }
                }
            });

            window.vehicleEditSelect = new TomSelect('#vehicle_id_event_edit', {
                valueField: 'id',
                labelField: 'text',
                searchField: ['text'],
                plugins: ['clear_button'],
                placeholder: 'Seleccione un vehículo',
                maxOptions: 20,
                create: false,
                preload: false,
                onType: (str) => {
                    lastVehicleQuery = str;
                },
                load: async (query, callback) => {
                    if (!query.length) return callback();
                    try {
                        const url = route('tenant.utils.searchVehicle', {
                            q: query,
                            customer_id: window.customerSelect.getValue()
                        });

                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Error al buscar vehiculos');
                        const data = await response.json();
                        const results = data.data ?? [];
                        callback(results);
                        if (results.length === 0) {
                            vehicleParams.plateSearchVehicle = lastVehicleQuery;
                            console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                        }
                    } catch (error) {
                        console.error('Error cargando vehiculos:', error);
                        callback();
                    }
                },
                render: {
                    option: (item, escape) => `
                        <div>
                            <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                            <strong>${escape(item.text)}</strong><br>
                            <small>${escape(item.subtext ?? '')}</small>
                        </div>
                    `,
                    item: (item, escape) => `
                            <div>
                                <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                                ${escape(item.text)}
                            </div>
                        `,
                    no_results: function(data, escape) {
                        return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
                    }
                }
            });

        }

        function updateEvent(formCreate) {

            const name = document.querySelector('#name_event_edit').value;

            Swal.fire({
                title: "Desea actualizar la cita?",
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
                        title: "Actualizando cita...",
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
                        formData.append('_method', 'PUT');

                        const res = await axios.post(route('tenant.taller.citas.update', {
                            id: paramsMdlEditEvent.event.id
                        }), formData);

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
                                paintValidationErrors(errors, 'event_edit_error');
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

        async function actionChangeVehicleEdit(value) {
            document.querySelector('#plate').value = '';
            const vehicleInfo = document.querySelector('#vehicle_info');
            vehicleInfo.classList.add('d-none');
            vehicleInfo.querySelector('.fw-semibold').textContent = '';


            if (!value) return;
            const vehicle = window.vehicleSelect.options[value];
            document.querySelector('#plate').value = vehicle.text;

            vehicleInfo.classList.remove('d-none');
            vehicleInfo.querySelector('.fw-semibold').textContent = vehicle.subtext;

            //========= TRAER CLIENTES ==========
            mostrarAnimacion1();
            try {

                const res = await axios.get(route('tenant.utils.searchCustomer', {
                    q: '',
                    vehicle_id: value
                }));

                if (res.data.success) {
                    toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                    setCustomerOfVehicleEdit(res.data.data, window.customerEditSelect);
                }

            } catch (error) {
                toastr.error(error, 'ERROR AL CARGAR CLIENTE DEL VEHÍCULO');
                return;
            } finally {
                ocultarAnimacion1();
            }
        }
    </script>
@endpush

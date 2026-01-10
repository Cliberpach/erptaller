<!-- Modal para Crear/Editar Evento -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">
                    <i class="fa-solid fa-calendar-plus me-2"></i>
                    <span id="modalTitle">Crear Evento</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('workshop.appointments.forms.form_create_event')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                </button>
                <button type="submit" form="formCreateEvent" class="btn btn-primary" id="saveEventBtn">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

@push('js-script')
    <script>
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
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
            loadTomSelectMdlCreateEvent();

            document.querySelector('#formCreateEvent').addEventListener('submit', (e) => {
                e.preventDefault();
                storeEvent(e.target);
            })

            window.customerSelect.on('change', function(value) {
                actionChangeClient(value,window.vehicleSelect);
            });

            window.vehicleSelect.on('change', function(value) {
                actionChangeVehicle(value);
            });
        }

        function resetForm() {
            document.getElementById('formCreateEvent').reset();
            modalTitle.textContent = 'Crear Evento';
            deleteEventBtn.style.display = 'none';
            startTimeContainer.style.display = 'block';
            endTimeContainer.style.display = 'block';
        }


        function openMdlCreateEvent(info) {
            resetForm();
            setFormData(info);

            eventAllDay.dispatchEvent(new Event('change'));

            modal.show();
        }

        function loadTomSelectMdlCreateEvent() {
            const initialCustomer = @json($customer_formatted);
            window.customerSelect = new TomSelect('#customer_id_event', {
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

            window.vehicleSelect = new TomSelect('#vehicle_id_event', {
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

        function setFormData(info) {
            console.log('✅ Clic en celda vacía:', info);
            console.log('FECHA INICIO', info.start);
            console.log('FECHA FIN', info.end);

            // inputs
            eventStartDate.value = formatDateInput(info.start);
            eventEndDate.value = formatDateInput(info.end);

            eventStartTime.value = formatTimeInput(info.start);
            eventEndTime.value = formatTimeInput(info.end);
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
                            $('#eventModal').modal('hide');
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

        async function actionChangeClient(value,_vehicleSelect) {

            if (!value) return;

            mostrarAnimacion1();
            try {

                const res = await axios.get(route('tenant.utils.searchVehicle', {
                    q: '',
                    customer_id: value
                }));

                if (res.data.success) {
                    toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                    setVehiclesClient(res.data.data, _vehicleSelect);
                }

            } catch (error) {
                toastr.error(error, 'ERROR AL CARGAR VEHÍCULOS DEL CLIENTE');
                return;
            } finally {
                ocultarAnimacion1();
            }
        }

        function setVehiclesClient(vehicles, _vehicleSelect) {
            _vehicleSelect.clear();
            _vehicleSelect.clearOptions();

            vehicles.forEach(v => {
                _vehicleSelect.addOption({
                    id: v.id,
                    text: v.text,
                    subtext: v.subtext
                });
            });
        }

        async function actionChangeVehicle(value) {
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
                    setCustomerOfVehicle(res.data.data, window.customerSelect);
                }

            } catch (error) {
                toastr.error(error, 'ERROR AL CARGAR CLIENTE DEL VEHÍCULO');
                return;
            } finally {
                ocultarAnimacion1();
            }
        }

        function setCustomerOfVehicle(customer, _customerSelect) {
            _customerSelect.clear();
            _customerSelect.clearOptions();

            customer.forEach(v => {
                _customerSelect.addOption({
                    id: v.id,
                    full_name: v.full_name,
                    email: v.email
                });
            });

            if (customer.length > 0) {
                _customerSelect.off('change');
                _customerSelect.setValue(customer[0].id);
                _customerSelect.on('change', actionChangeClient);
            }
        }
    </script>
@endpush

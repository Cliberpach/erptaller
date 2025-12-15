<div class="modal fade" id="mdlCreateVehicle" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Vehículo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('utils.modals.vehicles.forms.form_create_vehicle')
            </div>
            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="margin-right: 6px;">Cerrar</button>
                            <button class="btn btn-primary btnstoreCustomer" type="submit" form="form_create_vehicle">
                                <i class="fa-solid fa-floppy-disk"></i> Registrar
                            </button>
                        </div>

                        <div class="col-12">
                            <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los
                                campos con (*) son obligatorios</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let vehicleParams = {
        plateSearchVehicle: null
    };

    function openMdlCreateVehicle() {
        setDefaultMdlVehicle();
        if (typeof vehicleParams.plateSearchVehicle === 'string' &&
            (vehicleParams.plateSearchVehicle.length === 8 || vehicleParams.plateSearchVehicle.length === 6)) {
            document.querySelector('#plate_mdlvehicle').value = vehicleParams.plateSearchVehicle;
            document.querySelector('#btn_search_plate').click();
        }
        $('#mdlCreateVehicle').modal('show');

    }

    function eventsMdlVehicle() {
        loadSelectMdlVehicle();

        document.querySelector('#btn_search_plate').addEventListener('click', () => {
            accionBuscarPlaca();
        })

        document.querySelector('#form_create_vehicle').addEventListener('submit', (e) => {
            e.preventDefault();
            storeVehicle(e.target);
        })

        $('#mdlCreateVehicle').on('hidden.bs.modal', function() {
            clearMdlCreateVehicle();
        });

    }

    function loadSelectMdlVehicle() {

        window.clientMdlVehicleSelect = new TomSelect('#client_id_mdlvehicle', {
            valueField: 'id',
            labelField: 'full_name',
            searchField: ['full_name'],
            placeholder: 'Seleccione un cliente',
            maxOptions: 20,
            create: false,
            preload: false,
            load: async (query, callback) => {
                if (!query.length) return callback();
                try {
                    const url = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(query)}`;
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Error al buscar clientes');
                    const data = await response.json();
                    callback(data.data ?? []);
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
                item: (item, escape) => `<div>${escape(item.full_name)}</div>`
            }
        });

        const modelSelect = document.getElementById('model_id_mdlvehicle');
        if (modelSelect) {
            window.modelSelect = new TomSelect(modelSelect, {
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                placeholder: 'Buscar marca - modelo...',
                maxOptions: 50,
                loadThrottle: 300,
                closeAfterSelect: true,
                preload: false,
                maxItems: 1,
                create: false,
                plugins: ['remove_button'],
                load: function(query, callback) {
                    if (!query.length) return callback();
                    axios.get(route('tenant.utils.searchModel'), {
                            params: {
                                q: query
                            }
                        })
                        .then((res) => {
                            callback(res.data);
                        })
                        .catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `<div>
                        <strong>${escape(item.text)}</strong>
                    </div>`;
                    },
                    item: function(item, escape) {
                        return `<div>${escape(item.text)}</div>`;
                    }
                }
            });
        }

        const yearSelect = document.getElementById('year_id_mdlvehicle');
        if (yearSelect && !yearSelect.tomselect) {
            window.yearSelect = new TomSelect(yearSelect, {
                valueField: 'id',
                labelField: 'description',
                searchField: ['description', 'id'],
                create: false,
                plugins: ['clear_button'],
                render: {
                    option: (item, escape) => `
                            <div>
                                ${escape(item.description)}
                            </div>
                        `,
                    item: (item, escape) => `
                            <div>${escape(item.description)}</div>
                        `
                }
            });
        }

        const colorSelect = document.getElementById('color_id_mdlvehicle');
        if (colorSelect && !colorSelect.tomselect) {
            window.colorSelect = new TomSelect(colorSelect, {
                valueField: 'id',
                labelField: 'description',
                searchField: ['description', 'id'],
                create: false,
                sortField: {
                    field: 'id',
                    direction: 'desc'
                },
                plugins: ['clear_button'],
                render: {
                    option: (item, escape) => `
                            <div>
                                ${escape(item.description)}
                            </div>
                        `,
                    item: (item, escape) => `
                            <div>${escape(item.description)}</div>
                        `
                }
            });
        }

    }

    async function accionBuscarPlaca() {
        const placa = document.querySelector('#plate_mdlvehicle').value.trim();

        if (placa.length < 6 || placa.length > 8) {
            toastr.error('LA PLACA DEBE TENER ENTRE 6 Y 8 CARACTERES');
            return;
        }

        searchPlate(placa);
    }

    async function searchPlate(placa) {
        mostrarAnimacion1();
        try {
            toastr.clear();
            const res = await axios.get(route('tenant.utils.searchPlate', placa));
            if (res.data.success) {

                if (res.data.origin == 'BD') {
                    toastr.error('VEHICULO YA EXISTE EN BD');
                    return;
                }

                const dataApi = res.data.data;
                if (dataApi.mensaje == 'SUCCESS') {
                    toastr.info(dataApi.mensaje);
                    setDataApi(res);
                }
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN CONSULTAR PLACA');
            console.log("Stack:", error.stack);

        } finally {
            ocultarAnimacion1();
        }
    }

    function setDataApi(res) {

        const dataApi = res.data.data.data;
        const model = res.data.model;
        const color = res.data.color;

        const mensaje = dataApi.mensaje;
        if (mensaje == 'No encontrado') {
            toastr.error(mensaje);
            return;
        }

        const modelItem = {
            id: model.id,
            text: `${dataApi.marca}-${dataApi.modelo}`
        };
        addModelSelect(modelItem);

        if (dataApi.color) {
            const colorItem = {
                id: color.id,
                description: `${dataApi.color}`
            };
            addColorSelect(colorItem);
        }

        document.querySelector('#vin').value = dataApi.vin;
        document.querySelector('#serie').value = dataApi.serie;

    }

    function addModelSelect(item) {
        window.modelSelect.clear();
        window.modelSelect.clearOptions();
        window.modelSelect.addOption(item);
        window.modelSelect.setValue(item.id);
    }

    function addColorSelect(item) {
        window.colorSelect.addOption(item);
        window.colorSelect.setValue(item.id);
        window.colorSelect.refreshOptions(false);
    }

    async function storeVehicle(formCreateVehicle) {

        const result = await Swal.fire({
            title: '¿Desea registrar el vehículo?',
            text: "Confirme para continuar",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'SI, registrar',
            cancelButtonText: 'NO',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        });

        if (result.isConfirmed) {

            try {

                clearValidationErrors('msgError');

                Swal.fire({
                    title: 'Registrando vehículo...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const res = await axios.post(route('tenant.taller.vehiculos.store'), formCreateVehicle);
                if (res.data.success) {
                    toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                    setNewVehicle(res.data.vehicle);
                    $('#mdlCreateVehicle').modal('hide');
                    clearMdlVehicle();
                } else {
                    toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                }

            } catch (error) {
                Swal.close();
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    paintValidationErrors(errors, 'mdlvehicle_error');
                    return;
                }
            } finally {
                Swal.close();
            }

        } else {

            Swal.fire({
                icon: 'info',
                title: 'Operación cancelada',
                text: 'No se realizaron acciones.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            });

        }
    }

    function setNewVehicle(vehicle) {
        const option = {
            id: vehicle.id,
            text: vehicle.plate,
            subtext: `${vehicle.brand.description} - ${vehicle.model.description}`
        };

        window.vehicleSelect.clear();
        window.vehicleSelect.clearOptions();
        window.vehicleSelect.addOption(option);
        window.vehicleSelect.addItem(option.id);
    }

    function clearMdlVehicle() {
        window.clientMdlVehicleSelect.clear();
        document.querySelector('#plate_mdlvehicle_mdlvehicle').value = '';
        window.modelSelect.clear();
        window.yearSelect.clear();
        window.colorSelect.clear();
        document.querySelector('observation_mdlvehicle').textContent = '';
    }

    function setDefaultMdlVehicle() {
        window.clientMdlVehicleSelect.clear();

        const clientId = window.clientSelect.getValue();
        if (!clientId) return;
        const clientItem = window.clientSelect.options[clientId];
        window.clientMdlVehicleSelect.addOption(clientItem);
        window.clientMdlVehicleSelect.setValue(clientId);
    }

    function clearMdlCreateVehicle() {
        window.clientMdlVehicleSelect.clear();
        document.querySelector('#plate_mdlvehicle').value = '';
        window.modelSelect.clear();
        window.yearSelect.clear();
        window.colorSelect.clear();
        document.querySelector('#observation_mdlvehicle').textContent = '';
    }
</script>

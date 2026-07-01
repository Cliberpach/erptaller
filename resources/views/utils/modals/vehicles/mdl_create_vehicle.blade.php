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

<style>
    #mdlCreateVehicle {
        z-index: 9999;
    }
</style>

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
            buscarPlacaMdlVehicle();
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

    // Nombres ÚNICOS del modal (rompen la colisión con la lupa inline de quotes/edit,
    // que conserva accionBuscarPlaca/searchPlate/setDataApi sobre #plate).
    async function buscarPlacaMdlVehicle() {
        const placa = document.querySelector('#plate_mdlvehicle').value.trim();

        if (placa.length < 6 || placa.length > 8) {
            toastr.error('LA PLACA DEBE TENER ENTRE 6 Y 8 CARACTERES');
            return;
        }

        searchPlateMdlVehicle(placa);
    }

    async function searchPlateMdlVehicle(placa) {
        mostrarAnimacion1();
        try {
            toastr.clear();
            const res = await axios.get(route('tenant.utils.searchPlate', placa));
            if (res.data.success) {
                // Forma PLANA unificada (Capa 1): mismo prefill para BD y API.
                if (res.data.vehiculo) {
                    prefillMdlVehicle(res.data.vehiculo);
                    if (res.data.origin === 'BD') {
                        // No bloquea: el vehículo ya existe, solo se cargan sus datos.
                        toastr.info('Vehículo ya registrado — datos cargados');
                    }
                } else {
                    // Advertencia (no error rojo): la placa no está en BD ni en la API.
                    Swal.fire({
                        icon: 'warning',
                        title: 'Placa no encontrada',
                        text: 'No encontramos la placa en el sistema ni en el servicio de consulta. Ingrese los datos manualmente.',
                        confirmButtonText: 'Entendido'
                    });
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

    // Prefill ÚNICO desde vehiculo{} plano (BD y API por igual). Año queda manual
    // (el API no lo trae). No toca los legacy data/model/color de la respuesta.
    function prefillMdlVehicle(vehiculo) {

        // Placa en MAYÚSCULA (la Capa 1 la normaliza a lower; solo display).
        document.querySelector('#plate_mdlvehicle').value = (vehiculo.placa ?? '').toUpperCase();

        // Marca-Modelo: select REMOTO (valueField:'id', labelField:'text') -> el id no está
        // en su lista; hay que inyectar la opción + refreshOptions ANTES de setear el value.
        if (vehiculo.model_id) {
            window.modelSelect.addOption({
                id: vehiculo.model_id,
                text: `${vehiculo.marca ?? ''}-${vehiculo.modelo ?? ''}`
            });
            window.modelSelect.refreshOptions(false);
            window.modelSelect.setValue(vehiculo.model_id);
        }

        // Color: select server-seeded (valueField:'id', labelField:'description') -> setea
        // directo; addColorSelect inyecta+refresh por robustez.
        if (vehiculo.color_id) {
            addColorSelect({
                id: vehiculo.color_id,
                description: vehiculo.color ?? ''
            });
        }

        // Año (#year_id_mdlvehicle): NO se toca -> queda manual.

        document.querySelector('#vin').value = vehiculo.vin ?? '';
        document.querySelector('#serie').value = vehiculo.serie ?? '';
        document.querySelector('#motor').value = vehiculo.motor ?? '';

        // Aviso CASO A: SOLO los campos genuinamente ausentes (id null), no los seteados.
        const faltan = [];
        if (!vehiculo.model_id) faltan.push('Marca-Modelo');
        if (!vehiculo.color_id) faltan.push('Color');
        if (!vehiculo.year_id) faltan.push('Año');
        if (faltan.length) {
            toastr.info('Complete manualmente: ' + faltan.join(', '));
        }
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

        let clientId = null;
        let clientItem = null;
        if (window.clientSelect) {
            clientId = window.clientSelect.getValue();
            if (!clientId) return;
            clientItem = window.clientSelect.options[clientId];
        }
        if (window.customerSelect) {
            clientId = window.customerSelect.getValue();
            if (!clientId) return;
            clientItem = window.customerSelect.options[clientId];
        }

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

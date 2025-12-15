@extends('layouts.template')

@section('title')
    Vehículos
@endsection

@section('content')
    @include('utils.modals.customer.mdl_create_customer')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">REGISTRAR VEHÍCULO</h4>

            <div class="d-flex flex-wrap gap-2">

            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('workshop.vehicles.forms.form_create_vehicle')
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 d-flex justify-content-end">

                    <!-- BOTÓN VOLVER -->
                    <button type="button" class="btn btn-danger me-1" onclick="redirect('tenant.taller.vehiculos.index')">
                        <i class="fas fa-arrow-left"></i> VOLVER
                    </button>

                    <!-- BOTÓN REGISTRAR -->
                    <button class="btn btn-primary" form="form_create_vehicle" type="submit">
                        <i class="fas fa-save"></i> REGISTRAR
                    </button>

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
        let dtYears = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarTomSelect();
            events();
        })

        function events() {
            eventsMdlCreateCustomer();

            document.querySelector('#btn_search_plate').addEventListener('click', () => {
                accionBuscarPlaca();
            })

            document.querySelector('#form_create_vehicle').addEventListener('submit', (e) => {
                e.preventDefault();
                storeVehicle(e.target);
            })
        }

        function iniciarTomSelect() {

            window.clientSelect = new TomSelect('#client_id', {
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

            const modelSelect = document.getElementById('model_id');
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
                    plugins: ['remove_button'], // agrega la X
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

            const yearSelect = document.getElementById('year_id');
            if (yearSelect && !yearSelect.tomselect) {
                window.yearSelect = new TomSelect(yearSelect, {
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

            const colorSelect = document.getElementById('color_id');
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

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        async function accionBuscarPlaca() {
            const placa = document.querySelector('#plate').value.trim();

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

            document.querySelector('#vin').value    =   dataApi.vin;
            document.querySelector('#serie').value  =   dataApi.serie;

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
                        redirect('tenant.taller.vehiculos.index');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    Swal.close();
                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        paintValidationErrors(errors, 'error');
                        return;
                    }
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
    </script>
@endsection

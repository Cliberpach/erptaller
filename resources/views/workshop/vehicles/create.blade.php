@extends('layouts.template')

@section('title')
    Vehículos
@endsection

@section('content')
    @include('utils.modals.customer.mdl_create_customer')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">LISTA DE VEHÍCULOS</h4>

            <div class="d-flex flex-wrap gap-2">
                {{-- <button class="btn btn-warning" onclick="openMdlImportMarca()">
                    <i class="fa-solid fa-upload"></i> IMPORTAR
                </button> --}}

                <a onclick="openMdlCreateMarca()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('workshop.vehicles.forms.form_create_vehicle')
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
                new TomSelect(modelSelect, {
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


            const modelEditSelect = document.getElementById('model_id_edit');
            if (modelEditSelect) {
                window.modalEditSelect = new TomSelect(modelEditSelect, {
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
        }

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        function eliminar(id) {
            const fila = getRowById(dtYears, id);
            const descripcion = fila?.description || 'Sin descripción';

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar el año?',
                html: `
                    <div style="text-align: center; font-size: 15px;">
                        <p><i class="fa fa-palette text-primary"></i>
                            <strong>Descripción:</strong> ${descripcion}
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando año...',
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
                        const res = await axios.delete(route('tenant.taller.years.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtYears.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR AÑO');
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

        $(".btn-modal-file").on('click', function() {
            $("#modal_file").modal("show");
        });

        async function getYearsModel(modelId) {
            try {
                mostrarAnimacion1();
                const res = await axios.get(route('tenant.utils.getYearsModel', modelId));
                if (res.data.success) {
                    paintYearSelect(res.data.years);
                    toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                } else {
                    toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER AÑOS');
            } finally {
                ocultarAnimacion1();
            }
        }

        function paintYearSelect(years) {
            console.log(years);
            if (!window.yearSelect) {
                console.warn('TomSelect de años no inicializado');
                return;
            }

            const select = window.yearSelect;

            select.clear();
            select.clearOptions();

            if (Array.isArray(years) && years.length > 0) {
                select.addOptions(years.map(year => ({
                    id: year.id,
                    description: year.description
                })));
            }

            select.refreshOptions(false);
        }
    </script>
@endsection

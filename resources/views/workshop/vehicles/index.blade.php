@extends('layouts.template')

@section('title')
    Vehículos
@endsection

@section('content')
    @include('workshop.brands.modals.mdl_create_marca')
    @include('workshop.brands.modals.mdl_edit_marca')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">LISTA DE VEHÍCULOS</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tenant.taller.vehiculos.create') }}" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('workshop.vehicles.tables.tbl_list_vehiculos')
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
        let dtVehicles = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtVehicles();
            events();
        })

        function events() {}

        function iniciarDtVehicles() {
            dtVehicles = new DataTable('#dt-vehiculos', {
                "serverSide": true,
                "processing": true,
                "ajax": '{{ route('tenant.taller.vehiculos.getVehiculos') }}',
                "columns": [{
                        data: 'id',
                        className: "text-center",
                        "visible": false,
                        "searchable": false
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'plate',
                        name: 'v.plate',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'brand_name',
                        name: 'b.description',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'model_name',
                        name: 'm.description',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'year_name',
                        name: 'y.description',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'color_name',
                        name: 'c.description',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'observation',
                        name: 'v.observation',
                        searchable: false,
                        orderable: false,
                        className: "text-center"
                    },
                    {
                        searchable: false,
                        orderable: false,
                        data: null,
                        className: "text-center",
                        render: function(data) {

                            return `
                            <div class="btn-group">
                                <button
                                    class="btn btn-warning btn-sm modificarDetalle"
                                    onclick="redirectParams('tenant.taller.vehiculos.edit',${data.id})"
                                    type="button"
                                    title="Modificar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <a
                                    class="btn btn-danger btn-sm"
                                    href="#"
                                    onclick="eliminar(${data.id})"
                                    title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        `;
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar columna ascendente",
                        sortDescending: ": activar para ordenar columna descendente"
                    },
                    select: {
                        rows: {
                            _: "%d filas seleccionadas",
                            0: "Haz clic en una fila para seleccionarla",
                            1: "1 fila seleccionada"
                        }
                    }
                },
                "order": [
                    [0, "desc"]
                ],
            });

        }

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        function eliminar(id) {
            const fila = getRowById(dtVehicles, id);
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

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar el vehículo?',
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
                            dtVehicles.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR VEHÍCULO');
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
    </script>
@endsection

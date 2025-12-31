@extends('layouts.template')
@section('title')
    LISTA DE CONDICIONES DE PAGO
@endsection


@section('content')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">Condiciones de Pago</h6>
            <div class="input-group-append">
                <button onclick="goToCrearCondicionPago()" type="button" data-bs-whatever="Nueva caja"
                    class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2"> NUEVO</p>
                    </div>
                </button>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('sales.payment_conditions.tables.tbl_condicion_pago_list')
        </div>
    </div>
@endsection

<script>
    let dtCondicionPago = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadDtPaymentConditions();
    })

    function loadDtPaymentConditions() {
        const url = '{{ route('tenant.ventas.condiciones_pago.getCondicionPago') }}';

        dtCondicionPago = new DataTable('#tbl_condicion_pago_list', {
            serverSide: true,
            processing: true,
            ajax: {
                url: url,
                type: 'GET',
            },
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'name',
                    name: 'name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'type',
                    name: 'type',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'nro_days',
                    name: 'nro_days',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    searchable: false,
                    orderable: false
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        const baseUrlEdit =
                            `{{ route('tenant.ventas.condiciones_pago.edit', ['id' => ':id']) }}`;
                        urlEdit = baseUrlEdit.replace(':id', data.id);
                        return `
                            <div class="btn-group dropstart">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="${urlEdit}">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarCondicionPago(${data.id})">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                            </div>
                        `;
                    },
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles en la tabla",
                "aria": {
                    "sortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    function goToCrearCondicionPago() {
        window.location.href = @json(route('tenant.ventas.condiciones_pago.create'));
    }


    function eliminarCondicionPago(id) {
        toastr.clear();
        let row = getRowById(dtCondicionPago, id);
        let message = '';

        message = `Desea eliminar la condicion de pago: ${row.type} - DÍAS: ${row.nro_days}`;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: message,
            text: "Operación no reversible!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Eliminando condicion de pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let urlDelete =
                        `{{ route('tenant.ventas.condiciones_pago.destroy', ['id' => ':id']) }}`;
                    urlDelete = urlDelete.replace(':id', id);
                    const token = document.querySelector('input[name="_token"]').value;

                    const response = await fetch(urlDelete, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });

                    const res = await response.json();

                    if (res.success) {
                        dtCondicionPago.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CONDICION DE PAGO');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CONDICION DE PAGO');
                } finally {
                    Swal.close();
                }

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }
</script>

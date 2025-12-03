@extends('layouts.template')

@section('title')
    Cuentas Bancarias
@endsection

@section('content')
    @include('maintenance.bank_accounts.modals.mdl_cuenta_create')
    @include('maintenance.bank_accounts.modals.mdl_cuenta_edit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">LISTA DE CUENTAS BANCARIAS</h4>

            <div class="d-flex flex-wrap gap-2">
                <a href="javascript:void(0);" onclick="openMdlNuevoMetodoPago()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('maintenance.bank_accounts.tables.tbl_cuentas')
                    </div>
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


<script>
    let dtCuentas = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDtCuentas();
        iniciarSelect2();
        events();
    })

    function events() {
        eventsMdlCreateCuenta();
        eventsMdlEditCuenta();
    }

    function iniciarSelect2() {
        $('.select2_mdl_cuenta_edit').select2({
            width: '100%',
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            dropdownParent: $('#mdlEditCuenta')
        });

    }

    function iniciarDtCuentas() {
        let urlGet = '{{ route('tenant.mantenimiento.cuentas.getBankAccounts') }}';

        dtCuentas = new DataTable('#tbl_cuentas', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGet,
                type: 'GET',
            },
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'c.id'
                },
                {
                    data: 'bank_name',
                    name: 'c.bank_name'
                },
                {
                    data: 'bank_id',
                    name: 'c.bank_id',
                    visible: false
                },
                {
                    data: 'holder',
                    name: 'c.holder'
                },
                {
                    data: 'currency',
                    name: 'c.currency'
                },
                {
                    data: 'account_number',
                    name: 'c.account_number'
                },
                {
                    data: 'cci',
                    name: 'c.cci'
                },
                {
                    data: 'phone',
                    name: 'c.phone'
                },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton${row.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-th"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${row.id}">
                                <li>
                                    <a class="dropdown-item" href="#" data-id="${row.id}" data-action="ver">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-id="${row.id}" data-action="editar" onclick="openMdlEditCuenta(${row.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="eliminarCuenta(${row.id})" data-id="${row.id}" data-action="eliminar">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;
                    },
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

    function eliminarCuenta(id) {
        toastr.clear();
        let row = getRowById(dtCuentas, id);
        let message = '';

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: `DESEA ELIMINAR LA CUENTA BANCARIA?`,
            text: `${row.account_number}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Eliminando cuenta bancaria...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let urlDelete =
                        `{{ route('tenant.mantenimiento.cuentas.destroy', ['id' => ':id']) }}`;
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
                        dtCuentas.draw();
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CUENTA BANCARIA');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CUENTA BANCARIA');
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

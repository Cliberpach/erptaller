@extends('layouts.template')
@section('title')
    LISTA DE USUARIOS
@endsection

@section('content')
    @csrf
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">Usuarios <i class="fa-solid fa-user"></i></h4>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" onclick="goToCrearUsuario()">
                    <i class="fa-solid fa-plus"></i> NUEVO
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('maintenance.users.tables.table_list_usuarios')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtUsuarios = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDataTableUsuarios();
        })

        function iniciarDataTableUsuarios() {
            const urlGetUsuarios = '{{ route('tenant.mantenimientos.usuario.getAll') }}';

            dtUsuarios = new DataTable('#table_usuarios', {
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetUsuarios,
                    type: 'GET',
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'u.id',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'nombre',
                        name: 'u.name',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'rol_nombre',
                        name: 'r.name',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'correo',
                        name: 'u.email',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'fecha_registro',
                        name: 'u.created_at',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const baseUrlEdit =
                                `{{ route('tenant.mantenimientos.usuario.edit', ['id' => ':id']) }}`;
                            urlEdit = baseUrlEdit.replace(':id', data.id);

                            const urlDelete = `{{ route('tenant.mantenimientos.usuario.destroy', ':id') }}`
                                .replace(':id', data.id);

                            return `
                                <div class="btn-group">
                                <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-grip"></i>
                                </button>
                                <ul class="dropdown-menu" style="max-height: 100px; overflow-y: auto;">
                                    <li>
                                        <a class="dropdown-item" href="${urlEdit}">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarUsuario(${data.id})">
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

        function goToCrearUsuario() {
            window.location.href = @json(route('tenant.mantenimientos.usuario.create'));
        }

        function eliminarUsuario(id) {
            toastr.clear();
            let row = getRowById(dtUsuarios, id);
            let message = '';

            message = `Desea eliminar el usuario: ${row.nombre}`;

            Swal.fire({
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
                        html: 'Eliminando usuario...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let url = `{{ route('tenant.mantenimientos.usuario.destroy', ['id' => ':id']) }}`;
                        url = url.replace(':id', id);
                        const token = document.querySelector('input[name="_token"]').value;

                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token
                            }
                        });

                        const res = await response.json();

                        if (res.success) {
                            dtUsuarios.draw();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR USUARIO');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR USUARIO');
                    } finally {
                        Swal.close();
                    }

                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    Swal.fire({
                        title: "Operación cancelada",
                        text: "No se realizaron acciones",
                        icon: "error"
                    });
                }
            });
        }
    </script>
@endsection

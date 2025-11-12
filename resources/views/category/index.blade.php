@extends('layouts.template')

@section('title')
    Categorías
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @include('category.modals.mdl_create')
    @include('category.modals.mdl_edit')
    @include('category.modals.mdl_import')
    <!-- Tarjeta de Categorías -->
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h4 class="card-title mb-2 mb-md-0">LISTA DE CATEGORÍAS</h4>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-warning" onclick="openMdlImportCategoria()">
                    <i class="fa-solid fa-upload"></i> IMPORTAR
                </button>

                <a onclick="openMdlCreate()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Crear nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    @include('category.tables.tbl_list_categories')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtCategories = null;


        document.addEventListener('DOMContentLoaded', function() {
            loadDtCategories();
            loadDtImport();
            events();
        });

        function events() {
            eventsMdlCreate();
            eventsMdlEdit();
            eventsImport();
        }

        function loadDtCategories() {
            const url = '{{ route('tenant.inventarios.productos.categoria.get-all') }}';

            dtCategories = new DataTable('#tbl-list-categories', {
                serverSide: true,
                processing: true,
                responsive: true,
                ajax: {
                    url: url,
                    type: 'GET',
                },
                "order": [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            return `
                                <div class="btn-group dropup">
                                <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-grip"></i>
                                </button>
                                <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEdit(${data.id})">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="deleteCategory(${data.id})">
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
                    "lengthMenu": "Mostrar _MENU_ categorías por página",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ categorías",
                    "infoEmpty": "Mostrando 0 a 0 de 0 categorías",
                    "infoFiltered": "(filtrado de _MAX_ categorías totales)",
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

        function deleteCategory(id) {
            toastr.clear();
            let row = getRowById(dtCategories, id);
            let message = '';

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: `DESEA ELIMINAR LA CATEGORÍA?`,
                text: `Categoría: ${row.name}`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Eliminando categoría...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let url =
                            `{{ route('tenant.inventarios.productos.categoria.destroy', ['id' => ':id']) }}`;
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
                            dtCategories.draw();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR CATEGORÍA');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CATEGORÍA');
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
@endsection

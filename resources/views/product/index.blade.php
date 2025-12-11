@extends('layouts.template')

@section('title')
    Productos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @include('utils.lightbox.lightbox')
    @include('product.modals.mdl_create')
    @include('product.modals.mdl_edit')
    @include('product.modals.mdl_import')

    <x-card>
        <x-slot name="headerCard">
            <h4 class="card-title">LISTA DE PRODUCTOS</h4>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-warning" onclick="openMdlImportProducto()">
                    <i class="fa-solid fa-upload"></i> IMPORTAR
                </button>

                <button type="button" class="btn btn-primary" onclick="openMdlCreate()">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ms-2">NUEVO</p>
                    </div>
                </button>
            </div>
        </x-slot>

        <x-slot name="contentCard">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        @include('product.tables.tbl_list_products')
                    </div>
                </div>
            </div>
        </x-slot>
    </x-card>
@endsection


@section('js')
    <script>
        let dtProducts = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadDtProducts();
            iniciarSelect2();
            events();
        })

        function events() {
            eventsMdlCreateProduct();
            eventsMdlEditProduct();
            eventsMdlImportarProductos();
        }

        function loadDtProducts() {
            const urlGetProducts = '{{ route('tenant.inventarios.productos.producto.get-all') }}';

            dtProducts = new DataTable('#table-products', {
                serverSide: true,
                processing: true,
                responsive: true,
                ajax: {
                    url: urlGetProducts,
                    type: 'GET',
                    data: function(d) {
                        d.categoria_id = $('#categoria').val();
                        d.marca_id = $('#marca').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                autoWidth: true,
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable:false,
                        orderable:true
                    },
                    {
                        data: 'name',
                        name: 'p.name',
                        searchable:true,
                        orderable:true
                    },
                    {
                        data: 'category_name',
                        name: 'c.name',
                        searchable:true,
                        orderable:true
                    },
                    {
                        data: 'brand_name',
                        name: 'b.name',
                        searchable:true,
                        orderable:true
                    },
                    {
                        data: 'sale_price',
                        name: 'p.sale_price',
                        className: 'text-end',
                        searchable:false,
                        orderable:true
                    },
                    {
                        data: 'purchase_price',
                        name: 'p.purchase_price',
                        className: 'text-end',
                        searchable:false,
                        orderable:true
                    },
                    {
                        data: 'stock',
                        name: 'p.stock',
                        className: 'text-end',
                        searchable:false,
                        orderable:false
                    },
                    {
                        data: 'stock_min',
                        name: 'stock_min',
                        className: 'text-end',
                        searchable:false,
                        orderable:false
                    },
                    {
                        data: 'code_factory',
                        name: 'p.code_factory',
                        searchable:true,
                        orderable:false
                    },
                    {
                        data: 'code_bar',
                        name: 'p.code_bar',
                        searchable:true,
                        orderable:false
                    },
                    {
                        data: 'img_route',
                        name: 'img_route',
                        className: 'text-center',
                        searchable:false,
                        orderable:false,
                        render: function(data, type, row) {
                            if (data) {
                                return `<img class="imgShowLightBox" src="/${data}" alt="Imagen" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">`;
                            } else {
                                return '<span class="text-muted">Sin imagen</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const baseUrlEdit = `:id`;
                            urlEdit = baseUrlEdit.replace(':id', data.id);

                            return `
                            <div class="btn-group dropup">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlShowProducto(${data.id})">
                                        <i class="fa-solid fa-eye"></i> Ver
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEdit(${data.id})">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarProducto(${data.id})">
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
                pageLength: 25,
                lengthChange: false,
                dom: '<"row mb-3"<"col-md-6 d-flex align-items-center"f>>t<"row"<"col-6"i><"col-6"p>>',

                language: {
                    "lengthMenu": "Mostrar _MENU_ productos por página",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                    "infoFiltered": "(filtrado de _MAX_ productos totales)",
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

            const inputSearchDataTable = document.querySelector('#dt-search-0');
            if (inputSearchDataTable) {
                inputSearchDataTable.style.width = '500px';
                inputSearchDataTable.style.height = '50px';
                inputSearchDataTable.style.textAlign = 'left';
                inputSearchDataTable.placeholder = 'Buscar producto...';

                const previousSibling = inputSearchDataTable.previousElementSibling;
                if (previousSibling) {
                    previousSibling.style.display = 'none';
                }
            }
        }

        function iniciarSelect2() {
            $('.select2_form').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                dropdownParent: $('#mdl-create-product')
            });
        }


        function eliminarProducto(id) {
            toastr.clear();
            let row = getRowById(dtProducts, id);
            let message = '';
            let tipo_documento = '';

            message = `Desea eliminar el producto: ${row.name}`;

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
                        html: 'Eliminando producto...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let url = `{{ route('tenant.inventarios.productos.destroy', ['id' => ':id']) }}`;
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
                            dtProducts.ajax.reload();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR PRODUCTO');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR PRODUCTO');
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

        function exportarExcelProductos() {
            const categoriaId = document.getElementById('categoria').value;
            const marcaId = document.getElementById('marca').value;

            const url = '{{ route('tenant.inventarios.productos.producto.export-excel') }}' +
                `?categoriaId=${categoriaId}&marcaId=${marcaId}`;

            window.location.href = url;
        }
    </script>

    {{-- <script src="{{ asset('assets/js/products.js') }}" type="module"></script> --}}
@endsection

@extends('layouts.template')

@section('title')
    Documento Compra
@endsection

@section('content')
    @include('utils.spinners.spinner_1')
    @include('purchases.purchase_document.modals.mdl_show')

    <div class="card">
        @csrf
        <div class="card-header d-flex justify-content-between flex-row">
            <h6>Documento Compra <i class="fa-solid fa-truck-field"></i></h6>

            <div class="input-group-append">
                <button onclick="goToPurchaseDocumentCreate()" type="button" data-bs-whatever="Nueva caja"
                    class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2"> NUEVO</p>
                    </div>
                </button>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-12">
                    @include('purchases.purchase_document.tables.tbl_list_purchase_document')
                </div>
            </div>

        </div>
    </div>

    <!-- end card -->
@endsection

@section('js')
    <script>
        let dtPurchaseDocuments = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadDataTablePurchaseDocuments();
            eventsMdlPurchaseDocumentShow();
        })

        function loadDataTablePurchaseDocuments() {
            const urlGetPurchaseDocuments = '{{ route('tenant.compras.documento_compra.getPurchaseDocuments') }}';

            dtPurchaseDocuments = new DataTable('#tbl_list_purchase_document', {
                serverSide: true,
                processing: true,
                ajax: {
                    url: urlGetPurchaseDocuments,
                    type: 'GET',
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'pd.id',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'delivery_date',
                        name: 'pd.delivery_date',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            let document =
                                `<p style="margin:0;padding:0;">${data.supplier_type_document_abbreviation}:${data.supplier_document_number}-${data.supplier_name}<p>`;

                            return document;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'payment_condition_name',
                        name: 'pd.payment_condition_name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'currency',
                        name: 'pd.currency',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            let document =
                                `<p style="margin:0;padding:0;">${data.serie}-${data.correlative}<p>`;

                            return document;
                        },
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'payment_status',
                        name: 'pd.payment_status',
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            if (data === 'PAGADO') {
                                return '<span class="badge bg-primary">PAGADO</span>';
                            }

                            if (data === 'PENDIENTE') {
                                return '<span class="badge bg-danger">PENDIENTE</span>';
                            }

                            return data;
                        }
                    },
                    {
                        data: 'observation',
                        name: 'pd.observation',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            const baseUrlEdit =
                                `{{ route('tenant.compras.proveedor.edit', ['id' => ':id']) }}`;
                            urlEdit = baseUrlEdit.replace(':id', data.id);

                            return `
                                <div class="btn-group dropdown">
                                <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-grip"></i>
                                </button>
                                <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlShowPurchaseDocument(${data.id})">
                                            <i class="fas fa-eye"></i> Ver
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

        function goToPurchaseDocumentCreate() {
            window.location.href = @json(route('tenant.compras.documento_compra.create'));
        }


        function eliminarProveedor(id) {
            toastr.clear();
            let row = getRowById(dtPurchaseDocuments, id);
            let message = '';

            if (!row) {
                toastr.error('NO SE ENCUENTRA EL PROVEEDOR EN EL DATATABLE');
                return;
            }

            message = `Desea eliminar el proveedor: ${row.nombre}, ${row.tipo_documento_descripcion}:${row.nro_documento}`;

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
                        html: 'Eliminando proveedor...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let urlDeleteProveedor =
                            `{{ route('tenant.compras.proveedor.destroy', ['id' => ':id']) }}`;
                        urlDeleteProveedor = urlDeleteProveedor.replace(':id', id);
                        const token = document.querySelector('input[name="_token"]').value;

                        const response = await fetch(urlDeleteProveedor, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token
                            }
                        });

                        const res = await response.json();

                        if (res.success) {
                            dtPurchaseDocuments.draw();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR PROVEEDOR');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR PROVEEDOR');
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

        function eliminarProveedor(id) {
            toastr.clear();
            let row = getRowById(dtPurchaseDocuments, id);
            let message = '';

            if (!row) {
                toastr.error('NO SE ENCUENTRA EL PROVEEDOR EN EL DATATABLE');
                return;
            }

            message = `Desea eliminar el proveedor: ${row.name}, ${row.type_document_abbreviation}:${row.document_number}`;

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
                        html: 'Eliminando proveedor...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let urlDeleteProveedor =
                            `{{ route('tenant.compras.proveedor.destroy', ['id' => ':id']) }}`;
                        urlDeleteProveedor = urlDeleteProveedor.replace(':id', id);
                        const token = document.querySelector('input[name="_token"]').value;

                        const response = await fetch(urlDeleteProveedor, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token
                            }
                        });

                        const res = await response.json();

                        if (res.success) {
                            dtPurchaseDocuments.draw();
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR AL ELIMINAR PROVEEDOR');
                        }

                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR PROVEEDOR');
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

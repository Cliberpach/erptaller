@extends('layouts.template')

@section('title')
    Listado de proveedores
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

<div class="card">
    @csrf
    <div class="card-header d-flex flex-row justify-content-between">
        <h6>Proveedores <i class="fa-solid fa-truck-field"></i></h6>
        
        <div class="input-group-append">
            <button onclick="goToSupplierCreate()"  type="button" data-bs-whatever="Nueva caja" class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
               @include('purchases.supplier.tables.tbl_list_supplier')
            </div>
        </div>
      
    </div>
</div>

<!-- end card -->
@endsection

@if(Session::has('message_success'))
<script>
    var message = "{{ Session::get('message_success') }}";
    toastr.success(message, 'OPERACIÓN COMPLETADA');
</script>
@endif

<script>
    let dtSuppliers    =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        loadDataTableSuppliers();
    })

    function loadDataTableSuppliers(){
        const urlGetProveedores = '{{ route('tenant.compras.proveedor.getSuppliers') }}';

        dtSuppliers  =   new DataTable('#tbl_list_supplier',{
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetProveedores,
                type: 'GET',
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'type_identity_document_name', name: 'type_identity_document_name' },
                { data: 'document_number', name: 'document_number' },
                { data: 'name', name: 'name' },
                { data: 'address', name: 'address' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                {
                    data: null, 
                    render: function(data, type, row) {

                        const baseUrlEdit   =   `{{ route('tenant.compras.proveedor.edit', ['id' => ':id']) }}`;
                        urlEdit             =   baseUrlEdit.replace(':id', data.id); 

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
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarProveedor(${data.id})">
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

    function goToSupplierCreate(){
        window.location.href = @json(route('tenant.compras.proveedor.create'));
    }


    function eliminarProveedor(id){
        toastr.clear();
        let row             =   getRowById(dtSuppliers,id);
        let message         =   '';

        if(!row){
            toastr.error('NO SE ENCUENTRA EL PROVEEDOR EN EL DATATABLE');
            return;
        }
      
        message =   `Desea eliminar el proveedor: ${row.nombre}, ${row.tipo_documento_descripcion}:${row.nro_documento}`;

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
                html: 'Eliminando proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                let urlDeleteProveedor    =   `{{ route('tenant.compras.proveedor.destroy', ['id' => ':id']) }}`;
                urlDeleteProveedor        =   urlDeleteProveedor.replace(':id', id);
                const token               =   document.querySelector('input[name="_token"]').value;

                const response  =   await fetch(urlDeleteProveedor, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        }
                                    });

                const   res =   await response.json();

                if(res.success){
                    dtSuppliers.draw();
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR AL ELIMINAR PROVEEDOR');
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ELIMINAR PROVEEDOR');
            }finally{
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

    function eliminarProveedor(id){
        toastr.clear();
        let row             =   getRowById(dtSuppliers,id);
        let message         =   '';

        if(!row){
            toastr.error('NO SE ENCUENTRA EL PROVEEDOR EN EL DATATABLE');
            return;
        }
      
        message =   `Desea eliminar el proveedor: ${row.name}, ${row.type_document_abbreviation}:${row.document_number}`;

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
                html: 'Eliminando proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                let urlDeleteProveedor    =   `{{ route('tenant.compras.proveedor.destroy', ['id' => ':id']) }}`;
                urlDeleteProveedor        =   urlDeleteProveedor.replace(':id', id);
                const token               =   document.querySelector('input[name="_token"]').value;

                const response  =   await fetch(urlDeleteProveedor, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        }
                                    });

                const   res =   await response.json();

                if(res.success){
                    dtSuppliers.draw();
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR AL ELIMINAR PROVEEDOR');
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ELIMINAR PROVEEDOR');
            }finally{
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
<script src="{{asset('assets/js/utils.js')}}"></script>

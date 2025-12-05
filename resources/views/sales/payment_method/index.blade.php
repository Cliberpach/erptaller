@extends('layouts.template')

@section('title')
    Métodos pago
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

@include('sales.payment_method.modals.mdl_create_payment_method')
@include('sales.payment_method.modals.mdl_edit_payment_method')


<div class="card">
    @csrf
    <div class="card-header d-flex flex-row justify-content-between">
        <h4 class="card-title">Métodos pago <i class="fas fa-wallet" style="color: rgb(7, 45, 168);"></i></h4>
        <div class="input-group-append">
            <button onclick="openMdlCreatePaymentMethod()"  type="button" data-bs-whatever="Nueva caja" class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                <div class="table-responsive">
                    @include('sales.payment_method.tables.tbl_list_payment_methods') 
                </div>
            </div>
        </div>
    </div>

</div>

<!-- end card -->
@endsection

<script>
    let dtPaymentMethods    =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        iniciarDataTableAlmacenes();
        iniciarSelect2();
        events();
    })

    function events(){
        eventsMdlCreatePaymentMethod();
        eventsMdlEditPaymentMethod();
    }

    function iniciarSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true,
        } );
    }

    function iniciarDataTableAlmacenes(){
        const urlGet = `{{route('tenant.ventas.metodo_pago.getPaymentMethods')}}`;

        dtPaymentMethods  =   new DataTable('#tbl_list_payment_methods',{
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGet,
                type: 'GET',
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'description', name: 'description' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null, 
                    render: function(data, type, row) {
                        const routeAccounts = route('tenant.ventas.metodo_pago.assignAccountsCreate',{id:row.id});
                      
                        return `
                            <div class="btn-group dropdown">
                            <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEditPaymentMethod(${data.id})">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarAlmacen(${data.id})">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${routeAccounts}" >
                                        <i class="fas fa-piggy-bank"></i> Cuentas
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


    function eliminarAlmacen(id){
        toastr.clear();
        let row             =   getRowById(dtPaymentMethods,id);
        let message         =   '';
        let tipo_documento  =   '';

        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: `DESEA ELIMINAR EL ALMACÉN?`,
        text: `Almacén: ${row.nombre}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {
            
            Swal.fire({
                title: 'Cargando...',
                html: 'Eliminando almacén...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                let urlDeleteAlmacen    =   null;
                urlDeleteAlmacen        =   urlDeleteAlmacen.replace(':id', id);
                const token             =   document.querySelector('input[name="_token"]').value;

                const response  =   await fetch(urlDeleteAlmacen, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        }
                                    });

                const   res =   await response.json();

                if(res.success){
                    dtPaymentMethods.draw();
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR AL ELIMINAR ALMACÉN');
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ELIMINAR ALMACÉN');
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

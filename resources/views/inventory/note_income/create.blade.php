@extends('layouts.template')

@section('title')
    NOTAS INGRESO
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

@include('utils.spinners.spinner_1')
@include('inventory.note_income.modals.mdl_products')
@include('inventory.note_income.modals.mdl_edit_item')


<div class="card-style settings-card-1 mb-30">
    <div class="title mb-30 d-flex justify-content-between align-items-center">
      <h6>Datos de la Nota de Ingreso <i class="fa-solid fa-toolbox"></i></h6>
    </div>
    <div class="card-body">
        @include('inventory.note_income.forms.form_create_note_income')
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <span  style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>
        
        <div style="display:flex;">
            <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                <i class="fa-solid fa-door-open"></i> VOLVER
            </button>
            <button class="btn btn-primary" type="submit" form="formRegistrarCotizacionCompra">
                <i class="fa-solid fa-floppy-disk"></i> REGISTRAR
            </button>
        </div>
    </div>
</div>
<!-- end card -->
@endsection

@section('js')
<script>
    let dtProductos         =   null;
    let dtCompraDetalle     =   null;
    const lstNoteIncome     =   [];

    document.addEventListener('DOMContentLoaded',()=>{
        loadSelect2();
        loadDataTableProducts();
        iniciarDataTableCompraDetalle();
        events();
    })

    function events(){
        eventsMdlEditItem();

        document.querySelector('#formRegistrarCotizacionCompra').addEventListener('submit',(e)=>{
            e.preventDefault();
            const validacion    =   validacionRegistrarCotizacionCompra();
            if(validacion){
                storeNoteIncome();
            }
        })

        document.addEventListener('click',(e)=>{
            if (e.target.closest('.btnVolver')) {
                const rutaIndex         =   '{{route('tenant.inventarios.nota_ingreso')}}';
                window.location.href    =   rutaIndex;
            }

            if (e.target.closest('.btnAgregarProducto')) {

                toastr.clear();
                const inputCantidad =   document.querySelector('#cantidad'); 
                const validacion    =   validationAddProduct();

                if(validacion){
                    mostrarAnimacion1();
                    addProduct({...product_selected},inputCantidad.value);
                    clearFormSelectProduct();
                    ocultarAnimacion1();
                }
              
            }
        })

    }

    function loadSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true  
        } );

        $( '.select2_form_mdl' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true,
            dropdownParent: $('#mdlProductos')  
        } );
    }

    function loadDataTableProducts(){
        const urlGetProductos   =   @json(route('tenant.inventarios.nota_ingreso.getProducts'));
        
        dtProductos  =   new DataTable('#tbl_products',{
            serverSide: true,  
            processing: true,  
            ajax: {
                url: urlGetProductos, 
                type: 'GET',  
                data: function(d) {
                    d.categoria_id  =   $('#categoria').val();  
                    d.marca_id      =   $('#marca').val();  
                },
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'brand_name', name: 'brand_name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'stock', name: 'Stock' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).css('cursor', 'pointer');
                
                $(row).attr('onclick', 'selectProduct(' + data.id + ')');
            },
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

    function iniciarDataTableCompraDetalle(){
        dtCompraDetalle  =   new DataTable('#tbl_note_income_detail',{
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

    function validationAddProduct(){
        
        if(!product_selected.product_id){
            toastr.error('DEBE SELECCIONAR UN PRODUCTO!!');
            return false;
        }

        const inputCantidad =   document.querySelector('#cantidad'); 
        if(!inputCantidad.value){
            toastr.error('DEBE INGRESAR UNA CANTIDAD!!');
            return false;
        }
        if(inputCantidad.value == 0){
            toastr.error('LA CANTIDAD DEBE SER MAYOR A 0!!');
            return false;
        }

        return true;
    }

    function validacionRegistrarCotizacionCompra(){
        if(lstNoteIncome.length === 0){
            toastr.error('EL DETALLE DE LA NOTA DE INGRESO ESTÁ VACÍO!!!');
            return false;
        }
        return true;
    }

    function addProduct(producto,cantidad){
        producto.quantity   =   cantidad;

        const indiceProducto    =   lstNoteIncome.findIndex((p)=>{
            return p.product_id == producto.product_id;
        })

        if(indiceProducto !== -1){
            toastr.error('EL PRODUCTO YA EXISTE EN EL DETALLE');
            return;
        }

        lstNoteIncome.push(producto);
        clearTable('tbl_note_income_detail');
        destroyDataTable(dtCompraDetalle);
        pintarTableCompraDetalle(lstNoteIncome);
        iniciarDataTableCompraDetalle();
        toastr.info('PRODUCTO AGREGADO AL DETALLE');
    }

    function pintarTableCompraDetalle(lstItems){
        let filas   =   ``;
        lstItems.forEach((producto)=>{
            filas   +=  `<tr>
                            <th>
                                <div style="display:flex;justify-content:center;gap:5px;">
                                    <i class="fas fa-edit btn btn-warning btnEditItem" data-producto-id="${producto.product_id}"></i>
                                    <i class="fas fa-trash-alt btn btn-danger btnDeleteItem" data-producto-id="${producto.product_id}"></i>
                                </div>
                            </th>
                            <td>${producto.product_name}</td>
                            <td>${producto.category_name}</td>
                            <td>${producto.brand_name}</td>
                            <td>NIU</td>
                            <td>${producto.quantity}</td>
                        </tr>`;
        })

        const tbody =   document.querySelector('#tbl_note_income_detail tbody');
        tbody.innerHTML =   filas;
    }

    
    function destruirDataTableProductos(){
        if(dtProductos){
            dtProductos.destroy();
            dtProductos =   null;
        }
    }

    function destruirDataTableCompraDetalle(){
        if(dtCompraDetalle){
            dtCompraDetalle.destroy();
            dtCompraDetalle =   null;
        }
    }

    function pintarProductos(lstProductos){
        let filas   =   ``;
        lstProductos.forEach((producto)=>{
            filas   +=  `<tr style="cursor:pointer;" onclick="selectProduct(${producto.producto_id});">
                            <th>${producto.producto_id}</th>
                            <td>${producto.producto_nombre}</td>
                            <td>${producto.categoria_nombre}</td>
                            <td>${producto.marca_nombre}</td>
                            <td>${producto.producto_stock}</td>
                        </tr>`;
        })

        const tbody =   document.querySelector('#table_productos tbody');
        tbody.innerHTML =   filas;
    }


    function storeNoteIncome(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA REGISTRAR LA NOTA DE INGRESO?",
        text: "Ingreso de stock en almacén central!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {
            clearValidationErrors('msgError');
            const token                             =   document.querySelector('input[name="_token"]').value;
            const formRegistrarCotizacionCompra     =   document.querySelector('#formRegistrarCotizacionCompra');
            const formData                          =   new FormData();
            const urlStoreNoteIncome                =   @json(route('tenant.inventarios.nota_ingreso.store'));

            formData.append('lstNoteIncome',JSON.stringify(lstNoteIncome));
            formData.append('user_recorder_id',@json($colaborador_registrador->id));
            formData.append('user_recorder_name',@json($colaborador_registrador->name));
            formData.append('observation',document.querySelector('#observation').value);


            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando nueva nota de ingreso...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                const response  =   await fetch(urlStoreNoteIncome, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();
                
                console.log(res);
                
                if(response.status === 422){
                    if('errors' in res){
                        pintarErroresValidacion(res.errors);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    const note_income_index     =   @json(route('tenant.inventarios.nota_ingreso'));
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    window.location.href        =   note_income_index;
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

              
            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR NOTA DE INGRESO');
                Swal.close();
            }
          

        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
            title: "OPERACIÓN CANCELADA",
            text: "NO SE REALIZARON ACCIONES",
            icon: "error"
            });
        }
        });
    }

    function pintarErroresValidacion(objErroresValidacion){
        for (let clave in objErroresValidacion) {
            const pError        =   document.querySelector(`.${clave}_error`);
            pError.textContent  =   objErroresValidacion[clave][0];
        }
    }

</script>
<script src="{{asset('assets/js/utils.js')}}"></script>
<script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection


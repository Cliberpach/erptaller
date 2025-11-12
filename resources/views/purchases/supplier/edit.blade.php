@extends('layouts.template')

@section('title')
    EDITAR PROVEEDOR
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

@include('utils.spinners.spinner_1')
<div class="card">
    <div class="card-header d-flex flex-row justify-content-between">
        <h6>EDITAR PROVEEDOR <i class="fa-solid fa-truck-field"></i></h6>
    </div>
    <div class="card-body">
        @include('purchases.supplier.forms.form_edit_supplier')
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-12" style="display:flex; justify-content:end;">
                <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                    <i class="fa-solid fa-door-open"></i> VOLVER
                </button>
                <button class="btn btn-primary" type="submit" form="formActualizarProveedor">
                    <i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR
                </button>
            </div>
            <div class="col-12">
                <span  style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>
            </div>
        </div>
    </div>
</div>
<!-- end card -->
@endsection


<script>
    document.addEventListener('DOMContentLoaded',()=>{
        iniciarSelect2();
        events();
    })

    function events(){

        document.querySelector('#formActualizarProveedor').addEventListener('submit',(e)=>{
            e.preventDefault();
            actualizarProveedor();
        })

        document.addEventListener('click',(e)=>{
            if (e.target.closest('.btnVolver')) {
                const rutaIndex         =   '{{route('tenant.compras.proveedor')}}';
                window.location.href    =   rutaIndex;
            }
        })

        //======= CONSULTAR API DOCUMENTO DNI ========
        document.querySelector('#btn_consultar_documento').addEventListener('click',()=>{
            const nro_documento     =   document.querySelector('#nro_documento').value;
            const tipo_documento    =   document.querySelector('#tipo_documento').value;
            toastr.clear();

            if(tipo_documento != 1 && tipo_documento != 3){
                toastr.error('SOLO SE PUEDE CONSULTAR TIPO DE DOCUMENTO DNI Y RUC');
                return;
            }

            if(!nro_documento){
                toastr.error('DEBE INGRESAR UN NRO DE DOCUMENTO VÁLIDO');
                return;
            }

            if(tipo_documento == 1){
                if(nro_documento.length != 8){
                    toastr.error('NRO DE DNI DEBE CONTAR CON 8 DÍGITOS');
                    return;
                }

            }

            if(tipo_documento == 3){
                if(nro_documento.length != 11){
                    toastr.error('NRO DE RUC DEBE CONTAR CON 11 DÍGITOS');
                    return;
                }
            }

            consultarDocumento(tipo_documento,nro_documento);

        })

        //========== PERMITIR SOLO FORMATO DE CELULAR O TELEFONO ======
        document.querySelector('#telefono').addEventListener('input',(e)=>{
            const input = e.target;
            const maxLength = 20;
            
            // Expresión regular para validar números de teléfono internacionales
            const validPattern = /^\+?[0-9]*$/;

            // Reemplaza cualquier carácter que no sea un dígito o "+"
            let value = input.value.replace(/[^0-9+]/g, '');

            // Asegúrate de que el símbolo '+' esté al principio
            if (value.startsWith('+')) {
                value = '+' + value.slice(1).replace(/^\+/, '');
            } else {
                value = value.replace(/^\+/, '');
            }

            // Limita el valor a 20 caracteres
            if (value.length > maxLength) {
                value = value.slice(0, maxLength);
            }

            // Actualiza el valor del input
            input.value = value;
        })

        //===== PERMITIR SOLO NUMEROS ========
        document.querySelector('#nro_documento').addEventListener('input', (e) => {
            const input = e.target;

            input.value = input.value.replace(/\D/g, '');
        });
    }

    function iniciarSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
        } ); 
    }

    function actualizarProveedor(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA ACTUALIZAR EL PROVEEDOR?",
        text: "Se realizarán cambios en la BD!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, ACTUALIZAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Actualizando proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                clearValidationErrors('msgError');
                const token                         =   document.querySelector('input[name="_token"]').value;
                const formActualizarProveedor       =   document.querySelector('#formActualizarProveedor');
                const formData                      =   new FormData(formActualizarProveedor);

                const id                  =   @json($supplier->id);
                let urlUpdateProveedor    =   `{{ route('tenant.compras.proveedor.update', ['id' => ':id']) }}`;
                urlUpdateProveedor        =   urlUpdateProveedor.replace(':id', id);

                const response  =   await fetch(urlUpdateProveedor, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-HTTP-Method-Override': 'PUT' 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();
                
                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    const proveedor_index     =   @json(route('tenant.compras.proveedor'));
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    window.location.href    =   proveedor_index;
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ACTUALIAR PROVEEDOR');
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

    //======== CHANGE TIPO DOCUMENTO ======
    function changeTipoDoc(params) {
        const tipo_documento        =   document.querySelector('#tipo_documento').value;
        const inputNroDoc           =   document.querySelector('#nro_documento');
        const btnConsultarDocumento =   document.querySelector('#btn_consultar_documento');
        
        inputNroDoc.readOnly            =   true;
        inputNroDoc.value               =   '';
        btnConsultarDocumento.disabled  =   true;

        //======== DNI =======
        if(tipo_documento == 1){
            inputNroDoc.readOnly            =   false;
            inputNroDoc.maxLength           =   8;
            btnConsultarDocumento.disabled  =   false;
        }

        //====== RUC =====
        if(tipo_documento == 3){
            inputNroDoc.readOnly            =   false;
            inputNroDoc.maxLength           =   11;
            btnConsultarDocumento.disabled  =   false;
        }
    }

    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
    async function consultarDocumento(tipo_documento,nro_documento){
        mostrarAnimacion1();
        try {
            const token                     =   document.querySelector('input[name="_token"]').value;
            const urlConsultarDocumento     =   `/proveedores/consultarDocumento?tipo_documento=${encodeURIComponent(tipo_documento)}&nro_documento=${encodeURIComponent(nro_documento)}`;
            
            const response  =   await fetch(urlConsultarDocumento, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': token 
                                    },
                                });

            const   res =   await response.json();

            if(res.success){
                if(tipo_documento == 1){
                    setDatosDni(res.data.data);
                }
                if(tipo_documento == 3){
                    //console.log(res);
                    setDatosRuc(res.data.data);
                }

                toastr.info(res.message);
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR AL CONSULTAR DOCUMENTO');
            }
        } catch (error) {
            toastr.error(error,'ERROR EN LA PETICIÓN CONSULTAR DOCUMENTO');
        }finally{
            ocultarAnimacion1();
        }
    }

    function setDatosRuc(data){
        const nombre_o_razon_social     =   `${data.nombre_o_razon_social}`;
        const direccion_completa                 =   data.direccion_completa;

        document.querySelector('#nombre').value     =   nombre_o_razon_social;
        document.querySelector('#direccion').value  =   direccion_completa;
    }

    function setDatosDni(data){
        const nombre_completo   =   `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`;
        const direccion         =   data.direccion;

        document.querySelector('#nombre').value     =   nombre_completo;
        document.querySelector('#direccion').value  =   direccion;
    }

</script>
<script src="{{asset('assets/js/utils.js')}}"></script>



<div class="modal fade" id="mdlCreateProveedor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Proveedor</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @include('utils.modals.suppliers.forms.form_create_supplier')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary btnRegistrarproveedor" type="submit" form="formRegistrarProveedor">
            <i class="fa-solid fa-floppy-disk"></i> Registrar
        </button>
        </div>
      </div>
    </div>
</div>


<script>

    function eventsMdlCreateProveedor(){
        document.querySelector('#formRegistrarProveedor').addEventListener('submit',(e)=>{
            e.preventDefault();
            registrarproveedor();
        })

        $('#mdlCreateProveedor').on('hidden.bs.modal', function () {
           
            //======= RESETAER FORMULARIO ======
            $('#tipo_documento').val($('#tipo_documento option:first').val()).trigger('change');
            document.querySelector('#nombre').value     =   '';
            document.querySelector('#direccion').value  =   '';
            document.querySelector('#telefono').value   =   '';
            document.querySelector('#correo').value     =   '';

            limpiarErroresValidacion('msgErrorProveedor');

       });

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
    }

    function openMdlNuevoProveedor(){
        $('#mdlCreateProveedor').modal('show');
    }

    function registrarproveedor(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA REGISTRAR EL PROVEEDOR?",
        text: "Se creará un nuevo Proveedor!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

         
            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando nuevo proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {

                clearValidationErrors('msgError');
                const token                     =   document.querySelector('input[name="_token"]').value;
                const formRegistrarProveedor    =   document.querySelector('#formRegistrarProveedor');
                const formData                  =   new FormData(formRegistrarProveedor);
                const urlRegistrarProveedor     =   @json(route('tenant.compras.proveedor.store'));

                const response  =   await fetch(urlRegistrarProveedor, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();
                                
                if(response.status === 422){
                    if('errors' in res){
                        pintarErroresValidacionProveedor(res.errors);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    //======== TRAER LISTADO DE PROVEEDORES ACTUALIZADO =====
                    const lstProveedoresActualizados =   await getProveedoresActualizados();

                    //========= REPINTAR SELECT2 DE proveedor ========
                    pintarSelect2Proveedores(lstProveedoresActualizados);
                    // console.log('lst proveedors');
                    // console.log(lstProveedoresActualizados);
                    $('#mdlCreateProveedor').modal('hide');
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR PROVEEDOR');
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

    function pintarSelect2Proveedores(lstProveedores){
        $('#proveedor').empty();

        $('#proveedor').append('<option></option>');

        lstProveedores.forEach(function(proveedor) {
            $('#proveedor').append(
                $('<option></option>').val(proveedor.id).text(`${proveedor.type_document_abbreviation}:${proveedor.document_number}-${proveedor.name}`)
            );
        });

        $('#proveedor').select2({
            allowClear: true,
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
        });

        const lastId    =   lstProveedores[lstProveedores.length -1].id;

        if (lastId) {
            $('#proveedor').val(lastId).trigger('change'); 
        }
    }


    function pintarErroresValidacionProveedor(objErroresValidacion){
        for (let clave in objErroresValidacion) {
            const pError        =   document.querySelector(`.${clave}_error_proveedor`);
            pError.textContent  =   objErroresValidacion[clave][0];
        }
    }

    async function getProveedoresActualizados(){
        try {
            toastr.clear();
            const token                     =   document.querySelector('input[name="_token"]').value;
            const urlGetListProveedores     =   @json(route('tenant.compras.proveedor.getLstSuppliers'));

            const response  =   await fetch(urlGetListProveedores, {
                                        method: 'GET',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                    });

            const   res     =   await response.json();
                
            if(res.success){
                
                toastr.clear();
                toastr.info(res.message,'PROVEEDORES OBTENIDOS');
                return res.lstSuppliers;
                
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR');
                return null;
            }
 
        } catch (error) {
            toastr.error(error,'ERROR EN LA PETICIÓN OBTENER PROVEEDORES');
            return null;
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
            const urlConsultarDocumento     =   @json(route('tenant.compras.proveedor.consultarDocumento'));
            const urlWithParams = new URL(urlConsultarDocumento);
            urlWithParams.searchParams.append('tipo_documento', tipo_documento);
            urlWithParams.searchParams.append('nro_documento', nro_documento);

            const response  =   await fetch(urlWithParams, {
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
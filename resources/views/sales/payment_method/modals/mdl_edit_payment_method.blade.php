<div class="modal fade" id="mdlEditPaymentMethod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Método de pago</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @include('sales.payment_method.forms.form_edit_payment_method')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary btnActualizarAlmacen" type="submit" form="formUpdatePaymentMethod">
            <i class="fa-solid fa-floppy-disk"></i> Actualizar
        </button>
        </div>
      </div>
    </div>
</div>


<script>
    let rowEditar   =   null;

    function eventsMdlEditPaymentMethod(){
        document.querySelector('#formUpdatePaymentMethod').addEventListener('submit',(e)=>{
            e.preventDefault();
            updatePaymentMethod();
        })

        $('#mdlEditPaymentMethod').on('hidden.bs.modal', function (e) {
            const   formUpdatePaymentMethod    =   document.querySelector('#formUpdatePaymentMethod');
            formUpdatePaymentMethod.reset();
            limpiarErroresValidacion('msgError_edit');
        });
    }

    function openMdlEditPaymentMethod(id){
        rowEditar  =   getRowById(dtPaymentMethods,id);

        if(!rowEditar){
            toastr.error('NO SE ENCONTRÓ EL ALMACÉN EN EL DATATABLE');
            return;
        }

        //======== SETTEANDO DATA ========
        document.querySelector('#descripcion_edit').value   =   rowEditar.description;

        $('#mdlEditPaymentMethod').modal('show');
    }

    function updatePaymentMethod(){
        
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA ACTUALIZAR EL MÉTODO DE PAGO?",
        text: `Método de pago: ${rowEditar.description}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, ACTUALIZAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            clearValidationErrors('msgError_edit')
            const token                     =   document.querySelector('input[name="_token"]').value;
            const formUpdatePaymentMethod   =   document.querySelector('#formUpdatePaymentMethod');
            const formData                  =   new FormData(formUpdatePaymentMethod);
            let urlUpdatePaymentMethod      =   `{{ route('tenant.ventas.metodo_pago.update', ['id' => ':id']) }}`;
            urlUpdatePaymentMethod          =   urlUpdatePaymentMethod.replace(':id', rowEditar.id);

            Swal.fire({
                title: 'Cargando...',
                html: 'Actualizando método de pago...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                const response  =   await fetch(urlUpdatePaymentMethod, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-HTTP-Method-Override': 'PUT' 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();
                
                console.log(res);
                
                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors,'error');
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    dtPaymentMethods.draw();
                    $('#mdlEditPaymentMethod').modal('hide');
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ACTUALIZAR MÉTODO DE PAGO');
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

</script>
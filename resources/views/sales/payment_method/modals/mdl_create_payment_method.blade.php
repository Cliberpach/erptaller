<div class="modal fade" id="mdlCreatePaymentMethod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Método de pago</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @include('sales.payment_method.forms.form_create_payment_method')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary btnRegistrarMarca" type="submit" form="formPaymentMethod">
            <i class="fa-solid fa-floppy-disk"></i> Registrar
        </button>
        </div>
      </div>
    </div>
</div>


<script>

    function eventsMdlCreatePaymentMethod(){
        document.querySelector('#formPaymentMethod').addEventListener('submit',(e)=>{
            e.preventDefault();
            storePaymentMethod();
        })

        $('#mdlCreatePaymentMethod').on('hidden.bs.modal', function (e) {
            const   formPaymentMethod    =   document.querySelector('#formPaymentMethod');
            formPaymentMethod.reset();
            limpiarErroresValidacion('msgError');
        });

    }

    function openMdlCreatePaymentMethod(){
        $('#mdlCreatePaymentMethod').modal('show');
    }

    function storePaymentMethod(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA REGISTRAR EL MÉTODO DE PAGO?",
        text: "Se creará un nuevo método de pago!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            clearValidationErrors('msgError')
            const token                     =   document.querySelector('input[name="_token"]').value;
            const formPaymentMethod         =   document.querySelector('#formPaymentMethod');
            const formData                  =   new FormData(formPaymentMethod);
            const urlStorePaymentMethod     =   @json(route('tenant.ventas.metodo_pago.store'));

            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando nuevo método de pago...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                const response  =   await fetch(urlStorePaymentMethod, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();
                                
                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors,'error')
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    dtPaymentMethods.draw();
                    $('#mdlCreatePaymentMethod').modal('hide');
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

              
            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR MÉTODO DE PAGO');
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
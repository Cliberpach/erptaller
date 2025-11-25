<div class="modal fade" id="mdlCreateCargo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Cargo</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @include('maintenance.positions.forms.form_create_cargo')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary btnRegistrarCargo" type="submit" form="formRegistrarCargo">
            <i class="fa-solid fa-floppy-disk"></i> Registrar
        </button>
        </div>
      </div>
    </div>
</div>


<script>

    function eventsMdlCreateCargo(){
        document.querySelector('#formRegistrarCargo').addEventListener('submit',(e)=>{
            e.preventDefault();
            registrarCargo();
        })

        $('#mdlCreateCargo').on('hidden.bs.modal', function (e) {
            const   formRegistrarCargo    =   document.querySelector('#formRegistrarCargo');
            formRegistrarCargo.reset();
            clearValidationErrors('msgError');
        });

    }

    function openMdlNuevoCargo(){
        $('#mdlCreateCargo').modal('show');
    }

    function registrarCargo(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA REGISTRAR EL CARGO?",
        text: "Se creará un nuevo cargo!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {
            clearValidationErrors('msgError');
            const token                     =   document.querySelector('input[name="_token"]').value;
            const formRegistrarCargo        =   document.querySelector('#formRegistrarCargo');
            const formData                  =   new FormData(formRegistrarCargo);
            const urlRegistrarCargo         =   @json(route('tenant.mantenimientos.cargos.store'));

            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando nuevo cargo...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response  =   await fetch(urlRegistrarCargo, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors,'error');
                    }
                    Swal.close();
                    return;
                }

                if(res.success){
                    dtCargos.draw();
                    $('#mdlCreateCargo').modal('hide');
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }


            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR CARGO');
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

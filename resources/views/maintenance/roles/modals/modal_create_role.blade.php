<div class="modal fade" id="mdlCreateRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Registrar Rol</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="formRegistrarRol" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="name" style="font-weight: bold;" class="required_field">Nombre del rol</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                            <input required id="name" name="name" type="text" class="form-control"
                                style="background-color:#FFF9C4;" placeholder="ej. supervisor">
                        </div>
                        <span class="name_error msgError" style="color:red;"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit" form="formRegistrarRol">
                    <i class="fa-solid fa-floppy-disk"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlCreateRol() {
        document.querySelector('#formRegistrarRol').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarRol();
        });
        $('#mdlCreateRol').on('hidden.bs.modal', function () {
            document.querySelector('#formRegistrarRol').reset();
            clearValidationErrors('msgError');
        });
    }

    function openMdlNuevoRol() {
        $('#mdlCreateRol').modal('show');
    }

    function registrarRol() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "¿DESEA REGISTRAR EL ROL?",
            text: "Se creará un nuevo rol (sin permisos; luego se los asignás).",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            clearValidationErrors('msgError');
            const token = document.querySelector('input[name="_token"]').value;
            const formData = new FormData(document.querySelector('#formRegistrarRol'));
            const url = @json(route('tenant.mantenimientos.roles.store'));

            Swal.fire({ title: 'Cargando...', html: 'Registrando rol...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData });
                const res = await response.json();
                if (response.status === 422) {
                    if ('errors' in res) paintValidationErrors(res.errors, 'error');
                    Swal.close();
                    return;
                }
                if (res.success) {
                    dtRoles.draw();
                    $('#mdlCreateRol').modal('hide');
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    Swal.close();
                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    Swal.close();
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR ROL');
                Swal.close();
            }
        });
    }
</script>

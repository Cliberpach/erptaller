<div class="modal fade" id="mdlCreateAlmacen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Registrar Almacén</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.warehouses.forms.form_create_warehouse')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit" form="formRegistrarAlmacen">
                    <i class="fa-solid fa-floppy-disk"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlCreateAlmacen() {
        document.querySelector('#formRegistrarAlmacen').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarAlmacen();
        });
        $('#mdlCreateAlmacen').on('hidden.bs.modal', function () {
            document.querySelector('#formRegistrarAlmacen').reset();
            clearValidationErrors('msgError');
        });
    }

    function openMdlNuevoAlmacen() {
        $('#mdlCreateAlmacen').modal('show');
    }

    function registrarAlmacen() {
        const swalBtns = Swal.mixin({ customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" }, buttonsStyling: false });
        swalBtns.fire({
            title: "¿DESEA REGISTRAR EL ALMACÉN?",
            text: "Se creará en la sede activa.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            clearValidationErrors('msgError');
            const token = document.querySelector('input[name="_token"]').value;
            const formData = new FormData(document.querySelector('#formRegistrarAlmacen'));
            const url = @json(route('tenant.inventarios.almacenes.store'));

            Swal.fire({ title: 'Cargando...', html: 'Registrando almacén...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData });
                const res = await response.json();
                if (response.status === 422) {
                    if ('errors' in res) paintValidationErrors(res.errors, 'error');
                    Swal.close();
                    return;
                }
                if (res.success) {
                    dtAlmacenes.draw();
                    $('#mdlCreateAlmacen').modal('hide');
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    Swal.close();
                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    Swal.close();
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR ALMACÉN');
                Swal.close();
            }
        });
    }
</script>

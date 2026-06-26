<div class="modal fade" id="mdlEditAlmacen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Editar Almacén</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.warehouses.forms.form_edit_warehouse')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit" form="formActualizarAlmacen">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlEditAlmacen() {
        document.querySelector('#formActualizarAlmacen').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarAlmacen();
        });
        $('#mdlEditAlmacen').on('hidden.bs.modal', function () {
            document.querySelector('#formActualizarAlmacen').reset();
            clearValidationErrors('msgError_edit');
        });
    }

    function openMdlEditAlmacen(id) {
        // Carga vía endpoint (incluye el check de seguridad por sede → 403 si no es del usuario).
        const url = `{{ route('tenant.inventarios.almacenes.edit', ['id' => ':id']) }}`.replace(':id', id);
        Swal.fire({ title: 'Cargando...', html: 'Obteniendo almacén...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(url)
            .then(r => r.json().then(res => ({ status: r.status, res })))
            .then(({ status, res }) => {
                Swal.close();
                if (!res.success) { toastr.error(res.message, status === 403 ? 'ACCESO DENEGADO' : 'ERROR'); return; }
                document.querySelector('#almacen_id_edit').value   = res.warehouse.id;
                document.querySelector('#descripcion_edit').value  = res.warehouse.descripcion;
                $('#mdlEditAlmacen').modal('show');
            })
            .catch(err => { Swal.close(); toastr.error(err, 'ERROR EN LA PETICIÓN'); });
    }

    function actualizarAlmacen() {
        const swalBtns = Swal.mixin({ customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" }, buttonsStyling: false });
        swalBtns.fire({
            title: "¿DESEA ACTUALIZAR EL ALMACÉN?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            clearValidationErrors('msgError_edit');
            const id = document.querySelector('#almacen_id_edit').value;
            const token = document.querySelector('input[name="_token"]').value;
            const formData = new FormData(document.querySelector('#formActualizarAlmacen'));
            let url = `{{ route('tenant.inventarios.almacenes.update', ['id' => ':id']) }}`.replace(':id', id);

            Swal.fire({ title: 'Cargando...', html: 'Actualizando almacén...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-HTTP-Method-Override': 'PUT' },
                    body: formData
                });
                const res = await response.json();
                if (response.status === 422) {
                    if ('errors' in res) paintValidationErrors(res.errors, 'error');
                    Swal.close();
                    return;
                }
                if (res.success) {
                    dtAlmacenes.draw();
                    $('#mdlEditAlmacen').modal('hide');
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    Swal.close();
                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    Swal.close();
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR ALMACÉN');
                Swal.close();
            }
        });
    }
</script>

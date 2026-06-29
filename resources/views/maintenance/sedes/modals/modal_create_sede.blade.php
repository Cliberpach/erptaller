<div class="modal fade" id="mdlCreateSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Registrar Sede</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('maintenance.sedes.forms.form_create_sede')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary btnRegistrarSede" type="submit" form="formRegistrarSede">
                    <i class="fa-solid fa-floppy-disk"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Ubigeo de la sede = 3 selects encadenados (mismo mecanismo que Registrar Cliente):
    // data precargada + filtrado en JS. dropdownParent al modal de creación.
    function iniciarSelect2Sede() {
        $('.select2_form_sede').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccionar',
            dropdownParent: $('#mdlCreateSede'),
        });
    }

    function changeDepartmentSede(department_id) {
        const lstProvinces = @json($provinces);
        $('#province').empty().append(new Option('Seleccionar', '', false, false));
        $('#ubigeo').empty().append(new Option('Seleccionar', '', false, false)).trigger('change');

        if (department_id) {
            department_id = String(department_id).padStart(2, '0');
            lstProvinces
                .filter((p) => p.department_id == department_id)
                .forEach((p) => $('#province').append(new Option(p.name, p.id, false, false)));
        }
        $('#province').trigger('change');
    }

    function changeProvinceSede(province_id) {
        const lstDistricts = @json($districts);
        $('#ubigeo').empty().append(new Option('Seleccionar', '', false, false));

        if (province_id) {
            province_id = String(province_id).padStart(4, '0');
            lstDistricts
                .filter((d) => d.province_id == province_id)
                .forEach((d) => $('#ubigeo').append(new Option(d.name, d.id, false, false)));
        }
        $('#ubigeo').trigger('change');
    }

    function eventsMdlCreateSede() {
        iniciarSelect2Sede();

        document.querySelector('#formRegistrarSede').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarSede();
        });

        $('#mdlCreateSede').on('hidden.bs.modal', function () {
            document.querySelector('#formRegistrarSede').reset();
            // Reset de los selects de ubigeo (select2 no se limpia con form.reset()).
            $('#province').empty().append(new Option('Seleccionar', '', false, false));
            $('#ubigeo').empty().append(new Option('Seleccionar', '', false, false));
            $('.select2_form_sede').val('').trigger('change');
            clearValidationErrors('msgError');
        });
    }

    function openMdlNuevaSede() {
        $('#mdlCreateSede').modal('show');
    }

    function registrarSede() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "¿DESEA REGISTRAR LA SEDE?",
            text: "Se creará una nueva sede!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(document.querySelector('#formRegistrarSede'));
                const url = @json(route('tenant.mantenimientos.sedes.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nueva sede...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtSedes.draw();
                        $('#mdlCreateSede').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }
                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR SEDE');
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

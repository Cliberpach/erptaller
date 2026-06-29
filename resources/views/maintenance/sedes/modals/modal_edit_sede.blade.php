<div class="modal fade" id="mdlEditSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Editar Sede</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('maintenance.sedes.forms.form_edit_sede')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary btnActualizarSede" type="submit" form="formActualizarSede">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let rowEditarSede = null;

    // Ubigeo de la sede = 3 selects encadenados (mismo mecanismo que Registrar Cliente).
    // dropdownParent al modal de EDICIÓN (que no se abra detrás del modal).
    function iniciarSelect2SedeEdit() {
        $('.select2_form_sede_edit').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccionar',
            dropdownParent: $('#mdlEditSede'),
        });
    }

    function changeDepartmentSedeEdit(department_id) {
        const lstProvinces = @json($provinces);
        $('#province_edit').empty().append(new Option('Seleccionar', '', false, false));
        $('#ubigeo_edit').empty().append(new Option('Seleccionar', '', false, false)).trigger('change');

        if (department_id) {
            department_id = String(department_id).padStart(2, '0');
            lstProvinces
                .filter((p) => p.department_id == department_id)
                .forEach((p) => $('#province_edit').append(new Option(p.name, p.id, false, false)));
        }
        $('#province_edit').trigger('change');
    }

    function changeProvinceSedeEdit(province_id) {
        const lstDistricts = @json($districts);
        $('#ubigeo_edit').empty().append(new Option('Seleccionar', '', false, false));

        if (province_id) {
            province_id = String(province_id).padStart(4, '0');
            lstDistricts
                .filter((d) => d.province_id == province_id)
                .forEach((d) => $('#ubigeo_edit').append(new Option(d.name, d.id, false, false)));
        }
        $('#ubigeo_edit').trigger('change');
    }

    function eventsMdlEditSede() {
        iniciarSelect2SedeEdit();

        document.querySelector('#formActualizarSede').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarSede();
        });

        $('#mdlEditSede').on('hidden.bs.modal', function () {
            document.querySelector('#formActualizarSede').reset();
            $('#province_edit').empty().append(new Option('Seleccionar', '', false, false));
            $('#ubigeo_edit').empty().append(new Option('Seleccionar', '', false, false));
            $('.select2_form_sede_edit').val('').trigger('change');
            clearValidationErrors('msgError_edit');
        });
    }

    function openMdlEditSede(id) {
        rowEditarSede = getRowById(dtSedes, id);

        if (!rowEditarSede) {
            toastr.error('NO SE ENCONTRÓ LA SEDE EN EL DATATABLE');
            return;
        }

        document.querySelector('#sede_id_edit').value   = rowEditarSede.id;
        document.querySelector('#nombre_edit').value    = rowEditarSede.nombre;
        document.querySelector('#codigo_edit').value    = rowEditarSede.codigo;
        document.querySelector('#direccion_edit').value = rowEditarSede.direccion ?? '';
        document.querySelector('#telefono_edit').value  = rowEditarSede.telefono ?? '';

        // Descomponer el ubigeo guardado (6 díg) y preseleccionar los 3 selects:
        // dep = primeros 2, prov = primeros 4, distrito = los 6 (= ubigeo).
        const ubigeo = rowEditarSede.ubigeo ?? '';
        if (ubigeo && ubigeo.length === 6) {
            const dep  = ubigeo.substring(0, 2);
            const prov = ubigeo.substring(0, 4);
            const dist = ubigeo;

            $('#department_edit').val(dep).trigger('change');
            changeDepartmentSedeEdit(dep);   // carga provincias
            $('#province_edit').val(prov).trigger('change');
            changeProvinceSedeEdit(prov);    // carga distritos
            $('#ubigeo_edit').val(dist).trigger('change');
        } else {
            // Sin ubigeo: limpiar los 3.
            $('#department_edit').val('').trigger('change');
            $('#province_edit').empty().append(new Option('Seleccionar', '', false, false)).trigger('change');
            $('#ubigeo_edit').empty().append(new Option('Seleccionar', '', false, false)).trigger('change');
        }

        $('#mdlEditSede').modal('show');
    }

    function actualizarSede() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "¿DESEA ACTUALIZAR LA SEDE?",
            text: `Sede: ${rowEditarSede.nombre}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError_edit');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(document.querySelector('#formActualizarSede'));
                let url = `{{ route('tenant.mantenimientos.sedes.update', ['id' => ':id']) }}`;
                url = url.replace(':id', rowEditarSede.id);

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando sede...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
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
                        $('#mdlEditSede').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }
                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR SEDE');
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

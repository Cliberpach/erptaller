@php
    $labelsModulo = [
        'dashboard'     => 'Dashboard',
        'cajas'         => 'Cajas',
        'taller'        => 'Taller',
        'ventas'        => 'Ventas',
        'reservas'      => 'Reservas / Campos',
        'inventario'    => 'Inventario',
        'compras'       => 'Compras',
        'cuentas'       => 'Cuentas',
        'reportes'      => 'Reportes',
        'consultas'     => 'Consultas',
        'mantenimiento' => 'Mantenimiento',
    ];
@endphp

<div class="modal fade" id="mdlPermisos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Permisos del rol: <span id="permisos_role_name" class="text-primary"></span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permisos_role_id">

                <div class="d-flex justify-content-end mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkTodosGlobal">
                        <label class="form-check-label fw-bold" for="chkTodosGlobal">Marcar TODOS</label>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ($permisosAgrupados as $modulo => $permisos)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                    <strong>{{ $labelsModulo[$modulo] ?? ucfirst($modulo) }}</strong>
                                    <div class="form-check m-0">
                                        <input class="form-check-input chk-group" type="checkbox" data-group="{{ $modulo }}"
                                            id="chk_group_{{ $modulo }}">
                                        <label class="form-check-label small" for="chk_group_{{ $modulo }}">todos</label>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    @foreach ($permisos as $permiso)
                                        <div class="form-check">
                                            <input class="form-check-input chk-permiso" type="checkbox"
                                                data-group="{{ $modulo }}"
                                                value="{{ $permiso->name }}"
                                                id="perm_{{ $permiso->id }}">
                                            <label class="form-check-label" for="perm_{{ $permiso->id }}">
                                                {{ str_replace($modulo . '.', '', $permiso->name) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="button" onclick="guardarPermisos()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar permisos
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlPermisos() {
        // Marcar todos (global)
        document.querySelector('#chkTodosGlobal').addEventListener('change', function () {
            document.querySelectorAll('.chk-permiso, .chk-group').forEach(c => c.checked = this.checked);
        });
        // Marcar todos por grupo
        document.querySelectorAll('.chk-group').forEach(g => {
            g.addEventListener('change', function () {
                const grupo = this.dataset.group;
                document.querySelectorAll(`.chk-permiso[data-group="${grupo}"]`).forEach(c => c.checked = this.checked);
            });
        });
    }

    function abrirPermisos(id, name) {
        document.querySelector('#permisos_role_id').value = id;
        document.querySelector('#permisos_role_name').textContent = name;
        // limpiar
        document.querySelectorAll('.chk-permiso, .chk-group').forEach(c => c.checked = false);
        document.querySelector('#chkTodosGlobal').checked = false;

        const url = `{{ route('tenant.mantenimientos.roles.permisos', ['id' => ':id']) }}`.replace(':id', id);

        Swal.fire({ title: 'Cargando...', html: 'Obteniendo permisos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(url)
            .then(r => r.json())
            .then(res => {
                Swal.close();
                if (!res.success) { toastr.error(res.message, 'ERROR'); return; }
                (res.permisos || []).forEach(p => {
                    const chk = document.querySelector(`.chk-permiso[value="${p}"]`);
                    if (chk) chk.checked = true;
                });
                // sincronizar checks de grupo
                document.querySelectorAll('.chk-group').forEach(g => {
                    const grupo = g.dataset.group;
                    const total = document.querySelectorAll(`.chk-permiso[data-group="${grupo}"]`).length;
                    const marcados = document.querySelectorAll(`.chk-permiso[data-group="${grupo}"]:checked`).length;
                    g.checked = total > 0 && total === marcados;
                });
                $('#mdlPermisos').modal('show');
            })
            .catch(err => { Swal.close(); toastr.error(err, 'ERROR EN LA PETICIÓN'); });
    }

    function guardarPermisos() {
        const id = document.querySelector('#permisos_role_id').value;
        const permisos = Array.from(document.querySelectorAll('.chk-permiso:checked')).map(c => c.value);

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "¿GUARDAR LOS PERMISOS DEL ROL?",
            text: `Se aplicarán ${permisos.length} permiso(s).`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, GUARDAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            const token = document.querySelector('input[name="_token"]').value;
            const url = `{{ route('tenant.mantenimientos.roles.syncPermisos', ['id' => ':id']) }}`.replace(':id', id);

            const formData = new FormData();
            permisos.forEach(p => formData.append('permisos[]', p));

            Swal.fire({ title: 'Cargando...', html: 'Guardando permisos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData });
                const res = await response.json();
                if (res.success) {
                    dtRoles.draw();
                    $('#mdlPermisos').modal('hide');
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    Swal.close();
                } else {
                    toastr.warning(res.message, 'OPERACIÓN NO PERMITIDA');
                    Swal.close();
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN GUARDAR PERMISOS');
                Swal.close();
            }
        });
    }
</script>

@extends('layouts.template')

@section('title')
    CAJAS
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">CAJAS <small class="text-muted">(por sede)</small></h4>
            <a onclick="openMdlNuevaCaja()" class="btn btn-primary text-white">
                <i class="fas fa-plus-circle"></i> Nueva caja
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="table_cajas">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>SEDE</th>
                            <th>VENDEDORES</th>
                            <th>ESTADO</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== MODAL NUEVA CAJA ===== --}}
    <div class="modal fade" id="mdlCreateCaja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"><i class="fa-solid fa-cash-register"></i> Registrar Caja</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold required_field">Sede</label>
                        <select id="c_sede" class="form-select" style="background-color:#FFF9C4;">
                            @foreach ($sedes as $s)
                                <option value="{{ $s->id }}" {{ (int)$s->id === (int)$sedeActivaId ? 'selected' : '' }}>{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold required_field">Nombre</label>
                        <input id="c_name" type="text" class="form-control" style="background-color:#FFF9C4;" placeholder="ej. CAJA PRINCIPAL">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Combo de vendedores</label>
                        <div id="c_vendedores" class="border rounded p-2" style="max-height:180px; overflow-y:auto;"></div>
                    </div>
                    <div id="c_warn_combo" class="alert alert-warning py-2 mb-0" style="display:none;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Esta caja no tiene vendedores asignados; nadie podrá aperturarla.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCaja()"><i class="fa-solid fa-floppy-disk"></i> Registrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL EDITAR CAJA ===== --}}
    <div class="modal fade" id="mdlEditCaja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"><i class="fa-solid fa-pen-to-square"></i> Editar Caja</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="e_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sede</label>
                        <div><span class="badge bg-info" id="e_sede_nombre"></span> <small class="text-muted">(no se puede cambiar)</small></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold required_field">Nombre</label>
                        <input id="e_name" type="text" class="form-control" style="background-color:#FFF9C4;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Combo de vendedores</label>
                        <div id="e_vendedores" class="border rounded p-2" style="max-height:180px; overflow-y:auto;"></div>
                    </div>
                    <div id="e_warn_combo" class="alert alert-warning py-2 mb-0" style="display:none;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Esta caja no tiene vendedores asignados; nadie podrá aperturarla.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarCaja()"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>.swal2-container { z-index: 9999999; }</style>

<script>
    const usuariosPorSede = @json($usuariosPorSede);
    const sedesNombre = @json($sedes->pluck('nombre', 'id'));
    let dtCajas = null;

    document.addEventListener('DOMContentLoaded', () => {
        dtCajas = new DataTable('#table_cajas', {
            serverSide: true, processing: true,
            ajax: { url: '{{ route('tenant.cajas.getListCash') }}', type: 'GET' },
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name' },
                { data: 'sede_nombre', name: 'sede_nombre', orderable: false, render: d => `<span class="badge bg-info">${d ?? '-'}</span>` },
                { data: 'combo_count', name: 'combo_count', orderable: false, searchable: false,
                  render: d => Number(d) > 0 ? `<span class="badge bg-success">${d} vendedor(es)</span>` : '<span class="badge bg-warning text-dark">sin combo</span>' },
                { data: 'status', name: 'status', orderable: false, searchable: false,
                  render: d => d === 'ABIERTO' ? '<span class="badge bg-primary">ABIERTA</span>' : '<span class="badge bg-secondary">CERRADA</span>' },
                { data: null, orderable: false, searchable: false, name: 'actions', render: (data, t, row) => `
                    <div class="btn-group dropstart">
                      <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown"><i class="fa-solid fa-grip"></i></button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="openMdlEditCaja(${row.id})"><i class="fa-solid fa-pen-to-square"></i> Editar</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="anularCaja(${row.id}, '${(row.name||'').replace(/'/g,'')}')"><i class="fa-solid fa-ban"></i> Anular</a></li>
                      </ul>
                    </div>` }
            ],
            language: { emptyTable: "No hay cajas en sus sedes", zeroRecords: "Sin resultados", processing: "Procesando...",
                info: "Mostrando _START_ a _END_ de _TOTAL_", search: "Buscar:", lengthMenu: "Mostrar _MENU_", paginate: { next: "Siguiente", previous: "Anterior" } }
        });
    });

    function renderVendedores(containerId, warnId, sedeId, selected = []) {
        const users = usuariosPorSede[sedeId] || [];
        const cont = document.getElementById(containerId);
        if (!users.length) { cont.innerHTML = '<div class="text-muted small">No hay usuarios en esta sede.</div>'; }
        else {
            cont.innerHTML = users.map(u => `
                <div class="form-check">
                    <input class="form-check-input vchk" type="checkbox" value="${u.id}" id="${containerId}_${u.id}" ${selected.map(Number).includes(Number(u.id))?'checked':''} onchange="toggleWarn('${containerId}','${warnId}')">
                    <label class="form-check-label" for="${containerId}_${u.id}">${u.name}</label>
                </div>`).join('');
        }
        toggleWarn(containerId, warnId);
    }
    function getVendedores(containerId) {
        return [...document.querySelectorAll(`#${containerId} .vchk:checked`)].map(c => Number(c.value));
    }
    function toggleWarn(containerId, warnId) {
        document.getElementById(warnId).style.display = getVendedores(containerId).length === 0 ? 'block' : 'none';
    }

    function openMdlNuevaCaja() {
        document.getElementById('c_name').value = '';
        const sedeSel = document.getElementById('c_sede');
        renderVendedores('c_vendedores', 'c_warn_combo', sedeSel.value, []);
        sedeSel.onchange = () => renderVendedores('c_vendedores', 'c_warn_combo', sedeSel.value, []);
        new bootstrap.Modal('#mdlCreateCaja').show();
    }

    async function guardarCaja() {
        toastr.clear();
        const body = { name: document.getElementById('c_name').value.trim(), sede_id: document.getElementById('c_sede').value, vendedores: getVendedores('c_vendedores') };
        const token = document.querySelector('input[name="_token"]').value;
        try {
            const r = await fetch('{{ route('tenant.cajas.store') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
            const res = await r.json();
            if (r.status === 422) { toastr.warning(Object.values(res.errors).flat().join(' '), 'VALIDACIÓN'); return; }
            if (res.success) { dtCajas.draw(); bootstrap.Modal.getInstance('#mdlCreateCaja').hide(); toastr.success(res.message, 'OPERACIÓN COMPLETADA'); }
            else toastr.error(res.message, 'ERROR');
        } catch (e) { toastr.error(e, 'ERROR'); }
    }

    async function openMdlEditCaja(id) {
        toastr.clear();
        try {
            const r = await fetch('{{ route('tenant.cajas.getCash', ['id' => ':id']) }}'.replace(':id', id));
            const res = await r.json();
            if (!res.success) { toastr.error(res.message, 'ERROR'); return; }
            const c = res.data;
            document.getElementById('e_id').value = c.id;
            document.getElementById('e_sede_nombre').textContent = sedesNombre[c.sede_id] ?? '-';
            document.getElementById('e_name').value = c.name;
            renderVendedores('e_vendedores', 'e_warn_combo', c.sede_id, c.vendedores);
            new bootstrap.Modal('#mdlEditCaja').show();
        } catch (e) { toastr.error(e, 'ERROR'); }
    }

    async function actualizarCaja() {
        toastr.clear();
        const id = document.getElementById('e_id').value;
        const body = { name: document.getElementById('e_name').value.trim(), vendedores: getVendedores('e_vendedores') };
        const token = document.querySelector('input[name="_token"]').value;
        try {
            const r = await fetch('{{ route('tenant.cajas.update', ['id' => ':id']) }}'.replace(':id', id), { method: 'PUT', headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
            const res = await r.json();
            if (r.status === 422) { toastr.warning(Object.values(res.errors).flat().join(' '), 'VALIDACIÓN'); return; }
            if (res.success) { dtCajas.draw(); bootstrap.Modal.getInstance('#mdlEditCaja').hide(); toastr.success(res.message, 'OPERACIÓN COMPLETADA'); }
            else toastr.error(res.message, 'ERROR');
        } catch (e) { toastr.error(e, 'ERROR'); }
    }

    function anularCaja(id, name) {
        toastr.clear();
        Swal.mixin({ customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-secondary" }, buttonsStyling: false }).fire({
            title: '¿ANULAR LA CAJA?', text: `Caja: ${name}`, icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Sí, anular', cancelButtonText: 'Cancelar', reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            const token = document.querySelector('input[name="_token"]').value;
            try {
                const r = await fetch('{{ route('tenant.cajas.destroy', ['id' => ':id']) }}'.replace(':id', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                const res = await r.json();
                if (res.success) { dtCajas.draw(); toastr.success(res.message, 'OPERACIÓN COMPLETADA'); }
                else toastr.warning(res.message, 'NO PERMITIDO');
            } catch (e) { toastr.error(e, 'ERROR'); }
        });
    }
</script>

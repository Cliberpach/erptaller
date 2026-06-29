<div class="modal fade" id="mdlImportServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Importar Servicios</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-danger" onclick="descargarFormatoServicios();">
                            <i class="fa-solid fa-download"></i> Descargar plantilla
                        </button>
                    </div>
                    <div class="col-12">
                        <form action="" method="post" id="formImportarServicios">
                            <label class="form-label fw-bold required_field">Subir Excel</label>
                            <input id="inputImportExcelServicios" class="form-control form-control-sm" type="file"
                                accept=".xlsx, .xls">
                        </form>
                        <span class="servicios_import_excel_error msgError" style="color:red;"></span>
                    </div>

                    {{-- Reporte de la importación (creados / omitidos / errores) --}}
                    <div class="col-12 mt-3" id="reporteImportServicios"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit" form="formImportarServicios">
                    <i class="fa-solid fa-upload"></i> Importar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function eventsMdlImportarServicios() {
        document.querySelector('#formImportarServicios').addEventListener('submit', (e) => {
            e.preventDefault();
            importarServiciosExcel();
        });
    }

    function openMdlImportServicio() {
        document.getElementById('reporteImportServicios').innerHTML = '';
        document.getElementById('inputImportExcelServicios').value = '';
        $('#mdlImportServicio').modal('show');
    }

    function descargarFormatoServicios() {
        window.location.href = @json(route('tenant.taller.servicios.get-format-excel'));
    }

    async function importarServiciosExcel() {
        const input = document.querySelector('#inputImportExcelServicios');
        if (input.files.length === 0) {
            toastr.error('DEBE CARGAR UN EXCEL PARA PROCEDER CON LA IMPORTACIÓN');
            return;
        }

        const token = document.querySelector('input[name="_token"]').value;
        const formData = new FormData(document.querySelector('#formImportarServicios'));
        formData.append('servicios_import_excel', input.files[0]);
        const url = @json(route('tenant.taller.servicios.import-excel'));

        Swal.fire({ title: 'Cargando...', html: 'Importando servicios ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData });
            const res = await response.json();

            if (response.status === 422) {
                if ('errors' in res) paintValidationErrors(res.errors, 'error');
                Swal.close();
                return;
            }

            pintarReporteServicios(res);

            if (res.success) {
                toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                dtServices.ajax.reload(null, false);
            } else {
                toastr.warning(res.message, 'IMPORTACIÓN NO COMPLETADA');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN IMPORTAR EXCEL');
        } finally {
            Swal.close();
        }
    }

    // Reporte visual: creados (verde), omitidos/duplicados (ámbar), errores (rojo).
    function pintarReporteServicios(res) {
        const cont = document.getElementById('reporteImportServicios');
        const r = res.resultado || {};
        const errores = r.errores || [];
        const duplicados = r.duplicados || [];
        const validos = r.validos || [];
        let html = '';

        if (errores.length > 0) {
            html += `<div class="alert alert-danger">
                        <strong>IMPORTACIÓN ABORTADA — ${errores.length} error(es) de formato. NO se creó ningún servicio.</strong>
                        <ul class="mb-0 mt-1">` +
                errores.map(e => `<li>Fila ${e.fila}${e.nombre ? ' (' + e.nombre + ')' : ''}: ${e.mensaje}</li>`).join('') +
                `</ul></div>`;
        } else {
            html += `<div class="alert alert-success mb-2">
                        <strong>${validos.length} servicio(s) creado(s).</strong>` +
                (validos.length ? `<div class="small mt-1">${validos.map(v => v.name).join(', ')}</div>` : '') +
                `</div>`;
            if (duplicados.length > 0) {
                html += `<div class="alert alert-warning mb-0">
                            <strong>${duplicados.length} omitido(s) por duplicado:</strong>
                            <ul class="mb-0 mt-1">` +
                    duplicados.map(d => `<li>Fila ${d.fila} — ${d.nombre} (${d.motivo})</li>`).join('') +
                    `</ul></div>`;
            }
        }
        cont.innerHTML = html;
    }
</script>

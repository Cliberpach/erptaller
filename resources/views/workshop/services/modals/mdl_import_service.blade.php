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
                <button id="btnImportarServicios" class="btn btn-primary" type="submit" form="formImportarServicios" disabled>
                    <i class="fa-solid fa-upload"></i> Importar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Flag de defensa REAL contra doble carga: aunque el botón se deshabilite,
    // este guard impide que una segunda invocación dispare otra petición.
    let importarServiciosEnCurso = false;

    function eventsMdlImportarServicios() {
        document.querySelector('#formImportarServicios').addEventListener('submit', (e) => {
            e.preventDefault();
            importarServiciosExcel();
        });

        // El botón solo se habilita cuando hay un archivo seleccionado.
        document.querySelector('#inputImportExcelServicios').addEventListener('change', () => {
            toggleBtnImportarServicios();
        });
    }

    function toggleBtnImportarServicios() {
        const input = document.querySelector('#inputImportExcelServicios');
        const btn = document.querySelector('#btnImportarServicios');
        btn.disabled = importarServiciosEnCurso || input.files.length === 0;
    }

    function setBtnImportarServiciosCargando(cargando) {
        const btn = document.querySelector('#btnImportarServicios');
        if (cargando) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
            toggleBtnImportarServicios();
        }
    }

    function openMdlImportServicio() {
        document.getElementById('reporteImportServicios').innerHTML = '';
        document.getElementById('inputImportExcelServicios').value = '';
        importarServiciosEnCurso = false;
        setBtnImportarServiciosCargando(false); // queda deshabilitado (sin archivo)
        $('#mdlImportServicio').modal('show');
    }

    function descargarFormatoServicios() {
        window.location.href = @json(route('tenant.taller.servicios.get-format-excel'));
    }

    async function importarServiciosExcel() {
        // Guard anti-doble-click: si ya hay una importación en curso, no dispara otra.
        if (importarServiciosEnCurso) return;

        const input = document.querySelector('#inputImportExcelServicios');
        if (input.files.length === 0) {
            toastr.error('DEBE CARGAR UN EXCEL PARA PROCEDER CON LA IMPORTACIÓN');
            return;
        }

        importarServiciosEnCurso = true;
        setBtnImportarServiciosCargando(true);

        const token = document.querySelector('input[name="_token"]').value;
        const formData = new FormData(document.querySelector('#formImportarServicios'));
        formData.append('servicios_import_excel', input.files[0]);
        const url = @json(route('tenant.taller.servicios.import-excel'));

        try {
            const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData });
            const res = await response.json();

            if (response.status === 422) {
                if ('errors' in res) paintValidationErrors(res.errors, 'error');
                return; // error de validación: NO se limpia el archivo
            }

            pintarReporteServicios(res);

            if (res.success) {
                const n = (res.resultado && res.resultado.validos) ? res.resultado.validos.length : 0;
                toastr.success(`Importación completada: ${n} servicio(s) creado(s).`, 'OPERACIÓN COMPLETADA');
                input.value = '';                       // limpia el campo solo en éxito
                dtServices.ajax.reload(null, false);    // refresca la tabla con los nuevos
            } else {
                // Import abortado (error de formato): se muestra el reporte en rojo y
                // NO se limpia el archivo, para que el usuario corrija y reintente.
                toastr.warning(res.message, 'IMPORTACIÓN NO COMPLETADA');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN IMPORTAR EXCEL');
        } finally {
            importarServiciosEnCurso = false;
            setBtnImportarServiciosCargando(false); // re-habilita segun haya o no archivo
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

        // Encabezado-resumen siempre visible: creados (verde) · omitidos (ámbar) · errores (rojo).
        html += `<div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge bg-success">✓ ${validos.length} creado(s)</span>
                    <span class="badge bg-warning text-dark">⊘ ${duplicados.length} omitido(s)</span>
                    <span class="badge bg-danger">✕ ${errores.length} error(es)</span>
                 </div>`;

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

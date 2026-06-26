<div class="modal fade" id="mdlSeriesSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h1 class="modal-title fs-5">
                    <i class="fa-solid fa-hashtag"></i> SERIES — <span id="seriesSedeNombre"></span>
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2">
                    Series de comprobante de esta sede. El <strong>N° actual</strong> es el correlativo
                    (lo maneja la emisión, no editable).
                </p>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th>TIPO</th>
                                <th style="width:130px;">SERIE</th>
                                <th style="width:120px;">N° INICIAL</th>
                                <th style="width:90px;">N° ACTUAL</th>
                                <th style="width:90px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tblSeriesBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openMdlSeries(sedeId, sedeNombre) {
        document.querySelector('#seriesSedeNombre').textContent = sedeNombre ?? '';
        const tbody = document.querySelector('#tblSeriesBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>';
        $('#mdlSeriesSede').modal('show');

        let url = `{{ route('tenant.mantenimientos.sedes.getSeries', ['sede' => ':id']) }}`.replace(':id', sedeId);

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${res.message ?? 'Error'}</td></tr>`;
                    return;
                }
                document.querySelector('#seriesSedeNombre').textContent = res.sede_nombre ?? sedeNombre;
                if (!res.series.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin series</td></tr>';
                    return;
                }
                tbody.innerHTML = res.series.map(s => `
                    <tr data-id="${s.id}" data-current="${s.current_number}" data-serie="${s.serie}">
                        <td>${s.tipo ?? '-'}</td>
                        <td><input type="text" class="form-control input-serie" value="${s.serie}" maxlength="10" style="background-color:#FFF9C4;"></td>
                        <td><input type="number" class="form-control input-start" value="${s.start_number}" min="1" style="background-color:#FFF9C4;"></td>
                        <td><span class="badge bg-secondary">${s.current_number}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="guardarSerie(${s.id}, this)">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </td>
                    </tr>`).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error de red</td></tr>';
            });
    }

    function guardarSerie(serieId, btn) {
        toastr.clear();
        const row     = btn.closest('tr');
        const serie   = row.querySelector('.input-serie').value.trim();
        const start   = row.querySelector('.input-start').value;
        const current = Number(row.dataset.current);

        const doSave = () => enviarSerie(serieId, serie, start, row);

        // Advertencia ámbar si la serie ya emitió documentos (no bloquea, solo avisa).
        if (current > 0) {
            Swal.mixin({ customClass: { confirmButton: "btn btn-warning", cancelButton: "btn btn-secondary" }, buttonsStyling: false })
                .fire({
                    title: '¿GUARDAR CAMBIO DE SERIE?',
                    html: `Esta serie ya tiene documentos emitidos (N°${current}).<br>Cambiarla puede afectar la numeración.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(r => { if (r.isConfirmed) doSave(); });
        } else {
            doSave();
        }
    }

    function enviarSerie(serieId, serie, start, row) {
        let url = `{{ route('tenant.mantenimientos.sedes.updateSerie', ['serie' => ':id']) }}`.replace(':id', serieId);
        const token = document.querySelector('input[name="_token"]').value;

        fetch(url, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ serie: serie, start_number: start })
        })
        .then(async (response) => {
            const res = await response.json();
            if (response.status === 422) {
                const msg = res.errors ? Object.values(res.errors).flat().join(' ') : 'Datos inválidos';
                toastr.warning(msg, 'VALIDACIÓN');
                return;
            }
            if (res.success) {
                row.dataset.serie = serie;
                toastr.success(res.message, 'OPERACIÓN COMPLETADA');
            } else {
                toastr.error(res.message, 'OPERACIÓN NO PERMITIDA');
            }
        })
        .catch(err => toastr.error(err, 'ERROR EN LA PETICIÓN'));
    }
</script>

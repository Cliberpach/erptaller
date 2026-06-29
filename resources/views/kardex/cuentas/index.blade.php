@extends('layouts.template')

@section('title')
    KARDEX DE CUENTAS
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">KARDEX DE CUENTAS <small class="text-muted">(estado por cuenta bancaria)</small></h4>
        </div>
        <div class="card-body">
            {{-- Filtro --}}
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="form-label fw-bold"><i class="fas fa-university text-primary me-1"></i> Cuenta bancaria</label>
                    <select id="bank_account_id" class="form-control">
                        <option value="">Seleccionar cuenta</option>
                        @foreach ($bank_accounts as $cuenta)
                            <option value="{{ $cuenta->id }}">
                                {{ $cuenta->bank_name }} - {{ $cuenta->account_number }} ({{ $cuenta->holder }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 mb-2">
                    <label class="form-label fw-bold">Desde</label>
                    <input type="date" id="start_date" class="form-control" value="{{ $fecha_inicio }}">
                </div>
                <div class="col-lg-2 col-md-3 mb-2">
                    <label class="form-label fw-bold">Hasta</label>
                    <input type="date" id="end_date" class="form-control" value="{{ $fecha_fin }}">
                </div>
                <div class="col-lg-3 col-md-12 mb-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100" onclick="cargarKardex()"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <button class="btn btn-secondary" onclick="limpiarKardex()"><i class="fas fa-eraser"></i></button>
                </div>
            </div>

            {{-- Cabecera de totales --}}
            <div class="row mt-3">
                <div class="col-md-3 mb-2"><div class="border rounded p-2 text-center"><small class="text-muted d-block">SALDO CUENTA</small><span class="fw-bold" id="k_apertura">0.00</span></div></div>
                <div class="col-md-3 mb-2"><div class="border rounded p-2 text-center bg-light"><small class="text-success d-block">TOTAL INGRESOS</small><span class="fw-bold text-success" id="k_ingresos">0.00</span></div></div>
                <div class="col-md-3 mb-2"><div class="border rounded p-2 text-center bg-light"><small class="text-danger d-block">TOTAL EGRESOS</small><span class="fw-bold text-danger" id="k_egresos">0.00</span></div></div>
                <div class="col-md-3 mb-2"><div class="border rounded p-2 text-center"><small class="text-muted d-block">SALDO FINAL</small><span class="fw-bold" id="k_saldo">0.00</span></div></div>
            </div>

            {{-- Tabla --}}
            <div class="table-responsive mt-2">
                <table class="table table-hover table-striped" id="tbl_kardex">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>MÉTODO</th>
                            <th>TIPO</th>
                            <th>DOCUMENTO</th>
                            <th class="text-end">ENTRADA</th>
                            <th class="text-end">SALIDA</th>
                            <th class="text-end">SALDO ACUM.</th>
                            <th>REGISTRADOR</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const fmt = n => Number(n || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Cuenta OBLIGATORIA: al entrar no se auto-carga, se muestra el mensaje.
        document.addEventListener('DOMContentLoaded', limpiarKardex);

        function limpiarKardex() {
            document.getElementById('bank_account_id').value = '';
            ['k_apertura', 'k_ingresos', 'k_egresos', 'k_saldo'].forEach(id => document.getElementById(id).textContent = '0.00');
            document.querySelector('#tbl_kardex tbody').innerHTML =
                '<tr><td colspan="8" class="text-center text-muted">Elegí una cuenta bancaria para ver su kardex.</td></tr>';
        }

        async function cargarKardex() {
            const cuenta = document.getElementById('bank_account_id').value;
            const tbody = document.querySelector('#tbl_kardex tbody');
            tbody.innerHTML = '';

            if (!cuenta) {
                ['k_apertura', 'k_ingresos', 'k_egresos', 'k_saldo'].forEach(id => document.getElementById(id).textContent = '0.00');
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-warning">Elegí una cuenta bancaria (es obligatoria).</td></tr>';
                return;
            }

            const params = new URLSearchParams({
                bank_account_id: cuenta,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
            });
            const url = '{{ route('tenant.kardex.cuentas.data') }}?' + params.toString();

            try {
                const res = await (await fetch(url)).json();
                document.getElementById('k_apertura').textContent = fmt(res.saldo_cuenta);
                document.getElementById('k_ingresos').textContent = fmt(res.total_ingresos);
                document.getElementById('k_egresos').textContent = fmt(res.total_egresos);
                document.getElementById('k_saldo').textContent = fmt(res.saldo_final);

                if (!res.movimientos.length) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Sin movimientos en el rango.</td></tr>';
                    return;
                }
                tbody.innerHTML = res.movimientos.map(m => {
                    const badge = m.tipo === 'INGRESO' ? 'bg-success' : 'bg-danger';
                    return `<tr>
                        <td>${m.fecha}</td>
                        <td>${m.metodo ?? '-'}</td>
                        <td><span class="badge ${badge}">${m.tipo}</span></td>
                        <td>${m.documento ?? '-'}${m.operacion ? ' <small class="text-muted">('+m.operacion+')</small>' : ''}</td>
                        <td class="text-end">${Number(m.entrada) ? fmt(m.entrada) : '-'}</td>
                        <td class="text-end">${Number(m.salida) ? fmt(m.salida) : '-'}</td>
                        <td class="text-end fw-bold">${fmt(m.saldo)}</td>
                        <td>${m.registrador ?? '-'}</td>
                    </tr>`;
                }).join('');
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar el kardex.</td></tr>';
            }
        }
    </script>
@endsection

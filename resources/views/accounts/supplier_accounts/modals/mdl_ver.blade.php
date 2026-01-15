<div class="modal fade" id="mdlVerCobranza" tabindex="-1" aria-labelledby="modal_detalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 80%; margin-right:auto; margin-left:auto;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <h5 class="modal-title text-uppercase font-weight-bold" id="modal_detalleLabel">Detalle de Cuenta
                    Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Primera Columna -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Proveedor ID</strong>
                            <span id="mdlVer_proveedor_id"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Proveedor</strong>
                            <span id="mdlVer_proveedor_nombre"></span>
                        </div>
                    </div>

                    <!-- Segunda Columna -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Documento</strong>
                            <span id="mdlVer_documento"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Estado</strong>
                            <span id="mdlVer_estado"></span>
                        </div>
                    </div>

                    <!-- Tercera Columna -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Monto</strong>
                            <span id="mdlVer_monto"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Saldo</strong>
                            <span id="mdlVer_saldo"></span>
                        </div>
                    </div>

                    <!-- Cuarta Columna -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Fecha Creación</strong>
                            <span id="mdlVer_created_at"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Fecha Actualización</strong>
                            <span id="mdlVer_updated_at"></span>
                        </div>
                    </div>

                    <!-- Quinta Columna -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Compra ID</strong>
                            <span id="mdlVer_compra_id"></span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            @include('accounts.supplier_accounts.tables.tbl_detalle_cuenta')
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal"><i class="fa fa-times"></i>
                    Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('js-script')
    <script>
        async function openMdlVerCobranza(cuentaProveedorId) {

            const resGetCobranza = await getCobranza(cuentaProveedorId);
            if (!resGetCobranza) {
                return;
            }

            if (resGetCobranza.success) {
                const cobranza = resGetCobranza.data.cuenta;
                console.log(cobranza)
                const detalle = resGetCobranza.data.detalle;

                pintarVerCobranza(cobranza);
                pintarTablaDetalleCuenta(detalle);
                toastr.success(resGetCobranza.message, 'OPERACIÓN COMPLETADA');
                $('#mdlVerCobranza').modal('show');

            } else {
                toastr.error(resGetCobranza.message, 'ERROR EN EL SERVIDOR');
            }

        }

        function pintarVerCobranza(cobranza) {
            document.getElementById('mdlVer_proveedor_id').innerText = cobranza.supplier_id;
            document.getElementById('mdlVer_proveedor_nombre').innerText = cobranza.supplier_name;
            document.getElementById('mdlVer_documento').innerText = cobranza.document_number;
            document.getElementById('mdlVer_estado').innerText = cobranza.status;
            document.getElementById('mdlVer_monto').innerText = formatSoles(cobranza.amount);
            document.getElementById('mdlVer_saldo').innerText = formatSoles(cobranza.balance)
            document.getElementById('mdlVer_created_at').innerText = cobranza.created_at;
            document.getElementById('mdlVer_updated_at').innerText = cobranza.updated_at;
            document.getElementById('mdlVer_compra_id').innerText = cobranza.purchase_id;
        }

        function pintarTablaDetalleCuenta(detalle) {
            let filas = ``;
            const tbody = document.querySelector('#tbl_detalle_cuenta tbody');

            const BASE_STORAGE_URL = @json(asset(''));


            detalle.forEach((d) => {

                let imagenHTML = '-';

                if (d.img_route) {
                    const link_img = `${BASE_STORAGE_URL}${d.img_route}`;

                    imagenHTML = `
                                <a href="${link_img}" target="_blank">
                                    <img src="${link_img}"
                                        style="width:80px; height:80px; object-fit:contain; border-radius:4px; border:1px solid #ddd;" />
                                </a>
                            `;
                }

                filas += `<tr>
                        <td style="text-align:left;">${d.date}</td>
                        <td style="text-align:left;">${d.petty_cash_name}</td>
                        <td style="text-align:left;">${d.creator_user_name}</td>
                        <td style="text-align:right;">${formatSoles(d.total)}</td>
                        <td style="text-align:right;">${formatSoles(d.cash)}</td>
                        <td style="text-align:right;">${formatSoles(d.amount)}</td>
                        <td style="text-align:right;">${formatSoles(d.paid)}</td>
                        <td style="text-align:left;">${d.payment_method_name}</td>
                        <td style="text-align:left;">${d.observation}</td>
                        <td style="text-align:center;">
                           ${imagenHTML}
                        </td>
                        <td style="text-align:left;">${formatDateTime(d.created_at)}</td>
                    </tr>`;
            })

            tbody.innerHTML = filas;
        }
    </script>
@endpush

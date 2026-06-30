{{-- Modal Nota de Crédito (Capa 1): elegir líneas/cantidades (parcial/total) + motivo SUNAT.
     No mueve stock ni caja, no envía a SUNAT (eso va en capas siguientes). --}}
<div class="modal fade" id="mdlCreditNote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Nota de Crédito — <span id="nc_doc"></span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="nc_sale_id">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-bold required_field">Motivo (SUNAT)</label>
                        <select class="form-control" id="nc_cod_motivo">
                            <option value="01" data-text="Anulación de la operación">01 - Anulación de la operación</option>
                            <option value="02" data-text="Anulación por error en el RUC">02 - Anulación por error en el RUC</option>
                            <option value="03" data-text="Corrección por error en la descripción">03 - Corrección por error en la descripción</option>
                            <option value="06" data-text="Devolución total">06 - Devolución total</option>
                            <option value="07" data-text="Devolución por ítem">07 - Devolución por ítem</option>
                            <option value="09" data-text="Disminución en el valor">09 - Disminución en el valor</option>
                            <option value="10" data-text="Otros conceptos">10 - Otros conceptos</option>
                        </select>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-bold">Observación</label>
                        <input type="text" class="form-control" id="nc_observation" maxlength="200" placeholder="Opcional">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Marcá las líneas y la cantidad a acreditar (parcial o total).</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="nc_btn_all">Marcar todo (total)</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th class="text-end">Vendida</th>
                                <th class="text-end" style="width:120px;">A acreditar</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody id="nc_lines"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">TOTAL NC S/</th>
                                <th class="text-end" id="nc_total">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="text-muted small mb-0">No mueve stock ni caja todavía (capas siguientes). No se envía a SUNAT: queda PENDIENTE.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" id="nc_btn_emit">
                    <i class="fa-solid fa-file-invoice"></i> Emitir NC
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3 border-0 p-0 shadow-sm">
    <div class="card-header d-flex align-items-center text-white"
        style="background:#e5e368; padding: 6px 12px; height: 38px;">

        <i class="fa-solid fa-chart-simple me-2" style="font-size: 14px;"></i>
        <span class="fw-semibold text-primary" style="font-size: 14px;">CONSOLIDADO</span>

    </div>


    <div class="card-body">

        <!-- CAJA -->
        <div class="d-flex justify-content-between mb-1 px-2">
            <span class="fw-semibold text-dark">Caja:</span>
            <span id="consolidated_caja" class="fw-bold text-dark">-</span>
        </div>

        <!-- CAJERO -->
        <div class="d-flex justify-content-between mb-3 px-2">
            <span class="fw-semibold text-dark">Cajero:</span>
            <span id="consolidated_cajero" class="fw-bold text-dark">-</span>
        </div>

        <hr>

        <div class="row mt-2">

            <!-- VENTAS -->
            <div class="col-md-6">
                <div class="rounded border p-0 shadow-sm" style="background:#f8fafc;">
                    <div class="rounded-top d-flex align-items-center px-3 py-2" style="background:#22c55e;">
                        <i class="fa-solid fa-cash-register me-2 text-white"></i>
                        <span class="fw-semibold text-white">Ventas</span>
                    </div>

                    <div class="p-3" id="sales_container" style="min-height: 180px;">

                    </div>
                </div>
            </div>

            <!-- EGRESOS -->
            <div class="col-md-6">
                <div class="rounded border p-0 shadow-sm" style="background:#f8fafc;">
                    <div class="rounded-top d-flex align-items-center px-3 py-2" style="background:#ef4444;">
                        <i class="fa-solid fa-file-invoice-dollar me-2 text-white"></i>
                        <span class="fw-semibold text-white">Egresos</span>
                    </div>

                    <div class="p-3" id="expenses_container" style="min-height: 180px;">

                    </div>
                </div>
            </div>

        </div>

        <hr>

        <!-- SALDO INICIAL -->
        <div class="d-flex justify-content-between px-2">
            <span class="fw-semibold text-dark">Saldo inicial:</span>
            <span id="saldo_inicial_consolidated" class="fw-bold text-dark">0.00</span>
        </div>

        <!-- TOTALES GENERALES -->
        <div class="d-flex justify-content-between mt-1 px-2">
            <span class="fw-semibold text-dark">Total ventas:</span>
            <span id="total_sales_general" class="fw-bold text-success">0.00</span>
        </div>

        <div class="d-flex justify-content-between mt-1 px-2">
            <span class="fw-semibold text-dark">Total egresos:</span>
            <span id="total_expenses_general" class="fw-bold text-danger">0.00</span>
        </div>

        <hr>

        <!-- MONTO CIERRE -->
        <div class="d-flex justify-content-between px-2">
            <span class="fw-semibold text-dark">Monto cierre:</span>
            <span id="monto_cierre_consolidated" class="fw-bold text-dark fs-5">0.00</span>
        </div>

    </div>
</div>

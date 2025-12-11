<div class="card mt-3 border-0 shadow-sm">
    <div class="card-header bg-secondary fw-semibold d-flex align-items-center text-white">
        <i class="fa-solid fa-chart-simple me-2"></i>
        Consolidado
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

        <div id="consolidated_container" class="row g-3">
            <!-- Aquí se pintarán los métodos de pago -->
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

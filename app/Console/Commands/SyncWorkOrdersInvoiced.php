<?php

namespace App\Console\Commands;

use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recalcula work_orders_products/services.invoiced_quantity desde las
 * ventas (sales_documents) realmente emitidas para cada OT — el comprobante
 * es la fuente de verdad inmutable. Corrige el estado que pudo quedar mal en
 * OTs editadas ANTES de este fix (el bug viejo reseteaba `invoiced` a false
 * al editar, aunque el comprobante ya existía). Ver docs/PLAN_OT_INVOICE.md.
 *
 * Uso (multi-tenant, vía wrapper de Spatie):
 *   php artisan tenants:artisan "work-orders:sync-invoiced --dry-run"   # ver cambios
 *   php artisan tenants:artisan "work-orders:sync-invoiced"             # aplicar, todos los tenants
 *   php artisan tenants:artisan "work-orders:sync-invoiced" --tenant=1  # un solo tenant
 */
class SyncWorkOrdersInvoiced extends Command
{
    protected $signature = 'work-orders:sync-invoiced {--dry-run : Muestra los cambios sin guardarlos}';

    protected $description = 'Recalcula invoiced_quantity de las OT desde las ventas realmente emitidas (recuperación post-fix)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $workOrderIds = Sale::whereNotNull('work_order_id')
            ->where('status', '<>', 'ANULADO')
            ->distinct()
            ->pluck('work_order_id');

        if ($workOrderIds->isEmpty()) {
            $this->info('No hay órdenes de trabajo con ventas asociadas.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Revisando {$workOrderIds->count()} orden(es) de trabajo con ventas asociadas...");

        foreach ($workOrderIds as $workOrderId) {
            $this->syncWorkOrder((int) $workOrderId, $dryRun);
        }

        $this->info('Listo.');

        return self::SUCCESS;
    }

    private function syncWorkOrder(int $workOrderId, bool $dryRun): void
    {
        $sales = Sale::where('work_order_id', $workOrderId)
            ->where('status', '<>', 'ANULADO')
            ->orderBy('id')
            ->get();

        if ($sales->isEmpty()) {
            return;
        }

        $saleIds  = $sales->pluck('id');
        $lastSale = $sales->last();

        $facturadoPorProducto = DB::table('sales_documents_details')
            ->whereIn('sale_document_id', $saleIds)
            ->where('estado', 'ACTIVO')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->pluck('qty', 'product_id');

        $facturadoPorServicio = DB::table('sales_documents_services')
            ->whereIn('sale_document_id', $saleIds)
            ->where('estado', 'ACTIVO')
            ->groupBy('service_id')
            ->selectRaw('service_id, SUM(quantity) as qty')
            ->pluck('qty', 'service_id');

        $totalLineas     = 0;
        $lineasCompletas = 0;

        foreach (WorkOrderProduct::where('work_order_id', $workOrderId)->get() as $linea) {
            $facturado = min(
                round((float) ($facturadoPorProducto[$linea->product_id] ?? 0), 6),
                (float) $linea->quantity
            );

            $totalLineas++;
            if ($facturado >= (float) $linea->quantity) {
                $lineasCompletas++;
            }

            $this->aplicarSiCambia($linea, "OT-{$workOrderId} producto {$linea->product_name}", $facturado, $lastSale, $dryRun);
        }

        foreach (WorkOrderService::where('work_order_id', $workOrderId)->get() as $linea) {
            $facturado = min(
                round((float) ($facturadoPorServicio[$linea->service_id] ?? 0), 6),
                (float) $linea->quantity
            );

            $totalLineas++;
            if ($facturado >= (float) $linea->quantity) {
                $lineasCompletas++;
            }

            $this->aplicarSiCambia($linea, "OT-{$workOrderId} servicio {$linea->service_name}", $facturado, $lastSale, $dryRun);
        }

        $nuevoStatus = ($totalLineas > 0 && $lineasCompletas === $totalLineas) ? 'FACTURADO' : 'FACTURADO PARCIAL';

        $workOrder = WorkOrder::find($workOrderId);
        if ($workOrder && $workOrder->status_invoice !== $nuevoStatus) {
            $this->line("  OT-{$workOrderId} status_invoice: {$workOrder->status_invoice} -> {$nuevoStatus}");
            if (! $dryRun) {
                $workOrder->update(['status_invoice' => $nuevoStatus]);
            }
        }
    }

    private function aplicarSiCambia($linea, string $label, float $facturado, Sale $lastSale, bool $dryRun): void
    {
        $actual = round((float) $linea->invoiced_quantity, 6);
        $cambia = abs($actual - $facturado) > 1e-6;

        if (! $cambia) {
            return;
        }

        $this->line("  {$label}: invoiced_quantity {$actual} -> {$facturado}");

        if ($dryRun) {
            return;
        }

        $linea->update([
            'invoiced_quantity'   => $facturado,
            'invoiced'            => $facturado >= (float) $linea->quantity,
            'invoiced_sale_id'    => $lastSale->id,
            'invoiced_sale_serie' => $lastSale->serie . '-' . $lastSale->correlative,
        ]);
    }
}

<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Services\Tenant\Inventory\Kardex\KardexService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductManager;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleDetail;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Anulación de DOCUMENTO INTERNO (no-fiscal): TICKET (symbol '50') + NOTA DE VENTA
 * legacy ('NV'), solo CONTADO. Revierte stock + kardex (ENTRADA) y marca ANULADO.
 * Boleta/Factura (baja SUNAT) y crédito (revertir CxC) = pasos aparte.
 */
class SaleAnnulmentService
{
    /** Documento interno: NV legacy + TICKET. */
    private const INTERNOS = ['NV', '50'];

    private WarehouseProductManager $s_warehouse_product;
    private KardexService $s_kardex;

    public function __construct()
    {
        $this->s_warehouse_product = new WarehouseProductManager();
        $this->s_kardex            = new KardexService();
    }

    public function anular(int $saleId, ?string $reason = null): Sale
    {
        $sale = Sale::find($saleId);

        // ===== VALIDACIONES (excepción clara) =====
        if (! $sale) {
            throw new Exception('La venta no existe.');
        }
        if ($sale->status === 'ANULADO') {
            throw new Exception('La venta ya está anulada.');
        }
        if (! is_null($sale->converted_to_id)) {
            throw new Exception('No se puede anular: el documento ya fue convertido a fiscal.');
        }
        if (! in_array($sale->type_sale_code, self::INTERNOS, true)) {
            throw new Exception('Solo se anula documento interno (Ticket / Nota de Venta). Boleta/Factura requieren baja SUNAT (próximamente).');
        }
        if (strtoupper($sale->payment_condition_name) !== 'CONTADO') {
            throw new Exception('Anulación de venta a crédito: próximamente.');
        }
        // Visibilidad por rol: no-admin solo anula lo que él registró (espeja el index).
        if (! Auth::user()->hasRole('admin') && (int) $sale->user_recorder_id !== (int) Auth::id()) {
            throw new Exception('No puede anular una venta que no registró.');
        }

        DB::transaction(function () use ($sale, $reason) {

            // 1) REVERTIR STOCK — ESPEJO del descuento de la emisión.
            //    Emisión: SaleDetailService::storeDetail -> decreaseStock(wh, prod, cant) + kardex SALIDA.
            //    Anular : por la MISMA SaleDetail -> increaseStock(wh, prod, qty)   + kardex ENTRADA.
            $detalles = SaleDetail::where('sale_document_id', $sale->id)->get();
            foreach ($detalles as $d) {
                $this->s_warehouse_product->increaseStock($d->warehouse_id, $d->product_id, $d->quantity);
            }
            // kardex ENTRADA = getDtoFromSale (mismas filas) con type='ENTRADA'; atado por sale_id.
            $this->s_kardex->storeFromSaleAnulacion($sale);

            // 2) MARCAR ANULADO + AUDITORÍA — asignación DIRECTA (status NO está en $fillable
            //    -> esquiva el mass-assignment trap). Solo 'status' (decisión A: el cuadre y el
            //    index filtran por status). El correlativo NO se reusa.
            $sale->status           = 'ANULADO';
            $sale->annulled_at      = now();
            $sale->annulled_by      = Auth::id();
            $sale->annulled_by_name = Auth::user()->name;
            $sale->annulment_reason = $reason;
            $sale->save();
            // Pagos (sales_document_payments) intactos: el status los saca del cuadre.
        });

        return $sale->refresh();
    }
}

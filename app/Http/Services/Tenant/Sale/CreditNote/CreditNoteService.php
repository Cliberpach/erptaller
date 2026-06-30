<?php

namespace App\Http\Services\Tenant\Sale\CreditNote;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookRepository;
use App\Http\Services\Tenant\Inventory\Kardex\KardexService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductManager;
use App\Http\Services\Tenant\Sale\Sale\CorrelativeService;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\Sale;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Nota de Crédito desde una boleta/factura (parcial o total).
 *
 * CAPA 1: documento + detalle (credit_notes_details), correlativo FC001 atómico.
 * CAPA 2: reversa de STOCK al emitir, con DOS reglas:
 *   - ORIGEN: solo devuelve si la venta es DIRECTA. Convertida de ticket
 *     (converted_from_id) o desde OT (work_order_id) -> NO devuelve (el original ya movió).
 *   - ALMACÉN: devuelve al MISMO almacén de origen (credit_notes_details.warehouse_id).
 *   + GUARDA de sobre-devolución: lo acreditado acumulado por producto no supera lo vendido.
 * NO mueve caja, NO envía a SUNAT (sunat_status queda 'PENDIENTE').
 *
 * Building blocks: CorrelativeService(68) -> serie FC001 (lockForUpdate); espejo del detalle
 * como SaleConversionService; reversa = increaseStock + KardexService::storeFromCreditNote
 * (espejo invertido del decreaseStock + kardex SALIDA de la emisión).
 *
 * PK de credit_notes_details = (credit_note_id, product_id): el espejo AGRUPA por product_id
 * (suma cantidades/montos) para no colisionar si la venta repite producto en varios almacenes.
 */
class CreditNoteService
{
    private const TIPO_NC_ID   = 68;     // general_table_details: NOTA DE CRÉDITO ELECTRÓNICA (serie FC001)
    private const TIPO_NC_CODE = '07';   // SUNAT catálogo 01: nota de crédito
    private const FISCALES     = ['01', '03']; // factura / boleta

    private CorrelativeService $s_correlative;
    private WarehouseProductManager $s_warehouse_product;
    private KardexService $s_kardex;
    private PettyCashBookRepository $s_cash_book;

    public function __construct()
    {
        $this->s_correlative       = new CorrelativeService();
        $this->s_warehouse_product = new WarehouseProductManager();
        $this->s_kardex            = new KardexService();
        $this->s_cash_book         = new PettyCashBookRepository();
    }

    /**
     * Capa 3: ¿esta NC atribuye caja? Solo venta DIRECTA + CONTADO. Convertida/OT (el
     * original ya cobró) y CRÉDITO (la plata va por CxC, no por caja) -> NO atribuye.
     */
    private function atribuyeCaja(Sale $sale): bool
    {
        $esDirecta = is_null($sale->converted_from_id) && is_null($sale->work_order_id);
        $esContado = strtoupper($sale->payment_condition_name) === 'CONTADO';

        return $esDirecta && $esContado;
    }

    /**
     * Datos para el modal: la venta fiscal + sus líneas (para elegir parcial/total).
     */
    public function dataForCreditNote(int $saleId): array
    {
        $sale = Sale::findOrFail($saleId);

        if (! in_array($sale->type_sale_code, self::FISCALES, true)) {
            throw new Exception('Solo se puede emitir Nota de Crédito de una Boleta o Factura.');
        }

        $lines = DB::table('sales_documents_details')
            ->where('sale_document_id', $saleId)
            ->get([
                'product_id', 'warehouse_id', 'product_name',
                'brand_name', 'product_unit', 'quantity', 'price_sale',
            ]);

        return [
            'sale' => [
                'id'             => $sale->id,
                'doc'            => $sale->type_sale_name . ' ' . $sale->serie . '-' . $sale->correlative,
                'type_sale_code' => $sale->type_sale_code,
                'customer_name'  => $sale->customer_name,
                'total'          => number_format($sale->total, 2),
            ],
            'lines' => $lines,
            // Capa 3: gate de caja para el botón Emitir. Solo directa+CONTADO exige caja;
            // convertida/OT/crédito puede emitir sin caja (no atribuye).
            'requiere_caja' => $this->atribuyeCaja($sale),
            'caja_abierta'  => (bool) $this->s_cash_book->getCashBookUser(Auth::id()),
        ];
    }

    /**
     * Emite la NC.
     * $data = ['sale_id', 'codMotivo', 'desMotivo', 'observation'?,
     *          'lines' => [['product_id','warehouse_id','quantity'], ...]]
     */
    public function emit(array $data): CreditNote
    {
        $sale = Sale::find($data['sale_id'] ?? null);

        if (! $sale) {
            throw new Exception('La venta no existe.');
        }
        if (! in_array($sale->type_sale_code, self::FISCALES, true)) {
            throw new Exception('Solo se puede emitir Nota de Crédito de una Boleta o Factura.');
        }
        // NOTA: el gate "solo si ACEPTADO por SUNAT + sin NC previa" se aplica en la capa
        // de permisos/SUNAT. Capa 1: solo el documento.

        $seleccion = collect($data['lines'] ?? [])
            ->filter(fn ($l) => (float) ($l['quantity'] ?? 0) > 0);

        if ($seleccion->isEmpty()) {
            throw new Exception('Seleccione al menos una línea con cantidad a acreditar.');
        }

        // ===== CAPA 3: GATE de caja-abierta + ATRIBUCIÓN (antes del correlativo) =====
        // La NC que atribuye caja (directa + CONTADO) exige caja ABIERTA del usuario; se
        // atribuye a ESA caja (la de HOY, no la del cobro original -> resuelve caja-cerrada).
        // Convertida/OT/crédito -> no atribuye caja (petty_cash_book_id = null).
        $cajaId = null;
        if ($this->atribuyeCaja($sale)) {
            $caja = $this->s_cash_book->getCashBookUser(Auth::id());
            if (! $caja) {
                throw new Exception('Necesita una caja abierta para emitir una Nota de Crédito.');
            }
            $cajaId = $caja->petty_cash_book_id;
        }

        // Detalle de la venta indexado por (product_id, warehouse_id) para validar y leer montos.
        $origen = DB::table('sales_documents_details')
            ->where('sale_document_id', $sale->id)
            ->get()
            ->keyBy(fn ($r) => $r->product_id . '|' . $r->warehouse_id);

        // sales_documents_details NO guarda warehouse_name (solo warehouse_id); el detalle
        // de la NC sí lo exige -> se resuelve desde la tabla warehouses.
        $whNames = DB::table('warehouses')->pluck('descripcion', 'id');

        return DB::transaction(function () use ($sale, $seleccion, $origen, $data, $whNames, $cajaId) {

            // Correlativo NC atómico (serie FC001) por sede activa.
            $corr = $this->s_correlative->getCorrelative(self::TIPO_NC_ID);

            // Construir líneas (escala por cantidad acreditada) AGRUPANDO por product_id.
            $acc = [];
            foreach ($seleccion as $sel) {
                $key = $sel['product_id'] . '|' . ($sel['warehouse_id'] ?? '');
                $src = $origen->get($key);
                if (! $src) {
                    throw new Exception('Una línea seleccionada no pertenece a la venta.');
                }
                $qcr = (float) $sel['quantity'];
                if ($qcr > (float) $src->quantity) {
                    throw new Exception('La cantidad a acreditar supera la vendida.');
                }

                $pct        = (float) $src->porcentaje_igv;
                $valorVenta = round($qcr * (float) $src->mto_valor_unitario, 2); // base sin IGV
                $igv        = round($valorVenta * $pct / 100, 2);
                $amount     = round($valorVenta + $igv, 2);                       // con IGV

                $pid = $src->product_id;
                if (! isset($acc[$pid])) {
                    $acc[$pid] = [
                        'product_id'        => $src->product_id,
                        'category_id'       => $src->category_id,
                        'brand_id'          => $src->brand_id,
                        'warehouse_id'      => $src->warehouse_id,
                        'warehouse_name'    => $whNames[$src->warehouse_id] ?? '',
                        'product_name'      => $src->product_name,
                        'category_name'     => $src->category_name,
                        'brand_name'        => $src->brand_name,
                        'codProducto'       => $src->product_code,
                        'unity'             => $src->product_unit,
                        'description'       => $src->product_description ?: $src->product_name,
                        'quantity'          => 0,
                        'sale_price'        => $src->price_sale,
                        'sale_price_new'    => 0,
                        'amount'            => 0,
                        'amount_new'        => 0,
                        'discount_price'    => 0,
                        'discount_import'   => 0,
                        'mtoBaseIgv'        => 0,
                        'porcentajeIgv'     => $pct,
                        'igv'               => 0,
                        'tipAfeIgv'         => $src->tip_afe_igv,
                        'totalImpuestos'    => 0,
                        'mtoValorVenta'     => 0,
                        'mtoValorUnitario'  => $src->mto_valor_unitario,
                        'mtoPrecioUnitario' => $src->mto_precio_unitario,
                    ];
                }
                $acc[$pid]['quantity']       += $qcr;
                $acc[$pid]['mtoBaseIgv']     += $valorVenta;
                $acc[$pid]['mtoValorVenta']  += $valorVenta;
                $acc[$pid]['igv']            += $igv;
                $acc[$pid]['totalImpuestos'] += $igv;
                $acc[$pid]['amount']         += $amount;
            }

            // ===== GUARDA DE SOBRE-DEVOLUCIÓN (por producto, acumulado) =====
            // Permite NC parciales múltiples, pero impide acreditar más de lo vendido:
            // acreditado_acumulado(producto) + lo de esta NC <= vendido(producto).
            // Por producto (no por almacén): el detalle NC agrupa por product_id.
            $vendidoPorProducto = DB::table('sales_documents_details')
                ->where('sale_document_id', $sale->id)
                ->groupBy('product_id')
                ->selectRaw('product_id, SUM(quantity) as q')
                ->pluck('q', 'product_id');

            $acreditadoPorProducto = DB::table('credit_notes_details as cnd')
                ->join('credit_notes as cn', 'cn.id', '=', 'cnd.credit_note_id')
                ->where('cn.sale_id', $sale->id)   // NC previas de esta venta (esta aún no se insertó)
                ->groupBy('cnd.product_id')
                ->selectRaw('cnd.product_id, SUM(cnd.quantity) as q')
                ->pluck('q', 'cnd.product_id');

            foreach ($acc as $pid => $row) {
                $vendido    = (float) ($vendidoPorProducto[$pid] ?? 0);
                $yaAcred    = (float) ($acreditadoPorProducto[$pid] ?? 0);
                $disponible = round($vendido - $yaAcred, 6);
                if ((float) $row['quantity'] > $disponible + 1e-6) {
                    throw new Exception(
                        "La cantidad a acreditar de '{$row['product_name']}' supera lo disponible: "
                        . "vendido {$vendido}, ya acreditado {$yaAcred}, máximo {$disponible}."
                    );
                }
            }

            $subtotal = round(array_sum(array_column($acc, 'mtoValorVenta')), 2);
            $igvTotal = round(array_sum(array_column($acc, 'igv')), 2);
            $total    = round(array_sum(array_column($acc, 'amount')), 2);

            $tipoNc = GeneralTableDetail::find(self::TIPO_NC_ID);
            $wh     = reset($acc); // warehouse de cabecera = el de la primera línea (mono-almacén)

            // ===== Cabecera NC =====
            $nc = new CreditNote();
            $nc->sale_id                  = $sale->id;
            $nc->petty_cash_book_id       = $cajaId;   // Capa 3: caja abierta de hoy (null si convertida/OT/crédito)
            $nc->warehouse_id             = $wh['warehouse_id'] ?? null;
            $nc->warehouse_name           = $wh['warehouse_name'] ?? '';
            $nc->type_sale_id             = self::TIPO_NC_ID;
            $nc->type_sale_code           = self::TIPO_NC_CODE;
            $nc->type_sale_name           = $tipoNc->description ?? 'NOTA DE CREDITO ELECTRONICA';
            $nc->customer_name            = $sale->customer_name;
            $nc->customer_type_document   = $sale->customer_type_document;
            $nc->customer_document_number = $sale->customer_document_number;
            $nc->customer_document_code   = $sale->customer_document_code;
            $nc->customer_phone           = $sale->customer_phone;
            $nc->igv_percentage           = $sale->igv_percentage;
            $nc->subtotal                 = $subtotal;
            $nc->igv_amount               = $igvTotal;
            $nc->total                    = $total;
            $nc->legend                   = NumberToLettersController::numberToLetters($total);
            $nc->tipDocAfectado           = $sale->type_sale_code;               // 03 / 01
            $nc->numDocfectado            = $sale->serie . '-' . $sale->correlative;
            $nc->codMotivo                = $data['codMotivo'];
            $nc->desMotivo                = $data['desMotivo'];
            $nc->fechaEmision             = now()->toDateString();
            $nc->tipoMoneda               = 'PEN';
            $nc->mtoOperGravadas          = $subtotal;
            $nc->mtoIGV                   = $igvTotal;
            $nc->totalImpuestos           = $igvTotal;
            $nc->mtoImpVenta              = $total;
            $nc->ublVersion               = '2.1';
            $nc->obsevation               = $data['observation'] ?? null;        // (sic: columna 'obsevation')
            $nc->correlative              = $corr->correlative;
            $nc->serie                    = $corr->serie;
            $nc->sunat_status             = 'PENDIENTE';
            $nc->creator_user_id          = Auth::id();
            $nc->creator_user_name        = optional(Auth::user())->name;
            $nc->save();

            // ===== Detalle NC (agrupado por product_id) =====
            $now = now();
            foreach ($acc as $row) {
                $row['credit_note_id'] = $nc->id;
                $row['quantity']       = round($row['quantity'], 6);
                $row['mtoBaseIgv']     = round($row['mtoBaseIgv'], 2);
                $row['mtoValorVenta']  = round($row['mtoValorVenta'], 2);
                $row['igv']            = round($row['igv'], 2);
                $row['totalImpuestos'] = round($row['totalImpuestos'], 2);
                $row['amount']         = round($row['amount'], 2);
                $row['created_at']     = $now;
                $row['updated_at']     = $now;
                DB::table('credit_notes_details')->insert($row);
            }

            // ===== CAPA 2: reversa de STOCK (regla ORIGEN + regla ALMACÉN) =====
            // Solo venta DIRECTA devuelve. Convertida de ticket (converted_from_id) o desde
            // OT (work_order_id): el documento original ya movió el stock -> la NC NO toca
            // inventario (devolver sería doble conteo; se revierte sobre el original).
            $esDirecta = is_null($sale->converted_from_id) && is_null($sale->work_order_id);

            if ($esDirecta) {
                // Devuelve al MISMO almacén de origen, por la cantidad ACREDITADA (no la vendida).
                foreach ($acc as $row) {
                    $this->s_warehouse_product->increaseStock(
                        $row['warehouse_id'],
                        $row['product_id'],
                        $row['quantity']
                    );
                }
                // Kardex ENTRADA (espejo invertido de la SALIDA de la emisión), atado a la NC.
                $this->s_kardex->storeFromCreditNote($nc);
            } else {
                Log::info('NC sin reversa de stock: origen convertido/OT — revertir sobre el documento original.', [
                    'credit_note_id'    => $nc->id,
                    'sale_id'           => $sale->id,
                    'converted_from_id' => $sale->converted_from_id,
                    'work_order_id'     => $sale->work_order_id,
                ]);
            }

            return $nc;
        });
    }
}

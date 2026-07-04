<?php

namespace App\Http\Services\Tenant\Sale\CreditNote;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookRepository;
use App\Http\Services\Tenant\Invoicing\InvoicingManager;
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
    private const TIPO_NC_CODE = '07';   // SUNAT catálogo 01: nota de crédito
    private const FISCALES     = ['01', '03']; // factura / boleta
    private const GT_COMPROBANTES = 4;   // general_table_details: COMPROBANTES DE VENTA

    /**
     * NC de FACTURA (serie FF) vs NC de BOLETA (serie BB): la serie dobla la letra del
     * comprobante afectado, no hay un solo tipo genérico de NC.
     */
    private function resolveTipoNcId(string $saleTypeSaleCode): int
    {
        $parameter = $saleTypeSaleCode === '01' ? 'FF' : 'BB';

        $id = GeneralTableDetail::where('general_table_id', self::GT_COMPROBANTES)
            ->where('parameter', $parameter)
            ->where('status', 'ACTIVO')
            ->value('id');

        if (!$id) {
            throw new Exception("NO EXISTE EL TIPO DE NOTA DE CRÉDITO ({$parameter}) EN LA BD!!!");
        }

        return $id;
    }

    private CorrelativeService $s_correlative;
    private WarehouseProductManager $s_warehouse_product;
    private KardexService $s_kardex;
    private PettyCashBookRepository $s_cash_book;
    private InvoicingManager $s_invoicing;

    public function __construct()
    {
        $this->s_correlative       = new CorrelativeService();
        $this->s_warehouse_product = new WarehouseProductManager();
        $this->s_kardex            = new KardexService();
        $this->s_cash_book         = new PettyCashBookRepository();
        $this->s_invoicing         = new InvoicingManager();
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
     * Cada línea trae 'ya_acreditado' y 'disponible' (vendido - ya acreditado en NCs
     * previas de esta venta) para que el modal no deje tipear una cantidad que el
     * backend va a rechazar igual (guarda de sobre-devolución en emit()).
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

        $acreditadoPorProducto = DB::table('credit_notes_details as cnd')
            ->join('credit_notes as cn', 'cn.id', '=', 'cnd.credit_note_id')
            ->where('cn.sale_id', $saleId)
            ->groupBy('cnd.product_id')
            ->selectRaw('cnd.product_id, SUM(cnd.quantity) as q')
            ->pluck('q', 'cnd.product_id');

        $lines = $lines->map(function ($l) use ($acreditadoPorProducto) {
            $yaAcreditado     = (float) ($acreditadoPorProducto[$l->product_id] ?? 0);
            $l->ya_acreditado = $yaAcreditado;
            $l->disponible    = max(0, round((float) $l->quantity - $yaAcreditado, 6));
            return $l;
        });

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

            // NC de FACTURA (serie FF) o de BOLETA (serie BB) según el comprobante afectado.
            $tipoNcId = $this->resolveTipoNcId($sale->type_sale_code);

            // Correlativo NC atómico por sede activa.
            $corr = $this->s_correlative->getCorrelative($tipoNcId);

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

            $tipoNc = GeneralTableDetail::find($tipoNcId);
            $wh     = reset($acc); // warehouse de cabecera = el de la primera línea (mono-almacén)

            // ===== Cabecera NC =====
            $nc = new CreditNote();
            $nc->sale_id                  = $sale->id;
            $nc->sede_id                  = $sale->sede_id; // denormalizado de la venta origen, evita join al listar/filtrar
            $nc->petty_cash_book_id       = $cajaId;   // Capa 3: caja abierta de hoy (null si convertida/OT/crédito)
            $nc->warehouse_id             = $wh['warehouse_id'] ?? null;
            $nc->warehouse_name           = $wh['warehouse_name'] ?? '';
            $nc->type_sale_id             = $tipoNcId;
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
            // Venta CONVERTIDA de ticket (converted_from_id): NO devuelve acá. El ticket
            // original sigue existiendo y es anulable por separado (SaleController@anular
            // sí revierte stock) -> ese es el camino real, evita doble reversa.
            //
            // Venta de OT (work_order_id): el stock de la OT se descuenta en UNO de dos
            // momentos, según el switch "VALIDAR STOCK EN ORDENES DE TRABAJO"
            // (Configuration id=2) vigente AL CREAR esa OT específica (WorkOrder::validation_stock,
            // congelado en esa fila, no el valor actual del config):
            //   - validation_stock=1 -> se descontó AL CREAR la OT (WorkOrderRepository::insertWorkOrderDetail).
            //   - validation_stock=0 -> se descuenta recién AL FINALIZAR la OT (WorkOrderService::finish()).
            // Una OT se puede facturar (Sale) SIN pasar por finish() (el botón "Facturar" solo
            // exige status_invoice != FACTURADO, no status == FINALIZADO) -> si validation_stock=0
            // y la OT nunca se finalizó, el stock NUNCA bajó y la NC NO debe devolver nada
            // (sería stock fantasma). Se resuelve consultando la OT real, no solo "es de OT".
            $noDevuelve = ! is_null($sale->converted_from_id);

            if (! $noDevuelve && ! is_null($sale->work_order_id)) {
                $workOrder = DB::table('work_orders')->where('id', $sale->work_order_id)->first();
                $stockYaDescontado = $workOrder && ((bool) $workOrder->validation_stock || $workOrder->status === 'FINALIZADO');

                if (! $stockYaDescontado) {
                    $noDevuelve = true;
                    Log::info('NC sin reversa de stock: la OT de origen nunca descontó stock (validation_stock=0 y no finalizada).', [
                        'credit_note_id' => $nc->id,
                        'sale_id'        => $sale->id,
                        'work_order_id'  => $sale->work_order_id,
                    ]);
                }
            }

            if (! $noDevuelve) {
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
            } elseif (! is_null($sale->converted_from_id)) {
                Log::info('NC sin reversa de stock: origen convertido de ticket — revertir anulando el ticket original.', [
                    'credit_note_id'    => $nc->id,
                    'sale_id'           => $sale->id,
                    'converted_from_id' => $sale->converted_from_id,
                ]);
            }
            // Caso OT-sin-descuento ya logueado arriba (no duplicar mensaje acá).

            return $nc;
        });
    }

    /**
     * Envía la NC a SUNAT y persiste la respuesta. Mismo contrato de sunat_status que
     * SaleService::sendSunat (PENDIENTE/ACEPTADO/OBSERVADO/RECHAZADO, sin ENVIADO).
     */
    public function sendSunat(int $credit_note_id): CreditNote
    {
        $nc = CreditNote::findOrFail($credit_note_id);

        if ($nc->sunat_status === 'ACEPTADO') {
            throw new Exception('NOTA DE CRÉDITO: ' . $nc->serie . '-' . $nc->correlative . ', YA FUE ACEPTADA POR SUNAT');
        }

        $this->s_invoicing->isActiveTypeInvoice($nc->type_sale_id);

        $details = DB::table('credit_notes_details')
            ->where('credit_note_id', $credit_note_id)
            ->get()
            ->all();

        if (count($details) === 0) {
            throw new Exception('EL DETALLE DE LA NOTA DE CRÉDITO ESTÁ VACÍO!!!');
        }

        $customer = DB::table('customers')
            ->join('sales_documents', 'sales_documents.customer_id', '=', 'customers.id')
            ->where('sales_documents.id', $nc->sale_id)
            ->select('customers.address', 'customers.email', 'customers.phone')
            ->first();

        if (!$customer) {
            throw new Exception('ERROR AL OBTENER CLIENTE DE LA NOTA DE CRÉDITO!!!');
        }

        $data = $this->s_invoicing->sendCreditNote($nc, $details, $customer);

        return $this->saveSunatData($data, $nc);
    }

    private function saveSunatData(array $data, CreditNote $nc): CreditNote
    {
        $nc->response_success         = $data['response_success'];
        $nc->response_cdrZip          = $data['response_cdrZip'];
        $nc->response_error_code      = $data['response_error_code'];
        $nc->response_error_message   = $data['response_error_message'];
        $nc->cdr_response_id          = $data['cdr_response_id'];
        $nc->cdr_response_code        = $data['cdr_response_code'];
        $nc->cdr_response_description = $data['cdr_response_description'];
        $nc->cdr_response_notes       = $data['cdr_response_notes'];
        $nc->cdr_response_reference   = $data['cdr_response_reference'];
        $nc->ruta_xml                  = $data['ruta_xml'];
        $nc->ruta_cdr                  = $data['ruta_cdr'];
        $nc->sunat_status              = $data['sunat_status'];
        $nc->last_send_message         = $data['message'];
        $nc->save();

        return $nc;
    }
}

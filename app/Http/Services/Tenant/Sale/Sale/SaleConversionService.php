<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\Sale;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Convierte un DOCUMENTO INTERNO (Ticket '50' / NV legacy 'NV') a un comprobante
 * FISCAL (Boleta '03' / Factura '01'). SOLO FORMALIZA: no descuenta stock (ya salió
 * con el ticket), no suma a caja (ya se cobró), no crea líneas de pago.
 * El dinero cuenta UNA vez: queda en el ticket (sigue ACTIVO en el cuadre); la
 * boleta/factura se crea SIN pagos -> aporta 0 al cuadre. Referencias cruzadas.
 */
class SaleConversionService
{
    private const INTERNOS = ['NV', '50'];   // origen permitido
    private const FISCALES = ['01', '03'];   // destino permitido (factura / boleta)

    private CorrelativeService $s_correlative;

    public function __construct()
    {
        $this->s_correlative = new CorrelativeService();
    }

    /**
     * @param array $data ['type_sale' => idTipoFiscal, 'customer_id' => id]
     */
    public function convertir(int $ticketId, array $data): Sale
    {
        $ticket = Sale::find($ticketId);

        // ===== VALIDACIONES =====
        if (! $ticket) {
            throw new Exception('La venta no existe.');
        }
        if ($ticket->status !== 'ACTIVO') {
            throw new Exception('Solo se puede convertir un documento ACTIVO.');
        }
        if (! in_array($ticket->type_sale_code, self::INTERNOS, true)) {
            throw new Exception('Solo se convierte un documento interno (Ticket / Nota de Venta).');
        }
        if (! is_null($ticket->converted_to_id)) {
            throw new Exception('Este documento ya fue convertido.');
        }

        $tipoFiscal = GeneralTableDetail::findOrFail($data['type_sale']);
        if (! in_array($tipoFiscal->symbol, self::FISCALES, true)) {
            throw new Exception('El tipo destino debe ser Boleta o Factura.');
        }

        $cliente = DB::table('customers')->find($data['customer_id']);
        if (! $cliente) {
            throw new Exception('El cliente no existe.');
        }
        // Factura (parameter 'F') exige RUC ; Boleta (parameter 'B') no admite RUC.
        if ($tipoFiscal->parameter === 'F' && $cliente->type_document_abbreviation !== 'RUC') {
            throw new Exception('La factura exige un cliente con RUC.');
        }
        if ($tipoFiscal->parameter === 'B' && $cliente->type_document_abbreviation === 'RUC') {
            throw new Exception('No se permiten boletas con RUC.');
        }

        return DB::transaction(function () use ($ticket, $tipoFiscal, $cliente) {

            // Correlativo FISCAL de SU serie (atómico, lockForUpdate). El ticket conserva el suyo.
            $corr = $this->s_correlative->getCorrelative($tipoFiscal->id);

            // Clonar el maestro del ticket y reescribir lo fiscal. NO se tocan stock/kardex/pagos.
            $fiscal = $ticket->replicate();

            // Cliente (editable en el modal)
            $fiscal->customer_id             = $cliente->id;
            $fiscal->customer_name           = $cliente->name;
            $fiscal->customer_type_document  = $cliente->type_document_abbreviation;
            $fiscal->customer_document_number = $cliente->document_number;
            $fiscal->customer_document_code  = $cliente->type_document_code;
            $fiscal->customer_phone          = $cliente->phone;

            // Tipo + serie/correlativo fiscal
            $fiscal->type_sale_id   = $tipoFiscal->id;
            $fiscal->type_sale_code = $tipoFiscal->symbol;
            $fiscal->type_sale_name = $tipoFiscal->name;
            $fiscal->serie          = $corr->serie;
            $fiscal->correlative    = $corr->correlative;
            $fiscal->legend         = NumberToLettersController::numberToLetters($ticket->total);

            // Estado fiscal: nuevo, pendiente de envío a SUNAT. NO hereda el cruce del ticket.
            $fiscal->sunat_status      = 'PENDIENTE';
            $fiscal->status            = 'ACTIVO';
            $fiscal->annulled_at       = null;
            $fiscal->annulled_by       = null;
            $fiscal->annulled_by_name  = null;
            $fiscal->annulment_reason  = null;
            $fiscal->converted_to_id   = null;
            $fiscal->converted_from_id = $ticket->id;   // viene del ticket
            $fiscal->ruta_xml          = null;
            $fiscal->ruta_cdr          = null;
            $fiscal->ruta_qr           = null;
            $fiscal->registration_date = now();
            $fiscal->save();   // amounts, petty_cash_book_id, payment_* se heredan del ticket

            // Copiar el DETALLE del ticket al fiscal (sin tocar stock ni kardex).
            $detalles = DB::table('sales_documents_details')
                ->where('sale_document_id', $ticket->id)->get();
            foreach ($detalles as $d) {
                $row = (array) $d;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $row['sale_document_id'] = $fiscal->id;
                DB::table('sales_documents_details')->insert($row);
            }

            // NO se crean líneas de pago en el fiscal -> aporta 0 al cuadre (el cobro
            // sigue en el ticket). Marcar el ticket como convertido (asignación directa).
            $ticket->converted_to_id = $fiscal->id;
            $ticket->save();   // ticket queda ACTIVO -> sigue en el cuadre con su cobro

            return $fiscal->refresh();
        });
    }
}

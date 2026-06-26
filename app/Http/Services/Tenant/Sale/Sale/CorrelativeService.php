<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Concerns\HasSedeActiva;
use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\DocumentSerialization;
use Exception;
use Illuminate\Support\Facades\DB;

class CorrelativeService
{
    // Resuelve la sede activa (Auth/Session globales del request), mismo patrón que
    // DashboardMarketService (Capa 1). Las series cuelgan de la sede, no de la empresa.
    use HasSedeActiva;

    public function __construct() {}

/*
{#2181 // app\Http\Services\Tenant\Sale\Sale\SaleService.php:41
  +"correlative": "1"
  +"serie": "NV01"
}
*/
    /**
     * Multi-Sede Capa C: correlativo ATÓMICO por (sede activa, tipo de documento).
     *
     * Reemplaza el COUNT(*)+1 (no atómico, reutilizaba número al anular) y el company_id=1.
     * Debe correr DENTRO de la transacción del documento (la venta la abre en
     * SaleController@store:238; la factura de OT en WorkOrderController:178) — el
     * lockForUpdate (SELECT ... FOR UPDATE) solo serializa dentro de transacción.
     *
     * current_number es MONOTÓNICO: solo sube. Anular un documento NO llama a este método,
     * así que current_number queda intacto → un número anulado NUNCA se reutiliza.
     */
    public function getCorrelative($document_type_id): object
    {
        $sedeId = $this->sedeActivaId();

        // Lock pesimista de la fila (sede, tipo) hasta el commit del documento.
        $ds = DB::table('document_serializations')
            ->where('sede_id', $sedeId)
            ->where('document_type_id', $document_type_id)
            ->lockForUpdate()
            ->first();

        if (! $ds) {
            throw new Exception('La sede activa no tiene serie configurada para este tipo de documento.');
        }

        // Próximo número: nunca decrementa; respeta start_number si se reconfiguró.
        $next = max((int) $ds->current_number + 1, (int) $ds->start_number);

        DB::table('document_serializations')
            ->where('id', $ds->id)
            ->update(['current_number' => $next]);

        return (object) ['correlative' => $next, 'serie' => $ds->serie];
    }
}

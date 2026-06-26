<?php

namespace App\Http\Services\Tenant\Maintenance;

use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\DocumentSerialization;
use App\Models\Tenant\Sede;

/**
 * Única fuente de verdad para generar las series de una sede.
 *
 * La llaman SedeController@store (sedes nuevas) y el provisioning (sede principal del
 * tenant). NO se repite la lógica: un solo lugar genera las series.
 *
 * El sufijo de la serie = `numero` de la sede con padding a 3 dígitos:
 *   sede numero=1 -> B001/F001/NV001 ; numero=2 -> B002/F002/NV002 ; ...
 * El prefijo sale del `parameter` del tipo de comprobante (general_table_details, GT=4).
 */
class SerieService
{
    /** general_table_id de "COMPROBANTES DE VENTA" (tipos de documento con serie). */
    private const GT_COMPROBANTES = 4;

    /**
     * Genera (idempotente) todas las series de la sede.
     * Data-driven: una serie por cada tipo de comprobante ACTIVO. Si se agregan tipos
     * (NC/ND/guía/ticket) a general_table_details, se generan automáticamente.
     */
    public function generarSeriesSede(Sede $sede): void
    {
        $sufijo = str_pad((string) $sede->numero, 3, '0', STR_PAD_LEFT);

        $tipos = GeneralTableDetail::where('general_table_id', self::GT_COMPROBANTES)
            ->where('status', 'ACTIVO')
            ->get();

        foreach ($tipos as $tipo) {
            DocumentSerialization::firstOrCreate(
                ['sede_id' => $sede->id, 'document_type_id' => $tipo->id],
                [
                    'company_id'     => 1,                          // legado (se deprecará; manda sede_id)
                    'serie'          => $tipo->parameter . $sufijo, // ej. B + 001 = B001
                    'description'    => $tipo->description,
                    'destiny'        => null,
                    'default'        => 'NO',
                    'number_limit'   => 8,
                    'start_number'   => 1,
                    'current_number' => 0,                          // próximo a emitir = start_number (1)
                    'final_number'   => 0,
                    'initiated'      => 'NO',
                ]
            );
        }
    }
}

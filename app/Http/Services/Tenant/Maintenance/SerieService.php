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
 * La serie SIEMPRE tiene 4 caracteres: `parameter` del tipo de comprobante + el `numero`
 * de la sede paddeado a la izquierda hasta completar 4 (parameter 1 char -> pad 3 dígitos,
 * parameter 2 chars -> pad 2 dígitos). Ejemplos con sede numero=1: B001, F001, NV01, FF01, BB01.
 * El prefijo sale del `parameter` del tipo de comprobante (general_table_details, GT=4).
 */
class SerieService
{
    /** general_table_id de "COMPROBANTES DE VENTA" (tipos de documento con serie). */
    private const GT_COMPROBANTES = 4;

    /** La serie completa (parameter + número de sede) siempre mide esto. */
    private const LARGO_SERIE = 4;

    /**
     * Genera (idempotente) todas las series de la sede.
     * Data-driven: una serie por cada tipo de comprobante ACTIVO. Si se agregan tipos
     * (NC/ND/guía/ticket) a general_table_details, se generan automáticamente.
     */
    public function generarSeriesSede(Sede $sede): void
    {
        $tipos = GeneralTableDetail::where('general_table_id', self::GT_COMPROBANTES)
            ->where('status', 'ACTIVO')
            ->get();

        foreach ($tipos as $tipo) {
            $digitos = self::LARGO_SERIE - strlen($tipo->parameter);
            $sufijo  = str_pad((string) $sede->numero, max($digitos, 1), '0', STR_PAD_LEFT);

            DocumentSerialization::firstOrCreate(
                ['sede_id' => $sede->id, 'document_type_id' => $tipo->id],
                [
                    'company_id'     => 1,                          // legado (se deprecará; manda sede_id)
                    'serie'          => $tipo->parameter . $sufijo, // ej. B + 001 = B001 ; FF + 01 = FF01
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

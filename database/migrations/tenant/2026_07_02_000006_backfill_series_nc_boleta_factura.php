<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Genera (para cada sede existente) la serie de los 2 tipos nuevos de NC (BB boleta / FF
 * factura) agregados en landlord. Serie siempre 4 caracteres: parameter + numero de sede
 * paddeado a la izquierda hasta completar 4 (mismo criterio que SerieService).
 *
 * Sin Eloquent a propósito: los modelos con $connection='tenant' hardcodeado (Sede,
 * DocumentSerialization, GeneralTableDetail) no resuelven bien la conexión durante
 * tenant:rebuild-template (cache de conexión no se refresca) -> DB::table() puro.
 */
return new class extends Migration
{
    public function up(): void
    {
        $sedes = DB::table('sedes')->get();

        $tipos = DB::connection('landlord')->table('general_table_details')
            ->where('general_table_id', 4)
            ->whereIn('parameter', ['BB', 'FF'])
            ->where('status', 'ACTIVO')
            ->get();

        foreach ($sedes as $sede) {
            foreach ($tipos as $tipo) {
                $exists = DB::table('document_serializations')
                    ->where('sede_id', $sede->id)
                    ->where('document_type_id', $tipo->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $digitos = max(4 - strlen($tipo->parameter), 1);
                $sufijo  = str_pad((string) $sede->numero, $digitos, '0', STR_PAD_LEFT);

                DB::table('document_serializations')->insert([
                    'company_id'       => 1,
                    'sede_id'          => $sede->id,
                    'document_type_id' => $tipo->id,
                    'serie'            => $tipo->parameter . $sufijo,
                    'description'      => $tipo->description,
                    'destiny'          => null,
                    'default'          => 'NO',
                    'number_limit'     => 8,
                    'start_number'     => 1,
                    'current_number'   => 0,
                    'final_number'     => 0,
                    'initiated'        => 'NO',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: no se borran series ya usadas por facturación.
    }
};

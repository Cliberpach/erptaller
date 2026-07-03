<?php

use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;

/**
 * Flag UMB_DNI: monto mínimo (S/) a partir del cual una boleta exige DNI del cliente.
 * Symbol de tipo NUMERIC (input libre), no toggle ni select. Backfill para tenants YA
 * provisionados; ConfigurationSeeder lo siembra para tenants nuevos.
 *
 * symbol es varchar(10) -> "UMB_DNI" (7 chars), no "UMB_BOL_DNI" (11, se trunca).
 *
 * IDEMPOTENTE (firstOrCreate por symbol).
 * GUARDA (patrón VEP/VVC/AMB_GRE): solo backfillea si la config ya está sembrada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Configuration::where('symbol', 'MCB')->exists()) {
            return; // tenant nuevo: lo siembra ConfigurationSeeder
        }

        Configuration::firstOrCreate(
            ['symbol' => 'UMB_DNI'],
            [
                'description' => 'MONTO MÍNIMO (S/) PARA EXIGIR DNI EN BOLETA',
                'property'    => '700',
                'group_name'  => 'SUNAT',
            ]
        );
    }

    public function down(): void
    {
        Configuration::where('symbol', 'UMB_DNI')->delete();
    }
};

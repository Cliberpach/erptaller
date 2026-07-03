<?php

use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;

/**
 * Flag AMB_GRE: ambiente de facturación electrónica Greenter (DEMO/PRODUCCION). Reemplaza la
 * lectura fija de company_invoices.environment (InvoicingManager ahora lee este symbol).
 * Backfill para tenants YA provisionados.
 *
 * IDEMPOTENTE (firstOrCreate por symbol -> correr dos veces no duplica).
 *
 * GUARDA de ordering (patrón VEP/VVC): solo backfillea si la config ya está sembrada (MCB
 * existe). En un tenant NUEVO la config aún no se sembró al migrar -> lo hace el
 * ConfigurationSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Configuration::where('symbol', 'MCB')->exists()) {
            return; // tenant nuevo: lo siembra ConfigurationSeeder
        }

        Configuration::firstOrCreate(
            ['symbol' => 'AMB_GRE'],
            [
                'description' => 'AMBIENTE DE FACTURACIÓN ELECTRÓNICA (GREENTER)',
                'property'    => 'DEMO',
                'group_name'  => 'SUNAT',
            ]
        );
    }

    public function down(): void
    {
        Configuration::where('symbol', 'AMB_GRE')->delete();
    }
};

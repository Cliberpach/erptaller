<?php

use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;

/**
 * Flag VEP: "EL USUARIO VENTAS PUEDE EDITAR EL PRECIO DE VENTA". property '1' = puede editar
 * (default -> preserva el comportamiento actual). Backfill para tenants YA provisionados.
 *
 * IDEMPOTENTE (firstOrCreate por symbol -> correr dos veces no duplica).
 *
 * GUARDA de ordering: solo backfillea si la config ya está sembrada (MCB existe). En un tenant
 * NUEVO la config aún no se sembró al momento de migrar -> se deja al ConfigurationSeeder, para
 * que VEP quede como 3ra fila (id 3) y NO corra los ids de MCB/VSOT que otros lookups leen por id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Configuration::where('symbol', 'MCB')->exists()) {
            return; // tenant nuevo: lo siembra ConfigurationSeeder (VEP como 3ra fila)
        }

        Configuration::firstOrCreate(
            ['symbol' => 'VEP'],
            [
                'description' => 'EL USUARIO VENTAS PUEDE EDITAR EL PRECIO DE VENTA',
                'property'    => '1',
            ]
        );
    }

    public function down(): void
    {
        Configuration::where('symbol', 'VEP')->delete();
    }
};

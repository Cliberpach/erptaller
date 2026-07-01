<?php

use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño de Configuración en grupos: columna group_name + backfill de los flags existentes
 * + flag nuevo VVC (ventas ve costo). El agrupamiento es SOLO presentación; el guardado
 * (property 0/1 por configuration_{id}) no cambia.
 *
 * IDEMPOTENTE: hasColumn guarda la columna; los update por symbol y el firstOrCreate no duplican.
 *
 * GUARDA de ordering (patrón VEP): el backfill + la fila VVC solo corren si la config ya está
 * sembrada (MCB existe). En un tenant NUEVO la config aún no se sembró al migrar -> lo hace el
 * ConfigurationSeeder (con group_name), sin correr los ids de MCB/VSOT/VEP.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('configuration', 'group_name')) {
            Schema::table('configuration', function (Blueprint $table) {
                $table->string('group_name', 50)->nullable()->after('symbol');
            });
        }

        if (! Configuration::where('symbol', 'MCB')->exists()) {
            return; // tenant nuevo: lo siembra ConfigurationSeeder
        }

        Configuration::where('symbol', 'MCB')->update(['group_name' => 'EMPRESA']);
        Configuration::where('symbol', 'VSOT')->update(['group_name' => 'OT']);
        Configuration::where('symbol', 'VEP')->update(['group_name' => 'VENTAS']);

        Configuration::firstOrCreate(
            ['symbol' => 'VVC'],
            [
                'description' => 'EL USUARIO VENTAS PUEDE VER EL COSTO DEL PRODUCTO',
                'property'    => '1', // default: puede ver -> preserva el comportamiento actual
                'group_name'  => 'VENTAS',
            ]
        );
    }

    public function down(): void
    {
        Configuration::where('symbol', 'VVC')->delete();

        if (Schema::hasColumn('configuration', 'group_name')) {
            Schema::table('configuration', function (Blueprint $table) {
                $table->dropColumn('group_name');
            });
        }
    }
};

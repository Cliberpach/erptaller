<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos adicionales de la OT (manuales, histórico por OT, no del vehículo):
 *  - mileage: kilometraje de tablero (entero, comparable/ordenable).
 *  - observation: observación de la OT (estado del auto / pedido del cliente). Distinta
 *    de la observación del vehículo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedInteger('mileage')->nullable()->after('fuel_level');
            $table->string('observation', 1000)->nullable()->after('mileage');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['mileage', 'observation']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-Sede Etapa 3a: cada almacén pertenece a una sede.
     * Va DESPUÉS de la migración de 'sedes' (102565) por el FK.
     * Nota: el stock/kardex siguen siendo por warehouse_id; sede_id solo agrupa almacenes.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('sede_id')->nullable()->after('id');
            $table->foreign('sede_id')->references('id')->on('sedes');

            $table->boolean('es_principal')->default(false)->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropColumn(['sede_id', 'es_principal']);
        });
    }
};

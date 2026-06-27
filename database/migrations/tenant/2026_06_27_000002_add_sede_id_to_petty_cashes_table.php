<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-Sede Etapa 4: las cajas pasan a colgar de la SEDE (paralelo a almacenes/series).
     * petty_cash_books NO lleva sede_id: la sede sale vía petty_cash_id (la caja).
     */
    public function up(): void
    {
        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->unsignedBigInteger('sede_id')->nullable()->after('id');
            $table->foreign('sede_id')->references('id')->on('sedes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropColumn('sede_id');
        });
    }
};

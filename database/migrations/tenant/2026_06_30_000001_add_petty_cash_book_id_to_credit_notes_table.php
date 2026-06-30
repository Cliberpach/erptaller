<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nota de Crédito - Capa 3: caja a la que se ATRIBUYE la NC = la caja ABIERTA del usuario
 * al emitir (no la del cobro original -> resuelve el caso caja-cerrada). Nullable: la NC
 * convertida/OT/crédito NO atribuye caja. La RESTA efectiva en el cuadre la hace el reporte
 * (Capa 6) leyendo esta columna. Sin backfill: no hay NC previas con efecto de caja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('petty_cash_book_id')->nullable()->after('sale_id');
        });
    }

    public function down(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('petty_cash_book_id');
        });
    }
};

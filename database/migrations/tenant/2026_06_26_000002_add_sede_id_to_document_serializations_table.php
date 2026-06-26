<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-Sede Etapa 5: las series pasan a colgar de la SEDE (no de la empresa).
     *
     * - sede_id: la serie pertenece a una sede (el que MANDA ahora). company_id queda
     *   redundante pero NO se borra todavía (lo lee código legado de emisión; se deprecará).
     * - current_number: correlativo atómico por (sede, tipo). Reemplaza el COUNT(*)+1
     *   no-atómico (eso se activa en Capa C). 0 = próximo a emitir es start_number.
     * - único (sede_id, document_type_id): una serie por tipo de documento y sede.
     */
    public function up(): void
    {
        Schema::table('document_serializations', function (Blueprint $table) {
            $table->unsignedBigInteger('sede_id')->nullable()->after('company_id');
            $table->foreign('sede_id')->references('id')->on('sedes')->onDelete('cascade');

            $table->integer('current_number')->default(0)->after('final_number');

            $table->unique(['sede_id', 'document_type_id'], 'ds_sede_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('document_serializations', function (Blueprint $table) {
            $table->dropUnique('ds_sede_type_unique');
            $table->dropForeign(['sede_id']);
            $table->dropColumn(['sede_id', 'current_number']);
        });
    }
};

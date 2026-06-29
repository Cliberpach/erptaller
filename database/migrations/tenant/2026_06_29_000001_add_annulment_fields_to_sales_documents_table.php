<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de anulación de ventas (documento interno: Ticket / Nota de Venta).
 * Quién/cuándo/por qué anuló. El estado en sí vive en sales_documents.status
 * (enum ACTIVO/ANULADO, ya existente).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->timestamp('annulled_at')->nullable()->after('status');
            $table->unsignedBigInteger('annulled_by')->nullable()->after('annulled_at');
            $table->string('annulled_by_name', 160)->nullable()->after('annulled_by');
            $table->string('annulment_reason', 255)->nullable()->after('annulled_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->dropColumn(['annulled_at', 'annulled_by', 'annulled_by_name', 'annulment_reason']);
        });
    }
};

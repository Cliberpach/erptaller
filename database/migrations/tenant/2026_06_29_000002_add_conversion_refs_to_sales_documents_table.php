<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Referencias cruzadas de conversión documento interno -> fiscal.
 * converted_to_id  : en el TICKET/NV -> apunta al comprobante fiscal (boleta/factura).
 * converted_from_id: en la BOLETA/FACTURA -> apunta al documento interno de origen.
 * Self-FK nullable. La conversión NO mueve stock ni caja (solo formaliza).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_to_id')->nullable()->after('annulment_reason');
            $table->unsignedBigInteger('converted_from_id')->nullable()->after('converted_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->dropColumn(['converted_to_id', 'converted_from_id']);
        });
    }
};

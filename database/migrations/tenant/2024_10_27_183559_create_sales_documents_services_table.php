<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_documents_services', function (Blueprint $table) {

            $table->unsignedBigInteger('sale_document_id');
            $table->foreign('sale_document_id')->references('id')->on('sales_documents');

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('id')->on('services');

            $table->string('service_code', 160);
            $table->string('service_unit', 160);
            $table->string('service_description', 160);
            $table->string('service_name', 200);

            $table->decimal('quantity', 16, 2)->unsigned();
            $table->decimal('price_sale', 16, 6);
            $table->decimal('amount', 16, 6);

            // ===== SUNAT =====
            $table->decimal('mto_valor_unitario', 16, 6);
            $table->decimal('mto_valor_venta', 16, 6);
            $table->decimal('mto_base_igv', 16, 6);
            $table->decimal('porcentaje_igv', 16, 6);
            $table->decimal('igv', 16, 6);
            $table->unsignedBigInteger('tip_afe_igv');
            $table->decimal('total_impuestos', 16, 6);
            $table->decimal('mto_precio_unitario', 16, 6);

            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->primary(['sale_document_id', 'service_id'], 'pk_sd_serv');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_documents_services');
    }
};

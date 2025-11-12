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
        Schema::create('reservation_documents_detail', function (Blueprint $table) {

            $table->unsignedBigInteger('reservation_document_id');
            $table->foreign('reservation_document_id')->references('id')->on('reservation_documents');

            $table->string('product_code',100);
            $table->string('product_unit',100);
            $table->string('product_description',400);
            $table->string('product_name',200);

            $table->decimal('quantity', 10, 2)->unsigned();
            $table->decimal('price_sale', 10, 2)->unsigned();
            $table->decimal('amount', 10, 2)->unsigned();

            /* ======= SUNAT ======== */ 
            $table->decimal('mto_valor_unitario', 16, 6);
            $table->decimal('mto_valor_venta', 16, 6);
            $table->decimal('mto_base_igv', 16, 6);
            $table->decimal('porcentaje_igv', 16, 6);
            $table->decimal('igv', 16, 6);
            $table->unsignedBigInteger('tip_afe_igv');
            $table->decimal('total_impuestos', 16, 6);
            $table->decimal('mto_precio_unitario', 16, 6);


            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->primary(['reservation_document_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_documents_detail');
    }
};

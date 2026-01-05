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
        Schema::create('credit_notes_details', function (Blueprint $table) {
            $table->unsignedBigInteger('credit_note_id');
            $table->foreign('credit_note_id')->references('id')->on('credit_notes');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('warehouse_name', 120);
            $table->string('product_name', 500);
            $table->string('category_name', 160);
            $table->string('brand_name', 160);

            $table->string('codProducto');
            $table->string('unity');
            $table->longText('description');

            $table->decimal('quantity', 16, 6)->unsigned();
            $table->decimal('sale_price', 16, 6)->unsigned();
            $table->decimal('sale_price_new', 16, 6)->unsigned();

            $table->decimal('amount', 16, 6)->unsigned();
            $table->decimal('amount_new', 16, 6)->unsigned();

            $table->decimal('discount_price', 16, 6)->unsigned();
            $table->decimal('discount_import', 16, 6)->unsigned();

            $table->decimal('mtoBaseIgv', 16, 6)->unsigned();
            $table->decimal('porcentajeIgv', 16, 6)->unsigned();
            $table->decimal('igv', 16, 6)->unsigned();
            $table->decimal('tipAfeIgv', 16, 6)->unsigned();

            $table->decimal('totalImpuestos', 16, 6)->unsigned();
            $table->decimal('mtoValorVenta', 16, 6)->unsigned();
            $table->decimal('mtoValorUnitario', 16, 6)->unsigned();
            $table->decimal('mtoPrecioUnitario', 16, 6)->unsigned();

            $table->primary(['credit_note_id', 'product_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes_details');
    }
};

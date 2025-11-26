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
        Schema::create('work_orders_products', function (Blueprint $table) {

            $table->unsignedBigInteger('work_order_id');
            $table->foreign('work_order_id')->references('id')->on('work_orders');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->string('warehouse_name', 120);

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->string('product_code', 100);
            $table->string('product_unit', 100);
            $table->string('product_description', 400);
            $table->string('product_name', 200);
            $table->string('category_name', 200);
            $table->string('brand_name', 200);

            $table->decimal('quantity', 16, 6)->unsigned();
            $table->decimal('price_sale', 16, 6)->unsigned();
            $table->decimal('amount', 16, 6)->unsigned();

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->primary(['work_order_id', 'product_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders_products');
    }
};

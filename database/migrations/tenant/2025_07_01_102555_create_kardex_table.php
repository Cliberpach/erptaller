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
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_id')->nullable();
            $table->foreign('sale_id')->references('id')->on('sales_documents');

            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchase_documents');

            $table->unsignedBigInteger('note_income_id')->nullable();
            $table->foreign('note_income_id')->references('id')->on('notes_income');

            $table->unsignedBigInteger('note_release_id')->nullable();
            $table->foreign('note_release_id')->references('id')->on('notes_release');

            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->foreign('work_order_id')->references('id')->on('work_orders');

            $table->enum('type', ['ENTRADA', 'SALIDA']);
            $table->string('document_serie', 30);
            $table->dateTime('date');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->string('warehouse_name', 120);

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->string('product_unit', 100);
            $table->string('product_name', 200);
            $table->string('category_name', 200);
            $table->string('brand_name', 200);

            $table->decimal('quantity', 16, 6)->unsigned();
            $table->decimal('sale_price', 16, 6)->unsigned();
            $table->decimal('purchase_price', 16, 6)->unsigned();
            $table->decimal('amount', 16, 6)->unsigned();

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 160)->nullable();
            $table->string('customer_type_document_abbreviation', 20)->nullable();
            $table->string('customer_document_number', 20)->nullable();

            $table->decimal('total', 16, 6)->unsigned()->nullable();
            $table->decimal('subtotal', 16, 6)->unsigned()->nullable();
            $table->decimal('igv', 16, 6)->unsigned()->nullable();

            $table->unsignedBigInteger('creator_user_id');
            $table->string('creator_user_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};

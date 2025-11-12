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

            $table->unsignedBigInteger('warehouse_id')->comment('ALMACÉN');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->unsignedBigInteger('product_id')->comment('PRODUCTO');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('brand_id')->comment('MARCA');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->unsignedBigInteger('category_id')->comment('CATEGORÍA');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->decimal('quantity', 10, 2)->unsigned()->comment('CANTIDAD');
            $table->decimal('price_sale', 10, 2)->unsigned()->nullable()->comment('PRECIO VENTA');
            $table->decimal('amount', 10, 2)->unsigned()->nullable()->comment('SUBTOTAL');

            $table->enum('type',['IN','OUT'])->comment('IN OUT');
            $table->string('document')->comment('SERIE DEL DOCUMENTO');

            $table->string('product_name',160);
            $table->string('brand_name',160);
            $table->string('category_name',160);

            $table->unsignedBigInteger('sale_document_id')->nullable()->comment('VENTA');
            $table->foreign('sale_document_id')->references('id')->on('sales_documents');

            $table->unsignedBigInteger('note_income_id')->nullable()->comment('NOTA INGRESO');
            $table->foreign('note_income_id')->references('id')->on('notes_income');

            $table->unsignedBigInteger('note_release_id')->nullable()->comment('NOTA SALIDA');
            $table->foreign('note_release_id')->references('id')->on('notes_release');

            $table->unsignedBigInteger('purchase_document_id')->nullable()->comment('COMPRA');
            $table->foreign('purchase_document_id')->references('id')->on('purchase_documents');

            $table->unsignedBigInteger('user_recorder_id')->comment('REGISTRADOR');
            $table->foreign('user_recorder_id')->references('id')->on('users');

            $table->string('user_recorder_name',160);

            $table->unsignedBigInteger('customer_id')->nullable()->comment('CLIENTE');
            //$table->foreign('customer_id')->references('id')->on('customers');

            $table->string('customer_name',160)->nullable();

            $table->dateTime('registration_date')->comment('FECHA REGISTRO DEL DOCUMENTO');

            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');

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

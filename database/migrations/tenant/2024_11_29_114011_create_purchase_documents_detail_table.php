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
        Schema::create('purchase_documents_detail', function (Blueprint $table) {
            
            $table->unsignedBigInteger('purchase_document_id');
            $table->foreign('purchase_document_id')->references('id')->on('purchase_documents');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('warehouse_name',160);

            $table->string('product_name',200);
            $table->string('category_name',200);
            $table->string('brand_name',200);

            $table->decimal('quantity', 10, 2)->unsigned();
            $table->decimal('purchase_price', 10, 2)->unsigned();
            $table->decimal('subtotal', 10, 2)->unsigned();

        
            $table->primary(['purchase_document_id', 'product_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_documents_detail');
    }
};

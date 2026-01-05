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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->string('name', 500);
            $table->string('description')->nullable();
            $table->decimal('sale_price', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->integer('stock');
            $table->integer('stock_min');
            $table->string('code_factory')->nullable();
            $table->string('code_bar')->nullable();
            $table->string('image')->nullable();
            $table->longText('img_route')->nullable();
            $table->longText('img_name')->nullable();
            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('creator_user_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

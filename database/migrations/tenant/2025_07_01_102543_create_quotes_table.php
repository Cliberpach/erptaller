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
        Schema::create('quotes', function (Blueprint $table) {

            $table->id();

            $table->date('expiration_date')->nullable();
            $table->unsignedInteger('days_validity')->default(0);

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->string('warehouse_name', 120);

            $table->unsignedBigInteger('customer_id');

            $table->string('customer_name', 160);
            $table->string('customer_type_document_abbreviation', 20);
            $table->string('customer_document_number', 20);

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            $table->string('plate', 8);

            $table->decimal('total', 16, 6)->unsigned();
            $table->decimal('subtotal', 16, 6)->unsigned();
            $table->decimal('igv', 16, 6)->unsigned();

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('create_user_name')->nullable();

            $table->enum('status', ['ACTIVO', 'ANULADO', 'CONVERTIDO', 'EXPIRADO'])->default('ACTIVO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};

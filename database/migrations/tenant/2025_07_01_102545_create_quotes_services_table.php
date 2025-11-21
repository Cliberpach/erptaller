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
        Schema::create('quotes_services', function (Blueprint $table) {

            $table->unsignedBigInteger('quote_id');
            $table->foreign('quote_id')->references('id')->on('quotes');

            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('id')->on('services');

            $table->string('service_name', 160);

            $table->decimal('quantity', 16, 6)->unsigned();
            $table->decimal('price_sale', 16, 6)->unsigned();
            $table->decimal('amount', 16, 6)->unsigned();

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->primary(['quote_id', 'service_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes_services');
    }
};

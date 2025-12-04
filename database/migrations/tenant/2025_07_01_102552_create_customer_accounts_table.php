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
        Schema::create('customer_accounts', function (Blueprint $table) {

            $table->id();

            $table->unsignedInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales_documents');

            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();

            $table->unsignedDecimal('amount', 16, 6);
            $table->text('agreement')->nullable();

            $table->unsignedDecimal('balance');

            $table->enum('status', ['PENDIENTE', 'PAGADO', 'ANULADO'])->default('PENDIENTE');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_accounts');
    }
};

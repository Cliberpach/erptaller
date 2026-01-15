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
        Schema::create('supplier_accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchase_documents');

            $table->string('document_number')->nullable();
            $table->date('document_date')->nullable();

            $table->unsignedDecimal('amount', 16, 6);
            $table->unsignedDecimal('paid', 16, 6)->default(0);
            $table->unsignedDecimal('balance', 16, 6);

            $table->enum('status', ['PENDIENTE', 'PAGADO', 'ANULADO'])->default('PENDIENTE');

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->string('creator_user_name')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();
            $table->string('delete_user_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_accounts');
    }
};

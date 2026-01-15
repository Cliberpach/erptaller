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
        Schema::create('purchase_documents', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_date');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->string('supplier_name', 200);
            $table->string('supplier_type_document_abbreviation', 20);
            $table->string('supplier_document_number', 20);

            $table->unsignedBigInteger('user_recorder_id');
            $table->foreign('user_recorder_id')->references('id')->on('users');

            $table->string('user_recorder_name', 160);

            $table->string('condition', 100);
            $table->string('currency', 100);

            $table->string('document_type', 160);
            $table->string('serie', 20);
            $table->unsignedInteger('correlative');

            $table->string('observation', 200)->nullable();

            $table->tinyInteger('prices_with_igv')->unsigned();
            $table->decimal('igv', 16, 4)->unsigned();
            $table->decimal('subtotal', 16, 4)->unsigned();
            $table->decimal('amount_igv', 16, 4)->unsigned();
            $table->decimal('total', 16, 4)->unsigned();

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->unsignedBigInteger('payment_condition_id');
            $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');

            $table->string('payment_condition_name', 100);
            $table->unsignedInteger('payment_condition_days');
            $table->enum('payment_status', ['PAGADO', 'PENDIENTE'])->default('PENDIENTE');

            $table->date('registration_date');
            $table->date('expiration_date');

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
        Schema::dropIfExists('purchase_documents');
    }
};

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
        Schema::create('exit_money', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proof_payment_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('user_id');
            $table->string('payment_type');
            $table->string('number', 15);
            $table->date('date');
            $table->enum('reason', ['GASTO', 'DEVOLUCION','COMPRAS','LIMPIEZA','ENVIO']);
            $table->double('total');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('proof_payment_id')->references('id')->on('proof_payments');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_money');
    }
};

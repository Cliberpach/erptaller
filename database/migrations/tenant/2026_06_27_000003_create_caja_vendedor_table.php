<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-Sede Etapa 4: combo de vendedores por caja (M:N caja<->usuarios).
     * Una caja la abre un vendedor del combo; todos los del combo venden sobre esa apertura.
     */
    public function up(): void
    {
        Schema::create('caja_vendedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('petty_cash_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('petty_cash_id')->references('id')->on('petty_cashes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['petty_cash_id', 'user_id'], 'caja_vendedor_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_vendedor');
    }
};

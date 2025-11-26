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
        Schema::create('work_orders_technicians', function (Blueprint $table) {

            $table->unsignedBigInteger('work_order_id');
            $table->foreign('work_order_id')->references('id')->on('work_orders');

            $table->unsignedBigInteger('technical_id');
            $table->foreign('technical_id')->references('id')->on('users');

            $table->string('technical_name',255);

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            
            $table->primary(['work_order_id', 'technical_id']);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders_technicians');
    }
};

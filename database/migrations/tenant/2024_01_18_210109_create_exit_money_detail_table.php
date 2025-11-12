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
        Schema::create('exit_money_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exit_money_id');
            $table->string('description');
            $table->double('total');
            $table->timestamps();
            $table->foreign('exit_money_id')->references('id')->on('exit_money');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_money_detail');
    }
};

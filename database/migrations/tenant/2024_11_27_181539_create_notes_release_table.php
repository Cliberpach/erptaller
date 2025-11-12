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
        Schema::create('notes_release', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_recorder_id');
            $table->foreign('user_recorder_id')->references('id')->on('users');

            $table->string('user_recorder_name',160);

            $table->string('observation',200)->nullable();
            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_release');
    }
};

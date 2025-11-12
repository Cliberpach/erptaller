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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_field_id');
            $table->string('field');
            $table->string('location')->nullable();
            $table->enum('status', ['LIBRE', 'RESERVADO', 'ALQUILADO'])->default('LIBRE');
            $table->boolean('isDeleted')->default(false);
            $table->unsignedDecimal('day_price', 16, 6);
            $table->unsignedDecimal('night_price', 16, 6);
            $table->timestamps();

            $table->foreign('type_field_id')->references('id')->on('type_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};

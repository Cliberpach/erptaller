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
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            // numero: correlativo lógico de la sede (principal = 1, siguientes 2,3...).
            // Manda para las series SUNAT (sede 1 -> B001, sede 2 -> B002).
            $table->unsignedInteger('numero')->unique();
            $table->string('nombre', 120);
            $table->string('codigo', 20);
            $table->boolean('es_principal')->default(false);
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};

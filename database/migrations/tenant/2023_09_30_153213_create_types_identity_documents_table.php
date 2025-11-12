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
        Schema::create('types_identity_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('abbreviation',20);
            $table->string('code',4)->default('00');
            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_identity_documents');
    }
};

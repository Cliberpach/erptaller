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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('type_identity_document_id');

            $table->string('type_document_name',100); 
            $table->string('type_document_abbreviation',20); 
            $table->string('type_document_code',4); 
            
            $table->string('document_number',20);
            $table->string('name',200);
            $table->string('address',150)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('email',150)->nullable();
           
            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

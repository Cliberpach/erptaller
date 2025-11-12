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
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_name');
            $table->string('customer_document_number', 20);
            $table->string('customer_phone', 20)->nullable();
        
            $table->unsignedBigInteger('booking_id');
            
            $table->unsignedBigInteger('field_id');
            $table->string('field_name');
            
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('total_hours');
        
            $table->tinyInteger('ball')->default(0);
            $table->tinyInteger('vest')->default(0);
            $table->tinyInteger('dni')->default(0);
        
            $table->string('ruc_number')->nullable();
            $table->string('razon_social')->nullable();
        
            $table->decimal('amount', 10, 2);
            $table->enum('estado', ['PENDIENTE', 'PAGADO'])->default('PENDIENTE');

            $table->boolean('facturado')->default(0);
        
            $table->timestamps();
            $table->date('date');

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};

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
        Schema::create('appointments', function (Blueprint $table) {

            $table->id();

            $table->string('name', 500);
            $table->string('description', 500)->nullable();

            $table->unsignedBigInteger('customer_id');
            $table->string('customer_name', 160);
            $table->string('customer_type_document_abbreviation', 20);
            $table->string('customer_document_number', 20);

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            $table->string('plate', 8);

            $table->date('start_date');
            $table->date('end_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->enum('type_calendar', ['PERSONAL', 'TRABAJO']);
            $table->boolean('full_day')->default(false);

            $table->string('location', 500)->nullable();

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            //=========== AUDITORÍA ==========
            $table->unsignedBigInteger('creator_user_id');
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('creator_user_name');

            //========= ÍNDICES ========
            $table->index(['start_date', 'end_date'], 'idx_dates_range');
            $table->index('status', 'idx_status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->string('plate', 8);
            $table->string('name', 160);

            $table->string('vin', 160)->nullable();
            $table->string('serie', 160)->nullable();

            $table->unsignedBigInteger('customer_id');

            $table->unsignedBigInteger('brand_id');

            $table->unsignedBigInteger('model_id');

            $table->unsignedBigInteger('year_id')->nullable();

            $table->unsignedBigInteger('color_id');

            $table->string('observation', 300)->nullable();

            $table->string('vin', 160)->nullable();
            $table->string('serie', 160)->nullable();


            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('create_user_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

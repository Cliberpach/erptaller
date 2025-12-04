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
        Schema::create('petty_cash_books', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('petty_cash_id');
            $table->foreign('petty_cash_id')->references('id')->on('petty_cashes')->onDelete('cascade');

            $table->unsignedBigInteger('shift_id');
            $table->foreign('shift_id')->references('id')->on('shifts');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->string('creator_user_name')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();
            $table->string('delete_user_name')->nullable();

            $table->string('name');
            $table->enum('status', ['ANULADO', 'ABIERTO', 'CERRADO'])->default('CERRADO');
            $table->decimal('initial_amount', 10, 2);
            $table->decimal('closing_amount', 10, 2)->nullable();
            $table->datetime('initial_date');
            $table->datetime('final_date')->nullable();
            $table->decimal('sale_day', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_books');
    }
};

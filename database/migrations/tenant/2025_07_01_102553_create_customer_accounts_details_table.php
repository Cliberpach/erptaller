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
        Schema::create('customer_accounts_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_account_id');
            $table->foreign('customer_account_id')->references('id')->on('customer_accounts');

            $table->foreignId('petty_cash_book_id')->references('id')->on('petty_cash_books');

            $table->date('date');
            $table->text('observation')->nullable();
            $table->text('img_route')->nullable();

            $table->unsignedDecimal('amount', 16, 6);

            $table->unsignedInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');

            $table->unsignedDecimal('cash', 15, 6)->nullable()->default(0.00);
            $table->unsignedDecimal('amount', 16, 6)->nullable()->default(0.00);
            $table->unsignedDecimal('balance',16,6)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_accounts_details');
    }
};

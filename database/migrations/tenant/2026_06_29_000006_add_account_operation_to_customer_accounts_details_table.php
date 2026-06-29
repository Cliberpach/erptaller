<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trazabilidad por cuenta del cobro de CxC: a qué cuenta bancaria entró el cobro
 * y su N° de operación (para conciliar contra el extracto). El método ya se guarda
 * (payment_method_id); faltaban la cuenta y la operación. Ambos nullable (efectivo
 * no tiene cuenta).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_accounts_details', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_method_id');
            $table->string('operation_number', 20)->nullable()->after('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_accounts_details', function (Blueprint $table) {
            $table->dropColumn(['bank_account_id', 'operation_number']);
        });
    }
};

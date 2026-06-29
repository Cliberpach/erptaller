<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Egresos contra cuenta (Capa A): método + cuenta de origen + n° operación en el
     * egreso (a nivel cabecera). bank_account_id NULL = efectivo (sale de la caja física).
     * payment_type (legacy varchar) se depreca a nullable (la lee solo el PDF del egreso;
     * se reapunta al método en B/C).
     */
    public function up(): void
    {
        // 1) Columnas nullable (para poder backfillear sin romper el NOT NULL).
        Schema::table('exit_money', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('petty_cash_book_id');
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_method_id'); // NULL = efectivo
            $table->string('operation_number', 100)->nullable()->after('bank_account_id');
            $table->string('payment_type')->nullable()->change(); // legacy deprecada
        });

        // 2) Backfill: egresos existentes -> EFECTIVO (id 1). 0 huérfanos.
        DB::table('exit_money')->whereNull('payment_method_id')->update(['payment_method_id' => 1]);

        // 3) Ahora sí NOT NULL + FKs + índices.
        Schema::table('exit_money', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable(false)->change();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts');
            $table->index('payment_method_id');
            $table->index('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('exit_money', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['bank_account_id']);
            $table->dropIndex(['exit_money_payment_method_id_index']);
            $table->dropIndex(['exit_money_bank_account_id_index']);
            $table->dropColumn(['payment_method_id', 'bank_account_id', 'operation_number']);
            // payment_type se deja nullable (no se revierte a NOT NULL para no romper).
        });
    }
};

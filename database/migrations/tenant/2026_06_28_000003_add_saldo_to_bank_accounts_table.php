<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kardex de Cuenta: la cuenta tiene un saldo guardado (punto de partida del kardex).
     * default 0 -> backfill automático (cuentas existentes arrancan en 0), 0 huérfanos.
     */
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->decimal('saldo', 14, 2)->default(0)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
    }
};

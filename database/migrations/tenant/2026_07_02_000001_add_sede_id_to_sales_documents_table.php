<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-Sede: la venta pasa a llevar sede_id directo (evita join contra petty_cashes /
     * work_orders para filtrar por sede). Sin backfill: no hay data en BD todavía.
     */
    public function up(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('sede_id')->nullable()->after('id');
            $table->foreign('sede_id')->references('id')->on('sedes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropColumn('sede_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega las columnas de unidad de medida que el código (ProductController,
     * ProductDto, DashboardMarketService) ya usa pero que el esquema nunca tuvo.
     *
     * La unidad vive en general_table_details (BD landlord/central), por eso NO hay
     * FK física (sería cross-database): unit_id es solo el id; unit_symbol/unit_name
     * quedan desnormalizados (los setea ProductDto desde GeneralTableDetail al crear).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('code_bar');
            $table->string('unit_symbol')->nullable()->after('unit_id');
            $table->string('unit_name')->nullable()->after('unit_symbol');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'unit_symbol', 'unit_name']);
        });
    }
};

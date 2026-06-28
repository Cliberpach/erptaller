<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos P1: cada nodo del menú lleva el permiso Spatie que lo gobierna (data-driven).
     * El menú (P2) filtrará con @can($node->permission). NULL = sin permiso (landlord-only o
     * contenedor) → el menú lo resolverá. Vive en landlord (master) y tenant (copia).
     */
    public function up(): void
    {
        Schema::table('module_children', function (Blueprint $table) {
            $table->string('permission')->nullable()->after('route_name');
        });
        Schema::table('module_grand_children', function (Blueprint $table) {
            $table->string('permission')->nullable()->after('route_name');
        });
    }

    public function down(): void
    {
        Schema::table('module_children', fn (Blueprint $t) => $t->dropColumn('permission'));
        Schema::table('module_grand_children', fn (Blueprint $t) => $t->dropColumn('permission'));
    }
};

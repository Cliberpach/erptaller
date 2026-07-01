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
        Schema::table('work_orders_services', function (Blueprint $table) {
            $table->text('service_description')->nullable()->after('service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders_services', function (Blueprint $table) {
            $table->dropColumn('service_description');
        });
    }
};

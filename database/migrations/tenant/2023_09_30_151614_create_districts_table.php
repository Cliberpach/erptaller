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
        Schema::create('districts', function (Blueprint $table) {
            $table->char('id', 6)->primary();
            $table->char('department_id', 2);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('department');
            $table->char('province_id', 4);
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->string('province');
            $table->string('name');
            $table->string('legal_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};

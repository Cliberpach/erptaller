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
        Schema::create('module_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("module_id");
            $table->foreign("module_id")->references("id")->on("modules")->onDelete("cascade");
            $table->string("description", 50);
            $table->string("route_name", 50)->nullable();
            $table->integer("order");
            $table->string("show")->nullable()->default('tenant');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_children');
    }
};
